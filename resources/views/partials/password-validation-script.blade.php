<script>
    // Global password validation functions
    window.checkPasswordRules = function (password, confirmPassword = '') {
        const rules = {
            length: password.length >= 8,
            uppercase: /[A-Z]/.test(password),
            lowercase: /[a-z]/.test(password),
            digit: /\d/.test(password),
            special: /[!@#$%^&*()\,.\-?":{}|<>]/.test(password),
            noSpaces: !/\s/.test(password),
            match: password && confirmPassword ? password === confirmPassword : null
        };
        return rules;
    };

    window.updateRuleUI = function (ruleId, isSatisfied) {
        const ruleElement = document.getElementById(ruleId);
        if (!ruleElement) return;
        const iconElement = ruleElement.querySelector('.rule-icon');

        if (isSatisfied === null) {
            // Reset state
            ruleElement.classList.remove('satisfied');
            iconElement.className = 'ti ti-x absolute start-0 top-0 text-danger rule-icon';
        } else if (isSatisfied) {
            // Satisfied - green checkmark
            ruleElement.classList.add('satisfied');
            iconElement.className = 'ti ti-check absolute start-0 top-0 text-success rule-icon';
        } else {
            // Not satisfied - red X
            ruleElement.classList.remove('satisfied');
            iconElement.className = 'ti ti-x absolute start-0 top-0 text-danger rule-icon';
        }
    };

    window.checkAllRulesSatisfied = function (passwordId = 'password', confirmPasswordId = 'confirm_password', submitBtnId = 'password-submit-btn') {
        const passwordInput = document.getElementById(passwordId);
        const confirmPasswordInput = document.getElementById(confirmPasswordId);
        const submitBtn = document.getElementById(submitBtnId);

        if (!passwordInput || !submitBtn) return;

        const password = passwordInput.value;
        const confirmPassword = confirmPasswordInput ? confirmPasswordInput.value : '';
        const rules = window.checkPasswordRules(password, confirmPassword);

        // All rules must be satisfied
        const allSatisfied = rules.length && rules.uppercase && rules.lowercase && rules.digit && rules.special && rules.noSpaces && rules.match === true;

        submitBtn.disabled = !allSatisfied;
    };
</script>