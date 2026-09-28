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
    <div class="space-y-4">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <h2 class="text-2xl font-bold text-gray-900"><?php echo e($title); ?></h2>
        </div>

        <!-- Filters (aligned to Sales Reports filter card design) -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xs border border-gray-100 dark:border-gray-700/60 p-4 sm:p-5">
            <form id="filter-form" action="" method="GET" class="flex flex-wrap items-center gap-3"
                x-data="purchaseDateRange({ dateFrom: <?php echo \Illuminate\Support\Js::from(request('date_from'))->toHtml() ?>, dateTo: <?php echo \Illuminate\Support\Js::from(request('date_to'))->toHtml() ?> })"
                x-ref="filterForm"
            >
                <input type="hidden" name="date_from" x-model="dateFrom" />
                <input type="hidden" name="date_to" x-model="dateTo" />
                <!-- Type Filter -->
                <div class="relative shrink-0 w-full sm:w-auto">
                    <select name="type" onchange="this.form.submit()"
                            class="text-sm border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-900 pl-3 pr-8 py-2 text-gray-700 dark:text-gray-200 focus:ring-1 focus:ring-[#5542ff] focus:border-[#5542ff] appearance-none cursor-pointer w-full sm:w-[180px]">
                        <option value="">All Types</option>
                        <option value="CREATED" <?php if(request('type') === 'CREATED'): echo 'selected'; endif; ?>>Created</option>
                        <option value="UPDATED" <?php if(request('type') === 'UPDATED'): echo 'selected'; endif; ?>>Updated</option>
                        <option value="DELETED" <?php if(request('type') === 'DELETED'): echo 'selected'; endif; ?>>Deleted</option>
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-2.5 text-ink-muted">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </div>
                </div>

                <!-- By Filter (User) -->
                <div class="relative shrink-0 w-full sm:w-auto">
                    <select name="by" onchange="this.form.submit()"
                            class="text-sm border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-900 pl-3 pr-8 py-2 text-gray-700 dark:text-gray-200 focus:ring-1 focus:ring-[#5542ff] focus:border-[#5542ff] appearance-none cursor-pointer w-full sm:w-[200px]">
                        <option value="">All Users</option>
                        <?php $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($user->id); ?>" <?php if(request('by') == $user->id): echo 'selected'; endif; ?>><?php echo e($user->name); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-2.5 text-ink-muted">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </div>
                </div>

                <!-- Date Range Picker (using shared component — same as Sales) -->
                <div class="shrink-0" @keydown.escape.window="escapeClose()">
                    <?php echo $__env->make('components.date-range-picker', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                </div>

                <!-- Sort Toggle (matches Sales pattern) -->
                <input type="hidden" name="sort" id="sort-input" value="<?php echo e(request('sort', 'desc')); ?>">
                <button type="button" onclick="toggleSort()"
                        class="inline-flex items-center justify-center h-[38px] w-[38px] border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-900 hover:bg-gray-50 dark:hover:bg-gray-750 transition-colors shrink-0"
                        title="Toggle Sort Order">
                    <?php if(request('sort', 'desc') === 'asc'): ?>
                        <svg class="h-4 w-4 text-ink-muted" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4h13M3 8h9m-9 4h6m4 0l4-4m0 0l4 4m-4-4v12m0 0l-4-4m4 4l4-4" />
                        </svg>
                    <?php else: ?>
                        <svg class="h-4 w-4 text-ink-muted" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4h13M3 8h9m-9 4h9m5-4v12m0 0l-4-4m4 4l4-4" />
                        </svg>
                    <?php endif; ?>
                </button>
            </form>

            
            <?php if(request()->hasAny(['type', 'by', 'date_from', 'date_to']) || request('sort') && request('sort') !== 'desc'): ?>
                <div class="flex flex-wrap items-center gap-2 mt-3 pt-3 border-t border-gray-100 dark:border-gray-700/60">
                    <span class="text-xs font-semibold text-ink-muted">Active Filters:</span>

                    <?php if(request('type')): ?>
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium bg-[#5542ff]/10 text-[#5542ff] dark:text-indigo-300 border border-[#5542ff]/20">
                            Type: <?php echo e(request('type')); ?>

                            <a href="<?php echo e(request()->fullUrlWithQuery(['type' => null])); ?>" class="hover:text-red-500 font-bold ml-1" title="Remove">&times;</a>
                        </span>
                    <?php endif; ?>

                    <?php if(request('by')): ?>
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium bg-[#5542ff]/10 text-[#5542ff] dark:text-indigo-300 border border-[#5542ff]/20">
                            By: <?php echo e($users->firstWhere('id', request('by'))?->name ?? 'User #'.request('by')); ?>

                            <a href="<?php echo e(request()->fullUrlWithQuery(['by' => null])); ?>" class="hover:text-red-500 font-bold ml-1" title="Remove">&times;</a>
                        </span>
                    <?php endif; ?>

                    <?php if(request('date_from') || request('date_to')): ?>
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium bg-[#5542ff]/10 text-[#5542ff] dark:text-indigo-300 border border-[#5542ff]/20">
                            Date: <?php echo e(request('date_from', '…')); ?> – <?php echo e(request('date_to', '…')); ?>

                            <a href="<?php echo e(request()->fullUrlWithQuery(['date_from' => null, 'date_to' => null])); ?>" class="hover:text-red-500 font-bold ml-1" title="Remove">&times;</a>
                        </span>
                    <?php endif; ?>

                    <?php if(request('sort') && request('sort') !== 'desc'): ?>
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium bg-[#5542ff]/10 text-[#5542ff] dark:text-indigo-300 border border-[#5542ff]/20">
                            Sort: <?php echo e(strtoupper(request('sort'))); ?>

                            <a href="<?php echo e(request()->fullUrlWithQuery(['sort' => null])); ?>" class="hover:text-red-500 font-bold ml-1" title="Remove">&times;</a>
                        </span>
                    <?php endif; ?>

                    <a href="<?php echo e(route(request()->route()->getName())); ?>" class="text-xs text-red-600 dark:text-red-400 hover:underline font-semibold ml-1">Reset All Filters</a>
                </div>
            <?php endif; ?>
        </div>

        <!-- Activity Logs Table -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xs border border-gray-100 dark:border-gray-700/60 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="gradient-bg text-white">
                            <?php switch($reportType):
                                case ('purchase'): ?>
                                <?php case ('purchase-return'): ?>
                                <?php case ('sale'): ?>
                                <?php case ('sale-return'): ?>
                                    <th class="px-6 py-4 text-left font-semibold">Invoice No.</th>
                                <?php break; ?>
                                <?php case ('product'): ?>
                                    <th class="px-6 py-4 text-left font-semibold">Product Name</th>
                                    <th class="px-6 py-4 text-left font-semibold">SKU</th>
                                <?php break; ?>
                                <?php case ('customer'): ?>
                                    <th class="px-6 py-4 text-left font-semibold">Name</th>
                                    <th class="px-6 py-4 text-left font-semibold">Mobile</th>
                                <?php break; ?>
                                <?php case ('customer-payment'): ?>
                                    <th class="px-6 py-4 text-left font-semibold">TRX No.</th>
                                    <th class="px-6 py-4 text-left font-semibold">Customer</th>
                                    <th class="px-6 py-4 text-left font-semibold">Amount</th>
                                <?php break; ?>
                                <?php case ('supplier'): ?>
                                    <th class="px-6 py-4 text-left font-semibold">Name</th>
                                    <th class="px-6 py-4 text-left font-semibold">Mobile</th>
                                <?php break; ?>
                                <?php case ('supplier-payment'): ?>
                                    <th class="px-6 py-4 text-left font-semibold">TRX No.</th>
                                    <th class="px-6 py-4 text-left font-semibold">Supplier</th>
                                    <th class="px-6 py-4 text-left font-semibold">Amount</th>
                                <?php break; ?>
                                <?php case ('adjustment'): ?>
                                <?php case ('transfer'): ?>
                                    <th class="px-6 py-4 text-left font-semibold">Tracking No.</th>
                                <?php break; ?>
                                <?php case ('expense'): ?>
                                    <th class="px-6 py-4 text-left font-semibold">Expense Reason</th>
                                    <th class="px-6 py-4 text-left font-semibold">Amount</th>
                                <?php break; ?>
                            <?php endswitch; ?>
                            <th class="px-6 py-4 text-left font-semibold">Type</th>
                            <th class="px-6 py-4 text-left font-semibold">By</th>
                            <th class="px-6 py-4 text-left font-semibold">Time</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <?php $__empty_1 = true; $__currentLoopData = $activities; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $activity): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr class="hover:bg-gray-50 transition-colors">
                                <?php switch($reportType):
                                    case ('purchase'): ?>
                                    <?php case ('sale'): ?>
                                        <?php
                                            $model = $models[$activity->model_id] ?? null;
                                        ?>
                                        <td class="px-6 py-4 text-blue-600 font-medium">
                                            <?php echo e($model?->invoice_no ?? 'N/A'); ?>

                                        </td>
                                    <?php break; ?>
                                    <?php case ('purchase-return'): ?>
                                    <?php case ('sale-return'): ?>
                                        <?php
                                            $model = $models[$activity->model_id] ?? null;
                                        ?>
                                        <td class="px-6 py-4 text-blue-600 font-medium">
                                            <?php echo e($model?->return_invoice_no ?? $model?->invoice_no ?? 'N/A'); ?>

                                        </td>
                                    <?php break; ?>
                                    <?php case ('product'): ?>
                                        <?php
                                            $model = $models[$activity->model_id] ?? null;
                                        ?>
                                        <td class="px-6 py-4 text-gray-700">
                                            <?php echo e($model?->name ?? 'N/A'); ?>

                                        </td>
                                        <td class="px-6 py-4 text-gray-700">
                                            <?php echo e($model?->sku ?? 'N/A'); ?>

                                        </td>
                                    <?php break; ?>
                                    <?php case ('customer'): ?>
                                        <?php
                                            $model = $models[$activity->model_id] ?? null;
                                        ?>
                                        <td class="px-6 py-4 text-gray-700">
                                            <?php echo e($model?->name ?? 'N/A'); ?>

                                        </td>
                                        <td class="px-6 py-4 text-gray-700">
                                            <?php echo e($model?->phone ?? $model?->mobile ?? 'N/A'); ?>

                                        </td>
                                    <?php break; ?>
                                    <?php case ('customer-payment'): ?>
                                        <?php
                                            $model = $models[$activity->model_id] ?? null;
                                        ?>
                                        <td class="px-6 py-4 text-blue-600 font-medium">
                                            #<?php echo e($model?->id ?? 'N/A'); ?>

                                        </td>
                                        <td class="px-6 py-4 text-gray-700">
                                            <?php echo e($model?->sale?->customer?->name ?? 'N/A'); ?>

                                        </td>
                                        <td class="px-6 py-4 text-right text-gray-700 font-medium">
                                            <?php echo e(number_format($model?->amount ?? 0, 2)); ?>

                                        </td>
                                    <?php break; ?>
                                    <?php case ('supplier'): ?>
                                        <?php
                                            $model = $models[$activity->model_id] ?? null;
                                        ?>
                                        <td class="px-6 py-4 text-gray-700">
                                            <?php echo e($model?->name ?? 'N/A'); ?>

                                        </td>
                                        <td class="px-6 py-4 text-gray-700">
                                            <?php echo e($model?->phone ?? $model?->mobile ?? 'N/A'); ?>

                                        </td>
                                    <?php break; ?>
                                    <?php case ('supplier-payment'): ?>
                                        <?php
                                            $model = $models[$activity->model_id] ?? null;
                                        ?>
                                        <td class="px-6 py-4 text-blue-600 font-medium">
                                            #<?php echo e($model?->id ?? 'N/A'); ?>

                                        </td>
                                        <td class="px-6 py-4 text-gray-700">
                                            <?php echo e($model?->purchase?->supplier?->name ?? 'N/A'); ?>

                                        </td>
                                        <td class="px-6 py-4 text-right text-gray-700 font-medium">
                                            <?php echo e(number_format($model?->amount ?? 0, 2)); ?>

                                        </td>
                                    <?php break; ?>
                                    <?php case ('adjustment'): ?>
                                    <?php case ('transfer'): ?>
                                        <?php
                                            $model = $models[$activity->model_id] ?? null;
                                        ?>
                                        <td class="px-6 py-4 text-blue-600 font-medium">
                                            <?php echo e($model?->tracking_no ?? 'N/A'); ?>

                                        </td>
                                    <?php break; ?>
                                    <?php case ('expense'): ?>
                                        <?php
                                            $model = $models[$activity->model_id] ?? null;
                                        ?>
                                        <td class="px-6 py-4 text-gray-700">
                                            <?php echo e($model?->description ?? $model?->reason ?? $model?->expenseType?->name ?? 'N/A'); ?>

                                        </td>
                                        <td class="px-6 py-4 text-right text-gray-700 font-medium">
                                            <?php echo e(number_format($model?->amount ?? 0, 2)); ?>

                                        </td>
                                    <?php break; ?>
                                <?php endswitch; ?>
                                
                                <td class="px-6 py-4 text-gray-700">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold <?php echo e(strtoupper($activity->action) === 'CREATED' ? 'bg-green-100 text-green-800' : (strtoupper($activity->action) === 'UPDATED' ? 'bg-blue-100 text-blue-800' : 'bg-red-100 text-red-800')); ?>">
                                        <?php echo e(strtoupper($activity->action)); ?>

                                    </span>
                                </td>
                                <td class="px-6 py-4 text-gray-700">
                                    <?php echo e($activity->user?->name ?? 'System'); ?>

                                </td>
                                <td class="px-6 py-4 text-gray-700">
                                    <?php echo e(formatDateTime($activity->created_at)); ?>

                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="10" class="px-6 py-12 text-center text-gray-500">
                                    <div class="flex flex-col items-center justify-center">
                                        <svg class="w-12 h-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 21l-4.35-4.35M11 19a8 8 0 100-16 8 8 0 000 16z" />
                                        </svg>
                                        <p class="font-medium">No activity records found</p>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <?php if($activities->hasPages()): ?>
                <div class="px-4 py-3 bg-gray-50 border-t border-gray-100">
                    <?php echo e($activities->links()); ?>

                </div>
            <?php endif; ?>
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

<script>
function toggleSort() {
    const sortInput = document.getElementById('sort-input');
    sortInput.value = sortInput.value === 'asc' ? 'desc' : 'asc';
    document.getElementById('filter-form').submit();
}
</script>
<?php /**PATH C:\laragon\www\GE-System-1.1\resources\views/pages/data-entry-report.blade.php ENDPATH**/ ?>