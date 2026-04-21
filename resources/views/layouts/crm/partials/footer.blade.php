<div class="pc-content">
    <div class="py-5">
        <div class="row">
            <!-- Logo & Company Info -->
            <div class="mb-4 col-md-4">
                <a href="/" class="mb-3 d-inline-block">
                    <img src="/{{ $settings['admin_sidebar_logo'] }}" alt="LQH Markets Logo" class="img-fluid"
                        style="max-height: 40px;">
                </a>
                <p class="mb-1 text-muted">
                    <span class="notranslate">LQH Integrated Ltd </span> <br>
                    Ground Floor, Rodney Court Building, Rodney Bay, Gros Islet, Saint Lucia.
                </p>
                <p class="mb-1">
                    Email: <a href="mailto:support@lqhmarkets.com" class="text-success">support@lqhmarkets.com</a>
                </p>
                <p class="mb-0 text-muted notranslate">© {{ date('Y') }} LQH Markets | All rights reserved.</p>
            </div>

            <!-- Explore -->
            <div class="mb-4 col-6 col-md-2">
                <h6 class="mb-3 fw-bold">Explore</h6>
                <ul class="list-unstyled">
                    <li><a href="https://www.lqhmarkets.com/" class="text-decoration-none text-dark">Home</a></li>
                    <li><a href="https://www.lqhmarkets.com/mt5" class="text-decoration-none text-dark">MetaTrader 5</a>
                    </li>
                    <li><a href="https://www.lqhmarkets.com/about-us" class="text-decoration-none text-dark">About
                            Us</a></li>
                    <li><a href="https://www.lqhmarkets.com/help-center" class="text-decoration-none text-dark">Help
                            Center</a></li>
                    <li><a href="https://www.lqhmarkets.com/lot-size-calculator"
                            class="text-decoration-none text-dark">Lot Size Calculator</a></li>
                </ul>
            </div>

            <!-- Disclosures -->
            <div class="mb-4 col-6 col-md-2">
                <h6 class="mb-3 fw-bold footer-link-capitalize">Disclosures</h6>
                <ul class="list-unstyled">
                    <li><a href="https://www.lqhmarkets.com/risk-disclaimer"
                            class="text-decoration-none text-dark footer-link-capitalize">Risk Disclaimer</a></li>
                    <li><a href="https://www.lqhmarkets.com/terms-conditions"
                            class="text-decoration-none text-dark footer-link-capitalize">Terms &amp; Conditions</a>
                    </li>
                    <li><a href="https://www.lqhmarkets.com/privacy-policy"
                            class="text-decoration-none text-dark footer-link-capitalize">Privacy Policy</a></li>
                    <li><a href="https://www.lqhmarkets.com/client-agreement"
                            class="text-decoration-none text-dark footer-link-capitalize">Client Agreement</a></li>
                </ul>
            </div>

            <!-- Company -->
            <div class="mb-4 col-6 col-md-2">
                <h6 class="mb-3 fw-bold footer-link-capitalize">Company</h6>
                <ul class="list-unstyled">
                    <li><a href="https://www.lqhmarkets.com/about-us"
                            class="text-decoration-none text-dark footer-link-capitalize">About</a></li>
                    <li><a href="https://www.lqhmarkets.com/contact-us"
                            class="text-decoration-none text-dark footer-link-capitalize">Contact</a></li>
                </ul>
            </div>

            <!-- Social Media -->
            <div class="mb-4 col-6 col-md-2">
                <h6 class="mb-3 fw-bold">Social Media</h6>
                <div class="gap-2 d-flex flex-column">
                    <a href="https://discord.gg/lqhmarkets" target="_blank"
                        class="d-flex align-items-center text-decoration-none text-dark">
                        <img src="https://cdn.prod.website-files.com/66d6faa07d7bd55c6f3ca508/683d55e6248e95183cea86a5_icons8-discord-500.png"
                            alt="Discord" style="height: 20px; width: 20px;" class="me-2">
                        Discord
                    </a>
                    <a href="https://instagram.com/lqhmarkets" target="_blank"
                        class="d-flex align-items-center text-decoration-none text-dark">
                        <img src="https://cdn.prod.website-files.com/66d6faa07d7bd55c6f3ca508/683d5538ee0b29783635919a_icons8-instagram-500.png"
                            alt="Instagram" style="height: 20px; width: 20px;" class="me-2">
                        Instagram
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Legal & Risk Section -->
    <div class="py-4 border-top">
        <div class="text-muted small">
            <p><strong>Legal:</strong> <span class="notranslate">LQH Integrated Ltd</span> is LQHMarkets.com and the LQH
                Markets brand and trademark is owned by <span class="notranslate">LQH Integrated Ltd</span>.</p>
            <p><span class="notranslate">LQH Integrated Ltd</span> holds an International Brokerage and Clearing House
                License in Comoros with license number L15833/LIL.</p>
            <p><span class="notranslate">LQH Integrated Ltd</span> holds a license in St. Lucia as an International
                Business Company with registration number 2023-00570.</p>
            <p><strong>Risk Warning:</strong> An investment in derivatives may mean investors may lose an amount even
                greater than their original investment. Anyone wishing to invest in any of the products mentioned in
                <a href="https://www.LQHMarkets.com" class="text-success">www.LQHMarkets.com</a> should seek their own
                financial or professional advice…
            </p>
            <p><strong>Restricted Regions:</strong> <span class="notranslate">LQH Integrated Limited</span> does not
                provide services for citizens/residents of the United States, Cuba, Iran, Myanmar, North Korea, Sudan,
                China, Singapore and to jurisdictions on the FATF, OFAC and EU/UN sanctions lists.</p>
        </div>
    </div>
