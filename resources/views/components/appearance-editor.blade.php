@props([
    'prefix',
    'title',
    'scheme' => 1,
    'colors' => [],
])

@php
    $scheme = (int) $scheme;
    $colors = is_array($colors) ? $colors : [];
    $presets = \App\Services\AppearanceSettingService::SCHEME_PRESETS;
    while (count($colors) < 6) {
        $colors[] = $presets[$scheme][count($colors)] ?? '#F15A22';
    }
@endphp

<div class="appearance-editor border border-gray-200 rounded-xl p-5 bg-gray-50/80" data-appearance-editor data-prefix="{{ $prefix }}">
    <h3 class="text-base font-bold text-gray-800 mb-4">{{ $title }}</h3>

    <input type="hidden" name="{{ $prefix }}_scheme" value="{{ $scheme }}" data-scheme-input>
    <input type="hidden" name="{{ $prefix }}_colors" value="{{ json_encode($colors) }}" data-colors-input>

    <p class="text-sm text-gray-600 mb-3">1-Color Appearance Choices</p>
    <div class="flex flex-wrap gap-2 mb-5" role="group"
         x-data="{ 
            showModal: false,
            presets: [
                { name: 'Blue', hex: '#3B82F6' },
                { name: 'Green', hex: '#10B981' },
                { name: 'Red', hex: '#EF4444' },
                { name: 'Yellow', hex: '#EAB308' },
                { name: 'Orange', hex: '#F97316' },
                { name: 'Purple', hex: '#8B5CF6' },
                { name: 'Pink', hex: '#EC4899' }
            ],
            extendedColors: [
                { name: 'Slate', hex: '#64748B' }, { name: 'Gray', hex: '#6B7280' }, { name: 'Zinc', hex: '#71717A' }, { name: 'Neutral', hex: '#737373' }, { name: 'Stone', hex: '#78716C' },
                { name: 'Red', hex: '#EF4444' }, { name: 'Orange', hex: '#F97316' }, { name: 'Amber', hex: '#F59E0B' }, { name: 'Yellow', hex: '#EAB308' }, { name: 'Lime', hex: '#84CC16' },
                { name: 'Green', hex: '#10B981' }, { name: 'Emerald', hex: '#10B981' }, { name: 'Teal', hex: '#14B8A6' }, { name: 'Cyan', hex: '#06B6D4' }, { name: 'Sky', hex: '#0EA5E9' },
                { name: 'Blue', hex: '#3B82F6' }, { name: 'Indigo', hex: '#6366F1' }, { name: 'Violet', hex: '#8B5CF6' }, { name: 'Purple', hex: '#A855F7' }, { name: 'Fuchsia', hex: '#D946EF' },
                { name: 'Pink', hex: '#EC4899' }, { name: 'Rose', hex: '#F43F5E' }, { name: 'Brand', hex: '#4634ff' }, { name: 'Navy', hex: '#1E3A8A' }, { name: 'Forest', hex: '#064E3B' }
            ],
            customColor: '#4634ff',
            applyColor(hex) {
                // Find the closest parent editor and all its color inputs
                const editor = this.$el.closest('[data-appearance-editor]');
                if (!editor) return;
                
                // Clear the active state from scheme buttons
                const schemeBtns = editor.querySelectorAll('.appearance-scheme-btn');
                schemeBtns.forEach(b => {
                    b.classList.remove('bg-[#4634ff]', 'text-white', 'border-[#4634ff]');
                    b.classList.add('bg-white', 'text-gray-700', 'border-gray-300');
                });
                // Clear scheme hidden input
                const schemeInput = editor.querySelector('[data-scheme-input]');
                if (schemeInput) {
                    schemeInput.value = '0'; // 0 indicates custom/no scheme
                    schemeInput.dispatchEvent(new Event('change', { bubbles: true }));
                }

                const inputs = editor.querySelectorAll('.appearance-color-picker');
                inputs.forEach(input => {
                    input.value = hex;
                    input.dispatchEvent(new Event('input', { bubbles: true }));
                    input.dispatchEvent(new Event('change', { bubbles: true }));
                });
            }
         }">
        
        <template x-for="preset in presets" :key="preset.name">
            <button type="button"
                @click="applyColor(preset.hex)"
                class="px-3 py-1.5 text-xs font-semibold uppercase tracking-wide rounded-full border transition-colors flex items-center gap-2 bg-white text-gray-700 border-gray-300 hover:border-[#4634ff]">
                <span class="w-3 h-3 rounded-full" :style="`background-color: ${preset.hex}`"></span>
                <span x-text="preset.name"></span>
            </button>
        </template>
        <button type="button"
            @click="showModal = true"
            class="px-3 py-1.5 text-xs font-semibold uppercase tracking-wide rounded-full border transition-colors bg-white text-gray-700 border-gray-300 hover:border-[#4634ff] flex items-center gap-1">
            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" /></svg>
            More
        </button>

        <!-- More Colors Modal -->
        <div x-show="showModal" class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-gray-900/50 backdrop-blur-sm" style="display: none;"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0">
            
            <div class="bg-white rounded-xl shadow-xl w-full max-w-lg overflow-hidden" @click.away="showModal = false">
                <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                    <h3 class="text-lg font-bold text-gray-800">Select Overall System Color</h3>
                    <button @click="showModal = false" type="button" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>
                
                <div class="p-6">
                    <div class="grid grid-cols-5 sm:grid-cols-5 gap-4">
                        <template x-for="color in extendedColors" :key="color.name">
                            <button type="button"
                                @click="applyColor(color.hex); showModal = false"
                                class="flex flex-col items-center gap-2 group outline-none">
                                <div class="w-12 h-12 rounded-xl shadow-sm border border-gray-200 transition-all duration-200 group-hover:scale-110 group-hover:shadow-md group-hover:border-[#4634ff]"
                                     :style="`background-color: ${color.hex}`">
                                </div>
                                <span class="text-[10px] font-semibold text-gray-600 group-hover:text-gray-900" x-text="color.name"></span>
                            </button>
                        </template>
                    </div>
                    
                    <div class="mt-6 pt-5 border-t border-gray-100">
                        <label class="block text-xs font-bold text-gray-700 mb-2">Custom Hex Color</label>
                        <div class="flex gap-3">
                            <input type="color" class="w-10 h-10 rounded border border-gray-300 cursor-pointer p-0.5" x-model="customColor" @change="applyColor(customColor)">
                            <input type="text" class="flex-1 text-sm font-mono border border-gray-300 rounded-lg px-3 uppercase focus:ring-[#4634ff] focus:border-[#4634ff]" x-model="customColor" @change="applyColor(customColor)">
                        </div>
                    </div>
                </div>
                
                <div class="px-6 py-4 bg-gray-50 flex justify-end">
                    <button @click="showModal = false" type="button" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-lg font-semibold text-sm hover:bg-gray-300 transition-colors">
                        Done
                    </button>
                </div>
            </div>
        </div>
    </div>

    <p class="text-sm text-gray-600 mb-3">Default scheme</p>
    <div class="flex flex-wrap gap-2 mb-5" role="group">
        @foreach(range(1, 8) as $n)
        <button type="button"
            class="appearance-scheme-btn px-3 py-1.5 text-xs font-semibold uppercase tracking-wide rounded-full border transition-colors {{ $n === $scheme ? 'bg-[#4634ff] text-white border-[#4634ff]' : 'bg-white text-gray-700 border-gray-300 hover:border-[#4634ff]' }}"
            data-scheme="{{ $n }}">
            Scheme {{ $n }}
        </button>
        @endforeach
    </div>

    <p class="text-sm text-gray-600 mb-2">Customize colors</p>
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-4">
        @foreach(range(1, 6) as $i)
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Color {{ $i }}</label>
            <div class="flex items-center gap-2">
                <input type="color"
                    class="appearance-color-picker w-10 h-9 rounded border border-gray-300 cursor-pointer"
                    data-color-index="{{ $i }}"
                    value="{{ $colors[$i - 1] }}">
                <input type="text"
                    class="appearance-color-hex flex-1 min-w-0 text-xs font-mono border border-gray-300 rounded px-2 py-1.5 bg-white"
                    data-color-hex="{{ $i }}"
                    value="{{ strtoupper($colors[$i - 1]) }}"
                    maxlength="7">
            </div>
        </div>
        @endforeach
    </div>

    <div class="appearance-preview h-36 rounded-lg border border-gray-300 overflow-hidden relative bg-[#0a0e27]" data-preview-mount>
        <span class="absolute inset-0 flex items-center justify-center text-white/50 text-xs pointer-events-none z-10">Live preview</span>
    </div>
</div>
