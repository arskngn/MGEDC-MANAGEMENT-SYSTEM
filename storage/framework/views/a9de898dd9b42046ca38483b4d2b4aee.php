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
    <style media="print">
    html, body {
        background: #ffffff !important;
        color: #111827 !important;
    }
    
    /* Hide everything by default */
    body * {
        visibility: hidden;
    }
    
    /* Reveal the table container and all its children */
    .overflow-x-auto, .overflow-x-auto * {
        visibility: visible;
    }
    
    /* Position the table container at the top left of the printed page */
    .overflow-x-auto {
        position: absolute;
        left: 0;
        top: 0;
        width: 100% !important;
        min-width: 0 !important;
        background: #ffffff !important;
        margin: 0;
        padding: 0;
        overflow: visible !important;
    }

    table {
        min-width: 0 !important;
        width: 100% !important;
        background: #ffffff !important;
    }

    thead tr {
        background: #f3f4f6 !important;
        color: #374151 !important;
    }

    th {
        color: #374151 !important;
        border-bottom: 1.5px solid #374151 !important;
    }

    td {
        background: #ffffff !important;
        color: #111827 !important;
        border-bottom: 1px solid #f3f4f6 !important;
    }

    /* Hide the non-selected rows */
    .print-hidden { 
        display: none !important; 
    }

    /* Hide the Action and Checkbox columns */
    th.checkbox-column, td.checkbox-column,
    th:first-child, td:first-child {
        display: none !important;
    }

    /* Ensure backgrounds print correctly */
    * {
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }

    @page { size: landscape; margin: 10mm; }
