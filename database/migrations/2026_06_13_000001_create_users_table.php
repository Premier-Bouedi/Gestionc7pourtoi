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
        Schema::create('users', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->string('phone_whatsapp')->nullable();
            $table->string('business_name')->nullable();
            $table->string('activity_sector')->nullable();
            $table->string('currency')->default('MAD');
            $table->decimal('tva_rate', 5, 2)->default(20.00);
            $table->string('telegram_chat_id')->nullable();
            $table->json('ai_config')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
