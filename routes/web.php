<?php

use App\Models\Account;
use App\Http\Controllers\Ib;
use App\Models\TotalBalance;
use App\Models\WalletDeposit;
use App\Http\Controllers\Home;
use App\Http\Controllers\Users;
use App\Http\Controllers\Wallet;
use App\Models\TradeWithdrawals;
use App\Http\Controllers\Payment;
use App\Http\Controllers\Tickets;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Admin\Kyc;
use App\Http\Controllers\Admin\Login;
use App\Http\Controllers\MT5Accounts;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\Ticket;
use App\Http\Controllers\Transactions;
use App\Http\Controllers\Admin\Dashboard;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\TradeWithdrawal;
use App\Http\Controllers\InternalTransfer;
use App\Http\Controllers\Admin\Transaction;
use App\Http\Controllers\Admin\IBController;
use App\Http\Controllers\Admin\MT5Controller;
use App\Http\Controllers\Admin\AjaxController;
use App\Http\Controllers\Admin\StaffManagement;
use App\Http\Controllers\Admin\ClientController;
use App\Http\Controllers\Admin\SearchController;
use App\Http\Controllers\TradeDepositController;
use App\Http\Controllers\Admin\ApiAjaxController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\ClientAccController;
use App\Http\Controllers\PaymentCallbackController;
Route::get("/se",function(){
//     // Cache::put('test-key', 'test-value', 1000);
// $value = Cache::get('test-key');
// dd($value); // Should output 'test-value'
    // $settings = DB::table('page_categories')->get()->toArray();
    // file_put_contents('page_categories.json', json_encode($settings, JSON_PRETTY_PRINT));
    // $settings = DB::table('pages')->get()->toArray();
    // file_put_contents('pages.json', json_encode($settings, JSON_PRETTY_PRINT));
    // $settings = DB::table('countries')->get()->toArray();
    // file_put_contents('countries.json', json_encode($settings, JSON_PRETTY_PRINT));
    // $settings = DB::table('account_types')->get()->toArray();
    // file_put_contents('account_types.json', json_encode($settings, JSON_PRETTY_PRINT));
    // $settings = DB::table('mt5_groups')->get()->toArray();
    // file_put_contents('mt5_groups.json', json_encode($settings, JSON_PRETTY_PRINT));
    // $settings = DB::table('mt5_group_categories')->get()->toArray();
    // file_put_contents('mt5_group_categories.json', json_encode($settings, JSON_PRETTY_PRINT));
    // $settings = DB::table('leverage')->get()->toArray();
    // file_put_contents('leverage.json', json_encode($settings, JSON_PRETTY_PRINT));
    // $settings = DB::table('client_wallets')->get()->toArray();
    // file_put_contents(storage_path('app/client_wallets.json'), json_encode($settings, JSON_PRETTY_PRINT));
    // TradeWithdrawals::create([
    //     'user_id' => $user_id,
    //     'account_id' => $account->id,
    //     'withdrawal_amount' => $amount,
    //     'withdraw_type' => $withdraw_type,
    //     // 'withdraw_to' => $to_account_id,
    //     'wallet_qr' => '',
    //     'Status' => 1
    // ]);
    // $settings = \App\Models\TradeWithdrawals::get()->toArray();
    // file_put_contents(storage_path('app/trade_withdrawals.json'), json_encode($settings, JSON_PRETTY_PRINT));

    // $settings = \App\Models\TotalBalance::get()->toArray();
    // file_put_contents(storage_path('app/total_balance.json'), json_encode($settings, JSON_PRETTY_PRINT));

    // $settings = \App\Models\WalletDeposit::get()->toArray();
    // file_put_contents(storage_path('app/wallet_deposit.json'), json_encode($settings, JSON_PRETTY_PRINT));
    // $settings = \App\Models\Account::get()->toArray();
    // file_put_contents(storage_path('app/accounts.json'), json_encode($settings, JSON_PRETTY_PRINT));
});
Route::post('/paymentcallback', [PaymentCallbackController::class, 'handleCallback'])->name('paymentcallback');
Route::get('/', [LoginController::class, 'showLoginForm'])->name('login_index');
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::get('/forgot-password', [LoginController::class, 'forgot_password']);
Route::post('/forgot-password', [LoginController::class, 'sendResetLink']);
Route::get('/register', [LoginController::class, 'register'])->name('register');


