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

    public function dismissReviewPopup(Request $request)
    {
        return $this->recordReviewPopupMetric($request, 'dismissed_at');
    }

    public function clickReviewPopup(Request $request)
    {
        return $this->recordReviewPopupMetric($request, 'cta_clicked_at');
    }

    private function recordReviewPopupMetric(Request $request, string $column): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'popup_key' => ['required', 'string', 'max:120'],
        ]);

        $campaign = new ReviewPopupCampaign(settings());
        $user = $request->user();

        if (!$user || $request->input('popup_key') !== $campaign->key()) {
            return response()->json(['ok' => false], 422);
        }

        $impression = PopupImpression::where('user_id', $user->id)
            ->where('popup_key', $campaign->key())
            ->first();

        if (!$impression || $impression->{$column} !== null) {
            return response()->json(['ok' => true]);
        }

        $impression->update([$column => now()]);

        return response()->json(['ok' => true]);
    }
}
