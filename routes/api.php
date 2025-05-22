<?php

use Illuminate\Http\Request;
use App\Http\Controllers\Users;
use App\Http\Controllers\Wallet;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\DiagnosticsController;
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

// Diagnostic routes - only use in development/testing!
Route::prefix('diagnostics')->group(function () {
    Route::get('find-invalid-utf8-users', [DiagnosticsController::class, 'findInvalidUtf8Users']);
    Route::get('find-problematic-users-by-column', [DiagnosticsController::class, 'findProblematicUsersByColumn']);
    Route::get('scan-all-columns', [DiagnosticsController::class, 'scanAllColumns']);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('deposits', [Wallet::class, 'alldeposits'])->name('api.deposits.get');
    Route::get('withdrawals', [Wallet::class, 'allwithdrawals'])->name('account.deactivate');
});
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/tokens/fetch', [Users::class, 'api_call']);

//Cell Expert Integration API 

Route::get('/users', [UserController::class, 'index'])->name('api.users.index');
Route::get('/users/{id}', [UserController::class, 'show'])->name('api.users.show');
// Route::post('/users', [Api\Users::class, 'store'])->name('api.users.store');
// Route::put('/users/{id}', [Api\Users::class, 'update'])->name('api.users.update');
// Route::delete('/users/{id}', [Api\Users::class, 'destroy'])->name('api.users.destroy');
// Route::get('/users/{id}/trades', [Api\Users::class, 'trades'])->name('api.users.trades');
// Route::get('/users/{id}/trades/{trade_id}', [Api\Users::class, 'trade'])->name('api.users.trade');
// Route::get('/users/{id}/trades/{trade_id}/close', [Api\Users::class, 'closeTrade'])->name('api.users.closeTrade');
// Route::get('/users/{id}/trades/{trade_id}/delete', [Api\Users::class, 'deleteTrade'])->name('api.users.deleteTrade');
// Route::get('/users/{id}/trades/{trade_id}/history', [Api\Users::class, 'tradeHistory'])->name('api.users.tradeHistory');
// Route::get('/users/{id}/trades/{trade_id}/history/{history_id}', [Api\Users::class, 'tradeHistoryDetail'])->name('api.users.tradeHistoryDetail');
