<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            // Add missing foreign keys for transfer accounts
            if (!Schema::hasColumn('transactions', 'from_account_id')) {
                $table->foreignId('from_account_id')->nullable()->constrained('accounts')->nullOnDelete();
            }
            
            if (!Schema::hasColumn('transactions', 'to_account_id')) {
                $table->foreignId('to_account_id')->nullable()->constrained('accounts')->nullOnDelete();
            }
            
            if (!Schema::hasColumn('transactions', 'recurring_parent_id')) {
                $table->foreignId('recurring_parent_id')->nullable()->constrained('transactions')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            if (Schema::hasColumn('transactions', 'from_account_id')) {
                $table->dropForeignKeyIfExists('transactions_from_account_id_foreign');
                $table->dropColumn('from_account_id');
            }
            
            if (Schema::hasColumn('transactions', 'to_account_id')) {
                $table->dropForeignKeyIfExists('transactions_to_account_id_foreign');
                $table->dropColumn('to_account_id');
            }
            
            if (Schema::hasColumn('transactions', 'recurring_parent_id')) {
                $table->dropForeignKeyIfExists('transactions_recurring_parent_id_foreign');
                $table->dropColumn('recurring_parent_id');
            }
        });
    }
};
