<?php if (isset($component)) { $__componentOriginal9ac128a9029c0e4701924bd2d73d7f54 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54 = $attributes; } ?>
<?php $component = App\View\Components\AppLayout::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('app-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\AppLayout::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
    <?php
        $statusConfig = [
            'ordered'   => ['label' => 'Ordered',   'classes' => 'bg-orange-100 text-orange-700 border-orange-200'],
            'partial'   => ['label' => 'Partial',   'classes' => 'bg-amber-100 text-amber-700 border-amber-200'],
            'received'  => ['label' => 'Received',  'classes' => 'bg-green-100 text-green-700 border-green-200'],
            'completed' => ['label' => 'Completed', 'classes' => 'bg-purple-100 text-purple-700 border-purple-200'],
        ];
        $sc = $statusConfig[$purchase->status] ?? ['label' => ucfirst($purchase->status), 'classes' => 'bg-gray-100 text-gray-500 border-gray-200'];
    ?>

    <div class="max-w-7xl mx-auto space-y-6" id="receipt-print-area"
        x-data="{
            batchModalOpen: false,
            selectedIdx: null,
            batchFilter: '',
            pendingBatches: [],
            stagedBatches: {},
            items: <?php echo \Illuminate\Support\Js::from($purchase->items->map(fn ($i) => [
                'id' => $i->id,
                'product_name' => $i->product_name,
                'sku' => $i->sku,
                'unit_label' => $i->unit_label,
                'unit_price' => (float) $i->unit_price,
                'unit_discount' => (float) ($i->unit_discount ?? 0),
                'discount_rate' => (float) ($i->discount_rate ?? 0),
                'discount_type' => $i->discount_type ?? 'fixed',
                'quantity' => (float) $i->quantity,
                'received_quantity' => (float) $i->received_quantity,
                'line_total' => (float) $i->line_total,
                'has_expiry' => (bool) (optional($i->product)->has_expiry ?? false),
                'requires_batch_tracking' => (bool) (optional($i->product)->requires_batch_tracking ?? true),
            ])->values()->all())->toHtml() ?>,
            batchesByItem: <?php echo \Illuminate\Support\Js::from($batchesByItem->map(fn ($batches) => $batches->map(fn ($b) => [
                'batch_number' => $b->batch_number,
                'quantity' => (float) $b->quantity,
                'expiry_date' => $b->expiry_date?->format('d/m/Y'),
            ]))->toArray())->toHtml() ?>,
            fmtQty(v) { return parseFloat(v || 0).toFixed(4).replace(/0+$/,'').replace(/\.$/,''); },
            discountedPrice(item) {
                const rate = Math.max(0, parseFloat(item.discount_rate) || 0);
                const unitDiscount = Math.max(0, parseFloat(item.unit_discount) || 0);
                const basePrice = Math.max(0, item.unit_price - unitDiscount);
                if ((item.discount_type || 'fixed') === 'percentage') {
                    const disc = Math.min(100, rate);
                    return Math.max(0, basePrice - (basePrice * disc / 100));
                }
                return Math.max(0, basePrice - rate);
            },
            stagedQty(item) {
                const rows = this.stagedBatches[item?.id];
                if (!rows) return 0;
                return rows.reduce((sum, r) => sum + (parseFloat(r.quantity) || 0), 0);
            },
            remaining(item) {
                const committed = (item?.received_quantity || 0);
                const staged    = this.stagedQty(item);
                return Math.max(0, (item?.quantity || 0) - committed - staged);
            },
            get remainingForItem() {
                if (!this.selectedItem) return 0;
                const committed = this.selectedItem.received_quantity || 0;
                // When editing a staged item, exclude ITS OWN staged qty so user can replace it freely
                const isEditing = this.selectedIdx !== null && !!this.stagedBatches[this.selectedItem.id];
                return Math.max(0, (this.selectedItem.quantity || 0) - committed);
            },
            get batchTotalQty() {
                return this.pendingBatches.reduce((sum, r) => sum + (parseFloat(r.quantity) || 0), 0);
            },
            get requiresExpiryForSelectedItem() {
                return !!this.selectedItem?.has_expiry;
            },
            get todayStr() {
                const d = new Date();
                const year = d.getFullYear();
                const month = String(d.getMonth() + 1).padStart(2, '0');
                const day = String(d.getDate()).padStart(2, '0');
                return `${year}-${month}-${day}`;
            },
            rowMissingExpiry(row) {
                return this.requiresExpiryForSelectedItem && !row.expiry_date;
            },
            rowPastExpiry(row) {
                return !!row.expiry_date && row.expiry_date < this.todayStr;
            },
            rowInvalidExpiry(row) {
                return this.rowMissingExpiry(row) || this.rowPastExpiry(row);
            },
            get hasMissingRequiredExpiry() {
                return this.pendingBatches.some(row => this.rowMissingExpiry(row));
            },
            get hasInvalidExpiry() {
                return this.pendingBatches.some(row => this.rowInvalidExpiry(row));
            },
            get canSubmitBatches() {
                if (this.pendingBatches.length === 0) return false;
                const total  = Math.round(this.batchTotalQty * 10000);
                const needed = Math.round(this.remainingForItem * 10000);
                return total === needed && total > 0 && !this.hasInvalidExpiry;
            },
            get hasStagedBatches() {
                return Object.keys(this.stagedBatches).length > 0;
            },
            openBatchModal(index) {
                const item = this.items[index];
                // Block opening if fully received AND nothing staged (nothing to edit)
                if (item.received_quantity >= item.quantity && !this.stagedBatches[item.id]) return;
                this.selectedIdx = index;
                this.batchFilter = '';
                const existing = this.stagedBatches[item.id];
                // remainingForItem needs selectedIdx set first, so compute after
                const committed = item.received_quantity || 0;
                const staged    = existing ? existing.reduce((s, r) => s + (parseFloat(r.quantity) || 0), 0) : 0;
                const rem       = Math.max(0, item.quantity - committed - staged);
                this.pendingBatches = existing
                    ? existing.map(r => ({ ...r, qtyError: '' }))
                    : [{ batch_number: '', expiry_date: '', quantity: rem, qtyError: '' }];
                this.batchModalOpen = true;
            },
            closeBatchModal() { this.batchModalOpen = false; this.selectedIdx = null; this.pendingBatches = []; },
            get selectedItem() { return this.selectedIdx !== null ? this.items[this.selectedIdx] : null; },
            get selectedBatches() { return this.selectedItem ? (this.batchesByItem[this.selectedItem.id] || []) : []; },
            get filteredBatches() {
                const f = this.batchFilter.toLowerCase();
                if (!f) return this.selectedBatches;
                return this.selectedBatches.filter(b => b.batch_number.toLowerCase().includes(f));
            },
            addBatchRow() {
                const assigned = this.pendingBatches.reduce((sum, r) => sum + (parseFloat(r.quantity) || 0), 0);
                const leftover = Math.max(0, parseFloat((this.remainingForItem - assigned).toFixed(4)));
                this.pendingBatches.push({ batch_number: '', expiry_date: '', quantity: leftover, qtyError: '' });
            },
            removeBatchRow(idx) {
                if (this.pendingBatches.length > 1) this.pendingBatches.splice(idx, 1);
            },
            validateBatchQty(idx) {
                const row = this.pendingBatches[idx];
                const qty = parseFloat(row.quantity) || 0;
                if (qty <= 0)                      row.qtyError = 'Quantity must be greater than 0.';
                else if (qty > this.remainingForItem) row.qtyError = 'Exceeds remaining quantity.';
                else                                row.qtyError = '';
            },
            saveBatchStaged() {
                if (!this.canSubmitBatches) return;
                const id = this.selectedItem.id;
                // Store only the new batches being added this session (not previously committed ones)
                this.stagedBatches = { ...this.stagedBatches, [id]: this.pendingBatches.map(r => ({
                    batch_number: r.batch_number || '',
                    expiry_date:  r.expiry_date  || '',
                    quantity:     parseFloat(r.quantity) || 0,
                })) };
                this.closeBatchModal();
            },
            clearStaged(itemId) {
                const s = { ...this.stagedBatches };
                delete s[itemId];
                this.stagedBatches = s;
            },
            autoReceive(index) {
                const item = this.items[index];
                if (item.requires_batch_tracking) return;
                const remaining = this.remaining(item);
                if (remaining <= 0) return;
                this.stagedBatches = {
                    ...this.stagedBatches,
                    [item.id]: [{
                        batch_number: '',
                        expiry_date: '',
                        quantity: remaining,
                    }]
                };
            },
            submitReceipt() {
                if (!this.hasStagedBatches) return;
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '<?php echo e(route('purchases.receive', $purchase)); ?>';
                const add = (n, v) => { const el = document.createElement('input'); el.type='hidden'; el.name=n; el.value=v; form.appendChild(el); };
                add('_token', '<?php echo e(csrf_token()); ?>');
                add('delivery_date', '');
                let i = 0;
                Object.entries(this.stagedBatches).forEach(([itemId, rows]) => {
                    rows.forEach(row => {
                        add(`items[${i}][purchase_item_id]`, itemId);
                        add(`items[${i}][receipt_quantity]`, parseFloat(row.quantity).toFixed(4));
                        if (row.batch_number) add(`items[${i}][batch_number]`, row.batch_number);
                        if (row.expiry_date)  add(`items[${i}][expiry_date]`,  row.expiry_date);
                        i++;
                    });
                });
                document.body.appendChild(form);
                form.submit();
            },
        }">

        
        <div class="flex flex-col gap-3 sm:flex-row sm:justify-between sm:items-center">
            <div class="flex items-center gap-3 flex-wrap">
                <h2 class="text-xl font-bold text-[#0a1233]"><?php echo e($purchase->invoice_no); ?></h2>
                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-bold border <?php echo e($sc['classes']); ?>"><?php echo e($sc['label']); ?></span>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <a href="<?php echo e(route('purchases.all')); ?>" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors">
                    <svg class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>Back
                </a>
                <?php if(auth()->user()->hasPermission('Store Purchase Return') && $purchase->hasReceivedItems()): ?>
                    <a href="<?php echo e(route('purchases.return.create', $purchase)); ?>" class="inline-flex items-center px-4 py-2 bg-white border border-amber-400 text-amber-700 rounded-lg text-sm font-semibold hover:bg-amber-50 transition-colors">
                        <svg class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6" /></svg>Add Return
                    </a>
                <?php endif; ?>
                <?php if(auth()->user()->hasPermission('Edit Purchase') && $purchase->isOrderEditable()): ?>
                    <a href="<?php echo e(route('purchases.edit', $purchase)); ?>" class="inline-flex items-center px-4 py-2 bg-white border border-[#5542ff] text-[#5542ff] rounded-lg text-sm font-semibold hover:bg-[#5542ff]/5 transition-colors">
                        <svg class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>Edit
                    </a>
                <?php endif; ?>
                <?php if(auth()->user()->hasPermission('Download Purchase Invoice PDF')): ?>
                    <a href="<?php echo e(route('purchases.invoice.pdf', $purchase)); ?>?print=true" target="_blank" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors">
                        <svg class="h-4 w-4 mr-2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" /></svg>Print
                    </a>
                    <a href="<?php echo e(route('purchases.invoice.pdf', $purchase)); ?>" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors">
                        <svg class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>PDF
                    </a>
                <?php endif; ?>
                
                <button type="button"
                    x-show="hasStagedBatches"
                    x-cloak
                    @click="submitReceipt()"
                    class="inline-flex items-center px-4 py-2 rounded-lg bg-[#48cf82] text-white text-sm font-bold hover:bg-[#3dbd75] transition-colors shadow-sm animate-pulse hover:animate-none">
                    <svg class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                    Receive
                </button>
            </div>
        </div>

        <?php if(session('success')): ?><div class="rounded-lg bg-green-50 border border-green-200 text-green-800 px-4 py-3 text-sm"><?php echo e(session('success')); ?></div><?php endif; ?>
        <?php if(session('error')): ?><div class="rounded-lg bg-red-50 border border-red-200 text-red-800 px-4 py-3 text-sm"><?php echo e(session('error')); ?></div><?php endif; ?>

        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4">
                <div class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Supplier</div>
                <div class="text-sm font-bold text-[#5542ff]"><?php echo e($purchase->supplier->name); ?></div>
                <div class="text-xs text-gray-500 mt-0.5"><?php echo e($purchase->supplier->phone ? (Illuminate\Support\Str::startsWith($purchase->supplier->phone, '+') ? $purchase->supplier->phone : '+'.$purchase->supplier->phone) : '—'); ?></div>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4">
                <div class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Purchase Date</div>
                <div class="text-sm font-bold text-gray-800"><?php echo e($purchase->purchase_date->format('d M, Y')); ?></div>
                <?php if($purchase->delivery_date): ?><div class="text-xs text-gray-500 mt-0.5">Delivery: <?php echo e($purchase->delivery_date->format('d M, Y')); ?></div><?php endif; ?>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4">
                <div class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Invoice</div>
                <div class="text-sm font-bold text-gray-800"><?php echo e($purchase->invoice_no); ?></div>
                <div class="text-xs text-gray-500 mt-0.5">Items: <?php echo e($purchase->items->count()); ?></div>
            </div>
        </div>

        
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm min-w-[900px]">
                    <thead>
                        <tr class="gradient-bg text-white">
                            <th class="px-4 py-3 text-left text-[10px] font-black uppercase tracking-widest w-8">#</th>
                            <th class="px-4 py-3 text-left text-[10px] font-black uppercase tracking-widest">Product Details</th>
                            <th class="px-4 py-3 text-center text-[10px] font-black uppercase tracking-widest">Price</th>
                            <th class="px-4 py-3 text-center text-[10px] font-black uppercase tracking-widest">Discount</th>
                            <th class="px-4 py-3 text-center text-[10px] font-black uppercase tracking-widest">Disc. Price</th>
                            <th class="px-4 py-3 text-center text-[10px] font-black uppercase tracking-widest">Unit</th>
                            <th class="px-4 py-3 text-center text-[10px] font-black uppercase tracking-widest">Order Qty</th>
                            <th class="px-4 py-3 text-center text-[10px] font-black uppercase tracking-widest">Line Total</th>
                            <th class="px-4 py-3 text-center text-[10px] font-black uppercase tracking-widest">Batch</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <template x-for="(item, index) in items" :key="item.id">
                        <tr class="hover:bg-gray-50/50 transition-colors"
                            :class="(item.received_quantity + stagedQty(item)) >= item.quantity ? 'bg-green-50/30' : ''">
                                <td class="px-4 py-4 text-gray-400 font-bold" x-text="index + 1"></td>
                                <td class="px-4 py-4">
                                    <div class="font-bold text-gray-800" x-text="item.product_name"></div>
                                    <div class="text-xs text-gray-400 mt-0.5" x-text="item.sku"></div>
                                </td>
                                <td class="px-4 py-4 text-center"><span class="text-gray-400 mr-0.5 text-xs"><?php echo e(currencySymbol()); ?></span><span class="font-semibold text-gray-800" x-text="item.unit_price.toFixed(2)"></span></td>
                                <td class="px-4 py-4 text-center">
                                    <span x-show="item.unit_discount > 0" class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-700 border border-emerald-200 mr-1" x-text="'<?php echo e(currencySymbol()); ?> ' + item.unit_discount.toFixed(2) + ' / unit'"></span>
                                    <span x-show="item.discount_rate > 0" class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-700 border border-amber-200" x-text="(item.discount_type === 'percentage' ? item.discount_rate.toFixed(1) + '%' : '<?php echo e(currencySymbol()); ?> ' + item.discount_rate.toFixed(2))"></span>
                                    <span x-show="item.unit_discount <= 0 && item.discount_rate <= 0" class="text-gray-300">—</span>
                                </td>
                                <td class="px-4 py-4 text-center"><span class="text-gray-400 mr-0.5 text-xs"><?php echo e(currencySymbol()); ?></span><span class="font-bold text-gray-800" x-text="discountedPrice(item).toFixed(2)"></span></td>
                                <td class="px-4 py-4 text-center"><span class="text-xs font-bold text-gray-500 uppercase" x-text="item.unit_label"></span></td>
                                <td class="px-4 py-4 text-center"><span class="font-bold text-gray-800" x-text="fmtQty(item.quantity)"></span></td>
                                <td class="px-4 py-4 text-center"><span class="text-gray-400 mr-0.5 text-xs"><?php echo e(currencySymbol()); ?></span><span class="font-black text-gray-800" x-text="item.line_total.toFixed(2)"></span></td>
                                <td class="px-4 py-4 text-center">
                                    <div class="inline-flex flex-col items-center gap-1">
                                        <!-- Auto-receive button for non-batch items -->
                                        <template x-if="!item.requires_batch_tracking">
                                            <button type="button" @click="autoReceive(index)"
                                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border transition-all hover:shadow-sm"
                                                :class="
                                                    (item.received_quantity + stagedQty(item)) >= item.quantity
                                                        ? 'bg-green-50 border-green-300 text-green-700 hover:bg-green-100 cursor-default'
                                                        : (stagedBatches[item.id]
                                                            ? 'bg-[#5542ff]/10 border-[#5542ff]/40 text-[#5542ff] hover:bg-[#5542ff]/20 cursor-pointer'
                                                            : (item.received_quantity > 0
                                                                ? 'bg-amber-50 border-amber-300 text-amber-700 hover:bg-amber-100 cursor-pointer'
                                                                : 'bg-gray-50 border-gray-300 text-red-500 hover:bg-gray-100 cursor-pointer'))
                                                    "
                                                :disabled="(item.received_quantity + stagedQty(item)) >= item.quantity">
                                                <svg class="h-3.5 w-3.5 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" /></svg>
                                                <span class="text-sm font-black" x-text="fmtQty(item.received_quantity + stagedQty(item)) + '/' + fmtQty(item.quantity)"></span>
                                            </button>
                                        </template>
                                        <!-- Regular batch button for batch-tracked items -->
                                        <template x-if="item.requires_batch_tracking">
                                            <button type="button" @click="openBatchModal(index)"
                                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border transition-all hover:shadow-sm"
                                                :class="
                                                    (item.received_quantity + stagedQty(item)) >= item.quantity
                                                        ? 'bg-green-50 border-green-300 text-green-700 hover:bg-green-100 cursor-default'
                                                        : (stagedBatches[item.id]
                                                            ? 'bg-[#5542ff]/10 border-[#5542ff]/40 text-[#5542ff] hover:bg-[#5542ff]/20 cursor-pointer'
                                                            : (item.received_quantity > 0
                                                                ? 'bg-amber-50 border-amber-300 text-amber-700 hover:bg-amber-100 cursor-pointer'
                                                                : 'bg-gray-50 border-gray-300 text-red-500 hover:bg-gray-100 cursor-pointer'))
                                                    ">
                                                <svg class="h-3.5 w-3.5 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v2H7a1 1 0 100 2h2v2a1 1 0 102 0v-2h2a1 1 0 100-2h-2V7z" clip-rule="evenodd" /></svg>
                                                <span class="text-sm font-black" x-text="fmtQty(item.received_quantity + stagedQty(item)) + '/' + fmtQty(item.quantity)"></span>
                                            </button>
                                        </template>
                                        <!-- Staged indicator -->
                                        <template x-if="stagedBatches[item.id]">
                                            <div class="inline-flex items-center gap-1">
                                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-amber-100 border border-amber-300 text-amber-700 text-[10px] font-bold">
                                                    <svg class="h-2.5 w-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                                    Pending
                                                </span>
                                                <button type="button" @click.stop="clearStaged(item.id)" class="text-gray-400 hover:text-red-500 transition-colors" title="Clear staged batches">
                                                    <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" /></svg>
                                                </button>
                                            </div>
                                        </template>
                                    </div>
                                </td>
                            </tr>
                        </template>
                        <tr x-show="items.length === 0"><td colspan="9" class="px-4 py-12 text-center text-gray-400 italic bg-gray-50/30">No items in this purchase.</td></tr>
                    </tbody>
                </table>
            </div>
        </div>

        
        <?php echo $__env->make('purchases.partials.batch-modal', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

        
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
                <h3 class="text-sm font-bold text-gray-500 uppercase mb-3">Purchase Note</h3>
                <?php if($purchase->note): ?><p class="text-sm text-gray-700 whitespace-pre-line"><?php echo e($purchase->note); ?></p>
                <?php else: ?> <p class="text-sm text-gray-400 italic">No notes for this purchase.</p><?php endif; ?>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 space-y-4">
                <div class="flex justify-between items-center pb-3 border-b border-gray-100">
                    <span class="text-sm font-bold text-gray-500 uppercase">Subtotal</span>
                    <span class="text-lg font-black text-gray-800"><?php echo e(formatCurrency($purchase->subtotal)); ?></span>
                </div>
                <?php if((float) $purchase->discount > 0): ?>
                <div class="flex justify-between items-center">
                    <span class="text-sm font-bold text-gray-500 uppercase">
                        Discount (
                        <?php if($purchase->discount_type === 'percentage'): ?>
                            <?php echo e(number_format((float) $purchase->discount, 2)); ?>%
                        <?php else: ?>
                            <?php echo e(currencySymbol()); ?> <?php echo e(number_format((float) $purchase->discount, 2)); ?>

                        <?php endif; ?>
                        )
                    </span>
                    <span class="text-sm font-bold text-red-500">
                        - <?php echo e(currencySymbol()); ?>

                        <?php if($purchase->discount_type === 'percentage'): ?>
                            <?php echo e(number_format((float) $purchase->subtotal * (float) $purchase->discount / 100, 2)); ?>

                        <?php else: ?>
                            <?php echo e(number_format((float) $purchase->discount, 2)); ?>

                        <?php endif; ?>
                    </span>
                </div>
                <?php endif; ?>
                <?php if((float) $purchase->tax_rate > 0): ?>
                <div class="flex justify-between items-center">
                    <span class="text-sm font-bold text-gray-500 uppercase">Tax (<?php echo e(number_format((float) $purchase->tax_rate, 2)); ?>%)</span>
                    <span class="text-sm font-bold text-gray-600"><?php echo e(currencySymbol()); ?> <?php echo e(number_format((float) $purchase->tax_amount, 2)); ?></span>
                </div>
                <?php endif; ?>
                <div class="flex justify-between items-center pt-3 border-t border-gray-100">
                    <span class="text-base font-black text-[#0a1233] uppercase">Grand Total</span>
                    <span class="text-2xl font-black text-[#5542ff]"><?php echo e(formatCurrency($purchase->payable_amount)); ?></span>
                </div>
                <?php
                    $totalReturnReceivable = (float) $purchase->purchaseReturns->sum('receivable_amount');
                    $netPayable = max(0, (float) $purchase->payable_amount - $totalReturnReceivable);
                ?>
                <?php if($totalReturnReceivable > 0): ?>
                <div class="flex justify-between items-center">
                    <span class="text-sm font-bold text-gray-500 uppercase">Return Credit</span>
                    <span class="text-sm font-bold text-orange-600">- <?php echo e(formatCurrency($totalReturnReceivable)); ?></span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-sm font-bold text-gray-700 uppercase">Net Payable</span>
                    <span class="text-lg font-black text-gray-800"><?php echo e(formatCurrency($netPayable)); ?></span>
                </div>
                <?php endif; ?>
                <div class="flex justify-between items-center pt-3 border-t border-gray-100">
                    <span class="text-sm text-gray-500">Paid</span>
                    <span class="text-sm font-bold text-green-600"><?php echo e(formatCurrency($purchase->paid_amount)); ?></span>
                </div>
                <?php if($purchase->due_amount > 0.009): ?>
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-500">Due</span>
                    <span class="text-sm font-bold text-red-600"><?php echo e(formatCurrency($purchase->due_amount)); ?></span>
                </div>
                <?php endif; ?>
            </div>
        </div>

        
        <?php if($purchase->payments->count() > 0): ?>
            <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
                <div class="p-4 border-b border-gray-200">
                    <h3 class="text-sm font-bold text-gray-800">Payment History</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-[10px] font-black uppercase tracking-widest">Date</th>
                                <th class="px-4 py-3 text-left text-[10px] font-black uppercase tracking-widest">Payment Method</th>
                                <th class="px-4 py-3 text-left text-[10px] font-black uppercase tracking-widest">Check Number</th>
                                <th class="px-4 py-3 text-left text-[10px] font-black uppercase tracking-widest">Check Date</th>
                                <th class="px-4 py-3 text-right text-[10px] font-black uppercase tracking-widest">Amount</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <?php $__currentLoopData = $purchase->payments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $payment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr class="hover:bg-gray-50/50 transition-colors">
                                    <td class="px-4 py-3 text-gray-600"><?php echo e($payment->created_at->format('d M, Y')); ?></td>
                                    <td class="px-4 py-3">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold <?php echo e($payment->payment_method === 'cash' ? 'bg-green-100 text-green-700' : 'bg-blue-100 text-blue-700'); ?>">
                                            <?php echo e(ucfirst($payment->payment_method)); ?>

                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-gray-600"><?php echo e($payment->check_number ?? '—'); ?></td>
                                    <td class="px-4 py-3 text-gray-600"><?php echo e($payment->check_date ? $payment->check_date->format('d M, Y') : '—'); ?></td>
                                    <td class="px-4 py-3 text-right font-bold text-gray-800"><?php echo e(currencySymbol()); ?> <?php echo e(number_format($payment->amount, 2)); ?></td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>
    </div>

 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54)): ?>
<?php $attributes = $__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54; ?>
<?php unset($__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal9ac128a9029c0e4701924bd2d73d7f54)): ?>
<?php $component = $__componentOriginal9ac128a9029c0e4701924bd2d73d7f54; ?>
<?php unset($__componentOriginal9ac128a9029c0e4701924bd2d73d7f54); ?>
<?php endif; ?>
<?php /**PATH C:\laragon\www\GE-System-1.1\resources\views/purchases/receipt.blade.php ENDPATH**/ ?>