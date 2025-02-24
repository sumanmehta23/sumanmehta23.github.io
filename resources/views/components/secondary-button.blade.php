<button
    {{ $attributes->merge(['type' => 'button', 'class' => 'btn btn-outline-secondary d-grid mt-4']) }}>
    {{ $slot }}
</button>
