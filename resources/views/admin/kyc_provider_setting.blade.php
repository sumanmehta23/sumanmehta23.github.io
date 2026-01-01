<div class="card custom-card">
    <div class="card-header bg-secondary bg-gradient">
        <div class="card-title">KYC Provider</div>
    </div>
    <div class="card-body p-4">
        <form method="POST" action="{{ route('admin.kyc-provider.update') }}" id="kycProviderForm">
            @csrf
            <input type="hidden" name="kyc_provider" id="kycProviderInput"
                value="{{ $settings['kyc_provider'] ?? 'sumsub' }}">

            <div class="card border-0 bg-light mb-4 shadow-sm">
                <div class="card-body p-3">
                    <div class="row">
                        <div>
                            <div class="d-flex align-items-center mb-3">
                                <div class="me-3">
                                    <div class="bg-secondary text-white rounded-circle d-flex align-items-center justify-content-center"
                                         style="width: 50px; height: 50px;">
                                        <i class="ti ti-shield-check fs-4"></i>
                                    </div>
                                </div>
                                <div>
                                    <h5 class="mb-1 fw-bold">KYC Verification Provider</h5>
                                    <p class="text-muted mb-0 small">Select which provider is used for client KYC flows</p>
                                </div>
                            </div>

                            <div class="badge mb-3 p-2" id="kyc-provider-status"></div>

                            <div class="form-check custom-option pt-4 mb-3">
                                <input class="form-check-input" type="radio" name="kycProviderToggle" id="kyc_sumsub"
                                       value="sumsub"
                                       onchange="updateKycProviderStatus(this.value)"
                                       {{ ($settings['kyc_provider'] ?? 'sumsub') === 'sumsub' ? 'checked' : '' }}>
                                <label class="form-check-label" for="kyc_sumsub">
                                    <span class="d-block fw-semibold mb-1">Sumsub</span>
                                    <small class="text-muted">Current default provider with full integration</small>
                                </label>
                            </div>

                            <div class="form-check custom-option">
                                <input class="form-check-input" type="radio" name="kycProviderToggle" id="kyc_veriff"
                                       value="veriff"
                                       onchange="updateKycProviderStatus(this.value)"
                                       {{ ($settings['kyc_provider'] ?? 'sumsub') === 'veriff' ? 'checked' : '' }}>
                                <label class="form-check-label" for="kyc_veriff">
                                    <span class="d-block fw-semibold mb-1">Veriff</span>
                                    <small class="text-muted">Alternative KYC provider using Veriff Web SDK</small>
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
    function updateKycProviderStatus(value) {
        const statusBadge = document.getElementById('kyc-provider-status');
        const kycProviderInput = document.getElementById('kycProviderInput');

        if (!statusBadge || !kycProviderInput) return;

        if (value === 'veriff') {
            statusBadge.innerHTML = 'Veriff Selected';
            statusBadge.className = 'badge bg-info text-white d-inline-flex align-items-center';
            kycProviderInput.value = 'veriff';
        } else {
            statusBadge.innerHTML = 'Sumsub Selected';
            statusBadge.className = 'badge bg-success text-white d-inline-flex align-items-center';
            kycProviderInput.value = 'sumsub';
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        const checkedRadio = document.querySelector('input[name="kycProviderToggle"]:checked');
        if (checkedRadio) {
            updateKycProviderStatus(checkedRadio.value);
        } else {
            updateKycProviderStatus('sumsub');
        }
    });
</script>


