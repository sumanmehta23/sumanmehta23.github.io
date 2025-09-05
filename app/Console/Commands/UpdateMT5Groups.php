<?php

namespace App\Console\Commands;

use App\MT5\MTRetCode;
use App\MT5\MTWebAPI;
use App\Models\Account;
use App\Models\AccountType;
use App\Services\UniversalMT5Service;
use App\Services\MailService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UpdateMT5Groups extends Command
{
    protected $signature = 'app:alter-group-codes {--group_code=}';
    protected $description = 'Toggle MT5 Group codes from A-Book to B-Book or vice versa';

    protected $api;
    protected $mailService;
    protected $mt5Service;

    public function __construct(MailService $mailService, UniversalMT5Service $mt5Service, MTWebAPI $api)
    {
        parent::__construct();
        $this->mt5Service = $mt5Service;
        // Defer connection until handle() method
        $this->mailService = $mailService;
    }

    public function handle()
    {
        // Connect to MT5 using connection pool
        if (!$this->mt5Service->connect()) {
            $this->error('Failed to connect to MT5 via pool.');
            return 1;
        }
        $this->api = $this->mt5Service->getApi();

        $selectedGroupCode = $this->option('group_code');
        // dump('abhay');
        // dd($selectedGroupCode);
        if (!$selectedGroupCode || !in_array($selectedGroupCode, ['A-Book', 'B-Book'])) {
            $this->error("Invalid or missing --group_code option. Use --group_code=A-Book or --group_code=B-Book");
            return 1;
        }

        $batchSize = 50;
        $total = Account::with('accountType')
            ->whereHas('accountType', function ($query) {
                $query->where('ac_group', 'like', '%Book%');
            })
            ->count();

        $this->info("Total accounts to process: {$total}");
        Log::info("Starting batch update of MT5 groups for {$total} accounts...");

        $api = $this->api;

        $changedAccounts = []; // <- Array to store changed account codes

        Account::with(['accountType', 'user'])
            ->whereHas('accountType', function ($query) {
                $query->where('ac_group', 'like', '%Book%');
            })
            // ->where('code',594782)
            ->orderBy('id')
            ->chunk($batchSize, function ($accounts) use ($api, $selectedGroupCode, &$changedAccounts) {

                foreach ($accounts as $account) {
                    // dd($accounts);
                    $code = $account->code;

                    // if($code == 594782){
                        $trade_user = null;
                        if (($error_code = $api->UserGet($code, $trade_user)) != MTRetCode::MT_RET_OK) {
                            Log::warning("Failed to fetch MT5 user", [
                                'code' => $code,
                                'error' => MTRetCode::GetError($error_code)
                            ]);
                            continue;
                        }

                        $currentGroup = $trade_user->Group;

                        $newGroup = $currentGroup;

                        if (str_contains($selectedGroupCode, 'A-Book')) {
                            // If selected is A-Book → switch B-Book to A-Book
                            if (str_contains($currentGroup, 'B-Book')) {
                                $newGroup = str_replace('B-Book', 'A-Book', $currentGroup);
                                $newGroup = preg_replace('/-B($|\\\)/', '-A$1', $newGroup);
                            }
                        } elseif (str_contains($selectedGroupCode, 'B-Book')) {
                            // If selected is B-Book → switch A-Book to B-Book
                            if (str_contains($currentGroup, 'A-Book')) {
                                $newGroup = str_replace('A-Book', 'B-Book', $currentGroup);
                                $newGroup = preg_replace('/-A($|\\\)/', '-B$1', $newGroup);
                            }
                        }

                        if ($newGroup !== $currentGroup) {

                            $trade_user->Group = $newGroup;

                            if (($error_code = $api->UserUpdate($trade_user, $updated_user)) != MTRetCode::MT_RET_OK) {
                                Log::warning("Failed to update MT5 user", [
                                    'code' => $code,
                                    'error' => MTRetCode::GetError($error_code)
                                ]);
                                continue;
                            }

                            // Fetch corresponding AccountType (use first() not get())
                            $accountType = AccountType::where('ac_group', $newGroup)->first();

                            if ($accountType) {
                                Account::where('id', $account->id)->update([
                                    'mt5groupcode' => $newGroup,
                                    'account_type_id' => $accountType->id
                                ]);

                                Log::info("Updated MT5 Group", [
                                    'code' => $code,
                                    'new_group' => $newGroup,
                                    'account_type_id' => $accountType->id
                                ]);
                                $changedAccounts[] = $code;
                            } else {
                                Log::warning("AccountType not found for group: {$newGroup}");
                            }
                        }
                    // }
                }
            });

        // Log all changed account codes at the end
        if (!empty($changedAccounts)) {
            Log::info("List of account codes whose groups were changed:", $changedAccounts);
        } else {
            Log::info("No account groups were changed.");
        }

        $this->info("MT5 group update completed.");
        return 0;
    }
}
