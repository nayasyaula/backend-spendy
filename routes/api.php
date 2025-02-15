<?php

use App\Http\Controllers\CoasController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::apiResource('coas', CoasController::class);
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
