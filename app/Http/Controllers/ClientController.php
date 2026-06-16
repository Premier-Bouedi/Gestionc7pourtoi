<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\FirestoreService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ClientController extends Controller
{
    public function __construct(
        private readonly FirestoreService $firestore,
    ) {}

    /**
     * Liste des clients lue depuis la collection Firestore « clients ».
     */
    public function index(Request $request): View
    {
        $search = $request->input('search');
        $allClients = $this->firestore->clients($search);
        $clientsCount = $this->firestore->countClients();
        $firestoreConnected = $this->firestore->isConnected();

        $page = max(1, (int) $request->input('page', 1));
        $perPage = 15;
        $total = $allClients->count();
        $clients = $allClients->slice(($page - 1) * $perPage, $perPage)->values();

        return view('clients.index', compact(
            'clients',
            'search',
            'clientsCount',
            'firestoreConnected',
            'page',
            'perPage',
            'total',
        ));
    }

    /**
     * Création locale désactivée — source de vérité = Firestore.
     */
    public function store(Request $request): RedirectResponse
    {
        return back()->with('success', 'Les clients sont gérés dans Cloud Firestore (collection « clients »).');
    }
}
