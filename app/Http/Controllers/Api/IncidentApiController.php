<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\Incident;
use App\Models\Order;

class IncidentApiController extends Controller
{
    /**
     * Reçoit les signalements de soucis (type, description, photo) depuis l'app mobile
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'order_id' => 'required|exists:orders,id',
            'type' => 'required|in:client_absent,payment_issue,damaged,other',
            'description' => 'required|string',
            'photo' => 'nullable|image|max:5120', // Max 5MB
        ]);

        $photoPath = null;
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('incidents', 'public');
        }

        $incident = Incident::create([
            'order_id' => $validated['order_id'],
            'type' => $validated['type'],
            'description' => $validated['description'],
            'photo_path' => $photoPath,
            'status' => 'open',
        ]);

        // Mettre à jour le statut de la commande en "incident"
        $order = Order::find($validated['order_id']);
        if ($order) {
            $order->status = 'incident';
            $order->save();
        }

        return response()->json([
            'success' => true,
            'message' => 'Incident signalé avec succès',
            'data' => $incident
        ], 201);
    }
}
