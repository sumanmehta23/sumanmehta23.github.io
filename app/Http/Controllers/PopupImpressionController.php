<?php

namespace App\Http\Controllers;

use App\Models\PopupImpression;
use App\Support\PopupCampaigns\ReviewPopupCampaign;
use Illuminate\Http\Request;

class PopupImpressionController extends Controller
{
    public function storeReviewPopup(Request $request)
    {
        $request->validate([
            'popup_key' => ['required', 'string', 'max:120'],
            'current_path' => ['nullable', 'string', 'max:255'],
            'current_route_name' => ['nullable', 'string', 'max:255'],
        ]);

        $campaign = new ReviewPopupCampaign(settings());
        $user = $request->user();

        if (!$user || $request->input('popup_key') !== $campaign->key()) {
            return response()->json([
                'should_show' => false,
            ], 422);
        }

        $matchesCurrentPage = $campaign->matchesPageContext(
            $request->input('current_path'),
            $request->input('current_route_name')
        );

        if (
            !$campaign->isEnabled()
            || !$matchesCurrentPage
            || !$campaign->shouldAutoOpen()
            || !$campaign->isUserEligible($user)
        ) {
            return response()->json([
                'should_show' => false,
            ]);
        }

        $impression = PopupImpression::firstOrCreate(
            [
                'user_id' => $user->id,
                'popup_key' => $campaign->key(),
            ],
            [
                'shown_at' => now(),
            ]
        );

        return response()->json([
            'should_show' => $impression->wasRecentlyCreated,
        ]);
    }
}
