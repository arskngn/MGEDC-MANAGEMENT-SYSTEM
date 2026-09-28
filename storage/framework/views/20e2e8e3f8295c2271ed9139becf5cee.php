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
        button, input[type="button"], input[type="submit"], a {
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

    /* Custom scrollbar for category dropdown */
    .max-h-40::-webkit-scrollbar {
        width: 6px;
    }
    
    .max-h-40::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 3px;
    }
    
    .max-h-40::-webkit-scrollbar-thumb {
        background: #c1c1c1;
        border-radius: 3px;
    }
    
    .max-h-40::-webkit-scrollbar-thumb:hover {
        background: #a1a1a1;
    }
    </style>
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
                window.open('<?php echo e(route('customers.export.pdf')); ?>?ids=' + ids + '&print=true', '_blank');
            },
            bulkOpen: false,
            importOpen: <?php echo e($errors->has('csv_file') ? 'true' : 'false'); ?>,
            addOpen: false,
            editOpen: false,
            actionTop: '0px',
            actionRight: '0px',
            editActionUrl: '',
            storeCategories: <?php echo \Illuminate\Support\Js::from($categories)->toHtml() ?>,
            customerRoutes: <?php echo \Illuminate\Support\Js::from($routes)->toHtml() ?>,
            addStoreCategoryQuery: '',
            editStoreCategoryQuery: '',
            addStoreCategoryFocused: false,
            editStoreCategoryFocused: false,
            getFilteredCategories(query, exclude = '') {
                if (!query || query.trim() === '') {
                    return this.storeCategories.filter(c => c !== exclude);
                }
                const q = query.toLowerCase();
                return this.storeCategories.filter(c => 
                    c.toLowerCase().includes(q) && c !== exclude
                );
            },
            editCustomer: {
                id: null,
                name: '',
                email: '',
                mobile: '',
                address: '',
                store_category: '',
                route_id: '',
                status: 'active',
            },
            toggleAction($event) {
                this.bulkOpen = !this.bulkOpen;
                if (this.bulkOpen) {
                    const r = $event.currentTarget.getBoundingClientRect();
                    this.actionTop = (r.bottom + 4) + 'px';
                    this.actionRight = (window.innerWidth - r.right) + 'px';
                }
            },
            openEditCustomer(c) {
                this.editCustomer = {
                    id: c.id,
                    name: c.name ?? '',
                    email: c.email ?? '',
                    mobile: c.phone ?? '',
                    address: c.address ?? '',
                    store_category: c.store_category ?? '',
                    route_id: c.route_id ?? '',
                    status: c.status ?? 'active',
                };
                this.editStoreCategoryQuery = c.store_category ?? '';
                this.editStoreCategoryFocused = false;
                this.editActionUrl = `/customers/${c.id}`;
                this.editOpen = true;
                this.addOpen = false;
                this.bulkOpen = false;
            }
        }"
        @keydown.escape.window="importOpen = false; addOpen = false; editOpen = false; bulkOpen = false"
        @keydown.window.ctrl.p.prevent="printSelected()"
        @keydown.window.ctrl.a.prevent="selectAll = true; toggleSelectAll()"
        @scroll.window="if (bulkOpen) bulkOpen = false"
        class="space-y-4 px-3 sm:px-0 <?php if(auth()->user()->hasRole('salesperson')): ?> lg:px-0 <?php endif; ?>"
    >
        <div class="flex flex-col gap-3 <?php if(auth()->user()->hasRole('salesperson')): ?> lg:flex-row lg:items-center lg:justify-between <?php else: ?> sm:flex-row sm:items-center sm:justify-between <?php endif; ?>">
            <h2 class="text-lg sm:text-xl font-bold text-[#0a1233] dark:text-gray-100 shrink-0">Customers</h2>

            <div class="flex flex-col <?php if(auth()->user()->hasRole('salesperson')): ?> lg:flex-row lg:items-center <?php else: ?> sm:flex-row sm:items-center <?php endif; ?> gap-2.5 w-full <?php if(auth()->user()->hasRole('salesperson')): ?> lg:w-auto <?php else: ?> sm:w-auto <?php endif; ?>">
                <?php
                    $activeFilters = (int)(bool)request('store_category') + (int)(bool)request('route_id') + (int)(bool)request('status');
                ?>
                <form
                    method="GET"
                    action="<?php echo e(route('customers.index')); ?>"
                    x-data="{ 
                        mobileFiltersOpen: localStorage.getItem('customers_filters_open') === 'true' || <?php echo \Illuminate\Support\Js::from($activeFilters > 0)->toHtml() ?>,
                        toggleFilters() {
                            this.mobileFiltersOpen = !this.mobileFiltersOpen;
                            localStorage.setItem('customers_filters_open', this.mobileFiltersOpen);
                        }
                    }"
                    class="w-full <?php if(auth()->user()->hasRole('salesperson')): ?> lg:flex lg:flex-row lg:items-center lg:gap-2 lg:w-auto <?php else: ?> sm:flex sm:flex-row sm:items-center sm:gap-2 sm:w-auto <?php endif; ?>"
                >
                    
                    <div class="flex items-center gap-2 <?php if(auth()->user()->hasRole('salesperson')): ?> lg:contents <?php else: ?> sm:contents <?php endif; ?>">
                        
                        <div class="relative flex flex-1 <?php if(auth()->user()->hasRole('salesperson')): ?> lg:w-[240px] lg:flex-none <?php else: ?> sm:w-[240px] sm:flex-none <?php endif; ?>">
                            <input
                                type="text"
                                name="search"
                                value="<?php echo e(request('search')); ?>"
                                placeholder="Search name, phone, address..."
                                title="Search by customer name, phone, email, or address"
                                class="w-full pl-3 pr-8 py-2 border border-[#5542ff]/40 dark:border-gray-600 rounded-l-xl sm:rounded-l-md focus:outline-none focus:ring-1 focus:ring-[#5542ff] focus:border-[#5542ff] text-sm bg-white dark:bg-gray-900 text-ink-heading placeholder-gray-400 dark:placeholder-gray-500"
                            />
                            <?php if(request('search')): ?>
                                <a href="<?php echo e(request()->fullUrlWithQuery(['search' => null])); ?>" class="absolute right-9 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 text-xs px-1 h-4 w-4 flex items-center justify-center" title="Clear search">&#x2715;</a>
                            <?php endif; ?>
                            <button type="submit" class="px-3 gradient-bg text-white rounded-r-xl sm:rounded-r-md hover:opacity-90 transition-colors flex items-center justify-center shrink-0" title="Search">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                            </button>
                        </div>

                        
                        <button type="button" @click="toggleFilters()"
                                class="<?php if(auth()->user()->hasRole('salesperson')): ?> lg:hidden <?php else: ?> sm:hidden <?php endif; ?> relative flex items-center gap-1.5 px-3 py-2 rounded-xl border text-sm font-semibold transition-all shrink-0"
                                :class="mobileFiltersOpen ? 'bg-[#5542ff] text-white border-[#5542ff]' : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 border-gray-300 dark:border-gray-600'">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z"/></svg>
                            <span>Filter</span>
                            <?php if($activeFilters > 0): ?>
                                <span class="absolute -top-1.5 -right-1.5 h-4 w-4 flex items-center justify-center rounded-full bg-red-500 text-white text-[9px] font-black"><?php echo e($activeFilters); ?></span>
                            <?php endif; ?>
                        </button>
                    </div>

                    
                    <div x-show="mobileFiltersOpen" x-cloak class="<?php if(auth()->user()->hasRole('salesperson')): ?> lg:!block lg:flex lg:flex-row lg:items-center lg:gap-2 <?php else: ?> sm:!block sm:flex sm:flex-row sm:items-center sm:gap-2 <?php endif; ?>" style="display:none">
                        <div class="grid grid-cols-2 <?php if(auth()->user()->hasRole('salesperson')): ?> lg:flex lg:flex-row <?php else: ?> sm:flex sm:flex-row <?php endif; ?> gap-2 mt-2 <?php if(auth()->user()->hasRole('salesperson')): ?> lg:mt-0 <?php else: ?> sm:mt-0 <?php endif; ?>">
                            
                            <?php if(!empty($routes) && $routes->isNotEmpty()): ?>
                                <div class="relative w-full sm:w-auto">
                                    <select name="route_id" class="w-full text-sm border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-900 pl-3 pr-8 py-2 text-gray-700 dark:text-gray-100 focus:ring-1 focus:ring-[#5542ff] focus:border-[#5542ff] appearance-none cursor-pointer shadow-2xs transition-colors" onchange="this.form.submit()">
                                        <option value="">All Routes</option>
                                        <?php $__currentLoopData = $routes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $r): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <option value="<?php echo e($r->id); ?>" <?php if(request('route_id') == $r->id): echo 'selected'; endif; ?>><?php echo e($r->display_name); ?></option>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </select>
                                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-2.5 text-ink-muted">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                        </svg>
                                    </div>
                                </div>
                            <?php endif; ?>

                            
                            <?php if(!empty($categories)): ?>
                                <div class="relative w-full sm:w-auto">
                                    <select name="store_category" class="w-full text-sm border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-900 pl-3 pr-8 py-2 text-gray-700 dark:text-gray-100 focus:ring-1 focus:ring-[#5542ff] focus:border-[#5542ff] appearance-none cursor-pointer shadow-2xs transition-colors" onchange="this.form.submit()">
                                        <option value="">All Categories</option>
                                        <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <option value="<?php echo e($category); ?>" <?php if(request('store_category') == $category): echo 'selected'; endif; ?>><?php echo e($category); ?></option>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </select>
                                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-2.5 text-ink-muted">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                        </svg>
                                    </div>
                                </div>
                            <?php endif; ?>

                            
                            <div class="relative w-full sm:w-auto">
                                <select name="status" class="w-full text-sm border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-900 pl-3 pr-8 py-2 text-gray-700 dark:text-gray-100 focus:ring-1 focus:ring-[#5542ff] focus:border-[#5542ff] appearance-none cursor-pointer shadow-2xs transition-colors" onchange="this.form.submit()">
                                    <option value="">All Statuses</option>
                                    <option value="active" <?php if(request('status') == 'active'): echo 'selected'; endif; ?>>Active</option>
                                    <option value="inactive" <?php if(request('status') == 'inactive'): echo 'selected'; endif; ?>>Inactive</option>
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

                <div class="flex items-center gap-2 shrink-0 justify-end">
                    <?php if(auth()->user()->hasPermission('Store Customer')): ?>
                        <button
                            type="button"
                            @click="addOpen = true; editOpen = false; bulkOpen = false; addStoreCategoryQuery = ''; addStoreCategoryFocused = false;"
                            class="inline-flex items-center justify-center px-3 sm:px-4 py-2 bg-white dark:bg-gray-800 border border-[#5542ff] text-[#5542ff] dark:text-gray-100 dark:border-gray-600 rounded-xl sm:rounded-md hover:bg-[#5542ff]/5 dark:hover:bg-gray-700 transition-colors text-sm font-semibold whitespace-nowrap"
                        >
                            <svg class="h-4 w-4 sm:mr-1.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            <span class="hidden sm:inline">Add New</span>
                            <span class="sm:hidden">Add</span>
                        </button>
                    <?php endif; ?>

                    <?php if(auth()->user()->hasPermission('Download Customer PDF') || auth()->user()->hasPermission('Download Customer CSV') || auth()->user()->hasPermission('Import Customers')): ?>
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
                                    class="fixed w-52 bg-white dark:bg-gray-800 rounded-xl shadow-xl border border-gray-100 dark:border-gray-700 py-1 z-[200]"
                                    :style="{ top: actionTop, right: actionRight }"
                                >
                                    <?php if(auth()->user()->hasPermission('Download Customer PDF')): ?>
                                        <a href="<?php echo e(route('customers.export.pdf', request()->query())); ?>" class="flex items-center px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700">
                                            <svg class="h-4 w-4 mr-2 text-gray-400 dark:text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>
                                            Download PDF
                                        </a>
                                    <?php endif; ?>
                                    <button type="button" @click="printSelected()" class="w-full flex items-center px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 text-left">
                                        <svg class="h-4 w-4 mr-2 text-gray-400 dark:text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" /></svg>
                                        Print Selected
                                    </button>

                                    <?php if(auth()->user()->hasPermission('Download Customer CSV')): ?>
                                        <a href="<?php echo e(route('customers.export.csv', request()->query())); ?>" class="flex items-center px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700">
                                            <svg class="h-4 w-4 mr-2 text-gray-400 dark:text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                                            Download CSV
                                        </a>
                                    <?php endif; ?>

                                    <?php if(auth()->user()->hasPermission('Import Customers')): ?>
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

        
        <?php if(request()->hasAny(['search', 'route_id', 'store_category', 'status'])): ?>
            <div class="flex flex-wrap items-center gap-2 px-1 py-1">
                <span class="text-xs font-semibold text-ink-muted">Active Filters:</span>
                
                <?php if(request('search')): ?>
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium bg-[#5542ff]/10 text-[#5542ff] dark:bg-[#5542ff]/20 dark:text-indigo-300 border border-[#5542ff]/20">
                        Search: "<?php echo e(request('search')); ?>"
                        <a href="<?php echo e(request()->fullUrlWithQuery(['search' => null])); ?>" class="hover:text-red-500 font-bold ml-1" title="Remove search filter">&times;</a>
                    </span>
                <?php endif; ?>

                <?php if(request('route_id') && ($activeRoute = $routes->firstWhere('id', request('route_id')))): ?>
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium bg-[#5542ff]/10 text-[#5542ff] dark:bg-[#5542ff]/20 dark:text-indigo-300 border border-[#5542ff]/20">
                        Route: <?php echo e($activeRoute->display_name); ?>

                        <a href="<?php echo e(request()->fullUrlWithQuery(['route_id' => null])); ?>" class="hover:text-red-500 font-bold ml-1" title="Remove route filter">&times;</a>
                    </span>
                <?php endif; ?>

                <?php if(request('store_category')): ?>
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium bg-[#5542ff]/10 text-[#5542ff] dark:bg-[#5542ff]/20 dark:text-indigo-300 border border-[#5542ff]/20">
                        Category: <?php echo e(request('store_category')); ?>

                        <a href="<?php echo e(request()->fullUrlWithQuery(['store_category' => null])); ?>" class="hover:text-red-500 font-bold ml-1" title="Remove category filter">&times;</a>
                    </span>
                <?php endif; ?>

                <?php if(request('status')): ?>
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium bg-[#5542ff]/10 text-[#5542ff] dark:bg-[#5542ff]/20 dark:text-indigo-300 border border-[#5542ff]/20">
                        Status: <?php echo e(ucfirst(request('status'))); ?>

                        <a href="<?php echo e(request()->fullUrlWithQuery(['status' => null])); ?>" class="hover:text-red-500 font-bold ml-1" title="Remove status filter">&times;</a>
                    </span>
                <?php endif; ?>

                <a href="<?php echo e(route('customers.index')); ?>" class="text-xs text-red-600 dark:text-red-400 hover:underline font-semibold ml-1">
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
        <?php if($errors->any()): ?>
            <div class="rounded-lg bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-700 text-red-800 dark:text-red-300 px-4 py-3 text-sm">
                <div class="font-semibold mb-1">Please fix the following:</div>
                <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div><?php echo e($error); ?></div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        <?php endif; ?>

        
        <div class="<?php echo e(auth()->user()->hasRole('salesperson') ? 'lg:hidden' : 'sm:hidden'); ?> space-y-3">
            <?php if(!auth()->user()->hasRole('salesperson')): ?>
                <div class="flex items-center justify-between px-4 py-2.5 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm">
                    <label class="flex items-center gap-2 cursor-pointer select-none text-xs font-bold text-gray-700 dark:text-gray-200">
                        <input type="checkbox" x-model="selectAll" @change="toggleSelectAll" class="rounded border-gray-300 text-[#5542ff] focus:ring-[#5542ff]">
                        <span>Select All Customers</span>
                    </label>
                    <span class="text-xs text-gray-400 font-semibold" x-text="selectedItems.length + ' selected'"></span>
                </div>
            <?php endif; ?>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
            <?php $__empty_1 = true; $__currentLoopData = $customers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $customer): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <?php
                    $receivable = (float) ($customer->receivable_total ?? 0);
                    $payable = (float) ($customer->payable_total ?? 0);
                    $paymentDisabled = $receivable <= 0.009 && $payable <= 0.009;
                    $statusClasses = [
                        'active' => 'bg-green-100 text-green-800 border-green-200 dark:bg-green-900/30 dark:text-green-300 dark:border-green-700',
                        'inactive' => 'bg-gray-100 text-gray-800 border-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600',
                    ];
                ?>
                <div :class="{ 'print-hidden': !selectedItems.includes(String('<?php echo e($customer->id); ?>')) }"
                     class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-4 shadow-sm space-y-3 transition-colors">
                    
                    
                    <div class="flex items-start gap-3 border-b border-gray-100 dark:border-gray-700 pb-3">
                        <?php if(!auth()->user()->hasRole('salesperson')): ?>
                            <input type="checkbox" value="<?php echo e($customer->id); ?>" x-model="selectedItems" class="row-checkbox rounded border-gray-300 text-[#5542ff] focus:ring-[#5542ff] mt-0.5 shrink-0">
                        <?php endif; ?>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between gap-2">
                                <span class="font-black text-[#5542ff] dark:text-gray-100 text-base leading-tight truncate"><?php echo e($customer->name); ?></span>
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold border shrink-0 <?php echo e($statusClasses[$customer->status] ?? $statusClasses['active']); ?>">
                                    <?php echo e(ucfirst($customer->status)); ?>

                                </span>
                            </div>
                            <?php if(filled($customer->storeCategory?->name)): ?>
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-[#5542ff]/10 text-[#5542ff] border border-[#5542ff]/20 mt-1">
                                    <?php echo e($customer->storeCategory->name); ?>

                                </span>
                            <?php endif; ?>
                            <?php if($customer->route): ?>
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-indigo-50 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-700/50 mt-1">
                                    <svg class="w-2.5 h-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
                                    </svg>
                                    <?php echo e($customer->route->display_name); ?>

                                </span>
                            <?php endif; ?>
                        </div>
                    </div>

                    
                    <div class="space-y-1.5 text-xs text-gray-600 dark:text-gray-300">
                        <?php if(filled($customer->address)): ?>
                            <div class="flex items-start gap-2">
                                <svg class="w-3.5 h-3.5 text-gray-400 mt-0.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                <span class="text-xs text-ink-body leading-tight"><?php echo e($customer->address); ?></span>
                            </div>
                        <?php endif; ?>

                        <div class="flex items-center gap-4 flex-wrap pt-0.5">
                            <?php if(filled($customer->phone)): ?>
                                <a href="tel:<?php echo e($customer->phone); ?>" class="inline-flex items-center gap-1 text-xs font-semibold text-gray-700 dark:text-gray-200 hover:text-[#5542ff]">
                                    <svg class="w-3.5 h-3.5 text-[#5542ff]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                                    <?php echo e($customer->phone); ?>

                                </a>
                            <?php endif; ?>

                            <?php if(filled($customer->email)): ?>
                                <a href="mailto:<?php echo e($customer->email); ?>" class="inline-flex items-center gap-1 text-xs text-gray-500 hover:text-[#5542ff] truncate max-w-[200px]">
                                    <svg class="w-3.5 h-3.5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                    <?php echo e($customer->email); ?>

                                </a>
                            <?php endif; ?>
                        </div>
                    </div>

                    
                    <div class="grid grid-cols-2 gap-2 p-2.5 rounded-xl bg-gray-50 dark:bg-gray-700/40 text-xs">
                        <div>
                            <span class="text-[10px] text-gray-400 uppercase font-bold tracking-wide block">Receivable</span>
                            <span class="text-sm font-black <?php echo e($receivable > 0.009 ? 'text-green-600 dark:text-green-400' : 'text-ink-muted'); ?>">
                                <?php echo e(formatCurrency($receivable)); ?>

                            </span>
                        </div>
                        <div>
                            <span class="text-[10px] text-gray-400 uppercase font-bold tracking-wide block">Payable</span>
                            <span class="text-sm font-black <?php echo e($payable > 0.009 ? 'text-red-600 dark:text-red-400' : 'text-ink-muted'); ?>">
                                <?php echo e(formatCurrency($payable)); ?>

                            </span>
                        </div>
                    </div>

                    
                    <div class="flex items-center justify-end gap-2 pt-2 border-t border-gray-100 dark:border-gray-700">
                        <?php if(auth()->user()->hasPermission('Store Customer')): ?>
                            <button type="button"
                                class="inline-flex items-center px-3 py-1.5 bg-white dark:bg-gray-800 border border-[#5542ff] dark:border-gray-600 text-[#5542ff] dark:text-gray-100 rounded-lg hover:bg-[#5542ff]/5 text-xs font-bold transition-colors min-h-[38px]"
                                @click="openEditCustomer({ id: <?php echo e($customer->id); ?>, name: <?php echo \Illuminate\Support\Js::from($customer->name)->toHtml() ?>, email: <?php echo \Illuminate\Support\Js::from($customer->email)->toHtml() ?>, phone: <?php echo \Illuminate\Support\Js::from($customer->phone)->toHtml() ?>, address: <?php echo \Illuminate\Support\Js::from($customer->address)->toHtml() ?>, store_category: <?php echo \Illuminate\Support\Js::from($customer->storeCategory?->name)->toHtml() ?>, status: <?php echo \Illuminate\Support\Js::from($customer->status)->toHtml() ?> })">
                                <svg class="h-3.5 w-3.5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                Edit
                            </button>
                        <?php endif; ?>

                        <?php if(auth()->user()->hasPermission('All Customer Payments')): ?>
                            <a href="<?php echo e(route('customers.payments.index', $customer)); ?>"
                               class="inline-flex items-center px-3 py-1.5 bg-white dark:bg-gray-800 border border-[#2563eb] dark:border-gray-600 text-[#2563eb] dark:text-blue-300 rounded-lg text-xs font-bold hover:bg-[#2563eb]/5 min-h-[38px] <?php echo e($paymentDisabled ? 'pointer-events-none opacity-50 cursor-not-allowed' : ''); ?>"
                               <?php echo e($paymentDisabled ? 'tabindex=-1 aria-disabled=true' : ''); ?>>
                                <svg class="h-3.5 w-3.5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2h2m8-6l-4 4m0 0l-4-4m4 4V9" /></svg>
                                Payment
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-8 text-center text-sm text-gray-400 md:col-span-2">
                    No customers found.
                </div>
            <?php endif; ?>
            </div>

            <?php if($customers->hasPages()): ?>
                <div class="px-4 py-3 bg-gray-50 dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700">
                    <?php echo e($customers->links()); ?>

                </div>
            <?php endif; ?>
        </div>

        
        <div class="<?php echo e(auth()->user()->hasRole('salesperson') ? 'hidden lg:block' : 'hidden sm:block'); ?> bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-center border-collapse min-w-[980px]">
                    <thead>
                        <tr class="gradient-bg text-white">
                            <?php if(!auth()->user()->hasRole('salesperson')): ?>
                                <th class="checkbox-column px-4 py-3.5 text-[11px] font-bold uppercase tracking-wide w-10">
                                    <label class="flex items-center gap-2 cursor-pointer">
                                        <input type="checkbox" x-model="selectAll" @change="toggleSelectAll" class="rounded border-gray-300 text-[#5542ff] focus:ring-[#5542ff]">
                                        <span class="sr-only">Select All</span>
                                    </label>
                                </th>
                            <?php endif; ?>
                            <th class="px-4 py-4 text-[11px] font-bold uppercase tracking-wide text-center">S.N.</th>
                            <th class="px-4 py-4 text-[11px] font-bold uppercase tracking-wide text-center">Name | Address</th>
                            <th class="px-4 py-4 text-[11px] font-bold uppercase tracking-wide text-center">Mobile | Email</th>
                            <th class="px-4 py-4 text-[11px] font-bold uppercase tracking-wide text-center">Store Category</th>
                            <th class="px-4 py-4 text-[11px] font-bold uppercase tracking-wide text-center">Route</th>
                            <th class="px-4 py-4 text-[11px] font-bold uppercase tracking-wide text-center">Status</th>
                            <th class="px-4 py-4 text-[11px] font-bold uppercase tracking-wide text-center">Receivable</th>
                            <th class="px-4 py-4 text-[11px] font-bold uppercase tracking-wide text-center">Payable</th>
                            <th class="px-4 py-4 text-[11px] font-bold uppercase tracking-wide text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        <?php $__empty_1 = true; $__currentLoopData = $customers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $customer): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <?php
                                $receivable = (float) ($customer->receivable_total ?? 0);
                                $payable = (float) ($customer->payable_total ?? 0);
                                // The payment button should be enabled if there is either a receivable OR a payable balance to settle.
                                $paymentDisabled = $receivable <= 0.009 && $payable <= 0.009;
                            ?>
                            <tr :class="{ 'print-hidden': !selectedItems.includes(String('<?php echo e($customer->id); ?>')) }" class="hover:bg-gray-50/90 dark:hover:bg-gray-700 transition-colors bg-white dark:bg-gray-800">
                                <?php if(!auth()->user()->hasRole('salesperson')): ?>
                                    <td class="checkbox-column px-4 py-3.5 align-middle">
                                        <input type="checkbox" value="<?php echo e($customer->id); ?>" x-model="selectedItems" class="row-checkbox rounded border-gray-300 text-[#5542ff] focus:ring-[#5542ff]">
                                    </td>
                                <?php endif; ?>
                                <td class="px-4 py-4 align-middle text-sm text-gray-600"><?php echo e($i + 1); ?></td>
                                <td class="px-4 py-4 align-middle">
                                    <div class="flex flex-col items-start leading-snug">
                                        <div class="text-sm font-bold text-[#5542ff] dark:text-indigo-300"><?php echo e($customer->name); ?></div>
                                        <?php if(filled($customer->address)): ?>
                                            <div class="text-[12px] text-gray-500 line-clamp-2 max-w-[260px] text-left" title="<?php echo e($customer->address); ?>"><?php echo e($customer->address); ?></div>
                                        <?php else: ?>
                                            <div class="text-[12px] text-gray-400">—</div>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td class="px-4 py-4 align-middle">
                                    <div class="flex flex-col items-start leading-snug">
                                        <span class="text-sm font-medium text-gray-600"><?php echo e($customer->phone ?? '—'); ?></span>
                                        <span class="text-[12px] text-gray-500"><?php echo e($customer->email ?? '—'); ?></span>
                                    </div>
                                </td>
                                <td class="px-4 py-4 align-middle text-sm">
                                    <?php if(filled($customer->storeCategory?->name)): ?>
                                        <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-semibold bg-gradient-to-r from-[#4634ff]/10 to-[#5542ff]/10 text-[#4634ff] border border-[#4634ff]/20">
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                            </svg>
                                            <?php echo e($customer->storeCategory?->name); ?>

                                        </span>
                                    <?php else: ?>
                                        <span class="text-gray-400">—</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-4 py-4 align-middle text-sm">
                                    <?php if($customer->route): ?>
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-indigo-50 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-700/50">
                                            <svg class="w-3 h-3 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
                                            </svg>
                                            <?php echo e($customer->route->display_name); ?>

                                        </span>
                                    <?php else: ?>
                                        <span class="text-gray-400">—</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-4 py-4 align-middle">
                                    <?php
                                        $statusClasses = [
                                            'active' => 'bg-green-100 text-green-800 border-green-200 dark:bg-green-900/30 dark:text-green-300 dark:border-green-700',
                                            'inactive' => 'bg-gray-100 text-gray-800 border-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600',
                                        ];
                                    ?>
                                    <span class="px-2.5 py-1 rounded-full text-xs font-bold border <?php echo e($statusClasses[$customer->status] ?? $statusClasses['active']); ?>">
                                        <?php echo e(ucfirst($customer->status)); ?>

                                    </span>
                                </td>
                                <td class="px-4 py-4 align-middle text-sm font-bold <?php echo e($receivable > 0.009 ? 'text-green-600' : 'text-gray-600'); ?>">
                                    <?php echo e(formatCurrency($receivable)); ?>

                                </td>
                                <td class="px-4 py-4 align-middle text-sm font-bold <?php echo e($payable > 0.009 ? 'text-red-600' : 'text-gray-600'); ?>">
                                    <?php echo e(formatCurrency($payable)); ?>

                                </td>
                                <td class="px-4 py-4 align-middle text-right">
                                    <div class="flex justify-end gap-2 items-center flex-nowrap">
                                        <?php if(auth()->user()->hasPermission('Store Customer')): ?>
                                    <button
                                        type="button"
                                        class="inline-flex items-center justify-center px-2.5 py-1.5 bg-white dark:bg-gray-800 border border-[#5542ff] dark:border-gray-600 text-[#5542ff] dark:text-gray-100 rounded-md hover:bg-[#5542ff]/5 dark:hover:bg-gray-700 text-[11px] font-bold"
                                        @click="openEditCustomer({ id: <?php echo e($customer->id); ?>, name: <?php echo \Illuminate\Support\Js::from($customer->name)->toHtml() ?>, email: <?php echo \Illuminate\Support\Js::from($customer->email)->toHtml() ?>, phone: <?php echo \Illuminate\Support\Js::from($customer->phone)->toHtml() ?>, address: <?php echo \Illuminate\Support\Js::from($customer->address)->toHtml() ?>, store_category: <?php echo \Illuminate\Support\Js::from($customer->storeCategory?->name)->toHtml() ?>, route_id: <?php echo e($customer->route_id ?? 'null'); ?>, status: <?php echo \Illuminate\Support\Js::from($customer->status)->toHtml() ?> })"
                                    >
                                        <svg class="h-3.5 w-3.5 mr-1 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                        Edit
                                    </button>
                                <?php endif; ?>



                                <?php if(auth()->user()->hasPermission('All Customer Payments')): ?>
                                    <a
                                        href="<?php echo e(route('customers.payments.index', $customer)); ?>"
                                        class="inline-flex items-center justify-center px-2.5 py-1.5 bg-white dark:bg-gray-800 border border-[#2563eb] dark:border-gray-600 text-[#2563eb] dark:text-blue-300 rounded-md text-[11px] font-bold hover:bg-[#2563eb]/5 dark:hover:bg-gray-700 <?php echo e($paymentDisabled ? 'pointer-events-none opacity-50 cursor-not-allowed' : ''); ?>"
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
                                <td colspan="9" class="px-6 py-12 text-center text-gray-500">No customers found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <?php if($customers->hasPages()): ?>
                <div class="px-4 py-3 bg-gray-50 dark:bg-gray-900 border-t border-gray-100 dark:border-gray-700">
                    <?php echo e($customers->links()); ?>

                </div>
            <?php endif; ?>
        </div>

        
        <div x-show="addOpen" x-cloak class="fixed inset-0 z-[220] overflow-y-auto" style="display: none;">
            <div class="flex items-center justify-center min-h-screen px-4 py-8">
                <div class="fixed inset-0 bg-gray-900/50 z-0" @click="addOpen = false"></div>
                <div class="relative bg-white dark:bg-gray-800 rounded-2xl shadow-2xl max-w-lg w-full z-10 border border-gray-100 dark:border-gray-700" @click.stop>

                    
                    <div class="px-6 py-5 flex justify-between items-center rounded-t-2xl" style="background: linear-gradient(135deg, var(--gradient-from) 0%, var(--gradient-to) 100%)">
                        <div class="flex items-center gap-3">
                            <div class="bg-white/20 rounded-lg p-2">
                                <svg class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-base font-bold text-white leading-tight">Add New Customer</h3>
                                <p class="text-white/60 text-xs mt-0.5">Fill in the details below</p>
                            </div>
                        </div>
                        <button type="button" @click="addOpen = false" class="text-white/60 hover:text-white hover:bg-white/10 rounded-lg p-1.5 transition-colors" aria-label="Close">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                        </button>
                    </div>

                    
                    <div class="p-6">
                        <form method="POST" action="<?php echo e(route('customers.store')); ?>" class="space-y-4">
                            <?php echo csrf_field(); ?>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-semibold text-ink-muted uppercase tracking-wide mb-1.5">Name <span class="text-red-500">*</span></label>
                                    <input type="text" name="name" value="<?php echo e(old('name')); ?>" required
                                        placeholder="Store name or customer name"
                                        class="w-full rounded-lg border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-900 text-ink-heading px-3 py-2.5 text-sm focus:outline-none focus:ring-1 focus:ring-[#5542ff] focus:border-[#5542ff] transition-all placeholder-gray-400" />
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-ink-muted uppercase tracking-wide mb-1.5">E-Mail</label>
                                    <input type="email" name="email" value="<?php echo e(old('email')); ?>"
                                        placeholder="email@example.com"
                                        class="w-full rounded-lg border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-900 text-ink-heading px-3 py-2.5 text-sm focus:outline-none focus:ring-1 focus:ring-[#5542ff] focus:border-[#5542ff] transition-all placeholder-gray-400" />
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-ink-muted uppercase tracking-wide mb-1.5">Mobile</label>
                                <div class="relative">
                                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" /></svg>
                                    </span>
                                    <input type="tel" name="mobile" value="<?php echo e(old('mobile')); ?>"
                                        oninput="this.value = this.value.replace(/[^0-9+]/g, '')"
                                        placeholder="09XXXXXXXXX (optional)"
                                        class="w-full pl-9 pr-3 py-2.5 rounded-lg border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-900 text-ink-heading text-sm focus:outline-none focus:ring-1 focus:ring-[#5542ff] focus:border-[#5542ff] transition-all placeholder-gray-400"
                                        maxlength="11" />
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-ink-muted uppercase tracking-wide mb-1.5">Address</label>
                                <?php if (isset($component)) { $__componentOriginal985fdef8b2246be3e74382951fe17eee = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal985fdef8b2246be3e74382951fe17eee = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.address-input','data' => ['id' => 'add_address_customer','name' => 'address','value' => old('address', ''),'placeholder' => 'House no., street, barangay, city, province…','class' => 'bg-gray-50 dark:bg-gray-900 focus:ring-1 focus:ring-[#5542ff] focus:border-[#5542ff] transition-all']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('address-input'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['id' => 'add_address_customer','name' => 'address','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(old('address', '')),'placeholder' => 'House no., street, barangay, city, province…','class' => 'bg-gray-50 dark:bg-gray-900 focus:ring-1 focus:ring-[#5542ff] focus:border-[#5542ff] transition-all']); ?>
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

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <label class="block text-xs font-semibold text-ink-muted uppercase tracking-wide mb-1.5">Route <span class="text-red-500">*</span></label>
                                    <select name="route_id" required class="w-full rounded-lg border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-900 px-3 py-2.5 text-sm text-ink-heading focus:outline-none focus:ring-1 focus:ring-[#5542ff] focus:border-[#5542ff] transition-all cursor-pointer">
                                        <option value="">Select Route</option>
                                        <?php $__currentLoopData = $routes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $r): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <option value="<?php echo e($r->id); ?>" <?php echo e(old('route_id') == $r->id ? 'selected' : ''); ?>>
                                                <?php echo e($r->display_name); ?>

                                            </option>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </select>
                                    <?php $__errorArgs = ['route_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="text-red-500 text-xs mt-1"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-ink-muted uppercase tracking-wide mb-1.5">Store Category <span class="text-red-500">*</span></label>
                                    <select name="store_category" required class="w-full rounded-lg border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-900 px-3 py-2.5 text-sm text-ink-heading focus:outline-none focus:ring-1 focus:ring-[#5542ff] focus:border-[#5542ff] transition-all cursor-pointer">
                                        <option value="">Select Category</option>
                                        <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <option value="<?php echo e($category); ?>" <?php echo e(old('store_category') == $category ? 'selected' : ''); ?>>
                                                <?php echo e($category); ?>

                                            </option>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-ink-muted uppercase tracking-wide mb-1.5">Status</label>
                                    <select name="status" class="w-full rounded-lg border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-900 px-3 py-2.5 text-sm text-ink-heading focus:outline-none focus:ring-1 focus:ring-[#5542ff] focus:border-[#5542ff] transition-all cursor-pointer">
                                        <option value="active" <?php echo e(old('status') == 'active' ? 'selected' : ''); ?>>Active</option>
                                        <option value="inactive" <?php echo e(old('status') == 'inactive' ? 'selected' : ''); ?>>Inactive</option>
                                    </select>
                                </div>
                            </div>

                            <div class="pt-2">
                                <button type="submit" class="w-full py-3 rounded-xl text-white font-semibold hover:opacity-90 transition-all shadow-md hover:shadow-lg flex items-center justify-center gap-2 text-sm tracking-wide" style="background: linear-gradient(135deg, var(--gradient-from) 0%, var(--gradient-to) 100%)">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" /></svg>
                                    Add Customer
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
                                <h3 class="text-base font-bold text-white leading-tight">Edit Customer</h3>
                                <p class="text-white/60 text-xs mt-0.5">Update customer details</p>
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
                                    <label class="block text-xs font-semibold text-ink-muted uppercase tracking-wide mb-1.5">Name <span class="text-red-500">*</span></label>
                                    <input type="text" name="name" x-model="editCustomer.name" required
                                        placeholder="Store name or customer name"
                                        class="w-full rounded-lg border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-900 text-ink-heading px-3 py-2.5 text-sm focus:outline-none focus:ring-1 focus:ring-[#5542ff] focus:border-[#5542ff] transition-all placeholder-gray-400" />
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-ink-muted uppercase tracking-wide mb-1.5">E-Mail</label>
                                    <input type="email" name="email" x-model="editCustomer.email"
                                        placeholder="email@example.com"
                                        class="w-full rounded-lg border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-900 text-ink-heading px-3 py-2.5 text-sm focus:outline-none focus:ring-1 focus:ring-[#5542ff] focus:border-[#5542ff] transition-all placeholder-gray-400" />
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-ink-muted uppercase tracking-wide mb-1.5">Mobile</label>
                                <div class="relative">
                                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" /></svg>
                                    </span>
                                    <input type="tel" name="mobile" x-model="editCustomer.mobile"
                                        oninput="this.value = this.value.replace(/[^0-9+]/g, '')"
                                        placeholder="09XXXXXXXXX (optional)"
                                        class="w-full pl-9 pr-3 py-2.5 rounded-lg border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-900 text-ink-heading text-sm focus:outline-none focus:ring-1 focus:ring-[#5542ff] focus:border-[#5542ff] transition-all placeholder-gray-400"
                                        maxlength="11" />
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-ink-muted uppercase tracking-wide mb-1.5">Address</label>
                                <?php if (isset($component)) { $__componentOriginal985fdef8b2246be3e74382951fe17eee = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal985fdef8b2246be3e74382951fe17eee = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.address-input','data' => ['id' => 'edit_address_customer','name' => 'address','xModel' => 'editCustomer.address','placeholder' => 'House no., street, barangay, city, province…','class' => 'bg-gray-50 dark:bg-gray-900 focus:ring-1 focus:ring-[#5542ff] focus:border-[#5542ff] transition-all']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('address-input'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['id' => 'edit_address_customer','name' => 'address','x-model' => 'editCustomer.address','placeholder' => 'House no., street, barangay, city, province…','class' => 'bg-gray-50 dark:bg-gray-900 focus:ring-1 focus:ring-[#5542ff] focus:border-[#5542ff] transition-all']); ?>
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

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <label class="block text-xs font-semibold text-ink-muted uppercase tracking-wide mb-1.5">Route <span class="text-red-500">*</span></label>
                                    <select name="route_id" x-model="editCustomer.route_id" required class="w-full rounded-lg border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-900 px-3 py-2.5 text-sm text-ink-heading focus:outline-none focus:ring-1 focus:ring-[#5542ff] focus:border-[#5542ff] transition-all cursor-pointer">
                                        <option value="">Select Route</option>
                                        <template x-for="r in customerRoutes" :key="r.id">
                                            <option :value="r.id" x-text="r.display_name || (r.from_location + ' → ' + r.to_location)"></option>
                                        </template>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-ink-muted uppercase tracking-wide mb-1.5">Store Category <span class="text-red-500">*</span></label>
                                    <select name="store_category" x-model="editCustomer.store_category" required class="w-full rounded-lg border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-900 px-3 py-2.5 text-sm text-ink-heading focus:outline-none focus:ring-1 focus:ring-[#5542ff] focus:border-[#5542ff] transition-all cursor-pointer">
                                        <option value="">Select Category</option>
                                        <template x-for="category in storeCategories" :key="category">
                                            <option :value="category" x-text="category"></option>
                                        </template>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-ink-muted uppercase tracking-wide mb-1.5">Status</label>
                                    <select name="status" x-model="editCustomer.status" class="w-full rounded-lg border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-900 px-3 py-2.5 text-sm text-ink-heading focus:outline-none focus:ring-1 focus:ring-[#5542ff] focus:border-[#5542ff] transition-all cursor-pointer">
                                        <option value="active">Active</option>
                                        <option value="inactive">Inactive</option>
                                    </select>
                                </div>
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
            <div class="relative bg-white dark:bg-gray-800 rounded-xl shadow-xl max-sm w-full mx-4 p-6 z-10 text-center border border-gray-100 dark:border-gray-700">
                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-yellow-100 dark:bg-yellow-900/30 mb-4">
                    <svg class="h-6 w-6 text-yellow-600 dark:text-yellow-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                </div>
                <h3 class="text-lg font-bold text-ink-heading mb-2">Please select records</h3>
                <p class="text-sm text-ink-muted mb-6">Please select at least one record to print.</p>
                <button @click="showPrintMessage = false" class="w-full bg-[#5542ff] hover:bg-[#4736d6] text-white font-bold py-2 px-4 rounded-lg transition-colors">OK</button>
            </div>
        </div>
    </div>
    
    <style>
        .customer-status-select option:checked {
            background-color: #5542ff;
            color: white;
        }
    </style>
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

<?php /**PATH C:\laragon\www\GE-System-1.1\resources\views/customers/index.blade.php ENDPATH**/ ?>