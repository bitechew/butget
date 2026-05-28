<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\TransactionController;
use App\Http\Controllers\Api\BudgetController;
use App\Http\Controllers\Api\GoalController;
use App\Http\Controllers\Api\AccountController;
use App\Http\Controllers\Api\PaymentMethodController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\CategoryController;
use Illuminate\Support\Facades\Route;

// Public auth routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login',    [AuthController::class, 'login'])->name('login');

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // Auth
    Route::post('/logout',          [AuthController::class, 'logout']);
    Route::get('/user',             [AuthController::class, 'user']);
    Route::put('/user/profile',     [AuthController::class, 'updateProfile']);
    Route::put('/user/password',    [AuthController::class, 'changePassword']);

    // Dashboard
    Route::get('/dashboard',        [DashboardController::class, 'index']);

    // Transactions
    Route::get('/transactions/categories', [TransactionController::class, 'categories']);
    Route::apiResource('transactions', TransactionController::class);

    // Budgets
    Route::apiResource('budgets', BudgetController::class)->except(['show']);

    // Goals
    Route::post('/goals/{goal}/contribute', [GoalController::class, 'contribute']);
    Route::apiResource('goals', GoalController::class)->except(['show']);

    // Accounts
    Route::post('/accounts/{account}/restore', [AccountController::class, 'restore']);
    Route::apiResource('accounts', AccountController::class)->except(['show']);

    // Categories
    Route::post('/categories/{category}/restore', [CategoryController::class, 'restore']);
    Route::apiResource('categories', CategoryController::class)->except(['show']);

    // Categories
    Route::apiResource('categories', CategoryController::class)->except(['show']);

    // Payment Methods
    Route::apiResource('payment-methods', PaymentMethodController::class)->except(['show']);

    // Notifications
    Route::get('/notifications', [\App\Http\Controllers\NotificationController::class, 'index']);
    Route::post('/notifications', [\App\Http\Controllers\NotificationController::class, 'store']);
    Route::put('/notifications/{notification}/read', [\App\Http\Controllers\NotificationController::class, 'markAsRead']);
    Route::put('/notifications/mark-all-read', [\App\Http\Controllers\NotificationController::class, 'markAllRead']);
    Route::delete('/notifications/{notification}', [\App\Http\Controllers\NotificationController::class, 'destroy']);
});