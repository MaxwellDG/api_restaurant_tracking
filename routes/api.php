<?php

use App\Http\Controllers\Data\DataController;
use App\Http\Controllers\Product\ItemsController;
use App\Http\Controllers\Product\CategoriesController;
use App\Http\Controllers\Product\OrdersController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware(['auth:sanctum'])->group(function () {
    // Standard CRUD resources
    Route::resource('items', ItemsController::class);
    Route::resource('orders', OrdersController::class);
    Route::resource('categories', CategoriesController::class);

    // Custom endpoints for combined data
    Route::get('/inventory', [CategoriesController::class, 'inventory']);

    Route::get('/export', [DataController::class, 'exportData']);
    Route::get('/export/progress', [DataController::class, 'getExportProgress']);
});

Route::get("/test", function () {
    return Response::json(["message" => "Hello World"]);
});
