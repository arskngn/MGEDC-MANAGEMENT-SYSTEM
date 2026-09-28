
<template x-if="!item.requires_batch_tracking || item.use_fefo">
    <div class="space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <p class="text-xs font-bold uppercase tracking-wider text-ink-muted">Return Breakdown</p>
                <p class="text-[11px] text-ink-muted mt-0.5">Split quantities between restockable and damaged units</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <button type="button" @click="fillAllGood(item)" class="px-3 py-1.5 rounded-lg text-[11px] font-bold uppercase tracking-wide bg-emerald-50 text-emerald-700 border border-emerald-200 hover:bg-emerald-100 dark:bg-emerald-900/20 dark:text-emerald-300 dark:border-emerald-800/50 transition-colors">
                    All Good
                </button>
                <button type="button" @click="fillAllDamaged(item)" class="px-3 py-1.5 rounded-lg text-[11px] font-bold uppercase tracking-wide bg-red-50 text-red-700 border border-red-200 hover:bg-red-100 dark:bg-red-900/20 dark:text-red-300 dark:border-red-800/50 transition-colors">
                    All Damaged
                </button>
                <button type="button" @click="clearReturn(item)" class="px-3 py-1.5 rounded-lg text-[11px] font-bold uppercase tracking-wide bg-white text-gray-600 border border-gray-200 hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-600 transition-colors">
                    Clear
                </button>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            
            <div class="relative rounded-xl border-2 border-emerald-200 dark:border-emerald-800/50 bg-gradient-to-br from-emerald-50 to-white dark:from-emerald-950/30 dark:to-gray-900 p-4 overflow-hidden">
                <div class="absolute top-0 right-0 w-20 h-20 bg-emerald-400/10 rounded-full -translate-y-1/2 translate-x-1/2"></div>
                <div class="flex items-center gap-2 mb-3">
                    <span class="flex items-center justify-center w-7 h-7 rounded-lg bg-emerald-500 text-white">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" /></svg>
                    </span>
                    <div>
                        <p class="text-xs font-black uppercase tracking-wider text-emerald-800 dark:text-emerald-300">Good Condition</p>
                        <p class="text-[10px] text-emerald-600/80 dark:text-emerald-400/70">Returns to inventory</p>
                    </div>
                </div>
                <div class="relative">
                    <input
                        type="number"
                        step="0.0001"
                        min="0"
                        :name="'items['+itemIndex+'][good_quantity]'"
                        x-model.number="item.good_quantity"
                        class="w-full rounded-xl border-2 border-emerald-200 dark:border-emerald-700/60 bg-white dark:bg-gray-900 pl-4 pr-16 py-3 text-lg font-black text-emerald-800 dark:text-emerald-200 focus:ring-2 focus:ring-emerald-400/30 focus:border-emerald-400"
                        :class="isOverLimit(item) ? '!border-red-400' : ''"
                        placeholder="0"
                    />
                    <span class="absolute right-3 top-1/2 -translate-y-1/2 text-[10px] font-bold uppercase text-emerald-500/70" x-text="item.unit_label"></span>
                </div>
            </div>

            
            <div class="relative rounded-xl border-2 border-red-200 dark:border-red-800/50 bg-gradient-to-br from-red-50 to-white dark:from-red-950/30 dark:to-gray-900 p-4 overflow-hidden">
                <div class="absolute top-0 right-0 w-20 h-20 bg-red-400/10 rounded-full -translate-y-1/2 translate-x-1/2"></div>
                <div class="flex items-center gap-2 mb-3">
                    <span class="flex items-center justify-center w-7 h-7 rounded-lg bg-red-500 text-white">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" /></svg>
                    </span>
                    <div>
                        <p class="text-xs font-black uppercase tracking-wider text-red-800 dark:text-red-300">Damaged</p>
                        <p class="text-[10px] text-red-600/80 dark:text-red-400/70">Refunded, not restocked</p>
                    </div>
                </div>
                <div class="relative">
                    <input
                        type="number"
                        step="0.0001"
                        min="0"
                        :name="'items['+itemIndex+'][damaged_quantity]'"
                        x-model.number="item.damaged_quantity"
                        class="w-full rounded-xl border-2 border-red-200 dark:border-red-700/60 bg-white dark:bg-gray-900 pl-4 pr-16 py-3 text-lg font-black text-red-800 dark:text-red-200 focus:ring-2 focus:ring-red-400/30 focus:border-red-400"
                        :class="isOverLimit(item) ? '!border-red-400' : ''"
                        placeholder="0"
                    />
                    <span class="absolute right-3 top-1/2 -translate-y-1/2 text-[10px] font-bold uppercase text-red-500/70" x-text="item.unit_label"></span>
                </div>
            </div>
        </div>

    </div>
