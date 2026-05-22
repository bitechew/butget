<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Butget;
use App\Models\Expense;

class ButgetController extends Controller
{
    public function create()
    {
        return view('butget.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'monthly_income' => 'required|integer'
        ]);

        Butget::create([
            'monthly_income' => $request->monthly_income
        ]);

        return redirect(route('butgets.index'))->with('success', 'Income added successfully!');
    }

    public function index()
    {
        $incomes = Butget::all();
        $expenses = Expense::all();

        $totalIncome = $incomes->sum('monthly_income');
        $totalExpense = $expenses->sum('expense');
        $remaining = $totalIncome - $totalExpense;

        return view('dashboard', compact(
            'incomes',
            'expenses',
            'totalIncome',
            'totalExpense',
            'remaining'
        ));
    }

    public function edit($id)
    {
        $income = Butget::findOrFail($id);
        return view('butgets.edit', compact('income'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'monthly_income' => 'required|integer'
        ]);

        $income = Butget::findOrFail($id);
        $income->update($request->all());

        return redirect(route('butgets.index'))->with('success', 'Income updated!');
    }

    public function destroy($id)
    {
        Butget::destroy($id);
        return redirect(route('butgets.index'))->with('success', 'Income deleted!');
    }

}

