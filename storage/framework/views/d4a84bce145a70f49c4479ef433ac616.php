
<template x-teleport="body">
    <div x-show="batchModalOpen" x-cloak class="fixed inset-0 z-[500] flex items-center justify-center"
        @keydown.escape.window="closeBatchModal()">
        <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" @click="closeBatchModal()"
            x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"></div>

        <div class="relative bg-white dark:bg-gray-800 rounded-2xl shadow-2xl w-full max-w-xl mx-4 overflow-hidden flex flex-col"
            style="max-height: 92vh;"
            x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
            @click.stop>

            
            <div class="bg-[#5542ff] px-6 py-4 flex items-center justify-between flex-none">
                <div>
                    <h3 class="text-white font-bold text-sm">Receive items for</h3>
                    <p class="text-white/70 text-xs mt-0.5" x-text="'<?php echo e($purchase->invoice_no); ?>' + ' · ' + (selectedItem?.product_name || '')"></p>
                </div>
                <button type="button" @click="closeBatchModal()" class="text-white/60 hover:text-white transition-colors p-1">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>

            <div x-show="selectedItem" class="flex flex-col flex-1 overflow-hidden">

                
                <div class="px-6 pt-4 pb-3 flex-none border-b border-gray-100 dark:border-gray-700">
                    <div class="flex items-center justify-between mb-1.5">
                        <span class="text-xs font-bold text-ink-muted uppercase tracking-wider">Total Assigned</span>
                        <span class="text-sm font-black tabular-nums"
                            :class="batchTotalQty > remainingForItem ? 'text-red-500' : (batchTotalQty === remainingForItem && batchTotalQty > 0 ? 'text-green-600' : 'text-[#5542ff]')"
                            x-text="fmtQty(batchTotalQty) + ' / ' + fmtQty(remainingForItem)"></span>
                    </div>

                    <div x-show="batchTotalQty > remainingForItem" class="mt-1.5 text-xs font-semibold text-red-500"
                        x-text="'Exceeds remaining by ' + fmtQty(batchTotalQty - remainingForItem) + '. Reduce quantities.'">
                    </div>
                    <div x-show="batchTotalQty < remainingForItem && batchTotalQty > 0" class="mt-1.5 text-xs font-semibold text-amber-500"
                        x-text="fmtQty(remainingForItem - batchTotalQty) + ' more needed to fully receive.'">
                    </div>
                    <div x-show="batchTotalQty === remainingForItem && batchTotalQty > 0" class="mt-1.5 text-xs font-semibold text-green-600">
                        ✓ Quantities match — ready to receive.
                    </div>
                </div>

                
                <div x-show="selectedBatches.length > 0" class="px-6 pt-4 flex-none">
                    <div class="flex items-center gap-2 mb-2">
                        <span class="text-xs font-black text-gray-400 uppercase tracking-widest">Previously Received</span>
                    </div>
                    <div class="border border-gray-200 dark:border-gray-600 rounded-lg overflow-hidden">
                        <table class="w-full text-xs">
                            <thead class="bg-gray-50 dark:bg-gray-700 border-b border-gray-200 dark:border-gray-600">
                                <tr>
                                    <th class="px-3 py-2 text-left font-black text-gray-400 uppercase">Batch</th>
                                    <th x-show="selectedItem?.has_expiry" class="px-3 py-2 text-center font-black text-gray-400 uppercase">Expiry</th>
                                    <th class="px-3 py-2 text-right font-black text-gray-400 uppercase">Qty</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-600">
                                <template x-for="batch in selectedBatches" :key="batch.batch_number">
                                    <tr class="bg-white dark:bg-gray-800">
                                        <td class="px-3 py-2">
                                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 border border-indigo-200 dark:border-indigo-700" x-text="batch.batch_number"></span>
                                        </td>
                                        <td x-show="selectedItem?.has_expiry" class="px-3 py-2 text-center text-ink-muted" x-text="batch.expiry_date || '—'"></td>
                                        <td class="px-3 py-2 text-right font-bold text-gray-700 dark:text-gray-200" x-text="parseFloat(batch.quantity).toFixed(4).replace(/0+$/,'').replace(/\.$/,'')"></td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>

                
                <template x-if="remainingForItem <= 0">
                    <div class="px-6 py-6 flex-1 flex items-center justify-center">
                        <div class="flex flex-col items-center gap-3 p-6 bg-green-50 dark:bg-green-900/20 rounded-xl border border-green-200 dark:border-green-700 w-full text-center">
                            <svg class="h-10 w-10 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            <span class="text-sm font-bold text-green-700 dark:text-green-400">All quantities fully received for this item.</span>
                        </div>
                    </div>
                </template>

                
                <template x-if="remainingForItem > 0">
                    <div class="flex flex-col flex-1 overflow-hidden">

                        
                        <div class="flex-1 overflow-y-auto px-6 pt-4 pb-2 space-y-3" id="batch-rows-container">

                            
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-black text-ink-muted uppercase tracking-widest">New Batch Entries</span>
                                <button type="button" @click="addBatchRow()"
                                    class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-[#5542ff]/10 text-[#5542ff] text-xs font-bold hover:bg-[#5542ff]/20 transition-colors border border-[#5542ff]/20">
                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" /></svg>
                                    Add Batch
                                </button>
                            </div>

                            
                            <template x-for="(row, rowIdx) in pendingBatches" :key="rowIdx">
                                <div class="rounded-xl border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700/50 p-3 space-y-2 relative group">

                                    
                                    <div class="flex items-center justify-between mb-1">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-black bg-[#5542ff] text-white" x-text="'Batch #' + (rowIdx + 1)"></span>
                                        <button type="button" @click="removeBatchRow(rowIdx)"
                                            x-show="pendingBatches.length > 1"
                                            class="text-red-400 hover:text-red-600 transition-colors p-0.5 rounded">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                        </button>
                                    </div>

                                    <div class="grid gap-2" :class="selectedItem?.has_expiry ? 'grid-cols-3' : 'grid-cols-2'">
                                        
                                        <div>
                                            <label class="block text-[10px] font-bold text-ink-muted uppercase mb-1">Batch No.</label>
                                            <input type="text"
                                                x-model="row.batch_number"
                                                placeholder="Auto"
                                                class="w-full rounded-lg border border-gray-200 dark:border-gray-600 dark:bg-gray-800 dark:text-white px-2.5 py-1.5 text-sm focus:ring-1 focus:ring-[#5542ff] focus:border-[#5542ff] outline-none transition-colors" />
                                        </div>

                                        
                                        <div x-show="selectedItem?.has_expiry">
                                            <label class="block text-[10px] font-bold text-ink-muted uppercase mb-1">
                                                Expiry Date (required)
                                            </label>
                                            <input type="date"
                                                x-model="row.expiry_date"
                                                :min="todayStr"
                                                class="w-full rounded-lg border border-gray-200 dark:border-gray-600 dark:bg-gray-800 dark:text-white px-2.5 py-1.5 text-sm focus:ring-1 focus:ring-[#5542ff] focus:border-[#5542ff] outline-none transition-colors"
                                                :class="rowInvalidExpiry(row) ? 'border-red-400 focus:ring-red-400 focus:border-red-400' : ''" />
                                            <p x-show="rowMissingExpiry(row)" class="text-[10px] text-red-500 mt-0.5 font-semibold">
                                                Expiry date is required for this product.
                                            </p>
                                            <p x-show="rowPastExpiry(row)" class="text-[10px] text-red-500 mt-0.5 font-semibold">
                                                Expiry date cannot be in the past.
                                            </p>
                                        </div>

                                        
                                        <div>
                                            <label class="block text-[10px] font-bold text-ink-muted uppercase mb-1">
                                                Qty <span class="font-normal text-gray-400" x-text="'(rem: ' + fmtQty(remainingForItem) + ')'"></span>
                                            </label>
                                            <input type="number"
                                                x-model.number="row.quantity"
                                                step="0.0001" min="0.0001"
                                                :max="remainingForItem"
                                                @input="validateBatchQty(rowIdx)"
                                                class="w-full rounded-lg border border-gray-200 dark:border-gray-600 dark:bg-gray-800 dark:text-white px-2.5 py-1.5 text-sm font-bold focus:ring-1 focus:ring-[#5542ff] focus:border-[#5542ff] outline-none transition-colors"
                                                :class="row.qtyError ? 'border-red-400 focus:ring-red-400' : ''" />
                                            <p x-show="row.qtyError" class="text-[10px] text-red-500 mt-0.5 font-semibold" x-text="row.qtyError"></p>
                                        </div>
                                    </div>
                                </div>
                            </template>

                        </div>

                        
                        <div class="flex-none px-6 py-4 border-t border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50">
                            
                            <div x-show="pendingBatches.length > 0 && batchTotalQty !== remainingForItem"
                                class="mb-3 flex items-start gap-2 p-3 rounded-lg bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700">
                                <svg class="h-4 w-4 text-amber-500 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                                <span class="text-xs font-semibold text-amber-700 dark:text-amber-400"
                                    x-text="batchTotalQty > remainingForItem
                                        ? 'Total batch qty (' + fmtQty(batchTotalQty) + ') exceeds remaining (' + fmtQty(remainingForItem) + '). Please fix.'
                                        : 'Batch total (' + fmtQty(batchTotalQty) + ') must equal remaining quantity (' + fmtQty(remainingForItem) + ') to save.'">
                                </span>
                            </div>
                            <div x-show="hasMissingRequiredExpiry"
                                class="mb-3 flex items-start gap-2 p-3 rounded-lg bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-700">
                                <svg class="h-4 w-4 text-red-500 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01m-7.938 4h15.876c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                                <span class="text-xs font-semibold text-red-700 dark:text-red-400">
                                    Expiry date is required for every batch of products with expiration tracking.
                                </span>
                            </div>

                            <div class="flex justify-end gap-3">
                                <button type="button" @click="closeBatchModal()"
                                    class="px-5 py-2.5 rounded-lg border border-gray-200 dark:border-gray-600 text-sm font-semibold text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                                    Cancel
                                </button>
                                <button type="button"
                                    @click="saveBatchStaged()"
                                    :disabled="!canSubmitBatches"
                                    :class="canSubmitBatches
                                        ? 'bg-[#5542ff] hover:bg-[#4736d6] text-white shadow-sm cursor-pointer'
                                        : 'bg-gray-200 dark:bg-gray-600 text-ink-muted cursor-not-allowed'"
                                    class="px-5 py-2.5 rounded-lg text-sm font-bold transition-colors inline-flex items-center gap-2">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" /></svg>
                                    Save
                                </button>
                            </div>
                        </div>
                    </div>
                </template>

            </div>
        </div>
    </div>
</template>
<?php /**PATH C:\laragon\www\GE-System-1.1\resources\views/purchases/partials/batch-modal.blade.php ENDPATH**/ ?>