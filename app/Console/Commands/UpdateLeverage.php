<?php

namespace App\Console\Commands;

use App\MT5\MTRetCode;
use App\Models\Account;
use App\Services\MT5Service;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class UpdateLeverage extends Command
{
    protected $api;
    protected $mailService;
    protected $mt5Service;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    //It accepts account code and leverage as arguments
    protected $signature = 'app:update-leverage {account_code} {leverage}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';
    function __construct(MT5Service $mt5Service)
    {
        $this->mt5Service = $mt5Service;
        $this->mt5Service->connect();
        $this->api = $this->mt5Service->getApi();
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $account_code = $this->argument('account_code');
        $leverage = $this->argument('leverage');
        if (($error_code = $this->api->UserGet($account_code, $trade_user)) != MTRetCode::MT_RET_OK) {
            Log::error('error'.' Something went wrong on getting user  details' . MTRetCode::GetError($error_code));
        }
        if (($error_code = $this->api->PositionGetTotal($account_code, $total_positions)) != MTRetCode::MT_RET_OK) {
            Log::error('error'. ' Something went wrong on Updating details' . MTRetCode::GetError($error_code));
            // return redirect()->back()->with('error', 'Something went wrong on Updating details' . MTRetCode::GetError($error_code));
        }

        if($total_positions < 2){
            $trade_user->Leverage = $leverage;
            $error_code = $this->api->UserUpdate($trade_user, $updated_user);
                if ($error_code != MTRetCode::MT_RET_OK) {
                    Log::error("error ". $account_code." Something went wrong on Updating details" . MTRetCode::GetError($error_code));
                    $this->info($account_code.":".$total_positions);
                } else {
                    Account::where('code', $account_code)->update([
                        'leverage' => $leverage
                    ]);
                }
                Log::info($account_code." Done" );
                $this->info("Processed ".$account_code);
        }else{
            $this->info($account_code.":".$total_positions);
        }
    }
}
