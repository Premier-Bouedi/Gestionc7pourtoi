<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\Order;
use App\Models\Incident;

class OrderController extends Controller
{
    /**
     * Valider un panier (Création de la commande depuis Cursor)
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'customer_name' => 'required|string|max:255',
            'customer_whatsapp' => 'required|string|max:50',
            'address_libreville' => 'required|string|max:500',
            // Optionnel : products_ids, etc. pour lier aux produits
        ]);

        $order = Order::create([
            'customer_name' => $validated['customer_name'],
            'customer_whatsapp' => $validated['customer_whatsapp'],
            'address_libreville' => $validated['address_libreville'],
            'status' => 'pending',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Commande créée avec succès',
            'data' => $order
        ], 201);
    }

    /**
     * Mettre à jour le statut d'une commande depuis l'app mobile
     */
    public function updateStatus(Request $request, string $id): JsonResponse
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,assigned,delivered,incident',
            'incident_type' => 'required_if:status,incident|string',
            'incident_description' => 'required_if:status,incident|string',
        ]);

        $order = Order::findOrFail($id);
        $order->status = $validated['status'];
        $order->save();

        // Si le statut est incident, on crée l'incident lié
        if ($validated['status'] === 'incident') {
            $order->incidents()->create([
                'type' => $validated['incident_type'],
                'description' => $validated['incident_description'],
                // photo_path à gérer plus tard avec un upload de fichier
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Statut mis à jour avec succès',
            'data' => $order->load('incidents')
        ]);
    }
}
