<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Projet Firebase C7Pourt3
    |--------------------------------------------------------------------------
    |
    | Placez le fichier JSON du compte de service Firebase dans
    | storage/app/firebase/c7pourt3-credentials.json ou renseignez
    | FIREBASE_CREDENTIALS avec le chemin absolu.
    |
    */

    'project_id' => env('FIREBASE_PROJECT_ID', 'c7pourt3'),

    'credentials' => env('FIREBASE_CREDENTIALS', storage_path('app/firebase/c7pourt3-credentials.json')),

    'collections' => [
        'clients' => env('FIREBASE_CLIENTS_COLLECTION', 'clients'),
        'products' => env('FIREBASE_PRODUCTS_COLLECTION', 'products'),
        'orders' => env('FIREBASE_ORDERS_COLLECTION', 'orders'),
        'incidents' => env('FIREBASE_INCIDENTS_COLLECTION', 'incidents'),
    ],

];
