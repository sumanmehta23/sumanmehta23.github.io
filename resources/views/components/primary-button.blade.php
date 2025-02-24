<button
    {{ $attributes->merge([
        'type' => 'submit',
        'class' =>
            'flex items-center gap-1 w-full justify-center btn btn-sm btn-primary d-grid me-2',
    ]) }}>
    <span class="flex items-center gap-1 p-2">
        {{ $slot }}
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor">
            <path d="M9 5l7 7-7 7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
        </svg>
    </span>
</button>
