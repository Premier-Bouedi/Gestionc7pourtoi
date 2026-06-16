<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ProductController extends Controller
{
    private $projectId;
    private $baseUrl;

    public function __construct()
    {
        // On force l'ID du projet Firebase c7pourt3
        $this->projectId = env('FIREBASE_PROJECT_ID', 'c7pourt3');
        $this->baseUrl = "https://firestore.googleapis.com/v1/projects/{$this->projectId}/databases/(default)/documents/products";
    }

    /**
     * AFFICHER : Récupère et liste les produits du site
     */
    public function index()
    {
        $response = Http::get($this->baseUrl);
        $products = [];

        if ($response->successful() && isset($response->json()['documents'])) {
            foreach ($response->json()['documents'] as $doc) {
                $fields = $doc['fields'] ?? [];
                $pathArray = explode('/', $doc['name']);
                $id = end($pathArray);

                // Extraction ultra-sécurisée (NoSQL — champs optionnels)
                $products[] = (object)[
                    'id'               => $id,
                    'name'             => $fields['name']['stringValue']             ?? ($fields['nom']['stringValue']            ?? 'Sac sans nom'),
                    'model'            => $fields['model']['stringValue']            ?? ($fields['category']['stringValue']       ?? 'C7Pourt3'),
                    'price_xaf'        => (int)($fields['price_xaf']['integerValue'] ?? ($fields['base_price']['integerValue']    ?? 0)),
                    'price_mad'        => (int)($fields['price_mad']['integerValue'] ?? 0),
                    'image_url'        => $fields['image_url']['stringValue']        ?? '/images/products/default.png',
                    'stock_libreville' => (int)($fields['stock_libreville']['integerValue'] ?? 0),
                ];
            }
        }

        return view('products.index', [
            'products' => collect($products),
        ]);
    }

    /**
     * AJOUTER / REMPLACER : Crée ou écrase un produit sur Firebase via PATCH
     */
    public function store(Request $request)
    {
        $request->validate([
            'identifiant'      => 'required|string',
            'name'             => 'required|string',
            'model'            => 'required|string',
            'price_xaf'        => 'required|integer',
            'price_mad'        => 'required|integer',
            'stock_libreville' => 'required|integer',
        ]);

        // Nettoyage de l'ID (ex: "Sac Croco Noir" → "sac-croco-noir")
        $documentId = strtolower(str_replace(' ', '-', $request->identifiant));

        $firestoreData = [
            'fields' => [
                'name'             => ['stringValue'  => $request->name],
                'model'            => ['stringValue'  => $request->model],
                'price_xaf'        => ['integerValue' => (int)$request->price_xaf],
                'price_mad'        => ['integerValue' => (int)$request->price_mad],
                'image_url'        => ['stringValue'  => $request->image_url ?: "/images/products/{$documentId}.png"],
                'stock_libreville' => ['integerValue' => (int)$request->stock_libreville],
            ],
        ];

        // PATCH : ajoute si inexistant, remplace si déjà présent
        $response = Http::patch("{$this->baseUrl}/{$documentId}", $firestoreData);

        if ($response->successful()) {
            return redirect()->route('products.index')
                ->with('success', "✅ Produit \"{$request->name}\" mis à jour sur Firebase !");
        }

        return back()->with('error', '❌ Échec de synchronisation avec Firebase : ' . $response->body());
    }

    /**
     * RETIRER : Supprime définitivement un produit de Firebase
     */
    public function destroy($id)
    {
        $response = Http::delete("{$this->baseUrl}/{$id}");

        if ($response->successful()) {
            return redirect()->route('products.index')
                ->with('success', '🗑️ Produit retiré définitivement du catalogue Firebase.');
        }

        return back()->with('error', '❌ Erreur lors de la suppression sur Firebase.');
    }
}
