<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $query = $request->user()->transactions()->with('account');

        if ($search = $request->search) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('category', 'like', "%{$search}%")
                  ->orWhere('notes', 'like', "%{$search}%");
            });
        }

        if ($type = $request->type) {
            $query->where('type', $type);
        }

        if ($category = $request->category) {
            $query->where('category', $category);
        }

        if ($from = $request->date_from) {
            $query->whereDate('date', '>=', $from);
        }

        if ($to = $request->date_to) {
            $query->whereDate('date', '<=', $to);
        }

        $sortField = $request->get('sort', 'date');
        $sortDir   = $request->get('direction', 'desc');
        $allowedSorts = ['date', 'amount', 'title', 'category'];
        if (in_array($sortField, $allowedSorts)) {
            $query->orderBy($sortField, $sortDir === 'asc' ? 'asc' : 'desc');
        }

        $perPage = (int) $request->get('per_page', 15);
        return $query->paginate(min($perPage, 100));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title'               => 'required|string|max:255',
            'amount'              => 'required|numeric|min:0.01',
            'type'                => 'required|in:income,expense,transfer',
            'category'            => 'required|string|max:100',
            'date'                => 'required|date',
            'notes'               => 'nullable|string',
            'payment_method'      => 'nullable|string|max:100',
            'account_id'          => 'nullable|exists:accounts,id',
            'is_recurring'        => 'boolean',
            'recurrence_interval' => 'nullable|in:daily,weekly,monthly,yearly',
        ]);

        $data['user_id'] = $request->user()->id;
        $transaction = Transaction::create($data);

        return response()->json($transaction->load('account'), 201);
    }

    public function show(Request $request, Transaction $transaction)
    {
        $this->authorize('view', $transaction);
        return response()->json($transaction->load('account'));
    }

    public function update(Request $request, Transaction $transaction)
    {
        $this->authorize('update', $transaction);

        $data = $request->validate([
            'title'               => 'sometimes|string|max:255',
            'amount'              => 'sometimes|numeric|min:0.01',
            'type'                => 'sometimes|in:income,expense,transfer',
            'category'            => 'sometimes|string|max:100',
            'date'                => 'sometimes|date',
            'notes'               => 'nullable|string',
            'payment_method'      => 'nullable|string|max:100',
            'account_id'          => 'nullable|exists:accounts,id',
            'is_recurring'        => 'boolean',
            'recurrence_interval' => 'nullable|in:daily,weekly,monthly,yearly',
        ]);

        $transaction->update($data);
        return response()->json($transaction->load('account'));
    }

    public function destroy(Request $request, Transaction $transaction)
    {
        $this->authorize('delete', $transaction);
        $transaction->delete();
        return response()->json(null, 204);
    }

    public function categories(Request $request)
    {
        $categories = $request->user()->transactions()
            ->select('category')
            ->distinct()
            ->pluck('category')
            ->sort()
            ->values();

        return response()->json($categories);
    }
}
