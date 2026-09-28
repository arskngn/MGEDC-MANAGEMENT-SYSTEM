<x-app-layout>
    @php
        $brandsBase = rtrim(url('/products/brands'), '/');
    @endphp
    <div
        x-data="{
            addOpen: {{ $errors->any() && old('_form') === 'add' ? 'true' : 'false' }},
            editOpen: {{ $errors->any() && old('_form') === 'edit' ? 'true' : 'false' }},
            importOpen: {{ $errors->has('csv_file') || ($errors->any() && old('_form') === 'import') ? 'true' : 'false' }},
            editId: {{ old('brand_id') ? (int) old('brand_id') : 'null' }},
            editName: @js(old('name', '')),
            deleteOpen: false,
            deleteAction: '',
            deleteName: '',
            openEdit(id, name) {
                this.editId = id;
                this.editName = name;
                this.editOpen = true;
            },
            openDelete(action, name) {
                this.deleteAction = action;
                this.deleteName = name;
                this.deleteOpen = true;
            },
        }"
        @keydown.escape.window="addOpen = false; editOpen = false; importOpen = false; deleteOpen = false"
        class="space-y-4"
    >
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="text-xl font-bold text-[#0a1233] dark:text-gray-100 shrink-0">Brands</h2>

            <div class="flex flex-col sm:flex-row sm:items-center gap-2.5 w-full sm:w-auto">
                <form method="GET" action="{{ route('brands.index') }}" class="w-full sm:w-auto">
                    <div class="relative flex w-full sm:w-[240px]">
                        <input
                            type="text"
                            name="search"
                            value="{{ request('search') }}"
                            placeholder="Search brands..."
                            class="w-full pl-3 pr-8 py-2 border border-[#5542ff]/40 dark:border-gray-600 rounded-l-xl sm:rounded-l-md focus:outline-none focus:ring-1 focus:ring-[#5542ff] focus:border-[#5542ff] text-sm bg-white dark:bg-gray-900 text-ink-heading placeholder-gray-400 dark:placeholder-gray-500"
                        />
                        @if(request('search'))
                            <a href="{{ request()->fullUrlWithQuery(['search' => null]) }}" class="absolute right-9 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 text-xs px-1 h-4 w-4 flex items-center justify-center" title="Clear search">&#x2715;</a>
                        @endif
                        <button type="submit" class="px-3 gradient-bg text-white rounded-r-xl sm:rounded-r-md hover:opacity-90 transition-colors flex items-center justify-center shrink-0" title="Search">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                        </button>
                    </div>
                </form>

                <div class="flex items-center gap-2 shrink-0 justify-end">
                    @if(auth()->user()->hasPermission('Store Brand'))
                        <button type="button" @click="addOpen = true" class="inline-flex items-center justify-center px-3 sm:px-4 py-2 bg-white dark:bg-gray-800 border border-[#5542ff] text-[#5542ff] dark:text-gray-100 dark:border-gray-600 rounded-xl sm:rounded-md hover:bg-[#5542ff]/5 dark:hover:bg-gray-700 transition-colors text-sm font-semibold whitespace-nowrap">
                            <svg class="h-4 w-4 sm:mr-1.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            <span class="hidden sm:inline">Add New</span>
                            <span class="sm:hidden">Add</span>
                        </button>
                    @endif
                </div>
            </div>
        </div>

        {{-- Active Filters Chips Bar --}}
        @if(request('search'))
            <div class="flex flex-wrap items-center gap-2 px-1 py-1">
                <span class="text-xs font-semibold text-ink-muted">Active Filters:</span>
                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium bg-[#5542ff]/10 text-[#5542ff] dark:bg-[#5542ff]/20 dark:text-indigo-300 border border-[#5542ff]/20">
                    Search: "{{ request('search') }}"
                    <a href="{{ request()->fullUrlWithQuery(['search' => null]) }}" class="hover:text-red-500 font-bold ml-1" title="Remove search filter">&times;</a>
                </span>
                <a href="{{ route('brands.index') }}" class="text-xs text-red-600 dark:text-red-400 hover:underline font-semibold ml-1">
                    Reset All Filters
                </a>
            </div>
        @endif

        @if(session('success'))
            <div class="rounded-lg bg-green-50 border border-green-200 text-green-800 px-4 py-3 text-sm">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="rounded-lg bg-red-50 border border-red-200 text-red-800 px-4 py-3 text-sm">{{ session('error') }}</div>
        @endif

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full border-collapse min-w-[640px]">
                    <thead>
                        <tr class="gradient-bg text-white">
                            <th class="px-4 py-4 text-[11px] font-bold uppercase tracking-wide text-left">S.N.</th>
                            <th class="px-4 py-4 text-[11px] font-bold uppercase tracking-wide text-center">Name</th>
                            <th class="px-4 py-4 text-[11px] font-bold uppercase tracking-wide text-center">Products</th>
                            <th class="px-4 py-4 text-[11px] font-bold uppercase tracking-wide text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse($brands as $brand)
                            @php
                                $sn = ($brands->currentPage() - 1) * $brands->perPage() + $loop->iteration;
                            @endphp
                            <tr class="hover:bg-gray-50/90 transition-colors bg-white">
                                <td class="px-4 py-4 text-sm text-gray-800 align-middle">{{ $sn }}</td>
                                <td class="px-4 py-4 text-sm text-gray-800 align-middle text-center">{{ $brand->name }}</td>
                                <td class="px-4 py-4 text-sm text-gray-800 align-middle text-center">{{ $brand->products_count }}</td>
                                <td class="px-4 py-4 align-middle">
                                    <div class="flex flex-wrap items-center justify-center gap-2">
                                        @if(auth()->user()->hasPermission('Store Brand'))
                                            <button
                                                type="button"
                                                @click="openEdit({{ $brand->id }}, @js($brand->name))"
                                                class="inline-flex items-center justify-center px-2.5 py-1.5 bg-white border border-[#5542ff] text-[#5542ff] rounded-md hover:bg-[#5542ff]/5 text-[11px] font-bold"
                                            >
                                                <svg class="h-3.5 w-3.5 mr-1 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                </svg>
                                                Edit
                                            </button>
                                        @endif
                                        @if(auth()->user()->hasPermission('Delete Brand'))
                                            @if($brand->products_count > 0)
                                                <span
                                                    class="inline-flex items-center justify-center px-2.5 py-1.5 bg-white border border-rose-200 text-rose-300 rounded-md text-[11px] font-bold opacity-60 cursor-not-allowed select-none"
                                                    title="Cannot delete a brand that still has products assigned."
                                                    role="presentation"
                                                >
                                                    <svg class="h-3.5 w-3.5 mr-1 shrink-0 opacity-80" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                    </svg>
                                                    Delete
                                                </span>
                                            @else
                                                <button
                                                    type="button"
                                                    @click="openDelete('{{ route('brands.destroy', $brand) }}', {{ json_encode($brand->name) }})"
                                                    class="inline-flex items-center justify-center px-2.5 py-1.5 bg-white border border-rose-500 text-rose-600 rounded-md hover:bg-rose-50 text-[11px] font-bold"
                                                >
                                                    <svg class="h-3.5 w-3.5 mr-1 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                    </svg>
                                                    Delete
                                                </button>
                                            @endif
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-12 text-center text-gray-500">No brands found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($brands->hasPages())
                <div class="px-4 py-3 bg-gray-50 border-t border-gray-100">
                    {{ $brands->links() }}
                </div>
            @endif
        </div>

        {{-- Add brand --}}
        <div x-show="addOpen" x-cloak class="fixed inset-0 z-[210] overflow-y-auto" style="display: none;">
            <div class="flex items-center justify-center min-h-screen px-4 py-8">
                <div class="fixed inset-0 bg-gray-900/50 z-0" @click="addOpen = false"></div>
                <div class="relative bg-white rounded-xl shadow-xl max-w-md w-full z-10 border border-gray-100" @click.stop>
                    <div class="flex justify-between items-center px-6 pt-5 pb-3 border-b border-gray-100">
                        <h3 class="text-lg font-bold text-[#0a1233]">Add New Brand</h3>
                        <button type="button" @click="addOpen = false" class="text-gray-400 hover:text-gray-600 p-1" aria-label="Close">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                        </button>
                    </div>
                    <form method="POST" action="{{ route('brands.store') }}" class="px-6 py-4 space-y-4">
                        @csrf
                        <input type="hidden" name="_form" value="add" />
                        <div>
                            <label for="add_name" class="block text-sm font-medium text-gray-700 mb-1">Name <span class="text-red-500">*</span></label>
                            <input
                                id="add_name"
                                type="text"
                                name="name"
                                value="{{ old('_form') === 'add' ? old('name') : '' }}"
                                required
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-1 focus:ring-[#5542ff] focus:border-[#5542ff] text-sm"
                            />
                            @error('name')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="pt-2 border-t border-gray-100 pb-1">
                            <button type="submit" class="w-full py-2.5 rounded-lg bg-[#5542ff] text-white text-sm font-semibold hover:bg-[#4736d6] transition-colors">Submit</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Edit brand --}}
        <div x-show="editOpen" x-cloak class="fixed inset-0 z-[210] overflow-y-auto" style="display: none;">
            <div class="flex items-center justify-center min-h-screen px-4 py-8">
                <div class="fixed inset-0 bg-gray-900/50 z-0" @click="editOpen = false"></div>
                <div class="relative bg-white rounded-xl shadow-xl max-w-md w-full z-10 border border-gray-100" @click.stop>
                    <div class="flex justify-between items-center px-6 pt-5 pb-3 border-b border-gray-100">
                        <h3 class="text-lg font-bold text-[#0a1233]">Edit Brand</h3>
                        <button type="button" @click="editOpen = false" class="text-gray-400 hover:text-gray-600 p-1" aria-label="Close">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                        </button>
                    </div>
                    <form method="POST" :action="`{{ $brandsBase }}/${editId}`" class="px-6 py-4 space-y-4">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="_form" value="edit" />
                        <input type="hidden" name="brand_id" x-bind:value="editId" />
                        <div>
                            <label for="edit_name" class="block text-sm font-medium text-gray-700 mb-1">Name <span class="text-red-500">*</span></label>
                            <input
                                id="edit_name"
                                type="text"
                                name="name"
                                x-model="editName"
                                required
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-1 focus:ring-[#5542ff] focus:border-[#5542ff] text-sm"
                            />
                            @error('name')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="pt-2 border-t border-gray-100 pb-1">
                            <button type="submit" class="w-full py-2.5 rounded-lg bg-[#5542ff] text-white text-sm font-semibold hover:bg-[#4736d6] transition-colors">Submit</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>


        <x-delete-modal />
    </div>
</x-app-layout>
