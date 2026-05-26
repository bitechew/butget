<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PaymentMethod;
use Illuminate\Http\Request;

class PaymentMethodController extends Controller
{
    public function index(Request $request)
    {
        $query = $request->user()->paymentMethods();
        if ($request->has('include_inactive')) {
            // return all
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
            'name' => 'required|string|max:255',
            'icon' => 'nullable|string|max:50',
            'is_default' => 'boolean',
        ]);

        $data['user_id'] = $request->user()->id;
        $pm = PaymentMethod::create($data);

        if (!empty($data['is_default'])) {
            // unset other defaults
            PaymentMethod::where('user_id', $request->user()->id)->where('id', '!=', $pm->id)->update(['is_default' => false]);
        }

        return response()->json($pm, 201);
    }

    public function update(Request $request, PaymentMethod $paymentMethod)
    {
        $this->authorize('update', $paymentMethod);

        $data = $request->validate([
            'name' => 'sometimes|string|max:255',
            'icon' => 'nullable|string|max:50',
            'is_default' => 'boolean',
            'is_active' => 'boolean',
        ]);

        $paymentMethod->update($data);

        if (isset($data['is_default']) && $data['is_default']) {
            PaymentMethod::where('user_id', $request->user()->id)->where('id', '!=', $paymentMethod->id)->update(['is_default' => false]);
        }

        return response()->json($paymentMethod);
    }

    public function destroy(Request $request, PaymentMethod $paymentMethod)
    {
        $this->authorize('delete', $paymentMethod);
        $paymentMethod->update(['is_active' => false]);
        return response()->json(null, 204);
    }
}
