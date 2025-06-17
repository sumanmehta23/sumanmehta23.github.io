<div class="card custom-card">
    <div class="card-header">
        <div class="card-title">Toggle Group Code</div>
    </div>
    <div class="card-body p-4">
        <form method="POST" action="{{ route('admin.toggle_group_code.update') }}" id="groupToggleForm">
            @csrf
            <input type="hidden" name="group_code" id="groupCodeInput" value="{{ isset($settings['enable_group']) && $settings['enable_group'] == '1' ? 'b_book' : 'a_book' }}">
            <div class="card border-0 bg-light mb-4">
                <div class="card-body p-3">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <div class="d-flex align-items-center">
                                <div class="me-3">
                                    <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                        <i class="fab fa-bitcoin fs-4"></i>
                                    </div>
                                </div>
                                <div>
                                    <h5 class="mb-1 fw-bold">Group Codes</h5>
                                    <p class="text-muted mb-0 small">Toggle clients group code between A-Book and B-Book</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 text-md-end mt-3 mt-md-0">
                            <div class="d-flex align-items-center justify-content-md-end gap-4">
                                <div class="badge" style="margin-top: 4px" id="group-status"></div>
                                <div class="form-check form-switch">
                                    <input class="form-check-input fs-5" type="checkbox" id="enableGroup"
                                        {{ isset($settings['enable_group']) && $settings['enable_group'] == '1' ? 'checked' : '' }}
                                        onchange="updateStatus(this.checked)">
                                    <label class="form-check-label visually-hidden" for="enableGroup">Enable Group</label>
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
    function updateStatus(isEnabled) {
        const statusBadge = document.getElementById('group-status');
        const groupCodeInput = document.getElementById('groupCodeInput');

        if (isEnabled) {
            statusBadge.textContent = 'B-Book';
            statusBadge.className = 'badge bg-success';
            groupCodeInput.value = 'b_book';
        } else {
            statusBadge.textContent = 'A-Book';
            statusBadge.className = 'badge bg-secondary';
            groupCodeInput.value = 'a_book';
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        const groupCheckbox = document.getElementById('enableGroup');
        updateStatus(groupCheckbox.checked);
    });
</script>
