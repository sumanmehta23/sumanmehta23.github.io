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

use App\Http\Controllers\Api\V1\AccountController as V1AccountController;
use App\Http\Controllers\Api\V1\DepositController as V1DepositController;
use App\Http\Controllers\Api\V1\IbController;
use App\Http\Controllers\Api\V1\RmController;
use App\Http\Controllers\Api\V1\TradeController as V1TradeController;
use App\Http\Controllers\Api\V1\UserController as V1UserController;
use App\Http\Controllers\Api\V1\WithdrawalController as V1WithdrawalController;
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
// ============================================================================
// API v1 - Versioned Endpoints (Recommended)
// ============================================================================
Route::prefix('v1')->middleware(['auth:sanctum', 'throttle:1000,1'])->group(function () {

    // Users Endpoints
    Route::prefix('users')->group(function () {
        Route::get('/', [V1UserController::class, 'index'])
            ->middleware('check.api.permissions:api:kpi:users:read')
            ->name('api.v1.users.index');

        Route::get('/{id}', [V1UserController::class, 'show'])
            ->middleware('check.api.permissions:api:kpi:users:read')
            ->name('api.v1.users.show');

        Route::get('/{id}/bonus-history', [V1UserController::class, 'bonusHistory'])
            ->middleware('check.api.permissions:api:kpi:users:bonus-history:read')
            ->name('api.v1.users.bonus-history');

        Route::get('/{id}/competitions', [V1UserController::class, 'competitions'])
            ->middleware('check.api.permissions:api:kpi:users:competitions:read')
            ->name('api.v1.users.competitions');

        Route::get('/profile', [V1UserController::class, 'profile'])
            ->middleware('check.api.permissions:api:kpi:users:read')
            ->name('api.v1.users.profile');
    });

    // Withdrawals Endpoints
    Route::prefix('withdrawals')->group(function () {
        Route::get('/', [V1WithdrawalController::class, 'index'])
            ->middleware('check.api.permissions:api:kpi:withdrawals:read')
            ->name('api.v1.withdrawals.index');

        Route::get('/statistics', [V1WithdrawalController::class, 'statistics'])
            ->middleware('check.api.permissions:api:kpi:withdrawals:read')
            ->name('api.v1.withdrawals.statistics');

        Route::get('/{id}', [V1WithdrawalController::class, 'show'])
            ->middleware('check.api.permissions:api:kpi:withdrawals:read')
            ->name('api.v1.withdrawals.show');
    });

    // Deposits Endpoints
    Route::prefix('deposits')->group(function () {
        Route::get('/', [V1DepositController::class, 'index'])
            ->middleware('check.api.permissions:api:kpi:deposits:read')
            ->name('api.v1.deposits.index');

        Route::get('/statistics', [V1DepositController::class, 'statistics'])
            ->middleware('check.api.permissions:api:kpi:deposits:read')
            ->name('api.v1.deposits.statistics');

        Route::get('/{id}', [V1DepositController::class, 'show'])
            ->middleware('check.api.permissions:api:kpi:deposits:read')
            ->name('api.v1.deposits.show');
    });

    // Accounts Endpoints
    Route::prefix('accounts')->group(function () {
        Route::get('/', [V1AccountController::class, 'index'])
            ->middleware('check.api.permissions:api:kpi:accounts:read')
            ->name('api.v1.accounts.index');

        Route::get('/{id}', [V1AccountController::class, 'show'])
            ->middleware('check.api.permissions:api:kpi:accounts:read')
            ->name('api.v1.accounts.show');

        Route::get('/user/{userId}', [V1AccountController::class, 'userAccounts'])
            ->middleware('check.api.permissions:api:kpi:accounts:read')
            ->name('api.v1.accounts.byUser');

        Route::get('/statistics', [V1AccountController::class, 'statistics'])
            ->middleware('check.api.permissions:api:kpi:accounts:read')
            ->name('api.v1.accounts.statistics');
    });

    // Trades Endpoints
    Route::prefix('trades')->group(function () {
        Route::get('/', [V1TradeController::class, 'index'])
            ->middleware('check.api.permissions:api:kpi:trades:read')
            ->name('api.v1.trades.index');

        Route::get('/statistics', [V1TradeController::class, 'statistics'])
            ->middleware('check.api.permissions:api:kpi:trades:read')
            ->name('api.v1.trades.statistics');

        Route::get('/{id}', [V1TradeController::class, 'show'])
            ->middleware('check.api.permissions:api:kpi:trades:read')
            ->name('api.v1.trades.show');
    });

    // Relationship Managers Endpoints
    Route::prefix('relationship-managers')->group(function () {
        Route::get('/', [RmController::class, 'index'])
            ->middleware('check.api.permissions:api:kpi:relationship-managers:read')
            ->name('api.v1.rms.index');

        Route::get('/{id}', [RmController::class, 'show'])
            ->middleware('check.api.permissions:api:kpi:relationship-managers:read')
            ->name('api.v1.rms.show');
    });

    // IBs Endpoints
    Route::prefix('ibs')->group(function () {
        Route::get('/', [IbController::class, 'index'])
            ->middleware('check.api.permissions:api:kpi:ibs:read')
            ->name('api.v1.ibs.index');

        Route::get('/{id}', [IbController::class, 'show'])
            ->middleware('check.api.permissions:api:kpi:ibs:read')
            ->name('api.v1.ibs.show');
    });
});

