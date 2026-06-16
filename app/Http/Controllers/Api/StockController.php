<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\Product;

class StockController extends Controller
{
    /**
     * Obtenir l'état des stocks au Gabon (Libreville)
     */
    public function index(): JsonResponse
    {
        $products = Product::select('id', 'name', 'model', 'price_xaf', 'stock_libreville')->get();

        return response()->json([
            'success' => true,
            'data' => $products
        ]);
    }
}
