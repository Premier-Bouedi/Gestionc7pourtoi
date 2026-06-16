<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrderApiController extends Controller
{
    /**
     * Reçoit un panier du site vitrine (http://127.0.0.1) ou de l'app mobile,
     * vérifie le stock de chaque produit, le déduit atomiquement dans une
     * transaction SQL, puis crée la commande et ses lignes.
     *
     * Payload JSON attendu :
     * {
     *   "customer_name":      "Fatima Ondo",
     *   "customer_whatsapp":  "+24177001234",
     *   "address_libreville": "Quartier Glass, Libreville",
     *   "items": [
     *     { "product_id": "<uuid>", "quantity": 2 },
     *     { "product_id": "<uuid>", "quantity": 1 }
     *   ]
     * }
     */
    public function store(Request $request): JsonResponse
    {
        // ── 1. VALIDATION DE LA REQUÊTE ─────────────────────────────────────
        $validated = $request->validate([
            'customer_name'       => 'required|string|max:255',
            'customer_whatsapp'   => 'required|string|max:50',
            'address_libreville'  => 'required|string|max:500',
            'items'               => 'required|array|min:1',
            'items.*.product_id'  => 'required|uuid|exists:products,id',
            'items.*.quantity'    => 'required|integer|min:1',
        ]);

        // ── 2. TRANSACTION ATOMIQUE ──────────────────────────────────────────
        try {
            $order = DB::transaction(function () use ($validated): Order {

                // 2a. Charger tous les produits demandés en une seule requête
                //     avec un verrou pessimiste pour éviter les doubles ventes.
                $productIds = collect($validated['items'])->pluck('product_id');
                $products   = Product::whereIn('id', $productIds)
                                     ->lockForUpdate()
                                     ->get()
                                     ->keyBy('id');

                // 2b. Vérification des stocks AVANT toute écriture
                foreach ($validated['items'] as $line) {
                    $product  = $products->get($line['product_id']);
                    $demanded = (int) $line['quantity'];

                    if ($product->stock_libreville < $demanded) {
                        // On lève une exception métier – la transaction sera annulée automatiquement
                        throw new \DomainException(
                            "Stock insuffisant pour le modèle « {$product->name} – {$product->model} » "
                            . "(disponible : {$product->stock_libreville}, demandé : {$demanded})."
                        );
                    }
                }

                // 2c. Calculer le montant total et créer la commande
                $totalAmount = 0;
                foreach ($validated['items'] as $line) {
                    $product      = $products->get($line['product_id']);
                    $totalAmount += $product->price_xaf * (int) $line['quantity'];
                }

                $order = Order::create([
                    'customer_name'      => $validated['customer_name'],
                    'customer_whatsapp'  => $validated['customer_whatsapp'],
                    'address_libreville' => $validated['address_libreville'],
                    'total_amount'       => $totalAmount,
                    'status'             => 'pending',
                ]);

                // 2d. Déduire le stock et créer les lignes de commande
                foreach ($validated['items'] as $line) {
                    $product  = $products->get($line['product_id']);
                    $quantity = (int) $line['quantity'];

                    // Décrémentation du stock
                    $product->decrement('stock_libreville', $quantity);

                    // Rechargement pour obtenir la valeur à jour
                    $product->refresh();

                    StockMovement::create([
                        'product_id'  => $product->id,
                        'user_id'     => User::where('role', 'admin')->first()?->id ?? User::first()?->id,
                        'type'        => 'out',
                        'quantity'    => $quantity,
                        'description' => "Achat Vitrine/Mobile - Commande #{$order->id}",
                    ]);

                    // Création de la ligne de commande
                    OrderItem::create([
                        'order_id'       => $order->id,
                        'product_id'     => $product->id,
                        'quantity'       => $quantity,
                        'unit_price_xaf' => $product->price_xaf,
                    ]);

                    // 2e. Journalisation si le stock est tombé à zéro (rupture)
                    if ($product->stock_libreville <= 0) {
                        Log::warning("RUPTURE DE STOCK – Produit : {$product->name} – {$product->model} (ID : {$product->id})");
                    }
                }

                return $order->load('items.product');
            });

            return response()->json([
                'success' => true,
                'message' => 'Commande créée avec succès.',
                'data'    => $order,
            ], 201);

        } catch (\DomainException $e) {
            // Erreur métier : stock insuffisant
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'error'   => 'STOCK_INSUFFISANT',
            ], 422);

        } catch (\Throwable $e) {
            // Erreur inattendue : base de données, etc.
            Log::error('OrderApiController@store – Erreur inattendue : ' . $e->getMessage(), [
                'exception' => $e,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Une erreur serveur est survenue. Veuillez réessayer.',
                'error'   => 'SERVER_ERROR',
            ], 500);
        }
    }

    /**
     * Renvoie les commandes assignées à Libreville pour l'application mobile Flutter.
     * Inclut les lignes de produits pour affichage dans la liste de tournées.
     */
    public function deliveries(): JsonResponse
    {
        $orders = Order::with('items.product')
                       ->whereIn('status', ['pending', 'ready_for_delivery'])
                       ->latest()
                       ->get();

        return response()->json([
            'success' => true,
            'data'    => $orders,
        ]);
    }

    /**
     * Reçoit le changement de statut depuis l'application mobile.
     * Statuts valides : pending | ready_for_delivery | delivered | incident
     */
    public function updateStatus(Request $request, string $id): JsonResponse
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,ready_for_delivery,delivered,incident',
        ]);

        $order         = Order::findOrFail($id);
        $order->status = $validated['status'];
        $order->save();

        return response()->json([
            'success' => true,
            'message' => 'Statut mis à jour avec succès.',
            'data'    => $order->load('items.product', 'incidents'),
        ]);
    }
}
