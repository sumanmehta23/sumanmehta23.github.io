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
        $reviewPopupPagesRaw = (string) ($settings['review_popup_pages'] ?? 'liveAccounts');
        $reviewPopupPages = collect(preg_split('/[\r\n,]+/', $reviewPopupPagesRaw))
            ->map(fn($page) => trim($page))
            ->filter()
            ->values();

        $currentPath = trim(request()->path(), '/');
        $currentRouteName = optional(request()->route())->getName();

        $reviewPopupEnabledByPage = $reviewPopupPages->isNotEmpty() && $reviewPopupPages->contains(function ($page) use ($currentPath, $currentRouteName) {
            return \Illuminate\Support\Str::is($page, $currentPath)
                || (!empty($currentRouteName) && \Illuminate\Support\Str::is($page, $currentRouteName));
        });

        $reviewPopupEligibleByUserRules = false;
        if (auth()->check()) {
            $currentUser = auth()->user();

            $hasCompletedFirstWithdrawal =
                \App\Models\TradeWithdrawals::where('user_id', $currentUser->id)
                ->where('withdraw_type', 'Trade Withdrawal')
                ->where('status', 1)
                ->exists()
                || \App\Models\WalletWithdraw::where('user_id', $currentUser->id)
                ->where('withdraw_type', 'Wallet Withdrawal')
                ->where('status', 1)
                ->exists();

            $hasFlaggedAccount = \App\Models\Account::where('user_id', $currentUser->id)
                ->where('sync_status', 'flagged')
                ->exists();

            $isBannedUser = \App\Models\RestrictIps::where('email', $currentUser->email)
                ->whereNull('deleted_at')
                ->where('block_reason', 'General_Ban')
                ->exists();

            $hasLiveAccountOpenAtLeast3Days = \App\Models\Account::where('user_id', $currentUser->id)
                ->where('demo', false)
                ->where(function ($query) {
                    $query->whereNull('created_at')
                        ->orWhere('created_at', '<=', now()->subDays(3));
                })
                ->exists();

            $reviewPopupEligibleByUserRules = $hasCompletedFirstWithdrawal
                && !$hasFlaggedAccount
                && !$isBannedUser
                && $hasLiveAccountOpenAtLeast3Days;
        }
    @endphp

    @include('components.review-popup', [
        'popupId' => $settings['review_popup_id'] ?? 'globalReviewPopup',
        'enabled' => ($settings['review_popup_enabled'] ?? '1') === '1'
            && $reviewPopupEnabledByPage
            && $reviewPopupEligibleByUserRules,
        'showOnLoad' => ($settings['review_popup_show_on_load'] ?? '1') === '1',
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