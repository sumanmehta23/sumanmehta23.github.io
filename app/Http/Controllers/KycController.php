<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\KycLog;
use App\Models\KycUpdate;
use App\Events\KycVerifiedEvent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Actions\SubscribeToKlaviyoList;
use Illuminate\Support\Facades\Config;

class KycController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     */
    // {
    //     "applicantId": "67c71370f61f6b0356406d19",
    //     "inspectionId": "67c71370f61f6b0356406d19",
    //     "applicantType": "individual",
    //     "correlationId": "a4cc8c598736a2a00b8e8afbd61e985e",
    //     "levelName": "basic-kyc-level",
    //     "sandboxMode": false,
    //     "externalUserId": "level-4d9aca2e-f5fa-4414-87a2-27b4e41f1773",
    //     "type": "applicantReviewed",
    //     "reviewResult": {
    //       "reviewAnswer": "GREEN"
    //     },
    //     "reviewStatus": "completed",
    //     "createdAt": "2025-03-05 04:41:48+0000",
    //     "createdAtMs": "2025-03-05 04:41:48.704",
    //     "clientId": "lqhmarkets.com"
    //   }
    public function listener(SubscribeToKlaviyoList $subscribeToKlaviyoList)
    {

        $return = file_get_contents('php://input');

        $algoStr = $_SERVER['HTTP_X_PAYLOAD_DIGEST_ALG'];
        $digest = $_SERVER['HTTP_X_PAYLOAD_DIGEST'];


        $algo = match ($algoStr) {
            'HMAC_SHA1_HEX' => 'sha1',
            'HMAC_SHA256_HEX' => 'sha256',
            'HMAC_SHA512_HEX' => 'sha512',
            default => throw new \RuntimeException('Unsupported algorithm'),
        };

        $secretKey = config('services.sumsub.webhook_secret');
        $res = $digest === hash_hmac(
            $algo,
            $return,
            $secretKey
        );
        info(json_encode([$digest, $res, $return]));
        if (!$res) {
            return response()->json(['status' => 'false', 'message' => 'Invalid Request']);
        }
        $payload = json_decode($return, true);

        Log::info("message from sumsub", $payload);

        // info($return);
        $type = $payload['type'];
        $email = $payload['externalUserId'];
        // $subscribeToKlaviyoList = new SubscribeToKlaviyoList();
        if ($type == 'applicantReviewed') {

            $user = User::where("email", $email)->first();
            if (!$user) {
                return response()->json(['status' => 'false', 'message' => 'User not found']);
            }
            if ($user->kyc_verify) {

                KycLog::create([
                    'client_id' => $email,
                    'user_id' => $user->id,
                    'callback_code' => json_encode($type),
                    'callback_payload' => $payload,
                ]);

                return response()->json(['status' => 'true', 'message' => 'Your KYC Already Verified']);
            }
            // Store callback log in the database
            KycLog::create([
                'client_id' => $email,
                'user_id' => $user->id,
                'callback_code' => json_encode($type),
                'callback_payload' => $payload,
            ]);

            // Check review result
            if (isset($payload['reviewResult']['reviewAnswer']) && $payload['reviewResult']['reviewAnswer'] == 'GREEN') {
                // Find the user in the database
                // Update user's KYC status to verified
                $user->kyc_verify = 1;
                $user->save();

                // Fire the KycVerifiedEvent for Omnisend integration
                event(new KycVerifiedEvent($user));

                $list_id = @config('services.klaviyo.list_ids')['KYC_COMPLETED'];
                if ($list_id) {
                    $subscribeToKlaviyoList->handle($user, $list_id);
                }

                return response()->json(['status' => 'true', 'message' => 'KYC Verified']);
            } else {
                return response()->json(['status' => 'false', 'message' => 'Something went wrong. Please try again or Create a Support Ticket']);
            }
        } else {
            return response()->json(['status' => 'false', 'message' => 'Status in progress...']);
        }
    }

    /**
     * Veriff webhook listener.
     *
     * Expects HMAC SHA256 signature in X-Veriff-Signature header.
     */
    public function veriffListener(Request $request, SubscribeToKlaviyoList $subscribeToKlaviyoList)
    {
        $payload = $request->getContent();
        $signatureHeader = $request->header('x-hmac-signature');
        $secret = (string) Config::get('services.veriff.api_secret', '');

        if ($secret === '' || $signatureHeader === null) {
            Log::warning('Veriff webhook received without proper configuration or signature header.');

            return response()->json(['status' => 'false', 'message' => 'Invalid configuration or signature'], 400);
        }

        $expected = hash_hmac('sha256', $payload, $secret);

        if (! hash_equals($expected, $signatureHeader)) {
            Log::warning('Veriff webhook signature mismatch.', [
                'expected' => $expected,
                'received' => $signatureHeader,
            ]);

            return response()->json(['status' => 'false', 'message' => 'Invalid signature'], 400);
        }

        $data = json_decode($payload, true);

        if (! is_array($data) || empty($data['data']['verification'] ?? null)) {
            return response()->json(['status' => 'false', 'message' => 'Invalid payload'], 400);
        }

        $verification = $data['data']['verification'];
        $decision = $verification['decision'] ?? null;
        $email = $data['vendorData'] ?? null;

        Log::info('Veriff webhook received', [
            'verification' => $verification,
            'email' => $email,
            'decision' => $decision,
        ]);

        if (! $email) {
            return response()->json(['status' => 'false', 'message' => 'Missing user identifier'], 400);
        }

        $user = User::where('email', $email)->first();

        if (! $user) {
            return response()->json(['status' => 'false', 'message' => 'User not found'], 404);
        }

        // Store callback log in the database
        KycLog::create([
            'client_id' => $email,
            'user_id' => $user->id,
            'callback_code' => 'VERIFF_WEBHOOK',
            'callback_payload' => $verification,
        ]);

        if ($user->kyc_verify) {
            return response()->json(['status' => 'true', 'message' => 'Your KYC Already Verified']);
        }

        if ($decision === 'approved') {
            $user->kyc_verify = 1;
            $user->save();

            event(new KycVerifiedEvent($user));

            $list_id = @config('services.klaviyo.list_ids')['KYC_COMPLETED'];
            if ($list_id) {
                $subscribeToKlaviyoList->handle($user, $list_id);
            }

            return response()->json(['status' => 'true', 'message' => 'KYC Verified']);
        }

        return response()->json(['status' => 'false', 'message' => 'Verification not approved yet']);
    }
}
