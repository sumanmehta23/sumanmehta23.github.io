<div class="border card card-body bg-light border-secondary-subtle rounded-2">
    <h5 class="mb-3 fw-bold text-dark">Password Must Contain</h5>

    @if(!empty($additionalInstructions))
        <div class="mb-3 border-4 alert alert-info border-start" role="alert">
            <small class="text-info">
                {!! $additionalInstructions !!}
            </small>
        </div>
    @endif

    @php
        $prefix = $prefix ?? '';
    @endphp

    <ul class="list-unstyled ps-0">
        <li id="{{ $prefix }}rule-length" class="relative mb-1 text-sm ps-4 text-muted">
            <i class="ti ti-x absolute left-0 top-[-1px] text-base text-red-500 rule-icon"
                style="font-size: 0.95rem;"></i>
            <span>At least 8 characters</span>
        </li>

        <li id="{{ $prefix }}rule-lowercase" class="relative mb-1 text-sm ps-4 text-muted">
            <i class="ti ti-x absolute left-0 top-[-1px] text-base text-red-500 rule-icon"
                style="font-size: 0.95rem;"></i>
            <span>At least 1 lowercase letter (a-z)</span>
        </li>

        <li id="{{ $prefix }}rule-uppercase" class="relative mb-1 text-sm ps-4 text-muted">
            <i class="ti ti-x absolute left-0 top-[-1px] text-base text-red-500 rule-icon"
                style="font-size: 0.95rem;"></i>
            <span>At least 1 uppercase letter (A-Z)</span>
        </li>

        <li id="{{ $prefix }}rule-digit" class="relative mb-1 text-sm ps-4 text-muted">
            <i class="ti ti-x absolute left-0 top-[-1px] text-base text-red-500 rule-icon"
                style="font-size: 0.95rem;"></i>
            <span>At least 1 number (0-9)</span>
        </li>

        <li id="{{ $prefix }}rule-special" class="relative mb-1 text-sm ps-4 text-muted">
            <i class="ti ti-x absolute left-0 top-[-1px] text-base text-red-500 rule-icon"
                style="font-size: 0.95rem;"></i>
            <div>
                <span>At least 1 special character:</span>
                <div class="p-2 mt-2 text-xs border rounded bg-body-secondary border-secondary font-monospace">
                    ! @ # $ % ^ &amp; * ( ) , . - ? " : { } | &lt; &gt;
                </div>
            </div>
        </li>

        <li id="{{ $prefix }}rule-no-spaces" class="relative mb-1 text-sm ps-4 text-muted">
            <i class="ti ti-x absolute left-0 top-[-1px] text-base text-red-500 rule-icon"
                style="font-size: 0.95rem;"></i>
            <span>No spaces allowed</span>
        </li>

        <li id="{{ $prefix }}rule-match" class="relative text-sm ps-4 text-muted">
            <i class="ti ti-x absolute left-0 top-[-1px] text-base text-red-500 rule-icon"
                style="font-size: 0.95rem;"></i>
            <span>Passwords match</span>
        </li>
    </ul>
</div>