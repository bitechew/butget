<?php

namespace App\Services;

use App\Models\Budget;
use App\Models\User;
use App\Models\Account;
use Illuminate\Support\Facades\DB;

class BudgetService
{
    /**
     * Create or update a budget
     */
    public function createOrUpdateBudget(User $user, Account $account, array $data): Budget
    {
        return DB::transaction(function () use ($user, $account, $data) {
            $budget = Budget::firstOrCreate(
                [
                    'user_id' => $user->id,
                    'account_id' => $account->id,
                    'category' => $data['category'],
                ],
                [
                    'limit_amount' => $data['limit_amount'],
                    'period' => $data['period'] ?? 'monthly',
                    'alert_threshold' => $data['alert_threshold'] ?? 80,
                ]
            );

            // Update if already exists
            if ($budget->wasRecentlyCreated === false) {
                $budget->update([
                    'limit_amount' => $data['limit_amount'],
                    'period' => $data['period'] ?? $budget->period,
                    'alert_threshold' => $data['alert_threshold'] ?? $budget->alert_threshold,
                ]);
            }

            return $budget;
        });
    }

    /**
     * Check if budget is exceeded
     */
    public function isBudgetExceeded(Budget $budget): bool
    {
        $spent = $this->getCategorySpent($budget->user, $budget->account, $budget->category, $budget->period);
        return $spent >= $budget->limit_amount;
    }

    /**
     * Get spending for a category in a period
     */
    public function getCategorySpent(User $user, Account $account, string $category, string $period): float
    {
        $query = DB::table('transactions')
            ->where('user_id', $user->id)
            ->where('type', 'expense')
            ->where('category', $category);

        if ($account) {
            $query->where('account_id', $account->id);
        }

        // Add period filter
        $startDate = $this->getPeriodStartDate($period);
        $query->where('date', '>=', $startDate);

        return $query->sum('amount') ?? 0;
    }

    /**
     * Get period start date
     */
    private function getPeriodStartDate(string $period): string
    {
        return match ($period) {
            'daily' => now()->toDateString(),
            'weekly' => now()->startOfWeek()->toDateString(),
            'monthly' => now()->startOfMonth()->toDateString(),
            'yearly' => now()->startOfYear()->toDateString(),
            default => now()->startOfMonth()->toDateString(),
        };
    }

    /**
     * Get all budgets for user
     */
    public function getUserBudgets(User $user)
    {
        return Budget::where('user_id', $user->id)->get();
    }

    /**
     * Delete budget
     */
    public function deleteBudget(Budget $budget): bool
    {
        return $budget->delete();
    }
}
