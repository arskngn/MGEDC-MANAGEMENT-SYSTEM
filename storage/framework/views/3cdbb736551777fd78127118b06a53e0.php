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
    <div class="space-y-6">
        
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">Generated Reports</h1>
                <p class="text-sm text-ink-muted mt-1">
                    Centralized analytical reporting center for sales orders, purchasing inventory, and executive business metrics.
                </p>
            </div>

            
            <div class="flex flex-wrap items-center gap-2">
                <?php if($activeTab === 'sales'): ?>
                    <a href="<?php echo e(route('reports.generated.sales.pdf', request()->query())); ?>" 
                       class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-semibold rounded-lg bg-red-50 hover:bg-red-100 text-red-700 border border-red-200 dark:bg-red-950/30 dark:border-red-900/40 dark:text-red-300 transition-colors shadow-xs">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Export PDF
                    </a>
                    <a href="<?php echo e(route('reports.generated.sales.csv', request()->query())); ?>" 
                       class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-semibold rounded-lg bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border border-emerald-200 dark:bg-emerald-950/30 dark:border-emerald-900/40 dark:text-emerald-300 transition-colors shadow-xs">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Export CSV
                    </a>
                    <a href="<?php echo e(route('reports.generated.sales.pdf', array_merge(request()->query(), ['print' => 1]))); ?>" target="_blank"
                       class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-semibold rounded-lg bg-white hover:bg-gray-50 text-gray-700 border border-gray-300 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-200 dark:hover:bg-gray-750 transition-colors shadow-xs">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                        </svg>
                        Print
                    </a>
                <?php elseif($activeTab === 'purchases'): ?>
                    <a href="<?php echo e(route('reports.generated.purchases.pdf', request()->query())); ?>" 
                       class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-semibold rounded-lg bg-red-50 hover:bg-red-100 text-red-700 border border-red-200 dark:bg-red-950/30 dark:border-red-900/40 dark:text-red-300 transition-colors shadow-xs">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Export PDF
                    </a>
                    <a href="<?php echo e(route('reports.generated.purchases.csv', request()->query())); ?>" 
                       class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-semibold rounded-lg bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border border-emerald-200 dark:bg-emerald-950/30 dark:border-emerald-900/40 dark:text-emerald-300 transition-colors shadow-xs">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Export CSV
                    </a>
                    <a href="<?php echo e(route('reports.generated.purchases.pdf', array_merge(request()->query(), ['print' => 1]))); ?>" target="_blank"
                       class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-semibold rounded-lg bg-white hover:bg-gray-50 text-gray-700 border border-gray-300 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-200 dark:hover:bg-gray-750 transition-colors shadow-xs">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                        </svg>
                        Print
                    </a>
                <?php elseif($activeTab === 'dashboard'): ?>
                    <a href="<?php echo e(route('reports.generated.dashboard.pdf', request()->query())); ?>" 
                       class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-semibold rounded-lg bg-red-50 hover:bg-red-100 text-red-700 border border-red-200 dark:bg-red-950/30 dark:border-red-900/40 dark:text-red-300 transition-colors shadow-xs">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Export Summary PDF
                    </a>
                    <a href="<?php echo e(route('reports.generated.dashboard.pdf', array_merge(request()->query(), ['print' => 1]))); ?>" target="_blank"
                       class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-semibold rounded-lg bg-white hover:bg-gray-50 text-gray-700 border border-gray-300 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-200 dark:hover:bg-gray-750 transition-colors shadow-xs">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                        </svg>
                        Print Summary
                    </a>
                <?php endif; ?>
            </div>
        </div>

        
        <div class="border-b border-gray-200 dark:border-gray-700">
            <nav class="-mb-px flex space-x-6">
                <a href="<?php echo e(route('reports.generated', ['tab' => 'sales'])); ?>" 
                   class="whitespace-nowrap pb-3 px-1 border-b-2 font-bold text-sm flex items-center gap-2 transition-all <?php echo e($activeTab === 'sales' ? 'border-[#5542ff] text-[#5542ff] dark:text-[#7f6ffb]' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-200'); ?>">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                    </svg>
                    Sales Reports
                </a>
                <a href="<?php echo e(route('reports.generated', ['tab' => 'purchases'])); ?>" 
                   class="whitespace-nowrap pb-3 px-1 border-b-2 font-bold text-sm flex items-center gap-2 transition-all <?php echo e($activeTab === 'purchases' ? 'border-[#5542ff] text-[#5542ff] dark:text-[#7f6ffb]' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-200'); ?>">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    Purchasing Reports
                </a>
                <a href="<?php echo e(route('reports.generated', ['tab' => 'dashboard'])); ?>" 
                   class="whitespace-nowrap pb-3 px-1 border-b-2 font-bold text-sm flex items-center gap-2 transition-all <?php echo e($activeTab === 'dashboard' ? 'border-[#5542ff] text-[#5542ff] dark:text-[#7f6ffb]' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-200'); ?>">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                    Dashboard & Analytics
                </a>
            </nav>
        </div>

        
        
        
        <?php if($activeTab === 'sales'): ?>
            
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xs border border-gray-100 dark:border-gray-700/60 p-4 sm:p-5">
                <form method="GET" action="<?php echo e(route('reports.generated')); ?>" 
                      x-data="purchaseDateRange({ dateFrom: <?php echo \Illuminate\Support\Js::from(request('date_from'))->toHtml() ?>, dateTo: <?php echo \Illuminate\Support\Js::from(request('date_to'))->toHtml() ?> })"
                      x-ref="filterForm"
                      class="flex flex-wrap items-center gap-3">
                    <input type="hidden" name="tab" value="sales" />
                    <input type="hidden" name="date_from" x-model="dateFrom" />
                    <input type="hidden" name="date_to" x-model="dateTo" />

                    
                    <div class="relative flex w-full sm:w-auto shrink-0">
                        <input type="text" name="search" value="<?php echo e(request('search')); ?>" placeholder="Search invoice, customer..."
                               class="w-full pl-3.5 pr-8 py-2 border border-[#5542ff]/40 dark:border-gray-600 rounded-l-md text-sm bg-white dark:bg-gray-900 text-ink-heading focus:ring-1 focus:ring-[#5542ff] focus:border-[#5542ff] placeholder-gray-400" />
                        <?php if(request('search')): ?>
                            <a href="<?php echo e(request()->fullUrlWithQuery(['search' => null])); ?>" class="absolute right-10 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 text-xs h-4 w-4 flex items-center justify-center" title="Clear">&#x2715;</a>
                        <?php endif; ?>
                        <button type="submit" class="px-3 gradient-bg text-white rounded-r-md hover:opacity-90 transition-colors flex items-center justify-center shrink-0" title="Search">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                        </button>
                    </div>

                    
                    <div class="shrink-0">
                        <?php echo $__env->make('components.date-range-picker', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                    </div>

                    
                    <div class="relative shrink-0">
                        <select name="payment_status" onchange="this.form.submit()"
                                class="text-sm border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-900 pl-3 pr-8 py-2 text-gray-700 dark:text-gray-200 focus:ring-1 focus:ring-[#5542ff] focus:border-[#5542ff] appearance-none cursor-pointer">
                            <option value="">All Payment Status</option>
                            <option value="paid" <?php if(request('payment_status') === 'paid'): echo 'selected'; endif; ?>>Paid</option>
                            <option value="partial" <?php if(request('payment_status') === 'partial'): echo 'selected'; endif; ?>>Partial</option>
                            <option value="due" <?php if(request('payment_status') === 'due'): echo 'selected'; endif; ?>>Due</option>
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-2.5 text-ink-muted"><svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg></div>
                    </div>

                    
                    <?php if(!auth()->user()->hasRole('salesperson')): ?>
                        <div class="relative shrink-0">
                            <select name="salesperson_id" onchange="this.form.submit()"
                                    class="text-sm border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-900 pl-3 pr-8 py-2 text-gray-700 dark:text-gray-200 focus:ring-1 focus:ring-[#5542ff] focus:border-[#5542ff] appearance-none cursor-pointer">
                                <option value="">All Salespersons</option>
                                <?php $__currentLoopData = $salespersons; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sp): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($sp->id); ?>" <?php if(request('salesperson_id') == $sp->id): echo 'selected'; endif; ?>><?php echo e($sp->name); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-2.5 text-ink-muted"><svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg></div>
                        </div>
                    <?php endif; ?>

                    
                    <div class="relative shrink-0">
                        <select name="customer_id" onchange="this.form.submit()"
                                class="text-sm border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-900 pl-3 pr-8 py-2 text-gray-700 dark:text-gray-200 focus:ring-1 focus:ring-[#5542ff] focus:border-[#5542ff] appearance-none cursor-pointer">
                            <option value="">All Customers</option>
                            <?php $__currentLoopData = $customers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($c->id); ?>" <?php if(request('customer_id') == $c->id): echo 'selected'; endif; ?>><?php echo e($c->name); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-2.5 text-ink-muted"><svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg></div>
                    </div>

                    
                    <?php if($routes->isNotEmpty()): ?>
                        <div class="relative shrink-0">
                            <select name="route_id" onchange="this.form.submit()"
                                    class="text-sm border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-900 pl-3 pr-8 py-2 text-gray-700 dark:text-gray-200 focus:ring-1 focus:ring-[#5542ff] focus:border-[#5542ff] appearance-none cursor-pointer">
                                <option value="">All Routes</option>
                                <?php $__currentLoopData = $routes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $route): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($route->id); ?>" <?php if(request('route_id') == $route->id): echo 'selected'; endif; ?>><?php echo e($route->display_name); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-2.5 text-ink-muted"><svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg></div>
                        </div>
                    <?php endif; ?>

                    
                    <?php if($storeCategories->isNotEmpty()): ?>
                        <div class="relative shrink-0">
                            <select name="store_category_id" onchange="this.form.submit()"
                                    class="text-sm border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-900 pl-3 pr-8 py-2 text-gray-700 dark:text-gray-200 focus:ring-1 focus:ring-[#5542ff] focus:border-[#5542ff] appearance-none cursor-pointer">
                                <option value="">All Store Categories</option>
                                <?php $__currentLoopData = $storeCategories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sc): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($sc->id); ?>" <?php if(request('store_category_id') == $sc->id): echo 'selected'; endif; ?>><?php echo e($sc->name); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-2.5 text-ink-muted"><svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg></div>
                        </div>
                    <?php endif; ?>
                </form>

                
                <?php if(request()->hasAny(['search', 'date_from', 'date_to', 'payment_status', 'salesperson_id', 'customer_id', 'route_id', 'store_category_id'])): ?>
                    <div class="flex flex-wrap items-center gap-2 mt-3 pt-3 border-t border-gray-100 dark:border-gray-700/60">
                        <span class="text-xs font-semibold text-ink-muted">Active Filters:</span>

                        <?php if(request('search')): ?>
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium bg-[#5542ff]/10 text-[#5542ff] dark:text-indigo-300 border border-[#5542ff]/20">
                                Search: "<?php echo e(request('search')); ?>"
                                <a href="<?php echo e(request()->fullUrlWithQuery(['search' => null])); ?>" class="hover:text-red-500 font-bold ml-1" title="Remove">&times;</a>
                            </span>
                        <?php endif; ?>

                        <?php if(request('date_from') || request('date_to')): ?>
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium bg-[#5542ff]/10 text-[#5542ff] dark:text-indigo-300 border border-[#5542ff]/20">
                                Date: <?php echo e(request('date_from', '…')); ?> – <?php echo e(request('date_to', '…')); ?>

                                <a href="<?php echo e(request()->fullUrlWithQuery(['date_from' => null, 'date_to' => null])); ?>" class="hover:text-red-500 font-bold ml-1" title="Remove">&times;</a>
                            </span>
                        <?php endif; ?>

                        <?php if(request('payment_status')): ?>
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium bg-[#5542ff]/10 text-[#5542ff] dark:text-indigo-300 border border-[#5542ff]/20">
                                Payment: <?php echo e(ucfirst(request('payment_status'))); ?>

                                <a href="<?php echo e(request()->fullUrlWithQuery(['payment_status' => null])); ?>" class="hover:text-red-500 font-bold ml-1" title="Remove">&times;</a>
                            </span>
                        <?php endif; ?>

                        <?php if(request('salesperson_id')): ?>
                            <?php $spName = $salespersons->firstWhere('id', request('salesperson_id'))?->name ?? request('salesperson_id'); ?>
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium bg-[#5542ff]/10 text-[#5542ff] dark:text-indigo-300 border border-[#5542ff]/20">
                                Salesperson: <?php echo e($spName); ?>

                                <a href="<?php echo e(request()->fullUrlWithQuery(['salesperson_id' => null])); ?>" class="hover:text-red-500 font-bold ml-1" title="Remove">&times;</a>
                            </span>
                        <?php endif; ?>

                        <?php if(request('customer_id')): ?>
                            <?php $custName = $customers->firstWhere('id', request('customer_id'))?->name ?? request('customer_id'); ?>
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium bg-[#5542ff]/10 text-[#5542ff] dark:text-indigo-300 border border-[#5542ff]/20">
                                Customer: <?php echo e($custName); ?>

                                <a href="<?php echo e(request()->fullUrlWithQuery(['customer_id' => null])); ?>" class="hover:text-red-500 font-bold ml-1" title="Remove">&times;</a>
                            </span>
                        <?php endif; ?>

                        <?php if(request('route_id')): ?>
                            <?php $routeName = $routes->firstWhere('id', request('route_id'))?->display_name ?? request('route_id'); ?>
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium bg-[#5542ff]/10 text-[#5542ff] dark:text-indigo-300 border border-[#5542ff]/20">
                                Route: <?php echo e($routeName); ?>

                                <a href="<?php echo e(request()->fullUrlWithQuery(['route_id' => null])); ?>" class="hover:text-red-500 font-bold ml-1" title="Remove">&times;</a>
                            </span>
                        <?php endif; ?>

                        <?php if(request('store_category_id')): ?>
                            <?php $scName = $storeCategories->firstWhere('id', request('store_category_id'))?->name ?? request('store_category_id'); ?>
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium bg-[#5542ff]/10 text-[#5542ff] dark:text-indigo-300 border border-[#5542ff]/20">
                                Store Category: <?php echo e($scName); ?>

                                <a href="<?php echo e(request()->fullUrlWithQuery(['store_category_id' => null])); ?>" class="hover:text-red-500 font-bold ml-1" title="Remove">&times;</a>
                            </span>
                        <?php endif; ?>

                        <a href="<?php echo e(route('reports.generated', ['tab' => 'sales'])); ?>" class="text-xs text-red-600 dark:text-red-400 hover:underline font-semibold ml-1">Reset All Filters</a>
                    </div>
                <?php endif; ?>
            </div>

            
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 border border-gray-100 dark:border-gray-700/60 shadow-xs">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-ink-muted uppercase tracking-wider">Total Sales</span>
                        <div class="p-2 bg-indigo-50 dark:bg-indigo-950/40 text-[#5542ff] dark:text-[#7f6ffb] rounded-xl">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>
                    <div class="mt-3">
                        <div class="text-2xl font-black text-gray-900 dark:text-white"><?php echo e(formatCurrency($kpis['total_amount'])); ?></div>
                        <div class="text-xs text-ink-muted mt-1"><?php echo e(number_format($kpis['total_count'])); ?> orders in period</div>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 border border-gray-100 dark:border-gray-700/60 shadow-xs">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-ink-muted uppercase tracking-wider">Total Received</span>
                        <div class="p-2 bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 rounded-xl">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>
                    <div class="mt-3">
                        <div class="text-2xl font-black text-gray-900 dark:text-white"><?php echo e(formatCurrency($kpis['total_paid'])); ?></div>
                        <div class="text-xs text-ink-muted mt-1">Paid directly or collected</div>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 border border-gray-100 dark:border-gray-700/60 shadow-xs">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-ink-muted uppercase tracking-wider">Outstanding Due</span>
                        <div class="p-2 bg-amber-50 dark:bg-amber-950/40 text-amber-600 dark:text-amber-400 rounded-xl">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </div>
                    </div>
                    <div class="mt-3">
                        <div class="text-2xl font-black text-gray-900 dark:text-white"><?php echo e(formatCurrency($kpis['total_due'])); ?></div>
                        <div class="text-xs text-ink-muted mt-1">Receivables remaining</div>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 border border-gray-100 dark:border-gray-700/60 shadow-xs">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-ink-muted uppercase tracking-wider">Total Discounts</span>
                        <div class="p-2 bg-purple-50 dark:bg-purple-950/40 text-purple-600 dark:text-purple-400 rounded-xl">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                            </svg>
                        </div>
                    </div>
                    <div class="mt-3">
                        <div class="text-2xl font-black text-gray-900 dark:text-white"><?php echo e(formatCurrency($kpis['total_discount'])); ?></div>
                        <div class="text-xs text-ink-muted mt-1">Promotional / deducted</div>
                    </div>
                </div>
            </div>

            
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xs border border-gray-100 dark:border-gray-700/60 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-gray-50/80 dark:bg-gray-900/60 text-gray-600 dark:text-gray-300 font-semibold border-b border-gray-100 dark:border-gray-700/60 text-xs uppercase tracking-wider">
                            <tr>
                                <th class="py-3.5 px-4">Invoice No</th>
                                <th class="py-3.5 px-4">Date</th>
                                <th class="py-3.5 px-4">Customer</th>
                                <th class="py-3.5 px-4">Salesperson</th>
                                <th class="py-3.5 px-4 text-right">Total Amount</th>
                                <th class="py-3.5 px-4 text-right">Paid</th>
                                <th class="py-3.5 px-4 text-right">Due</th>
                                <th class="py-3.5 px-4 text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700/60 text-ink-body">
                            <?php $__empty_1 = true; $__currentLoopData = $sales; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <?php
                                    $due = max(0, (float) $s->receivable_amount - (float) $s->paid_amount);
                                ?>
                                <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-750/50 transition-colors">
                                    <td class="py-3 px-4 font-semibold text-[#5542ff] dark:text-[#7f6ffb]">
                                        <a href="<?php echo e(route('sales.show', $s)); ?>" class="hover:underline"><?php echo e($s->invoice_no); ?></a>
                                    </td>
                                    <td class="py-3 px-4 text-gray-600 dark:text-gray-300"><?php echo e($s->sale_date?->format('M d, Y')); ?></td>
                                    <td class="py-3 px-4">
                                        <div class="font-medium text-gray-900 dark:text-white"><?php echo e($s->customer?->name ?? 'Walk-in'); ?></div>
                                        <div class="text-xs text-gray-400"><?php echo e($s->customer?->phone); ?></div>
                                    </td>
                                    <td class="py-3 px-4 text-gray-600 dark:text-gray-300"><?php echo e($s->salesperson?->name ?? ($s->user?->name ?? 'N/A')); ?></td>
                                    <td class="py-3 px-4 text-right font-medium text-gray-900 dark:text-white"><?php echo e(formatCurrency($s->receivable_amount)); ?></td>
                                    <td class="py-3 px-4 text-right text-emerald-600 dark:text-emerald-400 font-medium"><?php echo e(formatCurrency($s->paid_amount)); ?></td>
                                    <td class="py-3 px-4 text-right font-semibold <?php echo e($due > 0 ? 'text-amber-600 dark:text-amber-400' : 'text-gray-400'); ?>">
                                        <?php echo e(formatCurrency($due)); ?>

                                    </td>
                                    <td class="py-3 px-4 text-center">
                                        <?php if($due <= 0): ?>
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300">Paid</span>
                                        <?php elseif($s->paid_amount > 0): ?>
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300">Partial</span>
                                        <?php else: ?>
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300">Due</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr>
                                    <td colspan="8" class="py-12 text-center text-ink-muted">
                                        <svg class="mx-auto h-9 w-9 text-gray-300 dark:text-gray-600 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                        No sales records found matching the specified filters.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <?php if($sales->hasPages()): ?>
                    <div class="p-4 border-t border-gray-100 dark:border-gray-700/60">
                        <?php echo e($sales->links()); ?>

                    </div>
                <?php endif; ?>
            </div>

        
        
        
        <?php elseif($activeTab === 'purchases'): ?>
            
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xs border border-gray-100 dark:border-gray-700/60 p-4 sm:p-5">
                <form method="GET" action="<?php echo e(route('reports.generated')); ?>" 
                      x-data="purchaseDateRange({ dateFrom: <?php echo \Illuminate\Support\Js::from(request('date_from'))->toHtml() ?>, dateTo: <?php echo \Illuminate\Support\Js::from(request('date_to'))->toHtml() ?> })"
                      x-ref="filterForm"
                      class="flex flex-wrap items-center gap-3">
                    <input type="hidden" name="tab" value="purchases" />
                    <input type="hidden" name="date_from" x-model="dateFrom" />
                    <input type="hidden" name="date_to" x-model="dateTo" />

                    
                    <div class="relative flex w-full sm:w-auto shrink-0">
                        <input type="text" name="search" value="<?php echo e(request('search')); ?>" placeholder="Search invoice, supplier..."
                               class="w-full pl-3.5 pr-8 py-2 border border-[#5542ff]/40 dark:border-gray-600 rounded-l-md text-sm bg-white dark:bg-gray-900 text-ink-heading focus:ring-1 focus:ring-[#5542ff] focus:border-[#5542ff] placeholder-gray-400" />
                        <?php if(request('search')): ?>
                            <a href="<?php echo e(request()->fullUrlWithQuery(['search' => null])); ?>" class="absolute right-10 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 text-xs h-4 w-4 flex items-center justify-center" title="Clear">&#x2715;</a>
                        <?php endif; ?>
                        <button type="submit" class="px-3 gradient-bg text-white rounded-r-md hover:opacity-90 transition-colors flex items-center justify-center shrink-0" title="Search">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                        </button>
                    </div>

                    
                    <div class="shrink-0">
                        <?php echo $__env->make('components.date-range-picker', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                    </div>

                    
                    <div class="relative shrink-0">
                        <select name="status" onchange="this.form.submit()"
                                class="text-sm border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-900 pl-3 pr-8 py-2 text-gray-700 dark:text-gray-200 focus:ring-1 focus:ring-[#5542ff] focus:border-[#5542ff] appearance-none cursor-pointer">
                            <option value="">All Order Status</option>
                            <option value="ordered" <?php if(request('status') === 'ordered'): echo 'selected'; endif; ?>>Ordered</option>
                            <option value="received" <?php if(request('status') === 'received'): echo 'selected'; endif; ?>>Received</option>
                            <option value="completed" <?php if(request('status') === 'completed'): echo 'selected'; endif; ?>>Completed</option>
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-2.5 text-ink-muted"><svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg></div>
                    </div>

                    
                    <div class="relative shrink-0">
                        <select name="payment_status" onchange="this.form.submit()"
                                class="text-sm border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-900 pl-3 pr-8 py-2 text-gray-700 dark:text-gray-200 focus:ring-1 focus:ring-[#5542ff] focus:border-[#5542ff] appearance-none cursor-pointer">
                            <option value="">All Payment Status</option>
                            <option value="paid" <?php if(request('payment_status') === 'paid'): echo 'selected'; endif; ?>>Paid</option>
                            <option value="partial" <?php if(request('payment_status') === 'partial'): echo 'selected'; endif; ?>>Partial</option>
                            <option value="due" <?php if(request('payment_status') === 'due'): echo 'selected'; endif; ?>>Due</option>
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-2.5 text-ink-muted"><svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg></div>
                    </div>

                    
                    <div class="relative shrink-0">
                        <select name="supplier_id" onchange="this.form.submit()"
                                class="text-sm border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-900 pl-3 pr-8 py-2 text-gray-700 dark:text-gray-200 focus:ring-1 focus:ring-[#5542ff] focus:border-[#5542ff] appearance-none cursor-pointer">
                            <option value="">All Suppliers</option>
                            <?php $__currentLoopData = $suppliers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sup): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($sup->id); ?>" <?php if(request('supplier_id') == $sup->id): echo 'selected'; endif; ?>><?php echo e($sup->name); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-2.5 text-ink-muted"><svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg></div>
                    </div>
                </form>

                
                <?php if(request()->hasAny(['search', 'date_from', 'date_to', 'status', 'payment_status', 'supplier_id'])): ?>
                    <div class="flex flex-wrap items-center gap-2 mt-3 pt-3 border-t border-gray-100 dark:border-gray-700/60">
                        <span class="text-xs font-semibold text-ink-muted">Active Filters:</span>

                        <?php if(request('search')): ?>
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium bg-[#5542ff]/10 text-[#5542ff] dark:text-indigo-300 border border-[#5542ff]/20">
                                Search: "<?php echo e(request('search')); ?>"
                                <a href="<?php echo e(request()->fullUrlWithQuery(['search' => null])); ?>" class="hover:text-red-500 font-bold ml-1" title="Remove">&times;</a>
                            </span>
                        <?php endif; ?>

                        <?php if(request('date_from') || request('date_to')): ?>
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium bg-[#5542ff]/10 text-[#5542ff] dark:text-indigo-300 border border-[#5542ff]/20">
                                Date: <?php echo e(request('date_from', '…')); ?> – <?php echo e(request('date_to', '…')); ?>

                                <a href="<?php echo e(request()->fullUrlWithQuery(['date_from' => null, 'date_to' => null])); ?>" class="hover:text-red-500 font-bold ml-1" title="Remove">&times;</a>
                            </span>
                        <?php endif; ?>

                        <?php if(request('status')): ?>
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium bg-[#5542ff]/10 text-[#5542ff] dark:text-indigo-300 border border-[#5542ff]/20">
                                Order: <?php echo e(ucfirst(request('status'))); ?>

                                <a href="<?php echo e(request()->fullUrlWithQuery(['status' => null])); ?>" class="hover:text-red-500 font-bold ml-1" title="Remove">&times;</a>
                            </span>
                        <?php endif; ?>

                        <?php if(request('payment_status')): ?>
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium bg-[#5542ff]/10 text-[#5542ff] dark:text-indigo-300 border border-[#5542ff]/20">
                                Payment: <?php echo e(ucfirst(request('payment_status'))); ?>

                                <a href="<?php echo e(request()->fullUrlWithQuery(['payment_status' => null])); ?>" class="hover:text-red-500 font-bold ml-1" title="Remove">&times;</a>
                            </span>
                        <?php endif; ?>

                        <?php if(request('supplier_id')): ?>
                            <?php $supName = $suppliers->firstWhere('id', request('supplier_id'))?->name ?? request('supplier_id'); ?>
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium bg-[#5542ff]/10 text-[#5542ff] dark:text-indigo-300 border border-[#5542ff]/20">
                                Supplier: <?php echo e($supName); ?>

                                <a href="<?php echo e(request()->fullUrlWithQuery(['supplier_id' => null])); ?>" class="hover:text-red-500 font-bold ml-1" title="Remove">&times;</a>
                            </span>
                        <?php endif; ?>

                        <a href="<?php echo e(route('reports.generated', ['tab' => 'purchases'])); ?>" class="text-xs text-red-600 dark:text-red-400 hover:underline font-semibold ml-1">Reset All Filters</a>
                    </div>
                <?php endif; ?>
            </div>

            
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 border border-gray-100 dark:border-gray-700/60 shadow-xs">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-ink-muted uppercase tracking-wider">Total Purchases</span>
                        <div class="p-2 bg-indigo-50 dark:bg-indigo-950/40 text-[#5542ff] dark:text-[#7f6ffb] rounded-xl">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                            </svg>
                        </div>
                    </div>
                    <div class="mt-3">
                        <div class="text-2xl font-black text-gray-900 dark:text-white"><?php echo e(formatCurrency($kpis['total_amount'])); ?></div>
                        <div class="text-xs text-ink-muted mt-1"><?php echo e(number_format($kpis['total_count'])); ?> orders in period</div>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 border border-gray-100 dark:border-gray-700/60 shadow-xs">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-ink-muted uppercase tracking-wider">Total Paid</span>
                        <div class="p-2 bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 rounded-xl">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>
                    <div class="mt-3">
                        <div class="text-2xl font-black text-gray-900 dark:text-white"><?php echo e(formatCurrency($kpis['total_paid'])); ?></div>
                        <div class="text-xs text-ink-muted mt-1">Disbursed to suppliers</div>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 border border-gray-100 dark:border-gray-700/60 shadow-xs">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-ink-muted uppercase tracking-wider">Payables Due</span>
                        <div class="p-2 bg-amber-50 dark:bg-amber-950/40 text-amber-600 dark:text-amber-400 rounded-xl">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>
                    <div class="mt-3">
                        <div class="text-2xl font-black text-gray-900 dark:text-white"><?php echo e(formatCurrency($kpis['total_due'])); ?></div>
                        <div class="text-xs text-ink-muted mt-1">Pending payments</div>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 border border-gray-100 dark:border-gray-700/60 shadow-xs">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-ink-muted uppercase tracking-wider">Purchase Discounts</span>
                        <div class="p-2 bg-purple-50 dark:bg-purple-950/40 text-purple-600 dark:text-purple-400 rounded-xl">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                            </svg>
                        </div>
                    </div>
                    <div class="mt-3">
                        <div class="text-2xl font-black text-gray-900 dark:text-white"><?php echo e(formatCurrency($kpis['total_discount'])); ?></div>
                        <div class="text-xs text-ink-muted mt-1">Savings negotiated</div>
                    </div>
                </div>
            </div>

            
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xs border border-gray-100 dark:border-gray-700/60 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-gray-50/80 dark:bg-gray-900/60 text-gray-600 dark:text-gray-300 font-semibold border-b border-gray-100 dark:border-gray-700/60 text-xs uppercase tracking-wider">
                            <tr>
                                <th class="py-3.5 px-4">Invoice No</th>
                                <th class="py-3.5 px-4">Date</th>
                                <th class="py-3.5 px-4">Supplier</th>
                                <th class="py-3.5 px-4 text-center">Order Status</th>
                                <th class="py-3.5 px-4 text-right">Total Payable</th>
                                <th class="py-3.5 px-4 text-right">Paid</th>
                                <th class="py-3.5 px-4 text-right">Due</th>
                                <th class="py-3.5 px-4 text-center">Payment Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700/60 text-ink-body">
                            <?php $__empty_1 = true; $__currentLoopData = $purchases; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <?php
                                    $due = max(0, (float) $p->payable_amount - (float) $p->paid_amount);
                                ?>
                                <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-750/50 transition-colors">
                                    <td class="py-3 px-4 font-semibold text-[#5542ff] dark:text-[#7f6ffb]">
                                        <a href="<?php echo e(route('purchases.show', $p)); ?>" class="hover:underline"><?php echo e($p->invoice_no); ?></a>
                                    </td>
                                    <td class="py-3 px-4 text-gray-600 dark:text-gray-300"><?php echo e($p->purchase_date?->format('M d, Y')); ?></td>
                                    <td class="py-3 px-4">
                                        <div class="font-medium text-gray-900 dark:text-white"><?php echo e($p->supplier?->name ?? 'N/A'); ?></div>
                                        <div class="text-xs text-gray-400"><?php echo e($p->supplier?->phone); ?></div>
                                    </td>
                                    <td class="py-3 px-4 text-center">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300">
                                            <?php echo e(ucfirst($p->status ?? 'Ordered')); ?>

                                        </span>
                                    </td>
                                    <td class="py-3 px-4 text-right font-medium text-gray-900 dark:text-white"><?php echo e(formatCurrency($p->payable_amount)); ?></td>
                                    <td class="py-3 px-4 text-right text-emerald-600 dark:text-emerald-400 font-medium"><?php echo e(formatCurrency($p->paid_amount)); ?></td>
                                    <td class="py-3 px-4 text-right font-semibold <?php echo e($due > 0 ? 'text-amber-600 dark:text-amber-400' : 'text-gray-400'); ?>">
                                        <?php echo e(formatCurrency($due)); ?>

                                    </td>
                                    <td class="py-3 px-4 text-center">
                                        <?php if($due <= 0): ?>
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300">Paid</span>
                                        <?php elseif($p->paid_amount > 0): ?>
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300">Partial</span>
                                        <?php else: ?>
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300">Due</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr>
                                    <td colspan="8" class="py-12 text-center text-ink-muted">
                                        <svg class="mx-auto h-9 w-9 text-gray-300 dark:text-gray-600 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                                        </svg>
                                        No purchasing records found matching the specified filters.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <?php if($purchases->hasPages()): ?>
                    <div class="p-4 border-t border-gray-100 dark:border-gray-700/60">
                        <?php echo e($purchases->links()); ?>

                    </div>
                <?php endif; ?>
            </div>

        
        
        
        <?php elseif($activeTab === 'dashboard'): ?>
            
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xs border border-gray-100 dark:border-gray-700/60 p-4 sm:p-5">
                <form method="GET" action="<?php echo e(route('reports.generated')); ?>" 
                      x-data="purchaseDateRange({ dateFrom: <?php echo \Illuminate\Support\Js::from($analytics['date_from'])->toHtml() ?>, dateTo: <?php echo \Illuminate\Support\Js::from($analytics['date_to'])->toHtml() ?> })"
                      x-ref="filterForm"
                      class="flex flex-wrap items-center gap-3">
                    <input type="hidden" name="tab" value="dashboard" />
                    <input type="hidden" name="date_from" x-model="dateFrom" />
                    <input type="hidden" name="date_to" x-model="dateTo" />

                    
                    <div class="shrink-0">
                        <?php echo $__env->make('components.date-range-picker', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                    </div>

                    <div class="text-xs text-ink-muted font-medium">
                        Showing analytics between <span class="font-bold text-gray-900 dark:text-white"><?php echo e(Carbon\Carbon::parse($analytics['date_from'])->format('M d, Y')); ?></span> and <span class="font-bold text-gray-900 dark:text-white"><?php echo e(Carbon\Carbon::parse($analytics['date_to'])->format('M d, Y')); ?></span>
                    </div>
                </form>

                
                <?php if(request()->hasAny(['date_from', 'date_to'])): ?>
                    <div class="flex flex-wrap items-center gap-2 mt-3 pt-3 border-t border-gray-100 dark:border-gray-700/60">
                        <span class="text-xs font-semibold text-ink-muted">Active Filters:</span>
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium bg-[#5542ff]/10 text-[#5542ff] dark:text-indigo-300 border border-[#5542ff]/20">
                            Date: <?php echo e(request('date_from', '…')); ?> – <?php echo e(request('date_to', '…')); ?>

                            <a href="<?php echo e(request()->fullUrlWithQuery(['date_from' => null, 'date_to' => null])); ?>" class="hover:text-red-500 font-bold ml-1" title="Remove">&times;</a>
                        </span>
                        <a href="<?php echo e(route('reports.generated', ['tab' => 'dashboard'])); ?>" class="text-xs text-red-600 dark:text-red-400 hover:underline font-semibold ml-1">Reset to Current Month</a>
                    </div>
                <?php endif; ?>
            </div>

            
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 border border-gray-100 dark:border-gray-700/60 shadow-xs">
                    <div class="text-xs font-bold text-ink-muted uppercase tracking-wider">Gross Sales Revenue</div>
                    <div class="text-2xl font-black text-gray-900 dark:text-white mt-2"><?php echo e(formatCurrency($analytics['total_sales'])); ?></div>
                    <div class="text-xs text-ink-muted mt-1"><?php echo e(number_format($analytics['sales_count'])); ?> sales orders processed</div>
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 border border-gray-100 dark:border-gray-700/60 shadow-xs">
                    <div class="text-xs font-bold text-ink-muted uppercase tracking-wider">Total Purchases Cost</div>
                    <div class="text-2xl font-black text-gray-900 dark:text-white mt-2"><?php echo e(formatCurrency($analytics['total_purchases'])); ?></div>
                    <div class="text-xs text-ink-muted mt-1"><?php echo e(number_format($analytics['purchases_count'])); ?> purchase orders placed</div>
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 border border-gray-100 dark:border-gray-700/60 shadow-xs">
                    <div class="text-xs font-bold text-ink-muted uppercase tracking-wider">Gross Margin / Profit</div>
                    <div class="text-2xl font-black text-gray-900 dark:text-white mt-2">
                        <?php echo e(formatCurrency($analytics['gross_profit'])); ?>

                    </div>
                    <div class="text-xs font-semibold text-ink-muted mt-1">
                        Margin: <?php echo e($analytics['profit_margin']); ?>%
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 border border-gray-100 dark:border-gray-700/60 shadow-xs">
                    <div class="text-xs font-bold text-ink-muted uppercase tracking-wider">Customer Collection Rate</div>
                    <?php
                        $collectionRate = $analytics['total_sales'] > 0 ? round(($analytics['total_sales_paid'] / $analytics['total_sales']) * 100, 1) : 0;
                    ?>
                    <div class="text-2xl font-black text-gray-900 dark:text-white mt-2"><?php echo e($collectionRate); ?>%</div>
                    <div class="text-xs text-ink-muted mt-1"><?php echo e(formatCurrency($analytics['sales_due'])); ?> still pending</div>
                </div>
            </div>

            
            
            
            
            <div class="space-y-4">
                <div class="flex items-center justify-between pb-1 border-b border-gray-100 dark:border-gray-700/60">
                    <div>
                        <h2 class="text-lg font-black tracking-tight text-gray-900 dark:text-white flex items-center gap-2">
                            <svg class="w-5 h-5 text-ink-body" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                            </svg>
                            Printable Dashboard Reports
                        </h2>
                        <p class="text-xs text-ink-muted mt-0.5">
                            Instant access to print or download all analytics, statements, and breakdown reports generated within the dashboard.
                        </p>
                    </div>
                </div>

                
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-2 xl:grid-cols-3 gap-4"
                     x-data="{
                         globalFrom: <?php echo \Illuminate\Support\Js::from($analytics['date_from'])->toHtml() ?>,
                         globalTo: <?php echo \Illuminate\Support\Js::from($analytics['date_to'])->toHtml() ?>,

                         // Card 1: Executive Dashboard Brief
                         execFrom: <?php echo \Illuminate\Support\Js::from($analytics['date_from'])->toHtml() ?>,
                         execTo: <?php echo \Illuminate\Support\Js::from($analytics['date_to'])->toHtml() ?>,
                         execOpenFilter: false,

                         // Card 2: Sales & Sales Returns
                         salesFrom: <?php echo \Illuminate\Support\Js::from($analytics['date_from'])->toHtml() ?>,
                         salesTo: <?php echo \Illuminate\Support\Js::from($analytics['date_to'])->toHtml() ?>,
                         salesOpenFilter: false,

                         // Card 3: Purchases & Purchase Returns
                         purchFrom: <?php echo \Illuminate\Support\Js::from($analytics['date_from'])->toHtml() ?>,
                         purchTo: <?php echo \Illuminate\Support\Js::from($analytics['date_to'])->toHtml() ?>,
                         purchOpenFilter: false,

                         // Card 4: Payments Flow Report
                         payFrom: <?php echo \Illuminate\Support\Js::from($analytics['date_from'])->toHtml() ?>,
                         payTo: <?php echo \Illuminate\Support\Js::from($analytics['date_to'])->toHtml() ?>,
                         payOpenFilter: false,

                         // Card 5: Top Selling Products Report
                         topFrom: <?php echo \Illuminate\Support\Js::from($analytics['date_from'])->toHtml() ?>,
                         topTo: <?php echo \Illuminate\Support\Js::from($analytics['date_to'])->toHtml() ?>,
                         topProductId: '',
                         topOpenFilter: false,

                         // Card 6: Category Sales Performance
                         catFrom: <?php echo \Illuminate\Support\Js::from($analytics['date_from'])->toHtml() ?>,
                         catTo: <?php echo \Illuminate\Support\Js::from($analytics['date_to'])->toHtml() ?>,
                         catOpenFilter: false,

                         // Card 7: Single Product Sales Performance & Peak Date Tracker
                         prodFrom: <?php echo \Illuminate\Support\Js::from($analytics['date_from'])->toHtml() ?>,
                         prodTo: <?php echo \Illuminate\Support\Js::from($analytics['date_to'])->toHtml() ?>,
                         prodId: '<?php echo e($allProducts->first()?->id ?? ''); ?>',
                         prodOpenFilter: false,

                         openUrl(baseUrl, params, newTab = true) {
                             const qs = new URLSearchParams();
                             for (const [k, v] of Object.entries(params)) {
                                 if (v !== null && v !== undefined && v !== '') {
                                     qs.set(k, v);
                                 }
                             }
                             const full = baseUrl + (baseUrl.includes('?') ? '&' : '?') + qs.toString();
                             if (newTab) {
                                 window.open(full, '_blank');
                             } else {
                                 window.location.href = full;
                             }
                         }
                     }">

                    
                    <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 border border-gray-200/80 dark:border-gray-700/80 shadow-xs flex flex-col justify-between hover:border-gray-300 dark:hover:border-gray-600 transition-all">
                        <div>
                            <div class="flex items-start justify-between gap-2 mb-2">
                                <div class="p-2.5 rounded-xl bg-[#5542ff]/10 text-[#5542ff] dark:bg-[#5542ff]/20 dark:text-[#7f6ffb] ring-1 ring-[#5542ff]/20">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                </div>
                                <div class="flex items-center gap-1.5">
                                    <button type="button" @click="execOpenFilter = !execOpenFilter" 
                                            :class="execOpenFilter ? 'border-[#5542ff] text-[#5542ff] dark:text-[#7f6ffb] dark:border-[#5542ff]/60 bg-[#5542ff]/5' : 'text-gray-600 dark:text-gray-300 border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/50'"
                                            class="inline-flex items-center gap-1 text-[11px] font-semibold px-2 py-0.5 rounded-md border transition-colors">
                                        <svg class="w-3 h-3 opacity-70" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                                        <span x-text="execOpenFilter ? 'Close' : 'Filter'"></span>
                                    </button>
                                    <span class="text-[10px] font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 bg-gray-100 dark:bg-gray-700/60 px-2 py-0.5 rounded-md border border-gray-200/70 dark:border-gray-700">Executive</span>
                                </div>
                            </div>
                            <h3 class="text-sm font-extrabold text-gray-900 dark:text-white">Executive Dashboard Brief</h3>
                            <p class="text-xs text-ink-muted mt-1 leading-relaxed">
                                Complete executive report including sales revenue, purchasing totals, gross profit margin, top products, and category shares.
                            </p>

                            
                            <div x-show="execOpenFilter" x-transition class="mt-3 p-3 bg-gray-50/80 dark:bg-gray-900/60 border border-gray-200/80 dark:border-gray-700 rounded-xl space-y-2.5 text-xs">
                                <div>
                                    <label class="block text-[11px] font-semibold text-gray-600 dark:text-gray-400 mb-1">Report Date Range</label>
                                    <div class="grid grid-cols-2 gap-2">
                                        <input type="date" x-model="execFrom" class="w-full text-xs py-1.5 px-2.5 rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-800 dark:text-gray-200">
                                        <input type="date" x-model="execTo" class="w-full text-xs py-1.5 px-2.5 rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-800 dark:text-gray-200">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="mt-5 pt-3 border-t border-gray-100 dark:border-gray-700/60 flex items-center justify-between gap-2">
                            <button type="button" @click="openUrl('<?php echo e(route('reports.generated.dashboard.pdf')); ?>', { date_from: execFrom, date_to: execTo }, false)"
                               class="inline-flex items-center gap-1.5 text-xs font-semibold text-gray-700 hover:text-[#5542ff] dark:text-gray-300 dark:hover:text-white transition-colors">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                </svg>
                                Download PDF
                            </button>
                            <button type="button" @click="openUrl('<?php echo e(route('reports.generated.dashboard.pdf')); ?>', { date_from: execFrom, date_to: execTo, print: 1 }, true)"
                               class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold gradient-bg text-white hover:opacity-90 transition-all shadow-xs">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                                </svg>
                                Print Report
                            </button>
                        </div>
                    </div>

                    
                    <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 border border-gray-200/80 dark:border-gray-700/80 shadow-xs flex flex-col justify-between hover:border-gray-300 dark:hover:border-gray-600 transition-all">
                        <div>
                            <div class="flex items-start justify-between gap-2 mb-2">
                                <div class="p-2.5 rounded-xl bg-blue-500/10 text-blue-600 dark:bg-blue-500/20 dark:text-blue-400 ring-1 ring-blue-500/20">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                                    </svg>
                                </div>
                                <div class="flex items-center gap-1.5">
                                    <button type="button" @click="salesOpenFilter = !salesOpenFilter" 
                                            :class="salesOpenFilter ? 'border-[#5542ff] text-[#5542ff] dark:text-[#7f6ffb] dark:border-[#5542ff]/60 bg-[#5542ff]/5' : 'text-gray-600 dark:text-gray-300 border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/50'"
                                            class="inline-flex items-center gap-1 text-[11px] font-semibold px-2 py-0.5 rounded-md border transition-colors">
                                        <svg class="w-3 h-3 opacity-70" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                                        <span x-text="salesOpenFilter ? 'Close' : 'Filter'"></span>
                                    </button>
                                    <span class="text-[10px] font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 bg-gray-100 dark:bg-gray-700/60 px-2 py-0.5 rounded-md border border-gray-200/70 dark:border-gray-700">Sales</span>
                                </div>
                            </div>
                            <h3 class="text-sm font-extrabold text-gray-900 dark:text-white">Sales & Sales Returns</h3>
                            <p class="text-xs text-ink-muted mt-1 leading-relaxed">
                                Detailed statement of daily sales totals, invoice references, returns deducted, and period receivables.
                            </p>

                            
                            <div x-show="salesOpenFilter" x-transition class="mt-3 p-3 bg-gray-50/80 dark:bg-gray-900/60 border border-gray-200/80 dark:border-gray-700 rounded-xl space-y-2.5 text-xs">
                                <div>
                                    <label class="block text-[11px] font-semibold text-gray-600 dark:text-gray-400 mb-1">Sales Period</label>
                                    <div class="grid grid-cols-2 gap-2">
                                        <input type="date" x-model="salesFrom" class="w-full text-xs py-1.5 px-2.5 rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-800 dark:text-gray-200">
                                        <input type="date" x-model="salesTo" class="w-full text-xs py-1.5 px-2.5 rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-800 dark:text-gray-200">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="mt-5 pt-3 border-t border-gray-100 dark:border-gray-700/60 flex items-center justify-between gap-2">
                            <button type="button" @click="openUrl('<?php echo e(route('dashboard.export.pdf')); ?>', { chart: 'chart1', start_date: salesFrom, end_date: salesTo }, true)"
                               class="inline-flex items-center gap-1.5 text-xs font-semibold text-gray-700 hover:text-[#5542ff] dark:text-gray-300 dark:hover:text-white transition-colors">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                                View PDF
                            </button>
                            <button type="button" @click="openUrl('<?php echo e(route('dashboard.export.pdf')); ?>', { chart: 'chart1', start_date: salesFrom, end_date: salesTo }, true)"
                               class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold gradient-bg text-white hover:opacity-90 transition-all shadow-xs">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                                </svg>
                                Print Report
                            </button>
                        </div>
                    </div>

                    
                    <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 border border-gray-200/80 dark:border-gray-700/80 shadow-xs flex flex-col justify-between hover:border-gray-300 dark:hover:border-gray-600 transition-all">
                        <div>
                            <div class="flex items-start justify-between gap-2 mb-2">
                                <div class="p-2.5 rounded-xl bg-emerald-500/10 text-emerald-600 dark:bg-emerald-500/20 dark:text-emerald-400 ring-1 ring-emerald-500/20">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                                    </svg>
                                </div>
                                <div class="flex items-center gap-1.5">
                                    <button type="button" @click="purchOpenFilter = !purchOpenFilter" 
                                            :class="purchOpenFilter ? 'border-[#5542ff] text-[#5542ff] dark:text-[#7f6ffb] dark:border-[#5542ff]/60 bg-[#5542ff]/5' : 'text-gray-600 dark:text-gray-300 border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/50'"
                                            class="inline-flex items-center gap-1 text-[11px] font-semibold px-2 py-0.5 rounded-md border transition-colors">
                                        <svg class="w-3 h-3 opacity-70" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                                        <span x-text="purchOpenFilter ? 'Close' : 'Filter'"></span>
                                    </button>
                                    <span class="text-[10px] font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 bg-gray-100 dark:bg-gray-700/60 px-2 py-0.5 rounded-md border border-gray-200/70 dark:border-gray-700">Purchases</span>
                                </div>
                            </div>
                            <h3 class="text-sm font-extrabold text-gray-900 dark:text-white">Purchases & Purchase Returns</h3>
                            <p class="text-xs text-ink-muted mt-1 leading-relaxed">
                                Complete record of supplier purchase orders, returns processed, invoice tracking, and payable costs.
                            </p>

                            
                            <div x-show="purchOpenFilter" x-transition class="mt-3 p-3 bg-gray-50/80 dark:bg-gray-900/60 border border-gray-200/80 dark:border-gray-700 rounded-xl space-y-2.5 text-xs">
                                <div>
                                    <label class="block text-[11px] font-semibold text-gray-600 dark:text-gray-400 mb-1">Purchasing Period</label>
                                    <div class="grid grid-cols-2 gap-2">
                                        <input type="date" x-model="purchFrom" class="w-full text-xs py-1.5 px-2.5 rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-800 dark:text-gray-200">
                                        <input type="date" x-model="purchTo" class="w-full text-xs py-1.5 px-2.5 rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-800 dark:text-gray-200">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="mt-5 pt-3 border-t border-gray-100 dark:border-gray-700/60 flex items-center justify-between gap-2">
                            <button type="button" @click="openUrl('<?php echo e(route('dashboard.export.pdf')); ?>', { chart: 'chart2', start_date: purchFrom, end_date: purchTo }, true)"
                               class="inline-flex items-center gap-1.5 text-xs font-semibold text-gray-700 hover:text-[#5542ff] dark:text-gray-300 dark:hover:text-white transition-colors">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                                View PDF
                            </button>
                            <button type="button" @click="openUrl('<?php echo e(route('dashboard.export.pdf')); ?>', { chart: 'chart2', start_date: purchFrom, end_date: purchTo }, true)"
                               class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold gradient-bg text-white hover:opacity-90 transition-all shadow-xs">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                                </svg>
                                Print Report
                            </button>
                        </div>
                    </div>

                    
                    <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 border border-gray-200/80 dark:border-gray-700/80 shadow-xs flex flex-col justify-between hover:border-gray-300 dark:hover:border-gray-600 transition-all">
                        <div>
                            <div class="flex items-start justify-between gap-2 mb-2">
                                <div class="p-2.5 rounded-xl bg-purple-500/10 text-purple-600 dark:bg-purple-500/20 dark:text-purple-400 ring-1 ring-purple-500/20">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <div class="flex items-center gap-1.5">
                                    <button type="button" @click="payOpenFilter = !payOpenFilter" 
                                            :class="payOpenFilter ? 'border-[#5542ff] text-[#5542ff] dark:text-[#7f6ffb] dark:border-[#5542ff]/60 bg-[#5542ff]/5' : 'text-gray-600 dark:text-gray-300 border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/50'"
                                            class="inline-flex items-center gap-1 text-[11px] font-semibold px-2 py-0.5 rounded-md border transition-colors">
                                        <svg class="w-3 h-3 opacity-70" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                                        <span x-text="payOpenFilter ? 'Close' : 'Filter'"></span>
                                    </button>
                                    <span class="text-[10px] font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 bg-gray-100 dark:bg-gray-700/60 px-2 py-0.5 rounded-md border border-gray-200/70 dark:border-gray-700">Cash Flow</span>
                                </div>
                            </div>
                            <h3 class="text-sm font-extrabold text-gray-900 dark:text-white">Payments Flow Report</h3>
                            <p class="text-xs text-ink-muted mt-1 leading-relaxed">
                                Complete timeline of payments sent out to suppliers vs. payments received from customers.
                            </p>

                            
                            <div x-show="payOpenFilter" x-transition class="mt-3 p-3 bg-gray-50/80 dark:bg-gray-900/60 border border-gray-200/80 dark:border-gray-700 rounded-xl space-y-2.5 text-xs">
                                <div>
                                    <label class="block text-[11px] font-semibold text-gray-600 dark:text-gray-400 mb-1">Cash Flow Period</label>
                                    <div class="grid grid-cols-2 gap-2">
                                        <input type="date" x-model="payFrom" class="w-full text-xs py-1.5 px-2.5 rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-800 dark:text-gray-200">
                                        <input type="date" x-model="payTo" class="w-full text-xs py-1.5 px-2.5 rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-800 dark:text-gray-200">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="mt-5 pt-3 border-t border-gray-100 dark:border-gray-700/60 flex items-center justify-between gap-2">
                            <button type="button" @click="openUrl('<?php echo e(route('dashboard.export.pdf')); ?>', { chart: 'chart3', start_date: payFrom, end_date: payTo }, true)"
                               class="inline-flex items-center gap-1.5 text-xs font-semibold text-gray-700 hover:text-[#5542ff] dark:text-gray-300 dark:hover:text-white transition-colors">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                                View PDF
                            </button>
                            <button type="button" @click="openUrl('<?php echo e(route('dashboard.export.pdf')); ?>', { chart: 'chart3', start_date: payFrom, end_date: payTo }, true)"
                               class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold gradient-bg text-white hover:opacity-90 transition-all shadow-xs">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                                </svg>
                                Print Report
                            </button>
                        </div>
                    </div>

                    
                    <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 border border-gray-200/80 dark:border-gray-700/80 shadow-xs flex flex-col justify-between hover:border-gray-300 dark:hover:border-gray-600 transition-all">
                        <div>
                            <div class="flex items-start justify-between gap-2 mb-2">
                                <div class="p-2.5 rounded-xl bg-amber-500/10 text-amber-600 dark:bg-amber-500/20 dark:text-amber-400 ring-1 ring-amber-500/20">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                                    </svg>
                                </div>
                                <div class="flex items-center gap-1.5">
                                    <button type="button" @click="topOpenFilter = !topOpenFilter" 
                                            :class="topOpenFilter ? 'border-[#5542ff] text-[#5542ff] dark:text-[#7f6ffb] dark:border-[#5542ff]/60 bg-[#5542ff]/5' : 'text-gray-600 dark:text-gray-300 border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/50'"
                                            class="inline-flex items-center gap-1 text-[11px] font-semibold px-2 py-0.5 rounded-md border transition-colors">
                                        <svg class="w-3 h-3 opacity-70" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                                        <span x-text="topOpenFilter ? 'Close' : 'Filter'"></span>
                                    </button>
                                    <span class="text-[10px] font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 bg-gray-100 dark:bg-gray-700/60 px-2 py-0.5 rounded-md border border-gray-200/70 dark:border-gray-700">Products</span>
                                </div>
                            </div>
                            <h3 class="text-sm font-extrabold text-gray-900 dark:text-white">Top Selling Products Report</h3>
                            <p class="text-xs text-ink-muted mt-1 leading-relaxed">
                                Complete ranking of top-performing products with quantities sold, total revenue, and average selling price.
                            </p>

                            
                            <div x-show="topOpenFilter" x-transition class="mt-3 p-3 bg-gray-50/80 dark:bg-gray-900/60 border border-gray-200/80 dark:border-gray-700 rounded-xl space-y-2.5 text-xs">
                                <div>
                                    <label class="block text-[11px] font-semibold text-gray-600 dark:text-gray-400 mb-1">Date Range</label>
                                    <div class="grid grid-cols-2 gap-2">
                                        <input type="date" x-model="topFrom" class="w-full text-xs py-1.5 px-2.5 rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-800 dark:text-gray-200">
                                        <input type="date" x-model="topTo" class="w-full text-xs py-1.5 px-2.5 rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-800 dark:text-gray-200">
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-[11px] font-semibold text-gray-600 dark:text-gray-400 mb-1">Single Product Focus (Optional)</label>
                                    <select x-model="topProductId" class="w-full text-xs py-1.5 px-2 rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-800 dark:text-gray-200">
                                        <option value="">All Top Selling Products</option>
                                        <?php $__currentLoopData = ($allProducts ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <option value="<?php echo e($p->id); ?>"><?php echo e($p->name); ?><?php echo e(!empty($p->sku) ? ' (' . $p->sku . ')' : ''); ?></option>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="mt-5 pt-3 border-t border-gray-100 dark:border-gray-700/60 flex items-center justify-between gap-2">
                            <button type="button" @click="openUrl('<?php echo e(route('dashboard.top-selling.export.pdf')); ?>', { ids: topProductId, start_date: topFrom, end_date: topTo }, false)"
                               class="inline-flex items-center gap-1.5 text-xs font-semibold text-gray-700 hover:text-[#5542ff] dark:text-gray-300 dark:hover:text-white transition-colors">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                </svg>
                                Download PDF
                            </button>
                            <button type="button" @click="openUrl('<?php echo e(route('dashboard.top-selling.export.pdf')); ?>', { ids: topProductId, start_date: topFrom, end_date: topTo, print: 'true' }, true)"
                               class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold gradient-bg text-white hover:opacity-90 transition-all shadow-xs">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                                </svg>
                                Print Report
                            </button>
                        </div>
                    </div>

                    
                    <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 border border-gray-200/80 dark:border-gray-700/80 shadow-xs flex flex-col justify-between hover:border-gray-300 dark:hover:border-gray-600 transition-all">
                        <div>
                            <div class="flex items-start justify-between gap-2 mb-2">
                                <div class="p-2.5 rounded-xl bg-rose-500/10 text-rose-600 dark:bg-rose-500/20 dark:text-rose-400 ring-1 ring-rose-500/20">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                    </svg>
                                </div>
                                <div class="flex items-center gap-1.5">
                                    <button type="button" @click="catOpenFilter = !catOpenFilter" 
                                            :class="catOpenFilter ? 'border-[#5542ff] text-[#5542ff] dark:text-[#7f6ffb] dark:border-[#5542ff]/60 bg-[#5542ff]/5' : 'text-gray-600 dark:text-gray-300 border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/50'"
                                            class="inline-flex items-center gap-1 text-[11px] font-semibold px-2 py-0.5 rounded-md border transition-colors">
                                        <svg class="w-3 h-3 opacity-70" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                                        <span x-text="catOpenFilter ? 'Close' : 'Filter'"></span>
                                    </button>
                                    <span class="text-[10px] font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 bg-gray-100 dark:bg-gray-700/60 px-2 py-0.5 rounded-md border border-gray-200/70 dark:border-gray-700">Categories</span>
                                </div>
                            </div>
                            <h3 class="text-sm font-extrabold text-gray-900 dark:text-white">Category Sales Performance</h3>
                            <p class="text-xs text-ink-muted mt-1 leading-relaxed">
                                Complete category audit detailing items sold, subtotals, product-level volume, and category revenue distribution.
                            </p>

                            
                            <div x-show="catOpenFilter" x-transition class="mt-3 p-3 bg-gray-50/80 dark:bg-gray-900/60 border border-gray-200/80 dark:border-gray-700 rounded-xl space-y-2.5 text-xs">
                                <div>
                                    <label class="block text-[11px] font-semibold text-gray-600 dark:text-gray-400 mb-1">Category Audit Period</label>
                                    <div class="grid grid-cols-2 gap-2">
                                        <input type="date" x-model="catFrom" class="w-full text-xs py-1.5 px-2.5 rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-800 dark:text-gray-200">
                                        <input type="date" x-model="catTo" class="w-full text-xs py-1.5 px-2.5 rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-800 dark:text-gray-200">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="mt-5 pt-3 border-t border-gray-100 dark:border-gray-700/60 flex items-center justify-between gap-2">
                            <button type="button" @click="openUrl('<?php echo e(route('dashboard.category-performance.export.pdf')); ?>', { start_date: catFrom, end_date: catTo }, true)"
                               class="inline-flex items-center gap-1.5 text-xs font-semibold text-gray-700 hover:text-[#5542ff] dark:text-gray-300 dark:hover:text-white transition-colors">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                                View PDF
                            </button>
                            <button type="button" @click="openUrl('<?php echo e(route('dashboard.category-performance.export.pdf')); ?>', { start_date: catFrom, end_date: catTo }, true)"
                               class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold gradient-bg text-white hover:opacity-90 transition-all shadow-xs">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                                </svg>
                                Print Report
                            </button>
                        </div>
                    </div>

                    
                    <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 border border-gray-200/80 dark:border-gray-700/80 shadow-xs flex flex-col justify-between hover:border-gray-300 dark:hover:border-gray-600 transition-all md:col-span-2 xl:col-span-3">
                        <div>
                            <div class="flex items-start justify-between gap-2 mb-2">
                                <div class="p-2.5 rounded-xl bg-[#5542ff]/10 text-[#5542ff] dark:bg-[#5542ff]/20 dark:text-[#7f6ffb] ring-1 ring-[#5542ff]/20">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                    </svg>
                                </div>
                                <div class="flex items-center gap-1.5">
                                    <button type="button" @click="prodOpenFilter = !prodOpenFilter" 
                                            :class="prodOpenFilter ? 'border-[#5542ff] text-[#5542ff] dark:text-[#7f6ffb] dark:border-[#5542ff]/60 bg-[#5542ff]/5' : 'text-gray-600 dark:text-gray-300 border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/50'"
                                            class="inline-flex items-center gap-1 text-[11px] font-semibold px-2 py-0.5 rounded-md border transition-colors">
                                        <svg class="w-3 h-3 opacity-70" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                                        <span x-text="prodOpenFilter ? 'Close' : 'Filter'"></span>
                                    </button>
                                    <span class="text-[10px] font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 bg-gray-100 dark:bg-gray-700/60 px-2 py-0.5 rounded-md border border-gray-200/70 dark:border-gray-700">Peak Tracker</span>
                                </div>
                            </div>
                            <h3 class="text-sm font-extrabold text-gray-900 dark:text-white">Product Performance & Peak Tracker</h3>
                            <p class="text-xs text-ink-muted mt-1 leading-relaxed">
                                Single-product sales report displaying volume, effective unit price, total sales amount, and highest single-day peak sales date.
                            </p>

                            
                            <div x-show="prodOpenFilter" x-transition class="mt-3 p-3.5 bg-gray-50/80 dark:bg-gray-900/60 border border-gray-200/80 dark:border-gray-700 rounded-xl space-y-2.5 text-xs">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-3 items-end">
                                    <div>
                                        <label class="block text-[11px] font-semibold text-gray-600 dark:text-gray-400 mb-1">Select Product <span class="text-red-500">*</span></label>
                                        <select x-model="prodId" class="w-full text-xs py-1.5 px-2.5 rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-800 dark:text-gray-200">
                                            <?php $__currentLoopData = ($allProducts ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <option value="<?php echo e($p->id); ?>"><?php echo e($p->name); ?><?php echo e(!empty($p->sku) ? ' (' . $p->sku . ')' : ''); ?></option>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-[11px] font-semibold text-gray-600 dark:text-gray-400 mb-1">Tracker Date Range</label>
                                        <div class="grid grid-cols-2 gap-2">
                                            <input type="date" x-model="prodFrom" class="w-full text-xs py-1.5 px-2.5 rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-800 dark:text-gray-200">
                                            <input type="date" x-model="prodTo" class="w-full text-xs py-1.5 px-2.5 rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-800 dark:text-gray-200">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="mt-5 pt-3 border-t border-gray-100 dark:border-gray-700/60 flex items-center justify-between gap-2">
                            <button type="button" @click="if(!prodId){ alert('Please select a product.'); return; } openUrl('<?php echo e(route('dashboard.product-sales-performance.export.pdf')); ?>', { product_id: prodId, start_date: prodFrom, end_date: prodTo }, false)"
                               class="inline-flex items-center gap-1.5 text-xs font-semibold text-gray-700 hover:text-[#5542ff] dark:text-gray-300 dark:hover:text-white transition-colors">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                </svg>
                                Download PDF
                            </button>
                            <button type="button" @click="if(!prodId){ alert('Please select a product.'); return; } openUrl('<?php echo e(route('dashboard.product-sales-performance.export.pdf')); ?>', { product_id: prodId, start_date: prodFrom, end_date: prodTo, print: 'true' }, true)"
                               class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold gradient-bg text-white hover:opacity-90 transition-all shadow-xs">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                                </svg>
                                Print Report
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-8">
                
                <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 border border-gray-100 dark:border-gray-700/60 shadow-xs flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between mb-4 pb-2 border-b border-gray-100 dark:border-gray-700/60">
                            <div>
                                <h3 class="text-base font-extrabold text-gray-900 dark:text-white">Top 10 Selling Products</h3>
                                <p class="text-xs text-gray-400 mt-0.5">Top performing products ranked by revenue</p>
                            </div>
                            <a href="<?php echo e(route('dashboard.top-selling.export.pdf', ['start_date' => $analytics['date_from'], 'end_date' => $analytics['date_to'], 'print' => 'true'])); ?>" target="_blank"
                               class="inline-flex items-center gap-1 text-xs font-semibold gradient-bg text-white hover:opacity-90 transition-all px-2.5 py-1 rounded-lg shadow-2xs">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                                </svg>
                                Print
                            </a>
                        </div>
                        <div class="divide-y divide-gray-100 dark:divide-gray-700/60">
                            <?php $__empty_1 = true; $__currentLoopData = $analytics['top_products']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $idx => $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <div class="py-3 flex items-center justify-between">
                                    <div class="flex items-center gap-3">
                                        <span class="w-6 h-6 flex items-center justify-center rounded-full text-xs font-bold bg-gray-100 dark:bg-gray-700 text-ink-body">
                                            <?php echo e($idx + 1); ?>

                                        </span>
                                        <div>
                                            <div class="text-sm font-semibold text-gray-900 dark:text-white"><?php echo e($p->name); ?></div>
                                            <div class="text-xs text-gray-400">SKU: <?php echo e($p->sku ?: 'N/A'); ?> &bull; Qty: <?php echo e(number_format($p->total_quantity)); ?></div>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <div class="text-sm font-bold text-gray-900 dark:text-white"><?php echo e(formatCurrency($p->total_amount)); ?></div>
                                        <div class="text-[11px] text-gray-400"><?php echo e(formatCurrency($p->total_quantity > 0 ? $p->total_amount / $p->total_quantity : 0)); ?> / unit</div>
                                    </div>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <div class="py-8 text-center text-gray-400 text-sm">No sales recorded for this date range.</div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                
                <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 border border-gray-100 dark:border-gray-700/60 shadow-xs flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between mb-4 pb-2 border-b border-gray-100 dark:border-gray-700/60">
                            <div>
                                <h3 class="text-base font-extrabold text-gray-900 dark:text-white">Sales Performance by Category</h3>
                                <p class="text-xs text-gray-400 mt-0.5">Category contribution to overall sales volume</p>
                            </div>
                            <a href="<?php echo e(route('dashboard.category-performance.export.pdf', ['start_date' => $analytics['date_from'], 'end_date' => $analytics['date_to']])); ?>" target="_blank"
                               class="inline-flex items-center gap-1 text-xs font-semibold gradient-bg text-white hover:opacity-90 transition-all px-2.5 py-1 rounded-lg shadow-2xs">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                                </svg>
                                Print
                            </a>
                        </div>
                        <div class="divide-y divide-gray-100 dark:divide-gray-700/60">
                            <?php $__empty_1 = true; $__currentLoopData = $analytics['category_breakdown']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <?php
                                    $percent = $analytics['total_sales'] > 0 ? round(($cat->total_amount / $analytics['total_sales']) * 100, 1) : 0;
                                ?>
                                <div class="py-3">
                                    <div class="flex items-center justify-between mb-1.5 text-sm">
                                        <span class="font-semibold text-gray-900 dark:text-white"><?php echo e($cat->category_name); ?></span>
                                        <span class="font-bold text-gray-900 dark:text-white"><?php echo e(formatCurrency($cat->total_amount)); ?> (<?php echo e($percent); ?>%)</span>
                                    </div>
                                    <div class="w-full bg-gray-200 dark:bg-gray-700/80 rounded-full h-2.5 overflow-hidden p-0.5 border border-gray-100 dark:border-gray-700">
                                        <div class="gradient-bg h-full rounded-full transition-all duration-500 shadow-xs" style="width: <?php echo e(min(max($percent, 0), 100)); ?>%"></div>
                                    </div>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <div class="py-8 text-center text-gray-400 text-sm">No category sales recorded for this date range.</div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
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
<?php /**PATH C:\laragon\www\GE-System-1.1\resources\views/reports/generated.blade.php ENDPATH**/ ?>