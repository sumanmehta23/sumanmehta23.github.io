@extends('layouts.admin.admin')
@section('content')
    @php
        $reviewPopupMetrics = $reviewPopupMetrics ?? [
            'campaign_key' => '—',
            'users_saw_popup' => 0,
            'users_dismissed_popup' => 0,
            'users_clicked_cta' => 0,
            'pure_dismissals' => 0,
            'clicked_then_dismissed' => 0,
            'pure_dismissal_rate_pct' => null,
            'cta_click_rate_pct' => null,
        ];

        $popupSettingOn = function ($value, $default = true) {
            if ($value === null || $value === '') {
                return $default;
            }
            if (is_bool($value)) {
                return $value;
            }
            if (is_numeric($value)) {
                return (int) $value === 1;
            }

            return in_array(strtolower((string) $value), ['1', 'true', 'on', 'yes'], true);
        };

        $previewLogo = !empty($settings['review_popup_logo'])
            ? asset($settings['review_popup_logo'])
            : (!empty($settings['admin_sidebar_logo']) ? asset($settings['admin_sidebar_logo']) : asset('assets/images/logo-dark.png'));
    @endphp

    <div class="main-content app-content">
        <div class="container-fluid">
            <div class="page-header">
                <h1 class="page-title">Review Popup Settings</h1>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="javascript:void(0);">Dashboard</a></li>
                    <li class="breadcrumb-item">Settings</li>
                    <li class="breadcrumb-item active" aria-current="page">Review Popup Settings</li>
                </ol>
            </div>

            <div class="row">
                <div class="col-12 col-xl-10">
                    <form method="post" action="{{ route('admin.review-popup-settings.update') }}" enctype="multipart/form-data">
                        @csrf
                        <div class="card custom-card">
                            <div class="card-header">
                                <div class="card-title">Review Popup Settings</div>
                            </div>
                            <div class="card-body">
                                <ul class="nav nav-tabs mb-4" id="review-popup-settings-tabs" role="tablist">
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link active" id="review-popup-content-tab" data-bs-toggle="tab"
                                            data-bs-target="#review-popup-content-pane" type="button" role="tab"
                                            aria-controls="review-popup-content-pane" aria-selected="true">
                                            Content
                                        </button>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link" id="review-popup-conditions-tab" data-bs-toggle="tab"
                                            data-bs-target="#review-popup-conditions-pane" type="button" role="tab"
                                            aria-controls="review-popup-conditions-pane" aria-selected="false">
                                            Conditions
                                        </button>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link" id="review-popup-preview-tab" data-bs-toggle="tab"
                                            data-bs-target="#review-popup-preview-pane" type="button" role="tab"
                                            aria-controls="review-popup-preview-pane" aria-selected="false">
                                            Preview
                                        </button>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link" id="review-popup-metrics-tab" data-bs-toggle="tab"
                                            data-bs-target="#review-popup-metrics-pane" type="button" role="tab"
                                            aria-controls="review-popup-metrics-pane" aria-selected="false">
                                            Metrics
                                        </button>
                                    </li>
                                </ul>

                                <div class="tab-content" id="review-popup-settings-tab-content">
                                    <div class="tab-pane fade show active" id="review-popup-content-pane" role="tabpanel"
                                        aria-labelledby="review-popup-content-tab" tabindex="0">
                                        <div class="mb-3 form-check form-switch">
                                            <input type="hidden" name="review_popup_enabled" value="0">
                                            <input class="form-check-input" type="checkbox" role="switch" id="review_popup_enabled"
                                                name="review_popup_enabled" value="1"
                                                {{ $popupSettingOn($settings['review_popup_enabled'] ?? null, true) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="review_popup_enabled">Enable popup</label>
                                        </div>

                                        <div class="mb-3 form-check form-switch">
                                            <input type="hidden" name="review_popup_show_on_load" value="0">
                                            <input class="form-check-input" type="checkbox" role="switch" id="review_popup_show_on_load"
                                                name="review_popup_show_on_load" value="1"
                                                {{ $popupSettingOn($settings['review_popup_show_on_load'] ?? null, true) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="review_popup_show_on_load">Show automatically on page load</label>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label">Popup ID</label>
                                            <input type="text" class="form-control" name="review_popup_id"
                                                value="{{ $settings['review_popup_id'] ?? 'globalReviewPopup' }}">
                                            <small class="text-muted">HTML element id for the popup on CRM pages. Keep unique if multiple popups exist.</small>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label">Campaign Key</label>
                                            <input type="text" class="form-control" name="review_popup_campaign_key"
                                                value="{{ $settings['review_popup_campaign_key'] ?? 'review_popup_v1' }}">
                                            <small class="text-muted">Used for impression tracking and the Metrics tab. Change it to start a new campaign without changing the Popup ID.</small>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label">Target pages (route names or URL paths)</label>
                                            <textarea class="form-control" rows="3" name="review_popup_pages"
                                                placeholder="Examples: dashboard, liveAccounts, trade-*, profile/*">{{ $settings['review_popup_pages'] ?? 'dashboard,liveAccounts' }}</textarea>
                                            <small class="text-muted">Use comma, semicolon, pipe, or new line separated values. Wildcards are supported.</small>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label">Logo Alt Text</label>
                                            <input type="text" class="form-control" name="review_popup_logo_alt"
                                                value="{{ $settings['review_popup_logo_alt'] ?? 'LQH Markets' }}">
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label">Popup Logo (optional)</label>
                                            @if (!empty($settings['review_popup_logo']))
                                                <div class="mb-2">
                                                    <img src="/{{ $settings['review_popup_logo'] }}" alt="Review Popup Logo"
                                                        style="object-fit: contain; max-width: 220px; height: auto;">
                                                </div>
                                            @endif
                                            <input class="form-control" type="file" name="review_popup_logo">
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label">Title</label>
                                            <input type="text" class="form-control" name="review_popup_title"
                                                value="{{ $settings['review_popup_title'] ?? 'Enjoying the Platform?' }}">
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label">Description Line 1</label>
                                            <textarea class="form-control" rows="2"
                                                name="review_popup_description_line_1">{{ $settings['review_popup_description_line_1'] ?? "If you've had a positive experience trading with us, we'd greatly appreciate you sharing your feedback." }}</textarea>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label">Description Line 2</label>
                                            <textarea class="form-control" rows="2"
                                                name="review_popup_description_line_2">{{ $settings['review_popup_description_line_2'] ?? 'Your review helps other traders discover our platform and supports our mission to keep improving the trading experience for everyone.' }}</textarea>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label">Prompt Text</label>
                                            <input type="text" class="form-control" name="review_popup_prompt_text"
                                                value="{{ $settings['review_popup_prompt_text'] ?? 'Take a moment to share your experience on Trustpilot.' }}">
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label">CTA Text</label>
                                            <input type="text" class="form-control" name="review_popup_cta_text"
                                                value="{{ $settings['review_popup_cta_text'] ?? 'Leave a Review on' }}">
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label">CTA Brand</label>
                                            <input type="text" class="form-control" name="review_popup_cta_brand"
                                                value="{{ $settings['review_popup_cta_brand'] ?? 'Trustpilot' }}">
                                        </div>

                                        <div class="mb-3 form-check form-switch">
                                            <input type="hidden" name="review_popup_show_brand_icon" value="0">
                                            <input class="form-check-input" type="checkbox" role="switch" id="review_popup_show_brand_icon"
                                                name="review_popup_show_brand_icon" value="1"
                                                {{ $popupSettingOn($settings['review_popup_show_brand_icon'] ?? null, true) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="review_popup_show_brand_icon">Show CTA brand icon</label>
                                        </div>

                                        <div class="mb-0">
                                            <label class="form-label">CTA URL</label>
                                            <input type="url" class="form-control" name="review_popup_cta_url"
                                                value="{{ $settings['review_popup_cta_url'] ?? 'https://www.trustpilot.com/review/lqhmarkets.com' }}">
                                        </div>
                                    </div>

                                    <div class="tab-pane fade" id="review-popup-conditions-pane" role="tabpanel"
                                        aria-labelledby="review-popup-conditions-tab" tabindex="0">
                                        <div class="border rounded-3 p-3">
                                            <div class="mb-4">
                                                <label class="form-label d-block mb-2">Require First Successful Withdrawal</label>
                                                <div class="form-check form-switch">
                                                    <input type="hidden" name="review_popup_require_withdrawal" value="0">
                                                    <input class="form-check-input" type="checkbox" role="switch" id="review_popup_require_withdrawal"
                                                        name="review_popup_require_withdrawal" value="1"
                                                        {{ $popupSettingOn($settings['review_popup_require_withdrawal'] ?? null, true) ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="review_popup_require_withdrawal">
                                                        Show popup only after the user’s first approved withdrawal
                                                    </label>
                                                </div>
                                            </div>

                                            <div class="mb-4">
                                                <label class="form-label d-block mb-2">Require Trading Account Age</label>
                                                <div class="form-check form-switch mb-2">
                                                    <input type="hidden" name="review_popup_require_live_account_age" value="0">
                                                    <input class="form-check-input" type="checkbox" role="switch" id="review_popup_require_live_account_age"
                                                        name="review_popup_require_live_account_age" value="1"
                                                        {{ $popupSettingOn($settings['review_popup_require_live_account_age'] ?? null, true) ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="review_popup_require_live_account_age">
                                                        Only show when the user has a live trading account open for at least the minimum days below
                                                    </label>
                                                </div>
                                                <input type="number" min="0" class="form-control" name="review_popup_live_account_min_days"
                                                    value="{{ (int) ($settings['review_popup_live_account_min_days'] ?? 3) }}">
                                                <small class="text-muted">Uses the live account <code>created_at</code> date. Enter <code>3</code> for a 3-day rule.</small>
                                            </div>

                                            <div class="mb-4">
                                                <label class="form-label d-block mb-2">Exclude Banned Accounts</label>
                                                <div class="form-check form-switch">
                                                    <input type="hidden" name="review_popup_exclude_banned" value="0">
                                                    <input class="form-check-input" type="checkbox" role="switch" id="review_popup_exclude_banned"
                                                        name="review_popup_exclude_banned" value="1"
                                                        {{ $popupSettingOn($settings['review_popup_exclude_banned'] ?? null, true) ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="review_popup_exclude_banned">
                                                        Hide popup for users with a <code>restrict_ips</code> record where <code>block_reason = General_Ban</code>
                                                    </label>
                                                </div>
                                            </div>

                                            <div class="mb-0">
                                                <label class="form-label d-block mb-2">Exclude Flagged Accounts</label>
                                                <div class="form-check form-switch">
                                                    <input type="hidden" name="review_popup_exclude_flagged" value="0">
                                                    <input class="form-check-input" type="checkbox" role="switch" id="review_popup_exclude_flagged"
                                                        name="review_popup_exclude_flagged" value="1"
                                                        {{ $popupSettingOn($settings['review_popup_exclude_flagged'] ?? null, true) ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="review_popup_exclude_flagged">
                                                        Hide popup for accounts with <code>sync_status = flagged</code>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="tab-pane fade" id="review-popup-preview-pane" role="tabpanel"
                                        aria-labelledby="review-popup-preview-tab" tabindex="0">
                                        <div class="border rounded-3 p-3">
                                            <h5 class="mb-2">Preview</h5>
                                            <p class="text-muted mb-3">
                                                This preview uses the current saved popup content and styling. Click the button below to open the same popup component used on CRM pages.
                                            </p>

                                            <div class="d-flex flex-wrap gap-2 mb-3">
                                                <button type="button" class="btn btn-primary" id="review-popup-preview-open">
                                                    Open Preview
                                                </button>
                                            </div>

                                            <div class="small text-muted">
                                                Active preview rules:
                                                withdrawal {{ $popupSettingOn($settings['review_popup_require_withdrawal'] ?? null, true) ? 'required' : 'optional' }},
                                                live account age {{ $popupSettingOn($settings['review_popup_require_live_account_age'] ?? null, true) ? ((int) ($settings['review_popup_live_account_min_days'] ?? 3)) . ' days' : 'disabled' }},
                                                banned {{ $popupSettingOn($settings['review_popup_exclude_banned'] ?? null, true) ? 'excluded' : 'allowed' }},
                                                flagged {{ $popupSettingOn($settings['review_popup_exclude_flagged'] ?? null, true) ? 'excluded' : 'allowed' }}.
                                            </div>
                                        </div>
                                    </div>

                                    <div class="tab-pane fade" id="review-popup-metrics-pane" role="tabpanel"
                                        aria-labelledby="review-popup-metrics-tab" tabindex="0">
                                        <style>
                                            #review-popup-metrics-pane .lqh-metrics-surface {
                                                background: linear-gradient(180deg, #f1f3f5 0%, #e9ecef 100%);
                                                border: 1px solid rgba(0, 0, 0, 0.06);
                                                border-radius: 0.75rem;
                                            }
                                            #review-popup-metrics-pane .lqh-metrics-stat {
                                                background: #fff;
                                                border: 1px solid rgba(0, 0, 0, 0.06);
                                                border-radius: 0.75rem;
                                                box-shadow: 0 1px 2px rgba(0, 0, 0, 0.04);
                                                transition: box-shadow 0.15s ease, border-color 0.15s ease;
                                            }
                                            #review-popup-metrics-pane .lqh-metrics-stat:hover {
                                                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.06);
                                                border-color: rgba(0, 0, 0, 0.08);
                                            }
                                            #review-popup-metrics-pane .lqh-metrics-stat__value {
                                                font-variant-numeric: tabular-nums;
                                                color: #1e2d4a;
                                                letter-spacing: -0.02em;
                                            }
                                            #review-popup-metrics-pane .lqh-metrics-table-wrap {
                                                background: #fff;
                                                border: 1px solid rgba(0, 0, 0, 0.06);
                                                border-radius: 0.75rem;
                                                box-shadow: 0 1px 2px rgba(0, 0, 0, 0.04);
                                                overflow: hidden;
                                            }
                                            #review-popup-metrics-pane .lqh-metrics-table-wrap .table {
                                                margin-bottom: 0;
                                            }
                                            #review-popup-metrics-pane .lqh-metrics-table-wrap thead th {
                                                font-size: 0.8125rem;
                                                font-weight: 600;
                                                text-transform: uppercase;
                                                letter-spacing: 0.04em;
                                                color: #6c757d;
                                                background: #f8f9fa;
                                                border-bottom: 1px solid rgba(0, 0, 0, 0.08);
                                            }
                                            #review-popup-metrics-pane .lqh-metrics-table-wrap tbody td {
                                                border-color: rgba(0, 0, 0, 0.06);
                                                vertical-align: middle;
                                            }
                                            #review-popup-metrics-pane .lqh-metrics-table-wrap tbody tr:last-child td {
                                                border-bottom: 0;
                                            }
                                            #review-popup-metrics-pane .lqh-metrics-table-wrap thead th,
                                            #review-popup-metrics-pane .lqh-metrics-table-wrap tbody td {
                                                padding-top: 0.875rem;
                                                padding-bottom: 0.875rem;
                                            }
                                            #review-popup-metrics-pane .lqh-metrics-table-wrap tbody td:last-child {
                                                font-variant-numeric: tabular-nums;
                                                color: #1e2d4a;
                                                font-weight: 600;
                                            }
                                        </style>
                                        <div class="lqh-metrics-surface px-3 px-lg-4 py-4 py-lg-5">
                                            <p class="small mb-4 pb-3 mb-lg-4 border-bottom text-muted border-light">
                                                Counts and rates for the current <span class="text-body fw-medium">Campaign Key</span>
                                                <code class="text-danger">{{ $reviewPopupMetrics['campaign_key'] ?? '—' }}</code>.
                                                Data is collected when clients see, close, or click the CTA on CRM pages.
                                            </p>

                                            <div class="row row-cols-2 row-cols-md-3 row-cols-xl-4 g-3 g-lg-4 mb-4 mb-lg-5">
                                                <div class="col">
                                                    <div class="lqh-metrics-stat h-100 d-flex align-items-center justify-content-center">
                                                        <div class="text-center px-3 py-4 px-lg-3 w-100">
                                                            <div class="small text-muted mb-2">Saw the popup</div>
                                                            <div class="fs-2 fw-bold lqh-metrics-stat__value">{{ number_format($reviewPopupMetrics['users_saw_popup'] ?? 0) }}</div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col">
                                                    <div class="lqh-metrics-stat h-100 d-flex align-items-center justify-content-center">
                                                        <div class="text-center px-3 py-4 px-lg-3 w-100">
                                                            <div class="small text-muted mb-2">Clicked the CTA</div>
                                                            <div class="fs-2 fw-bold lqh-metrics-stat__value">{{ number_format($reviewPopupMetrics['users_clicked_cta'] ?? 0) }}</div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col">
                                                    <div class="lqh-metrics-stat h-100 d-flex align-items-center justify-content-center">
                                                        <div class="text-center px-3 py-4 px-lg-3 w-100">
                                                            <div class="small text-muted mb-2">Pure dismissals</div>
                                                            <div class="fs-2 fw-bold lqh-metrics-stat__value">{{ number_format($reviewPopupMetrics['pure_dismissals'] ?? 0) }}</div>
                                                            <div class="small mt-2 text-muted">Dismissed, never clicked</div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col">
                                                    <div class="lqh-metrics-stat h-100 d-flex align-items-center justify-content-center">
                                                        <div class="text-center px-3 py-4 px-lg-3 w-100">
                                                            <div class="small text-muted mb-2">Clicked then dismissed</div>
                                                            <div class="fs-2 fw-bold lqh-metrics-stat__value">{{ number_format($reviewPopupMetrics['clicked_then_dismissed'] ?? 0) }}</div>
                                                            <div class="small mt-2 text-muted">Engaged but also closed</div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="lqh-metrics-table-wrap">
                                                <div class="table-responsive mb-0">
                                                    <table class="table align-middle mb-0">
                                                        <thead>
                                                            <tr>
                                                                <th class="ps-4 text-start">Metric</th>
                                                                <th class="text-end pe-4" style="min-width: 8rem;">Value</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <tr>
                                                                <td class="ps-4">Pure dismissal rate (never clicked)</td>
                                                                <td class="text-end pe-4">
                                                                    @if (($reviewPopupMetrics['pure_dismissal_rate_pct'] ?? null) !== null)
                                                                        {{ $reviewPopupMetrics['pure_dismissal_rate_pct'] }}%
                                                                    @else
                                                                        <span class="text-muted fw-normal">—</span>
                                                                    @endif
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td class="ps-4">CTA click rate (clicked ÷ saw)</td>
                                                                <td class="text-end pe-4">
                                                                    @if (($reviewPopupMetrics['cta_click_rate_pct'] ?? null) !== null)
                                                                        {{ $reviewPopupMetrics['cta_click_rate_pct'] }}%
                                                                    @else
                                                                        <span class="text-muted fw-normal">—</span>
                                                                    @endif
                                                                </td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>

                                            <p class="small mt-4 pt-3 mb-0 border-top text-muted border-light">
                                                A user can both dismiss and click the CTA in the same session; each action is counted once per user.
                                                Changing the <span class="text-body">Campaign Key</span> in Content starts a new campaign for future metrics.
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer text-end" id="review-popup-settings-footer">
                                <input type="submit" class="btn btn-primary" value="Update" name="update">
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @include('components.review-popup', [
        'popupId' => 'adminReviewPopupPreview',
        'enabled' => true,
        'showOnLoad' => false,
        'logo' => $previewLogo,
        'logoAlt' => $settings['review_popup_logo_alt'] ?? 'LQH Markets',
        'title' => $settings['review_popup_title'] ?? 'Enjoying the Platform?',
        'descriptionLine1' => $settings['review_popup_description_line_1'] ?? "If you've had a positive experience trading with us, we'd greatly appreciate you sharing your feedback.",
        'descriptionLine2' => $settings['review_popup_description_line_2'] ?? 'Your review helps other traders discover our platform and supports our mission to keep improving the trading experience for everyone.',
        'promptText' => $settings['review_popup_prompt_text'] ?? 'Take a moment to share your experience on Trustpilot.',
        'ctaText' => $settings['review_popup_cta_text'] ?? 'Leave a Review on',
        'ctaBrand' => $settings['review_popup_cta_brand'] ?? 'Trustpilot',
        'showCtaBrandIcon' => $popupSettingOn($settings['review_popup_show_brand_icon'] ?? null, true),
        'ctaUrl' => $settings['review_popup_cta_url'] ?? 'https://www.trustpilot.com/review/lqhmarkets.com',
    ])

    <script>
        (function() {
            var previewPopupId = 'adminReviewPopupPreview';
            var openButton = document.getElementById('review-popup-preview-open');
            var previewTabButton = document.getElementById('review-popup-preview-tab');
            var metricsTabButton = document.getElementById('review-popup-metrics-tab');
            var contentTabButton = document.getElementById('review-popup-content-tab');
            var conditionsTabButton = document.getElementById('review-popup-conditions-tab');
            var footer = document.getElementById('review-popup-settings-footer');

            var syncFooterVisibility = function() {
                if (!footer) {
                    return;
                }

                var hideFooter = (previewTabButton && previewTabButton.classList.contains('active'))
                    || (metricsTabButton && metricsTabButton.classList.contains('active'));

                footer.style.display = hideFooter ? 'none' : '';
            };

            if (openButton) {
                openButton.addEventListener('click', function() {
                    if (window.lqhReviewPopup) {
                        window.lqhReviewPopup.open(previewPopupId);
                    }
                });
            }

            [previewTabButton, metricsTabButton, contentTabButton, conditionsTabButton].forEach(function(tabButton) {
                if (!tabButton) {
                    return;
                }

                tabButton.addEventListener('shown.bs.tab', syncFooterVisibility);
            });

            syncFooterVisibility();
        })();
    </script>
@endsection
