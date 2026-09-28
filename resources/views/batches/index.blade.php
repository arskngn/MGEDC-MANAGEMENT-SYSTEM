<x-app-layout>
    <div
        x-data="{
            addBatchOpen: false,
            disposeOpen: false,
            selectedBatch: null,
            disposeQty: 0,
            disposeType: 'expired',
            disposeReason: '',
            disposeMethod: '',
            maxDisposeQty: 0,
            productsData: {{ Js::from($products->keyBy('id')) }},
            selectedProductId: null,
            currentProduct: null,
            batchNumberRequired: false,
            expiryFieldsVisible: false,
            expiryDateRequired: false,
            alertDaysRequired: false,
            shelfLifeDays: 0,
            manufacturedDate: '',
            expirationDate: '',
            alertDaysBeforeExpiry: 30,

            init() {
                this.$watch('addBatchOpen', (open) => {
                    if (!open) {
                        this.resetAddBatchForm();
                    }
                });
                this.$watch('selectedProductId', (productId) => {
                    this.updateBatchFormFields(productId);
                });
            },

            resetAddBatchForm() {
                this.selectedProductId = null;
                this.currentProduct = null;
                this.batchNumberRequired = false;
                this.expiryFieldsVisible = false;
                this.expirationDate = '';
            },

            updateBatchFormFields(productId) {
                const product = this.productsData[productId];
                if (product) {
                    this.currentProduct = product;
                    this.batchNumberRequired = product.requires_batch_tracking;
                    this.expiryFieldsVisible = product.has_expiry;
                } else {
                    this.resetAddBatchForm();
                }
            },
            deleteOpen: false,
            deleteAction: '',
            deleteName: '',
            openDelete(action, name) {
                this.deleteAction = action;
                this.deleteName = name;
                this.deleteOpen = true;
            }
        }"
        @keydown.escape.window="addBatchOpen = false; disposeOpen = false; deleteOpen = false"
        class="space-y-4"
    >
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="text-xl font-bold text-[#0a1233] dark:text-gray-100 shrink-0">Batch Management</h2>

            <div class="flex flex-col sm:flex-row sm:items-center gap-2.5 w-full sm:w-auto">
                <form
                    method="GET"
                    action="{{ route('batches.index') }}"
                    x-data="{ mobileFiltersOpen: false }"
                    class="w-full sm:flex sm:flex-row sm:items-center sm:gap-2 sm:w-auto"
                >
                    {{-- Mobile / tablet collapsible toolbar row --}}
                    <div class="flex items-center gap-2 sm:contents">
                        {{-- Search --}}
                        <div class="relative flex flex-1 sm:w-[240px] sm:flex-none">
                            <input
                                type="text"
                                name="search"
                                value="{{ request('search') }}"
                                placeholder="Search Batch # or Product..."
                                class="w-full pl-3 pr-8 py-2 border border-[#5542ff]/40 dark:border-gray-600 rounded-l-xl sm:rounded-l-md focus:outline-none focus:ring-1 focus:ring-[#5542ff] focus:border-[#5542ff] text-sm bg-white dark:bg-gray-900 text-ink-heading placeholder-gray-400 dark:placeholder-gray-500"
                            />
                            @if(request('search'))
                                <a href="{{ request()->fullUrlWithQuery(['search' => null]) }}" class="absolute right-9 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 text-xs px-1 h-4 w-4 flex items-center justify-center" title="Clear search">&#x2715;</a>
                            @endif
                            <button type="submit" class="px-3 gradient-bg text-white rounded-r-xl sm:rounded-r-md hover:opacity-90 transition-colors flex items-center justify-center shrink-0" title="Search">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                            </button>
                        </div>

                        {{-- Filter toggle button with active-count badge (mobile only) --}}
                        @php
                            $activeFilters = (int)(bool)request('product_id') + (int)(bool)request('status');
                        @endphp
                        <button type="button" @click="mobileFiltersOpen = !mobileFiltersOpen"
                                class="sm:hidden relative flex items-center gap-1.5 px-3 py-2 rounded-xl border text-sm font-semibold transition-all shrink-0"
                                :class="mobileFiltersOpen ? 'bg-[#5542ff] text-white border-[#5542ff]' : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 border-gray-300 dark:border-gray-600'">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z"/></svg>
                            <span>Filter</span>
                            @if($activeFilters > 0)
                                <span class="absolute -top-1.5 -right-1.5 h-4 w-4 flex items-center justify-center rounded-full bg-red-500 text-white text-[9px] font-black">{{ $activeFilters }}</span>
                            @endif
                        </button>
                    </div>

                    {{-- Filter dropdowns: always visible on desktop, toggle on mobile --}}
                    <div x-show="mobileFiltersOpen" x-cloak class="sm:!block sm:flex sm:flex-row sm:items-center sm:gap-2" style="display:none">
                        <div class="grid grid-cols-2 gap-2 mt-2 sm:mt-0 sm:flex sm:flex-row sm:gap-2">
                            {{-- Product Filter --}}
                            <div class="relative w-full sm:w-auto">
                                <select name="product_id" class="w-full sm:w-auto text-sm border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-900 pl-3 pr-8 py-2 text-gray-700 dark:text-gray-100 focus:ring-1 focus:ring-[#5542ff] focus:border-[#5542ff] appearance-none cursor-pointer shadow-2xs transition-colors" onchange="this.form.submit()">
                                    <option value="">All Products</option>
                                    @foreach($products as $prod)
                                        <option value="{{ $prod->id }}" @selected(request('product_id') == $prod->id)>{{ $prod->name }}</option>
                                    @endforeach
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-2.5 text-ink-muted">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </div>
                            </div>

                            {{-- Status Filter --}}
                            <div class="relative w-full sm:w-auto">
                                <select name="status" class="w-full sm:w-auto text-sm border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-900 pl-3 pr-8 py-2 text-gray-700 dark:text-gray-100 focus:ring-1 focus:ring-[#5542ff] focus:border-[#5542ff] appearance-none cursor-pointer shadow-2xs transition-colors" onchange="this.form.submit()">
                                    <option value="">All Statuses</option>
                                    @foreach(\App\Models\ProductBatch::getStatusOptions() as $val => $lbl)
                                        <option value="{{ $val }}" @selected(request('status') == $val)>{{ $lbl }}</option>
                                    @endforeach
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-2.5 text-ink-muted">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>

                <div class="flex items-center gap-2 shrink-0 justify-end">
                    <button type="button" @click="addBatchOpen = true" class="inline-flex items-center justify-center px-3 sm:px-4 py-2 bg-white dark:bg-gray-800 border border-[#5542ff] text-[#5542ff] dark:text-gray-100 dark:border-gray-600 rounded-xl sm:rounded-md hover:bg-[#5542ff]/5 dark:hover:bg-gray-700 transition-colors text-sm font-semibold whitespace-nowrap">
                        <svg class="h-4 w-4 sm:mr-1.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        <span class="hidden sm:inline">Add Batch</span>
                        <span class="sm:hidden">Add</span>
                    </button>
                </div>
            </div>
        </div>

        {{-- Active Filters Chips Bar --}}
        @if(request()->hasAny(['search', 'product_id', 'status']))
            <div class="flex flex-wrap items-center gap-2 px-1 py-1">
                <span class="text-xs font-semibold text-ink-muted">Active Filters:</span>
                
                @if(request('search'))
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium bg-[#5542ff]/10 text-[#5542ff] dark:bg-[#5542ff]/20 dark:text-indigo-300 border border-[#5542ff]/20">
                        Search: "{{ request('search') }}"
                        <a href="{{ request()->fullUrlWithQuery(['search' => null]) }}" class="hover:text-red-500 font-bold ml-1" title="Remove search filter">&times;</a>
                    </span>
                @endif

                @if(request('product_id') && $selectedProd = $products->firstWhere('id', request('product_id')))
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium bg-[#5542ff]/10 text-[#5542ff] dark:bg-[#5542ff]/20 dark:text-indigo-300 border border-[#5542ff]/20">
                        Product: {{ $selectedProd->name }}
                        <a href="{{ request()->fullUrlWithQuery(['product_id' => null]) }}" class="hover:text-red-500 font-bold ml-1" title="Remove product filter">&times;</a>
                    </span>
                @endif

                @if(request('status'))
                    @php $statusOptions = \App\Models\ProductBatch::getStatusOptions(); @endphp
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium bg-[#5542ff]/10 text-[#5542ff] dark:bg-[#5542ff]/20 dark:text-indigo-300 border border-[#5542ff]/20">
                        Status: {{ $statusOptions[request('status')] ?? ucfirst(request('status')) }}
                        <a href="{{ request()->fullUrlWithQuery(['status' => null]) }}" class="hover:text-red-500 font-bold ml-1" title="Remove status filter">&times;</a>
                    </span>
                @endif

                <a href="{{ route('batches.index') }}" class="text-xs text-red-600 dark:text-red-400 hover:underline font-semibold ml-1">
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

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-center border-collapse min-w-[1000px]">
                    <thead>
                        <tr class="gradient-bg text-white">
                            <th class="px-4 py-3.5 text-[11px] font-bold uppercase tracking-wide">Batch Number</th>
                            <th class="px-4 py-3.5 text-[11px] font-bold uppercase tracking-wide">Product</th>

                            <th class="px-4 py-3.5 text-[11px] font-bold uppercase tracking-wide">Available / Total</th>
                            <th class="px-4 py-3.5 text-[11px] font-bold uppercase tracking-wide">Expiry Date</th>
                            <th class="px-4 py-3.5 text-[11px] font-bold uppercase tracking-wide">Status</th>
                            <th class="px-4 py-3.5 text-[11px] font-bold uppercase tracking-wide">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($batches as $batch)
                            <tr class="hover:bg-gray-50/90 transition-colors bg-white">
                                <td class="px-4 py-3.5 align-middle">
                                    <div class="text-sm font-bold text-[#5542ff]">{{ $batch->batch_number }}</div>
                                </td>
                                <td class="px-4 py-3.5 align-middle">
                                    <div class="flex flex-col items-center">
                                        <div class="text-sm font-bold text-gray-800">{{ $batch->product->name }}</div>
                                        <div class="text-xs text-gray-500">{{ $batch->product->sku }}</div>
                                    </div>
                                </td>

                                <td class="px-4 py-3.5 align-middle">
                                    <div class="flex flex-col items-center">
                                        <div class="text-sm font-bold {{ $batch->quantity_available <= 5 ? 'text-red-600' : 'text-green-600' }}">
                                            {{ number_format($batch->quantity_available, 2) }}
                                        </div>
                                        <div class="text-xs text-gray-400">of {{ number_format($batch->quantity, 2) }}</div>
                                    </div>
                                </td>
                                {{-- Expiry Date --}}
                                <td class="px-4 py-3.5 align-middle">
                                    @if($batch->expiry_date)
                                        @php
                                            $daysLeft = now()->startOfDay()->diffInDays($batch->expiry_date->startOfDay(), false);
                                        @endphp
                                        <div class="flex flex-col items-center">
                                            <span class="text-sm font-bold
                                                {{ $daysLeft < 0 ? 'text-red-600' : ($daysLeft <= 30 ? 'text-amber-600' : 'text-gray-700') }}">
                                                {{ $batch->expiry_date->format('d M Y') }}
                                            </span>
                                            @if($daysLeft < 0)
                                                <span class="text-[10px] text-red-500 font-semibold">Expired</span>
                                            @elseif($daysLeft <= 30)
                                                <span class="text-[10px] text-amber-500 font-semibold">{{ $daysLeft }}d left</span>
                                            @endif
                                        </div>
                                    @else
                                        <span class="text-gray-300">—</span>
                                    @endif
                                </td>
                                {{-- Status --}}
                                <td class="px-4 py-3.5 align-middle">
                                    @php
                                        $statusClasses = [
                                            'active' => 'bg-green-100 text-green-800 border-green-200',
                                            'expired' => 'bg-red-100 text-red-800 border-red-200',
                                            'depleted' => 'bg-gray-100 text-gray-800 border-gray-200',
                                            'hold' => 'bg-amber-100 text-amber-800 border-amber-200',
                                            'quarantined' => 'bg-purple-100 text-purple-800 border-purple-200',
                                        ];
                                    @endphp
                                    <span class="px-2.5 py-1 rounded-full text-xs font-bold border {{ $statusClasses[$batch->status] ?? $statusClasses['active'] }}">
                                        {{ ucfirst($batch->status) }}
                                    </span>
                                </td>
                                {{-- Action --}}
                                <td class="px-4 py-3.5 align-middle">
                                    <div class="flex justify-center gap-2">
                                        <a href="{{ route('batches.show', $batch) }}" class="p-1.5 text-blue-600 hover:bg-blue-50 rounded-md transition-colors" title="View Details">
                                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                                        </a>
                                        @if($batch->quantity_available > 0)
                                            <button
                                                @click="selectedBatch = {{ $batch->id }}; maxDisposeQty = {{ $batch->quantity_available }}; disposeOpen = true"
                                                class="p-1.5 text-orange-600 hover:bg-orange-50 rounded-md transition-colors"
                                                title="Dispose / Adjustment"
                                            >
                                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                            </button>
                                        @endif

                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-12 text-center text-gray-500">No batches found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($batches->hasPages())
                <div class="px-4 py-3 bg-gray-50 dark:bg-gray-900 border-t border-gray-100 dark:border-gray-700">
                    {{ $batches->links() }}
                </div>
            @endif
        </div>

        {{-- Add Batch Modal --}}
        <div x-show="addBatchOpen" x-cloak class="fixed inset-0 z-[210] overflow-y-auto" style="display: none;">
            <div class="flex items-center justify-center min-h-screen px-4 py-8">
                <div class="fixed inset-0 bg-gray-900/50 z-0" @click="addBatchOpen = false"></div>
                <div class="relative bg-white dark:bg-gray-800 rounded-xl shadow-xl max-w-2xl w-full p-6 z-10 border border-gray-100 dark:border-gray-700" @click.stop>
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-lg font-bold text-[#0a1233]">Add New Product Batch</h3>
                        <button type="button" @click="addBatchOpen = false" class="text-gray-400 hover:text-gray-600">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                        </button>
                    </div>

                    <form action="{{ route('batches.store') }}" method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @csrf
                        <div class="space-y-1">
                            <label class="text-xs font-bold text-gray-700 uppercase">Product</label>
                            <select name="product_id" x-model="selectedProductId" @change="updateBatchFormFields($event.target.value)" required class="w-full text-sm border-gray-300 rounded-md focus:ring-[#5542ff] focus:border-[#5542ff]">
                                <option value="">Select Product</option>
                                @foreach($products as $product)
                                    <option value="{{ $product->id }}">{{ $product->name }} ({{ $product->sku }})</option>
                                @endforeach
                            </select>
                        </div>


                        <div class="space-y-1" x-show="batchNumberRequired">
                            <label class="text-xs font-bold text-gray-700 uppercase">Batch Number</label>
                            <input type="text" name="batch_number" x-bind:required="batchNumberRequired" class="w-full text-sm border-gray-300 rounded-md focus:ring-[#5542ff] focus:border-[#5542ff]" placeholder="e.g. LOT-2024-001">
                        </div>

                        <div class="space-y-1">
                            <label class="text-xs font-bold text-gray-700 uppercase">Quantity</label>
                            <input type="number" name="quantity" required step="0.01" min="0" class="w-full text-sm border-gray-300 rounded-md focus:ring-[#5542ff] focus:border-[#5542ff]">
                        </div>

                        <div class="space-y-1" x-show="expiryFieldsVisible">
                            <label class="text-xs font-bold text-gray-700 uppercase">Expiry Date <span class="text-red-500" x-show="expiryFieldsVisible">*</span></label>
                            <input
                                type="date"
                                name="expiry_date"
                                min="{{ date('Y-m-d') }}"
                                x-model="expirationDate"
                                x-bind:required="expiryFieldsVisible"
                                class="w-full text-sm border-gray-300 rounded-md focus:ring-[#5542ff] focus:border-[#5542ff]"
                            >
                        </div>

                        <div class="col-span-full pt-4">
                            <button type="submit" class="w-full py-2.5 bg-[#5542ff] text-white rounded-md font-bold hover:bg-[#4736d6] transition-colors shadow-sm">
                                Create Batch
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Dispose Modal --}}
        <div x-show="disposeOpen" x-cloak class="fixed inset-0 z-[210] overflow-y-auto" style="display: none;">
            <div class="flex items-center justify-center min-h-screen px-4 py-8">
                <div class="fixed inset-0 bg-gray-900/50 z-0" @click="disposeOpen = false"></div>
                <div class="relative bg-white rounded-xl shadow-xl max-w-lg w-full p-6 z-10 border border-gray-100" @click.stop>
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-lg font-bold text-[#0a1233]">Stock Disposal / Adjustment</h3>
                        <button type="button" @click="disposeOpen = false" class="text-gray-400 hover:text-gray-600">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                        </button>
                    </div>

                    <form :action="'/batches/' + selectedBatch + '/dispose'" method="POST" class="space-y-4">
                        @csrf
                        <div class="space-y-1">
                            <label class="text-xs font-bold text-gray-700 uppercase">Quantity to Dispose</label>
                            <div class="relative">
                                <input type="number" name="quantity_disposed" required step="0.01" min="0.01" :max="maxDisposeQty" x-model="disposeQty" class="w-full text-sm border-gray-300 rounded-md focus:ring-[#5542ff] focus:border-[#5542ff]">
                                <span class="absolute right-3 top-2 text-xs text-gray-400">Max: <span x-text="maxDisposeQty"></span></span>
                            </div>
                        </div>

                        <div class="space-y-1">
                            <label class="text-xs font-bold text-gray-700 uppercase">Reason Type</label>
                            <select name="disposal_type" required x-model="disposeType" class="w-full text-sm border-gray-300 rounded-md focus:ring-[#5542ff] focus:border-[#5542ff]">
                                <option value="expired">Expired Stock</option>
                                <option value="damaged_in_storage">Damaged in Storage</option>
                                <option value="spoiled">Spoiled / Degraded</option>
                                <option value="recall">Batch Recall</option>
                            </select>
                        </div>

                        <div class="space-y-1">
                            <label class="text-xs font-bold text-gray-700 uppercase">Disposal Method</label>
                            <input type="text" name="disposal_method" x-model="disposeMethod" class="w-full text-sm border-gray-300 rounded-md focus:ring-[#5542ff] focus:border-[#5542ff]" placeholder="e.g. Incineration, Return to Supplier">
                        </div>

                        <div class="space-y-1">
                            <label class="text-xs font-bold text-gray-700 uppercase">Reason Notes</label>
                            <textarea name="reason_notes" x-model="disposeReason" class="w-full text-sm border-gray-300 rounded-md focus:ring-[#5542ff] focus:border-[#5542ff]" rows="3"></textarea>
                        </div>

                        <div class="pt-4">
                            <button type="submit" class="w-full py-2.5 bg-orange-600 text-white rounded-md font-bold hover:bg-orange-700 transition-colors shadow-sm">
                                Confirm Disposal
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <x-delete-modal />
    </div>
    
    <style>
        .batch-status-select option:checked {
            background-color: #F15A22;
            color: white;
        }
        
        .batch-status-select option:hover {
            background-color: #F15A22;
            color: white;
        }
    </style>
</x-app-layout>