</div>


{{-- <div class="pc-content">
    <p class="m-0 text-center w-100" style="font-size: 11px; padding-top: 90px; padding-bottom: 20px; color:#2ca192">LQH
        Integrated Ltd
        <a target="_blank" href="https://www.lqhmarkets.com/risk-disclaimer">Risk Disclaimer</a> |
        <a target="_blank" href="https://www.lqhmarkets.com/terms-conditions">Terms & Conditions</a> |
        <a target="_blank" href="https://www.lqhmarkets.com/privacy-policy">Privacy Policy</a>
    </p>
</div> --}}

<script>
    // Track Intercom visibility state
    var isIntercomOpen = false;

    // Function to toggle Intercom messenger
    function toggleIntercom() {
        if (window.Intercom) {
            // We use a try-catch since Intercom API might not be fully loaded yet
            try {
                if (isIntercomOpen) {
                    window.Intercom('hide');
                    isIntercomOpen = false;
                } else {
                    window.Intercom('show');
                    isIntercomOpen = true;
                }
            } catch (e) {
                console.log('Intercom not ready yet');
            }
        }
    }

    // Listen for Intercom events to keep state in sync
    if (window.addEventListener) {
        window.addEventListener('message', function (e) {
            if (e.data && e.data.event) {
                if (e.data.event === 'intercom:show') {
                    isIntercomOpen = true;
                } else if (e.data.event === 'intercom:hide') {
                    isIntercomOpen = false;
                }
            }
        }, false);
    }

    // Handle Support button click in header (dropdown menu)
    document.addEventListener('DOMContentLoaded', function () {
        var supportBtn = document.getElementById('support-intercom-btn');
        if (supportBtn) {
            supportBtn.addEventListener('click', function (e) {
                e.preventDefault();
                toggleIntercom();
            });
        }

        // Handle Support button click in sidebar
        var supportSidebarBtn = document.getElementById('support-intercom-sidebar');
        if (supportSidebarBtn) {
            supportSidebarBtn.addEventListener('click', function (e) {
                e.preventDefault();
                toggleIntercom();
            });
        }
    });

    window.intercomSettings = {
        api_base: "https://api-iam.intercom.io",
        app_id: "hcaolnkq",
        user_id: "{{ auth()->user()->id }}",
        name: "{{ auth()->user()->fullname }}",
        email: "{{ auth()->user()->email }}",
        created_at: {{ optional(auth()->user()->created_at)->timestamp ?? 'null' }},
        user_hash: "{{ hash_hmac('sha256', auth()->user()->id, env('INTERCOM_SECRET_KEY')) }}"
    };
</script>

<script>
    (function () {
        var w = window;
        var ic = w.Intercom;
        if (typeof ic === "function") {
            ic('reattach_activator');
            ic('update', w.intercomSettings);
        } else {
            var d = document;
            var i = function () { i.c(arguments); };
            i.q = [];
            i.c = function (args) { i.q.push(args); };
            w.Intercom = i;
            var l = function () {
                var s = d.createElement('script');
                s.type = 'text/javascript';
                s.async = true;
                s.src = 'https://widget.intercom.io/widget/hcaolnkq';
                var x = d.getElementsByTagName('script')[0];
                x.parentNode.insertBefore(s, x);
            };
            if (document.readyState === 'complete') {
                l();
            } else if (w.attachEvent) {
                w.attachEvent('onload', l);
            } else {
                w.addEventListener('load', l, false);
            }
        }
    })();

