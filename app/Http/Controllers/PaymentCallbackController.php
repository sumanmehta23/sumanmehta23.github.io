<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Models\WalletDeposit;
use App\Models\TotalBalance;
use App\Models\User;
use Exception;

class PaymentCallbackController extends Controller
{
    public function handleCallback(Request $request)
    {
        $payload = $request->json()->all();
        $signature = $payload['signature'] ?? null;
        $callbackId = $payload['callback_id'] ?? null;

        if ($callbackId !== null) {
            $isValid = $signature === $this->encodeHmac(config('services.cryptochill.callbacktoken'), $callbackId);
        } else {
            $isValid = false;
        }

        if (!$isValid) {
            throw new Exception('Failed to verify CryptoChill callback signature. ' . $callbackId);
        }

        $logData = "IP: " . $request->ip() . "\nPayload: " . json_encode($payload, JSON_PRETTY_PRINT) . "\n";

        if (isset($payload["callback_status"]) && in_array($payload["callback_status"], ["transaction_confirmed", "transaction_complete"])) {
            $passedData = json_decode($payload['transaction']['invoice']['passthrough'], true);

            if (isset($passedData['customerID'])) {
                $logData .= "Customer ID: " . $passedData['customerID'] . "\n";
            }

            Log::channel('cryptochillcallback')->info($logData);

            if (!isset($passedData['depositTo'])) {
                return response("Deposit designation missing..!", 400);
            }

            $depositTo = $passedData['depositTo'];
            $amount = $payload['transaction']['amount']['paid']['quotes']['USD'];
            $email = $passedData['customerEmail'];
            $transactionId = $payload['transaction']['id'];
            $depositType = "CryptoChill";
            $callbackData = json_encode($payload);
            $callbackCode = json_encode($payload['transaction']['status']);

            // Check if transaction already exists
            if (WalletDeposit::where('transaction_id', $transactionId)->exists()) {
                return response("true", 200);
            }

            if ($depositTo === "wallet") {
                return $this->handleWalletDeposit($email, $depositType, $amount, $transactionId, $callbackData, $callbackCode, $logData);
            } else {
                if (!isset($passedData["accountID"])) {
                    return response("Account ID missing..!", 400);
                }

                $logData .= "Credit directly to Account ID: " . $passedData['accountID'] . "\n";
                Log::info($logData);

                return $this->handleTradeBalance($passedData, $amount, $email, $logData);
            }
        }

        return response("Invalid callback status.", 400);
    }

    protected function encodeHmac($token, $data)
    {
        return hash_hmac('sha256', $data, $token);
    }

    private function handleWalletDeposit($email, $depositType, $amount, $transactionId, $callbackData, $callbackCode, $logData)
    {
        try {
            // Get user_id from email to match the pattern used in Wallet.php secureProcessPayment
            $user = User::where('email', $email)->first();
            $userId = $user ? $user->id : null;

            DB::transaction(function () use ($email, $userId, $depositType, $amount, $transactionId, $callbackData, $callbackCode) {
                WalletDeposit::create([
                    'user_id' => $userId,
                    'email' => $email,
                    'deposit_type' => $depositType,
                    'deposit_amount' => $amount,
                    'company_bank' => $depositType,
                    'transaction_id' => $transactionId,
                    'status' => 1,
                    'currency_type' => 'USD',
                    'callback_data' => $callbackData,
                    'callback_code' => $callbackCode
                ]);

                TotalBalance::create([
                    'user_id' => $userId,
                    'email' => $email,
                    'deposit_amount' => $amount
                ]);

                // Only log activity if user is authenticated
                if (auth()->check()) {
                    activity()->causedBy(auth()->user()->id)
                        ->withProperties(
                            [
                                'ip' => request()->ip(),
                                'email' => auth()->user()->email,
                                'payment_amount' => $amount,
                                'payment_type' => $depositType,
                                'transaction_id' => $transactionId,
                                'remark' => 'Wallet Deposits'
                            ]
                        )
                        ->event('create')
                        ->log('Wallet Deposit');
                }
            });

            Log::info($logData . "Transaction Confirmed\n");
            return response("true", 200);
        } catch (Exception $e) {
            $error = "Something went wrong...!" . $e->getMessage();
            Log::error($logData . "Transaction Failed\n" . $error);
            return response($error, 500);
        }
    }

    private function handleTradeBalance($passedData, $amount, $email, $logData)
    {
        $login = $passedData['accountID'];
        $comment = "Deposit";
        $ticket = null;
        $tradingDeposited = $amount;

        // Assuming `api` service is injected and provides `TradeBalance` method
        $api = resolve('ApiService'); // Replace with your actual service name
        $errorCode = $api->TradeBalance($login, 'DEAL_BALANCE', $amount, $comment, $ticket, true);

        if ($errorCode !== 'MT_RET_OK') {
            $error = "MT5: " . $errorCode;
            return response($error, 500);
        }

        DB::transaction(function () use ($email, $tradingDeposited, $login) {
            DB::table('trade_deposit')->insert([
                'email' => $email,
                'code' => $login,
                'deposit_amount' => $tradingDeposited,
                'deposit_type' => 'CryptoChill'
            ]);

            TotalBalance::create([
                'email' => $email,
                'trading_deposited' => $tradingDeposited
            ]);
        });

        Log::info($logData . "Transaction Confirmed\n");
        return response("true", 200);
    }
}
