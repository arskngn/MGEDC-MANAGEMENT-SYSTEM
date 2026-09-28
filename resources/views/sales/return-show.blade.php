<x-app-layout>
    @php
        // Progressive ledger offset calculation
        $remainingUnpaid = max(0, (float)($sale->receivable_amount ?? 0) - (float)($sale->paid_amount ?? 0));
        $effectiveDue = 0;
        foreach ($sale->saleReturns ?? [] as $sr) {
            if ($sr->status === 'rejected') continue;
            $payable = (float)$sr->payable_amount;
            $offset = min($payable, $remainingUnpaid);
            $cashOwed = $payable - $offset;
            $remainingUnpaid -= $offset;
            if ($sr->id === $saleReturn->id) {
                $effectiveDue = max(0, $cashOwed - (float)$sr->paid_amount);
                break;
            }
        }
        $canStorePayment = auth()->user()->hasPermission('Store Customer Payment') || auth()->user()->hasRole('salesperson');
    @endphp

    <div x-data="{
            paymentOpen: false,
            paymentMethod: 'cash',
            checkNumber: '',
            checkDate: '',
            receivingAmount: '',
            maxDue: {{ (float)$effectiveDue }},
            get isReceivingExceeded() {
                if (!this.receivingAmount) return false;
                return (parseFloat(this.receivingAmount) || 0) > this.maxDue + 0.0001;
            }
        }"
        @keydown.escape.window="paymentOpen = false"
        class="max-w-6xl mx-auto space-y-6 pb-8 px-3 sm:px-0">

        {{-- ── PAGE HEADER ── --}}
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-[11px] font-bold uppercase tracking-widest text-[#4634ff] mb-1">Sales · Returns</p>
                <h2 class="text-xl sm:text-2xl font-black text-[#0a1233] dark:text-gray-100">View Sale Return</h2>
                <p class="text-xs sm:text-sm text-ink-muted mt-1">{{ $saleReturn->return_invoice_no }} · {{ $sale->customer->name }}</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">

                {{-- Back to returns list --}}
                <a href="{{ route('sales.return') }}" class="inline-flex items-center px-3 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                    <svg class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>Back
                </a>

                {{-- View original sale --}}
                <a href="{{ route('sales.show', $sale) }}" class="inline-flex items-center px-3 py-2 bg-violet-50 dark:bg-violet-950/30 border border-violet-300 dark:border-violet-700 text-[#4634ff] dark:text-violet-300 rounded-lg text-sm font-semibold hover:bg-violet-100 dark:hover:bg-violet-900/50 transition-colors">
                    <svg class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>View Sale
                </a>

                @if($saleReturn->status === 'pending')
                    @if(auth()->user()->hasPermission('Confirm Sale Return'))
                        <form method="POST" action="{{ route('sales.return.confirm', $saleReturn) }}" onsubmit="return confirm('Are you sure you want to confirm this sale return?');">
                            @csrf
                            <button type="submit" class="inline-flex items-center px-3 py-2 bg-emerald-600 border border-transparent rounded-lg text-sm font-bold text-white hover:bg-emerald-700 shadow-sm transition-colors">
                                <svg class="h-4 w-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>Confirm Return
                            </button>
                        </form>
                    @endif
                    @if(auth()->user()->hasPermission('Reject Sale Return'))
                        <form method="POST" action="{{ route('sales.return.reject', $saleReturn) }}" onsubmit="return confirm('Are you sure you want to reject this sale return?');">
                            @csrf
                            <button type="submit" class="inline-flex items-center px-3 py-2 bg-rose-600 border border-transparent rounded-lg text-sm font-bold text-white hover:bg-rose-700 shadow-sm transition-colors">
                                <svg class="h-4 w-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>Reject Return
                            </button>
                        </form>
                    @endif
                @endif

                @if(auth()->user()->hasPermission('Edit Sale Return') && $saleReturn->status === 'confirmed')
                    <a href="{{ route('sales.return.edit', $saleReturn) }}" class="inline-flex items-center px-3 py-2 bg-white dark:bg-gray-800 border border-[#5542ff] text-[#5542ff] dark:text-blue-400 rounded-lg text-sm font-semibold hover:bg-[#5542ff]/5 transition-colors">
                        <svg class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>Edit
                    </a>
                @endif

                @if($canStorePayment && $effectiveDue > 0.009 && $saleReturn->status === 'confirmed')
                    <button type="button"
                            @click="paymentOpen = true"
                            class="inline-flex items-center px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-sm font-bold shadow-sm transition-all active:scale-95">
                        <svg class="h-4 w-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                        Receive Payment
                    </button>
                @endif

                @if(auth()->user()->hasPermission('Download Sale Return Invoice PDF') && $saleReturn->status === 'confirmed')
                    <a href="{{ route('sales.return.pdf', $saleReturn) }}?print=true" target="_blank" class="inline-flex items-center px-3 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                        <svg class="h-4 w-4 mr-1 text-gray-400 dark:text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>Print
                    </a>
                    <a href="{{ route('sales.return.pdf', $saleReturn) }}" class="inline-flex items-center px-3 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                        <svg class="h-4 w-4 mr-1 text-gray-400 dark:text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>Download
                    </a>
                @endif
            </div>
        </div>

        {{-- ── META INFO GRID (2-col on mobile, 5-col on md+) ── --}}
        <div class="grid grid-cols-2 md:grid-cols-5 gap-3">
            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200/80 dark:border-gray-700 p-4 shadow-sm">
                <p class="text-[10px] font-extrabold text-ink-muted uppercase tracking-widest">Return Invoice</p>
                <div class="text-sm font-black text-[#0a1233] dark:text-gray-100 mt-1">{{ $saleReturn->return_invoice_no }}</div>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200/80 dark:border-gray-700 p-4 shadow-sm">
                <p class="text-[10px] font-extrabold text-ink-muted uppercase tracking-widest">Status</p>
                <div class="mt-1">
                    @if($saleReturn->status === 'pending')
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200 dark:bg-amber-900/30 dark:text-amber-300 dark:border-amber-700">Pending Approval</span>
                    @elseif($saleReturn->status === 'rejected')
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-rose-50 text-rose-700 border border-rose-200 dark:bg-rose-900/30 dark:text-rose-300 dark:border-rose-700">Rejected</span>
                    @else
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-emerald-900/30 dark:text-emerald-300 dark:border-emerald-700">Confirmed</span>
                    @endif
                </div>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200/80 dark:border-gray-700 p-4 shadow-sm">
                <p class="text-[10px] font-extrabold text-ink-muted uppercase tracking-widest">Sale Invoice</p>
                <a href="{{ route('sales.show', $sale) }}" class="text-sm font-black text-[#4634ff] dark:text-violet-300 hover:underline mt-1 block">{{ $sale->invoice_no }}</a>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200/80 dark:border-gray-700 p-4 shadow-sm">
                <p class="text-[10px] font-extrabold text-ink-muted uppercase tracking-widest">Customer</p>
                <div class="text-sm font-black text-[#0a1233] dark:text-gray-100 mt-1">{{ $sale->customer->name }}</div>
            </div>
            <div class="col-span-2 md:col-span-1 bg-white dark:bg-gray-800 rounded-2xl border border-gray-200/80 dark:border-gray-700 p-4 shadow-sm">
                <p class="text-[10px] font-extrabold text-ink-muted uppercase tracking-widest">Return Date</p>
                <div class="text-sm font-black text-[#0a1233] dark:text-gray-100 mt-1">{{ $saleReturn->return_date->format('d M, Y') }}</div>
            </div>
        </div>

        {{-- ── RETURNED PRODUCTS — MOBILE CARDS (< sm) ── --}}
        <div class="sm:hidden space-y-3">
            <div class="px-1">
                <h3 class="text-sm font-black text-gray-700 dark:text-gray-200 uppercase tracking-wide">Returned Products</h3>
            </div>
            @forelse($lines as $line)
                <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 overflow-hidden shadow-sm">
                    {{-- Product header --}}
                    <div class="p-4 border-b border-gray-100 dark:border-gray-700">
                        <div class="flex items-start justify-between gap-2">
                            <div>
                                <h4 class="text-sm font-bold text-ink-heading">{{ $line['product_name'] }}</h4>
                                <p class="text-xs text-ink-muted mt-0.5">SKU · {{ $line['sku'] }}</p>
                            </div>
                            <div class="text-right shrink-0">
                                <span class="text-[10px] font-bold text-ink-muted uppercase block">Sold Qty</span>
                                <span class="text-sm font-black text-ink-body">{{ $line['sale_quantity'] }}</span>
                            </div>
                        </div>
                    </div>

                    {{-- Good / Damaged 2-col --}}
                    <div class="grid grid-cols-2 divide-x divide-gray-100 dark:divide-gray-700 border-b border-gray-100 dark:border-gray-700">
                        <div class="p-4 bg-emerald-50/60 dark:bg-emerald-900/20">
                            <span class="text-[10px] font-bold text-emerald-700 dark:text-emerald-400 uppercase block mb-1">Good Returned</span>
                            <span class="text-2xl font-black text-ink-heading">{{ $line['good_quantity'] }}</span>
                        </div>
                        <div class="p-4 bg-rose-50/60 dark:bg-rose-900/20">
                            <span class="text-[10px] font-bold text-rose-700 dark:text-rose-400 uppercase block mb-1">Damaged</span>
                            <span class="text-2xl font-black text-ink-heading">{{ $line['damaged_quantity'] }}</span>
                        </div>
                    </div>

                    {{-- Batches --}}
                    @if(!empty($line['batches']))
                        <div class="p-4 bg-gray-50/50 dark:bg-gray-900/20 space-y-2">
                            <p class="text-[10px] font-bold text-ink-muted uppercase tracking-wide">Batches</p>
                            <div class="space-y-1.5">
                                @foreach($line['batches'] as $b)
                                    <div class="flex items-center justify-between bg-white dark:bg-gray-700 rounded-xl border border-gray-200 dark:border-gray-600 px-3 py-2">
                                        <div>
                                            <p class="text-xs font-bold text-gray-800 dark:text-gray-100">{{ $b['batch_number'] }}</p>
                                            @if(isset($b['condition']))
                                                <p class="text-[10px] text-ink-muted mt-0.5">{{ ucfirst($b['condition']) }}</p>
                                            @endif
                                        </div>
                                        <span class="text-xs font-bold text-ink-body">Qty: {{ $b['quantity'] ?? $b['good_quantity'] ?? 0 }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            @empty
                <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-8 text-center text-sm text-ink-muted italic">
                    No returned products.
                </div>
            @endforelse
        </div>

        {{-- ── RETURNED PRODUCTS — DESKTOP TABLE (≥ sm) ── --}}
        <div class="hidden sm:block bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200/80 dark:border-gray-700 overflow-hidden">
            <div class="px-5 py-3.5 border-b border-gray-100 dark:border-gray-700">
                <h3 class="text-sm font-black uppercase tracking-wider text-[#0a1233] dark:text-gray-100">Returned Products</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="gradient-bg text-white">
                            <th class="px-5 py-3.5 text-left text-[11px] font-black uppercase tracking-wider">Product</th>
                            <th class="px-5 py-3.5 text-center text-[11px] font-black uppercase tracking-wider">Sold Qty</th>
                            <th class="px-5 py-3.5 text-center text-[11px] font-black uppercase tracking-wider">Good Returned</th>
                            <th class="px-5 py-3.5 text-center text-[11px] font-black uppercase tracking-wider">Damaged</th>
                            <th class="px-5 py-3.5 text-left text-[11px] font-black uppercase tracking-wider">Batches</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700/60">
                        @forelse($lines as $line)
                            <tr class="hover:bg-gray-50/80 dark:hover:bg-gray-700/30 transition-colors">
                                <td class="px-5 py-4">
                                    <div class="font-black text-ink-heading">{{ $line['product_name'] }}</div>
                                    <div class="text-xs text-ink-muted mt-0.5">SKU · {{ $line['sku'] }}</div>
                                </td>
                                <td class="px-5 py-4 text-center font-bold text-ink-body">{{ $line['sale_quantity'] }}</td>
                                <td class="px-5 py-4 text-center">
                                    <span class="inline-flex items-center justify-center px-2.5 py-1 rounded-full text-sm font-black bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300">
                                        {{ $line['good_quantity'] }}
                                    </span>
                                </td>
                                <td class="px-5 py-4 text-center">
                                    <span class="inline-flex items-center justify-center px-2.5 py-1 rounded-full text-sm font-black bg-rose-50 dark:bg-rose-900/30 text-rose-700 dark:text-rose-300">
                                        {{ $line['damaged_quantity'] }}
                                    </span>
                                </td>
                                <td class="px-5 py-4">
                                    @if(!empty($line['batches']))
                                        <div class="space-y-1">
                                            @foreach($line['batches'] as $b)
                                                <div class="flex items-center gap-2 text-xs">
                                                    <span class="font-semibold text-ink-body">{{ $b['batch_number'] }}</span>
                                                    <span class="text-gray-300 dark:text-gray-600">·</span>
                                                    <span class="text-ink-muted">Qty {{ $b['quantity'] ?? $b['good_quantity'] ?? 0 }}</span>
                                                    @if(isset($b['condition']))
                                                        <span class="text-gray-300 dark:text-gray-600">·</span>
                                                        <span class="text-ink-muted">{{ ucfirst($b['condition']) }}</span>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <span class="text-xs text-ink-muted">—</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-5 py-8 text-center text-ink-muted italic">No returned products.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- ── FINANCIAL SUMMARY ── --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200/80 dark:border-gray-700 overflow-hidden">
            <div class="px-5 py-3.5 border-b border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/20">
                <h3 class="text-xs font-black text-gray-700 dark:text-gray-200 uppercase tracking-wider">Financial Summary</h3>
            </div>
            <div class="p-5 space-y-3.5 max-w-sm ml-auto">
                <div class="flex items-center justify-between text-sm">
                    <span class="text-ink-muted">Subtotal</span>
                    <span class="font-bold text-ink-body">{{ formatCurrency($saleReturn->subtotal) }}</span>
                </div>
                @if((float)($saleReturn->discount ?? 0) > 0)
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-ink-muted">Discount</span>
                        <span class="font-bold text-amber-600 dark:text-amber-400">- {{ formatCurrency($saleReturn->discount) }}</span>
                    </div>
                @endif
                @if((float)($saleReturn->restocking_fee ?? 0) > 0)
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-ink-muted">Restocking Fee</span>
                        <span class="font-bold text-rose-600 dark:text-rose-400">{{ formatCurrency($saleReturn->restocking_fee) }}</span>
                    </div>
                @endif
                <div class="pt-3 border-t border-gray-200 dark:border-gray-700 flex items-center justify-between">
                    <span class="text-sm font-bold text-ink-body">Payable</span>
                    <span class="font-black text-ink-heading text-lg">{{ formatCurrency($saleReturn->payable_amount) }}</span>
                </div>
                <div class="flex items-center justify-between text-sm">
                    <span class="text-ink-muted">Paid</span>
                    <span class="font-bold text-emerald-600 dark:text-emerald-400">{{ formatCurrency($saleReturn->paid_amount) }}</span>
                </div>
                @php $due = max(0, (float)$saleReturn->payable_amount - (float)$saleReturn->paid_amount); @endphp
                <div class="pt-2 border-t border-gray-200 dark:border-gray-700 flex items-center justify-between">
                    <span class="text-sm font-bold {{ $due > 0.009 ? 'text-red-600 dark:text-red-400' : 'text-green-600 dark:text-green-400' }}">Due Balance</span>
                    <span class="font-black text-lg {{ $due > 0.009 ? 'text-red-600 dark:text-red-400' : 'text-green-600 dark:text-green-400' }}">{{ formatCurrency($due) }}</span>
                </div>
            </div>
        </div>

        {{-- ── NOTE ── --}}
        @if($saleReturn->note)
            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200/80 dark:border-gray-700 p-5 shadow-sm">
                <p class="text-[11px] font-extrabold text-ink-muted uppercase tracking-widest mb-2">Note</p>
                <p class="text-sm text-ink-body leading-relaxed">{{ $saleReturn->note }}</p>
            </div>
        @endif

        {{-- ── RECEIVE PAYMENT MODAL ── --}}
        <div x-show="paymentOpen" x-cloak class="fixed inset-0 z-[210] overflow-y-auto" style="display: none;">
            <div class="flex items-center justify-center min-h-screen px-4 py-8">
                <div class="fixed inset-0 bg-gray-900/50 z-0" @click="paymentOpen = false"></div>
                <div class="relative bg-white dark:bg-gray-800 rounded-2xl shadow-2xl max-w-md w-full z-10 border border-gray-100 dark:border-gray-700" @click.stop>
                    
                    {{-- Modal Header --}}
                    <div class="px-6 py-5 flex justify-between items-center rounded-t-2xl" style="background: linear-gradient(135deg, var(--gradient-from) 0%, var(--gradient-to) 100%)">
                        <div class="flex items-center gap-3">
                            <div class="bg-white/20 rounded-lg p-2">
                                <svg class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-base font-bold text-white leading-tight">Receive Payment</h3>
                                <p class="text-white/60 text-xs mt-0.5">Record return transaction</p>
                            </div>
                        </div>
                        <button type="button" @click="paymentOpen = false" class="text-white/60 hover:text-white hover:bg-white/10 rounded-lg p-1.5 transition-colors" aria-label="Close">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                        </button>
                    </div>

                    {{-- Modal Body --}}
                    <div class="p-6">
                        <form method="POST" class="space-y-4" action="{{ route('sales.return.payment', $saleReturn) }}">
                            @csrf
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-semibold text-ink-muted uppercase tracking-wide mb-1.5">Invoice No.</label>
                                    <input type="text" value="{{ $saleReturn->return_invoice_no }}" disabled class="w-full rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-ink-muted px-3 py-2 text-sm" />
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-ink-muted uppercase tracking-wide mb-1.5">Customer</label>
                                    <input type="text" value="{{ $sale->customer->name ?? '—' }}" disabled class="w-full rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-ink-muted px-3 py-2 text-sm" />
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-semibold text-ink-muted uppercase tracking-wide mb-1.5">Payable Amount</label>
                                    <div class="relative flex items-center">
                                        <span class="absolute left-3 text-gray-400 text-sm font-semibold">{{ currencySymbol() }}</span>
                                        <input type="text" value="{{ number_format((float)$saleReturn->payable_amount, 2, '.', '') }}" disabled class="w-full pl-8 pr-3 py-2 rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-ink-muted text-sm" />
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-ink-muted uppercase tracking-wide mb-1.5">Due Amount</label>
                                    <div class="relative flex items-center">
                                        <span class="absolute left-3 text-gray-400 text-sm font-semibold">{{ currencySymbol() }}</span>
                                        <input type="text" value="{{ number_format($effectiveDue, 2, '.', '') }}" disabled class="w-full pl-8 pr-3 py-2 rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-ink-muted text-sm" />
                                    </div>
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-ink-muted uppercase tracking-wide mb-1.5">Receiving Amount <span class="text-red-500">*</span></label>
                                <div class="relative flex items-center">
                                    <span class="absolute left-3 text-gray-400 text-sm font-semibold">{{ currencySymbol() }}</span>
                                    <input type="number" name="receiving_amount" step="0.01" min="0.01" required 
                                        x-model="receivingAmount"
                                        max="{{ number_format($effectiveDue, 2, '.', '') }}"
                                        class="w-full pl-8 pr-3 py-2 rounded-lg border bg-white dark:bg-gray-900 text-ink-heading text-sm focus:outline-none transition-all" 
                                        :class="isReceivingExceeded ? 'border-red-500 text-red-600 focus:ring-1 focus:ring-red-500 focus:border-red-500' : 'border-gray-200 dark:border-gray-600 focus:ring-1 focus:ring-[#5542ff] focus:border-[#5542ff]'"
                                        placeholder="0.00" />
                                </div>
                                <p x-show="isReceivingExceeded" x-cloak class="text-red-500 text-xs mt-1.5 font-semibold flex items-center gap-1">
                                    <svg class="h-3.5 w-3.5 text-red-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    <span>Receiving amount cannot exceed {{ currencySymbol() }}{{ number_format($effectiveDue, 2) }}.</span>
                                </p>
                                @error('receiving_amount')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-ink-muted uppercase tracking-wide mb-1.5">Payment Method <span class="text-red-500">*</span></label>
                                <select name="payment_method" required 
                                    class="w-full rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-900 text-ink-heading px-3 py-2.5 text-sm focus:outline-none focus:ring-1 focus:ring-[#5542ff] focus:border-[#5542ff] transition-all" x-model="paymentMethod">
                                    <option value="cash">Cash</option>
                                    <option value="check">Check</option>
                                </select>
                            </div>
                            <div x-show="paymentMethod === 'check'" x-cloak class="space-y-4">
                                <div>
                                    <label class="block text-xs font-semibold text-ink-muted uppercase tracking-wide mb-1.5">Check Number <span class="text-red-500">*</span></label>
                                    <input type="text" name="check_number" x-model="checkNumber" :required="paymentMethod === 'check'" 
                                        class="w-full rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-900 text-ink-heading px-3 py-2.5 text-sm focus:outline-none focus:ring-1 focus:ring-[#5542ff] focus:border-[#5542ff] transition-all" placeholder="Enter check number" />
                                    @error('check_number')
                                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-ink-muted uppercase tracking-wide mb-1.5">Check Date <span class="text-red-500">*</span></label>
                                    <input type="date" name="check_date" x-model="checkDate" :required="paymentMethod === 'check'" 
                                        class="w-full rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-900 text-ink-heading px-3 py-2.5 text-sm focus:outline-none focus:ring-1 focus:ring-[#5542ff] focus:border-[#5542ff] transition-all" />
                                    @error('check_date')
                                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                            <button type="submit" 
                                :disabled="isReceivingExceeded"
                                class="w-full py-3 rounded-lg text-white font-bold text-sm shadow-md transition-all mt-2 disabled:opacity-50 disabled:cursor-not-allowed hover:opacity-95" 
                                style="background: linear-gradient(135deg, var(--gradient-from) 0%, var(--gradient-to) 100%)">
                                Submit Payment
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

    </div>
</x-app-layout>