</template>


<template x-if="item.requires_batch_tracking && !item.use_fefo">
    <div class="space-y-3">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 mb-1">
            <p class="text-xs font-bold uppercase tracking-wider text-ink-muted">Batch Returns</p>
            <p class="text-[11px] text-gray-400">Enter good and damaged qty per batch</p>
        </div>

        <div class="hidden md:grid grid-cols-12 gap-3 px-3 text-[10px] font-bold uppercase tracking-wider text-gray-400">
            <div class="col-span-4">Batch</div>
            <div class="col-span-2 text-center">Returnable</div>
            <div class="col-span-3 text-center text-emerald-600">Good</div>
            <div class="col-span-3 text-center text-red-600">Damaged</div>
        </div>

        <template x-for="(batch, batchIndex) in item.batches" :key="batchIndex">
            <div
                class="rounded-xl border bg-white dark:bg-gray-900 p-3 md:p-0 md:grid md:grid-cols-12 md:gap-3 md:items-center md:px-3 md:py-3 transition-colors"
                :class="isBatchOverLimit(batch) ? 'border-red-300 dark:border-red-700 bg-red-50/50 dark:bg-red-950/20' : 'border-gray-200 dark:border-gray-700'"
            >
                <input type="hidden" :name="'items['+itemIndex+'][batches]['+batchIndex+'][batch_id]'" :value="batch.batch_id" />

                <div class="md:col-span-4 mb-3 md:mb-0">
                    <p class="text-sm font-semibold text-ink-body" x-text="batch.batch_number"></p>
                    <p class="text-[11px] text-gray-400 mt-0.5">
                        Sold <span x-text="batch.sale_quantity"></span> <span x-text="item.unit_label"></span>
                        <template x-if="batch.expiry_date">
                            · Exp <span x-text="batch.expiry_date"></span>
                        </template>
                    </p>
                </div>

                <div class="md:col-span-2 mb-3 md:mb-0 md:text-center">
                    <span class="md:hidden text-[10px] font-bold uppercase text-gray-400 mr-2">Returnable</span>
                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-bold bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300">
                        <span x-text="batch.returnable_quantity"></span>&nbsp;<span x-text="item.unit_label"></span>
                    </span>
                </div>

                <div class="grid grid-cols-2 gap-3 md:contents">
                    <div class="md:col-span-3">
                        <label class="md:sr-only">Good quantity</label>
                        <div class="relative">
                            <input
                                type="number"
                                step="0.0001"
                                min="0"
                                :name="'items['+itemIndex+'][batches]['+batchIndex+'][good_quantity]'"
                                x-model.number="batch.good_quantity"
                                class="w-full rounded-lg border-2 border-emerald-200 dark:border-emerald-800/50 bg-emerald-50/50 dark:bg-emerald-950/20 px-3 py-2.5 text-sm font-bold text-emerald-800 dark:text-emerald-200 focus:ring-2 focus:ring-emerald-400/30"
                                placeholder="0 good"
                            />
                        </div>
                    </div>
                    <div class="md:col-span-3">
                        <label class="md:sr-only">Damaged quantity</label>
                        <div class="relative">
                            <input
                                type="number"
                                step="0.0001"
                                min="0"
                                :name="'items['+itemIndex+'][batches]['+batchIndex+'][damaged_quantity]'"
                                x-model.number="batch.damaged_quantity"
                                class="w-full rounded-lg border-2 border-red-200 dark:border-red-800/50 bg-red-50/50 dark:bg-red-950/20 px-3 py-2.5 text-sm font-bold text-red-800 dark:text-red-200 focus:ring-2 focus:ring-red-400/30"
                                placeholder="0 damaged"
                            />
                        </div>
                    </div>
                </div>

                <p x-show="isBatchOverLimit(batch)" class="md:col-span-12 text-[11px] font-semibold text-red-600 mt-2 md:mt-0 md:pt-1">
                    Exceeds returnable for this batch
                </p>
            </div>
        </template>
    </div>
</template>
<?php /**PATH C:\laragon\www\GE-System-1.1\resources\views/sales/partials/return-item-inputs.blade.php ENDPATH**/ ?>