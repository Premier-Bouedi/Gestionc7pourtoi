<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\FirestoreService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductController extends Controller
{
    /**
     * Récupère et affiche les vrais produits du site depuis Firestore
     */
    public function index()
    {
        // On récupère l'ID du projet configuré dans le .env (c7pourt3)
        $projectId = env('FIREBASE_PROJECT_ID', 'c7pourt3');
        
        // URL officielle pour lire la collection "products" du site
        $url = "https://firestore.googleapis.com/v1/projects/{$projectId}/databases/(default)/documents/products";
        
        $response = \Illuminate\Support\Facades\Http::get($url);
        $products = [];

        if ($response->successful() && isset($response->json()['documents'])) {
            foreach ($response->json()['documents'] as $doc) {
                $fields = $doc['fields'] ?? [];
                
                // Extraction de l'identifiant unique du document
                $pathArray = explode('/', $doc['name']);
                $id = end($pathArray);

                // Extraction des données avec les bons types Firestore NoSQL
                $products[] = (object)[
                    'id' => $id,
                    'name' => $fields['name']['stringValue'] ?? 'Sans nom',
                    'model' => $fields['model']['stringValue'] ?? ($fields['category']['stringValue'] ?? 'C7Pourt3'),
                    'price_xaf' => isset($fields['price_xaf']['integerValue']) ? (int)$fields['price_xaf']['integerValue'] : (int)($fields['price_xaf']['stringValue'] ?? ($fields['base_price']['integerValue'] ?? 0)),
                    'price_mad' => isset($fields['price_mad']['integerValue']) ? (int)$fields['price_mad']['integerValue'] : (int)($fields['price_mad']['stringValue'] ?? 0),
                    'image_url' => $fields['image_url']['stringValue'] ?? '/images/products/default.png',
                    'stock_libreville' => isset($fields['stock_libreville']['integerValue']) ? (int)$fields['stock_libreville']['integerValue'] : (int)($fields['stock_libreville']['stringValue'] ?? 0),
                ];
            }
        }

        // On passe la collection à la vue Blade existante
        return view('products.index', [
            'products' => collect($products),
            'firestoreConnected' => $response->successful()
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'model' => 'required|string',
            'name' => 'required|string',
            'price_xaf' => 'required|numeric',
            'price_mad' => 'required|numeric',
            'stock_libreville' => 'required|numeric',
            'image_url' => 'nullable|url',
        ]);

        $projectId = env('FIREBASE_PROJECT_ID', 'c7pourt3');
        $url = "https://firestore.googleapis.com/v1/projects/{$projectId}/databases/(default)/documents/products";
        
        // On crée un identifiant propre basé sur le nom (ex: "sac-croco-noir")
        $documentId = \Illuminate\Support\Str::slug($validated['name']);
        
        // On formate les données selon le standard Firestore
        $data = [
            'fields' => [
                'name' => ['stringValue' => $validated['name']],
                'model' => ['stringValue' => $validated['model']],
                'price_xaf' => ['integerValue' => (int)$validated['price_xaf']],
                'price_mad' => ['integerValue' => (int)$validated['price_mad']],
                'stock_libreville' => ['integerValue' => (int)$validated['stock_libreville']],
            ]
        ];

        if (!empty($validated['image_url'])) {
            $data['fields']['image_url'] = ['stringValue' => $validated['image_url']];
        }

        // Appel POST avec le documentId
        $response = \Illuminate\Support\Facades\Http::post("{$url}?documentId={$documentId}", $data);

        if ($response->successful()) {
            return redirect()->route('products.index')->with('success', "Le produit {$validated['name']} a été ajouté sur le site !");
        }

        return redirect()->route('products.index')->with('error', "Erreur Firebase lors de l'ajout.");
    }

    public function update(Request $request, string $id): RedirectResponse
    {
        $validated = $request->validate([
            'model' => 'required|string',
            'name' => 'required|string',
            'price_xaf' => 'required|numeric',
            'price_mad' => 'required|numeric',
            'stock_libreville' => 'required|numeric',
            'image_url' => 'nullable|url',
        ]);

        $projectId = env('FIREBASE_PROJECT_ID', 'c7pourt3');
        // L'URL pointe directement sur le document
        $url = "https://firestore.googleapis.com/v1/projects/{$projectId}/databases/(default)/documents/products/{$id}";

        $data = [
            'fields' => [
                'name' => ['stringValue' => $validated['name']],
                'model' => ['stringValue' => $validated['model']],
                'price_xaf' => ['integerValue' => (int)$validated['price_xaf']],
                'price_mad' => ['integerValue' => (int)$validated['price_mad']],
                'stock_libreville' => ['integerValue' => (int)$validated['stock_libreville']],
            ]
        ];

        if (!empty($validated['image_url'])) {
            $data['fields']['image_url'] = ['stringValue' => $validated['image_url']];
        }

        // Appel PATCH pour mettre à jour
        $response = \Illuminate\Support\Facades\Http::patch($url, $data);

        if ($response->successful()) {
            return redirect()->route('products.index')->with('success', "Le produit a été mis à jour avec succès !");
        }

        return redirect()->route('products.index')->with('error', "Erreur Firebase lors de la modification.");
    }

    public function destroy(string $id): RedirectResponse
    {
        $projectId = env('FIREBASE_PROJECT_ID', 'c7pourt3');
        $url = "https://firestore.googleapis.com/v1/projects/{$projectId}/databases/(default)/documents/products/{$id}";
        
        $response = \Illuminate\Support\Facades\Http::delete($url);

        if ($response->successful()) {
            return redirect()->route('products.index')->with('success', 'Produit supprimé définitivement du catalogue !');
        }

        return redirect()->route('products.index')->with('error', "Erreur Firebase lors de la suppression.");
    }
}
