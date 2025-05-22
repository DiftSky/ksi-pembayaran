<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PaymentController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::post('/login', [AuthController::class, 'login'])->name('api.login');

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('api.logout');
    Route::get('/user', [AuthController::class, 'user'])->name('api.user');

    Route::prefix('payments')->group(function () {
        Route::get('/', [PaymentController::class, 'index'])->name('api.payments.index');
        Route::post('/', [PaymentController::class, 'store'])->name('api.payments.store');
        Route::get('/{id}', [PaymentController::class, 'show'])->name('api.payments.show');
        Route::put('/{id}/status', [PaymentController::class, 'updateStatus'])->name('api.payments.update-status');
    });

    // Route::apiResource('invoices', InvoiceController::class);
    // Route::apiResource('customers', CustomerController::class);
    // Route::apiResource('merchants', MerchantController::class);
});

// Fallback route for undefined API routes
Route::fallback(function() {
    return response()->json([
        'success' => false,
        'message' => 'API endpoint not found. If you believe this is an error, please contact support.'
    ], 404);
});
