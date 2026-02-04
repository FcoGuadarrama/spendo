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
        Schema::table('accounts', function (Blueprint $table) {
            // Add closing_day if not exists (the day the billing cycle closes)
            if (!Schema::hasColumn('accounts', 'closing_day')) {
                $table->integer('closing_day')->nullable()->comment('Day of month when billing cycle closes (e.g., 10th)');
            }

            // Add statement_balance to track what needs to be paid
            if (!Schema::hasColumn('accounts', 'statement_balance')) {
                $table->decimal('statement_balance', 15, 2)->default(0)->comment('Total amount due on the account (for credit cards)');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('accounts', function (Blueprint $table) {
            if (Schema::hasColumn('accounts', 'closing_day')) {
                $table->dropColumn('closing_day');
            }
            if (Schema::hasColumn('accounts', 'statement_balance')) {
                $table->dropColumn('statement_balance');
            }
        });
    }
};
