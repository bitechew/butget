<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $query = $request->user()->categories();
        
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
            'color' => 'nullable|string|max:7',
            'type' => 'required|in:income,expense',
            'budget_limit' => 'nullable|numeric',
            'description' => 'nullable|string',
        ]);

        $data['user_id'] = $request->user()->id;
        $category = Category::create($data);

        return response()->json($category, 201);
    }

    public function update(Request $request, Category $category)
    {
        if ($category->user_id !== $request->user()->id) {
            abort(403);
        }

        $data = $request->validate([
            'name' => 'sometimes|string|max:255',
            'icon' => 'nullable|string|max:50',
            'color' => 'nullable|string|max:7',
            'type' => 'sometimes|in:income,expense',
            'budget_limit' => 'nullable|numeric',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $category->update($data);
        return response()->json($category);
    }

    public function destroy(Request $request, Category $category)
    {
        if ($category->user_id !== $request->user()->id) {
            abort(403);
        }
        $category->update(['is_active' => false]);
        return response()->json(null, 204);
    }
}
