@php
    $popupId = $popupId ?? 'lqhReviewPopup';
    $enabled = $enabled ?? true;
    $showOnLoad = $showOnLoad ?? true;

    $logo = $logo ?? asset('assets/images/logo-dark.png');
    $logoAlt = $logoAlt ?? 'LQH Markets';
    $title = $title ?? 'Enjoying the Platform?';
    $descriptionLine1 = $descriptionLine1 ?? "If you've had a positive experience trading with us, we'd greatly appreciate you sharing your feedback.";
    $descriptionLine2 = $descriptionLine2 ?? 'Your review helps other traders discover our platform and supports our mission to keep improving the trading experience for everyone.';
    $promptText = $promptText ?? 'Take a moment to share your experience on Trustpilot.';
    $ctaText = $ctaText ?? 'Leave a Review on';
    $ctaBrand = $ctaBrand ?? 'Trustpilot';
    $showCtaBrandIcon = isset($showCtaBrandIcon) ? (bool) $showCtaBrandIcon : true;
    $ctaUrl = $ctaUrl ?? 'https://www.trustpilot.com/review/lqhmarkets.com';
    $delayMs = isset($delayMs) ? max(0, (int) $delayMs) : 0;
    $popupKey = $popupKey ?? null;
    $impressionUrl = $impressionUrl ?? null;
    $currentRouteName = $currentRouteName ?? null;
    $localStorageFallbackKey = $localStorageFallbackKey ?? null;
    $metricsDismissUrl = $metricsDismissUrl ?? null;
    $metricsClickUrl = $metricsClickUrl ?? null;
@endphp

