@extends('layouts.app')

@section('title', 'Messagerie Interne | GestionFacture')

@section('content')
<div class="container-fluid px-4 py-4">

    <!-- En-tête -->
    <div class="mb-4">
        <h2 class="text-white fw-bold mb-0">
            <i class="bi bi-chat-left-dots me-2" style="color:#d4a843"></i>Messagerie Interne
        </h2>
        <p class="text-secondary small mb-0">Canal de communication sécurisé de la maison C7pourt3 (Mise à jour en direct)</p>
    </div>

    @if(session('success'))
    <div class="alert border-0 rounded-3 mb-4"
         style="background:rgba(34,197,94,.15);border-left:4px solid #22c55e !important;color:#4ade80">
        <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
    </div>
    @endif

    <div class="row g-4">
        
        <!-- Sidebar : Collaborateurs -->
        <div class="col-lg-4">
            <div class="card border-0" style="background:#1e293b;border-radius:12px;min-height:500px">
                <div class="card-header border-bottom border-secondary" style="color:#d4a843;font-weight:600">
                    <i class="bi bi-people me-2"></i>Collaborateurs
                </div>
                <div class="list-group list-group-flush p-2" style="background:transparent">
                    @foreach($users as $user)
                        <button type="button" 
                                onclick="selectUser('{{ $user->id }}', '{{ $user->name }}')" 
                                class="list-group-item list-group-item-action border-0 rounded-3 text-white mb-1 d-flex justify-content-between align-items-center"
                                style="background:rgba(255,255,255,.03);transition:all .2s">
                            <div>
                                <strong class="d-block text-white" style="font-size:.9rem">{{ $user->name }}</strong>
                                <small class="text-secondary" style="font-size:.75rem">{{ strtoupper($user->role) }}</small>
                            </div>
                            <span class="badge bg-amber-500 text-dark font-bold text-xs" style="background-color:#d4a843">Dispo</span>
                        </button>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Fenêtre de chat -->
        <div class="col-lg-8">
            <div class="card border-0 d-flex flex-column" style="background:#1e293b;border-radius:12px;height:550px">
                
                <div class="card-header border-bottom border-secondary text-white fw-bold d-flex align-items-center gap-2" id="chat-header">
                    <i class="bi bi-chat-square-text text-amber-500"></i>
                    <span id="chat-title">Messagerie Générale</span>
                </div>

                <!-- Zone de messages -->
                <div class="card-body flex-grow-1 overflow-y-auto p-4 d-flex flex-column gap-3" id="chat-box" style="max-height:400px">
                    @forelse($messages as $msg)
                        @php
                            $isMe = $msg->sender_id === auth()->id();
                        @endphp
                        <div class="d-flex flex-column {{ $isMe ? 'align-items-end' : 'align-items-start' }}">
                            <div class="px-3 py-2 rounded-3 text-white" 
                                 style="max-width:75%; font-size:.9rem; background: {{ $isMe ? 'linear-gradient(135deg, #d4a843, #b8922e)' : 'rgba(255,255,255,.05)' }}; color: {{ $isMe ? '#0f172a' : '#fff' }} !important">
                                <strong class="d-block mb-1" style="font-size:.75rem; opacity: .8">
                                    {{ $msg->sender->name }}
                                </strong>
                                {{ $msg->content }}
                            </div>
                            <small class="text-secondary mt-1" style="font-size:.7rem">{{ $msg->created_at->diffForHumans() }}</small>
                        </div>
                    @empty
                        <div class="text-center py-5 text-secondary flex-grow-1 d-flex flex-column align-items-center justify-content-center">
                            <i class="bi bi-chat-quote fs-1 mb-2" style="color:rgba(255,255,255,.05)"></i>
                            Aucun message récent. Sélectionnez un collaborateur et commencez à discuter.
                        </div>
                    @endforelse
                </div>

                <!-- Formulaire d'envoi -->
                <div class="card-footer border-top border-secondary p-3">
                    <form method="POST" action="{{ route('messages.store') }}" id="message-form">
                        @csrf
                        <!-- Par défaut on envoie au premier utilisateur de la liste si aucun n'est cliqué -->
                        <input type="hidden" name="receiver_id" id="receiver_id" value="{{ $users->first()?->id }}">
                        
                        <div class="input-group">
                            <input type="text" name="content" id="message-input" 
                                   class="form-control border-0 text-white" 
                                   placeholder="Écrivez votre message ici..." 
                                   style="background:rgba(255,255,255,.05)" required>
                            <button type="submit" class="btn" style="background:linear-gradient(135deg,#d4a843,#b8922e);color:#0f172a;font-weight:700">
                                <i class="bi bi-send-fill"></i>
                            </button>
                        </div>
                    </form>
                </div>

            </div>
        </div>

    </div>
</div>

<script>
function selectUser(id, name) {
    document.getElementById('receiver_id').value = id;
    document.getElementById('chat-title').textContent = "Discussion avec " + name;
    document.getElementById('message-input').placeholder = "Écrire à " + name + "...";
    document.getElementById('message-input').focus();
}

document.addEventListener('DOMContentLoaded', function() {
    // Scroll tout en bas du chat au chargement
    const chatBox = document.getElementById('chat-box');
    if (chatBox) {
        chatBox.scrollTop = chatBox.scrollHeight;
    }
});
</script>
@endsection
