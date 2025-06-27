<div class="card custom-card">
    <div class="card-header">
        <div class="card-title">Default Group Code</div>
    </div>
    <div class="card-body p-4">
        <form method="POST" action="{{ route('admin.toggle_group_code.update') }}" id="groupToggleForm">
            @csrf
            <input type="hidden" name="group_code" id="groupCodeInput"
                value="{{ isset($settings['enable_group']) && $settings['enable_group'] == '1' ? 'B-Book' : 'A-Book' }}">

            <div class="card border-0 bg-light mb-4">
                <div class="card-body p-3">
                    <div class="row">
                        <div>
                            <div class="d-flex align-items-center mb-3">
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

                            <div class="badge mb-3" id="group-status"></div>

                            <div class="form-check pt-4 mb-2">
                                <input class="form-check-input" type="radio" name="groupToggle" id="aBook" value="A-Book"
                                    {{ isset($toggle) && $toggle->a_book == 1 ? 'checked' : '' }}
                                    onchange="updateStatus(this.value)">
                                <label class="form-check-label" for="aBook">A-Book</label>
                            </div>

                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="groupToggle" id="bBook" value="B-Book"
                                    {{ isset($toggle) && $toggle->b_book == 1 ? 'checked' : '' }}
                                    onchange="updateStatus(this.value)">
                                <label class="form-check-label" for="bBook">B-Book</label>
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
    function updateStatus(value) {
        const statusBadge = document.getElementById('group-status');
        const groupCodeInput = document.getElementById('groupCodeInput');

        if (value === 'B-Book') {
            statusBadge.textContent = 'B-Book';
            statusBadge.className = 'badge bg-success';
            groupCodeInput.value = 'B-Book';
        } else {
            statusBadge.textContent = 'A-Book';
            statusBadge.className = 'badge bg-secondary';
            groupCodeInput.value = 'A-Book';
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        const checkedRadio = document.querySelector('input[name="groupToggle"]:checked');
        if (checkedRadio) {
            updateStatus(checkedRadio.value);
        }
    });
</script>
