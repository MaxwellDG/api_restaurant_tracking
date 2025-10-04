<?php

use App\Http\Controllers\Items\ItemsController;
use App\Http\Controllers\Product\CategoriesController;
use App\Http\Controllers\Product\OrdersController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});

Route::resource('items', ItemsController::class);
Route::resource('orders', OrdersController::class);
Route::resource('categories', CategoriesController::class);

Route::get("/test", function () {
    return Response::json(["message" => "Hello World"]);
});
