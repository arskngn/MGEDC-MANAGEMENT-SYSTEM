{{-- Date range: preset-first dropdown, expanding to dual calendars on Custom Range --}}
<div class="relative w-full sm:w-[260px] shrink-0" @click.outside="close()">
    <div class="flex rounded-md border border-[#5542ff]/40 dark:border-gray-600 overflow-hidden bg-white dark:bg-gray-900 focus-within:ring-1 focus-within:ring-[#5542ff] focus-within:border-[#5542ff]">
        <input
            type="text"
            readonly
            :value="displayPlaceholder()"
            @click="toggle()"
            placeholder="Start Date - End Date"
            class="flex-1 min-w-0 pl-3 pr-2 py-2 text-sm text-gray-600 dark:text-gray-300 cursor-pointer bg-white dark:bg-gray-900 border-0 focus:ring-0"
        />
        <button type="button" @click="submitSearch()" class="px-3 gradient-bg text-white hover:bg-opacity-90 shrink-0" title="Search with current filters">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
        </button>
    </div>

    {{-- Dropdown panel --}}
    <div
        x-show="open"
        x-cloak
        x-transition
        class="absolute z-[100] mt-1.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 shadow-2xl origin-top-right max-h-[85vh] overflow-y-auto"
        :class="customMode ? 'right-0 sm:right-0 left-0 sm:left-auto w-full sm:w-[580px]' : 'left-0 w-[180px]'"
    >
        {{-- Caret pointing up --}}
        <div class="hidden sm:block absolute -top-[5px] w-2.5 h-2.5 bg-white dark:bg-gray-800 border-t border-l border-gray-200 dark:border-gray-700 rotate-45 z-0" :class="customMode ? 'right-6' : 'left-6'"></div>

        <div class="relative flex bg-white dark:bg-gray-800 w-full rounded-t-xl z-10" :class="customMode ? 'flex-col sm:flex-row' : 'rounded-b-xl'">
            {{-- Presets column --}}
            <div class="shrink-0 py-2" :class="customMode ? 'w-full sm:w-[150px] border-b sm:border-b-0 sm:border-r border-gray-100 dark:border-gray-700 grid grid-cols-2 sm:grid-cols-1 gap-1 px-2 sm:px-0 sm:py-2' : 'w-full'">
                @foreach([
                    ['id' => 'today', 'label' => 'Today'],
                    ['id' => 'yesterday', 'label' => 'Yesterday'],
                    ['id' => 'last7', 'label' => 'Last 7 Days'],
                    ['id' => 'last15', 'label' => 'Last 15 Days'],
                    ['id' => 'last30', 'label' => 'Last 30 Days'],
                    ['id' => 'thisMonth', 'label' => 'This Month'],
                    ['id' => 'lastMonth', 'label' => 'Last Month'],
                    ['id' => 'last6', 'label' => 'Last 6 Months'],
                    ['id' => 'thisYear', 'label' => 'This Year'],
                    ['id' => 'custom', 'label' => 'Custom Range'],
                ] as $preset)
                    <button
                        type="button"
                        @click="selectPreset('{{ $preset['id'] }}')"
                        class="w-full text-left px-3 py-1.5 text-xs sm:text-sm rounded-lg sm:rounded-none transition-colors"
                        :class="activePreset === '{{ $preset['id'] }}' ? 'text-white font-medium' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700'"
                        :style="activePreset === '{{ $preset['id'] }}' ? 'background: linear-gradient(135deg, var(--gradient-from) 0%, var(--gradient-to) 100%)' : ''"
                    >
                        {{ $preset['label'] }}
                    </button>
                @endforeach
            </div>

            {{-- Calendars (custom range only) --}}
            <div class="flex-1 p-2.5 sm:p-3 min-w-0" x-show="customMode" x-cloak>
                <div class="flex flex-col sm:flex-row gap-3 justify-center">
                    {{-- Primary / Left calendar --}}
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center justify-between mb-2">
                            <button type="button" @click="prevMonths()" class="p-1 rounded hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-600 dark:text-gray-300" aria-label="Previous month">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
                            </button>
                            <div class="flex items-center gap-1">
                                <select class="text-xs font-semibold text-[#0a1233] dark:text-gray-200 border border-gray-200 dark:border-gray-600 rounded px-1 py-0.5 bg-white dark:bg-gray-900 cursor-pointer"
                                    x-model.number="viewMonth"
                                >
                                    @foreach(['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'] as $mIdx => $mName)
                                        <option value="{{ $mIdx }}">{{ $mName }}</option>
                                    @endforeach
                                </select>
                                <select class="text-xs font-semibold text-[#0a1233] dark:text-gray-200 border border-gray-200 dark:border-gray-600 rounded px-1 py-0.5 bg-white dark:bg-gray-900 cursor-pointer"
                                    x-model.number="viewYear"
                                >
                                    <template x-for="y in yearOptions()" :key="'ly'+y">
                                        <option :value="y" x-text="y"></option>
                                    </template>
                                </select>
                            </div>
                            <button type="button" @click="nextMonths()" class="sm:hidden p-1 rounded hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-600 dark:text-gray-300" aria-label="Next month">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                            </button>
                            <span class="hidden sm:inline-block w-6"></span>
                        </div>
                        <div class="grid grid-cols-7 gap-0.5 text-center text-[10px] font-semibold text-ink-muted mb-1">
                            <span>Su</span><span>Mo</span><span>Tu</span><span>We</span><span>Th</span><span>Fr</span><span>Sa</span>
                        </div>
                        <template x-for="(week, wi) in leftGrid()" :key="'lw'+wi">
                            <div class="grid grid-cols-7 gap-0.5">
                                <template x-for="(d, di) in week" :key="'ld'+wi+'-'+di">
                                    <div @click="clickDay(d)" :class="dayClass(d)">
                                        <span x-text="d ? d.getDate() : ''"></span>
                                    </div>
                                </template>
                            </div>
                        </template>
                    </div>

                    {{-- Secondary / Right calendar (Hidden on small mobile screens to keep dropdown compact) --}}
                    <div class="hidden sm:block flex-1 min-w-0">
                        <div class="flex items-center justify-between mb-2">
                            <span class="w-6"></span>
                            <div class="flex items-center gap-1">
                                <select class="text-xs font-semibold text-[#0a1233] dark:text-gray-200 border border-gray-200 dark:border-gray-600 rounded px-1 py-0.5 bg-white dark:bg-gray-900 cursor-pointer"
                                    @change="
                                        let rm = parseInt($event.target.value);
                                        let diff = rm - ((viewMonth + 1) > 11 ? 0 : viewMonth + 1);
                                        if (diff !== 0) {
                                            let nm = viewMonth + diff;
                                            if (nm < 0) { viewYear += Math.floor(nm / 12); nm = ((nm % 12) + 12) % 12; }
                                            else if (nm > 11) { viewYear += Math.floor(nm / 12); nm = nm % 12; }
                                            viewMonth = nm;
                                        }
                                    "
                                    :value="rightCalYearMonth().month"
                                >
                                    @foreach(['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'] as $mIdx => $mName)
                                        <option value="{{ $mIdx }}">{{ $mName }}</option>
                                    @endforeach
                                </select>
                                <select class="text-xs font-semibold text-[#0a1233] dark:text-gray-200 border border-gray-200 dark:border-gray-600 rounded px-1 py-0.5 bg-white dark:bg-gray-900 cursor-pointer"
                                    @change="
                                        let ry = parseInt($event.target.value);
                                        let cy = rightCalYearMonth().year;
                                        if (ry !== cy) { viewYear += (ry - cy); }
                                    "
                                    :value="rightCalYearMonth().year"
                                >
                                    <template x-for="y in yearOptions()" :key="'ry'+y">
                                        <option :value="y" x-text="y"></option>
                                    </template>
                                </select>
                            </div>
                            <button type="button" @click="nextMonths()" class="p-1 rounded hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-600 dark:text-gray-300" aria-label="Next month">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                            </button>
                        </div>
                        <div class="grid grid-cols-7 gap-0.5 text-center text-[10px] font-semibold text-ink-muted mb-1">
                            <span>Su</span><span>Mo</span><span>Tu</span><span>We</span><span>Th</span><span>Fr</span><span>Sa</span>
                        </div>
                        <template x-for="(week, wi) in rightGrid()" :key="'rw'+wi">
                            <div class="grid grid-cols-7 gap-0.5">
                                <template x-for="(d, di) in week" :key="'rd'+wi+'-'+di">
                                    <div @click="clickDay(d)" :class="dayClass(d)">
                                        <span x-text="d ? d.getDate() : ''"></span>
                                    </div>
                                </template>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </div>

        {{-- Footer: only when customMode --}}
        <div class="relative z-10 flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-2 px-3 py-2 border-t border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 rounded-b-xl"
             x-show="customMode" x-cloak>
            <span class="text-xs text-gray-600 dark:text-gray-300 font-medium truncate text-center sm:text-left" x-text="footerLabel()"></span>
            <div class="flex items-center justify-end gap-2 shrink-0">
                <button type="button" @click="clearRange()" class="px-3 py-1.5 text-xs sm:text-sm font-medium text-ink-body hover:text-black dark:hover:text-white">Clear</button>
                <button type="button" @click="applyFooter()" class="px-4 py-1.5 text-xs sm:text-sm font-semibold rounded-lg bg-[#5542ff] text-white hover:bg-[#4736d6]">Apply</button>
            </div>
        </div>
    </div>
</div>