</style>
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
                    this.showPrintMessage = true;
                    setTimeout(() => { this.showPrintMessage = false; }, 3000);
                    return;
                }
                const trx = this.selectedItems.join(',');
                window.open('<?php echo e(route('supplier-payments.export.pdf')); ?>?trx=' + encodeURIComponent(trx) + '&print=true', '_blank');
            },
            bulkOpen: false,
            filterDropdownOpen: false,
            actionTop: '0px',
            actionRight: '0px',
            filterValue: '<?php echo e($filter); ?>',
            searchValue: '<?php echo e($search); ?>',
            toggleAction($event) {
                this.bulkOpen = !this.bulkOpen;
                if (this.bulkOpen) {
                    const r = $event.currentTarget.getBoundingClientRect();
                    this.actionTop = (r.bottom + 4) + 'px';
                    this.actionRight = (window.innerWidth - r.right) + 'px';
                }
            },
            submitSearch() {
                document.getElementById('search-form').submit();
            }
        }"
        @keydown.escape.window="filterDropdownOpen = false; bulkOpen = false"
        @keydown.window.ctrl.p.prevent="printSelected()"
        @keydown.window.ctrl.a.prevent="selectAll = true; toggleSelectAll()"
        @scroll.window="if (filterDropdownOpen) filterDropdownOpen = false; if (bulkOpen) bulkOpen = false"
        class="space-y-4"
    >
        <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h2 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">Supplier Payments</h2>
                <p class="text-sm text-ink-muted mt-1">Payments issued for purchases and refunds received for purchase returns.</p>
            </div>

            
            <div class="flex flex-wrap items-center gap-2">
                <?php if(auth()->user()->hasPermission('Download Supplier Payment PDF')): ?>
                    <a href="<?php echo e(route('supplier-payments.export.pdf', request()->query())); ?>"
                       class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-semibold rounded-lg bg-red-50 hover:bg-red-100 text-red-700 border border-red-200 dark:bg-red-950/30 dark:border-red-900/40 dark:text-red-300 transition-colors shadow-xs">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Export PDF
                    </a>
                <?php endif; ?>
                <?php if(auth()->user()->hasPermission('Download Supplier Payment CSV')): ?>
                    <a href="<?php echo e(route('supplier-payments.export.csv', request()->query())); ?>"
                       class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-semibold rounded-lg bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border border-emerald-200 dark:bg-emerald-950/30 dark:border-emerald-900/40 dark:text-emerald-300 transition-colors shadow-xs">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Export CSV
                    </a>
                <?php endif; ?>
                <?php if(auth()->user()->hasPermission('Download Supplier Payment PDF') || auth()->user()->hasPermission('Download Supplier Payment CSV')): ?>
                    <button type="button" @click.stop="printSelected()"
                            class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-semibold rounded-lg bg-white hover:bg-gray-50 text-gray-700 border border-gray-300 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-200 dark:hover:bg-gray-750 transition-colors shadow-xs">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                        </svg>
                        Print Selected
                    </button>
                <?php endif; ?>
            </div>
        </div>

        
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xs border border-gray-100 dark:border-gray-700/60 p-4 sm:p-5">
            <form id="search-form"
                  method="GET"
                  action="<?php echo e(route('supplier-payments.index')); ?>"
                  x-data="purchaseDateRange({ dateFrom: <?php echo \Illuminate\Support\Js::from($startDate)->toHtml() ?>, dateTo: <?php echo \Illuminate\Support\Js::from($endDate)->toHtml() ?> })"
                  x-ref="filterForm"
                  class="flex flex-wrap items-center gap-3">
                <input type="hidden" name="date_from" x-model="dateFrom" />
                <input type="hidden" name="date_to" x-model="dateTo" />
                <input type="hidden" name="filter" value="<?php echo e($filter); ?>" />

                
                <div class="relative flex w-full sm:w-auto shrink-0">
                    <input type="text" name="search" value="<?php echo e($search); ?>" placeholder="Search PO, supplier, TRX..."
                           title="Search by supplier name, PO no., or TRX"
                           class="w-full pl-3.5 pr-8 py-2 border border-[#5542ff]/40 dark:border-gray-600 rounded-l-md text-sm bg-white dark:bg-gray-900 text-ink-heading focus:ring-1 focus:ring-[#5542ff] focus:border-[#5542ff] placeholder-gray-400" />
                    <?php if($search): ?>
                        <a href="<?php echo e(request()->fullUrlWithQuery(['search' => null])); ?>" class="absolute right-10 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 text-xs h-4 w-4 flex items-center justify-center" title="Clear">&#x2715;</a>
                    <?php endif; ?>
                    <button type="submit" class="px-3 gradient-bg text-white rounded-r-md hover:opacity-90 transition-colors flex items-center justify-center shrink-0" title="Search">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                    </button>
                </div>

                
                <div class="shrink-0" @keydown.escape.window="escapeClose()">
                    <?php echo $__env->make('components.date-range-picker', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                </div>

                
                <div class="relative shrink-0">
                    <button type="button"
                            @click="filterDropdownOpen = !filterDropdownOpen"
                            @click.outside="filterDropdownOpen = false"
                            class="inline-flex items-center gap-1.5 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-900 text-sm text-gray-700 dark:text-gray-200 focus:ring-1 focus:ring-[#5542ff] focus:border-[#5542ff] hover:bg-gray-50 dark:hover:bg-gray-750 transition-colors">
                        <?php if($filter === 'paid_for_purchase'): ?> Paid For Purchase
                        <?php elseif($filter === 'received_for_purchase_return'): ?> Received For Purchase Return
                        <?php else: ?> All Transactions <?php endif; ?>
                        <svg class="h-4 w-4 text-gray-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>

                    <div x-show="filterDropdownOpen"
                         x-cloak
                         x-transition
                         class="absolute top-full left-0 mt-1 w-64 bg-white dark:bg-gray-800 rounded-lg shadow-xl border border-gray-100 dark:border-gray-700 py-1 z-50">
                        <?php $__currentLoopData = [
                            'all' => 'All Transactions',
                            'paid_for_purchase' => 'Paid For Purchase',
                            'received_for_purchase_return' => 'Received For Purchase Return',
                        ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $val => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <a href="<?php echo e(route('supplier-payments.index', array_merge(request()->query(), ['filter' => $val]))); ?>"
                               class="block w-full text-left px-4 py-2 text-sm transition-colors <?php echo e($filter === $val ? 'bg-[#5542ff]/10 text-[#5542ff] dark:text-indigo-300 font-medium' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700/50'); ?>">
                                <?php echo e($label); ?>

                            </a>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                </div>

                
                <div class="relative shrink-0">
                    <select name="payment_method" onchange="document.getElementById('search-form').submit()"
                            class="text-sm border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-900 pl-3 pr-8 py-2 text-gray-700 dark:text-gray-200 focus:ring-1 focus:ring-[#5542ff] focus:border-[#5542ff] appearance-none cursor-pointer">
                        <option value="all" <?php if($paymentMethod === 'all'): echo 'selected'; endif; ?>>All Payment Methods</option>
                        <option value="cash" <?php if($paymentMethod === 'cash'): echo 'selected'; endif; ?>>Cash</option>
                        <option value="check" <?php if($paymentMethod === 'check'): echo 'selected'; endif; ?>>Check</option>
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-2.5 text-ink-muted">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </div>
                </div>
            </form>

            
            <?php if(request()->hasAny(['search', 'date_from', 'date_to']) || $filter !== 'all' || $paymentMethod !== 'all'): ?>
                <div class="flex flex-wrap items-center gap-2 mt-3 pt-3 border-t border-gray-100 dark:border-gray-700/60">
                    <span class="text-xs font-semibold text-ink-muted">Active Filters:</span>

                    <?php if($search): ?>
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium bg-[#5542ff]/10 text-[#5542ff] dark:text-indigo-300 border border-[#5542ff]/20">
                            Search: "<?php echo e($search); ?>"
                            <a href="<?php echo e(request()->fullUrlWithQuery(['search' => null])); ?>" class="hover:text-red-500 font-bold ml-1" title="Remove">&times;</a>
                        </span>
                    <?php endif; ?>

                    <?php if(request('date_from') || request('date_to')): ?>
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium bg-[#5542ff]/10 text-[#5542ff] dark:text-indigo-300 border border-[#5542ff]/20">
                            Date: <?php echo e(request('date_from', '…')); ?> – <?php echo e(request('date_to', '…')); ?>

                            <a href="<?php echo e(request()->fullUrlWithQuery(['date_from' => null, 'date_to' => null])); ?>" class="hover:text-red-500 font-bold ml-1" title="Remove">&times;</a>
                        </span>
                    <?php endif; ?>

                    <?php if($filter !== 'all'): ?>
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium bg-[#5542ff]/10 text-[#5542ff] dark:text-indigo-300 border border-[#5542ff]/20">
                            Type: <?php echo e(ucwords(str_replace('_', ' ', $filter))); ?>

                            <a href="<?php echo e(request()->fullUrlWithQuery(['filter' => 'all'])); ?>" class="hover:text-red-500 font-bold ml-1" title="Remove">&times;</a>
                        </span>
                    <?php endif; ?>

                    <?php if($paymentMethod !== 'all'): ?>
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium bg-[#5542ff]/10 text-[#5542ff] dark:text-indigo-300 border border-[#5542ff]/20">
                            Method: <?php echo e(ucfirst($paymentMethod)); ?>

                            <a href="<?php echo e(request()->fullUrlWithQuery(['payment_method' => 'all'])); ?>" class="hover:text-red-500 font-bold ml-1" title="Remove">&times;</a>
                        </span>
                    <?php endif; ?>

                    <a href="<?php echo e(route('supplier-payments.index')); ?>" class="text-xs text-red-600 dark:text-red-400 hover:underline font-semibold ml-1">Reset All Filters</a>
                </div>
            <?php endif; ?>
        </div>

        <?php if(session('success')): ?>
            <div class="rounded-lg bg-green-50 border border-green-200 text-green-800 px-4 py-3 text-sm"><?php echo e(session('success')); ?></div>
        <?php endif; ?>
        <?php if(session('error')): ?>
            <div class="rounded-lg bg-red-50 border border-red-200 text-red-800 px-4 py-3 text-sm"><?php echo e(session('error')); ?></div>
        <?php endif; ?>
        <?php if($errors->any()): ?>
            <div class="rounded-lg bg-red-50 border border-red-200 text-red-800 px-4 py-3 text-sm">
                <div class="font-semibold mb-1">Please fix the following:</div>
                <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div><?php echo e($error); ?></div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        <?php endif; ?>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-center border-collapse min-w-[980px]">
                    <thead>
                        <tr class="gradient-bg text-white">
                            <th class="checkbox-column px-4 py-3.5 text-[11px] font-bold uppercase tracking-wide w-10">
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="checkbox" x-model="selectAll" @change="toggleSelectAll" class="rounded border-gray-300 text-[#5542ff] focus:ring-[#5542ff]">
                                    <span class="sr-only">Select All</span>
                                </label>
                            </th>
                            <th class="px-4 py-4 text-[11px] font-bold uppercase tracking-wide text-center">S.N.</th>
                            <th class="px-4 py-4 text-[11px] font-bold uppercase tracking-wide text-center">Invoice No.</th>
                            <th class="px-4 py-4 text-[11px] font-bold uppercase tracking-wide text-center">Date</th>
                            <th class="px-4 py-4 text-[11px] font-bold uppercase tracking-wide text-center">Supplier</th>
                            <th class="px-4 py-4 text-[11px] font-bold uppercase tracking-wide text-center">TRX</th>
                            <th class="px-4 py-4 text-[11px] font-bold uppercase tracking-wide text-center">Payment Method</th>
                            <th class="px-4 py-4 text-[11px] font-bold uppercase tracking-wide text-center">Check Number</th>
                            <th class="px-4 py-4 text-[11px] font-bold uppercase tracking-wide text-center">Check Date</th>
                            <th class="px-4 py-4 text-[11px] font-bold uppercase tracking-wide text-center">Type</th>
                            <th class="px-4 py-4 text-[11px] font-bold uppercase tracking-wide text-center">Amount</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <?php $__empty_1 = true; $__currentLoopData = $payments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $payment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <?php
                                $isPayment = $payment['type'] === 'payment';
                            ?>
                            <tr :class="{ 'print-hidden': !selectedItems.includes(String('<?php echo e($payment['trx']); ?>')) }" class="hover:bg-gray-50/90 transition-colors bg-white">

                                <td class="checkbox-column px-4 py-3.5 align-middle">
                                    <input type="checkbox" value="<?php echo e($payment['trx']); ?>" x-model="selectedItems" class="row-checkbox rounded border-gray-300 text-[#5542ff] focus:ring-[#5542ff]">
                                </td>
                                <td class="px-4 py-4 align-middle text-sm text-gray-600"><?php echo e($i + 1); ?></td>
                                <td class="px-4 py-4 align-middle text-sm font-bold text-[#2563eb]"><?php echo e($payment['invoice_no']); ?></td>
                                <td class="px-4 py-4 align-middle text-sm text-gray-600">
                                    <?php echo e($payment['date'] instanceof \DateTimeInterface ? $payment['date']->format('F j, Y') : (\Carbon\Carbon::canBeCreatedFromFormat($payment['date'], 'Y-m-d') ? \Carbon\Carbon::parse($payment['date'])->format('F j, Y') : $payment['date'])); ?>

                                </td>
                                <td class="px-4 py-4 align-middle text-sm font-medium text-gray-700">
                                    <?php echo e($payment['supplier']->name); ?>

                                </td>
                                <td class="px-4 py-4 align-middle text-sm font-mono text-gray-600">
                                    <?php echo e($payment['trx']); ?>

                                </td>
                                <td class="px-4 py-4 align-middle">
                                    <?php if(isset($payment['payment_method'])): ?>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-semibold bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200">
                                            <?php echo e(ucfirst($payment['payment_method'])); ?>

                                        </span>
                                    <?php else: ?>
                                        <span class="text-gray-400">—</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-4 py-4 align-middle text-sm text-gray-600">
                                    <?php echo e($payment['check_number'] ?? '—'); ?>

                                </td>
                                <td class="px-4 py-4 align-middle text-sm text-gray-600">
                                    <?php echo e($payment['check_date'] ? $payment['check_date']->format('F j, Y') : '—'); ?>

                                </td>
                                <td class="px-4 py-4 align-middle">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-semibold bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200">
                                        <?php echo e($isPayment ? 'Paid' : 'Refunded'); ?>

                                    </span>
                                </td>
                                <td class="px-4 py-4 align-middle text-sm font-bold text-gray-900 dark:text-white">
                                    <?php echo e(formatCurrency($payment['amount'])); ?>

                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="11" class="px-6 py-12 text-center text-gray-500">No supplier payments found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
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
<?php /**PATH C:\laragon\www\GE-System-1.1\resources\views/supplier-payments/index.blade.php ENDPATH**/ ?>