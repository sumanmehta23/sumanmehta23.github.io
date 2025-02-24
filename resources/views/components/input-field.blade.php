<div class="relative">
    @if ($leftIcon)
        <div class="absolute inset-y-0 flex items-center pointer-events-none left-3">
            <i class="text-black {{ $leftIcon }} dark:text-white"></i>
        </div>
    @endif

    <input type="{{ $type }}" name="{{ $name }}" placeholder="{{ $placeholder }}"
        {{ $attributes->merge([
            'class' =>
                'dark:bg-white/5 bg-[#FFFFFF0D] border dark:border-white/50 border-[#3F3F3F33] text-black-900 text-black dark:text-white text-sm rounded-xs block w-full p-3 dark:placeholder-[#999999] placeholder-[#00000066] ' .
                ($leftIcon ? 'pl-[35px]' : '') .
                ($rightIcon || $rightButton ? ' pr-[35px]' : ''),
        ]) }}>

    @if ($rightIcon)
        <div class="absolute inset-y-0 flex items-center pointer-events-none right-3">
            <i class="text-black {{ $rightIcon }} dark:text-white"></i>
        </div>
    @elseif($rightButton)
        <button type="button" class="absolute inset-y-0 right-0 flex items-center px-2">
            {!! $rightButton !!}
        </button>
    @endif
</div>
