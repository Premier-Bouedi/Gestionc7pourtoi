@extends('layouts.app')

@section('title', 'Gestion Clients | C7Pourt3')

@section('content')
<div class="container-fluid px-4 py-4">

    <!-- ── En-tête ────────────────────────────────────────────────── -->
    <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-3">
        <div>
            <h2 class="text-white fw-bold mb-0">
                <i class="bi bi-people me-2" style="color:#d4a843"></i>Gestion des Clients
            </h2>
            <p class="text-secondary small mb-0">Source de vérité : Cloud Firestore — collection « clients »</p>
        </div>
        <div class="d-flex align-items-center gap-3">
            <span class="badge rounded-pill px-3 py-2" style="background:rgba(212,168,67,.15);color:#d4a843;font-size:1rem;">
                {{ $clientsCount }} client{{ $clientsCount > 1 ? 's' : '' }}
            </span>
            @if($firestoreConnected)
                <span class="badge bg-success-subtle text-success border border-success-subtle">Firestore connecté</span>
            @else
                <span class="badge bg-warning-subtle text-warning border border-warning-subtle">Firestore non configuré</span>
            @endif
        </div>
    </div>

    {{-- Alertes --}}
    @if(session('success'))
    <div class="alert border-0 rounded-3 mb-4"
         style="background:rgba(34,197,94,.15);border-left:4px solid #22c55e !important;color:#4ade80">
        <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
    </div>
    @endif

    <!-- ── Recherche ──────────────────────────────────────────────── -->
    <div class="card border-0 mb-4" style="background:#1e293b;border-radius:12px">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('clients.index') }}" class="row g-2">
                <div class="col-md-9 col-sm-8">
                    <div class="input-group">
                        <span class="input-group-text border-0 text-secondary" style="background:rgba(255,255,255,.05)">
                            <i class="bi bi-search"></i>
                        </span>
                        <input type="text" name="search" class="form-control border-0 text-white" 
                               value="{{ $search }}"
                               placeholder="Rechercher par nom, entreprise, email, téléphone ou ville..." 
                               style="background:rgba(255,255,255,.05)">
                    </div>
                </div>
                <div class="col-md-3 col-sm-4 d-flex gap-2">
                    <button type="submit" class="btn btn-primary w-100" style="border-radius:8px">Rechercher</button>
                    @if($search)
                        <a href="{{ route('clients.index') }}" class="btn btn-secondary" style="border-radius:8px">Annuler</a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- ── Tableau de la galerie ───────────────────────────────────── -->
    <div class="card border-0" style="background:#1e293b;border-radius:12px">
        <div class="table-responsive">
            <table class="table mb-0 align-middle" style="color:#e2e8f0">
                <thead>
                    <tr style="background:rgba(212,168,67,.08);font-size:.75rem;text-transform:uppercase;letter-spacing:.06em;color:#d4a843;border-bottom:1px solid rgba(255,255,255,.08)">
                        <th class="py-3 px-4">Identifiant</th>
                        <th>Nom & Entreprise</th>
                        <th>Email</th>
                        <th>WhatsApp / Tel</th>
                        <th>Ville & Adresse</th>
                        <th class="text-center">Factures générées</th>
                        <th>Notes</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($clients as $index => $client)
                    <tr style="border-bottom:1px solid rgba(255,255,255,.06)">
                        <td class="py-3 px-4 text-secondary"># {{ $index + 1 }}</td>
                        <td>
                            <span class="fw-bold text-white d-block">{{ $client->contact_name }}</span>
                            <small class="text-secondary">{{ $client->company_name }}</small>
                        </td>
                        <td>{{ $client->email ?? '—' }}</td>
                        <td>
                            @if($client->phone_whatsapp)
                                <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $client->phone_whatsapp) }}" target="_blank" class="text-info text-decoration-none">
                                    <i class="bi bi-whatsapp me-1 text-success"></i>{{ $client->phone_whatsapp }}
                                </a>
                            @else
                                —
                            @endif
                        </td>
                        <td>
                            <span class="d-block small text-white">{{ $client->city ?? '—' }}</span>
                            <small class="text-secondary">{{ $client->address ?? '' }}</small>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-secondary rounded-pill px-3 py-1 font-semibold">{{ $client->invoices_count }}</span>
                        </td>
                        <td class="small text-secondary" style="max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap" title="{{ $client->notes }}">
                            {{ $client->notes ?? '—' }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-5 text-secondary">
                            <i class="bi bi-people fs-1 d-block mb-2"></i>
                            Aucun client dans Firestore. Le compteur affiche <strong>0</strong>.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($total > $perPage)
        <div class="card-footer border-0 d-flex justify-content-between align-items-center py-3" style="background:rgba(255,255,255,.03)">
            <small class="text-secondary">Page {{ $page }} — {{ $total }} client(s) au total</small>
            <div class="d-flex gap-2">
                @if($page > 1)
                    <a href="{{ route('clients.index', array_filter(['search' => $search, 'page' => $page - 1])) }}" class="btn btn-sm btn-outline-secondary">Précédent</a>
                @endif
                @if($page * $perPage < $total)
                    <a href="{{ route('clients.index', array_filter(['search' => $search, 'page' => $page + 1])) }}" class="btn btn-sm btn-outline-secondary">Suivant</a>
                @endif
            </div>
        </div>
        @endif
    </div>

</div>
@endsection
