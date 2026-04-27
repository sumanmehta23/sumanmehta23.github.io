<?php

use App\Http\Controllers\Admin\AccountNotFoundController;
use App\Http\Controllers\Admin\AjaxController;
use App\Http\Controllers\Admin\ApiAjaxController;
use App\Http\Controllers\Admin\ClientAccController;
use App\Http\Controllers\Admin\ClientController;
use App\Http\Controllers\Admin\CompetitionProductController;
use App\Http\Controllers\Admin\Dashboard;
//use App\Http\Controllers\Admin\DeletedAccountsController;
use App\Http\Controllers\Admin\IbCommissionAnalysisController;
use App\Http\Controllers\Admin\IBController;
use App\Http\Controllers\Admin\IbWithdrawalController;
use App\Http\Controllers\Admin\Kyc;
use App\Http\Controllers\Admin\Leaderboard;
use App\Http\Controllers\Admin\LearnContentController;
use App\Http\Controllers\Admin\Login;
use App\Http\Controllers\Admin\ManualPaymentController;
use App\Http\Controllers\Admin\MT5Controller;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\ProductsController;
use App\Http\Controllers\Admin\SearchController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\StaffManagement;
//use App\Http\Controllers\Admin\SumsubController;
use App\Http\Controllers\Admin\TaskController;
use App\Http\Controllers\Admin\Ticket;
use App\Http\Controllers\Admin\Transaction;
use App\Http\Controllers\Admin\TwoFactorAuthController;
use App\Http\Controllers\Admin\WarningUserController;
use App\Http\Controllers\Admin\ZapierAccountsController;
use App\Http\Controllers\Api\Mt5CommonController;
use App\Http\Controllers\Api\ZapierWebhookController;
use App\Http\Controllers\ClientTaskController;
use App\Http\Controllers\CompetitionController;
use App\Http\Controllers\ForexNewsController;
use App\Http\Controllers\Home;
use App\Http\Controllers\Ib;
use App\Http\Controllers\InternalTransfer;
use App\Http\Controllers\KycController;
use App\Http\Controllers\KycSyncController;
use App\Http\Controllers\LearnController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\MT5Accounts;
use App\Http\Controllers\MT5RedisCoordinationDemoController;
use App\Http\Controllers\PammController;
use App\Http\Controllers\Payment;
use App\Http\Controllers\PaymentCallbackController;
use App\Http\Controllers\PopupImpressionController;
use App\Http\Controllers\Tickets;
use App\Http\Controllers\TradeDepositController;
use App\Http\Controllers\TradeWithdrawal;
use App\Http\Controllers\Transactions;
use App\Http\Controllers\Users;
use App\Http\Controllers\Wallet;
use App\Models\Account;
use App\Models\Ib1;
use App\Models\Ib1Commission;
use App\Models\KycUpdate;
use App\Models\Permission;
use App\Models\TotalBalance;
use App\Models\TradeWithdrawals;
use App\Models\User;
use App\Models\WalletDeposit;
use App\View\Components\AdminTwoFactorAuthentication;
use App\View\Components\TwoFactorAuthentication;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Laravel\Telescope\Telescope;
use Symfony\Component\Process\Process;
use Symfony\Component\HttpFoundation\StreamedResponse;
use function PHPUnit\Framework\throwException;

Route::get('/competitions-overview', [CompetitionController::class, 'competitionsOverview'])->name('competitionsOverview');
// Change GET → POST
Route::get('/competitions-overview/leaderboard/{id}', [CompetitionController::class, 'competitionsOverviewLeaderboard'])->name('competitionsOverviewLeaderboard');
Route::get('/competitions-overview/trader-data/{accountNo}/{startDate}/{endDate}', [CompetitionController::class, 'getTraderData'])->name('competitionsOverview.trader-data');

Route::post('/user/kyc/listener', [KycController::class, 'listener'])->name('kyc.listener');
Route::post('/user/kyc/veriff-listener', [KycController::class, 'veriffListener'])->name('kyc.veriff.listener');
Route::get('/payment-response', [Payment::class, 'handlePaymentResponse'])->name('handlePaymentResponse');

// Route::get('/failed-payment-response', [Payment::class, 'handleFailedPaymentResponse'])->name('handleFailedPaymentResponse');
Route::get('/handle-failed-payment-response', [Payment::class, 'handleFailedPaymentResponse'])->name('handleFailedPaymentResponse');

Route::get('/manually-payment-response', [Payment::class, 'manuallyPaymentResponse'])->name('manuallyPaymentResponse');

Route::post('/paymentcallback', [PaymentCallbackController::class, 'handleCallback'])->name('paymentcallback');
Route::get('/', [LoginController::class, 'showLoginForm'])->name('login_index');
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::get('/forgot-password', [LoginController::class, 'forgot_password']);
Route::post('/forgot-password', [LoginController::class, 'sendResetLink']);
Route::get('/register', [LoginController::class, 'register'])->name('register');

Route::get('register/ref', function () {

    // Get the 'refercode' from the query string
    $refercode = request()->query('refercode');

    // Perform the redirection to the new URL format
    return redirect()->route('ib-ref', ['refercode' => $refercode]);
});

Route::post('/register', [LoginController::class, 'addUser']);
Route::get('/email_verify', [LoginController::class, 'verifyEmail']);



Route::get('/reset-password', [LoginController::class, 'resetPassword']);
Route::post('/reset-password', [LoginController::class, 'resetPassword']);
Route::get('/ib-ref', [Ib::class, 'ibReference'])->name('ib-ref');
Route::post('/ib-ref', [LoginController::class, 'addUser'])->name('ib-ref-post');

