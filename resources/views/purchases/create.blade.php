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
    <div
        class="max-w-6xl mx-auto space-y-6"
        x-data="{
            productQuery: '',
            searchResults: [],
            searchLoading: false,
            searchTimer: null,
            items: [],
            discount: {{ old('discount', 0) }},
            discountType: 'fixed',
            taxRate: {{ old('tax_rate', 0) }},
            supplierQuery: @js(old('supplierQuery', optional($suppliers->firstWhere('id', old('supplier_id')))->name ?? '')),
            supplierResults: @js($suppliers),
            selectedSupplierId: @js(old('supplier_id', null)),
            showSupplierDropdown: false,
            
            async searchProducts() {
                // Require supplier selection before searching
                if (!this.selectedSupplierId) { this.searchResults = []; return; }
                clearTimeout(this.searchTimer);
                const q = this.productQuery.trim();
                this.searchTimer = setTimeout(async () => {
                    this.searchLoading = true;
                    try {
                        let url = '{{ route('purchases.products.search') }}?q=' + encodeURIComponent(q);
                        const r = await fetch(url, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } });
                        this.searchResults = await r.json();
                    } catch (e) { this.searchResults = []; }
                    this.searchLoading = false;
                }, 250);
            },
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
            addProduct(p) {
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
                // Clear the search and close the modal/dropdown after selecting
                this.productQuery = '';
                this.searchResults = [];
            },
            removeRow(i) {
                this.items.splice(i, 1);
            },
            rowSubTotal(i) {
                const r = this.items[i];
                return (parseFloat(r.quantity) || 0) * (parseFloat(r.unit_price) || 0);
            },
            rowTotal(i) {
                const item = this.items[i];
                const sub = this.rowSubTotal(i);
                const unitDiscountType = item.unit_discount_type || 'fixed';
                const unitDiscount = Math.max(0, parseFloat(item.unit_discount) || 0);
                let unitDiscountAmount = (parseFloat(item.quantity) || 0) * unitDiscount;
                if (unitDiscountType === 'percentage') {
                    const pct = Math.min(unitDiscount, 100);
                    unitDiscountAmount = sub * pct / 100;
                }
                const discVal = Math.max(0, parseFloat(item.discount_rate) || 0);
                const type = item.discount_type || 'fixed';
                let remainder = Math.max(0, sub - unitDiscountAmount);
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
        <div class="flex justify-between items-center">
            <h2 class="text-xl font-bold text-[#0a1233]">Add Purchase</h2>
            <div class="flex items-center gap-2">
                <a href="{{ route('purchases.all') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50">
                    <svg class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                    Back
                </a>
                <button type="submit" form="purchase-create-form" :disabled="items.length === 0" class="inline-flex items-center px-4 py-2 rounded-lg bg-[#5542ff] text-white text-sm font-semibold hover:bg-[#4736d6] disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                    Place
                </button>
            </div>
        </div>

        <form id="purchase-create-form" action="{{ route('purchases.store') }}" method="POST" class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-6">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-bold text-gray-700 uppercase mb-1">Invoice No. <span class="text-red-500">*</span></label>
                    <input type="text" name="invoice_no" value="{{ old('invoice_no', $invoiceNo) }}" required class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:ring-1 focus:ring-[#4634ff] focus:border-[#4634ff]" />
                    @error('invoice_no')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div class="relative" @click.outside="showSupplierDropdown = false">
                    <label class="block text-sm font-bold text-gray-700 uppercase mb-1">Supplier <span class="text-red-500">*</span></label>
                    <div class="flex rounded-lg border border-gray-200 overflow-hidden">
                        <span class="px-3 py-2 bg-gray-100 text-gray-400"><svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg></span>
                        <input type="text" x-model="supplierQuery" @focus="showSupplierDropdown = true" @input="showSupplierDropdown = true" autocomplete="off" placeholder="Search supplier name..." class="flex-1 px-3 py-2 text-sm focus:outline-none focus:ring-0" required />
                    </div>
                    <input type="hidden" name="supplier_id" x-model="selectedSupplierId" />
                    <div x-show="showSupplierDropdown && supplierResults.length > 0 && getFilteredSuppliers().length > 0" x-cloak class="absolute z-20 mt-1 w-full bg-white border border-gray-200 rounded-lg shadow-lg max-h-40 overflow-y-auto scroll-smooth">
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
                    <input type="date" name="purchase_date" value="{{ old('purchase_date', now()->format('Y-m-d')) }}" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:ring-1 focus:ring-[#4634ff]" />
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-700 uppercase mb-1">Delivery Date</label>
                    <input type="date" name="delivery_date" value="{{ old('delivery_date') }}" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:ring-1 focus:ring-[#4634ff]" />
                </div>
            </div>

            <div class="relative" data-product-search @click.outside="searchResults = []">
                <label class="block text-sm font-bold text-gray-700 uppercase mb-1">Search Product <span class="text-red-500">*</span></label>
                <div class="flex rounded-lg border border-gray-200 overflow-hidden shadow-sm">
                    <span class="px-3 py-2 bg-gray-50 text-gray-400 border-r border-gray-200"><svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg></span>
                    <input type="text" x-model="productQuery" @input="searchProducts()" @click="searchProducts()" @keydown.enter.prevent="if (searchResults.length) { addProduct(searchResults[0]); }" autocomplete="off" :disabled="!selectedSupplierId" :placeholder="selectedSupplierId ? 'Type Product Name or SKU...' : 'Select supplier first to search products'" class="flex-1 px-3 py-2 text-sm focus:outline-none focus:ring-0" />
                </div>
                <div x-show="searchResults.length && selectedSupplierId" x-cloak class="absolute z-20 mt-1 w-full bg-white border border-gray-200 rounded-lg shadow-lg max-h-56 overflow-y-auto">
                    <template x-for="p in searchResults" :key="p.id">
                        <button type="button" @click="addProduct(p)" class="w-full text-left px-4 py-3 text-sm hover:bg-[#5542ff]/5 border-b border-gray-50 last:border-0 transition-colors">
                            <span class="font-bold text-gray-800" x-text="p.name"></span>
                            <span class="text-gray-400 text-xs ml-2" x-text="p.sku"></span>
                            <span class="text-gray-400 text-xs ml-2">(Stock: <span x-text="p.availability"></span>)</span>
                        </button>
                    </template>
                </div>
            </div>

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
                                        <input type="number" step="0.0001" min="0.0001" :name="'items['+index+'][quantity]'" x-model="item.quantity" required class="w-20 rounded border border-gray-200 px-2 py-1 text-center" />
                                        <span class="text-xs text-gray-400 font-bold uppercase" x-text="item.unit_label"></span>
                                    </div>
                                </td>
                                <td class="px-4 py-4 text-center">
                                    <div class="flex items-center justify-center">
                                        <span class="text-gray-400 mr-1 text-xs">{{ currencySymbol() }}</span>
                                        <input type="number" step="0.01" min="0" :name="'items['+index+'][unit_price]'" x-model.number="item.unit_price" required class="w-24 rounded border border-gray-200 px-2 py-1 text-right" />
                                    </div>
                                </td>
                                <td class="px-4 py-4 text-center">
                                    <div class="flex items-center justify-center">
                                        <div class="flex items-stretch rounded border border-gray-200 w-32 bg-white" x-data="{ open: false, updatePos() { const rect = $refs.btn.getBoundingClientRect(); $refs.menu.style.top = (rect.bottom + 4) + 'px'; $refs.menu.style.left = rect.left + 'px'; } }" @click.outside="open = false" @scroll.window="open = false" @resize.window="open = false">
                                            <div class="relative flex items-stretch">
                                                <button type="button" x-ref="btn" @click="open = !open; if(open) $nextTick(() => updatePos())" class="px-2 bg-gray-100 text-gray-500 text-xs hover:bg-gray-200 transition-colors flex items-center justify-center gap-0.5 cursor-pointer rounded-l h-full border-r border-gray-200">
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
                                            <input type="number" step="0.01" min="0" :name="'items['+index+'][unit_discount]'" x-model="item.unit_discount" class="flex-1 w-full px-2 py-1.5 min-w-0 text-right [appearance:auto] rounded-r focus:outline-none bg-transparent" />
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-4 text-center">
                                    <span class="text-gray-400 mr-1 text-xs">{{ currencySymbol() }}</span>
                                    <span class="font-black text-gray-800" x-text="rowSubTotal(index).toFixed(2)"></span>
                                </td>
                                <td class="px-4 py-4 text-center">
                                    <input type="hidden" :name="'items['+index+'][unit_discount]'" :value="item.unit_discount" />
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
                                                <button type="button" x-ref="btn" @click="open = !open; if(open) $nextTick(() => updatePos())" class="px-2 bg-gray-100 text-gray-500 text-xs hover:bg-gray-200 transition-colors flex items-center justify-center gap-0.5 cursor-pointer rounded-l h-full border-r border-gray-200">
                                                    <span x-text="(item.discount_type || 'fixed') === 'percentage' ? '%' : '{{ currencySymbol() }}'"></span>
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                                </button>
                                                <template x-teleport="body">
                                                    <div x-show="open" x-cloak x-ref="menu" @click.outside="open = false" class="fixed z-[9999] bg-white border border-gray-200 rounded shadow-lg w-20">
                                                        <button type="button" @click="item.discount_type = 'fixed'; open = false" class="w-full text-left px-3 py-1.5 text-xs hover:bg-gray-50" :class="(item.discount_type || 'fixed') === 'fixed' ? 'font-bold text-[#4634ff]' : 'text-gray-600'">{{ currencySymbol() }} Fixed</button>
                                                        <button type="button" @click="item.discount_type = 'percentage'; open = false" class="w-full text-left px-3 py-1.5 text-xs hover:bg-gray-50" :class="item.discount_type === 'percentage' ? 'font-bold text-[#4634ff]' : 'text-gray-600'">% Percent</button>
                                                    </div>
                                                </template>
                                            </div>
                                            <input type="number" step="0.01" min="0" :max="(item.discount_type || 'fixed') === 'percentage' ? 100 : undefined" x-model="item.discount_rate" class="flex-1 w-full px-2 py-1.5 min-w-0 text-center [appearance:auto] rounded-r focus:outline-none bg-transparent" />
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-4 text-center">
                                    <span class="text-gray-400 mr-1 text-xs">{{ currencySymbol() }}</span>
                                    <span class="font-black text-gray-800" x-text="rowTotal(index).toFixed(2)"></span>
                                </td>
                                <td class="px-4 py-4 text-right">
                                    <button type="button" @click="removeRow(index)" class="p-2 text-red-500 hover:bg-red-50 rounded-lg transition-colors">
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

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                {{-- Left: Note --}}
                <div class="space-y-4">
                    <div class="pt-4">
                        <label class="block text-sm font-bold text-gray-700 uppercase mb-1">Purchase Note</label>
                        <textarea name="note" rows="4" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:ring-1 focus:ring-[#4634ff]" placeholder="Enter any internal notes here...">{{ old('note') }}</textarea>
                    </div>
                </div>

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
                                <button type="button" x-ref="btn" @click="open = !open; if(open) $nextTick(() => updatePos())" class="px-3 bg-gray-100 text-gray-500 hover:bg-gray-200 transition-colors flex items-center justify-center gap-1 cursor-pointer rounded-l-lg h-full border-r border-gray-200">
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
                            <input type="number" name="discount" step="0.01" min="0" :max="discountType === 'percentage' ? 100 : undefined" x-model="discount" class="flex-1 w-full px-3 py-2 text-sm focus:ring-1 focus:ring-[#4634ff] focus:outline-none rounded-r-lg bg-transparent" />
                            <input type="hidden" name="discount_type" :value="discountType" />
                        </div>
                    </div>
                    <input type="hidden" name="tax_rate" value="0" />

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
