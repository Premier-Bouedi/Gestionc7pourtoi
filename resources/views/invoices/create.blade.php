@extends('layouts.app')

@section('title', 'Nouvelle Facture – Caisse | GestionFacture')

@push('styles')
<style>
    /* ── Tokens ──────────────────────────────────────────────────── */
    :root {
        --gold:   #d4a843;
        --gold-lt: #f0c96b;
        --dark:   #0f172a;
        --card:   #1e293b;
        --border: rgba(255,255,255,.08);
        --muted:  #94a3b8;
    }

    /* ── Layout ─────────────────────────────────────────────────── */
    .caisse-hero {
        background: linear-gradient(135deg, #0f172a 0%, #1e3a5f 100%);
        border-bottom: 1px solid var(--border);
        padding: 2rem 0 1.5rem;
    }
    .caisse-hero h1 { font-size: 1.6rem; font-weight: 700; }
    .caisse-badge {
        background: rgba(212,168,67,.15);
        border: 1px solid var(--gold);
        color: var(--gold);
        border-radius: 50px;
        font-size: .75rem;
        font-weight: 600;
        padding: .25rem .75rem;
        letter-spacing: .05em;
    }

    /* ── Cards ───────────────────────────────────────────────────── */
    .glass-card {
        background: var(--card);
        border: 1px solid var(--border);
        border-radius: 12px;
    }
    .glass-card .card-header {
        background: rgba(255,255,255,.04);
        border-bottom: 1px solid var(--border);
        padding: 1rem 1.25rem;
        font-weight: 600;
        letter-spacing: .04em;
        color: var(--gold);
    }

    /* ── Form controls ───────────────────────────────────────────── */
    .form-control, .form-select {
        background: rgba(255,255,255,.05) !important;
        border: 1px solid var(--border) !important;
        color: #e2e8f0 !important;
        border-radius: 8px;
    }
    .form-control:focus, .form-select:focus {
        border-color: var(--gold) !important;
        box-shadow: 0 0 0 3px rgba(212,168,67,.2) !important;
    }
    .form-label { color: var(--muted); font-size: .85rem; font-weight: 500; }
    .form-control::placeholder { color: #475569; }
    option { background: #1e293b; color: #e2e8f0; }

    /* ── Lignes de facture ───────────────────────────────────────── */
    #items-table thead th {
        background: rgba(212,168,67,.08);
        color: var(--gold-lt);
        font-size: .75rem;
        text-transform: uppercase;
        letter-spacing: .06em;
        border-bottom: 1px solid var(--border);
        padding: .6rem .75rem;
    }
    #items-table tbody td { padding: .5rem .75rem; vertical-align: middle; }
    #items-table tbody tr { border-bottom: 1px solid var(--border); }
    #items-table tbody tr:last-child { border-bottom: none; }

    .btn-remove-line {
        background: transparent;
        border: 1px solid #ef4444;
        color: #ef4444;
        border-radius: 6px;
        width: 32px; height: 32px;
        display: flex; align-items: center; justify-content: center;
        transition: all .2s;
    }
    .btn-remove-line:hover { background: #ef4444; color: #fff; }

    /* ── Résumé totaux ───────────────────────────────────────────── */
    .totals-box {
        background: rgba(212,168,67,.06);
        border: 1px solid rgba(212,168,67,.2);
        border-radius: 10px;
        padding: 1.25rem 1.5rem;
    }
    .totals-box .label { color: var(--muted); font-size: .88rem; }
    .totals-box .value { color: #e2e8f0; font-weight: 600; font-size: .95rem; }
    .totals-box .total-line { font-size: 1.15rem; color: var(--gold); font-weight: 700; }

    /* ── Badge stock produit ─────────────────────────────────────── */
    .stock-tag { font-size: .7rem; border-radius: 50px; padding: .15rem .5rem; font-weight: 600; }
    .stock-ok   { background: rgba(34,197,94,.15); color: #4ade80; border: 1px solid rgba(34,197,94,.3); }
    .stock-warn { background: rgba(250,204,21,.15); color: #facc15; border: 1px solid rgba(250,204,21,.3); }
    .stock-out  { background: rgba(239,68,68,.15);  color: #f87171; border: 1px solid rgba(239,68,68,.3);  }

    /* ── Bouton principal ─────────────────────────────────────────── */
    .btn-gold {
        background: linear-gradient(135deg, var(--gold), #b8922e);
        border: none;
        color: #0f172a;
        font-weight: 700;
        letter-spacing: .04em;
        border-radius: 8px;
        padding: .65rem 2rem;
        transition: all .25s;
    }
    .btn-gold:hover { filter: brightness(1.15); transform: translateY(-1px); box-shadow: 0 4px 18px rgba(212,168,67,.35); }
    .btn-gold:active { transform: translateY(0); }
</style>
@endpush

@section('content')
<!-- ── Héro ─────────────────────────────────────────────────────── -->
<div class="caisse-hero">
    <div class="container-fluid px-4">
        <div class="d-flex align-items-center gap-3 mb-1">
            <i class="bi bi-receipt fs-3" style="color:var(--gold)"></i>
            <h1 class="text-white mb-0">Nouvelle Facture</h1>
            <span class="caisse-badge">CAISSE</span>
        </div>
        <p class="text-secondary mb-0 small">
            Sélectionnez les produits — le stock sera déduit automatiquement à la validation.
        </p>
    </div>
</div>

<div class="container-fluid px-4 py-4">

    {{-- Alertes flash --}}
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show border-0 rounded-3 mb-4" role="alert"
         style="background:rgba(34,197,94,.15);border-left:4px solid #22c55e !important;color:#4ade80">
        <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert"></button>
    </div>
    @endif
    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show border-0 rounded-3 mb-4" role="alert"
         style="background:rgba(239,68,68,.15);border-left:4px solid #ef4444 !important;color:#f87171">
        <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <form method="POST" action="{{ route('invoices.store') }}" id="invoice-form">
        @csrf

        <div class="row g-4">

            <!-- ── Colonne gauche : informations de la facture ─────── -->
            <div class="col-lg-4">

                {{-- Client --}}
                <div class="glass-card mb-4">
                    <div class="card-header">
                        <i class="bi bi-person-circle me-2"></i>Client
                    </div>
                    <div class="card-body p-3">
                        <div class="mb-3">
                            <label class="form-label" for="client_id">Client *</label>
                            <select name="client_id" id="client_id"
                                    class="form-select @error('client_id') is-invalid @enderror"
                                    required>
                                <option value="">— Sélectionner un client —</option>
                                @foreach($clients as $client)
                                    <option value="{{ $client->id }}"
                                            {{ old('client_id') == $client->id ? 'selected' : '' }}>
                                        {{ $client->name }}
                                        @if($client->email) — {{ $client->email }} @endif
                                    </option>
                                @endforeach
                            </select>
                            @error('client_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- Dates --}}
                <div class="glass-card mb-4">
                    <div class="card-header">
                        <i class="bi bi-calendar-event me-2"></i>Dates
                    </div>
                    <div class="card-body p-3">
                        <div class="mb-3">
                            <label class="form-label" for="issue_date">Date d'émission *</label>
                            <input type="date" name="issue_date" id="issue_date"
                                   class="form-control @error('issue_date') is-invalid @enderror"
                                   value="{{ old('issue_date', now()->toDateString()) }}" required>
                            @error('issue_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div>
                            <label class="form-label" for="due_date">Date d'échéance *</label>
                            <input type="date" name="due_date" id="due_date"
                                   class="form-control @error('due_date') is-invalid @enderror"
                                   value="{{ old('due_date', now()->addDays(30)->toDateString()) }}" required>
                            @error('due_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- Notes --}}
                <div class="glass-card mb-4">
                    <div class="card-header">
                        <i class="bi bi-sticky me-2"></i>Notes
                    </div>
                    <div class="card-body p-3">
                        <textarea name="notes" id="notes" rows="3"
                                  class="form-control"
                                  placeholder="Conditions de paiement, mentions légales…">{{ old('notes') }}</textarea>
                    </div>
                </div>

                {{-- Totaux --}}
                <div class="totals-box">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="label">Sous-total HT</span>
                        <span class="value" id="display-subtotal">0 XAF</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="label">TVA (18 %)</span>
                        <span class="value" id="display-tva">0 XAF</span>
                    </div>
                    <hr style="border-color:var(--border)">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="label" style="font-weight:700;color:#e2e8f0">Total TTC</span>
                        <span class="total-line" id="display-total">0 XAF</span>
                    </div>
                </div>

                <div class="mt-4 d-grid">
                    <button type="submit" class="btn btn-gold" id="submit-btn">
                        <i class="bi bi-check2-circle me-2"></i>
                        Valider la Facture & Déduire le Stock
                    </button>
                </div>

            </div>

            <!-- ── Colonne droite : lignes de la facture ───────────── -->
            <div class="col-lg-8">
                <div class="glass-card">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <span><i class="bi bi-list-ul me-2"></i>Lignes de Facture</span>
                        <button type="button" id="add-line-btn"
                                class="btn btn-sm"
                                style="background:rgba(212,168,67,.15);border:1px solid var(--gold);color:var(--gold);border-radius:6px">
                            <i class="bi bi-plus-lg me-1"></i>Ajouter une ligne
                        </button>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table mb-0" id="items-table">
                                <thead>
                                    <tr>
                                        <th style="width:35%">Produit / Service</th>
                                        <th style="width:30%">Description</th>
                                        <th style="width:10%">Qté</th>
                                        <th style="width:15%">Prix unit. (XAF)</th>
                                        <th style="width:8%">Sous-total</th>
                                        <th style="width:2%"></th>
                                    </tr>
                                </thead>
                                <tbody id="items-body">
                                    {{-- Les lignes sont injectées via JS --}}
                                </tbody>
                            </table>
                        </div>

                        <div id="empty-state" class="text-center py-5" style="color:var(--muted)">
                            <i class="bi bi-bag-plus fs-1 d-block mb-2" style="color:var(--border)"></i>
                            Aucun article. Cliquez sur "Ajouter une ligne" pour commencer.
                        </div>
                    </div>
                </div>
            </div>

        </div><!-- /.row -->
    </form>
</div>

{{-- Dataset JSON pour le sélecteur de produits ──────────────────── --}}
<script id="products-data" type="application/json">
    @json($products)
</script>
@endsection

@push('scripts')
<script>
(function () {
    'use strict';

    // ── Données produits ──────────────────────────────────────────
    const products = JSON.parse(
        document.getElementById('products-data').textContent
    );

    const productMap = {};
    products.forEach(p => { productMap[p.id] = p; });

    // ── État ──────────────────────────────────────────────────────
    let lineIndex = 0;

    // ── DOM ───────────────────────────────────────────────────────
    const tbody      = document.getElementById('items-body');
    const emptyState = document.getElementById('empty-state');
    const addBtn     = document.getElementById('add-line-btn');

    // Totaux
    const elSubtotal = document.getElementById('display-subtotal');
    const elTva      = document.getElementById('display-tva');
    const elTotal    = document.getElementById('display-total');

    const TVA = 0.18;

    function fmt(n) {
        return new Intl.NumberFormat('fr-FR').format(Math.round(n)) + ' XAF';
    }

    function stockTag(stock) {
        if (stock <= 0)  return '<span class="stock-tag stock-out">Rupture</span>';
        if (stock <= 3)  return '<span class="stock-tag stock-warn">Faible (' + stock + ')</span>';
        return '<span class="stock-tag stock-ok">En stock (' + stock + ')</span>';
    }

    // ── Recalcul totaux ───────────────────────────────────────────
    function recalcTotals() {
        let sub = 0;
        tbody.querySelectorAll('.line-row').forEach(row => {
            const qty   = parseFloat(row.querySelector('.inp-qty').value)   || 0;
            const price = parseFloat(row.querySelector('.inp-price').value) || 0;
            sub += qty * price;
            row.querySelector('.cell-sub').textContent = fmt(qty * price);
        });

        const tva   = sub * TVA;
        const total = sub + tva;

        elSubtotal.textContent = fmt(sub);
        elTva.textContent      = fmt(tva);
        elTotal.textContent    = fmt(total);

        emptyState.style.display = tbody.children.length ? 'none' : 'block';
    }

    // ── Créer une ligne ───────────────────────────────────────────
    function addLine() {
        const i   = lineIndex++;
        const row = document.createElement('tr');
        row.className = 'line-row';
        row.dataset.index = i;

        // Options produit
        let options = '<option value="">— Service / libellé libre —</option>';
        products.forEach(p => {
            const label = p.name + ' · ' + p.model;
            options += `<option value="${p.id}" data-price="${p.price_xaf}">${label}</option>`;
        });

        row.innerHTML = `
            <td>
                <select name="items[${i}][product_id]" class="form-select form-select-sm sel-product"
                        style="font-size:.82rem">
                    ${options}
                </select>
                <div class="mt-1 stock-info"></div>
            </td>
            <td>
                <input type="text" name="items[${i}][description]"
                       class="form-control form-control-sm inp-desc"
                       placeholder="Libellé…" required style="font-size:.82rem">
            </td>
            <td>
                <input type="number" name="items[${i}][quantity]"
                       class="form-control form-control-sm inp-qty"
                       value="1" min="1" required style="font-size:.82rem">
            </td>
            <td>
                <input type="number" name="items[${i}][unit_price]"
                       class="form-control form-control-sm inp-price"
                       value="0" min="0" required style="font-size:.82rem">
            </td>
            <td class="cell-sub text-end" style="color:#e2e8f0;font-size:.85rem;white-space:nowrap">0 XAF</td>
            <td>
                <button type="button" class="btn-remove-line" title="Supprimer la ligne">
                    <i class="bi bi-trash3-fill" style="font-size:.75rem"></i>
                </button>
            </td>`;

        // ── Événements sur la ligne ────────────────────────────────
        const selProd = row.querySelector('.sel-product');
        const inpDesc = row.querySelector('.inp-desc');
        const inpQty  = row.querySelector('.inp-qty');
        const inpPrix = row.querySelector('.inp-price');
        const stockInfo = row.querySelector('.stock-info');

        selProd.addEventListener('change', () => {
            const pid = selProd.value;
            if (pid && productMap[pid]) {
                const p = productMap[pid];
                inpDesc.value = p.name + ' – ' + p.model;
                inpPrix.value = p.price_xaf;
                stockInfo.innerHTML = stockTag(p.stock_libreville);
            } else {
                stockInfo.innerHTML = '';
            }
            recalcTotals();
        });

        inpQty.addEventListener('input',  recalcTotals);
        inpPrix.addEventListener('input', recalcTotals);

        row.querySelector('.btn-remove-line').addEventListener('click', () => {
            row.remove();
            recalcTotals();
        });

        tbody.appendChild(row);
        recalcTotals();
        inpDesc.focus();
    }

    addBtn.addEventListener('click', addLine);

    // ── Première ligne automatique ─────────────────────────────────
    addLine();

    // ── Confirmation soumission ────────────────────────────────────
    document.getElementById('invoice-form').addEventListener('submit', function (e) {
        const btn = document.getElementById('submit-btn');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Validation en cours…';
    });
})();
</script>
@endpush
