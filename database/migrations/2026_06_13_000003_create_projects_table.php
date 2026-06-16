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
        Schema::create('projects', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('client_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->text('description');
            $table->enum('status', ['draft', 'active', 'completed', 'paused'])->default('draft');
            $table->decimal('budget', 12, 2)->default(0);
            $table->decimal('hourly_rate', 10, 2)->nullable();
            $table->date('start_date');
            $table->date('deadline');
            $table->string('google_calendar_event_id')->nullable();
            $table->timestamps();

            $table->index('user_id');
            $table->index('client_id');
            $table->index('status');
            $table->index('deadline');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
