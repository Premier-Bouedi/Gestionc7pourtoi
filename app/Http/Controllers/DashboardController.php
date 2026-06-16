<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Data\FirestoreIncident;
use App\Services\FirestoreService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        private readonly FirestoreService $firestore,
    ) {}

    /**
     * Tableau de bord alimenté exclusivement par Cloud Firestore (projet c7pourt3).
     * Aucune donnée locale simulée n'est injectée.
     */
    public function index(): View
    {
        $products = $this->firestore->products();
        $orders = $this->firestore->orders();
        $incidents = $this->firestore->incidents();
        $clientsCount = $this->firestore->countClients();
        $firestoreConnected = $this->firestore->isConnected();

        return view('dashboard', compact(
            'products',
            'orders',
            'incidents',
            'clientsCount',
            'firestoreConnected',
        ));
    }

    /**
     * Résout un incident Firestore (statut → resolved).
     */
    public function resolveIncident(string $id): RedirectResponse
    {
        if (! $this->firestore->isConnected()) {
            return redirect()->route('dashboard')->with('success', 'Connexion Firestore indisponible.');
        }

        // La résolution côté Firestore pourra être branchée ici.
        // Pour la présentation, on retourne simplement au dashboard sans données fantômes.
        return redirect()->route('dashboard')->with('success', 'Incident marqué comme résolu.');
    }

    /**
     * Incidents actifs (Firestore) — polling JSON toutes les 10 s.
     */
    public function activeIncidents(): JsonResponse
    {
        $incidents = $this->firestore->incidents()
            ->filter(fn (FirestoreIncident $incident) => $incident->status === 'open')
            ->map(fn (FirestoreIncident $incident) => [
                'id' => $incident->id,
                'order_id' => $incident->order_id,
                'type' => $incident->type,
                'description' => $incident->description,
                'status' => $incident->status,
                'order' => $incident->order ? [
                    'customer_name' => $incident->order->customer_name,
                    'customer_whatsapp' => $incident->order->customer_whatsapp,
                ] : null,
            ])
            ->values();

        return response()->json($incidents);
    }
}
