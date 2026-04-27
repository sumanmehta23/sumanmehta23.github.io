@extends('layouts.admin.admin')
@section('content')
<div class="main-content app-content">
    <div class="container-fluid">

        <!-- PAGE-HEADER -->
        <div class="page-header">
            <h1 class="page-title">Update Password</h1>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="javascript:void(0);">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">Update Passowrd</li>
            </ol>
        </div>
        <!-- PAGE-HEADER END -->

        <!-- ROW-1 OPEN -->
        <div class="row">
            <div class="col-12">
                <form method="post" action="{{route('admin.update_password.store')}}">
                    @csrf
                    <div class="card">
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label">Old Password</label>
                                <div class="input-group">
                                    <input type="password" class="form-control" name="oldpassword" id="oldpassword" required>
                                    <span class="cursor-pointer input-group-text togglePassword" id="toggleOldPassword">
                                        <i class="fa fa-eye-slash"></i>
                                    </span>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">New Password</label>
                                <div class="input-group">
                                    <input type="password" class="form-control" name="newpassword" id="updatePasswordPassword" required>
                                    <span class="cursor-pointer input-group-text togglePassword" id="toggleUpdatePasswordPassword">
                                        <i class="fa fa-eye-slash"></i>
                                    </span>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Confirm Password</label>
                                <div class="input-group">
                                    <input type="password" class="form-control" name="newpassword_confirmation" id="updatePasswordConfirmPassword" required>
                                    <span class="cursor-pointer input-group-text togglePassword" id="toggleUpdatePasswordConfirmPassword">
                                        <i class="fa fa-eye-slash"></i>
                                    </span>
                                </div>
                            </div>
                            <div class="col-12">
                                @include('partials.password-validation-rules-admin', ['prefix' => 'update-password-'])
                            </div>
                        </div>
                        <div class="card-footer text-end">
                            <input type="submit" class="btn btn-primary" value="Update" name="change_password" id="updatePasswordSubmitBtn">
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@include('partials.password-validation-script')
<script>
    $(document).ready(function() {
        // Update Password Password Validation
        const updatePasswordPassword = document.getElementById('updatePasswordPassword');
        const updatePasswordConfirmPassword = document.getElementById('updatePasswordConfirmPassword');
        const toggleUpdatePasswordPassword = document.getElementById('toggleUpdatePasswordPassword');
        const toggleUpdatePasswordConfirmPassword = document.getElementById('toggleUpdatePasswordConfirmPassword');
        const updatePasswordSubmitBtn = document.getElementById('updatePasswordSubmitBtn');

        if (updatePasswordPassword && updatePasswordConfirmPassword) {
            // Initialize all rules to false
            window.updateRuleUI('update-password-rule-length', false);
            window.updateRuleUI('update-password-rule-uppercase', false);
            window.updateRuleUI('update-password-rule-lowercase', false);
            window.updateRuleUI('update-password-rule-digit', false);
            window.updateRuleUI('update-password-rule-special', false);
            window.updateRuleUI('update-password-rule-no-spaces', false);
            window.updateRuleUI('update-password-rule-match', false);

            const handleUpdatePasswordInput = () => {
                const password = updatePasswordPassword.value;
                const confirmPassword = updatePasswordConfirmPassword.value;

                if (!password) {
                    window.updateRuleUI('update-password-rule-length', false);
                    window.updateRuleUI('update-password-rule-uppercase', false);
                    window.updateRuleUI('update-password-rule-lowercase', false);
                    window.updateRuleUI('update-password-rule-digit', false);
                    window.updateRuleUI('update-password-rule-special', false);
                    window.updateRuleUI('update-password-rule-no-spaces', false);
                    window.updateRuleUI('update-password-rule-match', false);
                } else {
                    const rules = window.checkPasswordRules(password, confirmPassword);
                    window.updateRuleUI('update-password-rule-length', rules.length);
                    window.updateRuleUI('update-password-rule-uppercase', rules.uppercase);
                    window.updateRuleUI('update-password-rule-lowercase', rules.lowercase);
                    window.updateRuleUI('update-password-rule-digit', rules.digit);
                    window.updateRuleUI('update-password-rule-special', rules.special);
                    window.updateRuleUI('update-password-rule-no-spaces', rules.noSpaces);
                    window.updateRuleUI('update-password-rule-match', confirmPassword ? rules.match : null);
                }
                window.checkAllRulesSatisfied('updatePasswordPassword', 'updatePasswordConfirmPassword', 'updatePasswordSubmitBtn');
            };

            const handleUpdatePasswordConfirmInput = () => {
                const password = updatePasswordPassword.value;
                const confirmPassword = updatePasswordConfirmPassword.value;

                if (!confirmPassword) {
                    window.updateRuleUI('update-password-rule-match', false);
                } else {
                    const rules = window.checkPasswordRules(password, confirmPassword);
                    window.updateRuleUI('update-password-rule-match', confirmPassword ? rules.match : null);
                }
                window.checkAllRulesSatisfied('updatePasswordPassword', 'updatePasswordConfirmPassword', 'updatePasswordSubmitBtn');
            };

            updatePasswordPassword.addEventListener('input', handleUpdatePasswordInput);
            updatePasswordConfirmPassword.addEventListener('input', handleUpdatePasswordConfirmInput);
        }

        // Password Visibility Toggles for Update Password
        if (toggleUpdatePasswordPassword && updatePasswordPassword) {
            toggleUpdatePasswordPassword.addEventListener('click', (e) => {
                e.preventDefault();
                const type = updatePasswordPassword.type === 'password' ? 'text' : 'password';
                updatePasswordPassword.type = type;
                toggleUpdatePasswordPassword.innerHTML = type === 'password' ? '<i class="fa fa-eye-slash"></i>' : '<i class="fa fa-eye"></i>';
            });
        }

        if (toggleUpdatePasswordConfirmPassword && updatePasswordConfirmPassword) {
            toggleUpdatePasswordConfirmPassword.addEventListener('click', (e) => {
                e.preventDefault();
                const type = updatePasswordConfirmPassword.type === 'password' ? 'text' : 'password';
                updatePasswordConfirmPassword.type = type;
                toggleUpdatePasswordConfirmPassword.innerHTML = type === 'password' ? '<i class="fa fa-eye-slash"></i>' : '<i class="fa fa-eye"></i>';
            });
        }

        // Old Password Visibility Toggle
        const oldPassword = document.getElementById('oldpassword');
        const toggleOldPassword = document.getElementById('toggleOldPassword');
        if (toggleOldPassword && oldPassword) {
            toggleOldPassword.addEventListener('click', (e) => {
                e.preventDefault();
                const type = oldPassword.type === 'password' ? 'text' : 'password';
                oldPassword.type = type;
                toggleOldPassword.innerHTML = type === 'password' ? '<i class="fa fa-eye-slash"></i>' : '<i class="fa fa-eye"></i>';
            });
        }
    });
</script>
@endSection
