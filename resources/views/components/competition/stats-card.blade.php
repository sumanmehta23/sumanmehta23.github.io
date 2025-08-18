@props(['title', 'value', 'icon' => null])

<div class="competition-stat-card">
    <div class="card shadow-sm">
        <div class="card-body">
            <div class="d-flex align-items-center">
                @if($icon)
                    <div class="stat-icon me-3">
                        <i class="feather {{ $icon }} fs-3"></i>
                    </div>
                @endif
                <div>
                    <h6 class="mb-1 p-2 text-muted">{{ $title }}</h6>
                    <h3 class="mb-0 pt-2">{{ $value }}</h3>
                </div>
            </div>
            @isset($slot)
                <div class="mt-auto">
                    {{ $slot }}
                </div>
            @endisset
        </div>
    </div>
</div>
