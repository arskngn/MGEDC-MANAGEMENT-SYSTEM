<x-app-layout>
    <div id="staff-page" x-data="{ 
        addModalOpen: {{ $errors->any() && old('_form_type', 'add') === 'add' ? 'true' : 'false' }}, 
        editModalOpen: {{ $errors->any() && old('_form_type') === 'edit' ? 'true' : 'false' }}, 
        banModalOpen: false,
        selectedStaff: null,
        staffData: { 
            name: '{{ old('name', '') }}', 
            email: '{{ old('email', '') }}', 
            phone: '{{ old('phone', '') }}', 
            role_id: '{{ old('role_id', '') }}', 
            password: '' 
        },
        userStatuses: {},
        init() {
            this.fetchStatuses();
            setInterval(() => this.fetchStatuses(), 30000); // Poll every 30 seconds
        },
        fetchStatuses() {
            fetch('{{ route('staff.statuses') }}')
                .then(response => response.json())
                .then(data => {
                    this.userStatuses = data;
                });
        },
        openEditModal(staff) {
            this.selectedStaff = staff;
            this.staffData = { 
                name: staff.name, 
                email: staff.email, 
                phone: staff.phone,
                role_id: staff.role_id || '', 
                password: '' 
            };
            this.editModalOpen = true;
        },
        openBanModal(staff) {
            this.selectedStaff = staff;
            this.banModalOpen = true;
        }
    }">
        @if(session('success'))
            <div class="mb-4 rounded-xl bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-700 text-green-800 dark:text-green-200 px-4 py-3 text-sm font-semibold">
                {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="mb-4 rounded-xl bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-700 text-red-800 dark:text-red-200 px-4 py-3 text-sm font-semibold">
                {{ session('error') }}
            </div>
        @endif
        @if($errors->any())
            <div class="mb-4 rounded-xl bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-700 text-red-800 dark:text-red-200 px-4 py-3 text-sm font-semibold">
                <p class="font-bold mb-1">Please fix the following validation errors:</p>
                <ul class="list-disc list-inside space-y-0.5 text-xs">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between mb-4">
            <h2 class="text-lg sm:text-xl font-bold text-[#0a1233] shrink-0">All Staff</h2>

            <div class="flex flex-col sm:flex-row sm:items-center gap-2.5 w-full sm:w-auto">
                <form
                    action="{{ route('staff.index') }}"
                    method="GET"
                    x-data="{ mobileFiltersOpen: false }"
                    class="w-full sm:flex sm:flex-row sm:items-center sm:gap-2 sm:w-auto"
                >
                    <input type="hidden" name="sort" id="sort-input" value="{{ request('sort', 'desc') }}">

                    {{-- Search + mobile filter toggle row --}}
                    <div class="flex items-center gap-2 sm:contents">
                        {{-- Search --}}
                        <div class="relative flex flex-1 sm:w-[240px] sm:flex-none">
                            <input
                                type="text"
                                name="search"
                                value="{{ request('search') }}"
                                placeholder="Search name, email..."
                                class="w-full pl-3 pr-8 py-2 border border-[#5542ff]/40 rounded-l-xl sm:rounded-l-md focus:outline-none focus:ring-1 focus:ring-[#5542ff] focus:border-[#5542ff] text-sm bg-white placeholder-gray-400"
                            />
                            @if(request('search'))
                                <a href="{{ request()->fullUrlWithQuery(['search' => null]) }}" class="absolute right-9 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 text-xs px-1 h-4 w-4 flex items-center justify-center" title="Clear search">&#x2715;</a>
                            @endif
                            <button type="submit" class="px-3 gradient-bg text-white rounded-r-xl sm:rounded-r-md hover:opacity-90 transition-colors flex items-center justify-center shrink-0" title="Search">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                            </button>
                        </div>

                        {{-- Sort toggle --}}
                        <button type="button" onclick="toggleSort()" class="p-2 bg-white border border-gray-300 rounded-xl sm:rounded-md hover:bg-gray-50 transition-colors shrink-0" title="Toggle Sort Order">
                            @if(request('sort', 'desc') === 'asc')
                                <svg class="h-4 w-4 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4h13M3 8h9m-9 4h6m4 0l4-4m0 0l4 4m-4-4v12" /></svg>
                            @else
                                <svg class="h-4 w-4 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4h13M3 8h9m-9 4h9m5-4v12m0 0l-4-4m4 4l4-4" /></svg>
                            @endif
                        </button>

                        {{-- Mobile filter toggle --}}
                        @php $activeStaffFilters = (int)(bool)request('role') + (int)(bool)request('status'); @endphp
                        <button type="button" @click="mobileFiltersOpen = !mobileFiltersOpen"
                                class="sm:hidden relative flex items-center gap-1.5 px-3 py-2 rounded-xl border text-sm font-semibold transition-all shrink-0"
                                :class="mobileFiltersOpen ? 'bg-[#5542ff] text-white border-[#5542ff]' : 'bg-white text-gray-700 border-gray-300'">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z"/></svg>
                            <span>Filter</span>
                            @if($activeStaffFilters > 0)
                                <span class="absolute -top-1.5 -right-1.5 h-4 w-4 flex items-center justify-center rounded-full bg-red-500 text-white text-[9px] font-black">{{ $activeStaffFilters }}</span>
                            @endif
                        </button>
                    </div>

                    {{-- Filter dropdowns: always visible desktop, toggle mobile --}}
                    <div x-show="mobileFiltersOpen" x-cloak class="sm:!block sm:flex sm:flex-row sm:items-center sm:gap-2" style="display:none">
                        <div class="grid grid-cols-2 gap-2 mt-2 sm:mt-0 sm:flex sm:flex-row sm:gap-2">
                            {{-- Role Filter --}}
                            <div class="relative w-full sm:w-auto">
                                <select name="role" onchange="this.form.submit()" class="w-full sm:w-auto text-sm border border-gray-300 rounded-md bg-white pl-3 pr-8 py-2 text-gray-700 focus:ring-1 focus:ring-[#5542ff] focus:border-[#5542ff] appearance-none cursor-pointer shadow-2xs transition-colors">
                                    <option value="">All Roles</option>
                                    @foreach($roles as $role)
                                        <option value="{{ $role->id }}" @selected(request('role') == $role->id)>{{ ucwords($role->name) }}</option>
                                    @endforeach
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-2.5 text-gray-400">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                                </div>
                            </div>

                            {{-- Status Filter --}}
                            <div class="relative w-full sm:w-auto">
                                <select name="status" onchange="this.form.submit()" class="w-full sm:w-auto text-sm border border-gray-300 rounded-md bg-white pl-3 pr-8 py-2 text-gray-700 focus:ring-1 focus:ring-[#5542ff] focus:border-[#5542ff] appearance-none cursor-pointer shadow-2xs transition-colors">
                                    <option value="">All Statuses</option>
                                    <option value="online"  @selected(request('status') === 'online')>Online</option>
                                    <option value="offline" @selected(request('status') === 'offline')>Offline</option>
                                    <option value="active"  @selected(request('status') === 'active')>Active</option>
                                    <option value="banned"  @selected(request('status') === 'banned')>Deactivated</option>
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-2.5 text-gray-400">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>

                <div class="flex items-center gap-2 shrink-0 justify-end">
                    <button @click="addModalOpen = true; staffData = { name: '', email: '', phone: '', role_id: '', password: '' }"
                            class="inline-flex items-center justify-center px-3 sm:px-4 py-2 bg-white border border-[#5542ff] text-[#5542ff] rounded-xl sm:rounded-md hover:bg-[#5542ff]/5 transition-colors text-sm font-semibold whitespace-nowrap">
                        <svg class="h-4 w-4 sm:mr-1.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                        <span class="hidden sm:inline">Add New</span>
                        <span class="sm:hidden">Add</span>
                    </button>
                </div>
            </div>
        </div>

        {{-- Active Filters Chips --}}
        @if(request()->hasAny(['search', 'role', 'status']))
            <div class="flex flex-wrap items-center gap-2 px-1 py-1 mb-3">
                <span class="text-xs font-semibold text-gray-500">Active Filters:</span>

                @if(request('search'))
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium bg-[#5542ff]/10 text-[#5542ff] border border-[#5542ff]/20">
                        Search: "{{ request('search') }}"
                        <a href="{{ request()->fullUrlWithQuery(['search' => null]) }}" class="hover:text-red-500 font-bold ml-1" title="Remove">&times;</a>
                    </span>
                @endif

                @if(request('role'))
                    @php $roleName = $roles->firstWhere('id', request('role'))?->name ?? request('role'); @endphp
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium bg-[#5542ff]/10 text-[#5542ff] border border-[#5542ff]/20">
                        Role: {{ ucwords($roleName) }}
                        <a href="{{ request()->fullUrlWithQuery(['role' => null]) }}" class="hover:text-red-500 font-bold ml-1" title="Remove">&times;</a>
                    </span>
                @endif

                @if(request('status'))
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium bg-[#5542ff]/10 text-[#5542ff] border border-[#5542ff]/20">
                        Status: {{ ucfirst(request('status') === 'banned' ? 'Deactivated' : request('status')) }}
                        <a href="{{ request()->fullUrlWithQuery(['status' => null]) }}" class="hover:text-red-500 font-bold ml-1" title="Remove">&times;</a>
                    </span>
                @endif

                <a href="{{ route('staff.index') }}" class="text-xs text-red-600 hover:underline font-semibold ml-1">Reset All Filters</a>
            </div>
        @endif

        <div class="bg-white rounded-xl shadow-sm overflow-visible">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-[#4634ff] text-white">
                        <th class="px-6 py-4 text-[12px] font-bold uppercase tracking-wider">S.N.</th>
                        <th class="px-6 py-4 text-[12px] font-bold uppercase tracking-wider">NAME</th>
                        <th class="px-6 py-4 text-[12px] font-bold uppercase tracking-wider">PHONE</th>
                        <th class="px-6 py-4 text-[12px] font-bold uppercase tracking-wider">ROLE</th>
                        <th class="px-6 py-4 text-[12px] font-bold uppercase tracking-wider">STATUS</th>
                        <th class="px-6 py-4 text-[12px] font-bold uppercase tracking-wider text-right">ACTION</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($staffs as $index => $staff)
                        <tr class="hover:bg-gray-50 transition-colors" 
                            x-data="{ showTooltip: false, mouseX: 0, mouseY: 0 }"
                            @mouseenter="showTooltip = true"
                            @mouseleave="showTooltip = false"
                            @mousemove="mouseX = $event.clientX; mouseY = $event.clientY">
                            <td class="px-6 py-4 text-sm text-gray-600">{{ $staffs->firstItem() + $index }}</td>
                            <td class="px-6 py-4 text-sm text-gray-600 font-medium">
                                <div class="flex items-center space-x-3">
                                     <div class="h-8 w-8 rounded-full flex items-center justify-center overflow-hidden border border-gray-200 shadow-sm flex-none"
                                         style="background: hsl({{ hexdec(substr(md5($staff->name), 0, 4)) % 360 }}, 65%, 45%) !important;">
                                        @if($staff->image)
                                            <img src="{{ asset($staff->image) }}" class="h-full w-full object-cover">
                                        @else
                                            <span class="text-[10px] font-extrabold text-white uppercase">
                                                {{ collect(explode(' ', $staff->name))->map(fn($n) => strtoupper(substr($n, 0, 1)))->take(2)->implode('') }}
                                            </span>
                                        @endif
                                    </div>
                                    <div class="relative">
                                        <a href="{{ route('staff.performance.show', $staff->id) }}" 
                                            class="hover:text-[#4634ff] hover:underline transition-colors decoration-2 underline-offset-4">
                                             {{ $staff->name }}
                                         </a>

                                        <!-- Hover Tooltip/Modal -->
                                        <div x-show="showTooltip" 
                                             x-transition:enter="transition ease-out duration-200"
                                             x-transition:enter-start="opacity-0 scale-95"
                                             x-transition:enter-end="opacity-100 scale-100"
                                             class="fixed z-[999] w-64 bg-white rounded-xl shadow-2xl border border-gray-100 p-4 pointer-events-none"
                                             :style="'left: ' + (mouseX + 15) + 'px; top: ' + (mouseY + 15) + 'px;'">
                                            <div class="flex flex-col items-center text-center">
                                                   <div class="h-16 w-16 rounded-full flex items-center justify-center overflow-hidden border-2 border-gray-50 shadow-sm mb-3"
                                                     style="background: hsl({{ hexdec(substr(md5($staff->name), 0, 4)) % 360 }}, 65%, 45%) !important;">
                                                    @if($staff->image)
                                                        <img src="{{ asset($staff->image) }}" class="h-full w-full object-cover">
                                                    @else
                                                        <span class="text-xl font-extrabold text-white uppercase">
                                                            {{ collect(explode(' ', $staff->name))->map(fn($n) => strtoupper(substr($n, 0, 1)))->take(2)->implode('') }}
                                                        </span>
                                                    @endif
                                                </div>
                                                <h4 class="font-bold text-[#0a1233] text-base">{{ $staff->name }}</h4>
                                                <p class="text-[11px] text-gray-500 mb-3">{{ $staff->email }}</p>
                                                
                                                <div class="w-full grid grid-cols-2 gap-2 border-t border-gray-50 pt-3">
                                                    <div class="flex flex-col">
                                                        <span class="text-[9px] font-bold text-gray-400 uppercase">Target (Monthly)</span>
                                                        <span class="text-[11px] font-bold text-gray-700">{{ formatCurrency($staff->performance->target_amount) }}</span>
                                                    </div>
                                                    <div class="flex flex-col border-l border-gray-50 pl-2">
                                                        <span class="text-[9px] font-bold text-gray-400 uppercase">Actual Sales</span>
                                                        <span class="text-[11px] font-bold text-[#0a1233]">{{ formatCurrency($staff->performance->actual_sales) }}</span>
                                                    </div>
                                                </div>
                                                <div class="mt-2 w-full">
                                                    <div class="flex justify-between items-center mb-1">
                                                        <span class="text-[9px] font-bold text-gray-400 uppercase">Progress</span>
                                                        <span class="text-[10px] font-bold text-blue-600">{{ $staff->performance->progress }}%</span>
                                                    </div>
                                                    @php
                                                        $barColor = match($staff->performance->color) {
                                                            'green' => 'bg-green-500',
                                                            'blue' => 'bg-blue-500',
                                                            'red' => 'bg-red-500',
                                                            default => 'bg-gray-500'
                                                        };
                                                    @endphp
                                                    <div class="w-full h-1.5 bg-gray-100 rounded-full overflow-hidden" x-data="{ p: {{ min($staff->performance->progress, 100) }} }">
                                                        <div class="h-full rounded-full transition-all duration-500 {{ $barColor }}" x-bind:style="{ width: p + '%' }" style="width: 0%"></div>
                                                    </div>
                                                </div>

                                                @if(isset($staff->performance->commission))
                                                <div class="mt-2.5 pt-2 w-full border-t border-gray-100 flex items-center justify-between text-[10px]">
                                                    <span class="text-gray-400 font-bold uppercase">Commission</span>
                                                    <span class="font-extrabold text-emerald-600">
                                                        {{ formatCurrency($staff->performance->commission->earned_commission) }}
                                                        <span class="text-gray-400 font-normal">/ {{ formatCurrency($staff->performance->commission->max_commission) }}</span>
                                                    </span>
                                                </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600">{{ $staff->phone }}</td>
                            <td class="px-6 py-4 text-sm text-gray-600">
                                {{ $staff->role ? ucwords($staff->role->name) : 'N/A' }}
                            </td>
                            <td class="px-6 py-4 text-sm">
                                <template x-if="userStatuses[{{ $staff->id }}] === 'banned'">
                                    <span class="px-3 py-1 rounded-full text-[11px] font-bold border border-red-500 text-red-500">
                                        Deactivated
                                    </span>
                                </template>
                                <template x-if="userStatuses[{{ $staff->id }}] === 'you'">
                                    <span class="px-3 py-1 rounded-full text-[11px] font-bold border border-blue-500 text-blue-500">
                                        You
                                    </span>
                                </template>
                                <template x-if="userStatuses[{{ $staff->id }}] === 'online'">
                                    <span class="px-3 py-1 rounded-full text-[11px] font-bold border border-green-500 text-green-500">
                                        Online
                                    </span>
                                </template>
                                <template x-if="userStatuses[{{ $staff->id }}] === 'offline' || !userStatuses[{{ $staff->id }}]">
                                    <span class="px-3 py-1 rounded-full text-[11px] font-bold border border-gray-400 text-gray-400">
                                        Offline
                                    </span>
                                </template>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-end space-x-2">
                                    @php
                                        $isSelf = $staff->id === auth()->id();
                                    @endphp

                                    <button @click="openEditModal({{ $staff->toJson() }})" class="inline-flex items-center px-3 py-1.5 bg-white border border-blue-500 text-blue-600 rounded-md hover:bg-blue-50 transition-colors text-[11px] font-bold whitespace-nowrap">
                                        <svg class="h-3.5 w-3.5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                        Edit
                                    </button>

                                    @if(!$isSelf)
                                    <button @click="openBanModal({{ $staff->toJson() }})" class="inline-flex items-center px-3 py-1.5 bg-white border border-red-500 text-red-500 rounded-md hover:bg-red-50 transition-colors text-[11px] font-bold whitespace-nowrap">
                                        <svg class="h-3.5 w-3.5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728L5.636 5.636" />
                                        </svg>
                                        {{ $staff->status ? 'Deactivate' : 'Activate' }}
                                    </button>
                                @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-10 text-center text-gray-500 italic">No staff members found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            <div class="px-6 py-4 bg-gray-50 border-t border-gray-100">
                {{ $staffs->links() }}
            </div>
        </div>

        <!-- Add Staff Modal -->
        <div x-show="addModalOpen" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 transition-opacity" aria-hidden="true">
                    <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
                </div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center">
                        <h3 class="text-lg font-bold text-[#0a1233]">Add New Staff</h3>
                        <button @click="addModalOpen = false" class="text-gray-400 hover:text-gray-600">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                    <form action="{{ route('staff.store') }}" method="POST">
                        @csrf
                        <input type="hidden" name="_form_type" value="add">
                        <div class="p-6 space-y-4">
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-1">Name <span class="text-red-500">*</span></label>
                                <input type="text" name="name" x-model="staffData.name" required class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-blue-500 text-sm">
                                @error('name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-1">Phone <span class="text-red-500">*</span></label>
                                <input type="text" name="phone" x-model="staffData.phone" required class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-blue-500 text-sm" maxlength="11" oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                                @error('phone')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-1">Email</label>
                                <input type="email" name="email" x-model="staffData.email" class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-blue-500 text-sm">
                                @error('email')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-1">Role <span class="text-red-500">*</span></label>
                                <select name="role_id" x-model="staffData.role_id" required class="custom-select w-full px-4 py-2 border border-gray-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-blue-500 text-sm appearance-none bg-no-repeat bg-[right_1rem_center] bg-[length:1em_1em]">
                                    <option value="">Select One</option>
                                    @foreach($roles as $role)
                                        <option value="{{ $role->id }}">{{ $role->name }}</option>
                                    @endforeach
                                </select>
                                @error('role_id')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-1">Password <span class="text-red-500">*</span></label>
                                <input type="password" name="password" x-model="staffData.password" required class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-blue-500 text-sm">
                                @error('password')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                            </div>
                        </div>
                        <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex justify-end">
                            <button type="submit" class="w-full py-2.5 bg-[#4634ff] text-white rounded-lg hover:bg-blue-700 transition-colors font-bold">Submit</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Edit Staff Modal -->
        <div x-show="editModalOpen" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 transition-opacity" aria-hidden="true">
                    <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
                </div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center">
                        <h3 class="text-lg font-bold text-[#0a1233]">Edit Staff</h3>
                        <button @click="editModalOpen = false" class="text-gray-400 hover:text-gray-600">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                    <form :action="'{{ route('staff.update', ['staff' => 'ID_PLACEHOLDER']) }}'.replace('ID_PLACEHOLDER', selectedStaff ? selectedStaff.id : '')" method="POST">
                        @csrf
                        <input type="hidden" name="_form_type" value="edit">
                        <div class="p-6 space-y-4">
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-1">Name <span class="text-red-500">*</span></label>
                                <input type="text" name="name" x-model="staffData.name" required class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-blue-500 text-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-1">Phone <span class="text-red-500">*</span></label>
                                <input type="text" name="phone" x-model="staffData.phone" required class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-blue-500 text-sm" maxlength="11" oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-1">Email</label>
                                <input type="email" name="email" x-model="staffData.email" class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-blue-500 text-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-1">Role <span class="text-red-500">*</span></label>
                                <select name="role_id" x-model="staffData.role_id" required class="custom-select w-full px-4 py-2 border border-gray-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-blue-500 text-sm appearance-none bg-no-repeat bg-[right_1rem_center] bg-[length:1em_1em]">
                                    <option value="">Select One</option>
                                    @foreach($roles as $role)
                                        <option value="{{ $role->id }}">{{ $role->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-1">Password</label>
                                <input type="password" name="password" x-model="staffData.password" class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-blue-500 text-sm">
                            </div>
                        </div>
                        <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex justify-end">
                            <button type="submit" class="w-full py-2.5 bg-[#4634ff] text-white rounded-lg hover:bg-blue-700 transition-colors font-bold">Update Staff</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Deactivate/Activate Confirmation Modal -->
        <div x-show="banModalOpen" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 transition-opacity" aria-hidden="true">
                    <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
                </div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full">
                    <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center">
                        <div class="flex items-center space-x-2">
                            <svg class="h-6 w-6 text-[#4634ff]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                            <h3 class="text-lg font-bold text-[#0a1233]">Confirmation Alert!</h3>
                        </div>
                        <button @click="banModalOpen = false" class="text-gray-400 hover:text-gray-600">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                    <div class="p-6">
                        <p class="text-sm text-gray-600">Are you sure to <span x-text="selectedStaff && selectedStaff.status ? 'deactivate' : 'activate'"></span> this staff?</p>
                    </div>
                    <div class="px-6 py-4 bg-gray-50 flex justify-end space-x-3">
                        <button @click="banModalOpen = false" class="px-6 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition-all text-sm font-bold">No</button>
                        <form :action="'{{ route('staff.toggle-status', ['staff' => 'ID_PLACEHOLDER']) }}'.replace('ID_PLACEHOLDER', selectedStaff ? selectedStaff.id : '')" method="POST">
                            @csrf
                            <button type="submit" class="px-6 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 transition-all text-sm font-bold">Yes</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>


    </div>

    <style>
        .custom-select {
            background-image: url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A//www.w3.org/2000/svg%22%20width%3D%22292.4%22%20height%3D%22292.4%22%3E%3Cpath%20fill%3D%22%23666%22%20d%3D%22M287%2069.4a17.6%2017.6%200%200%200-13-5.4H18.4c-5%200-9.3%201.8-12.9%205.4A17.6%2017.6%200%200%200%200%2082.2c0%205%201.8%209.3%205.4%2012.9l128%20127.9c3.6%203.6%207.8%205.4%2012.8%205.4s9.2-1.8%2012.8-5.4L287%2095c3.5-3.5%205.4-7.8%205.4-12.8%200-5-1.9-9.2-5.4-12.8z%22/%3E%3C/svg%3E');
        }
    </style>

    <script>
        function toggleSort() {
            const input = document.getElementById('sort-input');
            input.value = input.value === 'asc' ? 'desc' : 'asc';
            input.form.submit();
        }
    </script>
</x-app-layout>
