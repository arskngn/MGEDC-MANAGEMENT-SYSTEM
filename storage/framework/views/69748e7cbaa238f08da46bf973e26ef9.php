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
        width: 100%;
        margin: 0;
        padding: 0;
    }

    /* Hide the non-selected rows */
    .print-hidden { 
        display: none !important; 
    }

    /* Hide the Action and Checkbox columns */
    th:first-child, td:first-child, th:last-child, td:last-child {
        display: none !important;
    }

    /* Ensure backgrounds print correctly */
    * {
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }

    @page { size: auto; margin: 10mm; }
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
                const ids = this.selectedItems.join(',');
                window.open('<?php echo e(route('suppliers.export.pdf')); ?>?ids=' + ids + '&print=true', '_blank');
            },
            bulkOpen: false,
            importOpen: <?php echo e($errors->has('csv_file') ? 'true' : 'false'); ?>,
            addOpen: false,
            editOpen: false,
            actionTop: '0px',
            actionRight: '0px',
            editActionUrl: '',
            editSupplier: {
                id: null,
                name: '',
                email: '',
                mobile: '',
                company_name: '',
                address: '',
            },
            toggleAction($event) {
                this.bulkOpen = !this.bulkOpen;
                if (this.bulkOpen) {
                    const r = $event.currentTarget.getBoundingClientRect();
                    this.actionTop = (r.bottom + 4) + 'px';
                    this.actionRight = (window.innerWidth - r.right) + 'px';
                }
            },
            openEditSupplier(s) {
                this.editSupplier = {
                    id: s.id,
                    name: s.name ?? '',
                    email: s.email ?? '',
                    mobile: s.phone ?? '',
                    company_name: s.company_name ?? '',
                    address: s.address ?? '',
                };
                this.editActionUrl = `/suppliers/${s.id}`;
                this.editOpen = true;
                this.addOpen = false;
                this.bulkOpen = false;
            }
        }"
        @keydown.escape.window="importOpen = false; addOpen = false; editOpen = false; bulkOpen = false"
        @keydown.window.ctrl.p.prevent="printSelected()"
        @keydown.window.ctrl.a.prevent="selectAll = true; toggleSelectAll()"
        @scroll.window="if (bulkOpen) bulkOpen = false"
        class="space-y-4"
    >
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="text-xl font-bold text-[#0a1233] dark:text-gray-100 shrink-0">Suppliers</h2>

            <div class="flex flex-col sm:flex-row sm:items-center gap-2.5 w-full sm:w-auto">
                <form method="GET" action="<?php echo e(route('suppliers.index')); ?>" class="w-full sm:w-auto">
                    <div class="relative flex w-full sm:w-[240px]">
                        <input
                            type="text"
                            name="search"
                            value="<?php echo e(request('search')); ?>"
                            placeholder="Search name, phone, company..."
                            title="Search by supplier name, phone, email, or company"
                            class="w-full pl-3 pr-8 py-2 border border-[#5542ff]/40 dark:border-gray-600 rounded-l-xl sm:rounded-l-md focus:outline-none focus:ring-1 focus:ring-[#5542ff] focus:border-[#5542ff] text-sm bg-white dark:bg-gray-900 text-ink-heading placeholder-gray-400 dark:placeholder-gray-500"
                        />
                        <?php if(request('search')): ?>
                            <a href="<?php echo e(request()->fullUrlWithQuery(['search' => null])); ?>" class="absolute right-9 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 text-xs px-1 h-4 w-4 flex items-center justify-center" title="Clear search">&#x2715;</a>
                        <?php endif; ?>
                        <button type="submit" class="px-3 gradient-bg text-white rounded-r-xl sm:rounded-r-md hover:opacity-90 transition-colors flex items-center justify-center shrink-0" title="Search">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                        </button>
                    </div>
                </form>

                <div class="flex items-center gap-2 shrink-0 justify-end">
                    <?php if(auth()->user()->hasPermission('Store Supplier')): ?>
                        <button
                            type="button"
                            @click="addOpen = true; editOpen = false; bulkOpen = false"
                            class="inline-flex items-center justify-center px-3 sm:px-4 py-2 bg-white dark:bg-gray-800 border border-[#5542ff] text-[#5542ff] dark:text-gray-100 dark:border-gray-600 rounded-xl sm:rounded-md hover:bg-[#5542ff]/5 dark:hover:bg-gray-700 transition-colors text-sm font-semibold whitespace-nowrap"
                        >
                            <svg class="h-4 w-4 sm:mr-1.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            <span class="hidden sm:inline">Add New</span>
                            <span class="sm:hidden">Add</span>
                        </button>
                    <?php endif; ?>

                    <?php if(auth()->user()->hasPermission('Download Supplier PDF') || auth()->user()->hasPermission('Download Supplier CSV') || auth()->user()->hasPermission('Import Suppliers')): ?>
                        <div class="relative inline-flex">
                            <button type="button" @click.stop="toggleAction($event)" class="inline-flex items-center justify-center px-4 py-2.5 sm:py-2 bg-[#5542ff] border border-[#5542ff] text-white rounded-xl sm:rounded-md hover:bg-[#4535e8] text-sm font-semibold shadow-sm">
                                Action
                                <svg class="h-4 w-4 ml-1 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>

                            <template x-teleport="body">
                                <div
                                    x-show="bulkOpen"
                                    @click.outside="bulkOpen = false"
                                    x-cloak
                                    class="fixed w-52 bg-white dark:bg-gray-800 rounded-lg shadow-xl border border-gray-100 dark:border-gray-700 py-1 z-[200]"
                                    :style="{ top: actionTop, right: actionRight }"
                                >
                                    <?php if(auth()->user()->hasPermission('Download Supplier PDF')): ?>
                                        <a href="<?php echo e(route('suppliers.export.pdf', request()->query())); ?>" class="flex items-center px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700">
                                            <svg class="h-4 w-4 mr-2 text-gray-400 dark:text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>
                                            Download PDF
                                        </a>
                                    <?php endif; ?>
                            <button type="button" @click="printSelected()" class="w-full flex items-center px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 text-left">
                                <svg class="h-4 w-4 mr-2 text-gray-400 dark:text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" /></svg>
                                Print Selected
                            </button>
                                    <?php if(auth()->user()->hasPermission('Download Supplier CSV')): ?>
                                        <a href="<?php echo e(route('suppliers.export.csv', request()->query())); ?>" class="flex items-center px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700">
                                            <svg class="h-4 w-4 mr-2 text-gray-400 dark:text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                                            Download CSV
                                        </a>
                                    <?php endif; ?>
                                    <?php if(auth()->user()->hasPermission('Import Suppliers')): ?>
                                        <button type="button" @click="importOpen = true; bulkOpen = false" class="w-full flex items-center px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 text-left">
                                            <svg class="h-4 w-4 mr-2 text-gray-400 dark:text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" /></svg>
                                            Import CSV
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </template>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        
        <?php if(request('search')): ?>
            <div class="flex flex-wrap items-center gap-2 px-1 py-1">
                <span class="text-xs font-semibold text-ink-muted">Active Filters:</span>
                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium bg-[#5542ff]/10 text-[#5542ff] dark:bg-[#5542ff]/20 dark:text-indigo-300 border border-[#5542ff]/20">
                    Search: "<?php echo e(request('search')); ?>"
                    <a href="<?php echo e(request()->fullUrlWithQuery(['search' => null])); ?>" class="hover:text-red-500 font-bold ml-1" title="Remove search filter">&times;</a>
                </span>
                <a href="<?php echo e(route('suppliers.index')); ?>" class="text-xs text-red-600 dark:text-red-400 hover:underline font-semibold ml-1">
                    Reset All Filters
                </a>
            </div>
        <?php endif; ?>

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

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-center border-collapse min-w-[900px]">
                    <thead>
                        <tr class="gradient-bg text-white">
                            <th class="checkbox-column px-4 py-3.5 text-[11px] font-bold uppercase tracking-wide w-10">
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="checkbox" x-model="selectAll" @change="toggleSelectAll" class="rounded border-gray-300 text-[#5542ff] focus:ring-[#5542ff]">
                                    <span class="sr-only">Select All</span>
                                </label>
                            </th>
                            <th class="px-4 py-4 text-[11px] font-bold uppercase tracking-wide text-center">S.N.</th>
                            <th class="px-4 py-4 text-[11px] font-bold uppercase tracking-wide text-center">Name</th>
                            <th class="px-4 py-4 text-[11px] font-bold uppercase tracking-wide text-center">Mobile | Email</th>
                            <th class="px-4 py-4 text-[11px] font-bold uppercase tracking-wide text-center">Payable</th>
                            <th class="px-4 py-4 text-[11px] font-bold uppercase tracking-wide text-center">Receivable</th>
                            <th class="px-4 py-4 text-[11px] font-bold uppercase tracking-wide text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        <?php $__empty_1 = true; $__currentLoopData = $suppliers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $supplier): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <?php
                                $payable = (float) ($supplier->payable_total ?? 0);
                                $receivable = (float) ($supplier->receivable_total ?? 0);
                                // The payment button should be enabled if there is either a payable OR a receivable balance to settle.
                                $paymentDisabled = $payable <= 0.009 && $receivable <= 0.009;
                            ?>
                            <tr :class="{ 'print-hidden': !selectedItems.includes(String('<?php echo e($supplier->id); ?>')) }" class="hover:bg-gray-50/90 transition-colors bg-white">

                                <td class="checkbox-column px-4 py-3.5 align-middle">
                                    <input type="checkbox" value="<?php echo e($supplier->id); ?>" x-model="selectedItems" class="row-checkbox rounded border-gray-300 text-[#5542ff] focus:ring-[#5542ff]">
                                </td>
                                <td class="px-4 py-4 align-middle text-sm text-gray-600"><?php echo e($i + 1); ?></td>
                                <td class="px-4 py-4 align-middle">
                                    <div class="text-sm font-bold text-[#5542ff] leading-snug"><?php echo e($supplier->name); ?></div>
                                </td>
                                <td class="px-4 py-4 align-middle text-sm font-medium text-gray-600">
                                    <div class="flex flex-col items-start leading-snug">
                                        <span><?php echo e($supplier->phone ?? '—'); ?></span>
                                        <span class="text-[12px] text-gray-500"><?php echo e($supplier->email ?? '—'); ?></span>
                                    </div>
                                </td>
                                <td class="px-4 py-4 align-middle text-sm font-bold <?php echo e($payable > 0.009 ? 'text-red-600' : 'text-gray-600'); ?>">
                                    <?php echo e(formatCurrency($payable)); ?>

                                </td>
                                <td class="px-4 py-4 align-middle text-sm font-bold <?php echo e($receivable > 0.009 ? 'text-green-600' : 'text-gray-600'); ?>">
                                    <?php echo e(formatCurrency($receivable)); ?>

                                </td>
                                <td class="px-4 py-4 align-middle">
                                    <div class="flex justify-center gap-2 items-center">
                                        <?php if(auth()->user()->hasPermission('Store Supplier')): ?>
                                            <button
                                                type="button"
                                                class="inline-flex items-center justify-center px-2.5 py-1.5 bg-white border border-[#5542ff] text-[#5542ff] rounded-md hover:bg-[#5542ff]/5 text-[11px] font-bold"
                                                @click="openEditSupplier({ id: <?php echo e($supplier->id); ?>, name: <?php echo \Illuminate\Support\Js::from($supplier->name)->toHtml() ?>, email: <?php echo \Illuminate\Support\Js::from($supplier->email)->toHtml() ?>, phone: <?php echo \Illuminate\Support\Js::from($supplier->phone)->toHtml() ?>, company_name: <?php echo \Illuminate\Support\Js::from($supplier->company_name)->toHtml() ?>, address: <?php echo \Illuminate\Support\Js::from($supplier->address)->toHtml() ?> })"
                                            >
                                                <svg class="h-3.5 w-3.5 mr-1 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                </svg>
                                                Edit
                                            </button>
                                        <?php endif; ?>

                                        <?php if(auth()->user()->hasPermission('Supplier Payment Index')): ?>
                                            <a
                                                href="<?php echo e(route('suppliers.payments.index', $supplier)); ?>"
                                                class="inline-flex items-center justify-center px-2.5 py-1.5 bg-white border border-[#2563eb] text-[#2563eb] rounded-md text-[11px] font-bold hover:bg-[#2563eb]/5 <?php echo e($paymentDisabled ? 'pointer-events-none opacity-50 cursor-not-allowed' : ''); ?>"
                                                <?php echo e($paymentDisabled ? 'tabindex=-1 aria-disabled=true' : ''); ?>

                                            >
                                                <svg class="h-3.5 w-3.5 mr-1 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2h2m8-6l-4 4m0 0l-4-4m4 4V9" />
                                                </svg>
                                                Payment
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="8" class="px-6 py-12 text-center text-gray-500">No suppliers found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <?php if($suppliers->hasPages()): ?>
                <div class="px-4 py-3 bg-gray-50 dark:bg-gray-900 border-t border-gray-100 dark:border-gray-700">
                    <?php echo e($suppliers->links()); ?>

                </div>
            <?php endif; ?>
        </div>

        
        <div x-show="importOpen" x-cloak class="fixed inset-0 z-[210] overflow-y-auto" style="display: none;">
            <div class="flex items-center justify-center min-h-screen px-4 py-8">
                <div class="fixed inset-0 bg-gray-900/50 z-0" @click="importOpen = false"></div>
                <div class="relative bg-white dark:bg-gray-800 rounded-xl shadow-xl max-w-lg w-full p-6 z-10 border border-gray-100 dark:border-gray-700" @click.stop>
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-bold text-[#0a1233]">Import Supplier</h3>
                        <button type="button" @click="importOpen = false" class="text-gray-400 hover:text-gray-600" aria-label="Close">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                        </button>
                    </div>

                    <div class="rounded-lg border border-amber-200 bg-amber-50 text-amber-950 text-xs p-3 mb-4 space-y-1">
                        <p class="font-semibold">CSV import requirements:</p>
                        <ul class="list-disc pl-4 space-y-1">
                            <li>Columns must be: <strong>name</strong>, <strong>email</strong>, <strong>mobile</strong>, <strong>company_name</strong>, <strong>address</strong>.</li>
                            <li>Required columns: <strong>name</strong>, <strong>mobile</strong>.</li>
                            <li>Cells for required fields must not be empty.</li>
                        </ul>
                    </div>

                    <form method="POST" action="<?php echo e(route('suppliers.import')); ?>" enctype="multipart/form-data" class="space-y-4">
                        <?php echo csrf_field(); ?>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Select File <span class="text-red-500">*</span></label>
                            <input type="file" name="csv_file" accept=".csv,.txt" required class="block w-full text-sm text-gray-600 file:mr-3 file:py-2 file:px-3 file:rounded-md file:border-0 file:bg-[#5542ff]/10 file:text-[#5542ff] file:font-semibold" />
                            <?php $__errorArgs = ['csv_file'];
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

                        <p class="text-sm text-gray-600">
                            Download sample template:
                            <a href="<?php echo e(route('suppliers.import.sample')); ?>" class="text-[#5542ff] font-semibold hover:underline">supplier.csv</a>
                        </p>

                        <button type="submit" class="w-full py-3 rounded-lg bg-[#5542ff] text-white font-semibold hover:bg-[#4736d6] transition-colors">
                            Import
                        </button>
                    </form>
                </div>
            </div>
        </div>

        
        <div x-show="addOpen" x-cloak class="fixed inset-0 z-[220] overflow-y-auto" style="display: none;">
            <div class="flex items-center justify-center min-h-screen px-4 py-8">
                <div class="fixed inset-0 bg-gray-900/50 z-0" @click="addOpen = false"></div>

                <div class="relative bg-white dark:bg-gray-800 rounded-2xl shadow-2xl max-w-lg w-full z-10 border border-gray-100 dark:border-gray-700" @click.stop>
                    
                    
                    <div class="px-6 py-5 flex justify-between items-center rounded-t-2xl" style="background: linear-gradient(135deg, var(--gradient-from) 0%, var(--gradient-to) 100%)">
                        <div class="flex items-center gap-3">
                            <div class="bg-white/20 rounded-lg p-2">
                                <svg class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-base font-bold text-white leading-tight">Add New Supplier</h3>
                                <p class="text-white/60 text-xs mt-0.5">Fill in the details below</p>
                            </div>
                        </div>
                        <button type="button" @click="addOpen = false" class="text-white/60 hover:text-white hover:bg-white/10 rounded-lg p-1.5 transition-colors" aria-label="Close">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                        </button>
                    </div>

                    
                    <div class="p-6">
                        <form method="POST" action="<?php echo e(route('suppliers.store')); ?>" class="space-y-4">
                            <?php echo csrf_field(); ?>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-semibold text-ink-muted uppercase tracking-wide mb-1.5">Company Name <span class="text-red-500">*</span></label>
                                    <div class="relative">
                                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>
                                        </span>
                                        <input type="text" name="company_name" value="<?php echo e(old('company_name')); ?>" required
                                            placeholder="Company name"
                                            class="w-full pl-9 pr-3 py-2.5 rounded-lg border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-900 text-ink-heading text-sm focus:outline-none focus:ring-1 focus:ring-[#5542ff] focus:border-[#5542ff] transition-all placeholder-gray-400" />
                                    </div>
                                    <?php $__errorArgs = ['company_name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-red-500 text-xs mt-1"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-ink-muted uppercase tracking-wide mb-1.5">E-Mail</label>
                                    <input type="email" name="email" value="<?php echo e(old('email')); ?>" 
                                        placeholder="email@example.com"
                                        class="w-full rounded-lg border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-900 text-ink-heading px-3 py-2.5 text-sm focus:outline-none focus:ring-1 focus:ring-[#5542ff] focus:border-[#5542ff] transition-all placeholder-gray-400" />
                                    <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-red-500 text-xs mt-1"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-ink-muted uppercase tracking-wide mb-1.5">Mobile <span class="text-red-500">*</span></label>
                                    <div class="relative">
                                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" /></svg>
                                        </span>
                                        <input type="tel" name="mobile" value="<?php echo e(old('mobile')); ?>" 
                                            oninput="this.value = this.value.replace(/[^0-9+]/g, '')" 
                                            required placeholder="09XXXXXXXXX"
                                            class="w-full pl-9 pr-3 py-2.5 rounded-lg border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-900 text-ink-heading text-sm focus:outline-none focus:ring-1 focus:ring-[#5542ff] focus:border-[#5542ff] transition-all placeholder-gray-400" maxlength="11" />
                                    </div>
                                    <?php $__errorArgs = ['mobile'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-red-500 text-xs mt-1"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-ink-muted uppercase tracking-wide mb-1.5">Address</label>
                                <?php if (isset($component)) { $__componentOriginal985fdef8b2246be3e74382951fe17eee = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal985fdef8b2246be3e74382951fe17eee = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.address-input','data' => ['id' => 'add_address','name' => 'address','value' => old('address', ''),'placeholder' => 'Enter full address...','class' => 'bg-gray-50 dark:bg-gray-900 focus:ring-1 focus:ring-[#5542ff] focus:border-[#5542ff] transition-all']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('address-input'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['id' => 'add_address','name' => 'address','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(old('address', '')),'placeholder' => 'Enter full address...','class' => 'bg-gray-50 dark:bg-gray-900 focus:ring-1 focus:ring-[#5542ff] focus:border-[#5542ff] transition-all']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal985fdef8b2246be3e74382951fe17eee)): ?>
<?php $attributes = $__attributesOriginal985fdef8b2246be3e74382951fe17eee; ?>
<?php unset($__attributesOriginal985fdef8b2246be3e74382951fe17eee); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal985fdef8b2246be3e74382951fe17eee)): ?>
<?php $component = $__componentOriginal985fdef8b2246be3e74382951fe17eee; ?>
<?php unset($__componentOriginal985fdef8b2246be3e74382951fe17eee); ?>
<?php endif; ?>
                            </div>

                            <div class="pt-2">
                                <button type="submit" class="w-full py-3 rounded-xl text-white font-semibold hover:opacity-90 transition-all shadow-md hover:shadow-lg flex items-center justify-center gap-2 text-sm tracking-wide" style="background: linear-gradient(135deg, var(--gradient-from) 0%, var(--gradient-to) 100%)">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                                    Add Supplier
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        
        <div x-show="editOpen" x-cloak class="fixed inset-0 z-[230] overflow-y-auto" style="display: none;">
            <div class="flex items-center justify-center min-h-screen px-4 py-8">
                <div class="fixed inset-0 bg-gray-900/50 z-0" @click="editOpen = false"></div>

                <div class="relative bg-white dark:bg-gray-800 rounded-2xl shadow-2xl max-w-lg w-full z-10 border border-gray-100 dark:border-gray-700" @click.stop>
                    
                    
                    <div class="px-6 py-5 flex justify-between items-center rounded-t-2xl" style="background: linear-gradient(135deg, var(--gradient-from) 0%, var(--gradient-to) 100%)">
                        <div class="flex items-center gap-3">
                            <div class="bg-white/20 rounded-lg p-2">
                                <svg class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-base font-bold text-white leading-tight">Edit Supplier</h3>
                                <p class="text-white/60 text-xs mt-0.5">Update supplier details</p>
                            </div>
                        </div>
                        <button type="button" @click="editOpen = false" class="text-white/60 hover:text-white hover:bg-white/10 rounded-lg p-1.5 transition-colors" aria-label="Close">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                        </button>
                    </div>

                    
                    <div class="p-6">
                        <form method="POST" :action="editActionUrl" class="space-y-4">
                            <?php echo csrf_field(); ?>
                            <?php echo method_field('PUT'); ?>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-semibold text-ink-muted uppercase tracking-wide mb-1.5">Company Name <span class="text-red-500">*</span></label>
                                    <div class="relative">
                                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>
                                        </span>
                                        <input type="text" name="company_name" x-model="editSupplier.company_name" required
                                            placeholder="Company name"
                                            class="w-full pl-9 pr-3 py-2.5 rounded-lg border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-900 text-ink-heading text-sm focus:outline-none focus:ring-1 focus:ring-[#5542ff] focus:border-[#5542ff] transition-all placeholder-gray-400" />
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-ink-muted uppercase tracking-wide mb-1.5">E-Mail</label>
                                    <input type="email" name="email" x-model="editSupplier.email" 
                                        placeholder="email@example.com"
                                        class="w-full rounded-lg border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-900 text-ink-heading px-3 py-2.5 text-sm focus:outline-none focus:ring-1 focus:ring-[#5542ff] focus:border-[#5542ff] transition-all placeholder-gray-400" />
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-ink-muted uppercase tracking-wide mb-1.5">Mobile <span class="text-red-500">*</span></label>
                                    <div class="relative">
                                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" /></svg>
                                        </span>
                                        <input type="tel" name="mobile" x-model="editSupplier.mobile" 
                                            oninput="this.value = this.value.replace(/[^0-9+]/g, '')" 
                                            required placeholder="09XXXXXXXXX"
                                            class="w-full pl-9 pr-3 py-2.5 rounded-lg border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-900 text-ink-heading text-sm focus:outline-none focus:ring-1 focus:ring-[#5542ff] focus:border-[#5542ff] transition-all placeholder-gray-400" maxlength="11" />
                                    </div>
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-ink-muted uppercase tracking-wide mb-1.5">Address</label>
                                <?php if (isset($component)) { $__componentOriginal985fdef8b2246be3e74382951fe17eee = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal985fdef8b2246be3e74382951fe17eee = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.address-input','data' => ['id' => 'edit_address','name' => 'address','xModel' => 'editSupplier.address','placeholder' => 'House no., street, barangay, city, province…','class' => 'bg-gray-50 dark:bg-gray-900 focus:ring-1 focus:ring-[#5542ff] focus:border-[#5542ff] transition-all']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('address-input'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['id' => 'edit_address','name' => 'address','x-model' => 'editSupplier.address','placeholder' => 'House no., street, barangay, city, province…','class' => 'bg-gray-50 dark:bg-gray-900 focus:ring-1 focus:ring-[#5542ff] focus:border-[#5542ff] transition-all']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal985fdef8b2246be3e74382951fe17eee)): ?>
<?php $attributes = $__attributesOriginal985fdef8b2246be3e74382951fe17eee; ?>
<?php unset($__attributesOriginal985fdef8b2246be3e74382951fe17eee); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal985fdef8b2246be3e74382951fe17eee)): ?>
<?php $component = $__componentOriginal985fdef8b2246be3e74382951fe17eee; ?>
<?php unset($__componentOriginal985fdef8b2246be3e74382951fe17eee); ?>
<?php endif; ?>
                            </div>

                            <div class="pt-2">
                                <button type="submit" class="w-full py-3 rounded-xl text-white font-semibold hover:opacity-90 transition-all shadow-md hover:shadow-lg flex items-center justify-center gap-2 text-sm tracking-wide" style="background: linear-gradient(135deg, var(--gradient-from) 0%, var(--gradient-to) 100%)">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                    Save Changes
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

<?php /**PATH C:\laragon\www\GE-System-1.1\resources\views/suppliers/index.blade.php ENDPATH**/ ?>