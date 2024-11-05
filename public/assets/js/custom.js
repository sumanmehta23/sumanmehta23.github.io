function theme_change(value) {
  if (value == 'true') {
    document.getElementsByTagName("body")[0].setAttribute("data-pc-theme", 'light');
  } else {
    document.getElementsByTagName("body")[0].setAttribute("data-pc-theme", 'dark');
  }
}
$(document).ready(function () {
  $('#deposit_amount').on('input', function () {
    $('#amount_deposit').val($(this).val());
  });
  $('.tradedeposit_amount').on('input', function () {
    $('.tradedeposit_amount').val($(this).val());
  });
  $('.select-liveaccount').change(function () {
    var tradeID = $(this).val();
    var minDep = $(this).data('mindep');
    $('#deposit_amount').attr('min', minDep);
    $('.tradedeposit_amount').attr('min', minDep);
    $('.user_trade_id').val(tradeID);
  });
  $('.address-check').click(function () {
    $(this).find('input[type="radio"]').prop('checked', true);
    $(this).find('input[type="radio"]').trigger('change');
  });
  $('.address-check:first').click();
  $('.trade-deposit-type').click(function () {
    $(this).find('input[type="radio"]').prop('checked', true);
    $(this).find('input[type="radio"]').trigger('change');
  });
  setTimeout(function () {
    $('.trade-deposit-type:first').click();
  }, 1000);
  $('.wallet-payment').change(function () {
    $('.wallet-amount').val('');
    $('.wallet-amount-usd').val('');
    let type = $(this).data('type');
    $('.wallet-deposit-details').hide();
    $('.' + type).show();
    $('.deposit_type').val(type);
  });
  $('.wallet-withdraw').change(function () {
    let type = $(this).data('type');
    $('.wallet-withdrawal').hide();
    $('.withdraw-type').val(type);
    $('.' + type).show();
  });
  $('#currencyType').change(function () {
    let type = $(this).val();
    $('.currency-type').html(type);
  });
  $('.wallet-amount').on('input', function () {
    $(this).closest('.wallet-deposit-details').find('.wallet-amount-usd').val($(this).val());
  });
  $('.tradefund-deposit').change(function () {
    let deposittype = $(this).val();
    let type = $(this).data('type');
    $('.tradedeposit_amount').val('');
    $('.trade-deposit-details').hide();
    $('.' + type).show();
    $('.tradedeposittype').val(deposittype);
  });  
});
