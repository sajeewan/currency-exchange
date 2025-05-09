<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ExchangeRateController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::get('/exchange-rates/usd', [ExchangeRateController::class, 'getUsdRates'])->name('exchange-rates.usd');
Route::get('/exchange-rates', [ExchangeRateController::class, 'index']);

Route::middleware('auth:sanctum')->group(function () {
   
    Route::post('/exchange-rates', [ExchangeRateController::class, 'store']);
    Route::get('/exchange-rates/{id}', [ExchangeRateController::class, 'show']);
    Route::put('/exchange-rates/{id}', [ExchangeRateController::class, 'update']);
    Route::delete('/exchange-rates/{id}', [ExchangeRateController::class, 'destroy']);
    Route::get('/audit-trails', [ExchangeRateController::class, 'getAuditTrails']);
    Route::post('/logout', [AuthController::class, 'logout']);
});
