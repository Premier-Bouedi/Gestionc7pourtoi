<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\FirestoreService;
use Illuminate\Http\JsonResponse;

class ProductApiController extends Controller
{
    public function __construct(
        private readonly FirestoreService $firestore,
    ) {}

    /**
     * Liste des produits depuis Cloud Firestore (collection « products »).
     */
    public function index(): JsonResponse
    {
        $products = $this->firestore->products()->map(fn ($product) => [
            'id' => $product->id,
            'name' => $product->name,
            'model' => $product->model,
            'price_xaf' => $product->price_xaf,
            'price_mad' => $product->price_mad,
            'stock_libreville' => $product->stock_libreville,
            'image_url' => $product->image_url,
        ]);

        return response()->json([
            'success' => true,
            'data' => $products,
        ]);
    }
}
