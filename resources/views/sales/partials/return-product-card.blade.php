<template x-for="(item, itemIndex) in items" :key="itemIndex">
    <article
        class="rounded-2xl border overflow-hidden transition-all duration-200"
        :class="{
            'border-gray-200 dark:border-gray-700 shadow-sm': itemTotalReturning(item) === 0,
            'border-[#4634ff]/30 ring-2 ring-[#4634ff]/15 shadow-md shadow-[#4634ff]/5': itemTotalReturning(item) > 0 && !isOverLimit(item),
            'border-red-300 dark:border-red-600 ring-2 ring-red-200/60 dark:ring-red-500/20': isOverLimit(item)
        }"
    >
        {{-- Product header --}}
        <div class="px-5 py-4 bg-white dark:bg-gray-800 border-b border-gray-100 dark:border-gray-700">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                <div class="flex items-start gap-3 min-w-0">
                    <div class="flex-shrink-0 w-11 h-11 rounded-xl bg-gradient-to-br from-[#4634ff]/15 to-[#4634ff]/5 flex items-center justify-center">
                        <svg class="h-5 w-5 text-[#4634ff]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <h3 class="font-bold text-[#0a1233] dark:text-gray-100 text-base leading-tight truncate" x-text="item.product_name"></h3>
                        <p class="text-xs text-ink-muted mt-0.5 font-medium" x-text="'SKU · ' + item.sku"></p>
                        <div class="flex flex-wrap gap-2 mt-2" x-show="itemTotalReturning(item) > 0">
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wide bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300" x-show="itemGoodQty(item) > 0">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                <span x-text="itemGoodQty(item) + ' good'"></span>
                            </span>
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wide bg-red-50 text-red-700 dark:bg-red-900/30 dark:text-red-300" x-show="itemDamagedQty(item) > 0">
                                <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span>
                                <span x-text="itemDamagedQty(item) + ' damaged'"></span>
                            </span>
                        </div>
                    </div>
                </div>

                <div class="flex flex-wrap gap-2 lg:justify-end">
                    <div class="inline-flex flex-col items-center justify-center min-w-[4.5rem] px-3 py-2 rounded-xl bg-gray-50 dark:bg-gray-900/50 border border-gray-100 dark:border-gray-700">
                        <span class="text-[9px] font-bold uppercase tracking-wider text-gray-400">Sold</span>
                        <span class="text-sm font-black text-[#0a1233] dark:text-gray-100"><span x-text="item.sale_quantity"></span></span>
                    </div>
                    <div class="inline-flex flex-col items-center justify-center min-w-[4.5rem] px-3 py-2 rounded-xl bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-100 dark:border-emerald-800/40">
                        <span class="text-[9px] font-bold uppercase tracking-wider text-emerald-600 dark:text-emerald-400">Returnable</span>
                        <span class="text-sm font-black text-emerald-700 dark:text-emerald-300"><span x-text="item.returnable_quantity"></span></span>
                    </div>
                    <div class="inline-flex flex-col items-center justify-center min-w-[4.5rem] px-3 py-2 rounded-xl border"
                        :class="itemTotalReturning(item) > 0 ? 'bg-[#4634ff]/5 border-[#4634ff]/20' : 'bg-gray-50 dark:bg-gray-900/50 border-gray-100 dark:border-gray-700'">
                        <span class="text-[9px] font-bold uppercase tracking-wider" :class="itemTotalReturning(item) > 0 ? 'text-[#4634ff]' : 'text-gray-400'">Returning</span>
                        <span class="text-sm font-black" :class="itemTotalReturning(item) > 0 ? 'text-[#4634ff]' : 'text-gray-400'"><span x-text="itemTotalReturning(item)"></span></span>
                    </div>
                    <div class="inline-flex flex-col items-center justify-center min-w-[4.5rem] px-3 py-2 rounded-xl bg-gray-50 dark:bg-gray-900/50 border border-gray-100 dark:border-gray-700">
                        <span class="text-[9px] font-bold uppercase tracking-wider text-gray-400">Price</span>
                        <span class="text-sm font-black text-[#0a1233] dark:text-gray-100">{{ currencySymbol() }}<span x-text="parseFloat(item.unit_price).toFixed(2)"></span></span>
                    </div>
                </div>
            </div>
        </div>

        <input type="hidden" :name="'items['+itemIndex+'][sale_item_id]'" :value="item.sale_item_id" />
        <input type="hidden" :name="'items['+itemIndex+'][use_fefo]'" :value="item.use_fefo ? '1' : '0'" />

        <div class="p-5 bg-gray-50/50 dark:bg-gray-900/20">
            @include('sales.partials.return-item-inputs')
        </div>
    </article>
</template>
