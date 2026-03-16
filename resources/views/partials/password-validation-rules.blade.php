<div>
    <h5>
        Password Must Contain
    </h5>
    @if(!empty($additionalInstructions))
        <div class="p-3 mb-3 border border-blue-200 rounded-sm bg-blue-50 dark:bg-blue-950 dark:border-blue-700">
            <p class="text-sm text-blue-800 dark:text-blue-200">
                {!! $additionalInstructions !!}
            </p>
        </div>
    @endif
    <ul class="list-group list-group-flush">
        <li id="rule-length" class="list-group-item">
            <i class="ti ti-x absolute left-0 top-[-1px] text-base text-red-500 rule-icon"></i>
            At least 8 characters
        </li>
        <li id="rule-lowercase" class="list-group-item">
            <i class="ti ti-x absolute left-0 top-[-1px] text-base text-red-500 rule-icon"></i>
            At least 1 lowercase letter (a-z)
        </li>
        <li id="rule-uppercase" class="list-group-item">
            <i class="ti ti-x absolute left-0 top-[-1px] text-base text-red-500 rule-icon"></i>
            At least 1 uppercase letter (A-Z)
        </li>
        <li id="rule-digit" class="list-group-item">
            <i class="ti ti-x absolute left-0 top-[-1px] text-base text-red-500 rule-icon"></i>
            At least 1 number (0-9)
        </li>
        <li id="rule-special" class="list-group-item">
            <i class="ti ti-x absolute left-0 top-[-1px] text-base text-red-500 rule-icon"></i>
            <span>At least 1 special character:</span>
            <div
                class="p-2 mt-1 font-mono border rounded">
                ! @ # $ % ^ &amp; * ( ) , . - ? " : { } | &lt; &gt;
            </div>
        </li>
        <li id="rule-no-spaces" class="list-group-item">
            <i class="ti ti-x absolute left-0 top-[-1px] text-base text-red-500 rule-icon"></i>
            No spaces allowed
        </li>
        <li id="rule-match" class="list-group-item">
            <i class="ti ti-x absolute left-0 top-[-1px] text-base text-red-500 rule-icon"></i>
            Passwords match
        </li>
    </ul>
</div>