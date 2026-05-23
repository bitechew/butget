<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user  = $request->user();
        $now   = Carbon::now();
        $month = $now->month;
        $year  = $now->year;
        $prevMonth = $now->copy()->subMonth();

        // Current month stats
        $income = (float) $user->transactions()
            ->where('type', 'income')
            ->whereMonth('date', $month)->whereYear('date', $year)
            ->sum('amount');

        $expenses = (float) $user->transactions()
            ->where('type', 'expense')
            ->whereMonth('date', $month)->whereYear('date', $year)
            ->sum('amount');

        // Previous month
        $prevIncome = (float) $user->transactions()
            ->where('type', 'income')
            ->whereMonth('date', $prevMonth->month)->whereYear('date', $prevMonth->year)
            ->sum('amount');

        $prevExpenses = (float) $user->transactions()
            ->where('type', 'expense')
            ->whereMonth('date', $prevMonth->month)->whereYear('date', $prevMonth->year)
            ->sum('amount');

        // Total balance from accounts
        $totalBalance = (float) $user->accounts()->where('is_active', true)->sum('balance');

        // All accounts
        $accounts = $user->accounts()->where('is_active', true)->orderBy('name')->get();

        // Net worth = assets - liabilities
        $assets = (float) $user->accounts()
            ->whereIn('type', ['cash', 'checking', 'savings', 'investment'])
            ->where('is_active', true)->sum('balance');

        $liabilities = (float) $user->accounts()
            ->whereIn('type', ['credit', 'loan'])
            ->where('is_active', true)->sum('balance');

        $netWorth = $assets - abs($liabilities);

        // Savings rate
        $savingsRate = $income > 0 ? round((($income - $expenses) / $income) * 100, 1) : 0;

        // Cash flow
        $cashFlow = $income - $expenses;

        // 12-month balance history
        $history = [];
        for ($i = 11; $i >= 0; $i--) {
            $dt = $now->copy()->subMonths($i);
            $mIncome = (float) $user->transactions()
                ->where('type', 'income')
                ->whereMonth('date', $dt->month)->whereYear('date', $dt->year)
                ->sum('amount');
            $mExpenses = (float) $user->transactions()
                ->where('type', 'expense')
                ->whereMonth('date', $dt->month)->whereYear('date', $dt->year)
                ->sum('amount');
            $history[] = [
                'name'     => $dt->format('M'),
                'income'   => $mIncome,
                'expenses' => $mExpenses,
                'net'      => $mIncome - $mExpenses,
            ];
        }

        // Category spending (current month)
        $categorySpending = $user->transactions()
            ->where('type', 'expense')
            ->whereMonth('date', $month)->whereYear('date', $year)
            ->selectRaw('category, SUM(amount) as total')
            ->groupBy('category')
            ->orderByDesc('total')
            ->get()
            ->map(fn($row) => ['name' => $row->category, 'value' => (float) $row->total]);

        // Recent transactions
        $recentTransactions = $user->transactions()
            ->with(['fromAccount', 'toAccount'])
            ->orderByDesc('date')
            ->orderByDesc('created_at')
            ->limit(7)
            ->get();

        // Insights
        $insights = $this->generateInsights($user, $month, $year, $prevMonth, $income, $expenses, $prevIncome, $prevExpenses);

        return response()->json([
            'total_balance'       => $totalBalance,
            'net_worth'           => $netWorth,
            'total_income'        => $income,
            'total_expenses'      => $expenses,
            'accounts'            => $accounts,
            'history'             => $history,
            'category_spending'   => $categorySpending,
            'recent_transactions' => $recentTransactions,
            'insights'            => $insights,
        ]);
    }

    private function generateInsights($user, $month, $year, $prevMonth, $income, $expenses, $prevIncome, $prevExpenses): array
    {
        $insights = [];

        if ($prevExpenses > 0 && $expenses > $prevExpenses) {
            $pct = round((($expenses - $prevExpenses) / $prevExpenses) * 100, 1);
            $insights[] = ['type' => 'warning', 'message' => "Your spending is up {$pct}% compared to last month."];
        }

        if ($income > 0 && ($income - $expenses) / $income > 0.2) {
            $insights[] = ['type' => 'positive', 'message' => "Great job! You're saving " . round((($income - $expenses) / $income) * 100, 1) . "% of your income."];
        }

        // Top spending category
        $topCat = $user->transactions()
            ->where('type', 'expense')
            ->whereMonth('date', $month)->whereYear('date', $year)
            ->selectRaw('category, SUM(amount) as total')
            ->groupBy('category')
            ->orderByDesc('total')
            ->first();

        if ($topCat) {
            $insights[] = ['type' => 'info', 'message' => "Your top spending category this month is {$topCat->category} (" . number_format($topCat->total, 0, ',', '.') . ")."];
        }

        return $insights;
    }
}