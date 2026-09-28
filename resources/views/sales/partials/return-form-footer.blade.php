<div class="grid grid-cols-1 lg:grid-cols-5 gap-6 pt-2">
    <div class="lg:col-span-2">
        <label class="block text-xs font-bold text-ink-muted uppercase tracking-wider mb-2">Return Note</label>
        <textarea
            name="note"
            rows="5"
            class="w-full rounded-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-900 text-ink-heading px-4 py-3 text-sm focus:ring-2 focus:ring-[#4634ff]/30 focus:border-[#4634ff] resize-none"
            placeholder="Optional note about this return..."
        >{{ $noteValue }}</textarea>
    </div>

    <div class="lg:col-span-3 rounded-2xl border border-gray-200 dark:border-gray-700 bg-gradient-to-br from-white via-gray-50 to-[#4634ff]/5 dark:from-gray-800 dark:via-gray-800 dark:to-[#4634ff]/10 p-6 space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-gray-200/80 dark:border-gray-700">
            <h4 class="text-xs font-black uppercase tracking-wider text-ink-muted">Refund Summary</h4>
            <span class="text-[10px] font-semibold text-gray-400" x-text="anyItemsSelected ? items.filter(i => itemTotalReturning(i) > 0).length + ' item(s)' : 'No items selected'"></span>
        </div>

        <div class="flex justify-between items-center">
            <span class="text-sm font-semibold text-gray-600 dark:text-gray-300">Subtotal</span>
            <span class="text-lg font-black text-[#0a1233] dark:text-gray-100">{{ currencySymbol() }} <span x-text="subtotal.toFixed(2)"></span></span>
        </div>

        <div class="flex justify-between items-center gap-4">
            <span class="text-sm font-semibold text-gray-600 dark:text-gray-300 shrink-0">Discount</span>
            <div class="flex rounded-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-900 overflow-hidden w-full max-w-[11rem] ml-auto">
                <span class="px-3 py-2.5 bg-gray-100 dark:bg-gray-800 text-gray-500 text-xs font-bold">{{ currencySymbol() }}</span>
                <input type="number" name="discount" step="0.01" min="0" x-model="discount" class="flex-1 px-3 py-2.5 text-sm text-right bg-transparent text-ink-heading focus:outline-none" />
            </div>
        </div>

        <div class="flex justify-between items-center gap-4">
            <span class="text-sm font-semibold text-gray-600 dark:text-gray-300 shrink-0">Restocking Fee</span>
            <div class="flex rounded-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-900 overflow-hidden w-full max-w-[11rem] ml-auto">
                <span class="px-3 py-2.5 bg-gray-100 dark:bg-gray-800 text-gray-500 text-xs font-bold">{{ currencySymbol() }}</span>
                <input type="number" name="restocking_fee" step="0.01" min="0" x-model="restockingFee" class="flex-1 px-3 py-2.5 text-sm text-right bg-transparent text-ink-heading focus:outline-none" />
            </div>
        </div>

        <div class="pt-4 mt-1 border-t-2 border-dashed border-gray-200 dark:border-gray-600 flex justify-between items-end">
            <div>
                <p class="text-xs font-bold uppercase tracking-wider text-ink-muted">Payable to Customer</p>
                <p class="text-[10px] text-gray-400 mt-0.5">Good & damaged items both refund at unit price</p>
            </div>
            <span class="text-3xl font-black text-[#4634ff] leading-none">{{ currencySymbol() }}<span x-text="payableToCustomer.toFixed(2)"></span></span>
        </div>
    </div>
</div>
