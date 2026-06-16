<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class InvoiceController extends Controller
{
    /**
     * Affiche l'écran de création de facture (Caisse).
     * Fournit la liste des clients et des produits disponibles au caissier.
     */
    public function create(): View
    {
        $clients  = Client::orderBy('name')->get(['id', 'name', 'email']);
        $products = Product::orderBy('name')
                           ->where('stock_libreville', '>', 0)
                           ->get(['id', 'name', 'model', 'price_xaf', 'price_mad', 'stock_libreville']);

        return view('invoices.create', compact('clients', 'products'));
    }

    /**
     * Valide et crée une nouvelle facture ERP depuis l'écran caisse.
     *
     * La déduction du stock est effectuée dans une transaction atomique :
     * si une écriture échoue (ex. rupture détectée en cours de route),
     * toute l'opération est annulée (rollback automatique).
     *
     * Payload formulaire attendu :
     *  - client_id       : UUID client existant
     *  - issue_date      : date ISO
     *  - due_date        : date ISO (>= issue_date)
     *  - notes           : texte libre (optionnel)
     *  - items[]         : tableau de lignes
     *      - product_id  : UUID produit (optionnel – null = service libre)
     *      - description : libellé de la ligne
     *      - quantity    : entier >= 1
     *      - unit_price  : décimal >= 0
     */
    public function store(Request $request): RedirectResponse
    {
        // ── 1. VALIDATION ────────────────────────────────────────────────────
        $validated = $request->validate([
            'client_id'              => 'required|uuid|exists:clients,id',
            'issue_date'             => 'required|date',
            'due_date'               => 'required|date|after_or_equal:issue_date',
            'notes'                  => 'nullable|string|max:1000',
            'items'                  => 'required|array|min:1',
            'items.*.product_id'     => 'nullable|uuid|exists:products,id',
            'items.*.description'    => 'required|string|max:255',
            'items.*.quantity'       => 'required|integer|min:1',
            'items.*.unit_price'     => 'required|numeric|min:0',
        ]);

        // ── 2. TRANSACTION ATOMIQUE ──────────────────────────────────────────
        try {
            DB::transaction(function () use ($validated): void {

                // 2a. Rassembler les IDs de produits liés (on ignore les lignes service)
                $productIds = collect($validated['items'])
                    ->pluck('product_id')
                    ->filter()
                    ->unique();

                // 2b. Verrou pessimiste sur les produits concernés
                $products = Product::whereIn('id', $productIds)
                                   ->lockForUpdate()
                                   ->get()
                                   ->keyBy('id');

                // 2c. Vérification des stocks AVANT toute écriture
                foreach ($validated['items'] as $line) {
                    if (empty($line['product_id'])) {
                        continue; // Ligne de service libre → pas de stock à vérifier
                    }

                    $product  = $products->get($line['product_id']);
                    $demanded = (int) $line['quantity'];

                    if ($product->stock_libreville < $demanded) {
                        throw new \DomainException(
                            "Stock insuffisant pour « {$product->name} – {$product->model} » "
                            . "(disponible : {$product->stock_libreville}, demandé : {$demanded})."
                        );
                    }
                }

                // 2d. Calcul des totaux
                $subtotal = collect($validated['items'])->sum(
                    fn(array $l) => (float) $l['unit_price'] * (int) $l['quantity']
                );
                $tvaRate   = 0.18;           // 18 % TVA Gabon / CEMAC
                $tvaAmount = $subtotal * $tvaRate;
                $total     = $subtotal + $tvaAmount;

                // 2e. Génération du numéro de facture (ex. FAC-2026-0042)
                $year   = now()->year;
                $seq    = Invoice::whereYear('created_at', $year)->count() + 1;
                $number = sprintf('FAC-%d-%04d', $year, $seq);

                // 2f. Création de la facture
                $invoice = Invoice::create([
                    'user_id'        => Auth::id(),
                    'client_id'      => $validated['client_id'],
                    'invoice_number' => $number,
                    'status'         => 'draft',
                    'issue_date'     => $validated['issue_date'],
                    'due_date'       => $validated['due_date'],
                    'subtotal'       => $subtotal,
                    'tva_amount'     => $tvaAmount,
                    'total'          => $total,
                    'notes'          => $validated['notes'] ?? null,
                ]);

                // 2g. Création des lignes et déduction du stock
                foreach ($validated['items'] as $line) {
                    $lineSubtotal = (float) $line['unit_price'] * (int) $line['quantity'];

                    InvoiceItem::create([
                        'invoice_id'  => $invoice->id,
                        'product_id'  => $line['product_id'] ?: null,
                        'description' => $line['description'],
                        'quantity'    => (int) $line['quantity'],
                        'unit_price'  => (float) $line['unit_price'],
                        'subtotal'    => $lineSubtotal,
                    ]);

                    // Déduction de stock uniquement pour les lignes produit
                    if (!empty($line['product_id'])) {
                        $product = $products->get($line['product_id']);
                        $product->decrement('stock_libreville', (int) $line['quantity']);
                        $product->refresh();

                        StockMovement::create([
                            'product_id'  => $product->id,
                            'user_id'     => Auth::id(),
                            'type'        => 'out',
                            'quantity'    => (int) $line['quantity'],
                            'description' => "Vente boutique - Facture {$number}",
                        ]);

                        if ($product->stock_libreville <= 0) {
                            Log::warning(
                                "RUPTURE DE STOCK (ERP Caisse) – "
                                . "{$product->name} – {$product->model} (ID : {$product->id})"
                            );
                        }
                    }
                }
            });

            return redirect()
                ->route('invoices.index')
                ->with('success', 'Facture créée avec succès. Le stock a été mis à jour.');

        } catch (\DomainException $e) {
            return back()
                ->withInput()
                ->with('error', $e->getMessage());

        } catch (\Throwable $e) {
            Log::error('InvoiceController@store – Erreur inattendue : ' . $e->getMessage(), [
                'exception' => $e,
            ]);

            return back()
                ->withInput()
                ->with('error', 'Une erreur serveur est survenue. Aucune écriture effectuée.');
        }
    }

    /**
     * Liste toutes les factures de l'utilisateur connecté (ou toutes si Admin).
     */
    public function index(): View
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $invoices = Invoice::with('client')
            ->when($user->role !== 'admin', fn($q) => $q->where('user_id', $user->id))
            ->latest('issue_date')
            ->paginate(20);

        return view('invoices.index', compact('invoices'));
    }

    /**
     * Affiche le détail d'une facture avec ses lignes et le produit associé.
     */
    public function show(string $id): View
    {
        $invoice = Invoice::with(['client', 'items.product', 'user'])
                          ->findOrFail($id);

        return view('invoices.show', compact('invoice'));
    }

    /**
     * Marque une facture comme 'paid' (action Admin uniquement).
     * Protéger cette route avec le middleware 'admin' dans web.php.
     */
    public function markPaid(string $id): RedirectResponse
    {
        $invoice          = Invoice::findOrFail($id);
        $invoice->status  = 'paid';
        $invoice->paid_at = now();
        $invoice->save();

        return back()->with('success', "Facture {$invoice->invoice_number} marquée comme payée.");
    }
}
