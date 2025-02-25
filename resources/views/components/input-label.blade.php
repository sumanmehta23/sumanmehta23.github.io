@props(['value'])

<label {{ $attributes->merge(['class' => 'block text-sm font-medium text-black dark:text-white']) }}>
    {{ $value ?? $slot }}
</label>
