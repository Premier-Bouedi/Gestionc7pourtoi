<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Crée la table de lignes de facture (pivot invoice <-> product).
     * Permet au caissier de sélectionner plusieurs produits par facture
     * et de déduire le stock automatiquement à la validation.
     */
    public function up(): void
    {
        Schema::create('invoice_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('invoice_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('product_id')->nullable()->constrained()->nullOnDelete();
            $table->string('description');          // libellé libre (produit ou service)
            $table->unsignedInteger('quantity');
            $table->decimal('unit_price', 12, 2);  // prix unitaire en devise locale
            $table->decimal('subtotal', 12, 2);    // quantity * unit_price
            $table->timestamps();

            $table->index('invoice_id');
            $table->index('product_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_items');
    }
};