Route::middleware(['auth'])->group(function () {

    Route::get('ragapay-success', function () {
        return "Payment Successful! Thank you for your purchase.";
    })->name('ragapay.success');

    Route::get('ragapay-cancel', function () {
        return "Payment Cancelled. You have cancelled the payment process.";
    })->name('ragapay.cancel');

    Route::get('ragapay-error', function () {
        return "Payment Error. An error occurred during the payment process.";
    })->name('ragapay.error');

    Route::get('/wallet_address_verify', [Wallet::class, 'wallet_address_verify']);

    Route::get('/switchToAdmin', [AjaxController::class, 'switchToAdmin'])->name("switchToAdmin");
    Route::get('/two_factor_auth', [LoginController::class, 'two_factor_auth'])->name('two_factor_auth');
    Route::post('/verify-2fa', [LoginController::class, 'verify_two_factor_auth'])->name('verify-2fa');

    Route::get('/delete_wallet_address', [Wallet::class, 'delete_wallet_address'])->name('delete_wallet_address');
    Route::get('/logout', [LoginController::class, 'logout'])->name('logout');

    Route::get('/confirm_password', [LoginController::class, 'showConfirmPasswordForm'])
        ->name('confirm_password');
    Route::get('/wallet_withdrawal_verify', [Wallet::class, 'wallet_withdrawal_verify'])->name('wallet_withdrawal_verify');
    // Route::get('/', [Home::class, 'dashboard'])->name('dashboardIndex');
    Route::get('dashboard', [Home::class, 'dashboard'])->name('dashboard');
    Route::get('/forex-news', [ForexNewsController::class, 'index'])->name('forex-news.index');
    Route::get('/view_account_details', [MT5Accounts::class, 'viewAccountDetails'])->name('view_account_details');
//    Route::get('/select_account_deposit', [MT5Accounts::class, 'select_account_deposit'])->name('select_account_deposit');

    // Route::get('/wallet', [Wallet::class, 'index'])->name('wallet');
    Route::get('/transactions', [Transactions::class, 'index'])->name('transactions');
    Route::post('/update-transaction', [Transactions::class, 'updateTransaction'])->name('updateTransaction');
    Route::post('/popup-impressions/review-popup', [PopupImpressionController::class, 'storeReviewPopup'])
        ->name('popup-impressions.review-popup');
    Route::post('/popup-impressions/review-popup/dismiss', [PopupImpressionController::class, 'dismissReviewPopup'])
        ->name('popup-impressions.review-popup.dismiss');
    Route::post('/popup-impressions/review-popup/click', [PopupImpressionController::class, 'clickReviewPopup'])
        ->name('popup-impressions.review-popup.click');

    Route::get('/liveAccounts', [MT5Accounts::class, 'liveAccounts'])->name('liveAccounts');
    Route::get('/demoAccounts', [MT5Accounts::class, 'demoAccounts'])->name('demoAccounts');
    Route::get('/view-account-details/{account}', [MT5Accounts::class, 'viewAccountDetails'])->where('account', '.*')->name('view-account-details');
    Route::get('/createLiveAccount', [MT5Accounts::class, 'showLiveAccountForm'])->name('show-live-account-form');
    Route::post('/createLiveAccount', [MT5Accounts::class, 'createLiveAccount'])->name('create-live-account');
    Route::get('/createDemoAccount', [MT5Accounts::class, 'showDemoAccountForm'])->name('show-demo-account-form');
    Route::post('/createDemoAccount', [MT5Accounts::class, 'createDemoAccount'])->name('create-demo-account');
    Route::post('/view-account-details/{account}', [MT5Accounts::class, 'changeMt5Password'])->where('account', '.*')->name('change-mt5-password');

    Route::get('/getLeverage', [MT5Accounts::class, 'getLeverage'])->name('get-leverage');

    Route::post('/update-nickname', [MT5Accounts::class, 'updateNickname'])->name('update.nickname');
    // Route::post('/update-leverage', [MT5Accounts::class, 'updateLeverage'])->name('update-leverage');

    Route::get('/tasks', [ClientTaskController::class, 'index'])->name('tasks');
    Route::post('/task/client_verify', [ClientTaskController::class, 'client_verify'])->name('task.client_verify');

    // X9 Testing Routes
    Route::get('/test-x9-connection', [App\Http\Controllers\X9TestController::class, 'testConnection'])->name('test-x9-connection');
    Route::get('/test-x9-demo', [App\Http\Controllers\X9TestController::class, 'testDemoPage'])->name('test-x9-demo');

    Route::post('/task/screenshot/upload', [TaskController::class, 'uploadScreenshot'])->name('task.screenshot.upload');

    Route::get('/competition', [CompetitionController::class, 'competition'])->name('competition');
    Route::get('/learn', [LearnController::class, 'index'])->name('learn');
    Route::get('/joinCompetition', [CompetitionController::class, 'showCompetitionForm'])->name('showCompetitionForm');
    Route::post('/joinCompetition', [CompetitionController::class, 'createCompetition'])->name('joinCompetition');
    Route::get('/competition/leaderboard', [CompetitionController::class, 'leaderboard'])->name('competition.leaderboard');
    Route::get('/competition/trader/{accountNo}/{start_date}/{end_date}', [CompetitionController::class, 'getTraderData'])->name('competition.trader-data');
    Route::get('/competition/export', [CompetitionController::class, 'exportLeaderboard'])->name('user.competition.export');

    Route::get('/get-account-rank', [CompetitionController::class, 'getAccountRank'])->name('get-account-rank');

    Route::get('/support', [Tickets::class, 'index'])->name('supports');
    Route::post('/support', [Tickets::class, 'createTicket'])->name('support');
    Route::get('/ticket_details', [Tickets::class, 'showDetails'])->name('ticket_details');
    Route::post('/ticket_details', [Tickets::class, 'addRemark'])->name('ticket_details_store');
    Route::get('/ticket_followups', [Tickets::class, 'fetchFollowups'])->name('ticket_followups');

    Route::get('/ib-profile', [Ib::class, 'ib_profile'])->name('ib-profile');
    Route::get('/ib', [Ib::class, 'index'])->name('ib');
    Route::post('/ib-profile', [Ib::class, 'processTransfer'])->name('ib-profile-store');
    Route::post('/ib-enroll', [Ib::class, 'ibEnroll'])->name('ib-enroll');
    Route::post('/ib-resend', [Ib::class, 'ibResend'])->name('ib-resend');
    Route::post('/ib-update-referral', [Ib::class, 'ibUpdateReferral'])->name('ib-update-referral');

    // IB Profile DataTables AJAX routes
    Route::get('/ib/commission-data', [Ib::class, 'getCommissionData'])->name('ib.commission-data');
    Route::get('/ib/client-profile', [Ib::class, 'getClientIbProfile'])->name('ib.client-profile');

    Route::get('/user-profile', [Users::class, 'profile'])->name('user-profile');
    Route::get('/sumsub', [Users::class, 'sumsub'])->name('sumsub');
    Route::get('/veriff', [Users::class, 'veriff'])->name('veriff');
    Route::get('/pamm/manager', [PammController::class, 'manager'])->name('pamm.manager');
    Route::get('/pamm/investor', [PammController::class, 'investor'])->name('pamm.investor');
    Route::post('/sumsub_verify', [Users::class, 'sumsub_verify'])->name('sumsub_verify');
    Route::post('/veriff_event', [Users::class, 'veriff_event'])->name('veriff_event');
    Route::post('/log_kyc_verification', [Users::class, 'logVerification'])->name('logVerification');

    // KYC Sync Routes
    Route::post('/sync-user-kyc', [KycSyncController::class, 'syncUser'])->name('sync.user.kyc');
    Route::post('/bulk-sync-kyc', [KycSyncController::class, 'bulkSync'])->name('bulk.sync.kyc');

    Route::post('/wallet/store', [Wallet::class, 'storeClientWallet'])->name('wallet.store');
    Route::post('/wallet/updateStatus', [Wallet::class, 'updateStatus'])->name('wallet.updateStatus');
    Route::post('/verify_delete_wallet_address', [Wallet::class, 'verify_delete_wallet_address'])->name('verify_delete_wallet_address');
    Route::get('/get_editing_wallet_details', [Wallet::class, 'get_editing_wallet_details'])->name('get_editing_wallet_details');
    Route::post('/wallet/verify_edit', [Wallet::class, 'verify_edit_wallet_details'])->name('wallet.verify_edit');
    Route::get('/edit_wallet_details', [Wallet::class, 'edit_wallet_details'])->name('edit_wallet_details');

    Route::get('/wallet_deposit', [Wallet::class, 'showDepositForm'])->name('wallet_deposit');
    Route::get('/wallet_withdrawal', [Wallet::class, 'showWithdrawalForm'])->name('wallet_withdrawal');
    Route::post('/wallet_deposit', [Wallet::class, 'deposit'])->name('wallet_deposit_store');
    Route::post('/wallet_withdrawal', [Wallet::class, 'withdrawal'])->name('wallet_withdrawal_store');
    Route::post('/resend-wallet-withdrawal-email', [Wallet::class, 'resend_wallet_withdrawal_verify_email'])
        ->name('resend.wallet.withdrawal.email');
    Route::post('/wallet_payment', [Wallet::class, 'processPayment'])->name('wallet_payment');
    Route::post('/change_password', [Users::class, 'changePassword'])->name('password.change');
    Route::post('/resend_wallet_address_confirmation', [Wallet::class, 'resend_wallet_address_confirmation_email'])->name('resend.wallet.confirmation');
    Route::post('/resend_wallet_address_delete_confirmation', [Wallet::class, 'resend_wallet_address_delete_confirmation_email'])->name('resend.wallet.delete.confirmation');

    Route::post('/change_profileimage', [Users::class, 'changeProfileImage'])->name('profileimage.change');
    // Route::post('/change_email', [Users::class, 'changeEmail'])->name('email.change');

    Route::get('/trade-deposit', [TradeDepositController::class, 'index'])->name('trade-deposit');
    Route::post('/trade-deposit', [TradeDepositController::class, 'deposit'])->name('trade-deposit_store');
//    Route::post('/new_trade_deposit', [TradeDepositController::class, 'new_trade_deposit'])->name('new_trade_deposit_store');

    Route::get('/sync_amount', [TradeDepositController::class, 'sync_amount']);

    Route::get('/trade_deposit_manually/{user_id}/{amount}/{account}', [TradeDepositController::class, 'deposit_manually'])->name('trade_deposit_manually');

    Route::get('/transaction_deposit_manually/{trx_id}/{amount}/{account_code}/{deposit_type}', [Wallet::class, 'transaction_deposit_manually']);

    Route::get('/trade-withdrawal/{account_id?}', [TradeWithdrawal::class, 'index'])->name('trade-withdrawal');
    Route::post('/trade-withdrawal/{account_id?}', [TradeWithdrawal::class, 'withdraw'])->name('trade-withdrawal_store');
    Route::get('/account_withdrawal_verify', [TradeWithdrawal::class, 'account_withdrawal_verify'])->name('account_withdrawal_verify');
    Route::get('/internal-transfer', [InternalTransfer::class, 'index'])->name('internal-transfer');
    Route::post('/process-transfer', [InternalTransfer::class, 'processTransfer'])->name('process-transfer_store');

    Route::post('/verify-promocode', [AjaxController::class, 'verify_promocode'])->name('verify.promocode');


});
Route::post('/cryptochill/callback', [Wallet::class, 'secureProcessPayment'])->name('secure_wallet_payment');

