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
            // Reset state - neutral gray
            ruleElement.classList.remove('valid');
            ruleElement.classList.add('invalid');
            if (iconElement) {
                iconElement.classList.remove('valid');
                iconElement.classList.add('invalid');
            }
        } else if (isSatisfied) {
            // Satisfied - green
            ruleElement.classList.remove('invalid');
            ruleElement.classList.add('valid');
            if (iconElement) {
                iconElement.classList.remove('invalid');
                iconElement.classList.add('valid');
            }
        } else {
            // Not satisfied - red
            ruleElement.classList.remove('valid');
            ruleElement.classList.add('invalid');
            if (iconElement) {
                iconElement.classList.remove('valid');
                iconElement.classList.add('invalid');
            }
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