</script>

{{-- <div id="changeLeverage" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle"
    aria-hidden="true">
    <div class="modal-dialog modal-md modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLiveLabel">Edit Leverage</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" id="updateLeverageForm" action="{{ route('update-leverage') }}">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="form-group">
                                <label class="form-label">Leverage</label>
                                <select class="form-control" name="leverage" id="leverage">
                                    <!-- Options will be populated dynamically -->
                                </select>
                            </div>
                            <hr class="my-3 border border-secondary-subtle">
                        </div>
                    </div>
                    <div class="flex-grow-1 text-end">
                        <button type="button" class="btn btn-link-danger btn-pc-default"
                            data-bs-dismiss="modal">Cancel</button>
                        <input class="btn btn-primary" type="submit" name="update_leverage" value="Update Leverage">
                    </div>
                </div>
                <input type="hidden" name="modalAccountId" id="modalAccountId">
                <input type="hidden" name="accountId" id="accountId">
            </form>
        </div>
    </div>
</div> --}}
{{-- @if(session('success'))
<script>
    Swal.fire({
        title: '{{ session('success') }}',
        icon: 'success'
    }).then(() => {
        // Optionally, you can reload the page after showing the alert
        location.reload();
    });
</script>
@endif
@if(session('warning'))
<script>
    Swal.fire({
        title: '{{ session('warning') }}',
        html: '{!! session('error') !!}',
        icon: 'warning'
    }).then(() => {
        // Optionally, you can reload the page after showing the alert
        location.reload();
    });
</script>
@endif --}}

@if(session('warning'))
    @php
        $errorMessage = session('error');
    @endphp
    <script>
        Swal.fire({
            title: '{{ session('warning') }}',
            html: `{!! $errorMessage !!}`,
            icon: 'warning',
            allowOutsideClick: false,
            allowEscapeKey: false,
            backdrop: true,
            didOpen: () => {
                document.body.style.overflow = 'hidden';
            },
            willClose: () => {
                document.body.style.overflow = 'auto';
            }
        }).then(() => {
            // Optionally, you can reload the page after showing the alert
            location.reload();
        });
    </script>
@endif

@if(session('error'))
    @php
        $errorTitle = session('error_title') ?? 'Something went wrong';
    @endphp
    <script>
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
    </script>
@endif





