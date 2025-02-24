<button
    {{ $attributes->merge([
        'type' => 'submit',
        'class' =>
            'bg-theme-light-text hover:bg-red-800 hover:text-white dark:bg-white dark:hover:bg-red-800 dark:hover:text-white flex items-center gap-1 font-medium text-white dark:text-theme-light-text px-3 py-[8px] rounded-xs text-sm  transition-colors w-full justify-center text-sm transition-all focus:opacity-[0.85] active:opacity-[0.85] disabled:pointer-events-none disabled:opacity-50 ',
    ]) }}>
    {{ $slot }}
    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor">
        <path d="M9 5l7 7-7 7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
    </svg>
</button>
