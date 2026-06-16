<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;

class CaissePersonnelController extends Controller
{
    /**
     * Liste du personnel de caisse avec le nombre de factures émises.
     */
    public function index(): View
    {
        $staff = User::withCount('invoices')
                     ->orderBy('role')
                     ->orderBy('name')
                     ->get();

        return view('admin.caisses.index', compact('staff'));
    }

    /**
     * Formulaire de création de personnel.
     */
    public function create(): View
    {
        return view('admin.caisses.create');
    }

    /**
     * Enregistre un nouveau caissier ou administrateur.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'           => 'required|string|max:255',
            'email'          => 'required|string|email|max:255|unique:users,email',
            'password'       => 'required|string|min:8',
            'role'           => 'required|in:admin,caissier',
            'phone_whatsapp' => 'nullable|string|max:50',
        ]);

        User::create([
            'name'           => $validated['name'],
            'email'          => $validated['email'],
            'password'       => Hash::make($validated['password']),
            'role'           => $validated['role'],
            'phone_whatsapp' => $validated['phone_whatsapp'] ?? null,
        ]);

        return redirect()
            ->route('admin.caisses.index')
            ->with('success', 'Nouveau membre du personnel ajouté avec succès.');
    }
}
