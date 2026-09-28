<x-app-layout>
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-bold text-gray-800">Logo & Favicon</h2>
            <a href="{{ route('settings.index') }}" class="flex items-center text-sm text-gray-500 hover:text-[#4634ff] transition-colors">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Back to Settings
            </a>
        </div>

        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                <span class="block sm:inline">{{ session('success') }}</span>
            </div>
        @endif

        @if($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                <ul class="list-disc list-inside">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="bg-blue-50 border-l-4 border-blue-500 p-6 rounded-r-xl shadow-sm">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-blue-700 leading-relaxed">
                        If the logo and favicon are not changed after you update from this page, please clear the cache from your browser. Page background colors are configured under <a href="{{ route('settings.page-appearance') }}" class="font-semibold underline hover:text-blue-800">Page Appearance</a>.
                    </p>
                </div>
            </div>
        </div>

        <form action="{{ route('settings.logo-favicon.update') }}" method="POST" enctype="multipart/form-data" class="space-y-8">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="space-y-3">
                    <label class="block text-sm font-medium text-gray-700">Logo icon for light background</label>
                    <div class="relative group">
                        <div class="aspect-square w-32 mx-auto bg-white border-2 border-dashed @if($setting->hasCustomLogoLight()) border-[#4634ff] @else border-gray-200 @endif rounded-full flex items-center justify-center overflow-hidden transition-all group-hover:border-[#4634ff]/50 shadow-sm">
                            <img id="logo_light_preview" src="{{ asset($setting->logo_light) }}" class="h-full w-full object-cover p-1" alt="Logo Light">
                        </div>
                        <label for="logo_light" class="absolute bottom-0 right-1/4 cursor-pointer bg-[#4634ff] text-white p-2 rounded-xl shadow-lg hover:bg-[#3828cc] transition-all transform translate-x-1/2 translate-y-1/2 hover:scale-110 active:scale-95">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                            </svg>
                            <input type="file" name="logo_light" id="logo_light" class="hidden" accept="image/png, image/jpeg, image/jpg" onchange="previewImage(this, 'logo_light_preview')">
                        </label>
                    </div>
                    <p class="text-center text-xs text-gray-400 mt-4">Icon only. "MGEDC" text is added automatically.</p>
                </div>

                <div class="space-y-3">
                    <label class="block text-sm font-medium text-gray-700">Logo icon for dark background</label>
                    <div class="relative group">
                        <div class="aspect-square w-32 mx-auto bg-[#0a1233] border-2 border-dashed @if($setting->hasCustomLogoDark()) border-[#4634ff] @else border-gray-700 @endif rounded-full flex items-center justify-center overflow-hidden transition-all group-hover:border-[#4634ff]/50 shadow-sm">
                            <img id="logo_dark_preview" src="{{ asset($setting->logo_dark) }}" class="h-full w-full object-cover p-1" alt="Logo Dark">
                        </div>
                        <label for="logo_dark" class="absolute bottom-0 right-1/4 cursor-pointer bg-[#4634ff] text-white p-2 rounded-xl shadow-lg hover:bg-[#3828cc] transition-all transform translate-x-1/2 translate-y-1/2 hover:scale-110 active:scale-95">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                            </svg>
                            <input type="file" name="logo_dark" id="logo_dark" class="hidden" accept="image/png, image/jpeg, image/jpg" onchange="previewImage(this, 'logo_dark_preview')">
                        </label>
                    </div>
                    <p class="text-center text-xs text-gray-400 mt-4">Icon only. "MGEDC" text is added automatically.</p>
                </div>

                <div class="space-y-3">
                    <label class="block text-sm font-medium text-gray-700">Favicon</label>
                    <div class="relative group">
                        <div class="aspect-square w-32 mx-auto bg-white border-2 border-dashed @if($setting->hasCustomFavicon()) border-[#4634ff] @else border-gray-200 @endif rounded-full flex items-center justify-center overflow-hidden transition-all group-hover:border-[#4634ff]/50 shadow-sm">
                            <img id="favicon_preview" src="{{ asset($setting->favicon) }}" class="h-full w-full object-contain p-1" alt="Favicon">
                        </div>
                        <label for="favicon" class="absolute bottom-0 right-1/4 cursor-pointer bg-[#4634ff] text-white p-2 rounded-xl shadow-lg hover:bg-[#3828cc] transition-all transform translate-x-1/2 translate-y-1/2 hover:scale-110 active:scale-95">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                            </svg>
                            <input type="file" name="favicon" id="favicon" class="hidden" accept="image/png, image/jpeg, image/jpg" onchange="previewImage(this, 'favicon_preview')">
                        </label>
                    </div>
                    <p class="text-center text-xs text-gray-400 mt-4">Icon only. "MGEDC" text is added automatically.</p>
                </div>
            </div>

            <button type="submit" class="w-full bg-[#4634ff] text-white py-4 rounded-xl font-bold text-lg shadow-lg hover:bg-[#3828cc] transition-all transform hover:translate-y-[-2px] active:translate-y-[0px]">
                Submit
            </button>
        </form>
    </div>

    @push('scripts')
    <script>
        function previewImage(input, previewId) {
            const preview = document.getElementById(previewId);
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                };
                reader.readAsDataURL(input.files[0]);
            }
        }
    </script>
    @endpush
</x-app-layout>
