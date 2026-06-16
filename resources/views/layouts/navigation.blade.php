{{-- ================================================================
     NAVIGATION PRINCIPALE – ERP C7Pourt3
     - Fond gris très foncé (bg-gray-900 / dark layout)
     - Liens : Nouvelle Facture, Factures, Clients, Catalogue, Messages, Stock & Produits, Caisses, Paramètres.
     - Bouton "Espace Admin" distinctif pour le rôle admin
     - Bouton Baffle/Sourdine (Web Audio API)
================================================================ --}}
<nav x-data="{ open: false }" class="bg-gray-900 border-b border-gray-800">

    {{-- ── Barre principale ────────────────────────────────────────────────── --}}
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">

            {{-- ── Gauche : Logo + Liens principaux ──────────────────────── --}}
            <div class="flex items-center flex-1">

                {{-- Logo C7pourt3 --}}
                <div class="shrink-0 flex items-center me-6">
                    <a href="{{ route('dashboard') }}" class="flex items-center gap-2">
                        <i class="bi bi-gem text-amber-500 fs-4"></i>
                        <span class="text-white font-bold tracking-wider text-sm">C7Pourt3</span>
                    </a>
                </div>

                {{-- Liens de navigation (desktop) --}}
                <div class="hidden space-x-2 lg:flex items-center">
                    
                    {{-- 1. Nouvelle Facture (Caisse) --}}
                    <a href="{{ route('invoices.create') }}" 
                       class="text-gray-300 hover:text-amber-400 hover:bg-gray-800 px-3 py-2 rounded-md text-xs font-semibold uppercase tracking-wider {{ request()->routeIs('invoices.create') ? 'text-amber-400 bg-gray-800' : '' }}">
                        <i class="bi bi-plus-circle me-1"></i> Nouvelle Facture
                    </a>

                    {{-- 2. Factures --}}
                    <a href="{{ route('invoices.index') }}" 
                       class="text-gray-300 hover:text-white hover:bg-gray-800 px-3 py-2 rounded-md text-xs font-semibold uppercase tracking-wider {{ request()->routeIs('invoices.index') || request()->routeIs('invoices.show') ? 'text-white bg-gray-800' : '' }}">
                        <i class="bi bi-file-earmark-text me-1"></i> Factures
                    </a>

                    {{-- 3. Clients --}}
                    <a href="{{ route('clients.index') }}" 
                       class="text-gray-300 hover:text-white hover:bg-gray-800 px-3 py-2 rounded-md text-xs font-semibold uppercase tracking-wider {{ request()->routeIs('clients.index') ? 'text-white bg-gray-800' : '' }}">
                        <i class="bi bi-people me-1"></i> Clients
                    </a>

                    {{-- 4. Catalogue (Produits/Sacs) --}}
                    <a href="{{ route('products.index') }}" 
                       class="text-gray-300 hover:text-white hover:bg-gray-800 px-3 py-2 rounded-md text-xs font-semibold uppercase tracking-wider {{ request()->routeIs('products.index') ? 'text-white bg-gray-800' : '' }}">
                        <i class="bi bi-bag me-1"></i> Catalogue
                    </a>

                    {{-- 5. Messages --}}
                    <a href="{{ route('messages.index') }}" 
                       class="text-gray-300 hover:text-white hover:bg-gray-800 px-3 py-2 rounded-md text-xs font-semibold uppercase tracking-wider relative {{ request()->routeIs('messages.index') ? 'text-white bg-gray-800' : '' }}">
                        <i class="bi bi-chat-dots me-1"></i> Messages
                        <span id="unread-badge-desktop" class="absolute -top-1 -right-1 bg-red-500 text-white text-[10px] px-1.5 py-0.5 rounded-full font-bold hidden">0</span>
                    </a>

                    @auth
                        @if(Auth::user()->role === 'admin')
                            {{-- 6. Stock & Produits --}}
                            <a href="{{ route('admin.stocks.index') }}" 
                               class="text-gray-300 hover:text-white hover:bg-gray-800 px-3 py-2 rounded-md text-xs font-semibold uppercase tracking-wider {{ request()->routeIs('admin.stocks.index') ? 'text-white bg-gray-800' : '' }}">
                                <i class="bi bi-arrow-left-right me-1"></i> Stock & Flux
                            </a>

                            {{-- 7. Caisses (Personnel) --}}
                            <a href="{{ route('admin.caisses.index') }}" 
                               class="text-gray-300 hover:text-white hover:bg-gray-800 px-3 py-2 rounded-md text-xs font-semibold uppercase tracking-wider {{ request()->routeIs('admin.caisses.*') ? 'text-white bg-gray-800' : '' }}">
                                <i class="bi bi-person-badge me-1"></i> Caisses
                            </a>
                        @endif
                    @endauth

                    {{-- 8. Paramètres --}}
                    <a href="{{ route('profile.edit') }}" 
                       class="text-gray-300 hover:text-white hover:bg-gray-800 px-3 py-2 rounded-md text-xs font-semibold uppercase tracking-wider {{ request()->routeIs('profile.edit') ? 'text-white bg-gray-800' : '' }}">
                        <i class="bi bi-gear me-1"></i> Paramètres
                    </a>

                </div>
            </div>

            {{-- ── Droite : Espace Admin + Sourdine + Dropdown Utilisateur ────────── --}}
            <div class="hidden lg:flex lg:items-center lg:gap-3">

                {{-- 🔴 Bouton Espace Admin (visible uniquement pour les admins/patrons) --}}
                @auth
                    @if(Auth::user()->role === 'admin')
                        <a href="{{ route('admin.caisses.index') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-red-600 hover:bg-red-700 text-white text-xs font-bold rounded-md shadow-md transition-colors duration-150 uppercase tracking-wider">
                            <i class="bi bi-shield-lock-fill"></i>
                            Espace Admin
                        </a>
                    @endif
                @endauth

                {{-- 🔊 Bouton Baffle / Sourdine --}}
                <button id="audioToggle"
                    title="Activer / Désactiver les alertes sonores"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 border border-gray-700 hover:border-gray-500 text-gray-400 hover:text-white text-xs font-semibold rounded-md transition-all duration-150 focus:outline-none">
                    <svg id="audioIcon-on" class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M9.383 3.076A1 1 0 0110 4v12a1 1 0 01-1.707.707L4.586 13H2a1 1 0 01-1-1V8a1 1 0 011-1h2.586l3.707-3.707a1 1 0 011.09-.217zM14.657 2.929a1 1 0 011.414 0A9.972 9.972 0 0119 10a9.972 9.972 0 01-2.929 7.071 1 1 0 01-1.414-1.414A7.971 7.971 0 0017 10c0-2.21-.894-4.208-2.343-5.657a1 1 0 010-1.414zm-2.829 2.828a1 1 0 011.415 0A5.983 5.983 0 0115 10a5.984 5.984 0 01-1.757 4.243 1 1 0 01-1.415-1.415A3.984 3.984 0 0013 10a3.983 3.983 0 00-1.172-2.828 1 1 0 010-1.415z" clip-rule="evenodd" />
                    </svg>
                    <svg id="audioIcon-off" class="h-4 w-4 hidden" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M9.383 3.076A1 1 0 0110 4v12a1 1 0 01-1.707.707L4.586 13H2a1 1 0 01-1-1V8a1 1 0 011-1h2.586l3.707-3.707a1 1 0 011.09-.217zM12.293 7.293a1 1 0 011.414 0L15 8.586l1.293-1.293a1 1 0 111.414 1.414L16.414 10l1.293 1.293a1 1 0 01-1.414 1.414L15 11.414l-1.293 1.293a1 1 0 01-1.414-1.414L13.586 10l-1.293-1.293a1 1 0 010-1.414z" clip-rule="evenodd" />
                    </svg>
                    <span id="audioText">Sourdine</span>
                </button>

                {{-- Dropdown Utilisateur --}}
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-3 py-2 border border-gray-800 text-sm leading-4 font-medium rounded-md text-gray-300 bg-gray-800 hover:text-white transition ease-in-out duration-150">
                            <div>{{ Auth::user()->name }} <span class="text-xs text-amber-500 font-bold ml-1">({{ strtoupper(Auth::user()->role) }})</span></div>
                            <div class="ms-1">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')" class="text-gray-300 hover:bg-gray-800">
                            {{ __('Mon Profil') }}
                        </x-dropdown-link>

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <x-dropdown-link :href="route('logout')"
                                onclick="event.preventDefault(); this.closest('form').submit();"
                                class="text-gray-300 hover:bg-gray-800">
                                {{ __('Déconnexion') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            {{-- Hamburger (mobile/tablette) --}}
            <div class="-me-2 flex items-center lg:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-white hover:bg-gray-800 focus:outline-none transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    {{-- Menu responsive (mobile/tablette) --}}
    <div :class="{'block': open, 'hidden': ! open}" class="hidden lg:hidden bg-gray-900 border-t border-gray-800 px-4 pt-2 pb-4 space-y-1">
        
        <a href="{{ route('invoices.create') }}" class="block px-3 py-2 rounded-md text-sm font-semibold text-gray-300 hover:text-white hover:bg-gray-800">
            <i class="bi bi-plus-circle me-1"></i> Nouvelle Facture
        </a>
        <a href="{{ route('invoices.index') }}" class="block px-3 py-2 rounded-md text-sm font-semibold text-gray-300 hover:text-white hover:bg-gray-800">
            <i class="bi bi-file-earmark-text me-1"></i> Factures
        </a>
        <a href="{{ route('clients.index') }}" class="block px-3 py-2 rounded-md text-sm font-semibold text-gray-300 hover:text-white hover:bg-gray-800">
            <i class="bi bi-people me-1"></i> Clients
        </a>
        <a href="{{ route('products.index') }}" class="block px-3 py-2 rounded-md text-sm font-semibold text-gray-300 hover:text-white hover:bg-gray-800">
            <i class="bi bi-bag me-1"></i> Catalogue
        </a>
        <a href="{{ route('messages.index') }}" class="block px-3 py-2 rounded-md text-sm font-semibold text-gray-300 hover:text-white hover:bg-gray-800 relative">
            <i class="bi bi-chat-dots me-1"></i> Messages
            <span id="unread-badge-mobile" class="absolute top-2 right-2 bg-red-500 text-white text-[10px] px-1.5 py-0.5 rounded-full font-bold hidden">0</span>
        </a>

        @auth
            @if(Auth::user()->role === 'admin')
                <a href="{{ route('admin.stocks.index') }}" class="block px-3 py-2 rounded-md text-sm font-semibold text-gray-300 hover:text-white hover:bg-gray-800">
                    <i class="bi bi-arrow-left-right me-1"></i> Stock & Flux
                </a>
                <a href="{{ route('admin.caisses.index') }}" class="block px-3 py-2 rounded-md text-sm font-semibold text-gray-300 hover:text-white hover:bg-gray-800">
                    <i class="bi bi-person-badge me-1"></i> Caisses
                </a>
                <a href="{{ route('admin.caisses.index') }}" class="block px-3 py-2 rounded-md text-sm font-bold text-white bg-red-600 hover:bg-red-700 text-center uppercase tracking-wider mt-2 shadow">
                    Espace Admin
                </a>
            @endif
        @endauth

        <a href="{{ route('profile.edit') }}" class="block px-3 py-2 rounded-md text-sm font-semibold text-gray-300 hover:text-white hover:bg-gray-800">
            <i class="bi bi-gear me-1"></i> Paramètres
        </a>

        {{-- Bouton Baffle/Sourdine mobile --}}
        <button id="audioToggle-mobile"
            class="w-full text-left mt-2 px-3 py-2 text-sm text-gray-300 hover:text-white border border-gray-700 rounded-md flex items-center justify-between">
            <span>🔊 Alerte Sonore</span>
            <span id="audioText-mobile">Sourdine</span>
        </button>

        {{-- Utilisateur Info & Logout --}}
        <div class="pt-4 border-t border-gray-800 mt-4">
            <div class="text-xs text-gray-400 px-3">Connecté en tant que :</div>
            <div class="font-bold text-gray-200 px-3">{{ Auth::user()->name }} ({{ strtoupper(Auth::user()->role) }})</div>
            <form method="POST" action="{{ route('logout') }}" class="mt-2">
                @csrf
                <button type="submit" class="w-full text-left px-3 py-2 text-sm text-red-400 hover:text-white hover:bg-gray-800 rounded-md">
                    <i class="bi bi-box-arrow-right me-1"></i> Déconnexion
                </button>
            </form>
        </div>
    </div>
</nav>

{{-- Script Polling pour les messages non-lus ────────────────────────── --}}
<script>
document.addEventListener('DOMContentLoaded', function() {
    function fetchUnreadMessagesCount() {
        fetch('/messages/unread-count')
            .then(res => res.json())
            .then(data => {
                const badgeDesk = document.getElementById('unread-badge-desktop');
                const badgeMob = document.getElementById('unread-badge-mobile');
                const count = data.unread_count || 0;

                if (count > 0) {
                    if (badgeDesk) { badgeDesk.textContent = count; badgeDesk.classList.remove('hidden'); }
                    if (badgeMob) { badgeMob.textContent = count; badgeMob.classList.remove('hidden'); }
                } else {
                    if (badgeDesk) badgeDesk.classList.add('hidden');
                    if (badgeMob) badgeMob.classList.add('hidden');
                }
            })
            .catch(err => console.warn('Erreur Polling messages:', err));
    }

    // Lancer au démarrage, puis toutes les 15 secondes
    fetchUnreadMessagesCount();
    setInterval(fetchUnreadMessagesCount, 15000);
});
</script>
