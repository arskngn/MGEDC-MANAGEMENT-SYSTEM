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
    <div
        class="max-w-6xl mx-auto space-y-6"
        x-data="{
            productQuery: '',
            searchResults: [],
            searchLoading: false,
            searchTimer: null,
            items: [],
            async searchProducts() {
                clearTimeout(this.searchTimer);
                const q = this.productQuery.trim();
                this.searchTimer = setTimeout(async () => {
                    this.searchLoading = true;
                    try {
                        const r = await fetch(`<?php echo e(route('adjustments.products.search')); ?>?q=${encodeURIComponent(q)}`, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } });
                        this.searchResults = await r.json();
                    } catch (e) { this.searchResults = []; }
                    this.searchLoading = false;
                }, 150);
            },
            addProduct(p) {
                if (this.items.find(i => i.product_id === p.id)) return;
                this.items.push({
                    product_id: p.id,
                    name: p.name,
                    sku: p.sku,
                    unit_label: p.unit_label,
                    in_stock: p.in_stock,
                    quantity: 1,
                    type: 'Added',
                    requires_batch_tracking: p.requires_batch_tracking,
                    product_batch_id: '',
                    batches: p.batches || []
                });
                this.productQuery = '';
                this.searchResults = [];
            },
            removeRow(i) { this.items.splice(i, 1); },
        }"
    >
        <div class="flex justify-between items-center">
            <h2 class="text-xl font-bold text-[#0a1233]">New Adjustment</h2>
            <a href="<?php echo e(route('adjustments.index')); ?>" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50">
                <svg class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                Back
            </a>
        </div>

        <form action="<?php echo e(route('adjustments.store')); ?>" method="POST" class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-6">
            <?php echo csrf_field(); ?>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tracking No. <span class="text-red-500">*</span></label>
                    <input type="text" name="tracking_no" value="<?php echo e(old('tracking_no', $trackingNo)); ?>" required class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm bg-gray-100" readonly />
                    <?php $__errorArgs = ['tracking_no'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="text-red-500 text-xs mt-1"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Date</label>
                    <input type="date" name="adjustment_date" value="<?php echo e(old('adjustment_date', now()->format('Y-m-d'))); ?>" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:ring-1 focus:ring-[#4634ff]" />
                    <?php $__errorArgs = ['adjustment_date'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="text-red-500 text-xs mt-1"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Reason</label>
                    <select name="reason" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:ring-1 focus:ring-[#4634ff]">
                        <option value="">-- Select Reason --</option>
                        <option value="Stock Audit Discrepancy" <?php echo e(old('reason') == 'Stock Audit Discrepancy' ? 'selected' : ''); ?>>Stock Audit Discrepancy</option>
                        <option value="Damaged / Spoilage" <?php echo e(old('reason') == 'Damaged / Spoilage' ? 'selected' : ''); ?>>Damaged / Spoilage</option>
                        <option value="Expired Stock" <?php echo e(old('reason') == 'Expired Stock' ? 'selected' : ''); ?>>Expired Stock</option>
                        <option value="Internal Use / Sample" <?php echo e(old('reason') == 'Internal Use / Sample' ? 'selected' : ''); ?>>Internal Use / Sample</option>
                        <option value="Found Inventory" <?php echo e(old('reason') == 'Found Inventory' ? 'selected' : ''); ?>>Found Inventory</option>
                        <option value="Supplier Bonus" <?php echo e(old('reason') == 'Supplier Bonus' ? 'selected' : ''); ?>>Supplier Bonus</option>
                    </select>
                    <?php $__errorArgs = ['reason'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="text-red-500 text-xs mt-1"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>
            </div>

            <div class="relative" @click.outside="searchResults = []">
                <label class="block text-sm font-medium text-gray-700 mb-1">Product <span class="text-red-500">*</span></label>
                <div class="flex rounded-lg border border-gray-200 overflow-hidden bg-white focus-within:border-[#4634ff] focus-within:ring-1 focus-within:ring-[#4634ff] transition-all">
                    <span class="px-3 py-2 bg-gray-50 text-gray-400 border-r border-gray-100 flex items-center">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                    </span>
                    <input type="text" x-model="productQuery" @input="searchProducts()" @focus="searchProducts()" @click="searchProducts()" @keydown.enter.prevent="if (searchResults.length) { addProduct(searchResults[0]); }" autocomplete="off" placeholder="Search by Product Name or SKU..." class="flex-1 px-3 py-2 text-sm focus:outline-none bg-transparent" />
                    <template x-if="productQuery.length > 0">
                        <button type="button" @click="productQuery = ''; searchResults = []" class="px-3 text-gray-400 hover:text-gray-600">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </template>
                </div>
                <div x-show="searchResults.length" x-cloak class="absolute z-20 mt-1 w-full bg-white border border-gray-200 rounded-lg shadow-xl max-h-56 overflow-y-auto">
                    <template x-for="p in searchResults" :key="p.id">
                        <button type="button" @click="addProduct(p)" class="w-full text-left px-4 py-2.5 text-sm hover:bg-[#4634ff]/5 border-b border-gray-50 last:border-0 transition-colors flex items-center justify-between">
                            <div>
                                <span class="font-bold text-gray-900" x-text="p.name"></span>
                                <span class="text-gray-400 text-xs ml-2" x-text="'(SKU: ' + p.sku + ')'"></span>
                            </div>
                            <span class="text-xs font-semibold px-2 py-0.5 rounded-full bg-blue-50 text-blue-700" x-text="'Stock: ' + p.in_stock + ' ' + p.unit_label"></span>
                        </button>
                    </template>
                </div>
            </div>

            <div class="overflow-x-auto rounded-xl border border-gray-200">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-[#0a1233] text-white">
                            <th class="px-4 py-3 text-left text-[11px] font-bold uppercase">Name</th>
                            <th class="px-4 py-3 text-left text-[11px] font-bold uppercase">Batch</th>
                            <th class="px-4 py-3 text-left text-[11px] font-bold uppercase">Current Stock</th>
                            <th class="px-4 py-3 text-left text-[11px] font-bold uppercase">Stock - After Adjust</th>
                            <th class="px-4 py-3 text-right text-[11px] font-bold uppercase">Adjust Qty <span class="text-red-300">*</span></th>
                            <th class="px-4 py-3 text-left text-[11px] font-bold uppercase">Type <span class="text-red-300">*</span></th>
                            <th class="px-4 py-3 text-center text-[11px] font-bold uppercase">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <template x-for="(item, idx) in items" :key="idx">
                            <tr class="bg-white hover:bg-gray-50 transition-colors">
                                <td class="px-4 py-3">
                                    <input type="hidden" :name="`items[${idx}][product_id]`" :value="item.product_id" />
                                    <div class="text-sm font-semibold text-gray-900" x-text="item.name"></div>
                                    <div class="text-xs text-gray-500" x-text="item.sku"></div>
                                </td>
                                <td class="px-4 py-3">
                                    <template x-if="item.requires_batch_tracking">
                                        <select :name="`items[${idx}][product_batch_id]`" x-model="item.product_batch_id" class="w-full px-3 py-2 border border-gray-200 rounded text-sm focus:ring-1 focus:ring-[#4634ff]">
                                            <option value="">-- Select Batch --</option>
                                            <template x-for="b in item.batches" :key="b.id">
                                                <option :value="b.id" x-text="`${b.batch_number} (${b.available} avail)`"></option>
                                            </template>
                                        </select>
                                    </template>
                                    <template x-if="!item.requires_batch_tracking">
                                        <div class="flex items-center gap-1.5">
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-medium bg-gray-100 text-gray-600 border border-gray-200">
                                                N/A (Standard Item)
                                            </span>
                                            <input type="hidden" :name="`items[${idx}][product_batch_id]`" value="" />
                                        </div>
                                    </template>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="text-sm text-gray-600" x-text="`${item.in_stock} ${item.unit_label}`"></div>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="text-sm font-semibold" :class="item.type === 'Added' ? 'text-blue-600' : 'text-red-600'">
                                        <span x-text="item.type === 'Added' 
                                            ? (parseInt(item.in_stock) + parseInt(item.quantity)) 
                                            : Math.max(0, parseInt(item.in_stock) - parseInt(item.quantity))"></span>
                                        <span x-text="` ${item.unit_label}`"></span>
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    <input 
                                        type="number" 
                                        :name="`items[${idx}][quantity]`" 
                                        x-model.number="item.quantity"
                                        placeholder="0" 
                                        class="w-full px-3 py-2 border border-gray-200 rounded text-sm focus:ring-1 focus:ring-[#4634ff]"
                                    />
                                </td>
                                <td class="px-4 py-3">
                                    <select :name="`items[${idx}][type]`" x-model="item.type" class="w-full px-3 py-2 border border-gray-200 rounded text-sm focus:ring-1 focus:ring-[#4634ff]">
                                        <option value="Added">Added</option>
                                        <option value="Removed">Removed</option>
                                    </select>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <button type="button" @click="removeRow(idx)" class="text-red-600 hover:text-red-800 font-semibold text-sm">
                                        Remove
                                    </button>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Note</label>
                <textarea name="note" rows="3" placeholder="Add any notes about this adjustment..." class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-1 focus:ring-[#4634ff]"><?php echo e(old('note')); ?></textarea>
                <?php $__errorArgs = ['note'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="text-red-500 text-xs mt-1"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>

            <div class="flex gap-3">
                <button type="submit" class="flex-1 bg-[#5542ff] text-white py-2.5 rounded-lg font-semibold hover:bg-[#4634ff] transition-colors">
                    Submit
                </button>
            </div>
        </form>
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
<?php /**PATH C:\laragon\www\GE-System-1.1\resources\views/adjustments/create.blade.php ENDPATH**/ ?>