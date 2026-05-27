<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ButgetController;
use App\Http\Controllers\ExpenseController;

Route::controller(ButgetController::class)->group(function () {
    Route::get('/', 'index')->name('butgets.index'); 
    Route::get('/butgets/create', 'create')->name('butgets.create');
    Route::post('/butgets', 'store')->name('butgets.store');
    Route::get('/butgets/{id}/edit', 'edit')->name('butgets.edit');
    Route::put('/butgets/{id}', 'update')->name('butgets.update');       
    Route::delete('/butgets/{id}', 'destroy')->name('butgets.destroy'); 
});

Route::controller(ExpenseController::class)->group(function () {
    Route::get('/expenses', 'create')->name('expenses.create');
    Route::get('/expenses/create', 'create')->name('expense.create');
    Route::post('/expenses', 'store')->name('expenses.store');
    Route::get('/expenses/{id}/edit', 'edit')->name('expenses.edit');
    Route::put('/expenses/{id}', 'update')->name('expenses.update');
    Route::delete('/expenses/{id}', 'destroy')->name('expenses.destroy');
});

