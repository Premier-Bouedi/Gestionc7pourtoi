<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\OrderApiController;
use App\Http\Controllers\Api\IncidentApiController;
use App\Http\Controllers\Api\StockController;
use App\Http\Controllers\Api\ProductApiController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Routes API C7pourt3
Route::get('/products', [ProductApiController::class, 'index']);
Route::post('/orders', [OrderApiController::class, 'store']);
Route::get('/libreville/stock', [StockController::class, 'index']);
Route::get('/libreville/deliveries', [OrderApiController::class, 'deliveries']);
Route::post('/libreville/orders/{id}/status', [OrderApiController::class, 'updateStatus']);
Route::post('/libreville/incidents', [IncidentApiController::class, 'store']);
