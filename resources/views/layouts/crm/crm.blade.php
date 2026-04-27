<!DOCTYPE html>
<html lang="en">
{{--

<head> --}}
    <!-- Include head content like meta tags, title, etc. -->
    @include('layouts.crm.partials.header') <!-- Include the header partial -->
    {{--
</head> --}}
{{--

<body> --}}
    <!-- Main Content -->
    @yield('content')

    @php
        $reviewPopupCampaign = new \App\Support\PopupCampaigns\ReviewPopupCampaign($settings);
        $reviewPopupPopupKey = $reviewPopupCampaign->key();
        $reviewPopupCurrentUser = auth()->user();
        $reviewPopupCurrentRouteName = optional(request()->route())->getName();
        $reviewPopupEligibleByUserRules = $reviewPopupCampaign->isUserEligible($reviewPopupCurrentUser);
        $reviewPopupShouldRender = $reviewPopupCampaign->canRender(request())
            && $reviewPopupEligibleByUserRules;
        $reviewPopupAlreadySeen = $reviewPopupCurrentUser
            ? \App\Models\PopupImpression::where('user_id', $reviewPopupCurrentUser->id)
                ->where('popup_key', $reviewPopupPopupKey)
                ->exists()
            : false;
        $reviewPopupAutoOpen = $reviewPopupShouldRender
            && !$reviewPopupAlreadySeen
            && $reviewPopupCampaign->shouldAutoOpen();
    @endphp

    @include('components.review-popup', [
        'popupId' => $settings['review_popup_id'] ?? 'globalReviewPopup',
        'enabled' => $reviewPopupShouldRender && !$reviewPopupAlreadySeen,
        'showOnLoad' => $reviewPopupAutoOpen,
        'delayMs' => $reviewPopupCampaign->delayMs(),
        'popupKey' => $reviewPopupPopupKey,
        'impressionUrl' => route('popup-impressions.review-popup'),
        'metricsDismissUrl' => route('popup-impressions.review-popup.dismiss'),
        'metricsClickUrl' => route('popup-impressions.review-popup.click'),
        'currentRouteName' => $reviewPopupCurrentRouteName,
        'localStorageFallbackKey' => 'popup-impression:' . $reviewPopupPopupKey . ':' . ($reviewPopupCurrentUser->id ?? 'guest'),
        'logo' => !empty($settings['review_popup_logo']) ? asset($settings['review_popup_logo']) : (isset($settings['admin_sidebar_logo']) ? asset($settings['admin_sidebar_logo']) : asset('assets/images/logo-dark.png')),
        'logoAlt' => $settings['review_popup_logo_alt'] ?? 'LQH Markets',
        'title' => $settings['review_popup_title'] ?? 'Enjoying the Platform?',
        'descriptionLine1' => $settings['review_popup_description_line_1'] ?? "If you've had a positive experience trading with us, we'd greatly appreciate you sharing your feedback.",
        'descriptionLine2' => $settings['review_popup_description_line_2'] ?? 'Your review helps other traders discover our platform and supports our mission to keep improving the trading experience for everyone.',
        'promptText' => $settings['review_popup_prompt_text'] ?? 'Take a moment to share your experience on Trustpilot.',
        'ctaText' => $settings['review_popup_cta_text'] ?? 'Leave a Review on',
        'ctaBrand' => $settings['review_popup_cta_brand'] ?? 'Trustpilot',
        'showCtaBrandIcon' => ($settings['review_popup_show_brand_icon'] ?? '1') === '1',
        'ctaUrl' => $settings['review_popup_cta_url'] ?? 'https://www.trustpilot.com/review/lqhmarkets.com',
    ])

    <!-- Include footer partial -->
    @include('layouts.crm.partials.footer')
    {{--
</body>

</html> --}}