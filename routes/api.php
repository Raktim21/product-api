<?php

use App\Http\Controllers\ProductApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');

Route::get('product/quantity', [ProductApiController::class, 'quantity']);
Route::get('product/quantity/all', [ProductApiController::class, 'quantityAll']);
Route::get('vendors', [ProductApiController::class, 'vendor']);

Route::post('product/sales', [ProductApiController::class, 'storeSales']);
