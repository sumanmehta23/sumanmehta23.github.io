<?php

use App\Models\Ib1;
use App\Models\User;
use App\Models\Account;
use App\Models\Permission;
use Illuminate\Support\Str;
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
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use App\Http\Controllers\Admin\Login;
use App\Http\Controllers\MT5Accounts;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\Ticket;
use App\Http\Controllers\Transactions;
use App\Actions\SubscribeToKlaviyoList;
use App\Http\Controllers\PammController;
use App\Http\Controllers\Admin\Dashboard;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\TradeWithdrawal;
use App\Http\Controllers\InternalTransfer;
use EonVisualMedia\LaravelKlaviyo\Klaviyo;
use App\Http\Controllers\Admin\Transaction;
use App\Http\Controllers\Admin\IBController;
use App\Http\Controllers\Admin\MT5Controller;
use App\Http\Controllers\Admin\AjaxController;
use App\Http\Controllers\Admin\StaffManagement;
use App\Http\Controllers\Admin\ClientController;
use App\Http\Controllers\Admin\SearchController;
use App\Http\Controllers\Admin\SumsubController;
use App\Http\Controllers\TradeDepositController;
use App\Http\Controllers\Admin\ApiAjaxController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\ClientAccController;
use App\Http\Controllers\PaymentCallbackController;
use App\Http\Controllers\Admin\PermissionController;

