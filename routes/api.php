<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\VerifyEmailController;
use App\Http\Controllers\Company\CompanyController;
use App\Http\Controllers\Data\DataController;
use App\Http\Controllers\Product\ItemsController;
use App\Http\Controllers\Product\CategoriesController;
use App\Http\Controllers\Product\OrdersController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Route;

// Auth routes (no authentication required)
Route::post('/register', [RegisteredUserController::class, 'store'])
    ->middleware('guest')
    ->name('register');

Route::post('/login', [AuthenticatedSessionController::class, 'store'])
    ->middleware('guest')
    ->name('login');

Route::post('/refresh', [AuthenticatedSessionController::class, 'refresh'])
    ->middleware('auth:sanctum')
    ->name('refresh');

Route::post('/forgot-password', [PasswordResetLinkController::class, 'store'])
    ->middleware('guest')
    ->name('password.email');

Route::post('/reset-password', [NewPasswordController::class, 'store'])
    ->middleware('guest')
    ->name('password.store');

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/email/verify/status', function (Request $request) {
        return response()->json([
            'email_verified' => $request->user()->hasVerifiedEmail(),
            'email' => $request->user()->email,
            'email_verified_at' => $request->user()->email_verified_at,
        ]);
    });
    
    Route::post('/email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
        ->middleware('throttle:6,1');

    Route::get('/verify-email/{id}/{hash}', VerifyEmailController::class)
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');

    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
        ->name('logout');

    // Standard CRUD resources
    Route::resource('items', ItemsController::class);
    Route::resource('orders', OrdersController::class);
    Route::resource('categories', CategoriesController::class);
    Route::resource('companies', CompanyController::class);

    // Custom endpoints for combined data
    Route::get('/inventory', [CategoriesController::class, 'inventory']);
    
    Route::get('/export', [DataController::class, 'exportData']);
    Route::get('/export/progress', [DataController::class, 'getExportProgress']);

});

Route::get("/test", function () {
    return Response::json(["message" => "Hello World"]);
});
