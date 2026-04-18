<!DOCTYPE html>
<html lang="en" dir="ltr" data-nav-layout="vertical" data-vertical-style="light" data-theme-mode="light"
    data-header-styles="light" data-menu-styles="light">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <link rel="icon" href="/{{ $settings['favicon'] }}">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description"
        content="Your partner in profitable trading. Trade forex, commodities, indices, and cryptocurrencies with low spreads and fast execution">
    <meta name="keywords"
        content="forex broker, forex trading, commodities trading, indices trading, cryptocurrencies trading, low spreads, fast execution">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>
        {{ $settings['admin_title'] . ' - Admin Dashboard' }}
    </title>
    <link id="style" href="/admin_assets/assets/libs/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="/admin_assets/assets/css/icons.css" rel="stylesheet">
    <script src="/admin_assets/assets/libs/choices.js/public/assets/scripts/choices.min.js"></script>
    <script src="/admin_assets/assets/js/main.js"></script>
    <script src="/admin_assets/assets/js/jquery-3.5.1.min.js"></script>
    <link id="style" href="/admin_assets/assets/libs/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="/admin_assets/assets/css/styles.css?v={{ time() }}" rel="stylesheet">
    <link href="/admin_assets/assets/css/icons.css" rel="stylesheet">
    <link href="/admin_assets/assets/libs/node-waves/waves.min.css" rel="stylesheet">
    <link href="/admin_assets/assets/libs/simplebar/simplebar.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/admin_assets/assets/libs/flatpickr/flatpickr.min.css">
    <link rel="stylesheet" href="/admin_assets/assets/libs/@simonwep/pickr/themes/nano.min.css">
    <link rel="stylesheet" href="/admin_assets/assets/libs/choices.js/public/assets/styles/choices.min.css">
    <link rel="stylesheet" href="/admin_assets/assets/libs/flatpickr/flatpickr.min.css">
    <link rel="stylesheet" href="/admin_assets/assets/libs/@tarekraafat/autocomplete.js/css/autoComplete.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/lipis/flag-icons@7.2.3/css/flag-icons.min.css">
    @if (!View::hasSection('noDatatable'))
        <link rel="stylesheet" href="https://cdn.datatables.net/1.12.1/css/dataTables.bootstrap5.min.css">
        <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.3.0/css/responsive.bootstrap.min.css">
        <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.2.3/css/buttons.bootstrap5.min.css">
        <script src="https://cdn.datatables.net/1.12.1/js/jquery.dataTables.min.js"></script>
        <script src="https://cdn.datatables.net/1.12.1/js/dataTables.bootstrap5.min.js"></script>
        <script src="https://cdn.datatables.net/responsive/2.3.0/js/dataTables.responsive.min.js"></script>
        <script src="https://cdn.datatables.net/buttons/2.2.3/js/dataTables.buttons.min.js"></script>
        <script src="https://cdn.datatables.net/buttons/2.2.3/js/buttons.print.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.6/pdfmake.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
        <script src="https://cdn.datatables.net/buttons/2.2.3/js/buttons.html5.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
        <script src="/admin_assets/assets/js/datatables.js"></script>
    @endif
    <script src="/admin_assets/assets/js/sweetalert2.all.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>



    <?php
