@php
    $user = auth()->user();
@endphp
@if (isset($user))
    @if ($user->profile_image_url == null)
        @php
            $profile_image_url = asset('assets/images/user.png');
        @endphp
    @else
        @php
            $profile_image_url = Storage::url('profile_images/' . $user->profile_image_url);
        @endphp
    @endif
@else
    @php
        $profile_image_url = asset('assets/images/user.png');
    @endphp
@endif

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <link rel="icon" href="{{ asset($settings['favicon']) }}">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description"
        content="Your partner in profitable trading. Trade forex, commodities, indices, and cryptocurrencies with low spreads and fast execution">
    <meta name="keywords"
        content="forex broker, forex trading, commodities trading, indices trading, cryptocurrencies trading, low spreads, fast execution">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $settings['admin_title'] }} - Client Portal</title>
    <script src="{{ asset('assets/js/vuejs-datepicker.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('assets/js/vue-simple-search-dropdown.min.js') }}"></script>
    <link rel="stylesheet" crossorigin="anonymous" href="{{ asset('assets/css/main.css?v=244.1') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
    <link rel="stylesheet" href="{{ asset('admin_assets/assets/icon-fonts/feather/feather-v2.css?v=5') }}">
    <link rel="stylesheet" type="text/css" href="https://unpkg.com/@phosphor-icons/web@2.1.1/src/duotone/style.css" />
    <link rel="stylesheet" crossorigin="anonymous" href="{{ asset('assets/css/custom.css?v=4.5') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/lipis/flag-icons@7.2.3/css/flag-icons.min.css">

    <script src="https://code.jquery.com/jquery-3.3.1.min.js"
        integrity="sha256-FgpCb/KJQlLNfOu91ta32o/NMZxltwRo8QtmkMRdAu8=" crossorigin="anonymous"></script>
    <script type="text/javascript"
        src="https://cdnjs.cloudflare.com/ajax/libs/jQuery-slimScroll/1.3.8/jquery.slimscroll.min.js"></script>
    <script src="{{ asset('assets1/vendors/datatables.net/jquery.dataTables.js') }}"></script>
    <script src="{{ asset('assets/js/sweetalert2.all.min.js') }}"></script>
    <script>
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
    </script>

    <!-- Meta Pixel Code -->
    <script>
        !function(f,b,e,v,n,t,s)
        {if(f.fbq)return;n=f.fbq=function(){n.callMethod?
        n.callMethod.apply(n,arguments):n.queue.push(arguments)};
        if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
        n.queue=[];t=b.createElement(e);t.async=!0;
        t.src=v;s=b.getElementsByTagName(e)[0];
        s.parentNode.insertBefore(t,s)}(window, document,'script',
        'https://connect.facebook.net/en_US/fbevents.js');
        fbq('init', '2659568854245574');
        fbq('track', 'PageView');
    </script>
    <noscript><img height="1" width="1" style="display:none"
        src="https://www.facebook.com/tr?id=2659568854245574&ev=PageView&noscript=1"
        />
    </noscript>
    <!-- End Meta Pixel Code -->

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @yield('styles')
    <?php
