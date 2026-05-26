<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Account;
use Illuminate\Http\Request;

class AccountController extends Controller
{
    public function index(Request $request)
    {
        $query = $request->user()->accounts();
        
        if ($request->has('include_inactive')) {
            // return all accounts
        } else if ($request->has('inactive_only')) {
            $query->where('is_active', false);
        } else {
            $query->where('is_active', true);
        }

        return response()->json($query->get());
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'     => 'required|string|max:255',
            'type'     => 'required|in:cash,checking,savings,investment,credit,loan,other',
            'balance'  => 'required|numeric',
            'currency' => 'nullable|string|size:3',
            'color'    => 'nullable|string|max:7',
            'icon'     => 'nullable|string|max:50',
        ]);

        $data['user_id'] = $request->user()->id;
        $account = Account::create($data);

        return response()->json($account, 201);
    }

    public function update(Request $request, Account $account)
    {
        $this->authorize('update', $account);

        $data = $request->validate([
            'name'      => 'sometimes|string|max:255',
            'type'      => 'sometimes|in:cash,checking,savings,investment,credit,loan,other',
            'balance'   => 'sometimes|numeric',
            'currency'  => 'nullable|string|size:3',
            'color'     => 'nullable|string|max:7',
            'icon'      => 'nullable|string|max:50',
            'is_active' => 'boolean',
        ]);

        $account->update($data);
        return response()->json($account);
    }

    public function destroy(Request $request, Account $account)
    {
        $this->authorize('delete', $account);
        $account->update(['is_active' => false]);
        return response()->json(null, 204);
    }

    public function restore(Request $request, Account $account)
    {
        $this->authorize('update', $account);
        $account->update(['is_active' => true]);
        return response()->json($account);
    }
}
