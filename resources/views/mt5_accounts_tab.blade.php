<div class="col-12">
    <div class="card">
        <div class="card-body p-0">
            <ul class="nav nav-tabs checkout-tabs mb-0" id="myTab" role="tablist">
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
