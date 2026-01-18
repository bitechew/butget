<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ButgetController;
use App\Http\Controllers\ExpenseController;

Route::get('/butget', [ButgetController::class, 'create']);
Route::post('/butget', [ButgetController::class, 'store']);

Route::get('/expense', [ExpenseController::class, 'create']);
Route::post('/expense', [ExpenseController::class, 'store']);

Route::get('/dashboard', [ButgetController::class, 'index']);

Route::get('/butget/{id}/edit', [ButgetController::class, 'edit']);
Route::put('/butget/{id}', [ButgetController::class, 'update']);
Route::delete('/butget/{id}', [ButgetController::class, 'destroy']);

Route::get('/expense/{id}/edit', [ExpenseController::class, 'edit']);
Route::put('/expense/{id}', [ExpenseController::class, 'update']);
Route::delete('/expense/{id}', [ExpenseController::class, 'destroy']);
