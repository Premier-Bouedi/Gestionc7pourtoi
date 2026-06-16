<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ProductController extends Controller
{
    private $firebaseUrl;

    public function __construct()
    {
        $projectId = env('FIREBASE_PROJECT_ID', 'c7pourt3');
        $this->firebaseUrl = "https://firestore.googleapis.com/v1/projects/{$projectId}/databases/(default)/documents/products";
    }

    /**
     * 1. LIRE : depuis MySQL local — rapide, sans dépendance Firebase
     */
    public function index()
    {
        $products = Product::orderBy('name')->get();
        return view('products.index', compact('products'));
    }

    /**
     * 2. AJOUTER / REMPLACER : MySQL d'abord, puis sync Firestore
     */
    public function store(Request $request)
    {
        $request->validate([
            'code'             => 'required|string',
            'name'             => 'required|string',
            'model'            => 'required|string',
            'price_xaf'        => 'required|integer|min:0',
            'price_mad'        => 'required|integer|min:0',
            'stock_libreville' => 'required|integer|min:0',
        ]);

        // ID propre : "Sac Croco Noir" → "sac-croco-noir"
        $documentId = strtolower(str_replace(' ', '-', trim($request->code)));

        // a) Sauvegarde locale MySQL (updateOrCreate évite les doublons)
        $product = Product::updateOrCreate(
            ['code' => $documentId],
            [
                'name'             => $request->name,
                'model'            => $request->model,
                'price_xaf'        => (int)$request->price_xaf,
                'price_mad'        => (int)$request->price_mad,
                'image_url'        => $request->image_url ?: "/images/products/{$documentId}.png",
                'stock_libreville' => (int)$request->stock_libreville,
            ]
        );

        // b) Synchronisation Firestore pour le site vitrine (non bloquant)
        $firestoreData = [
            'fields' => [
                'name'             => ['stringValue'  => $product->name],
                'model'            => ['stringValue'  => $product->model],
                'price_xaf'        => ['integerValue' => $product->price_xaf],
                'price_mad'        => ['integerValue' => $product->price_mad],
                'image_url'        => ['stringValue'  => $product->image_url],
                'stock_libreville' => ['integerValue' => $product->stock_libreville],
            ],
        ];

        Http::patch("{$this->firebaseUrl}/{$documentId}", $firestoreData);

        return redirect()->route('products.index')
            ->with('success', "✅ \"{$product->name}\" enregistré en base locale et synchronisé sur le site !");
    }

    /**
     * 3. SUPPRIMER : MySQL d'abord, puis suppression Firestore
     */
    public function destroy($id)
    {
        $product = Product::findOrFail($id);

        // Suppression sur Firestore (via le code = ID du document)
        if ($product->code) {
            Http::delete("{$this->firebaseUrl}/{$product->code}");
        }

        // Suppression locale MySQL
        $product->delete();

        return redirect()->route('products.index')
            ->with('success', '🗑️ Produit supprimé de la base locale et du catalogue en ligne.');
    }
}
