<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-bs-theme="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'C7Pourt3 — Logistique & ERP')</title>

    <!-- Bootstrap 5.3 (Dark Theme nativement supporté) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- Fonts Premium : Playfair Display pour la marque, Plus Jakarta Sans pour le texte -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400..900;1,400..900&family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        /* ── Charte Graphique Sombre / Anthracite Premium ── */
        body {
            background-color: #0b0f19; /* Gris anthracite très profond */
            color: #e2e8f0;
            font-family: 'Plus Jakarta Sans', sans-serif;
            min-height: 100vh;
        }

        .navbar-fac {
            background-color: #05070c !important; /* Noir mat */
            border-bottom: 1px solid rgba(212, 175, 55, 0.15) !important; /* Fine bordure dorée */
        }

        .nav-link {
            color: #94a3b8 !important;
            font-weight: 700;
            font-size: 0.78rem;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            padding: 0.5rem 0.8rem !important;
            transition: all 0.2s ease;
        }

        .nav-link:hover, .nav-link.active {
            color: #ffffff !important;
            background-color: rgba(255, 255, 255, 0.04);
            border-radius: 6px;
        }

        .nav-link.active-gold {
            color: #d4af37 !important; /* Couleur Or/Gold */
            background-color: rgba(212, 175, 55, 0.08);
            border-radius: 6px;
            border: 1px solid rgba(212, 175, 55, 0.2);
        }

        /* ── Badge notification messages ── */
        .badge-msg-count {
            font-size: 0.65rem;
            position: absolute;
            top: 4px;
            right: 4px;
            padding: 0.25em 0.5em;
            border-radius: 50rem;
        }

        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
        }
        ::-webkit-scrollbar-track {
            background: #0b0f19;
        }
        ::-webkit-scrollbar-thumb {
            background: #1e293b;
            border-radius: 4px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #d4af37;
        }
    </style>
    @stack('styles')
