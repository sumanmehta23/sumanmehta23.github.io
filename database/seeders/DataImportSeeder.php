<?php

namespace Database\Seeders;

use App\Models\Ib1;
use App\Models\User;
use App\Models\IbPlan;
use App\Models\KycLog;
use App\Models\Account;
use App\Models\Country;
use App\Models\UserLog;
use App\Models\IbWallet;
use App\Models\IbCategory;
use App\Models\AccountType;
use App\Models\DemoDeposit;
use App\Http\Controllers\Ib;
use App\Models\ClientWallet;
use App\Models\LoginHistory;
use App\Models\TotalBalance;
use App\Models\TradeDeposit;
use App\Models\Ib1Commission;
use App\Models\IbPlanDetails;
use App\Models\WalletDeposit;
use App\Models\WalletWithdraw;
use Illuminate\Database\Seeder;
use App\Models\BonusTransaction;
use App\Models\TradeWithdrawals;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\admin\Kyc;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class DataImportSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        ini_set('memory_limit', '1024M');
        ini_set('max_execution_time', 3000);
        $this->users();
        $this->userLogs();
        $this->liveaccounts();
        $this->demoaccounts();
        $this->bonusTransaction();
        $this->clientWallets();
        $this->demoDeposit();
        $this->ib_categories();
        $this->ib_plans();
        $this->ib_plan_details();
        $this->ib1(); 
        $this->ib1_commission();
        $this->ib1_withdraw();
        $this->ib_wallet();
        $this->kyc_logs();
        $this->loginHistory();
        $this->totalBalance();
        $this->tradeDeposit();
        $this->tradeWithdrawal();
        $this->walletDeposit();
        $this->walletWithdraw();
    }
    private function users()
    {
        $usersdata = json_decode(File::get(storage_path('app/olddata/lqhcore_82_table_aspnetusers.json')), true);

        foreach ($usersdata as $user) {
            $user['uid'] = $user['id'];
            unset($user['id']);
            $newuser = User::create($user);
        }
    }
    private function demoaccounts()
    {
        $replacementgroups = ['LQH MARKETS\NO-COMMISION-B-USD' => 'LM\B-Book\NC\DF-B', "LQH MARKETS\LM-STANDARD-A-USD" => "LM\A-Book\STD\DF-A"];
        $usersdata = json_decode(File::get(storage_path('app/olddata/lqhcore_82_table_demoaccount.json')), true);
        $missingaccountcodes = ['125717', 855017, 540606, 123831, 768456];

        foreach ($usersdata as $account) {
            if (in_array($account['trade_id'], $missingaccountcodes)) {
                continue;
            }
            //$user['uid'] = $account['id'];
            unset($account['id']);
            $account['demo'] = true;
            $account['code'] = $account['trade_id'];
            unset($account['trade_id']);
            $group = isset($replacementgroups[$account['account_type']]) ? $replacementgroups[$account['account_type']] : $account['account_type'];
            $account['account_type_id'] = AccountType::where('ac_group', $group)->value('id');
            if (!$account['account_type_id']) {
                Log::error('Account Type not found', $account);
                continue;
            }
            unset($account['account_type']);
            $account['platform'] = $account['tradePlatform'];
            unset($account['tradePlatform']);
            $account['lots_completed'] = $account['lotsCompleted'];
            unset($account['lotsCompleted']);
            $account['margin_free'] = $account['MarginFree'];
            unset($account['MarginFree']);
            $account['margin_level'] = $account['MarginLevel'];
            unset($account['MarginLevel']);
            $account['margin_level_type'] = $account['MarginLevelType'];
            unset($account['MarginLevelType']);
            $account['adjustment'] = $account['adj'];
            unset($account['adj']);
            $account['trader_password'] = $account['trader_pwd'];
            unset($account['trader_pwd']);

            $account['invester_password'] = $account['invester_pwd'];
            unset($account['invester_pwd']);

            $account['phone_password'] = $account['phone_pwd'];
            unset($account['phone_pwd']);

            $account['internal_deposit'] = $account['internalDeposit'];
            unset($account['internalDeposit']);
            $account['bonus_deposit'] = $account['bonusDeposit'];
            unset($account['bonusDeposit']);
            $account['w_bonus_deposit'] = $account['wBonusDeposit'];
            unset($account['wBonusDeposit']);

            $account['user_id'] = User::where('email', $account['email'])->value('id');

            $newuser = Account::updateOrCreate([
                'code' => $account['code'],
                'demo' => true,
            ], $account);
        }
    }
    private function liveaccounts()
    {
        $usersdata = json_decode(File::get(storage_path('app/olddata/lqhcore_82_table_liveaccount.json')), true);

        foreach ($usersdata as $account) {
            //$user['uid'] = $account['id'];
            unset($account['id']);
            $account['demo'] = false;
            $account['code'] = $account['trade_id'];
            unset($account['trade_id']);
            $account['account_type_id'] = AccountType::where('ac_index', $account['account_type'])->value('id');
            if (!$account['account_type_id']) {
                Log::error('Account Type not found ', $account);
                continue;
            }
            unset($account['account_type']);
            $account['platform'] = $account['tradePlatform'];
            unset($account['tradePlatform']);
            $account['lots_completed'] = $account['lotsCompleted'];
            unset($account['lotsCompleted']);
            $account['margin_free'] = $account['MarginFree'];
            unset($account['MarginFree']);
            $account['margin_level'] = $account['MarginLevel'];
            unset($account['MarginLevel']);
            $account['margin_level_type'] = $account['MarginLevelType'];
            unset($account['MarginLevelType']);
            $account['adjustment'] = $account['adj'];
            unset($account['adj']);
            $account['trader_password'] = $account['trader_pwd'];
            unset($account['trader_pwd']);

            $account['invester_password'] = $account['invester_pwd'];
            unset($account['invester_pwd']);

            $account['phone_password'] = $account['phone_pwd'];
            unset($account['phone_pwd']);

            $account['internal_deposit'] = $account['internalDeposit'];
            unset($account['internalDeposit']);
            $account['bonus_deposit'] = $account['bonusDeposit'];
            unset($account['bonusDeposit']);
            $account['w_bonus_deposit'] = $account['wBonusDeposit'];
            unset($account['wBonusDeposit']);

            $account['user_id'] = User::where('email', $account['email'])->value('id');

            $newuser = Account::create($account);
        }
    }
    
    private function userLogs()
    {
        $userlogsdata = json_decode(File::get(storage_path('app/olddata/lqhcore_82_table_aspnetusers_log.json')), true);

        foreach ($userlogsdata as $userlog) {
            unset($userlog['id']);
            $user = User::where('email', $userlog['email'])->first();
            if(!$user){
                continue;
            }   
            $userlog['user_id'] = $user->id;
            UserLog::create($userlog);
        }
    }
    // BonusTransaction
    private function bonusTransaction()
    {
        $bonusTransactiondata = json_decode(File::get(storage_path('app/olddata/lqhcore_82_table_bonus_trans.json')), true);

        foreach ($bonusTransactiondata as $bonusTransaction) {
            unset($bonusTransaction['id']);
            $bonusTransaction['user_id'] = User::where('email', $bonusTransaction['email'])->value('id');
            $bonusTransaction['account_id'] = Account::where('code', $bonusTransaction['trade_id'])->value('id');
            $bonusTransaction['code'] = $bonusTransaction['trade_id'];
            unset($bonusTransaction['trade_id']);
            $bonusTransaction['admin_remark'] = $bonusTransaction['adminRemark'];
            unset($bonusTransaction['adminRemark']);
            BonusTransaction::create($bonusTransaction);
        }
    }
    private function clientWallets()
    {
        $clientWalletsdata = json_decode(File::get(storage_path('app/olddata/lqhcore_82_table_client_wallets.json')), true);

        foreach ($clientWalletsdata as $clientWallet) {
            unset($clientWallet['id']);
            unset($clientWallet['created_by']);
            $user = $clientWallet['user_id'];
            $clientWallet['user_id'] = User::where('email', $clientWallet['user_id'])->value('id');
            if (!$clientWallet['user_id']) {
                if ($user == '') {
                    info('empty user id' . json_encode($clientWallet));
                    continue;
                }
                continue;
                // dd($clientWallet);
            }
            ClientWallet::updateOrCreate([
                'user_id' => $clientWallet['user_id'],
                'wallet_address' => $clientWallet['wallet_address'],
            ], $clientWallet);
        }
    }
    private function demoDeposit()
    {
        $demoDeposits = json_decode(File::get(storage_path('app/olddata/lqhcore_82_table_demo_deposit.json')), true);
        foreach ($demoDeposits as $deposit) {
            $deposit['user_id'] = User::where('email', $deposit['email'])->value('id');
            $deposit['account_id'] = Account::where('code', $deposit['trade_id'])->value('id');
            $deposit['code'] = $deposit['trade_id'];
            $deposit['admin_remark'] = $deposit['AdminRemark'];
            unset($deposit['AdminRemark']);
            unset($deposit['trade_id']);
            try {
                DemoDeposit::updateOrCreate([
                    'user_id' => $deposit['user_id'],
                    'deposted_date' => $deposit['deposted_date'],
                ], $deposit);
            } catch (\Throwable $th) {
                Log::error( $th->getMessage(),$deposit);
            }
            
        }
    }
    private function ib1()
    {
        $ibs = json_decode(File::get(storage_path('app/olddata/lqhcore_82_table_ib1.json')), true);
        foreach ($ibs as $ib) {
            $ib['user_id'] = User::where('email', $ib['email'])->value('id');
            if($ib['acc_type']){
                $catid=IbCategory::where('ib_cat_id', $ib['acc_type'])->value('id');
                $ib['ib_plan_details_id'] = IbPlanDetails::where('ib_category_id', $catid)->value('id');
            }
            try {
                Ib1::create($ib);
            } catch (\Throwable $th) {
                Log::error( $th->getMessage(),$ib);
            }
            
        }
    }
    private function ib1_commission()
    {
        $ibcommissions = json_decode(File::get(storage_path('app/olddata/lqhcore_82_table_ib1_commission.json')), true);
        foreach ($ibcommissions as $ibcommission) {
            $ibcommission['user_id'] = User::where('email', $ibcommission['user_id'])->value('id');
            $ibcommission['account_id'] = Account::where('code', $ibcommission['login'])->value('id');
            $ibcommission['code'] = $ibcommission['login'];
            unset($ibcommission['login']);
            unset($ibcommission['id']);
            try {
                Ib1Commission::create($ibcommission);
            } catch (\Throwable $th) {
                Log::error( $th->getMessage(),$ibcommission);
            }   
        }
    }
    private function ib1_withdraw()
    {
        // $demoDeposits = json_decode(File::get(storage_path('app/olddata/lqhcore_82_table_demo_deposit.json')), true);
        // foreach ($demoDeposits as $deposit) {
        //     $deposit['user_id'] = User::where('email', $deposit['email'])->value('id');
        //     $deposit['account_id'] = Account::where('code', $deposit['trade_id'])->value('id');
        //     $deposit['code'] = $deposit['trade_id'];
        //     $deposit['admin_remark'] = $deposit['AdminRemark'];
        //     unset($deposit['AdminRemark']);
        //     unset($deposit['trade_id']);
        //     DemoDeposit::updateOrCreate([
        //         'user_id' => $deposit['user_id'],
        //         'deposted_date' => $deposit['deposted_date'],
        //     ], $deposit);
        // }
    }
    private function ib_categories()
    {
        $ibcategories = json_decode(File::get(storage_path('app/olddata/lqhcore_82_table_ib_categories.json')), true);
        foreach ($ibcategories as $category) {
            IbCategory::create($category);
        }
    }
    private function ib_plans()
    {
        // $demoDeposits = json_decode(File::get(storage_path('app/olddata/lqhcore_82_table_demo_deposit.json')), true);
        // foreach ($demoDeposits as $deposit) {
        //     $deposit['user_id'] = User::where('email', $deposit['email'])->value('id');
        //     $deposit['account_id'] = Account::where('code', $deposit['trade_id'])->value('id');
        //     $deposit['code'] = $deposit['trade_id'];
        //     $deposit['admin_remark'] = $deposit['AdminRemark'];
        //     unset($deposit['AdminRemark']);
        //     unset($deposit['trade_id']);
        //     DemoDeposit::updateOrCreate([
        //         'user_id' => $deposit['user_id'],
        //         'deposted_date' => $deposit['deposted_date'],
        //     ], $deposit);
        // }
    }

    private function ib_plan_details()
    {
        $planDetails = json_decode(File::get(storage_path('app/olddata/lqhcore_82_table_ib_plan_details.json')), true);
        foreach ($planDetails as $plan) {
            // $plan['ib_plan_id'] = IbPlan::where('ib_plan_name', $plan['ib_plan_name'])->value('id');
            $plan['ib_category_id'] = IbCategory::where('ib_cat_id', $plan['ib_plan_id'])->value('id');
            $plan['account_type_id'] = AccountType::where('ac_index', $plan['acc_type'])->value('id');
            unset($plan['ib_plan_id']);
            unset($plan['id']);
            // unset($plan['acc_type']);
            try {
                IbPlanDetails::create($plan);
            } catch (\Throwable $th) {
                Log::error( $th->getMessage(),$plan);
                
            }
            
        }
    }
    private function ib_wallet()
    {
        $ibwallets = json_decode(File::get(storage_path('app/olddata/lqhcore_82_table_ib_wallet.json')), true);
        foreach ($ibwallets as $ibwallet) {
            $ibwallet['user_id'] = Ib1::where(['referral_code'=> $ibwallet['email']])->value('user_id');
            $ibwallet['account_id'] = Account::where('code', $ibwallet['trade_id'])->value('id');
            $ibwallet['code'] = $ibwallet['trade_id'];
            unset($ibwallet['trade_id']);
            unset($ibwallet['id']);
            try {
                IbWallet::create($ibwallet);
            } catch (\Throwable $th) {
                Log::error( $th->getMessage(),$ibwallet);
            }
            
        }
    }


    private function kyc_logs()
    {
        $kycData = json_decode(File::get(storage_path('app/olddata/lqhcore_82_table_kyc_logs.json')), true);
        foreach ($kycData as $kyc) {
            $kyc['user_id'] = User::where('email', $kyc['client_id'])->value('id');
            KycLog::create($kyc);
        }
    }
    // loginHistory
    private function loginHistory()
    {
        $loginHistoryData = json_decode(File::get(storage_path('app/olddata/lqhcore_82_table_login_history.json')), true);
        foreach ($loginHistoryData as $loginHistory) {
            $loginHistory['user_id'] = User::where('email', $loginHistory['email'])->value('id');
            if(!$loginHistory['user_id']){
                // dd($loginHistory);
                continue;
            }
            LoginHistory::create($loginHistory);
        }
    }
    private function totalBalance()
    {
        $balances = json_decode(File::get(storage_path('app/olddata/lqhcore_82_table_total_balance.json')), true);
        foreach ($balances as $key => $balance) {
            unset($balance['id']);
            $balance['user_id'] = User::where('email', $balance['email'])->value('id');
            $balance['code']=$balance['trade_id'];
            if($balance['code']){
                $balance['account_id'] = Account::where('code', $balance['code'])->value('id');
            }
            
            
            unset($balance['trade_id']);
            TotalBalance::create($balance);
        }
    }
    private function tradeDeposit()
    {
        $tradeDeposits = json_decode(File::get(storage_path('app/olddata/lqhcore_82_table_trade_deposit.json')), true);
        foreach ($tradeDeposits as $deposit) {
            unset($deposit['id']);
            $deposit['user_id'] = User::where('email', $deposit['email'])->value('id');
            $deposit['account_id'] = Account::where('code', $deposit['trade_id'])->value('id');
            if(!$deposit['account_id']){
                Log::error('Account not found', $deposit);
                continue;

            }
            if($deposit['deposit_from']){
                $deposit['deposit_from'] =  Account::where('code', $deposit['deposit_from'])->value('id');
            }
            
            $deposit['code'] = $deposit['trade_id'];
            $deposit['admin_remark'] = $deposit['AdminRemark'];
            unset($deposit['AdminRemark']);
            unset($deposit['trade_id']);
            TradeDeposit::updateOrCreate([
                'user_id' => $deposit['user_id'],
                'deposted_date' => $deposit['deposted_date'],
            ], $deposit);
        }
    }
    private function tradeWithdrawal()
    {
        $tradeWithdrawals = json_decode(File::get(storage_path('app/olddata/lqhcore_82_table_trade_withdrawal.json')), true);
        foreach ($tradeWithdrawals as $withdrawal) {
            unset($withdrawal['id']);
            $withdrawal['user_id'] = User::where('email', $withdrawal['email'])->value('id');
            $withdrawal['account_id'] = Account::where('code', $withdrawal['trade_id'])->value('id');
            if(!$withdrawal['account_id']){
                Log::error('Account not found', $withdrawal);
                continue;

            }
            
            $withdrawal['admin_remark'] = $withdrawal['AdminRemark'];
            unset($withdrawal['AdminRemark']);
            $withdrawal['code'] = $withdrawal['trade_id'];
            unset($withdrawal['trade_id']);
            TradeWithdrawals::updateOrCreate([
                'user_id' => $withdrawal['user_id'],
                'account_id' => $withdrawal['account_id'],
                'withdraw_date' => $withdrawal['withdraw_date'],
            ], $withdrawal);
        }
    }
    private function walletDeposit()
    {
        $walletDeposits = json_decode(File::get(storage_path('app/olddata/lqhcore_82_table_wallet_deposit.json')), true);
        foreach ($walletDeposits as $deposit) {
            unset($deposit['id']);
            $deposit['user_id'] = User::where('email', $deposit['email'])->value('id');
            
            if($deposit['client_bank']){
                $deposit['client_wallet_id'] =  ClientWallet::where('client_wallet_id', $deposit['client_bank'])->value('id');
            }
            $deposit['admin_remark'] = $deposit['AdminRemark'];
            unset($deposit['AdminRemark']);
            WalletDeposit::create($deposit);
        }
    }
    private function walletWithdraw()
    {
        $walletWithdraws = json_decode(File::get(storage_path('app/olddata/lqhcore_82_table_wallet_withdraw.json')), true);
        foreach ($walletWithdraws as $withdraw) {
            unset($withdraw['id']);
            $withdraw['user_id'] = User::where('email', $withdraw['email'])->value('id');
            
            if($withdraw['client_bank']){
                $withdraw['client_wallet_id'] =  ClientWallet::where('client_wallet_id', $withdraw['client_bank'])->value('id');
            }
            $withdraw['admin_remark'] = $withdraw['AdminRemark'];
            unset($withdraw['AdminRemark']);
            WalletWithdraw::updateOrCreate([
                'user_id' => $withdraw['user_id'],
                'withdraw_date' => $withdraw['withdraw_date'],
            ], $withdraw);
        }
    }

}
