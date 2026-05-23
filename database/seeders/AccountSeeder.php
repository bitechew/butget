<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Account;
use Illuminate\Support\Facades\DB;

class AccountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Cari user pertama, atau buat baru jika database masih kosong
        $user = User::first();
        if (!$user) {
            $user = User::factory()->create([
                'name' => 'Test User',
                'email' => 'test@example.com',
                'password' => 'password',
            ]);
        }

        // 2. Cek apakah user ini sudah punya akun agar tidak terjadi duplikat data
        if (Account::where('user_id', $user->id)->count() > 0) {
            $this->command->info('Accounts already exist for the user. Seeding skipped.');
            return;
        }

        // 3. Masukkan data default dompet (Wallet) dan rekening bank (Bank Account)
        DB::table('accounts')->insert([
            [
                'user_id' => $user->id,
                'name' => 'Wallet',
                'type' => 'cash',
                'balance' => 500000.00,
                'color' => '#4CAF50',
                'icon' => 'account_balance_wallet',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => $user->id,
                'name' => 'Bank Account',
                'type' => 'checking',
                'balance' => 2500000.00,
                'color' => '#2196F3',
                'icon' => 'account_balance',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}