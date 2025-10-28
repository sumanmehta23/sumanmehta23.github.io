 <div class="card custom-card">
     <div class="card-header">
         <div class="card-title">Payment Gateways </div>
     </div>
     <div class="card-body p-4">
         <form method="POST" action="{{ route('admin.payment-gateways.update') }}">
             @csrf
             <!-- CryptoChill Gateway -->
             <div class="card border-0 bg-light mb-4">
                 <div class="card-body p-3">
                     <div class="row align-items-center">
                         <div class="col-md-8">
                             <div class="d-flex align-items-center">
                                 <div class="me-3">
                                     <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center"
                                         style="width: 50px; height: 50px;">
                                         <i class="fab fa-bitcoin fs-4"></i>
                                     </div>
                                 </div>
                                 <div>
                                     <h5 class="mb-1 fw-bold">CryptoChill</h5>
                                     <p class="text-muted mb-0 small">Accept cryptocurrency payments with secure
                                         blockchain processing</p>
                                 </div>
                             </div>
                         </div>
                         <div class="col-md-4 text-md-end mt-3 mt-md-0">
                             <div class="d-flex align-items-center justify-content-md-end gap-3">
                                 <span class="badge bg-secondary" id="cryptochill-status">Inactive</span>
                                 <div class="form-check form-switch">
                                     <input class="form-check-input fs-5" type="checkbox" id="enableCryptoChill"
                                         name="enable_cryptochill" value="1"
                                         {{ isset($settings['enable_cryptochill']) && $settings['enable_cryptochill'] == '1' ? 'checked' : '' }}
                                         onchange="updateStatus('cryptochill', this.checked)">
                                     <label class="form-check-label visually-hidden" for="enableCryptoChill">Enable
                                         CryptoChill</label>
                                 </div>
                             </div>
                         </div>
                     </div>
                 </div>
             </div>

             <!-- Credit Card Gateway -->
             <div class="card border-0 bg-light mb-4">
                 <div class="card-body p-3">
                     <div class="row align-items-center">
                         <div class="col-md-8">
                             <div class="d-flex align-items-center">
                                 <div class="me-3">
                                     <div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center"
                                         style="width: 50px; height: 50px;">
                                         <i class="fas fa-credit-card fs-4"></i>
                                     </div>
                                 </div>
                                 <div>
                                     <h5 class="mb-1 fw-bold">Credit Card</h5>
                                     <p class="text-muted mb-0 small">Process Visa, Mastercard, and other major credit
                                         card payments</p>
                                     <small class="text-info" id="credit-card-auto-note" style="display: none;">
                                         <i class="fas fa-info-circle"></i> Automatically managed based on providers below
                                     </small>
                                 </div>
                             </div>
                         </div>
                         <div class="col-md-4 text-md-end mt-3 mt-md-0">
                             <div class="d-flex align-items-center justify-content-md-end gap-3">
                                 <span class="badge bg-success" id="creditcard-status">Active</span>
                                 <div class="form-check form-switch">
                                     <input class="form-check-input fs-5" type="checkbox" id="enableCreditCard"
                                         name="enable_credit" value="1"
                                         {{ isset($settings['enable_credit']) && $settings['enable_credit'] == '1' ? 'checked' : '' }}
                                         onchange="handleCreditCardToggle(this.checked);">
                                     <label class="form-check-label visually-hidden"
                                         for="enableCreditCard">Enable Credit Card</label>
                                 </div>
                             </div>
                         </div>
                     </div>

                     <!-- Credit Card Options (Sub-options) -->
                     <div id="credit-card-options" class="mt-4" style="display: none;">
                         <div class="row g-3">
                             <div class="col-12">
                                 <h6 class="text-muted mb-3">Credit Card Providers</h6>
                             </div>
                             
                             <!-- Payissa Option -->
                             <div class="col-md-6">
                                 <div class="card border-1 border-secondary">
                                     <div class="card-body p-2">
                                         <div class="d-flex align-items-center justify-content-between">
                                             <div class="d-flex align-items-center">
                                                 <div class="me-1">
                                                     <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center"
                                                         style="width: 40px; height: 40px;">
                                                         <i class="fas fa-credit-card fs-6"></i>
                                                     </div>
                                                 </div>
                                                 <div>
                                                     <h6 class="mb-0 fw-bold">Payissa</h6>
                                                     <small class="text-muted">CC processor</small>
                                                 </div>
                                             </div>
                                             <div class="d-flex align-items-center gap-2">
                                                 <span class="badge bg-secondary" id="payissa-status">Inactive</span>
                                                 <div class="form-check form-switch">
                                                     <input class="form-check-input" type="checkbox" id="enablePayissa"
                                                         name="enable_creditcardpayissa" value="1"
                                                         {{ isset($settings['enable_creditcardpayissa']) && $settings['enable_creditcardpayissa'] == '1' ? 'checked' : '' }}
                                                         onchange="updateStatus('payissa', this.checked); checkCreditCardSubOptions();">
                                                     <label class="form-check-label visually-hidden" for="enablePayissa">Enable Payissa</label>
                                                 </div>
                                             </div>
                                         </div>
                                     </div>
                                 </div>
                             </div>

                             <!-- Ragapay Option -->
                             <div class="col-md-6">
                                 <div class="card border-1 border-secondary">
                                     <div class="card-body p-2">
                                         <div class="d-flex align-items-center justify-content-between">
                                             <div class="d-flex align-items-center">
                                                 <div class="me-1">
                                                     <div class="bg-info text-white rounded-circle d-flex align-items-center justify-content-center"
                                                         style="width: 40px; height: 40px;">
                                                         <i class="fas fa-wallet fs-6"></i>
                                                     </div>
                                                 </div>
                                                 <div>
                                                     <h6 class="mb-0 fw-bold">Ragapay</h6>
                                                     <small class="text-muted">Payment gateway</small>
                                                 </div>
                                             </div>
                                             <div class="d-flex align-items-center gap-2">
                                                 <span class="badge bg-secondary" id="ragapay-status">Inactive</span>
                                                 <div class="form-check form-switch">
                                                     <input class="form-check-input" type="checkbox" id="enableRagaPay"
                                                         name="enable_ragapay" value="1"
                                                         {{ isset($settings['enable_ragapay']) && $settings['enable_ragapay'] == '1' ? 'checked' : '' }}
                                                         onchange="updateStatus('ragapay', this.checked); checkCreditCardSubOptions();">
                                                     <label class="form-check-label visually-hidden" for="enableRagaPay">Enable Ragapay</label>
                                                 </div>
                                             </div>
                                         </div>
                                     </div>
                                 </div>
                             </div>
                         </div>
                     </div>
                 </div>
            </div>



            <hr class="my-4">
            <div class="d-flex justify-content-end">
                <button type="submit" class="btn btn-primary btn-sm">
                    <i class="fas fa-save me-2"></i>
                    Save Settings
                </button>
            </div>
        </form>
     </div>
 </div>
 <script>
     function updateStatus(gateway, isEnabled) {
         const statusBadge = document.getElementById(gateway + '-status');

         if (isEnabled) {
             statusBadge.textContent = 'Active';
             statusBadge.className = 'badge bg-success';
         } else {
             statusBadge.textContent = 'Inactive';
             statusBadge.className = 'badge bg-secondary';
         }
     }

     function updateCreditCardOptions(isEnabled) {
         const creditCardOptions = document.getElementById('credit-card-options');
         
         if (isEnabled) {
             creditCardOptions.style.display = 'block';
         } else {
             creditCardOptions.style.display = 'none';
             // When disabling credit card, also disable sub-options
             const payissaCheckbox = document.getElementById('enablePayissa');
             const ragapayCheckbox = document.getElementById('enableRagaPay');
             
             if (payissaCheckbox && payissaCheckbox.checked) {
                 payissaCheckbox.checked = false;
                 updateStatus('payissa', false);
             }
             if (ragapayCheckbox && ragapayCheckbox.checked) {
                 ragapayCheckbox.checked = false;
                 updateStatus('ragapay', false);
             }
         }
     }

     function handleCreditCardToggle(isEnabled) {
         updateStatus('creditcard', isEnabled);
         updateCreditCardOptions(isEnabled);
         
         const autoNote = document.getElementById('credit-card-auto-note');
         
         if (!isEnabled) {
             // User is trying to disable Credit Card - this will disable sub-options
             updateCreditCardOptions(false);
             if (autoNote) autoNote.style.display = 'none';
         } else {
             // User is enabling Credit Card - show the options
             updateCreditCardOptions(true);
             if (autoNote) autoNote.style.display = 'none';
         }
     }

     function checkCreditCardSubOptions() {
         const payissaCheckbox = document.getElementById('enablePayissa');
         const ragapayCheckbox = document.getElementById('enableRagaPay');
         const creditCardCheckbox = document.getElementById('enableCreditCard');
         const autoNote = document.getElementById('credit-card-auto-note');
         
         const hasActiveSubOption = (payissaCheckbox && payissaCheckbox.checked) || 
                                   (ragapayCheckbox && ragapayCheckbox.checked);
         
         if (!hasActiveSubOption && creditCardCheckbox && creditCardCheckbox.checked) {
             // If no sub-options are active, automatically disable the main Credit Card option
             creditCardCheckbox.checked = false;
             updateStatus('creditcard', false);
             updateCreditCardOptions(false);
             if (autoNote) autoNote.style.display = 'block';
         } else if (hasActiveSubOption && creditCardCheckbox && !creditCardCheckbox.checked) {
             // If sub-options are active but main option is off, enable the main option
             creditCardCheckbox.checked = true;
             updateStatus('creditcard', true);
             updateCreditCardOptions(true);
             if (autoNote) autoNote.style.display = 'block';
         }
     }

     function initializeCreditCardState() {
         const payissaCheckbox = document.getElementById('enablePayissa');
         const ragapayCheckbox = document.getElementById('enableRagaPay');
         const creditCardCheckbox = document.getElementById('enableCreditCard');
         const autoNote = document.getElementById('credit-card-auto-note');
         
         const hasActiveSubOption = (payissaCheckbox && payissaCheckbox.checked) || 
                                   (ragapayCheckbox && ragapayCheckbox.checked);
         
         // Set the correct initial state for Credit Card main toggle
         if (hasActiveSubOption) {
             if (!creditCardCheckbox.checked) {
                 creditCardCheckbox.checked = true;
                 updateStatus('creditcard', true);
                 if (autoNote) autoNote.style.display = 'block';
             }
             updateCreditCardOptions(true);
         } else {
             if (creditCardCheckbox.checked) {
                 creditCardCheckbox.checked = false;
                 updateStatus('creditcard', false);
                 if (autoNote) autoNote.style.display = 'block';
             }
             updateCreditCardOptions(false);
         }
     }

    // Initialize status on page load
    document.addEventListener('DOMContentLoaded', function() {
        const cryptochillCheckbox = document.getElementById('enableCryptoChill');
        const payissaCheckbox = document.getElementById('enablePayissa');
        const ragapayCheckbox = document.getElementById('enableRagaPay');

        // Initialize individual statuses
        if (cryptochillCheckbox) updateStatus('cryptochill', cryptochillCheckbox.checked);
        if (payissaCheckbox) updateStatus('payissa', payissaCheckbox.checked);
        if (ragapayCheckbox) updateStatus('ragapay', ragapayCheckbox.checked);
        
        // Initialize credit card state based on sub-options
        initializeCreditCardState();

        // Handle form submission to ensure proper values
        const form = document.querySelector('form');
        if (form) {
            form.addEventListener('submit', function(e) {
                // Ensure credit card state is correct before submission
                checkCreditCardSubOptions();
            });
        }
    });
</script>
