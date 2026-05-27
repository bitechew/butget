<?php

namespace App\Services;

use App\Models\Transaction;
use App\Models\Account;
use App\Models\Category;
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

    // Convenience wrappers for legacy/recurring callers
    public function createExpense($userOrId, array $data): Transaction
    {
        $data = $this->normalizeData($userOrId, $data, 'expense');
        return $this->createTransaction($data);
    }

    public function createIncome($userOrId, array $data): Transaction
    {
        $data = $this->normalizeData($userOrId, $data, 'income');
        return $this->createTransaction($data);
    }

    public function createTransfer($userOrId, array $data): Transaction
    {
        $data = $this->normalizeData($userOrId, $data, 'transfer');
        return $this->createTransaction($data);
    }

    /**
     * Normalize incoming payloads from different callers (recurring service, etc.)
     */
    protected function normalizeData($userOrId, array $data, string $type): array
    {
        // Ensure user_id
        if (is_object($userOrId) && isset($userOrId->id)) {
            $data['user_id'] = $userOrId->id;
        } elseif (is_int($userOrId)) {
            $data['user_id'] = $userOrId;
        }

        // Normalize category key (some code passes 'category' as relation)
        if (isset($data['category']) && !isset($data['category_id'])) {
            // If category is a relation / array / object with id
            if (is_array($data['category']) && isset($data['category']['id'])) {
                $data['category_id'] = $data['category']['id'];
            } elseif (is_object($data['category']) && isset($data['category']->id)) {
                $data['category_id'] = $data['category']->id;
            } else {
                // If it's a numeric string or integer, cast it
                if (is_numeric($data['category'])) {
                    $data['category_id'] = (int) $data['category'];
                } else {
                    // It's a name string: find or create category for the user
                    $catName = trim((string) $data['category']);
                    $catType = $type === 'expense' ? 'expense' : ($type === 'income' ? 'income' : ($data['type'] ?? 'expense'));
                    $category = Category::firstOrCreate([
                        'user_id' => $data['user_id'] ?? null,
                        'name' => $catName,
                        'type' => $catType,
                    ], [
                        'color' => '#2dd4bf',
                        'icon' => '🏷️',
                    ]);
                    $data['category_id'] = $category->id;
                }
            }
            unset($data['category']);
        }

        // Ensure type is set
        $data['type'] = $type;

        // Ensure description is present (some callers provide only notes or category)
        if (empty($data['description'])) {
            if (!empty($data['notes'])) {
                $data['description'] = substr((string) $data['notes'], 0, 255);
            } elseif (!empty($data['category_id'])) {
                try {
                    $cat = Category::find($data['category_id']);
                    $data['description'] = $cat ? $cat->name : ucfirst($type);
                } catch (\Exception $e) {
                    $data['description'] = ucfirst($type);
                }
            } else {
                $data['description'] = ucfirst($type);
            }
        }

        // If we have a category_id, ensure legacy 'category' column is populated with the name
        if (!empty($data['category_id']) && empty($data['category'])) {
            try {
                $c = Category::find($data['category_id']);
                if ($c) $data['category'] = $c->name;
            } catch (\Exception $e) {
                // ignore
            }
        }

        // Ensure legacy 'category' string is never empty (DB is strict)
        if (empty($data['category'])) {
            $data['category'] = $data['description'] ?? ucfirst($type);
        }

        return $data;
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