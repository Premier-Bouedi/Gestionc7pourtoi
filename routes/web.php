<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\CaissePersonnelController;
use App\Http\Controllers\StockMovementController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Toutes les routes de l'ERP nécessitent d'être connecté
Route::middleware(['auth', 'verified'])->group(function () {
    
    // ── 1. Routes Communes (Admin + Caissier) ─────────────────────────────────
    
    // Dashboard principal
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/active-incidents', [DashboardController::class, 'activeIncidents'])->name('incidents.active');
    Route::post('/dashboard/incidents/{id}/resolve', [DashboardController::class, 'resolveIncident'])->name('incidents.resolve');

    // Factures & Caisse
    Route::get('/invoices', [InvoiceController::class, 'index'])->name('invoices.index');
    Route::get('/invoices/create', [InvoiceController::class, 'create'])->name('invoices.create');
    Route::post('/invoices', [InvoiceController::class, 'store'])->name('invoices.store');
    Route::get('/invoices/{id}', [InvoiceController::class, 'show'])->name('invoices.show');

    // Clients VIP
    Route::get('/clients', [ClientController::class, 'index'])->name('clients.index');
    Route::post('/clients', [ClientController::class, 'store'])->name('clients.store');

    // Catalogue & CRUD Produits (Sacs de Luxe)
    Route::get('/products', [ProductController::class, 'index'])->name('products.index');
    Route::post('/products', [ProductController::class, 'store'])->name('products.store');
    Route::put('/products/{id}', [ProductController::class, 'update'])->name('products.update');
    Route::delete('/products/{id}', [ProductController::class, 'destroy'])->name('products.destroy');

    // Messagerie Interne
    Route::get('/messages', [MessageController::class, 'index'])->name('messages.index');
    Route::post('/messages', [MessageController::class, 'store'])->name('messages.store');
    Route::get('/messages/unread-count', [MessageController::class, 'unreadCount'])->name('messages.unread');

    // ── 2. Routes Strictement Admin (Middleware 'admin') ─────────────────────
    Route::middleware(['admin'])->group(function () {
        // Espace Admin - Validation Facture
        Route::post('/invoices/{id}/mark-paid', [InvoiceController::class, 'markPaid'])->name('invoices.mark-paid');

        // Gestion du personnel / caissiers
        Route::get('/admin/caisses', [CaissePersonnelController::class, 'index'])->name('admin.caisses.index');
        Route::get('/admin/caisses/create', [CaissePersonnelController::class, 'create'])->name('admin.caisses.create');
        Route::post('/admin/caisses', [CaissePersonnelController::class, 'store'])->name('admin.caisses.store');

        // Flux logistiques et mouvements de stocks
        Route::get('/admin/stocks', [StockMovementController::class, 'index'])->name('admin.stocks.index');
    });
});

// Profil utilisateur
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
