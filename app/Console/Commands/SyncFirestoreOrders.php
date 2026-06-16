<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\FirestoreOrderService;

class SyncFirestoreOrders extends Command
{
    /**
     * Le nom de la commande
     *
     * @var string
     */
    protected $signature = 'firestore:sync-orders';

    /**
     * La description de la commande
     *
     * @var string
     */
    protected $description = 'Synchronise les nouvelles commandes depuis Cloud Firestore vers la base de données Laravel';

    /**
     * Exécution de la commande
     */
    public function handle(FirestoreOrderService $firestoreService)
    {
        $this->info('Connexion à Firestore (C7Pourt3) en cours...');

        $syncedCount = $firestoreService->syncPendingOrders();

        if ($syncedCount !== false) {
            $this->info("Succès ! {$syncedCount} nouvelle(s) commande(s) en attente importée(s) dans le Dashboard.");
        } else {
            $this->error("Erreur lors de la communication avec l'API REST de Firebase.");
        }
    }
}
