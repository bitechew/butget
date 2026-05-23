<?php

namespace App\Services;

use App\Models\Transaction;
use Illuminate\Support\Facades\Log;

class RecurringTransactionService
{
    private TransactionService $transactionService;

    public function __construct(TransactionService $transactionService)
    {
        $this->transactionService = $transactionService;
    }

    /**
     * Generate all due recurring transactions
     */
    public function generateRecurringTransactions(): int
    {
        $parentTransactions = Transaction::where('is_recurring', true)
            ->whereNull('recurring_parent_id')
            ->get();

        $count = 0;
        foreach ($parentTransactions as $transaction) {
            if ($this->generateNextOccurrence($transaction)) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * Generate next occurrence of a recurring transaction
     */
    public function generateNextOccurrence(Transaction $transaction): bool
    {
        try {
            // Find last child transaction
            $lastChild = Transaction::where('recurring_parent_id', $transaction->id)
                ->latest('date')
                ->first();

            $nextDate = $this->calculateNextDate(
                $lastChild ? $lastChild->date : $transaction->date,
                $transaction->recurrence_interval
            );

            // Check if next date is in the past or today
            if ($nextDate > now()->toDateString()) {
                return false;
            }

            // Create new transaction based on type
            $data = [
                'category' => $transaction->category,
                'amount' => $transaction->amount,
                'date' => $nextDate,
                'notes' => $transaction->notes,
                'payment_method' => $transaction->payment_method,
                'is_recurring' => false, // Child transactions are not recurring
                'recurring_parent_id' => $transaction->id,
            ];

            if ($transaction->type === 'expense') {
                $data['from_account_id'] = $transaction->from_account_id;
                $this->transactionService->createExpense($transaction->user, $data);
            } elseif ($transaction->type === 'income') {
                $data['to_account_id'] = $transaction->to_account_id;
                $this->transactionService->createIncome($transaction->user, $data);
            } elseif ($transaction->type === 'transfer') {
                $data['from_account_id'] = $transaction->from_account_id;
                $data['to_account_id'] = $transaction->to_account_id;
                $this->transactionService->createTransfer($transaction->user, $data);
            }

            return true;
        } catch (\Exception $e) {
            Log::error('Error generating recurring transaction', [
                'transaction_id' => $transaction->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Calculate next date based on interval
     */
    public function calculateNextDate(string $lastDate, ?string $interval): string
    {
        $date = \Carbon\Carbon::parse($lastDate);

        return match ($interval) {
            'daily' => $date->addDay()->toDateString(),
            'weekly' => $date->addWeek()->toDateString(),
            'monthly' => $date->addMonth()->toDateString(),
            'yearly' => $date->addYear()->toDateString(),
            default => $date->toDateString(),
        };
    }

    /**
     * Get upcoming recurring transactions
     */
    public function getUpcomingRecurring(int $daysAhead = 30)
    {
        $upcomingDate = now()->addDays($daysAhead)->toDateString();

        return Transaction::where('is_recurring', true)
            ->whereNull('recurring_parent_id')
            ->where('date', '<=', $upcomingDate)
            ->orderBy('date')
            ->get();
    }
}