<div id="addBankModal2" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle"
    aria-hidden="true">
    <div class="modal-dialog modal-md modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLiveLabel">Wallet Details</h5><button type="button"
                    class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" id="bankDetailsForm">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="form-group"><label class="form-label">Wallet Name</label><input type="text"
                                    class="form-control" autofocus name="wallet_name" required></div>
                            {{-- <div class="form-group"><label class="form-label">Select Your Cryptocurrency</label>
                                <select id="my-select" class="form-control" name="wallet_currency" required>
                                    <option value="BTC">BTC</option>
                                    <option value="USDT">USDT</option>
                                </select>
                            </div> --}}
                            <div class="form-group"><label class="form-label">Wallet Network</label>
                                <select id="my-select" class="form-control" name="wallet_network" required>
                                    <option value="BTC">BTC</option>
                                    <option value="ETH_USDT">USDT/ERC20</option>
                                    <option value="USDT-TRX">USDT/TRC20</option>
                                </select>
                            </div>
                            <div class="form-group"><label class="form-label">Crypto Wallet Address</label><input
                                    type="text" class="form-control" name="wallet_address" id="walletAddressInput"
                                    required>
                                <div class="invalid-feedback" id="walletAddressError" style="display: none;"></div>
                            </div>
                            <div class="form-group"><label class="form-label">Status</label>
                                <select id="my-select" class="form-control" name="status" required>
                                    <option value="1">Active</option>
                                    <option value="0">Inactive</option>
                                </select>
                            </div>
                            <hr class="my-3 border border-secondary-subtle">
                        </div>
                    </div>
                    <div class="flex-grow-1 text-end"><button type="button" class="btn btn-link-danger btn-pc-default"
                            data-bs-dismiss="modal">Cancel</button>
                        <input class="btn btn-primary" type="submit" name="add_wallet_details"
                            value="Add Wallet Details">
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<div id="editBankModal2" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle"
    aria-hidden="true">
    <div class="modal-dialog modal-md modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLiveLabel">Edit Wallet Details</h5><button type="button"
                    class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" id="editDetailsForm">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-sm-12">
                            <input type="hidden" class="form-control" name="id">
                            <div class="form-group"><label class="form-label">Wallet Name</label>
                                <input type="text" class="form-control" autofocus name="wallet_name" required>
                            </div>

                            <div class="form-group"><label class="form-label">Wallet Network</label>
                                <select id="my-select" class="form-control" name="wallet_network" required>
                                    <option value="BTC">BTC</option>
                                    <option value="ETH_USDT">USDT/ERC20</option>
                                    <option value="USDT-TRX">USDT/TRC20</option>
                                </select>
                            </div>
                            <div class="form-group"><label class="form-label">Wallet Address</label><input type="text"
                                    class="form-control" name="wallet_address" id="editWalletAddressInput" required>
                                <div class="invalid-feedback" id="editWalletAddressError" style="display: none;"></div>
                            </div>
                            <div class="form-group"><label class="form-label">Status</label>
                                <select id="my-select" class="form-control" name="status" required>
                                    <option value="1">Active</option>
                                    <option value="0">Inactive</option>
                                </select>
                            </div>
                            <hr class="my-3 border border-secondary-subtle">
                        </div>
                    </div>
                    <div class="flex-grow-1 text-end"><button type="button" class="btn btn-link-danger btn-pc-default"
                            data-bs-dismiss="modal">Cancel</button>
                        <input class="btn btn-primary" type="submit" name="update_wallet_details"
                            value="Update Wallet Details">
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // Client-side wallet address validation
    function validateWalletAddress(address, network) {
        address = address.trim();
        var length = address.length;

        // Check for spaces in the address
        if (address.includes(' ')) {
            return 'Wallet address cannot contain spaces. Please remove any spaces from the address.';
        }

        switch (network) {
            case 'BTC':
                // BTC: Must start with 1, 3, or bc1, length 26-62
                var startsWithValid = address.startsWith('1') ||
                    address.startsWith('3') ||
                    address.startsWith('bc1');

                if (!startsWithValid) {
                    return 'BTC address must start with 1, 3, or bc1.';
                }

                if (length < 26 || length > 62) {
                    return 'BTC address must be between 26 and 62 characters long.';
                }
                break;

            case 'ETH_USDT':
                // USDT ERC20: Must start with 0x, must be 42 characters long
                if (!address.startsWith('0x')) {
                    return 'USDT ERC20 address must start with 0x.';
                }

                if (length !== 42) {
                    return 'USDT ERC20 address must be exactly 42 characters long.';
                }
                break;

            case 'USDT-TRX':
                // USDT TRC20: Must start with T, must be 34 characters long
                if (!address.startsWith('T')) {
                    return 'USDT TRC20 address must start with T.';
                }

                if (length !== 34) {
                    return 'USDT TRC20 address must be exactly 34 characters long.';
                }
                break;

            default:
                return 'Invalid wallet network type.';
        }

        return null; // Valid
    }

    // Real-time validation for addBankModal2
    $(document).ready(function () {
        var $walletAddressInput = $('#walletAddressInput');
        var $walletNetworkSelect = $('select[name="wallet_network"]');
        var $errorDiv = $('#walletAddressError');

        function validateAndShowError() {
            var address = $walletAddressInput.val();
            var network = $walletNetworkSelect.val();

            // Only validate if there's input
            if (address.trim() === '') {
                $walletAddressInput.removeClass('is-invalid');
                $errorDiv.hide();
                return;
            }

            var error = validateWalletAddress(address, network);
            if (error) {
                $walletAddressInput.addClass('is-invalid');
                $errorDiv.text(error).show();
            } else {
                $walletAddressInput.removeClass('is-invalid');
                $errorDiv.hide();
            }
        }

        // Validate on address input
        $walletAddressInput.on('input', validateAndShowError);

        // Validate on network change
        $walletNetworkSelect.on('change', validateAndShowError);

        // Clear validation when modal is closed
        $('#addBankModal2').on('hidden.bs.modal', function () {
            $walletAddressInput.val('').removeClass('is-invalid');
            $errorDiv.hide();
        });

        // Reset network to default when modal opens
        $('#addBankModal2').on('shown.bs.modal', function () {
            $walletNetworkSelect.val('BTC');
            $walletAddressInput.removeClass('is-invalid');
            $errorDiv.hide();
        });
    });

    // Real-time validation for editBankModal2
    $(document).ready(function () {
        var $editWalletAddressInput = $('#editWalletAddressInput');
        var $editWalletNetworkSelect = $('#editBankModal2 select[name="wallet_network"]');
        var $editErrorDiv = $('#editWalletAddressError');

        function validateEditWalletAndShowError() {
            var address = $editWalletAddressInput.val();
            var network = $editWalletNetworkSelect.val();

            // Only validate if there's input
            if (address.trim() === '') {
                $editWalletAddressInput.removeClass('is-invalid');
                $editErrorDiv.hide();
                return;
            }

            var error = validateWalletAddress(address, network);
            if (error) {
                $editWalletAddressInput.addClass('is-invalid');
                $editErrorDiv.text(error).show();
            } else {
                $editWalletAddressInput.removeClass('is-invalid');
                $editErrorDiv.hide();
            }
        }

        // Validate on address input
        $editWalletAddressInput.on('input', validateEditWalletAndShowError);

        // Validate on network change
        $editWalletNetworkSelect.on('change', validateEditWalletAndShowError);

        // Clear validation when modal is closed
        $('#editBankModal2').on('hidden.bs.modal', function () {
            $editWalletAddressInput.val('').removeClass('is-invalid');
            $editErrorDiv.hide();
        });
    });

    $("#bankDetailsForm").submit(function (e) {
        e.preventDefault();

        var walletAddress = $("input[name='wallet_address']", this).val();
        var walletNetwork = $("select[name='wallet_network']", this).val();

        // Client-side validation
        var validationError = validateWalletAddress(walletAddress, walletNetwork);
        if (validationError) {
            Swal.fire({
                title: "Invalid Wallet Address",
                text: validationError,
                icon: "error"
            });
            return false;
        }

        $.ajax({
            url: "{{ route('wallet.store') }}",
            type: "POST",
            data: $(this).serialize(),
            beforeSend: function () {
                $("#bankDetailsForm input,#bankDetailsForm select").attr("disabled", "true");
            },
            success: function (data) {
                if (data.success == true) {
                    Swal.fire({
                        title: "Check email to verify new wallet address",
                        icon: "success",
                        allowOutsideClick: false,
                        allowEscapeKey: false
                    }).then((result) => {
                        $('#addBankModal2').modal('hide');
                        window.location.href = '/user-profile#wallets';
                    });
                } else {
                    Swal.fire({
                        title: data.message || "Something went wrong",
                        icon: "error"
                    });
                    $("#bankDetailsForm input,#bankDetailsForm select").removeAttr("disabled");
                }
            },
            error: function (xhr, status, error) {
                var errorMessage = "Something went wrong";
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }
                Swal.fire({
                    title: "Error",
                    text: errorMessage,
                    icon: "error"
                });
                $("#bankDetailsForm input,#bankDetailsForm select").removeAttr("disabled");
            }
        });
    })
    $("#editDetailsForm").submit(function (e) {
        e.preventDefault();

        var walletAddress = $("input[name='wallet_address']", this).val();
        var walletNetwork = $("select[name='wallet_network']", this).val();

        // Client-side validation
        var validationError = validateWalletAddress(walletAddress, walletNetwork);
        if (validationError) {
            Swal.fire({
                title: "Invalid Wallet Address",
                text: validationError,
                icon: "error"
            });
            return false;
        }

        $.ajax({
            url: "{{ route('wallet.verify_edit') }}",
            type: "POST",
            data: $(this).serialize(),
            beforeSend: function () {
                $("#editDetailsForm input, #editDetailsForm select, #editDetailsForm button").attr("disabled", "true");
            },
            success: function (data) {
                console.log(data);
                if (data.success === true) {
                    Swal.fire({
                        title: "Check your email to verify new wallet details",
                        icon: "success"
                    }).then(() => {
                        $('#editBankModal2').modal('hide');
                        window.location.href = '/user-profile#wallets';
                    });
                } else {
                    Swal.fire({
                        title: data.message || "Something went wrong",
                        icon: "error"
                    });
                    $("#editDetailsForm input, #editDetailsForm select, #editDetailsForm button").removeAttr("disabled");
                }
            },
            error: function (xhr, status, error) {
                console.error("AJAX Error:", status, error);
                var errorMessage = "Please try again later.";
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }
                Swal.fire({
                    title: "An error occurred",
                    text: errorMessage,
                    icon: "error"
                });
                $("#editDetailsForm input, #editDetailsForm select, #editDetailsForm button").removeAttr("disabled");
            }
        });
    })
    $("#verify-user-kyc").click(function (e) {
        e.preventDefault();

        // Decide which KYC provider to use based on settings shared to views
        var provider = "{{ $settings['kyc_provider'] ?? 'sumsub' }}";
        var cardBody = $(this).closest(".card-body");

        if (provider === 'veriff') {
            // Show loader inside KYC section
            var loader = `
                <div class="py-5 text-center" id="veriff-loader">
                    <div class="mb-4 d-flex justify-content-center">
                        <div class="spinner-border" style="width: 50px; height: 50px; color: #00b2a9;" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                    <h5 class="mb-2">Redirecting to verification...</h5>
                    <p class="text-muted">Please wait while we connect you to Veriff</p>
                </div>
            `;
            cardBody.html(loader);

            // Small delay to show loader, then redirect to Veriff
            setTimeout(function () {
                window.location.href = '/veriff';
            }, 500);
        } else {
            // Sumsub supports iframe embedding
            var iframe = "<iframe id='kyc_verification_frame' src='/sumsub' class='w-100' style='height: 100vh;'></iframe>";
            cardBody.html(iframe);
        }
    });
