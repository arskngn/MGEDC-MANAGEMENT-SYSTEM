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
                    { label: 'Batch Print', desc: 'Print each return invoice on its own page.', handler: function() { window.open('/sales/returns/export/pdf?ids=' + ids + '&print=true&type=batch', '_blank'); } },
                    { label: 'Total Print', desc: 'Print all selected returns combined in a single consolidated document.', handler: function() { window.open('/sales/returns/export/pdf?ids=' + ids + '&print=true&type=total', '_blank'); } },
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
                const amt = parseFloat(this.receivingAmount) || 0;
                const due = parseFloat(this.paymentReturn.due) || 0;
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
            openPayment(r) {
                this.paymentReturn = r;
                this.paymentOpen = true;
                this.paymentMethod = 'cash';
                this.checkNumber = '';
                this.checkDate = '';
                this.receivingAmount = '';
                this.openMoreId = null;
                this.$nextTick(() => {
                    const inp = this.$el.querySelector('.payment-receiving-input');
                    if (inp) { inp.max = r.due; }
                });
            },
            approvalOpen: false,
            approvalReturnId: null,
            approvalAction: '',
            approvalReturnInvoice: '',
            openApprovalModal(id, action, invoice) {
                this.approvalReturnId = id;
                this.approvalAction = action;
                this.approvalReturnInvoice = invoice;
                this.approvalOpen = true;
            }
        }"
        @keydown.escape.window="paymentOpen = false; openMoreId = null; bulkOpen = false; approvalOpen = false;"
        @keydown.window.ctrl.p.prevent="printSelected()"
        @keydown.window.ctrl.a.prevent="selectAll = true; toggleSelectAll()"
        @scroll.window="if (openMoreId) openMoreId = null"
        class="space-y-4 px-3 sm:px-0"
    >
        <div class="flex flex-col gap-3 @if(auth()->user()->hasRole('salesperson'))lg:flex-row lg:items-center lg:justify-between @else sm:flex-row sm:items-center sm:justify-between @endif">
            <div class="flex items-center gap-3">
                <h2 class="text-lg sm:text-xl font-bold text-[#0a1233] dark:text-gray-100 shrink-0">Returns</h2>
                {{-- Quick Nav Pills between Sales and Returns (mobile/tablet for salesperson) --}}
                <div class="@if(auth()->user()->hasRole('salesperson'))lg:hidden @else sm:hidden @endif inline-flex p-1 bg-gray-100 dark:bg-gray-800 rounded-xl border border-gray-200/80 dark:border-gray-700">
                    <a href="{{ route('sales.all') }}" class="px-3 py-1 rounded-lg text-xs font-bold transition-all {{ request()->routeIs('sales.all') || request()->routeIs('sales.index') ? 'bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-xs' : 'text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white' }}">
                        Sales Orders
                    </a>
                    <a href="{{ route('sales.return') }}" class="px-3 py-1 rounded-lg text-xs font-bold transition-all {{ request()->routeIs('sales.return*') ? 'bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-xs' : 'text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white' }}">
                        Returns
                    </a>
                </div>
            </div>

            <div class="flex flex-col @if(auth()->user()->hasRole('salesperson'))lg:flex-row lg:items-center @else sm:flex-row sm:items-center @endif gap-2.5 w-full @if(auth()->user()->hasRole('salesperson'))lg:w-auto @else sm:w-auto @endif">
                <form
                    method="GET"
                    action="{{ route('sales.return') }}"
                    x-data="{
                        ...purchaseDateRange({ dateFrom: @js(request('date_from')), dateTo: @js(request('date_to')) }),
                        mobileFiltersOpen: localStorage.getItem('sales_returns_mobile_filters_open') === 'true' || @js(request()->hasAny(['customer_id', 'status', 'date_from', 'date_to'])),
                        toggleFilters() {
                            this.mobileFiltersOpen = !this.mobileFiltersOpen;
                            localStorage.setItem('sales_returns_mobile_filters_open', this.mobileFiltersOpen);
                        }
                    }"
                    x-ref="filterForm"
                    @keydown.escape.window="escapeClose()"
                    class="w-full @if(auth()->user()->hasRole('salesperson'))lg:flex lg:flex-row lg:items-center lg:gap-2 lg:w-auto @else sm:flex sm:flex-row sm:items-center sm:gap-2 sm:w-auto @endif"
                >
                    <input type="hidden" name="date_from" x-model="dateFrom" />
                    <input type="hidden" name="date_to" x-model="dateTo" />

                    {{-- Search + Filter toggle + inline Action button (mobile only) all on one row --}}
                    <div class="flex items-center gap-2 @if(auth()->user()->hasRole('salesperson'))lg:contents @else sm:contents @endif">
                        <div class="relative flex flex-1 @if(auth()->user()->hasRole('salesperson'))lg:w-[240px] lg:flex-none @else sm:w-[240px] sm:flex-none @endif">
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
                            $activeFilters = (int)(bool)request('date_from') + (int)(bool)request('date_to') + (int)(bool)request('customer_id') + (int)(bool)request('status');
                        @endphp
                        <button type="button" @click="toggleFilters()"
                                class="@if(auth()->user()->hasRole('salesperson'))lg:hidden @else sm:hidden @endif relative flex items-center gap-1.5 px-3 py-2 rounded-xl border text-sm font-semibold transition-all shrink-0 cursor-pointer shadow-xs"
                                :class="mobileFiltersOpen ? 'bg-[#5542ff] text-white border-[#5542ff]' : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 border-gray-300 dark:border-gray-600'">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z"/></svg>
                            <span>Filter</span>
                            @if($activeFilters > 0)
                                <span class="absolute -top-1.5 -right-1.5 h-4 w-4 flex items-center justify-center rounded-full bg-red-500 text-white text-[9px] font-black">{{ $activeFilters }}</span>
                            @endif
                        </button>

                        {{-- Action button shown inline on mobile next to Filter (hidden on desktop where it shows outside the form) --}}
                        @if(auth()->user()->hasPermission('Download Sale Return PDF') || auth()->user()->hasPermission('Download Sale Return CSV'))
                            <div class="relative inline-flex shrink-0 @if(auth()->user()->hasRole('salesperson'))lg:hidden @else sm:hidden @endif">
                                <button type="button" @click="bulkOpen = !bulkOpen; openMoreId = null"
                                        class="inline-flex items-center gap-1.5 px-3 py-2 bg-[#5542ff] border border-[#5542ff] text-white rounded-xl hover:bg-[#4535e8] text-sm font-semibold shadow-sm">
                                    Action
                                    <svg class="h-3.5 w-3.5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </button>
                                <div
                                    x-show="bulkOpen"
                                    @click.outside="bulkOpen = false"
                                    x-cloak
                                    class="absolute right-0 top-full mt-1 w-52 bg-white dark:bg-gray-800 rounded-xl shadow-xl border border-gray-100 dark:border-gray-700 z-[200] py-1"
                                >
                                    @if(auth()->user()->hasPermission('Download Sale Return PDF'))
                                        <a href="{{ route('sales.returns.export.pdf', request()->query()) }}" class="flex items-center px-4 py-2.5 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700">
                                            <svg class="h-4 w-4 mr-2.5 text-gray-400 dark:text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>
                                            Download PDF
                                        </a>
                                    @endif
                                    <button type="button" @click="printSelected()" class="w-full flex items-center px-4 py-2.5 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 text-left">
                                        <svg class="h-4 w-4 mr-2.5 text-gray-400 dark:text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" /></svg>
                                        Print Selected
                                    </button>
                                    @if(auth()->user()->hasPermission('Download Sale Return CSV'))
                                        <a href="{{ route('sales.returns.export.csv', request()->query()) }}" class="flex items-center px-4 py-2.5 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700">
                                            <svg class="h-4 w-4 mr-2.5 text-gray-400 dark:text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                                            Download CSV
                                        </a>
                                    @endif
                                </div>
                            </div>
                        @endif
                    </div>

                    {{-- Collapsible Filter options on mobile/salesperson tablet, inline on desktop --}}
                    <div x-show="mobileFiltersOpen" x-cloak class="@if(auth()->user()->hasRole('salesperson'))lg:!block lg:flex lg:flex-row lg:items-center lg:gap-2 @else sm:!block sm:flex sm:flex-row sm:items-center sm:gap-2 @endif" style="display:none">
                        <div class="grid grid-cols-2 gap-2 mt-2 @if(auth()->user()->hasRole('salesperson'))lg:mt-0 lg:flex lg:flex-row lg:gap-2 @else sm:mt-0 sm:flex sm:flex-row sm:gap-2 @endif">
                            <div class="col-span-2 @if(auth()->user()->hasRole('salesperson'))lg:col-span-1 lg:w-auto @else sm:col-span-1 sm:w-auto @endif">
                                @include('components.date-range-picker')
                            </div>

                            <div class="relative w-full @if(auth()->user()->hasRole('salesperson'))lg:w-auto @else sm:w-auto @endif">
                                <select name="customer_id" class="w-full @if(auth()->user()->hasRole('salesperson'))lg:w-auto @else sm:w-auto @endif text-sm border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-900 pl-3 pr-8 py-2 text-gray-700 dark:text-gray-100 focus:ring-1 focus:ring-[#5542ff] focus:border-[#5542ff] appearance-none cursor-pointer shadow-2xs transition-colors" onchange="this.form.submit()">
                                    <option value="">All Customers</option>
                                    @foreach($customers as $c)
                                        <option value="{{ $c->id }}" @selected(request('customer_id') == $c->id)>{{ $c->name }}</option>
                                    @endforeach
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-2.5 text-ink-muted">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </div>
                            </div>

                            <div class="relative w-full @if(auth()->user()->hasRole('salesperson'))lg:w-auto @else sm:w-auto @endif">
                                <select name="status" class="w-full @if(auth()->user()->hasRole('salesperson'))lg:w-auto @else sm:w-auto @endif text-sm border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-900 pl-3 pr-8 py-2 text-gray-700 dark:text-gray-100 focus:ring-1 focus:ring-[#5542ff] focus:border-[#5542ff] appearance-none cursor-pointer shadow-2xs transition-colors" onchange="this.form.submit()">
                                    <option value="">All Status</option>
                                    <option value="confirmed" @selected(request('status') === 'confirmed')>Confirmed</option>
                                    <option value="pending" @selected(request('status') === 'pending')>Pending</option>
                                    <option value="rejected" @selected(request('status') === 'rejected')>Rejected</option>
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-2.5 text-ink-muted">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>

                {{-- Action button on desktop only (mobile version is inlined above inside the form row) --}}
                @if(auth()->user()->hasPermission('Download Sale Return PDF') || auth()->user()->hasPermission('Download Sale Return CSV'))
                    <div class="relative hidden @if(auth()->user()->hasRole('salesperson'))lg:inline-flex @else sm:inline-flex @endif shrink-0">
                        <button type="button" @click="bulkOpen = !bulkOpen; openMoreId = null" class="inline-flex items-center justify-center px-4 py-2 bg-[#5542ff] border border-[#5542ff] text-white rounded-md hover:bg-[#4535e8] text-sm font-semibold shadow-sm">
                            Action
                            <svg class="h-4 w-4 ml-1 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        <div
                            x-show="bulkOpen"
                            @click.outside="bulkOpen = false"
                            x-cloak
                            class="absolute right-0 top-full mt-1 w-48 bg-white dark:bg-gray-800 rounded-xl shadow-xl border border-gray-100 dark:border-gray-700 z-[200] py-1"
                        >
                            @if(auth()->user()->hasPermission('Download Sale Return PDF'))
                                <a href="{{ route('sales.returns.export.pdf', request()->query()) }}" class="flex items-center px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700">
                                    <svg class="h-4 w-4 mr-2 text-gray-400 dark:text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>
                                    Download PDF
                                </a>
                            @endif
                            <button type="button" @click="printSelected()" class="w-full flex items-center px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 text-left">
                                <svg class="h-4 w-4 mr-2 text-gray-400 dark:text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" /></svg>
                                Print Selected
                            </button>
                            @if(auth()->user()->hasPermission('Download Sale Return CSV'))
                                <a href="{{ route('sales.returns.export.csv', request()->query()) }}" class="flex items-center px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700">
                                    <svg class="h-4 w-4 mr-2 text-gray-400 dark:text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                                    Download CSV
                                </a>
                            @endif
                        </div>
                    </div>
                @endif
            </div>
        </div>

        @if(request()->hasAny(['search', 'date_from', 'date_to', 'customer_id', 'status']))
            <div class="flex flex-wrap items-center gap-2 px-1 py-1">
                <span class="text-xs font-semibold text-ink-muted">Active Filters:</span>
                
                @if(request('search'))
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium bg-[#5542ff]/10 text-[#5542ff] dark:bg-[#5542ff]/20 dark:text-indigo-300 border border-[#5542ff]/20">
                        Search: "{{ request('search') }}"
                        <a href="{{ request()->fullUrlWithQuery(['search' => null]) }}" class="hover:text-red-500 font-bold ml-1" title="Remove search filter">&times;</a>
                    </span>
                @endif

                @if(request('date_from') || request('date_to'))
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium bg-[#5542ff]/10 text-[#5542ff] dark:bg-[#5542ff]/20 dark:text-indigo-300 border border-[#5542ff]/20">
                        Date: {{ request('date_from') ?? 'Start' }} &rarr; {{ request('date_to') ?? 'End' }}
                        <a href="{{ request()->fullUrlWithQuery(['date_from' => null, 'date_to' => null]) }}" class="hover:text-red-500 font-bold ml-1" title="Remove date filter">&times;</a>
                    </span>
                @endif

                @if(request('customer_id'))
                    @php $cName = $customers->firstWhere('id', request('customer_id'))?->name; @endphp
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium bg-[#5542ff]/10 text-[#5542ff] dark:bg-[#5542ff]/20 dark:text-indigo-300 border border-[#5542ff]/20">
                        Customer: {{ $cName ?? 'ID '.request('customer_id') }}
                        <a href="{{ request()->fullUrlWithQuery(['customer_id' => null]) }}" class="hover:text-red-500 font-bold ml-1" title="Remove customer filter">&times;</a>
                    </span>
                @endif

                @if(request('status'))
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium bg-[#5542ff]/10 text-[#5542ff] dark:bg-[#5542ff]/20 dark:text-indigo-300 border border-[#5542ff]/20">
                        Status: {{ ucfirst(request('status')) }}
                        <a href="{{ request()->fullUrlWithQuery(['status' => null]) }}" class="hover:text-red-500 font-bold ml-1" title="Remove status filter">&times;</a>
                    </span>
                @endif

                <a href="{{ route('sales.return') }}" class="text-xs text-red-600 dark:text-red-400 hover:underline font-semibold ml-1">
                    Reset All Filters
                </a>
            </div>
        @endif

        @if(session('success'))
            <div class="rounded-lg bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-700 text-green-800 dark:text-green-200 px-4 py-3 text-sm">
                {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="rounded-lg bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-700 text-red-800 dark:text-red-200 px-4 py-3 text-sm">
                {{ session('error') }}
            </div>
        @endif
        @if($errors->any())
            <div class="rounded-lg bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-700 text-red-800 dark:text-red-300 px-4 py-3 text-sm">
                <div class="font-semibold mb-1">Please fix the following:</div>
                @foreach($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        {{-- ── MOBILE & TABLET CARD LAYOUT (mobile for all, tablet 2-cols for salesperson) ── --}}
        <div class="@if(auth()->user()->hasRole('salesperson'))lg:hidden @else sm:hidden @endif space-y-3">
            @php $canSelect = auth()->user()->hasPermission('Download Sale Return PDF') || auth()->user()->hasPermission('Download Sale Return CSV'); @endphp
            @if($canSelect)
                <div class="flex items-center justify-between px-3 py-2 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm">
                    <label class="flex items-center gap-2 cursor-pointer select-none text-xs font-bold text-gray-700 dark:text-gray-200">
                        <input type="checkbox" x-model="selectAll" @change="toggleSelectAll()" class="rounded border-gray-300 dark:border-gray-600 text-[#5542ff] focus:ring-[#5542ff] bg-white dark:bg-gray-800">
                        <span>Select All Returns</span>
                    </label>
                    <span class="text-xs font-semibold" :class="selectedItems.length > 0 ? 'text-[#5542ff]' : 'text-gray-400'" x-text="selectedItems.length > 0 ? selectedItems.length + ' selected' : 'None selected'"></span>
                </div>
            @endif

            <div class="@if(auth()->user()->hasRole('salesperson'))md:grid md:grid-cols-2 md:gap-3 space-y-3 md:space-y-0 @else space-y-3 @endif">
            @forelse($salesReturns as $r)
                @php
                    $phone = $r->sale?->customer?->phone;
                    $phoneDisp = $phone ? (\Illuminate\Support\Str::startsWith($phone, '+') ? $phone : "+{$phone}") : '—';
                    
                    // Calculate progressive ledger offsets across all returns for this sale
                    $remainingUnpaid = max(0, (float)($r->sale?->receivable_amount ?? 0) - (float)($r->sale?->paid_amount ?? 0));
                    $effectiveDue = 0;
                    
                    foreach ($r->sale?->saleReturns ?? [] as $sr) {
                        if ($sr->status === 'rejected') continue;

                        $payable = (float)$sr->payable_amount;
                        $offset = min($payable, $remainingUnpaid);
                        $cashOwed = $payable - $offset;
                        $remainingUnpaid -= $offset;
                        
                        if ($sr->id === $r->id) {
                            $effectiveDue = max(0, $cashOwed - (float)$sr->paid_amount);
                            break;
                        }
                    }
                @endphp
                <div :class="{ 'ring-2 ring-[#5542ff]/40': selectedItems.includes(String('{{ $r->id }}')) || selectedItems.includes({{ $r->id }}) }"
                     class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden transition-all hover:border-gray-300 dark:hover:border-gray-600">

                    {{-- Clickable main card body --}}
                    <a href="{{ route('sales.return.show', $r) }}" class="block p-3.5 space-y-2.5">
                        {{-- Row 1: Checkbox + Return Invoice + Date + Status Badges --}}
                        <div class="flex items-center gap-2.5">
                            @if($canSelect)
                                <input type="checkbox" value="{{ $r->id }}" x-model="selectedItems"
                                       class="row-checkbox rounded border-gray-300 dark:border-gray-600 text-[#5542ff] focus:ring-[#5542ff] bg-white dark:bg-gray-800 shrink-0"
                                       @click.stop>
                            @endif
                            <div class="flex items-center gap-1.5 min-w-0">
                                @if($effectiveDue > 0.009)
                                    <span class="inline-block w-2 h-2 rounded-full bg-red-500 shrink-0 animate-pulse" title="Refund due to customer"></span>
                                @endif
                                <span class="font-black text-[#5542ff] dark:text-indigo-400 text-sm leading-none truncate">
                                    {{ $r->return_invoice_no }}
                                </span>
                            </div>
                            <span class="text-[10px] text-ink-muted shrink-0">{{ $r->return_date->format('d M Y') }}</span>
                            <div class="ml-auto flex items-center gap-1 shrink-0 justify-end">
                                @if($r->status === 'pending')
                                    <span class="px-2 py-0.5 rounded-full text-[9px] font-black bg-amber-50 dark:bg-amber-900/30 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-700 uppercase tracking-wide">Pending</span>
                                @elseif($r->status === 'rejected')
                                    <span class="px-2 py-0.5 rounded-full text-[9px] font-black bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-300 border border-red-200 dark:border-red-700 uppercase tracking-wide">Rejected</span>
                                @else
                                    <span class="px-2 py-0.5 rounded-full text-[9px] font-black bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-700 uppercase tracking-wide">Confirmed</span>
                                @endif
                                @if($r->status === 'confirmed')
                                    @if($effectiveDue <= 0.009)
                                        <span class="px-2 py-0.5 rounded-full text-[9px] font-black bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300 border border-green-200 dark:border-green-800 uppercase tracking-wide">Settled</span>
                                    @elseif($r->paid_amount > 0.009)
                                        <span class="px-2 py-0.5 rounded-full text-[9px] font-black bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-700 uppercase tracking-wide">Partial</span>
                                    @else
                                        <span class="px-2 py-0.5 rounded-full text-[9px] font-black bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400 border border-red-200 dark:border-red-700 uppercase tracking-wide">Due</span>
                                    @endif
                                @endif
                            </div>
                        </div>

                        {{-- Row 2: Customer Name, Phone & Orig Sale link --}}
                        <div class="flex items-center justify-between gap-2 pt-0.5">
                            <div class="min-w-0 flex-1">
                                <p class="font-bold text-ink-heading text-sm leading-snug truncate">{{ $r->sale?->customer?->name ?? '—' }}</p>
                                <p class="text-[11px] text-ink-muted leading-tight mt-0.5">{{ $phoneDisp }}</p>
                            </div>
                            @if($r->sale)
                                <a href="{{ route('sales.show', $r->sale) }}"
                                   class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded-md text-[11px] font-semibold text-[#5542ff] dark:text-indigo-300 hover:text-[#4535e8] dark:hover:text-indigo-200 hover:bg-indigo-50 dark:hover:bg-indigo-950/40 transition-colors shrink-0"
                                   title="View Original Sale Invoice"
                                   @click.stop>
                                    <svg class="w-3 h-3 text-indigo-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    <span class="tabular-nums tracking-tight">{{ $r->sale->invoice_no }}</span>
                                </a>
                            @endif
                        </div>

                        {{-- Row 3: Financial metrics bar --}}
                        <div class="grid grid-cols-3 rounded-xl overflow-hidden border border-gray-100 dark:border-gray-700 divide-x divide-gray-100 dark:divide-gray-700 bg-gray-50/60 dark:bg-gray-800/40 text-[10px]">
                            <div class="px-2.5 py-2">
                                <span class="text-gray-400 dark:text-gray-400 uppercase font-bold tracking-wider block text-[9px] leading-tight mb-0.5">Total Return</span>
                                <span class="font-extrabold text-ink-heading text-xs truncate block">{{ formatCurrency($r->subtotal) }}</span>
                            </div>
                            <div class="px-2.5 py-2">
                                <span class="text-gray-400 dark:text-gray-400 uppercase font-bold tracking-wider block text-[9px] leading-tight mb-0.5">Payable</span>
                                <span class="font-extrabold text-ink-heading text-xs truncate block">{{ formatCurrency($r->payable_amount) }}</span>
                            </div>
                            <div class="px-2.5 py-2 {{ $effectiveDue > 0.009 ? 'bg-red-50/60 dark:bg-red-950/30' : '' }}">
                                <span class="text-gray-400 dark:text-gray-400 uppercase font-bold tracking-wider block text-[9px] leading-tight mb-0.5">{{ $effectiveDue > 0.009 ? 'Refund Due' : 'Balance Offset' }}</span>
                                <span class="font-extrabold text-xs truncate block {{ $effectiveDue > 0.009 ? 'text-red-600 dark:text-red-400' : 'text-emerald-600 dark:text-emerald-400' }}">
                                    {{ $effectiveDue > 0.009 ? formatCurrency($effectiveDue) : formatCurrency(0) }}
                                </span>
                            </div>
                        </div>
                    </a>

                    {{-- Row 4: Quick Action Toolbar --}}
                    <div class="px-3.5 py-2.5 bg-gray-50/70 dark:bg-gray-800/80 border-t border-gray-100 dark:border-gray-700 flex items-center justify-between text-xs gap-2">
                        <a href="{{ route('sales.return.show', $r) }}" class="inline-flex items-center text-xs font-bold text-[#5542ff] dark:text-indigo-400 hover:underline">
                            Details &rarr;
                        </a>

                        <div class="flex items-center gap-1.5 flex-wrap ml-auto">
                            @if($r->status === 'pending')
                                @if(auth()->user()->hasPermission('Confirm Sale Return'))
                                    <button type="button" @click.stop.prevent="openApprovalModal({{ $r->id }}, 'confirm', '{{ $r->return_invoice_no }}')" class="inline-flex items-center gap-1 px-2.5 py-1 bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-700 hover:bg-emerald-100 dark:hover:bg-emerald-900/50 rounded-full text-[11px] font-bold transition-all active:scale-95">
                                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                        Confirm
                                    </button>
                                @endif
                                @if(auth()->user()->hasPermission('Reject Sale Return'))
                                    <button type="button" @click.stop.prevent="openApprovalModal({{ $r->id }}, 'reject', '{{ $r->return_invoice_no }}')" class="inline-flex items-center gap-1 px-2.5 py-1 bg-rose-50 dark:bg-rose-900/30 text-rose-700 dark:text-rose-300 border border-rose-200 dark:border-rose-700 hover:bg-rose-100 dark:hover:bg-rose-900/50 rounded-full text-[11px] font-bold transition-all active:scale-95">
                                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                                        Reject
                                    </button>
                                @endif
                            @endif

                            @php
                                $canStorePayment = auth()->user()->hasPermission('Store Customer Payment') || auth()->user()->hasRole('salesperson');
                            @endphp
                            @if($canStorePayment && $effectiveDue > 0.009 && $r->status === 'confirmed')
                                <button type="button"
                                        @click.stop.prevent="openPayment({ id: {{ $r->id }}, invoice: @js($r->return_invoice_no), customer: @js($r->sale?->customer?->name ?? 'Customer'), receivable: @js(number_format((float)$r->payable_amount, 2, '.', '')), due: @js(number_format($effectiveDue, 2, '.', '')) })"
                                        class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold border border-orange-300 bg-orange-50 dark:bg-orange-900/30 text-orange-700 dark:text-orange-300 whitespace-nowrap active:scale-95 transition-all">
                                    <svg class="w-2.5 h-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/></svg>
                                    Refund
                                </button>
                            @endif

                            @if(auth()->user()->hasPermission('Edit Sale Return') && $r->status === 'confirmed')
                                <a href="{{ route('sales.return.edit', $r) }}" class="inline-flex items-center px-2 py-1 bg-white dark:bg-gray-700 border border-[#5542ff] text-[#5542ff] dark:text-indigo-400 rounded-lg hover:bg-[#5542ff]/5 text-xs font-bold transition-all">
                                    Edit
                                </a>
                            @endif

                            @if(auth()->user()->hasPermission('Download Sale Return Invoice PDF') && $r->status === 'confirmed')
                                <a href="{{ route('sales.return.pdf', $r) }}?print=true" target="_blank" class="inline-flex items-center p-1.5 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 rounded-lg hover:bg-gray-50 text-xs font-bold transition-all" title="Print Invoice">
                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" /></svg>
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-8 text-center text-sm text-gray-400 col-span-2">
                    No sales returns found.
                </div>
            @endforelse
            </div>

            @if($salesReturns->hasPages())
                <div class="px-4 py-3 bg-gray-50 dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700">
                    {{ $salesReturns->links() }}
                </div>
            @endif
        </div>

        {{-- ── DESKTOP TABLE LAYOUT (hidden on mobile and tablet for salesperson) ── --}}
        <div class="@if(auth()->user()->hasRole('salesperson'))hidden lg:block @else hidden sm:block @endif bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse min-w-[1100px]">
                    <thead>
                        <tr class="gradient-bg text-white">
                            <th class="checkbox-column px-4 py-3.5 text-[11px] font-bold uppercase tracking-wide w-10">
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="checkbox" x-model="selectAll" @change="toggleSelectAll()" class="rounded border-gray-300 dark:border-gray-600 text-[#5542ff] focus:ring-[#5542ff] bg-white dark:bg-gray-800">
                                    <span class="sr-only">Select All</span>
                                </label>
                            </th>
                            <th class="px-4 py-3.5 text-[11px] font-bold uppercase tracking-wide">Return Invoice | Date</th>
                            <th class="px-4 py-3.5 text-[11px] font-bold uppercase tracking-wide">From Sale</th>
                            <th class="px-4 py-3.5 text-[11px] font-bold uppercase tracking-wide">Customer | Mobile</th>
                            <th class="px-4 py-3.5 text-[11px] font-bold uppercase tracking-wide">Total Amount</th>
                            <th class="px-4 py-3.5 text-[11px] font-bold uppercase tracking-wide">Lessed | Payable</th>
                            <th class="px-4 py-3.5 text-[11px] font-bold uppercase tracking-wide">Received | Due</th>
                            <th class="px-4 py-3.5 text-[11px] font-bold uppercase tracking-wide text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($salesReturns as $r)
                            @php
                                $phone = $r->sale?->customer?->phone;
                                $phoneDisp = $phone ? (\Illuminate\Support\Str::startsWith($phone, '+') ? $phone : "+{$phone}") : '—';
                                
                                // Calculate progressive ledger offsets across all returns for this sale
                                $remainingUnpaid = max(0, (float)($r->sale?->receivable_amount ?? 0) - (float)($r->sale?->paid_amount ?? 0));
                                $effectiveDue = 0;
                                
                                foreach ($r->sale?->saleReturns ?? [] as $sr) {
                                    if ($sr->status === 'rejected') continue;

                                    $payable = (float)$sr->payable_amount;
                                    $offset = min($payable, $remainingUnpaid);
                                    $cashOwed = $payable - $offset;
                                    $remainingUnpaid -= $offset;
                                    
                                    if ($sr->id === $r->id) {
                                        $effectiveDue = max(0, $cashOwed - (float)$sr->paid_amount);
                                        break;
                                    }
                                }
                            @endphp
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors bg-white dark:bg-gray-800 cursor-pointer" onclick="if(event.target.closest('.checkbox-column, .text-right, .status-actions')) return; window.location.href='{{ route('sales.return.show', $r) }}'">
                                <td class="checkbox-column px-4 py-3.5 align-middle">
                                    <input type="checkbox" value="{{ $r->id }}" x-model="selectedItems" class="row-checkbox rounded border-gray-300 dark:border-gray-600 text-[#5542ff] focus:ring-[#5542ff] bg-white dark:bg-gray-800">
                                </td>
                                <td class="px-4 py-3.5 align-top">
                                    <div class="flex items-start gap-1.5">
                                        @if($effectiveDue > 0.009)
                                            <span class="inline-block w-2 h-2 rounded-full bg-red-500 mt-1.5 shrink-0" title="Cash refund due"></span>
                                        @endif
                                        <div>
                                            <div class="flex items-center gap-1.5">
                                                <span class="text-sm font-bold text-[#2563eb] dark:text-blue-400">{{ $r->return_invoice_no }}</span>
                                                @if($r->status === 'pending')
                                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 dark:bg-amber-900/30 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-700">Pending</span>
                                                @elseif($r->status === 'rejected')
                                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-300 border border-red-200 dark:border-red-700">Rejected</span>
                                                @else
                                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-700">Confirmed</span>
                                                @endif
                                            </div>
                                            <div class="text-xs text-ink-muted mt-0.5">{{ $r->return_date->format('d M, Y') }}</div>
                                        </div>
                                    </div>
                                </td>
                                {{-- From Sale column --}}
                                <td class="px-4 py-3.5 align-top">
                                    @if($r->sale)
                                        <a href="{{ route('sales.show', $r->sale) }}" class="inline-flex items-center gap-1 text-sm font-bold text-[#5542ff] hover:text-[#4535e8] hover:underline transition-colors" onclick="event.stopPropagation()" title="View sale {{ $r->sale->invoice_no }}">
                                            <svg class="h-3.5 w-3.5 shrink-0 opacity-70" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                                            {{ $r->sale->invoice_no }}
                                        </a>
                                        <div class="text-[10px] text-ink-muted mt-0.5">{{ $r->sale->sale_date?->format('d M, Y') ?? $r->sale->created_at?->format('d M, Y') }}</div>
                                    @else
                                        <span class="text-sm text-gray-400">—</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3.5 align-top">
                                    <div class="text-sm font-semibold text-ink-heading">{{ $r->sale?->customer?->name ?? '—' }}</div>
                                    <div class="text-xs text-ink-muted mt-0.5">{{ $phoneDisp }}</div>
                                </td>
                                <td class="px-4 py-3.5 align-top">
                                    <div class="text-sm font-bold text-ink-heading">{{ formatCurrency($r->subtotal) }}</div>
                                </td>
                                <td class="px-4 py-3.5 align-top">
                                    <div class="text-sm text-ink-muted">{{ formatCurrency($r->discount) }}</div>
                                    <div class="text-sm font-bold text-ink-heading mt-0.5">{{ formatCurrency($r->payable_amount) }}</div>
                                </td>
                                <td class="px-4 py-3.5 align-top">
                                    <div class="text-sm text-ink-muted">{{ formatCurrency($r->paid_amount) }}</div>
                                    @if($effectiveDue > 0.009)
                                        <div class="text-sm font-bold text-red-600 dark:text-red-400 mt-0.5">{{ formatCurrency($effectiveDue) }}</div>
                                        <div class="mt-1 text-[10px] text-orange-600 dark:text-orange-400 font-medium">Cash refund owed</div>
                                    @else
                                        <div class="text-sm font-bold text-green-600 dark:text-green-400 mt-0.5">{{ formatCurrency(0) }}</div>
                                        <div class="mt-1 text-[10px] text-ink-muted">Offset against balance</div>
                                    @endif
                                </td>
                                <td class="px-4 py-3.5 align-top text-right">
                                    <div class="flex items-center justify-end gap-2 flex-nowrap whitespace-nowrap">
                                        @if($r->status === 'pending')
                                            <div class="status-actions flex gap-1 mr-1">
                                                @if(auth()->user()->hasPermission('Confirm Sale Return'))
                                                    <button type="button" @click.stop.prevent="openApprovalModal({{ $r->id }}, 'confirm', '{{ $r->return_invoice_no }}')" class="inline-flex items-center px-2 py-1 bg-green-50 dark:bg-green-900/30 text-green-600 dark:text-green-300 border border-green-200 dark:border-green-700 hover:bg-green-100 dark:hover:bg-green-900/50 rounded text-[11px] font-bold transition-colors">
                                                        <svg class="w-3 h-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                                        Confirm
                                                    </button>
                                                @endif
                                                @if(auth()->user()->hasPermission('Reject Sale Return'))
                                                    <button type="button" @click.stop.prevent="openApprovalModal({{ $r->id }}, 'reject', '{{ $r->return_invoice_no }}')" class="inline-flex items-center px-2 py-1 bg-red-50 dark:bg-red-900/30 text-red-600 dark:text-red-300 border border-red-200 dark:border-red-700 hover:bg-red-100 dark:hover:bg-red-900/50 rounded text-[11px] font-bold transition-colors">
                                                        <svg class="w-3 h-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                                                        Reject
                                                    </button>
                                                @endif
                                            </div>
                                        @endif

                                        @if(auth()->user()->hasPermission('Edit Sale Return') && $r->status === 'confirmed')
                                            <a href="{{ route('sales.return.edit', $r) }}" class="inline-flex items-center px-2.5 py-1.5 bg-white dark:bg-gray-700 border border-[#5542ff] text-[#5542ff] dark:text-blue-400 rounded-md hover:bg-[#5542ff]/5 dark:hover:bg-gray-600 text-[11px] font-bold">
                                                <svg class="h-3.5 w-3.5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                </svg>
                                                Edit
                                            </a>
                                        @endif

                                        @if($r->status === 'confirmed')
                                            <div class="inline-flex">
                                                <button
                                                    type="button"
                                                    @click.stop="toggleMore({{ $r->id }}, $event)"
                                                    class="inline-flex items-center px-2.5 py-1.5 bg-white dark:bg-gray-700 border border-sky-400 dark:border-sky-500 text-sky-600 dark:text-sky-400 rounded-md hover:bg-sky-50 dark:hover:bg-gray-600 text-[11px] font-bold"
                                                >
                                                    <svg class="h-3.5 w-3.5 mr-1 text-sky-600 dark:text-sky-400" fill="currentColor" viewBox="0 0 24 24"><path d="M12 8c1.1 0 2-.9 2-2s-.9-2-2-2-2 .9-2 2 .9 2 2 2zm0 2c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm0 6c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2z"/></svg>
                                                    More
                                                    <svg class="h-3 w-3 ml-0.5 text-sky-600 dark:text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                                                </button>
                                                <template x-teleport="body">
                                                    <div
                                                        x-show="openMoreId === {{ $r->id }}"
                                                        @click.outside="openMoreId = null"
                                                        x-cloak
                                                        class="fixed w-56 bg-white dark:bg-gray-800 rounded-lg shadow-xl border border-gray-100 dark:border-gray-700 py-1 text-left z-[200]"
                                                        :style="{ top: dropdownTop, right: dropdownRight }"
                                                    >
                                                        <a href="{{ route('sales.return.show', $r) }}" @click="openMoreId = null" class="flex items-center px-3 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700">
                                                            <svg class="h-4 w-4 mr-2 text-gray-400 dark:text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                                                            View Details
                                                        </a>
                                                        @php
                                                            $canStorePayment = auth()->user()->hasPermission('Store Customer Payment') || auth()->user()->hasRole('salesperson');
                                                        @endphp
                                                        @if($canStorePayment && $effectiveDue > 0.009)
                                                            <button
                                                                type="button"
                                                                @click="openPayment({ id: {{ $r->id }}, invoice: @js($r->return_invoice_no), customer: @js($r->sale->customer->name), receivable: @js(number_format((float)$r->payable_amount, 2, '.', '')), due: @js(number_format($effectiveDue, 2, '.', '')) })"
                                                                class="w-full flex items-center px-3 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700"
                                                            >
                                                                <svg class="h-4 w-4 mr-2 text-orange-500 dark:text-orange-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/></svg>
                                                                Process Refund
                                                            </button>
                                                        @endif
                                                        @if(auth()->user()->hasPermission('Download Sale Return Invoice PDF'))
                                                            <a href="{{ route('sales.return.pdf', $r) }}?print=true" target="_blank" @click="openMoreId = null" class="flex items-center px-3 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700">
                                                                <svg class="h-4 w-4 mr-2 text-gray-400 dark:text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" /></svg>
                                                                Print
                                                            </a>
                                                            <a href="{{ route('sales.return.pdf', $r) }}" @click="openMoreId = null" class="flex items-center px-3 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700">
                                                                <svg class="h-4 w-4 mr-2 text-gray-400 dark:text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>
                                                                Download Details
                                                            </a>
                                                        @endif
                                                    </div>
                                                </template>
                                            </div>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-12 text-center text-ink-muted">No sales returns found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($salesReturns->hasPages())
                <div class="px-4 py-3 bg-gray-50 dark:bg-gray-900 border-t border-gray-100 dark:border-gray-700">
                    {{ $salesReturns->links() }}
                </div>
            @endif
        </div>

        <!-- Approval confirmation modal (Confirm / Reject) -->
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
                                <h3 class="text-base font-bold text-white leading-tight" x-text="approvalAction === 'confirm' ? 'Confirm Sale Return' : 'Reject Sale Return'"></h3>
                                <p class="text-white/80 text-xs mt-0.5" x-text="approvalAction === 'confirm' ? 'Acknowledge & confirm this return' : 'Reject this return request'"></p>
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
                                Are you sure you want to <span class="font-black uppercase tracking-wide text-xs px-2 py-0.5 rounded" :class="approvalAction === 'confirm' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300' : 'bg-rose-100 text-rose-700 dark:bg-rose-900/40 dark:text-rose-300'" x-text="approvalAction"></span> return <span class="font-extrabold text-ink-heading" x-text="approvalReturnInvoice"></span>?
                            </p>
                        </div>

                        <form method="POST" :action="approvalReturnId ? `/sales-returns/${approvalReturnId}/${approvalAction}` : '#'">
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

        <div x-show="paymentOpen" x-cloak class="fixed inset-0 z-[210] overflow-y-auto" style="display: none;">
            <div class="flex items-center justify-center min-h-screen px-4 py-8">
                <div class="fixed inset-0 bg-gray-900/50 z-0" @click="paymentOpen = false"></div>
                <div class="relative bg-white dark:bg-gray-800 rounded-2xl shadow-2xl max-w-md w-full z-10 border border-gray-100 dark:border-gray-700" @click.stop>
                    
                    {{-- Modal Header --}}
                    <div class="px-6 py-5 flex justify-between items-center rounded-t-2xl" style="background: linear-gradient(135deg, #ea580c 0%, #f97316 100%)">
                        <div class="flex items-center gap-3">
                            <div class="bg-white/20 rounded-lg p-2">
                                <svg class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-base font-bold text-white leading-tight">Process Refund</h3>
                                <p class="text-white/70 text-xs mt-0.5">Issue refund for return transaction</p>
                            </div>
                        </div>
                        <button type="button" @click="paymentOpen = false" class="text-white/60 hover:text-white hover:bg-white/10 rounded-lg p-1.5 transition-colors" aria-label="Close">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                        </button>
                    </div>

                    {{-- Modal Body --}}
                    <div class="p-6">
                        <div x-show="paymentReturn" x-cloak>
                            <form method="POST" class="space-y-4" :action="paymentReturn ? `/sales-returns/${paymentReturn.id}/payment` : '#'">
                                @csrf
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-xs font-semibold text-ink-muted uppercase tracking-wide mb-1.5">Return Invoice</label>
                                        <input type="text" :value="paymentReturn ? paymentReturn.invoice : ''" disabled class="w-full rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-ink-muted px-3 py-2 text-sm" />
                                    </div>
                                    <div>
                                        <label class="block text-xs font-semibold text-ink-muted uppercase tracking-wide mb-1.5">Customer</label>
                                        <input type="text" :value="paymentReturn ? paymentReturn.customer : ''" disabled class="w-full rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-ink-muted px-3 py-2 text-sm" />
                                    </div>
                                </div>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-xs font-semibold text-ink-muted uppercase tracking-wide mb-1.5">Total Payable</label>
                                        <div class="relative flex items-center">
                                            <span class="absolute left-3 text-gray-400 text-sm font-semibold">{{ currencySymbol() }}</span>
                                            <input type="text" :value="paymentReturn ? paymentReturn.receivable : ''" disabled class="w-full pl-8 pr-3 py-2 rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-ink-muted text-sm" />
                                        </div>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-semibold text-ink-muted uppercase tracking-wide mb-1.5">Refund Due</label>
                                        <div class="relative flex items-center">
                                            <span class="absolute left-3 text-gray-400 text-sm font-semibold">{{ currencySymbol() }}</span>
                                            <input type="text" :value="paymentReturn ? paymentReturn.due : ''" disabled class="w-full pl-8 pr-3 py-2 rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-ink-muted text-sm" />
                                        </div>
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-ink-muted uppercase tracking-wide mb-1.5">Refund Amount <span class="text-red-500">*</span></label>
                                    <div class="relative flex items-center">
                                        <span class="absolute left-3 text-gray-400 text-sm font-semibold">{{ currencySymbol() }}</span>
                                        <input type="number" name="receiving_amount" step="0.01" min="0.01" required 
                                            x-model="receivingAmount"
                                            :max="paymentReturn ? paymentReturn.due : null"
                                            class="w-full pl-8 pr-3 py-2 rounded-lg border bg-white dark:bg-gray-900 text-ink-heading text-sm focus:outline-none transition-all payment-receiving-input" 
                                            :class="isReceivingExceeded ? 'border-red-500 text-red-600 focus:ring-1 focus:ring-red-500 focus:border-red-500' : 'border-gray-200 dark:border-gray-600 focus:ring-1 focus:ring-orange-500 focus:border-orange-500'"
                                            placeholder="0.00" />
                                    </div>
                                    <p x-show="isReceivingExceeded" x-cloak class="text-red-500 text-xs mt-1.5 font-semibold flex items-center gap-1">
                                        <svg class="h-3.5 w-3.5 text-red-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        <span>Refund amount cannot exceed the due amount ({{ currencySymbol() }}<span x-text="paymentReturn ? Number(paymentReturn.due).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}) : '0.00'"></span>).</span>
                                    </p>
                                    @error('receiving_amount')
                                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-ink-muted uppercase tracking-wide mb-1.5">Refund Method <span class="text-red-500">*</span></label>
                                    <select name="payment_method" required 
                                        class="w-full rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-900 text-ink-heading px-3 py-2.5 text-sm focus:outline-none focus:ring-1 focus:ring-orange-500 focus:border-orange-500 transition-all" x-model="paymentMethod">
                                        <option value="cash">Cash</option>
                                        <option value="check">Check</option>
                                    </select>
                                </div>
                                <div x-show="paymentMethod === 'check'" x-cloak class="space-y-4">
                                    <div>
                                        <label class="block text-xs font-semibold text-ink-muted uppercase tracking-wide mb-1.5">Check Number <span class="text-red-500">*</span></label>
                                        <input type="text" name="check_number" x-model="checkNumber" :required="paymentMethod === 'check'" 
                                            class="w-full rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-900 text-ink-heading px-3 py-2.5 text-sm focus:outline-none focus:ring-1 focus:ring-orange-500 focus:border-orange-500 transition-all" placeholder="Enter check number" />
                                        @error('check_number')
                                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    <div>
                                        <label class="block text-xs font-semibold text-ink-muted uppercase tracking-wide mb-1.5">Check Date <span class="text-red-500">*</span></label>
                                        <input type="date" name="check_date" x-model="checkDate" :required="paymentMethod === 'check'" 
                                            class="w-full rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-900 text-ink-heading px-3 py-2.5 text-sm focus:outline-none focus:ring-1 focus:ring-orange-500 focus:border-orange-500 transition-all" />
                                        @error('check_date')
                                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>
                                <button type="submit" 
                                    :disabled="isReceivingExceeded"
                                    class="w-full py-3 rounded-lg text-white font-bold text-sm shadow-md transition-all mt-2 disabled:opacity-50 disabled:cursor-not-allowed hover:opacity-95" 
                                    :class="isReceivingExceeded ? 'opacity-50 cursor-not-allowed' : ''"
                                    style="background: linear-gradient(135deg, #ea580c 0%, #f97316 100%)">
                                    Issue Refund
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
                <h3 class="text-lg font-bold text-ink-heading mb-2">Please select records</h3>
                <p class="text-sm text-ink-muted mb-6">Please select at least one record to print.</p>
                <button @click="showPrintMessage = false" class="w-full bg-[#5542ff] hover:bg-[#4736d6] text-white font-bold py-2 px-4 rounded-lg transition-colors">OK</button>
            </div>
        </div>
    </div>
</x-app-layout>
