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
        Schema::create('budgets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            
            $table->decimal('amount', 15, 2); // Presupuesto mensual
            $table->enum('period', ['weekly', 'monthly', 'yearly'])->default('monthly');
            $table->integer('year');
            $table->integer('month')->nullable(); // null para presupuestos anuales
            
            $table->boolean('is_active')->default(true);
            $table->boolean('notify_at_threshold')->default(true);
            $table->integer('threshold_percentage')->default(80); // Notificar al 80%
            
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['user_id', 'category_id', 'year', 'month']);
            $table->index(['user_id', 'year', 'month']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('budgets');
    }
};