// Public Blog Routes
Route::get('/blog', [\App\Http\Controllers\BlogController::class, 'index'])->name('blog.index');
Route::get('/blog/{slug}', [\App\Http\Controllers\BlogController::class, 'show'])->name('blog.show');

Route::prefix("/admin")->name("admin.")->group(function () {



    Route::get('/', [Login::class, 'showLoginForm']);
    Route::get('/verify_2fa', [Login::class, 'verify_2fa'])->name('verify_2fa');
    Route::post('/verify_two_factor_auth', [Login::class, 'verify_two_factor_auth'])->name('verify_two_factor_auth');
    Route::post('/', [Login::class, 'adminLogin']);
    Route::get('/login', [Login::class, 'showLoginForm'])->name('login');
    Route::post('/login', [Login::class, 'adminLogin']);
    Route::get('/logout', [Login::class, 'logout'])->name('logout');
    Route::get('/forgot-password', [Login::class, 'showAdminForgotPasswordForm'])->name('forgot-password');
    Route::post('/forgot-password', [Login::class, 'sendAdminResetLink'])->name('send-reset-link');
    Route::get('/reset-password', [Login::class, 'resetAdminPassword'])->name('reset-password');
    Route::post('/reset-password', [Login::class, 'resetAdminPassword']);

    Route::post('/confirm-password', [LoginController::class, 'confirmPassword'])->name('password.confirm');

    Route::post('/resend-credentials', [MT5Accounts::class, 'resendCredentials'])->name('resend-credentials');

    // Route::get('/admin/activity-logs', [ActivityLogController::class, 'index'])->name('activity-logs.index');
    // Route::get('/users/{user}', 'Users@show')->name('users.show');
    // Route::get('/transactions/{transaction}', 'Transaction@show')->name('transactions.show');

    // Route::get('/users/{user}', [Users::class, 'profile'])->name('users.show');
    // Route::get('/user-profile', [Users::class, 'profile'])->name('user-profile');
    // Route::get('/client_details/{userId}', [ClientController::class, 'clientDetails'])->name('admin-view-client-details')->middleware('check.permissions:client:view');
    // Route::get('/transactions/{transaction}', 'TransactionController@show')->name('admin.transactions.show');
    // Route::get('/orders/{order}', 'OrderController@show')->name('admin.orders.show');
    // Route::get('/products/{product}', 'ProductController@show')->name('admin.products.show');

// MT5 Not Found Accounts Routes
        Route::prefix('accounts/not-found-in-mt5')->name('accounts.not_found_in_mt5.')->group(function () {
            Route::get('/', [AccountNotFoundController::class, 'index'])->name('index');
            Route::get('/export', [AccountNotFoundController::class, 'export'])->name('export');
            Route::get('/stats', [AccountNotFoundController::class, 'stats'])->name('stats');
            Route::post('/bulk-verify-and-archive', [AccountNotFoundController::class, 'bulkVerifyAndArchive'])->name('bulk_verify_and_archive');
        });
 // MT5 server information endpoint
    Route::get('/common/get', [Mt5CommonController::class, 'get'])->name('server.common.get')->middleware('throttle:500,1');
        Route::post('/sync-mt5-account-status', [AccountNotFoundController::class, 'syncMT5AccountStatus'])->name('accounts.sync-mt5-account-status');
    Route::middleware(['is_admin'])->group(function () {
        Route::get('/ajax', [AjaxController::class, 'index']);

        Route::post('/ajax', [AjaxController::class, 'index']);
        Route::get('/api/ajax', [ApiAjaxController::class, 'handleRequest']);
        Route::post('/api/ajax', [ApiAjaxController::class, 'handleRequest']);
        Route::post('/getClientSwitch', [AjaxController::class, 'getClientSwitch']);

        Route::get('/getCompetitionsData', [AjaxController::class, 'getCompetitionsData']);
        Route::get('/export-competitions', [AjaxController::class, 'exportCompetitions'])->name('export.competitions')->middleware('check.permissions:export:requested_competition');
        Route::get('/getRequestedCompetitionList', [AjaxController::class, 'getRequestedCompetitionList']);

        Route::get('/logs/export', [SettingsController::class, 'export'])->name('logs.export')->middleware('check.permissions:export:logs');
        Route::get('/getRequestedAccountsList', [AjaxController::class, 'getRequestedAccountsList']);
        Route::get('/getClientList', [AjaxController::class, 'getClientList']);
        Route::get('/export-all-clients', [AjaxController::class, 'exportAllClients'])->name('export.all_clients')->middleware('check.permissions:export:clients');
        Route::get('/getLiveAccountsList', [AjaxController::class, 'getLiveAccountsList']);
        Route::get('/getDemoAccountsList', [AjaxController::class, 'getDemoAccountsList']);

        Route::get('/export-all-live-accounts', [AjaxController::class, 'exportAllLiveAccounts'])->name('export.all_live_accounts')->middleware('check.permissions:export:live_accounts');
        Route::get('/export-all-demo-accounts', [AjaxController::class, 'exportAllDemoAccounts'])->name('export.all_demo_accounts')->middleware('check.permissions:export:demo_accounts');

        Route::get('/getWalletDeposit2', [AjaxController::class, 'getWalletDeposit2']);
        Route::get('/getWalletWithdrawal2', [AjaxController::class, 'getWalletWithdrawal2']);
        Route::get('/getTradingDeposit2', [AjaxController::class, 'getTradingDeposit2']);
        Route::get('/export-all-trading-deposit', [AjaxController::class, 'exportAllTradingDeposit'])->name('export.all_trading_deposit')->middleware('check.permissions:export:trading_deposit');
        Route::get('/getTradingWithdrawal2', [AjaxController::class, 'getTradingWithdrawal2']);
        Route::get('/getTradeHistory', [AjaxController::class, 'getTradeHistory'])->name('admin.getTradeHistory');
        Route::get('/export-all-trades', [AjaxController::class, 'exportAllTrades'])->name('export.all_trades')->middleware('check.permissions:export:trading_deposit');
        Route::get('/export-filtered-trades', [AjaxController::class, 'exportFilteredTrades'])->name('export.filtered_trades')->middleware('check.permissions:export:trading_deposit');
        Route::get('/getInternalTransfer2', [AjaxController::class, 'getInternalTransfer2']);
        Route::get('/export-all-internal-transfer', [AjaxController::class, 'exportAllInternalTransfer'])->name('export.all_internal_transfer')->middleware('check.permissions:export:internal_transfer');

        Route::get('/getPendingWalletDeposit2', [AjaxController::class, 'getPendingWalletDeposit2']);
        Route::get('/getPermissions', [AjaxController::class, 'getPermissions']);

        Route::get("/getPromocodes", [AjaxController::class, 'getPromocodes']);
        Route::get('/getTasks', [AjaxController::class, 'getTasks'])->middleware('check.permissions:task:viewAny');
        Route::get('/getClientTasks', [AjaxController::class, 'getClientTasks'])->middleware('check.permissions:task:viewAny');


        // Manual Payment Routes
        Route::get('/manual-payments', [ManualPaymentController::class, 'index'])->name('manual-payments.index');
        Route::post('/manual-payments/process', [ManualPaymentController::class, 'processPayments'])->name('manual-payments.process');
        Route::post('/manual-payments/reject', [ManualPaymentController::class, 'rejectPayments'])->name('manual-payments.reject');
        Route::post('/manual-payments/{id}/refresh-usd', [ManualPaymentController::class, 'refreshUsdValue'])->name('manual-payments.refresh-usd');
        Route::post('/manual-payments/{id}/notes', [ManualPaymentController::class, 'updateNotes'])->name('manual-payments.update-notes');
        Route::get('/manual-payments/{id}', [ManualPaymentController::class, 'show'])->name('manual-payments.show');

        //
        Route::get('/getPendingWalletWithdrawal2', [AjaxController::class, 'getPendingWalletWithdrawal2']);
        Route::get('/getPendingTradingDeposit2', [AjaxController::class, 'getPendingTradingDeposit2']);
        Route::get('/getPendingTradingWithdrawal2', [AjaxController::class, 'getPendingTradingWithdrawal2']);

        Route::get('/getPendingIbUsers2', [AjaxController::class, 'getPendingIbUsers2']);
        Route::post('/bulkIbApprove', [AjaxController::class, 'bulkIbApprove']);
        Route::get('/getIbUsers2', [AjaxController::class, 'getIbUsers2']);

        Route::get('/getComissionData2', [AjaxController::class, 'getComissionData2']);

        Route::get('/getBlockedIPs', [AjaxController::class, 'getBlockedIPs']);

        Route::get('/getWarningLogs', [WarningUserController::class, 'getWarningLogs'])->middleware('check.permissions:settings:warningUsers');

        Route::get('/getClientIbProfile', [AjaxController::class, 'getClientIbProfile']);
        Route::resource('groups', ProductsController::class);
        Route::resource('competitions', CompetitionProductController::class);

        Route::get('/competition/leaderboard', [Leaderboard::class, 'leaderboard'])->name('competition.leaderboard');
        Route::get('/competiton_dashboard', [Leaderboard::class, 'competiton_dashboard'])->name('competition.dashboard');
        Route::get('/requested_competition', [Leaderboard::class, 'requested_competition'])->name('competition.requested');
        // Route::get('/competitions', [Leaderboard::class, 'index'])->name('competition.create');
        Route::get('/competition/trader-data/{accountNo}/{start_date}/{end_date}', [Leaderboard::class, 'getTraderData'])->name('competition.trader-data');
        Route::get('/competition/export', [Leaderboard::class, 'exportLeaderboard'])->name('competition.export')->middleware('check.permissions:export:leaderboard');

        Route::post('competition/activate_competition', [Leaderboard::class, 'activateCompetition'])->name('competition.activate_competition');

        Route::post('/competition/send-reminder-email', [Leaderboard::class, 'sendReminderEmail'])->name('competition.sendReminderEmail');

        // Route::post('/two-factor/enable', [AdminTwoFactorAuthentication::class, 'enableTwoFactorAuthentication'])->name('two-factor.enable');
        // Route::delete('/two-factor/disable', [AdminTwoFactorAuthentication::class, 'disableTwoFactorAuthentication'])->name('two-factor.disable');
        // Route::post('/two-factor/confirm', [AdminTwoFactorAuthentication::class, 'confirmTwoFactorAuthentication'])->name('two-factor.confirm');
        // Route::post('/two-factor/recovery-codes', [AdminTwoFactorAuthentication::class, 'regenerateRecoveryCodes'])->name('two-factor.recovery-codes');

        Route::prefix('/2fa')->group(function () {
            Route::get('/', [TwoFactorAuthController::class, 'index'])->name('2fa.index')->middleware('check.permissions:setting:viewAny');
            Route::get('/status', [TwoFactorAuthController::class, 'getStatus'])->name('two-factor.status');
            Route::post('/enable', [TwoFactorAuthController::class, 'enableTwoFactorAuthentication'])->name('two-factor.enable');
            Route::delete('/disable', [TwoFactorAuthController::class, 'disableTwoFactorAuthentication'])->name('two-factor.disable');
            Route::post('/confirm', [TwoFactorAuthController::class, 'confirmTwoFactorAuthentication'])->name('two-factor.confirm');
            Route::post('/recovery-codes', [TwoFactorAuthController::class, 'regenerateRecoveryCodes'])->name('two-factor.recovery-codes');
            Route::get('/recovery-codes', [TwoFactorAuthController::class, 'showRecoveryCodes'])->name('two-factor.recovery-codes.show');
        });

        Route::get('/dashboard', [Dashboard::class, 'index'])->name('dashboard');
        Route::get('/transactions/wallet-deposit', [Transaction::class, 'wallet_deposit'])->name('transactions.wallet-deposit')
            ->middleware('check.permissions:wallet_deposit:viewAny');
        Route::get('/transactions/wallet-withdrawal', [Transaction::class, 'wallet_withdrawal'])->name('transactions.wallet-withdrawal')
            ->middleware('check.permissions:wallet_withdraw:viewAny');
        Route::get('/transactions/trading-deposit', [Transaction::class, 'trading_deposit'])->name('transactions.trading-deposit')
            ->middleware('check.permissions:trade_deposit:viewAny');
        Route::get('/transactions/trading-withdrawal', [Transaction::class, 'trading_withdrawal'])->name('transactions.trading-withdrawal')
            ->middleware('check.permissions:trade_withdrawals:viewAny');
        Route::get('/transactions/internal-transfer', [Transaction::class, 'internal_transfer'])->name('transactions.internal-transfer')
            ->middleware('check.permissions:internal_transfer:viewAny');
        // Route::get('/transactions/{id}', [Transaction::class, 'index'])->name('transactions')->middleware('check.permissions:wallet_deposit:viewAny');
        Route::get('/transactions/pending/wallet-deposit', [Transaction::class, 'pendingWalletDeposit'])->name('transactions.pending.wallet-deposit')
            ->middleware('check.permissions:wallet_deposit:viewAny');
        Route::get('/transactions/pending/wallet-withdrawal', [Transaction::class, 'pendingWalletWithdrawal'])->name('transactions.pending.wallet-withdrawal')
            ->middleware('check.permissions:wallet_withdraw:viewAny');
        Route::get('/transactions/pending/trading-deposit', [Transaction::class, 'pendingTradingDeposit'])->name('transactions.pending.trading-deposit')
            ->middleware('check.permissions:trade_deposit:viewAny');
        Route::get('/transactions/pending/trading-withdrawal', [Transaction::class, 'pendingTradingWithdrawal'])->name('transactions.pending.trading-withdrawal')
            ->middleware('check.permissions:trade_withdrawals:viewAny');

        // All Trades (server-side DataTable)
        Route::get('/trades', [\App\Http\Controllers\Admin\TradeController::class, 'index'])
            ->name('trades.index')
            ->middleware('check.permissions:trade_deposit:viewAny');
        Route::get('/trades/data', [\App\Http\Controllers\Admin\TradeController::class, 'getTradesData'])
            ->name('trades.data')
            ->middleware('check.permissions:trade_deposit:viewAny');
        Route::get('/trades/{trade}', [\App\Http\Controllers\Admin\TradeController::class, 'show'])
            ->name('trades.show')
            ->middleware('check.permissions:trade_deposit:viewAny');

        Route::get('/clients', [ClientController::class, 'index'])->name('clients.index')->middleware('check.permissions:client:viewAny');
        Route::get('/client_details/{userId}', [ClientController::class, 'clientDetails'])->name('admin-view-client-details')->middleware('check.permissions:client:view');
        Route::post('/updateIB', [ClientController::class, 'updateIB'])->name('updateIB');
        Route::post('/updateRM', [ClientController::class, 'updateRM'])->name('updateRM');
        Route::post('/addUser', [ClientController::class, 'addUser'])->name('addUser')->middleware("check.permissions:client:create");
        Route::post('/updateUser', [ClientController::class, 'updateUser'])->name('updateUser');
        Route::post('/sendPasswordResetLink', [ClientController::class, 'sendPasswordResetLink'])->name('sendPasswordResetLink');
        Route::post('/client/notes/store', [ClientController::class, 'storeNote'])->name('client.notes.store');
        Route::post('/client/remove-2fa', [ClientController::class, 'removeTwoFactor'])->name('client.remove-2fa');

        Route::get('/roles', [StaffManagement::class, 'roles'])->name('roles')->middleware('check.permissions:role:viewAny');
        Route::get('/rm_dashboard', [StaffManagement::class, 'rmDashboard'])->name('rm_dashboard');
        Route::post('/roles', [StaffManagement::class, 'addRole'])->name('roles.store');
        Route::post('/update_roles', [StaffManagement::class, 'updateRole'])->name('update_roles');
        Route::post('/update_role_status', [StaffManagement::class, 'updateRoleStatus'])->name('update_role_status');
        Route::post('/update_role_permissions', [StaffManagement::class, 'updateRolePermissions'])->name('update_role_permissions');
        Route::post('/save_user', [StaffManagement::class, 'saveUser'])->name('saveUser')->middleware('check.permissions:employee:update,employee:create');

        Route::get('/role_permissions', [StaffManagement::class, 'rolePermissions'])->name('role_permissions')->middleware('check.permissions:permission:update');
        Route::get('/admin_users', [StaffManagement::class, 'adminUsers'])->name('admin_users')->middleware('check.permissions:employee:viewAny');
        Route::get('/permissionsList', [StaffManagement::class, 'permissionsList'])->name('permissionsList');

        Route::post('/addTicket', [Ticket::class, 'addTicket'])->name('addTicket');
        Route::post('/assignTicket', [Ticket::class, 'assignTicket'])->name('assignTicket');
        Route::post('/updateStatus', [Ticket::class, 'updateStatus'])->name('updateStatus');
        Route::match(['get', 'post'], '/all_tickets', [Ticket::class, 'tickets'])->name('all_tickets');
        Route::match(['get', 'post'], '/open_tickets', [Ticket::class, 'tickets'])->name('open_tickets');
        Route::match(['get', 'post'], '/closed_tickets', [Ticket::class, 'tickets'])->name('closed_tickets');
        Route::get('/ticket_details', [Ticket::class, 'showDetails'])->name('ticket_details');
        Route::post('/ticket_details', [Ticket::class, 'addRemark'])->name('ticket_details_store');
        Route::get('/ticket_followups', [Tickets::class, 'fetchFollowups'])->name('ticket_followups');

        Route::post('/updateKyc', [Kyc::class, 'updateKyc'])->name('updateKyc');
        Route::get('/kyc-sync', [KycSyncController::class, 'index'])->name('kyc.sync.page')->middleware('check.permissions:settings:sumsub');

        // Admin KYC Sync Routes
        Route::post('/sync-user-kyc', [KycSyncController::class, 'syncUser'])->name('kyc.sync.user')->middleware('check.permissions:settings:sumsub');
        Route::post('/bulk-sync-kyc', [KycSyncController::class, 'bulkSync'])->name('kyc.sync.bulk');
        Route::get('/debug-applicant', [KycSyncController::class, 'debugApplicant'])->name('kyc.debug.applicant');

        Route::get('/wallet_deposit_details', [Transaction::class, 'wallet_deposit_details']);
        Route::get('/wallet_withdrawal_details', [Transaction::class, 'wallet_withdrawal_details']);
        Route::post('/wallet_withdrawal_details', [Transaction::class, 'update_wallet_withdrawal'])->name('wallet_withdrawal_details');
        Route::post('/manually_approve_withdrawal', [Transaction::class, 'manually_approve_withdrawal'])->name('manually_approve_withdrawal');
        Route::get('/trading_deposit_details', [Transaction::class, 'trading_deposit_details']);
        Route::get('/trading_withdrawal_details', [Transaction::class, 'trading_withdrawal_details']);
        Route::post('/trading_withdrawal_details', [Transaction::class, 'update_trading_withdrawal']);
        Route::post('/update_wallet_withdraw_amount', [Transaction::class, 'walletWithdrawalAmountUpdate'])->name('update_wallet_withdraw_amount');
        Route::post('/update_trade_account_withdraw_amount', [Transaction::class, 'tradeAccountWithdrawalAmountUpdate'])->name('update_trade_account_withdraw_amount');

        Route::prefix('/clientAccounts')->group(function () {
            Route::get("/liveAccounts", [ClientAccController::class, 'live_accounts'])->name('liveAccounts')->middleware('check.permissions:account:viewLiveAccounts');
            Route::get("/demoAccounts", [ClientAccController::class, 'demo_accounts'])->name('demoAccounts')->middleware('check.permissions:account:viewDemoAccounts');
            Route::get("/requestedAccounts", [ClientAccController::class, 'requested_accounts'])->name('requestedAccounts')->middleware('check.permissions:account:viewRequestedAccounts');
            Route::post("/deleteAccounts", [MT5Accounts::class, 'deleteAccounts'])->name('deleteAccounts')->middleware('check.permissions:account:viewLiveAccounts');
            Route::post('/activate_account', [MT5Accounts::class, 'activateAccount'])->name('activate_account');
            Route::post('/bulk_activate_account', [MT5Accounts::class, 'bulkActivateAccount'])->name('bulk_activate_account');
        });

        Route::get('/2fa-settings', [SettingsController::class, 'twoFactorAuthenticationAdmin'])->name("2fa-settings")->middleware('check.permissions:setting:viewAny');

        Route::prefix('/ui_settings')->group(function () {
            Route::get('/', [SettingsController::class, 'index'])->name("ui-settings.view")->middleware('check.permissions:settings:uiSettings');
            Route::post('/', [SettingsController::class, 'store'])->name('ui-settings.update')->middleware('check.permissions:settings:uiSettings');
        });

        Route::prefix('/review-popup-settings')->group(function () {
            Route::get('/', [SettingsController::class, 'reviewPopupSettings'])->name('review-popup-settings.view')->middleware('check.permissions:settings:reviewPopup');
            Route::post('/', [SettingsController::class, 'updateReviewPopupSettings'])->name('review-popup-settings.update')->middleware('check.permissions:settings:reviewPopup');
        });

        Route::post('/payment_gateways/update', [SettingsController::class, 'updatePaymentGateways'])
            ->name('payment-gateways.update')
            ->middleware('check.permissions:setting:update');

        Route::post('/toggle_group_code/update', [SettingsController::class, 'toggleGroupCode'])
            ->name('toggle_group_code.update')
            ->middleware('check.permissions:setting:update');

        Route::post('/toggle_ib_approve_request', [SettingsController::class, 'toggleIbApproveRequest'])
            ->name('toggle_ib_approve_request')
            ->middleware('check.permissions:setting:update');

        Route::post('/kyc-provider/update', [SettingsController::class, 'updateKycProvider'])
            ->name('kyc-provider.update')
            ->middleware('check.permissions:setting:update');

        Route::prefix('/logs')->group(function () {
            Route::get('/', [SettingsController::class, 'logs'])->name("logs.view")->middleware('check.permissions:settings:logs');
        });

        Route::prefix('/update_password')->group(function () {
            Route::get('/', [SettingsController::class, 'update_password'])->name('update_password')->middleware('check.permissions:settings:updatePassword');
            Route::post('/', [SettingsController::class, 'store_password'])->name('update_password.store')->middleware('check.permissions:settings:updatePassword');;
        });
        Route::prefix('/api-token')->group(function () {
            Route::get('/', [SettingsController::class, 'create_apitoken'])->name('apitoken.create')->middleware('check.permissions:settings:apiToken');
            Route::post('/', [SettingsController::class, 'store_apitoken'])->name('apitoken.store')->middleware(['check.permissions:settings:apiToken']);
            Route::delete('/apitoken/{id}', [SettingsController::class, 'destroy_apitoken'])->name('apitoken.destroy')->middleware(['check.permissions:settings:apiToken']);
        });

        Route::get('/email_broadcast', [SettingsController::class, 'email_broadcast'])->name('emailbroadcast')->middleware('check.permissions:settings:emailBroadcasting');
        Route::post('/email_broadcast', [SettingsController::class, 'send_email_broadcast'])->name('send_emailbroadcast')->middleware('check.permissions:settings:emailBroadcasting');

        // Maintenance Email
        Route::get('/maintenance-email', [\App\Http\Controllers\Admin\MaintenanceEmailController::class, 'index'])->name('maintenance.index')->middleware('check.permissions:setting:update');
        Route::get('/maintenance-email/fetch', [\App\Http\Controllers\Admin\MaintenanceEmailController::class, 'fetchEmails'])->name('maintenance.fetch')->middleware('check.permissions:setting:update');
        Route::get('/maintenance-email/preview', [\App\Http\Controllers\Admin\MaintenanceEmailController::class, 'previewEmail'])->name('maintenance.preview')->middleware('check.permissions:setting:update');
        Route::post('/maintenance-email/send', [\App\Http\Controllers\Admin\MaintenanceEmailController::class, 'sendEmails'])->name('maintenance.send')->middleware('check.permissions:setting:update');

//        // Warning Users
        Route::get('/warning-users', [WarningUserController::class, 'index'])->name('warning_users')->middleware('check.permissions:settings:warningUsers');
        Route::post('/warning-users/send', [WarningUserController::class, 'sendWarnings'])->name('send_warnings')->middleware('check.permissions:settings:warningUsers');
        Route::get('/warning-users/logs', [WarningUserController::class, 'getWarningLogs'])->name('get_warning_logs')->middleware('check.permissions:settings:warningUsers');

        // Account Termination Email Preview
//        Route::get('/account-termination-email/preview', [\App\Http\Controllers\Admin\MaintenanceEmailController::class, 'previewAccountTerminationEmail'])->name('account-termination.preview')->middleware('check.permissions:setting:update');

        // Account Review Email Preview
//        Route::get('/account-review-email/preview', [\App\Http\Controllers\Admin\MaintenanceEmailController::class, 'previewAccountReviewEmail'])->name('account-review.preview')->middleware('check.permissions:setting:update');


        Route::get('/ip_ban', [SettingsController::class, 'ip_ban'])->name('ip_ban')->middleware('check.permissions:settings:banIps');
        Route::post('/send_ip_ban_reason', [SettingsController::class, 'send_ip_ban_reason'])->name('send_ip_ban_reason')->middleware('check.permissions:settings:banIps');
        Route::get('/delete_ip_ban', [SettingsController::class, 'delete_ip_ban'])->name('delete_ip_ban')->middleware('check.permissions:settings:banIps');

        Route::get("/ibdashboard", [IBController::class, 'index'])->name('ib.dashboard')->middleware('check.permissions:ib:viewAny');
        Route::get("/iblist", [IBController::class, 'list'])->name('ib.list')->middleware('check.permissions:ib:manageRequests');;
        Route::get("/iblist_active", [IBController::class, 'list_active'])->name('ib.active.list')->middleware('check.permissions:ib:viewAny');;;
        Route::get("/ib_settings", [IBController::class, 'ib_settings'])->name('ib.settings')->middleware('check.permissions:ib:manageSettings');
        Route::get("/ibCommission", [IBController::class, 'ibCommission']);
        Route::post("/ibCommission", [IBController::class, 'updateIbPlan']);
        Route::match(['GET', 'POST'], '/export-all-ib-users', [IBController::class, 'exportAllIbUsers'])->name('admin.ib.export')->middleware('check.permissions:export:ib_list_active,export:ib_dashboard');
        Route::get('/download-export/{file}/{token}', [IBController::class, 'downloadExport'])->name('admin.download.export');
        Route::get("/ibCommissionEdit/{planId}/{accType}", [IBController::class, 'ibCommissionEdit']);
        Route::post("/ibCommissionEdit/{planId}/{accType}", [IBController::class, 'ibCommissionEdit']);

        // IB Commission Analysis
        Route::get('/ib-commission-analysis', [IbCommissionAnalysisController::class, 'index'])->name('ib.commission-analysis')->middleware('check.permissions:ib:viewAny');
        Route::post('/ib-commission-analysis/start', [IbCommissionAnalysisController::class, 'startAnalysis'])->name('ib.commission-analysis.start')->middleware('check.permissions:ib:viewAny');
        Route::get('/ib-commission-analysis/status', [IbCommissionAnalysisController::class, 'getStatus'])->name('ib.commission-analysis.status')->middleware('check.permissions:ib:viewAny');
        Route::get('/ib-commission-analysis/table-data', [IbCommissionAnalysisController::class, 'getTableData'])->name('ib.commission-analysis.table-data')->middleware('check.permissions:ib:viewAny');
        Route::post('/ib-commission-analysis/fix-duplicates', [IbCommissionAnalysisController::class, 'fixDuplicateWallets'])->name('ib.commission-analysis.fix-duplicates')->middleware('check.permissions:ib:manageSettings');
        Route::post('/ib-commission-analysis/fix-duplicate-commissions', [IbCommissionAnalysisController::class, 'fixDuplicateCommissions'])->name('ib.commission-analysis.fix-duplicate-commissions')->middleware('check.permissions:ib:manageSettings');
        Route::post('/ib-commission-analysis/process-stuck', [IbCommissionAnalysisController::class, 'processStuckCommissions'])->name('ib.commission-analysis.process-stuck')->middleware('check.permissions:ib:manageSettings');
        Route::get('/ib-commission-analysis/stuck-status', [IbCommissionAnalysisController::class, 'getStuckProcessStatus'])->name('ib.commission-analysis.stuck-status')->middleware('check.permissions:ib:viewAny');

        // Overpayment fix endpoints
        Route::get('/ib-commission-analysis/fixable-duplicates', [IbCommissionAnalysisController::class, 'getFixableDuplicates'])->name('ib.commission-analysis.fixable-duplicates')->middleware('check.permissions:ib:viewAny');
        Route::get('/ib-commission-analysis/fixable-entries', [IbCommissionAnalysisController::class, 'getFixableEntries'])->name('ib.commission-analysis.fixable-entries')->middleware('check.permissions:ib:viewAny');
        Route::get('/ib-commission-analysis/commission-timeline', [IbCommissionAnalysisController::class, 'getCommissionTimeline'])->name('ib.commission-analysis.commission-timeline')->middleware('check.permissions:ib:viewAny');
        Route::post('/ib-commission-analysis/fix-overpaid', [IbCommissionAnalysisController::class, 'fixOverpaidCommissions'])->name('ib.commission-analysis.fix-overpaid')->middleware('check.permissions:ib:manageSettings');

        // IB Withdrawal tracking
        Route::get('/ib-withdrawals', [IbWithdrawalController::class, 'index'])->name('ib.withdrawals.index')->middleware('check.permissions:ib:viewAny');
        Route::get('/ib-withdrawals/{id}', [IbWithdrawalController::class, 'show'])->name('ib.withdrawals.show')->middleware('check.permissions:ib:viewAny');
        Route::get('/ib-withdrawals/{id}/overpaid-details', [IbWithdrawalController::class, 'getOverpaidDetails'])->name('ib.withdrawals.overpaid-details')->middleware('check.permissions:ib:viewAny');

        Route::get("/mt5_groups", [MT5Controller::class, 'index']);

        Route::get("/promocode", [MT5Controller::class, 'promocode']);

        Route::post('/get_promocode/{id}', [MT5Controller::class, 'get_promocode'])->name('get_promocode');
        Route::post('/edit/promocode', [MT5Controller::class, 'edit_promocode'])->name('edit_promocode');

        Route::post("/create/promocode", [MT5Controller::class, 'createPromoCode']);
        Route::post("/update_promocode_status", [MT5Controller::class, 'update_promocode_status']);
        Route::post("/delete_promocode", [MT5Controller::class, 'delete_promocode']);

        Route::get("/view_account_details/{accountId}", [MT5Controller::class, 'view'])->where('account', '.*')->name('admin-view-account-details');
        Route::post("/updatePassword", [MT5Controller::class, 'updatePassword'])->name('updatePassword');
        Route::post("/updateAccountDetails", [MT5Controller::class, 'updateAccountDetails'])->name('updateAccountDetails');
        Route::post("/depositToAccount", [MT5Controller::class, 'depositToAccount'])->name('depositToAccount')->middleware('check.permissions:trade_deposit:create');
        Route::post("/deleteAccount", [MT5Controller::class, 'deleteAccount'])->name('deleteAccount')->middleware('check.permissions:account:delete');
        Route::post("/softDeleteAccount", [MT5Controller::class, 'softDeleteAccount'])->name('softDeleteAccount')->middleware('check.permissions:account:delete');
        Route::post("/restoreAccount", [MT5Controller::class, 'restoreAccount'])->name('restoreAccount')->middleware('check.permissions:account:delete');
        Route::post('/archiveAccount', [MT5Controller::class, 'archiveAccount'])->name('archiveAccount')->middleware('check.permissions:account:delete');
        Route::post("/depositToCellexpertAccount", [MT5Controller::class, 'depositToCellexpertAccount'])->name('depositToCellexpertAccount')->middleware('check.permissions:trade_deposit:create');
        Route::post("/withdrawFromAccount", [MT5Controller::class, 'withdrawFromAccount'])->name('withdrawFromAccount')->middleware('check.permissions:trade_withdrawals:create');
        Route::post("/withdrawFromCellexpertAccount", [MT5Controller::class, 'withdrawFromCellexpertAccount'])->name('withdrawFromCellexpertAccount')->middleware('check.permissions:trade_withdrawals:create');
        Route::post("/bonusToAccount", [MT5Controller::class, 'bonusToAccount'])->name('bonusToAccount')->middleware('check.permissions:bonus_transaction:create');
        Route::post("/creditBonusToAccount", [MT5Controller::class, 'creditBonusToAccount'])->name('creditBonusToAccount')->middleware('check.permissions:bonus_transaction:create');

        Route::get("/search", [SearchController::class, 'index']);
        Route::resource('permissions', PermissionController::class);
        // Route::get("/roles-permissions", [SearchController::class, 'index']);
        Route::get("/sendMarketEmail", [Dashboard::class, 'sendMarketingEmail']);

        // Tasks Section
        Route::prefix('/tasks')->name('tasks.')->group(function () {
            Route::get('/', [TaskController::class, 'index'])->name('index')->middleware('check.permissions:task:viewAny');
            Route::get('/client_tasks', [TaskController::class, 'client_tasks'])->name('client_tasks')->middleware('check.permissions:clientTask:viewAny');
            Route::post('/store', [TaskController::class, 'store'])->name('store')->middleware('check.permissions:task:viewAny');
//            Route::put('/edit', [TaskController::class, 'edit'])->name('edit')->middleware('check.permissions:task:viewAny');
            Route::put('/{task}', [TaskController::class, 'update'])->name('update')->middleware('check.permissions:task:viewAny');
            // Route::put('/approve_reject', [TaskController::class, 'approve_reject'])->name('approve_reject');
            Route::post('/approve_reject', [TaskController::class, 'approve_reject'])->name('approve_reject')->middleware('check.permissions:task:viewAny');
            Route::put('/{task}', [TaskController::class, 'update'])->name('update')->middleware('check.permissions:task:viewAny');
            Route::delete('/{task}', [TaskController::class, 'destroy'])->name('destroy')->middleware('check.permissions:task:viewAny');
        });

        Route::prefix('/learn-content')->name('learn-content.')->group(function () {
            Route::get('/', [LearnContentController::class, 'index'])->name('index');

            Route::post('/sections', [LearnContentController::class, 'storeSection'])->name('sections.store');
            Route::put('/sections/{learnSection}', [LearnContentController::class, 'updateSection'])->name('sections.update');
            Route::delete('/sections/{learnSection}', [LearnContentController::class, 'destroySection'])->name('sections.destroy');

            Route::post('/videos', [LearnContentController::class, 'storeVideo'])->name('videos.store');
            Route::put('/videos/{learnVideo}', [LearnContentController::class, 'updateVideo'])->name('videos.update');
            Route::delete('/videos/{learnVideo}', [LearnContentController::class, 'destroyVideo'])->name('videos.destroy');
        });

        // Sync Monitor Dashboard Routes
        Route::prefix('sync-monitor')->name('sync-monitor.')->group(function () {
            Route::get('/dashboard', [App\Http\Controllers\Admin\SyncMonitorController::class, 'dashboard'])->name('dashboard');
            Route::get('/data', [App\Http\Controllers\Admin\SyncMonitorController::class, 'getSyncData'])->name('data');
            Route::get('/accounts/{account}/details', [App\Http\Controllers\Admin\SyncMonitorController::class, 'accountDetails'])->name('account.details');
            Route::post('/accounts/{account}/clear-cache', [App\Http\Controllers\Admin\SyncMonitorController::class, 'clearAccountCache'])->name('account.clear-cache');
            Route::post('/accounts/{account}/unflag', [App\Http\Controllers\Admin\SyncMonitorController::class, 'unflagAccount'])->name('account.unflag');
        });

        // Account Details with MT5 API Integration
        Route::prefix('accounts')->name('accounts.')->group(function () {
            Route::get('/{account}', [App\Http\Controllers\Admin\AccountDetailsController::class, 'show'])->name('details');
            Route::get('/{account}/current-balance', [App\Http\Controllers\Admin\AccountDetailsController::class, 'getCurrentBalance'])->name('current-balance');
            Route::get('/{account}/current-positions', [App\Http\Controllers\Admin\AccountDetailsController::class, 'getCurrentPositions'])->name('current-positions');
            Route::get('/{account}/recent-trade-stats', [App\Http\Controllers\Admin\AccountDetailsController::class, 'getRecentTradeStats'])->name('recent-trade-stats');
        });

        // Admin Blog Routes
        Route::resource('blog', \App\Http\Controllers\Admin\BlogPostController::class)->names([
            'index' => 'blog.index',
            'create' => 'blog.create',
            'store' => 'blog.store',
            'show' => 'blog.show',
            'edit' => 'blog.edit',
            'update' => 'blog.update',
            'destroy' => 'blog.destroy',
        ]);
        // Affiliate Management Routes
        Route::prefix('affiliates')->name('affiliates.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\AffiliateController::class, 'index'])->name('index');
            Route::get('/data', [\App\Http\Controllers\Admin\AffiliateController::class, 'getAffiliates'])->name('data');
            Route::get('/import', [\App\Http\Controllers\Admin\AffiliateController::class, 'importForm'])->name('import.form');
            Route::post('/import', [\App\Http\Controllers\Admin\AffiliateController::class, 'import'])->name('import');
            Route::get('/export', [\App\Http\Controllers\Admin\AffiliateController::class, 'export'])->name('export')->middleware('check.permissions:export:affiliates');
            Route::get('/sample', [\App\Http\Controllers\Admin\AffiliateController::class, 'downloadSample'])->name('sample');
            Route::get('/{id}', [\App\Http\Controllers\Admin\AffiliateController::class, 'show'])->name('show');
            Route::post('/{id}/status', [\App\Http\Controllers\Admin\AffiliateController::class, 'updateStatus'])->name('update.status');
            Route::delete('/{id}', [\App\Http\Controllers\Admin\AffiliateController::class, 'destroy'])->name('destroy');
        });

        // Login History Routes
        Route::prefix('login-history')->name('login-history.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\LoginHistoryController::class, 'index'])->name('index');
            Route::get('/data', [\App\Http\Controllers\Admin\LoginHistoryController::class, 'getLoginHistory'])->name('data');
            Route::get('/export', [\App\Http\Controllers\Admin\LoginHistoryController::class, 'export'])->name('export')->middleware('check.permissions:export:login_history');
        });

        // Inactive Users Routes
        Route::prefix('inactive-users')->name('inactive-users.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\InactiveUsersController::class, 'index'])->name('index');
            Route::get('/data', [\App\Http\Controllers\Admin\InactiveUsersController::class, 'getInactiveUsers'])->name('data');
        });


    });


});
// Test route for affiliate reference code functionality
Route::get('/test-affiliate', function (Request $request) {
    $output = [
        'url_param_cxd' => $request->get('cxd'),
        'cookie_cxd' => $request->cookie('cxd'),
        'has_cookie' => $request->hasCookie('cxd'),
        'all_cookies' => $request->cookies->all(),
    ];

    return response()->json($output);
});

