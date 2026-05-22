<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Expense;

class ExpenseController extends Controller
{
    public function create()
    {
        return view('expense.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'expense' => 'required|integer',
            'category' => 'required',
            'description' => 'nullable'
        ]);

        Expense::create($request->all());

        return redirect(route('butgets.index'))->with('success', 'Expense added successfully!');
    }

    public function edit($id)
    {
        $expense = Expense::findOrFail($id);
        return view('expense.edit', compact('expense'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'expense' => 'required|integer',
            'date' => 'required|date',
            'category' => 'required',
            'description' => 'nullable'
        ]);

        Expense::findOrFail($id)->update($request->all());

        return redirect(route('butgets.index'))->with('success', 'Expense updated successfully!');
    }

    public function destroy($id)
    {
        Expense::destroy($id);
        return redirect(route('butgets.index'))->with('success', 'Expense deleted!');
    }

}

