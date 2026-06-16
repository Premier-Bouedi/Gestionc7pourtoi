@extends('layouts.app')

@section('title', 'Suivi des Flux & Mouvements Stocks | Espace Admin')

@section('content')
<div class="container-fluid px-4 py-4">

    <!-- En-tête -->
    <div class="mb-4">
        <h2 class="text-white fw-bold mb-0">
            <i class="bi bi-arrow-left-right me-2 text-warning"></i>Espace Admin : Flux Logistiques & Stocks
        </h2>
        <p class="text-secondary small mb-0">Tracé complet de l'historique des entrées, sorties et ajustements de sacs de luxe</p>
    </div>

    <!-- Tableau de suivi des flux -->
    <div class="card border-0" style="background:#1e293b;border-radius:12px">
        <div class="card-header border-bottom border-secondary text-white fw-bold d-flex justify-content-between align-items-center">
            <span><i class="bi bi-list-columns-reverse me-2 text-warning"></i>Registre des Mouvements de Stock</span>
            <a href="{{ route('products.index') }}" class="btn btn-sm btn-outline-warning">Ajuster un Stock (Catalogue)</a>
        </div>
        <div class="table-responsive">
            <table class="table mb-0 align-middle" style="color:#e2e8f0">
                <thead>
                    <tr style="background:rgba(245,158,11,.08);font-size:.75rem;text-transform:uppercase;letter-spacing:.06em;color:#f59e0b;border-bottom:1px solid rgba(255,255,255,.08)">
                        <th class="py-3 px-4">Date</th>
                        <th>Sac de Luxe</th>
                        <th>Modèle/Maison</th>
                        <th class="text-center">Type</th>
                        <th class="text-center">Quantité</th>
                        <th>Opérateur</th>
                        <th>Description du Flux</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($movements as $movement)
                    <tr style="border-bottom:1px solid rgba(255,255,255,.06)">
                        <td class="py-3 px-4 text-secondary small">
                            {{ $movement->created_at->format('d/m/Y H:i') }}
                        </td>
                        <td>
                            <strong class="text-white">{{ $movement->product->name ?? 'Produit supprimé' }}</strong>
                        </td>
                        <td>
                            <span class="text-secondary small">{{ $movement->product->model ?? '—' }}</span>
                        </td>
                        <td class="text-center">
                            @if($movement->type === 'in')
                                <span class="badge bg-success text-dark rounded-pill px-2.5 py-1 text-xs">Entrée (+)</span>
                            @elseif($movement->type === 'out')
                                <span class="badge bg-danger rounded-pill px-2.5 py-1 text-xs">Sortie (-)</span>
                            @else
                                <span class="badge bg-warning text-dark rounded-pill px-2.5 py-1 text-xs">Ajustement</span>
                            @endif
                        </td>
                        <td class="text-center fw-bold">
                            @if($movement->type === 'in')
                                <span class="text-success">+ {{ $movement->quantity }}</span>
                            @else
                                <span class="text-danger">- {{ $movement->quantity }}</span>
                            @endif
                        </td>
                        <td>
                            <span class="text-white small">{{ $movement->user->name ?? 'Automate API' }}</span>
                        </td>
                        <td class="small text-secondary">
                            {{ $movement->description }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-5 text-secondary">
                            <i class="bi bi-arrow-left-right fs-1 d-block mb-2"></i>
                            Aucun mouvement de stock enregistré pour le moment.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($movements->hasPages())
        <div class="card-footer border-0 d-flex justify-content-center py-3" style="background:rgba(255,255,255,.03)">
            {{ $movements->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
