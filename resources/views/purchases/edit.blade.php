<x-app-layout>
    <style>
    /* Custom scrollbar for supplier dropdown */
    .max-h-40::-webkit-scrollbar {
        width: 6px;
    }
    
    .max-h-40::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 3px;
    }
    
    .max-h-40::-webkit-scrollbar-thumb {
        background: #c1c1c1;
        border-radius: 3px;
    }
    
    .max-h-40::-webkit-scrollbar-thumb:hover {
        background: #a1a1a1;
    }
    </style>
    @php
        /** @var \App\Models\Purchase $purchase */
        $initialItems = $purchase->items->map(fn ($i) => [
            'product_id' => $i->product_id,
            'name'       => $i->product_name,
            'sku'        => $i->sku,
            'unit_label' => $i->unit_label,
            'availability' => (float) \App\Models\ProductBatch::query()
                         ->where([
                             'product_id' => $i->product_id,
                             'status' => 'active'
                         ])
                         ->sum('quantity_available'),
            'quantity'   => (float) $i->quantity,
            'unit_price' => (float) $i->unit_price,
            'unit_discount' => (float) ($i->unit_discount ?? 0),
            'unit_discount_type' => $i->unit_discount_type ?? 'fixed',
            'discount_rate' => (float) ($i->discount_rate ?? 0),
        ])->values()->all();
    @endphp
    <script type="application/json" id="purchase-edit-initial-items">@json($initialItems)</script>

    <div
        class="max-w-6xl mx-auto space-y-6"
        x-init="init()"
        x-data="{
            productQuery: '',
            searchResults: [],
            searchLoading: false,
            searchTimer: null,
            items: [],
            discount: {{ old('discount', $purchase->discount) }},
            discountType: '{{ old('discount_type', $purchase->discount_type ?? 'fixed') }}',
            taxRate: {{ old('tax_rate', $purchase->tax_rate) }},
            isLocked: @json($isLocked),
            cloneOpen: false,
            supplierQuery: @js($purchase->supplier->name),
            supplierResults: @js($suppliers),
            selectedSupplierId: {{ $purchase->supplier_id }},
            showSupplierDropdown: false,
            getFilteredSuppliers() {
                if (!this.supplierQuery || this.supplierQuery.trim() === '') {
                    return this.supplierResults;
                }
                const q = this.supplierQuery.toLowerCase();
                return this.supplierResults.filter(s => 
                    s.name.toLowerCase().includes(q)
                );
            },
            selectSupplier(s) {
                this.supplierQuery = s.name;
                this.selectedSupplierId = s.id;
                this.showSupplierDropdown = false;
            },
            init() {
                const el = document.getElementById('purchase-edit-initial-items');
                if (el && el.textContent) { this.items = JSON.parse(el.textContent); }
            },
            async searchProducts() {
                if (this.isLocked) return;
                // Require supplier selection before searching
                if (!this.selectedSupplierId) { this.searchResults = []; return; }
                clearTimeout(this.searchTimer);
                const q = this.productQuery.trim();
                this.searchTimer = setTimeout(async () => {
                    this.searchLoading = true;
                    try {
                        const url = '{{ route('purchases.products.search') }}?q=' + encodeURIComponent(q);
                        const r = await fetch(url, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } });
                        this.searchResults = await r.json();
                    } catch (e) { this.searchResults = []; }
                    this.searchLoading = false;
                }, 250);
            },
            addProduct(p) {
                if (this.isLocked) return;
                if (this.items.find(i => i.product_id === p.id)) return;
                this.items.push({
                    product_id: p.id,
                    name: p.name,
                    sku: p.sku,
                    unit_label: p.unit_label,
                    availability: p.availability ?? 0,
                    quantity: 1,
                    unit_price: p.fixed_price || 0,
                    unit_discount: 0,
                    unit_discount_type: 'fixed',
                    discount_rate: 0,
                    discount_type: 'fixed',
                });
                this.productQuery = '';
                this.searchResults = [];
            },
            removeRow(i) { if (!this.isLocked) this.items.splice(i, 1); },
            rowSubTotal(i) {
                const r = this.items[i];
                return (parseFloat(r.quantity) || 0) * (parseFloat(r.unit_price) || 0);
            },
            rowTotal(i) {
                const r = this.items[i];
                const sub = this.rowSubTotal(i);
                const unitDiscountType = r.unit_discount_type || 'fixed';
                const unitDiscount = Math.max(0, parseFloat(r.unit_discount) || 0);
                let discountAmount = (parseFloat(r.quantity) || 0) * unitDiscount;
                if (unitDiscountType === 'percentage') {
                    const pct = Math.min(unitDiscount, 100);
                    discountAmount = sub * pct / 100;
                }
                const discVal = Math.max(0, parseFloat(r.discount_rate) || 0);
                const type = r.discount_type || 'fixed';
                const remainder = Math.max(0, sub - discountAmount);
                if (type === 'percentage') {
                    const pct = Math.min(discVal, 100);
                    return Math.max(0, remainder - (remainder * pct / 100));
                }
                return Math.max(0, remainder - discVal);
            },
            get subtotal() {
                return this.items.reduce((a, _, i) => a + this.rowTotal(i), 0);
            },
            get payable() {
                const discVal = Math.max(0, parseFloat(this.discount) || 0);
                if (this.discountType === 'percentage') {
                    const pct = Math.min(discVal, 100);
                    const discountAmount = this.subtotal * pct / 100;
                    return Math.max(0, this.subtotal - discountAmount);
                }
                return Math.max(0, this.subtotal - discVal);
            },
        }"
    >
        {{-- Header --}}
        <div class="flex flex-col gap-3 sm:flex-row sm:justify-between sm:items-center">
            <h2 class="text-xl font-bold text-[#0a1233]">Edit Purchase</h2>
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('purchases.all') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50">
                    <svg class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                    Back
                </a>
                <button type="submit" form="purchase-edit-form" :disabled="isLocked || items.length === 0" class="inline-flex items-center px-4 py-2 rounded-lg bg-[#5542ff] text-white text-sm font-semibold hover:bg-[#4736d6] disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                    <svg class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                    Update
                </button>
                <a href="{{ route('purchases.receipt', $purchase) }}" class="inline-flex items-center px-4 py-2 rounded-lg bg-[#48cf82] text-white text-sm font-bold hover:bg-[#3dbd75] transition-colors shadow-sm">
                    <svg class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                    Receive
                </a>

            </div>
        </div>

        {{-- Locked order warning --}}
        @if($isLocked)
            <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 flex gap-3 items-start">
                <svg class="h-5 w-5 text-red-500 shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                <div class="text-sm">
                    @if($lockReason === 'received')
                        <p class="font-bold text-red-800">Items have been received on this purchase</p>
                        <p class="text-red-700 mt-1">You cannot change the order after stock has been received. Use <strong>Receive</strong> for remaining quantities or <strong>Add Return</strong> to reverse received stock.</p>
                    @else
                        <p class="font-bold text-red-800">A return has been recorded for this purchase</p>
                        <p class="text-red-700 mt-1">You cannot edit a purchase after a return has been recorded.</p>
                    @endif
                </div>
            </div>
        @endif

        @if(session('success'))
            <div class="rounded-lg bg-green-50 border border-green-200 text-green-800 px-4 py-3 text-sm">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="rounded-lg bg-red-50 border border-red-200 text-red-800 px-4 py-3 text-sm">{{ session('error') }}</div>
        @endif

        <form id="purchase-edit-form" action="{{ route('purchases.update', $purchase) }}" method="POST" class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-6">
            @csrf
            @method('PUT')

            {{-- Row 1: Invoice, Supplier, Purchase Date, Delivery Date --}}
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-bold text-gray-700 uppercase mb-1">Invoice No. <span class="text-red-500">*</span></label>
                    <input type="text" name="invoice_no" value="{{ old('invoice_no', $purchase->invoice_no) }}" :disabled="isLocked" required class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:ring-1 focus:ring-[#4634ff] disabled:bg-gray-100" />
                    @error('invoice_no')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div class="relative" @click.outside="showSupplierDropdown = false">
                    <label class="block text-sm font-bold text-gray-700 uppercase mb-1">Supplier <span class="text-red-500">*</span></label>
                    <div class="flex rounded-lg border border-gray-200 overflow-hidden" :class="isLocked ? 'opacity-50 pointer-events-none' : ''">
                        <span class="px-3 py-2 bg-gray-100 text-gray-400"><svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg></span>
                        <input type="text" x-model="supplierQuery" @focus="showSupplierDropdown = true" @input="showSupplierDropdown = true" autocomplete="off" placeholder="Search supplier name..." class="flex-1 px-3 py-2 text-sm focus:outline-none focus:ring-0" required />
                    </div>
                    <input type="hidden" name="supplier_id" x-model="selectedSupplierId" />
                    <div x-show="showSupplierDropdown && supplierResults.length > 0 && getFilteredSuppliers().length > 0 && !isLocked" x-cloak class="absolute z-20 mt-1 w-full bg-white border border-gray-200 rounded-lg shadow-lg max-h-40 overflow-y-auto scroll-smooth">
                        <template x-for="s in getFilteredSuppliers()" :key="s.id">
                            <button type="button" @click="selectSupplier(s)" class="w-full text-left px-4 py-2 text-sm hover:bg-gray-50 border-b border-gray-50 last:border-0">
                                <span x-text="s.name"></span>
                            </button>
                        </template>
                    </div>
                    @error('supplier_id')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-700 uppercase mb-1">Purchase Date</label>
                    <input type="date" name="purchase_date" value="{{ old('purchase_date', $purchase->purchase_date->format('Y-m-d')) }}" :disabled="isLocked" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:ring-1 focus:ring-[#4634ff] disabled:bg-gray-100" />
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-700 uppercase mb-1">Delivery Date</label>
                    <input type="date" name="delivery_date" value="{{ old('delivery_date', $purchase->delivery_date?->format('Y-m-d')) }}" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:ring-1 focus:ring-[#4634ff]" />
                </div>
            </div>

            {{-- Product Search --}}
            <div class="relative" data-product-search @click.outside="searchResults = []">
                <label class="block text-sm font-bold text-gray-700 uppercase mb-1">Search Product <span class="text-red-500">*</span></label>
                <div class="flex rounded-lg border border-gray-200 overflow-hidden shadow-sm">
                    <span class="px-3 py-2 bg-gray-50 text-gray-400 border-r border-gray-200"><svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg></span>
                    <input type="text" x-model="productQuery" @input="searchProducts()" @click="searchProducts()" @keydown.enter.prevent="if (searchResults.length) { addProduct(searchResults[0]); }" autocomplete="off" :disabled="isLocked || !selectedSupplierId" :placeholder="selectedSupplierId ? 'Type Product Name or SKU...' : 'Select supplier first to search products'" class="flex-1 px-3 py-2 text-sm focus:outline-none focus:ring-0" />
                </div>
                <div x-show="searchResults.length && !isLocked && selectedSupplierId" x-cloak class="absolute z-20 mt-1 w-full bg-white border border-gray-200 rounded-lg shadow-lg max-h-56 overflow-y-auto">
                    <template x-for="p in searchResults" :key="p.id">
                        <button type="button" @click="addProduct(p)" class="w-full text-left px-4 py-3 text-sm hover:bg-[#5542ff]/5 border-b border-gray-50 last:border-0 transition-colors">
                            <span class="font-bold text-gray-800" x-text="p.name"></span>
                            <span class="text-gray-400 text-xs ml-2" x-text="p.sku"></span>
                            <span class="text-gray-400 text-xs ml-2">(Stock: <span x-text="p.availability"></span>)</span>
                        </button>
                    </template>
                </div>
            </div>

            {{-- Items Table --}}
            <div class="overflow-x-auto rounded-xl border border-gray-200 shadow-sm">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="gradient-bg text-white">
                            <th class="px-4 py-3 text-left text-[10px] font-black uppercase tracking-widest">Product Details</th>
                            <th class="px-4 py-3 text-center text-[10px] font-black uppercase tracking-widest">Availability</th>
                            <th class="px-4 py-3 text-center text-[10px] font-black uppercase tracking-widest">Quantity</th>
                            <th class="px-4 py-3 text-center text-[10px] font-black uppercase tracking-widest">Price</th>
                            <th class="px-4 py-3 text-center text-[10px] font-black uppercase tracking-widest">Unit Discount</th>
                            <th class="px-4 py-3 text-center text-[10px] font-black uppercase tracking-widest">Sub Total</th>
                            <th class="px-4 py-3 text-center text-[10px] font-black uppercase tracking-widest">Discount</th>
                            <th class="px-4 py-3 text-center text-[10px] font-black uppercase tracking-widest">Total</th>
                            <th class="px-4 py-3 text-right text-[10px] font-black uppercase tracking-widest">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <template x-for="(item, index) in items" :key="index">
                            <tr class="hover:bg-gray-50/50 transition-colors">
                                <td class="px-4 py-4">
                                    <input type="hidden" :name="'items['+index+'][product_id]'" :value="item.product_id" />
                                    <div class="font-bold text-gray-800" x-text="item.name"></div>
                                    <div class="text-xs text-gray-400 mt-0.5" x-text="item.sku"></div>
                                </td>
                                <td class="px-4 py-4 text-center">
                                    <span class="text-sm font-semibold text-gray-700" x-text="(parseFloat(item.availability) || 0).toFixed(4).replace(/0+$/,'').replace(/\\.$/,'')"></span>
                                </td>
                                <td class="px-4 py-4 text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <input type="number" step="0.0001" min="0.0001" :name="'items['+index+'][quantity]'" x-model="item.quantity" :disabled="isLocked" required class="w-20 rounded border border-gray-200 px-2 py-1 text-center disabled:bg-gray-100" />
                                        <span class="text-xs text-gray-400 font-bold uppercase" x-text="item.unit_label"></span>
                                    </div>
                                </td>
                                <td class="px-4 py-4 text-center">
                                    <div class="flex items-center justify-center">
                                        <span class="text-gray-400 mr-1 text-xs">{{ currencySymbol() }}</span>
                                        <input type="number" step="0.01" min="0" :name="'items['+index+'][unit_price]'" x-model.number="item.unit_price" :disabled="isLocked" required class="w-24 rounded border border-gray-200 px-2 py-1 text-right disabled:bg-gray-100" />
                                    </div>
                                </td>
                                <td class="px-4 py-4 text-center">
                                    <div class="flex items-center justify-center">
                                        <div class="flex items-stretch rounded border border-gray-200 w-32 bg-white" x-data="{ open: false, updatePos() { const rect = $refs.btn.getBoundingClientRect(); $refs.menu.style.top = (rect.bottom + 4) + 'px'; $refs.menu.style.left = rect.left + 'px'; } }" @click.outside="open = false" @scroll.window="open = false" @resize.window="open = false">
                                            <div class="relative flex items-stretch">
                                                <button type="button" x-ref="btn" @click="if(!isLocked) { open = !open; if(open) $nextTick(() => updatePos()) }" class="px-2 bg-gray-100 text-gray-500 text-xs hover:bg-gray-200 transition-colors flex items-center justify-center gap-0.5 cursor-pointer rounded-l h-full border-r border-gray-200" :disabled="isLocked">
                                                    <span x-text="(item.unit_discount_type || 'fixed') === 'percentage' ? '%' : '{{ currencySymbol() }}'"></span>
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                                </button>
                                                <template x-teleport="body">
                                                    <div x-show="open" x-cloak x-ref="menu" @click.outside="open = false" class="fixed z-[9999] bg-white border border-gray-200 rounded shadow-lg w-20">
                                                        <button type="button" @click="item.unit_discount_type = 'fixed'; open = false" class="w-full text-left px-3 py-1.5 text-xs hover:bg-gray-50" :class="(item.unit_discount_type || 'fixed') === 'fixed' ? 'font-bold text-[#4634ff]' : 'text-gray-600'">{{ currencySymbol() }} Fixed</button>
                                                        <button type="button" @click="item.unit_discount_type = 'percentage'; open = false" class="w-full text-left px-3 py-1.5 text-xs hover:bg-gray-50" :class="item.unit_discount_type === 'percentage' ? 'font-bold text-[#4634ff]' : 'text-gray-600'">% Percent</button>
                                                    </div>
                                                </template>
                                            </div>
                                            <input type="number" step="0.01" min="0" :name="'items['+index+'][unit_discount]'" x-model="item.unit_discount" :disabled="isLocked" class="flex-1 w-full px-2 py-1.5 min-w-0 text-right [appearance:auto] disabled:bg-gray-100 rounded-r focus:outline-none bg-transparent" />
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-4 text-center">
                                    <span class="text-gray-400 mr-1 text-xs">{{ currencySymbol() }}</span>
                                    <span class="font-black text-gray-800" x-text="rowSubTotal(index).toFixed(2)"></span>
                                </td>
                                <td class="px-4 py-4 text-center">
                                    <input type="hidden" :name="'items['+index+'][discount_rate]'" :value="item.discount_rate" />
                                    <input type="hidden" :name="'items['+index+'][discount_type]'" :value="item.discount_type || 'fixed'" />
                                    <input type="hidden" :name="'items['+index+'][unit_discount_type]'" :value="item.unit_discount_type || 'fixed'" />
                                    <div class="flex items-center justify-center">
                                        <div class="flex items-stretch rounded border border-gray-200 w-28 bg-white" 
                                             x-data="{ 
                                                 open: false,
                                                 updatePos() {
                                                     const rect = $refs.btn.getBoundingClientRect();
                                                     $refs.menu.style.top = (rect.bottom + 4) + 'px';
                                                     $refs.menu.style.left = rect.left + 'px';
                                                 }
                                             }" 
                                             @click.outside="open = false"
                                             @scroll.window="open = false"
                                             @resize.window="open = false">
                                            <div class="relative flex items-stretch">
                                                <button type="button" x-ref="btn" @click="if(!isLocked) { open = !open; if(open) $nextTick(() => updatePos()) }" class="px-2 bg-gray-100 text-gray-500 text-xs hover:bg-gray-200 transition-colors flex items-center justify-center gap-0.5 cursor-pointer rounded-l h-full border-r border-gray-200" :disabled="isLocked">
                                                    <span x-text="(item.discount_type || 'fixed') === 'percentage' ? '%' : '{{ currencySymbol() }}'" ></span>
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                                </button>
                                                <template x-teleport="body">
                                                    <div x-show="open" x-cloak x-ref="menu" @click.outside="open = false" class="fixed z-[9999] bg-white border border-gray-200 rounded shadow-lg w-20">
                                                        <button type="button" @click="item.discount_type = 'fixed'; open = false" class="w-full text-left px-3 py-1.5 text-xs hover:bg-gray-50" :class="(item.discount_type || 'fixed') === 'fixed' ? 'font-bold text-[#4634ff]' : 'text-gray-600'">{{ currencySymbol() }} Fixed</button>
                                                        <button type="button" @click="item.discount_type = 'percentage'; open = false" class="w-full text-left px-3 py-1.5 text-xs hover:bg-gray-50" :class="item.discount_type === 'percentage' ? 'font-bold text-[#4634ff]' : 'text-gray-600'">% Percent</button>
                                                    </div>
                                                </template>
                                            </div>
                                            <input type="number" step="0.01" min="0" :max="(item.discount_type || 'fixed') === 'percentage' ? 100 : undefined" x-model="item.discount_rate" :disabled="isLocked" class="flex-1 w-full px-2 py-1.5 min-w-0 text-center [appearance:auto] disabled:bg-gray-100 rounded-r focus:outline-none bg-transparent" />
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-4 text-center">
                                    <span class="text-gray-400 mr-1 text-xs">{{ currencySymbol() }}</span>
                                    <span class="font-black text-gray-800" x-text="rowTotal(index).toFixed(2)"></span>
                                </td>
                                <td class="px-4 py-4 text-right">
                                    <button type="button" @click="removeRow(index)" :disabled="isLocked" class="p-2 text-red-500 hover:bg-red-50 rounded-lg transition-colors disabled:opacity-40 disabled:pointer-events-none">
                                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                    </button>
                                </td>
                            </tr>
                        </template>
                        <tr x-show="items.length === 0">
                            <td colspan="9" class="px-4 py-12 text-center text-gray-400 italic bg-gray-50/30">
                                <svg class="h-12 w-12 mx-auto mb-2 text-gray-200" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" /></svg>
                                Add products using the search field above.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            {{-- Bottom: Note + Totals --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                {{-- Left: Note --}}
                <div class="space-y-4">
                    <div class="pt-4">
                        <label class="block text-sm font-bold text-gray-700 uppercase mb-1">Purchase Note</label>
                        <textarea name="note" rows="4" :disabled="isLocked" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:ring-1 focus:ring-[#4634ff] disabled:bg-gray-100" placeholder="Enter any internal notes here...">{{ old('note', $purchase->note) }}</textarea>
                    </div>
                </div>

                {{-- Right: Totals --}}
                {{-- Right: Totals --}}
                <div class="space-y-3 pt-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Subtotal <span class="text-red-500">*</span></label>
                        <div class="flex rounded-lg border border-gray-200 bg-gray-50 overflow-hidden">
                            <span class="px-3 py-2 bg-gray-100 text-gray-500">{{ currencySymbol() }}</span>
                            <input type="text" readonly :value="subtotal.toFixed(2)" class="flex-1 px-3 py-2 bg-gray-50 text-sm" />
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Discount</label>
                        <div class="flex items-stretch rounded-lg border border-gray-200 w-full bg-white" 
                             x-data="{ 
                                 open: false,
                                 updatePos() {
                                     const rect = $refs.btn.getBoundingClientRect();
                                     $refs.menu.style.top = (rect.bottom + 4) + 'px';
                                     $refs.menu.style.left = rect.left + 'px';
                                 }
                             }" 
                             @click.outside="open = false"
                             @scroll.window="open = false"
                             @resize.window="open = false">
                            <div class="relative flex items-stretch">
                                <button type="button" x-ref="btn" @click="if(!isLocked) { open = !open; if(open) $nextTick(() => updatePos()) }" class="px-3 bg-gray-100 text-gray-500 hover:bg-gray-200 transition-colors flex items-center justify-center gap-1 cursor-pointer rounded-l-lg h-full border-r border-gray-200" :disabled="isLocked">
                                    <span x-text="discountType === 'percentage' ? '%' : '{{ currencySymbol() }}'"></span>
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                </button>
                                <template x-teleport="body">
                                    <div x-show="open" x-cloak x-ref="menu" @click.outside="open = false" class="fixed z-[9999] bg-white border border-gray-200 rounded-lg shadow-lg w-24">
                                        <button type="button" @click="discountType = 'fixed'; open = false" class="w-full text-left px-3 py-2 text-sm hover:bg-gray-50" :class="discountType === 'fixed' ? 'font-bold text-[#4634ff]' : 'text-gray-600'">{{ currencySymbol() }} Fixed</button>
                                        <button type="button" @click="discountType = 'percentage'; open = false" class="w-full text-left px-3 py-2 text-sm hover:bg-gray-50" :class="discountType === 'percentage' ? 'font-bold text-[#4634ff]' : 'text-gray-600'">% Percent</button>
                                    </div>
                                </template>
                            </div>
                            <input type="number" name="discount" step="0.01" min="0" :max="discountType === 'percentage' ? 100 : undefined" x-model="discount" :disabled="isLocked" class="flex-1 w-full px-3 py-2 text-sm focus:ring-1 focus:ring-[#4634ff] disabled:bg-gray-100 rounded-r-lg focus:outline-none bg-transparent" />
                            <input type="hidden" name="discount_type" :value="discountType" />
                        </div>
                    </div>
                    <input type="hidden" name="tax_rate" value="0" />

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Already Paid</label>
                        <div class="flex rounded-lg border border-gray-200 bg-gray-50 overflow-hidden">
                            <span class="px-3 py-2 bg-gray-100 text-gray-500">{{ currencySymbol() }}</span>
                            <input type="text" readonly value="{{ number_format((float) $purchase->paid_amount, 2, '.', '') }}" class="flex-1 px-3 py-2 bg-gray-50 text-sm" />
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Grand Total</label>
                        <div class="flex rounded-lg border border-gray-200 bg-gray-50 overflow-hidden">
                            <span class="px-3 py-2 bg-gray-100 text-gray-500">{{ currencySymbol() }}</span>
                            <input type="text" readonly :value="payable.toFixed(2)" class="flex-1 px-3 py-2 bg-gray-50 text-sm font-semibold" />
                        </div>
                    </div>
                </div>
            </div>
        </form>

    </div>
</x-app-layout>

