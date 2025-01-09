<?php
$requestUri = trim($_SERVER['REQUEST_URI']);
$requestUri = parse_url($requestUri, PHP_URL_PATH);
$userRoleID = session('userID');
$userRole = session('userRole');
$categories = page_categories(session('userRoleID'));

// $userRoleID = session('userRoleID');

$rolePermissionsList = rolePermissions($userRole);
$filePermissions = filePermissions($userRole);
?>
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
    @if (!View::hasSection("noDatatable"))
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
        if (app()->environment('local')) {
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
            --primary-color: {{ $settings['sidebar_color'] }};
            --primary-rgb: {{ hexToRGB($settings['sidebar_color']) }}
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

        .cursor-pointer{
            cursor: pointer !important;
        }

        /* @media (min-width: 768px) {
      .header-search {
        width: 450px;
      }
    } */
    </style>

    @yield("styles")
</head>

<body>
    @if (app()->environment('local'))
        <div style="position: fixed; top: 0; width: 100%; background-color: #ff1f32; color: #ffffff; text-align: center; padding: 10px; z-index: 1000;">
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
                            <!-- <li><a class="dropdown-item d-flex align-items-center" href="javascript:void(0);"><i
                    class="fe fe-user me-2 fs-18 text-primary"></i>Profile</a></li> -->
                            <!-- <li><a class="dropdown-item d-flex align-items-center" href="javascript:void(0);"><i
                    class="fe fe-calendar me-2 fs-18 text-primary"></i>Task Borad</a></li> -->
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
                    <ul class="main-menu">
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
                            @if (
                                (in_array($main->id, $rolePermissionsList) || $userRole == "Super Admin") &&
                                $main->show_in_menu == 1
                            )
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
                                            @if (in_array($sub->id, $rolePermissionsList) || $userRole == "Super Admin")
                                                @if($sub->pagename != 'Permissions List')
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

                    </ul>
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
