<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Goal;
use Illuminate\Http\Request;

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
        ]);

        $goal->increment('current_amount', $data['amount']);

        if ($goal->current_amount >= $goal->target_amount) {
            $goal->update(['is_completed' => true]);
        }

        return response()->json($goal->fresh());
    }
}
