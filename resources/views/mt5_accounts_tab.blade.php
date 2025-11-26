<div class="col-12">
    <div class="card">
        <div class="p-0 card-body">
            <ul class="mb-0 nav nav-tabs checkout-tabs" id="myTab" role="tablist">
                <li class="nav-item" role="presentation">
                    <a class="nav-link {{ request()->is('liveAccounts') ? 'active' : '' }}" id="ecomtab-tab-1"
                        href="{{ url('/liveAccounts') }}" role="tab" aria-controls="ecomtab-1" aria-selected="true" tabindex="-1">
                        <div class="media align-items-center">
                            <div class="avtar avtar-s">
                                <span class="pc-micon">
                                    <svg class="pc-icon">
                                        <use xlink:href="#custom-shield"></use>
                                    </svg>
                                </span>
                            </div>
                            <div class="media-body ms-2">
                                <h6 class="mb-0">Live Accounts</h6>
                            </div>
                        </div>
                    </a>
                </li>
                <li class="nav-item" role="presentation">
                    <a class="nav-link {{ request()->is('demoAccounts') ? 'active' : '' }}" href="{{ url('/demoAccounts') }}"
                        aria-controls="ecomtab-2" aria-selected="false" tabindex="-1">
                        <div class="media align-items-center">
                            <div class="avtar avtar-s">
                                <span class="pc-micon">
                                    <svg class="pc-icon">
                                        <use xlink:href="#custom-setting-outline"></use>
                                    </svg>
                                </span>
                            </div>
                            <div class="media-body ms-2">
                                <h6 class="mb-0">Demo Accounts</h6>
                            </div>
                        </div>
                    </a>
                </li>

                <li class="nav-item" role="presentation">
                    <a class="nav-link" href="#" data-bs-toggle="modal" data-bs-target="#staticBackdrop"
                        aria-controls="ecomtab-3" aria-selected="false" tabindex="-1">
                        <div class="media align-items-center">
                            <div class="avtar avtar-s">
                                <span class="pc-micon">
                                    <svg class="pc-icon">
                                        <use xlink:href="#custom-direct-inbox"></use>
                                    </svg>
                                </span>
                            </div>
                            <div class="media-body ms-2">
                                <h6 class="mb-0">Platform Download</h6>
                            </div>
                        </div>
                    </a>
                </li>
            </ul>
        </div>
    </div>
</div>

@once
    <div class="modal fade" id="staticBackdrop" tabindex="-1" aria-labelledby="staticBackdropLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable platform-download-modal">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="staticBackdropLabel">Platform Downloads</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-12">
                            <p class="mb-3 text-uppercase text-muted fw-semibold small">MT5 Platform</p>
                        </div>
                        <div class="col-lg-4 col-md-4 col-sm-6 mb-3">
                            <a target="_blank" href="{{ $settings['mt5_android_platform'] }}"
                                class="text-center d-block platform-download-btn">
                                <div class="platform-icon-wrapper">
                                    <img class="platform-icon" src="/assets/platform/playstore.png"
                                        alt="Android">
                                </div>
                                <span class="platform-label">Android</span>
                            </a>
                        </div>
                        <div class="col-lg-4 col-md-4 col-sm-6 mb-3">
                            <a target="_blank" href="{{ $settings['mt5_ios_platform'] }}"
                                class="text-center d-block platform-download-btn">
                                <div class="platform-icon-wrapper">
                                    <img class="platform-icon" src="/assets/platform/appstore.png"
                                        alt="Apple iOS">
                                </div>
                                <span class="platform-label">Apple iOS</span>
                            </a>
                        </div>
                        <div class="col-lg-4 col-md-4 col-sm-6 mb-3">
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
                        <div class="col-lg-4 col-md-4 col-sm-6 mb-3">
                            <a target="_blank" href="https://web.x9trader.com/login?returnUrl=%2Fterminal"
                                class="text-center d-block platform-download-btn">
                                <div class="platform-icon-wrapper">
                                    <img class="platform-icon" src="/assets/images/x9.png"
                                        alt="X9 Web Terminal">
                                </div>
                                <span class="platform-label">Web Terminal</span>
                            </a>
                        </div>
                        <div class="col-lg-4 col-md-4 col-sm-6 mb-3">
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
@endonce
