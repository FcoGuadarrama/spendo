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
        Schema::create('debts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->decimal('total_amount', 15, 2);
            $table->decimal('remaining_amount', 15, 2);
            $table->decimal('monthly_payment', 15, 2)->nullable(); // Might be flexible
            $table->date('start_date');
            $table->date('end_date')->nullable(); // Estimated finish date
            $table->integer('due_day')->nullable(); // Day of month to pay
            $table->integer('total_installments')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('closed_at')->nullable(); // When debt is fully paid
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['user_id', 'remaining_amount']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('debts');
    }
};
