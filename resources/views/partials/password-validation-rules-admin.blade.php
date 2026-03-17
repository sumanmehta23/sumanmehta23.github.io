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

<style>
    #{{ $prefix }}rule-length,
    #{{ $prefix }}rule-lowercase,
    #{{ $prefix }}rule-uppercase,
    #{{ $prefix }}rule-digit,
    #{{ $prefix }}rule-special,
    #{{ $prefix }}rule-no-spaces,
    #{{ $prefix }}rule-match {
        transition: color 0.2s ease;
    }
    #{{ $prefix }}rule-length.valid,
    #{{ $prefix }}rule-lowercase.valid,
    #{{ $prefix }}rule-uppercase.valid,
    #{{ $prefix }}rule-digit.valid,
    #{{ $prefix }}rule-special.valid,
    #{{ $prefix }}rule-no-spaces.valid,
    #{{ $prefix }}rule-match.valid {
        color: #28a745 !important;
    }
    #{{ $prefix }}rule-length.invalid,
    #{{ $prefix }}rule-lowercase.invalid,
    #{{ $prefix }}rule-uppercase.invalid,
    #{{ $prefix }}rule-digit.invalid,
    #{{ $prefix }}rule-special.invalid,
    #{{ $prefix }}rule-no-spaces.invalid,
    #{{ $prefix }}rule-match.invalid {
        color: #6c757d !important;
    }
    #{{ $prefix }}rule-length .rule-icon,
    #{{ $prefix }}rule-lowercase .rule-icon,
    #{{ $prefix }}rule-uppercase .rule-icon,
    #{{ $prefix }}rule-digit .rule-icon,
    #{{ $prefix }}rule-special .rule-icon,
    #{{ $prefix }}rule-no-spaces .rule-icon,
    #{{ $prefix }}rule-match .rule-icon {
        transition: color 0.2s ease;
    }
    #{{ $prefix }}rule-length .rule-icon.valid,
    #{{ $prefix }}rule-lowercase .rule-icon.valid,
    #{{ $prefix }}rule-uppercase .rule-icon.valid,
    #{{ $prefix }}rule-digit .rule-icon.valid,
    #{{ $prefix }}rule-special .rule-icon.valid,
    #{{ $prefix }}rule-no-spaces .rule-icon.valid,
    #{{ $prefix }}rule-match .rule-icon.valid {
        color: #28a745 !important;
    }
    #{{ $prefix }}rule-length .rule-icon.invalid,
    #{{ $prefix }}rule-lowercase .rule-icon.invalid,
    #{{ $prefix }}rule-uppercase .rule-icon.invalid,
    #{{ $prefix }}rule-digit .rule-icon.invalid,
    #{{ $prefix }}rule-special .rule-icon.invalid,
    #{{ $prefix }}rule-no-spaces .rule-icon.invalid,
    #{{ $prefix }}rule-match .rule-icon.invalid {
        color: #dc3545 !important;
    }
</style>