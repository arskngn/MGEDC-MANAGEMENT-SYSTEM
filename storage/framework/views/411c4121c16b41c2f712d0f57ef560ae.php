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
    <?php
        $initialLines = collect($lines)->values()->all();
        $returnDate = old('return_date', $saleReturn->return_date->format('Y-m-d'));
        $noteValue = old('note', $saleReturn->note);
    ?>
    <script type="application/json" id="sale-return-initial-lines"><?php echo json_encode($initialLines, 15, 512) ?></script>

    <div
        class="max-w-6xl mx-auto space-y-6 pb-8"
        x-data="saleReturnForm({
            discount: <?php echo e(old('discount', $saleReturn->discount)); ?>,
            restockingFee: <?php echo e(old('restocking_fee', $saleReturn->restocking_fee)); ?>

        })"
    >
        
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <p class="text-[11px] font-bold uppercase tracking-widest text-[#4634ff] mb-1">Sales · Returns</p>
                <h2 class="text-2xl font-black text-[#0a1233] dark:text-gray-100">Edit Sale Return</h2>
                <p class="text-sm text-ink-muted mt-1"><?php echo e($saleReturn->return_invoice_no); ?> · <?php echo e($sale->customer->name); ?></p>
            </div>
            <div class="flex items-center gap-2 shrink-0">
                <a href="<?php echo e(route('sales.return')); ?>" class="inline-flex items-center px-4 py-2.5 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-600 rounded-xl text-sm font-semibold text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                    <svg class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                    Back
                </a>
                <button
                    type="submit"
                    form="sale-return-form"
                    :disabled="!anyItemsSelected || hasErrors"
                    class="inline-flex items-center px-5 py-2.5 rounded-xl bg-[#4634ff] text-white text-sm font-bold hover:bg-violet-700 disabled:opacity-50 disabled:cursor-not-allowed transition-all shadow-lg shadow-[#4634ff]/25"
                >
                    <svg class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                    Update Return
                </button>
            </div>
        </div>

        <?php if($errors->any()): ?>
            <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-700 rounded-2xl p-4 flex items-start gap-3">
                <span class="flex-shrink-0 w-8 h-8 rounded-full bg-red-100 dark:bg-red-900/40 flex items-center justify-center">
                    <svg class="h-4 w-4 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                </span>
                <div class="flex-1">
                    <h4 class="font-bold text-red-800 dark:text-red-200 text-sm mb-2">Please fix the following errors</h4>
                    <ul class="text-sm text-red-700 dark:text-red-300 space-y-1">
                        <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <li><?php echo e($error); ?></li>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </ul>
                </div>
            </div>
        <?php endif; ?>

        <form id="sale-return-form" action="<?php echo e(route('sales.return.update', $saleReturn)); ?>" method="POST" class="space-y-6">
            <?php echo csrf_field(); ?>
            <?php echo method_field('PUT'); ?>

            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <?php echo $__env->make('sales.partials.return-form-meta', [
                    'returnInvoiceNo' => $saleReturn->return_invoice_no,
                    'returnDate' => $returnDate,
                ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            </div>

            <div class="space-y-4">
                <div class="flex items-center justify-between px-1">
                    <div>
                        <h3 class="text-sm font-black uppercase tracking-wider text-[#0a1233] dark:text-gray-100">Products to Return</h3>
                        <p class="text-xs text-ink-muted mt-0.5">Enter good qty to restock, damaged qty for write-off</p>
                    </div>
                    <span class="text-xs font-semibold text-gray-400" x-text="items.length + ' line(s)'"></span>
                </div>

                <?php echo $__env->make('sales.partials.return-product-card', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            </div>

            <?php $__errorArgs = ['items'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                <p class="text-red-600 text-sm bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-700 rounded-xl px-4 py-3"><?php echo e($message); ?></p>
            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>

            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <?php echo $__env->make('sales.partials.return-form-footer', ['noteValue' => $noteValue], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            </div>
        </form>
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
<?php /**PATH C:\laragon\www\GE-System-1.1\resources\views/sales/return-edit.blade.php ENDPATH**/ ?>