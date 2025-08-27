<div class="card custom-card">
    <div class="card-header bg-info bg-gradient">
        <div class="card-title ">IB Approval Configuration</div>
    </div>
    <div class="card-body p-4">
        <form method="POST" action="{{ route('admin.toggle_ib_approve_request') }}" id="ibApproveToggleForm">
            @csrf
            <input type="hidden" name="ibApprovalType" id="ibApprovalType"
                value="{{ isset($settings['ib_toggle_activation']) && $settings['ib_toggle_activation'] == 'manually' ? 'manually' : 'automatic' }}">
            <div class="card border-0 bg-light mb-4 shadow-sm">
                <div class="card-body p-3">
                    <div class="row">
                        <div>
                            <div class="d-flex align-items-center mb-3">
                                <div class="me-3">
                                    <div class="bg-info text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                        <i class="fab fa-bitcoin fs-4"></i>
                                    </div>
                                </div>
                                <div>
                                    <h5 class="mb-1 fw-bold">IB Request Processing</h5>
                                    <p class="text-muted mb-0 small">Configure how Introducing Broker requests are processed in the system.</p>
                                </div>
                            </div>

                            <div class="badge mb-3 p-2" id="ib-status"></div>

                            <div class="form-check custom-option pt-4 mb-3">
                                <input class="form-check-input" type="radio" name="ibRequestToggle" id="manually" value='manually'
                                    onchange="updateIbStatus(this.value)">
                                <label class="form-check-label" for="manually">
                                    <span class="d-block fw-semibold mb-1">Manual Approval</span>
                                    <small class="text-muted">Admin review required for each IB request</small>
                                </label>
                            </div>

                            <div class="form-check custom-option">
                                <input class="form-check-input" type="radio" name="ibRequestToggle" id="automatic" value='automatic'
                                    onchange="updateIbStatus(this.value)">
                                <label class="form-check-label" for="automatic">
                                    <span class="d-block fw-semibold mb-1">Automatic Processing</span>
                                    <small class="text-muted">Requests are processed automatically based on criteria</small>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <hr class="my-4">
            <div class="d-flex justify-content-end">
                <button type="submit" class="btn btn-primary btn-sm">
                    Save Settings
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function updateIbStatus(value) {
        const statusBadge = document.getElementById('ib-status');
        const ibApprovalType = document.getElementById('ibApprovalType');

        if (value === 'manually') {
            statusBadge.innerHTML = 'Manual Review';
            statusBadge.className = 'badge bg-info text-white d-inline-flex align-items-center';
            ibApprovalType.value = 'manually';
        } else {
            statusBadge.innerHTML = 'Auto-Process';
            statusBadge.className = 'badge bg-success text-white d-inline-flex align-items-center';
            ibApprovalType.value = 'automatic';
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        const checkedRadio = document.querySelector('input[name="ibRequestToggle"]:checked');
        if (checkedRadio) {
            updateIbStatus(checkedRadio.value);
        } else {
            // Set default state if none selected
            const defaultValue = document.getElementById('ibApprovalType').value.toLowerCase();
            const radio = document.getElementById(defaultValue);
            if (radio) {
                radio.checked = true;
                updateIbStatus(defaultValue);
            }
        }
    });
</script>
