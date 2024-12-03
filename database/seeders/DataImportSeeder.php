<?php

namespace Database\Seeders;
use App\Models\User;
use App\Models\Account;
use App\Models\Country;
use App\Models\UserLog;
use App\Models\AccountType;
use App\Models\ClientWallet;
use Illuminate\Database\Seeder;
use App\Models\BonusTransaction;
use App\Models\DemoDeposit;
use Illuminate\Support\Facades\DB;
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
        // $this->users();
        // $this->userLogs();
        // $this->liveaccounts();
        // $this->demoaccounts();
        // $this->bonusTransaction();
        // $this->clientWallets();
        $this->demoDeposit();
    }
    private function users(){
        $usersdata = json_decode(File::get(storage_path('app/olddata/aspnetusers.json')), true);

        foreach ($usersdata as $user) {
            $user['uid'] = $user['id'];
            unset($user['id']);
            $newuser=User::create($user);
        }
    }
    private function liveaccounts(){
        $usersdata = json_decode(File::get(storage_path('app/olddata/liveaccounts.json')), true);

        foreach ($usersdata as $account) {
            //$user['uid'] = $account['id'];
            unset($account['id']);
            $account['demo']=false;
            $account['code']=$account['trade_id'];
            unset($account['trade_id']);
            $account['account_type_id']=AccountType::where('ac_index',$account['account_type'])->value('id');
            if(!$account['account_type_id']){
                dd($account);
            }
            unset($account['account_type']);
            $account['trade_platform']=$account['tradePlatform'];
            unset($account['tradePlatform']);
            $account['lots_completed']=$account['lotsCompleted'];
            unset($account['lotsCompleted']);
            $account['margin_free']=$account['MarginFree'];
            unset($account['MarginFree']);
            $account['margin_level']=$account['MarginLevel'];
            unset($account['MarginLevel']);
            $account['margin_level_type']=$account['MarginLevelType'];
            unset($account['MarginLevelType']);
            $account['adjustment']=$account['adj'];
            unset($account['adj']);
            $account['trader_password']=$account['trader_pwd'];
            unset($account['trader_pwd']);
            
            $account['invester_password']=$account['invester_pwd'];
            unset($account['invester_pwd']);
            
            $account['phone_password']=$account['phone_pwd'];
            unset($account['phone_pwd']);
            
            $account['internal_deposit']=$account['internalDeposit'];
            unset($account['internalDeposit']);
            $account['bonus_deposit']=$account['bonusDeposit'];
            unset($account['bonusDeposit']);
            $account['w_bonus_deposit']=$account['wBonusDeposit'];
            unset($account['wBonusDeposit']);

            $account['user_id']=User::where('email',$account['email'])->value('id');
            
            $newuser=Account::create($account);
        }
    }
    private function demoaccounts(){
        $replacementgroups=['LQH MARKETS\NO-COMMISION-B-USD'=>'LM\B-Book\NC\DF-B',"LQH MARKETS\LM-STANDARD-A-USD"=>"LM\A-Book\STD\DF-A"];
        $usersdata = json_decode(File::get(storage_path('app/olddata/demoaccounts.json')), true);
        $missingaccountcodes=['125717',855017,540606,123831,768456];

        foreach ($usersdata as $account) {
            if(in_array($account['trade_id'],$missingaccountcodes)){
                continue;
            }
            //$user['uid'] = $account['id'];
            unset($account['id']);
            $account['demo']=true;
            $account['code']=$account['trade_id'];
            unset($account['trade_id']);
            $group=isset($replacementgroups[$account['account_type']])?$replacementgroups[$account['account_type']]:$account['account_type'];
            $account['account_type_id']=AccountType::where('ac_group',$group)->value('id');
            if(!$account['account_type_id']){
                dd($account);
            }
            unset($account['account_type']);
            $account['trade_platform']=$account['tradePlatform'];
            unset($account['tradePlatform']);
            $account['lots_completed']=$account['lotsCompleted'];
            unset($account['lotsCompleted']);
            $account['margin_free']=$account['MarginFree'];
            unset($account['MarginFree']);
            $account['margin_level']=$account['MarginLevel'];
            unset($account['MarginLevel']);
            $account['margin_level_type']=$account['MarginLevelType'];
            unset($account['MarginLevelType']);
            $account['adjustment']=$account['adj'];
            unset($account['adj']);
            $account['trader_password']=$account['trader_pwd'];
            unset($account['trader_pwd']);
            
            $account['invester_password']=$account['invester_pwd'];
            unset($account['invester_pwd']);
            
            $account['phone_password']=$account['phone_pwd'];
            unset($account['phone_pwd']);
            
            $account['internal_deposit']=$account['internalDeposit'];
            unset($account['internalDeposit']);
            $account['bonus_deposit']=$account['bonusDeposit'];
            unset($account['bonusDeposit']);
            $account['w_bonus_deposit']=$account['wBonusDeposit'];
            unset($account['wBonusDeposit']);

            $account['user_id']=User::where('email',$account['email'])->value('id');
            
            $newuser=Account::updateOrCreate([
                'code'=>$account['code'],
                'demo'=>true,
            ],$account);
        }
    }
    private function userLogs(){
        $userlogsdata = json_decode(File::get(storage_path('app/olddata/user_logs.json')), true);

        foreach ($userlogsdata as $userlog) {
            unset($userlog['id']);
            $user=User::where('email',$userlog['email'])->first();
            $userlog['user_id']=$user->id;
            UserLog::create($userlog);
        }
    }
    // BonusTransaction
    private function bonusTransaction(){
        $bonusTransactiondata = json_decode(File::get(storage_path('app/olddata/bonus_transactions.json')), true);

        foreach ($bonusTransactiondata as $bonusTransaction) {
            unset($bonusTransaction['id']);
            $bonusTransaction['user_id']=User::where('email',$bonusTransaction['email'])->value('id');
            $bonusTransaction['account_id']=Account::where('code',$bonusTransaction['trade_id'])->value('id');
            $bonusTransaction['code']=$bonusTransaction['trade_id'];
            unset($bonusTransaction['trade_id']);
            $bonusTransaction['admin_remark']=$bonusTransaction['adminRemark'];
            unset($bonusTransaction['adminRemark']);
            BonusTransaction::create($bonusTransaction);
        }
    }
    private function clientWallets(){
        $clientWalletsdata = json_decode(File::get(storage_path('app/olddata/client_wallets.json')), true);

        foreach ($clientWalletsdata as $clientWallet) {
            unset($clientWallet['id']);
            unset($clientWallet['created_by']);
            $user=$clientWallet['user_id'];
            $clientWallet['user_id']=User::where('email',$clientWallet['user_id'])->value('id');
            if(!$clientWallet['user_id']){
                if($user==''){
                    info('empty user id'.json_encode($clientWallet));
                    continue;
                }
                // continue;
                dd($clientWallet);
            }
            ClientWallet::updateOrCreate([
                'user_id'=>$clientWallet['user_id'],
                'wallet_address'=>$clientWallet['wallet_address'],
            ],$clientWallet);
        }
    }
    private function demoDeposit(){
        $demoDeposits = json_decode(File::get(storage_path('app/olddata/demo_deposit.json')), true);
        foreach ($demoDeposits as $deposit) {
            $deposit['user_id']=User::where('email',$deposit['email'])->value('id');
            $deposit['account_id']=Account::where('code',$deposit['trade_id'])->value('id');
            $deposit['code']=$deposit['trade_id'];
            $deposit['admin_remark']=$deposit['AdminRemark'];
            unset($deposit['AdminRemark']);
            unset($deposit['trade_id']);
            DemoDeposit::updateOrCreate([
                'user_id'=>$deposit['user_id'],
                'deposted_date'=>$deposit['deposted_date'],
            ],$deposit);
        }
    }
}
