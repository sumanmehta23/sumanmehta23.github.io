<?php

namespace App\Jobs;

use Exception;
use App\Models\Ib1;
use App\MT5\MTWebAPI;
use App\Models\Symbol;
use App\MT5\MTRetCode;
use App\Models\Account;
use Illuminate\Support\Str;
use App\Services\MT5Service;
use App\Models\Ib1Commission;
use App\Models\IbPlanDetails;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

class SyncAccountTradesJob implements ShouldQueue
{
    use Queueable;
    protected $mt5Service;
    protected $api;
    protected  $account;
    protected $accountId;
    protected $newTrades = true;
    protected $referral_code;
    protected $ib_user_id;
    protected $ib_acc_plans = [];
    /**
     * Create a new job instance.
     */
    public function __construct($accountId, $referral_code, $ib_user_id, $ib_acc_plans)
    {
        $this->accountId = $accountId;
        $this->referral_code = $referral_code;
        $this->ib_user_id = $ib_user_id;
        $this->ib_acc_plans = $ib_acc_plans;
        $this->onQueue('syncaccountstrades');
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $api = new MTWebAPI;
        $mt5Service = new MT5Service($api);
        $this->mt5Service = $mt5Service;
        $this->mt5Service->connect();
        $this->api = $this->mt5Service->getApi();
        $this->account = Account::find($this->accountId);
        info('Syncing account trades for account: ' . $this->account->code);
        $login = $this->account->code;
        $from = 'September 01,2024';
        $to = 'March 31,2080';
        $total = 0;

        $error_code = $this->api->HistoryGetTotal($login, $from, $to, $total);
        if ($error_code != MTRetCode::MT_RET_OK) {
            Log::error('MT5 ' . $login . ': ' . MTRetCode::GetError($error_code));
            return;
        }

        $closedOrderHistory = $total;
        if ($closedOrderHistory == 0) {
            return;
        }

        $offset = Ib1Commission::where('code', $login)->count();
        $total = $closedOrderHistory;

        $maxTries = 10;
        $attempts = 0;
        $processedOrders = [];

        while ($offset < $total && $attempts < $maxTries) {
            $error_code = $this->api->HistoryGetPage($login, $from, $to, $offset, $total, $orders);
            if ($error_code != MTRetCode::MT_RET_OK) {
                Log::error('MT5 ' . $login . ': ' . MTRetCode::GetError($error_code));
                break;
            }

            if ($orders) {
                $ibcommissions = [];
                $orderIdsAndCodes = [];

                foreach ($orders as $item) {
                    $symbolWithoutP = $item->Symbol;
                    if (!isset($symbolmap[$symbolWithoutP])) {
                        try {
                            $symbol = Symbol::where('symbol', $symbolWithoutP)->first();
                            $symbolmap[$symbolWithoutP] = $symbol ? $symbol->path : 'default/path';
                        } catch (Exception $e) {
                            Log::error('Error fetching symbol: ' . $e->getMessage());
                            $symbolmap[$symbolWithoutP] = 'error/path';
                        }
                    }

                    $symbolpath = $symbolmap[$symbolWithoutP];
                    $b = (strpos($symbolpath, 'Energy') !== false || strpos($symbolpath, 'Indices') !== false || strpos($symbolpath, 'Cryptocurrencies') !== false) ? 0.00001 : 0.0001;

                    if (in_array($item->Order . '-' . $item->Login, $processedOrders)) {
                        continue;
                    }

                    $existingCommission = Ib1Commission::where('order_id', $item->Order)
                        ->where('code', $item->Login)
                        ->exists();

                    if ($existingCommission) {
                        continue;
                    }

                    $processedOrders[] = $item->Order . '-' . $item->Login;

                    $ibcommissions[] = [
                        'id' => (string)Str::orderedUuid(),
                        'user_id' => $this->account->user_id,
                        'account_id' => $this->account->id,
                        'order_id' => $item->Order,
                        'code' => $item->Login,
                        'init_volume' => $item->VolumeInitial,
                        'symbol' => $symbolWithoutP,
                        'volume' => $item->VolumeInitial * $b,
                        'time_closed' => Carbon::createFromTimestamp($item->TimeDone),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];

                    if (count($ibcommissions) >= 50) {
                        try {
                            Ib1Commission::insert($ibcommissions);
                            $this->newTrades = true;
                        } catch (Exception $e) {
                            Log::error('Error inserting commission: ' . $e->getMessage());
                        }
                        $ibcommissions = [];
                    }
                }

                if (count($ibcommissions) > 0) {
                    try {
                        Ib1Commission::insert($ibcommissions);
                        $this->newTrades = true;
                    } catch (Exception $e) {
                        Log::error('Error inserting commission: ' . $e->getMessage());
                    }
                }
            }

            $offset += count($orders);

            $attempts++;
            if ($attempts >= $maxTries) {
                Log::warning("Reached max tries for account: $login after $attempts attempts.");
            }
        }

        if ($attempts >= $maxTries) {
            Log::error("Reached maximum attempts for account: $login. Skipping.");
        }
        if ($this->newTrades) {
            // ($referral_code, $userId, $ib_acc_plans)
            info('Dispatching DistributeIbCommissionJob for account: ' . json_encode([$this->referral_code, $this->ib_user_id, $this->ib_acc_plans, $this->account->id]));
            DistributeIbCommissionJob::dispatch($this->referral_code, $this->ib_user_id, $this->ib_acc_plans, $this->account->id);
        }
    }
}
