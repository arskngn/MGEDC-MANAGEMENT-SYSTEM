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
    <script>
        function showAdjustmentModal(title, subtitle, options, onClose) {
            var existing = document.getElementById('_adjustment_action_modal');
            if (existing) existing.remove();

            var overlay = document.createElement('div');
            overlay.id = '_adjustment_action_modal';
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
            bulkOpen: false,
            toggleSelectAll() {
                if (this.selectAll) {
                    this.selectedItems = Array.from(document.querySelectorAll('.row-checkbox')).map(cb => cb.value);
                } else {
                    this.selectedItems = [];
                }
            },
            printSelected() {
                if (this.selectedItems.length === 0) {
                    showAdjustmentModal('No Records Selected', 'Please select at least one adjustment to print.', [], null);
                    return;
                }
                const ids = this.selectedItems.join(',');
                this.bulkOpen = false;
                showAdjustmentModal('Print Options', 'How would you like to print the selected adjustments?', [
                    { label: 'Batch Print', desc: 'Print each adjustment as a separate slip on its own page.', handler: function() { window.open('/adjustments/export/pdf?ids=' + ids + '&print=true&type=batch', '_blank'); } },
                    { label: 'Total Print', desc: 'Print all selected adjustments combined in a single summary table.', handler: function() { window.open('/adjustments/export/pdf?ids=' + ids + '&print=true&type=total', '_blank'); } },
                ], null);
            },
            downloadSelected() {
                if (this.selectedItems.length === 0) {
                    showAdjustmentModal('No Records Selected', 'Please select at least one adjustment to download.', [], null);
                    return;
                }
                const ids = this.selectedItems.join(',');
                this.bulkOpen = false;
                showAdjustmentModal('Download Options', 'How would you like to download the selected adjustments?', [
                    { label: 'Batch Download', desc: 'Download each adjustment slip PDF separately.', handler: function() { window.location.href = '/adjustments/export/pdf?ids=' + ids + '&type=batch'; } },
                    { label: 'Total Download', desc: 'Download a single consolidated summary PDF.', handler: function() { window.location.href = '/adjustments/export/pdf?ids=' + ids + '&type=total'; } },
                ], null);
            },
            openMoreId: null,
            dropdownTop: '0px',
            dropdownRight: '0px',
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
        }"
        @keydown.escape.window="bulkOpen = false; openMoreId = null"
        @keydown.window.ctrl.p.prevent="printSelected()"
        @keydown.window.ctrl.a.prevent="selectAll = true; toggleSelectAll()"
        @scroll.window="if (openMoreId) openMoreId = null"
        class="space-y-4"
    >
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="text-lg sm:text-xl font-bold text-[#0a1233] shrink-0">All Adjustments</h2>

            <div class="flex flex-col sm:flex-row sm:items-center gap-2.5 w-full sm:w-auto">
                <form
                    method="GET"
                    action="<?php echo e(route('adjustments.index')); ?>"
                    x-data="purchaseDateRange({ dateFrom: <?php echo \Illuminate\Support\Js::from(request('date_from'))->toHtml() ?>, dateTo: <?php echo \Illuminate\Support\Js::from(request('date_to'))->toHtml() ?> })"
                    x-ref="filterForm"
                    @keydown.escape.window="escapeClose()"
                    class="w-full sm:flex sm:flex-row sm:items-center sm:gap-2 sm:w-auto"
                >
                    <input type="hidden" name="date_from" x-model="dateFrom" />
                    <input type="hidden" name="date_to" x-model="dateTo" />

                    <div class="flex items-center gap-2 sm:contents">
                        
                        <div class="relative flex flex-1 sm:w-[240px] sm:flex-none">
                            <input
                                type="text"
                                name="search"
                                value="<?php echo e(request('search')); ?>"
                                placeholder="Search..."
                                class="w-full pl-3 pr-8 py-2 border border-[#5542ff]/40 rounded-l-xl sm:rounded-l-md focus:outline-none focus:ring-1 focus:ring-[#5542ff] focus:border-[#5542ff] text-sm bg-white placeholder-gray-400"
                            />
                            <?php if(request('search')): ?>
                                <a href="<?php echo e(request()->fullUrlWithQuery(['search' => null])); ?>" class="absolute right-9 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 text-xs px-1 h-4 w-4 flex items-center justify-center" title="Clear search">&#x2715;</a>
                            <?php endif; ?>
                            <button type="submit" class="px-3 gradient-bg text-white rounded-r-xl sm:rounded-r-md hover:opacity-90 transition-colors flex items-center justify-center shrink-0" title="Search">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                            </button>
                        </div>

                        <?php echo $__env->make('components.date-range-picker', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                    </div>
                </form>

                <div class="flex items-center gap-2 shrink-0 justify-end">
                    <?php if(auth()->user()->hasPermission('Create Adjustment')): ?>
                        <a href="<?php echo e(route('adjustments.create')); ?>" class="inline-flex items-center justify-center px-3 sm:px-4 py-2 bg-white border border-[#5542ff] text-[#5542ff] rounded-xl sm:rounded-md hover:bg-[#5542ff]/5 transition-colors text-sm font-semibold whitespace-nowrap">
                            <svg class="h-4 w-4 sm:mr-1.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                            <span class="hidden sm:inline">Add New</span>
                            <span class="sm:hidden">Add</span>
                        </a>
                    <?php endif; ?>

                    <?php if(auth()->user()->hasPermission('Download Adjustment PDF') || auth()->user()->hasPermission('Download Adjustment CSV')): ?>
                        <div class="relative">
                            <button type="button" @click="bulkOpen = !bulkOpen; openMoreId = null" class="inline-flex items-center px-4 py-2 bg-[#5542ff] border border-[#5542ff] text-white rounded-xl sm:rounded-md hover:bg-[#4535e8] text-sm font-semibold shadow-sm">
                                Action
                                <svg class="h-4 w-4 ml-1 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                            </button>
                            <div
                                x-show="bulkOpen"
                                @click.outside="bulkOpen = false"
                                x-cloak
                                class="absolute right-0 mt-1 w-48 bg-white rounded-lg shadow-lg border border-gray-100 z-[200] py-1"
                            >
                                <?php if(auth()->user()->hasPermission('Download Adjustment PDF')): ?>
                                    <a href="<?php echo e(route('adjustments.export.pdf', request()->query())); ?>" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                                        <svg class="h-4 w-4 mr-2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>
                                        Download PDF
                                    </a>
                                <?php endif; ?>
                                <button type="button" @click="printSelected()" class="w-full flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 text-left">
                                    <svg class="h-4 w-4 mr-2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" /></svg>
                                    Print Selected
                                </button>
                                <button type="button" @click="downloadSelected()" class="w-full flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 text-left">
                                    <svg class="h-4 w-4 mr-2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>
                                    Download Selected
                                </button>
                                <?php if(auth()->user()->hasPermission('Download Adjustment CSV')): ?>
                                    <a href="<?php echo e(route('adjustments.export.csv', request()->query())); ?>" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                                        <svg class="h-4 w-4 mr-2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                                        Download CSV
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        
        <?php if(request()->hasAny(['search', 'date_from', 'date_to'])): ?>
            <div class="flex flex-wrap items-center gap-2 px-1 py-1">
                <span class="text-xs font-semibold text-gray-500">Active Filters:</span>

                <?php if(request('search')): ?>
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium bg-[#5542ff]/10 text-[#5542ff] border border-[#5542ff]/20">
                        Search: "<?php echo e(request('search')); ?>"
                        <a href="<?php echo e(request()->fullUrlWithQuery(['search' => null])); ?>" class="hover:text-red-500 font-bold ml-1" title="Remove">&times;</a>
                    </span>
                <?php endif; ?>

                <?php if(request('date_from') || request('date_to')): ?>
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium bg-[#5542ff]/10 text-[#5542ff] border border-[#5542ff]/20">
                        Date: <?php echo e(request('date_from', '…')); ?> – <?php echo e(request('date_to', '…')); ?>

                        <a href="<?php echo e(request()->fullUrlWithQuery(['date_from' => null, 'date_to' => null])); ?>" class="hover:text-red-500 font-bold ml-1" title="Remove">&times;</a>
                    </span>
                <?php endif; ?>

                <a href="<?php echo e(route('adjustments.index')); ?>" class="text-xs text-red-600 hover:underline font-semibold ml-1">Reset All Filters</a>
            </div>
        <?php endif; ?>

        <?php if(session('success')): ?>
            <div class="rounded-lg bg-green-50 border border-green-200 text-green-800 px-4 py-3 text-sm"><?php echo e(session('success')); ?></div>
        <?php endif; ?>
        <?php if(session('error')): ?>
            <div class="rounded-lg bg-red-50 border border-red-200 text-red-800 px-4 py-3 text-sm"><?php echo e(session('error')); ?></div>
        <?php endif; ?>

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
                            <th class="px-4 py-3.5 text-[11px] font-bold uppercase tracking-wide">S.N.</th>
                            <th class="px-4 py-3.5 text-[11px] font-bold uppercase tracking-wide">Tracking No.</th>
                            <th class="px-4 py-3.5 text-[11px] font-bold uppercase tracking-wide">Date</th>
                            <th class="px-4 py-3.5 text-[11px] font-bold uppercase tracking-wide">Reason</th>
                            <th class="px-4 py-3.5 text-[11px] font-bold uppercase tracking-wide">Products</th>
                            <th class="px-4 py-3.5 text-[11px] font-bold uppercase tracking-wide text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <?php $__empty_1 = true; $__currentLoopData = $adjustments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $adjustment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr class="hover:bg-gray-50/90 transition-colors bg-white">

                                <td class="checkbox-column px-4 py-3.5 align-middle">
                                    <input type="checkbox" value="<?php echo e($adjustment->id); ?>" x-model="selectedItems" class="row-checkbox rounded border-gray-300 text-[#5542ff] focus:ring-[#5542ff]">
                                </td>
                                <td class="px-4 py-3.5 align-top">
                                    <div class="text-sm font-semibold text-gray-900"><?php echo e(($adjustments->currentPage() - 1) * $adjustments->perPage() + $i + 1); ?></div>
                                </td>
                                <td class="px-4 py-3.5 align-top">
                                    <div class="text-sm font-bold text-gray-900"><?php echo e($adjustment->tracking_no); ?></div>
                                </td>
                                <td class="px-4 py-3.5 align-top">
                                    <div class="text-sm text-gray-600"><?php echo e($adjustment->adjustment_date->format('d M, Y')); ?></div>
                                </td>
                                <td class="px-4 py-3.5 align-top">
                                    <?php if($adjustment->reason): ?>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-gray-100 text-gray-800 border border-gray-200">
                                            <?php echo e($adjustment->reason); ?>

                                        </span>
                                    <?php else: ?>
                                        <span class="text-sm text-gray-400">—</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-4 py-3.5 align-top">
                                    <div class="text-sm text-gray-600"><?php echo e(count($adjustment->items)); ?></div>
                                </td>
                                <td class="px-4 py-3.5 text-right align-top">
                                    <div class="flex justify-end gap-2 flex-wrap">
                                        <?php if(auth()->user()->hasPermission('Edit Adjustment')): ?>
                                            <a href="<?php echo e(route('adjustments.edit', $adjustment)); ?>" class="inline-flex items-center px-3 py-1.5 bg-blue-50 text-blue-600 border border-blue-300 rounded hover:bg-blue-100 transition-colors text-xs font-semibold">
                                                <svg class="h-3.5 w-3.5 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                                Edit
                                            </a>
                                        <?php endif; ?>
                                        <?php if(auth()->user()->hasPermission('Download Adjustment PDF')): ?>
                                            <a href="<?php echo e(route('adjustments.pdf', $adjustment)); ?>?print=true" target="_blank" class="inline-flex items-center px-3 py-1.5 bg-white border border-[#5542ff] text-[#5542ff] rounded hover:bg-[#5542ff]/5 transition-colors text-xs font-semibold">
                                                <svg class="h-3.5 w-3.5 mr-1.5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" /></svg>
                                                Print
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="7" class="px-4 py-8 text-center text-gray-500">
                                    <div class="flex flex-col items-center gap-2">
                                        <svg class="h-12 w-12 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" /></svg>
                                        <p>No adjustments found</p>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="flex justify-center">
            <?php echo e($adjustments->links()); ?>

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
<?php /**PATH C:\laragon\www\GE-System-1.1\resources\views/adjustments/index.blade.php ENDPATH**/ ?>