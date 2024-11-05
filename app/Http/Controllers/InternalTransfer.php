<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\LiveAccount;
use App\MT5\MTWebAPI;
use App\MT5\MTRetCode;
use App\MT5\MTEnDealAction;
use App\Helpers\AccountHelper;
use Illuminate\Support\Facades\DB;
use App\Models\TradeWithdrawals;
use App\Models\TradeDeposits;
use App\Models\TotalBalance;

class InternalTransfer extends Controller
{
    protected $api;

    public function __construct(MTWebAPI $api)
    {
        $this->api = $api;
    }
    public function index()
    {
        $email = auth()->user()->email;
        AccountHelper::updateLiveAndDemoAccounts($email, $this->api);
        $liveaccount_details = LiveAccount::with('accountType')
            ->where('email', $email)
            ->get();
        return view('internal-transfer', compact('liveaccount_details'));
    }
    public function processTransfer(Request $request)
    {
        $settings = settings();
        $this->api->SetLoggerWriteDebug(config('constants.IS_WRITE_DEBUG_LOG'));
        $this->api->Connect(
            $settings['mt5_server_ip'],
            $settings['mt5_server_port'],
            300,
            $settings['mt5_server_web_login'],
            $settings['mt5_server_web_password']
        );
        $validated = $request->validate([
            'fromAccount' => 'required',
            'toAccount' => 'required|different:fromAccount',
            'transferable_amount' => 'required|numeric|min:1',
        ]);

        $fromAccount = $request->input('fromAccount');
        $toAccount = $request->input('toAccount');
        $transferable_amount = $request->input('transferable_amount');
        $email = auth()->user()->email;
        $ticket = NULL;

        // Withdraw from the first account
        $errorCode = $this->api->TradeBalance($fromAccount, $type = MTEnDealAction::DEAL_BALANCE, -$transferable_amount, 'withdraw', $ticket, true);
        if ($errorCode != MTRetCode::MT_RET_OK) {
            $error = MTRetCode::GetError($errorCode);
            return redirect()->back()->with('error', 'Failed to withdraw from the account.');
        } else {
            DB::transaction(function () use ($email, $fromAccount, $toAccount, $transferable_amount) {
                TradeWithdrawals::create([
                    'email' => $email,
                    'trade_id' => $fromAccount,
                    'withdrawal_amount' => $transferable_amount,
                    'withdraw_type' => 'Internal Transfer',
                    'withdraw_to' => $toAccount,
                    'withdraw_date' => now(),
                    'Status' => 1
                ]);
                // Deposit to the second account
                $errorCode = $this->api->TradeBalance($toAccount, $type = MTEnDealAction::DEAL_BALANCE, $transferable_amount, 'deposit', $ticket, true);
                if ($errorCode != MTRetCode::MT_RET_OK) {
                    $error = MTRetCode::GetError($errorCode);
                    return redirect()->back()->with('error', 'Deposit Failed.');
                } else {
                    // Log deposit
                    TradeDeposits::create([
                        'email' => $email,
                        'trade_id' => $toAccount,
                        'deposit_amount' => $transferable_amount,
                        'deposit_type' => 'Internal Transfer',
                        'deposit_from' => $fromAccount,
                        'status' => 1,
                    ]);
                    TotalBalance::create([
                        'email' => $email,
                        'trade_id' => $toAccount,
                        'trading_deposited' => $transferable_amount,
                        'deposit_type' => 'Internal Transfer',
                    ]);
                }
            });
        }
        return redirect()->back()->with('success', 'Internal Transfer Successfully Done');
    }
}
