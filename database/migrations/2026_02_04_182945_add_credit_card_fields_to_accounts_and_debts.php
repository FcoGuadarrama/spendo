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
            $table->unsignedTinyInteger('closing_day')->nullable()->after('icon');
            $table->unsignedTinyInteger('due_day')->nullable()->after('closing_day');
        });

        Schema::table('debts', function (Blueprint $table) {
            $table->string('type')->default('loan')->after('name'); // 'loan', 'credit_card'
            $table->foreignId('account_id')->nullable()->after('user_id')->constrained()->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('accounts', function (Blueprint $table) {
            $table->dropColumn(['closing_day', 'due_day']);
        });

        Schema::table('debts', function (Blueprint $table) {
            $table->dropForeign(['account_id']);
            $table->dropColumn(['type', 'account_id']);
        });
    }
};
