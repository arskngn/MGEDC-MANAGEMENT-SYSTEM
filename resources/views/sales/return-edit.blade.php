<x-app-layout>
    @php
        $initialLines = collect($lines)->values()->all();
        $returnDate = old('return_date', $saleReturn->return_date->format('Y-m-d'));
        $noteValue = old('note', $saleReturn->note);
    @endphp
    <script type="application/json" id="sale-return-initial-lines">@json($initialLines)</script>

    <div
        class="max-w-6xl mx-auto space-y-6 pb-8"
        x-data="saleReturnForm({
            discount: {{ old('discount', $saleReturn->discount) }},
            restockingFee: {{ old('restocking_fee', $saleReturn->restocking_fee) }}
        })"
    >
        {{-- Page header --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <p class="text-[11px] font-bold uppercase tracking-widest text-[#4634ff] mb-1">Sales · Returns</p>
                <h2 class="text-2xl font-black text-[#0a1233] dark:text-gray-100">Edit Sale Return</h2>
                <p class="text-sm text-ink-muted mt-1">{{ $saleReturn->return_invoice_no }} · {{ $sale->customer->name }}</p>
            </div>
            <div class="flex items-center gap-2 shrink-0">
                <a href="{{ route('sales.return') }}" class="inline-flex items-center px-4 py-2.5 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-600 rounded-xl text-sm font-semibold text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                    <svg class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                    Back
                </a>
                <button
                    type="submit"
                    form="sale-return-form"
                    :disabled="!anyItemsSelected || hasErrors"
                    class="inline-flex items-center px-5 py-2.5 rounded-xl bg-[#4634ff] text-white text-sm font-bold hover:bg-violet-700 disabled:opacity-50 disabled:cursor-not-allowed transition-all shadow-lg shadow-[#4634ff]/25"
                >
                    <svg class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                    Update Return
                </button>
            </div>
        </div>

        @if ($errors->any())
            <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-700 rounded-2xl p-4 flex items-start gap-3">
                <span class="flex-shrink-0 w-8 h-8 rounded-full bg-red-100 dark:bg-red-900/40 flex items-center justify-center">
                    <svg class="h-4 w-4 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                </span>
                <div class="flex-1">
                    <h4 class="font-bold text-red-800 dark:text-red-200 text-sm mb-2">Please fix the following errors</h4>
                    <ul class="text-sm text-red-700 dark:text-red-300 space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        <form id="sale-return-form" action="{{ route('sales.return.update', $saleReturn) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                @include('sales.partials.return-form-meta', [
                    'returnInvoiceNo' => $saleReturn->return_invoice_no,
                    'returnDate' => $returnDate,
                ])
            </div>

            <div class="space-y-4">
                <div class="flex items-center justify-between px-1">
                    <div>
                        <h3 class="text-sm font-black uppercase tracking-wider text-[#0a1233] dark:text-gray-100">Products to Return</h3>
                        <p class="text-xs text-ink-muted mt-0.5">Enter good qty to restock, damaged qty for write-off</p>
                    </div>
                    <span class="text-xs font-semibold text-gray-400" x-text="items.length + ' line(s)'"></span>
                </div>

                @include('sales.partials.return-product-card')
            </div>

            @error('items')
                <p class="text-red-600 text-sm bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-700 rounded-xl px-4 py-3">{{ $message }}</p>
            @enderror

            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                @include('sales.partials.return-form-footer', ['noteValue' => $noteValue])
            </div>
        </form>
    </div>
</x-app-layout>
