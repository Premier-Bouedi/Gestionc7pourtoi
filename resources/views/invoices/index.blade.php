@extends('layouts.app')

@section('title', 'Factures | GestionFacture')

@section('content')
<div class="container-fluid px-4 py-4">

    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h2 class="text-white fw-bold mb-0">
                <i class="bi bi-journal-text me-2" style="color:#d4a843"></i>Factures
            </h2>
            <p class="text-secondary small mb-0">Historique et suivi de toutes les factures</p>
        </div>
        <a href="{{ route('invoices.create') }}" class="btn"
           style="background:linear-gradient(135deg,#d4a843,#b8922e);color:#0f172a;font-weight:700;border-radius:8px">
            <i class="bi bi-plus-lg me-2"></i>Nouvelle Facture
        </a>
    </div>

    @if(session('success'))
    <div class="alert border-0 rounded-3 mb-4"
         style="background:rgba(34,197,94,.15);border-left:4px solid #22c55e !important;color:#4ade80">
        <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
    </div>
    @endif

    <div class="card border-0" style="background:#1e293b;border-radius:12px">
        <div class="table-responsive">
            <table class="table mb-0 align-middle" style="color:#e2e8f0">
                <thead>
                    <tr style="background:rgba(212,168,67,.08);font-size:.75rem;text-transform:uppercase;letter-spacing:.06em;color:#d4a843;border-bottom:1px solid rgba(255,255,255,.08)">
                        <th class="py-3 px-4">N° Facture</th>
                        <th>Client</th>
                        <th>Date</th>
                        <th>Échéance</th>
                        <th>Total TTC</th>
                        <th>Statut</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($invoices as $invoice)
                    <tr style="border-bottom:1px solid rgba(255,255,255,.06)">
                        <td class="py-3 px-4">
                            <span class="fw-bold" style="color:#d4a843">{{ $invoice->invoice_number }}</span>
                        </td>
                        <td>{{ $invoice->client->name ?? '—' }}</td>
                        <td class="text-secondary small">{{ $invoice->issue_date->format('d/m/Y') }}</td>
                        <td class="text-secondary small">{{ $invoice->due_date->format('d/m/Y') }}</td>
                        <td class="fw-bold">{{ number_format($invoice->total, 0, ',', ' ') }} XAF</td>
                        <td>
                            @php
                                $badges = [
                                    'draft'     => ['bg-secondary', 'Brouillon'],
                                    'sent'      => ['bg-info text-dark', 'Envoyée'],
                                    'paid'      => ['bg-success', 'Payée'],
                                    'overdue'   => ['bg-danger', 'En retard'],
                                    'cancelled' => ['bg-dark border', 'Annulée'],
                                ];
                                [$cls, $lbl] = $badges[$invoice->status] ?? ['bg-secondary', $invoice->status];
                            @endphp
                            <span class="badge rounded-pill {{ $cls }}">{{ $lbl }}</span>
                        </td>
                        <td class="text-end pe-4">
                            <a href="{{ route('invoices.show', $invoice->id) }}"
                               class="btn btn-sm btn-outline-primary me-1" title="Voir">
                                <i class="bi bi-eye"></i>
                            </a>
                            @if(auth()->user()->role === 'admin' && $invoice->status !== 'paid')
                            <form method="POST" action="{{ route('invoices.mark-paid', $invoice->id) }}"
                                  class="d-inline"
                                  onsubmit="return confirm('Marquer la facture {{ $invoice->invoice_number }} comme payée ?')">
                                @csrf
                                <button class="btn btn-sm btn-outline-success" title="Marquer payée">
                                    <i class="bi bi-check2-circle"></i>
                                </button>
                            </form>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-5 text-secondary">
                            <i class="bi bi-journal-x fs-1 d-block mb-2"></i>
                            Aucune facture enregistrée.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($invoices->hasPages())
        <div class="card-footer border-0 d-flex justify-content-center py-3"
             style="background:rgba(255,255,255,.03)">
            {{ $invoices->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