$marginTopStyle = ''; // Default value
if (app()->environment('local') || config('services.sales.promotion') || app()->environment('development')) {
    $marginTopStyle = 'style="margin-top: 40px;"';
}
    ?>

    <style>
        .pc-sidebar .navbar-content {
            /* overflow-y: scroll; */
        }

        body .swal2-container {
            z-index: 999999999999999999 !important;
        }

        body .swal2-backdrop-show {
            background-color: rgba(0, 0, 0, 0.4) !important;
            backdrop-filter: blur(2px);
        }

        a.btn.btn-outline-light {
            color: var(--bs-primary);
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
            ;
            --bs-btn-active-bg:
                {{ $settings['sidebar_color'] }}
            ;
            --bs-primary:
                {{ $settings['sidebar_color'] }}
            ;
            --bs-btn-bg: #fff !important;
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

        :root [data-pc-theme="dark"],
        :root [data-pc-theme="dark"] * {
            --bs-primary: #fff;
            --bs-btn-bg: transparent !important;
            --bs-black-rgb: 255, 255, 255 !important;
            --pc-sidebar-active-color: #fff;
            --bs-blue: var(--bs-primary);
            --bs-primary-rgb: 229, 138, 0;
            --bs-primary-light: #fcf3e6;
            --bs-link-color: var(--bs-primary);
            --bs-link-color-rgb: 229, 138, 0;
            --bs-link-hover-color: var(--bs-primary);
            --bs-link-hover-color-rgb: to-rgb(shift-color($ pc-primary, $ link-shade-percentage));
            --dt-row-selected: 229, 138, 0;
            --bs-btn-disabled-bg: #000000;
        }

        [data-pc-theme="dark"] .pc-sidebar .pc-badge {
            color: var(--primary-color);
        }

        [data-pc-theme=dark] .card {
            --bs-white-rgb: var(--primary-color);
        }

        [data-pc-theme="dark"] .checkout-tabs .nav-item.show .nav-link .avtar,
        [data-pc-theme="dark"] .checkout-tabs .nav-link.active .avtar {
            background-color: var(--primary-color) !important;
        }

        [data-pc-preset=preset-7] .btn-primary {
            --bs-btn-color: var(--primary-color);
        }

        [data-pc-preset=preset-7][data-pc-theme="dark"] .link-primary {
            color:
                {{ $settings['sidebar_color'] }}
                !important;
        }

        [data-pc-preset=preset-7][data-pc-theme="dark"] div:where(.swal2-container) button:where(.swal2-styled) {
            color: black !important;
        }

        .platform-download {
            transition-duration: 400ms;
        }

        .platform-download:hover {
            box-shadow: 0px 6px 6px 2px #0000004d;
            transition-duration: 400ms;
        }

        .platform-download-modal {
            max-width: 650px;
            width: 90%;
        }

        .platform-download-btn {
            display: block;
            padding: 20px 15px;
            border: 1px solid rgba(220, 53, 69, 0.4);
            border-radius: 12px;
            text-decoration: none;
            transition: all 0.3s ease;
            background-color: #fff;
            color: #333;
        }

        .platform-download-btn:hover {
            border-color: rgba(220, 53, 69, 0.6);
            box-shadow: 0px 4px 8px rgba(220, 53, 69, 0.2);
            transform: translateY(-2px);
            text-decoration: none;
            color: #333;
        }

        .platform-icon-wrapper {
            display: flex;
            justify-content: center;
            align-items: center;
            margin-bottom: 10px;
            min-height: 60px;
        }

        .platform-icon {
            max-width: 60px;
            max-height: 60px;
            width: auto;
            height: auto;
            object-fit: contain;
        }

        .platform-label {
            display: block;
            font-weight: 500;
            font-size: 14px;
            color: #333;
            margin-top: 8px;
        }

        /* Style the main item */
        .pc-item {
            position: relative;
        }

        /* Hide the submenu by default */
        .submenu {
            display: none;
            background-color: #fff;
            padding: 10px;
            box-shadow: 0 0 5px rgba(0, 0, 0, 0.1);
            border-radius: 5px;
            margin-top: 5px;
            max-height: 0;
            overflow: hidden;
            background: none;
            box-shadow: none;
        }

        .submenu li {
            list-style: none;
        }

        .submenu li a {
            display: block;
            padding: 5px 10px;
            color: #333;
            text-decoration: none;
        }

        .submenu li a:hover {
            background-color: #f4f4f4;
        }

        .caret-icon-container {
            position: absolute;
            right: 28px;
            top: 35%;
            transform: translateY(-50%);
        }

        .caret-icon {
            transition: transform 0.3s ease, opacity 0.3s ease;
            position: absolute;
            right: 0;
        }

        .caret-up {
            opacity: 0;
        }

        .caret-down {
            opacity: 1;
        }

        .pc-item.active .caret-down {
            opacity: 0;
            transform: rotate(180deg);
        }

        .pc-item.active .caret-up {
            opacity: 1;
            transform: rotate(0deg);
        }

        .pc-item.active .submenu {
            display: block;
            max-height: 500px;
            padding: 10px;
        }

        .no-wrap {
            white-space: nowrap;
        }

        .w-xs-50 {
            width: 50%;
        }

        .w-xs-75 {
            width: 75%;
        }

        .w-xs-100 {
            width: 100%;
        }

        @media (min-width: 768px) {
            .w-md-25 {
                width: 25% !important;
            }


        }
    </style>
    <script type="text/javascript">
        window.omnisend = window.omnisend || [];
        omnisend.push(["brandID", "691e3cc91ce6ae348df16739"]);
        omnisend.push(["track", "$pageViewed"]);
        !function(){var e=document.createElement("script");
        e.type="text/javascript",e.async=!0,
        e.src="https://omnisnippet1.com/inshop/launcher-v2.js";
        var t=document.getElementsByTagName("script")[0];
        t.parentNode.insertBefore(e,t)}();
</script>
</head>

<body class="@if (!Auth::guest()) loggedin @endif" data-pc-preset="preset-7" data-pc-sidebar-caption="true"
    data-pc-direction="ltr" data-pc-theme_contrast="" <?php
if (!isset($_COOKIE["sitetheme"])) { ?>
    data-pc-theme="light" <?php } elseif ($_COOKIE["sitetheme"] == 'true') { ?> data-pc-theme="light" <?php } else { ?>
    data-pc-theme="dark" <?php } ?>>
    <div id="app" data-v-app="">
        <div>
            <h1></h1>
            <nav class="pc-sidebar" <?php echo $marginTopStyle; ?>>
                <div class="navbar-wrapper">
                    <div class="m-header">
                        <a href="/dashboard" class="b-brand text-primary">
                            @if (!isset($_COOKIE['sitetheme']))
                                <img src="/{{ $settings['admin_sidebar_logo'] }}" class="img-fluid logo-lg 1" alt="logo"
                                    style="width: 70%;">
                            @elseif ($_COOKIE['sitetheme'] == 'true')
                                <img src="/{{ $settings['admin_sidebar_logo'] }}" class="img-fluid logo-lg 2" alt="logo"
                                    style="width: 70%;">
                            @else
                                <img src="/{{ $settings['admin_sidebar_logo_dark'] }}" class="img-fluid logo-lg 3"
                                    alt="logo" style="width: 70%;">
                            @endif
                            {{-- <span class="badge bg-light-primary rounded-pill ms-2 theme-version">v1.0</span> --}}
                        </a>
                    </div>
                    <div class="navbar-content">
                        <div class="card pc-user-card">
                            <div class="card-body">
                                <div class="d-flex align-items-center w-75">
                                    <div class="flex-shrink-0">
                                        <img src="{{ $profile_image_url }}" alt="user-image"
                                            class="user-avtar wid-70 hei-70 rounded-circle" style="object-fit: cover">
                                    </div>
                                    <div class="flex-grow-1 ms-3 me-2 w-75">
                                        @auth
                                            <h6 class="mb-0 w-75">{{ ucfirst(auth()->user()->fullname) }}</h6>
                                            <small class="ellipsis w-75"
                                                tooltip="{{ auth()->user()->email }}">{{ auth()->user()->email }}</small>
                                        @endauth
                                    </div>
                                </div>
                                <div class="pc-user-links">
                                    <div class="pt-3 d-flex flex-column">
                                        <a href="/user-profile" class=""><i class="ti ti-user"></i><span>My
                                                Account</span></a>
                                        <a href="javascript:void(0);" class="" id="support-intercom-sidebar">
                                            <i class="ti ti-headset"></i>
                                            <span class="pc-mtext">Support</span>
                                        </a>
                                        <a href="/logout" id="logout-link"><i class="ti ti-power"></i><span>Logout</span></a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <ul class="pc-navbar">
                            <li class="pc-item pc-caption"><label>Navigation</label></li>
                            <li class="pc-item">
                                <a href="/dashboard" class="pc-link" aria-current="page">
                                    <span class="pc-micon">
                                        <svg class="pc-icon">
                                            <use xlink:href="#custom-status-up"></use>
                                        </svg>
                                    </span>
                                    <span class="pc-mtext">Dashboard</span>
                                </a>
                            </li>
                            @if (auth()->user()->wallet_enabled == 1)
                                {{-- <li class="pc-item">
                                    <a href="/wallet" class="pc-link">
                                        <span class="pc-micon">
                                            <svg class="pc-icon">
                                                <use xlink:href="#custom-security-safe"></use>
                                            </svg>
                                        </span>
                                        <span class="pc-mtext">My Wallet</span>
                                    </a>
                                </li> --}}
                                <li class="pc-item">
                                    <a href="/transactions" class="pc-link">
                                        <span class="pc-micon">
                                            <svg class="pc-icon">
                                                <use xlink:href="#custom-keyboard"></use>
                                            </svg>
                                        </span>
                                        <span class="pc-mtext">Transactions</span>
                                    </a>
                                </li>
                            @else
                                <li class="pc-item">
                                    <a href="/transactions" class="pc-link">
                                        <span class="pc-micon">
                                            <svg class="pc-icon">
                                                <use xlink:href="#custom-keyboard"></use>
                                            </svg>
                                        </span>
                                        <span class="pc-mtext">Transactions</span>
                                    </a>
                                </li>
                                {{-- <li class="pc-item">
                                    <a href="/wallet" class="pc-link">
                                        <span class="pc-micon">
                                            <svg class="pc-icon">
                                                <use xlink:href="#custom-security-safe"></use>
                                            </svg>
                                        </span>
                                        <span class="pc-mtext">My Wallet</span>
                                    </a>
                                </li> --}}
                            @endif
                            @if ((strpos(auth()->user()->email, 'lqhmarkets.com') !== false) || (strpos(auth()->user()->email, 'serverfront') !== false))
                                <li class="pc-item">
                                    <a href="/tasks" class="pc-link">
                                        <span class="pc-micon">
                                            <svg class="pc-icon">
                                                <use xlink:href="#custom-level"></use>
                                            </svg>
                                        </span>
                                        <span class="pc-mtext">Tasks</span>
                                    </a>
                                </li>
                            @endif
                            <li class="pc-item">
                                <a href="/liveAccounts" class="pc-link">
                                    <span class="pc-micon">
                                        <svg class="pc-icon">
                                            <use xlink:href="#custom-user-square"></use>
                                        </svg>
                                    </span>
                                    <span class="pc-mtext">Accounts</span>
                                </a>
                            </li>
                            {{-- @if ((strpos(auth()->user()->email, 'lqhmarkets.com') !== false) || (strpos(auth()->user()->email, 'serverfront') !== false)) --}}
                                <li class="pc-item">
                                    <a href="/competition" class="pc-link">
                                        <span class="pc-micon">
                                            <svg class="pc-icon">
                                                <use xlink:href="#custom-shield"></use>
                                            </svg>
                                        </span>
                                        <span class="pc-mtext">Competition</span>
                                    </a>
                                </li>
                            {{-- @endif --}}
                            <li class="pc-item">
                                <a href="javascript:void(0);" class="pc-link" id="pamm-menu">
                                    <span class="pc-micon">
                                        <svg class="pc-icon">
                                            <use xlink:href="#custom-user-square"></use>
                                        </svg>
                                    </span>
                                    <span class="pc-mtext">PAMM</span>
                                    <span class="caret-icon-container">
                                        <i class="caret-icon ti ti-chevron-down caret-down"></i>
                                        <i class="caret-icon ti ti-chevron-up caret-up"></i>
                                    </span>
                                </a>
                                <ul class="submenu" id="pamm-submenu">
                                    <li><a href="{{ route('pamm.manager') }}">Manager</a></li>
                                    <li><a href="{{ route('pamm.investor') }}">Investor</a></li>
                                </ul>
                            </li>
                            {{-- <li class="pc-item">
                                <a href="{{ route('forex-news.index') }}" class="pc-link">
                                    <span class="pc-micon">
                                        <svg class="pc-icon">
                                            <use xlink:href="#custom-status-up"></use>
                                        </svg>
                                    </span>
                                    <span class="pc-mtext">News</span>
                                </a>
                            </li> --}}

                            {{-- <li class="pc-item">
                                <a href="{{ route('learn') }}" class="pc-link">
                                    <span class="pc-micon">
                                        <svg class="pc-icon">
                                            <use xlink:href="#custom-level"></use>
                                        </svg>
                                    </span>
                                    <span class="pc-mtext">Learn</span>
                                </a>
                            </li> --}}

                            {{-- @if (!empty($ibResult)) --}}
                            <li class="pc-item">
                                <a href="/ib-profile" class="pc-link">
                                    <span class="pc-micon">
                                        <svg class="pc-icon">
                                            <use xlink:href="#custom-profile-2user-outline"></use>
                                        </svg>
                                    </span>
                                    <span class="pc-mtext">IB Profile</span>
                                </a>
                            </li>
                            {{-- @endif --}}
                        </ul>
                    </div>
                </div>
            </nav>
            <div class="modal fade" id="staticBackdrop" tabindex="-1" aria-labelledby="staticBackdropLabel"
                aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable platform-download-modal">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="staticBackdropLabel">Platform Downloads</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-12">
                                    <p class="mb-3 text-uppercase text-muted fw-semibold small">MT5 Platform</p>
                                </div>
                                <div class="mb-3 col-lg-4 col-md-4 col-sm-6">
                                    <a target="_blank" href="{{ $settings['mt5_android_platform'] }}"
                                        class="text-center d-block platform-download-btn">
                                        <div class="platform-icon-wrapper">
                                            <img class="platform-icon" src="/assets/platform/playstore.png"
                                                alt="Android">
                                        </div>
                                        <span class="platform-label">Android</span>
                                    </a>
                                </div>
                                <div class="mb-3 col-lg-4 col-md-4 col-sm-6">
                                    <a target="_blank" href="{{ $settings['mt5_ios_platform'] }}"
                                        class="text-center d-block platform-download-btn">
                                        <div class="platform-icon-wrapper">
                                            <img class="platform-icon" src="/assets/platform/appstore.png"
                                                alt="Apple iOS">
                                        </div>
                                        <span class="platform-label">Apple iOS</span>
                                    </a>
                                </div>
                                <div class="mb-3 col-lg-4 col-md-4 col-sm-6">
                                    <a target="_blank" href="{{ $settings['mt5_windows_platform'] }}"
                                        class="text-center d-block platform-download-btn">
                                        <div class="platform-icon-wrapper">
                                            <img class="platform-icon" src="/assets/platform/windowslogo.png"
                                                alt="Windows">
                                        </div>
                                        <span class="platform-label">Windows</span>
                                    </a>
                                </div>
                                <div class="col-12">
                                    <p class="mt-4 mb-3 text-uppercase text-muted fw-semibold small">X9 Platform</p>
                                </div>
                                <div class="mb-3 col-lg-4 col-md-4 col-sm-6">
                                    <a target="_blank" href="https://web.x9trader.com/login?returnUrl=%2Fterminal"
                                        class="text-center d-block platform-download-btn">
                                        <div class="platform-icon-wrapper">
                                            <img class="platform-icon" src="/assets/images/x9.png"
                                                alt="X9 Web Terminal">
                                        </div>
                                        <span class="platform-label">Web Terminal</span>
                                    </a>
                                </div>
                                <div class="mb-3 col-lg-4 col-md-4 col-sm-6">
                                    <a target="_blank" href="https://app.x9trader.com/login"
                                        class="text-center d-block platform-download-btn">
                                        <div class="platform-icon-wrapper">
                                            <img class="platform-icon" src="/assets/images/x9.png"
                                                alt="X9 Mobile Trader">
                                        </div>
                                        <span class="platform-label">Mobile Trader</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @if (app()->environment('local'))
                <div
                    style="position: fixed; top: 0; width: 100%; background-color: #ff1f32; color: #ffffff; text-align: center; padding: 10px; z-index: 1030;">
                    <b>Local ENVIRONMENT</b>
                </div>
                @elseif (app()->environment('development'))
                <div
                    style="position: fixed; top: 0; width: 100%; background-color: #ff1f32; color: #ffffff; text-align: center; padding: 10px; z-index: 1030;">
                    <b>DEV ENVIRONMENT</b>
                </div>
            @endif
            @if (config('services.sales.promotion'))
                {{-- <div class=" w-100 sales-banner-container">
                    <div class="banner-link">
                        <div class="lqh-sale-banner">
                            <h1 class="animated pulse">{!!config("services.sales.promotiontext")!!}</h1>
                        </div>
                    </div>
                </div> --}}
            @endif

            <header class="pc-header" <?php echo $marginTopStyle; ?>>
                <div class="header-wrapper">
                    <div class="me-auto pc-mob-drp">
                        <ul class="list-unstyled">
                            {{-- <li class="pc-h-item pc-sidebar-collapse">
                                <a href="/dashboard" class="pc-head-link ms-0" id="sidebar-hide">
                                    <i class="ti ti-menu-2"></i>
                                </a>
                            </li> --}}
                            <li class="pc-h-item pc-sidebar-popup">
                                <a href="#" class="pc-head-link ms-0" id="mobile-collapse">
                                    <i class="ti ti-menu-2"></i>
                                </a>
                            </li>
                            {{-- <li class="dropdown pc-h-item">
                                <a class="m-0 pc-head-link dropdown-toggle arrow-none trig-drp-search"
                                    data-bs-toggle="dropdown" href="/dashboard" role="button" aria-haspopup="false"
                                    aria-expanded="false">
                                    <svg class="pc-icon">
                                        <use xlink:href="#custom-search-normal-1"></use>
                                    </svg>
                                </a>
                                <div class="dropdown-menu pc-h-dropdown drp-search">
                                    <form class="px-3 py-2">
                                        <input type="search" class="border-0 shadow-none form-control"
                                            placeholder="Search here. . .">
                                    </form>
                                </div>
                            </li> --}}
                        </ul>
                    </div>
                    <div class="ms-auto">
                        @if (session('admin'))
                            <ul>
                                <a href="{{ route('switchToAdmin') }}" class="">
                                    <span>
                                        Switch Back To {{ session('admin')->username }}
                                    </span>
                                </a>
                            </ul>
                        @endif
                        <ul class="list-unstyled">
                            <li class="pc-h-item">
                                @include('components.language-dropdown', [
                                    'selectId' => 'custom_translate_select_header_crm',
                                    'flagPreviewId' => 'flag-preview-crm-header'
                                ])
                            </li>
                            <li class="dropdown pc-h-item">
                                <a class="pc-head-link dropdown-toggle arrow-none me-0" data-bs-toggle="dropdown"
                                    href="/dashboard" role="button" aria-haspopup="false" aria-expanded="false">
                                    <svg class="pc-icon">
                                        <use xlink:href="#custom-setting-2"></use>
                                    </svg>
                                </a>
                                <div class="dropdown-menu dropdown-menu-end pc-h-dropdown">
                                    <a href="/user-profile" class="dropdown-item"><i class="ti ti-user"></i><span>My
                                            Account</span></a>
                                    <a href="javascript:void(0);" class="dropdown-item" id="support-intercom-btn"><i
                                            class="ti ti-headset"></i><span>Support</span></a>
                                    <a href="/logout" class="dropdown-item" id="logout-link-2"><i
                                            class="ti ti-power"></i><span>Logout</span></a>
                                </div>
                            </li>
                            {{-- <li class="pc-h-item">
                                <a href="/dashboard" class="pc-head-link me-0" data-bs-toggle="offcanvas"
                                    data-bs-target="#announcement" aria-controls="announcement">
                                    <svg class="pc-icon">
                                        <use xlink:href="#custom-flash"></use>
                                    </svg>
                                </a>
                            </li> --}}
                            <li class="dropdown pc-h-item d-none">
                                <a class="pc-head-link dropdown-toggle arrow-none me-0" data-bs-toggle="dropdown"
                                    href="/dashboard" role="button" aria-haspopup="false" aria-expanded="false">
                                    <svg class="pc-icon">
                                        <use xlink:href="#custom-notification"></use>
                                    </svg>
                                    <span class="badge bg-success pc-h-badge">3</span>
                                </a>
                                <div class="dropdown-menu dropdown-notification dropdown-menu-end pc-h-dropdown">
                                    <div class="dropdown-body text-wrap header-notification-scroll position-relative"
                                        style="max-height: calc(-215px + 100vh);">
                                        <p class="text-span">Today</p>
                                        <div class="mb-2 card">
                                            <div class="card-body">
                                                <div class="d-flex">
                                                    <div class="flex-shrink-0">
                                                        <svg class="pc-icon text-primary">
                                                            <use xlink:href="#custom-layer"></use>
                                                        </svg>
                                                    </div>
                                                    <div class="flex-grow-1 ms-3">
                                                        <span class="text-sm float-end text-muted">19 April.
                                                            Friday</span>
                                                        <h5 class="mb-2 text-body">We've Upgraded Our Client Portal!
                                                        </h5>
                                                        <p class="mb-0">We're excited to announce that our client
                                                            portal
                                                            has
                                                            been upgraded! We've introduced several enhancements to
                                                            improve
                                                            functionality and provide you with a more intuitive and
                                                            streamlined
                                                            experience.</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="py-2 text-center"><a href="/dashboard" class="link-danger">Clear
                                            all
                                            Notifications</a></div>
                                </div>
                            </li>
                            <li class="dropdown pc-h-item header-user/profile">
                                <a class=" dropdown-toggle arrow-none me-0" data-bs-toggle="dropdown" href="/dashboard"
                                    role="button" aria-haspopup="false" data-bs-auto-close="outside"
                                    aria-expanded="false">
                                    {{-- <img
                                        src="{{ Storage::url('profile_images/' . (isset($user) ? $user->profile_image_url : auth()->user()->profile_image_url)) }}"
                                        alt="user-image" class="user-avtar"> --}}

                                    <img src="{{ $profile_image_url }}" alt="user-image" class="user-avtar"
                                        style="object-fit: cover">
                                </a>
                                <div class="dropdown-menu dropdown-user/profile dropdown-menu-end pc-h-dropdown">
                                    <div class="dropdown-header d-flex align-items-center justify-content-between">
                                        <h5 class="m-0">Profile</h5>
                                    </div>
                                    <div class="dropdown-body">
                                        <div class="profile-notification-scroll position-relative"
                                            style="max-height: calc(-225px + 100vh);">
                                            <div class="mb-1 d-flex">
                                                <div class="flex-shrink-0">
                                                    {{-- <img
                                                        src="{{ Storage::url('profile_images/' .(isset($user) ? $user->profile_image_url : auth()->user()->profile_image_url)) }}"
                                                        alt="user-image" class="user-avtar wid-35"> --}}
                                                    <img src="{{ $profile_image_url }}" alt="user-image"
                                                        class="user-avtar wid-35" style="object-fit: cover">
                                                </div>
                                                <div class="flex-grow-1 ms-3">
                                                    <h6 class="mb-1">{{ ucfirst(auth()->user()->fullname) }} 🖖
                                                    </h6>
                                                    <span>{{ auth()->user()->email }}</span>
                                                </div>
                                            </div>
                                            <hr class="border-opacity-50 border-secondary">
                                            <div class="card">
                                                <div class="py-3 card-body">
                                                    <a href="/user-profile" class="">
                                                        <div class="d-flex align-items-center justify-content-between">
                                                            <h6 class="mb-0 d-inline-flex align-items-center">
                                                                <svg class="pc-icon text-muted me-2">
                                                                    <use xlink:href="#custom-user"></use>
                                                                </svg>My Profile
                                                            </h6>
                                                        </div>
                                                    </a>
                                                </div>
                                            </div>
                                            <hr class="border-opacity-50 border-secondary">
                                            <div class="mb-3 d-grid">
                                                <a href="/logout" class="btn btn-primary">
                                                    <svg class="pc-icon me-2">
                                                        <use xlink:href="#custom-logout-1-outline"></use>
                                                    </svg>Logout
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
            </header>
            <div class="offcanvas pc-announcement-offcanvas offcanvas-end" tabindex="-1" id="announcement"
                aria-labelledby="announcementLabel">
                <div class="offcanvas-header">
                    <h5 class="offcanvas-title" id="announcementLabel">What's new announcement?</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                </div>
                {{-- <div class="offcanvas-body">
                    <p class="text-span">Today</p>
                    <div class="mb-3 card">
                        <div class="card-body">
                            <div class="flex-wrap gap-2 mb-3 align-items-center d-flex">
                                <div class="badge bg-light-success f-12">Big News</div>
                                <p class="mb-0 text-muted">2 min ago</p>
                                <span class="badge dot bg-warning"></span>
                            </div>
                            <!-- Blade syntax to output the dynamic $title -->
                            <h5 class="mb-3">{{ $settings['admin_title'] }} is Redesigned</h5>
                            <p class="text-muted">
                                Please note that we are still in the process of renewing aspects of the user
                                experience. You might encounter some areas under development, but rest assured,
                                these improvements are being made to better serve your needs.
                            </p>
                            <div class="row">
                                <div class="col-12">
                                    <div class="d-grid">
                                        <!-- Use Laravel's route helper for better maintainability -->
                                        <a href="{{ url('/dashboard') }}"
                                            class="router-link-active router-link-exact-active btn btn-outline-secondary"
                                            aria-current="page">
                                            Explore More
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div> --}}
                <div class="offcanvas pc-announcement-offcanvas offcanvas-end" tabindex="-1" id="announcement"
                    aria-labelledby="announcementLabel">
                    <div class="offcanvas-header">
                        <h5 class="offcanvas-title" id="announcementLabel">What's new announcement?</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                    </div>
                    {{-- <div class="offcanvas-body">
                        <p class="text-span">Today</p>
                        <div class="mb-3 card">
                            <div class="card-body">
                                <div class="flex-wrap gap-2 mb-3 align-items-center d-flex">
                                    <div class="badge bg-light-success f-12">Big News</div>
                                    <p class="mb-0 text-muted">2 min ago</p>
                                    <span class="badge dot bg-warning"></span>
                                </div>
                                <!-- Laravel Blade Syntax to dynamically insert the title -->
                                <h5 class="mb-3">{{ $settings['admin_title'] }} is Redesigned</h5>
                                <p class="text-muted">
                                    Please note that we are still in the process of renewing aspects of the user
                                    experience.
                                    You might encounter some areas under development, but rest assured, these
                                    improvements
                                    are being made to better serve your needs.
                                </p>
                                <div class="row">
                                    <div class="col-12">
                                        <div class="d-grid">
                                            <!-- Use the route() helper function if you are generating URLs from named routes -->
                                            <a href="{{ url('/dashboard') }}"
                                                class="router-link-active router-link-exact-active btn btn-outline-secondary"
                                                aria-current="page">Explore More</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div> --}}
                </div>
            </div>
        </div>
    </div>
    <script>
        document.getElementById('pamm-menu').addEventListener('click', function (event) {
            event.preventDefault();
            var parentMenuItem = this.parentElement;
            parentMenuItem.classList.toggle('active');
        });
    </script>
