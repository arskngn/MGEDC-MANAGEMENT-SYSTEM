<x-app-layout>
    <script>
        function showReturnModal(title, subtitle, options, onClose) {
            var existing = document.getElementById('_return_action_modal');
            if (existing) existing.remove();

            var overlay = document.createElement('div');
            overlay.id = '_return_action_modal';
            overlay.style.cssText = 'position:fixed;inset:0;z-index:99999;display:flex;align-items:center;justify-content:center;background:rgba(0,0,0,0.5);backdrop-filter:blur(2px)';

            var card = document.createElement('div');
            card.style.cssText = 'background:#fff;border-radius:14px;box-shadow:0 20px 60px rgba(0,0,0,0.25);max-width:420px;width:calc(100% - 2rem);padding:28px;position:relative';

            var header = document.createElement('div');
            header.style.cssText = 'display:flex;justify-content:space-between;align-items:center;margin-bottom:8px';

            var titleEl = document.createElement('h3');
            titleEl.style.cssText = 'font-size:1.05rem;font-weight:700;color:#111827;margin:0';
            titleEl.textContent = title;

            var closeBtn = document.createElement('button');
            closeBtn.style.cssText = 'background:none;border:none;cursor:pointer;color:#9ca3af;font-size:1.4rem;line-height:1;padding:2px 6px';
            closeBtn.innerHTML = '&times;';

            header.appendChild(titleEl);
            header.appendChild(closeBtn);
            card.appendChild(header);

            var sub = document.createElement('p');
            sub.style.cssText = 'font-size:0.82rem;color:#6b7280;margin:0 0 20px';
            sub.textContent = subtitle;
            card.appendChild(sub);

            var optWrap = document.createElement('div');
            optWrap.style.cssText = 'display:flex;flex-direction:column;gap:10px';
            options.forEach(function(opt) {
                var btn = document.createElement('button');
                btn.style.cssText = 'display:flex;flex-direction:column;align-items:flex-start;padding:13px 16px;background:#f9fafb;border:1px solid #e5e7eb;border-radius:9px;cursor:pointer;width:100%;text-align:left;transition:background 0.15s';
                btn.addEventListener('mouseover', function() { btn.style.background = '#f3f4f6'; });
                btn.addEventListener('mouseout', function() { btn.style.background = '#f9fafb'; });
                var lbl = document.createElement('span');
                lbl.style.cssText = 'font-size:0.875rem;font-weight:700;color:#111827';
                lbl.textContent = opt.label;
                var dsc = document.createElement('span');
                dsc.style.cssText = 'font-size:0.75rem;color:#6b7280;margin-top:4px';
                dsc.textContent = opt.desc;
                btn.appendChild(lbl);
                btn.appendChild(dsc);
                btn.addEventListener('click', function() { close(); opt.handler(); });
                optWrap.appendChild(btn);
            });
            card.appendChild(optWrap);

            var footer = document.createElement('div');
            footer.style.cssText = 'margin-top:22px;display:flex;justify-content:flex-end';
            var cancelBtn = document.createElement('button');
            cancelBtn.style.cssText = 'padding:8px 20px;border:1px solid #d1d5db;border-radius:8px;background:#fff;color:#374151;font-size:0.875rem;font-weight:600;cursor:pointer';
            cancelBtn.textContent = 'Cancel';
            footer.appendChild(cancelBtn);
            card.appendChild(footer);

            overlay.appendChild(card);
            document.body.appendChild(overlay);

            function close() { overlay.remove(); if (onClose) onClose(); }
            overlay.addEventListener('click', function(e) { if (e.target === overlay) close(); });
            closeBtn.addEventListener('click', close);
            cancelBtn.addEventListener('click', close);
        }
    </script>

    <div
        x-data="{
            selectedItems: [],
            selectAll: false,
            showPrintMessage: false,
            toggleSelectAll() {
                if (this.selectAll) {
                    this.selectedItems = Array.from(document.querySelectorAll('.row-checkbox')).map(cb => cb.value);
                } else {
                    this.selectedItems = [];
                }
            },
            printSelected() {
                if (this.selectedItems.length === 0) {
                    showReturnModal('No Records Selected', 'Please select at least one return to print.', [], null);
                    return;
                }
                const ids = this.selectedItems.join(',');
                this.bulkOpen = false;
                showReturnModal('Print Options', 'How would you like to print the selected returns?', [
                    { label: 'Batch Print', desc: 'Print each return invoice on its own page.', handler: function() { window.open('/purchases/return/export/pdf?ids=' + ids + '&print=true&type=batch', '_blank'); } },
                    { label: 'Total Print', desc: 'Print all selected returns combined in a single consolidated document.', handler: function() { window.open('/purchases/return/export/pdf?ids=' + ids + '&print=true&type=total', '_blank'); } },
                ], null);
            },
            openMoreId: null,
            dropdownTop: '0px',
            dropdownRight: '0px',
            bulkOpen: false,
            paymentOpen: false,
            paymentReturn: null,
            paymentMethod: 'cash',
            checkNumber: '',
            checkDate: '',
            receivingAmount: '',
            get isReceivingExceeded() {
                if (!this.paymentReturn || !this.receivingAmount) return false;
                return parseFloat(this.receivingAmount) > parseFloat(this.paymentReturn.due);
            },
            toggleMore(id, $event) {
                if (this.openMoreId === id) {
                    this.openMoreId = null;
                    return;
                }
                const r = $event.currentTarget.getBoundingClientRect();
                this.dropdownTop = (r.bottom + 4) + 'px';
                this.dropdownRight = (window.innerWidth - r.right) + 'px';
                this.openMoreId = id;
            },
            openPayment(r) {
                this.paymentReturn = r;
                this.paymentOpen = true;
                this.paymentMethod = 'cash';
                this.checkNumber = '';
                this.checkDate = '';
                this.receivingAmount = '';
                this.openMoreId = null;
            },
        }"
        @keydown.escape.window="paymentOpen = false; openMoreId = null; bulkOpen = false"
        @keydown.window.ctrl.p.prevent="printSelected()"
        @keydown.window.ctrl.a.prevent="selectAll = true; toggleSelectAll()"
        @scroll.window="if (openMoreId) openMoreId = null"
        class="space-y-4"
    >
        <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
            <h2 class="text-xl font-bold text-[#0a1233] shrink-0">All Purchase Return</h2>

            <div class="flex flex-col sm:flex-row sm:items-center gap-2.5 w-full sm:w-auto">
                <form
                    method="GET"
                    action="{{ route('purchases.return') }}"
                    x-data="{
                        ...purchaseDateRange({ dateFrom: @js(request('date_from')), dateTo: @js(request('date_to')) }),
                        mobileFiltersOpen: false
                    }"
                    x-ref="filterForm"
                    @keydown.escape.window="escapeClose()"
                    class="w-full sm:flex sm:flex-row sm:items-center sm:gap-2 sm:w-auto"
                >
                    @if($filterPurchaseId)
                        <input type="hidden" name="purchase_id" value="{{ $filterPurchaseId }}" />
                    @endif
                    <input type="hidden" name="date_from" x-model="dateFrom" />
                    <input type="hidden" name="date_to" x-model="dateTo" />

                    {{-- Search row + Filter toggle button on mobile --}}
                    <div class="flex items-center gap-2 sm:contents">
                        <div class="relative flex flex-1 sm:w-[240px] sm:flex-none">
                            <input
                                type="text"
                                name="search"
                                value="{{ request('search') }}"
                                placeholder="Search..."
                                class="w-full pl-3 pr-8 py-2 border border-[#5542ff]/40 dark:border-gray-600 rounded-l-xl sm:rounded-l-md focus:outline-none focus:ring-1 focus:ring-[#5542ff] focus:border-[#5542ff] text-sm bg-white dark:bg-gray-900 text-ink-heading placeholder-gray-400 dark:placeholder-gray-500"
                            />
                            @if(request('search'))
                                <a href="{{ request()->fullUrlWithQuery(['search' => null]) }}" class="absolute right-9 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 text-xs px-1 h-4 w-4 flex items-center justify-center" title="Clear search">&#x2715;</a>
                            @endif
                            <button type="submit" class="px-3 gradient-bg text-white rounded-r-xl sm:rounded-r-md hover:opacity-90 transition-colors flex items-center justify-center shrink-0" title="Search">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                            </button>
                        </div>

                        @php
                            $activeFilters = (int)(bool)request('date_from') + (int)(bool)request('date_to') + (int)(bool)request('supplier_id');
                        @endphp
                        <button type="button" @click="mobileFiltersOpen = !mobileFiltersOpen"
                                class="sm:hidden relative flex items-center gap-1.5 px-3 py-2 rounded-xl border text-sm font-semibold transition-all shrink-0"
                                :class="mobileFiltersOpen ? 'bg-[#5542ff] text-white border-[#5542ff]' : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 border-gray-300 dark:border-gray-600'">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z"/></svg>
                            <span>Filter</span>
                            @if($activeFilters > 0)
                                <span class="absolute -top-1.5 -right-1.5 h-4 w-4 flex items-center justify-center rounded-full bg-red-500 text-white text-[9px] font-black">{{ $activeFilters }}</span>
                            @endif
                        </button>
                    </div>

                    {{-- Collapsible Filter options on mobile, inline on desktop --}}
                    <div x-show="mobileFiltersOpen" x-cloak class="sm:!block sm:flex sm:flex-row sm:items-center sm:gap-2" style="display:none">
                        <div class="grid grid-cols-2 gap-2 mt-2 sm:mt-0 sm:flex sm:flex-row sm:gap-2">
                            <div class="col-span-2 sm:col-span-1 sm:w-auto">
                                @include('components.date-range-picker')
                            </div>

                            {{-- Supplier Filter --}}
                            @if(isset($suppliers) && $suppliers->isNotEmpty())
                                <div class="relative col-span-2 sm:col-span-1 w-full sm:w-auto">
                                    <select name="supplier_id" class="w-full sm:w-auto text-sm border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-900 pl-3 pr-8 py-2 text-gray-700 dark:text-gray-100 focus:ring-1 focus:ring-[#5542ff] focus:border-[#5542ff] appearance-none cursor-pointer shadow-2xs transition-colors" onchange="this.form.submit()">
                                        <option value="">All Suppliers</option>
                                        @foreach($suppliers as $s)
                                            <option value="{{ $s->id }}" @selected(request('supplier_id') == $s->id)>{{ $s->name }}</option>
                                        @endforeach
                                    </select>
                                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-2.5 text-ink-muted">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                        </svg>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </form>

                @if(auth()->user()->hasPermission('Download Purchase Return PDF') || auth()->user()->hasPermission('Download Purchase Return CSV'))
                    <div class="relative inline-flex self-end sm:self-auto shrink-0">
                        <button type="button" @click="bulkOpen = !bulkOpen; openMoreId = null" class="inline-flex items-center justify-center px-4 py-2.5 sm:py-2 bg-[#5542ff] border border-[#5542ff] text-white rounded-xl sm:rounded-md hover:bg-[#4535e8] text-sm font-semibold shadow-sm">
                            Action
                            <svg class="h-4 w-4 ml-1 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        <div
                            x-show="bulkOpen"
                            @click.outside="bulkOpen = false"
                            x-cloak
                            class="absolute right-0 mt-1 w-48 bg-white dark:bg-gray-800 rounded-xl shadow-xl border border-gray-100 dark:border-gray-700 z-[200] py-1"
                        >
                            @if(auth()->user()->hasPermission('Download Purchase Return PDF'))
                                <a href="{{ route('purchases.return.export.pdf', request()->query()) }}" class="flex items-center px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700">
                                    <svg class="h-4 w-4 mr-2 text-gray-400 dark:text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>
                                    Download PDF
                                </a>
                            @endif
                            <button type="button" @click="printSelected()" class="w-full flex items-center px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 text-left">
                                <svg class="h-4 w-4 mr-2 text-gray-400 dark:text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" /></svg>
                                Print Selected
                            </button>
                            @if(auth()->user()->hasPermission('Download Purchase Return CSV'))
                                <a href="{{ route('purchases.return.export.csv', request()->query()) }}" class="flex items-center px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700">
                                    <svg class="h-4 w-4 mr-2 text-gray-400 dark:text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                                    Download CSV
                                </a>
                            @endif
                        </div>
                    </div>
                @endif
            </div>
        </div>

        @if(request()->hasAny(['search', 'date_from', 'date_to', 'supplier_id']))
            <div class="flex flex-wrap items-center gap-2 px-1 py-1">
                <span class="text-xs font-semibold text-ink-muted">Active Filters:</span>

                @if(request('search'))
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium bg-[#5542ff]/10 text-[#5542ff] dark:bg-[#5542ff]/20 dark:text-indigo-300 border border-[#5542ff]/20">
                        Search: &quot;{{ request('search') }}&quot;
                        <a href="{{ request()->fullUrlWithQuery(['search' => null]) }}" class="hover:text-red-500 font-bold ml-1" title="Remove search filter">&times;</a>
                    </span>
                @endif

                @if(request('date_from') || request('date_to'))
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium bg-[#5542ff]/10 text-[#5542ff] dark:bg-[#5542ff]/20 dark:text-indigo-300 border border-[#5542ff]/20">
                        Date: {{ request('date_from') ?? 'Start' }} &rarr; {{ request('date_to') ?? 'End' }}
                        <a href="{{ request()->fullUrlWithQuery(['date_from' => null, 'date_to' => null]) }}" class="hover:text-red-500 font-bold ml-1" title="Remove date filter">&times;</a>
                    </span>
                @endif

                @if(request('supplier_id'))
                    @php $supplierName = isset($suppliers) ? $suppliers->firstWhere('id', request('supplier_id'))?->name : null; @endphp
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium bg-[#5542ff]/10 text-[#5542ff] dark:bg-[#5542ff]/20 dark:text-indigo-300 border border-[#5542ff]/20">
                        Supplier: {{ $supplierName ?? 'ID '.request('supplier_id') }}
                        <a href="{{ request()->fullUrlWithQuery(['supplier_id' => null]) }}" class="hover:text-red-500 font-bold ml-1" title="Remove supplier filter">&times;</a>
                    </span>
                @endif

                <a href="{{ route('purchases.return') }}" class="text-xs text-red-600 dark:text-red-400 hover:underline font-semibold ml-1">
                    Reset All Filters
                </a>
            </div>
        @endif

        @if(session('success'))
            <div class="rounded-lg bg-green-50 border border-green-200 text-green-800 px-4 py-3 text-sm">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="rounded-lg bg-red-50 border border-red-200 text-red-800 px-4 py-3 text-sm">{{ session('error') }}</div>
        @endif

        @if($filterPurchaseId)
            <div class="rounded-lg border border-[#5542ff]/30 bg-[#5542ff]/5 px-4 py-2 text-sm text-[#0a1233]">
                Filtered by purchase.
                <a href="{{ route('purchases.return') }}" class="font-semibold text-[#5542ff] hover:underline ml-1">Show all returns</a>
            </div>
        @endif

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse min-w-[1100px]">
                    <thead>
                        <tr class="gradient-bg text-white">
                            <th class="checkbox-column px-4 py-3.5 text-[11px] font-bold uppercase tracking-wide w-10">
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="checkbox" x-model="selectAll" @change="toggleSelectAll" class="rounded border-gray-300 text-[#5542ff] focus:ring-[#5542ff]">
                                    <span class="sr-only">Select All</span>
                                </label>
                            </th>
                            <th class="px-4 py-3.5 text-[11px] font-bold uppercase tracking-wide">Return Invoice | Date</th>
                            <th class="px-4 py-3.5 text-[11px] font-bold uppercase tracking-wide">From Purchase</th>
                            <th class="px-4 py-3.5 text-[11px] font-bold uppercase tracking-wide">Supplier | Mobile</th>
                            <th class="px-4 py-3.5 text-[11px] font-bold uppercase tracking-wide">Total Amount</th>
                            <th class="px-4 py-3.5 text-[11px] font-bold uppercase tracking-wide">Lessed | Receivable</th>
                            <th class="px-4 py-3.5 text-[11px] font-bold uppercase tracking-wide">Received | Due</th>
                            <th class="px-4 py-3.5 text-[11px] font-bold uppercase tracking-wide text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse($returns as $r)
                            @php
                                $phone = $r->purchase->supplier->phone;
                                $phoneDisp = $phone ? (\Illuminate\Support\Str::startsWith($phone, '+') ? $phone : "+{$phone}") : '—';

                                // Calculate progressive ledger offsets across all returns for this purchase
                                $remainingUnpaid = max(0, (float)$r->purchase->payable_amount - (float)$r->purchase->paid_amount);
                                $effectiveDue = 0;

                                foreach ($r->purchase->purchaseReturns as $pr) {
                                    $receivable = (float)$pr->receivable_amount;
                                    $offset = min($receivable, $remainingUnpaid);
                                    $cashOwed = $receivable - $offset;
                                    $remainingUnpaid -= $offset;

                                    if ($pr->id === $r->id) {
                                        $effectiveDue = max(0, $cashOwed - (float)$pr->received_amount);
                                        break;
                                    }
                                }
                            @endphp
                            <tr class="hover:bg-gray-50/90 transition-colors bg-white cursor-pointer" onclick="if(event.target.closest('.checkbox-column, .text-right, .status-actions')) return; window.location.href='{{ route('purchases.return.show', $r) }}'">
                                <td class="checkbox-column px-4 py-3.5 align-middle">
                                    <input type="checkbox" value="{{ $r->id }}" x-model="selectedItems" class="row-checkbox rounded border-gray-300 text-[#5542ff] focus:ring-[#5542ff]">
                                </td>
                                <td class="px-4 py-3.5 align-top">
                                    <div class="flex items-start gap-1.5">
                                        @if($effectiveDue > 0.009)
                                            <span class="inline-block w-2 h-2 rounded-full bg-red-500 mt-1.5 shrink-0" title="Cash receivable due"></span>
                                        @endif
                                        <div>
                                            <div class="text-sm font-bold text-[#2563eb]">{{ $r->return_invoice_no }}</div>
                                            <div class="text-xs text-gray-500 mt-0.5">{{ $r->return_date->format('d M, Y') }}</div>
                                        </div>
                                    </div>
                                </td>
                                {{-- From Purchase column --}}
                                <td class="px-4 py-3.5 align-top">
                                    <a href="{{ route('purchases.receipt', $r->purchase) }}" class="inline-flex items-center gap-1 text-sm font-bold text-[#5542ff] hover:text-[#4535e8] hover:underline transition-colors" onclick="event.stopPropagation()" title="View purchase {{ $r->purchase->invoice_no }}">
                                        <svg class="h-3.5 w-3.5 shrink-0 opacity-70" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                                        {{ $r->purchase->invoice_no }}
                                    </a>
                                    <div class="text-[10px] text-gray-400 mt-0.5">{{ $r->purchase->purchase_date?->format('d M, Y') }}</div>
                                </td>
                                <td class="px-4 py-3.5 align-top">
                                    <div class="text-sm font-semibold text-gray-900">{{ $r->purchase->supplier->name }}</div>
                                    <div class="text-xs text-gray-500 mt-0.5">{{ $phoneDisp }}</div>
                                </td>
                                <td class="px-4 py-3.5 align-top">
                                    <div class="text-sm font-bold text-gray-900">{{ formatCurrency($r->subtotal) }}</div>
                                </td>
                                <td class="px-4 py-3.5 align-top">
                                    <div class="text-sm text-gray-500">{{ formatCurrency($r->discount) }}</div>
                                    <div class="text-sm font-bold text-gray-900 mt-0.5">{{ formatCurrency($r->receivable_amount) }}</div>
                                </td>
                                <td class="px-4 py-3.5 align-top">
                                    <div class="text-sm text-gray-500">{{ formatCurrency($r->received_amount) }}</div>
                                    @if($effectiveDue > 0.009)
                                        <div class="text-sm font-bold text-red-600 mt-0.5">{{ formatCurrency($effectiveDue) }}</div>
                                        <div class="mt-1 text-[10px] text-orange-600 font-medium">Cash receivable owed</div>
                                    @else
                                        <div class="text-sm font-bold text-green-600 mt-0.5">{{ formatCurrency(0) }}</div>
                                        <div class="mt-1 text-[10px] text-gray-400">Offset against balance</div>
                                    @endif
                                </td>
                                <td class="px-4 py-3.5 align-top text-right">
                                    <div class="flex items-center justify-end gap-2 flex-nowrap whitespace-nowrap">
                                        @if(auth()->user()->hasPermission('Edit Purchase Return'))
                                            <a href="{{ route('purchases.return.edit', $r) }}" class="inline-flex items-center px-2.5 py-1.5 bg-white border border-[#5542ff] text-[#5542ff] rounded-md hover:bg-[#5542ff]/5 text-[11px] font-bold">
                                                <svg class="h-3.5 w-3.5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                </svg>
                                                Edit
                                            </a>
                                        @endif

                                        <div class="inline-flex">
                                            <button
                                                type="button"
                                                @click.stop="toggleMore({{ $r->id }}, $event)"
                                                class="inline-flex items-center px-2.5 py-1.5 bg-white border border-sky-400 text-sky-600 rounded-md hover:bg-sky-50 text-[11px] font-bold"
                                            >
                                                <svg class="h-3.5 w-3.5 mr-1 text-sky-600" fill="currentColor" viewBox="0 0 24 24"><path d="M12 8c1.1 0 2-.9 2-2s-.9-2-2-2-2 .9-2 2 .9 2 2 2zm0 2c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm0 6c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2z"/></svg>
                                                More
                                                <svg class="h-3 w-3 ml-0.5 text-sky-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                                            </button>
                                            <template x-teleport="body">
                                                <div
                                                    x-show="openMoreId === {{ $r->id }}"
                                                    @click.outside="openMoreId = null"
                                                    x-cloak
                                                    class="fixed w-56 bg-white rounded-lg shadow-xl border border-gray-100 py-1 text-left z-[200]"
                                                    :style="{ top: dropdownTop, right: dropdownRight }"
                                                >
                                                    @if(auth()->user()->hasPermission('Store Supplier Payment Receive') && $effectiveDue > 0.009)
                                                        <button
                                                            type="button"
                                                            @click="openPayment({ id: {{ $r->id }}, invoice: @js($r->return_invoice_no), supplier: @js($r->purchase->supplier->name), receivable: @js(number_format((float)$r->receivable_amount, 2, '.', '')), due: @js(number_format($effectiveDue, 2, '.', '')) })"
                                                            class="w-full flex items-center px-3 py-2 text-sm text-gray-700 hover:bg-gray-50"
                                                        >
                                                            <svg class="h-4 w-4 mr-2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                                                            Receive Payment
                                                        </button>
                                                    @endif
                                                    @if(auth()->user()->hasPermission('Download Purchase Return Invoice PDF'))
                                                        <a href="{{ route('purchases.return.invoice.pdf', $r) }}?print=true" target="_blank" @click="openMoreId = null" class="flex items-center px-3 py-2 text-sm text-gray-700 hover:bg-gray-50">
                                                            <svg class="h-4 w-4 mr-2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" /></svg>
                                                            Print
                                                        </a>
                                                        <a href="{{ route('purchases.return.invoice.pdf', $r) }}" @click="openMoreId = null" class="flex items-center px-3 py-2 text-sm text-gray-700 hover:bg-gray-50">
                                                            <svg class="h-4 w-4 mr-2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>
                                                            Download Details
                                                        </a>
                                                    @endif
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-12 text-center text-gray-500">No purchase returns found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($returns->hasPages())
                <div class="px-4 py-3 bg-gray-50 border-t border-gray-100">
                    {{ $returns->links() }}
                </div>
            @endif
        </div>

        <div x-show="paymentOpen" x-cloak class="fixed inset-0 z-[210] overflow-y-auto" style="display: none;">
            <div class="flex items-center justify-center min-h-screen px-4">
                <div class="fixed inset-0 bg-gray-900/50 z-0" @click="paymentOpen = false"></div>
                <div class="relative bg-white rounded-xl shadow-xl max-w-md w-full p-6 z-10" @click.stop>
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-bold text-[#0a1233]">Receive Payment</h3>
                        <button type="button" @click="paymentOpen = false" class="text-gray-400 hover:text-gray-600">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                        </button>
                    </div>
                    <div x-show="paymentReturn" x-cloak>
                        <form method="POST" class="space-y-4" :action="paymentReturn ? `/purchases/return/${paymentReturn.id}/payment` : '#'">
                            @csrf
                            <div>
                                <label class="block text-xs font-semibold text-gray-500 mb-1">Invoice No.</label>
                                <input type="text" :value="paymentReturn?.invoice || ''" disabled class="w-full rounded-lg border border-gray-200 bg-gray-100 px-3 py-2 text-sm text-gray-700" />
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-500 mb-1">Supplier</label>
                                <input type="text" :value="paymentReturn?.supplier || ''" disabled class="w-full rounded-lg border border-gray-200 bg-gray-100 px-3 py-2 text-sm text-gray-700" />
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-500 mb-1">Receivable amount</label>
                                <div class="flex rounded-lg border border-gray-200 overflow-hidden">
                                    <span class="px-3 py-2 bg-gray-100 text-gray-500 text-sm">{{ currencySymbol() }}</span>
                                    <input type="text" :value="paymentReturn?.receivable || ''" disabled class="flex-1 px-3 py-2 bg-gray-100 text-sm text-gray-700" />
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-500 mb-1">Payment Method <span class="text-red-500">*</span></label>
                                <select name="payment_method" x-model="paymentMethod" required class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-[#5542ff]">
                                    <option value="cash">Cash</option>
                                    <option value="check">Check</option>
                                </select>
                            </div>
                            <div x-show="paymentMethod === 'check'" x-cloak>
                                <div class="mb-4">
                                    <label class="block text-xs font-semibold text-gray-500 mb-1">Check Number <span class="text-red-500">*</span></label>
                                    <input type="text" name="check_number" x-model="checkNumber" :required="paymentMethod === 'check'" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-[#5542ff]" placeholder="Enter check number" />
                                    @error('check_number')
                                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-gray-500 mb-1">Check Date <span class="text-red-500">*</span></label>
                                    <input type="date" name="check_date" x-model="checkDate" :required="paymentMethod === 'check'" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-[#5542ff]" />
                                    @error('check_date')
                                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-500 mb-1">Receiving amount <span class="text-red-500">*</span></label>
                                <div class="flex rounded-lg overflow-hidden" :class="isReceivingExceeded ? 'border border-red-500' : 'border border-gray-200'">
                                    <span class="px-3 py-2 bg-gray-100 text-gray-500 text-sm">{{ currencySymbol() }}</span>
                                    <input type="number" name="receiving_amount" step="0.01" min="0.01" required 
                                        x-model="receivingAmount"
                                        :max="paymentReturn ? paymentReturn.due : null"
                                        class="flex-1 px-3 py-2 text-sm focus:outline-none payment-receiving-input" 
                                        :class="isReceivingExceeded ? 'text-red-600 focus:ring-1 focus:ring-red-500' : 'focus:ring-1 focus:ring-[#5542ff]'"
                                        placeholder="0.00" />
                                </div>
                                <p x-show="isReceivingExceeded" x-cloak class="text-red-500 text-xs mt-1.5 font-semibold flex items-center gap-1">
                                    <svg class="h-3.5 w-3.5 text-red-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    <span>Receiving amount cannot exceed the due amount ({{ currencySymbol() }}<span x-text="paymentReturn ? Number(paymentReturn.due).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}) : '0.00'"></span>).</span>
                                </p>
                                @error('receiving_amount')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <button type="submit" 
                                :disabled="isReceivingExceeded"
                                class="w-full py-3 rounded-md bg-[#5542ff] text-white font-semibold transition-colors disabled:opacity-50 disabled:cursor-not-allowed" 
                                :class="isReceivingExceeded ? 'opacity-50 cursor-not-allowed' : 'hover:bg-[#4736d6]'">
                                Submit
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Print Warning Modal -->
        <div x-show="showPrintMessage" x-cloak class="fixed inset-0 z-[500] flex items-center justify-center">
            <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" @click="showPrintMessage = false"></div>
            <div class="relative bg-white rounded-xl shadow-xl max-w-sm w-full mx-4 p-6 z-10 text-center">
                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-yellow-100 mb-4">
                    <svg class="h-6 w-6 text-yellow-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                </div>
                <h3 class="text-lg font-bold text-gray-900 mb-2">Please select records</h3>
                <p class="text-sm text-gray-500 mb-6">Please select at least one record to print.</p>
                <button @click="showPrintMessage = false" class="w-full bg-[#5542ff] hover:bg-[#4736d6] text-white font-bold py-2 px-4 rounded-lg transition-colors">OK</button>
            </div>
        </div>
    </div>
</x-app-layout>
