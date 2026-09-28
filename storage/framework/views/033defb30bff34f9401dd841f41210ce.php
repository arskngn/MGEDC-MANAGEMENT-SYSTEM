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
                window.open('<?php echo e(route('products.export.pdf')); ?>?ids=' + ids + '&print=true', '_blank');
            },
            bulkOpen: false,
            importOpen: <?php echo e($errors->has('csv_file') ? 'true' : 'false'); ?>,
            actionTop: '0px',
            actionRight: '0px',
            toggleAction($event) {
                this.bulkOpen = !this.bulkOpen;
                if (this.bulkOpen) {
                    const r = $event.currentTarget.getBoundingClientRect();
                    this.actionTop = (r.bottom + 4) + 'px';
                    this.actionRight = (window.innerWidth - r.right) + 'px';
                }
            },
        }"
        @keydown.escape.window="importOpen = false; bulkOpen = false"
        @keydown.window.ctrl.p.prevent="printSelected()"
        @keydown.window.ctrl.a.prevent="selectAll = true; toggleSelectAll()"
        @scroll.window="if (bulkOpen) bulkOpen = false"
        class="space-y-4"
    >
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="text-xl font-bold text-[#0a1233] dark:text-gray-100 shrink-0">Products</h2>

            <div class="flex flex-col sm:flex-row sm:items-center gap-2.5 w-full sm:w-auto">
                <form
                    method="GET"
                    action="<?php echo e(route('products.all')); ?>"
                    x-data="{ mobileFiltersOpen: false }"
                    class="w-full sm:flex sm:flex-row sm:items-center sm:gap-2 sm:w-auto"
                >
                    
                    <div class="flex items-center gap-2 sm:contents">
                        
                        <div class="relative flex flex-1 sm:w-[240px] sm:flex-none">
                            <input
                                type="text"
                                name="search"
                                value="<?php echo e(request('search')); ?>"
                                placeholder="Search name, SKU, category..."
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
                            $activeFilters = (int)(bool)request('category_id') + (int)(bool)request('brand_id') + (int)(bool)request('stock_status');
                        ?>
                        <button type="button" @click="mobileFiltersOpen = !mobileFiltersOpen"
                                class="sm:hidden relative flex items-center gap-1.5 px-3 py-2 rounded-xl border text-sm font-semibold transition-all shrink-0"
                                :class="mobileFiltersOpen ? 'bg-[#5542ff] text-white border-[#5542ff]' : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 border-gray-300 dark:border-gray-600'">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z"/></svg>
                            <span>Filter</span>
                            <?php if($activeFilters > 0): ?>
                                <span class="absolute -top-1.5 -right-1.5 h-4 w-4 flex items-center justify-center rounded-full bg-red-500 text-white text-[9px] font-black"><?php echo e($activeFilters); ?></span>
                            <?php endif; ?>
                        </button>
                    </div>

                    
                    <div x-show="mobileFiltersOpen" x-cloak class="sm:!block sm:flex sm:flex-row sm:items-center sm:gap-2" style="display:none">
                        <div class="grid grid-cols-2 gap-2 mt-2 sm:mt-0 sm:flex sm:flex-row sm:gap-2">
                            
                            <div class="relative w-full sm:w-auto">
                                <select name="category_id" class="w-full sm:w-auto text-sm border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-900 pl-3 pr-8 py-2 text-gray-700 dark:text-gray-100 focus:ring-1 focus:ring-[#5542ff] focus:border-[#5542ff] appearance-none cursor-pointer shadow-2xs transition-colors" onchange="this.form.brand_id.value=''; this.form.submit()">
                                    <option value="">All Categories</option>
                                    <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($category->id); ?>" <?php if(request('category_id') == $category->id): echo 'selected'; endif; ?>><?php echo e($category->name); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-2.5 text-ink-muted">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </div>
                            </div>

                            
                            <div class="relative w-full sm:w-auto">
                                <select name="brand_id" class="w-full sm:w-auto text-sm border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-900 pl-3 pr-8 py-2 text-gray-700 dark:text-gray-100 focus:ring-1 focus:ring-[#5542ff] focus:border-[#5542ff] appearance-none cursor-pointer shadow-2xs transition-colors" onchange="this.form.submit()">
                                    <option value="">All Brands</option>
                                    <?php $__currentLoopData = $brands; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $brand): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($brand->id); ?>" <?php if(request('brand_id') == $brand->id): echo 'selected'; endif; ?>><?php echo e($brand->name); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-2.5 text-ink-muted">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </div>
                            </div>

                            
                            <div class="relative col-span-2 sm:col-span-1 w-full sm:w-auto">
                                <select name="stock_status" class="w-full sm:w-auto text-sm border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-900 pl-3 pr-8 py-2 text-gray-700 dark:text-gray-100 focus:ring-1 focus:ring-[#5542ff] focus:border-[#5542ff] appearance-none cursor-pointer shadow-2xs transition-colors" onchange="this.form.submit()">
                                    <option value="">All Stock Status</option>
                                    <option value="in_stock" <?php if(request('stock_status') === 'in_stock'): echo 'selected'; endif; ?>>In Stock</option>
                                    <option value="low_stock" <?php if(request('stock_status') === 'low_stock'): echo 'selected'; endif; ?>>Low Stock</option>
                                    <option value="out_of_stock" <?php if(request('stock_status') === 'out_of_stock'): echo 'selected'; endif; ?>>Out of Stock</option>
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
                    <?php if(auth()->user()->hasPermission('Add Product')): ?>
                        <a href="<?php echo e(route('products.create')); ?>" class="inline-flex items-center justify-center px-3 sm:px-4 py-2 bg-white dark:bg-gray-800 border border-[#5542ff] text-[#5542ff] dark:text-gray-100 dark:border-gray-600 rounded-xl sm:rounded-md hover:bg-[#5542ff]/5 dark:hover:bg-gray-700 transition-colors text-sm font-semibold whitespace-nowrap">
                            <svg class="h-4 w-4 sm:mr-1.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                            <span class="hidden sm:inline">Add New</span>
                        </a>
                    <?php endif; ?>

                    <?php if(auth()->user()->hasPermission('Download Product PDF') || auth()->user()->hasPermission('Download Product CSV') || auth()->user()->hasPermission('Product Import')): ?>
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
                                    <?php if(auth()->user()->hasPermission('Download Product PDF')): ?>
                                        <a href="<?php echo e(route('products.export.pdf', request()->query())); ?>" class="flex items-center px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700">
                                            <svg class="h-4 w-4 mr-2 text-gray-400 dark:text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>
                                            Download PDF
                                        </a>
                                    <?php endif; ?>
                                    <button type="button" @click="printSelected()" class="w-full flex items-center px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 text-left">
                                        <svg class="h-4 w-4 mr-2 text-gray-400 dark:text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" /></svg>
                                        Print Selected
                                    </button>
                                    <?php if(auth()->user()->hasPermission('Download Product CSV')): ?>
                                        <a href="<?php echo e(route('products.export.csv', request()->query())); ?>" class="flex items-center px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700">
                                            <svg class="h-4 w-4 mr-2 text-gray-400 dark:text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                                            Download CSV
                                        </a>
                                    <?php endif; ?>
                                    <?php if(auth()->user()->hasPermission('Product Import')): ?>
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

        
        <?php if(request()->hasAny(['search', 'category_id', 'brand_id', 'stock_status'])): ?>
            <div class="flex flex-wrap items-center gap-2 px-1 py-1">
                <span class="text-xs font-semibold text-ink-muted">Active Filters:</span>
                
                <?php if(request('search')): ?>
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium bg-[#5542ff]/10 text-[#5542ff] dark:bg-[#5542ff]/20 dark:text-indigo-300 border border-[#5542ff]/20">
                        Search: "<?php echo e(request('search')); ?>"
                        <a href="<?php echo e(request()->fullUrlWithQuery(['search' => null])); ?>" class="hover:text-red-500 font-bold ml-1" title="Remove search filter">&times;</a>
                    </span>
                <?php endif; ?>

                <?php if(request('category_id') && $selectedCat = $categories->firstWhere('id', request('category_id'))): ?>
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium bg-[#5542ff]/10 text-[#5542ff] dark:bg-[#5542ff]/20 dark:text-indigo-300 border border-[#5542ff]/20">
                        Category: <?php echo e($selectedCat->name); ?>

                        <a href="<?php echo e(request()->fullUrlWithQuery(['category_id' => null])); ?>" class="hover:text-red-500 font-bold ml-1" title="Remove category filter">&times;</a>
                    </span>
                <?php endif; ?>

                <?php if(request('brand_id') && $selectedBrand = $brands->firstWhere('id', request('brand_id'))): ?>
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium bg-[#5542ff]/10 text-[#5542ff] dark:bg-[#5542ff]/20 dark:text-indigo-300 border border-[#5542ff]/20">
                        Brand: <?php echo e($selectedBrand->name); ?>

                        <a href="<?php echo e(request()->fullUrlWithQuery(['brand_id' => null])); ?>" class="hover:text-red-500 font-bold ml-1" title="Remove brand filter">&times;</a>
                    </span>
                <?php endif; ?>

                <?php if(request('stock_status')): ?>
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium bg-[#5542ff]/10 text-[#5542ff] dark:bg-[#5542ff]/20 dark:text-indigo-300 border border-[#5542ff]/20">
                        Stock: <?php echo e(ucfirst(str_replace('_', ' ', request('stock_status')))); ?>

                        <a href="<?php echo e(request()->fullUrlWithQuery(['stock_status' => null])); ?>" class="hover:text-red-500 font-bold ml-1" title="Remove stock filter">&times;</a>
                    </span>
                <?php endif; ?>

                <a href="<?php echo e(route('products.all')); ?>" class="text-xs text-red-600 dark:text-red-400 hover:underline font-semibold ml-1">
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

        
        <div class="<?php echo e(auth()->user()->hasRole('salesperson') ? 'lg:hidden' : 'sm:hidden'); ?> space-y-3">
            <?php if(!auth()->user()->hasRole('salesperson')): ?>
                <div class="flex items-center justify-between px-4 py-2.5 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm">
                    <label class="flex items-center gap-2 cursor-pointer select-none text-xs font-bold text-gray-700 dark:text-gray-200">
                        <input type="checkbox" x-model="selectAll" @change="toggleSelectAll" class="rounded border-gray-300 text-[#5542ff] focus:ring-[#5542ff]">
                        <span>Select All Products</span>
                    </label>
                    <span class="text-xs text-gray-400 font-semibold" x-text="selectedItems.length + ' selected'"></span>
                </div>
            <?php endif; ?>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
            <?php $__empty_1 = true; $__currentLoopData = $products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <?php
                    $unitLabel = $product->unit->short_name ?? $product->unit->name;
                    $stockStr = rtrim(rtrim(number_format((float) $product->current_stock, 4, '.', ''), '0'), '.') ?: '0';
                    $low = (float) $product->current_stock <= (float) $product->alert_quantity;
                ?>
                <div :class="{ 'print-hidden': !selectedItems.includes(String('<?php echo e($product->id); ?>')) }"
                     class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-4 shadow-sm space-y-3 transition-colors">
                    
                    
                    <div class="flex items-start gap-3 border-b border-gray-100 dark:border-gray-700 pb-3">
                        <?php if(!auth()->user()->hasRole('salesperson')): ?>
                            <input type="checkbox" value="<?php echo e($product->id); ?>" x-model="selectedItems" class="row-checkbox rounded border-gray-300 text-[#5542ff] focus:ring-[#5542ff] mt-0.5 shrink-0">
                        <?php endif; ?>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-1.5 flex-wrap">
                                <span class="font-black text-[#5542ff] dark:text-gray-100 text-sm leading-snug break-words"><?php echo e($product->name); ?></span>
                                <?php if($low): ?>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-orange-100 text-orange-700 dark:bg-orange-900/40 dark:text-orange-300 border border-orange-200 dark:border-orange-700">
                                        Low Stock
                                    </span>
                                <?php endif; ?>
                            </div>
                            <p class="text-xs font-mono text-ink-muted mt-0.5"><?php echo e($product->sku); ?></p>
                        </div>
                    </div>

                    
                    <div class="grid grid-cols-2 gap-2.5 text-xs">
                        <div>
                            <span class="text-gray-400 text-[10px] uppercase font-bold tracking-wide block">Category / Brand</span>
                            <span class="text-ink-body font-medium"><?php echo e($product->category?->name ?? '—'); ?></span>
                            <?php if($product->brand?->name): ?>
                                <span class="text-[#5542ff] font-bold block"><?php echo e($product->brand->name); ?></span>
                            <?php endif; ?>
                        </div>
                        <div>
                            <span class="text-gray-400 text-[10px] uppercase font-bold tracking-wide block">Stock / Unit</span>
                            <span class="text-sm font-black <?php echo e($low ? 'text-orange-600 dark:text-orange-400' : 'text-gray-900 dark:text-white'); ?>"><?php echo e($stockStr); ?> <?php echo e($unitLabel); ?></span>
                            <span class="text-[10px] text-gray-400 block">Alert at <?php echo e(rtrim(rtrim(number_format((float) $product->alert_quantity, 4, '.', ''), '0'), '.') ?: '0'); ?></span>
                        </div>
                        <div>
                            <span class="text-gray-400 text-[10px] uppercase font-bold tracking-wide block">Price</span>
                            <span class="text-sm font-extrabold text-gray-900 dark:text-white"><?php echo e(currencySymbol()); ?><?php echo e(number_format((float) $product->fixed_price, 2)); ?></span>
                        </div>
                        <div>
                            <span class="text-gray-400 text-[10px] uppercase font-bold tracking-wide block">Total Sold</span>
                            <span class="text-xs font-semibold text-ink-body"><?php echo e($product->total_sold); ?></span>
                        </div>
                    </div>

                    
                    <div class="flex items-center justify-between pt-2.5 border-t border-gray-100 dark:border-gray-700 text-xs">
                        <div>
                            <?php if($product->has_expiry): ?>
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-amber-50 border border-amber-300 text-amber-700 dark:bg-amber-900/30 dark:border-amber-700 dark:text-amber-300">
                                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    <?php echo e($product->expiry_alert_days ?? 30); ?>d expiry alert
                                </span>
                            <?php else: ?>
                                <span class="text-[10px] text-gray-400 italic">No Expiry</span>
                            <?php endif; ?>
                        </div>

                        <?php if(auth()->user()->hasPermission('Product Edit')): ?>
                            <a href="<?php echo e(route('products.edit', $product)); ?>" class="inline-flex items-center px-3 py-1.5 bg-white dark:bg-gray-800 border border-[#5542ff] dark:border-gray-600 text-[#5542ff] dark:text-gray-100 rounded-lg hover:bg-[#5542ff]/5 text-xs font-bold transition-colors">
                                <svg class="h-3.5 w-3.5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                                Edit
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-8 text-center text-sm text-gray-400 md:col-span-2">
                    No products found.
                </div>
            <?php endif; ?>
            </div>

            <?php if($products->hasPages()): ?>
                <div class="px-4 py-3 bg-gray-50 dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700">
                    <?php echo e($products->links()); ?>

                </div>
            <?php endif; ?>
        </div>

        
        <div class="<?php echo e(auth()->user()->hasRole('salesperson') ? 'hidden lg:block' : 'hidden sm:block'); ?> bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-center border-collapse min-w-[900px]">
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
                            <th class="px-4 py-4 text-[11px] font-bold uppercase tracking-wide text-center">Name | SKU</th>
                            <th class="px-4 py-4 text-[11px] font-bold uppercase tracking-wide text-center">Category | Brand</th>
                            <th class="px-4 py-4 text-[11px] font-bold uppercase tracking-wide text-center">Stock</th>
                            <th class="px-4 py-4 text-[11px] font-bold uppercase tracking-wide text-center">Price</th>
                            <th class="px-4 py-4 text-[11px] font-bold uppercase tracking-wide text-center">Total Sale | Alert Qty</th>
                            <th class="px-4 py-4 text-[11px] font-bold uppercase tracking-wide text-center">Unit</th>
                            <th class="px-4 py-4 text-[11px] font-bold uppercase tracking-wide text-center">Expiration Alert</th>
                            <th class="px-4 py-4 text-[11px] font-bold uppercase tracking-wide text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        <?php $__empty_1 = true; $__currentLoopData = $products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <?php
                                $unitLabel = $product->unit->short_name ?? $product->unit->name;
                                $stockStr = rtrim(rtrim(number_format((float) $product->current_stock, 4, '.', ''), '0'), '.') ?: '0';
                                $low = (float) $product->current_stock <= (float) $product->alert_quantity;
                            ?>
                            <tr :class="{ 'print-hidden': !selectedItems.includes(String('<?php echo e($product->id); ?>')) }" class="hover:bg-gray-50/90 dark:hover:bg-gray-700 transition-colors bg-white dark:bg-gray-800">
                                <?php if(!auth()->user()->hasRole('salesperson')): ?>
                                    <td class="checkbox-column px-4 py-3.5 align-middle">
                                        <input type="checkbox" value="<?php echo e($product->id); ?>" x-model="selectedItems" class="row-checkbox rounded border-gray-300 text-[#5542ff] focus:ring-[#5542ff]">
                                    </td>
                                <?php endif; ?>
                                <td class="px-4 py-4 align-middle">
                                    <div class="flex flex-col items-center justify-center gap-1.5">
                                        <?php if($low): ?>
                                            <span class="inline-block w-2 h-2 rounded-full bg-orange-500 shrink-0" title="At or below alert quantity"></span>
                                        <?php endif; ?>
                                        <div class="flex flex-col items-center max-w-[240px] mx-auto">
                                            <div class="text-sm font-bold text-[#5542ff] leading-snug break-words"><?php echo e($product->name); ?></div>
                                            <div class="text-xs font-normal text-ink-muted mt-1"><?php echo e($product->sku); ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-4 align-middle">
                                    <div class="flex flex-col items-center justify-center gap-1">
                                        <div class="text-sm font-normal text-ink-muted"><?php echo e($product->category?->name ?? '—'); ?></div>
                                        <div class="text-sm font-bold text-[#5542ff]"><?php echo e($product->brand?->name ?? '—'); ?></div>
                                    </div>
                                </td>
                                <td class="px-4 py-4 align-middle text-sm font-normal text-ink-muted"><?php echo e($stockStr); ?></td>
                                <td class="px-4 py-4 align-middle text-sm font-normal text-ink-muted"><?php echo e(currencySymbol()); ?><?php echo e(number_format((float) $product->fixed_price, 2)); ?></td>
                                <td class="px-4 py-4 align-middle">
                                    <div class="flex flex-col items-center justify-center gap-2">
                                        <div class="text-sm font-normal text-ink-muted"><?php echo e($product->total_sold); ?></div>
                                        <span class="inline-flex items-center justify-center min-w-[2.5rem] px-2.5 py-1 rounded-full text-xs font-semibold border border-orange-400 dark:border-orange-600 text-orange-600 dark:text-orange-300 bg-white dark:bg-orange-900/30">
                                            <?php echo e(rtrim(rtrim(number_format((float) $product->alert_quantity, 4, '.', ''), '0'), '.') ?: '0'); ?>

                                        </span>
                                    </div>
                                </td>
                                <td class="px-4 py-4 align-middle text-sm font-normal text-ink-muted"><?php echo e($unitLabel); ?></td>
                                
                                <td class="px-4 py-4 align-middle text-center">
                                    <?php if($product->has_expiry): ?>
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-50 border border-amber-400 text-amber-700 dark:bg-amber-900/30 dark:border-amber-600 dark:text-amber-300">
                                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                            <?php echo e($product->expiry_alert_days ?? 30); ?>d before
                                        </span>
                                    <?php else: ?>
                                        <span class="text-xs text-ink-muted italic">No Expiry</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-4 py-4 align-middle">
                                    <div class="flex justify-center">
                                        <?php if(auth()->user()->hasPermission('Product Edit')): ?>
                                    <a href="<?php echo e(route('products.edit', $product)); ?>" class="inline-flex items-center justify-center px-2.5 py-1.5 bg-white dark:bg-gray-800 border border-[#5542ff] dark:border-gray-600 text-[#5542ff] dark:text-gray-100 rounded-md hover:bg-[#5542ff]/5 dark:hover:bg-gray-700 text-[11px] font-bold">
                                        <svg class="h-3.5 w-3.5 mr-1 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                        Edit
                                    </a>
                                <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="9" class="px-6 py-12 text-center text-ink-muted">No products found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <?php if($products->hasPages()): ?>
                <div class="px-4 py-3 bg-gray-50 dark:bg-gray-900 border-t border-gray-100 dark:border-gray-700">
                    <?php echo e($products->links()); ?>

                </div>
            <?php endif; ?>
        </div>

        
        <div x-show="importOpen" x-cloak class="fixed inset-0 z-[210] overflow-y-auto" style="display: none;">
            <div class="flex items-center justify-center min-h-screen px-4 py-8">
                <div class="fixed inset-0 bg-gray-900/50 z-0" @click="importOpen = false"></div>
                <div class="relative bg-white dark:bg-gray-800 rounded-xl shadow-xl max-w-lg w-full p-6 z-10 border border-gray-100 dark:border-gray-700" @click.stop>
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-bold text-[#0a1233] dark:text-gray-100">Import Product</h3>
                        <button type="button" @click="importOpen = false" class="text-gray-400 hover:text-ink-muted" aria-label="Close">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                        </button>
                    </div>

                    <div class="rounded-lg border border-amber-200 dark:border-amber-700 bg-amber-50 dark:bg-amber-900/20 text-amber-950 dark:text-amber-300 text-xs p-3 mb-4 space-y-1">
                        <p class="font-semibold">CSV must match the sample: same columns and order.</p>
                        <p>Required: <strong>name</strong>, <strong>category</strong>, <strong>sku</strong>, <strong>brand</strong>, <strong>unit</strong>, <strong>alert_quantity</strong>. Optional: <strong>note</strong>.</p>
                        <p><strong>name</strong> and <strong>sku</strong> must be unique. Category, brand, and unit must match names (or unit short name) already in the system.</p>
                        <p>Fix errors in your file and re-import. Large files may need higher PHP time/memory limits.</p>
                        <p><strong>Comma</strong> or <strong>semicolon</strong> separators work (e.g. Excel “CSV UTF-8”).</p>
                    </div>

                    <form method="POST" action="<?php echo e(route('products.import')); ?>" enctype="multipart/form-data" class="space-y-4">
                        <?php echo csrf_field(); ?>
                        <div>
                            <label class="block text-sm font-medium text-ink-body mb-1">Select File <span class="text-red-500 dark:text-red-400">*</span></label>
                            <input type="file" name="csv_file" accept=".csv,.txt" required class="block w-full text-sm text-ink-muted file:mr-3 file:py-2 file:px-3 file:rounded-md file:border-0 file:bg-[#5542ff]/10 file:text-[#5542ff] file:font-semibold" />
                            <p class="text-xs text-ink-muted mt-1">Supported files: <strong>csv</strong></p>
                            <?php $__errorArgs = ['csv_file'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <p class="text-red-500 dark:text-red-400 text-xs mt-1"><?php echo e($message); ?></p>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                        <p class="text-sm text-ink-muted">
                            Download sample template:
                            <a href="<?php echo e(route('products.import.sample')); ?>" class="text-[#5542ff] font-semibold hover:underline">product.csv</a>
                        </p>
                        <button type="submit" class="w-full py-3 rounded-lg bg-[#5542ff] text-white font-semibold hover:bg-[#4736d6] transition-colors">
                            Import
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Print Warning Modal -->
        <div x-show="showPrintMessage" x-cloak class="fixed inset-0 z-[500] flex items-center justify-center">
            <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" @click="showPrintMessage = false"></div>
            <div class="relative bg-white dark:bg-gray-800 rounded-xl shadow-xl max-w-sm w-full mx-4 p-6 z-10 text-center border border-gray-100 dark:border-gray-700">
                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-yellow-100 dark:bg-yellow-900/30 mb-4">
                    <svg class="h-6 w-6 text-yellow-600 dark:text-yellow-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                </div>
                <h3 class="text-lg font-bold text-ink-heading mb-2">Please select records</h3>
                <p class="text-sm text-ink-muted mb-6">Please select at least one record to print.</p>
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
<?php /**PATH C:\laragon\www\GE-System-1.1\resources\views/products/index.blade.php ENDPATH**/ ?>