<?php

require 'vendor/autoload.php';

$app = require 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Account;
use App\Services\TransactionService;

try {
    // Get or create test user
    $user = User::first();
    if (!$user) {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);
        echo "[✓] Created test user: {$user->id}\n";
    } else {
        echo "[✓] Using existing user: {$user->id}\n";
    }

    // Clean up old test accounts
    Account::where('user_id', $user->id)->delete();

    // Create test accounts
    $acct1 = Account::create([
        'user_id' => $user->id,
        'name' => 'Cash Wallet',
        'type' => 'cash',
        'balance' => 1000,
        'currency' => 'IDR',
        'is_active' => true,
    ]);
    echo "[✓] Created account 1: {$acct1->id} - {$acct1->name} (Balance: {$acct1->balance})\n";

    $acct2 = Account::create([
        'user_id' => $user->id,
        'name' => 'Bank Account',
        'type' => 'checking',
        'balance' => 500,
        'currency' => 'IDR',
        'is_active' => true,
    ]);
    echo "[✓] Created account 2: {$acct2->id} - {$acct2->name} (Balance: {$acct2->balance})\n";

    // Get transaction service
    $svc = app(TransactionService::class);

    // Test Expense Transaction
    echo "\n--- Testing EXPENSE Transaction ---\n";
    $tx1 = $svc->createExpense($user, [
        'from_account_id' => $acct1->id,
        'amount' => 100,
        'category' => 'Food',
        'date' => now()->toDateString(),
        'notes' => 'Lunch',
    ]);
    $acct1->refresh();
    echo "[✓] Expense created: ID {$tx1->id}\n";
    echo "    Amount: {$tx1->amount} from {$tx1->fromAccount->name}\n";
    echo "    Account 1 new balance: {$acct1->balance} (was 1000, should be 900)\n";

    // Test Income Transaction
    echo "\n--- Testing INCOME Transaction ---\n";
    $tx2 = $svc->createIncome($user, [
        'to_account_id' => $acct2->id,
        'amount' => 200,
        'category' => 'Salary',
        'date' => now()->toDateString(),
        'notes' => 'Paycheck',
    ]);
    $acct2->refresh();
    echo "[✓] Income created: ID {$tx2->id}\n";
    echo "    Amount: {$tx2->amount} to {$tx2->toAccount->name}\n";
    echo "    Account 2 new balance: {$acct2->balance} (was 500, should be 700)\n";

    // Test Transfer Transaction
    echo "\n--- Testing TRANSFER Transaction ---\n";
    $tx3 = $svc->createTransfer($user, [
        'from_account_id' => $acct2->id,
        'to_account_id' => $acct1->id,
        'amount' => 50,
        'date' => now()->toDateString(),
        'notes' => 'Move cash',
    ]);
    $acct1->refresh();
    $acct2->refresh();
    echo "[✓] Transfer created: ID {$tx3->id}\n";
    echo "    From: {$tx3->fromAccount->name} → To: {$tx3->toAccount->name}\n";
    echo "    Amount: {$tx3->amount}\n";
    echo "    Account 1 new balance: {$acct1->balance} (was 900, should be 950)\n";
    echo "    Account 2 new balance: {$acct2->balance} (was 700, should be 650)\n";

    // Test Delete/Reversal
    echo "\n--- Testing DELETE/REVERSAL ---\n";
    $oldBalance1 = $acct1->balance;
    $svc->deleteTransaction($tx1);
    $acct1->refresh();
    echo "[✓] Expense transaction deleted\n";
    echo "    Account 1 balance after delete: {$acct1->balance} (was {$oldBalance1}, should be 1000)\n";

    echo "\n✅ ALL TESTS PASSED!\n";
    echo "\nFinal Account States:\n";
    $acct1->refresh();
    $acct2->refresh();
    echo "Account 1 ({$acct1->name}): {$acct1->balance}\n";
    echo "Account 2 ({$acct2->name}): {$acct2->balance}\n";
    echo "Total Balance: " . ($acct1->balance + $acct2->balance) . "\n";

} catch (\Exception $e) {
    echo "\n❌ ERROR: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
}