Route::post('/register', [LoginController::class, 'addUser']);
Route::get('/email_verify', [LoginController::class, 'verifyEmail']);
Route::get('/reset-password', [LoginController::class, 'resetPassword']);
Route::post('/reset-password', [LoginController::class, 'resetPassword']);
Route::get('/ib-ref', [Ib::class, 'ibReference'])->name('ib-ref');
Route::post('/ib-ref', [LoginController::class, 'addUser'])->name('ib-ref-post');

Route::get('/logout', [LoginController::class, 'logout'])->name('logout');

Route::middleware(['auth'])->group(function () {
    // Route::get('/', [Home::class, 'dashboard'])->name('dashboardIndex');
    Route::get('dashboard', [Home::class, 'dashboard'])->name('dashboard');
    Route::get('/view_account_details', [MT5Accounts::class, 'viewAccountDetails'])->name('view_account_details');
    Route::get('/select_account_deposit', [MT5Accounts::class, 'select_account_deposit'])->name('select_account_deposit');

    Route::get('/wallet', [Wallet::class, 'index'])->name('wallet');
    Route::get('/transactions', [Transactions::class, 'index'])->name('transactions');

    Route::get('/liveAccounts', [MT5Accounts::class, 'liveAccounts'])->name('liveAccounts');
    Route::get('/demoAccounts', [MT5Accounts::class, 'demoAccounts'])->name('demoAccounts');
    Route::get('/view-account-details/{account}', [MT5Accounts::class, 'viewAccountDetails'])->where('account', '.*')->name('view-account-details');
    Route::get('/createLiveAccount', [MT5Accounts::class, 'showLiveAccountForm'])->name('show-live-account-form');
    Route::post('/createLiveAccount', [MT5Accounts::class, 'createLiveAccount'])->name('create-live-account');
    Route::get('/createDemoAccount', [MT5Accounts::class, 'showDemoAccountForm'])->name('show-demo-account-form');
    Route::post('/createDemoAccount', [MT5Accounts::class, 'createDemoAccount'])->name('create-demo-account');
    Route::post('/view-account-details/{account}', [MT5Accounts::class, 'changeMt5Password'])->where('account', '.*')->name('change-mt5-password');

    Route::get('/getLeverage', [MT5Accounts::class, 'getLeverage'])->name('get-leverage');

    Route::get('/support', [Tickets::class, 'index'])->name('supports');
    Route::post('/support', [Tickets::class, 'createTicket'])->name('support');
    Route::get('/ticket_details', [Tickets::class, 'showDetails'])->name('ticket_details');
    Route::post('/ticket_details', [Tickets::class, 'addRemark'])->name('ticket_details_store');
    Route::get('/ticket_followups', [Tickets::class, 'fetchFollowups'])->name('ticket_followups');

    Route::get('/ib-profile', [Ib::class, 'ib_profile'])->name('ib-profile');
    Route::get('/ib', [Ib::class, 'index'])->name('ib');
    Route::post('/ib-profile', [Ib::class, 'processTransfer'])->name('ib-profile-store');
    Route::post('/ib-enroll', [Ib::class, 'ibEnroll'])->name('ib-enroll');
    Route::post('/ib-update-referral', [Ib::class, 'ibUpdateReferral'])->name('ib-update-referral');

    Route::get('/user-profile', [Users::class, 'profile'])->name('user-profile');
    Route::get('/sumsub', [Users::class, 'sumsub'])->name('sumsub');
    Route::post('/sumsub_verify', [Users::class, 'sumsub_verify'])->name('sumsub_verify');

    Route::post('/wallet/store', [Wallet::class, 'storeClientWallet'])->name('wallet.store');
    Route::post('/wallet/updateStatus', [Wallet::class, 'updateStatus'])->name('wallet.updateStatus');
    Route::get('/wallet_deposit', [Wallet::class, 'showDepositForm'])->name('wallet_deposit');
    Route::get('/wallet_withdrawal', [Wallet::class, 'showWithdrawalForm'])->name('wallet_withdrawal');
    Route::post('/wallet_deposit', [Wallet::class, 'deposit'])->name('wallet_deposit_store');
    Route::post('/wallet_withdrawal', [Wallet::class, 'withdrawal'])->name('wallet_withdrawal_store');
    Route::post('/wallet_payment', [Wallet::class, 'processPayment'])->name('wallet_payment');
    Route::get('/payment-response', [Payment::class, 'handlePaymentResponse'])->name('handlePaymentResponse');
    Route::post('/change_password', [Users::class, 'changePassword'])->name('password.change');
    Route::post('/change_profileimage', [Users::class, 'changeProfileImage'])->name('profileimage.change');
    Route::get('/trade-deposit', [TradeDepositController::class, 'index'])->name('trade-deposit');
    Route::post('/trade-deposit', [TradeDepositController::class, 'deposit'])->name('trade-deposit_store');
    Route::get('/trade-withdrawal', [TradeWithdrawal::class, 'index'])->name('trade-withdrawal');
    Route::post('/trade-withdrawal', [TradeWithdrawal::class, 'withdraw'])->name('trade-withdrawal_store');
    Route::get('/internal-transfer', [InternalTransfer::class, 'index'])->name('internal-transfer');
    Route::post('/process-transfer', [InternalTransfer::class, 'processTransfer'])->name('process-transfer_store');
});
Route::post('/cryptochill/callback', [Wallet::class, 'secureProcessPayment'])->name('secure_wallet_payment');
Route::prefix("/admin")->name("admin.")->group(function () {

    Route::get('/memory-limit', function () {

        return ini_get('memory_limit');
    });
    Route::get('/', [Login::class, 'showLoginForm']);
    Route::post('/', [Login::class, 'adminLogin']);
    Route::get('/login', [Login::class, 'showLoginForm'])->name('login');
    Route::post('/login', [Login::class, 'adminLogin']);
    Route::get('/ajax', [AjaxController::class, 'index']);
    Route::get('/getClientList', [AjaxController::class, 'getClientList']);
    Route::get('/getLiveAccountsList', [AjaxController::class, 'getLiveAccountsList']);
    Route::post('/ajax', [AjaxController::class, 'index']);
    Route::get('/api/ajax', [ApiAjaxController::class, 'handleRequest']);
    Route::post('/api/ajax', [ApiAjaxController::class, 'handleRequest']);
    Route::get('/logout', [Login::class, 'logout'])->name('logout');
    Route::middleware(['is_admin', 'check.permissions'])->group(function () {
        Route::get('/dashboard', [Dashboard::class, 'index'])->name('dashboard');
        Route::get('/transactions/{id}', [Transaction::class, 'index']);
        Route::get('/transactions/pending/{id}', [Transaction::class, 'pending']);

        Route::get('/client_list', [ClientController::class, 'index'])->name('client_list');
        Route::get('/client_details/{userId}', [ClientController::class, 'clientDetails'])->name('admin-view-client-details');
        Route::post('/updateIB', [ClientController::class, 'updateIB'])->name('updateIB');
        Route::post('/updateRM', [ClientController::class, 'updateRM'])->name('updateRM');
        Route::post('/addUser', [ClientController::class, 'addUser'])->name('addUser');
        Route::post('/updateUser', [ClientController::class, 'updateUser'])->name('updateUser');
        Route::post('/sendPasswordResetLink', [ClientController::class, 'sendPasswordResetLink'])->name('sendPasswordResetLink');

        Route::get('/roles', [StaffManagement::class, 'roles']);
        Route::get('/rm_dashboard', [StaffManagement::class, 'rmDashboard'])->name('rm_dashboard');
        Route::post('/roles', [StaffManagement::class, 'addRole'])->name('roles');
        Route::post('/update_roles', [StaffManagement::class, 'updateRole'])->name('update_roles');
        Route::post('/update_role_status', [StaffManagement::class, 'updateRoleStatus'])->name('update_role_status');
        Route::post('/update_role_permissions', [StaffManagement::class, 'updateRolePermissions'])->name('update_role_permissions');
        Route::post('/save_user', [StaffManagement::class, 'saveUser'])->name('saveUser');

        Route::get('/role_permissions', [StaffManagement::class, 'rolePermissions']);
        Route::get('/admin_users', [StaffManagement::class, 'adminUsers']);
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

        Route::get('/wallet_deposit_details', [Transaction::class, 'wallet_deposit_details']);
        Route::get('/wallet_withdrawal_details', [Transaction::class, 'wallet_withdrawal_details']);
        Route::post('/wallet_withdrawal_details', [Transaction::class, 'update_wallet_withdrawal'])->name('wallet_withdrawal_details');
        Route::get('/trading_deposit_details', [Transaction::class, 'trading_deposit_details']);
        Route::get('/trading_withdrawal_details', [Transaction::class, 'trading_withdrawal_details']);
        Route::post('/trading_withdrawal_details', [Transaction::class, 'update_trading_withdrawal']);

        Route::prefix('/clientAccounts')->group(function () {
            Route::get("/liveAccounts", [ClientAccController::class, 'live_accounts']);
            Route::get("/demoAccounts", [ClientAccController::class, 'demo_accounts']);
        });

        Route::prefix('/ui_settings')->group(function () {
            Route::get('/', [SettingsController::class, 'index']);
            Route::post('/', [SettingsController::class, 'store']);
        });
        Route::prefix('/update_password')->group(function () {
            Route::get('/', [SettingsController::class, 'update_password']);
            Route::post('/', [SettingsController::class, 'store_password'])->name('update_password');
        });

        Route::get("/ibdashboard", [IBController::class, 'index']);
        Route::get("/iblist", [IBController::class, 'list']);
        Route::get("/iblist_active", [IBController::class, 'list_active']);
        Route::get("/ib_settings", [IBController::class, 'ib_settings']);
        Route::get("/ibCommission", [IBController::class, 'ibCommission']);
        Route::post("/ibCommission", [IBController::class, 'updateIbPlan']);
        Route::get("/ibCommissionEdit/{planId}/{accType}", [IBController::class, 'ibCommissionEdit']);
        Route::post("/ibCommissionEdit/{planId}/{accType}", [IBController::class, 'ibCommissionEdit']);

        Route::get("/mt5_groups", [MT5Controller::class, 'index']);

        Route::get("/view_account_details/{accountId}", [MT5Controller::class, 'view'])->where('account', '.*')->name('admin-view-account-details');
        Route::post("/updatePassword", [MT5Controller::class, 'updatePassword'])->name('updatePassword');
        Route::post("/updateAccountDetails", [MT5Controller::class, 'updateAccountDetails'])->name('updateAccountDetails');
        Route::post("/depositToAccount", [MT5Controller::class, 'depositToAccount'])->name('depositToAccount');
        Route::post("/withdrawFromAccount", [MT5Controller::class, 'withdrawFromAccount'])->name('withdrawFromAccount');
        Route::post("/bonusToAccount", [MT5Controller::class, 'bonusToAccount'])->name('bonusToAccount');
        Route::post("/creditBonusToAccount", [MT5Controller::class, 'creditBonusToAccount'])->name('creditBonusToAccount');

        Route::get("/search", [SearchController::class, 'index']);
    });
});
