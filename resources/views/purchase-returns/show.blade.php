<x-app-layout>
    <div class="max-w-6xl mx-auto space-y-6 pb-8">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-[11px] font-bold uppercase tracking-widest text-[#4634ff] mb-1">Purchases · Returns</p>
                <h2 class="text-2xl font-black text-[#0a1233]">View Purchase Return</h2>
                <p class="text-sm text-gray-500 mt-1">{{ $purchaseReturn->return_invoice_no }} · {{ $purchaseReturn->purchase->supplier->name }}</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('purchases.return') }}" class="inline-flex items-center px-3 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors">
                    <svg class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>Back
                </a>
                @if(auth()->user()->hasPermission('Edit Purchase Return'))
                    <a href="{{ route('purchases.return.edit', $purchaseReturn) }}" class="inline-flex items-center px-3 py-2 bg-white border border-[#5542ff] text-[#5542ff] rounded-lg text-sm font-semibold hover:bg-[#5542ff]/5 transition-colors">
                        <svg class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>Edit
                    </a>
                @endif
                @if(auth()->user()->hasPermission('Download Purchase Return Invoice PDF'))
                    <a href="{{ route('purchases.return.invoice.pdf', $purchaseReturn) }}?print=true" target="_blank" class="inline-flex items-center px-3 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors">
                        <svg class="h-4 w-4 mr-1 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" /></svg>Print
                    </a>
                    <a href="{{ route('purchases.return.invoice.pdf', $purchaseReturn) }}" class="inline-flex items-center px-3 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors">
                        <svg class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>Download
                    </a>
                @endif
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border p-6">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <p class="text-xs font-bold text-gray-500 uppercase">Return Invoice</p>
                    <div class="text-sm font-black text-[#0a1233]">{{ $purchaseReturn->return_invoice_no }}</div>
                </div>
                <div>
                    <p class="text-xs font-bold text-gray-500 uppercase">Purchase Invoice</p>
                    <div class="text-sm font-black text-[#0a1233]">{{ $purchaseReturn->purchase->invoice_no }}</div>
                </div>
                <div>
                    <p class="text-xs font-bold text-gray-500 uppercase">Supplier</p>
                    <div class="text-sm font-black text-[#0a1233]">{{ $purchaseReturn->purchase->supplier->name }}</div>
                </div>
                <div>
                    <p class="text-xs font-bold text-gray-500 uppercase">Return Date</p>
                    <div class="text-sm text-gray-700">{{ $purchaseReturn->return_date->format('d M, Y') }}</div>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border p-6">
            <h3 class="text-sm font-black uppercase tracking-wider text-[#0a1233]">Returned Products</h3>
            <div class="mt-4 space-y-4">
                @foreach($lines as $line)
                    <div class="rounded-xl border p-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <div class="font-bold text-[#0a1233]">{{ $line['product_name'] }}</div>
                                <div class="text-xs text-gray-500">SKU · {{ $line['sku'] }}</div>
                            </div>
                            <div class="text-right">
                                <div class="text-sm text-gray-500">Purchased</div>
                                <div class="font-bold">{{ $line['purchase_quantity'] }}</div>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4 mt-4">
                            <div class="rounded-lg bg-emerald-50 p-3">
                                <div class="text-xs text-emerald-700 font-bold">Returned</div>
                                <div class="font-black text-emerald-800 text-lg">{{ $line['return_quantity'] }}</div>
                            </div>
                            <div class="rounded-lg bg-gray-50 p-3">
                                <div class="text-xs text-gray-700 font-bold">Unit Price</div>
                                <div class="font-black text-gray-800 text-lg">{{ formatCurrency($line['unit_price'] ?? 0) }}</div>
                            </div>
                        </div>

                        @if(!empty($line['batches']))
                            <div class="mt-3">
                                <div class="text-xs font-bold text-gray-500 mb-2">Batches</div>
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-2">
                                    @foreach($line['batches'] as $b)
                                        <div class="rounded-lg border p-2">
                                            <div class="text-sm font-semibold">{{ $b['batch_number'] }}</div>
                                            <div class="text-xs text-gray-500 mt-1">Quantity: {{ $b['quantity'] ?? 0 }}</div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border p-6">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <div class="text-xs text-gray-500">Subtotal</div>
                    <div class="font-black text-[#0a1233]">{{ formatCurrency($purchaseReturn->subtotal) }}</div>
                </div>
                <div>
                    <div class="text-xs text-gray-500">Receivable</div>
                    <div class="font-black text-[#0a1233]">{{ formatCurrency($purchaseReturn->receivable_amount) }}</div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
