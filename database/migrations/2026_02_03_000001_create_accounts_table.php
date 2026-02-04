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
        Schema::create('accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name'); // "Banco Santander", "Efectivo", "Tarjeta Crédito"
            $table->enum('type', ['checking', 'savings', 'credit_card', 'cash', 'investment'])->default('checking');
            $table->decimal('balance', 15, 2)->default(0); // Saldo actual
            $table->decimal('credit_limit', 15, 2)->nullable(); // Solo para tarjetas de crédito
            $table->string('currency', 3)->default('MXN');
            $table->string('color', 7)->nullable(); // Color hex para UI
            $table->string('icon')->nullable(); // Icono para UI
            $table->boolean('is_active')->default(true);
            $table->boolean('include_in_total')->default(true); // Incluir en balance total
            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('accounts');
    }
};
