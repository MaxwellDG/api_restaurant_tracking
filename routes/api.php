<?php

use App\Http\Controllers\Items\ItemsController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});

Route::resource('items', ItemsController::class);

Route::get("/test", function () {
    return Response::json(["message" => "Hello World"]);
});
