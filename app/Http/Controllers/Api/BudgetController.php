<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Budget;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class BudgetController extends Controller
{
    public function index(Request $request)
    {
        $month = (int) $request->get('month', now()->month);
        $year  = (int) $request->get('year', now()->year);

        $budgets = $request->user()->budgets()
            ->where('month', $month)
            ->where('year', $year)
            ->get();

        // Attach spent amount for each budget category
        $budgets = $budgets->map(function ($budget) use ($request, $month, $year) {
            $spent = $request->user()->transactions()
                ->where('category', $budget->category)
                ->where('type', 'expense')
                ->whereMonth('date', $month)
                ->whereYear('date', $year)
                ->sum('amount');

            $budget->spent   = (float) $spent;
            $budget->remaining = max(0, (float) $budget->amount - (float) $spent);
            $budget->percentage = $budget->amount > 0
                ? min(100, round(($spent / $budget->amount) * 100, 1))
                : 0;

            return $budget;
        });

        return response()->json($budgets);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'category' => 'required|string|max:100',
            'amount'   => 'required|numeric|min:0.01',
            'month'    => 'required|integer|min:1|max:12',
            'year'     => 'required|integer|min:2020|max:2099',
            'color'    => 'nullable|string|max:7',
            'icon'     => 'nullable|string|max:50',
        ]);

        $data['user_id'] = $request->user()->id;

        $budget = Budget::updateOrCreate(
            ['user_id' => $data['user_id'], 'category' => $data['category'], 'month' => $data['month'], 'year' => $data['year']],
            $data
        );

        return response()->json($budget, 201);
    }

    public function update(Request $request, Budget $budget)
    {
        $this->authorize('update', $budget);

        $data = $request->validate([
            'category' => 'sometimes|string|max:100',
            'amount'   => 'sometimes|numeric|min:0.01',
            'month'    => 'sometimes|integer|min:1|max:12',
            'year'     => 'sometimes|integer|min:2020|max:2099',
            'color'    => 'nullable|string|max:7',
            'icon'     => 'nullable|string|max:50',
        ]);

        $budget->update($data);
        return response()->json($budget);
    }

    public function destroy(Request $request, Budget $budget)
    {
        $this->authorize('delete', $budget);
        $budget->delete();
        return response()->json(null, 204);
    }
}