// MT5 Redis Coordination Demo Routes
Route::prefix('mt5-redis-demo')->group(function () {
    Route::get('/status', [MT5RedisCoordinationDemoController::class, 'status'])
        ->name('mt5.redis.demo.status');

    Route::get('/balance', [MT5RedisCoordinationDemoController::class, 'checkBalance'])
        ->name('mt5.redis.demo.balance');

    Route::get('/trades', [MT5RedisCoordinationDemoController::class, 'tradeHistory'])
        ->name('mt5.redis.demo.trades');

    Route::post('/cleanup', [MT5RedisCoordinationDemoController::class, 'cleanupStaleProcesses'])
        ->name('mt5.redis.demo.cleanup');

    Route::post('/switch-mode', [MT5RedisCoordinationDemoController::class, 'switchMode'])
        ->name('mt5.redis.demo.switch');

    Route::get('/stress-test', [MT5RedisCoordinationDemoController::class, 'stressTest'])
        ->name('mt5.redis.demo.stress');

    Route::get('/dispatch-jobs', function () {
        $count = request('count', 3);
        $login = request('login', 12345);

        $jobs = \App\Jobs\MT5RedisCoordinationDemoJob::dispatchDemoJobs($count, $login);

        return response()->json([
            'success' => true,
            'jobs_dispatched' => count($jobs),
            'jobs' => $jobs,
            'message' => "Dispatched {$count} queue jobs that will coordinate through Redis with HTTP requests"
        ]);
    })->name('mt5.redis.demo.jobs');
});

// Zapier Webhook Routes (API)
Route::prefix('api/zapier')->name('api.zapier.')->group(function () {
    Route::post('/create-account', [ZapierWebhookController::class, 'createAccount'])->name('create-account');
    Route::get('/health', [ZapierWebhookController::class, 'healthCheck'])->name('health-check');
});

// Admin Zapier Accounts Routes
Route::prefix('/admin/zapier-accounts')->name('admin.zapier-accounts.')->middleware(['is_admin'])->group(function () {
    Route::get('/', [ZapierAccountsController::class, 'index'])->name('index');
    Route::get('/data', [ZapierAccountsController::class, 'getData'])->name('data');
    Route::get('/export', [ZapierAccountsController::class, 'export'])->name('export');
    Route::get('/stats', [ZapierAccountsController::class, 'getStats'])->name('stats');
    Route::post('/resend', [ZapierAccountsController::class, 'resendEmail'])->name('resend');
    Route::post('/delete', [ZapierAccountsController::class, 'deleteUser'])->name('delete');
});