// ============================================================================
// Legacy API Endpoints (Deprecated - Use v1 endpoints instead)
// ============================================================================

// Routes with pagination that need higher rate limits - using direct throttle definition
Route::middleware('auth:sanctum')->group(function () {
    Route::get('deposits', [Wallet::class, 'alldeposits'])->name('api.deposits.get');
    Route::get('withdrawals', [Wallet::class, 'allwithdrawals'])->name('account.deactivate');

    // High-volume pagination endpoints with increased rate limits (still authenticated)
    Route::middleware('throttle:api-pagination')->group(function () {
        Route::get('/users', [UserController::class, 'index'])->name('api.users.index');
        Route::get('/trades', [TradeController::class, 'index'])->name('api.trades.index');
        Route::get('/transactions', [TransactionController::class, 'index']);
        Route::get('/deposit', [TransactionController::class, 'index']); //API for Cell Expert
        Route::get('/withdrawal', [WithdrawalController::class, 'index']); //API for Cell Expert
    });

    // Regular rate limit for other endpoints
    Route::get('/users/{id}', [UserController::class, 'show'])->name('api.users.show');
    Route::get('/trades/{id}', [TradeController::class, 'show'])->name('api.trades.show');

    // Competition routes
    Route::get('/competition/trader-data/{account}', [\App\Http\Controllers\Api\CompetitionController::class, 'getTraderData']);
    Route::get('/competition/current', [\App\Http\Controllers\Api\CompetitionController::class, 'getCurrentCompetition']);
});
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/tokens/fetch', [Users::class, 'api_call']);
Route::post('/calculate-lot-size', [LotSizeCalculatorController::class, 'index']);
// X9 CRM API Routes
Route::middleware(['auth:sanctum'])->prefix('crm')->group(function () {
    Route::get('/connection', [\App\Http\Controllers\Api\X9Controller::class, 'testConnection']);
    Route::post('/create_user', [\App\Http\Controllers\Api\X9Controller::class, 'createUser']);
    Route::get('/user/{loginId}', [\App\Http\Controllers\Api\X9Controller::class, 'getUserDetails']);
    Route::post('/user/balance', [\App\Http\Controllers\Api\X9Controller::class, 'manageBalance']);
    Route::get('/client_group_types', [\App\Http\Controllers\Api\X9Controller::class, 'getClientGroupTypes']);
    Route::get('/client_groups_by_type/{typeId}', [\App\Http\Controllers\Api\X9Controller::class, 'getClientGroupsByType']);
});




// Route::post('/users', [Api\Users::class, 'store'])->name('api.users.store');
// Route::put('/users/{id}', [Api\Users::class, 'update'])->name('api.users.update');
// Route::delete('/users/{id}', [Api\Users::class, 'destroy'])->name('api.users.destroy');
// Route::get('/users/{id}/trades', [Api\Users::class, 'trades'])->name('api.users.trades');
// Route::get('/users/{id}/trades/{trade_id}', [Api\Users::class, 'trade'])->name('api.users.trade');
// Route::get('/users/{id}/trades/{trade_id}/close', [Api\Users::class, 'closeTrade'])->name('api.users.closeTrade');
// Route::get('/users/{id}/trades/{trade_id}/delete', [Api\Users::class, 'deleteTrade'])->name('api.users.deleteTrade');
// Route::get('/users/{id}/trades/{trade_id}/history', [Api\Users::class, 'tradeHistory'])->name('api.users.tradeHistory');
// Route::get('/users/{id}/trades/{trade_id}/history/{history_id}', [Api\Users::class, 'tradeHistoryDetail'])->name('api.users.tradeHistoryDetail');
