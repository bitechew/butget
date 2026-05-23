<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            
            // From/To accounts for transfers
            $table->foreignId('from_account_id')->nullable()->constrained('accounts')->nullOnDelete();
            $table->foreignId('to_account_id')->nullable()->constrained('accounts')->nullOnDelete();

            $table->string('description');
            $table->decimal('amount', 15, 2);
            $table->enum('type', ['income', 'expense', 'transfer']);
            $table->string('category');
            $table->date('date');
            $table->text('notes')->nullable();

            // Recurring Transactions
            $table->boolean('is_recurring')->default(false);
            $table->enum('recurrence_interval', ['daily', 'weekly', 'monthly', 'yearly'])->nullable();
            $table->foreignId('recurring_parent_id')->nullable()->constrained('transactions')->nullOnDelete();
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};