$marginTopStyle = ''; // Default value
if (app()->environment('local') || app()->environment('development')) {
    $marginTopStyle = 'style="margin-top: 40px;"';
}
    ?>

    <style>
        input[readonly] {
            background: var(--input-border);
        }

        .hei-50 {
            height: 30px;
        }

        :root {
            --primary-color:
                {{ $settings['sidebar_color'] }}
            ;
            --primary-rgb:
                {{ hexToRGB($settings['sidebar_color']) }}
        }

        .auth-bg-cover {
            background: linear-gradient(-45deg, #01112d 30%, var(--primary-color));
        }

        .app-sidebar .side-menu__label,
        .app-sidebar .side-menu__item {
            color: var(--custom-black);
            font-weight: 500;
        }

        .app-sidebar .slide__category {
            opacity: 0.8 !important;
            color: var(--custom-black);
        }

        .cursor-pointer {
            cursor: pointer !important;
        }

        /* Header Content Middle Styles */
        .header-content-middle {
            display: flex;
            align-items: stretch;
            justify-content: center;
            flex: 1;
            margin: 0 auto;
        }

        .header-server-time {
            display: flex;
            align-items: center;
        }

        /* Server Time Display Styles */
        .server-time-container {
            padding: 0 0.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .server-time-icon {
            font-size: 1rem;
            margin-right: 0.5rem;
            color: var(--header-prime-color);
        }

        .server-time-content {
            line-height: 1.2;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
        }

        .server-time-label {
            font-size: 0.7rem;
            color: #7b8191;
            line-height: 1;
            margin-bottom: 0.1rem;
            text-align: center;
        }

        .server-time-display {
            font-size: 0.875rem;
            line-height: 1.2;
            color: var(--header-prime-color);
            white-space: nowrap;
            text-align: center;
        }

        /* Mobile Responsive Styles */
        @media (max-width: 991.98px) {
            .header-content-middle {
                flex: 0 0 auto;
            }

            .server-time-container {
                padding: 0 0.4rem;
            }

            .server-time-icon {
                font-size: 0.9rem;
                margin-right: 0.4rem;
            }

            .server-time-display {
                font-size: 0.8rem;
            }
        }

        @media (max-width: 575.98px) {
            .header-content-middle {
                position: absolute;
                left: 50%;
                transform: translateX(-50%);
                flex: 0 0 auto;
            }

            .server-time-container {
                padding: 0 0.3rem;
            }

            .server-time-icon {
                font-size: 0.85rem;
                margin-right: 0.3rem;
            }

            .server-time-display {
                font-size: 0.75rem;
            }

            .server-time-label {
                display: none !important;
            }
        }

        /* @media (min-width: 768px) {
      .header-search {
        width: 450px;
      }
    } */
    </style>

    @yield('styles')
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

<body>
    @if(session('session_expired'))
        <script>
            alert('Session expired. Please log in again.');
        </script>
    @endif
    @if (app()->environment('local'))
        <div
            style="position: fixed; top: 0; width: 100%; background-color: #ff1f32; color: #ffffff; text-align: center; padding: 10px; z-index: 1000;">
            <b>LOCAL ENVIRONMENT</b>
        </div>
        @elseif (app()->environment('development'))
        <div
            style="position: fixed; top: 0; width: 100%; background-color: #ff1f32; color: #ffffff; text-align: center; padding: 10px; z-index: 1000;">
            <b>DEV ENVIRONMENT</b>
        </div>
    @endif


    <!-- Loader -->
    <div id="loader">
        <img src="/admin_assets/assets/images/media/loader.svg" alt="">
    </div>
    <!-- Loader -->
    <div class="page" <?php echo $marginTopStyle; ?>>

        <!-- app-header -->

        <header class="sticky app-header sticky-pin" id="header" <?php echo $marginTopStyle; ?>>

            <!-- Start::main-header-container -->
            <div class="main-header-container container-fluid">

                <!-- Start::header-content-left -->
                <div class="header-content-left">

                    <!-- Start::header-element -->
                    <div class="header-element">
                        <div class="horizontal-logo">
                            <a href="/admin/dashboard" class="header-logo">
                                <img src="/{{ $settings['logo_url'] }}" class="img-fluid logo-lg" alt="logo"
                                    style="width:50%">
                            </a>
                        </div>
                    </div>
                    <!-- End::header-element -->

                    <!-- Start::header-element -->
                    <div class="mx-2 header-element mx-lg-0">
                        <a aria-label="Hide Sidebar"
                            class="sidemenu-toggle header-link animated-arrow hor-toggle horizontal-navtoggle"
                            data-bs-toggle="sidebar" href="javascript:void(0);"><span></span></a>
                    </div>
                    <!-- End::header-element -->

                    <!-- Start::header-element -->
                    <div class="my-auto header-element header-search">
                        <form action="/admin/search" method="get" class="w-100">
                            <div class="input-group">
                                <input type="search" name="search"
                                    value="{{ isset($_GET['search']) ? $_GET['search'] : '' }}" class="form-control"
                                    required aria-label="Text input with segmented dropdown button">
                                <button class="btn btn-primary" type="submit">
                                    <i class="fa fa-search"></i>
                                </button>
                            </div>
                        </form>

                    </div>
                    <!-- End::header-element -->

                </div>
                <!-- End::header-content-left -->

                <!-- Start::header-content-middle -->
                <div class="header-content-middle">
                    <!-- Start::header-element: Server Time -->
                    <div class="my-auto header-element header-server-time">
                        <div class="d-flex align-items-center justify-content-center server-time-container">
                            <i class="fe fe-clock server-time-icon"></i>
                            <div class="d-flex flex-column align-items-center server-time-content">
                                <span class="server-time-label d-none d-md-block">Server Time</span>
                                <span id="server-time-display" class="fw-medium server-time-display">
                                    {{ now()->format('H:i:s') }}
                                </span>
                            </div>
                        </div>
                    </div>
                    <!-- End::header-element: Server Time -->
                </div>
                <!-- End::header-content-middle -->

                <!-- Start::header-content-right -->
                <ul class="header-content-right">

                    <!-- Start::header-element -->
                    <li class="header-element d-md-none d-block">
                        <a href="javascript:void(0);" class="header-link" data-bs-toggle="modal"
                            data-bs-target="#header-responsive-search">
                            <!-- Start::header-link-icon -->
                            <i class="bi bi-search header-link-icon"></i>
                            <!-- End::header-link-icon -->
                        </a>
                    </li>
                    <!-- End::header-element -->

                    <!-- Start::header-element: Language Dropdown -->
                    {{-- <li class="header-element">
                        @include('components.language-dropdown', [
                            'selectId' => 'custom_translate_select_header',
                            'flagPreviewId' => 'flag-preview-admin-header'
                        ])
                    </li> --}}
                    <!-- End::header-element: Language Dropdown -->

                    <!-- Start::header-element -->
                    <li class="header-element dropdown">
                        <!-- Start::header-link|dropdown-toggle -->
                        <a href="javascript:void(0);" class="header-link dropdown-toggle" id="mainHeaderProfile"
                            data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false">
                            <div class="d-flex align-items-center">
                                <div class="me-xl-2 me-0">
                                    <img src="/admin_assets/assets/images/users/user.png" alt="img"
                                        class="avatar avatar-sm avatar-rounded">
                                </div>
                                <div class="d-xl-block d-none lh-1">
                                    <span class="fw-medium lh-1">{{ session('userData')['username'] }}</span>
                                </div>
                            </div>
                        </a>
                        <!-- End::header-link|dropdown-toggle -->
                        <ul class="pt-0 overflow-hidden main-header-dropdown dropdown-menu header-profile-dropdown dropdown-menu-end"
                            aria-labelledby="mainHeaderProfile">
                            <li class="drop-heading border-bottom">
                                <p class="mb-0 text-center d-grid">Welcome
                                    <span
                                        class="mb-0 text-dark fs-14 fw-semibold">{{ ucfirst(session('userData')['username']) }}</span>
                                </p>
                            </li>

                            <li><a class="dropdown-item d-flex align-items-center" href="/admin/logout"><i
                                        class="fe fe-alert-circle me-2 fs-18 text-primary"></i>Logout</a></li>
                        </ul>
                    </li>
                    <!-- End::header-element -->
                </ul>
                <!-- End::header-content-right -->

            </div>
            <!-- End::main-header-container -->

        </header>
        <!-- /app-header -->
        <!-- Start::app-sidebar -->
        <aside class="sticky app-sidebar sticky-pin" id="sidebar" <?php echo $marginTopStyle; ?>>

            <!-- Start::main-sidebar-header -->
            <div class="main-sidebar-header">
                <a href="/admin/dashboard" class="header-logo">
                    <img src="/{{ $settings['favicon'] }}" alt="logo" class="toggle-logo"
                        style="max-width: 26px;height: 20px;">
                    <img src="/{{ $settings['admin_sidebar_logo'] }}" alt="logo" class="desktop-logo"
                        style="max-width:100px">
                </a>
            </div>
            <!-- End::main-sidebar-header -->

            <!-- Start::main-sidebar -->
            <div class="main-sidebar" id="sidebar-scroll" data-simplebar="init">

                <!-- Start::nav -->
                <nav class="main-menu-container nav nav-pills flex-column sub-open">
                    <div class="slide-left" id="slide-left">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="#7b8191" width="24" height="24"
                            viewBox="0 0 24 24">
                            <path d="M13.293 6.293 7.586 12l5.707 5.707 1.414-1.414L10.414 12l4.293-4.293z"></path>
                        </svg>
                    </div>
                    <ul class="main-menu" style="margin-left: 0px; margin-right: 0px;">

                        <li class="slide__category menu-item-category">
                            <span class="category-name">MAIN</span>
                        </li>

                        <li class="slide menu-item-main ">
                            <a href="{{ route('admin.dashboard') }}" class="side-menu__item">
                                <i class="side-menu__icon fe fe-airplay"></i>
                                <span class="side-menu__label">Dashboard</span>
                            </a>
                            <ul class="slide-menu child1">
                            </ul>
                        </li>
                        @if (auth('admin')->check() && (auth('admin')->user()->can('account:viewLiveAccounts') || auth('admin')->user()->can('account:viewDemoAccounts') || auth('admin')->user()->can('client:viewAny')))
                            <li class="slide__category menu-item-category">
                                <span class="category-name">CLIENT</span>
                            </li>
                            @if (auth('admin')->check() && auth('admin')->user() && auth('admin')->user()->can('client:viewAny'))
                                <li class="slide menu-item-main ">
                                    <a href="{{ route('admin.clients.index') }}" class="side-menu__item">
                                        <i class="side-menu__icon fe fe-users"></i>
                                        <span class="side-menu__label">Client List</span>
                                    </a>
                                    <ul class="slide-menu child1">
                                    </ul>
                                </li>
                            @endif

                            <li class="slide has-sub menu-item-main ">
                                <a href="#" class="side-menu__item">
                                    <i class="side-menu__icon fe fe-user-plus"></i>
                                    <span class="side-menu__label">Client Accounts</span>
                                    <i class="ri-arrow-down-s-line side-menu__angle"></i>
                                </a>
                                <ul class="slide-menu child1"
                                    style="position: relative; left: 0px; top: 0px; margin: 0px; transform: translate(128px, 288px);"
                                    data-popper-placement="bottom">
                                    @if (auth('admin')->check() && auth('admin')->user() && auth('admin')->user()->can('account:viewLiveAccounts'))
                                        <li class="slide menu-item-sub">
                                            <a href="{{ route('admin.liveAccounts') }}" class="side-menu__item ">Live
                                                Accounts</a>
                                        </li>
                                    @endif

                                    @if (auth('admin')->check() && auth('admin')->user() && auth('admin')->user()->can('account:viewDemoAccounts'))
                                        <li class="slide menu-item-sub">
                                            <a href="{{ route('admin.demoAccounts') }}" class="side-menu__item ">Demo
                                                Accounts</a>
                                        </li>
                                    @endif
                                    @if (auth('admin')->check() && auth('admin')->user() && auth('admin')->user()->can('account:viewRequestedAccounts'))
                                        <li class="slide menu-item-sub">
                                            <a href="{{ route('admin.requestedAccounts') }}" class="side-menu__item ">Requested
                                                Accounts</a>
                                        </li>
                                    @endif
                                      <li class="slide menu-item-sub ">
                                            <a href="{{ route('admin.zapier-accounts.index') }}" class="side-menu__item">
                                                <span class="side-menu__label">Zapier Accounts</span>
                                            </a>
                                            <ul class="slide-menu child1">
                                            </ul>
                                        </li>
                                    @if (auth('admin')->check() && auth('admin')->user() && auth('admin')->user()->can('accounts:view_not_found'))
                                        <li class="slide menu-item-sub">
                                            <a href="{{ route('admin.accounts.not_found_in_mt5.index') }}" class="side-menu__item">
                                                <i class="side-menu__icon fe fe-alert-circle"></i>
                                                <span class="side-menu__label">Not Found in MT5</span>
                                            </a>
                                        </li>
                                    @endif
                                </ul>
                            </li>
                        @endif
                        @if (auth('admin')->check() && (auth('admin')->user()->can('wallet_deposit:viewAny') || auth('admin')->user()->can('wallet_withdrawal:viewAny') || auth('admin')->user()->can('trade_deposit:viewAny') || auth('admin')->user()->can('trade_withdrawals:viewAny') || auth('admin')->user()->can('internal_transfer:viewAny') || auth('admin')->user()->can('trade:viewAny')))
                            <li class="slide__category menu-item-category">
                                <span class="category-name">FINANCE</span>
                            </li>

                            <li class="slide has-sub menu-item-main ">
                                <a href="#" class="side-menu__item">
                                    <i class="side-menu__icon fe fe-credit-card"></i>
                                    <span class="side-menu__label">Transactions</span>
                                    <i class="ri-arrow-down-s-line side-menu__angle"></i>
                                </a>
                                <ul class="slide-menu child1"
                                    style="position: relative; left: 0px; top: 0px; margin: 0px; transform: translate(128px, 417px);"
                                    data-popper-placement="bottom">
                                    @if (auth('admin')->check() && auth('admin')->user() && auth('admin')->user()->can('wallet_deposit:viewAny'))
                                        <li class="slide menu-item-sub">
                                            <a href="{{ route('admin.transactions.wallet-deposit') }}"
                                                class="side-menu__item ">Wallet Deposit</a>
                                        </li>
                                    @endif
                                    @if (auth('admin')->check() && auth('admin')->user() && auth('admin')->user()->can('wallet_withdraw:viewAny'))
                                        <li class="slide menu-item-sub">
                                            <a href="{{ route('admin.transactions.wallet-withdrawal') }}"
                                                class="side-menu__item ">Wallet Withdrawal</a>
                                        </li>
                                    @endif
                                    @if (auth('admin')->check() && auth('admin')->user() && auth('admin')->user()->can('trade_deposit:viewAny'))
                                        <li class="slide menu-item-sub">
                                            <a href="{{ route('admin.transactions.trading-deposit') }}"
                                                class="side-menu__item ">
                                                Trading Deposit
                                            </a>
                                        </li>
                                    @endif
                                    @if (auth('admin')->check() && auth('admin')->user() && auth('admin')->user()->can('trade_withdrawals:viewAny'))
                                        <li class="slide menu-item-sub">
                                            <a href="{{ route('admin.transactions.trading-withdrawal') }}"
                                                class="side-menu__item ">
                                                Trading Withdrawal
                                            </a>
                                        </li>
                                    @endif
                                    @if (auth('admin')->check() && auth('admin')->user() && auth('admin')->user()->can('internal_transfer:viewAny'))
                                        <li class="slide menu-item-sub">
                                            <a href="{{ route('admin.transactions.internal-transfer') }}"
                                                class="side-menu__item ">
                                                Internal Transfer
                                            </a>
                                        </li>
                                    @endif
                                </ul>
                            </li>

                            <li class="slide has-sub menu-item-main ">
                                <a href="#" class="side-menu__item">
                                    <i class="side-menu__icon fe fe-list"></i>
                                    <span class="side-menu__label">Pend.,Transactions</span>
                                    <i class="ri-arrow-down-s-line side-menu__angle"></i>
                                </a>
                                <ul class="slide-menu child1"
                                    style="position: relative; left: 0px; top: 0px; margin: 0px; transform: translate(128px, 461px);"
                                    data-popper-placement="bottom">
                                    @if (auth('admin')->check() && auth('admin')->user() && auth('admin')->user()->can('wallet_deposit:viewAny'))
                                        <li class="slide menu-item-sub">
                                            <a href="{{ route('admin.transactions.pending.wallet-deposit') }}"
                                                class="side-menu__item ">Wallet Deposit</a>
                                        </li>
                                    @endif
                                    @if (auth('admin')->check() && auth('admin')->user() && auth('admin')->user()->can('wallet_withdraw:viewAny'))
                                        <li class="slide menu-item-sub">
                                            <a href="{{ route('admin.transactions.pending.wallet-withdrawal') }}"
                                                class="side-menu__item ">Wallet Withdrawal</a>
                                        </li>
                                    @endif
                                    @if (auth('admin')->check() && auth('admin')->user() && auth('admin')->user()->can('trade_deposit:viewAny'))
                                        <li class="slide menu-item-sub">
                                            <a href="{{ route('admin.transactions.pending.trading-deposit') }}"
                                                class="side-menu__item ">Trading Deposit</a>
                                        </li>
                                    @endif
                                    @if (auth('admin')->check() && auth('admin')->user() && auth('admin')->user()->can('trade_withdrawals:viewAny'))
                                        <li class="slide menu-item-sub">
                                            <a href="{{ route('admin.transactions.pending.trading-withdrawal') }}"
                                                class="side-menu__item ">Trading Withdrawal</a>
                                        </li>
                                    @endif
                                </ul>
                            </li>
                        @endif

                        @if (auth('admin')->check() && auth('admin')->user() && auth('admin')->user()->can('trades:view'))
                            <li class="slide__category menu-item-category">
                                <span class="category-name">TRADES</span>
                            </li>
                            @if (auth('admin')->check() && auth('admin')->user() && auth('admin')->user()->can('trades:viewAny'))
                                <li class="slide menu-item-main ">
                                    <a href="{{ route('admin.trades.index') }}" class="side-menu__item">
                                        <i class="side-menu__icon fe fe-trending-up"></i>
                                        <span class="side-menu__label">Trades</span>
                                    </a>
                                    <ul class="slide-menu child1"></ul>
                                </li>
                            @endif
                        @endif

                        @if (auth('admin')->check() && auth('admin')->user() && (auth('admin')->user()->can('ib:viewAny') || auth('admin')->user()->can('ib:manageSettings')))
                            <li class="slide__category menu-item-category">
                                <span class="category-name">INTRODUCING BROKER</span>
                            </li>

                            <li class="slide has-sub menu-item-main ">
                                <a href="#" class="side-menu__item">
                                    <i class="side-menu__icon fe fe-user"></i>
                                    <span class="side-menu__label">IB</span>
                                    <i class="ri-arrow-down-s-line side-menu__angle"></i>
                                </a>
                                <ul class="slide-menu child1"
                                    style="position: relative; left: 0px; top: 0px; margin: 0px; transform: translate(128px, 501px);"
                                    data-popper-placement="top">
                                    @if (auth('admin')->check() && auth('admin')->user() && auth('admin')->user()->can('ib:viewAny'))
                                        <li class="slide menu-item-sub">
                                            <a href="{{ route('admin.ib.dashboard') }}" class="side-menu__item ">IB
                                                Dashboard</a>
                                        </li>
                                    @endif
                                    @if (auth('admin')->check() && auth('admin')->user() && auth('admin')->user()->can('ib:manageRequests'))
                                        <li class="slide menu-item-sub">
                                            <a href="{{ route('admin.ib.list') }}" class="side-menu__item ">IB
                                                Requests</a>
                                        </li>
                                    @endif
                                    @if (auth('admin')->check() && auth('admin')->user() && auth('admin')->user()->can('ib:viewAny'))
                                        <li class="slide menu-item-sub">
                                            <a href="{{ route('admin.ib.active.list') }}" class="side-menu__item ">IB
                                                Users</a>
                                        </li>
                                    @endif
                                    @if (auth('admin')->check() && auth('admin')->user() && auth('admin')->user()->can('ib:manageSettings'))
                                        <li class="slide menu-item-sub">
                                            <a href="/admin/ib_settings" class="side-menu__item ">IB Com. Settings</a>
                                        </li>
                                    @endif
                                    @if (auth('admin')->check() && auth('admin')->user() && auth('admin')->user()->can('ib:viewAny'))
                                        <li class="slide menu-item-sub">
                                            <a href="{{ route('admin.ib.commission-analysis') }}" class="side-menu__item ">Commission Analysis</a>
                                        </li>
                                    @endif
                                    @if (auth('admin')->check() && auth('admin')->user() && auth('admin')->user()->can('ib:viewAny'))
                                        <li class="slide menu-item-sub">
                                            <a href="{{ route('admin.ib.withdrawals.index') }}" class="side-menu__item ">IB Withdrawals</a>
                                        </li>
                                    @endif

                                </ul>
                            </li>
                        @endif
                        @if (auth('admin')->check() && auth('admin')->user() && auth('admin')->user()->can('m_t5_group:viewAny'))
                            <li class="slide__category menu-item-category">
                                <span class="category-name">MT5 CONFIGURATION</span>
                            </li>

                            <li class="slide has-sub menu-item-main ">
                                <a href="#" class="side-menu__item">
                                    <i class="side-menu__icon fe fe-help-circle"></i>
                                    <span class="side-menu__label">META Config</span>
                                    <i class="ri-arrow-down-s-line side-menu__angle"></i>
                                </a>
                                <ul class="slide-menu child1"
                                    style="position: relative; left: 0px; top: 0px; margin: 0px; transform: translate(128px, 585px);"
                                    data-popper-reference-hidden="" data-popper-escaped="" data-popper-placement="top">

                                    <li class="slide menu-item-sub">
                                        <a href="/admin/mt5_groups" class="side-menu__item ">
                                            MT5 Groups
                                        </a>
                                    </li>

                                    <li class="slide menu-item-sub">
                                        <a href="/admin/promocode" class="side-menu__item ">
                                            Promo Code
                                        </a>
                                    </li>

                                    <li class="slide menu-item-sub">
                                        <a href="{{ route('admin.learn-content.index') }}" class="side-menu__item ">
                                            Learn Content
                                        </a>
                                    </li>

                                </ul>
                            </li>
                        @endif

                        @if (auth('admin')->check() && auth('admin')->user() && auth('admin')->user()->can('menu:tasks'))
                        <li class="slide__category menu-item-category">
                            <span class="category-name">TASKS</span>
                        </li>
                            @if (auth('admin')->check() && auth('admin')->user() && auth('admin')->user()->can('task:viewAny'))
                            <li class="slide menu-item-main">
                                <a href="{{ route('admin.tasks.index') }}" class="side-menu__item">
                                    <i class="side-menu__icon fe fe-list"></i>
                                    <span class="side-menu__label">Tasks</span>
                                </a>
                            </li>
                            @endif
                            @if (auth('admin')->check() && auth('admin')->user() && auth('admin')->user()->can('clientTask:viewAny'))
                            <li class="slide menu-item-main">
                                <a href="{{ route('admin.tasks.client_tasks') }}" class="side-menu__item">
                                    <i class="side-menu__icon fe fe-list"></i>
                                    <span class="side-menu__label">Client Tasks</span>
                                </a>
                            </li>
                            @endif
                        @endif


                        @if (auth('admin')->check() && auth('admin')->user() && auth('admin')->user()->can('m_t5_group:viewAny'))
                            {{-- @if (strpos(auth('admin')->user()->email, 'lqhmarkets.com') !== false) --}}
                                <li class="slide__category menu-item-category">
                                    <span class="category-name">MT5 COMPETITION</span>
                                </li>
                                <li class="slide has-sub menu-item-main ">
                                    <a href="#" class="side-menu__item">
                                        <i class="side-menu__icon fe fe-user-plus"></i>
                                        <span class="side-menu__label">Competition</span>
                                        <i class="ri-arrow-down-s-line side-menu__angle"></i>
                                    </a>
                                    <ul class="slide-menu child1"
                                        style="position: relative; left: 0px; top: 0px; margin: 0px; transform: translate(128px, 288px);"
                                        data-popper-placement="bottom">
                                        <li class="slide menu-item-sub ">
                                            <a href="{{ route('admin.competitions.index') }}" class="side-menu__item">
                                                <span class="side-menu__label">Competition List</span>
                                            </a>
                                            <ul class="slide-menu child1">
                                            </ul>
                                        </li>
                                        <li class="slide menu-item-sub ">
                                            <a href="{{ route('admin.competition.dashboard') }}" class="side-menu__item">
                                                <span class="side-menu__label">Accounts list</span>
                                            </a>
                                            <ul class="slide-menu child1">
                                            </ul>
                                        </li>
                                        <li class="slide menu-item-sub ">
                                            <a href="{{ route('admin.competition.requested') }}" class="side-menu__item">
                                                <span class="side-menu__label">Requested Accounts</span>
                                            </a>
                                            <ul class="slide-menu child1">
                                            </ul>
                                        </li>
                                    </ul>
                                </li>
                            {{-- @endif --}}
                        @endif

                        @if (auth('admin')->check() && auth('admin')->user() && auth('admin')->user()->can('setting:viewAny'))
                            <li class="slide__category menu-item-category">
                                <span class="category-name">AFFILIATE MANAGEMENT</span>
                            </li>

                            <li class="slide menu-item-main ">
                                <a href="{{ route('admin.affiliates.index') }}" class="side-menu__item">
                                    <i class="side-menu__icon fe fe-users"></i>
                                    <span class="side-menu__label">Affiliates</span>
                                </a>
                                <ul class="slide-menu child1">
                                </ul>
                            </li>

                            <li class="slide menu-item-main ">
                                <a href="{{ route('admin.login-history.index') }}" class="side-menu__item">
                                    <i class="side-menu__icon fe fe-clock"></i>
                                    <span class="side-menu__label">Login History</span>
                                </a>
                                <ul class="slide-menu child1">
                                </ul>
                            </li>

                            <li class="slide menu-item-main ">
                                <a href="{{ route('admin.inactive-users.index') }}" class="side-menu__item">
                                    <i class="side-menu__icon fe fe-user-x"></i>
                                    <span class="side-menu__label">Inactive Users</span>
                                </a>
                                <ul class="slide-menu child1">
                                </ul>
                            </li>
                        @endif

                        @if (auth('admin')->check() && auth('admin')->user() && auth('admin')->user()->can('setting:viewAny'))
                            <li class="slide__category menu-item-category">
                                <span class="category-name">2FA SETTINGS</span>
                            </li>
                            <li class="slide menu-item-main ">
                                <a href="{{ route('admin.2fa.index') }}" class="side-menu__item">
                                    <i class="side-menu__icon fe fe-settings"></i>
                                    <span class="side-menu__label">2FA Authentication</span>
                                </a>
                                <ul class="slide-menu child1">
                                </ul>
                            </li>
                        @endif

                        @if (auth('admin')->check() && auth('admin')->user() && auth('admin')->user()->can('employee:viewAny'))
                            <li class="slide__category menu-item-category">
                                <span class="category-name">ADMIN USERS</span>
                            </li>

                            <li class="slide has-sub menu-item-main ">
                                <a href="#" class="side-menu__item">
                                    <i class="side-menu__icon fe fe-user"></i>
                                    <span class="side-menu__label">Staff Management</span>
                                    <i class="ri-arrow-down-s-line side-menu__angle"></i>
                                </a>
                                <ul class="slide-menu child1"
                                    style="position: relative; left: 0px; top: 0px; margin: 0px; transform: translate(128px, 669px);"
                                    data-popper-reference-hidden="" data-popper-escaped="" data-popper-placement="top">
                                    @if (auth('admin')->check() && auth('admin')->user() && auth('admin')->user()->can('role:viewAny'))
                                        <li class="slide menu-item-sub">
                                            <a href="{{ route('admin.roles') }}" class="side-menu__item ">
                                                Roles
                                            </a>
                                        </li>
                                    @endif
                                    @if (auth('admin')->check() && auth('admin')->user() && auth('admin')->user()->can('permission:update'))
                                        <li class="slide menu-item-sub">
                                            <a href="{{ route('admin.role_permissions') }}" class="side-menu__item "> Role
                                                Permissions</a>
                                        </li>
                                    @endif

                                    @if (auth('admin')->check() && auth('admin')->user() && auth('admin')->user()->can('employee:viewAny'))
                                        <li class="slide menu-item-sub">
                                            <a href="{{ route('admin.admin_users') }}" class="side-menu__item ">Staffs
                                                Management</a>
                                        </li>
                                    @endif
                                </ul>
                            </li>
                        @endif
                        {{-- <li class="slide has-sub menu-item-main ">
                            <a href="#" class="side-menu__item">
                                <i class="side-menu__icon fe fe-help-circle"></i>
                                <span class="side-menu__label">Help Desk</span>
                                <i class="ri-arrow-down-s-line side-menu__angle"></i>
                            </a>
                            <ul class="slide-menu child1"
                                style="position: relative; left: 0px; top: 0px; margin: 0px; transform: translate(128px, 713px);"
                                data-popper-reference-hidden="" data-popper-escaped="" data-popper-placement="top">

                                <li class="slide menu-item-sub">
                                    <a href="/admin/all_tickets" class="side-menu__item ">
                                        All Tickets
                                    </a>
                                </li>


                                <li class="slide menu-item-sub">
                                    <a href="/admin/open_tickets" class="side-menu__item ">
                                        Open Tickets
                                    </a>
                                </li>


                                <li class="slide menu-item-sub">
                                    <a href="/admin/closed_tickets" class="side-menu__item ">
                                        Closed Tickets
                                    </a>
                                </li>

                            </ul>
                        </li> --}}
                        @if (auth('admin')->check() && auth('admin')->user() && auth('admin')->user()->can('blog:view'))
                            <li class="slide__category menu-item-category">
                                <span class="category-name">BLOG</span>
                            </li>
                            @if (auth('admin')->check() && auth('admin')->user() && auth('admin')->user()->can('blog:viewAny'))
                                <li class="slide menu-item-main">
                                    <a href="{{ route('admin.blog.index') }}" class="side-menu__item">
                                        <i class="side-menu__icon fe fe-file-text"></i>
                                        <span class="side-menu__label">Blog Posts</span>
                                    </a>
                                    <ul class="slide-menu child1">
                                    </ul>
                                </li>
                            @endif
                        @endif


                        @if (auth('admin')->check() && auth('admin')->user() && auth('admin')->user()->can('menu:settings'))
                        <li class="slide has-sub menu-item-main ">
                            <a href="#" class="side-menu__item">
                                <i class="side-menu__icon fe fe-settings"></i>
                                <span class="side-menu__label">Settings</span>
                                <i class="ri-arrow-down-s-line side-menu__angle"></i>
                            </a>
                            <ul class="slide-menu child1"
                                style="position: relative; left: 0px; top: 0px; margin: 0px; transform: translate(128px, 758px);"
                                data-popper-reference-hidden="" data-popper-escaped="" data-popper-placement="top">
                                @if (auth('admin')->check() && auth('admin')->user() && auth('admin')->user()->can('settings:sumsub'))
                                    <li class="slide menu-item-sub">
                                        <a href="{{ route('admin.kyc.sync.page') }}" class="side-menu__item ">
                                            Sumsub KYC Sync
                                        </a>
                                    </li>
                                @endif
                                @if (auth('admin')->check() && auth('admin')->user() && auth('admin')->user()->can('settings:updatePassword'))
                                    <li class="slide menu-item-sub">
                                        <a href="{{ route('admin.update_password') }}" class="side-menu__item ">
                                            Update Password
                                        </a>
                                    </li>
                                @endif
                                @if (auth('admin')->check() && auth('admin')->user() && auth('admin')->user()->can('settings:uiSettings'))
                                    <li class="slide menu-item-sub">
                                        <a href="{{ route('admin.ui-settings.view') }}" class="side-menu__item ">
                                            UI Settings
                                        </a>
                                    </li>
                                @endif
                                @if (auth('admin')->check() && auth('admin')->user() && auth('admin')->user()->can('settings:reviewPopup'))
                                    <li class="slide menu-item-sub">
                                        <a href="{{ route('admin.review-popup-settings.view') }}" class="side-menu__item ">
                                            Review Popup Settings
                                        </a>
                                    </li>
                                @endif
                                @if (auth('admin')->check() && auth('admin')->user() && auth('admin')->user()->can('settings:logs'))
                                    <li class="slide menu-item-sub">
                                        <a href="{{ route('admin.logs.view') }}" class="side-menu__item ">
                                            Logs
                                        </a>
                                    </li>
                                @endif
                                @if (auth('admin')->check() && auth('admin')->user() && auth('admin')->user()->can('settings:apiToken'))
                                    <li class="slide menu-item-sub">
                                        <a href="{{ route('admin.apitoken.create') }}" class="side-menu__item ">
                                            API Token
                                        </a>
                                    </li>
                                @endif
                                @if (auth('admin')->check() && auth('admin')->user() && auth('admin')->user()->can('settings:banIps'))
                                    <li class="slide menu-item-sub">
                                        <a href="{{ route('admin.ip_ban') }}" class="side-menu__item ">
                                            Ban IP's
                                        </a>
                                    </li>
                                @endif
                                @if (auth('admin')->check() && auth('admin')->user() && auth('admin')->user()->can('settings:emailBroadcasting'))
                                    <li class="slide menu-item-sub">
                                        <a href="{{ route('admin.emailbroadcast') }}" class="side-menu__item ">
                                            Email Broadcasting
                                        </a>
                                    </li>
                                @endif

                            </ul>
                        </li>
                        @endif
                    </ul>
                    {{-- <ul class="main-menu">

                        @foreach ($categories as $category)
                        <li class="slide__category menu-item-category">
                            <span class="category-name">{{ $category->category_name }}</span>
                        </li>

                        @foreach ($category->main_menus as $main)
                        @php
                        // Check if the current menu has submenus
                        $sub_menus = $main->sub_menus;
                        $requestUri = request()->getPathInfo();
                        $open = $sub_menus->contains(function ($item) use ($requestUri) {
                        return $item->filename == $requestUri;
                        }) ? 'open' : '';
                        @endphp
                        @if ((in_array($main->id, $rolePermissionsList) || $userRole == 'Super Admin') &&
                        $main->show_in_menu == 1)
                        <li class="slide {{ ($sub_menus->count() > 0) ? 'has-sub' : '' }} menu-item-main {{ $open }}">
                            <a href="{{ $main->filename }}" class="side-menu__item">
                                <i class="side-menu__icon {{ $main->icon }}"></i>
                                <span class="side-menu__label">{{ $main->pagename }}</span>
                                @if (!empty($sub_menus->count() > 0))
                                <i class="ri-arrow-down-s-line side-menu__angle"></i>
                                @endif
                            </a>
                            <ul class="slide-menu child1">
                                @foreach ($sub_menus as $sub)
                                @php
                                $active = ($requestUri == $sub->filename) ? 'active' : '';
                                @endphp
                                @if (in_array($sub->id, $rolePermissionsList) || $userRole == 'Super Admin')
                                @if ($sub->pagename != 'Permissions List')
                                <li class="slide menu-item-sub">
                                    <a href="{{ $sub->filename }}" class="side-menu__item {{ $active }}">
                                        {{ $sub->pagename }}
                                    </a>
                                </li>
                                @endif
                                @endif
                                @endforeach
                            </ul>
                        </li>
                        @endif
                        @endforeach
                        @endforeach

                    </ul> --}}
                    <div class="slide-right" id="slide-right"><svg xmlns="http://www.w3.org/2000/svg" fill="#7b8191"
                            width="24" height="24" viewBox="0 0 24 24">
                            <path d="M10.707 17.707 16.414 12l-5.707-5.707-1.414 1.414L13.586 12l-4.293 4.293z"></path>
                        </svg></div>
                </nav>
                <!-- End::nav -->

            </div>
            <!-- End::main-sidebar -->

        </aside>
        <!-- End::app-sidebar -->
