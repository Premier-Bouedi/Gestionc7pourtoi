@extends('layouts.app')

@section('title', 'Facture ' . $invoice->invoice_number . ' | GestionFacture')

@section('content')
<div class="container-fluid px-4 py-4">

    <div class="d-flex align-items-center gap-3 mb-4">
        <a href="{{ route('invoices.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left"></i>
        </a>
        <div>
            <h2 class="text-white fw-bold mb-0">
                <i class="bi bi-receipt me-2" style="color:#d4a843"></i>
                Facture {{ $invoice->invoice_number }}
            </h2>
            <p class="text-secondary small mb-0">
                Émise le {{ $invoice->issue_date->format('d/m/Y') }}
                · Échéance {{ $invoice->due_date->format('d/m/Y') }}
            </p>
        </div>
    </div>

    <div class="row g-4">

        <!-- Infos client + statut -->
        <div class="col-lg-4">
            <div class="card border-0 mb-3" style="background:#1e293b;border-radius:12px">
                <div class="card-body p-4">
                    <h6 class="text-uppercase small mb-3" style="color:#d4a843;letter-spacing:.06em">Client</h6>
                    <p class="fw-bold text-white mb-1">{{ $invoice->client->name ?? '—' }}</p>
                    <p class="text-secondary small mb-0">{{ $invoice->client->email ?? '' }}</p>
                </div>
            </div>
            <div class="card border-0 mb-3" style="background:#1e293b;border-radius:12px">
                <div class="card-body p-4">
                    <h6 class="text-uppercase small mb-3" style="color:#d4a843;letter-spacing:.06em">Montants</h6>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-secondary small">Sous-total HT</span>
                        <span class="text-white small fw-semibold">{{ number_format($invoice->subtotal, 0, ',', ' ') }} XAF</span>
                    </div>
                    <div class="d-flex justify-content-between mb-3">
                        <span class="text-secondary small">TVA (18 %)</span>
                        <span class="text-white small fw-semibold">{{ number_format($invoice->tva_amount, 0, ',', ' ') }} XAF</span>
                    </div>
                    <hr style="border-color:rgba(255,255,255,.08)">
                    <div class="d-flex justify-content-between">
                        <span class="text-white fw-bold">Total TTC</span>
                        <span style="color:#d4a843;font-size:1.1rem;font-weight:700">
                            {{ number_format($invoice->total, 0, ',', ' ') }} XAF
                        </span>
                    </div>
                </div>
            </div>
            @if($invoice->notes)
            <div class="card border-0" style="background:#1e293b;border-radius:12px">
                <div class="card-body p-4">
                    <h6 class="text-uppercase small mb-2" style="color:#d4a843;letter-spacing:.06em">Notes</h6>
                    <p class="text-secondary small mb-0">{{ $invoice->notes }}</p>
                </div>
            </div>
            @endif
        </div>

        <!-- Lignes de facture -->
        <div class="col-lg-8">
            <div class="card border-0" style="background:#1e293b;border-radius:12px">
                <div class="card-header border-0 py-3 px-4"
                     style="background:rgba(212,168,67,.08);color:#d4a843;font-weight:600;letter-spacing:.04em">
                    <i class="bi bi-list-ul me-2"></i>Détail des Lignes
                </div>
                <div class="table-responsive">
                    <table class="table mb-0 align-middle" style="color:#e2e8f0">
                        <thead>
                            <tr style="font-size:.75rem;text-transform:uppercase;letter-spacing:.06em;
                                       color:#94a3b8;border-bottom:1px solid rgba(255,255,255,.08)">
                                <th class="py-3 ps-4">Description</th>
                                <th>Produit lié</th>
                                <th class="text-center">Qté</th>
                                <th class="text-end">Prix unit.</th>
                                <th class="text-end pe-4">Sous-total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($invoice->items as $item)
                            <tr style="border-bottom:1px solid rgba(255,255,255,.06)">
                                <td class="py-3 ps-4">{{ $item->description }}</td>
                                <td>
                                    @if($item->product)
                                        <span class="badge rounded-pill"
                                              style="background:rgba(212,168,67,.15);color:#d4a843;border:1px solid rgba(212,168,67,.3)">
                                            {{ $item->product->name }}
                                        </span>
                                    @else
                                        <span class="text-secondary small">Service</span>
                                    @endif
                                </td>
                                <td class="text-center">{{ $item->quantity }}</td>
                                <td class="text-end">{{ number_format($item->unit_price, 0, ',', ' ') }} XAF</td>
                                <td class="text-end pe-4 fw-semibold">{{ number_format($item->subtotal, 0, ',', ' ') }} XAF</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection
