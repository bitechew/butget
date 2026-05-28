<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Goal;
use App\Services\TransactionService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class GoalController extends Controller
{
    public function index(Request $request)
    {
        return response()->json(
            $request->user()->goals()->orderBy('target_date')->get()
        );
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title'          => 'required|string|max:255',
            'target_amount'  => 'required|numeric|min:0.01',
            'current_amount' => 'nullable|numeric|min:0',
            'target_date'    => 'required|date|after:today',
            'icon'           => 'nullable|string|max:50',
            'color'          => 'nullable|string|max:7',
            'notes'          => 'nullable|string',
        ]);

        $data['user_id'] = $request->user()->id;
        $goal = Goal::create($data);

        return response()->json($goal, 201);
    }

    public function update(Request $request, Goal $goal)
    {
        $this->authorize('update', $goal);

        $data = $request->validate([
            'title'          => 'sometimes|string|max:255',
            'target_amount'  => 'sometimes|numeric|min:0.01',
            'current_amount' => 'sometimes|numeric|min:0',
            'target_date'    => 'sometimes|date',
            'icon'           => 'nullable|string|max:50',
            'color'          => 'nullable|string|max:7',
            'notes'          => 'nullable|string',
            'is_completed'   => 'boolean',
        ]);

        $goal->update($data);
        return response()->json($goal);
    }

    public function destroy(Request $request, Goal $goal)
    {
        $this->authorize('delete', $goal);
        $goal->delete();
        return response()->json(null, 204);
    }

    public function contribute(Request $request, Goal $goal)
    {
        $this->authorize('update', $goal);

        $data = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'account_id' => 'nullable|exists:accounts,id',
            'date' => 'nullable|date',
        ]);

        if (!empty($data['account_id'])) {
            $account = $request->user()->accounts()->find($data['account_id']);
            if (!$account) {
                abort(403);
            }
        }

        $goal->increment('current_amount', $data['amount']);

        if ($goal->current_amount >= $goal->target_amount) {
            $goal->update(['is_completed' => true]);
        }

        // Create a transaction record for this contribution if an account was provided
        if (!empty($data['account_id'])) {
            $txService = app(TransactionService::class);
            $txPayload = [
                'amount' => $data['amount'],
                'from_account_id' => $data['account_id'],
                'date' => $data['date'] ?? Carbon::now()->toDateString(),
                'category' => 'Goal Contribution',
                'notes' => 'Contribution to goal: ' . $goal->title,
            ];
            // Use createExpense which will update account balance atomically
            $txService->createExpense($request->user(), $txPayload);
        }

        return response()->json($goal->fresh());
    }
}