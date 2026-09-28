<div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
    <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50/80 dark:bg-gray-900/40 p-4">
        <p class="text-[10px] font-bold uppercase tracking-wider text-ink-muted mb-1">Return Invoice</p>
        <p class="text-sm font-black text-[#0a1233] dark:text-gray-100">{{ $returnInvoiceNo }}</p>
    </div>
    <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50/80 dark:bg-gray-900/40 p-4">
        <p class="text-[10px] font-bold uppercase tracking-wider text-ink-muted mb-1">Sale Invoice</p>
        <p class="text-sm font-black text-[#0a1233] dark:text-gray-100">{{ $sale->invoice_no }}</p>
    </div>
    <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50/80 dark:bg-gray-900/40 p-4">
        <p class="text-[10px] font-bold uppercase tracking-wider text-ink-muted mb-1">Customer</p>
        <p class="text-sm font-black text-[#0a1233] dark:text-gray-100 truncate">{{ $sale->customer->name }}</p>
    </div>
    <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-4">
        <label class="block text-[10px] font-bold uppercase tracking-wider text-ink-muted mb-1.5">
            Return Date <span class="text-red-500">*</span>
        </label>
        <input
            type="date"
            name="return_date"
            value="{{ $returnDate }}"
            required
            class="w-full rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-900 text-ink-heading px-3 py-2 text-sm focus:ring-2 focus:ring-[#4634ff]/30 focus:border-[#4634ff]"
        />
        @error('return_date')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
    </div>
</div>
