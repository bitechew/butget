<?php

namespace App\Services;

use App\Models\Transaction;
use App\Models\Account;
use Illuminate\Support\Facades\DB;

class TransactionService
{
    public function createTransaction(array $data): Transaction
    {
        return DB::transaction(function () use ($data) {
            $transaction = Transaction::create($data);

            if (($transaction->type === 'expense' || $transaction->type === 'transfer') && $transaction->from_account_id) {
                $this->updateAccountBalance($transaction->from_account_id, -$transaction->amount);
            }

            if (($transaction->type === 'income' || $transaction->type === 'transfer') && $transaction->to_account_id) {
                $this->updateAccountBalance($transaction->to_account_id, $transaction->amount);
            }

            return $transaction;
        });
    }

    public function updateTransaction(Transaction $transaction, array $data): Transaction
    {
        return DB::transaction(function () use ($transaction, $data) {
            // Revert old transaction
            if (($transaction->type === 'expense' || $transaction->type === 'transfer') && $transaction->from_account_id) {
                $this->updateAccountBalance($transaction->from_account_id, $transaction->amount);
            }

            if (($transaction->type === 'income' || $transaction->type === 'transfer') && $transaction->to_account_id) {
                $this->updateAccountBalance($transaction->to_account_id, -$transaction->amount);
            }

            // Update transaction
            $transaction->update($data);

            // Get the freshly updated transaction data
            $transaction->refresh();

            // Apply new transaction
            if (($transaction->type === 'expense' || $transaction->type === 'transfer') && $transaction->from_account_id) {
                $this->updateAccountBalance($transaction->from_account_id, -$transaction->amount);
            }

            if (($transaction->type === 'income' || $transaction->type === 'transfer') && $transaction->to_account_id) {
                $this->updateAccountBalance($transaction->to_account_id, $transaction->amount);
            }

            return $transaction;
        });
    }

    public function deleteTransaction(Transaction $transaction): void
    {
        DB::transaction(function () use ($transaction) {
            if (($transaction->type === 'expense' || $transaction->type === 'transfer') && $transaction->from_account_id) {
                $this->updateAccountBalance($transaction->from_account_id, $transaction->amount);
            }

            if (($transaction->type === 'income' || $transaction->type === 'transfer') && $transaction->to_account_id) {
                $this->updateAccountBalance($transaction->to_account_id, -$transaction->amount);
            }

            $transaction->delete();
        });
    }

    protected function updateAccountBalance(int $accountId, float $amount): void
    {
        // Use a pessimistic lock to prevent race conditions when updating the balance.
        $account = Account::where('id', $accountId)->lockForUpdate()->firstOrFail();
        $account->balance += $amount;
        $account->save();
    }
}