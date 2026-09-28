<x-app-layout>
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-bold text-gray-800">Page Appearance</h2>
                <p class="text-sm text-gray-500 mt-1">Customize gradient backgrounds for the home page and login page. Each user has their own settings.</p>
            </div>
            <a href="{{ route('settings.index') }}" class="flex items-center text-sm text-gray-500 hover:text-[#4634ff] transition-colors">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Back to Settings
            </a>
        </div>

        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                {{ session('success') }}
            </div>
        @endif

        <form action="{{ route('settings.page-appearance.update') }}" method="POST" class="space-y-6">
            @csrf



            <x-appearance-editor
                prefix="system"
                title="Overall System Color Scheme"
                :scheme="$personal['system']['scheme']"
                :colors="$personal['system']['colors']"
            />

            <p class="text-xs text-gray-500">The overall color scheme will be globally applied to the sidebar, and your login/home pages.</p>

            <button type="submit" class="w-full bg-[#4634ff] text-white py-3 rounded-xl font-bold shadow-lg hover:bg-[#3828cc] transition-colors" :style="`background-color: var(--button-color, #4634ff)`">
                Save My Appearance
            </button>
        </form>

        @if($isAdmin)
        <div class="border-t border-gray-200 pt-8 mt-8">
            <h3 class="text-lg font-bold text-gray-800 mb-2">Site-wide defaults (Admin)</h3>
            <p class="text-sm text-gray-500 mb-4">Used for the public home page, login screen, and default user interface for anyone without personal settings.</p>

            <form action="{{ route('settings.page-appearance.defaults') }}" method="POST" class="space-y-6">
                @csrf



                <x-appearance-editor
                    prefix="default_system"
                    title="Overall System Color Scheme — Site Default"
                    :scheme="$siteDefaults['system']['scheme']"
                    :colors="$siteDefaults['system']['colors']"
                />

                <button type="submit" class="w-full bg-gray-800 text-white py-3 rounded-xl font-bold shadow-lg hover:bg-gray-900 transition-colors">
                    Save Site Defaults
                </button>
            </form>
        </div>
        @endif
    </div>

    @push('scripts')
    <script>
        window.APPEARANCE_SCHEME_PRESETS = {{ Js::from(\App\Services\AppearanceSettingService::SCHEME_PRESETS) }};
    </script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
    <script src="{{ asset('js/welcome-background.js') }}?v={{ filemtime(public_path('js/welcome-background.js')) }}"></script>
    <script src="{{ asset('js/appearance-editor.js') }}?v={{ filemtime(public_path('js/appearance-editor.js')) }}"></script>
    @endpush
</x-app-layout>
