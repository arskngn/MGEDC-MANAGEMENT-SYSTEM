<?php if (isset($component)) { $__componentOriginal9ac128a9029c0e4701924bd2d73d7f54 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54 = $attributes; } ?>
<?php $component = App\View\Components\AppLayout::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('app-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\AppLayout::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
    <style>
    /* Mobile-first responsive styles */
    @media (max-width: 768px) {
        /* Prevent iOS auto-zoom */
        input, select, textarea {
            font-size: 16px !important;
        }
        
        /* Larger touch targets */
        button, input[type="button"], input[type="submit"] {
            min-height: 44px;
            padding: 12px;
        }
        
        /* Improve form spacing */
        .flex-wrap {
            gap: 0.5rem !important;
        }
    }
    
    /* Mobile responsive table */
    @media (max-width: 1024px) {
        .hidden-mobile-table {
            display: none;
        }
    }
    </style>
    <script>
    function showSalesModal(title, subtitle, options, onClose) {
        var existing = document.getElementById('_sales_action_modal');
        if (existing) existing.remove();
        var overlay = document.createElement('div');
        overlay.id = '_sales_action_modal';
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

    function showSetlistNoteModal(onConfirm) {
        var existing = document.getElementById('_sales_setlist_note_modal');
        if (existing) existing.remove();

        var overlay = document.createElement('div');
        overlay.id = '_sales_setlist_note_modal';
        overlay.style.cssText = 'position:fixed;inset:0;z-index:99999;display:flex;align-items:center;justify-content:center;background:rgba(0,0,0,0.5);backdrop-filter:blur(2px)';

        var card = document.createElement('div');
        card.style.cssText = 'background:#fff;border-radius:14px;box-shadow:0 20px 60px rgba(0,0,0,0.25);max-width:420px;width:calc(100% - 2rem);padding:28px;position:relative';

        var header = document.createElement('div');
        header.style.cssText = 'display:flex;justify-content:space-between;align-items:center;margin-bottom:8px';

        var titleEl = document.createElement('h3');
        titleEl.style.cssText = 'font-size:1.05rem;font-weight:700;color:#111827;margin:0';
        titleEl.textContent = 'Setlist Note';

        var closeBtn = document.createElement('button');
        closeBtn.style.cssText = 'background:none;border:none;cursor:pointer;color:#9ca3af;font-size:1.4rem;line-height:1;padding:2px 6px';
        closeBtn.innerHTML = '&times;';

        header.appendChild(titleEl);
        header.appendChild(closeBtn);
        card.appendChild(header);

        var sub = document.createElement('p');
        sub.style.cssText = 'font-size:0.82rem;color:#6b7280;margin:0 0 16px';
        sub.textContent = 'Add an optional note to display below the company name on the setlist.';
        card.appendChild(sub);

        var textarea = document.createElement('textarea');
        textarea.style.cssText = 'width:100%;min-height:96px;padding:12px;border:1px solid #d1d5db;border-radius:8px;font-size:0.875rem;color:#111827;resize:vertical;outline:none';
        textarea.placeholder = 'Enter note (optional)';
        textarea.maxLength = 500;
        card.appendChild(textarea);

        var footer = document.createElement('div');
        footer.style.cssText = 'margin-top:20px;display:flex;justify-content:flex-end;gap:10px';

        var cancelBtn = document.createElement('button');
        cancelBtn.style.cssText = 'padding:8px 20px;border:1px solid #d1d5db;border-radius:8px;background:#fff;color:#374151;font-size:0.875rem;font-weight:600;cursor:pointer';
        cancelBtn.textContent = 'Cancel';

        var confirmBtn = document.createElement('button');
        confirmBtn.style.cssText = 'padding:8px 20px;border:none;border-radius:8px;background:#5542ff;color:#fff;font-size:0.875rem;font-weight:600;cursor:pointer';
        confirmBtn.textContent = 'Print Setlist';

        footer.appendChild(cancelBtn);
        footer.appendChild(confirmBtn);
        card.appendChild(footer);
        overlay.appendChild(card);
        document.body.appendChild(overlay);

        function close() {
            overlay.remove();
        }

        overlay.addEventListener('click', function(e) {
            if (e.target === overlay) close();
        });
        closeBtn.addEventListener('click', close);
        cancelBtn.addEventListener('click', close);
        confirmBtn.addEventListener('click', function() {
            var note = textarea.value.trim();
            close();
            onConfirm(note);
        });

        setTimeout(function() {
            textarea.focus();
        }, 50);
    }
    </script>
    <div
        @sale:created.window="handleRealtimeSale($event.detail)"
        x-data="{
            selectedItems: [],
            selectAll: false,
            showPrintMessage: false,
            liveSales: [],
            formatMoney(amount) {
                return '₱' + (parseFloat(amount) || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            },
            handleRealtimeSale(sale) {
                // Prevent duplicate addition
                if (this.liveSales.some(s => s.id === sale.id)) return;
                
                // Add to live sales array at top
                this.liveSales.unshift(sale);

                // Flash document title briefly
                const origTitle = document.title;
                document.title = '🔔 New Sale: ' + sale.invoice_no;
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
                    showSalesModal('No Records Selected', 'Please select at least one sale to print.', [], null);
                    return;
                }
                const ids = this.selectedItems.join(',');
                this.bulkOpen = false;
                showSalesModal('Print Options', 'How would you like to print the selected sales?', [
                    { label: 'Batch Print', desc: 'Print each sale as a separate invoice on its own page.', handler: function() { window.open('/sales/export/pdf?ids=' + ids + '&print=true&type=batch', '_blank'); } },
                    { label: 'Total Print', desc: 'Print all selected sales combined in a single table.', handler: function() { window.open('/sales/export/pdf?ids=' + ids + '&print=true&type=total', '_blank'); } },
                ], null);
            },
            downloadSelected() {
                if (this.selectedItems.length === 0) {
                    showSalesModal('No Records Selected', 'Please select at least one sale to download.', [], null);
                    return;
                }
                const ids = this.selectedItems.join(',');
                this.bulkOpen = false;
                showSalesModal('Download Options', 'How would you like to download the selected sales?', [
                    { label: 'Batch Download', desc: 'Download a PDF containing each sale on a separate page.', handler: function() { window.location.href = '/sales/export/pdf?ids=' + ids + '&type=batch'; } },
                    { label: 'Total Download', desc: 'Download a single consolidated invoice PDF.', handler: function() { window.location.href = '/sales/export/pdf?ids=' + ids + '&type=total'; } },
                ], null);
            },
            printLoadingList() {
                if (this.selectedItems.length === 0) {
                    showSalesModal('No Records Selected', 'Please select at least one sale to print.', [], null);
                    return;
                }
                const ids = this.selectedItems.join(',');
                this.bulkOpen = false;
                showSetlistNoteModal(function(note) {
                    let url = `/sales/export/pdf?ids=${ids}&print=true&type=loading_list`;
                    if (note) {
                        url += `&note=${encodeURIComponent(note)}`;
                    }
                    window.open(url, '_blank');
                });
            },
            downloadLoadingList() {
                if (this.selectedItems.length === 0) {
                    showSalesModal('No Records Selected', 'Please select at least one sale to download.', [], null);
                    return;
                }
                const ids = this.selectedItems.join(',');
                const url = `/sales/export/pdf?ids=${ids}&type=loading_list`;
                window.location.href = url;
                this.bulkOpen = false;
            },
            openMoreId: null,
            dropdownTop: '0px',
            dropdownRight: '0px',
            bulkOpen: false,
            paymentOpen: false,
            paymentSale: null,
            paymentMethod: 'cash',
            checkNumber: '',
            checkDate: '',
            receivingAmount: '',
            get isReceivingExceeded() {
                if (!this.paymentSale || !this.receivingAmount) return false;
                const amt = parseFloat(this.receivingAmount) || 0;
                const due = parseFloat(this.paymentSale.due) || 0;
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
            openPayment(s) {
                this.paymentSale = s;
                this.paymentOpen = true;
                this.paymentMethod = 'cash';
                this.checkNumber = '';
                this.checkDate = '';
                this.receivingAmount = '';
                this.openMoreId = null;
                this.$nextTick(() => {
                    const inp = this.$el.querySelector('.payment-receiving-input');
                    if (inp) { inp.max = s.due; }
                });
            },
            approvalOpen: false,
            approvalSaleId: null,
            approvalAction: '',
            approvalSaleInvoice: '',
            openApprovalModal(id, action, invoice) {
                this.approvalSaleId = id;
                this.approvalAction = action;
                this.approvalSaleInvoice = invoice;
                this.approvalOpen = true;
            },
            cancelOpen: false,
            cancelSaleUrl: '',
            cancelSaleInvoice: '',
            deliveryModalOpen: false,
            deliverySale: null,
            deliveryStatus: 'in_truck',
            deliveryNote: '',
            openDeliveryModal(sale) {
                this.deliverySale = sale;
                this.deliveryStatus = sale.delivery_status && sale.delivery_status !== 'not_dispatched' ? sale.delivery_status : 'in_truck';
                this.deliveryNote = sale.delivery_note || '';
                this.openMoreId = null;
                this.deliveryModalOpen = true;
            },
            bulkDeliveryOpen: false,
            bulkDeliveryStatus: 'in_truck',
            bulkDeliveryNote: '',
            bulkDeliveryLoading: false,
            openBulkDeliveryModal() {
                if (this.selectedItems.length === 0) {
                    showSalesModal('No Records Selected', 'Please select at least one sale to update delivery status.', [], null);
                    return;
                }
                this.bulkDeliveryStatus = 'in_truck';
                this.bulkDeliveryNote = '';
                this.bulkDeliveryLoading = false;
                this.bulkOpen = false;
                this.bulkDeliveryOpen = true;
            },
            submitBulkDelivery(form) {
                if (this.selectedItems.length === 0) return;
                this.bulkDeliveryLoading = true;
                const data = new FormData(form);
                fetch(form.action, {
                    method: 'POST',
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    body: data,
                })
                .then(res => {
                    if (res.redirected || res.ok) {
                        window.location.reload();
                    } else {
                        return res.text().then(text => {
                            this.bulkDeliveryLoading = false;
                            alert('Error updating delivery status. Please try again.');
                        });
                    }
                })
                .catch(() => {
                    this.bulkDeliveryLoading = false;
                    alert('Network error. Please try again.');
                });
            }
        }"
        @keydown.escape.window="paymentOpen = false; openMoreId = null; bulkOpen = false; approvalOpen = false; deliveryModalOpen = false; bulkDeliveryOpen = false;"
        @keydown.window.ctrl.p.prevent="printSelected()"
        @keydown.window.ctrl.a.prevent="selectAll = true; toggleSelectAll()"
        @scroll.window="if (openMoreId) openMoreId = null"
        class="space-y-4 px-3 sm:px-0"
    >
        <div class="flex flex-col gap-3 <?php if(auth()->user()->hasRole('salesperson')): ?>lg:flex-row lg:items-center lg:justify-between <?php else: ?> sm:flex-row sm:items-center sm:justify-between <?php endif; ?>">
            <div class="flex items-center gap-3">
                <h2 class="text-lg sm:text-xl font-bold text-[#0a1233] dark:text-gray-100 shrink-0">Sales</h2>
                
                <div class="<?php if(auth()->user()->hasRole('salesperson')): ?>lg:hidden <?php else: ?> sm:hidden <?php endif; ?> inline-flex p-1 bg-gray-100 dark:bg-gray-800 rounded-xl border border-gray-200/80 dark:border-gray-700">
                    <a href="<?php echo e(route('sales.all')); ?>" class="px-3 py-1 rounded-lg text-xs font-bold transition-all <?php echo e(request()->routeIs('sales.all') || request()->routeIs('sales.index') ? 'bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-xs' : 'text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white'); ?>">
                        Sales Orders
                    </a>
                    <a href="<?php echo e(route('sales.return')); ?>" class="px-3 py-1 rounded-lg text-xs font-bold transition-all <?php echo e(request()->routeIs('sales.return*') ? 'bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-xs' : 'text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white'); ?>">
                        Returns
                    </a>
                </div>
            </div>

            <div class="flex flex-col <?php if(auth()->user()->hasRole('salesperson')): ?>lg:flex-row lg:items-center <?php else: ?> sm:flex-row sm:items-center <?php endif; ?> gap-2.5 w-full <?php if(auth()->user()->hasRole('salesperson')): ?>lg:w-auto <?php else: ?> sm:w-auto <?php endif; ?>">
                <form
                    method="GET"
                    action="<?php echo e(route('sales.all')); ?>"
                    x-data="{
                        ...purchaseDateRange({ dateFrom: <?php echo \Illuminate\Support\Js::from(request('date_from'))->toHtml() ?>, dateTo: <?php echo \Illuminate\Support\Js::from(request('date_to'))->toHtml() ?> }),
                        mobileFiltersOpen: localStorage.getItem('sales_mobile_filters_open') === 'true' || <?php echo \Illuminate\Support\Js::from(request()->hasAny(['payment_status', 'delivery_status', 'salesperson_id', 'route_id', 'date_from', 'date_to']))->toHtml() ?>,
                        toggleFilters() {
                            this.mobileFiltersOpen = !this.mobileFiltersOpen;
                            localStorage.setItem('sales_mobile_filters_open', this.mobileFiltersOpen);
                        }
                    }"
                    x-ref="filterForm"
                    @keydown.escape.window="escapeClose()"
                    class="w-full <?php if(auth()->user()->hasRole('salesperson')): ?>lg:flex lg:flex-row lg:items-center lg:gap-2 lg:w-auto <?php else: ?> sm:flex sm:flex-row sm:items-center sm:gap-2 sm:w-auto <?php endif; ?>"
                >
                    <input type="hidden" name="date_from" x-model="dateFrom" />
                    <input type="hidden" name="date_to" x-model="dateTo" />

                    
                    <div class="flex items-center gap-2 <?php if(auth()->user()->hasRole('salesperson')): ?>lg:contents <?php else: ?> sm:contents <?php endif; ?>">
                        
                        <div class="relative flex flex-1 <?php if(auth()->user()->hasRole('salesperson')): ?>lg:w-[240px] lg:flex-none <?php else: ?> sm:w-[240px] sm:flex-none <?php endif; ?>">
                            <input
                                type="text"
                                name="search"
                                value="<?php echo e(request('search')); ?>"
                                placeholder="Search..."
                                class="w-full pl-3 pr-8 py-2 border border-[#5542ff]/40 dark:border-gray-600 rounded-l-xl sm:rounded-l-md focus:outline-none focus:ring-1 focus:ring-[#5542ff] focus:border-[#5542ff] text-sm bg-white dark:bg-gray-900 text-ink-heading placeholder-gray-400 dark:placeholder-gray-500"
                            />
                            <?php if(request('search')): ?>
                                <a href="<?php echo e(request()->fullUrlWithQuery(['search' => null])); ?>" class="absolute right-9 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 text-xs px-1 h-4 w-4 flex items-center justify-center" title="Clear search">&#x2715;</a>
                            <?php endif; ?>
                            <button type="submit" class="px-3 gradient-bg text-white rounded-r-xl sm:rounded-r-md hover:opacity-90 transition-colors flex items-center justify-center shrink-0" title="Search">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                            </button>
                        </div>

                        
                        <?php
                            $activeFilters = (int)(bool)request('date_from') + (int)(bool)request('payment_status') + (int)(bool)request('delivery_status') + (int)(bool)request('salesperson_id') + (int)(bool)request('route_id');
                        ?>
                        <button type="button" @click="toggleFilters()"
                                class="<?php if(auth()->user()->hasRole('salesperson')): ?>lg:hidden <?php else: ?> sm:hidden <?php endif; ?> relative flex items-center gap-1.5 px-3 py-2 rounded-xl border text-sm font-semibold transition-all shrink-0 cursor-pointer shadow-xs"
                                :class="mobileFiltersOpen ? 'bg-[#5542ff] text-white border-[#5542ff]' : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 border-gray-300 dark:border-gray-600'">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z"/></svg>
                            <span>Filter</span>
                            <?php if($activeFilters > 0): ?>
                                <span class="absolute -top-1.5 -right-1.5 h-4 w-4 flex items-center justify-center rounded-full bg-red-500 text-white text-[9px] font-black"><?php echo e($activeFilters); ?></span>
                            <?php endif; ?>
                        </button>
                    </div>

                    
                    <div x-show="mobileFiltersOpen" x-cloak class="<?php if(auth()->user()->hasRole('salesperson')): ?>lg:!block lg:flex lg:flex-row lg:items-center lg:gap-2 <?php else: ?> sm:!block sm:flex sm:flex-row sm:items-center sm:gap-2 <?php endif; ?>" style="display:none">
                        <div class="grid grid-cols-2 gap-2 mt-2 <?php if(auth()->user()->hasRole('salesperson')): ?>lg:mt-0 lg:flex lg:flex-row lg:gap-2 <?php else: ?> sm:mt-0 sm:flex sm:flex-row sm:gap-2 <?php endif; ?>">
                            <div class="col-span-2 <?php if(auth()->user()->hasRole('salesperson')): ?>lg:col-span-1 lg:w-auto <?php else: ?> sm:col-span-1 sm:w-auto <?php endif; ?>">
                                <?php echo $__env->make('components.date-range-picker', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                            </div>

                            <div class="relative w-full <?php if(auth()->user()->hasRole('salesperson')): ?>lg:w-auto <?php else: ?> sm:w-auto <?php endif; ?>">
                                <select name="payment_status" class="w-full <?php if(auth()->user()->hasRole('salesperson')): ?>lg:w-auto <?php else: ?> sm:w-auto <?php endif; ?> text-sm border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-900 pl-3 pr-8 py-2 text-gray-700 dark:text-gray-100 focus:ring-1 focus:ring-[#5542ff] focus:border-[#5542ff] appearance-none cursor-pointer shadow-2xs transition-colors" onchange="this.form.submit()">
                                    <option value="">All Payment</option>
                                    <option value="paid" <?php if(request('payment_status') === 'paid'): echo 'selected'; endif; ?>>Paid</option>
                                    <option value="partial" <?php if(request('payment_status') === 'partial'): echo 'selected'; endif; ?>>Partial</option>
                                    <option value="due" <?php if(request('payment_status') === 'due'): echo 'selected'; endif; ?>>Due</option>
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-2.5 text-ink-muted">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </div>
                            </div>

                            <div class="relative w-full <?php if(auth()->user()->hasRole('salesperson')): ?>lg:w-auto <?php else: ?> sm:w-auto <?php endif; ?>">
                                <select name="delivery_status" class="w-full <?php if(auth()->user()->hasRole('salesperson')): ?>lg:w-auto <?php else: ?> sm:w-auto <?php endif; ?> text-sm border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-900 pl-3 pr-8 py-2 text-gray-700 dark:text-gray-100 focus:ring-1 focus:ring-[#5542ff] focus:border-[#5542ff] appearance-none cursor-pointer shadow-2xs transition-colors" onchange="this.form.submit()">
                                    <option value="">All Delivery</option>
                                    <option value="not_dispatched" <?php if(request('delivery_status') === 'not_dispatched'): echo 'selected'; endif; ?>>Not Dispatched</option>
                                    <option value="in_truck" <?php if(request('delivery_status') === 'in_truck'): echo 'selected'; endif; ?>>In Truck</option>
                                    <option value="out_for_delivery" <?php if(request('delivery_status') === 'out_for_delivery'): echo 'selected'; endif; ?>>Out for Delivery</option>
                                    <option value="delivered" <?php if(request('delivery_status') === 'delivered'): echo 'selected'; endif; ?>>Delivered</option>
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-2.5 text-ink-muted">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </div>
                            </div>

                            <?php if(auth()->user()->hasPermission('All Sales') && !auth()->user()->hasRole('salesperson')): ?>
                                <div class="relative w-full <?php if(auth()->user()->hasRole('salesperson')): ?>lg:w-auto <?php else: ?> sm:w-auto <?php endif; ?>">
                                    <select name="salesperson_id" class="w-full <?php if(auth()->user()->hasRole('salesperson')): ?>lg:w-auto <?php else: ?> sm:w-auto <?php endif; ?> text-sm border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-900 pl-3 pr-8 py-2 text-gray-700 dark:text-gray-100 focus:ring-1 focus:ring-[#5542ff] focus:border-[#5542ff] appearance-none cursor-pointer shadow-2xs transition-colors" onchange="this.form.submit()">
                                        <option value="">All Salespersons</option>
                                        <?php $__currentLoopData = $salespersons; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sp): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <option value="<?php echo e($sp->id); ?>" <?php if(request('salesperson_id') == $sp->id): echo 'selected'; endif; ?>><?php echo e($sp->name); ?></option>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </select>
                                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-2.5 text-ink-muted">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                        </svg>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <?php if($routes->isNotEmpty()): ?>
                                <div class="relative w-full <?php if(auth()->user()->hasRole('salesperson')): ?>lg:w-auto <?php else: ?> sm:w-auto <?php endif; ?>">
                                    <select name="route_id" class="w-full <?php if(auth()->user()->hasRole('salesperson')): ?>lg:w-auto <?php else: ?> sm:w-auto <?php endif; ?> text-sm border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-900 pl-3 pr-8 py-2 text-gray-700 dark:text-gray-100 focus:ring-1 focus:ring-[#5542ff] focus:border-[#5542ff] appearance-none cursor-pointer shadow-2xs transition-colors" onchange="this.form.submit()">
                                        <option value="">All Routes</option>
                                        <?php $__currentLoopData = $routes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $route): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <option value="<?php echo e($route->id); ?>" <?php if(request('route_id') == $route->id): echo 'selected'; endif; ?>><?php echo e($route->display_name); ?></option>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </select>
                                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-2.5 text-ink-muted">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                        </svg>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </form>

                <div class="flex items-center gap-2 shrink-0 justify-end">
                    <?php if(auth()->user()->hasPermission('Create Sale')): ?>
                        <a href="<?php echo e(route('sales.create')); ?>" class="inline-flex items-center justify-center px-3 sm:px-4 py-2 bg-white dark:bg-gray-800 border border-[#5542ff] text-[#5542ff] dark:text-gray-100 dark:border-gray-600 rounded-xl sm:rounded-md hover:bg-[#5542ff]/5 dark:hover:bg-gray-700 transition-colors text-sm font-semibold whitespace-nowrap">
                            <svg class="h-4 w-4 sm:mr-1.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                            <span class="hidden sm:inline">Add New</span>
                        </a>
                    <?php endif; ?>

                    <?php if(auth()->user()->hasPermission('Download Sales PDF') || auth()->user()->hasPermission('Download Sales CSV') || auth()->user()->hasPermission('Update Sale Delivery Status')): ?>
                        <div class="relative inline-flex">
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
                                class="absolute right-0 mt-1 w-52 bg-white dark:bg-gray-800 rounded-xl shadow-xl border border-gray-100 dark:border-gray-700 z-[200] py-1"
                            >
                                <?php if(auth()->user()->hasPermission('Download Sales PDF')): ?>
                                    <button type="button" @click="downloadSelected()" class="w-full flex items-center px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 text-left">
                                        <svg class="h-4 w-4 mr-2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>
                                        Download Selected
                                    </button>
                                <?php endif; ?>
                                <?php if(!auth()->user()->hasRole('salesperson')): ?>
                                <button type="button" @click="printSelected()" class="w-full flex items-center px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 text-left">
                                    <svg class="h-4 w-4 mr-2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" /></svg>
                                    Print Selected
                                </button>
                                <?php endif; ?>
                                <?php if(!auth()->user()->hasRole('salesperson')): ?>
                                <button type="button" @click="downloadLoadingList()" class="w-full flex items-center px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 text-left border-t border-gray-100 dark:border-gray-700">
                                    <svg class="h-4 w-4 mr-2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" /></svg>
                                    Download Setlist
                                </button>
                                <?php endif; ?>
                                <?php if(!auth()->user()->hasRole('salesperson')): ?>
                                <button type="button" @click="printLoadingList()" class="w-full flex items-center px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 text-left border-b border-gray-100 dark:border-gray-700">
                                    <svg class="h-4 w-4 mr-2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" /></svg>
                                    Print Setlist
                                </button>
                                <?php endif; ?>
                                <?php if(auth()->user()->hasPermission('Update Sale Delivery Status')): ?>
                                    <button type="button" @click="openBulkDeliveryModal()" class="w-full flex items-center px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 text-left border-t border-gray-100 dark:border-gray-700">
                                        <svg class="h-4 w-4 mr-2 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zm12 0a2 2 0 11-4 0 2 2 0 014 0M1 3h15v13H1zm15 6h4l3 4v4h-7V9z"/></svg>
                                        Bulk Update Delivery
                                    </button>
                                <?php endif; ?>
                                <?php if(auth()->user()->hasPermission('Download Sales CSV')): ?>
                                    <a href="<?php echo e(route('sales.export.csv', request()->query())); ?>" class="flex items-center px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700">
                                        <svg class="h-4 w-4 mr-2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>
                                        Download CSV
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <?php if(request()->hasAny(['search', 'address', 'date_from', 'date_to', 'payment_status', 'delivery_status', 'salesperson_id', 'route_id'])): ?>
            <div class="flex flex-wrap items-center gap-2 px-1 py-1">
                <span class="text-xs font-semibold text-ink-muted">Active Filters:</span>
                
                <?php if(request('search')): ?>
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium bg-[#5542ff]/10 text-[#5542ff] dark:bg-[#5542ff]/20 dark:text-indigo-300 border border-[#5542ff]/20">
                        Search: "<?php echo e(request('search')); ?>"
                        <a href="<?php echo e(request()->fullUrlWithQuery(['search' => null])); ?>" class="hover:text-red-500 font-bold ml-1" title="Remove search filter">&times;</a>
                    </span>
                <?php endif; ?>

                <?php if(request('date_from') || request('date_to')): ?>
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium bg-[#5542ff]/10 text-[#5542ff] dark:bg-[#5542ff]/20 dark:text-indigo-300 border border-[#5542ff]/20">
                        Date: <?php echo e(request('date_from') ?? 'Start'); ?> &rarr; <?php echo e(request('date_to') ?? 'End'); ?>

                        <a href="<?php echo e(request()->fullUrlWithQuery(['date_from' => null, 'date_to' => null])); ?>" class="hover:text-red-500 font-bold ml-1" title="Remove date filter">&times;</a>
                    </span>
                <?php endif; ?>

                <?php if(request('payment_status')): ?>
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium bg-[#5542ff]/10 text-[#5542ff] dark:bg-[#5542ff]/20 dark:text-indigo-300 border border-[#5542ff]/20">
                        Payment: <?php echo e(ucfirst(request('payment_status'))); ?>

                        <a href="<?php echo e(request()->fullUrlWithQuery(['payment_status' => null])); ?>" class="hover:text-red-500 font-bold ml-1" title="Remove payment status filter">&times;</a>
                    </span>
                <?php endif; ?>

                <?php if(request('delivery_status')): ?>
                    <?php
                        $deliveryLabels = [
                            'not_dispatched' => 'Not Dispatched',
                            'in_truck' => 'In Truck',
                            'out_for_delivery' => 'Out for Delivery',
                            'delivered' => 'Delivered',
                            'returned' => 'Returned',
                            'failed_delivery' => 'Failed Delivery',
                        ];
                        $dlName = $deliveryLabels[request('delivery_status')] ?? request('delivery_status');
                    ?>
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium bg-[#5542ff]/10 text-[#5542ff] dark:bg-[#5542ff]/20 dark:text-indigo-300 border border-[#5542ff]/20">
                        Delivery: <?php echo e($dlName); ?>

                        <a href="<?php echo e(request()->fullUrlWithQuery(['delivery_status' => null])); ?>" class="hover:text-red-500 font-bold ml-1" title="Remove delivery status filter">&times;</a>
                    </span>
                <?php endif; ?>

                <?php if(request('salesperson_id')): ?>
                    <?php $spName = $salespersons->firstWhere('id', request('salesperson_id'))?->name; ?>
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium bg-[#5542ff]/10 text-[#5542ff] dark:bg-[#5542ff]/20 dark:text-indigo-300 border border-[#5542ff]/20">
                        Salesperson: <?php echo e($spName ?? 'ID '.request('salesperson_id')); ?>

                        <a href="<?php echo e(request()->fullUrlWithQuery(['salesperson_id' => null])); ?>" class="hover:text-red-500 font-bold ml-1" title="Remove salesperson filter">&times;</a>
                    </span>
                <?php endif; ?>

                <?php if(request('route_id')): ?>
                    <?php $routeName = $routes->firstWhere('id', request('route_id'))?->display_name; ?>
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium bg-[#5542ff]/10 text-[#5542ff] dark:bg-[#5542ff]/20 dark:text-indigo-300 border border-[#5542ff]/20">
                        Route: <?php echo e($routeName ?? 'ID '.request('route_id')); ?>

                        <a href="<?php echo e(request()->fullUrlWithQuery(['route_id' => null])); ?>" class="hover:text-red-500 font-bold ml-1" title="Remove route filter">&times;</a>
                    </span>
                <?php endif; ?>

                <?php if(request('address')): ?>
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium bg-[#5542ff]/10 text-[#5542ff] dark:bg-[#5542ff]/20 dark:text-indigo-300 border border-[#5542ff]/20">
                        Address: <?php echo e(request('address')); ?>

                        <a href="<?php echo e(request()->fullUrlWithQuery(['address' => null])); ?>" class="hover:text-red-500 font-bold ml-1" title="Remove address filter">&times;</a>
                    </span>
                <?php endif; ?>

                <a href="<?php echo e(route('sales.all')); ?>" class="text-xs text-red-600 dark:text-red-400 hover:underline font-semibold ml-1">
                    Reset All Filters
                </a>
            </div>
        <?php endif; ?>

        <?php if(session('success')): ?>
            <div class="rounded-lg bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-700 text-green-800 dark:text-green-300 px-4 py-3 text-sm"><?php echo e(session('success')); ?></div>
        <?php endif; ?>
        <?php if(session('error')): ?>
            <div class="rounded-lg bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-700 text-red-800 dark:text-red-300 px-4 py-3 text-sm"><?php echo e(session('error')); ?></div>
        <?php endif; ?>

        
        <div class="<?php if(auth()->user()->hasRole('salesperson')): ?>lg:hidden <?php else: ?> sm:hidden <?php endif; ?> space-y-3">
            <?php $canSelect = auth()->user()->hasPermission('Download Sales PDF') || auth()->user()->hasPermission('Download Sales CSV') || auth()->user()->hasPermission('Update Sale Delivery Status'); ?>
            <?php if($canSelect): ?>
                <div class="flex items-center justify-between px-3 py-2 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm">
                    <label class="flex items-center gap-2 cursor-pointer select-none text-xs font-bold text-gray-700 dark:text-gray-200">
                        <input type="checkbox" x-model="selectAll" @change="toggleSelectAll" class="rounded border-gray-300 text-[#5542ff] focus:ring-[#5542ff]">
                        <span>Select All</span>
                    </label>
                    <span class="text-xs font-semibold" :class="selectedItems.length > 0 ? 'text-[#5542ff]' : 'text-gray-400'" x-text="selectedItems.length > 0 ? selectedItems.length + ' selected' : 'None selected'"></span>
                </div>
            <?php endif; ?>

            
            <template x-for="liveSale in liveSales" :key="'mob-live-' + liveSale.id">
                <div class="bg-indigo-50/80 dark:bg-indigo-950/50 rounded-2xl border-2 border-[#5542ff]/30 p-4 shadow-md space-y-3 transition-colors animate-pulse-once">
                    <div class="flex items-start gap-3 border-b border-gray-100 dark:border-gray-700 pb-3">
                        <?php if(!auth()->user()->hasRole('salesperson')): ?>
                            <input type="checkbox" :value="liveSale.id" x-model="selectedItems" class="row-checkbox rounded border-gray-300 text-[#5542ff] focus:ring-[#5542ff] mt-0.5 shrink-0">
                        <?php endif; ?>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between gap-2">
                                <div class="flex items-center gap-2">
                                    <a :href="liveSale.show_url" class="font-black text-[#5542ff] dark:text-gray-100 text-base leading-tight hover:underline" x-text="liveSale.invoice_no"></a>
                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold bg-[#5542ff] text-white">NEW</span>
                                </div>
                                <div class="flex items-center gap-1.5 flex-wrap">
                                    <template x-if="liveSale.status === 'pending'">
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 border border-gray-200 dark:border-gray-600">Pending</span>
                                    </template>
                                    <template x-if="liveSale.due_amount <= 0.009 && liveSale.status !== 'pending'">
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300 border border-green-200 dark:border-green-800">Paid</span>
                                    </template>
                                </div>
                            </div>
                            <p class="text-xs text-ink-muted mt-0.5" x-text="liveSale.sale_date"></p>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-2 text-xs">
                        <div>
                            <span class="text-gray-400 text-[10px] uppercase font-bold tracking-wide block">Customer</span>
                            <span class="text-ink-heading font-bold block truncate" x-text="liveSale.customer_name"></span>
                            <span class="text-ink-muted block text-[11px]" x-text="liveSale.customer_phone || '—'"></span>
                        </div>
                        <div>
                            <span class="text-gray-400 text-[10px] uppercase font-bold tracking-wide block">Salesperson</span>
                            <span class="text-ink-body font-medium block truncate" x-text="liveSale.salesperson_name"></span>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 p-2.5 rounded-xl bg-white/60 dark:bg-gray-800/60 text-xs">
                        <div>
                            <span class="text-[10px] text-gray-400 uppercase font-bold tracking-wide block">Receivable</span>
                            <span class="text-xs font-bold text-ink-body" x-text="formatMoney(liveSale.receivable_amount)"></span>
                        </div>
                        <div>
                            <span class="text-[10px] text-gray-400 uppercase font-bold tracking-wide block">Paid</span>
                            <span class="text-xs font-bold text-green-600 dark:text-green-400" x-text="formatMoney(liveSale.paid_amount)"></span>
                        </div>
                        <div>
                            <span class="text-[10px] text-gray-400 uppercase font-bold tracking-wide block">Due Balance</span>
                            <span class="text-xs font-black" :class="liveSale.due_amount > 0.009 ? 'text-red-600 dark:text-red-400' : 'text-ink-muted'" x-text="formatMoney(liveSale.due_amount)"></span>
                        </div>
                    </div>

                    <div class="flex items-center justify-between pt-2 border-t border-gray-100 dark:border-gray-700 text-xs">
                        <a :href="liveSale.show_url" class="inline-flex items-center text-xs font-bold text-[#5542ff] dark:text-blue-400 hover:underline">
                            View Invoice Details &rarr;
                        </a>
                    </div>
                </div>
            </template>

            <div class="<?php if(auth()->user()->hasRole('salesperson')): ?>md:grid md:grid-cols-2 md:gap-3 space-y-3 md:space-y-0 <?php else: ?> space-y-3 <?php endif; ?>">
            <?php $__empty_1 = true; $__currentLoopData = $sales; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sale): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <?php
                    $phone = $sale->customer->phone;
                    $phoneDisp = $phone ? (\Illuminate\Support\Str::startsWith($phone, '+') ? $phone : "+{$phone}") : '—';
                    $hasReturn = $sale->sale_returns_count > 0;
                    $totalReturnPayable = (float) ($sale->sale_returns_sum_payable_amount ?? 0);
                    $netReceivable = max(0, (float)$sale->receivable_amount - $totalReturnPayable);
                    $netDue = max(0, $netReceivable - (float)$sale->paid_amount);
                ?>
                <div :class="{ 'ring-2 ring-[#5542ff]/40': selectedItems.includes(String('<?php echo e($sale->id); ?>')) || selectedItems.includes(<?php echo e($sale->id); ?>) }"
                     class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden transition-all">

                    
                    <a href="<?php echo e(route('sales.show', $sale)); ?>" class="block p-3.5 space-y-2.5">

                        
                        <div class="flex items-center gap-2.5">
                            <?php if($canSelect): ?>
                                <input type="checkbox" value="<?php echo e($sale->id); ?>" x-model="selectedItems"
                                       class="row-checkbox rounded border-gray-300 text-[#5542ff] focus:ring-[#5542ff] shrink-0"
                                       @click.stop>
                            <?php endif; ?>
                            <span class="font-black text-[#5542ff] dark:text-indigo-300 text-sm leading-none"><?php echo e($sale->invoice_no); ?></span>
                            <span class="text-[10px] text-ink-muted"><?php echo e($sale->sale_date->format('d M Y')); ?></span>
                            <div class="ml-auto flex items-center gap-1 flex-wrap justify-end">
                                
                                <?php if($sale->status === 'pending'): ?>
                                    <span class="px-1.5 py-0.5 rounded text-[9px] font-black bg-gray-100 dark:bg-gray-700 text-ink-muted uppercase tracking-wide">Pending</span>
                                <?php elseif($sale->status === 'rejected'): ?>
                                    <span class="px-1.5 py-0.5 rounded text-[9px] font-black bg-red-100 text-red-600 uppercase tracking-wide">Rejected</span>
                                <?php elseif($sale->status === 'cancelled'): ?>
                                    <span class="px-1.5 py-0.5 rounded text-[9px] font-black bg-orange-100 text-orange-600 uppercase tracking-wide">Cancelled</span>
                                <?php elseif($netDue <= 0.009): ?>
                                    <span class="px-1.5 py-0.5 rounded text-[9px] font-black bg-green-100 text-green-700 uppercase tracking-wide">Paid</span>
                                <?php elseif($sale->paid_amount > 0.009): ?>
                                    <span class="px-1.5 py-0.5 rounded text-[9px] font-black bg-amber-100 text-amber-700 uppercase tracking-wide">Partial</span>
                                <?php else: ?>
                                    <span class="px-1.5 py-0.5 rounded text-[9px] font-black bg-red-100 text-red-600 uppercase tracking-wide">Due</span>
                                <?php endif; ?>
                                <?php if($hasReturn): ?>
                                    <span class="px-1.5 py-0.5 rounded text-[9px] font-black bg-orange-100 text-orange-600 uppercase tracking-wide">Return</span>
                                <?php endif; ?>
                            </div>
                        </div>

                        
                        <div class="flex items-start justify-between gap-2">
                            <div class="min-w-0">
                                <p class="font-bold text-ink-heading text-sm leading-tight truncate"><?php echo e($sale->customer->name); ?></p>
                                <p class="text-[11px] text-ink-muted leading-tight"><?php echo e($phoneDisp); ?></p>
                            </div>
                            <?php if($sale->salesperson): ?>
                                <span class="text-[11px] text-ink-muted shrink-0 pt-0.5"><?php echo e($sale->salesperson->name); ?></span>
                            <?php endif; ?>
                        </div>

                        
                        <div class="grid grid-cols-3 rounded-xl overflow-hidden border border-gray-100 dark:border-gray-700 divide-x divide-gray-100 dark:divide-gray-700 bg-gray-50/60 dark:bg-gray-800/40 text-[10px]">
                            <div class="px-2.5 py-2">
                                <span class="text-gray-400 dark:text-gray-400 uppercase font-bold tracking-wider block text-[9px] leading-tight mb-0.5">Receivable</span>
                                <span class="font-extrabold text-ink-heading text-xs truncate block"><?php echo e(formatCurrency($netReceivable)); ?></span>
                            </div>
                            <div class="px-2.5 py-2">
                                <span class="text-gray-400 dark:text-gray-400 uppercase font-bold tracking-wider block text-[9px] leading-tight mb-0.5">Paid</span>
                                <span class="font-extrabold text-green-600 dark:text-green-400 text-xs truncate block"><?php echo e(formatCurrency($sale->paid_amount)); ?></span>
                            </div>
                            <div class="px-2.5 py-2 <?php echo e($netDue > 0.009 ? 'bg-red-50/60 dark:bg-red-950/30' : ''); ?>">
                                <span class="text-gray-400 dark:text-gray-400 uppercase font-bold tracking-wider block text-[9px] leading-tight mb-0.5">Due</span>
                                <span class="font-extrabold text-xs truncate block <?php echo e($netDue > 0.009 ? 'text-red-600 dark:text-red-400' : 'text-ink-muted'); ?>">
                                    <?php echo e(formatCurrency($netDue)); ?>

                                </span>
                            </div>
                        </div>

                        
                        <?php if($sale->status === 'confirmed'): ?>
                            <?php $dc = $sale->delivery_status_color; ?>
                            <div class="flex items-center gap-2">
                                <?php if(auth()->user()->hasPermission('Update Sale Delivery Status')): ?>
                                    <button type="button"
                                            @click.stop.prevent="openDeliveryModal({ id: <?php echo e($sale->id); ?>, invoice: <?php echo \Illuminate\Support\Js::from($sale->invoice_no)->toHtml() ?>, delivery_status: <?php echo \Illuminate\Support\Js::from($sale->delivery_status)->toHtml() ?>, delivery_note: <?php echo \Illuminate\Support\Js::from($sale->delivery_note)->toHtml() ?> })"
                                            class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold border <?php echo e($dc['bg']); ?> <?php echo e($dc['text']); ?> <?php echo e($dc['border']); ?> whitespace-nowrap active:scale-95 transition-all">
                                        <?php if($sale->delivery_status === 'in_truck'): ?>
                                            <svg class="w-2.5 h-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zm12 0a2 2 0 11-4 0 2 2 0 014 0M1 3h15v13H1zm15 6h4l3 4v4h-7V9z"/></svg>
                                        <?php elseif($sale->delivery_status === 'out_for_delivery'): ?>
                                            <svg class="w-2.5 h-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7H4a2 2 0 00-2 2v8a2 2 0 002 2h16a2 2 0 002-2V9a2 2 0 00-2-2zm-9 8H7m2-4H7"/></svg>
                                        <?php elseif($sale->delivery_status === 'delivered'): ?>
                                            <svg class="w-2.5 h-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                        <?php elseif($sale->delivery_status === 'returned'): ?>
                                            <svg class="w-2.5 h-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/></svg>
                                        <?php elseif($sale->delivery_status === 'failed_delivery'): ?>
                                            <svg class="w-2.5 h-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                        <?php else: ?>
                                            <svg class="w-2.5 h-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        <?php endif; ?>
                                        <span><?php echo e($sale->delivery_status_label); ?></span>
                                        <svg class="w-2.5 h-2.5 opacity-50" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                    </button>
                                <?php else: ?>
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold border <?php echo e($dc['bg']); ?> <?php echo e($dc['text']); ?> <?php echo e($dc['border']); ?>"><?php echo e($sale->delivery_status_label); ?></span>
                                <?php endif; ?>

                                <?php
                                    $canStorePaymentSale = auth()->user()->hasPermission('Store Customer Payment') || auth()->user()->hasRole('salesperson');
                                ?>
                                <?php if($canStorePaymentSale && $netDue > 0.009): ?>
                                    <button type="button"
                                            @click.stop.prevent="openPayment({ id: <?php echo e($sale->id); ?>, invoice: <?php echo \Illuminate\Support\Js::from($sale->invoice_no)->toHtml() ?>, customer: <?php echo \Illuminate\Support\Js::from($sale->customer->name)->toHtml() ?>, receivable: <?php echo \Illuminate\Support\Js::from(number_format((float)$sale->receivable_amount, 2, '.', ''))->toHtml() ?>, due: <?php echo \Illuminate\Support\Js::from(number_format($netDue, 2, '.', ''))->toHtml() ?> })"
                                            class="ml-auto inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold border border-green-300 bg-green-50 dark:bg-green-900/30 text-green-700 dark:text-green-300 whitespace-nowrap active:scale-95 transition-all">
                                        <svg class="w-2.5 h-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                        Collect
                                    </button>
                                <?php endif; ?>
                                <?php if(auth()->user()->hasPermission('Cancel Sale') && $sale->delivery_status === \App\Models\Sale::DELIVERY_NOT_DISPATCHED && (float)$sale->paid_amount <= 0): ?>
                                    <button type="button"
                                            @click.stop.prevent="cancelSaleUrl = '<?php echo e(route('sales.cancel', $sale)); ?>'; cancelSaleInvoice = '<?php echo e($sale->invoice_no); ?>'; cancelOpen = true"
                                            class="ml-auto inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold border border-orange-300 bg-orange-50 dark:bg-orange-900/30 text-orange-700 dark:text-orange-300 whitespace-nowrap active:scale-95 transition-all">
                                        <svg class="w-2.5 h-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" /></svg>
                                        Cancel
                                    </button>
                                <?php endif; ?>
                            </div>
                        <?php else: ?>
                            
                            <?php if(auth()->user()->hasPermission('Confirm Sale') && $sale->status === 'pending'): ?>
                                <div class="flex items-center gap-2">
                                    <form method="POST" action="<?php echo e(route('sales.confirm', $sale)); ?>" @click.stop @submit.stop>
                                        <?php echo csrf_field(); ?>
                                        <button type="submit" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold border border-emerald-300 bg-emerald-50 text-emerald-700 active:scale-95 transition-all">
                                            <svg class="w-2.5 h-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                            Confirm
                                        </button>
                                    </form>
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>
                    </a>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-8 text-center">
                    <svg class="h-10 w-10 text-gray-300 dark:text-gray-600 mx-auto mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <p class="text-sm text-gray-400 font-medium">No sales records found.</p>
                </div>
            <?php endif; ?>
            </div>

            <?php if($sales->hasPages()): ?>
                <div class="px-4 py-3 bg-gray-50 dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700">
                    <?php echo e($sales->links()); ?>

                </div>
            <?php endif; ?>
        </div>

        
        <div class="<?php if(auth()->user()->hasRole('salesperson')): ?>hidden lg:block <?php else: ?> hidden sm:block <?php endif; ?> bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-600 dark:border-gray-700 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse min-w-[1100px]">
                    <thead>
                        <tr class="gradient-bg text-white">
                            <?php if($canSelect): ?>
                                <th class="checkbox-column px-4 py-3.5 text-[11px] font-bold uppercase tracking-wide w-10">
                                    <label class="flex items-center gap-2 cursor-pointer">
                                        <input type="checkbox" x-model="selectAll" @change="toggleSelectAll" class="rounded border-gray-300 text-[#5542ff] focus:ring-[#5542ff]">
                                        <span class="sr-only">Select All</span>
                                    </label>
                                </th>
                            <?php endif; ?>
                            <th class="px-4 py-3.5 text-[11px] font-bold uppercase tracking-wide">Invoice No. | Date</th>
                            <th class="px-4 py-3.5 text-[11px] font-bold uppercase tracking-wide">Customer | Mobile</th>
                            <th class="px-4 py-3.5 text-[11px] font-bold uppercase tracking-wide">Salesperson</th>
                            <th class="px-4 py-3.5 text-[11px] font-bold uppercase tracking-wide">Total Amount</th>
                            <th class="px-4 py-3.5 text-[11px] font-bold uppercase tracking-wide">Discount | Receivable</th>
                            <th class="px-4 py-3.5 text-[11px] font-bold uppercase tracking-wide">Return | Net Receivable</th>
                            <th class="px-4 py-3.5 text-[11px] font-bold uppercase tracking-wide">Received | Due</th>
                            <th class="px-4 py-3.5 text-[11px] font-bold uppercase tracking-wide">Delivery</th>
                            <th class="px-4 py-3.5 text-[11px] font-bold uppercase tracking-wide text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        
                        <template x-for="liveSale in liveSales" :key="'live-' + liveSale.id">
                            <tr class="bg-indigo-50/70 dark:bg-indigo-950/40 hover:bg-indigo-100/70 dark:hover:bg-indigo-900/50 transition-all duration-500 cursor-pointer animate-pulse-once"
                                @click="if($event.target.closest('.checkbox-column, .text-right, .status-actions')) return; window.location.href = liveSale.show_url">
                                <?php if($canSelect): ?>
                                    <td class="checkbox-column px-4 py-3.5 align-middle">
                                        <input type="checkbox" :value="liveSale.id" x-model="selectedItems" class="row-checkbox rounded border-gray-300 text-[#5542ff] focus:ring-[#5542ff]">
                                    </td>
                                <?php endif; ?>
                                <td class="px-4 py-3.5 align-top">
                                    <div class="flex items-start gap-1.5">
                                        <div>
                                            <div class="flex items-center gap-1.5 flex-wrap">
                                                <div class="text-sm font-bold text-ink-heading" x-text="liveSale.invoice_no"></div>
                                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold bg-[#5542ff] text-white animate-bounce">NEW</span>
                                                <template x-if="liveSale.status === 'pending'">
                                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 border border-gray-200 dark:border-gray-600">Pending</span>
                                                </template>
                                                <template x-if="liveSale.due_amount <= 0.009 && liveSale.status !== 'pending'">
                                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold bg-green-50 dark:bg-green-900/30 text-green-700 dark:text-green-300 border border-green-200 dark:border-green-800">Paid</span>
                                                </template>
                                                <template x-if="liveSale.paid_amount > 0.009 && liveSale.due_amount > 0.009 && liveSale.status !== 'pending'">
                                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold bg-amber-50 dark:bg-amber-900/30 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-800">Partial</span>
                                                </template>
                                                <template x-if="liveSale.paid_amount <= 0.009 && liveSale.status !== 'pending'">
                                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold bg-red-50 dark:bg-red-900/30 text-red-700 dark:text-red-300 border border-red-200 dark:border-red-800">Due</span>
                                                </template>
                                            </div>
                                            <div class="text-xs text-ink-muted mt-0.5" x-text="liveSale.sale_date"></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3.5 align-top">
                                    <div class="text-sm font-semibold text-[#5542ff] dark:text-indigo-300" x-text="liveSale.customer_name"></div>
                                    <div class="text-xs text-ink-muted mt-0.5" x-text="liveSale.customer_phone || '—'"></div>
                                </td>
                                <td class="px-4 py-3.5 align-top">
                                    <div class="text-sm font-medium text-gray-700 dark:text-gray-200" x-text="liveSale.salesperson_name"></div>
                                </td>
                                <td class="px-4 py-3.5 align-top">
                                    <div class="text-sm font-bold text-ink-heading" x-text="formatMoney(liveSale.receivable_amount)"></div>
                                </td>
                                <td class="px-4 py-3.5 align-top">
                                    <div class="text-xs text-ink-muted">₱0.00</div>
                                    <div class="text-sm font-bold text-ink-heading mt-1" x-text="formatMoney(liveSale.receivable_amount)"></div>
                                </td>
                                <td class="px-4 py-3.5 align-top">
                                    <div class="text-xs text-ink-muted">—</div>
                                    <div class="text-sm font-bold text-ink-heading mt-1" x-text="formatMoney(liveSale.receivable_amount)"></div>
                                </td>
                                <td class="px-4 py-3.5 align-top">
                                    <div class="text-sm font-bold text-ink-heading" x-text="formatMoney(liveSale.paid_amount)"></div>
                                    <div class="text-xs mt-0.5" :class="liveSale.due_amount <= 0.009 ? 'text-green-600 dark:text-green-400' : 'text-ink-muted'" x-text="formatMoney(liveSale.due_amount)"></div>
                                </td>
                                <td class="px-4 py-3.5 align-top text-right">
                                    <div class="flex items-center justify-end gap-2 flex-nowrap whitespace-nowrap">
                                        <template x-if="liveSale.status === 'pending'">
                                            <div class="status-actions flex gap-1 mr-2">
                                                <?php if(auth()->user()->hasPermission('Confirm Sale')): ?>
                                                    <button type="button" @click.stop.prevent="openApprovalModal(liveSale.id, 'confirm', liveSale.invoice_no)" class="inline-flex items-center px-2 py-1 bg-green-50 dark:bg-green-900/30 text-green-600 dark:text-green-300 border border-green-200 dark:border-green-700 hover:bg-green-100 dark:hover:bg-green-900/50 rounded text-[11px] font-bold transition-colors">
                                                        <svg class="w-3 h-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                                        Confirm
                                                    </button>
                                                <?php endif; ?>
                                                <?php if(auth()->user()->hasPermission('Reject Sale')): ?>
                                                    <button type="button" @click.stop.prevent="openApprovalModal(liveSale.id, 'reject', liveSale.invoice_no)" class="inline-flex items-center px-2 py-1 bg-red-50 dark:bg-red-900/30 text-red-600 dark:text-red-300 border border-red-200 dark:border-red-700 hover:bg-red-100 dark:hover:bg-red-900/50 rounded text-[11px] font-bold transition-colors">
                                                        <svg class="w-3 h-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                                                        Reject
                                                    </button>
                                                <?php endif; ?>
                                            </div>
                                        </template>
                                        <a :href="liveSale.show_url" class="inline-flex items-center px-2.5 py-1.5 bg-white dark:bg-gray-800 border border-[#5542ff] dark:border-gray-600 text-[#5542ff] dark:text-gray-100 rounded-md hover:bg-[#5542ff]/5 text-[11px] font-bold">
                                            View
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        </template>

                        <?php $__empty_1 = true; $__currentLoopData = $sales; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sale): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <?php
                                $phone = $sale->customer->phone;
                                $phoneDisp = $phone ? (\Illuminate\Support\Str::startsWith($phone, '+') ? $phone : "+{$phone}") : '—';
                                $hasReturn = $sale->sale_returns_count > 0;
                                $totalReturnPayable = (float) ($sale->sale_returns_sum_payable_amount ?? 0);
                                $netReceivable = max(0, (float)$sale->receivable_amount - $totalReturnPayable);
                                $netDue = max(0, $netReceivable - (float)$sale->paid_amount);
                            ?>
                            <tr class="hover:bg-gray-50/90 dark:hover:bg-gray-700 transition-colors bg-white dark:bg-gray-800 cursor-pointer" onclick="if(event.target.closest('.checkbox-column, .text-right, .status-actions')) return; window.location.href='<?php echo e(route('sales.show', $sale)); ?>'">
                                <?php if($canSelect): ?>
                                    <td class="checkbox-column px-4 py-3.5 align-middle">
                                        <input type="checkbox" value="<?php echo e($sale->id); ?>" x-model="selectedItems" class="row-checkbox rounded border-gray-300 text-[#5542ff] focus:ring-[#5542ff]">
                                    </td>
                                <?php endif; ?>
                                <td class="px-4 py-3.5 align-top">
                                    <div class="flex items-start gap-1.5">
                                        <?php if($hasReturn): ?>
                                            <span class="inline-block w-2 h-2 rounded-full bg-red-500 mt-1.5 shrink-0 cursor-pointer" title="Has return(s)"></span>
                                        <?php endif; ?>
                                        <div>
                                            <div class="flex items-center gap-1.5 flex-wrap">
                                                <div class="text-sm font-bold text-ink-heading"><?php echo e($sale->invoice_no); ?></div>
                                                <?php if($sale->status === 'pending'): ?>
                                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 border border-gray-200 dark:border-gray-600">Pending</span>
                                                <?php endif; ?>
                                                <?php if($netDue <= 0.009): ?>
                                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold bg-green-50 dark:bg-green-900/30 text-green-700 dark:text-green-300 border border-green-200 dark:border-green-800">Paid</span>
                                                <?php elseif($sale->paid_amount > 0.009): ?>
                                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold bg-amber-50 dark:bg-amber-900/30 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-800">Partial</span>
                                                <?php else: ?>
                                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold bg-red-50 dark:bg-red-900/30 text-red-700 dark:text-red-300 border border-red-200 dark:border-red-800">Due</span>
                                                <?php endif; ?>
                                            </div>
                                            <div class="text-xs text-ink-muted mt-0.5"><?php echo e($sale->sale_date->format('d M, Y')); ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3.5 align-top">
                                    <div class="text-sm font-semibold text-[#5542ff] dark:text-indigo-300"><?php echo e($sale->customer->name); ?></div>
                                    <div class="text-xs text-ink-muted mt-0.5"><?php echo e($phoneDisp); ?></div>
                                    <?php if(!empty($sale->customer->address)): ?>
                                        <div class="text-[11px] text-ink-muted mt-0.5 flex items-center gap-1">
                                            <svg class="h-3 w-3 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            </svg>
                                            <span class="truncate max-w-[160px]" title="<?php echo e($sale->customer->address); ?>"><?php echo e($sale->customer->address); ?></span>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td class="px-4 py-3.5 align-top">
                                    <div class="text-sm font-medium text-gray-700 dark:text-gray-200"><?php echo e($sale->salesperson->name ?? '—'); ?></div>
                                </td>
                                <td class="px-4 py-3.5 align-top">
                                    <div class="text-sm font-bold text-gray-900 mt-0.5"><?php echo e(formatCurrency($sale->gross_subtotal)); ?></div>
                                </td>
                                <td class="px-4 py-3.5 align-top">
                                    <div class="text-xs text-ink-muted"><?php echo e(formatCurrency($sale->total_discount)); ?></div>
                                    <div class="text-sm font-bold text-ink-heading mt-1"><?php echo e(formatCurrency($sale->receivable_amount)); ?></div>
                                </td>
                                <td class="px-4 py-3.5 align-top">
                                    <div class="text-xs text-ink-muted"><?php echo e($totalReturnPayable > 0 ? '-'.formatCurrency($totalReturnPayable) : '—'); ?></div>
                                    <div class="text-sm font-bold text-ink-heading mt-1"><?php echo e(formatCurrency($netReceivable)); ?></div>
                                </td>
                                <td class="px-4 py-3.5 align-top">
                                    <div class="text-sm font-bold text-ink-heading"><?php echo e(formatCurrency($sale->paid_amount)); ?></div>
                                    <div class="text-xs mt-0.5 <?php echo e($netDue <= 0.009 ? 'text-green-600 dark:text-green-400' : 'text-ink-muted'); ?>">
                                        <?php echo e(formatCurrency($netDue)); ?>

                                    </div>
                                </td>
                                
                                <td class="px-4 py-3.5 align-top">
                                    <?php if($sale->status === 'confirmed'): ?>
                                        <?php $dc = $sale->delivery_status_color; ?>
                                        <div class="flex items-center gap-1.5 flex-wrap">
                                            <?php if(auth()->user()->hasPermission('Update Sale Delivery Status')): ?>
                                                <button type="button"
                                                        @click.stop="openDeliveryModal({ id: <?php echo e($sale->id); ?>, invoice: <?php echo \Illuminate\Support\Js::from($sale->invoice_no)->toHtml() ?>, delivery_status: <?php echo \Illuminate\Support\Js::from($sale->delivery_status)->toHtml() ?>, delivery_note: <?php echo \Illuminate\Support\Js::from($sale->delivery_note)->toHtml() ?> })"
                                                        class="group inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold border <?php echo e($dc['bg']); ?> <?php echo e($dc['text']); ?> <?php echo e($dc['border']); ?> hover:ring-2 hover:ring-blue-400 hover:shadow-xs transition-all cursor-pointer"
                                                        title="Click to update delivery status">
                                                    <?php if($sale->delivery_status === 'in_truck'): ?>
                                                        <svg class="w-3 h-3 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zm12 0a2 2 0 11-4 0 2 2 0 014 0M1 3h15v13H1zm15 6h4l3 4v4h-7V9z"/></svg>
                                                    <?php elseif($sale->delivery_status === 'out_for_delivery'): ?>
                                                        <svg class="w-3 h-3 text-purple-600 dark:text-purple-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7H4a2 2 0 00-2 2v8a2 2 0 002 2h16a2 2 0 002-2V9a2 2 0 00-2-2zm-9 8H7m2-4H7"/></svg>
                                                    <?php elseif($sale->delivery_status === 'delivered'): ?>
                                                        <svg class="w-3 h-3 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                                    <?php elseif($sale->delivery_status === 'failed_delivery'): ?>
                                                        <svg class="w-3 h-3 text-rose-600 dark:text-rose-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                                    <?php elseif($sale->delivery_status === 'returned'): ?>
                                                        <svg class="w-3 h-3 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/></svg>
                                                    <?php else: ?>
                                                        <svg class="w-3 h-3 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                    <?php endif; ?>
                                                    <span><?php echo e($sale->delivery_status_label); ?></span>
                                                    <svg class="w-2.5 h-2.5 opacity-60 group-hover:opacity-100 group-hover:translate-x-0.5 transition-all" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                                </button>
                                            <?php else: ?>
                                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold border <?php echo e($dc['bg']); ?> <?php echo e($dc['text']); ?> <?php echo e($dc['border']); ?>">
                                                    <?php echo e($sale->delivery_status_label); ?>

                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-xs text-ink-muted">—</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-4 py-3.5 align-top text-right">
                                    <div class="flex items-center justify-end gap-2 flex-nowrap whitespace-nowrap">
                                        <?php if($sale->status === 'pending'): ?>
                                            <div class="status-actions flex gap-1 mr-2">
                                                <?php if(auth()->user()->hasPermission('Confirm Sale')): ?>
                                                    <button type="button" @click.stop.prevent="openApprovalModal(<?php echo e($sale->id); ?>, 'confirm', '<?php echo e($sale->invoice_no); ?>')" class="inline-flex items-center px-2 py-1 bg-green-50 dark:bg-green-900/30 text-green-600 dark:text-green-300 border border-green-200 dark:border-green-700 hover:bg-green-100 dark:hover:bg-green-900/50 rounded text-[11px] font-bold transition-colors">
                                                        <svg class="w-3 h-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                                        Confirm
                                                    </button>
                                                <?php endif; ?>
                                                <?php if(auth()->user()->hasPermission('Reject Sale')): ?>
                                                    <button type="button" @click.stop.prevent="openApprovalModal(<?php echo e($sale->id); ?>, 'reject', '<?php echo e($sale->invoice_no); ?>')" class="inline-flex items-center px-2 py-1 bg-red-50 dark:bg-red-900/30 text-red-600 dark:text-red-300 border border-red-200 dark:border-red-700 hover:bg-red-100 dark:hover:bg-red-900/50 rounded text-[11px] font-bold transition-colors">
                                                        <svg class="w-3 h-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                                                        Reject
                                                    </button>
                                                <?php endif; ?>
                                            </div>
                                        <?php elseif($sale->status === 'rejected'): ?>
                                            <span class="inline-flex items-center px-3 py-1.5 bg-red-50 dark:bg-red-900/30 text-red-700 dark:text-red-300 border border-red-200 dark:border-red-700 rounded text-[11px] font-bold">
                                                <svg class="w-3.5 h-3.5 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4v.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                                Rejected
                                            </span>
                                        <?php elseif($sale->status === 'cancelled'): ?>
                                            <span class="inline-flex items-center px-3 py-1.5 bg-orange-50 dark:bg-orange-900/30 text-orange-700 dark:text-orange-300 border border-orange-200 dark:border-orange-700 rounded text-[11px] font-bold">
                                                <svg class="w-3.5 h-3.5 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" /></svg>
                                                Cancelled
                                            </span>
                                        <?php endif; ?>
                                        
                                        <?php if(auth()->user()->hasPermission('Edit Sale') && $sale->status === 'confirmed'): ?>
                                    <a href="<?php echo e(route('sales.edit', $sale)); ?>" class="inline-flex items-center px-2.5 py-1.5 bg-white dark:bg-gray-800 border border-[#5542ff] dark:border-gray-600 text-[#5542ff] dark:text-gray-100 rounded-md hover:bg-[#5542ff]/5 dark:hover:bg-gray-700 text-[11px] font-bold">
                                        <svg class="h-3.5 w-3.5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                        Edit
                                    </a>
                                <?php endif; ?>

                                <?php if($sale->status === 'confirmed' && (!auth()->user()->hasPermission('View Own Sales Only') || auth()->user()->hasPermission('Download Sale Invoice PDF') || auth()->user()->hasPermission('Store Customer Payment'))): ?>
                                <div class="inline-flex">
                                    <button
                                        type="button"
                                        @click.stop="toggleMore(<?php echo e($sale->id); ?>, $event)"
                                        class="inline-flex items-center px-2.5 py-1.5 bg-white dark:bg-gray-800 border border-[#5542ff] dark:border-gray-600 text-[#5542ff] dark:text-gray-100 rounded-md hover:bg-[#5542ff]/5 dark:hover:bg-gray-700 text-[11px] font-bold"
                                    >
                                        <svg class="h-3.5 w-3.5 mr-1 text-[#5542ff] dark:text-gray-300" fill="currentColor" viewBox="0 0 24 24"><path d="M12 8c1.1 0 2-.9 2-2s-.9-2-2-2-2 .9-2 2 .9 2 2 2zm0 2c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm0 6c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2z" /></svg>
                                        More
                                        <svg class="h-3 w-3 ml-0.5 text-[#5542ff] dark:text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                                    </button>
                                    <template x-teleport="body">
                                        <div
                                            x-show="openMoreId === <?php echo e($sale->id); ?>"
                                            @click.outside="openMoreId = null"
                                            x-cloak
                                            class="fixed w-52 bg-white dark:bg-gray-800 rounded-lg shadow-xl border border-gray-100 dark:border-gray-700 py-1 text-left z-[200]"
                                            :style="{ top: dropdownTop, right: dropdownRight }"
                                        >
                                                    <?php
                                                        $canStorePaymentDesk = auth()->user()->hasPermission('Store Customer Payment') || auth()->user()->hasRole('salesperson');
                                                    ?>
                                                    <?php if($canStorePaymentDesk && $netDue > 0.009): ?>
                                                        <button
                                                            type="button"
                                                            @click="openPayment({ id: <?php echo e($sale->id); ?>, invoice: <?php echo \Illuminate\Support\Js::from($sale->invoice_no)->toHtml() ?>, customer: <?php echo \Illuminate\Support\Js::from($sale->customer->name)->toHtml() ?>, receivable: <?php echo \Illuminate\Support\Js::from(number_format((float)$sale->receivable_amount, 2, '.', ''))->toHtml() ?>, due: <?php echo \Illuminate\Support\Js::from(number_format($netDue, 2, '.', ''))->toHtml() ?> })"
                                                            class="w-full flex items-center px-3 py-2 text-sm text-gray-700 dark:text-gray-200 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700"
                                                        >
                                                            <svg class="h-4 w-4 mr-2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                                                            Receive Payment
                                                        </button>
                                                    <?php endif; ?>
                                                    <?php if(auth()->user()->hasPermission('Store Sale Return') && $sale->status === 'confirmed'): ?>
                                                        <a href="<?php echo e(route('sales.return.create', $sale)); ?>" class="flex items-center px-3 py-2 text-sm text-gray-700 dark:text-gray-200 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700">
                                                            <svg class="h-4 w-4 mr-2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6" /></svg>
                                                            Return Sale
                                                        </a>
                                                    <?php endif; ?>
                                                    <?php if(auth()->user()->hasPermission('Update Sale Delivery Status') && $sale->status === 'confirmed'): ?>
                                                        <button
                                                            type="button"
                                                            @click="openDeliveryModal({ id: <?php echo e($sale->id); ?>, invoice: <?php echo \Illuminate\Support\Js::from($sale->invoice_no)->toHtml() ?>, delivery_status: <?php echo \Illuminate\Support\Js::from($sale->delivery_status)->toHtml() ?>, delivery_note: <?php echo \Illuminate\Support\Js::from($sale->delivery_note)->toHtml() ?> })"
                                                            class="w-full flex items-center px-3 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700"
                                                        >
                                                            <svg class="h-4 w-4 mr-2 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zm12 0a2 2 0 11-4 0 2 2 0 014 0M1 3h15v13H1zm15 6h4l3 4v4h-7V9z"/></svg>
                                                            Update Delivery
                                                        </button>
                                                    <?php endif; ?>
                                                    <?php if(auth()->user()->hasPermission('Cancel Sale') && $sale->status === 'confirmed' && $sale->delivery_status === \App\Models\Sale::DELIVERY_NOT_DISPATCHED && (float)$sale->paid_amount <= 0): ?>
                                                        <button
                                                            type="button"
                                                            @click="openMoreId = null; cancelSaleUrl = '<?php echo e(route('sales.cancel', $sale)); ?>'; cancelSaleInvoice = '<?php echo e($sale->invoice_no); ?>'; cancelOpen = true"
                                                            class="w-full flex items-center px-3 py-2 text-sm text-orange-600 dark:text-orange-400 hover:bg-orange-50 dark:hover:bg-orange-950/30"
                                                        >
                                                            <svg class="h-4 w-4 mr-2 text-orange-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" /></svg>
                                                            Cancel Order
                                                        </button>
                                                    <?php endif; ?>
                                                    <?php if(auth()->user()->hasPermission('Download Sale Invoice PDF')): ?>
                                                        <a href="<?php echo e(route('sales.invoice.pdf', $sale)); ?>?print=true" target="_blank" @click="openMoreId = null" class="flex items-center px-3 py-2 text-sm text-gray-700 dark:text-gray-200 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700">
                                                            <svg class="h-4 w-4 mr-2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" /></svg>
                                                            Print
                                                        </a>
                                                        <a href="<?php echo e(route('sales.invoice.pdf', $sale)); ?>" @click="openMoreId = null" class="flex items-center px-3 py-2 text-sm text-gray-700 dark:text-gray-200 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700">
                                                            <svg class="h-4 w-4 mr-2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>
                                                            Download Invoice
                                                        </a>
                                                    <?php endif; ?>
                                                </div>
                                            </template>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="7" class="px-6 py-12 text-center text-ink-muted">No sales found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <?php if($sales->hasPages()): ?>
                <div class="px-4 py-3 bg-gray-50 dark:bg-gray-900 border-t border-gray-100 dark:border-gray-700">
                    <?php echo e($sales->links()); ?>

                </div>
            <?php endif; ?>
        </div>

        <!-- Approval Modal -->
        <div x-show="approvalOpen" x-cloak class="fixed inset-0 z-[210] overflow-y-auto" style="display: none;">
            <div class="flex items-center justify-center min-h-screen px-4 py-8">
                <div class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm z-0" @click="approvalOpen = false"></div>
                <div class="relative bg-white dark:bg-gray-800 rounded-2xl shadow-2xl max-w-md w-full z-10 border border-gray-100 dark:border-gray-700 overflow-hidden" @click.stop>
                    
                    
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

                    
                    <div class="p-6 space-y-4">
                        <div class="p-4 rounded-xl bg-gray-50 dark:bg-gray-700/40 border border-gray-100 dark:border-gray-700">
                            <p class="text-sm text-gray-700 dark:text-gray-200">
                                Are you sure you want to <span class="font-black uppercase tracking-wide text-xs px-2 py-0.5 rounded" :class="approvalAction === 'confirm' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300' : 'bg-rose-100 text-rose-700 dark:bg-rose-900/40 dark:text-rose-300'" x-text="approvalAction"></span> sale <span class="font-extrabold text-ink-heading" x-text="approvalSaleInvoice"></span>?
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

                        <form method="POST" :action="approvalSaleId ? `/sales/${approvalSaleId}/${approvalAction}` : '#'">
                            <?php echo csrf_field(); ?>
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

        <!-- Payment modal -->
        <div x-show="paymentOpen" x-cloak class="fixed inset-0 z-[210] overflow-y-auto" style="display: none;">
            <div class="flex items-center justify-center min-h-screen px-4 py-8">
                <div class="fixed inset-0 bg-gray-900/50 z-0" @click="paymentOpen = false"></div>
                <div class="relative bg-white dark:bg-gray-800 rounded-2xl shadow-2xl max-w-md w-full z-10 border border-gray-100 dark:border-gray-700" @click.stop>
                    
                    
                    <div class="px-6 py-5 flex justify-between items-center rounded-t-2xl" style="background: linear-gradient(135deg, var(--gradient-from) 0%, var(--gradient-to) 100%)">
                        <div class="flex items-center gap-3">
                            <div class="bg-white/20 rounded-lg p-2">
                                <svg class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-base font-bold text-white leading-tight">Receive Payment</h3>
                                <p class="text-white/60 text-xs mt-0.5">Record customer transaction</p>
                            </div>
                        </div>
                        <button type="button" @click="paymentOpen = false" class="text-white/60 hover:text-white hover:bg-white/10 rounded-lg p-1.5 transition-colors" aria-label="Close">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                        </button>
                    </div>

                    
                    <div class="p-6">
                        <div x-show="paymentSale" x-cloak>
                            <form method="POST" class="space-y-4" :action="paymentSale ? `/sales/${paymentSale.id}/payment` : '#'">
                                <?php echo csrf_field(); ?>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-xs font-semibold text-ink-muted uppercase tracking-wide mb-1.5">Invoice No.</label>
                                        <input type="text" :value="paymentSale ? paymentSale.invoice : ''" disabled class="w-full rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-ink-muted px-3 py-2 text-sm" />
                                    </div>
                                    <div>
                                        <label class="block text-xs font-semibold text-ink-muted uppercase tracking-wide mb-1.5">Customer</label>
                                        <input type="text" :value="paymentSale ? paymentSale.customer : ''" disabled class="w-full rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-ink-muted px-3 py-2 text-sm" />
                                    </div>
                                </div>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-xs font-semibold text-ink-muted uppercase tracking-wide mb-1.5">Receivable</label>
                                        <div class="relative flex items-center">
                                            <span class="absolute left-3 text-gray-400 text-sm font-semibold"><?php echo e(currencySymbol()); ?></span>
                                            <input type="text" :value="paymentSale ? paymentSale.receivable : ''" disabled class="w-full pl-8 pr-3 py-2 rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-ink-muted text-sm" />
                                        </div>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-semibold text-ink-muted uppercase tracking-wide mb-1.5">Due Amount</label>
                                        <div class="relative flex items-center">
                                            <span class="absolute left-3 text-gray-400 text-sm font-semibold"><?php echo e(currencySymbol()); ?></span>
                                            <input type="text" :value="paymentSale ? paymentSale.due : ''" disabled class="w-full pl-8 pr-3 py-2 rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-ink-muted text-sm" />
                                        </div>
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-ink-muted uppercase tracking-wide mb-1.5">Receiving Amount <span class="text-red-500">*</span></label>
                                    <div class="relative flex items-center">
                                        <span class="absolute left-3 text-gray-400 text-sm font-semibold"><?php echo e(currencySymbol()); ?></span>
                                        <input type="number" name="receiving_amount" step="0.01" min="0.01" required 
                                            x-model="receivingAmount"
                                            :max="paymentSale ? paymentSale.due : null"
                                            class="w-full pl-8 pr-3 py-2 rounded-lg border bg-white dark:bg-gray-900 text-ink-heading text-sm focus:outline-none transition-all payment-receiving-input" 
                                            :class="isReceivingExceeded ? 'border-red-500 text-red-600 focus:ring-1 focus:ring-red-500 focus:border-red-500' : 'border-gray-200 dark:border-gray-600 focus:ring-1 focus:ring-[#5542ff] focus:border-[#5542ff]'"
                                            placeholder="0.00" />
                                    </div>
                                    <p x-show="isReceivingExceeded" x-cloak class="text-red-500 text-xs mt-1.5 font-semibold flex items-center gap-1">
                                        <svg class="h-3.5 w-3.5 text-red-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        <span>Receiving amount cannot exceed the due amount (<?php echo e(currencySymbol()); ?><span x-text="paymentSale ? Number(paymentSale.due).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}) : '0.00'"></span>).</span>
                                    </p>
                                    <?php $__errorArgs = ['receiving_amount'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                        <p class="text-red-500 text-xs mt-1"><?php echo e($message); ?></p>
                                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
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
                                        <?php $__errorArgs = ['check_number'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                            <p class="text-red-500 text-xs mt-1"><?php echo e($message); ?></p>
                                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-semibold text-ink-muted uppercase tracking-wide mb-1.5">Check Date <span class="text-red-500">*</span></label>
                                        <input type="date" name="check_date" x-model="checkDate" :required="paymentMethod === 'check'" 
                                            class="w-full rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-900 text-ink-heading px-3 py-2.5 text-sm focus:outline-none focus:ring-1 focus:ring-[#5542ff] focus:border-[#5542ff] transition-all" />
                                        <?php $__errorArgs = ['check_date'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                            <p class="text-red-500 text-xs mt-1"><?php echo e($message); ?></p>
                                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                    </div>
                                </div>
                                <button type="submit" 
                                    :disabled="isReceivingExceeded"
                                    class="w-full py-3 rounded-lg text-white font-bold text-sm shadow-md transition-all mt-2 disabled:opacity-50 disabled:cursor-not-allowed" 
                                    :class="isReceivingExceeded ? 'opacity-50 cursor-not-allowed' : 'hover:opacity-95'"
                                    style="background: linear-gradient(135deg, var(--gradient-from) 0%, var(--gradient-to) 100%)">
                                    Submit Payment
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Delivery Status Modal -->
        <div x-show="deliveryModalOpen" x-cloak class="fixed inset-0 z-[210] overflow-y-auto" style="display: none;">
            <div class="flex items-center justify-center min-h-screen px-4 py-8">
                <div class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm z-0" @click="deliveryModalOpen = false"></div>
                <div class="relative bg-white dark:bg-gray-800 rounded-2xl shadow-2xl max-w-md w-full z-10 border border-gray-100 dark:border-gray-700 overflow-hidden" @click.stop>
                    
                    
                    <div class="px-6 py-5 flex justify-between items-center bg-gradient-to-r from-blue-600 to-indigo-600 text-white">
                        <div class="flex items-center gap-3">
                            <div class="bg-white/20 rounded-xl p-2.5 backdrop-blur-sm">
                                <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zm12 0a2 2 0 11-4 0 2 2 0 014 0M1 3h15v13H1zm15 6h4l3 4v4h-7V9z"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-base font-bold text-white leading-tight">Update Delivery Status</h3>
                                <p class="text-white/80 text-xs mt-0.5" x-text="deliverySale ? deliverySale.invoice : ''"></p>
                            </div>
                        </div>
                        <button type="button" @click="deliveryModalOpen = false" class="text-white/70 hover:text-white hover:bg-white/10 rounded-lg p-1.5 transition-colors" aria-label="Close">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                        </button>
                    </div>

                    
                    <div class="p-6">
                        <form method="POST" :action="deliverySale ? `/sales/${deliverySale.id}/delivery-status` : '#'" class="space-y-4">
                            <?php echo csrf_field(); ?>
                            <div>
                                <label class="block text-xs font-bold text-ink-body uppercase tracking-wide mb-1.5">
                                    Delivery Status <span class="text-red-500">*</span>
                                </label>
                                <select name="delivery_status" x-model="deliveryStatus" required
                                        class="w-full rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-ink-heading px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    <?php $__currentLoopData = \App\Models\Sale::$deliveryStatuses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($key); ?>"><?php echo e($label); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-ink-body uppercase tracking-wide mb-1.5">
                                    Delivery Note / Driver Remarks <span class="text-gray-400 font-normal lowercase">(optional)</span>
                                </label>
                                <input type="text" name="delivery_note" x-model="deliveryNote" maxlength="500"
                                       placeholder="e.g. In Truck: Plate ABC-1234, Driver: Juan"
                                       class="w-full rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-ink-heading px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" />
                            </div>

                            
                            <div>
                                <span class="block text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-2">Quick Pick:</span>
                                <div class="grid grid-cols-2 gap-2">
                                    <button type="button" @click="deliveryStatus = 'in_truck'"
                                            class="flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-bold border transition-all text-left"
                                            :class="deliveryStatus === 'in_truck' ? 'bg-blue-100 border-blue-400 text-blue-800 dark:bg-blue-900/60 dark:text-blue-200' : 'bg-gray-50 dark:bg-gray-700/50 border-gray-200 dark:border-gray-600 text-ink-body hover:bg-gray-100'">
                                        <svg class="w-3.5 h-3.5 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zm12 0a2 2 0 11-4 0 2 2 0 014 0M1 3h15v13H1zm15 6h4l3 4v4h-7V9z"/></svg>
                                        <span>In Truck</span>
                                    </button>
                                    <button type="button" @click="deliveryStatus = 'out_for_delivery'"
                                            class="flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-bold border transition-all text-left"
                                            :class="deliveryStatus === 'out_for_delivery' ? 'bg-purple-100 border-purple-400 text-purple-800 dark:bg-purple-900/60 dark:text-purple-200' : 'bg-gray-50 dark:bg-gray-700/50 border-gray-200 dark:border-gray-600 text-ink-body hover:bg-gray-100'">
                                        <svg class="w-3.5 h-3.5 text-purple-600 dark:text-purple-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7H4a2 2 0 00-2 2v8a2 2 0 002 2h16a2 2 0 002-2V9a2 2 0 00-2-2zm-9 8H7m2-4H7"/></svg>
                                        <span>Out for Delivery</span>
                                    </button>
                                    <button type="button" @click="deliveryStatus = 'delivered'"
                                            class="flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-bold border transition-all text-left"
                                            :class="deliveryStatus === 'delivered' ? 'bg-emerald-100 border-emerald-400 text-emerald-800 dark:bg-emerald-900/60 dark:text-emerald-200' : 'bg-gray-50 dark:bg-gray-700/50 border-gray-200 dark:border-gray-600 text-ink-body hover:bg-gray-100'">
                                        <svg class="w-3.5 h-3.5 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                        <span>Delivered</span>
                                    </button>
                                    <button type="button" @click="deliveryStatus = 'returned'"
                                            class="flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-bold border transition-all text-left"
                                            :class="deliveryStatus === 'returned' ? 'bg-amber-100 border-amber-400 text-amber-800 dark:bg-amber-900/60 dark:text-amber-200' : 'bg-gray-50 dark:bg-gray-700/50 border-gray-200 dark:border-gray-600 text-ink-body hover:bg-gray-100'">
                                        <svg class="w-3.5 h-3.5 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/></svg>
                                        <span>Returned</span>
                                    </button>
                                </div>
                            </div>

                            <div class="flex items-center gap-3 pt-3 border-t border-gray-100 dark:border-gray-700">
                                <button type="button" @click="deliveryModalOpen = false"
                                        class="flex-1 py-2.5 px-4 rounded-xl border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 text-sm font-bold hover:bg-gray-50 dark:hover:bg-gray-700 transition-all">
                                    Cancel
                                </button>
                                <button type="submit"
                                        class="flex-1 py-2.5 px-4 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-sm font-bold shadow-md hover:shadow-lg transition-all flex items-center justify-center gap-1.5">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                    Save Status
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bulk Delivery Status Modal -->
        <div x-show="bulkDeliveryOpen" x-cloak class="fixed inset-0 z-[210] overflow-y-auto" style="display: none;">
            <div class="flex items-center justify-center min-h-screen px-4 py-8">
                <div class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm z-0" @click="bulkDeliveryOpen = false"></div>
                <div class="relative bg-white dark:bg-gray-800 rounded-2xl shadow-2xl max-w-md w-full z-10 border border-gray-100 dark:border-gray-700 overflow-hidden" @click.stop>

                    
                    <div class="px-6 py-5 flex justify-between items-center bg-gradient-to-r from-indigo-600 to-blue-600 text-white">
                        <div class="flex items-center gap-3">
                            <div class="bg-white/20 rounded-xl p-2.5 backdrop-blur-sm">
                                <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zm12 0a2 2 0 11-4 0 2 2 0 014 0M1 3h15v13H1zm15 6h4l3 4v4h-7V9z"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-base font-bold text-white leading-tight">Bulk Update Delivery Status</h3>
                                <p class="text-white/80 text-xs mt-0.5" x-text="selectedItems.length + ' order(s) selected'"></p>
                            </div>
                        </div>
                        <button type="button" @click="bulkDeliveryOpen = false" class="text-white/70 hover:text-white hover:bg-white/10 rounded-lg p-1.5 transition-colors" aria-label="Close">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                        </button>
                    </div>

                    
                    <div class="mx-6 mt-4 px-4 py-3 rounded-xl bg-indigo-50 dark:bg-indigo-950/40 border border-indigo-200 dark:border-indigo-800/60 flex items-start gap-2.5">
                        <svg class="h-4 w-4 text-indigo-500 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <p class="text-xs text-indigo-700 dark:text-indigo-300">Only <strong>confirmed</strong> orders will be updated. Pending or rejected orders in the selection will be skipped.</p>
                    </div>

                    
                    <div class="p-6">
                        <form method="POST" action="<?php echo e(route('sales.bulk-delivery-status')); ?>" class="space-y-4" @submit.prevent="submitBulkDelivery($el)">
                            <?php echo csrf_field(); ?>
                            
                            <input type="hidden" name="sale_ids" :value="selectedItems.join(',')">

                            <div>
                                <label class="block text-xs font-bold text-ink-body uppercase tracking-wide mb-1.5">
                                    New Delivery Status <span class="text-red-500">*</span>
                                </label>
                                <select name="delivery_status" x-model="bulkDeliveryStatus" required
                                        class="w-full rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-ink-heading px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                    <?php $__currentLoopData = \App\Models\Sale::$deliveryStatuses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($key); ?>"><?php echo e($label); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-ink-body uppercase tracking-wide mb-1.5">
                                    Delivery Note / Driver Remarks <span class="text-gray-400 font-normal lowercase">(optional — applies to all)</span>
                                </label>
                                <input type="text" name="delivery_note" x-model="bulkDeliveryNote" maxlength="500"
                                       placeholder="e.g. Truck plate ABC-1234, Driver: Juan"
                                       class="w-full rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-ink-heading px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" />
                            </div>

                            
                            <div>
                                <span class="block text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-2">Quick Pick:</span>
                                <div class="grid grid-cols-2 gap-2">
                                    <button type="button" @click="bulkDeliveryStatus = 'in_truck'"
                                            class="flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-bold border transition-all text-left"
                                            :class="bulkDeliveryStatus === 'in_truck' ? 'bg-blue-100 border-blue-400 text-blue-800 dark:bg-blue-900/60 dark:text-blue-200' : 'bg-gray-50 dark:bg-gray-700/50 border-gray-200 dark:border-gray-600 text-ink-body hover:bg-gray-100'">
                                        <svg class="w-3.5 h-3.5 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zm12 0a2 2 0 11-4 0 2 2 0 014 0M1 3h15v13H1zm15 6h4l3 4v4h-7V9z"/></svg>
                                        <span>In Truck</span>
                                    </button>
                                    <button type="button" @click="bulkDeliveryStatus = 'out_for_delivery'"
                                            class="flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-bold border transition-all text-left"
                                            :class="bulkDeliveryStatus === 'out_for_delivery' ? 'bg-purple-100 border-purple-400 text-purple-800 dark:bg-purple-900/60 dark:text-purple-200' : 'bg-gray-50 dark:bg-gray-700/50 border-gray-200 dark:border-gray-600 text-ink-body hover:bg-gray-100'">
                                        <svg class="w-3.5 h-3.5 text-purple-600 dark:text-purple-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7H4a2 2 0 00-2 2v8a2 2 0 002 2h16a2 2 0 002-2V9a2 2 0 00-2-2zm-9 8H7m2-4H7"/></svg>
                                        <span>Out for Delivery</span>
                                    </button>
                                    <button type="button" @click="bulkDeliveryStatus = 'delivered'"
                                            class="flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-bold border transition-all text-left"
                                            :class="bulkDeliveryStatus === 'delivered' ? 'bg-emerald-100 border-emerald-400 text-emerald-800 dark:bg-emerald-900/60 dark:text-emerald-200' : 'bg-gray-50 dark:bg-gray-700/50 border-gray-200 dark:border-gray-600 text-ink-body hover:bg-gray-100'">
                                        <svg class="w-3.5 h-3.5 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                        <span>Delivered</span>
                                    </button>
                                    <button type="button" @click="bulkDeliveryStatus = 'returned'"
                                            class="flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-bold border transition-all text-left"
                                            :class="bulkDeliveryStatus === 'returned' ? 'bg-amber-100 border-amber-400 text-amber-800 dark:bg-amber-900/60 dark:text-amber-200' : 'bg-gray-50 dark:bg-gray-700/50 border-gray-200 dark:border-gray-600 text-ink-body hover:bg-gray-100'">
                                        <svg class="w-3.5 h-3.5 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/></svg>
                                        <span>Returned</span>
                                    </button>
                                </div>
                            </div>

                            <div class="flex items-center gap-3 pt-3 border-t border-gray-100 dark:border-gray-700">
                                <button type="button" @click="bulkDeliveryOpen = false"
                                        class="flex-1 py-2.5 px-4 rounded-xl border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 text-sm font-bold hover:bg-gray-50 dark:hover:bg-gray-700 transition-all">
                                    Cancel
                                </button>
                                <button type="submit"
                                        :disabled="bulkDeliveryLoading"
                                        class="flex-1 py-2.5 px-4 rounded-xl bg-indigo-600 hover:bg-indigo-700 disabled:opacity-60 disabled:cursor-not-allowed text-white text-sm font-bold shadow-md hover:shadow-lg transition-all flex items-center justify-center gap-1.5">
                                    <template x-if="!bulkDeliveryLoading">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                    </template>
                                    <template x-if="bulkDeliveryLoading">
                                        <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                                    </template>
                                    <span x-text="bulkDeliveryLoading ? 'Updating...' : 'Update All Selected'"></span>
                                </button>
                            </div>
                        </form>
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

        
        <div x-show="cancelOpen" x-cloak class="fixed inset-0 z-[220] overflow-y-auto" style="display: none;">
            <div class="flex items-center justify-center min-h-screen px-4 py-8">
                <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm z-0" @click="cancelOpen = false"></div>
                <div class="relative bg-white dark:bg-gray-800 rounded-2xl shadow-2xl max-w-md w-full z-10 border border-gray-100 dark:border-gray-700 overflow-hidden" @click.stop>

                    
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

                    
                    <div class="p-6 space-y-4">
                        <div class="p-4 rounded-xl bg-gray-50 dark:bg-gray-700/40 border border-gray-100 dark:border-gray-700">
                            <p class="text-sm text-gray-700 dark:text-gray-200">
                                Are you sure you want to cancel sale <span class="font-extrabold text-ink-heading" x-text="cancelSaleInvoice"></span>?
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

                        <form method="POST" :action="cancelSaleUrl">
                            <?php echo csrf_field(); ?>
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

 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54)): ?>
<?php $attributes = $__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54; ?>
<?php unset($__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal9ac128a9029c0e4701924bd2d73d7f54)): ?>
<?php $component = $__componentOriginal9ac128a9029c0e4701924bd2d73d7f54; ?>
<?php unset($__componentOriginal9ac128a9029c0e4701924bd2d73d7f54); ?>
<?php endif; ?>
<?php /**PATH C:\laragon\www\GE-System-1.1\resources\views/sales/index.blade.php ENDPATH**/ ?>