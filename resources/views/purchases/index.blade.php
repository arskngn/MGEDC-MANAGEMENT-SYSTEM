<x-app-layout>
    <script>
        function showPurchaseModal(title, subtitle, options, onClose) {
            var existing = document.getElementById('_purchase_action_modal');
            if (existing) existing.remove();

            var overlay = document.createElement('div');
            overlay.id = '_purchase_action_modal';
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
        @purchase:created.window="handleRealtimePurchase($event.detail)"
        x-data="{
            selectedItems: [],
            selectAll: false,
            showPrintMessage: false,
            livePurchases: [],
            formatMoney(amount) {
                return '₱' + (parseFloat(amount) || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            },
            handleRealtimePurchase(purchase) {
                if (this.livePurchases.some(p => p.id === purchase.id)) return;
                this.livePurchases.unshift(purchase);

                const origTitle = document.title;
                document.title = '🔔 New Purchase: ' + purchase.invoice_no;
                setTimeout(() => { document.title = origTitle; }, 4000);
            },
            toggleSelectAll() {
                if (this.selectAll) {
                    this.selectedItems = Array.from(document.querySelectorAll('.row-checkbox')).map(cb => cb.value);
                } else {
                    this.selectedItems = [];
                }
            },
            printSelected() {
                if (this.selectedItems.length === 0) {
                    showPurchaseModal('No Records Selected', 'Please select at least one purchase to print.', [], null);
                    return;
                }
                const ids = this.selectedItems.join(',');
                this.bulkOpen = false;
                showPurchaseModal('Print Options', 'How would you like to print the selected purchases?', [
                    { label: 'Batch Print', desc: 'Print each purchase invoice on its own page.', handler: function() { window.open('/purchases/export/pdf?ids=' + ids + '&print=true&type=batch', '_blank'); } },
                    { label: 'Total Print', desc: 'Print all selected purchases combined in a single consolidated DR.', handler: function() { window.open('/purchases/export/pdf?ids=' + ids + '&print=true&type=total', '_blank'); } },
                ], null);
            },
            downloadSelected() {
                if (this.selectedItems.length === 0) {
                    showPurchaseModal('No Records Selected', 'Please select at least one purchase to download.', [], null);
                    return;
                }
                const ids = this.selectedItems.join(',');
                this.bulkOpen = false;
                showPurchaseModal('Download Options', 'How would you like to download the selected purchases?', [
                    { label: 'Batch Download', desc: 'Download each purchase invoice PDF separately.', handler: function() { window.location.href = '/purchases/export/pdf?ids=' + ids + '&type=batch'; } },
                    { label: 'Total Download', desc: 'Download a single consolidated PDF for all selected purchases.', handler: function() { window.location.href = '/purchases/export/pdf?ids=' + ids + '&type=total'; } },
                ], null);
            },
            openMoreId: null,
            dropdownTop: '0px',
            dropdownRight: '0px',
            bulkOpen: false,
            paymentOpen: false,
            paymentPurchase: null,
            paymentMethod: 'cash',
            checkNumber: '',
            checkDate: '',
            payingAmount: '',
            get isPayingExceeded() {
                if (!this.paymentPurchase || !this.payingAmount) return false;
                const amt = parseFloat(this.payingAmount) || 0;
                const due = parseFloat(this.paymentPurchase.due) || 0;
                return amt > due + 0.0001;
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
            openPayment(p) {
                this.paymentPurchase = p;
                this.paymentOpen = true;
                this.paymentMethod = 'cash';
                this.checkNumber = '';
                this.checkDate = '';
                this.payingAmount = '';
                this.openMoreId = null;
                this.$nextTick(() => {
                    const inp = this.$el.querySelector('.payment-paying-input');
                    if (inp) { inp.max = p.due; }
                });
            },
            confirmClone() {
                if (this.cloneFormId) {
                    document.getElementById(this.cloneFormId).submit();
                }
            },
        }"
        @keydown.escape.window="paymentOpen = false; openMoreId = null; bulkOpen = false; cloneOpen = false"
        @keydown.window.ctrl.p.prevent="printSelected()"
        @keydown.window.ctrl.a.prevent="selectAll = true; toggleSelectAll()"
        @scroll.window="if (openMoreId) openMoreId = null"
        class="space-y-4"
    >
        <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
            <h2 class="text-xl font-bold text-[#0a1233] dark:text-gray-100 shrink-0">All Purchases</h2>

            <div class="flex flex-col sm:flex-row sm:items-center gap-2.5 w-full sm:w-auto">
            <form
                method="GET"
                action="{{ route('purchases.all') }}"
                x-data="{
                    ...purchaseDateRange({ dateFrom: @js(request('date_from')), dateTo: @js(request('date_to')) }),
                    mobileFiltersOpen: false
                }"
                x-ref="filterForm"
                @keydown.escape.window="escapeClose()"
                class="w-full sm:flex sm:flex-row sm:items-center sm:gap-2 sm:w-auto"
            >
                <input type="hidden" name="date_from" x-model="dateFrom" />
                <input type="hidden" name="date_to" x-model="dateTo" />

                {{-- Mobile / tablet toolbar row --}}
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
                        $activeFilters = (int)(bool)request('date_from') + (int)(bool)request('payment_status') + (int)(bool)request('status') + (int)(bool)request('supplier_id');
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

                {{-- Filter dropdowns: always visible on desktop, toggle on mobile --}}
                <div x-show="mobileFiltersOpen" x-cloak class="sm:!block sm:flex sm:flex-row sm:items-center sm:gap-2" style="display:none">
                    <div class="grid grid-cols-2 gap-2 mt-2 sm:mt-0 sm:flex sm:flex-row sm:gap-2">
                        <div class="col-span-2 sm:col-span-1 sm:w-auto">
                            @include('components.date-range-picker')
                        </div>

                        {{-- Payment Status Filter --}}
                        <div class="relative w-full sm:w-auto">
                            <select name="payment_status" class="w-full sm:w-auto text-sm border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-900 pl-3 pr-8 py-2 text-gray-700 dark:text-gray-100 focus:ring-1 focus:ring-[#5542ff] focus:border-[#5542ff] appearance-none cursor-pointer shadow-2xs transition-colors" onchange="this.form.submit()">
                                <option value="">All Payment</option>
                                <option value="paid" @selected(request('payment_status') === 'paid')>Paid</option>
                                <option value="partial" @selected(request('payment_status') === 'partial')>Partial</option>
                                <option value="due" @selected(request('payment_status') === 'due')>Due</option>
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-2.5 text-ink-muted">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </div>
                        </div>

                        {{-- Purchase Order Status Filter --}}
                        <div class="relative w-full sm:w-auto">
                            <select name="status" class="w-full sm:w-auto text-sm border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-900 pl-3 pr-8 py-2 text-gray-700 dark:text-gray-100 focus:ring-1 focus:ring-[#5542ff] focus:border-[#5542ff] appearance-none cursor-pointer shadow-2xs transition-colors" onchange="this.form.submit()">
                                <option value="" @selected(!request('status'))>All Status</option>
                                <option value="ordered" @selected(request('status') === 'ordered')>Ordered</option>
                                <option value="received" @selected(request('status') === 'received')>Received</option>
                                <option value="completed" @selected(request('status') === 'completed')>Completed</option>
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-2.5 text-ink-muted">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </div>
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

            <div class="flex flex-wrap items-center gap-2 shrink-0">
                @if(auth()->user()->hasPermission('New Purchase'))
                    <a href="{{ route('purchases.create') }}" class="inline-flex items-center px-4 py-2 bg-white border border-[#5542ff] text-[#5542ff] rounded-md hover:bg-[#5542ff]/5 transition-colors text-sm font-semibold">
                        <svg class="h-4 w-4 mr-1.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Add New
                    </a>
                @endif

                @if(auth()->user()->hasPermission('Download Purchase PDF') || auth()->user()->hasPermission('Download Purchase CSV'))
                    <div class="relative">
                        <button type="button" @click="bulkOpen = !bulkOpen; openMoreId = null" class="inline-flex items-center px-4 py-2 bg-[#5542ff] border border-[#5542ff] text-white rounded-md hover:bg-[#4535e8] text-sm font-semibold shadow-sm">
                            Action
                            <svg class="h-4 w-4 ml-1 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        <div
                            x-show="bulkOpen"
                            @click.outside="bulkOpen = false"
                            x-cloak
                            class="absolute right-0 mt-1 w-48 bg-white dark:bg-gray-800 rounded-lg shadow-lg border border-gray-100 dark:border-gray-700 z-[200] py-1"
                        >
                            @if(auth()->user()->hasPermission('Download Purchase PDF'))
                                <button type="button" @click="downloadSelected()" class="w-full flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 text-left">
                                    <svg class="h-4 w-4 mr-2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>
                                    Download PDF (Selected)
                                </button>
                                <a href="{{ route('purchases.export.pdf', request()->query()) }}" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                                    <svg class="h-4 w-4 mr-2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>
                                    Download PDF (All)
                                </a>
                            @endif
                            <button type="button" @click="printSelected()" class="w-full flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 text-left">
                                <svg class="h-4 w-4 mr-2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" /></svg>
                                Print Selected
                            </button>
                            @if(auth()->user()->hasPermission('Download Purchase CSV'))
                                <a href="{{ route('purchases.export.csv', request()->query()) }}" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                                    <svg class="h-4 w-4 mr-2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>
                                    Download CSV
                                </a>
                            @endif
                        </div>
                    </div>
                @endif
            </div>
            </div>
        </div>

        @if(request()->hasAny(['search', 'date_from', 'date_to', 'payment_status', 'status', 'supplier_id']))
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

                @if(request('payment_status'))
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium bg-[#5542ff]/10 text-[#5542ff] dark:bg-[#5542ff]/20 dark:text-indigo-300 border border-[#5542ff]/20">
                        Payment: {{ ucfirst(request('payment_status')) }}
                        <a href="{{ request()->fullUrlWithQuery(['payment_status' => null]) }}" class="hover:text-red-500 font-bold ml-1" title="Remove payment status filter">&times;</a>
                    </span>
                @endif

                @if(request('status'))
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium bg-[#5542ff]/10 text-[#5542ff] dark:bg-[#5542ff]/20 dark:text-indigo-300 border border-[#5542ff]/20">
                        Status: {{ ucfirst(request('status')) }}
                        <a href="{{ request()->fullUrlWithQuery(['status' => null]) }}" class="hover:text-red-500 font-bold ml-1" title="Remove status filter">&times;</a>
                    </span>
                @endif

                @if(request('supplier_id'))
                    @php $supplierName = isset($suppliers) ? $suppliers->firstWhere('id', request('supplier_id'))?->name : null; @endphp
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium bg-[#5542ff]/10 text-[#5542ff] dark:bg-[#5542ff]/20 dark:text-indigo-300 border border-[#5542ff]/20">
                        Supplier: {{ $supplierName ?? 'ID '.request('supplier_id') }}
                        <a href="{{ request()->fullUrlWithQuery(['supplier_id' => null]) }}" class="hover:text-red-500 font-bold ml-1" title="Remove supplier filter">&times;</a>
                    </span>
                @endif

                <a href="{{ route('purchases.all') }}" class="text-xs text-red-600 dark:text-red-400 hover:underline font-semibold ml-1">
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

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
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
                            <th class="px-4 py-3.5 text-[11px] font-bold uppercase tracking-wide">Invoice No. | Date</th>
                            <th class="px-4 py-3.5 text-[11px] font-bold uppercase tracking-wide">Supplier | Mobile</th>
                            <th class="px-4 py-3.5 text-[11px] font-bold uppercase tracking-wide">Total Amount</th>
                            <th class="px-4 py-3.5 text-[11px] font-bold uppercase tracking-wide">Discount | Original Payable</th>
                            <th class="px-4 py-3.5 text-[11px] font-bold uppercase tracking-wide">Return Credit | Net Payable</th>
                            <th class="px-4 py-3.5 text-[11px] font-bold uppercase tracking-wide">Paid | Due</th>
                            <th class="px-4 py-3.5 text-[11px] font-bold uppercase tracking-wide">Status</th>
                            <th class="px-4 py-3.5 text-[11px] font-bold uppercase tracking-wide text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        {{-- Real-time Newly Added Purchases (Desktop) --}}
                        <template x-for="livePurchase in livePurchases" :key="'live-pur-' + livePurchase.id">
                            <tr class="bg-indigo-50/70 dark:bg-indigo-950/40 hover:bg-indigo-100/70 dark:hover:bg-indigo-900/50 transition-all duration-500 cursor-pointer animate-pulse-once"
                                @click="if($event.target.closest('.checkbox-column, .text-right')) return; window.location.href = livePurchase.receipt_url">
                                <td class="checkbox-column px-4 py-3.5 align-middle">
                                    <input type="checkbox" :value="livePurchase.id" x-model="selectedItems" class="row-checkbox rounded border-gray-300 text-[#5542ff] focus:ring-[#5542ff]">
                                </td>
                                <td class="px-4 py-3.5 align-top">
                                    <div class="flex items-start gap-1.5">
                                        <div>
                                            <div class="flex items-center gap-1.5 flex-wrap">
                                                <span class="text-sm font-bold text-gray-800 dark:text-gray-100" x-text="livePurchase.invoice_no"></span>
                                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold bg-[#5542ff] text-white">NEW</span>
                                                <template x-if="livePurchase.due_amount <= 0.009">
                                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold bg-green-50 dark:bg-green-900/30 text-green-700 dark:text-green-300 border border-green-200 dark:border-green-800">Paid</span>
                                                </template>
                                                <template x-if="livePurchase.paid_amount > 0.009 && livePurchase.due_amount > 0.009">
                                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold bg-amber-50 dark:bg-amber-900/30 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-800">Partial</span>
                                                </template>
                                                <template x-if="livePurchase.paid_amount <= 0.009 && livePurchase.due_amount > 0.009">
                                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold bg-red-50 dark:bg-red-900/30 text-red-700 dark:text-red-300 border border-red-200 dark:border-red-800">Due</span>
                                                </template>
                                            </div>
                                            <div class="text-xs text-ink-muted mt-0.5" x-text="livePurchase.purchase_date"></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3.5 align-top">
                                    <div class="text-sm font-semibold text-[#5542ff]" x-text="livePurchase.supplier_name"></div>
                                    <div class="text-xs text-ink-muted mt-0.5" x-text="livePurchase.supplier_phone || '—'"></div>
                                </td>
                                <td class="px-4 py-3.5 align-top">
                                    <div class="text-sm font-bold text-ink-heading" x-text="formatMoney(livePurchase.subtotal)"></div>
                                </td>
                                <td class="px-4 py-3.5 align-top">
                                    <div class="text-xs text-ink-muted" x-text="formatMoney(livePurchase.discount)"></div>
                                    <div class="text-sm font-bold text-ink-heading mt-1" x-text="formatMoney(livePurchase.payable_amount)"></div>
                                </td>
                                <td class="px-4 py-3.5 align-top">
                                    <div class="text-xs text-ink-muted">—</div>
                                    <div class="text-sm font-bold text-ink-heading mt-1" x-text="formatMoney(livePurchase.payable_amount)"></div>
                                </td>
                                <td class="px-4 py-3.5 align-top">
                                    <div class="text-sm font-bold text-ink-heading" x-text="formatMoney(livePurchase.paid_amount)"></div>
                                    <div class="text-xs mt-0.5" :class="livePurchase.due_amount <= 0.009 ? 'text-green-600 dark:text-green-400' : 'text-ink-muted'" x-text="formatMoney(livePurchase.due_amount)"></div>
                                </td>
                                <td class="px-4 py-3.5 align-top">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-bold border bg-orange-100 text-orange-700 border-orange-200 capitalize" x-text="livePurchase.status"></span>
                                </td>
                                <td class="px-4 py-3.5 align-top text-right">
                                    <div class="flex items-center justify-end gap-2 flex-nowrap whitespace-nowrap">
                                        <a :href="livePurchase.receipt_url" class="inline-flex items-center px-2.5 py-1.5 bg-white dark:bg-gray-800 border border-[#5542ff] dark:border-gray-600 text-[#5542ff] dark:text-gray-100 rounded-md hover:bg-[#5542ff]/5 text-[11px] font-bold">
                                            Receive
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        </template>

                        @forelse($purchases as $purchase)
                            @php
                                $phone = $purchase->supplier->phone;
                                $phoneDisp = $phone ? (\Illuminate\Support\Str::startsWith($phone, '+') ? $phone : "+{$phone}") : '—';
                                $canReturnNav = auth()->user()->hasPermission('All Purchase Return') || auth()->user()->hasPermission('Item Of Purchase Return');
                                $canCreateReturn = auth()->user()->hasPermission('Store Purchase Return')
                                    && in_array($purchase->status, ['partial', 'received', 'completed'], true);
                                $hasReturn = $purchase->purchase_returns_count > 0;
                                $totalReturnReceivable = (float) ($purchase->purchase_returns_sum_receivable_amount ?? 0);
                                $netPayable = max(0, (float) $purchase->payable_amount - $totalReturnReceivable);
                                $netDue = max(0, $netPayable - (float) $purchase->paid_amount);
                            @endphp
                            <tr class="hover:bg-gray-50/90 transition-colors bg-white cursor-pointer" onclick="if(event.target.closest('.checkbox-column, .text-right')) return; window.location.href='{{ route('purchases.receipt', $purchase) }}'">
                                <td class="checkbox-column px-4 py-3.5 align-middle">
                                    <input type="checkbox" value="{{ $purchase->id }}" x-model="selectedItems" class="row-checkbox rounded border-gray-300 text-[#5542ff] focus:ring-[#5542ff]">
                                </td>
                                <td class="px-4 py-3.5 align-top">
                                    <div class="flex items-start gap-1.5">
                                        @if($hasReturn)
                                            <span class="inline-block w-2 h-2 rounded-full bg-red-500 mt-1.5 shrink-0 cursor-pointer" title="Has return(s)"></span>
                                        @elseif($netDue > 0.009)
                                            <span class="inline-block w-2 h-2 rounded-full bg-red-500 mt-1.5 shrink-0" title="Balance due"></span>
                                        @endif
                                        <div>
                                            <div class="flex items-center gap-1.5 flex-wrap">
                                                <span class="text-sm font-bold text-gray-800">{{ $purchase->invoice_no }}</span>
                                                @if($netDue <= 0.009)
                                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold bg-green-50 text-green-700 border border-green-200">Paid</span>
                                                @elseif($purchase->paid_amount > 0.009)
                                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">Partial</span>
                                                @else
                                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold bg-red-50 text-red-700 border border-red-200">Due</span>
                                                @endif
                                            </div>
                                            <div class="text-xs text-gray-500 mt-0.5">{{ $purchase->purchase_date->format('d M, Y') }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3.5 align-top">
                                    <div class="text-sm font-semibold text-[#5542ff]">{{ $purchase->supplier->name }}</div>
                                    <div class="text-xs text-ink-muted mt-0.5">{{ $phoneDisp }}</div>
                                </td>
                                <td class="px-4 py-3.5 align-top">
                                    <div class="text-sm font-bold text-gray-900">{{ formatCurrency($purchase->subtotal) }}</div>

                                </td>
                                <td class="px-4 py-3.5 align-top">
                                    <div class="text-xs text-gray-500">{{ formatCurrency($purchase->discount) }}</div>
                                    <div class="text-sm font-bold text-gray-900 mt-1">{{ formatCurrency($purchase->payable_amount) }}</div>
                                </td>
                                <td class="px-4 py-3.5 align-top">
                                    <div class="text-xs text-gray-500">{{ $totalReturnReceivable > 0 ? '-'.formatCurrency($totalReturnReceivable) : '—' }}</div>
                                    <div class="text-sm font-bold text-gray-900 mt-1">{{ formatCurrency($netPayable) }}</div>
                                </td>
                                <td class="px-4 py-3.5 align-top">
                                    <div class="text-sm font-bold text-gray-900">{{ formatCurrency($purchase->paid_amount) }}</div>
                                    <div class="text-xs mt-0.5 {{ $netDue <= 0.009 ? 'text-green-600' : 'text-gray-500' }}">
                                        {{ formatCurrency($netDue) }}
                                    </div>
                                </td>
                                @php
                                    $statusConfig = [
                                        'ordered'   => ['label' => 'Ordered',   'classes' => 'bg-orange-100 text-orange-700 border-orange-200'],
                                        'received'  => ['label' => 'Received',  'classes' => 'bg-green-100 text-green-700 border-green-200'],
                                        'completed' => ['label' => 'Completed', 'classes' => 'bg-purple-100 text-purple-700 border-purple-200'],
                                    ];
                                    $sc = $statusConfig[$purchase->status] ?? ['label' => ucfirst($purchase->status), 'classes' => 'bg-gray-100 text-gray-500 border-gray-200'];
                                @endphp
                                <td class="px-4 py-3.5 align-top">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-bold border {{ $sc['classes'] }}">
                                        {{ $sc['label'] }}
                                    </span>
                                </td>
                                <td class="px-4 py-3.5 align-top text-right">
                                    <div class="flex items-center justify-end gap-2 flex-nowrap whitespace-nowrap">

                                        @if(auth()->user()->hasPermission('Edit Purchase') && $purchase->isOrderEditable())
                                            <a href="{{ route('purchases.edit', $purchase) }}" class="inline-flex items-center px-2.5 py-1.5 bg-white border border-[#5542ff] text-[#5542ff] rounded-md hover:bg-[#5542ff]/5 text-[11px] font-bold">
                                                <svg class="h-3.5 w-3.5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                                Edit
                                            </a>
                                        @endif


                                        <div class="inline-flex">
                                            <button
                                                type="button"
                                                @click.stop="toggleMore({{ $purchase->id }}, $event)"
                                                class="inline-flex items-center px-2.5 py-1.5 bg-white border border-[#5542ff] text-[#5542ff] rounded-md hover:bg-[#5542ff]/5 text-[11px] font-bold"
                                            >
                                                <svg class="h-3.5 w-3.5 mr-1 text-[#5542ff]" fill="currentColor" viewBox="0 0 24 24"><path d="M12 8c1.1 0 2-.9 2-2s-.9-2-2-2-2 .9-2 2 .9 2 2 2zm0 2c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm0 6c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2z"/></svg>
                                                More
                                                <svg class="h-3 w-3 ml-0.5 text-[#5542ff]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                                            </button>
                                            <template x-teleport="body">
                                                <div
                                                    x-show="openMoreId === {{ $purchase->id }}"
                                                    @click.outside="openMoreId = null"
                                                    x-cloak
                                                    class="fixed w-52 bg-white dark:bg-gray-800 rounded-lg shadow-xl border border-gray-100 dark:border-gray-700 py-1 text-left z-[200]"
                                                    :style="{ top: dropdownTop, right: dropdownRight }"
                                                >
                                                    @if(auth()->user()->hasPermission('All Purchases'))
                                                        <a href="{{ route('purchases.receipt', $purchase) }}" @click="openMoreId = null" class="flex items-center px-3 py-2 text-sm text-gray-700 hover:bg-gray-50">
                                                            <svg class="h-4 w-4 mr-2 text-[#48cf82]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                                                            Receive
                                                        </a>
                                                        <a href="{{ route('purchases.invoice.pdf', $purchase) }}?print=true" target="_blank" @click="openMoreId = null" class="flex items-center px-3 py-2 text-sm text-gray-700 hover:bg-gray-50">
                                                            <svg class="h-4 w-4 mr-2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" /></svg>
                                                            Print
                                                        </a>
                                                    @endif
                                                    @if(auth()->user()->hasPermission('Store Supplier Payment') && $netDue > 0.009)
                                                        <button
                                                            type="button"
                                                            @click="openPayment({ id: {{ $purchase->id }}, invoice: @js($purchase->invoice_no), supplier: @js($purchase->supplier->name), payable: @js(number_format($netPayable, 2, '.', '')), due: @js(number_format($netDue, 2, '.', '')) })"
                                                            class="w-full flex items-center px-3 py-2 text-sm text-gray-700 hover:bg-gray-50"
                                                        >
                                                            <svg class="h-4 w-4 mr-2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                                                            Give payment
                                                        </button>
                                                    @endif
                                                    @if(auth()->user()->hasPermission('Download Purchase Invoice PDF'))
                                                        <a href="{{ route('purchases.invoice.pdf', $purchase) }}" class="flex items-center px-3 py-2 text-sm text-gray-700 hover:bg-gray-50">
                                                            <svg class="h-4 w-4 mr-2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>
                                                            Download details
                                                        </a>
                                                    @endif
                                                    @if($canCreateReturn)
                                                        <a href="{{ route('purchases.return.create', $purchase) }}" class="flex items-center px-3 py-2 text-sm text-gray-700 hover:bg-gray-50">
                                                            <svg class="h-4 w-4 mr-2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6" /></svg>
                                                            Create return
                                                        </a>
                                                    @endif
                                                    @if($canReturnNav && $purchase->purchase_returns_count > 0)
                                                        <a href="{{ route('purchases.return', ['purchase_id' => $purchase->id]) }}" class="flex items-center px-3 py-2 text-sm text-gray-700 hover:bg-gray-50">
                                                            <svg class="h-4 w-4 mr-2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6" /></svg>
                                                            View return details
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
                                <td colspan="8" class="px-6 py-12 text-center text-gray-500">No purchases found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($purchases->hasPages())
                <div class="px-4 py-3 bg-gray-50 dark:bg-gray-900 border-t border-gray-100 dark:border-gray-700">
                    {{ $purchases->links() }}
                </div>
            @endif
        </div>

        <!-- Payment modal -->
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
                                <h3 class="text-base font-bold text-white leading-tight">Send Payment</h3>
                                <p class="text-white/60 text-xs mt-0.5">Record supplier transaction</p>
                            </div>
                        </div>
                        <button type="button" @click="paymentOpen = false" class="text-white/60 hover:text-white hover:bg-white/10 rounded-lg p-1.5 transition-colors" aria-label="Close">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                        </button>
                    </div>

                    {{-- Modal Body --}}
                    <div class="p-6">
                        <div x-show="paymentPurchase" x-cloak>
                            <form method="POST" class="space-y-4" :action="paymentPurchase ? '/purchases/' + paymentPurchase.id + '/payment' : '#'">
                                @csrf
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-xs font-semibold text-ink-muted uppercase tracking-wide mb-1.5">Invoice No.</label>
                                        <input type="text" :value="paymentPurchase ? paymentPurchase.invoice : ''" disabled class="w-full rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-ink-muted px-3 py-2 text-sm" />
                                    </div>
                                    <div>
                                        <label class="block text-xs font-semibold text-ink-muted uppercase tracking-wide mb-1.5">Supplier</label>
                                        <input type="text" :value="paymentPurchase ? paymentPurchase.supplier : ''" disabled class="w-full rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-ink-muted px-3 py-2 text-sm" />
                                    </div>
                                </div>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-xs font-semibold text-ink-muted uppercase tracking-wide mb-1.5">Payable</label>
                                        <div class="relative flex items-center">
                                            <span class="absolute left-3 text-gray-400 text-sm font-semibold">{{ currencySymbol() }}</span>
                                            <input type="text" :value="paymentPurchase ? paymentPurchase.payable : ''" disabled class="w-full pl-8 pr-3 py-2 rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-ink-muted text-sm" />
                                        </div>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-semibold text-ink-muted uppercase tracking-wide mb-1.5">Due Amount</label>
                                        <div class="relative flex items-center">
                                            <span class="absolute left-3 text-gray-400 text-sm font-semibold">{{ currencySymbol() }}</span>
                                            <input type="text" :value="paymentPurchase ? paymentPurchase.due : ''" disabled class="w-full pl-8 pr-3 py-2 rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-ink-muted text-sm" />
                                        </div>
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-ink-muted uppercase tracking-wide mb-1.5">Paying Amount <span class="text-red-500">*</span></label>
                                    <div class="relative flex items-center">
                                        <span class="absolute left-3 text-gray-400 text-sm font-semibold">{{ currencySymbol() }}</span>
                                        <input type="number" name="paying_amount" step="0.01" min="0.01" required 
                                            x-model="payingAmount"
                                            :max="paymentPurchase ? paymentPurchase.due : null"
                                            class="w-full pl-8 pr-3 py-2 rounded-lg border bg-white dark:bg-gray-900 text-ink-heading text-sm focus:outline-none transition-all payment-paying-input" 
                                            :class="isPayingExceeded ? 'border-red-500 text-red-600 focus:ring-1 focus:ring-red-500 focus:border-red-500' : 'border-gray-200 dark:border-gray-600 focus:ring-1 focus:ring-[#5542ff] focus:border-[#5542ff]'"
                                            placeholder="0.00" />
                                    </div>
                                    <p x-show="isPayingExceeded" x-cloak class="text-red-500 text-xs mt-1.5 font-semibold flex items-center gap-1">
                                        <svg class="h-3.5 w-3.5 text-red-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        <span>Payment amount cannot exceed the due amount ({{ currencySymbol() }}<span x-text="paymentPurchase ? Number(paymentPurchase.due).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}) : '0.00'"></span>).</span>
                                    </p>
                                    @error('paying_amount')
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
                                    :disabled="isPayingExceeded"
                                    class="w-full py-3 rounded-lg text-white font-bold text-sm shadow-md transition-all mt-2 disabled:opacity-50 disabled:cursor-not-allowed" 
                                    :class="isPayingExceeded ? 'opacity-50 cursor-not-allowed' : 'hover:opacity-95'"
                                    style="background: linear-gradient(135deg, var(--gradient-from) 0%, var(--gradient-to) 100%)">
                                    Submit Payment
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Print Warning Modal -->
        <div x-show="showPrintMessage" x-cloak class="fixed inset-0 z-[500] flex items-center justify-center">
            <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" @click="showPrintMessage = false"></div>
            <div class="relative bg-white dark:bg-gray-800 rounded-xl shadow-xl max-w-sm w-full mx-4 p-6 z-10 text-center border border-gray-100 dark:border-gray-700">
                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-yellow-100 mb-4">
                    <svg class="h-6 w-6 text-yellow-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                </div>
                <h3 class="text-lg font-bold text-gray-900 mb-2">Please select records</h3>
                <p class="text-sm text-gray-500 mb-6">Please select at least one record to print.</p>
                <button @click="showPrintMessage = false" class="w-full bg-[#5542ff] hover:bg-[#4736d6] text-white font-bold py-2 px-4 rounded-lg transition-colors">OK</button>
            </div>
        </div>

    </div>

    <style>
        /* Custom Select Arrow */
        .status-select {
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%23F15A22'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M19 9l-7 7-7-7'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 0.5rem center;
            background-size: 1.25rem;
        }
        
        .status-select option:checked {
            background-color: #F15A22;
            color: white;
        }
        
        .status-select option:hover {
            background-color: #F15A22;
            color: white;
        }
    </style>
</x-app-layout>
