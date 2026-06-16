<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

class MessageController extends Controller
{
    /**
     * Affiche l'interface de messagerie et marque les messages reçus comme lus.
     */
    public function index(): View
    {
        $userId = Auth::id();

        // Liste des autres collaborateurs
        $users = User::where('id', '!=', $userId)->orderBy('name')->get();

        // Charger les 50 derniers messages de la messagerie centrale
        $messages = Message::with(['sender', 'receiver'])
            ->where(function($q) use ($userId) {
                $q->where('sender_id', $userId)
                  ->orWhere('receiver_id', $userId);
            })
            ->latest()
            ->take(50)
            ->get()
            ->reverse();

        // Marquer comme lus
        Message::where('receiver_id', $userId)
               ->where('is_read', false)
               ->update(['is_read' => true]);

        return view('messages.index', compact('users', 'messages'));
    }

    /**
     * Envoie un nouveau message interne.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'receiver_id' => 'required|uuid|exists:users,id',
            'content'     => 'required|string|max:2000',
        ]);

        Message::create([
            'sender_id'   => Auth::id(),
            'receiver_id' => $validated['receiver_id'],
            'content'     => $validated['content'],
            'is_read'     => false,
        ]);

        return back()->with('success', 'Message envoyé.');
    }

    /**
     * Endpoint API de Polling pour compter les messages non lus de la navbar.
     */
    public function unreadCount(): JsonResponse
    {
        $count = Message::where('receiver_id', Auth::id())
                        ->where('is_read', false)
                        ->count();

        return response()->json([
            'unread_count' => $count
        ]);
    }
}
