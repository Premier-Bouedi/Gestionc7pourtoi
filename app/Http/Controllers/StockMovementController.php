<?php

namespace App\Http\Controllers;

use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StockMovementController extends Controller
{
    /**
     * Affiche l'historique et le tracé des flux de stocks des sacs de luxe.
     */
    public function index(Request $request): View
    {
        $movements = StockMovement::with(['product', 'user'])
            ->latest()
            ->paginate(20);

        return view('admin.stocks.index', compact('movements'));
    }
}
