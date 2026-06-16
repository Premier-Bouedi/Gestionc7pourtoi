@extends('layouts.app')

@section('title', 'Ajouter Personnel Caisse | Espace Admin')

@section('content')
<div class="container-fluid px-4 py-4">

    <!-- En-tête -->
    <div class="d-flex align-items-center gap-3 mb-4">
        <a href="{{ route('admin.caisses.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left"></i>
        </a>
        <div>
            <h2 class="text-white fw-bold mb-0">
                <i class="bi bi-person-plus text-danger"></i> Ajouter un Personnel de Caisse
            </h2>
            <p class="text-secondary small mb-0">Créez un nouveau compte avec accès sécurisé à votre ERP GestionFacture</p>
        </div>
    </div>

    @if ($errors->any())
    <div class="alert alert-danger border-0 rounded-3 mb-4" 
         style="background:rgba(239,68,68,.15);border-left:4px solid #ef4444 !important;color:#f87171">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <div class="row">
        <div class="col-lg-6">
            <div class="card border-0" style="background:#1e293b;border-radius:12px">
                <div class="card-header border-bottom border-secondary text-white fw-bold">
                    <i class="bi bi-shield-plus me-2 text-danger"></i>Informations d'identification
                </div>
                <div class="card-body p-4">
                    <form method="POST" action="{{ route('admin.caisses.store') }}">
                        @csrf

                        <!-- Nom complet -->
                        <div class="mb-3">
                            <label class="form-label text-secondary small">Nom Complet *</label>
                            <input type="text" name="name" class="form-control border-0 text-white" 
                                   style="background:rgba(255,255,255,.05)" required value="{{ old('name') }}"
                                   placeholder="Ex: Jean Paul Ndong">
                        </div>

                        <!-- Email -->
                        <div class="mb-3">
                            <label class="form-label text-secondary small">Adresse Email *</label>
                            <input type="email" name="email" class="form-control border-0 text-white" 
                                   style="background:rgba(255,255,255,.05)" required value="{{ old('email') }}"
                                   placeholder="Ex: jp.ndong@c7pourt3.com">
                        </div>

                        <!-- Mot de passe -->
                        <div class="mb-3">
                            <label class="form-label text-secondary small">Mot de passe temporaire *</label>
                            <input type="password" name="password" class="form-control border-0 text-white" 
                                   style="background:rgba(255,255,255,.05)" required
                                   placeholder="Minimum 8 caractères">
                        </div>

                        <!-- Rôle -->
                        <div class="mb-3">
                            <label class="form-label text-secondary small">Rôle & Privilèges *</label>
                            <select name="role" class="form-select border-0 text-white" 
                                    style="background:rgba(255,255,255,.05)" required>
                                <option value="caissier" {{ old('role') == 'caissier' ? 'selected' : '' }}>Caissier (Accès restreint)</option>
                                <option value="admin" {{ old('role') == 'admin' ? 'selected' : '' }}>Administrateur (Accès total)</option>
                            </select>
                        </div>

                        <!-- Whatsapp -->
                        <div class="mb-4">
                            <label class="form-label text-secondary small">Numéro WhatsApp</label>
                            <input type="text" name="phone_whatsapp" class="form-control border-0 text-white" 
                                   style="background:rgba(255,255,255,.05)" value="{{ old('phone_whatsapp') }}"
                                   placeholder="Ex: +24177112233">
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-danger font-bold py-2 rounded-3">
                                Enregistrer le collaborateur
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection
