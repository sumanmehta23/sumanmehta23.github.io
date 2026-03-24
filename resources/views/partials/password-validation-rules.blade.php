
<style>
    .password-validation-rules .rule-icon {
        width: 16px;
        height: 16px;
        display: inline-block;
        text-align: center;
        line-height: 16px;
        font-size: 14px;
        font-style: normal;
        font-weight: bold;
    }
    /* Default state - show red X */
    .password-validation-rules .rule-icon::before {
        content: "✗";
        color: #dc3545;
    }
    .password-validation-rules .rule-icon.valid::before {
        content: "✓";
        color: #28a745 !important;
    }
    .password-validation-rules .rule-icon.invalid::before {
        content: "✗";
        color: #dc3545 !important;
    }
    .password-validation-rules li.valid {
        color: #28a745 !important;
    }
    .password-validation-rules li.invalid {
        color: #6c757d !important;
    }
</style>
<div class="password-validation-rules">
    <h6 class="mb-2">Password Must Contain</h6>
    @if(!empty($additionalInstructions))
        <div class="p-3 mb-3 border rounded border-primary bg-light">
            <p class="mb-0 text-sm">{!! $additionalInstructions !!}</p>
        </div>
    @endif
    <ul class="list-group list-group-flush" style="padding-left: 0;">
        <li id="rule-length" class="list-group-item d-flex align-items-center invalid" style="padding-left: 25px; position: relative;">
            <i class="rule-icon invalid" style="position: absolute; left: 0; top: 50%; transform: translateY(-50%);"></i>
            At least 8 characters
        </li>
        <li id="rule-lowercase" class="list-group-item d-flex align-items-center invalid" style="padding-left: 25px; position: relative;">
            <i class="rule-icon invalid" style="position: absolute; left: 0; top: 50%; transform: translateY(-50%);"></i>
            At least 1 lowercase letter (a-z)
        </li>
        <li id="rule-uppercase" class="list-group-item d-flex align-items-center invalid" style="padding-left: 25px; position: relative;">
            <i class="rule-icon invalid" style="position: absolute; left: 0; top: 50%; transform: translateY(-50%);"></i>
            At least 1 uppercase letter (A-Z)
        </li>
        <li id="rule-digit" class="list-group-item d-flex align-items-center invalid" style="padding-left: 25px; position: relative;">
            <i class="rule-icon invalid" style="position: absolute; left: 0; top: 50%; transform: translateY(-50%);"></i>
            At least 1 number (0-9)
        </li>
        <li id="rule-special" class="list-group-item d-flex align-items-center invalid" style="padding-left: 25px; position: relative;">
            <i class="rule-icon invalid" style="position: absolute; left: 0; top: 50%; transform: translateY(-50%);"></i>
            <div class="flex-vertical">
                <div class="mb-2 ">At least 1 special character:</div>
                <div class="p-1 border rounded font-monospace">
                    ! @ # $ % ^ & * ( ) , . - ? " : { } | < >
                </div>
            </div>
        </li>
        <li id="rule-no-spaces" class="list-group-item d-flex align-items-center invalid" style="padding-left: 25px; position: relative;">
            <i class="rule-icon invalid" style="position: absolute; left: 0; top: 50%; transform: translateY(-50%);"></i>
            No spaces allowed
        </li>
        <li id="rule-match" class="list-group-item d-flex align-items-center invalid" style="padding-left: 25px; position: relative;">
            <i class="rule-icon invalid" style="position: absolute; left: 0; top: 50%; transform: translateY(-50%);"></i>
            Passwords match
        </li>
    </ul>
</div>
