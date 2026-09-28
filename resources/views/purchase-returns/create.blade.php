<x-app-layout>
    @php
        $initialLines = collect($lines)
            ->map(function (array $l): array {
                return array_merge($l, [
                    'max_return' => (float) $l['stock_quantity'],
                ]);
            })
            ->values()
            ->all();
    @endphp
    <script type="application/json" id="purchase-return-initial-lines">@json($initialLines)</script>
    <div
        class="max-w-6xl mx-auto space-y-6"
        x-data="{
            items: [],
            discount: {{ old('discount', 0) }},
            init() {
                const el = document.getElementById('purchase-return-initial-lines');
                if (el && el.textContent) {
                    this.items = JSON.parse(el.textContent);
                }
            },
            getReturning(itemIndex) {
                const item = this.items[itemIndex];
                if (item.requires_batch_tracking) {
                    return item.selected_batches.reduce((sum, b) => sum + (parseFloat(b.quantity) || 0), 0);
                }
                return parseFloat(item.return_quantity) || 0;
            },
            batchTotal(itemIndex) {
                return this.getReturning(itemIndex);
            },
            addBatch(itemIndex) {
                this.items[itemIndex].selected_batches.push({
                    batch_id: '',
                    quantity: 0,
                });
            },
            removeBatch(itemIndex, batchIndex) {
                this.items[itemIndex].selected_batches.splice(batchIndex, 1);
            },
            getBatchAvailable(batch, item, currentBatchIndex) {
                const baseAvailable = parseFloat(batch.quantity_available || 0);
                let otherSelectedQty = 0;
                item.selected_batches.forEach((sb, idx) => {
                    if (sb.batch_id == batch.id && idx !== currentBatchIndex) {
                        otherSelectedQty += parseFloat(sb.quantity || 0);
                    }
                });
                return Math.max(0, baseAvailable - otherSelectedQty);
            },
            isBatchOverLimit(batch, item, batchIndex) {
                if (!batch || batch.quantity <= 0) return false;
                const batchInfo = item.batches.find(b => b.id == batch.batch_id);
                if (!batchInfo) return false;
                const available = this.getBatchAvailable(batchInfo, item, batchIndex);
                return parseFloat(batch.quantity) > available;
            },
            isOverLimit(itemIndex) {
                const item = this.items[itemIndex];
                const maxReturn = item.max_return || item.stock_quantity;
                return this.getReturning(itemIndex) > maxReturn;
            },
            rowTotal(i) {
                const r = this.items[i];
                return this.getReturning(i) * (parseFloat(r.unit_price) || 0);
            },
            get subtotal() {
                return this.items.reduce((a, _, i) => a + this.rowTotal(i), 0);
            },
            get receivable() { return Math.max(0, this.subtotal - (parseFloat(this.discount) || 0)); },
            get anyItemsSelected() {
                return this.items.some((item, i) => this.getReturning(i) > 0);
            },
            get hasErrors() {
                return this.items.some((item, i) => {
                    if (item.requires_batch_tracking) {
                        return item.selected_batches.some((batch, j) => this.isBatchOverLimit(batch, item, j));
                    } else {
                        return this.isOverLimit(i);
                    }
                });
            }
        }"
    >
        <div class="flex justify-between items-center">
            <h2 class="text-xl font-bold text-[#0a1233]">Add Purchase Return</h2>
            <div class="flex items-center gap-2">
                <a href="{{ route('purchases.all') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50">
                    <svg class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                    Back
                </a>
                <button type="submit" form="purchase-return-form" :disabled="!anyItemsSelected || hasBatchErrors" class="inline-flex items-center px-4 py-2 rounded-lg bg-[#5542ff] text-white text-sm font-semibold hover:bg-[#4736d6] disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                    Submit Return
                </button>
            </div>
        </div>

        @if ($errors->any())
            <div class="bg-red-50 border border-red-200 rounded-xl p-4 flex items-start gap-3">
                <svg class="h-5 w-5 text-red-500 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <div class="flex-1">
                    <h4 class="font-bold text-red-800 text-sm mb-2">Please fix the following errors:</h4>
                    <ul class="text-sm text-red-700 space-y-1">
                        @foreach ($errors->all() as $error)
                            <li class="flex items-start gap-2">
                                <span class="text-red-400 mt-0.5">•</span>
                                {{ $error }}
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        <form id="purchase-return-form" action="{{ route('purchases.return.store', $purchase) }}" method="POST" class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-6">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-bold text-gray-700 uppercase mb-1">Invoice No.</label>
                    <div class="flex rounded-lg border border-gray-200 bg-gray-50 overflow-hidden">
                        <span class="px-3 py-2 bg-gray-100 text-gray-500 text-sm border-r border-gray-200"><svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg></span>
                        <input type="text" value="{{ $invoiceNo }}" disabled class="flex-1 px-3 py-2 text-sm bg-gray-50 text-gray-600" />
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-700 uppercase mb-1">Supplier</label>
                    <div class="flex rounded-lg border border-gray-200 bg-gray-50 overflow-hidden">
                        <span class="px-3 py-2 bg-gray-100 text-gray-500 text-sm border-r border-gray-200"><svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg></span>
                        <input type="text" value="{{ $purchase->supplier->name }}" disabled class="flex-1 px-3 py-2 text-sm bg-gray-50 text-gray-600" />
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-700 uppercase mb-1">Return Date <span class="text-red-500">*</span></label>
                    <div class="flex rounded-lg border border-gray-200 overflow-hidden shadow-sm">
                        <span class="px-3 py-2 bg-gray-50 text-gray-400 border-r border-gray-200"><svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg></span>
                        <input type="date" name="return_date" value="{{ old('return_date', now()->format('Y-m-d')) }}" required class="flex-1 px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-[#5542ff] focus:border-[#5542ff]" />
                    </div>
                    @error('return_date')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="space-y-4">
                <div class="flex items-center justify-between pb-2 border-b border-gray-200">
                    <h3 class="text-sm font-black uppercase text-[#0a1233] tracking-wider">Products to Return</h3>
                    <span class="text-xs text-gray-500">Select batches and quantities to return</span>
                </div>
                
                <template x-for="(item, itemIndex) in items" :key="itemIndex">
                    <div class="border border-gray-200 rounded-xl overflow-hidden shadow-sm" :class="{ 'ring-2 ring-[#5542ff]/20 border-[#5542ff]/30': getReturning(itemIndex) > 0 && !hasErrors, 'ring-2 ring-red-200 border-red-300': hasErrors }">
                        <div class="bg-gradient-to-r from-gray-50 to-white px-5 py-4 border-b border-gray-100">
                            <div class="flex flex-wrap gap-4 items-center justify-between">
                                <div class="flex items-center gap-4">
                                    <div class="flex-shrink-0 w-10 h-10 rounded-lg bg-[#5542ff]/10 flex items-center justify-center">
                                        <svg class="h-5 w-5 text-[#5542ff]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" /></svg>
                                    </div>
                                    <div>
                                        <h3 class="font-bold text-[#0a1233] text-base" x-text="item.product_name"></h3>
                                        <p class="text-xs text-gray-500 font-medium" x-text="'SKU: ' + item.sku"></p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-6">
                                    <div class="text-right">
                                        <p class="text-[10px] font-bold text-gray-500 uppercase tracking-wider">Stock Available</p>
                                        <p class="text-sm font-black text-[#0a1233]"><span x-text="parseFloat(item.stock_quantity).toFixed(4).replace(/0+$/,'').replace(/\\.$/,'')"></span> <span class="text-xs font-medium text-gray-500" x-text="item.unit_label"></span></p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-[10px] font-bold text-gray-500 uppercase tracking-wider">Returning</p>
                                        <p class="text-sm font-black" :class="getReturning(itemIndex) > 0 ? 'text-[#5542ff]' : 'text-gray-400'"><span x-text="getReturning(itemIndex).toFixed(4).replace(/0+$/,'').replace(/\\.$/,'') || '0'"></span> <span class="text-xs font-medium" :class="getReturning(itemIndex) > 0 ? 'text-[#5542ff]' : 'text-gray-400'" x-text="item.unit_label"></span></p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-[10px] font-bold text-gray-500 uppercase tracking-wider">Unit Price</p>
                                        <p class="text-sm font-black text-[#0a1233]">{{ currencySymbol() }}<span x-text="parseFloat(item.unit_price).toFixed(2)"></span></p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <input type="hidden" :name="'items['+itemIndex+'][purchase_item_id]'" :value="item.purchase_item_id" />

                        <div class="p-5 space-y-4">
                            <!-- For products that require batch tracking -->
                            <template x-if="item.requires_batch_tracking">
                                <div>
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-2">
                                            <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16" /></svg>
                                            <h4 class="text-sm font-bold text-gray-700 uppercase tracking-wider">Batch Selection</h4>
                                        </div>
                                        <button type="button" @click="addBatch(itemIndex)" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-white bg-[#5542ff] hover:bg-[#4736d6] rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-[#5542ff]/50 transition-colors">
                                            <svg class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" /></svg>
                                            Add Batch
                                        </button>
                                    </div>

                                    <div class="space-y-2">
                                        <template x-for="(batch, batchIndex) in item.selected_batches" :key="batchIndex">
                                            <div class="flex gap-3 items-end p-3 bg-gray-50/50 rounded-lg border transition-all" :class="{ 'border-red-300 bg-red-50/70': isBatchOverLimit(batch, item, batchIndex), 'border-gray-100 hover:border-gray-200 hover:bg-gray-50': !isBatchOverLimit(batch, item, batchIndex) }">
                                                <div class="flex-1">
                                                    <label class="block text-[10px] font-bold text-gray-600 uppercase tracking-wider mb-1.5">Batch</label>
                                                    <select 
                                                        :name="'items['+itemIndex+'][batches]['+batchIndex+'][batch_id]'" 
                                                        :value="batch.batch_id"
                                                        @change="batch.batch_id = $event.target.value"
                                                        class="w-full rounded-lg border px-3 py-2 text-sm bg-white focus:ring-2 transition-all"
                                                        :class="{ 'border-red-300 focus:ring-red-200 focus:border-red-500': isBatchOverLimit(batch, item, batchIndex), 'border-gray-200 focus:ring-[#5542ff]/20 focus:border-[#5542ff]': !isBatchOverLimit(batch, item, batchIndex) }"
                                                    >
                                                        <option value="">Select a batch...</option>
                                                        <template x-for="b in item.batches" :key="b.id">
                                                            <option 
                                                                :value="b.id" 
                                                                :disabled="!item.selected_batches.some(sb => sb.batch_id == b.id) && b.quantity_available <= 0"
                                                                :selected="String(b.id) === String(batch.batch_id)"
                                                                x-text="b.batch_number + ' (Available: ' + getBatchAvailable(b, item, batchIndex).toFixed(4).replace(/0+$/,'').replace(/\.$/,'') + ')' + (b.expiry_date ? ' | Exp: ' + b.expiry_date : '')"
                                                            ></option>
                                                        </template>
                                                    </select>
                                                </div>
                                                <div class="w-48">
                                                    <label class="block text-[10px] font-bold text-gray-600 uppercase tracking-wider mb-1.5">Quantity</label>
                                                    <div class="flex items-center gap-0 rounded-lg border overflow-hidden transition-all" :class="{ 'border-red-300 bg-white focus-within:ring-2 focus-within:ring-red-200 focus-within:border-red-500': isBatchOverLimit(batch, item, batchIndex), 'border-gray-200 bg-white focus-within:ring-2 focus-within:ring-[#5542ff]/20 focus-within:border-[#5542ff]': !isBatchOverLimit(batch, item, batchIndex) }">
                                                        <input
                                                            type="number"
                                                            step="0.0001"
                                                            min="0"
                                                            :name="'items['+itemIndex+'][batches]['+batchIndex+'][quantity]'"
                                                            x-model.number="batch.quantity"
                                                            class="flex-1 min-w-0 px-3 py-2 text-sm border-0 focus:ring-0 focus:outline-none"
                                                            placeholder="0"
                                                        />
                                                        <span class="px-3 py-2 bg-gray-100 text-gray-600 text-xs font-bold shrink-0 border-l" :class="{ 'border-red-200': isBatchOverLimit(batch, item, batchIndex), 'border-gray-200': !isBatchOverLimit(batch, item, batchIndex) }" x-text="item.unit_label"></span>
                                                    </div>
                                                    <div x-show="isBatchOverLimit(batch, item, batchIndex)" class="flex items-center gap-1 mt-1.5 text-red-600">
                                                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                        </svg>
                                                        <span class="text-[10px] font-semibold">
                                                            Exceeds available!
                                                        </span>
                                                    </div>
                                                </div>
                                                <div class="pb-0.5">
                                                    <button type="button" @click="removeBatch(itemIndex, batchIndex)" class="p-2 text-red-500 hover:text-red-700 hover:bg-red-50 rounded-lg transition-colors">
                                                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                        </svg>
                                                    </button>
                                                </div>
                                            </div>
                                        </template>
                                    </div>

                                    <div x-show="item.selected_batches.length === 0" class="text-center py-6">
                                        <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-gray-100 mb-3">
                                            <svg class="h-6 w-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" /></svg>
                                        </div>
                                        <p class="text-sm text-gray-500 font-medium">No batches selected</p>
                                        <p class="text-xs text-gray-400 mt-1">Click "Add Batch" to select batches to return</p>
                                    </div>
                                </div>
                            </template>

                            <!-- For products that do NOT require batch tracking -->
                            <template x-if="!item.requires_batch_tracking">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-[10px] font-bold text-gray-600 uppercase tracking-wider mb-1.5">Return Quantity</label>
                                        <div class="flex items-center gap-0 rounded-lg border overflow-hidden transition-all" :class="{ 'border-red-300 bg-white focus-within:ring-2 focus-within:ring-red-200 focus-within:border-red-500': isOverLimit(itemIndex), 'border-gray-200 bg-white focus-within:ring-2 focus-within:ring-[#5542ff]/20 focus-within:border-[#5542ff]': !isOverLimit(itemIndex) }">
                                            <input
                                                type="number"
                                                step="0.0001"
                                                min="0"
                                                :max="item.max_return || item.stock_quantity"
                                                :name="'items['+itemIndex+'][return_quantity]'"
                                                x-model.number="item.return_quantity"
                                                class="flex-1 min-w-0 px-3 py-2 text-sm border-0 focus:ring-0 focus:outline-none"
                                                placeholder="0"
                                            />
                                            <span class="px-3 py-2 bg-gray-100 text-gray-600 text-xs font-bold shrink-0 border-l" :class="{ 'border-red-200': isOverLimit(itemIndex), 'border-gray-200': !isOverLimit(itemIndex) }" x-text="item.unit_label"></span>
                                        </div>
                                        <p x-show="isOverLimit(itemIndex)" class="text-[10px] font-semibold text-red-600 mt-1">Exceeds returnable quantity!</p>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </template>
            </div>
            @error('items')<p class="text-red-500 text-sm bg-red-50 border border-red-200 rounded-lg px-4 py-3">{{ $message }}</p>@enderror

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 pt-4">
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-bold text-gray-700 uppercase mb-1">Return Note</label>
                        <div class="rounded-lg border border-gray-200 overflow-hidden shadow-sm">
                            <textarea name="note" rows="4" class="w-full px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-[#5542ff]" placeholder="Enter any notes about this return...">{{ old('note') }}</textarea>
                        </div>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-gray-50 to-gray-100 rounded-xl p-6 space-y-4 border border-gray-200 shadow-sm">
                    <div class="flex justify-between items-center pb-3 border-b border-gray-200/70">
                        <span class="text-sm font-bold text-gray-500 uppercase tracking-wider">Subtotal</span>
                        <span class="text-lg font-black text-[#0a1233]">{{ currencySymbol() }} <span x-text="subtotal.toFixed(2)"></span></span>
                    </div>
                    
                    <div class="flex justify-between items-center">
                        <span class="text-sm font-bold text-gray-500 uppercase tracking-wider">Discount</span>
                        <div class="flex rounded-lg border border-gray-200 bg-white overflow-hidden w-48 shadow-sm">
                            <span class="px-3 py-2 bg-gray-100 text-gray-500 text-xs font-bold">{{ currencySymbol() }}</span>
                            <input type="number" name="discount" step="0.01" min="0" x-model="discount" class="flex-1 px-3 py-2 text-sm text-center bg-white focus:ring-1 focus:ring-[#5542ff] border-0 [appearance:auto]" />
                        </div>
                    </div>
                    @error('discount')<p class="text-red-500 text-xs">{{ $message }}</p>@enderror

                    <div class="pt-4 mt-4 border-t-2 border-gray-200 flex justify-between items-center">
                        <span class="text-base font-black text-[#0a1233] uppercase tracking-wider">Receivable</span>
                        <span class="text-2xl font-black text-[#5542ff]">{{ currencySymbol() }} <span x-text="receivable.toFixed(2)"></span></span>
                    </div>
                </div>
            </div>
        </form>
    </div>
</x-app-layout>
