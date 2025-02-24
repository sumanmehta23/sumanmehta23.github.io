@props(['disabled' => false])

<div class="relative">
    <div class="absolute inset-y-0 flex items-center pointer-events-none left-3">
    </div>
    <input
        {{ $attributes->merge(['class' => 'form-control w-25']) }}>
</div>
