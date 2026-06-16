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
        Schema::create('messages', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('sender_id')->constrained('users')->cascadeOnDelete();
            $table->foreignUuid('receiver_id')->nullable()->constrained('users')->cascadeOnDelete();
            $table->text('content');
            $table->boolean('is_read')->default(false);
            $table->timestamps();

            $table->index('sender_id');
            $table->index('receiver_id');
            $table->index('is_read');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};