</script>
<script type="module" src="/assets/js/popper.min.js"></script>
<script type="module" src="/assets/js/simplebar.min.js"></script>
<script type="module" src="/assets/js/bootstrap.min.js"></script>
<script type="module" src="/assets/js/custom-font.js"></script>
<script src="/assets1/vendors/datatables.net-bs4/dataTables.bootstrap4.js"></script>
<script type="module" src="/assets/js/pcoded.js"></script>
<script type="module" src="/assets/js/feather.min.js?v=1"></script>
<script type="module" src="/assets/js/dashboard-default.js"></script>
@include('sweetalert::alert')
<script>
    // $(document).ready(function() {
    //     $('[role="tab"]').click(function() {
    //         if ($(this).attr("href")) {
    //             location.hash = $(this).attr("href");
    //         } else if ($(this).data("bs-target")) {
    //             location.hash = $(this).data("bs-target");
    //         }
    //     });

    //     if (location.hash) {
    //         var tab = location.hash;

    //         if ($('a[href="' + tab + '"]').length) {
    //             const triggerEl = document.querySelector('a[href="' + tab + '"]');
    //             bootstrap.Tab.getInstance(triggerEl).show(); // Select tab by name
    //             //     $('a[href="' + tab + '"]').tab('show');
    //         }
    //     }
    // })
