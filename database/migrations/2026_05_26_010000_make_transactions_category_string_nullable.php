<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The original transactions table has a string 'category' column (NOT NULL)
 * from the initial migration, but the system now uses category_id FK.
 * This migration makes the legacy string column nullable so inserts don't fail
 * when only category_id is provided.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            if (Schema::hasColumn('transactions', 'category')) {
                $table->string('category')->nullable()->default(null)->change();
            }
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            if (Schema::hasColumn('transactions', 'category')) {
                $table->string('category')->nullable(false)->change();
            }
        });
    }
};
