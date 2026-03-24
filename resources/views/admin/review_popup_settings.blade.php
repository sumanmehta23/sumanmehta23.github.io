@extends('layouts.admin.admin')
@section('content')
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
                <div class="col-lg-8 col-sm-12">
                    <form method="post" action="{{ route('admin.review-popup-settings.update') }}" enctype="multipart/form-data">
                        @csrf
                        <div class="card custom-card">
                            <div class="card-header">
                                <div class="card-title">Review Popup Settings</div>
                            </div>
                            <div class="card-body">
                                <div class="mb-3 form-check form-switch">
                                    <input type="hidden" name="review_popup_enabled" value="0">
                                    <input class="form-check-input" type="checkbox" role="switch" id="review_popup_enabled"
                                        name="review_popup_enabled" value="1"
                                        {{ ($settings['review_popup_enabled'] ?? '1') === '1' ? 'checked' : '' }}>
                                    <label class="form-check-label" for="review_popup_enabled">Enable popup</label>
                                </div>

                                <div class="mb-3 form-check form-switch">
                                    <input type="hidden" name="review_popup_show_on_load" value="0">
                                    <input class="form-check-input" type="checkbox" role="switch" id="review_popup_show_on_load"
                                        name="review_popup_show_on_load" value="1"
                                        {{ ($settings['review_popup_show_on_load'] ?? '1') === '1' ? 'checked' : '' }}>
                                    <label class="form-check-label" for="review_popup_show_on_load">Show automatically on page load</label>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Popup ID</label>
                                    <input type="text" class="form-control" name="review_popup_id"
                                        value="{{ $settings['review_popup_id'] ?? 'globalReviewPopup' }}">
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Target pages (route names or URL paths)</label>
                                    <textarea class="form-control" rows="3" name="review_popup_pages"
                                        placeholder="Examples: liveAccounts, dashboard, trade-deposit, view-account-details">{{ $settings['review_popup_pages'] ?? 'liveAccounts' }}</textarea>
                                    <small class="text-muted">Use comma or new line separated values. Wildcards supported (example: trade-*)</small>
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
                                        {{ ($settings['review_popup_show_brand_icon'] ?? '1') === '1' ? 'checked' : '' }}>
                                    <label class="form-check-label" for="review_popup_show_brand_icon">Show CTA brand icon</label>
                                </div>

                                <div class="mb-0">
                                    <label class="form-label">CTA URL</label>
                                    <input type="url" class="form-control" name="review_popup_cta_url"
                                        value="{{ $settings['review_popup_cta_url'] ?? 'https://www.trustpilot.com/review/lqhmarkets.com' }}">
                                </div>
                            </div>
                            <div class="card-footer text-end">
                                <input type="submit" class="btn btn-primary" value="Update" name="update">
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
