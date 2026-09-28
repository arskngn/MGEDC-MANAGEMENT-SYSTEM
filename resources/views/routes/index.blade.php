<x-app-layout>
    @php
        $routesBase = rtrim(url('/customer-routes'), '/');
    @endphp
    <div
        x-data="{
            addOpen: {{ $errors->any() && old('_form') === 'add' ? 'true' : 'false' }},
            editOpen: {{ $errors->any() && old('_form') === 'edit' ? 'true' : 'false' }},
            editId: {{ old('route_id') ? (int) old('route_id') : 'null' }},
            editName: @js(old('name', '')),
            editFrom: @js(old('from_location', '')),
            editTo: @js(old('to_location', '')),
            editDesc: @js(old('description', '')),
            editStatus: @js(old('status', 'active')),
            deleteOpen: false,
            deleteAction: '',
            deleteName: '',
            openEdit(id, name, from, to, desc, status) {
                this.editId = id;
                this.editName = name;
                this.editFrom = from;
                this.editTo = to;
                this.editDesc = desc;
                this.editStatus = status;
                this.editOpen = true;
            },
            openDelete(action, name) {
                this.deleteAction = action;
                this.deleteName = name;
                this.deleteOpen = true;
            },
        }"
        @keydown.escape.window="addOpen = false; editOpen = false; deleteOpen = false"
        class="space-y-4"
    >
        <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
            <h2 class="text-xl font-bold text-[#0a1233] dark:text-gray-100 shrink-0">Delivery Routes</h2>

            <div class="flex flex-wrap items-center gap-2 lg:gap-2 lg:ml-auto lg:justify-end">
                <form method="GET" action="{{ route('customer-routes.index') }}" class="inline-flex flex-wrap items-center gap-2">
                    <div class="relative flex w-full sm:w-[280px] shrink-0">
                        <input
                            type="text"
                            name="search"
                            value="{{ request('search') }}"
                            placeholder="Search route, from, to..."
                            title="Search by route name, from or to location"
                            class="w-full pl-4 pr-10 py-2 border border-[#5542ff]/40 dark:border-gray-600 rounded-l-md focus:outline-none focus:ring-1 focus:ring-[#5542ff] focus:border-[#5542ff] text-sm bg-white dark:bg-gray-900 text-ink-heading"
                        />
                        <button type="submit" class="px-3 gradient-bg text-white rounded-r-md hover:bg-opacity-90 transition-colors shrink-0" title="Search">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </button>
                    </div>

                    {{-- Status Filter --}}
                    <div class="relative">
                        <select name="status" class="text-sm border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-900 pl-3 pr-8 py-2 text-gray-700 dark:text-gray-100 focus:ring-1 focus:ring-[#5542ff] focus:border-[#5542ff] appearance-none cursor-pointer" onchange="this.form.submit()">
                            <option value="">All Statuses</option>
                            <option value="active" @selected(request('status') === 'active')>Active</option>
                            <option value="inactive" @selected(request('status') === 'inactive')>Inactive</option>
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-2.5 text-gray-400">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </div>
                    </div>

                    @if(request()->filled('search') || request()->filled('status'))
                        <a href="{{ route('customer-routes.index') }}" class="text-sm text-gray-500 hover:text-[#5542ff] dark:text-gray-400 dark:hover:text-[#5542ff] px-2">Clear</a>
                    @endif
                </form>

                <div class="flex flex-wrap items-center gap-2 shrink-0">
                    <button type="button" @click="addOpen = true" class="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-800 border border-[#5542ff] text-[#5542ff] dark:text-indigo-400 rounded-md hover:bg-[#5542ff]/5 dark:hover:bg-gray-700 transition-colors text-sm font-semibold">
                        <svg class="h-4 w-4 mr-1.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Add Route
                    </button>
                </div>
            </div>
        </div>

        {{-- Active Filters Chips Bar --}}
        @if(request()->hasAny(['search', 'status']))
            <div class="flex flex-wrap items-center gap-2 px-1 py-1">
                <span class="text-xs font-semibold text-ink-muted">Active Filters:</span>
                
                @if(request('search'))
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium bg-[#5542ff]/10 text-[#5542ff] dark:bg-[#5542ff]/20 dark:text-indigo-300 border border-[#5542ff]/20">
                        Search: "{{ request('search') }}"
                        <a href="{{ request()->fullUrlWithQuery(['search' => null]) }}" class="hover:text-red-500 font-bold ml-1" title="Remove search filter">&times;</a>
                    </span>
                @endif

                @if(request('status'))
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium bg-[#5542ff]/10 text-[#5542ff] dark:bg-[#5542ff]/20 dark:text-indigo-300 border border-[#5542ff]/20">
                        Status: {{ ucfirst(request('status')) }}
                        <a href="{{ request()->fullUrlWithQuery(['status' => null]) }}" class="hover:text-red-500 font-bold ml-1" title="Remove status filter">&times;</a>
                    </span>
                @endif

                <a href="{{ route('customer-routes.index') }}" class="text-xs text-red-600 dark:text-red-400 hover:underline font-semibold ml-1">
                    Reset All Filters
                </a>
            </div>
        @endif

        @if(session('success'))
            <div class="rounded-lg bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-700 text-green-800 dark:text-green-300 px-4 py-3 text-sm">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="rounded-lg bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-700 text-red-800 dark:text-red-300 px-4 py-3 text-sm">{{ session('error') }}</div>
        @endif

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full border-collapse min-w-[760px]">
                    <thead>
                        <tr class="gradient-bg text-white">
                            <th class="px-4 py-4 text-[11px] font-bold uppercase tracking-wide text-left">S.N.</th>
                            <th class="px-4 py-4 text-[11px] font-bold uppercase tracking-wide text-left">Route Name</th>
                            <th class="px-4 py-4 text-[11px] font-bold uppercase tracking-wide text-left">From (Origin)</th>
                            <th class="px-4 py-4 text-[11px] font-bold uppercase tracking-wide text-left">To (Destination)</th>
                            <th class="px-4 py-4 text-[11px] font-bold uppercase tracking-wide text-center">Customers</th>
                            <th class="px-4 py-4 text-[11px] font-bold uppercase tracking-wide text-center">Status</th>
                            <th class="px-4 py-4 text-[11px] font-bold uppercase tracking-wide text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($routes as $route)
                            @php
                                $sn = ($routes->currentPage() - 1) * $routes->perPage() + $loop->iteration;
                                $statusClasses = [
                                    'active' => 'bg-green-100 text-green-800 border-green-200 dark:bg-green-900/30 dark:text-green-300 dark:border-green-700',
                                    'inactive' => 'bg-gray-100 text-gray-800 border-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600',
                                ];
                            @endphp
                            <tr class="hover:bg-gray-50/90 dark:hover:bg-gray-700/50 transition-colors bg-white dark:bg-gray-800">
                                <td class="px-4 py-4 text-sm text-ink-body align-middle">{{ $sn }}</td>
                                <td class="px-4 py-4 text-sm font-bold text-[#5542ff] dark:text-indigo-400 align-middle">
                                    {{ $route->name ?: ($route->from_location . ' → ' . $route->to_location) }}
                                    @if($route->description)
                                        <div class="text-xs font-normal text-gray-400 mt-0.5">{{ $route->description }}</div>
                                    @endif
                                </td>
                                <td class="px-4 py-4 text-sm text-ink-body align-middle">
                                    <div class="flex items-center gap-1.5">
                                        <span class="h-2 w-2 rounded-full bg-emerald-500 shrink-0"></span>
                                        <span class="font-medium">{{ $route->from_location }}</span>
                                    </div>
                                </td>
                                <td class="px-4 py-4 text-sm text-ink-body align-middle">
                                    <div class="flex items-center gap-1.5">
                                        <span class="h-2 w-2 rounded-full bg-indigo-500 shrink-0"></span>
                                        <span class="font-medium">{{ $route->to_location }}</span>
                                    </div>
                                </td>
                                <td class="px-4 py-4 text-sm text-ink-body align-middle text-center">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-[#5542ff]/10 text-[#5542ff] dark:bg-indigo-900/30 dark:text-indigo-300">
                                        {{ $route->customers_count }}
                                    </span>
                                </td>
                                <td class="px-4 py-4 text-sm align-middle text-center">
                                    <span class="px-2.5 py-1 rounded-full text-xs font-bold border {{ $statusClasses[$route->status] ?? $statusClasses['active'] }}">
                                        {{ ucfirst($route->status) }}
                                    </span>
                                </td>
                                <td class="px-4 py-4 align-middle">
                                    <div class="flex flex-wrap items-center justify-center gap-2">
                                        <button
                                            type="button"
                                            class="inline-flex items-center justify-center px-2.5 py-1.5 bg-white dark:bg-gray-700 border border-[#5542ff] dark:border-gray-600 text-[#5542ff] dark:text-gray-100 rounded-md hover:bg-[#5542ff]/5 dark:hover:bg-gray-600 text-xs font-semibold"
                                            @click="openEdit({{ $route->id }}, @js($route->name), @js($route->from_location), @js($route->to_location), @js($route->description ?? ''), @js($route->status))"
                                        >
                                            <svg class="h-3.5 w-3.5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                            Edit
                                        </button>

                                        @if($route->customers_count > 0)
                                            <button
                                                type="button"
                                                disabled
                                                title="Cannot delete: this route has {{ $route->customers_count }} customer(s) assigned to it."
                                                class="inline-flex items-center justify-center px-2.5 py-1.5 bg-gray-100 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 text-gray-400 dark:text-gray-500 rounded-md text-xs font-semibold cursor-not-allowed opacity-60"
                                            >
                                                <svg class="h-3.5 w-3.5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                                Delete
                                            </button>
                                        @else
                                            <button
                                                type="button"
                                                class="inline-flex items-center justify-center px-2.5 py-1.5 bg-white dark:bg-gray-700 border border-red-500 text-red-600 dark:text-red-400 rounded-md hover:bg-red-50 dark:hover:bg-red-950/30 text-xs font-semibold"
                                                @click="openDelete(`{{ $routesBase }}/{{ $route->id }}`, @js($route->name ?: ($route->from_location . ' → ' . $route->to_location)))"
                                            >
                                                <svg class="h-3.5 w-3.5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                                Delete
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-12 text-center text-ink-muted">No delivery routes found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($routes->hasPages())
                <div class="px-4 py-3 bg-gray-50 dark:bg-gray-900 border-t border-gray-100 dark:border-gray-700">
                    {{ $routes->links() }}
                </div>
            @endif
        </div>

        {{-- Add route modal --}}
        <div x-show="addOpen" x-cloak class="fixed inset-0 z-[210] overflow-y-auto" style="display: none;">
            <div class="flex items-center justify-center min-h-screen px-4 py-8">
                <div class="fixed inset-0 bg-gray-900/50 z-0" @click="addOpen = false"></div>
                <div class="relative bg-white dark:bg-gray-800 rounded-xl shadow-xl max-w-lg w-full z-10 border border-gray-100 dark:border-gray-700" @click.stop>
                    <div class="flex justify-between items-center px-6 pt-5 pb-3 border-b border-gray-100 dark:border-gray-700">
                        <div class="flex items-center gap-2">
                            <div class="p-2 rounded-lg bg-[#5542ff]/10 text-[#5542ff] dark:bg-indigo-900/40 dark:text-indigo-400">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-[#0a1233] dark:text-gray-100">Add New Delivery Route</h3>
                                <p class="text-xs text-ink-muted">Set route origin & destination points</p>
                            </div>
                        </div>
                        <button type="button" @click="addOpen = false" class="text-gray-400 hover:text-gray-600 p-1" aria-label="Close">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                        </button>
                    </div>

                    <form method="POST" action="{{ route('customer-routes.store') }}" class="px-6 py-4 space-y-4">
                        @csrf
                        <input type="hidden" name="_form" value="add" />

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wide mb-1">From (Origin) <span class="text-red-500">*</span></label>
                                <input
                                    type="text"
                                    name="from_location"
                                    value="{{ old('_form') === 'add' ? old('from_location') : '' }}"
                                    required
                                    placeholder="e.g. Manila Warehouse, District 1"
                                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-900 text-ink-heading focus:ring-1 focus:ring-[#5542ff] focus:border-[#5542ff] text-sm"
                                />
                                @error('from_location')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wide mb-1">To (Destination) <span class="text-red-500">*</span></label>
                                <input
                                    type="text"
                                    name="to_location"
                                    value="{{ old('_form') === 'add' ? old('to_location') : '' }}"
                                    required
                                    placeholder="e.g. Quezon City, North Sector"
                                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-900 text-ink-heading focus:ring-1 focus:ring-[#5542ff] focus:border-[#5542ff] text-sm"
                                />
                                @error('to_location')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wide mb-1">Route Name (Optional)</label>
                            <input
                                type="text"
                                name="name"
                                value="{{ old('_form') === 'add' ? old('name') : '' }}"
                                placeholder="Leave blank to auto-generate (From → To)"
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-900 text-ink-heading focus:ring-1 focus:ring-[#5542ff] focus:border-[#5542ff] text-sm"
                            />
                            @error('name')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wide mb-1">Status <span class="text-red-500">*</span></label>
                                <select name="status" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-900 text-ink-heading focus:ring-1 focus:ring-[#5542ff] focus:border-[#5542ff] text-sm cursor-pointer">
                                    <option value="active" {{ old('status', 'active') === 'active' ? 'selected' : '' }}>Active</option>
                                    <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wide mb-1">Description / Notes</label>
                                <input
                                    type="text"
                                    name="description"
                                    value="{{ old('_form') === 'add' ? old('description') : '' }}"
                                    placeholder="Optional notes or schedule info"
                                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-900 text-ink-heading focus:ring-1 focus:ring-[#5542ff] focus:border-[#5542ff] text-sm"
                                />
                            </div>
                        </div>

                        <div class="pt-2 border-t border-gray-100 dark:border-gray-700 pb-1">
                            <button type="submit" class="w-full py-2.5 rounded-lg bg-[#5542ff] text-white text-sm font-semibold hover:bg-[#4736d6] transition-colors shadow-md">Create Route</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Edit route modal --}}
        <div x-show="editOpen" x-cloak class="fixed inset-0 z-[210] overflow-y-auto" style="display: none;">
            <div class="flex items-center justify-center min-h-screen px-4 py-8">
                <div class="fixed inset-0 bg-gray-900/50 z-0" @click="editOpen = false"></div>
                <div class="relative bg-white dark:bg-gray-800 rounded-xl shadow-xl max-w-lg w-full z-10 border border-gray-100 dark:border-gray-700" @click.stop>
                    <div class="flex justify-between items-center px-6 pt-5 pb-3 border-b border-gray-100 dark:border-gray-700">
                        <div class="flex items-center gap-2">
                            <div class="p-2 rounded-lg bg-[#5542ff]/10 text-[#5542ff] dark:bg-indigo-900/40 dark:text-indigo-400">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-[#0a1233] dark:text-gray-100">Edit Delivery Route</h3>
                                <p class="text-xs text-ink-muted">Update route information</p>
                            </div>
                        </div>
                        <button type="button" @click="editOpen = false" class="text-gray-400 hover:text-gray-600 p-1" aria-label="Close">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                        </button>
                    </div>

                    <form method="POST" :action="`{{ $routesBase }}/${editId}`" class="px-6 py-4 space-y-4">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="_form" value="edit" />
                        <input type="hidden" name="route_id" x-bind:value="editId" />

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wide mb-1">From (Origin) <span class="text-red-500">*</span></label>
                                <input
                                    type="text"
                                    name="from_location"
                                    x-model="editFrom"
                                    required
                                    placeholder="e.g. Manila Warehouse"
                                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-900 text-ink-heading focus:ring-1 focus:ring-[#5542ff] focus:border-[#5542ff] text-sm"
                                />
                                @error('from_location')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wide mb-1">To (Destination) <span class="text-red-500">*</span></label>
                                <input
                                    type="text"
                                    name="to_location"
                                    x-model="editTo"
                                    required
                                    placeholder="e.g. Quezon City"
                                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-900 text-ink-heading focus:ring-1 focus:ring-[#5542ff] focus:border-[#5542ff] text-sm"
                                />
                                @error('to_location')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wide mb-1">Route Name</label>
                            <input
                                type="text"
                                name="name"
                                x-model="editName"
                                placeholder="Route name (or auto-generates From → To)"
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-900 text-ink-heading focus:ring-1 focus:ring-[#5542ff] focus:border-[#5542ff] text-sm"
                            />
                            @error('name')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wide mb-1">Status <span class="text-red-500">*</span></label>
                                <select name="status" x-model="editStatus" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-900 text-ink-heading focus:ring-1 focus:ring-[#5542ff] focus:border-[#5542ff] text-sm cursor-pointer">
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wide mb-1">Description / Notes</label>
                                <input
                                    type="text"
                                    name="description"
                                    x-model="editDesc"
                                    placeholder="Optional notes or schedule info"
                                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-900 text-ink-heading focus:ring-1 focus:ring-[#5542ff] focus:border-[#5542ff] text-sm"
                                />
                            </div>
                        </div>

                        <div class="pt-2 border-t border-gray-100 dark:border-gray-700 pb-1">
                            <button type="submit" class="w-full py-2.5 rounded-lg bg-[#5542ff] text-white text-sm font-semibold hover:bg-[#4736d6] transition-colors shadow-md">Save Changes</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <x-delete-modal />
    </div>
</x-app-layout>