@if ($enabled)
    @once
        <style>
            .lqh-review-popup {
                position: fixed;
                inset: 0;
                z-index: 1060;
                display: none;
                align-items: center;
                justify-content: center;
                padding: 1rem;
            }

            .lqh-review-popup.is-visible {
                display: flex;
            }

            .lqh-review-popup__backdrop {
                position: absolute;
                inset: 0;
                background: rgba(17, 24, 39, 0.55);
                backdrop-filter: blur(3px);
            }

            .lqh-review-popup__card {
                position: relative;
                z-index: 1;
                width: 100%;
                max-width: 640px;
                max-height: calc(100dvh - 2rem);
                overflow-y: auto;
                margin: 0;
                background: #ffffff;
                border-radius: 18px;
                box-shadow: 0 20px 50px rgba(15, 23, 42, 0.3);
                padding: 0.9rem 0.9rem 1rem;
            }

            .lqh-review-popup__close {
                position: absolute;
                top: 0.65rem;
                right: 0.65rem;
                width: 2rem;
                height: 2rem;
                border: 0;
                border-radius: 9999px;
                background: #eef2f7;
                color: #475569;
                font-size: 1.2rem;
                line-height: 1;
                cursor: pointer;
                display: inline-flex;
                align-items: center;
                justify-content: center;
            }

            .lqh-review-popup__logo {
                display: block;
                width: 168px;
                max-width: 72%;
                margin: 0.3rem auto 0.75rem;
                height: auto;
            }

            .lqh-review-popup__message {
                background: #e9f2f0;
                border: 1px solid #b8d8d1;
                border-radius: 12px;
                padding: 1.1rem 0.95rem;
                text-align: center;
            }

            .lqh-review-popup__title {
                margin: 0 0 0.7rem;
                color: #005b55;
                font-size: 1.6rem;
                font-weight: 700;
                line-height: 1.2;
            }

            .lqh-review-popup__copy {
                margin: 0;
                color: #202735;
                font-size: 1rem;
                line-height: 1.5;
            }

            .lqh-review-popup__copy + .lqh-review-popup__copy {
                margin-top: 0.8rem;
            }

            .lqh-review-popup__prompt {
                margin: 1rem 0 0.8rem;
                text-align: center;
                color: #111827;
                font-size: 1.04rem;
                line-height: 1.45;
            }

            .lqh-review-popup__cta {
                display: block;
                width: 100%;
                border: 0;
                border-radius: 10px;
                text-align: center;
                text-decoration: none;
                background: #015a4f;
                color: #ffffff;
                font-size: 1.2rem;
                font-weight: 500;
                line-height: 1.2;
                padding: 0.95rem 0.8rem;
                transition: transform 0.2s ease, box-shadow 0.2s ease, background-color 0.2s ease;
            }

            .lqh-review-popup__cta-content {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                gap: 0.5rem;
            }

            .lqh-review-popup__cta-brand {
                display: inline-flex;
                align-items: center;
                gap: 0.28rem;
            }

            .lqh-review-popup__cta-icon {
                width: 1.2rem;
                height: 1.2rem;
                color: #00b67a;
                flex: 0 0 auto;
            }

            .lqh-review-popup__cta:hover,
            .lqh-review-popup__cta:focus {
                color: #ffffff;
                background: #00463d;
                transform: translateY(-1px);
                box-shadow: 0 10px 22px rgba(1, 90, 79, 0.25);
            }

            .lqh-review-popup__cta:focus-visible {
                outline: 2px solid #1d9d92;
                outline-offset: 2px;
            }

            .lqh-review-popup-open {
                overflow: hidden;
            }

            @media (min-width: 576px) {
                .lqh-review-popup__card {
                    padding: 1.05rem 1.1rem 1.15rem;
                }

                .lqh-review-popup__logo {
                    width: 190px;
                    margin-bottom: 0.95rem;
                }

                .lqh-review-popup__message {
                    padding: 1.45rem 1.4rem;
                }

                .lqh-review-popup__title {
                    font-size: 2.05rem;
                }

                .lqh-review-popup__copy {
                    font-size: 1.03rem;
                }

                .lqh-review-popup__prompt {
                    margin-top: 1.15rem;
                    font-size: 1.06rem;
                }
            }
        </style>
    @endonce

    <div id="{{ $popupId }}" class="lqh-review-popup" role="dialog" aria-modal="true" aria-labelledby="{{ $popupId }}Title"
        @if (!empty($popupKey)) data-review-popup-key="{{ $popupKey }}" @endif
        @if (!empty($metricsDismissUrl)) data-review-popup-dismiss-url="{{ $metricsDismissUrl }}" @endif
        @if (!empty($metricsClickUrl)) data-review-popup-click-url="{{ $metricsClickUrl }}" @endif>
        <div class="lqh-review-popup__backdrop" data-review-popup-close="true"></div>
        <div class="lqh-review-popup__card">
            <button type="button" class="lqh-review-popup__close" data-review-popup-close="true" aria-label="Close popup">
                &times;
            </button>

            <img src="{{ $logo }}" alt="{{ $logoAlt }}" class="lqh-review-popup__logo">

            <div class="lqh-review-popup__message">
                <h2 id="{{ $popupId }}Title" class="lqh-review-popup__title">{{ $title }}</h2>
                <p class="lqh-review-popup__copy">{{ $descriptionLine1 }}</p>
                <p class="lqh-review-popup__copy">{{ $descriptionLine2 }}</p>
            </div>

            <p class="lqh-review-popup__prompt">{{ $promptText }}</p>

            <a href="{{ $ctaUrl }}" class="lqh-review-popup__cta" target="_blank" rel="noopener noreferrer">
                <span class="lqh-review-popup__cta-content">
                    <span>{{ $ctaText }}</span>
                    @if (!empty($ctaBrand))
                        <span class="lqh-review-popup__cta-brand">
                            @if ($showCtaBrandIcon)
                                <svg class="lqh-review-popup__cta-icon" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                                    <path fill="currentColor" d="M12 1.9l3.1 6.3 7 .9-5.1 5 1.2 7-6.2-3.3-6.2 3.3 1.2-7-5.1-5 7-.9z"/>
                                </svg>
                            @endif
                            <span>{{ $ctaBrand }}</span>
                        </span>
                    @endif
                </span>
            </a>
        </div>
    </div>

    @once
        <script>
            (function() {
                if (window.lqhReviewPopup) {
                    return;
                }

                var bodyClassName = 'lqh-review-popup-open';

                var getOpenCount = function() {
                    return document.querySelectorAll('.lqh-review-popup.is-visible').length;
                };

                var addBodyLockIfNeeded = function() {
                    if (getOpenCount() > 0) {
                        document.body.classList.add(bodyClassName);
                    }
                };

                var removeBodyLockIfNeeded = function() {
                    if (getOpenCount() === 0) {
                        document.body.classList.remove(bodyClassName);
                    }
                };

                var getCsrfToken = function() {
                    var meta = document.querySelector('meta[name="csrf-token"]');

                    return meta ? meta.getAttribute('content') : '';
                };

                var buildApi = function(root) {
                    var metricsSentDismiss = false;
                    var metricsSentCta = false;

                    var postMetric = function(url) {
                        if (!url) {
                            return;
                        }

                        var key = root.getAttribute('data-review-popup-key');
                        if (!key) {
                            return;
                        }

                        var body = JSON.stringify({ popup_key: key });

                        try {
                            fetch(url, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': getCsrfToken()
                                },
                                credentials: 'same-origin',
                                body: body,
                                keepalive: true
                            }).catch(function() {});
                        } catch (e) {}
                    };

                    var open = function() {
                        root.classList.add('is-visible');
                        addBodyLockIfNeeded();
                    };

                    var close = function() {
                        var wasVisible = root.classList.contains('is-visible');
                        root.classList.remove('is-visible');
                        removeBodyLockIfNeeded();

                        if (wasVisible && !metricsSentDismiss) {
                            metricsSentDismiss = true;
                            postMetric(root.getAttribute('data-review-popup-dismiss-url'));
                        }
                    };

                    var init = function(options) {
                        var shouldShow = !options || typeof options.shouldShow === 'undefined' ? true : !!options.shouldShow;
                        if (shouldShow) {
                            open();
                        } else {
                            close();
                        }
                    };

                    root.addEventListener('click', function(event) {
                        var cta = event.target.closest('.lqh-review-popup__cta');
                        if (cta && root.contains(cta)) {
                            if (!metricsSentCta) {
                                metricsSentCta = true;
                                postMetric(root.getAttribute('data-review-popup-click-url'));
                            }

                            return;
                        }

                        if (event.target.closest('[data-review-popup-close="true"]')) {
                            close();
                        }
                    });

                    return {
                        open: open,
                        close: close,
                        init: init
                    };
                };

                var registry = {};

                window.lqhReviewPopup = {
                    register: function(id) {
                        if (!id || registry[id]) {
                            return registry[id] || null;
                        }

                        var root = document.getElementById(id);
                        if (!root) {
                            return null;
                        }

                        registry[id] = buildApi(root);
                        return registry[id];
                    },
                    get: function(id) {
                        return registry[id] || null;
                    },
                    open: function(id) {
                        var instance = this.register(id);
                        if (instance) {
                            instance.open();
                        }
                    },
                    close: function(id) {
                        var instance = this.register(id);
                        if (instance) {
                            instance.close();
                        }
                    },
                    init: function(id, options) {
                        var instance = this.register(id);
                        if (instance) {
                            instance.init(options);
                        }
                    }
                };

                document.addEventListener('keydown', function(event) {
                    if (event.key !== 'Escape') {
                        return;
                    }

                    var openedPopups = document.querySelectorAll('.lqh-review-popup.is-visible');
                    if (!openedPopups.length) {
                        return;
                    }

                    var lastPopup = openedPopups[openedPopups.length - 1];
                    if (lastPopup && lastPopup.id) {
                        window.lqhReviewPopup.close(lastPopup.id);
                    }
                });
            })();
        </script>
    @endonce

    <script>
        (function() {
            var popupId = @json($popupId);
            var showOnLoad = @json((bool) $showOnLoad);
            var delayMs = @json($delayMs);
            var popupKey = @json($popupKey);
            var impressionUrl = @json($impressionUrl);
            var currentRouteName = @json($currentRouteName);
            var localStorageFallbackKey = @json($localStorageFallbackKey);
            var currentPath = window.location.pathname.replace(/^\/+|\/+$/g, '');

            var markLocalFallback = function() {
                if (!localStorageFallbackKey || typeof window.localStorage === 'undefined') {
                    return;
                }

                try {
                    window.localStorage.setItem(localStorageFallbackKey, '1');
                } catch (e) {}
            };

            var hasLocalFallback = function() {
                if (!localStorageFallbackKey || typeof window.localStorage === 'undefined') {
                    return false;
                }

                try {
                    return window.localStorage.getItem(localStorageFallbackKey) === '1';
                } catch (e) {
                    return false;
                }
            };

            var requestFirstImpression = function() {
                if (!impressionUrl || !popupKey) {
                    return Promise.resolve(showOnLoad);
                }

                if (hasLocalFallback()) {
                    return Promise.resolve(false);
                }

                return fetch(impressionUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                    },
                    body: JSON.stringify({
                        popup_key: popupKey,
                        current_path: currentPath,
                        current_route_name: currentRouteName
                    })
                }).then(function(response) {
                    if (!response.ok) {
                        throw new Error('Failed to store popup impression.');
                    }

                    return response.json();
                }).then(function(data) {
                    if (data && data.should_show) {
                        markLocalFallback();
                        return true;
                    }

                    return false;
                }).catch(function() {
                    if (hasLocalFallback()) {
                        return false;
                    }

                    markLocalFallback();
                    return showOnLoad;
                });
            };

            var initCurrentPopup = function() {
                if (!window.lqhReviewPopup) {
                    return;
                }

                if (!showOnLoad) {
                    window.lqhReviewPopup.init(popupId, {
                        shouldShow: false
                    });
                    return;
                }

                window.setTimeout(function() {
                    requestFirstImpression().then(function(shouldShow) {
                        window.lqhReviewPopup.init(popupId, {
                            shouldShow: !!shouldShow
                        });
                    });
                }, delayMs);
            };

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', initCurrentPopup);
            } else {
                initCurrentPopup();
            }
        })();
    </script>
@endif
