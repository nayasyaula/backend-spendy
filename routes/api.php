<?php

use App\Http\Controllers\ReportController;
use App\Http\Controllers\CoasController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\TransactionController;

Route::post('login', [AuthController::class, 'login']);

Route::middleware('auth:api')->group(function () {
    Route::get('profile', [AuthController::class, 'profile']);
    Route::post('logout', [AuthController::class, 'logout']);
    Route::apiResource('coas', CoasController::class);
    Route::prefix('transactions')->group(function () {
        Route::get('/', [TransactionController::class, 'index']); // Get all transactions
        Route::post('/', [TransactionController::class, 'store']); // Create new transaction
        Route::get('/{id}', [TransactionController::class, 'show']); // Get a specific transaction
        Route::put('/{id}', [TransactionController::class, 'update']); // Update a transaction
        Route::delete('/{id}', [TransactionController::class, 'destroy']); // Delete a transaction
    });
    // Route untuk mengambil semua laporan transaksi yang telah disimpan
    Route::get('/reports', [ReportController::class, 'showReports']);
});
