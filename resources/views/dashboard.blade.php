@extends('layouts.app')

@section('title', 'C7Pourt3 — Centre Logistique Central')

@section('content')
<div class="container-fluid py-2">

    <!-- En-tête -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h2 class="text-white fw-bold mb-1" style="font-family: 'Playfair Display', serif;">
                C7Pourt3 — Centre Logistique Central
            </h2>
            <p class="text-secondary small mb-0">Données en direct — Cloud Firestore (projet c7pourt3)</p>
        </div>
        @if($firestoreConnected)
        <span class="badge rounded-pill border px-3 py-2 text-success bg-success-subtle" style="background: rgba(34,197,94,.1); border-color: rgba(34,197,94,.25) !important;">
            <i class="bi bi-cloud-check me-1"></i> Firestore connecté
        </span>
        @else
        <span class="badge rounded-pill border px-3 py-2 text-warning bg-warning-subtle" style="background: rgba(234,179,8,.1); border-color: rgba(234,179,8,.25) !important;">
            <i class="bi bi-cloud-slash me-1"></i> Firestore non configuré — compteurs à 0
        </span>
        @endif
    </div>

    <!-- Alertes Flash -->
    @if(session('success'))
    <div class="alert border-0 rounded-3 mb-4"
         style="background:rgba(34,197,94,.15);border-left:4px solid #22c55e !important;color:#4ade80" role="alert">
        <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
    </div>
    @endif

    @php
        $totalStock = $products->sum('stock_libreville');
        $valXAF = $products->sum(fn($p) => $p->stock_libreville * $p->price_xaf);
        $valMAD = $products->sum(fn($p) => $p->stock_libreville * $p->price_mad);
    @endphp

    <!-- ── 1. KPI Catalogue & Clients (Firestore) ── -->
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card border-0 p-4 shadow-sm" style="background:#131a2c; border-radius:12px; height:100%">
                <div class="text-secondary uppercase text-xs tracking-wider fw-bold">TOTAL CLIENTS</div>
                <div class="text-white fs-3 fw-bold mt-2">{{ $clientsCount }}</div>
                <div class="text-secondary text-xs mt-1">Documents réels — collection Firestore « clients »</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 p-4 shadow-sm" style="background:#131a2c; border-radius:12px; height:100%">
                <div class="text-secondary uppercase text-xs tracking-wider fw-bold">TOTAL MODÈLES</div>
                <div class="text-white fs-3 fw-bold mt-2">{{ $products->count() }}</div>
                <div class="text-secondary text-xs mt-1">Produits actifs — collection « products »</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 p-4 shadow-sm" style="background:#131a2c; border-radius:12px; height:100%">
                <div class="text-secondary uppercase text-xs tracking-wider fw-bold">STOCK GLOBAL</div>
                <div class="text-white fs-3 fw-bold mt-2">{{ $totalStock }} sacs</div>
                <div class="text-secondary text-xs mt-1">Disponibles à Libreville</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 p-4 shadow-sm" style="background:#131a2c; border-radius:12px; height:100%">
                <div class="text-secondary uppercase text-xs tracking-wider fw-bold">VALEUR XAF</div>
                <div class="fs-3 fw-bold mt-2" style="color:#22c55e !important;">{{ number_format($valXAF, 0, ',', ' ') }} XAF</div>
                <div class="text-secondary text-xs mt-1">Valeur totale du stock au Gabon</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 p-4 shadow-sm" style="background:#131a2c; border-radius:12px; height:100%">
                <div class="text-secondary uppercase text-xs tracking-wider fw-bold">VALEUR MAD</div>
                <div class="fs-3 fw-bold mt-2" style="color:#60a5fa !important;">{{ number_format($valMAD, 0, ',', ' ') }} MAD</div>
                <div class="text-secondary text-xs mt-1">Équivalent Maroc (taux 1 MAD ≈ 60 XAF)</div>
            </div>
        </div>
    </div>

    <!-- ── 2. KPI Logistique ── -->
    <div class="row g-4 mb-4">
        <div class="col-md-6">
            <div class="card border-0 p-4 shadow-sm" style="background:#131a2c; border-radius:12px; height:100%">
                <div class="text-secondary uppercase text-xs tracking-wider fw-bold">LIVRAISONS (GABON)</div>
                <div class="text-amber-500 fs-3 fw-bold mt-2">
                    {{ $orders->whereIn('status', ['pending', 'ready_for_delivery'])->count() }} Active(s)
                </div>
                <div class="text-secondary text-xs mt-1">Commandes en transit à Libreville</div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card border-0 p-4 shadow-sm" style="background:#131a2c; border-radius:12px; height:100%">
                <div class="text-secondary uppercase text-xs tracking-wider fw-bold">INCIDENTS LOGISTIQUES</div>
                <div class="text-danger fs-3 fw-bold mt-2" id="kpi-incident-count">
                    {{ $incidents->where('status', 'open')->count() }} Actif(s)
                </div>
                <div class="text-secondary text-xs mt-1">Signalés par l'app mobile couriers</div>
            </div>
        </div>
    </div>

    <!-- ── 2. Sections Principales ── -->
    <div class="row g-4 mb-4">
        
        <!-- Liste des Incidents Actifs (Dynamic via 10s Polling) -->
        <div class="col-lg-7">
            <div class="card border-0 p-4 shadow-sm" style="background:#131a2c; border-radius:12px; min-height: 400px">
                <div class="d-flex justify-content-between align-items-center mb-3 border-bottom border-secondary pb-3">
                    <h5 class="text-white mb-0 fw-bold">
                        <i class="bi bi-exclamation-triangle-fill text-danger me-2"></i> Incidents de Terrain
                    </h5>
                    <span class="badge bg-danger rounded-pill px-2.5 py-1 text-xs" id="active-incidents-badge">
                        {{ $incidents->where('status', 'open')->count() }} en cours
                    </span>
                </div>

                <!-- Conteneur d'incidents chargés dynamiquement -->
                <div id="incidents-container" class="d-flex flex-column gap-3 overflow-y-auto pr-1" style="max-height: 320px;">
                    @forelse($incidents->where('status', 'open') as $incident)
                        <div class="p-3 rounded bg-dark border border-secondary d-flex justify-content-between align-items-center gap-3">
                            <div class="text-start">
                                <div class="d-flex align-items-center gap-2 mb-1">
                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded text-uppercase text-[10px]" style="font-size:0.7rem">
                                        {{ str_replace('_', ' ', $incident->type) }}
                                    </span>
                                    <small class="text-secondary">Commande #{{ substr($incident->order_id, 0, 8) }}</small>
                                </div>
                                <p class="text-white small mb-1 fw-medium">{{ $incident->description }}</p>
                                <small class="text-secondary">
                                    Client : <strong class="text-white">{{ $incident->order->customer_name }}</strong> ({{ $incident->order->customer_whatsapp }})
                                </small>
                            </div>
                            <form action="{{ route('incidents.resolve', $incident->id) }}" method="POST" class="shrink-0">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-outline-success">
                                    <i class="bi bi-check2"></i> Résoudre
                                </button>
                            </form>
                        </div>
                    @empty
                        <div class="text-center py-5 text-secondary">
                            <i class="bi bi-check-circle fs-1 d-block mb-2 text-success"></i>
                            Aucun incident de terrain signalé. Tous les colis sont en cours d'expédition.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Catalogue & Stocks Rapides -->
        <div class="col-lg-5">
            <div class="card border-0 p-4 shadow-sm" style="background:#131a2c; border-radius:12px; height: 100%">
                <div class="d-flex justify-content-between align-items-center mb-3 border-bottom border-secondary pb-3">
                    <h5 class="text-white mb-0 fw-bold">
                        <i class="bi bi-bag-fill text-warning me-2"></i> Catalogue & Stocks
                    </h5>
                    <span class="badge bg-secondary rounded-pill px-2.5 py-1 text-xs">
                        {{ $products->count() }} Modèles
                    </span>
                </div>

                <div class="d-flex flex-column gap-3 overflow-y-auto pr-1" style="max-height: 320px;">
                    @forelse($products as $product)
                        <div class="p-3 rounded bg-dark border border-secondary d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-white fw-bold mb-1">{{ $product->name }}</h6>
                                <small class="text-secondary">{{ $product->model }} • {{ number_format($product->price_xaf, 0, ',', ' ') }} XAF</small>
                            </div>
                            <div>
                                @if($product->stock_libreville > 10)
                                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded px-2.5 py-1">
                                        {{ $product->stock_libreville }} En stock
                                    </span>
                                @elseif($product->stock_libreville > 0)
                                    <span class="badge bg-warning-subtle text-warning border border-warning-subtle rounded px-2.5 py-1">
                                        {{ $product->stock_libreville }} Faible
                                    </span>
                                @else
                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded px-2.5 py-1">
                                        Rupture
                                    </span>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-5 text-secondary">
                            <i class="bi bi-bag fs-1 d-block mb-2"></i>
                            Aucun produit dans Firestore. Ajoutez vos sacs C7Pourt3 dans la collection « products ».
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

    </div>

    <!-- ── 3. Table de suivi logistique (Libreville) ── -->
    <div class="card border-0 p-4 shadow-sm" style="background:#131a2c; border-radius:12px">
        <div class="d-flex justify-content-between align-items-center mb-3 border-bottom border-secondary pb-3">
            <h5 class="text-white mb-0 fw-bold">
                <i class="bi bi-truck me-2 text-info"></i> Suivi des Livraisons à Libreville (Gabon)
            </h5>
            <span class="badge bg-info text-dark rounded-pill px-2.5 py-1 text-xs">{{ $orders->count() }} commandes</span>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 text-white" style="color: #e2e8f0 !important;">
                <thead>
                    <tr class="text-secondary" style="border-bottom: 1px solid rgba(255,255,255,.08)">
                        <th class="py-3">Identifiant</th>
                        <th>Client</th>
                        <th>Adresse Libreville</th>
                        <th>Total Commande</th>
                        <th>Statut</th>
                        <th class="text-end">Créé le</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($orders as $order)
                        <tr style="border-bottom: 1px solid rgba(255,255,255,.05)">
                            <td class="font-monospace text-secondary small py-3">#{{ substr($order->id, 0, 8) }}</td>
                            <td>
                                <strong class="text-white d-block">{{ $order->customer_name }}</strong>
                                <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $order->customer_whatsapp) }}" target="_blank" class="text-emerald-400 text-decoration-none small">
                                    <i class="bi bi-whatsapp text-success me-1"></i>{{ $order->customer_whatsapp }}
                                </a>
                            </td>
                            <td>{{ $order->address_libreville }}</td>
                            <td class="fw-bold">{{ number_format($order->total_amount, 0, ',', ' ') }} XAF</td>
                            <td>
                                @switch($order->status)
                                    @case('pending')
                                        <span class="badge bg-warning-subtle text-warning border border-warning-subtle rounded-pill px-2.5 py-1">En attente</span>
                                        @break
                                    @case('ready_for_delivery')
                                        <span class="badge bg-info-subtle text-info border border-info-subtle rounded-pill px-2.5 py-1">Prêt livraison</span>
                                        @break
                                    @case('delivered')
                                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-1">Livré</span>
                                        @break
                                    @case('incident')
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-2.5 py-1">Incident</span>
                                        @break
                                @endswitch
                            </td>
                            <td class="text-end text-secondary small">{{ $order->created_at->format('d/m/Y H:i') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-secondary">
                                Aucun achat vitrine ou mobile enregistré.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

<!-- ── Polling des Incidents en direct (10 secondes) ── -->
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    let lastIncidentCount = {{ $incidents->where('status', 'open')->count() }};

    function pollActiveIncidents() {
        fetch('{{ route("incidents.active") }}')
            .then(res => res.json())
            .then(data => {
                const count = data.length || 0;
                
                // Mettre à jour les compteurs du dashboard
                const kpiCount = document.getElementById('kpi-incident-count');
                const badge = document.getElementById('active-incidents-badge');
                
                if (kpiCount) kpiCount.textContent = count + " Actif(s)";
                if (badge) badge.textContent = count + " en cours";

                // Re-dessiner le conteneur d'incidents
                const container = document.getElementById('incidents-container');
                if (container) {
                    if (count === 0) {
                        container.innerHTML = `
                            <div class="text-center py-5 text-secondary">
                                <i class="bi bi-check-circle fs-1 d-block mb-2 text-success"></i>
                                Aucun incident de terrain signalé. Tous les colis sont en cours d'expédition.
                            </div>`;
                    } else {
                        let html = '';
                        data.forEach(inc => {
                            const cleanType = inc.type.replace('_', ' ').toUpperCase();
                            const subId = inc.order_id.substring(0, 8);
                            const resolveUrl = `{{ url('/dashboard/incidents') }}/${inc.id}/resolve`;
                            
                            html += `
                                <div class="p-3 rounded bg-dark border border-secondary d-flex justify-content-between align-items-center gap-3">
                                    <div class="text-start">
                                        <div class="d-flex align-items-center gap-2 mb-1">
                                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded text-uppercase" style="font-size:0.7rem">
                                                ${cleanType}
                                            </span>
                                            <small class="text-secondary">Commande #${subId}</small>
                                        </div>
                                        <p class="text-white small mb-1 fw-medium">${inc.description}</p>
                                        <small class="text-secondary">
                                            Client : <strong class="text-white">${inc.order ? inc.order.customer_name : '—'}</strong> (${inc.order ? inc.order.customer_whatsapp : ''})
                                        </small>
                                    </div>
                                    <form action="${resolveUrl}" method="POST" class="shrink-0">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-success">
                                            <i class="bi bi-check2"></i> Résoudre
                                        </button>
                                    </form>
                                </div>`;
                        });
                        container.innerHTML = html;
                    }
                }

                // Si de nouveaux incidents sont apparus, émettre un bip d'alerte (si non-muté)
                if (count > lastIncidentCount) {
                    if (window.playSuccessBeep) {
                        window.playSuccessBeep(); // alerte l'opérateur
                    }
                }
                lastIncidentCount = count;
            })
            .catch(err => console.warn('Erreur Polling Incidents:', err));
    }

    // Lancer toutes les 10 secondes
    setInterval(pollActiveIncidents, 10000);
});
</script>
@endpush
@endsection
