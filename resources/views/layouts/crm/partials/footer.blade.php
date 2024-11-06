</div>
<div class="pc-content">
    <p class="m-0 text-center w-100" style="font-size: 11px; padding-top: 90px; padding-bottom: 20px; color:#2ca192">LQH Integrated Ltd
        <a target="_blank" href="https://www.liquidityhouse.com/risk-disclaimer">Risk Disclaimer</a> |
        <a target="_blank" href="https://www.liquidityhouse.com/terms-conditions">Terms & Conditions</a> |
        <a target="_blank" href="https://www.liquidityhouse.com/privacy-policy">Privacy Policy</a>
    </p>
</div>
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
                            <div class="form-group"><label class="form-label">Select Your Cryptocurrency</label>
                                <select id="my-select" class="form-control" name="wallet_currency" required>
                                    <option value="USDT">USDT</option>
                                </select>
                            </div>
                            <div class="form-group"><label class="form-label">Wallet Network</label>
                                <select id="my-select" class="form-control" name="wallet_network" required>
                                    <option value="USDT-TRX">ERC20</option>
                                    <option value="USDT-TRX">TRC20</option>
                                </select>
                            </div>
                            <div class="form-group"><label class="form-label">Wallet Address</label><input
                                    type="text" class="form-control" name="wallet_address" required></div>
                            <div class="form-group"><label class="form-label">Status</label>
                                <select id="my-select" class="form-control" name="status" required>
                                    <option value="1">Active</option>
                                    <option value="0">In-Active</option>
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

</body>

</html>
