<button type="button"
        @click="window.toggleBookYourHotelTheme()"
        onclick="if (!window.Alpine) window.toggleBookYourHotelTheme()"
        aria-label="Toggle color theme"
        title="Toggle color theme"
        class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-gray-700 bg-gray-900 text-gray-200 transition hover:border-gray-500 hover:text-white focus:outline-none focus:ring-2 focus:ring-blue-400 dark:bg-gray-900">
    <svg data-theme-icon="sun" aria-hidden="true" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
        <circle cx="12" cy="12" r="3.5"/><path stroke-linecap="round" d="M12 2v2m0 16v2M4.93 4.93l1.42 1.42m11.3 11.3l1.42 1.42M2 12h2m16 0h2M4.93 19.07l1.42-1.42m11.3-11.3l1.42-1.42"/>
    </svg>
    <svg data-theme-icon="moon" aria-hidden="true" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
        <path stroke-linecap="round" stroke-linejoin="round" d="M20.5 15.2A8.5 8.5 0 0 1 8.8 3.5 8.5 8.5 0 1 0 20.5 15.2Z"/>
    </svg>
</button>
