<x-app-layout>
    <style>
    /* Accent bars - always match system theme */
    .section-accent { display: inline-block; width: 4px; height: 20px; border-radius: 9999px; flex-shrink: 0; background: var(--gradient-from, #4634ff); }
    .section-accent-primary { background: var(--gradient-from, #4634ff); }
    .section-accent-green { background: var(--gradient-from, #4634ff); }
    .section-accent-amber { background: var(--gradient-from, #4634ff); }

    /* System-color focus ring for inputs */
    .sale-input:focus {
        border-color: var(--gradient-from, #4634ff) !important;
        box-shadow: 0 0 0 3px color-mix(in srgb, var(--gradient-from, #4634ff) 12%, transparent);
        outline: none;
    }
    .sale-input { transition: border-color 0.2s, box-shadow 0.2s; }
    /* Focus-within for search input wrappers */
    .search-wrap:focus-within {
        border-color: var(--gradient-from, #4634ff) !important;
        box-shadow: 0 0 0 3px color-mix(in srgb, var(--gradient-from, #4634ff) 10%, transparent);
    }
    /* Submit button */
    .submit-btn:not(:disabled) {
        background: linear-gradient(135deg, var(--gradient-from, #4634ff) 0%, var(--gradient-to, #5542ff) 100%);
        animation: subtlePulse 3s ease-in-out infinite;
    }
    .submit-btn:not(:disabled):hover {
        background: linear-gradient(135deg, var(--gradient-to, #5542ff) 0%, var(--gradient-from, #4634ff) 100%);
    }
    /* System-color availability pill */
    .avail-badge {
        background-color: color-mix(in srgb, var(--gradient-from, #4634ff) 10%, transparent);
        color: var(--gradient-from, #4634ff);
    }
    .avail-badge-low { background-color: #fef2f2; color: #dc2626; }
    @keyframes subtlePulse {
        0%, 100% { box-shadow: 0 0 0 0 color-mix(in srgb, var(--gradient-from, #4634ff) 30%, transparent); }
        50% { box-shadow: 0 0 0 6px color-mix(in srgb, var(--gradient-from, #4634ff) 0%, transparent); }
    }

    /* Custom scrollbar */
    .custom-scroll::-webkit-scrollbar { width: 5px; }
    .custom-scroll::-webkit-scrollbar-track { background: #f1f5f9; border-radius: 10px; }
    .custom-scroll::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
    .custom-scroll::-webkit-scrollbar-thumb:hover { background: #94a3b8; }

    /* Table row hover effect */
    .item-row { transition: background 0.15s; }

    /* ── Mobile Product Card ── */
    .mobile-product-card { transition: box-shadow 0.15s, border-color 0.15s; animation: cardIn 0.2s ease-out; }
    @keyframes cardIn { from { opacity: 0; transform: translateY(8px); } to { opacity: 1; transform: translateY(0); } }

    /* ── Mobile Step Indicator ── */
    .step-dot {
        width: 28px; height: 28px; border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        font-size: 11px; font-weight: 900; flex-shrink: 0;
        transition: all 0.3s ease;
    }
    .step-dot-done  { background: linear-gradient(135deg, var(--gradient-from, #4634ff) 0%, var(--gradient-to, #5542ff) 100%); color: #fff; }
    .step-dot-active { background: color-mix(in srgb, var(--gradient-from, #4634ff) 15%, transparent); color: var(--gradient-from, #4634ff); border: 2px solid var(--gradient-from, #4634ff); }
    .step-dot-pending { background: #e5e7eb; color: #9ca3af; }
    .dark .step-dot-pending { background: #374151; color: #6b7280; }
    .step-line { height: 2px; flex: 1; border-radius: 9999px; transition: background 0.4s ease; }
    .step-line-done { background: linear-gradient(90deg, var(--gradient-from, #4634ff), var(--gradient-to, #5542ff)); }
    .step-line-pending { background: #e5e7eb; }
    .dark .step-line-pending { background: #374151; }

    /* ── Sticky Mobile Total Bar ── */
    .mobile-total-bar {
        backdrop-filter: blur(16px);
        -webkit-backdrop-filter: blur(16px);
    }

    /* ── Mobile Touch Targets ── */
    @media (max-width: 639px) {
        input, select, textarea { font-size: 16px !important; }
        .mobile-input { min-height: 48px !important; }
        button { min-height: 44px; }
    }
    </style>

    <script type="application/json" id="sale-customers">@json($customers)</script>

    <div
        class="max-w-7xl mx-auto space-y-5 px-3 sm:px-0"
        x-data="salesForm({
            searchProductsUrl: '{{ route('sales.products.search') }}',
            discount: {{ old('discount', 0) ?: 0 }}
        })"
    >
        {{-- ══ Page Header ══ --}}
        <div class="flex justify-between items-center">
            <div>
                <h2 class="text-xl font-black text-gray-900 dark:text-white tracking-tight">New Sale</h2>
                <p class="text-xs text-ink-muted mt-0.5">Fill in the details below to create a new sales invoice.</p>
            </div>
            <a href="{{ route('sales.all') }}" class="inline-flex items-center gap-1.5 px-4 py-2 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl text-sm font-semibold text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 hover:border-gray-300 shadow-sm transition-all duration-200">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                <span class="hidden sm:inline">Back to Sales</span>
                <span class="sm:hidden">Back</span>
            </a>
        </div>

        {{-- ══ Mobile Step Indicator ══ --}}
        <div class="@if(auth()->user()->hasRole('salesperson'))lg:hidden @else sm:hidden @endif mb-4 bg-white dark:bg-gray-800 rounded-2xl px-4 py-3 shadow-sm border border-gray-100 dark:border-gray-700">
            <div class="flex items-center gap-0">
                <div class="step-dot" :class="selectedCustomerId ? 'step-dot-done' : 'step-dot-active'">1</div>
                <div class="step-line" :class="selectedCustomerId ? 'step-line-done' : 'step-line-pending'"></div>
                <div class="step-dot" :class="items.length > 0 ? 'step-dot-done' : (selectedCustomerId ? 'step-dot-active' : 'step-dot-pending')">2</div>
                <div class="step-line" :class="items.length > 0 ? 'step-line-done' : 'step-line-pending'"></div>
                <div class="step-dot" :class="items.length > 0 ? 'step-dot-active' : 'step-dot-pending'">3</div>
            </div>
            <div class="flex items-center justify-between mt-2 px-0.5">
                <span class="text-[9px] font-black uppercase tracking-widest transition-colors" :class="selectedCustomerId ? 'text-[var(--gradient-from,#4634ff)]' : 'text-gray-400'">Info</span>
                <span class="text-[9px] font-black uppercase tracking-widest transition-colors" :class="items.length > 0 ? 'text-[var(--gradient-from,#4634ff)]' : 'text-gray-400'">Products</span>
                <span class="text-[9px] font-black uppercase tracking-widest transition-colors" :class="items.length > 0 ? 'text-[var(--gradient-from,#4634ff)]' : 'text-gray-400'">Review</span>
            </div>
        </div>

        <form action="{{ route('sales.store') }}" method="POST" id="sale-form" class="space-y-5">
            @csrf

            {{-- ══ Section 1: Invoice Info ══ --}}
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-5 sm:p-6">
                <div class="flex items-center gap-2 mb-5">
                    <span class="section-accent section-accent-primary"></span>
                    <h3 class="text-sm font-extrabold text-gray-700 dark:text-gray-200 uppercase tracking-wider">Invoice Information</h3>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
                    {{-- Invoice No --}}
                    <div>
                        <label class="block text-xs font-bold text-ink-muted uppercase tracking-wide mb-1.5">Invoice No. <span class="text-red-500">*</span></label>
                        <input type="text" name="invoice_no" value="{{ old('invoice_no', $invoiceNo) }}" required readonly
                            class="w-full rounded-xl border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700/50 text-ink-muted px-3.5 py-2.5 text-sm font-mono font-bold cursor-not-allowed" />
                        @error('invoice_no')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    {{-- Customer --}}
                    <div class="relative sm:col-span-2" @click.outside="showCustomerDropdown = false">
                        <label class="block text-xs font-bold text-ink-muted uppercase tracking-wide mb-1.5">Customer <span class="text-red-500">*</span></label>
                        <div class="flex rounded-xl border border-gray-200 dark:border-gray-600 overflow-hidden search-wrap bg-white dark:bg-gray-800">
                            <span class="px-3 py-2.5 bg-gray-50 dark:bg-gray-700 text-gray-400 flex items-center">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                            </span>
                            <input type="text" x-model="customerQuery" @focus="showCustomerDropdown = true" @input="showCustomerDropdown = true" autocomplete="off"
                                placeholder="Search customer name, phone, or address..." class="flex-1 px-3 py-2.5 text-sm bg-transparent text-ink-heading focus:outline-none" required />
                        </div>
                        <input type="hidden" name="customer_id" x-model="selectedCustomerId" />
                        <div x-show="showCustomerDropdown && customerResults.length > 0 && getFilteredCustomers().length > 0" x-cloak
                            class="absolute z-20 mt-1.5 w-full bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-xl max-h-56 overflow-y-auto custom-scroll">
                            <template x-for="c in getFilteredCustomers()" :key="c.id">
                                <button type="button" @click="selectCustomer(c)"
                                    class="w-full text-left px-4 py-2.5 text-sm hover:bg-[#4634ff]/5 dark:hover:bg-[#4634ff]/10 border-b border-gray-50 dark:border-gray-700 last:border-0 transition-colors">
                                    <div class="flex items-center justify-between">
                                        <span class="font-semibold text-ink-heading" x-text="c.name"></span>
                                        <span class="text-xs text-gray-400" x-text="c.phone || ''"></span>
                                    </div>
                                    <template x-if="c.address">
                                        <div class="text-xs text-gray-400 dark:text-gray-400 mt-0.5 flex items-center gap-1">
                                            <svg class="h-3 w-3 shrink-0 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            </svg>
                                            <span class="truncate" x-text="c.address"></span>
                                        </div>
                                    </template>
                                </button>
                            </template>
                        </div>
                        @error('customer_id')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    {{-- Customer Address --}}
                    <div class="sm:col-span-2 lg:col-span-2">
                        <label class="block text-xs font-bold text-ink-muted uppercase tracking-wide mb-1.5">Customer Address</label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                            </span>
                            <input type="text" x-model="customerAddress" disabled placeholder="Address will auto-fill from customer"
                                class="w-full rounded-xl border border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-700/50 text-ink-body pl-9 pr-3.5 py-2.5 text-sm cursor-not-allowed italic" />
                            <template x-if="customerAddress">
                                <span class="absolute right-3 top-1/2 -translate-y-1/2 inline-flex items-center px-2 py-0.5 rounded-full bg-[#4634ff]/10 dark:bg-[#4634ff]/20 text-[#4634ff] text-[10px] font-black uppercase">Auto</span>
                            </template>
                        </div>
                    </div>

                    {{-- Date --}}
                    <div>
                        <label class="block text-xs font-bold text-ink-muted uppercase tracking-wide mb-1.5">Date</label>
                        <input type="date" name="sale_date" value="{{ old('sale_date', now()->format('Y-m-d')) }}"
                            class="w-full rounded-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 text-ink-heading px-3.5 py-2.5 text-sm sale-input" />
                        @error('sale_date')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    {{-- Store Category --}}
                    <div class="sm:col-span-2 lg:col-span-1">
                        <label class="block text-xs font-bold text-ink-muted uppercase tracking-wide mb-1.5">Store Category</label>
                        <div class="relative">
                            <input type="text" name="store_category" x-model="storeCategory" disabled placeholder="Auto-fill from customer"
                                class="w-full rounded-xl border border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-700/50 text-ink-muted px-3.5 py-2.5 text-sm cursor-not-allowed italic" />
                            <template x-if="storeCategory">
                                <span class="absolute right-3 top-1/2 -translate-y-1/2 inline-flex items-center px-2 py-0.5 rounded-full bg-[#4634ff]/10 dark:bg-[#4634ff]/20 text-[#4634ff] text-[10px] font-black uppercase">Auto</span>
                            </template>
                        </div>
                        @error('store_category')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    {{-- Salesperson --}}
                    @if(!auth()->user()->hasRole('salesperson'))
                    <div class="sm:col-span-2 lg:col-span-1">
                        <label class="block text-xs font-bold text-ink-muted uppercase tracking-wide mb-1.5">Salesperson</label>
                        <select name="salesperson_id"
                            class="w-full rounded-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 text-ink-heading px-3.5 py-2.5 text-sm sale-input">
                            <option value="">— None —</option>
                            @foreach($salespersons as $sp)
                                <option value="{{ $sp->id }}" @selected(old('salesperson_id') == $sp->id)>{{ $sp->name }}</option>
                            @endforeach
                        </select>
                        @error('salesperson_id')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    @endif
                </div>
            </div>

            {{-- ══ Section 2: Products ══ --}}
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-5 sm:p-6">
                <div class="flex items-center gap-2 mb-5">
                    <span class="section-accent section-accent-green"></span>
                    <h3 class="text-sm font-extrabold text-gray-700 dark:text-gray-200 uppercase tracking-wider">Products</h3>
                    <span class="ml-auto text-xs font-bold text-ink-muted" x-text="items.length + ' item(s) added'"></span>
                </div>

                {{-- Product Search --}}
                <div class="relative mb-4" @click.outside="searchResults = []">
                    <label class="block text-xs font-bold text-ink-muted uppercase tracking-wide mb-1.5">Add Product <span class="text-red-500">*</span></label>
                    <div class="flex rounded-xl border overflow-hidden transition-all search-wrap"
                        :class="selectedCustomerId
                            ? 'border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800'
                            : 'border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-700/50'">
                        <span class="px-3.5 py-2.5 bg-gray-50 dark:bg-gray-700 text-gray-400 flex items-center">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                        </span>
                        <input type="text" x-model="productQuery" @input="searchProducts()" @click="searchProducts()" autocomplete="off"
                            :disabled="!selectedCustomerId"
                            :placeholder="selectedCustomerId ? 'Search by product name or SKU...' : 'Select a customer first to search products'"
                            class="flex-1 px-3 py-2.5 text-sm bg-transparent text-ink-heading focus:outline-none placeholder:text-gray-400 dark:placeholder:text-gray-500 disabled:cursor-not-allowed" />
                        <template x-if="productQuery.length > 0 && selectedCustomerId">
                            <button type="button" @click="productQuery = ''; searchResults = []" class="px-3 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </template>
                    </div>
                    <div x-show="searchResults.length && selectedCustomerId" x-cloak
                        class="absolute z-20 mt-1.5 w-full bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-xl max-h-56 overflow-y-auto custom-scroll">
                        <template x-for="p in searchResults" :key="p.id">
                            <button type="button" @click="addProduct(p)"
                                class="w-full text-left px-4 py-3 hover:bg-[#4634ff]/5 dark:hover:bg-[#4634ff]/10 border-b border-gray-50 dark:border-gray-700 last:border-0 transition-colors">
                                <div class="font-bold text-ink-heading text-sm" x-text="p.name"></div>
                                <div class="flex items-center gap-3 mt-0.5">
                                    <span class="text-xs text-gray-400" x-text="'SKU: ' + p.sku"></span>
                                    <span class="text-xs font-semibold" :class="p.in_stock > 0 ? 'text-emerald-600' : 'text-red-500'" x-text="'In stock: ' + p.in_stock"></span>
                                </div>
                            </button>
                        </template>
                    </div>
                    @error('items')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                {{-- Products Table --}}

                {{-- ── MOBILE CARD LAYOUT (hidden on sm+) ── --}}
                <div class="@if(auth()->user()->hasRole('salesperson'))lg:hidden @else sm:hidden @endif space-y-3 mb-0">
                    <template x-for="(item, index) in items" :key="index">
                        <div class="mobile-product-card rounded-2xl border overflow-hidden"
                             :class="item.is_free
                                ? 'border-violet-200 dark:border-violet-800 bg-violet-50/40 dark:bg-violet-950/20'
                                : 'border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800'">

                            {{-- Card Header: Name + Free Toggle + Remove --}}
                            <div class="px-4 py-3.5 flex items-start justify-between gap-2 border-b border-gray-100 dark:border-gray-700">
                                <div class="flex-1 min-w-0">
                                    <p class="font-black text-gray-900 dark:text-white text-sm leading-tight truncate" x-text="item.name"></p>
                                    <p class="text-[10px] font-mono text-ink-muted mt-0.5" x-text="item.sku"></p>
                                </div>
                                <div class="flex items-center gap-2 flex-shrink-0">
                                    {{-- Free toggle --}}
                                    <label class="inline-flex items-center gap-1.5 cursor-pointer select-none">
                                        <div class="relative">
                                            <input type="checkbox" :name="'items['+index+'][is_free]'" x-model="item.is_free" @change="toggleFreeItem(index)" value="1" class="sr-only" />
                                            <div :class="item.is_free ? 'bg-[#4634ff]' : 'bg-gray-200 dark:bg-gray-600'" class="w-9 h-5 rounded-full transition-colors duration-200"></div>
                                            <div :class="item.is_free ? 'translate-x-4' : 'translate-x-0'" class="absolute top-1 left-1 w-3 h-3 bg-white rounded-full shadow-sm transition-transform duration-200"></div>
                                        </div>
                                        <span x-show="!item.is_free" class="text-[10px] text-gray-400 font-semibold">Free?</span>
                                        <span x-show="item.is_free" x-cloak class="text-[10px] font-black text-[#4634ff]">🎁 FREE</span>
                                    </label>
                                    {{-- Remove button --}}
                                    <button type="button" @click="removeRow(index)"
                                        class="p-2 text-gray-300 dark:text-gray-600 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-950/30 rounded-xl transition-all">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                    </button>
                                </div>
                            </div>

                            {{-- Stock Badge --}}
                            <div class="px-4 pt-3 pb-1 flex items-center gap-2">
                                <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wide">In Stock:</span>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold"
                                    :class="(parseFloat(item.in_stock) || 0) > 0 ? 'avail-badge' : 'avail-badge-low'"
                                    x-text="(parseFloat(item.in_stock) || 0).toFixed(4).replace(/0+$/,'').replace(/\.$/,'') + ' ' + item.unit_label"></span>
                            </div>

                            {{-- Qty + Price Row --}}
                            <div class="px-4 pb-3 grid grid-cols-2 gap-3">
                                <div>
                                    <label class="text-[10px] font-bold text-gray-400 uppercase tracking-wide block mb-1.5">Qty <span class="text-red-400">*</span></label>
                                    <input type="number" step="0.0001" min="0.0001" :max="item.in_stock || 0"
                                        :name="'items['+index+'][quantity]'" x-model="item.quantity" required
                                        class="mobile-input w-full rounded-xl border px-3 py-3 text-center font-bold transition-colors"
                                        :class="quantityExceedsAvailable(index)
                                            ? 'border-red-400 bg-red-50 dark:bg-red-950/30 text-red-700'
                                            : 'border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 text-ink-heading'" />
                                    <p x-show="quantityExceedsAvailable(index)" x-text="quantityErrorMessage(index)" class="text-[10px] text-red-600 mt-1"></p>
                                </div>
                                <div>
                                    <label class="text-[10px] font-bold text-gray-400 uppercase tracking-wide block mb-1.5">Unit Price <span class="text-red-400">*</span></label>
                                    <div class="relative">
                                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs font-bold pointer-events-none">{{ currencySymbol() }}</span>
                                        <input type="number" step="0.01" min="0" x-model.number="item.unit_price" :readonly="item.is_free" required
                                            class="mobile-input w-full rounded-xl border pl-7 pr-3 py-3 text-right font-semibold"
                                            :class="item.is_free
                                                ? 'bg-gray-100 dark:bg-gray-700 text-gray-400 border-gray-200 dark:border-gray-600 cursor-not-allowed'
                                                : 'bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-600 text-ink-heading'" />
                                    </div>
                                </div>
                            </div>

                            {{-- Discount Row --}}
                            <div class="px-4 pb-3 grid grid-cols-2 gap-3" :class="item.is_free ? 'opacity-40 pointer-events-none' : ''">
                                {{-- Unit Discount --}}
                                <div>
                                    <label class="text-[10px] font-bold text-gray-400 uppercase tracking-wide block mb-1.5">Unit Disc.</label>
                                    <div class="flex items-stretch rounded-xl border border-gray-200 dark:border-gray-600 overflow-hidden bg-white dark:bg-gray-800"
                                        x-data="{ open: false, updatePos() { const rect = $refs.udBtn.getBoundingClientRect(); $refs.udMenu.style.top = (rect.bottom+4)+'px'; $refs.udMenu.style.left = rect.left+'px'; } }"
                                        @click.outside="open = false" @scroll.window="open = false">
                                        <button type="button" x-ref="udBtn" @click="open = !open; if(open) $nextTick(()=>updatePos())"
                                            class="px-2.5 bg-gray-50 dark:bg-gray-700 text-gray-500 text-xs flex items-center gap-0.5 border-r border-gray-200 dark:border-gray-600 min-h-[48px]">
                                            <span x-text="(item.unit_discount_type||'fixed')==='percentage'?'%':'{{ currencySymbol() }}'"></span>
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                        </button>
                                        <template x-teleport="body">
                                            <div x-show="open" x-cloak x-ref="udMenu" @click.outside="open = false" class="fixed z-[9999] bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-xl w-28">
                                                <button type="button" @click="item.unit_discount_type='fixed';open=false" class="w-full text-left px-3 py-3 text-sm hover:bg-gray-50 dark:hover:bg-gray-700" :class="(item.unit_discount_type||'fixed')==='fixed'?'font-black text-[#4634ff]':'text-gray-600 dark:text-gray-300'">{{ currencySymbol() }} Fixed</button>
                                                <button type="button" @click="item.unit_discount_type='percentage';open=false" class="w-full text-left px-3 py-3 text-sm hover:bg-gray-50 dark:hover:bg-gray-700" :class="item.unit_discount_type==='percentage'?'font-black text-[#4634ff]':'text-gray-600 dark:text-gray-300'">% Percent</button>
                                            </div>
                                        </template>
                                        <input type="number" step="0.01" min="0" x-model="item.unit_discount"
                                            class="flex-1 min-w-0 px-2 py-3 text-right text-sm focus:outline-none bg-transparent text-ink-heading" />
                                    </div>
                                </div>
                                {{-- Line Discount --}}
                                <div>
                                    <label class="text-[10px] font-bold text-gray-400 uppercase tracking-wide block mb-1.5">Line Disc.</label>
                                    <div class="flex items-stretch rounded-xl border border-gray-200 dark:border-gray-600 overflow-hidden bg-white dark:bg-gray-800"
                                        x-data="{ open: false, updatePos() { const rect = $refs.ldBtn.getBoundingClientRect(); $refs.ldMenu.style.top = (rect.bottom+4)+'px'; $refs.ldMenu.style.left = rect.left+'px'; } }"
                                        @click.outside="open = false" @scroll.window="open = false">
                                        <button type="button" x-ref="ldBtn" @click="open = !open; if(open) $nextTick(()=>updatePos())"
                                            class="px-2.5 bg-gray-50 dark:bg-gray-700 text-gray-500 text-xs flex items-center gap-0.5 border-r border-gray-200 dark:border-gray-600 min-h-[48px]">
                                            <span x-text="(item.discount_type||'fixed')==='percentage'?'%':'{{ currencySymbol() }}'"></span>
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                        </button>
                                        <template x-teleport="body">
                                            <div x-show="open" x-cloak x-ref="ldMenu" @click.outside="open = false" class="fixed z-[9999] bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-xl w-28">
                                                <button type="button" @click="item.discount_type='fixed';open=false" class="w-full text-left px-3 py-3 text-sm hover:bg-gray-50 dark:hover:bg-gray-700" :class="(item.discount_type||'fixed')==='fixed'?'font-black text-[#4634ff]':'text-gray-600 dark:text-gray-300'">{{ currencySymbol() }} Fixed</button>
                                                <button type="button" @click="item.discount_type='percentage';open=false" class="w-full text-left px-3 py-3 text-sm hover:bg-gray-50 dark:hover:bg-gray-700" :class="item.discount_type==='percentage'?'font-black text-[#4634ff]':'text-gray-600 dark:text-gray-300'">% Percent</button>
                                            </div>
                                        </template>
                                        <input type="number" step="0.01" min="0" :max="(item.discount_type||'fixed')==='percentage'?100:undefined"
                                            x-model="item.discount_rate"
                                            class="flex-1 min-w-0 px-2 py-3 text-center text-sm focus:outline-none bg-transparent text-ink-heading" />
                                    </div>
                                </div>
                            </div>

                            {{-- Totals Footer --}}
                            <div class="px-5 py-3 flex items-center justify-between rounded-b-2xl"
                                 :class="item.is_free ? 'bg-violet-50/60 dark:bg-violet-950/20' : 'bg-gray-50 dark:bg-gray-700/40'">
                                <div>
                                    <p class="text-[10px] text-gray-400 font-semibold uppercase tracking-wide">Subtotal</p>
                                    <p class="text-sm font-black text-gray-700 dark:text-gray-200">
                                        <span class="text-[10px] font-normal text-gray-400">{{ currencySymbol() }}</span>
                                        <span x-text="rowSubTotal(index).toFixed(2)"></span>
                                    </p>
                                </div>
                                <svg class="w-5 h-5 text-gray-300 dark:text-gray-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                                <div class="text-right">
                                    <p class="text-[10px] text-gray-400 font-semibold uppercase tracking-wide">Row Total</p>
                                    <p class="text-base font-black text-gray-900 dark:text-white">
                                        <span class="text-[10px] font-normal text-gray-400">{{ currencySymbol() }}</span>
                                        <span x-text="rowTotal(index).toFixed(2)"></span>
                                    </p>
                                </div>
                            </div>

                            {{-- Batch Tracking --}}
                            <template x-if="item.requires_batch_tracking">
                                <div class="border-t border-violet-100 dark:border-violet-900/30 bg-violet-50/30 dark:bg-violet-950/10 px-4 py-4">
                                    <div class="flex items-center justify-between mb-3">
                                        <label class="flex items-center gap-2 cursor-pointer">
                                            <input type="checkbox" x-model="item.use_fefo" @click="toggleUseFefoManually(index, $event)"
                                                class="w-4 h-4 rounded border-gray-300 dark:border-gray-600 text-[#4634ff] focus:ring-[#4634ff] bg-white dark:bg-gray-800" />
                                            <span class="text-xs font-bold text-ink-muted uppercase tracking-wide">FEFO (First Expiry, First Out)</span>
                                        </label>
                                        <template x-if="!item.use_fefo && item.batches.length > 0">
                                            <button type="button" @click="addBatchRow(index)"
                                                class="inline-flex items-center gap-1 px-3 py-2 bg-[#4634ff] text-white rounded-xl text-xs font-bold shadow-sm">
                                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                                                Add Batch
                                            </button>
                                        </template>
                                    </div>
                                    <div x-show="!item.use_fefo">
                                        <template x-if="item.batches.length === 0">
                                            <div class="text-center py-4 text-gray-400 italic text-xs">No batches available for this product.</div>
                                        </template>
                                        <template x-if="item.batches.length > 0">
                                            <div class="space-y-2">
                                                <template x-for="(batch, batchIndex) in item.selected_batches" :key="batchIndex">
                                                    <div class="p-3 bg-white dark:bg-gray-800 rounded-xl border transition-colors"
                                                        :class="batchQuantityExceedsAvailable(index, batchIndex) ? 'border-red-300 dark:border-red-700 bg-red-50/50 dark:bg-red-950/20' : 'border-gray-200 dark:border-gray-700'">
                                                        <div class="flex items-center justify-between mb-2">
                                                            <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wide">Select Batch</span>
                                                            <button type="button" @click="removeBatchRow(index, batchIndex)"
                                                                class="p-1.5 text-red-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-950/30 rounded-lg transition-colors">
                                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                                            </button>
                                                        </div>
                                                        <select x-model="batch.batch_id"
                                                            class="w-full rounded-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 text-ink-heading px-3 py-3 text-sm mb-3 focus:ring-1 focus:ring-[#4634ff]">
                                                            <template x-for="b in item.batches" :key="b.id">
                                                                <option :value="b.id" x-text="b.batch_number + (b.expiry_date ? ' (Exp: ' + b.expiry_date + ')' : '') + ' · Avail: ' + b.quantity_available" :selected="Number(b.id) === Number(batch.batch_id)"></option>
                                                            </template>
                                                        </select>
                                                        <input type="number" step="0.0001" min="0"
                                                            x-model.number="batch.quantity"
                                                            placeholder="Batch quantity"
                                                            class="w-full rounded-xl border px-3 py-3 text-sm focus:ring-1 focus:ring-[#4634ff] bg-white dark:bg-gray-800 text-ink-heading"
                                                            :class="batchQuantityExceedsAvailable(index, batchIndex) ? 'border-red-400' : 'border-gray-200 dark:border-gray-600'" />
                                                        <p x-show="batchQuantityExceedsAvailable(index, batchIndex)" x-text="batchQuantityErrorMessage(index, batchIndex)" class="text-[10px] text-red-600 mt-1"></p>
                                                        <p x-show="!batchQuantityExceedsAvailable(index, batchIndex) && batch.batch_id" class="text-[10px] text-gray-400 mt-1">
                                                            Available: <span class="font-bold text-emerald-600" x-text="getBatchAvailableForRow(index, batchIndex)"></span>
                                                        </p>
                                                    </div>
                                                </template>
                                                <template x-if="item.selected_batches.length === 0">
                                                    <div class="text-center py-5 text-gray-400 italic text-xs border-2 border-dashed border-gray-200 dark:border-gray-700 rounded-xl">
                                                        Tap "Add Batch" to select batches.
                                                    </div>
                                                </template>
                                                <div class="flex items-center justify-between pt-2 border-t border-gray-100 dark:border-gray-700 mt-2">
                                                    <span class="text-xs text-ink-muted">Batch total:</span>
                                                    <span class="text-xs font-black"
                                                        :class="getBatchTotalQuantity(index) > parseFloat(item.quantity)
                                                            ? 'text-red-600'
                                                            : (getBatchTotalQuantity(index) < parseFloat(item.quantity) && getBatchTotalQuantity(index) > 0
                                                                ? 'text-amber-600'
                                                                : 'text-ink-body')">
                                                        <span x-text="getBatchTotalQuantity(index)"></span>
                                                        <span class="text-gray-300 mx-1">/</span>
                                                        <span x-text="item.quantity"></span>
                                                    </span>
                                                </div>
                                                <template x-if="getBatchTotalQuantity(index) > parseFloat(item.quantity)">
                                                    <div class="flex items-start gap-2 mt-2 px-3 py-2.5 rounded-lg bg-red-50 dark:bg-red-950/30 border border-red-200 dark:border-red-800">
                                                        <svg class="h-4 w-4 text-red-500 mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
                                                        <p class="text-xs text-red-700 dark:text-red-400 font-semibold">Batch total exceeds required qty <span class="font-black" x-text="item.quantity"></span>.</p>
                                                    </div>
                                                </template>
                                                <template x-if="getBatchTotalQuantity(index) < parseFloat(item.quantity) && getBatchTotalQuantity(index) > 0">
                                                    <div class="flex items-start gap-2 mt-2 px-3 py-2.5 rounded-lg bg-amber-50 dark:bg-amber-950/30 border border-amber-200 dark:border-amber-700">
                                                        <svg class="h-4 w-4 text-amber-500 mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
                                                        <p class="text-xs text-amber-700 dark:text-amber-400 font-semibold">Batch total (<span x-text="getBatchTotalQuantity(index)"></span>) less than required (<span x-text="item.quantity"></span>).</p>
                                                    </div>
                                                </template>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </template>

                    {{-- Mobile Empty State --}}
                    <div x-show="items.length === 0" class="text-center py-14 bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700">
                        <svg class="h-14 w-14 mx-auto mb-3 text-gray-200 dark:text-gray-700" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" /></svg>
                        <p class="text-sm text-ink-muted font-medium">No products added yet.</p>
                        <p class="text-xs text-gray-300 dark:text-gray-600 mt-1">Search above to add products.</p>
                    </div>
                </div>

                {{-- ── DESKTOP TABLE LAYOUT (hidden on mobile, visible sm+) ── --}}
                <div class="@if(auth()->user()->hasRole('salesperson'))hidden lg:block @else hidden sm:block @endif overflow-x-auto rounded-xl border border-gray-100 dark:border-gray-700">
                    <table class="w-full text-xs sm:text-sm">
                        <thead class="@if(auth()->user()->hasRole('salesperson'))hidden lg:table-header-group @else hidden sm:table-header-group @endif">
                            <tr class="gradient-bg text-white">
                                <th class="px-4 py-3 text-left text-[10px] font-black uppercase tracking-widest">Product Details</th>
                                <th class="px-4 py-3 text-center text-[10px] font-black uppercase tracking-widest">Availability</th>
                                <th class="px-4 py-3 text-center text-[10px] font-black uppercase tracking-widest">Qty</th>
                                <th class="px-4 py-3 text-center text-[10px] font-black uppercase tracking-widest">Price</th>
                                <th class="px-4 py-3 text-center text-[10px] font-black uppercase tracking-widest">Unit Disc.</th>
                                <th class="px-4 py-3 text-center text-[10px] font-black uppercase tracking-widest">Subtotal</th>
                                <th class="px-4 py-3 text-center text-[10px] font-black uppercase tracking-widest">Discount</th>
                                <th class="px-4 py-3 text-center text-[10px] font-black uppercase tracking-widest">Total</th>
                                <th class="px-4 py-3 text-right text-[10px] font-black uppercase tracking-widest"></th>
                            </tr>
                        </thead>
                        <template x-for="(item, index) in items" :key="index">
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-700 border-b-2 border-gray-100 dark:border-gray-700">
                                <tr class="item-row" :class="item.is_free ? 'bg-violet-50/60 dark:bg-violet-950/20' : 'hover:bg-gray-50/60 dark:hover:bg-gray-700/30'">
                                    {{-- Product Details --}}
                                    <td class="px-4 py-3.5">
                                        <input type="hidden" :name="'items['+index+'][product_id]'" :value="item.product_id" />
                                        <input type="hidden" :name="'items['+index+'][use_fefo]'" :value="item.use_fefo ? '1' : '0'" />
                                        <div class="font-black text-ink-body text-sm" x-text="item.name"></div>
                                        <div class="text-[10px] text-ink-muted mt-0.5 font-mono" x-text="item.sku"></div>
                                        {{-- Free Item Toggle --}}
                                        <label class="inline-flex items-center gap-2 mt-2 cursor-pointer select-none">
                                            <div class="relative">
                                                <input type="checkbox" :name="'items['+index+'][is_free]'" x-model="item.is_free" @change="toggleFreeItem(index)" value="1" class="sr-only" />
                                                <div :class="item.is_free ? 'bg-[#4634ff]' : 'bg-gray-200 dark:bg-gray-600'" class="w-8 h-4 rounded-full transition-colors duration-200"></div>
                                                <div :class="item.is_free ? 'translate-x-4' : 'translate-x-0'" class="absolute top-0.5 left-0.5 w-3 h-3 bg-white rounded-full shadow-sm transition-transform duration-200"></div>
                                            </div>
                                            <span x-show="!item.is_free" class="text-[10px] text-gray-400 font-semibold">Mark Free</span>
                                            <span x-show="item.is_free" x-cloak class="text-[10px] font-black uppercase tracking-wider text-[var(--color-accent,#4634ff)] bg-[color-mix(in_srgb,var(--color-accent,#4634ff),transparent_90%)] px-2 py-0.5 rounded-full">🎁 FREE</span>
                                        </label>
                                    </td>
                                    {{-- Availability --}}
                                    <td class="px-4 py-3.5 text-center">
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold"
                                            :class="(parseFloat(item.in_stock) || 0) > 0 ? 'avail-badge' : 'avail-badge-low'"
                                            x-text="(parseFloat(item.in_stock) || 0).toFixed(4).replace(/0+$/,'').replace(/\.$/,'') + ' ' + item.unit_label"></span>
                                    </td>
                                    {{-- Qty --}}
                                    <td class="px-4 py-3.5 text-center">
                                        <div class="flex flex-col items-center gap-1">
                                            <input type="number" step="0.0001" min="0.0001" :max="item.in_stock || 0"
                                                :name="'items['+index+'][quantity]'" x-model="item.quantity" required
                                                class="w-20 rounded-lg border px-2 py-1.5 text-center text-sm font-bold transition-colors"
                                                :class="quantityExceedsAvailable(index) ? 'border-red-400 bg-red-50 dark:bg-red-950/30 text-red-700' : 'border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 text-ink-heading'" />
                                            <p x-show="quantityExceedsAvailable(index)" x-text="quantityErrorMessage(index)" class="text-[10px] text-red-600"></p>
                                        </div>
                                    </td>
                                    {{-- Price --}}
                                    <td class="px-4 py-3.5 text-center">
                                        <div class="flex items-center justify-center gap-1">
                                            <span class="text-gray-400 text-xs font-bold">{{ currencySymbol() }}</span>
                                            <input type="hidden" :name="'items['+index+'][unit_price]'" :value="item.unit_price" />
                                            <input type="number" step="0.01" min="0" x-model.number="item.unit_price" :readonly="item.is_free" required
                                                class="w-24 rounded-lg border px-2 py-1.5 text-right text-sm font-semibold"
                                                :class="item.is_free ? 'bg-gray-100 dark:bg-gray-700 text-gray-400 border-gray-200 dark:border-gray-600 cursor-not-allowed' : 'bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-600 text-ink-heading'" />
                                        </div>
                                    </td>
                                    {{-- Unit Discount --}}
                                    <td class="px-4 py-3.5 text-center">
                                        <div class="flex items-stretch rounded-lg border border-gray-200 dark:border-gray-600 w-32 bg-white dark:bg-gray-800"
                                            :class="item.is_free ? 'opacity-50' : ''"
                                            x-data="{ open: false, updatePos() { const rect = $refs.btn.getBoundingClientRect(); $refs.menu.style.top = (rect.bottom + 4) + 'px'; $refs.menu.style.left = rect.left + 'px'; } }"
                                            @click.outside="open = false" @scroll.window="open = false">
                                            <div class="relative flex items-stretch">
                                                <button type="button" x-ref="btn" @click="if(!item.is_free) { open = !open; if(open) $nextTick(() => updatePos()) }"
                                                    class="px-2 bg-gray-50 dark:bg-gray-700 text-gray-500 text-xs hover:bg-gray-100 dark:hover:bg-gray-600 flex items-center gap-0.5 rounded-l border-r border-gray-200 dark:border-gray-600 h-full"
                                                    :disabled="item.is_free">
                                                    <span x-text="(item.unit_discount_type || 'fixed') === 'percentage' ? '%' : '{{ currencySymbol() }}'"></span>
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                                </button>
                                                <template x-teleport="body">
                                                    <div x-show="open" x-cloak x-ref="menu" @click.outside="open = false" class="fixed z-[9999] bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-xl w-24">
                                                        <button type="button" @click="item.unit_discount_type = 'fixed'; open = false" class="w-full text-left px-3 py-2 text-xs hover:bg-gray-50 dark:hover:bg-gray-700" :class="(item.unit_discount_type || 'fixed') === 'fixed' ? 'font-black text-[#4634ff]' : 'text-gray-600 dark:text-gray-300'">{{ currencySymbol() }} Fixed</button>
                                                        <button type="button" @click="item.unit_discount_type = 'percentage'; open = false" class="w-full text-left px-3 py-2 text-xs hover:bg-gray-50 dark:hover:bg-gray-700" :class="item.unit_discount_type === 'percentage' ? 'font-black text-[#4634ff]' : 'text-gray-600 dark:text-gray-300'">% Percent</button>
                                                    </div>
                                                </template>
                                            </div>
                                            <input type="number" step="0.01" min="0" :name="'items['+index+'][unit_discount]'" x-model="item.unit_discount" :disabled="item.is_free"
                                                class="flex-1 w-full px-2 py-1.5 text-right text-sm rounded-r focus:outline-none bg-transparent disabled:bg-gray-100 dark:disabled:bg-gray-700 text-ink-heading" />
                                        </div>
                                    </td>
                                    {{-- SubTotal --}}
                                    <td class="px-4 py-3.5 text-center">
                                        <span class="text-gray-400 text-xs">{{ currencySymbol() }}</span>
                                        <span class="font-black text-ink-body ml-0.5" x-text="rowSubTotal(index).toFixed(2)"></span>
                                    </td>
                                    {{-- Line Discount --}}
                                    <td class="px-4 py-3.5 text-center">
                                        <input type="hidden" :name="'items['+index+'][unit_discount]'" :value="item.unit_discount" />
                                        <input type="hidden" :name="'items['+index+'][discount_rate]'" :value="item.discount_rate" />
                                        <input type="hidden" :name="'items['+index+'][discount_type]'" :value="item.discount_type || 'fixed'" />
                                        <input type="hidden" :name="'items['+index+'][unit_discount_type]'" :value="item.unit_discount_type || 'fixed'" />
                                        <div class="flex items-stretch rounded-lg border border-gray-200 dark:border-gray-600 w-28 bg-white dark:bg-gray-800"
                                            :class="item.is_free ? 'opacity-50' : ''"
                                            x-data="{ open: false, updatePos() { const rect = $refs.btn.getBoundingClientRect(); $refs.menu.style.top = (rect.bottom + 4) + 'px'; $refs.menu.style.left = rect.left + 'px'; } }"
                                            @click.outside="open = false" @scroll.window="open = false">
                                            <div class="relative flex items-stretch">
                                                <button type="button" x-ref="btn" @click="if(!item.is_free) { open = !open; if(open) $nextTick(() => updatePos()) }"
                                                    class="px-2 bg-gray-50 dark:bg-gray-700 text-gray-500 text-xs hover:bg-gray-100 dark:hover:bg-gray-600 flex items-center gap-0.5 rounded-l border-r border-gray-200 dark:border-gray-600 h-full"
                                                    :disabled="item.is_free">
                                                    <span x-text="(item.discount_type || 'fixed') === 'percentage' ? '%' : '{{ currencySymbol() }}'"></span>
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                                </button>
                                                <template x-teleport="body">
                                                    <div x-show="open" x-cloak x-ref="menu" @click.outside="open = false" class="fixed z-[9999] bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-xl w-24">
                                                        <button type="button" @click="item.discount_type = 'fixed'; open = false" class="w-full text-left px-3 py-2 text-xs hover:bg-gray-50 dark:hover:bg-gray-700" :class="(item.discount_type || 'fixed') === 'fixed' ? 'font-black text-[#4634ff]' : 'text-gray-600 dark:text-gray-300'">{{ currencySymbol() }} Fixed</button>
                                                        <button type="button" @click="item.discount_type = 'percentage'; open = false" class="w-full text-left px-3 py-2 text-xs hover:bg-gray-50 dark:hover:bg-gray-700" :class="item.discount_type === 'percentage' ? 'font-black text-[#4634ff]' : 'text-gray-600 dark:text-gray-300'">% Percent</button>
                                                    </div>
                                                </template>
                                            </div>
                                            <input type="number" step="0.01" min="0" :max="(item.discount_type || 'fixed') === 'percentage' ? 100 : undefined" x-model="item.discount_rate" :disabled="item.is_free"
                                                class="flex-1 w-full px-2 py-1.5 text-center text-sm rounded-r focus:outline-none bg-transparent disabled:bg-gray-100 dark:disabled:bg-gray-700 text-ink-heading" />
                                        </div>
                                    </td>
                                    {{-- Total --}}
                                    <td class="px-4 py-3.5 text-center">
                                        <span class="text-gray-400 text-xs">{{ currencySymbol() }}</span>
                                        <span class="font-black text-gray-900 dark:text-white ml-0.5" x-text="rowTotal(index).toFixed(2)"></span>
                                    </td>
                                    {{-- Action --}}
                                    <td class="px-4 py-3.5 text-right">
                                        <button type="button" @click="removeRow(index)"
                                            class="p-2 text-gray-300 dark:text-gray-600 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-950/30 rounded-lg transition-all duration-150 group">
                                            <svg class="h-4 w-4 group-hover:scale-110 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                        </button>
                                    </td>
                                </tr>
                                {{-- Batch Tracking Sub-row --}}
                                <template x-if="item.requires_batch_tracking">
                                    <tr class="bg-violet-50/30 dark:bg-violet-950/10">
                                        <td colspan="9" class="px-4 py-3">
                                            <div class="flex items-center justify-between mb-3">
                                                <label class="flex items-center gap-2 cursor-pointer">
                                                    <input type="checkbox" x-model="item.use_fefo" @click="toggleUseFefoManually(index, $event)"
                                                        class="rounded border-gray-300 dark:border-gray-600 text-[#4634ff] focus:ring-[#4634ff] bg-white dark:bg-gray-800" />
                                                    <span class="text-xs font-bold text-ink-muted uppercase tracking-wide">Use FEFO (First Expiry, First Out)</span>
                                                </label>
                                                <template x-if="!item.use_fefo && item.batches.length > 0">
                                                    <button type="button" @click="addBatchRow(index)"
                                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-[#4634ff] text-white rounded-lg hover:bg-violet-700 text-xs font-bold transition-colors shadow-sm">
                                                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                                                        Add Batch
                                                    </button>
                                                </template>
                                            </div>
                                            <div x-show="!item.use_fefo">
                                                <template x-if="item.batches.length === 0">
                                                    <div class="text-center py-4 text-gray-400 italic text-xs">No batches available for this product.</div>
                                                </template>
                                                <template x-if="item.batches.length > 0">
                                                    <div class="space-y-2">
                                                        <template x-for="(batch, batchIndex) in item.selected_batches" :key="batchIndex">
                                                            <div class="flex items-center gap-3 p-3 bg-white dark:bg-gray-800 rounded-xl border transition-colors"
                                                                :class="batchQuantityExceedsAvailable(index, batchIndex) ? 'border-red-300 dark:border-red-700 bg-red-50/50 dark:bg-red-950/20' : 'border-gray-200 dark:border-gray-700'">
                                                                <div class="flex-1 grid grid-cols-1 md:grid-cols-3 gap-3">
                                                                    <div>
                                                                        <label class="text-[10px] font-bold text-gray-400 uppercase tracking-wide block mb-1">Batch</label>
                                                                        <select :name="'items['+index+'][batches]['+batchIndex+'][batch_id]'" :value="batch.batch_id"
                                                                            @change="batch.batch_id = $event.target.value ? Number($event.target.value) : null"
                                                                            class="w-full rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 text-ink-heading px-3 py-2 text-xs focus:ring-1 focus:ring-[#4634ff]">
                                                                            <template x-for="b in item.batches" :key="b.id">
                                                                                <option :value="b.id" x-text="b.batch_number + (b.expiry_date ? ' (Exp: ' + b.expiry_date + ')' : '') + ' · Avail: ' + b.quantity_available" :selected="Number(b.id) === Number(batch.batch_id)"></option>
                                                                            </template>
                                                                        </select>
                                                                    </div>
                                                                    <div>
                                                                        <label class="text-[10px] font-bold text-gray-400 uppercase tracking-wide block mb-1">Quantity</label>
                                                                        <input type="number" step="0.0001" min="0" :max="getBatchAvailableForRow(index, batchIndex) || null"
                                                                            :name="'items['+index+'][batches]['+batchIndex+'][quantity]'" x-model.number="batch.quantity"
                                                                            class="w-full rounded-lg border px-3 py-2 text-xs focus:ring-1 focus:ring-[#4634ff] bg-white dark:bg-gray-800 text-ink-heading"
                                                                            :class="batchQuantityExceedsAvailable(index, batchIndex) ? 'border-red-400' : 'border-gray-200 dark:border-gray-600'" />
                                                                        <p x-show="batchQuantityExceedsAvailable(index, batchIndex)" x-text="batchQuantityErrorMessage(index, batchIndex)" class="text-[10px] text-red-600 mt-1"></p>
                                                                        <p x-show="!batchQuantityExceedsAvailable(index, batchIndex) && batch.batch_id" class="text-[10px] text-gray-400 mt-1">
                                                                            Available: <span class="font-bold text-emerald-600" x-text="getBatchAvailableForRow(index, batchIndex)"></span>
                                                                        </p>
                                                                    </div>
                                                                    <div>
                                                                        <label class="text-[10px] font-bold text-gray-400 uppercase tracking-wide block mb-1">&nbsp;</label>
                                                                        <button type="button" @click="removeBatchRow(index, batchIndex)"
                                                                            class="w-full px-3 py-2 border border-red-200 dark:border-red-800 text-red-500 rounded-lg hover:bg-red-50 dark:hover:bg-red-950/30 text-xs font-bold transition-colors">Remove</button>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </template>
                                                        <template x-if="item.selected_batches.length === 0">
                                                            <div class="text-center py-5 text-gray-400 italic text-xs border-2 border-dashed border-gray-200 dark:border-gray-700 rounded-xl">
                                                                Click "Add Batch" to select which batches to use.
                                                            </div>
                                                        </template>
                                                        <div class="flex items-center justify-between pt-2 border-t border-gray-100 dark:border-gray-700 mt-2">
                                                            <span class="text-xs text-ink-muted">Batch total:</span>
                                                            <span class="text-xs font-black"
                                                                :class="getBatchTotalQuantity(index) > parseFloat(item.quantity)
                                                                    ? 'text-red-600'
                                                                    : (getBatchTotalQuantity(index) < parseFloat(item.quantity) && getBatchTotalQuantity(index) > 0
                                                                        ? 'text-amber-600'
                                                                        : 'text-ink-body')">
                                                                <span x-text="getBatchTotalQuantity(index)"></span>
                                                                <span class="text-gray-300 mx-1">/</span>
                                                                <span x-text="item.quantity"></span>
                                                            </span>
                                                        </div>
                                                        {{-- Batch quantity mismatch notice --}}
                                                        <template x-if="getBatchTotalQuantity(index) > parseFloat(item.quantity)">
                                                            <div class="flex items-start gap-2 mt-2 px-3 py-2.5 rounded-lg bg-red-50 dark:bg-red-950/30 border border-red-200 dark:border-red-800">
                                                                <svg class="h-4 w-4 text-red-500 mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
                                                                <p class="text-xs text-red-700 dark:text-red-400 font-semibold">
                                                                    Batch total exceeds the required quantity. Please reduce batch quantities to match <span class="font-black" x-text="item.quantity"></span>.
                                                                </p>
                                                            </div>
                                                        </template>
                                                        <template x-if="getBatchTotalQuantity(index) < parseFloat(item.quantity) && getBatchTotalQuantity(index) > 0">
                                                            <div class="flex items-start gap-2 mt-2 px-3 py-2.5 rounded-lg bg-amber-50 dark:bg-amber-950/30 border border-amber-200 dark:border-amber-700">
                                                                <svg class="h-4 w-4 text-amber-500 mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
                                                                <p class="text-xs text-amber-700 dark:text-amber-400 font-semibold">
                                                                    Batch total (<span x-text="getBatchTotalQuantity(index)"></span>) is less than the required quantity (<span x-text="item.quantity"></span>). The sale cannot be submitted until all quantities are allocated.
                                                                </p>
                                                            </div>
                                                        </template>
                                                    </div>
                                                </template>
                                            </div>
                                        </td>
                                    </tr>
                                </template>
                                <template x-if="!item.requires_batch_tracking">
                                    <tr class="bg-transparent"><td colspan="9"></td></tr>
                                </template>
                            </tbody>
                        </template>

                        {{-- Empty State --}}
                        <tbody x-show="items.length === 0">
                            <tr>
                                <td colspan="9" class="px-4 py-16 text-center">
                                    <svg class="h-14 w-14 mx-auto mb-3 text-gray-200 dark:text-gray-700" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" /></svg>
                                    <p class="text-sm text-ink-muted font-medium">No products added yet.</p>
                                    <p class="text-xs text-gray-300 dark:text-gray-600 mt-1">Use the search bar above to add products.</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- ══ Section 3: Notes & Totals ══ --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
                {{-- Note --}}
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-5 sm:p-6"
                     x-data="{ noteOpen: false }">
                    <div class="flex items-center gap-2 mb-4 cursor-pointer sm:cursor-default"
                         @click="noteOpen = !noteOpen">
                        <span class="section-accent section-accent-amber"></span>
                        <h3 class="text-sm font-extrabold text-gray-700 dark:text-gray-200 uppercase tracking-wider flex-1">Note</h3>
                        <button type="button" class="sm:hidden text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors p-1">
                            <svg class="w-4 h-4 transition-transform duration-200" :class="noteOpen ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>
                    </div>
                    <div :class="noteOpen ? 'block' : 'hidden sm:block'">
                        <textarea name="note" rows="4"
                            class="w-full rounded-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 text-ink-heading px-3.5 py-3 text-sm sale-input placeholder:text-gray-300 dark:placeholder:text-gray-600 resize-none"
                            placeholder="Optional note for this sale...">{{ old('note') }}</textarea>
                    </div>
                </div>

                {{-- Totals --}}
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-5 sm:p-6">
                    <div class="flex items-center gap-2 mb-5">
                        <span class="section-accent section-accent-green"></span>
                        <h3 class="text-sm font-extrabold text-gray-700 dark:text-gray-200 uppercase tracking-wider">Order Summary</h3>
                    </div>
                    <div class="space-y-3">
                        {{-- Total Price --}}
                        <div class="flex items-center justify-between py-2 border-b border-gray-50 dark:border-gray-700">
                            <span class="text-sm text-ink-muted">Subtotal</span>
                            <div class="flex items-center gap-1">
                                <span class="text-xs text-gray-400">{{ currencySymbol() }}</span>
                                <span class="text-sm font-black text-ink-body" x-text="subtotal.toFixed(2)"></span>
                            </div>
                        </div>
                        {{-- Discount --}}
                        <div class="flex items-center justify-between py-2 border-b border-gray-50 dark:border-gray-700">
                            <span class="text-sm text-ink-muted">Discount</span>
                            <div class="flex items-stretch rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 overflow-hidden"
                                x-data="{
                                    open: false,
                                    updatePos() { const rect = $refs.btn.getBoundingClientRect(); $refs.menu.style.top = (rect.bottom + 4) + 'px'; $refs.menu.style.left = rect.left + 'px'; }
                                }"
                                @click.outside="open = false" @scroll.window="open = false">
                                <div class="relative flex items-stretch">
                                    <button type="button" x-ref="btn" @click="open = !open; if(open) $nextTick(() => updatePos())"
                                        class="px-2.5 bg-gray-50 dark:bg-gray-700 text-gray-500 text-xs hover:bg-gray-100 dark:hover:bg-gray-600 flex items-center gap-0.5 border-r border-gray-200 dark:border-gray-600 h-full">
                                        <span x-text="discountType === 'percentage' ? '%' : '{{ currencySymbol() }}'"></span>
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                    </button>
                                    <template x-teleport="body">
                                        <div x-show="open" x-cloak x-ref="menu" @click.outside="open = false" class="fixed z-[9999] bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-xl w-28">
                                            <button type="button" @click="discountType = 'fixed'; open = false" class="w-full text-left px-3 py-2 text-xs hover:bg-gray-50 dark:hover:bg-gray-700" :class="discountType === 'fixed' ? 'font-black text-[#4634ff]' : 'text-gray-600 dark:text-gray-300'">{{ currencySymbol() }} Fixed</button>
                                            <button type="button" @click="discountType = 'percentage'; open = false" class="w-full text-left px-3 py-2 text-xs hover:bg-gray-50 dark:hover:bg-gray-700" :class="discountType === 'percentage' ? 'font-black text-[#4634ff]' : 'text-gray-600 dark:text-gray-300'">% Percent</button>
                                        </div>
                                    </template>
                                </div>
                                <input type="number" name="discount" step="0.01" min="0" :max="discountType === 'percentage' ? 100 : undefined"
                                    x-model="discount"
                                    class="w-28 px-2.5 py-1.5 text-sm text-right focus:outline-none bg-transparent text-ink-heading" />
                                <input type="hidden" name="discount_type" :value="discountType" />
                            </div>
                        </div>
                        {{-- Receivable --}}
                        <div class="flex items-center justify-between py-3 bg-gradient-to-r from-[#4634ff]/5 to-emerald-500/5 dark:from-[#4634ff]/10 dark:to-emerald-500/10 rounded-xl px-3 mt-2">
                            <span class="text-sm font-extrabold text-gray-700 dark:text-gray-200">Receivable Amount</span>
                            <div class="flex items-center gap-1">
                                <span class="text-xs text-gray-400">{{ currencySymbol() }}</span>
                                <span class="text-xl font-black text-gray-900 dark:text-white" x-text="receivable.toFixed(2)"></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ══ Error + Submit (Desktop) ══ --}}
            <div class="@if(auth()->user()->hasRole('salesperson'))hidden lg:block @else hidden sm:block @endif bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-5">
                <button type="submit" :disabled="items.length === 0 || hasAnyErrors()"
                    class="submit-btn w-full py-3.5 rounded-xl font-black text-sm tracking-wide transition-all duration-200
                           bg-gradient-to-r from-[#4634ff] to-violet-600 hover:from-[#3a28e0] hover:to-violet-700
                           text-white shadow-md hover:shadow-lg
                           disabled:opacity-40 disabled:cursor-not-allowed disabled:shadow-none disabled:from-gray-400 disabled:to-gray-500">
                    <span class="flex items-center justify-center gap-2">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                        Confirm &amp; Submit Sale
                    </span>
                </button>
            </div>

            {{-- ══ Mobile Spacer (so sticky bar doesn't cover last section) ══ --}}
            <div class="@if(auth()->user()->hasRole('salesperson'))lg:hidden @else sm:hidden @endif h-24"></div>

        </form>

        {{-- ══ Sticky Mobile Total Bar (sm:hidden) ══ --}}
        <div class="@if(auth()->user()->hasRole('salesperson'))lg:hidden @else sm:hidden @endif fixed bottom-20 left-0 right-0 z-30 px-3"
             x-show="items.length > 0"
             x-cloak
             x-transition:enter="transition ease-out duration-250"
             x-transition:enter-start="opacity-0 translate-y-4"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 translate-y-4">
            <div class="mobile-total-bar bg-white/95 dark:bg-gray-800/95 rounded-2xl shadow-2xl border border-gray-200 dark:border-gray-700 overflow-hidden">
                {{-- Error hint if any --}}
                <div x-show="hasAnyErrors()" x-cloak class="px-4 py-2 bg-red-50 dark:bg-red-950/40 border-b border-red-100 dark:border-red-900/30 flex items-center gap-2">
                    <svg class="h-3.5 w-3.5 text-red-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
                    <p class="text-[10px] text-red-600 dark:text-red-400 font-semibold">Fix quantity / batch errors before submitting.</p>
                </div>
                <div class="flex items-center px-4 py-3 gap-3">
                    {{-- Item count badge --}}
                    <div class="flex-shrink-0 w-9 h-9 rounded-full flex items-center justify-center"
                         style="background: linear-gradient(135deg, var(--gradient-from,#4634ff) 0%, var(--gradient-to,#5542ff) 100%)">
                        <span class="text-white text-xs font-black" x-text="items.length"></span>
                    </div>
                    {{-- Running total --}}
                    <div class="flex-1 min-w-0">
                        <p class="text-[10px] text-gray-400 font-semibold uppercase tracking-wide">Receivable</p>
                        <p class="text-lg font-black text-gray-900 dark:text-white leading-tight">
                            <span class="text-xs font-normal text-gray-400">{{ currencySymbol() }}</span>
                            <span x-text="receivable.toFixed(2)"></span>
                        </p>
                    </div>
                    {{-- Submit CTA --}}
                    <button type="submit" form="sale-form" :disabled="items.length === 0 || hasAnyErrors()"
                        class="submit-btn flex-shrink-0 px-5 py-3 rounded-xl font-black text-sm text-white shadow-lg transition-all
                               disabled:opacity-40 disabled:cursor-not-allowed disabled:shadow-none">
                        <span class="flex items-center gap-1.5">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                            Submit
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
