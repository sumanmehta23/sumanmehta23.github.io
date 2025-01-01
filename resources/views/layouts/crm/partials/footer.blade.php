</div>
<div class="pc-content">
    <p class="m-0 text-center w-100" style="font-size: 11px; padding-top: 90px; padding-bottom: 20px; color:#2ca192">LQH Integrated Ltd
        <a target="_blank" href="https://www.lqhmarkets.com/risk-disclaimer">Risk Disclaimer</a> |
        <a target="_blank" href="https://www.lqhmarkets.com/terms-conditions">Terms & Conditions</a> |
        <a target="_blank" href="https://www.lqhmarkets.com/privacy-policy">Privacy Policy</a>
    </p>
</div>

<script>
    window.intercomSettings = {
      api_base: "https://api-iam.intercom.io",
      app_id: "hcaolnkq",
      user_id: "{{ auth()->user()->id }}", // IMPORTANT: Replace "user.id" with the variable you use to capture the user's ID
      name: "{{ auth()->user()->fullname }}", // IMPORTANT: Replace "user.name" with the variable you use to capture the user's name
      email:  "{{ auth()->user()->email }}", // IMPORTANT: Replace "user.email" with the variable you use to capture the user's email address
      created_at:  "{{ auth()->user()->created_at }}", // IMPORTANT: Replace "user.createdAt" with the variable you use to capture the user's sign-up date
    };
</script>



<script>
    // We pre-filled your app ID in the widget URL: 'https://widget.intercom.io/widget/hcaolnkq'
    (function(){var w=window;var ic=w.Intercom;if(typeof ic==="function"){ic('reattach_activator');ic('update',w.intercomSettings);}else{var d=document;var i=function(){i.c(arguments);};i.q=[];i.c=function(args){i.q.push(args);};w.Intercom=i;var l=function(){var s=d.createElement('script');s.type='text/javascript';s.async=true;s.src='https://widget.intercom.io/widget/hcaolnkq';var x=d.getElementsByTagName('script')[0];x.parentNode.insertBefore(s,x);};if(document.readyState==='complete'){l();}else if(w.attachEvent){w.attachEvent('onload',l);}else{w.addEventListener('load',l,false);}}})();
</script>

<div id="changeLeverage" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle"
    aria-hidden="true">
    <div class="modal-dialog modal-md modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLiveLabel">Edit Leverage</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" id="updateLeverageForm"  action="{{ route('update-leverage') }}">
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
                        <button type="button" class="btn btn-link-danger btn-pc-default" data-bs-dismiss="modal">Cancel</button>
                        <input class="btn btn-primary" type="submit" name="update_leverage" value="Update Leverage">
                    </div>
                </div>
                <input type="hidden" name="modalAccountId" id="modalAccountId">
                <input type="hidden" name="accountId" id="accountId">
            </form>
        </div>
    </div>
</div>
@if(session('success'))
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
                                    <option value="ETH_USDT">ERC20</option>
                                    <option value="USDT-TRX">TRC20</option>
                                </select>
                            </div>
                            <div class="form-group"><label class="form-label">Wallet Address</label><input
                                    type="text" class="form-control" name="wallet_address" required></div>
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
<script>
    $("#bankDetailsForm").submit(function(e) {
        e.preventDefault();
        $.ajax({
            url: "{{ route('wallet.store') }}",
            type: "POST",
            data: $(this).serialize(),
            beforeSend: function() {
                $("#bankDetailsForm input,#bankDetailsForm select").attr("disabled", "true");
            },
            success: function(data) {
                $("#bankDetailsForm input,#bankDetailsForm select").attr("disabled", "true");
                if (data.success == true) {
                    Swal.fire({
                        title: "Wallet Details Successfully Added",
                        icon: "success"
                    }).then((val) => {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        title: "Something went wrong",
                        icon: "error"
                    });
                }
            }
        });
    })
    $("#verify-user-kyc").click(function(e) {
        e.preventDefault();
        var iframe =
            "<iframe id='kyc_verification_frame' src='/sumsub' class='w-100' style='height: 100vh;'></iframe>";
        $(this).closest(".card-body").html(iframe);
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
            success: function(data) {
                // Clear existing options
                $("#leverage").html("");

                // Populate the select with new options
                $.each(data, function(key, value) {
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
});

</script>

</body>

</html>