Route::get("/se", function (SubscribeToKlaviyoList $subscribeToKlaviyoList) {

    // return Klaviyo::post("profile-import", [
    //     'data' => [
    //         'type'          => 'profile',
    //         'attributes' => [
    //             "location"=> [
    //                 "address1"=> "89 E 42nd St",
    //                 "address2"=> "1st floor",
    //                 "city"=> "New York",
    //                 "country"=> "United States",
    //                 "region"=> "NY",
    //                 "zip"=> "10017",
    //                 "timezone"=> "America/New_York",
    //                 "ip"=> "127.0.0.1"
    //             ],
    //             'email'         => 'foo@example.com',
    //             'external_id'   => '12345',
    //             'phone_number'  => '+12345678901',
    //             "first_name"=> "John",
    //             "last_name"=> "Stean",

    //         ]
    //     ]
    // ]);

    // return config("services.klaviyo.list_ids");
    // $uuids=[];
    // for($i=0;$i<100;$i++){
    //     $uuids[]=Str::orderedUuid()->__tostring();
    // }
    // dump($uuids);
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
Route::get('/payment-response', [Payment::class, 'handlePaymentResponse'])->name('handlePaymentResponse');

Route::post('/paymentcallback', [PaymentCallbackController::class, 'handleCallback'])->name('paymentcallback');
Route::get('/', [LoginController::class, 'showLoginForm'])->name('login_index');
// Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
// Route::post('/login', [LoginController::class, 'login']);
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

Route::get('/wallet_address_verify', [Wallet::class, 'wallet_address_verify']);
Route::get('/delete_wallet_address', [Wallet::class, 'delete_wallet_address'])->name('delete_wallet_address');
Route::get('/reset-password', [LoginController::class, 'resetPassword']);
Route::post('/reset-password', [LoginController::class, 'resetPassword']);
Route::get('/ib-ref', [Ib::class, 'ibReference'])->name('ib-ref');
Route::post('/ib-ref', [LoginController::class, 'addUser'])->name('ib-ref-post');

Route::get('/logout', [LoginController::class, 'logout'])->name('logout');



Route::middleware(['auth'])->group(function () {
    Route::get('/wallet_withdrawal_verify', [Wallet::class, 'wallet_withdrawal_verify'])->name('wallet_withdrawal_verify');
    // Route::get('/', [Home::class, 'dashboard'])->name('dashboardIndex');
    Route::get('dashboard', [Home::class, 'dashboard'])->name('dashboard');
    Route::get('/view_account_details', [MT5Accounts::class, 'viewAccountDetails'])->name('view_account_details');
    Route::get('/select_account_deposit', [MT5Accounts::class, 'select_account_deposit'])->name('select_account_deposit');

    Route::get('/wallet', [Wallet::class, 'index'])->name('wallet');
    Route::get('/transactions', [Transactions::class, 'index'])->name('transactions');
    Route::post('/update-transaction', [Transactions::class, 'updateTransaction'])->name('updateTransaction');

    Route::get('/liveAccounts', [MT5Accounts::class, 'liveAccounts'])->name('liveAccounts');
    Route::get('/demoAccounts', [MT5Accounts::class, 'demoAccounts'])->name('demoAccounts');
    Route::get('/view-account-details/{account}', [MT5Accounts::class, 'viewAccountDetails'])->where('account', '.*')->name('view-account-details');
    Route::get('/createLiveAccount', [MT5Accounts::class, 'showLiveAccountForm'])->name('show-live-account-form');
    Route::post('/createLiveAccount', [MT5Accounts::class, 'createLiveAccount'])->name('create-live-account');
    Route::get('/createDemoAccount', [MT5Accounts::class, 'showDemoAccountForm'])->name('show-demo-account-form');
    Route::post('/createDemoAccount', [MT5Accounts::class, 'createDemoAccount'])->name('create-demo-account');
    Route::post('/view-account-details/{account}', [MT5Accounts::class, 'changeMt5Password'])->where('account', '.*')->name('change-mt5-password');

    Route::get('/getLeverage', [MT5Accounts::class, 'getLeverage'])->name('get-leverage');
    // Route::post('/update-leverage', [MT5Accounts::class, 'updateLeverage'])->name('update-leverage');



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
    Route::get('/pamm/manager', [PammController::class, 'manager'])->name('pamm.manager');
    Route::get('/pamm/investor', [PammController::class, 'investor'])->name('pamm.investor');
    Route::post('/sumsub_verify', [Users::class, 'sumsub_verify'])->name('sumsub_verify');
    Route::post('/log_kyc_verification', [Users::class, 'logVerification'])->name('logVerification');

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
    Route::post('/wallet_payment', [Wallet::class, 'processPayment'])->name('wallet_payment');
    Route::post('/change_password', [Users::class, 'changePassword'])->name('password.change');
    Route::post('/change_profileimage', [Users::class, 'changeProfileImage'])->name('profileimage.change');
    Route::post('/change_email', [Users::class, 'changeEmail'])->name('email.change');

    Route::get('/trade-deposit', [TradeDepositController::class, 'index'])->name('trade-deposit');
    Route::post('/trade-deposit', [TradeDepositController::class, 'deposit'])->name('trade-deposit_store');
    Route::get('/trade-withdrawal', [TradeWithdrawal::class, 'index'])->name('trade-withdrawal');
    Route::post('/trade-withdrawal', [TradeWithdrawal::class, 'withdraw'])->name('trade-withdrawal_store');
    Route::get('/internal-transfer', [InternalTransfer::class, 'index'])->name('internal-transfer');
    Route::post('/process-transfer', [InternalTransfer::class, 'processTransfer'])->name('process-transfer_store');
});
Route::post('/cryptochill/callback', [Wallet::class, 'secureProcessPayment'])->name('secure_wallet_payment');
Route::get('/switchToAdmin', [AjaxController::class, 'switchToAdmin'])->name("switchToAdmin");
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
    Route::get('/getDemoAccountsList', [AjaxController::class, 'getDemoAccountsList']);
    Route::get('/getRequestedAccountsList', [AjaxController::class, 'getRequestedAccountsList']);

    Route::get('/getWalletDeposit2', [AjaxController::class, 'getWalletDeposit2']);
    Route::get('/getWalletWithdrawal2', [AjaxController::class, 'getWalletWithdrawal2']);
    Route::get('/getTradingDeposit2', [AjaxController::class, 'getTradingDeposit2']);
    Route::get('/getTradingWithdrawal2', [AjaxController::class, 'getTradingWithdrawal2']);
    Route::get('/getInternalTransfer2', [AjaxController::class, 'getInternalTransfer2']);

    Route::get('/getPendingWalletDeposit2', [AjaxController::class, 'getPendingWalletDeposit2']);
    Route::get('/getPermissions', [AjaxController::class, 'getPermissions']);

    //
    Route::get('/getPendingWalletWithdrawal2', [AjaxController::class, 'getPendingWalletWithdrawal2']);
    Route::get('/getPendingTradingDeposit2', [AjaxController::class, 'getPendingTradingDeposit2']);
    Route::get('/getPendingTradingWithdrawal2', [AjaxController::class, 'getPendingTradingWithdrawal2']);

    Route::get('/getPendingIbUsers2', [AjaxController::class, 'getPendingIbUsers2']);
    Route::post('/bulkIbApprove', [AjaxController::class, 'bulkIbApprove']);
    Route::get('/getIbUsers2', [AjaxController::class, 'getIbUsers2']);

    Route::get('/getComissionData2', [AjaxController::class, 'getComissionData2']);



    Route::get('/getClientIbProfile', [AjaxController::class, 'getClientIbProfile']);

    Route::post('/ajax', [AjaxController::class, 'index']);
    Route::get('/api/ajax', [ApiAjaxController::class, 'handleRequest']);
    Route::post('/api/ajax', [ApiAjaxController::class, 'handleRequest']);
    Route::get('/logout', [Login::class, 'logout'])->name('logout');
    Route::post('/getClientSwitch', [AjaxController::class, 'getClientSwitch']);


    // Route::get('/admin/activity-logs', [ActivityLogController::class, 'index'])->name('activity-logs.index');
    // Route::get('/users/{user}', 'Users@show')->name('users.show');
    // Route::get('/transactions/{transaction}', 'Transaction@show')->name('transactions.show');

    // Route::get('/users/{user}', [Users::class, 'profile'])->name('users.show');
    // Route::get('/user-profile', [Users::class, 'profile'])->name('user-profile');
    // Route::get('/client_details/{userId}', [ClientController::class, 'clientDetails'])->name('admin-view-client-details')->middleware('check.permissions:client:view');
    // Route::get('/transactions/{transaction}', 'TransactionController@show')->name('admin.transactions.show');
    // Route::get('/orders/{order}', 'OrderController@show')->name('admin.orders.show');
    // Route::get('/products/{product}', 'ProductController@show')->name('admin.products.show');


    Route::middleware(['is_admin'])->group(function () {



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


        Route::get('/clients', [ClientController::class, 'index'])->name('clients.index')->middleware('check.permissions:client:viewAny');
        Route::get('/client_details/{userId}', [ClientController::class, 'clientDetails'])->name('admin-view-client-details')->middleware('check.permissions:client:view');
        Route::post('/updateIB', [ClientController::class, 'updateIB'])->name('updateIB');
        Route::post('/updateRM', [ClientController::class, 'updateRM'])->name('updateRM');
        Route::post('/addUser', [ClientController::class, 'addUser'])->name('addUser')->middleware("check.permissions:client:create");
        Route::post('/updateUser', [ClientController::class, 'updateUser'])->name('updateUser');
        Route::post('/sendPasswordResetLink', [ClientController::class, 'sendPasswordResetLink'])->name('sendPasswordResetLink');

        Route::get('/roles', [StaffManagement::class, 'roles'])->name('roles')->middleware('check.permissions:role:viewAny');
        Route::get('/rm_dashboard', [StaffManagement::class, 'rmDashboard'])->name('rm_dashboard');
        Route::post('/roles', [StaffManagement::class, 'addRole'])->name('roles');
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

        Route::get('/wallet_deposit_details', [Transaction::class, 'wallet_deposit_details']);
        Route::get('/wallet_withdrawal_details', [Transaction::class, 'wallet_withdrawal_details']);
        Route::post('/wallet_withdrawal_details', [Transaction::class, 'update_wallet_withdrawal'])->name('wallet_withdrawal_details');
        Route::post('/manually_approve_withdrawal', [Transaction::class, 'manually_approve_withdrawal'])->name('manually_approve_withdrawal');
        Route::get('/trading_deposit_details', [Transaction::class, 'trading_deposit_details']);
        Route::get('/trading_withdrawal_details', [Transaction::class, 'trading_withdrawal_details']);
        Route::post('/trading_withdrawal_details', [Transaction::class, 'update_trading_withdrawal']);

        Route::prefix('/clientAccounts')->group(function () {
            Route::get("/liveAccounts", [ClientAccController::class, 'live_accounts'])->name('liveAccounts')->middleware('check.permissions:account:viewLiveAccounts');
            Route::get("/demoAccounts", [ClientAccController::class, 'demo_accounts'])->name('demoAccounts')->middleware('check.permissions:account:viewDemoAccounts');
            Route::get("/requestedAccounts", [ClientAccController::class, 'requested_accounts'])->name('requestedAccounts')->middleware('check.permissions:account:viewRequestedAccounts');
            Route::post("/deleteAccounts", [MT5Accounts::class, 'deleteAccounts'])->name('deleteAccounts')->middleware('check.permissions:account:viewLiveAccounts');
            Route::post('/activate_account', [MT5Accounts::class, 'activateAccount'])->name('activate_account');
        });

        Route::prefix('/ui_settings')->group(function () {
            Route::get('/', [SettingsController::class, 'index'])->name("ui-settings.view")->middleware('check.permissions:setting:viewAny');
            Route::post('/', [SettingsController::class, 'store'])->name('ui-settings.update')->middleware('check.permissions:setting:update');
        });
        Route::prefix('/logs')->group(function () {
            Route::get('/', [SettingsController::class, 'logs'])->name("logs.view")->middleware('check.permissions:setting:viewAny');
        });

        Route::prefix('/update_password')->group(function () {
            Route::get('/', [SettingsController::class, 'update_password'])->name('update_password')->middleware('check.permissions:setting:update');
            Route::post('/', [SettingsController::class, 'store_password'])->name('update_password')->middleware('check.permissions:setting:update');;
        });
        Route::get("/ibdashboard", [IBController::class, 'index'])->name('ib.dashboard')->middleware('check.permissions:ib:viewAny');
        Route::get("/iblist", [IBController::class, 'list'])->name('ib.list')->middleware('check.permissions:ib:manageRequests');;
        Route::get("/iblist_active", [IBController::class, 'list_active'])->name('ib.active.list')->middleware('check.permissions:ib:viewAny');;;
        Route::get("/ib_settings", [IBController::class, 'ib_settings'])->name('ib.settings')->middleware('check.permissions:ib:manageSettings');
        Route::get("/ibCommission", [IBController::class, 'ibCommission']);
        Route::post("/ibCommission", [IBController::class, 'updateIbPlan']);
        Route::get("/ibCommissionEdit/{planId}/{accType}", [IBController::class, 'ibCommissionEdit']);
        Route::post("/ibCommissionEdit/{planId}/{accType}", [IBController::class, 'ibCommissionEdit']);

        Route::get("/mt5_groups", [MT5Controller::class, 'index']);

        Route::get("/view_account_details/{accountId}", [MT5Controller::class, 'view'])->where('account', '.*')->name('admin-view-account-details');
        Route::post("/updatePassword", [MT5Controller::class, 'updatePassword'])->name('updatePassword');
        Route::post("/updateAccountDetails", [MT5Controller::class, 'updateAccountDetails'])->name('updateAccountDetails');
        Route::post("/depositToAccount", [MT5Controller::class, 'depositToAccount'])->name('depositToAccount')->middleware('check.permissions:trade_deposit:create');
        Route::post("/withdrawFromAccount", [MT5Controller::class, 'withdrawFromAccount'])->name('withdrawFromAccount')->middleware('check.permissions:trade_withdrawals:create');
        Route::post("/bonusToAccount", [MT5Controller::class, 'bonusToAccount'])->name('bonusToAccount')->middleware('check.permissions:bonus_transaction:create');
        Route::post("/creditBonusToAccount", [MT5Controller::class, 'creditBonusToAccount'])->name('creditBonusToAccount')->middleware('check.permissions:bonus_transaction:create');

        Route::get("/search", [SearchController::class, 'index']);
        Route::resource('permissions', PermissionController::class);
        // Route::get("/roles-permissions", [SearchController::class, 'index']);
        // Route::get("/sendMarketEmail", [Dashboard::class, 'sendMarketingEmail']);
    });
});
