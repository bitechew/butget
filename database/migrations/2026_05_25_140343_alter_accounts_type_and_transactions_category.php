<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('accounts', function (Blueprint $table) {
            // First we drop the enum column (might be tricky in some DBs, usually ok with string)
            // But since this is local testing we can use doctrine/dbal or string change
            $table->string('type')->change();
            $table->string('description')->nullable()->after('color');
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->foreignId('category_id')->nullable()->after('type')->constrained('categories')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('accounts', function (Blueprint $table) {
            $table->enum('type', ['cash', 'checking', 'savings', 'investment', 'credit', 'loan', 'other'])->change();
            $table->dropColumn('description');
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
            $table->dropColumn('category_id');
        });
    }
};
