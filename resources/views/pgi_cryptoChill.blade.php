<script type="text/javascript" src="https://static.cryptochill.com/static/js/sdk2.js"></script>
<script type="text/javascript">
  window.isCalled = 0;
  localStorage.setItem('isCalled', 'false');
//   $(document).ready(function() {
//     $("#crypto_deposit_amount").val("10");
//   });
var customerID = "{{auth()->user()->id}}";
  var customerEmail = "{{auth()->user()->email}}";
  var depositTo=  'wallet';
  // CC Payment gateway options
  $("#ccpay").attr("disabled", "true");
  $("#deposit_amount_cc").on('change keypress keydown keyup', function() {
    if ($(this).val() >= 10) {
      $("#ccpay").attr("disabled", false);
    } else {
      $("#ccpay").attr("disabled", "true");
    }
    $("#ccpay").attr("data-amount", $(this).val());
  });
  // CC Payment gateway options Ends
  $("#paynow").attr("disabled", "true");
//   $("#crypto_deposit_amount").on('change keypress keydown keyup', function() {
//     if ($(this).val() >= 10) {
//       $("#paynow").attr("disabled", false);
//     } else {
//       $("#paynow").attr("disabled", "true");
//     }
//     $("#paynow").attr("data-amount", $(this).val());
//   });
$("#paynow").on("click", function (event) {
    var checkboxChecked = $("#cryptoWarningCheckbox").prop("checked");

    if (!checkboxChecked) {
        event.preventDefault(); // Prevent form submission
        alert("You must check the confirmation box before proceeding.");
    }
});
$("#crypto_deposit_amount, #cryptoWarningCheckbox").on('change keypress keydown keyup', function() {
    var amountValid = $("#crypto_deposit_amount").val() >= 10;
    var checkboxChecked = $("#cryptoWarningCheckbox").prop("checked");

    if (amountValid && checkboxChecked) {
        $("#paynow").attr("disabled", false);
    } else {
        $("#paynow").attr("disabled", "true");
    }

    $("#paynow").attr("data-amount", $("#crypto_deposit_amount").val());
});


  function onPaymentSuccess(data, code) {
    if(localStorage.getItem('isCalled') == 'true'){
      return true;
    }
    localStorage.setItem('isCalled', 'true');
    var code = $('[name="code"]').val();
    var amount = $("#crypto_deposit_amount").val();
    $.ajax({
      url: "{{ route('wallet_payment') }}",
      type: "POST",
      data: {
        paymentGateway: "true",
        deposit_to: "Account",
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
        console.log("onPaymentSuccess",data);
        console.log("onPaymentSuccess status ",data.status);
        if (data.status === true) {
          window.isCalled = 1;
          swal.fire({
            icon: "success",
            title: "Payment Successful",
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
  function onWalletPaymentSuccess(data, code) {
    // console.log(code, data)
    // console.log("Starts");

  }
  function onWalletPaymentIncomplete(data, code) {
    console.log("onPaymentIncomplete");
    console.log(code, data);
    if(typeof data.payment.id != 'undefined'){
      console.log("Incomplete payment..!",data.payment.id );
    }


    // // if(localStorage.getItem('isCalled') == 'true'){
    // //   return true;
    // // }
    // // localStorage.setItem('isCalled', 'true');

    // // var code = $('[name="code"]').val();
    // var amoutnToDeposit = $("#crypto_deposit_amount").val();
    // // alert("Triggered");
    // $.ajax({
    //   url: "/ajax/post",
    //   type: "POST",
    //   data: {
    //     paymentGatewayIncomplete: "true",
    //     _deposit_to: "wallet",
    //    _code: code,
    //     _data: data,
    //     _time: <?= time() ?><?= rand(1111111111,99999999999) ?>,
    //     _amount: amoutnToDeposit,
    //     _deposit_type: "CryptoChill"
    //   },
    //   beforeSend: function() {


    //   },
    //   success: function(data) {
    //     // console.log("data==> ", data);

    //   }
    // });
  }
  function onWalletPaymentUpdate(data, code){

    if(typeof data != 'undefined'){
      console.log("onPaymentUpdate");
    console.log(code, data);

    if(typeof data.payment.status == 'undefined'){
      console.log("Incomplete payment..!",data.payment.id );
      return;
    }

    if(data.payment.status != 'paid'){
      PaymentIntiated=true;
      console.log("Payment processing..!",data.payment.is_expired );
      return;
    }

    if(localStorage.getItem('isCalled') == 'true'){
      return true;
    }
    localStorage.setItem('isCalled', 'true');


    var code = $('[name="code"]').val();
    var amount = $("#crypto_deposit_amount").val();
    // alert("Triggered");
    $.ajax({
      url: "{{ route('wallet_payment') }}",
      type: "POST",
      data: {
        paymentGateway: "true",
        deposit_to: "Account",
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
        console.log(" onWalletPaymentUpdate data==> ", data);

        if (data.status === true) {
          window.isCalled = 1;
          swal.fire({
            icon: "success",
            title: "Payment Successful",
            allowEscapeKey: false,
            allowOutsideClick: false,
            showCancelButton: false,
            showConfirmButton: true
          }).then((val) => {
            if (val.isConfirmed) {
              location.href = location.href;
            }
          });
        } else {
          swal.fire({
            icon: "error",
            title: "Error: " + data,
            text: "Please try again later or contact support.",
            allowEscapeKey: false,
            allowOutsideClick: false,
            showCancelButton: false,
            showConfirmButton: true
          }).then((val) => {
            if (val.isConfirmed) {
              location.href = location.href;
            }
          });
        }
      }
    });
  }
  }

  function onWalletPaymentCancel(data, code) {
    var message='ser Side Interruption';
    if(PaymentIntiated){
      message="Once payment is confirmed it will be reflected on your wallet";

      swal.fire({
        icon: "info",
        allowEscapeKey: false,
        allowOutsideClick: false,
        showConfirmButton: true,
        title: "",
        text: "Once payment is confirmed it will be reflected on your wallet"
      }).then((val) => {
        if (val.isConfirmed) {
          location.href = location.href;
        }
      });
    }
  }
  CryptoChill.setup({
    account: '{{config('services.cryptochill.accountid')}}',
    profile: '{{config('services.cryptochill.profileid')}}',
    // Event callbacks
    // onOpen: onPaymentSuccess,
    // onUpdate: onPaymentUpdate,
    onUpdate: onWalletPaymentUpdate,
    onSuccess: onWalletPaymentSuccess,
    passthrough: JSON.stringify({'customerID': customerID,'customerEmail':customerEmail,'depositTo':depositTo}),
    // onIncomplete: onPaymentIncomplete,

    onCancel: onWalletPaymentCancel
  })
</script>
