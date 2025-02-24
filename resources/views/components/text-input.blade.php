@props(['disabled' => false])

<div class="relative">
    <div class="absolute inset-y-0 flex items-center pointer-events-none left-3">
    </div>
    <input
        {{ $attributes->merge(['class' => 'dark:bg-white/5 bg-[#FFFFFF0D] border dark:border-white/50 border-[#3F3F3F33] text-black-900 text-black dark:text-white text-sm rounded-xs block w-full p-3 dark:placeholder-[#999999] placeholder-[#00000066] pl-[35px]']) }}>
</div>
