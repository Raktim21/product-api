<?php

use App\Http\Controllers\ProductApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');

Route::get('product/quality', [ProductApiController::class, 'quality']);
Route::get('product/quantity/all', [ProductApiController::class, 'qualityAll']);
