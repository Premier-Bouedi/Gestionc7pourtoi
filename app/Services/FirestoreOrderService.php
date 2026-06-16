<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use App\Models\Order;
use App\Models\Product; // Pour lier les informations importantes des produits

class FirestoreOrderService
{
    /**
     * Synchronise les commandes et met à jour les informations importantes des produits (Prix, Stocks)
     */
    public function syncPendingOrders()
    {
        $projectId = env('FIREBASE_PROJECT_ID', 'c7pourt3');
        $baseUrl = "https://firestore.googleapis.com/v1/projects/{$projectId}/databases/(default)/documents";

        // 1. RECUPÉRATION DES COMMANDES
        $responseOrders = Http::get("{$baseUrl}/orders");
        
        if ($responseOrders->successful()) {
            $data = $responseOrders->json();
            
            if (!isset($data['documents'])) {
                return 0; // Collection vide
            }
            
            $count = 0;

            foreach ($data['documents'] as $doc) {
                
                $pathArray = explode('/', $doc['name']);
                $firestoreId = end($pathArray);
                $fields = $doc['fields'] ?? [];

                $deliveryStatus = $fields['delivery_status']['stringValue'] ?? 'pending';

                // Si la commande est en attente, on l'enregistre avec toutes ses informations
                if ($deliveryStatus === 'pending') {
                    $orderExists = Order::where('firebase_id', $firestoreId)->exists();
                    
                    if (!$orderExists) {
                        try {
                            Order::create([
                                'firebase_id'    => $firestoreId,
                                'product_id'     => $fields['product_id']['stringValue'] ?? null,
                                'product_name'   => $fields['product_name']['stringValue'] ?? 'Produit Inconnu',
                                'total_amount'   => $fields['total_amount']['integerValue'] ?? ($fields['total_amount']['doubleValue'] ?? 0),
                                'payment_status' => $fields['payment_status']['stringValue'] ?? 'pending',
                                'status'         => 'pending'
                            ]);

                            // 2. MISE À JOUR AUTOMATIQUE DU STOCK LOCAL DE CE PRODUIT
                            $productId = $fields['product_id']['stringValue'] ?? null;
                            if ($productId) {
                                $this->mettreAJourStockLocal($productId);
                            }

                            $count++;
                        } catch (\Exception $e) {
                            continue;
                        }
                    }
                }
            }
            return $count;
        }

        return false;
    }

    /**
     * Va chercher les vraies informations du produit sur Firebase pour synchroniser les stocks locaux
     */
    private function mettreAJourStockLocal($productId)
    {
        $projectId = env('FIREBASE_PROJECT_ID', 'c7pourt3');
        $responseProduct = Http::get("https://firestore.googleapis.com/v1/projects/{$projectId}/databases/(default)/documents/products/{$productId}");

        if ($responseProduct->successful()) {
            $fields = $responseProduct->json()['fields'] ?? [];
            
            // On cherche le produit dans notre code/BDD locale
            // J'ai remplacé ->where('code') par ->find() car ton modèle Product utilise 'id' et n'a pas de colonne 'code'.
            $localProduct = Product::find($productId);
            
            if ($localProduct) {
                $localProduct->update([
                    'stock_libreville' => $fields['stock_libreville']['integerValue'] ?? $localProduct->stock_libreville,
                    'price_xaf'        => $fields['price_xaf']['integerValue'] ?? $localProduct->price_xaf,
                    'price_mad'        => $fields['price_mad']['integerValue'] ?? $localProduct->price_mad,
                ]);
            }
        }
    }
}
