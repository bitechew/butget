<?php

namespace App\Services;

use App\Models\Account;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class TransactionService
{
    /**
     * Create an expense transaction (money out)
     */
    public function createExpense(User $user, array $data): Transaction
    {
        return DB::transaction(function () use ($user, $data) {
            $account = Account::findOrFail($data['from_account_id']);
            
            // Verify account belongs to user
            if ($account->user_id !== $user->id) {
                throw new \Exception('Unauthorized account access');
            }

            // Deduct from account balance
            $account->decrement('balance', $data['amount']);

            // Create transaction record
            $title = $data['title'] ?? ('Expense: ' . ($data['category'] ?? 'Uncategorized'));
            
            $transaction = Transaction::create([
                'user_id' => $user->id,
                'from_account_id' => $account->id,
                'account_id' => $account->id, // Legacy field
                'title' => $title,
                'amount' => $data['amount'],
                'type' => 'expense',
                'category' => $data['category'] ?? 'Uncategorized',
                'date' => $data['date'] ?? now()->toDateString(),
                'notes' => $data['notes'] ?? null,
                'payment_method' => $data['payment_method'] ?? 'cash',
                'is_recurring' => $data['is_recurring'] ?? false,
                'recurrence_interval' => $data['recurrence_interval'] ?? null,
            ]);

            return $transaction;
        });
    }

    /**
     * Create an income transaction (money in)
     */
    public function createIncome(User $user, array $data): Transaction
    {
        return DB::transaction(function () use ($user, $data) {
            $account = Account::findOrFail($data['to_account_id']);
            
            // Verify account belongs to user
            if ($account->user_id !== $user->id) {
                throw new \Exception('Unauthorized account access');
            }

            // Add to account balance
            $account->increment('balance', $data['amount']);

            // Create transaction record
            $title = $data['title'] ?? ('Income: ' . ($data['category'] ?? 'Income'));
            
            $transaction = Transaction::create([
                'user_id' => $user->id,
                'to_account_id' => $account->id,
                'account_id' => $account->id, // Legacy field
                'title' => $title,
                'amount' => $data['amount'],
                'type' => 'income',
                'category' => $data['category'] ?? 'Income',
                'date' => $data['date'] ?? now()->toDateString(),
                'notes' => $data['notes'] ?? null,
                'payment_method' => $data['payment_method'] ?? 'transfer',
                'is_recurring' => $data['is_recurring'] ?? false,
                'recurrence_interval' => $data['recurrence_interval'] ?? null,
            ]);

            return $transaction;
        });
    }

    /**
     * Create a transfer transaction (move between accounts)
     */
    public function createTransfer(User $user, array $data): Transaction
    {
        return DB::transaction(function () use ($user, $data) {
            $fromAccount = Account::findOrFail($data['from_account_id']);
            $toAccount = Account::findOrFail($data['to_account_id']);

            // Verify both accounts belong to user
            if ($fromAccount->user_id !== $user->id || $toAccount->user_id !== $user->id) {
                throw new \Exception('Unauthorized account access');
            }

            // Move money between accounts
            $fromAccount->decrement('balance', $data['amount']);
            $toAccount->increment('balance', $data['amount']);

            // Create transaction record
            $title = $data['title'] ?? ("Transfer from {$fromAccount->name} to {$toAccount->name}");
            
            $transaction = Transaction::create([
                'user_id' => $user->id,
                'from_account_id' => $fromAccount->id,
                'to_account_id' => $toAccount->id,
                'account_id' => $fromAccount->id, // Legacy field (source account)
                'title' => $title,
                'amount' => $data['amount'],
                'type' => 'transfer',
                'category' => 'Transfer',
                'date' => $data['date'] ?? now()->toDateString(),
                'notes' => $data['notes'] ?? null,
                'payment_method' => 'transfer',
                'is_recurring' => $data['is_recurring'] ?? false,
                'recurrence_interval' => $data['recurrence_interval'] ?? null,
            ]);

            return $transaction;
        });
    }

    /**
     * Delete a transaction and reverse its effects
     */
    public function deleteTransaction(Transaction $transaction): bool
    {
        return DB::transaction(function () use ($transaction) {
            if ($transaction->type === 'expense') {
                // Refund the amount back to the account
                $account = Account::find($transaction->from_account_id);
                if ($account) {
                    $account->increment('balance', $transaction->amount);
                }
            } elseif ($transaction->type === 'income') {
                // Deduct the amount from the account
                $account = Account::find($transaction->to_account_id);
                if ($account) {
                    $account->decrement('balance', $transaction->amount);
                }
            } elseif ($transaction->type === 'transfer') {
                // Reverse the transfer
                $fromAccount = Account::find($transaction->from_account_id);
                $toAccount = Account::find($transaction->to_account_id);
                if ($fromAccount && $toAccount) {
                    $fromAccount->increment('balance', $transaction->amount);
                    $toAccount->decrement('balance', $transaction->amount);
                }
            }

            return $transaction->delete();
        });
    }

    /**
     * Update a transaction (delete and recreate)
     */
    public function updateTransaction(Transaction $transaction, array $data): Transaction
    {
        $this->deleteTransaction($transaction);
        
        // Determine transaction type and create appropriate type
        $type = $data['type'] ?? $transaction->type;
        
        if ($type === 'expense') {
            return $this->createExpense($transaction->user, $data);
        } elseif ($type === 'income') {
            return $this->createIncome($transaction->user, $data);
        } elseif ($type === 'transfer') {
            return $this->createTransfer($transaction->user, $data);
        }

        throw new \Exception('Invalid transaction type');
    }

    /**
     * Get user's transactions with optional filters
     */
    public function getUserTransactions(User $user, array $filters = [])
    {
        $query = Transaction::where('user_id', $user->id);

        if (!empty($filters['account_id'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('account_id', $filters['account_id'])
                  ->orWhere('from_account_id', $filters['account_id'])
                  ->orWhere('to_account_id', $filters['account_id']);
            });
        }

        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (!empty($filters['category'])) {
            $query->where('category', $filters['category']);
        }

        if (!empty($filters['start_date']) && !empty($filters['end_date'])) {
            $query->whereBetween('date', [$filters['start_date'], $filters['end_date']]);
        }

        if (!empty($filters['search'])) {
            $query->where('title', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('notes', 'like', '%' . $filters['search'] . '%');
        }

        return $query->orderBy('date', 'desc')->paginate($filters['per_page'] ?? 20);
    }
}
