<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('expenses', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->decimal('amount', 12, 2);
            $table->enum('category', ['hosting', 'tools', 'transport', 'office', 'communication', 'other'])->default('other');
            $table->date('expense_date');
            $table->string('receipt_image_path')->nullable();
            $table->text('ai_analysis_raw')->nullable();
            $table->boolean('is_deductible')->default(true);
            $table->timestamps();

            $table->index('user_id');
            $table->index('category');
            $table->index('expense_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};
