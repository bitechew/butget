<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('butgets', function (Blueprint $table) {
            $table->id();
            $table->integer('monthly_income');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('butgets');
    }
};