</head>
<body>

    <!-- ── Barre de Navigation C7Pourt3 ── -->
    <nav class="navbar navbar-expand-xl navbar-dark navbar-fac py-2 sticky-top shadow-sm">
        <div class="container-fluid px-4">
            
            <!-- Logo OR/GOLD corrigé -->
            <a class="navbar-brand d-flex align-items-center gap-2" href="{{ route('dashboard') }}" style="text-decoration: none;">
                <span style="color: #d4af37; font-family: 'Playfair Display', serif; font-weight: 800; font-size: 1.6rem; letter-spacing: 0.5px;">
                    C7Pourt3
                </span>
            </a>

            <!-- Toggle Mobile -->
            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent" aria-controls="navbarContent" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <!-- Liens et Options -->
            <div class="collapse navbar-collapse" id="navbarContent">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0 gap-1 ms-3">
                    
                    <!-- 1. + NOUVELLE FACTURE -->
                    <li class="nav-item">
                        <a class="nav-link active-gold" href="{{ route('invoices.create') }}">
                            + NOUVELLE FACTURE
                        </a>
                    </li>

                    <!-- 2. 📄 FACTURES -->
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('invoices.index') || request()->routeIs('invoices.show') ? 'active' : '' }}" href="{{ route('invoices.index') }}">
                            📄 FACTURES
                        </a>
                    </li>

                    <!-- 3. 👥 CLIENTS -->
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('clients.index') ? 'active' : '' }}" href="{{ route('clients.index') }}">
                            👥 CLIENTS
                        </a>
                    </li>

                    <!-- 4. 👜 CATALOGUE -->
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('products.index') ? 'active' : '' }}" href="{{ route('products.index') }}">
                            👜 CATALOGUE
                        </a>
                    </li>

                    <!-- 5. 💬 MESSAGES -->
                    <li class="nav-item position-relative">
                        <a class="nav-link {{ request()->routeIs('messages.index') ? 'active' : '' }}" href="{{ route('messages.index') }}">
                            💬 MESSAGES
                            <span id="unread-badge-global" class="badge bg-danger badge-msg-count d-none">0</span>
                        </a>
                    </li>

                    @auth
                        @if(Auth::user()->role === 'admin')
                            <!-- 6. 📦 STOCK & PRODUITS (Admin Only) -->
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('admin.stocks.index') ? 'active' : '' }}" href="{{ route('admin.stocks.index') }}">
                                    📦 STOCK & PRODUITS
                                </a>
                            </li>

                            <!-- 7. 🧮 CAISSES (Admin Only) -->
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('admin.caisses.index') ? 'active' : '' }}" href="{{ route('admin.caisses.index') }}">
                                    🧮 CAISSES
                                </a>
                            </li>
                        @endif
                    @endauth

                    <!-- 8. ⚙️ PARAMÈTRES -->
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('profile.edit') ? 'active' : '' }}" href="{{ route('profile.edit') }}">
                            ⚙️ PARAMÈTRES
                        </a>
                    </li>

                </ul>

                <!-- Options Droite : Espace Admin + Sourdine + Utilisateur -->
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    
                    <!-- 🚨 Espace Admin (Bouton Rouge Distinctif) -->
                    @auth
                        @if(Auth::user()->role === 'admin')
                            <a href="{{ route('admin.caisses.index') }}" class="btn btn-danger btn-sm px-3 rounded-2 fw-bold text-uppercase" style="font-size: 0.75rem;">
                                🚨 Espace Admin
                            </a>
                        @endif
                    @endauth

                    <!-- 🔊 Bouton Sourdine / Baffle (Marquage exact requis) -->
                    <button id="audioToggle" class="btn btn-outline-secondary btn-sm px-3 ms-2 text-uppercase font-monospace" style="font-size: 0.75rem;">
                        <i id="audioIcon" class="bi bi-volume-up-fill"></i> <span id="audioText">SOURDINE</span>
                    </button>

                    <!-- Profile Dropdown -->
                    <div class="dropdown">
                        <button class="btn btn-dark btn-sm dropdown-toggle px-3 text-secondary" type="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-person-circle me-1"></i> {{ Auth::user()->name }}
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end border-secondary shadow bg-dark" aria-labelledby="userDropdown">
                            <li>
                                <a class="dropdown-item text-secondary hover:text-white" href="{{ route('profile.edit') }}">
                                    <i class="bi bi-person me-2"></i> Mon Profil
                                </a>
                            </li>
                            <li><hr class="dropdown-divider border-secondary"></li>
                            <li>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="dropdown-item text-danger">
                                        <i class="bi bi-box-arrow-right me-2"></i> Déconnexion
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </div>

                </div>
            </div>

        </div>
    </nav>

    <!-- Zone de Contenu Dynamique -->
    <main class="container py-4">
        @isset($header)
            <div class="mb-4">
                {{ $header }}
            </div>
        @endisset

        @yield('content')

        @isset($slot)
            {{ $slot }}
        @endisset
    </main>

    <!-- Bootstrap 5 JavaScript Bundle via CDN -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>

    {{-- ================================================================
         SYSTEME D'ALERTE SONORE GLOBAL – ERP GestionFacture
         Web Audio API : bip de succès à 800Hz pendant 0.15s, sourdine, localStorage
    ================================================================ --}}
    <script>
    (function () {
        'use strict';

        let audioCtx = null;
        let audioReady = false;

        function initAudioContext() {
            if (audioCtx) return;
            try {
                audioCtx = new (window.AudioContext || window.webkitAudioContext)();
                audioReady = true;
            } catch (err) {
                console.warn('[Audio ERP] Web Audio API non disponible:', err);
            }
        }

        document.addEventListener('click', function onFirstClick() {
            initAudioContext();
            document.removeEventListener('click', onFirstClick);
        }, { once: true });

        function playSuccessBeep() {
            if (!audioCtx || !audioReady) return;
            if (isMuted()) return;

            try {
                if (audioCtx.state === 'suspended') {
                    audioCtx.resume();
                }

                const oscillator = audioCtx.createOscillator();
                const gainNode = audioCtx.createGain();

                oscillator.connect(gainNode);
                gainNode.connect(audioCtx.destination);

                oscillator.type = 'sine';
                oscillator.frequency.setValueAtTime(800, audioCtx.currentTime); // 800 Hz

                gainNode.gain.setValueAtTime(0.5, audioCtx.currentTime);
                gainNode.gain.exponentialRampToValueAtTime(0.001, audioCtx.currentTime + 0.15);

                oscillator.start(audioCtx.currentTime);
                oscillator.stop(audioCtx.currentTime + 0.15);
            } catch (err) {
                console.warn('[Audio ERP] Erreur bip:', err);
            }
        }

        window.playSuccessBeep = playSuccessBeep;

        const MUTE_KEY = 'erp_audio_muted';

        function isMuted() {
            return localStorage.getItem(MUTE_KEY) === 'true';
        }

        function setMuted(state) {
            localStorage.setItem(MUTE_KEY, state ? 'true' : 'false');
        }

        function toggleMute() {
            const wasMuted = isMuted();
            setMuted(!wasMuted);
            updateAudioUI(!wasMuted);
        }

        function updateAudioUI(muted) {
            const btn = document.getElementById('audioToggle');
            const icon = document.getElementById('audioIcon');
            const text = document.getElementById('audioText');

            if (btn && icon && text) {
                if (muted) {
                    icon.className = 'bi bi-volume-mute-fill text-warning';
                    text.textContent = 'MUTÉ';
                    btn.classList.remove('btn-outline-secondary');
                    btn.classList.add('btn-outline-warning');
                } else {
                    icon.className = 'bi bi-volume-up-fill';
                    text.textContent = 'SOURDINE';
                    btn.classList.remove('btn-outline-warning');
                    btn.classList.add('btn-outline-secondary');
                }
            }
        }

        document.addEventListener('DOMContentLoaded', function () {
            updateAudioUI(isMuted());

            const toggleBtn = document.getElementById('audioToggle');
            if (toggleBtn) {
                toggleBtn.addEventListener('click', function (e) {
                    e.preventDefault();
                    initAudioContext();
                    toggleMute();
                });
            }

            @if(session('success'))
                setTimeout(function () {
                    if (!isMuted()) {
                        try {
                            if (!audioCtx) {
                                audioCtx = new (window.AudioContext || window.webkitAudioContext)();
                                audioReady = true;
                            }
                            playSuccessBeep();
                        } catch (err) {
                            console.info('[Audio ERP] Bip auto bloqué par le navigateur.');
                        }
                    }
                }, 300);
            @endif
        });

        // Polling dynamique pour les messages non-lus
        function fetchUnreadMessagesCount() {
            fetch('{{ route("messages.unread") }}')
                .then(res => res.json())
                .then(data => {
                    const badge = document.getElementById('unread-badge-global');
                    const count = data.unread_count || 0;

                    if (badge) {
                        if (count > 0) {
                            badge.textContent = count;
                            badge.classList.remove('d-none');
                        } else {
                            badge.classList.add('d-none');
                        }
                    }
                })
                .catch(err => console.warn('Erreur Polling messages:', err));
        }

        document.addEventListener('DOMContentLoaded', function() {
            fetchUnreadMessagesCount();
            setInterval(fetchUnreadMessagesCount, 15000);
        });

    })();
    </script>
    @stack('scripts')
</body>
</html>
