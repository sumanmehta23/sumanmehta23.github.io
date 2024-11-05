<script type="text/javascript" src="https://static.cryptochill.com/static/js/sdk2.js"></script>
<script type="text/javascript">
  window.isCalled = 0;
  localStorage.setItem('isCalled', 'false');
  $(document).ready(function() {
    $("#crypto_deposit_amount").val("10");
  });
  $("#paynow").attr("disabled", "true");
  $("#crypto_deposit_amount").on('change keypress keydown keyup', function() {
    if ($(this).val() >= 10) {
      $("#paynow").attr("disabled", false);
    } else {
      $("#paynow").attr("disabled", "true");
    }
    $("#paynow").attr("data-amount", $(this).val());
  });

  function onPaymentSuccess(data, code) {
    if(localStorage.getItem('isCalled') == 'true'){
      return true;
    }
    localStorage.setItem('isCalled', 'true');
    var trade_id = $('[name="trade_id"]').val();
    var amount = $("#crypto_deposit_amount").val();
    $.ajax({
      url: "{{ route('wallet_payment') }}",
      type: "POST",
      data: {
        paymentGateway: "true",
        deposit_to: "wallet",
        code: code,
        data: data,
        time: <?= time() ?><?= rand(1111111111,99999999999) ?>,
        amount: amount,
        deposit_type: "CryptoChill"
      },
      beforeSend: function() {
        swal.fire({
          showConfirmButton: false,
          showCancelButton: false,
          allowEscapeKey: false,
          allowOutsideClick: false,
          didOpen: function() {
            swal.showLoading();
          }
        });
      },
      success: function(data) {
        console.log(data);
        console.log(data.status);
        if (data.status === true) {
          window.isCalled = 1;
          swal.fire({
            icon: "success",
            title: "Payment Successfully Done.",
            allowEscapeKey: false,
            allowOutsideClick: false,
            showCancelButton: false
          }).then((val) => {
            if (val.isConfirmed) {
              location.href = location.href;
            }
          });
        } else {
          swal.fire({
            icon: "error",
            title: "Error: " + data.message,
            text: "Please try again later or contact support.",
            allowEscapeKey: false,
            allowOutsideClick: false,
            showCancelButton: false
          }).then((val) => {
            if (val.isConfirmed) {
              location.href = location.href;
            }
          });
        }
      }
    });
  }

  function onPaymentCancel(data, code) {
    swal.fire({
      icon: "info",
      allowEscapeKey: false,
      allowOutsideClick: false,
      title: "Payment Cancelled",
      text: "User Side Interruption"
    }).then((val) => {
      if (val.isConfirmed) {
        location.href = location.href;
      }
    })
  }

  CryptoChill.setup({
    account: 'bc38bb94-e7da-4b56-a07a-cfe3f06bab03',
    profile: 'f759196c-cf55-4618-b277-9f311ff3efcb',
    // Event callbacks
    // onOpen: onPaymentSuccess,
    // onUpdate: onPaymentUpdate,
    onSuccess: onPaymentSuccess,
    // onIncomplete: onPaymentIncomplete,
    onCancel: onPaymentCancel
  })
</script>
