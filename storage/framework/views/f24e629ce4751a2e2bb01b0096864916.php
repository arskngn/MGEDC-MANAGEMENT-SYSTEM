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
    <div class="max-w-4xl mx-auto space-y-6"
        x-data="{
            hasExpiry: <?php echo e(old('has_expiry') ? 'true' : 'false'); ?>,
            requiresBatch: <?php echo e(old('requires_batch_tracking', old('has_expiry') ? '1' : '0') ? 'true' : 'false'); ?>,
        }"
    >
        <div class="flex justify-between items-center">
            <h2 class="text-xl font-bold text-[#0a1233] dark:text-white">Add Product</h2>
            <a href="<?php echo e(route('products.all')); ?>" class="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                <svg class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                Back
            </a>
        </div>

        <form action="<?php echo e(route('products.store')); ?>" method="POST" class="bg-gray-50/80 dark:bg-gray-800/60 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 space-y-6">
            <?php echo csrf_field(); ?>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-ink-body mb-1">Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" value="<?php echo e(old('name')); ?>" required class="w-full rounded-lg border border-gray-300/80 dark:border-gray-600 bg-gray-100 dark:bg-gray-900 px-3 py-2 text-sm text-ink-heading placeholder:text-gray-400 dark:placeholder:text-gray-500 focus:bg-white dark:focus:bg-gray-800 focus:ring-1 focus:ring-[#5542ff] focus:border-[#5542ff] transition-colors" />
                    <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="text-red-500 text-xs mt-1"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>
                <div>
                    <label class="block text-sm font-medium text-ink-body mb-1">Category <span class="text-red-500">*</span></label>
                    <select name="category_id" required class="w-full rounded-lg border border-gray-300/80 dark:border-gray-600 bg-gray-100 dark:bg-gray-900 px-3 py-2 text-sm text-ink-heading focus:bg-white dark:focus:bg-gray-800 focus:ring-1 focus:ring-[#5542ff] focus:border-[#5542ff] transition-colors">
                        <option value="">Select One</option>
                        <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($c->id); ?>" <?php if(old('category_id') == $c->id): echo 'selected'; endif; ?>><?php echo e($c->name); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                    <?php $__errorArgs = ['category_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="text-red-500 text-xs mt-1"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>
                <div>
                    <label class="block text-sm font-medium text-ink-body mb-1">Brand <span class="text-red-500">*</span></label>
                    <select name="brand_id" required class="w-full rounded-lg border border-gray-300/80 dark:border-gray-600 bg-gray-100 dark:bg-gray-900 px-3 py-2 text-sm text-ink-heading focus:bg-white dark:focus:bg-gray-800 focus:ring-1 focus:ring-[#5542ff] focus:border-[#5542ff] transition-colors">
                        <option value="">Select One</option>
                        <?php $__currentLoopData = $brands; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $b): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($b->id); ?>" <?php if(old('brand_id') == $b->id): echo 'selected'; endif; ?>><?php echo e($b->name); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                    <?php $__errorArgs = ['brand_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="text-red-500 text-xs mt-1"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>
                <div>
                    <label class="block text-sm font-medium text-ink-body mb-1">SKU <span class="text-red-500">*</span></label>
                    <input type="text" name="sku" value="<?php echo e(old('sku')); ?>" required class="w-full rounded-lg border border-gray-300/80 dark:border-gray-600 bg-gray-100 dark:bg-gray-900 px-3 py-2 text-sm text-ink-heading placeholder:text-gray-400 dark:placeholder:text-gray-500 focus:bg-white dark:focus:bg-gray-800 focus:ring-1 focus:ring-[#5542ff] focus:border-[#5542ff] transition-colors" />
                    <?php $__errorArgs = ['sku'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="text-red-500 text-xs mt-1"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>
                <div>
                    <label class="block text-sm font-medium text-ink-body mb-1">Unit (UoM) <span class="text-red-500">*</span></label>
                    <select name="unit_id" required class="w-full rounded-lg border border-gray-300/80 dark:border-gray-600 bg-gray-100 dark:bg-gray-900 px-3 py-2 text-sm text-ink-heading focus:bg-white dark:focus:bg-gray-800 focus:ring-1 focus:ring-[#5542ff] focus:border-[#5542ff] transition-colors">
                        <option value="">Select One</option>
                        <?php $__currentLoopData = $units; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $u): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($u->id); ?>" <?php if(old('unit_id') == $u->id): echo 'selected'; endif; ?>><?php echo e($u->name); ?> (<?php echo e($u->short_name); ?>)</option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                    <?php $__errorArgs = ['unit_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="text-red-500 text-xs mt-1"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>
                <div>
                    <label class="block text-sm font-medium text-ink-body mb-1">Alert Quantity <span class="text-red-500">*</span></label>
                    <input type="number" name="alert_quantity" value="<?php echo e(old('alert_quantity', 0)); ?>" step="0.0001" min="0" required class="w-full rounded-lg border border-gray-300/80 dark:border-gray-600 bg-gray-100 dark:bg-gray-900 px-3 py-2 text-sm text-ink-heading focus:bg-white dark:focus:bg-gray-800 focus:ring-1 focus:ring-[#5542ff] focus:border-[#5542ff] transition-colors" />
                    <?php $__errorArgs = ['alert_quantity'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="text-red-500 text-xs mt-1"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>
                <div>
                    <label class="block text-sm font-medium text-ink-body mb-1">Price <span class="text-red-500">*</span></label>
                    <input type="number" name="fixed_price" value="<?php echo e(old('fixed_price', '0.00')); ?>" step="0.01" min="0" required class="w-full rounded-lg border border-gray-300/80 dark:border-gray-600 bg-gray-100 dark:bg-gray-900 px-3 py-2 text-sm text-ink-heading focus:bg-white dark:focus:bg-gray-800 focus:ring-1 focus:ring-[#5542ff] focus:border-[#5542ff] transition-colors" />
                    <?php $__errorArgs = ['fixed_price'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="text-red-500 text-xs mt-1"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-ink-body mb-1">Note</label>
                    <textarea name="note" rows="4" class="w-full rounded-lg border border-gray-300/80 dark:border-gray-600 bg-gray-100 dark:bg-gray-900 px-3 py-2 text-sm text-ink-heading placeholder:text-gray-400 dark:placeholder:text-gray-500 focus:bg-white dark:focus:bg-gray-800 focus:ring-1 focus:ring-[#5542ff] focus:border-[#5542ff] transition-colors resize-y min-h-[100px]" placeholder="Optional"><?php echo e(old('note')); ?></textarea>
                    <?php $__errorArgs = ['note'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="text-red-500 text-xs mt-1"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>

                
                <div class="md:col-span-2 pt-4">
                    <div class="flex items-center space-x-2 mb-4">
                        <h3 class="text-md font-semibold text-ink-body">Tracking Settings</h3>
                        <div class="h-px flex-1 bg-gray-200 dark:bg-gray-700"></div>
                    </div>

                    <div class="space-y-4">
                        
                        <label class="flex items-start gap-3 cursor-pointer group">
                            <div class="relative flex items-center pt-0.5">
                                <input type="hidden" name="has_expiry" value="0">
                                <input type="checkbox" name="has_expiry" value="1"
                                    x-model="hasExpiry"
                                    @change="if(hasExpiry) { requiresBatch = true; }"
                                    class="h-5 w-5 rounded border-gray-300 dark:border-gray-600 text-[#5542ff] focus:ring-[#5542ff] dark:bg-gray-900 transition-colors" />
                            </div>
                            <div>
                                <span class="text-sm font-semibold text-ink-body group-hover:text-[#5542ff] transition-colors">Has Expiry Date</span>
                                <p class="text-xs text-ink-muted mt-0.5">Enable if this product has an expiration date. Batch tracking will be automatically enabled.</p>
                            </div>
                        </label>

                        
                        <label class="flex items-start gap-3 cursor-pointer group">
                            <div class="relative flex items-center pt-0.5">
                                <input type="hidden" name="requires_batch_tracking" value="0">
                                <input type="checkbox" name="requires_batch_tracking" value="1"
                                    x-model="requiresBatch"
                                    :disabled="hasExpiry"
                                    class="h-5 w-5 rounded border-gray-300 dark:border-gray-600 text-[#5542ff] focus:ring-[#5542ff] dark:bg-gray-900 transition-colors disabled:opacity-50" />
                            </div>
                            <div>
                                <span class="text-sm font-semibold text-ink-body group-hover:text-[#5542ff] transition-colors">Batch Tracking</span>
                                <p class="text-xs text-ink-muted mt-0.5">
                                    Track inventory by batch/lot numbers.
                                    <template x-if="hasExpiry">
                                        <span class="text-[#5542ff] font-medium"> (Auto-enabled with expiry)</span>
                                    </template>
                                </p>
                            </div>
                        </label>

                        
                        <div x-show="hasExpiry" x-collapse class="ml-8 pl-4 border-l-2 border-[#5542ff]/20 dark:border-[#5542ff]/30 space-y-4 pt-2">
                            <div>
                                <label class="block text-sm font-medium text-ink-body mb-1">
                                    Shelf Life
                                    <span class="text-xs text-gray-400 font-normal">(days)</span>
                                </label>
                                <input type="number" name="shelf_life_days" value="<?php echo e(old('shelf_life_days')); ?>" min="1" placeholder="e.g. 365"
                                    class="w-full max-w-xs rounded-lg border border-gray-300/80 dark:border-gray-600 bg-gray-100 dark:bg-gray-900 px-3 py-2 text-sm text-ink-heading focus:bg-white dark:focus:bg-gray-800 focus:ring-1 focus:ring-[#5542ff] focus:border-[#5542ff] transition-colors" />
                                <p class="text-xs text-ink-muted mt-1">Optional. If set, expiry date auto-calculates from manufacture date.</p>
                                <?php $__errorArgs = ['shelf_life_days'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="text-red-500 text-xs mt-1"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-ink-body mb-1">
                                    Alert Before Expiry
                                    <span class="text-xs text-gray-400 font-normal">(days)</span>
                                </label>
                                <input type="number" name="expiry_alert_days" value="<?php echo e(old('expiry_alert_days', 30)); ?>" min="1" placeholder="30"
                                    class="w-full max-w-xs rounded-lg border border-gray-300/80 dark:border-gray-600 bg-gray-100 dark:bg-gray-900 px-3 py-2 text-sm text-ink-heading focus:bg-white dark:focus:bg-gray-800 focus:ring-1 focus:ring-[#5542ff] focus:border-[#5542ff] transition-colors" />
                                <p class="text-xs text-ink-muted mt-1">System will send notification this many days before the product expires.</p>
                                <?php $__errorArgs = ['expiry_alert_days'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="text-red-500 text-xs mt-1"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <button type="submit" class="w-full py-3 rounded-lg bg-[#5542ff] text-white font-semibold hover:bg-[#4736d6] transition-colors">
                Submit
            </button>
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
<?php /**PATH C:\laragon\www\GE-System-1.1\resources\views/products/create.blade.php ENDPATH**/ ?>