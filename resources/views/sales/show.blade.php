<x-app-layout>
    @php
        $statusConfig = [
            'ordered'   => ['label' => 'Ordered',   'classes' => 'bg-orange-100 text-orange-700 border-orange-200 dark:bg-orange-900/30 dark:text-orange-300 dark:border-orange-800'],
            'partial'   => ['label' => 'Partial',   'classes' => 'bg-amber-100 text-amber-700 border-amber-200 dark:bg-amber-900/30 dark:text-amber-300 dark:border-amber-800'],
            'received'  => ['label' => 'Received',  'classes' => 'bg-green-100 text-green-700 border-green-200 dark:bg-green-900/30 dark:text-green-300 dark:border-green-800'],
            'completed' => ['label' => 'Completed', 'classes' => 'bg-purple-100 text-purple-700 border-purple-200 dark:bg-purple-900/30 dark:text-purple-300 dark:border-purple-800'],
            'confirmed' => ['label' => 'Confirmed', 'classes' => 'bg-blue-100 text-blue-700 border-blue-200 dark:bg-blue-900/30 dark:text-blue-300 dark:border-blue-800'],
            'pending'   => ['label' => 'Pending',   'classes' => 'bg-gray-100 text-gray-600 border-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600'],
            'rejected'  => ['label' => 'Rejected',  'classes' => 'bg-red-100 text-red-700 border-red-200 dark:bg-red-900/30 dark:text-red-300 dark:border-red-800'],
            'cancelled' => ['label' => 'Cancelled', 'classes' => 'bg-orange-100 text-orange-700 border-orange-200 dark:bg-orange-900/30 dark:text-orange-300 dark:border-orange-800'],
        ];
        $sc = $statusConfig[$sale->status] ?? ['label' => ucfirst($sale->status), 'classes' => 'bg-gray-100 text-gray-500 border-gray-200 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-700'];

        $totalReturnPayable = (float) ($sale->sale_returns_sum_payable_amount ?? 0);
        $netReceivable      = max(0, (float)$sale->receivable_amount - $totalReturnPayable);
        $netDue             = max(0, $netReceivable - (float)$sale->paid_amount);
    @endphp

    <div x-data="{ approvalOpen: false, approvalAction: 'confirm', deliveryOpen: false, cancelOpen: false }" class="max-w-7xl mx-auto space-y-4 sm:space-y-6 px-3 sm:px-0 pb-24 sm:pb-6">

        {{-- ── HEADER & ACTIONS ── --}}
        <div class="flex flex-col gap-4 sm:flex-row sm:justify-between sm:items-center bg-white dark:bg-gray-800 p-4 sm:p-5 rounded-2xl border border-gray-200/80 dark:border-gray-700 shadow-sm">
            <div class="flex items-center gap-3 flex-wrap">
                <h2 class="text-xl sm:text-2xl font-black text-ink-heading tracking-tight">{{ $sale->invoice_no }}</h2>
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-extrabold border shadow-sm {{ $sc['classes'] }}">{{ $sc['label'] }}</span>
            </div>

            {{-- Action Buttons --}}
            <div class="flex items-center gap-2 overflow-x-auto pb-1 sm:pb-0 -mx-1 px-1 sm:mx-0 sm:px-0 sm:flex-wrap">
                <a href="{{ route('sales.all') }}" class="inline-flex items-center shrink-0 px-3.5 py-2 bg-gray-100 dark:bg-gray-700/80 border border-gray-200 dark:border-gray-600 rounded-xl text-xs font-bold text-gray-700 dark:text-gray-200 hover:bg-gray-200 dark:hover:bg-gray-600 transition-all shadow-sm">
                    <svg class="h-4 w-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>Back
                </a>
                @if($sale->status === 'pending')
                    @if(auth()->user()->hasPermission('Confirm Sale'))
                        <button type="button" @click="approvalAction = 'confirm'; approvalOpen = true" class="inline-flex items-center shrink-0 px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold transition-all shadow-sm">
                            <svg class="h-4 w-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>Confirm
                        </button>
                    @endif
                    @if(auth()->user()->hasPermission('Reject Sale'))
                        <button type="button" @click="approvalAction = 'reject'; approvalOpen = true" class="inline-flex items-center shrink-0 px-3.5 py-2 bg-red-600 hover:bg-red-700 text-white rounded-xl text-xs font-bold transition-all shadow-sm">
                            <svg class="h-4 w-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>Reject
                        </button>
                    @endif
                @endif
                @if(auth()->user()->hasPermission('Cancel Sale') && $sale->status === 'confirmed' && $sale->delivery_status === \App\Models\Sale::DELIVERY_NOT_DISPATCHED && (float)$sale->paid_amount <= 0)
                    <button type="button" @click="cancelOpen = true" class="inline-flex items-center shrink-0 px-3.5 py-2 bg-orange-500 hover:bg-orange-600 text-white rounded-xl text-xs font-bold transition-all shadow-sm">
                        <svg class="h-4 w-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" /></svg>Cancel Order
                    </button>
                @endif
                @if(auth()->user()->hasPermission('Store Sale Return') && $sale->status === 'confirmed')
                    <a href="{{ route('sales.return.create', $sale) }}" class="inline-flex items-center shrink-0 px-3.5 py-2 bg-amber-50 dark:bg-amber-950/30 border border-amber-300 dark:border-amber-700 text-amber-700 dark:text-amber-300 rounded-xl text-xs font-bold hover:bg-amber-100 dark:hover:bg-amber-900/50 transition-all shadow-sm">
                        <svg class="h-4 w-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6" /></svg>Sale Return
                    </a>
                @endif
                @if(auth()->user()->hasPermission('Edit Sale') && $sale->status === 'confirmed')
                    <a href="{{ route('sales.edit', $sale) }}" class="inline-flex items-center shrink-0 px-3.5 py-2 bg-violet-50 dark:bg-violet-950/30 border border-violet-300 dark:border-violet-700 text-[#4634ff] dark:text-violet-300 rounded-xl text-xs font-bold hover:bg-violet-100 dark:hover:bg-violet-900/50 transition-all shadow-sm">
                        <svg class="h-4 w-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>Edit
                    </a>
                @elseif(auth()->user()->hasRole('salesperson') && (int) $sale->salesperson_id === (int) auth()->id() && $sale->status === 'pending')
                    <a href="{{ route('sales.edit', $sale) }}" class="inline-flex items-center shrink-0 px-3.5 py-2 bg-violet-50 dark:bg-violet-950/30 border border-violet-300 dark:border-violet-700 text-[#4634ff] dark:text-violet-300 rounded-xl text-xs font-bold hover:bg-violet-100 dark:hover:bg-violet-900/50 transition-all shadow-sm">
                        <svg class="h-4 w-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>Edit Sale
                    </a>
                @endif
                @if(auth()->user()->hasPermission('Download Sale Invoice PDF') && $sale->status === 'confirmed')
                    <a href="{{ route('sales.invoice.pdf', $sale) }}?print=true" target="_blank" class="inline-flex items-center shrink-0 px-3.5 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-xl text-xs font-bold text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-600 transition-all shadow-sm">
                        <svg class="h-4 w-4 mr-1.5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" /></svg>Print
                    </a>
                    <a href="{{ route('sales.invoice.pdf', $sale) }}" class="inline-flex items-center shrink-0 px-3.5 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-xl text-xs font-bold text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-600 transition-all shadow-sm">
                        <svg class="h-4 w-4 mr-1.5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>PDF
                    </a>
                @endif
            </div>
        </div>

        @if(session('success'))
            <div class="rounded-xl bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-700 text-green-800 dark:text-green-200 px-4 py-3 text-sm font-semibold">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="rounded-xl bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-700 text-red-800 dark:text-red-200 px-4 py-3 text-sm font-semibold">{{ session('error') }}</div>
        @endif

        {{-- ── INFO CARDS: 2×2 on mobile, 4-col on desktop ── --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3.5">
            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200/80 dark:border-gray-700 p-4 shadow-sm hover:border-gray-300 transition-all">
                <p class="text-[10px] font-extrabold text-ink-muted uppercase tracking-widest">Customer</p>
                <p class="text-sm sm:text-base font-black text-ink-heading mt-1 leading-tight">{{ $sale->customer->name }}</p>
                @if($sale->customer->phone)
                    <p class="text-xs text-ink-muted mt-1 flex items-center gap-1 font-medium">
                        <svg class="w-3 h-3 text-gray-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                        {{ $sale->customer->phone }}
                    </p>
                @endif
                @if($sale->customer->address)
                    <p class="text-xs text-ink-muted mt-1 flex items-center gap-1 font-medium">
                        <svg class="w-3 h-3 text-gray-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        <span class="truncate" title="{{ $sale->customer->address }}">{{ $sale->customer->address }}</span>
                    </p>
                @endif
                @if($sale->customer->route)
                    <p class="text-xs text-indigo-600 dark:text-indigo-400 mt-1.5 flex items-center gap-1 font-semibold">
                        <svg class="w-3 h-3 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/></svg>
                        <span class="truncate" title="{{ $sale->customer->route->display_name }}">{{ $sale->customer->route->display_name }}</span>
                    </p>
                @endif
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200/80 dark:border-gray-700 p-4 shadow-sm hover:border-gray-300 transition-all">
                <p class="text-[10px] font-extrabold text-ink-muted uppercase tracking-widest">Sale Date</p>
                <p class="text-sm sm:text-base font-black text-ink-heading mt-1 leading-tight">{{ $sale->sale_date->format('d M, Y') }}</p>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200/80 dark:border-gray-700 p-4 shadow-sm hover:border-gray-300 transition-all">
                <p class="text-[10px] font-extrabold text-ink-muted uppercase tracking-widest">Store Category</p>
                <p class="text-sm sm:text-base font-black text-ink-heading mt-1 leading-tight">{{ $sale->storeCategory?->name ?? $sale->customer->storeCategory?->name ?? '—' }}</p>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200/80 dark:border-gray-700 p-4 shadow-sm hover:border-gray-300 transition-all">
                <p class="text-[10px] font-extrabold text-ink-muted uppercase tracking-widest">Salesperson</p>
                <p class="text-sm sm:text-base font-black text-ink-heading mt-1 leading-tight">{{ $sale->salesperson->name ?? '—' }}</p>
            </div>
        </div>

        {{-- ── DELIVERY STATUS PANEL ── --}}
        @if($sale->status === 'confirmed')
        @php
            $deliverySteps = [
                ['key' => 'not_dispatched', 'label' => 'Confirmed',       'icon' => 'check-circle'],
                ['key' => 'in_truck',       'label' => 'In Truck',         'icon' => 'truck'],
                ['key' => 'out_for_delivery','label'=> 'Out for Delivery', 'icon' => 'package'],
                ['key' => 'delivered',      'label' => 'Delivered',        'icon' => 'check-badge'],
            ];
            $stepKeys      = array_column($deliverySteps, 'key');
            $currentIdx    = array_search($sale->delivery_status, $stepKeys);
            $currentIdx    = $currentIdx === false ? 0 : (int) $currentIdx;
            // Failed/Returned don't fit the forward pipeline; treat as separate
            $isOffPipeline = in_array($sale->delivery_status, ['failed_delivery', 'returned']);
            $dc            = $sale->delivery_status_color;
        @endphp
        <div id="delivery-panel" class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200/80 dark:border-gray-700 shadow-sm overflow-hidden"
             x-data="{ selectedStatus: '{{ $sale->delivery_status ?: 'in_truck' }}' }">

            {{-- Panel header --}}
            <div class="p-4 sm:px-5 sm:py-4 border-b border-gray-100 dark:border-gray-700/80">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl bg-blue-50 dark:bg-blue-900/30 border border-blue-200/60 dark:border-blue-700/50 flex items-center justify-center shrink-0">
                            <svg class="w-4 h-4 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zm12 0a2 2 0 11-4 0 2 2 0 014 0M1 3h15v13H1zm15 6h4l3 4v4h-7V9z"/>
                            </svg>
                        </div>
                        <div class="min-w-0">
                            <p class="text-[10px] sm:text-xs font-extrabold text-ink-muted uppercase tracking-widest leading-none">Delivery Status</p>
                            <div class="flex items-center gap-1.5 mt-1 flex-wrap">
                                <span class="inline-flex items-center whitespace-nowrap px-2.5 py-0.5 rounded-full text-xs font-bold border {{ $dc['bg'] }} {{ $dc['text'] }} {{ $dc['border'] }}">
                                    {{ $sale->delivery_status_label }}
                                </span>
                                @if($sale->delivered_at)
                                    <span class="text-[10px] text-ink-muted whitespace-nowrap">
                                        {{ $sale->delivered_at->format('d M Y, h:i A') }}
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                    @if(auth()->user()->hasPermission('Update Sale Delivery Status'))
                        <button type="button" @click="deliveryOpen = !deliveryOpen"
                                class="group inline-flex items-center justify-center px-3.5 py-2 bg-blue-50 hover:bg-blue-600 dark:bg-blue-950/40 dark:hover:bg-blue-600 text-blue-700 hover:!text-white dark:text-blue-300 dark:hover:!text-white border border-blue-200 hover:border-transparent dark:border-blue-700 rounded-xl text-xs font-bold transition-all shadow-sm shrink-0">
                            <svg class="w-3.5 h-3.5 mr-1.5 text-blue-700 group-hover:!text-white dark:text-blue-300 dark:group-hover:!text-white transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            <span class="text-blue-700 group-hover:!text-white dark:text-blue-300 dark:group-hover:!text-white transition-colors" x-text="deliveryOpen ? 'Close' : 'Update Status'"></span>
                        </button>
                    @endif
                </div>
            </div>

            {{-- Delivery pipeline stepper --}}
            @if(!$isOffPipeline)
            <div class="px-3 py-5 sm:px-6">
                <div class="relative flex items-start justify-between">
                    @foreach($deliverySteps as $i => $step)
                        @php 
                            $done = $i <= $currentIdx; 
                            $active = $i === $currentIdx;
                            $stepCount = count($deliverySteps);
                        @endphp
                        <div class="relative z-10 flex-1 flex flex-col items-center group">
                            {{-- Connecting line to next step --}}
                            @if($i < $stepCount - 1)
                                <div class="absolute top-3.5 sm:top-4 left-1/2 w-full h-[3px] sm:h-1 -z-10 bg-gray-200 dark:bg-gray-700">
                                    <div class="h-full transition-all duration-500 {{ $i < $currentIdx ? 'bg-blue-600 dark:bg-blue-500 w-full' : 'w-0' }}"></div>
                                </div>
                            @endif

                            <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-full flex items-center justify-center border-2 transition-all duration-300 shadow-xs
                                {{ $done ? 'bg-blue-600 dark:bg-blue-500 border-blue-600 dark:border-blue-500' : 'bg-white dark:bg-gray-800 border-gray-300 dark:border-gray-600' }}">
                                @if($done)
                                    <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                @else
                                    <span class="w-1.5 h-1.5 sm:w-2 sm:h-2 rounded-full bg-gray-300 dark:bg-gray-600"></span>
                                @endif
                            </div>
                            <span class="text-[9px] sm:text-[11px] font-bold text-center leading-tight mt-1.5 sm:mt-2 px-0.5 break-words max-w-[65px] sm:max-w-none
                                {{ $active ? 'text-blue-700 dark:text-blue-300 font-extrabold' : ($done ? 'text-ink-body' : 'text-ink-muted') }}">
                                {{ $step['label'] }}
                            </span>
                        </div>
                    @endforeach
                </div>
            </div>
            @else
            {{-- Off-pipeline: Failed / Returned --}}
            <div class="px-5 py-4 flex items-center gap-3">
                @php $dc = $sale->delivery_status_color; @endphp
                <span class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-bold border {{ $dc['bg'] }} {{ $dc['text'] }} {{ $dc['border'] }}">
                    @if($sale->delivery_status === 'failed_delivery')
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        Delivery Failed
                    @else
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/></svg>
                        Returned to Warehouse
                    @endif
                </span>
                <p class="text-xs text-ink-muted">This delivery did not complete the standard pipeline.</p>
            </div>
            @endif

            {{-- Delivery note display --}}
            @if($sale->delivery_note)
            <div class="px-5 pb-4">
                <div class="flex items-start gap-2 p-3 rounded-xl bg-amber-50 dark:bg-amber-950/20 border border-amber-200 dark:border-amber-800">
                    <svg class="w-4 h-4 text-amber-500 mt-0.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/></svg>
                    <p class="text-xs text-amber-800 dark:text-amber-300 font-medium leading-relaxed">{{ $sale->delivery_note }}</p>
                </div>
            </div>
            @endif

            {{-- Update form (collapsible) --}}
            @if(auth()->user()->hasPermission('Update Sale Delivery Status'))
            <div x-show="deliveryOpen" x-collapse
                 class="border-t border-gray-100 dark:border-gray-700 px-5 py-5 bg-gray-50/60 dark:bg-gray-700/20">
                <form action="{{ route('sales.delivery-status', $sale) }}" method="POST" class="space-y-4">
                    @csrf
                    <p class="text-xs font-extrabold text-ink-muted uppercase tracking-widest">Update Delivery Status</p>
                    
                    {{-- Quick Pick Pills --}}
                    <div>
                        <label class="block text-xs font-bold text-gray-600 dark:text-gray-300 mb-2">Quick Pick</label>
                        <div class="flex items-center gap-2 flex-wrap">
                            <button type="button" @click="selectedStatus = 'in_truck'"
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold border transition-all cursor-pointer"
                                    :class="selectedStatus === 'in_truck' ? 'bg-blue-600 text-white border-blue-600 shadow-xs' : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 border-gray-300 dark:border-gray-600 hover:bg-gray-50'">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zm12 0a2 2 0 11-4 0 2 2 0 014 0M1 3h15v13H1zm15 6h4l3 4v4h-7V9z"/></svg>
                                In Truck
                            </button>
                            <button type="button" @click="selectedStatus = 'out_for_delivery'"
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold border transition-all cursor-pointer"
                                    :class="selectedStatus === 'out_for_delivery' ? 'bg-purple-600 text-white border-purple-600 shadow-xs' : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 border-gray-300 dark:border-gray-600 hover:bg-gray-50'">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7H4a2 2 0 00-2 2v8a2 2 0 002 2h16a2 2 0 002-2V9a2 2 0 00-2-2zm-9 8H7m2-4H7"/></svg>
                                Out for Delivery
                            </button>
                            <button type="button" @click="selectedStatus = 'delivered'"
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold border transition-all cursor-pointer"
                                    :class="selectedStatus === 'delivered' ? 'bg-emerald-600 text-white border-emerald-600 shadow-xs' : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 border-gray-300 dark:border-gray-600 hover:bg-gray-50'">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                Delivered
                            </button>
                            <button type="button" @click="selectedStatus = 'returned'"
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold border transition-all cursor-pointer"
                                    :class="selectedStatus === 'returned' ? 'bg-amber-600 text-white border-amber-600 shadow-xs' : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 border-gray-300 dark:border-gray-600 hover:bg-gray-50'">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/></svg>
                                Returned
                            </button>
                        </div>
                    </div>

                    <div class="grid sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-600 dark:text-gray-300 mb-1.5">Delivery Status <span class="text-red-500">*</span></label>
                            <select name="delivery_status" x-model="selectedStatus" required
                                    class="w-full border border-gray-300 dark:border-gray-600 rounded-xl text-sm px-3 py-2.5 bg-white dark:bg-gray-800 text-ink-heading focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                @foreach(\App\Models\Sale::$deliveryStatuses as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-600 dark:text-gray-300 mb-1.5">Note / Remarks <span class="text-gray-400 font-normal">(optional)</span></label>
                            <input type="text" name="delivery_note"
                                   value="{{ old('delivery_note', $sale->delivery_note) }}"
                                   placeholder="e.g. Left at gate, driver: Juan"
                                   maxlength="500"
                                   class="w-full border border-gray-300 dark:border-gray-600 rounded-xl text-sm px-3 py-2.5 bg-white dark:bg-gray-800 text-ink-heading focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                    </div>
                    <div class="flex items-center gap-3 pt-1">
                        <button type="submit"
                                class="inline-flex items-center px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm font-bold transition-all shadow-sm">
                            <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            Save Delivery Status
                        </button>
                        <button type="button" @click="deliveryOpen = false"
                                class="inline-flex items-center px-4 py-2.5 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 text-gray-600 dark:text-gray-300 rounded-xl text-sm font-bold hover:bg-gray-50 dark:hover:bg-gray-600 transition-all">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
            @endif
        </div>
        @endif

        {{-- ── ITEMS: MOBILE CARD / DESKTOP TABLE ── --}}

        {{-- Mobile product cards (< sm) --}}
        <div class="sm:hidden space-y-3">
            <div class="px-1">
                <h3 class="text-sm font-black text-gray-700 dark:text-gray-200 uppercase tracking-wide">Order Items</h3>
            </div>

            @forelse($sale->items as $item)
                <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 overflow-hidden shadow-sm">
                    {{-- Item header --}}
                    <div class="p-4 border-b border-gray-100 dark:border-gray-700">
                        <div class="flex items-start justify-between gap-2">
                            <div>
                                <h4 class="text-sm font-bold text-ink-heading">{{ $item->product_name }}</h4>
                                <p class="text-xs text-ink-muted mt-0.5">{{ $item->sku }}</p>
                            </div>
                            @if($item->is_free)
                                <span class="px-2 py-0.5 bg-[#5542ff]/10 text-[#4634ff] rounded-full text-[10px] font-black uppercase">FREE</span>
                            @endif
                        </div>
                    </div>
                    {{-- Item stats grid --}}
                    <div class="grid grid-cols-2 divide-x divide-gray-100 dark:divide-gray-700 border-b border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-700/20 text-xs">
                        <div class="p-3">
                            <span class="text-[10px] font-bold text-gray-400 uppercase block">Quantity</span>
                            <span class="font-black text-ink-body">{{ number_format($item->quantity, 4) }} {{ $item->unit_label }}</span>
                        </div>
                        <div class="p-3 text-right">
                            <span class="text-[10px] font-bold text-gray-400 uppercase block">Unit Price</span>
                            <span class="font-black text-ink-body">
                                @if($item->is_free) Free @else {{ currencySymbol() }} {{ number_format($item->unit_price, 2) }} @endif
                            </span>
                        </div>
                    </div>
                    <div class="p-4 flex items-center justify-between bg-white dark:bg-gray-800">
                        <span class="text-xs text-gray-500">Sub Total</span>
                        <span class="text-base font-black text-ink-heading">
                            @if($item->is_free) Free @else {{ currencySymbol() }} {{ number_format($item->line_total, 2) }} @endif
                        </span>
                    </div>

                    @if($item->use_fefo || $item->batches->isNotEmpty())
                        <div class="p-4 border-t border-gray-100 dark:border-gray-700 bg-violet-50/20 dark:bg-violet-950/10 space-y-2">
                            @if($item->use_fefo)
                                <div class="flex items-center gap-1.5 text-xs text-blue-600 dark:text-blue-400 font-bold">
                                    <span class="px-1.5 py-0.5 bg-blue-100 dark:bg-blue-900/40 rounded text-[10px]">FEFO</span>
                                    <span>First Expiry First Out</span>
                                </div>
                            @endif
                            @if($item->batches->isNotEmpty())
                                <p class="text-[10px] font-bold text-ink-muted uppercase tracking-wide">Batches Used</p>
                                <div class="space-y-1.5">
                                    @foreach($item->batches as $batch)
                                        <div class="flex items-center justify-between bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 px-3 py-2">
                                            <div>
                                                <p class="text-xs font-bold text-gray-800 dark:text-gray-100">{{ $batch->productBatch->batch_number }}</p>
                                                @if($batch->productBatch->expiry_date)
                                                    <p class="text-[10px] text-gray-400">Exp: {{ $batch->productBatch->expiry_date->format('d M, Y') }}</p>
                                                @endif
                                            </div>
                                            <span class="text-xs font-bold text-ink-body">{{ number_format($batch->quantity, 4) }} {{ $item->unit_label }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @endif
                </div>
            @empty
                <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-8 text-center text-sm text-gray-400 italic">
                    No items in this sale.
                </div>
            @endforelse
        </div>

        {{-- Desktop items table (≥ sm) --}}
        <div class="hidden sm:block bg-white dark:bg-gray-800 rounded-2xl border border-gray-200/80 dark:border-gray-700 overflow-hidden shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="gradient-bg text-white">
                            <th class="px-5 py-3.5 text-left text-[11px] font-black uppercase tracking-wider">Product Details</th>
                            <th class="px-5 py-3.5 text-center text-[11px] font-black uppercase tracking-wider">Quantity</th>
                            <th class="px-5 py-3.5 text-center text-[11px] font-black uppercase tracking-wider">Unit Price</th>
                            <th class="px-5 py-3.5 text-center text-[11px] font-black uppercase tracking-wider">Discount</th>
                            <th class="px-5 py-3.5 text-right text-[11px] font-black uppercase tracking-wider">Sub Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700/60">
                        @forelse($sale->items as $item)
                            <tr class="hover:bg-gray-50/80 dark:hover:bg-gray-700/30 transition-colors">
                                <td class="px-5 py-4">
                                    <div class="font-black text-ink-heading">{{ $item->product_name }}</div>
                                    <div class="text-xs text-ink-muted font-medium mt-0.5">{{ $item->sku }}</div>
                                    @if($item->is_free)
                                        <span class="inline-flex items-center mt-1 px-2.5 py-0.5 bg-[#5542ff]/10 dark:bg-[#5542ff]/20 text-[#4634ff] border border-[#4634ff]/20 dark:border-[#4634ff]/30 rounded-full text-[10px] font-black uppercase tracking-wider">FREE</span>
                                    @endif
                                </td>
                                <td class="px-5 py-4 text-center">
                                    <span class="text-sm font-bold text-ink-body">{{ number_format($item->quantity, 4) }} <span class="text-xs text-gray-500 font-normal">{{ $item->unit_label }}</span></span>
                                </td>
                                <td class="px-5 py-4 text-center">
                                    @if($item->is_free)
                                        <span class="font-bold text-ink-muted">Free</span>
                                    @else
                                        <span class="text-ink-muted text-xs">{{ currencySymbol() }}</span>
                                        <span class="font-bold text-ink-body">{{ number_format($item->unit_price, 2) }}</span>
                                    @endif
                                </td>
                                <td class="px-5 py-4 text-center">
                                    @if($item->is_free)
                                        <span class="text-ink-muted text-xs">—</span>
                                    @else
                                        @if($item->discount_type === 'percentage')
                                            <span class="font-semibold text-ink-body">{{ $item->discount_rate ?? 0 }}%</span>
                                        @else
                                            <span class="text-ink-muted text-xs">{{ currencySymbol() }}</span>
                                            <span class="font-semibold text-ink-body">{{ number_format($item->discount_rate ?? 0, 2) }}</span>
                                        @endif
                                    @endif
                                </td>
                                <td class="px-5 py-4 text-right">
                                    @if($item->is_free)
                                        <span class="font-bold text-ink-muted">Free</span>
                                    @else
                                        <span class="text-ink-muted text-xs">{{ currencySymbol() }}</span>
                                        <span class="font-black text-ink-heading text-base">{{ number_format($item->line_total, 2) }}</span>
                                    @endif
                                </td>
                            </tr>
                            {{-- Batch Details Row --}}
                            @if($item->use_fefo || $item->batches->isNotEmpty())
                                <tr class="bg-slate-50/70 dark:bg-gray-900/40 border-t border-b border-gray-100 dark:border-gray-700/60">
                                    <td colspan="5" class="px-5 py-3.5">
                                        <div class="space-y-2">
                                            @if($item->use_fefo)
                                                <div class="flex items-center gap-2">
                                                    <span class="inline-flex items-center px-2 py-0.5 bg-blue-100 dark:bg-blue-900/40 text-blue-700 dark:text-blue-300 rounded-md text-[10px] font-black uppercase tracking-wider">FEFO</span>
                                                    <span class="text-xs text-ink-muted font-medium">First Expiry First Out - automatically selects batches by expiration date</span>
                                                </div>
                                            @endif
                                            @if($item->batches->isNotEmpty())
                                                <div>
                                                    <p class="text-[11px] font-bold text-ink-muted uppercase tracking-wider mb-2">Batches Used:</p>
                                                    <div class="space-y-1.5">
                                                        @foreach($item->batches as $batch)
                                                            <div class="flex items-center justify-between bg-white dark:bg-gray-800 rounded-xl border border-gray-200/80 dark:border-gray-700 px-4 py-2.5 text-xs shadow-xs">
                                                                <div class="flex items-center gap-3">
                                                                    <span class="font-bold text-ink-heading">{{ $batch->productBatch->batch_number }}</span>
                                                                    @if($batch->productBatch->expiry_date)
                                                                        <span class="text-ink-muted">Expires: <span class="font-semibold text-gray-600 dark:text-gray-300">{{ $batch->productBatch->expiry_date->format('d M, Y') }}</span></span>
                                                                    @endif
                                                                </div>
                                                                <span class="font-bold text-ink-body">{{ number_format($batch->quantity, 4) }} <span class="text-gray-400 font-normal">{{ $item->unit_label }}</span></span>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            @else
                                                @if(!$item->use_fefo)
                                                    <p class="text-xs text-ink-muted italic">No specific batches assigned</p>
                                                @endif
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endif
                        @empty
                            <tr>
                                <td colspan="5" class="px-5 py-8 text-center text-ink-muted italic">No items in this sale</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- ── SUMMARY ── --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 sm:gap-6">
            <div class="lg:col-span-2 space-y-4">
                @if($sale->note)
                    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200/80 dark:border-gray-700 p-5 shadow-sm">
                        <p class="text-[11px] font-extrabold text-ink-muted uppercase tracking-widest mb-2">Note</p>
                        <p class="text-sm text-ink-body leading-relaxed">{{ $sale->note }}</p>
                    </div>
                @endif
            </div>

            {{-- Financial Summary Card --}}
            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200/80 dark:border-gray-700 overflow-hidden shadow-sm">
                <div class="px-5 py-3.5 border-b border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800">
                    <h3 class="text-xs font-black text-gray-700 dark:text-gray-200 uppercase tracking-wider">Financial Summary</h3>
                </div>
                <div class="p-5 space-y-3.5">
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-ink-muted">Total Price</span>
                        <span class="font-bold text-ink-body">{{ currencySymbol() }} {{ number_format($sale->gross_subtotal, 2) }}</span>
                    </div>
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-ink-muted">Discount</span>
                        <span class="text-sm font-bold text-amber-600 dark:text-amber-400">- {{ currencySymbol() }} {{ number_format($sale->total_discount, 2) }}</span>
                    </div>
                    <div class="pt-3 border-t border-gray-200 dark:border-gray-700 flex items-center justify-between">
                        <span class="text-sm font-bold text-ink-body">Receivable</span>
                        <span class="font-black text-ink-heading text-lg">{{ currencySymbol() }} {{ number_format($sale->receivable_amount, 2) }}</span>
                    </div>
                    @if($totalReturnPayable > 0)
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-ink-muted">Return Deduction</span>
                            <span class="text-sm font-bold text-orange-600 dark:text-orange-400">- {{ currencySymbol() }} {{ number_format($totalReturnPayable, 2) }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-bold text-ink-body">Net Receivable</span>
                            <span class="font-black text-ink-heading">{{ currencySymbol() }} {{ number_format($netReceivable, 2) }}</span>
                        </div>
                    @endif
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-ink-muted">Received</span>
                        <span class="text-sm font-bold text-green-600 dark:text-green-400">{{ currencySymbol() }} {{ number_format($sale->paid_amount, 2) }}</span>
                    </div>
                    <div class="pt-2 border-t border-gray-200 dark:border-gray-700 flex items-center justify-between">
                        <span class="text-sm font-bold {{ $netDue > 0.009 ? 'text-red-600 dark:text-red-400' : 'text-green-600 dark:text-green-400' }}">Due Balance</span>
                        <span class="font-black text-lg {{ $netDue > 0.009 ? 'text-red-600 dark:text-red-400' : 'text-green-600 dark:text-green-400' }}">{{ currencySymbol() }} {{ number_format($netDue, 2) }}</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── PAYMENT HISTORY ── --}}
        @if($sale->payments->count() > 0)
            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-sm font-black text-gray-800 dark:text-gray-100">Payment History</h3>
                </div>

                {{-- Mobile Payment Cards --}}
                <div class="sm:hidden divide-y divide-gray-100 dark:divide-gray-700">
                    @foreach($sale->payments as $payment)
                        <div class="p-4 space-y-2">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold {{ $payment->payment_method === 'cash' ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300' : 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300' }}">
                                        {{ ucfirst($payment->payment_method) }}
                                    </span>
                                    <span class="text-xs text-ink-muted">{{ $payment->created_at->format('d M, Y') }}</span>
                                </div>
                                <span class="text-base font-black text-ink-heading">{{ currencySymbol() }} {{ number_format($payment->amount, 2) }}</span>
                            </div>
                            @if($payment->check_number || $payment->check_date)
                                <div class="flex items-center gap-3 text-xs text-ink-muted">
                                    @if($payment->check_number)
                                        <span>Check #{{ $payment->check_number }}</span>
                                    @endif
                                    @if($payment->check_date)
                                        <span>Date: {{ $payment->check_date->format('d M, Y') }}</span>
                                    @endif
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>

                {{-- Desktop Payment Table --}}
                <div class="hidden sm:block overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 dark:bg-gray-700/30">
                            <tr>
                                <th class="px-4 py-3 text-left text-[10px] font-black uppercase tracking-widest">Date</th>
                                <th class="px-4 py-3 text-left text-[10px] font-black uppercase tracking-widest">Payment Method</th>
                                <th class="px-4 py-3 text-left text-[10px] font-black uppercase tracking-widest">Check Number</th>
                                <th class="px-4 py-3 text-left text-[10px] font-black uppercase tracking-widest">Check Date</th>
                                <th class="px-4 py-3 text-right text-[10px] font-black uppercase tracking-widest">Amount</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach($sale->payments as $payment)
                                <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-700/30 transition-colors">
                                    <td class="px-4 py-3 text-ink-muted">{{ $payment->created_at->format('d M, Y') }}</td>
                                    <td class="px-4 py-3">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold {{ $payment->payment_method === 'cash' ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300' : 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300' }}">
                                            {{ ucfirst($payment->payment_method) }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-ink-muted">{{ $payment->check_number ?? '—' }}</td>
                                    <td class="px-4 py-3 text-ink-muted">{{ $payment->check_date ? $payment->check_date->format('d M, Y') : '—' }}</td>
                                    <td class="px-4 py-3 text-right font-bold text-gray-800 dark:text-gray-100">{{ currencySymbol() }} {{ number_format($payment->amount, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        {{-- ── SALE RETURNS (NAV & LIST) ── --}}
        @if($sale->saleReturns && $sale->saleReturns->count() > 0)
            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200/80 dark:border-gray-700 overflow-hidden shadow-sm">
                <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between flex-wrap gap-2 bg-gray-50/50 dark:bg-gray-800">
                    <div class="flex items-center gap-2">
                        <span class="p-1.5 bg-amber-100 dark:bg-amber-900/40 text-amber-600 dark:text-amber-400 rounded-lg">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6" /></svg>
                        </span>
                        <h3 class="text-sm font-black text-gray-800 dark:text-gray-100 uppercase tracking-wider">Sale Returns</h3>
                        <span class="px-2 py-0.5 rounded-full text-xs font-bold bg-amber-50 dark:bg-amber-950/30 text-amber-700 dark:text-amber-400 border border-amber-200 dark:border-amber-800">{{ $sale->saleReturns->count() }}</span>
                    </div>
                    @if(auth()->user()->hasPermission('All Sales Return'))
                        <a href="{{ route('sales.return') }}" class="text-xs font-bold text-[#4634ff] dark:text-violet-300 hover:underline flex items-center gap-1">
                            All Returns &rarr;
                        </a>
                    @endif
                </div>

                {{-- Mobile Returns Cards (< sm) --}}
                <div class="sm:hidden divide-y divide-gray-100 dark:divide-gray-700">
                    @foreach($sale->saleReturns as $ret)
                        <div class="p-4 space-y-3">
                            <div class="flex items-start justify-between gap-2">
                                <div>
                                    <span class="font-black text-sm text-[#0a1233] dark:text-gray-100">{{ $ret->return_invoice_no }}</span>
                                    <p class="text-xs text-ink-muted mt-0.5">{{ $ret->return_date->format('d M, Y') }}</p>
                                </div>
                                <div>
                                    @if($ret->status === 'pending')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200 dark:bg-amber-900/30 dark:text-amber-300 dark:border-amber-700">Pending</span>
                                    @elseif($ret->status === 'rejected')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200 dark:bg-rose-900/30 dark:text-rose-300 dark:border-rose-700">Rejected</span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-emerald-900/30 dark:text-emerald-300 dark:border-emerald-700">Confirmed</span>
                                    @endif
                                </div>
                            </div>
                            
                            <div class="flex items-center justify-between text-xs bg-gray-50 dark:bg-gray-700/30 p-2.5 rounded-xl">
                                <div>
                                    <span class="text-gray-400 block text-[10px] uppercase font-bold">Payable</span>
                                    <span class="font-black text-ink-body text-sm">{{ currencySymbol() }} {{ number_format($ret->payable_amount, 2) }}</span>
                                </div>
                                <div class="text-right">
                                    <span class="text-gray-400 block text-[10px] uppercase font-bold">Items Count</span>
                                    <span class="font-bold text-ink-body">{{ $ret->items->count() }} item(s)</span>
                                </div>
                            </div>

                            <div class="flex justify-end pt-1">
                                <a href="{{ route('sales.return.show', $ret) }}" class="inline-flex items-center px-3 py-1.5 bg-[#4634ff]/10 hover:bg-[#4634ff]/20 text-[#4634ff] dark:text-violet-300 rounded-lg text-xs font-bold transition-colors">
                                    <svg class="h-3.5 w-3.5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>View Return
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Desktop Returns Table (≥ sm) --}}
                <div class="hidden sm:block overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="gradient-bg text-white">
                                <th class="px-5 py-3.5 text-left text-[11px] font-black uppercase tracking-wider text-white">Return Invoice</th>
                                <th class="px-5 py-3.5 text-left text-[11px] font-black uppercase tracking-wider text-white">Date</th>
                                <th class="px-5 py-3.5 text-center text-[11px] font-black uppercase tracking-wider text-white">Status</th>
                                <th class="px-5 py-3.5 text-center text-[11px] font-black uppercase tracking-wider text-white">Items</th>
                                <th class="px-5 py-3.5 text-right text-[11px] font-black uppercase tracking-wider text-white">Payable</th>
                                <th class="px-5 py-3.5 text-right text-[11px] font-black uppercase tracking-wider text-white">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700/60">
                            @foreach($sale->saleReturns as $ret)
                                <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-700/30 transition-colors">
                                    <td class="px-5 py-3.5 font-bold text-[#0a1233] dark:text-gray-100">{{ $ret->return_invoice_no }}</td>
                                    <td class="px-5 py-3.5 text-ink-muted text-xs">{{ $ret->return_date->format('d M, Y') }}</td>
                                    <td class="px-5 py-3.5 text-center">
                                        @if($ret->status === 'pending')
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200 dark:bg-amber-900/30 dark:text-amber-300 dark:border-amber-700">Pending</span>
                                        @elseif($ret->status === 'rejected')
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200 dark:bg-rose-900/30 dark:text-rose-300 dark:border-rose-700">Rejected</span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-emerald-900/30 dark:text-emerald-300 dark:border-emerald-700">Confirmed</span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-3.5 text-center text-xs text-ink-muted font-medium">{{ $ret->items->count() }} item(s)</td>
                                    <td class="px-5 py-3.5 text-right font-black text-gray-800 dark:text-gray-100">{{ currencySymbol() }} {{ number_format($ret->payable_amount, 2) }}</td>
                                    <td class="px-5 py-3.5 text-right">
                                        <a href="{{ route('sales.return.show', $ret) }}" class="inline-flex items-center px-2.5 py-1 bg-[#4634ff]/10 hover:bg-[#4634ff]/20 text-[#4634ff] dark:text-violet-300 rounded-lg text-xs font-bold transition-colors">
                                            <svg class="h-3.5 w-3.5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>View
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        <!-- Approval Modal -->
        <div x-show="approvalOpen" x-cloak class="fixed inset-0 z-[210] overflow-y-auto" style="display: none;">
            <div class="flex items-center justify-center min-h-screen px-4 py-8">
                <div class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm z-0" @click="approvalOpen = false"></div>
                <div class="relative bg-white dark:bg-gray-800 rounded-2xl shadow-2xl max-w-md w-full z-10 border border-gray-100 dark:border-gray-700 overflow-hidden" @click.stop>
                    
                    {{-- Modal Header --}}
                    <div class="px-6 py-5 flex justify-between items-center transition-colors duration-300"
                        :style="approvalAction === 'confirm' 
                            ? 'background: linear-gradient(135deg, #059669 0%, #10b981 100%)' 
                            : 'background: linear-gradient(135deg, #dc2626 0%, #f43f5e 100%)'">
                        <div class="flex items-center gap-3">
                            <div class="bg-white/20 rounded-xl p-2.5 backdrop-blur-sm">
                                <template x-if="approvalAction === 'confirm'">
                                    <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </template>
                                <template x-if="approvalAction === 'reject'">
                                    <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </template>
                            </div>
                            <div>
                                <h3 class="text-base font-bold text-white leading-tight" x-text="approvalAction === 'confirm' ? 'Confirm Sale' : 'Reject Sale'"></h3>
                                <p class="text-white/80 text-xs mt-0.5" x-text="approvalAction === 'confirm' ? 'Approve transaction & commit stock' : 'Cancel transaction & restore inventory'"></p>
                            </div>
                        </div>
                        <button type="button" @click="approvalOpen = false" class="text-white/70 hover:text-white hover:bg-white/10 rounded-lg p-1.5 transition-colors" aria-label="Close">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                        </button>
                    </div>

                    {{-- Modal Body --}}
                    <div class="p-6 space-y-4">
                        <div class="p-4 rounded-xl bg-gray-50 dark:bg-gray-700/40 border border-gray-100 dark:border-gray-700">
                            <p class="text-sm text-gray-700 dark:text-gray-200">
                                Are you sure you want to <span class="font-black uppercase tracking-wide text-xs px-2 py-0.5 rounded" :class="approvalAction === 'confirm' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300' : 'bg-rose-100 text-rose-700 dark:bg-rose-900/40 dark:text-rose-300'" x-text="approvalAction"></span> sale <span class="font-extrabold text-ink-heading">{{ $sale->invoice_no }}</span>?
                            </p>
                        </div>

                        <template x-if="approvalAction === 'reject'">
                            <div class="p-3.5 rounded-xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800/60 flex items-start gap-2.5">
                                <svg class="h-5 w-5 text-rose-600 dark:text-rose-400 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                                <div>
                                    <span class="text-xs font-bold text-rose-800 dark:text-rose-300 block">Stock Restoration</span>
                                    <p class="text-xs text-rose-700 dark:text-rose-400 mt-0.5">Rejecting this sale will return the reserved quantities back to product inventory batches.</p>
                                </div>
                            </div>
                        </template>

                        <form method="POST" :action="approvalAction === 'confirm' ? '{{ route('sales.confirm', $sale) }}' : '{{ route('sales.reject', $sale) }}'">
                            @csrf
                            <div class="flex gap-3 pt-2">
                                <button type="button" @click="approvalOpen = false" class="flex-1 py-2.5 px-4 border border-gray-300 dark:border-gray-600 rounded-xl text-gray-700 dark:text-gray-200 text-sm font-bold hover:bg-gray-50 dark:hover:bg-gray-700 transition-all shadow-sm">
                                    Cancel
                                </button>
                                <button type="submit" class="flex-1 py-2.5 px-4 rounded-xl text-white text-sm font-bold shadow-md hover:shadow-lg hover:opacity-95 transition-all"
                                    :class="approvalAction === 'confirm' ? 'bg-gradient-to-r from-emerald-600 to-green-600' : 'bg-gradient-to-r from-rose-600 to-red-600'"
                                    x-text="approvalAction === 'confirm' ? 'Yes, Confirm' : 'Yes, Reject'">
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── CANCEL ORDER MODAL ── --}}
        <div x-show="cancelOpen" x-cloak class="fixed inset-0 z-[220] overflow-y-auto" style="display: none;">
            <div class="flex items-center justify-center min-h-screen px-4 py-8">
                <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm z-0" @click="cancelOpen = false"></div>
                <div class="relative bg-white dark:bg-gray-800 rounded-2xl shadow-2xl max-w-md w-full z-10 border border-gray-100 dark:border-gray-700 overflow-hidden" @click.stop>

                    {{-- Modal Header --}}
                    <div class="px-6 py-5 flex justify-between items-center" style="background: linear-gradient(135deg, #ea580c 0%, #f97316 100%)">
                        <div class="flex items-center gap-3">
                            <div class="bg-white/20 rounded-xl p-2.5 backdrop-blur-sm">
                                <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-base font-bold text-white leading-tight">Cancel Order</h3>
                                <p class="text-white/80 text-xs mt-0.5">Void confirmed sale & restore inventory</p>
                            </div>
                        </div>
                        <button type="button" @click="cancelOpen = false" class="text-white/70 hover:text-white hover:bg-white/10 rounded-lg p-1.5 transition-colors" aria-label="Close">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                        </button>
                    </div>

                    {{-- Modal Body --}}
                    <div class="p-6 space-y-4">
                        <div class="p-4 rounded-xl bg-gray-50 dark:bg-gray-700/40 border border-gray-100 dark:border-gray-700">
                            <p class="text-sm text-gray-700 dark:text-gray-200">
                                Are you sure you want to cancel sale <span class="font-extrabold text-ink-heading">{{ $sale->invoice_no }}</span>?
                            </p>
                        </div>

                        <div class="p-3.5 rounded-xl bg-orange-50 dark:bg-orange-950/40 border border-orange-200 dark:border-orange-800/60 flex items-start gap-2.5">
                            <svg class="h-5 w-5 text-orange-600 dark:text-orange-400 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                            <div>
                                <span class="text-xs font-bold text-orange-800 dark:text-orange-300 block">Stock Restoration</span>
                                <p class="text-xs text-orange-700 dark:text-orange-400 mt-0.5">Cancelling this order will return all product quantities back to inventory. This action cannot be undone.</p>
                            </div>
                        </div>

                        <form method="POST" action="{{ route('sales.cancel', $sale) }}">
                            @csrf
                            <div class="flex gap-3 pt-2">
                                <button type="button" @click="cancelOpen = false" class="flex-1 py-2.5 px-4 border border-gray-300 dark:border-gray-600 rounded-xl text-gray-700 dark:text-gray-200 text-sm font-bold hover:bg-gray-50 dark:hover:bg-gray-700 transition-all shadow-sm">
                                    Go Back
                                </button>
                                <button type="submit" class="flex-1 py-2.5 px-4 rounded-xl text-white text-sm font-bold shadow-md hover:shadow-lg hover:opacity-95 transition-all bg-gradient-to-r from-orange-600 to-orange-500">
                                    Yes, Cancel Order
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

    </div>
</x-app-layout>
