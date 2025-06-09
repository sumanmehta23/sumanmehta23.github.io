<?php

use Illuminate\Http\Request;
use App\Http\Controllers\Users;
use App\Http\Controllers\Wallet;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LotSizeCalculatorController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\TradeController;
use App\Http\Controllers\Api\TransactionController;
use App\Http\Controllers\Api\WithdrawalController;

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

Route::middleware('auth:sanctum')->group(function () {
    Route::get('deposits', [Wallet::class, 'alldeposits'])->name('api.deposits.get');
    Route::get('withdrawals', [Wallet::class, 'allwithdrawals'])->name('account.deactivate');
    Route::get('/users', [UserController::class, 'index'])->name('api.users.index');
    Route::get('/users/{id}', [UserController::class, 'show'])->name('api.users.show');
    Route::get('/trades', [TradeController::class, 'index'])->name('api.trades.index');
    Route::get('/trades/{id}', [TradeController::class, 'show'])->name('api.trades.show');
    Route::get('/transactions', [TransactionController::class, 'index']);
    Route::get('/withdrawal', [WithdrawalController::class, 'index']);
});
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/tokens/fetch', [Users::class, 'api_call']);
Route::post('/calculate-lot-size', [LotSizeCalculatorController::class, 'index']);

//Cell Expert Integration API




// Route::post('/users', [Api\Users::class, 'store'])->name('api.users.store');
// Route::put('/users/{id}', [Api\Users::class, 'update'])->name('api.users.update');
// Route::delete('/users/{id}', [Api\Users::class, 'destroy'])->name('api.users.destroy');
// Route::get('/users/{id}/trades', [Api\Users::class, 'trades'])->name('api.users.trades');
// Route::get('/users/{id}/trades/{trade_id}', [Api\Users::class, 'trade'])->name('api.users.trade');
// Route::get('/users/{id}/trades/{trade_id}/close', [Api\Users::class, 'closeTrade'])->name('api.users.closeTrade');
// Route::get('/users/{id}/trades/{trade_id}/delete', [Api\Users::class, 'deleteTrade'])->name('api.users.deleteTrade');
// Route::get('/users/{id}/trades/{trade_id}/history', [Api\Users::class, 'tradeHistory'])->name('api.users.tradeHistory');
// Route::get('/users/{id}/trades/{trade_id}/history/{history_id}', [Api\Users::class, 'tradeHistoryDetail'])->name('api.users.tradeHistoryDetail');
