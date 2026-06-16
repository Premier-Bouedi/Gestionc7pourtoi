<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ajoute la colonne 'code' (clé Firebase) à la table products existante.
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Ex: "sac-croco-noir" — correspond à l'ID du document Firestore
            $table->string('code')->unique()->nullable()->after('id');
        });
    }

    /**
     * Retire la colonne 'code' si on annule la migration.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('code');
        });
    }
};
