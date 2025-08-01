<?php

namespace App\Http\Controllers;

use App\MT5\MTWebAPI;
use App\MT5\MTRetCode;
use App\Models\Account;
use App\MT5\MTEnDealAction;
use App\Models\TradeDeposit;
use Illuminate\Http\Request;
use App\Models\WalletDeposit;
use App\Models\WalletWithdraw;
use App\Models\BonusTransaction;
use App\Models\InternalTransfer;
use App\Models\TradeWithdrawals;
use App\Http\Controllers\Controller;
use App\Http\Controllers\TradeWithdrawal;
use App\Services\MailService as MailService;


class Transactions extends Controller
{
    protected $mailService;
    protected $api;
    protected $settings;
    public function __construct(MailService $mailService,MTWebAPI $api)
    {
        $this->mailService = $mailService;
        $this->settings = settings();
        $this->api = $api;
    }
    public function index()
    {

        $email = $email = auth()->user()->email;

        $deposit_history1 = WalletDeposit::where('user_id',  auth()->user()->id)
            ->whereIn('deposit_type', ['CryptoChill','CreditCardPayissa'])
            ->orderBy('id', 'desc')
            ->get();

        $deposit_history2 = TradeDeposit::where('user_id',  auth()->user()->id)
            ->whereIn('deposit_type', ['CryptoChill','CreditCardPayissa'])
            ->orderBy('id', 'desc')
            ->get();

        $deposit_history = $deposit_history1->merge($deposit_history2)
            ->values(); // reset the keys

            // dd($wallet_deposit_history);
        // Fetching withdrawal history

        $withdrawal_history1 = WalletWithdraw::where('email', $email)
            ->where('withdraw_type', 'Wallet Withdrawal')
            ->orderBy('withdraw_date', 'desc')
            ->get();

        $withdrawal_history2 = TradeWithdrawals::where('email', $email)
            ->where('withdraw_type', 'Trade Withdrawal')
            ->orderBy('withdraw_date', 'desc')
            ->get();

        $withdrawal_history = $withdrawal_history2->merge($withdrawal_history1)
            ->values(); // reset the keys

        // dd($withdrawal_history);

        // Fetching internal transfers

        $internal_transfer = InternalTransfer::where('email', $email)
            ->with('accountTo','accountFrom')
            ->whereIn('type', ['Internal Transfer','Wallet Withdrawal','Wallet Transfer', 'CRM','IB Withdraw'])
            ->where('status', 1)
            ->orderBy('date', 'desc')
            ->get();
        // dd($internal_transfer);
        // $tradeWithdrawals = TradeWithdrawals::with('account')->whereIn('withdraw_type', ['Internal Transfer','Wallet Withdrawal','Wallet Transfer', 'CRM'])
        //     ->select('id','withdrawal_amount', 'withdraw_type','withdraw_date','email','status','withdraw_to','account_id')
        //     ->where('user_id', auth()->user()->id)
        //     ->get()
        //     ->map(function ($withdrawal) {
        //         // if($withdrawal->withdraw_to){
        //         //     $acc = Account::where('id',$withdrawal->withdraw_to)->first();
        //         // }
        //         return [
        //             'type' => $withdrawal->withdraw_to ? 'Internal Transfer' : 'Withdrawal',
        //             'amount' => $withdrawal->withdrawal_amount,
        //             'transaction_type' => $withdrawal->withdraw_type,
        //             'email' => $withdrawal->email,
        //             'status' => $withdrawal->status,
        //             'it_to' => $withdrawal->withdraw_to ?? 'Wallet',
        //             'it_from' => optional($withdrawal->account)->code ?? 'Wallet',
        //             'source' => 'TDID',
        //             'raw_id' => $withdrawal->id,
        //             'date' => $withdrawal->withdraw_date,
        //         ];
        //     });
        //     // dd($tradeWithdrawals);
        // // Fetch filtered data from TradeDeposit with deposit_amount
        // $tradeDeposits = TradeDeposit::whereIn('deposit_type', ['Internal Transfer','Wallet Withdrawal','Wallet Transfer', 'CRM'])
        //     ->select('id', 'deposit_amount','deposted_date','deposit_type','email','status','code','deposit_from')
        //     ->where('user_id', auth()->user()->id)
        //     ->with('account')
        //     ->get()
        //     ->map(function ($deposit) {
        //         // dd($deposit);
        //         if(optional($deposit->account)->code){
        //             $it_from = optional($deposit->account)->code;
        //         }elseif($deposit->deposit_type == 'Wallet Transfer'){
        //             $it_from = 'Wallet';
        //         }else{
        //             $it_from = 'CRM';
        //         }
        //         return [
        //             'type' => $deposit->deposit_type,
        //             'amount' => $deposit->deposit_amount,
        //             'transaction_type' => $deposit->deposit_type,
        //             'email' => $deposit->email,
        //             'status' => $deposit->status,
        //             'it_to' => $deposit->code,
        //             'it_from' => $it_from, // Safe access
        //             'source' => 'TDID',
        //             'raw_id' => $deposit->id,
        //             'date' => $deposit->deposted_date,
        //         ];
        //     });
        //     // dd($tradeDeposits);
        // if($tradeDeposits->isEmpty() && $tradeWithdrawals->isEmpty()){
        //     $internal_transfer = [];
        // }elseif($tradeDeposits->isEmpty()){
        //     $internal_transfer = $tradeWithdrawals;
        // }else{
        //     $internal_transfer = $tradeDeposits->merge($tradeWithdrawals);
        // }


        // Combine data into a single variable
        // $internal_transfer = $tradeDeposits->merge($tradeWithdrawals);
        // Optional: Sort by date
        $internal_transfer = !empty($internal_transfer) ? $internal_transfer->sortBy('date') : collect();
        // dd($internal_transfer);
        // foreach ($internal_transfer as $key => $value) {
        //     echo ($value).'/n';
        // }
        // die;
        return view('transactions', compact('deposit_history', 'withdrawal_history', 'internal_transfer'));
    }
    public function updateTransaction(Request $request){

        $settings = settings();
        $status = $request->status;
        if ($status == '3') {
            $validatedData = $request->validate([
                'status' => 'required|integer',
                'email' => 'required|email',
                'amount' => 'required|numeric',
            ]);
        }elseif($status == '1'){
            $validatedData = $request->validate([
                'status' => 'required|integer',
                'email' => 'required|email',
                'amount' => 'required|numeric',
            ]);
        }
        $status = $validatedData['status'];
        $email = $validatedData['email'];
        $depositAmount = $validatedData['amount'];
        $did = $request->input('transaction_id');
        // dd($did);
        $transaction_id = $request->input('id');

        $transaction = TradeWithdrawals::whereRaw('id = ?', [$did])->first();

        if($transaction->status == 1){
            return redirect()->back()->with('status', 'Your transaction is already approved.');
        }
        if ($transaction) {
            activity()->causedBy(auth()->user()->id)
                ->withProperties(
                    [
                        'ip' => $request->ip(),
                        'email' => auth()->user()->email,
                        'transaction_id' => $transaction_id,
                        'amount' => $depositAmount,
                        'status' => $status,
                        'remark' => 'Wallet Withdraw Cancel By Client'
                    ])
            ->event('delete')
            ->log('Wallet Withdraw Cancel');
            $transaction->Status =$status;
            $transaction->transaction_id = $transaction_id;
            $transaction->admin_remark = 'Cancelled by User';
            $transaction->save();
            if($status==3){
                $comment = "Deposit";
                $ticket = NULL;

                $settings = settings();

                $this->api->SetLoggerWriteDebug(config('constants.IS_WRITE_DEBUG_LOG'));
                $this->api->Connect(
                    $settings['mt5_server_ip'],
                    $settings['mt5_server_port'],
                    300,
                    $settings['mt5_server_web_login'],
                    $settings['mt5_server_web_password']
                );

                $errorCode = $this->api->TradeBalance($transaction->code, $typed = MTEnDealAction::DEAL_BALANCE, ($transaction->withdrawal_amount + $transaction->transaction_fee), $comment, $ticket, $margin_check = true);

                // if ($errorCode != MTRetCode::MT_RET_OK) {
                //     $error = MTRetCode::GetError($errorCode);
                //     return response()->json([
                //         'success' => false,
                //         'message' => 'Something went wrong',
                //         'error' => $error,
                //     ], 400);
                // }else{

                    $bonus = BonusTransaction::where('account_id', $transaction->account_id)
                            ->where(function ($query) {
                                $query->where('bonus_type', 'Bonus In')
                                    ->orWhere('bonus_type', 'Bonus Out');
                            })
                            ->where('admin_remark', 'LIKE', '%Promo Bonus%')
                            ->selectRaw("
                                SUM(bonus_amount) as total_promo_bonus_amount,
                                SUM(bonus_used) as total_promo_bonus_used
                            ")
                            ->first();
                    $total_promo_bonus = $bonus->total_promo_bonus_amount;
                    $total_promo_bonus_used = $bonus->total_promo_bonus_used;
                    $promo_left = $total_promo_bonus - $total_promo_bonus_used;

                    if ($total_promo_bonus_used) {
                        $promo_deduction = $transaction->promo_deduction;
                        $remaining_deduction = $promo_deduction;
                        $account = Account::where('id', $transaction->account_id)->first();
                        $promos = $account->BonusTransaction()
                            ->where('admin_remark', 'Promo Bonus')
                            ->with('promocode') // assuming 'promocode' is the relation name
                            ->get()
                            ->sortByDesc(function ($transaction) {
                                return $transaction->bonus_used;
                            });

                        foreach ($promos as $promo) {
                            if ($remaining_deduction <= 0) {
                                break;
                            }

                            $deduct_from_this = min($promo->bonus_used, $remaining_deduction);
                            dd($deduct_from_this);
                            $promo->bonus_used -= $deduct_from_this;
                            $promo->save();

                            // Call API only once, when you’ve calculated total to transfer back
                            // Or accumulate and call later if needed

                            // Log this deduction (optional - if tracking partial usage is important)
                            BonusTransaction::create([
                                'email' => $account->email,
                                'user_id' => $transaction->user_id,
                                'account_id' => $account->id,
                                'code' => $account->code,
                                'bonus_amount' => $deduct_from_this,
                                'bonus_type' => 'Bonus In',
                                'status' => 1,
                                'admin_remark' => 'Promo Addition',
                                'bonus_currency' => 'USD',
                            ]);

                            $remaining_deduction -= $deduct_from_this;
                        }

                        // $i = 0;

                        // if ($promo_left > 0 && isset($promos[$i])) {
                        //     $promo = $promos[$i];



                        //     $promo->bonus_used -= $transaction->promo_deduction;
                        //     $promo->save();

                        //     $promo_transfer_back = $transaction->promo_deduction;
                        //     dd($promo_transfer_back);
                        //     if (($error_code2 = $this->api->TradeBalance($account->code, MTEnDealAction::DEAL_BONUS, $promo_transfer_back, 'Promo Addition', $ticket1, true)) !== MTRetCode::MT_RET_OK) {
                        //         return redirect()->back()->with('error', MTRetCode::GetError($error_code2));
                        //     }

                        //     // Record the deduction
                        //     BonusTransaction::create([
                        //         'email' => $account->email,
                        //         'user_id' => $transaction->user_id,
                        //         'account_id' => $account->id,
                        //         'code' => $account->code,
                        //         'bonus_amount' => $transaction->promo_deduction,
                        //         'bonus_type' => 'Bonus In',
                        //         'status' => 1,
                        //         'admin_remark' => 'Promo Addition',
                        //         'bonus_currency' => 'USD',
                        //     ]);
                        // }
                    }
                // }



                if ( ($transaction->payout_res) == NULL) {
                    // Decode the JSON string if it's not null or empty
                    // $payout_res = !empty($transaction->payout_res) ? json_decode($transaction->payout_res, true) : [];
                    // $message = isset($payout_res['message']) ? $payout_res['message'] : '';

                    // if($message){
                        //Send email
                        $from = $settings['email_from_address'];
                        // $transid = "WDID" . $payout_res;
                        $headers = "MIME-Version: 1.0" . "\r\n";
                        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
                        $headers .= 'From:' . $settings['admin_title'] . '<' . $from . '>' . "\r\n";
                        $emailSubject = $settings['admin_title'] . ' - Transaction Cancelled';
                        $content = '<p>
                                        We are pleased to inform you that your withdraw request has been successfully cancelled.
                                    </p>
                                    <p>
                                        The cancelled amount has been credited back to your wallet.
                                    </p>
                                    <p>
                                        Transaction Details
                                    </p>
                                    <p>
                                        Withdrawal Cancelled Amount: '.$depositAmount.'
                                    </p>
                                    <p>
                                        Transaction ID: '.$did.'
                                    </p>
                                    <p>
                                        Withdrawal Date: '.$transaction->withdraw_date.'
                                    </p>
                                    <p>
                                        Withdrawal Type: Wallet Withdrawal
                                    </p>';

                        $templateVars = [
                            'name' => $transaction->user->fullname,
                            'site_link' => $settings['copyright_site_name_text'],
                            'email' => $settings['email_from_address'],
                            'content' => $content,
                            'title_right' => 'Transaction',
                            'subtitle_right' => 'Cancelled',
                            'btn_text' => 'Go To Dashboard',
                        ];
                        $this->mailService->sendEmail($email, $emailSubject, $headers, '', $templateVars);
                    // }
                }
            }
            return redirect()->back()->with('status', 'Transaction Cancelled Successfully');
        } else {
            return redirect()->back()->with('error', 'Transaction Not Found');
        }
    }
    // public function history()
    // {
    //     $email = auth()->user()->email;
    //     $deposit_history = TradeDeposit::with('liveAccount.accountType') // Eager loading relationships
    //         ->where('email', $email)
    //         ->whereNotIn('deposit_type', ['Internal Transfer'])
    //         ->orderBy('id', 'desc')
    //         ->get();
    //     $withdrawal_history = [];
    //     $internal_transfer = [];
    //     return view('transaction-history', compact('deposit_history', 'withdrawal_history', 'internal_transfer'));
    // }
}
