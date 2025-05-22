<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Users;
use App\Http\Controllers\Wallet;

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

    // Competition routes
    Route::get('/competition/trader-data/{account}', [\App\Http\Controllers\Api\CompetitionController::class, 'getTraderData']);
    Route::get('/competition/current', [\App\Http\Controllers\Api\CompetitionController::class, 'getCurrentCompetition']);
});
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/tokens/fetch', [Users::class, 'api_call']);
