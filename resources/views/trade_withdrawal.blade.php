@extends('layouts.crm.crm')
@section('content')
    <style>
        @media (max-width: 768px) {

            /* Adjust breakpoint as needed */
            .mob_width {
                width: 150%;
            }
        }
    </style>
    @if (session('success'))
        <script>
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: '{{ session('success') }}'
            }).then(() => {
                window.location.href = '{{ route('demoAccounts') }}';
            });
        </script>
    @endif
    @if (session('error'))
        <script>
            Swal.fire({
                icon: 'warning',
                title: "Something Went Wrong !",
                text: '{{ session('error') }}',
            });
        </script>
    @endif
    <div class="pc-container">
        <div class="pc-content">
            <div class="pb-0 mb-0 page-header">
                <div class="page-block">
                    <div class="row align-items-center">
                        <div class="col-md-12">
                            <div class="page-header-title h2">
                                {{-- <h4 class="mb-0">Fund</h4> --}}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-12">
                    <div class="card">
                        <div class="p-0 card-body">
                            @include('sub_header')
                        </div>
                    </div>
                    <div class="tab-content">


                        <div class="row">
                            <div class="col-xl-8">
                                <div class="card">
                                    <div class="card-body border-bottom">
                                        <h6>Withdraw Funds</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="my-4 divider"><span>SELECT MT5 ACCOUNT</span></div>
                                        <div class="row g-1">
                                            @foreach ($liveaccount_details as $liveaccount)

                                                <div class="col-md-3 col-lg-4 col-xl-4">
                                                    <div class="border rounded address-check">
                                                        <div class="form-check paycard">

                                                            <input id="liveaccount{{ $liveaccount->code }}" type="radio" name="live-account"
                                                            class="select-liveaccount form-check-input input-primary" data-balance="{{ $liveaccount->balance }}"
                                                            value="{{ $liveaccount->id }}"
                                                            @if(isset($account_id) && $account_id == $liveaccount->id) checked @endif>

                                                            <label class="form-check-label d-block" required>
                                                                <div class="p-1 my-1 row">
                                                                    <span class="mt-1 col-6">
                                                                        <span class="pb-0 mb-0 h5 d-block f-w-500 f-14">
                                                                            <img src="{{ asset('assets/images/mt5.png') }}"
                                                                                alt="user-image" class="wid-25 me-1 ms-1">
                                                                            {{ $liveaccount->code }}
                                                                        </span>
                                                                    </span>
                                                                    <span class="pb-0 mb-0 col-6 text-end pe-3">
                                                                        <span class="mb-0 h5 d-block f-w-500">

                                                                            ${{ $liveaccount->balance - $liveaccount->totalBonusDeposit }}
                                                                        </span>
                                                                        <span class="mb-0 text-muted f-10">Current
                                                                            Balance</span>
                                                                    </span>
                                                                </div>
                                                            </label>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                        {{-- <div class="my-4 divider"><span>SELECT WITHDRAW METHOD</span>
                                        </div>
                                        <div class="row g-1">
                                            @if ($walletenabled)
                                            <div class="col-6 col-lg-6 col-xl-6">
                                                <div class="border rounded address-check trade-withdraw-type mob_width">
                                                    <div class="form-check">
                                                        <input type="radio" name="withdraw_type"
                                                            class="form-check-input input-primary tradefund-deposit"
                                                            id="wallet_withdraw" value="Wallet Transfer"
                                                            data-type="Wallet-Transfer" checked>

                                                        <label class="form-check-label d-block" for="wallet_withdraw">
                                                            <div class="p-2 my-1">
                                                                <span class="row">
                                                                    <span class="mt-1 col-6">
                                                                        <span
                                                                            class="pb-0 mb-0 h5 d-block f-w-500 f-14">Wallets</span>
                                                                        <span class="f-10 text-muted">Wallet
                                                                            Transfer</span>
                                                                    </span>
                                                                    <span class="pb-0 mb-0 col-6 text-end pe-3">
                                                                        <span class="mb-0 h5 d-block f-w-500">${{
                                                                            $walletBalance ?? '0.0000' }}</span>
                                                                        <span class="mb-0 text-muted f-10">Current
                                                                            Balance</span>
                                                                    </span>
                                                                </span>
                                                            </div>
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                            @endif
                                        </div>
                                        <div class="my-4 divider"><span>WITHDRAW DETAILS</span></div>
                                        <div id="walletwithdrawal" class="trade-withdrawal-content">
                                            <form method="post" style="padding:10px;"
                                                class="md-float-material form-material" enctype="multipart/form-data"
                                                id="tradeWithdrawalForm">
                                                @csrf
                                                <input type="hidden" name="user[email]" value="{{ session('clogin') }}"
                                                    required class="form-control fill">
                                                <input type="hidden" name="account_id" value=""
                                                    class="user_code form-control fill" readonly required>
                                                <input type="hidden" name="withdraw_type" value="Wallet Withdrawal">

                                                <div class="row">
                                                    <div class="mt-2 col-12">
                                                        <div class="form-group row">
                                                            <label class="col-lg-4 col-form-label">ENTER AMOUNT:
                                                                <small class="text-muted d-block">Please
                                                                    enter the amount that you need to
                                                                    transfer</small>
                                                            </label>
                                                            <div class="col-lg-8">
                                                                <div class="mb-3 input-group">
                                                                    <span class="input-group-text">$</span>
                                                                    <input type="number" class="form-control"
                                                                        name="withdraw_amount"
                                                                        aria-label="Amount (to the nearest dollar)"
                                                                        min="0.01" step="0.01" required>
                                                                    <span class="input-group-text">.00</span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-lg-4"></div>
                                                    <div class="col-lg-8">
                                                        <div class="row g-1">
                                                            <input type="submit" name="fund_add"
                                                                class="btn btn-primary col-12"
                                                                value="Withdraw From Trade Account">
                                                        </div>
                                                    </div>
                                                </div>
                                            </form>
                                        </div> --}}
                                        <div class="card-body">
                                            <div class="my-4 divider"><span>SELECT WITHDRAW METHOD</span></div>
                                        </div>
                                        <div class="row g-1">
                                            <div class="col-md-3 col-lg-4 col-xl-4">
                                                <div class="border rounded address-check">
                                                    <div class="form-check"><input type="radio" name="withdraw_type"
                                                            class="form-check-input input-primary wallet-withdraw" value="1"
                                                            data-type="Trade Withdrawal" id="payopn-check-1" required>
                                                        <label class="form-check-label d-block" for="payopn-check-1">
                                                            <span class="p-2 card-body d-block">
                                                                <span class="mb-1 h6 f-w-500 d-block">CRYPTO
                                                                    WITHDRAWAL</span>
                                                                <span class="d-flex align-items-center">
                                                                    <span class="f-10 badge bg-light-success me-1">CRYPTO
                                                                        WALLET</span>
                                                                    <span class="ti ti-currency-bitcoin"></span>
                                                                </span>
                                                            </span>
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="my-4 divider"><span>WITHDRAW DETAILS</span></div>
                                        {{-- @if ($errors->any())
                                        <div class="alert alert-danger">
                                            <ul>
                                                @foreach ($errors->all() as $error)
                                                <li>{{ $error }}</li>
                                                @endforeach
                                            </ul>
                                        </div>
                                        @endif --}}
                                        <div class="trade-withdrawal Trade_Withdrawal">
                                            <form method="post" id="withdrawForm" style="padding:10px;"
                                                class="md-float-material form-material">
                                                @csrf
                                                <div class="row">
                                                    <input type="hidden" name="withdraw_type" class="withdraw-type"
                                                        value="Trade Withdrawal">
                                                    <input type="hidden" id="hiddenTwoFactorCode" name="two_factor_code">
                                                    <input type="hidden" name="account_id" id="selectedLiveAccount">
                                                    <div class="mt-2 col-12">
                                                        <div class="form-group row">
                                                            <label class="col-lg-4 col-form-label">
                                                                SELECT WALLET ACCOUNT:
                                                                <small class="text-muted d-block">
                                                                    Please select the Wallet account to which you wish to
                                                                    transfer your funds
                                                                </small>
                                                            </label>
                                                            <div class="col-lg-8">
                                                                @if (count($client_banks) == 0)
                                                                    <div class="form-group">
                                                                        <button type="button" class="btn btn-primary"
                                                                            data-bs-toggle="modal"
                                                                            data-bs-target="#addBankModal2">
                                                                            <i class="ti ti-plus f-18"></i> Add Wallet
                                                                            Information
                                                                        </button>
                                                                    </div>
                                                                @else
                                                                    <select name="client_wallet_id" required
                                                                        class="form-control fill" style="color:black;">
                                                                        @foreach ($client_banks as $bank)
                                                                            <option value="{{ $bank->id }}">
                                                                                {{ $bank->wallet_name }} /
                                                                                {{ $bank->wallet_network != 'BTC' ? $bank->wallet_currency : $bank->wallet_network }}
                                                                                / {{ $bank->wallet_network }}
                                                                            </option>
                                                                        @endforeach
                                                                    </select>
                                                                    <small data-bs-toggle="modal"
                                                                        data-bs-target="#addBankModal2"
                                                                        style="color: var(--primary-color); cursor: pointer;">
                                                                        + Add another wallet
                                                                    </small>
                                                                @endif
                                                            </div>
                                                        </div>


                                                        <div class="form-group row">
                                                            <label class="col-lg-4 col-form-label">
                                                                ENTER AMOUNT:
                                                                <small class="text-muted d-block">Please enter the amount
                                                                    that you need to withdraw</small>
                                                            </label>
                                                            <div class="col-lg-8">
                                                                <div class="mb-3 input-group">
                                                                    <span class="input-group-text">$</span>
                                                                    <input type="number" min="10" step="0.01" id="withdrawAmount"
                                                                        class="form-control" name="withdraw_amount"
                                                                        aria-label="Amount (to the nearest dollar)"
                                                                        @if(count($client_banks) > 0) required @endif>
                                                                    <span class="input-group-text">.00</span>
                                                                </div>
                                                                <p id="message" style="color: red; display: none;"></p>
                                                                <div id="confirmTransaction"
                                                                    style="display: none; margin-top: 10px;">
                                                                    <label class="col-lg-4 col-form-label"
                                                                        style="display: flex; align-items: center; padding-right: 10px; width: 100%;">
                                                                        Amount You Will Receive After Transaction:
                                                                        <span id="amountAfterFee"
                                                                            style="font-weight: bold; margin-left: 10px;">$0.00</span>
                                                                    </label>
                                                                    <input class="mt-1 form-check-input" type="checkbox"
                                                                        id="confirmCheckbox" name="confirmCheckbox"
                                                                        required>
                                                                    <label for="confirmCheckbox">I confirm this
                                                                        transaction.</label>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-lg-4"></div>
                                                    <div class="pb-4 col-lg-8">
                                                        <div class="form-check">
                                                            <input class="mt-1 form-check-input" type="checkbox"
                                                                id="cryptoWarningCheckbox" name="confirmcryptoCheckbox"
                                                                required>
                                                            <label class="form-check-label" for="cryptoWarningCheckbox">
                                                                Please ensure you send the correct cryptocurrency to the
                                                                correct wallet address and network. Transactions are
                                                                irreversible, and we are not responsible for any loss of
                                                                funds due to incorrect withdrawals. Double-check all details
                                                                before proceeding.
                                                            </label>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="row">
                                                    <div class="col-lg-4"></div>
                                                    <div class="col-lg-8">
                                                        <div class="row g-1">
                                                            <input type="submit" name="account_withdraw"
                                                                class="btn btn-primary col-12"
                                                                value="Withdraw From Trade Account">
                                                        </div>
                                                    </div>
                                                </div>
                                            </form>

                                            <!-- 2FA Verification Modal -->
                                            <div class="modal fade" id="twoFactorModal" tabindex="-1"
                                                aria-labelledby="twoFactorModalLabel" aria-hidden="true">
                                                <div class="modal-dialog modal-dialog-centered">
                                                    <!-- This class centers the modal -->
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title" id="twoFactorModalLabel">Enter 2FA Code
                                                            </h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                                aria-label="Close"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <label for="twoFactorCode">Enter your 2FA authentication
                                                                code:</label>
                                                            <input type="text" class="form-control" id="twoFactorCode"
                                                                name="two_factor_code" required maxlength="6" minlength="6">
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-primary"
                                                                data-bs-dismiss="modal">Cancel</button>
                                                            <button type="button" id="submitTwoFactor"
                                                                class="btn btn-primary">Confirm Withdrawal</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-4">
                                <div class="card coupon-card bg-primary">
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-8 d-flex flex-column align-items-start justify-content-center">
                                                <h3 class="text-white f-w-500">Fuel Your Trading Journey
                                                </h3>
                                                <span class="py-2 text-white f-16">Deposit now and unlock
                                                    the gateway to global markets.</span>
                                            </div>
                                            <div class="col-4 text-end">
                                                <img src="{{ asset('assets/images/fund_now.png') }}" alt="img"
                                                    class="img-fluid wid-110">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="card">
                                    <div class="py-2 card-body">
                                        <ul class="list-group list-group-flush">
                                            <li class="px-0 list-group-item">
                                                <div class="float-end">
                                                    <h3 class="mb-0 fw-medium">${{ $totals->balance }}
                                                    </h3>
                                                </div>
                                                <h5 class="mb-0 d-inline-block">TOTAL BALANCE</h5>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    {{--
    <script>
        $("#tradeWithdrawalForm").submit(function (e) {
            e.preventDefault();
            $.ajax({
                type: "POST",
                url: "{{ route('trade-withdrawal') }}",
                data: $(this).serialize(),
                success: function (response) {
                    Swal.fire({
                        icon: 'success',
                        title: response.success
                    }).then(() => {
                        window.location.reload(true);
                    });
                },
                error: function (xhr) {
                    Swal.fire({
                        icon: 'error',
                        title: xhr.responseJSON.message
                    });
                }
            });
        });
    </script> --}}
    <script>
        document.getElementById('withdrawAmount').addEventListener('input', function () {
            const amount = parseFloat(this.value);
            const message = document.getElementById('message');
            const confirmTransaction = document.getElementById('confirmTransaction');
            const confirmCheckbox = document.getElementById('confirmCheckbox');
            const amountAfterFee = document.getElementById('amountAfterFee');
            if (amount >= 10 && amount < 100) {
                message.style.display = 'block';
                message.textContent = "A network fee of $5 will be charged for this transaction. To avoid the network fee, the withdrawal amount must be greater than $100.";
                confirmTransaction.style.display = 'block';
                confirmCheckbox.required = true;
                amountAfterFee.textContent = `$${(amount - 5).toFixed(2)}`;
            } else if (amount < 10) {
                message.style.display = 'block';
                message.textContent = "The minimum withdrawal amount is $10.";
                confirmTransaction.style.display = 'none';
                confirmCheckbox.checked = false;
                confirmCheckbox.required = false;
            } else if (amount >= 100) {
                message.style.display = 'none';
                confirmTransaction.style.display = 'none';
                confirmCheckbox.checked = false;
                confirmCheckbox.required = false;
                amountAfterFee.textContent = `$${amount.toFixed(2)}`;
            } else {
                message.style.display = 'none';
                confirmTransaction.style.display = 'none';
                confirmCheckbox.checked = false;
                confirmCheckbox.required = false;
            }
        });


    </script>
    <!-- Bootstrap & jQuery for Modal Handling -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        jQuery(document).ready(function () {
            // Check if two-factor authentication is enabled for the user
            var twoFactorEnabled = <?= json_encode(auth()->user()->two_factor_secret ? true : false); ?>;
            console.log(twoFactorEnabled);
            jQuery('#twoFactorModal').modal('hide');

            // Handle the withdrawal button click
            jQuery('#account_withdraw').click(function () {
                var withdrawAmount = parseFloat(jQuery('#withdrawAmount').val());

                // Validate withdraw amount before proceeding
                if (isNaN(withdrawAmount) || withdrawAmount < 10) {
                    alert('Withdrawal amount must be at least 10.');
                    return; // Stop execution if validation fails
                }

                if (twoFactorEnabled) {
                    // Show the modal for 2FA **only if validation passes**
                    jQuery('#twoFactorModal').modal('show');

                    // Handle the 2FA submission
                    jQuery('#submitTwoFactor').off('click').on('click', function () { // Prevent multiple bindings
                        var twoFactorCode = jQuery('#twoFactorCode').val();

                        // Validate the 2FA code
                        if (twoFactorCode.length !== 6 || isNaN(twoFactorCode)) {
                            alert('Please enter a valid 6-digit 2FA code.');
                            return; // Prevent form submission if the code is invalid
                        }

                        // Store the code in the hidden input field and submit the form
                        jQuery('#hiddenTwoFactorCode').val(twoFactorCode);
                        jQuery('#withdrawForm').submit();
                    });

                } else {
                    // If 2FA is not enabled, directly submit the form
                    jQuery('#withdrawForm').submit();
                }
            });


            // Corrected change event for .select-liveaccount
            jQuery('.select-liveaccount').on('change', function () {
                const selectedValue = this.value;
                document.getElementById('selectedLiveAccount').value = selectedValue;
            });

            // Optionally: Set default if one is preselected
            const checkedRadio = document.querySelector('.select-liveaccount:checked');
            if (checkedRadio) {
                document.getElementById('selectedLiveAccount').value = checkedRadio.value;
            }
        });
    </script>

@endsection
