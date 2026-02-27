<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <link rel="icon" href="{{ asset($settings['favicon']) }}">
    <link rel="shortcut icon" href="{{ asset($settings['favicon']) }}">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description"
        content="Your partner in profitable trading. Trade forex, commodities, indices, and cryptocurrencies with low spreads and fast execution">
    <meta name="keywords"
        content="forex broker, forex trading, commodities trading, indices trading, cryptocurrencies trading, low spreads, fast execution">
    <title>{{ $settings['admin_title'] }} - Client Portal</title>
    <link rel="stylesheet" href="{{ asset('assets/css/main.css?v=244.1') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/custom.css?v=4.5') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/lipis/flag-icons@7.2.3/css/flag-icons.min.css" />
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css?v=2&" rel="stylesheet" />
    <script src="{{ asset('assets/js/sweetalert2.all.min.js') }}"></script>
    <script src="{{ asset('assets/js/jquery-3.3.1.min.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <!-- Meta Pixel Code -->
    <script>
        !function (f, b, e, v, n, t, s) {
            if (f.fbq) return; n = f.fbq = function () {
                n.callMethod ?
                n.callMethod.apply(n, arguments) : n.queue.push(arguments)
            };
            if (!f._fbq) f._fbq = n; n.push = n; n.loaded = !0; n.version = '2.0';
            n.queue = []; t = b.createElement(e); t.async = !0;
            t.src = v; s = b.getElementsByTagName(e)[0];
            s.parentNode.insertBefore(t, s)
        }(window, document, 'script',
            'https://connect.facebook.net/en_US/fbevents.js');
        fbq('init', '2659568854245574');
        fbq('track', 'PageView');
    </script>
    <noscript><img height="1" width="1" style="display:none"
            src="https://www.facebook.com/tr?id=2659568854245574&ev=PageView&noscript=1" />
    </noscript>
    <!-- End Meta Pixel Code -->

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body .swal2-container {
            z-index: 1090 !important;
        }

        body .swal2-backdrop-show {
            background-color: rgba(0, 0, 0, 0.4) !important;
            backdrop-filter: blur(2px);
        }

        button.close {
            background: none;
            border: none;
            font-weight: bold;
        }

        :root,
        [data-pc-preset=preset-7],
        [data-pc-preset=preset-7] * {
            --primary-color:
                {{ $settings['sidebar_color'] }}
                !important;
            --bs-btn-active-bg:
                {{ $settings['sidebar_color'] }}
                !important;
            --bs-primary:
                {{ $settings['sidebar_color'] }}
                !important;
            --bs-btn-bg:
                {{ $settings['sidebar_color'] }}
                !important;
            --bs-btn-hover-bg:
                {{ $settings['sidebar_color'] }}
                !important;
            --bs-link-color-rgb:
                {{ $settings['sidebar_color'] }}
                !important;
            --bs-primary-rgb:
                {{ hexToRGB($settings['sidebar_color']) }}
                !important;
            --primary-rgb:
                {{ hexToRGB($settings['sidebar_color']) }}
                !important;
        }

        [data-pc-preset=preset-7] .link-primary {
            color:
                {{ $settings['sidebar_color'] }}
                !important;
        }

        body form [data-pc-preset=preset-7] .link-primary,
        form [data-pc-preset=preset-7] .link-primary,
        [data-pc-preset=preset-7] .link-primary:focus,
        [data-pc-preset=preset-7] .link-primary:hover {
            color: var(--bs-primary) !important;
        }
    </style>
    <script type="text/javascript">
        window.omnisend = window.omnisend || [];
        omnisend.push(["brandID", "691e3cc91ce6ae348df16739"]);
        omnisend.push(["track", "$pageViewed"]);
        !function () {
            var e = document.createElement("script");
            e.type = "text/javascript", e.async = !0,
                e.src = "https://omnisnippet1.com/inshop/launcher-v2.js";
            var t = document.getElementsByTagName("script")[0];
            t.parentNode.insertBefore(e, t)
        }();
    </script>
</head>

<body data-pc-preset="preset-7" data-pc-sidebar-caption="true" data-pc-direction="ltr" data-pc-theme_contrast=""
    data-pc-theme="light" class="" style="padding-right: 0px;">
    <header>
        <!-- Navigation or header content -->
    </header>
    <main>
        @yield('content')
    </main>
    <footer>
        <!-- Footer content -->
    </footer>
    @if (session('success'))
        <script>
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: '{{ session('success') }}',
                showConfirmButton: true
            });
        </script>
    @endif
    @if (session('error'))
        @php
            $errorTitle = session('error_title') ?? 'Something went wrong';
            $retryAfter = session('retry_after');
        @endphp
        <script>
            @if ($retryAfter)
                let retryAfter = {{ $retryAfter }};
                let timerInterval;

                Swal.fire({
                    icon: 'warning',
                    title: '{{ $errorTitle }}',
                    html: '<div id="swal-timer-content">Too many requests. Please wait <strong id="swal-countdown">00:30</strong> before trying again.</div>',
                    showConfirmButton: true,
                    confirmButtonText: 'OK',
                    allowOutsideClick: true,
                    allowEscapeKey: true,
                    backdrop: true,
                    didOpen: () => {
                        document.body.style.overflow = 'hidden';

                        const countdownElement = document.getElementById('swal-countdown');

                        function updateTimer() {
                            if (retryAfter <= 0) {
                                Swal.close();
                                clearInterval(timerInterval);
                                return;
                            }

                            const minutes = Math.floor(retryAfter / 60);
                            const seconds = retryAfter % 60;
                            const timeString = `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;

                            if (countdownElement) {
                                countdownElement.textContent = timeString;
                            }

                            retryAfter--;
                        }

                        updateTimer();
                        timerInterval = setInterval(updateTimer, 1000);
                    },
                    willClose: () => {
                        document.body.style.overflow = 'auto';
                        clearInterval(timerInterval);
                    }
                });
            @else
                Swal.fire({
                    icon: 'warning',
                    title: '{{ $errorTitle }}',
                    html: '{{ session('error') }}',
                    showConfirmButton: true,
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    backdrop: true,
                    didOpen: () => {
                        document.body.style.overflow = 'hidden';
                    },
                    willClose: () => {
                        document.body.style.overflow = 'auto';
                    }
                });
            @endif
        </script>
    @endif

    <!-- Add your scripts here -->
    @stack('scripts')
    @include('components.google-translate')
</body>

</html>