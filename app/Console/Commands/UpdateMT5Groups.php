<?php

namespace App\Console\Commands;

use App\MT5\MTRetCode;
use App\MT5\MTWebAPI;
use App\Models\Account;
use App\Models\AccountType;
use App\Services\MT5Service;
use App\Services\MailService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UpdateMT5Groups extends Command
{
    // protected $signature = 'app:alter-group-codes';
    protected $signature = 'app:alter-group-codes {--group_code=}';

    protected $description = 'Toggle MT5 Group codes from A-Book To B-Book';

    protected $api;
    protected $mailService;
    protected $mt5Service;

    public function __construct(MailService $mailService, MT5Service $mt5Service, MTWebAPI $api)
    {
        parent::__construct();

        $this->mt5Service = $mt5Service;
        $this->mt5Service->connect();
        $this->api = $this->mt5Service->getApi();
        $this->mailService = $mailService;
        // $this->api = $api;
    }

    public function handle()
    {
        $selectedGroupCode = $this->option('group_code');
        $batchSize = 50;
        $total = Account::with('accountType')
                ->whereHas('accountType', function ($query) {
                    $query->where('ac_group', 'like', '%Book%');
                })
                ->count();
        dd($selectedGroupCode);
        $this->info("Total accounts to process: {$total}");

        Log::info("Starting batch update of MT5 groups for {$total} accounts...");

        $api = $this->api;

        Account::with(['accountType', 'user'])
            ->whereHas('accountType', function ($query) {
                $query->where('ac_group', 'like', '%Book%');
            })
            ->where('code',945423)
            ->orderBy('id')
            ->chunk($batchSize, function ($accounts) use ($api) {

                foreach ($accounts as $account) {
                    $code = $account->code;
                    if($code == 945423){

                        dd($accountType);

                        $trade_user = null;
                        // dd($trade_user);
                        if (($error_code = $api->UserGet($code, $trade_user)) != MTRetCode::MT_RET_OK) {
                            Log::warning("Failed to fetch MT5 user", [
                                'code' => $code,
                                'error' => MTRetCode::GetError($error_code)
                            ]);
                            continue;
                        }
                        $groupCode = $trade_user->Group;

                        if ($account->accountType && $groupCode) {

                            if (str_contains($selectedGroupCode, 'B-Book')) {
                                $groupCode = str_replace('B-Book', 'A-Book', $groupCode);
                                $groupCode = preg_replace('/-B($|\\\)/', '-A$1', $groupCode);
                            } elseif (str_contains($groupCode, 'A-Book')) {
                                $groupCode = str_replace('A-Book', 'B-Book', $groupCode);
                                $groupCode = preg_replace('/-A($|\\\)/', '-B$1', $groupCode);
                            }

                            $trade_user->Group = $groupCode;

                            $updated_user = "";
                            if (($error_code = $api->UserUpdate($trade_user, $updated_user)) != MTRetCode::MT_RET_OK) {
                                Log::warning("Failed to update MT5 user", [
                                    'code' => $code,
                                    'error' => MTRetCode::GetError($error_code)
                                ]);
                                continue;
                            }

                            $accountType = AccountType::where('ac_group',$groupCode)->get();


                            DB::table('accounts')->where('id', $account->id)->update([
                                'mt5groupcode' => $groupCode,
                                'account_type_id' => $accountType->id
                            ]);

                            Log::info("Updated MT5 Group", [
                                'code' => $code,
                                'group' => $groupCode,
                                'account_type_id' => $accountType->id
                            ]);
                        }
                    }
                }
            });

        $this->info("MT5 group update completed.");
        return 0;
    }
}
