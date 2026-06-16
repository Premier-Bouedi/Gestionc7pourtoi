@extends('layouts.app')

@section('title', 'Gestion Personnel de Caisse | Espace Admin')

@section('content')
<div class="container-fluid px-4 py-4">

    <!-- En-tête -->
    <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-3">
        <div>
            <h2 class="text-white fw-bold mb-0">
                <i class="bi bi-shield-lock me-2 text-danger"></i>Espace Admin : Personnel de Caisse
            </h2>
            <p class="text-secondary small mb-0">Supervisez vos collaborateurs et comptabilisez leurs ventes de sacs de luxe</p>
        </div>
        <a href="{{ route('admin.caisses.create') }}" class="btn btn-danger font-bold rounded-3">
            <i class="bi bi-person-plus-fill me-2"></i>Ajouter un Personnel
        </a>
    </div>

    @if(session('success'))
    <div class="alert border-0 rounded-3 mb-4"
         style="background:rgba(34,197,94,.15);border-left:4px solid #22c55e !important;color:#4ade80">
        <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
    </div>
    @endif

    <!-- Tableau du personnel -->
    <div class="card border-0" style="background:#1e293b;border-radius:12px">
        <div class="card-header border-bottom border-secondary text-white fw-bold">
            <i class="bi bi-people-fill me-2 text-danger"></i>Liste des Collaborateurs (Ventes)
        </div>
        <div class="table-responsive">
            <table class="table mb-0 align-middle" style="color:#e2e8f0">
                <thead>
                    <tr style="background:rgba(239,68,68,.08);font-size:.75rem;text-transform:uppercase;letter-spacing:.06em;color:#f87171;border-bottom:1px solid rgba(255,255,255,.08)">
                        <th class="py-3 px-4">Identifiant</th>
                        <th>Nom Complet</th>
                        <th>Email</th>
                        <th>Rôle</th>
                        <th>Contact WhatsApp</th>
                        <th class="text-center">Factures générées</th>
                        <th class="text-center">Statut</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($staff as $index => $user)
                    <tr style="border-bottom:1px solid rgba(255,255,255,.06)">
                        <td class="py-3 px-4 text-secondary"># {{ $index + 1 }}</td>
                        <td>
                            <strong class="text-white d-block">{{ $user->name }}</strong>
                        </td>
                        <td>{{ $user->email }}</td>
                        <td>
                            @if($user->role === 'admin')
                                <span class="badge bg-danger rounded-pill px-2.5 py-1 text-xs">Patron (Admin)</span>
                            @else
                                <span class="badge bg-info text-dark rounded-pill px-2.5 py-1 text-xs">Caissier</span>
                            @endif
                        </td>
                        <td>{{ $user->phone_whatsapp ?? '—' }}</td>
                        <td class="text-center fw-bold text-white fs-5">
                            {{ $user->invoices_count }}
                        </td>
                        <td class="text-center">
                            <span class="badge bg-success rounded-pill px-2.5 py-1 text-xs">Actif</span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
