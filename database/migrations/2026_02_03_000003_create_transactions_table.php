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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('account_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('transfer_to_account_id')->nullable()->constrained('accounts')->nullOnDelete();
            
            $table->enum('type', ['income', 'expense', 'transfer'])->default('expense');
            $table->decimal('amount', 15, 2); // Siempre positivo
            $table->string('description')->nullable();
            $table->text('notes')->nullable();
            $table->date('date'); // Fecha de la transacción
            $table->time('time')->nullable(); // Hora opcional
            
            // Para transacciones recurrentes
            $table->boolean('is_recurring')->default(false);
            $table->enum('recurring_frequency', ['daily', 'weekly', 'biweekly', 'monthly', 'yearly'])->nullable();
            $table->date('recurring_end_date')->nullable();
            
            // Metadata
            $table->boolean('is_confirmed')->default(true); // Para transacciones pendientes
            $table->string('reference')->nullable(); // Referencia bancaria
            $table->json('tags')->nullable(); // Tags adicionales
            
            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'date']);
            $table->index(['user_id', 'type', 'date']);
            $table->index(['account_id', 'date']);
            $table->index(['category_id', 'date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
