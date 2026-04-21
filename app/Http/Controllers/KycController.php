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
        $applicantId = $payload['applicantId'] ?? null;
        // $subscribeToKlaviyoList = new SubscribeToKlaviyoList();
        
        switch ($type) {
            case 'applicantReviewed':

                $user = User::where("email", $email)->first();
                if (!$user) {
                    return response()->json(['status' => 'false', 'message' => 'User not found']);
                }
                
                // Update existing log entry or create new one
                $this->createOrUpdateKycLog($user, $type, $payload, $applicantId);

                // Update user KYC status and reason
                $this->updateUserKycData($user, $payload);
                
                if ($user->kyc_verify) {

                    return response()->json(['status' => 'true', 'message' => 'Your KYC Already Verified']);
                }
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
                break;

            case 'applicantPending':
            case 'applicantActionPending':

                $user = User::where("email", $email)->first();
                if ($user) {
                    // Update existing log entry or create new one
                    $this->createOrUpdateKycLog($user, $type, $payload, $applicantId);

                    Log::info('KYC Webhook: Applicant pending', [
                        'user_id' => $user->id,
                        'email' => $user->email,
                        'type' => $type
                    ]);
                }

                return response()->json(['status' => 'true', 'message' => 'Status in progress...']);
                break;

            default:
                return response()->json(['status' => 'false', 'message' => 'Status in progress...']);
        }
    }

    /**
     * Create or update KYC log entry based on applicant_id
     */
    private function createOrUpdateKycLog(User $user, string $callbackCode, array $payload, ?string $applicantId = null): void
    {
        try {
            $applicantId = $applicantId ?? ($payload['applicantId'] ?? null);

            if ($applicantId) {
                // Try to find existing log by applicant_id
                $existingLog = KycLog::where('applicant_id', $applicantId)->first();

                if ($existingLog) {
                    // Update existing log
                    $existingLog->update([
                        'callback_code' => json_encode($callbackCode),
                        'callback_payload' => $payload,
                    ]);
                    Log::info('KYC log updated', [
                        'user_id' => $user->id,
                        'applicant_id' => $applicantId
                    ]);
                    return;
                }
            }

            // Create new log if not found
            KycLog::create([
                'client_id' => $user->email,
                'user_id' => $user->id,
                'applicant_id' => $applicantId,
                'callback_code' => json_encode($callbackCode),
                'callback_payload' => $payload,
            ]);
            Log::info('KYC log created', [
                'user_id' => $user->id,
                'applicant_id' => $applicantId
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to create/update KYC log', [
                'user_id' => $user->id,
                'applicant_id' => $applicantId ?? 'unknown',
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Update user KYC status and reason from payload
     */
    private function updateUserKycData(User $user, array $payload): void
    {
        try {
            $reviewAnswer = $payload['reviewResult']['reviewAnswer'] ?? null;

            // Determine status
            $status = match ($reviewAnswer) {
                'GREEN' => 'APPROVED',
                'RED' => 'REJECTED',
                'YELLOW' => 'PENDING',
                default => 'PENDING'
            };

            // Build reason
            $reason = null;
            if ($status === 'APPROVED') {
                $reason = null;
            } elseif ($status === 'REJECTED') {
                $reviewResult = $payload['reviewResult'] ?? [];
                
                // Priority: moderationComment > clientComment > rejectLabels > reviewRejectType
                $reason = $reviewResult['moderationComment'] ?? null;
                if (empty($reason)) {
                    $reason = $reviewResult['clientComment'] ?? null;
                }
                if (empty($reason)) {
                    $rejectLabels = $reviewResult['rejectLabels'] ?? [];
                    if (!empty($rejectLabels)) {
                        $labelsStr = implode(', ', $rejectLabels);
                        $reviewRejectType = $reviewResult['reviewRejectType'] ?? 'UNKNOWN';
                        $reason = "Issue: {$labelsStr} | Status: {$reviewRejectType}";
                    }
                }
                if (empty($reason)) {
                    $reason = $reviewResult['reviewRejectType'] ?? 'KYC verification rejected';
                }
            } elseif ($status === 'PENDING') {
                $reason = 'KYC verification in progress';
            }

            // Truncate reason to reasonable length
            if ($reason && strlen($reason) > 5000) {
                $reason = substr($reason, 0, 5000);
            }

            // Update user with new columns
            $user->update([
                'kyc_status' => $status,
                'kyc_reason' => $reason,
                'kyc_synced_at' => now(),
            ]);

            Log::info('User KYC data updated', [
                'user_id' => $user->id,
                'kyc_status' => $status,
                'has_reason' => !empty($reason)
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to update user KYC data', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
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

