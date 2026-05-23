<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Memanggil AccountSeeder agar otomatis dijalankan
        $this->call([
            AccountSeeder::class,
        ]);
    }
}