</script>
<script type="module" src="/assets/js/custom.js?v=<?= time() ?>"></script>
{{--
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const changeLeverageModal = document.getElementById('changeLeverage');
        const leverageSelect = document.getElementById('leverage');
        const modalAccountIdInput = document.getElementById('modalAccountId');
        const accountIdInput = document.getElementById('accountId');

        changeLeverageModal.addEventListener('show.bs.modal', (event) => {
            // Get the button that triggered the modal
            const button = event.relatedTarget;

            // Extract data from the button
            const accountTypeId = button.getAttribute('data-id');
            const leverageValue = button.getAttribute('data-leverage');
            const accountId = button.getAttribute('data-account');

            // Populate the leverage select field dynamically
            $.ajax({
                url: "{{ route('get-leverage') }}?id=" + accountTypeId,
                success: function (data) {
                    // Clear existing options
                    $("#leverage").html("");

                    // Populate the select with new options
                    $.each(data, function (key, value) {
                        const isSelected = value.account_leverage == leverageValue ? "selected" : "";
                        $("#leverage").append(
                            `<option value="${value.account_leverage}" ${isSelected}>${value.account_leverage}</option>`
                        );
                    });

                    // Set the selected leverage in the dropdown
                    leverageSelect.value = leverageValue;
                }
            });

            // Set the account type ID in the hidden input field
            modalAccountIdInput.value = accountTypeId;
            accountIdInput.value = accountId;
        });
    }); --}}

</script>

@include('components.google-translate')

</body>

</html>