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
        $expenseTypesBase = rtrim(url('/expense_types'), '/');
    ?>
    <div
        x-data="{
            addOpen: <?php echo e($errors->any() && old('_form') === 'add' ? 'true' : 'false'); ?>,
            editOpen: <?php echo e($errors->any() && old('_form') === 'edit' ? 'true' : 'false'); ?>,
            importOpen: <?php echo e($errors->has('csv_file') || ($errors->any() && old('_form') === 'import') ? 'true' : 'false'); ?>,
            deleteOpen: false,
            deleteId: null,
            deleteName: '',
            editId: <?php echo e(old('expense_type_id') ? (int) old('expense_type_id') : 'null'); ?>,
            editName: <?php echo \Illuminate\Support\Js::from(old('name', ''))->toHtml() ?>,
            openEdit(id, name) {
                this.editId = id;
                this.editName = name;
                this.editOpen = true;
            },
            openDelete(id, name) {
                this.deleteId = id;
                this.deleteName = name;
                this.deleteOpen = true;
            },
            confirmDelete() {
                const form = document.getElementById(`delete-form-${this.deleteId}`);
                if (form) {
                    form.submit();
                }
                this.deleteOpen = false;
            }
        }"
        @keydown.escape.window="addOpen = false; editOpen = false; importOpen = false; deleteOpen = false"
        class="space-y-4"
    >
        <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
            <h2 class="text-xl font-bold text-[#0a1233] shrink-0">Expense Types</h2>

            <div class="flex flex-wrap items-center gap-2 lg:gap-2 lg:ml-auto lg:justify-end">
                <form method="GET" action="<?php echo e(route('expense_types.index')); ?>" class="inline-flex flex-wrap items-center gap-2">
                    <div class="relative flex w-full sm:w-[280px] shrink-0">
                        <input
                            type="text"
                            name="search"
                            value="<?php echo e(request('search')); ?>"
                            placeholder="Search..."
                            class="w-full pl-4 pr-10 py-2 border border-[#5542ff]/40 rounded-l-md focus:outline-none focus:ring-1 focus:ring-[#5542ff] focus:border-[#5542ff] text-sm bg-white"
                            title="Search by expense type name"
                        />
                        <button type="submit" class="px-3 gradient-bg text-white rounded-r-md hover:bg-opacity-90 transition-colors shrink-0" title="Search">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </button>
                    </div>
                    <?php if(request()->filled('search')): ?>
                        <a href="<?php echo e(route('expense_types.index')); ?>" class="text-sm text-gray-500 hover:text-[#5542ff] px-2">Clear</a>
                    <?php endif; ?>
                </form>

                <div class="flex flex-wrap items-center gap-2 shrink-0">
                    <?php if(auth()->user()->hasPermission('Store Expense Type')): ?>
                        <button type="button" @click="addOpen = true" class="inline-flex items-center px-4 py-2 bg-white border border-[#5542ff] text-[#5542ff] rounded-md hover:bg-[#5542ff]/5 transition-colors text-sm font-semibold">
                            <svg class="h-4 w-4 mr-1.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            Add New
                        </button>
                    <?php endif; ?>


                </div>
            </div>
        </div>

        <?php if(session('success')): ?>
            <div class="rounded-lg bg-green-50 border border-green-200 text-green-800 px-4 py-3 text-sm"><?php echo e(session('success')); ?></div>
        <?php endif; ?>
        <?php if(session('error')): ?>
            <div class="rounded-lg bg-red-50 border border-red-200 text-red-800 px-4 py-3 text-sm"><?php echo e(session('error')); ?></div>
        <?php endif; ?>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full border-collapse min-w-[640px]">
                    <thead>
                        <tr class="gradient-bg text-white">
                            <th class="px-4 py-4 text-[11px] font-bold uppercase tracking-wide text-left">S.N.</th>
                            <th class="px-4 py-4 text-[11px] font-bold uppercase tracking-wide text-left">Name</th>
                            <th class="px-4 py-4 text-[11px] font-bold uppercase tracking-wide text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <?php $__empty_1 = true; $__currentLoopData = $expenseTypes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $expenseType): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <?php
                                $sn = ($expenseTypes->currentPage() - 1) * $expenseTypes->perPage() + $loop->iteration;
                            ?>
                            <tr class="hover:bg-gray-50/90 transition-colors bg-white">
                                <td class="px-4 py-4 text-sm text-gray-800 align-middle"><?php echo e($sn); ?></td>
                                <td class="px-4 py-4 align-middle text-left">
                                    <span class="text-sm font-semibold text-gray-900"><?php echo e($expenseType->name); ?></span>
                                </td>
                                <td class="px-4 py-4 align-middle text-right">
                                    <div class="flex flex-wrap items-center justify-end gap-2">
                                        <?php if(auth()->user()->hasPermission('Store Expense Type')): ?>
                                            <button
                                                type="button"
                                                @click="openEdit(<?php echo e($expenseType->id); ?>, <?php echo \Illuminate\Support\Js::from($expenseType->name)->toHtml() ?>)"
                                                class="inline-flex items-center justify-center px-2.5 py-1.5 bg-white border border-[#5542ff] text-[#5542ff] rounded-md hover:bg-[#5542ff]/5 text-[11px] font-bold transition-colors"
                                            >
                                                <svg class="h-3.5 w-3.5 mr-1 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                </svg>
                                                Edit
                                            </button>
                                        <?php endif; ?>
                                        <?php if(auth()->user()->hasPermission('Delete Expense Type')): ?>
                                            <?php if($expenseType->expenses_count > 0): ?>
                                                <span
                                                    class="inline-flex items-center justify-center px-2.5 py-1.5 bg-white border border-rose-300 text-rose-400 rounded-md text-[11px] font-bold opacity-50 cursor-not-allowed select-none"
                                                    title="Cannot delete an expense type that still has expenses assigned."
                                                    role="presentation"
                                                >
                                                    <svg class="h-3.5 w-3.5 mr-1 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                    </svg>
                                                    Delete
                                                </span>
                                            <?php else: ?>
                                                <form
                                                    id="delete-form-<?php echo e($expenseType->id); ?>"
                                                    method="POST"
                                                    action="<?php echo e(route('expense_types.destroy', $expenseType)); ?>"
                                                    class="inline"
                                                >
                                                    <?php echo csrf_field(); ?>
                                                    <?php echo method_field('DELETE'); ?>
                                                    <button
                                                        type="button"
                                                        @click="openDelete(<?php echo e($expenseType->id); ?>, <?php echo \Illuminate\Support\Js::from($expenseType->name)->toHtml() ?>)"
                                                        class="inline-flex items-center justify-center px-2.5 py-1.5 bg-white border border-rose-500 text-rose-600 rounded-md hover:bg-rose-50 text-[11px] font-bold transition-colors"
                                                    >
                                                        <svg class="h-3.5 w-3.5 mr-1 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                        </svg>
                                                        Delete
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="3" class="px-6 py-12 text-center text-gray-500">No expense types found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <?php if($expenseTypes->hasPages()): ?>
                <div class="px-4 py-3 bg-gray-50 border-t border-gray-100">
                    <?php echo e($expenseTypes->links()); ?>

                </div>
            <?php endif; ?>
        </div>

        
        <div x-show="addOpen" x-cloak class="fixed inset-0 z-[210] overflow-y-auto" style="display: none;">
            <div class="flex items-center justify-center min-h-screen px-4 py-8">
                <div class="fixed inset-0 bg-gray-900/50 z-0" @click="addOpen = false"></div>
                <div class="relative bg-white rounded-xl shadow-xl max-w-md w-full z-10 border border-gray-100" @click.stop>
                    <div class="flex justify-between items-center px-6 pt-5 pb-3 border-b border-gray-100">
                        <h3 class="text-lg font-bold text-[#0a1233]">Add New Expense Type</h3>
                        <button type="button" @click="addOpen = false" class="text-gray-400 hover:text-gray-600 p-1" aria-label="Close">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                        </button>
                    </div>
                    <form method="POST" action="<?php echo e(route('expense_types.store')); ?>" class="px-6 py-4 space-y-4">
                        <?php echo csrf_field(); ?>
                        <input type="hidden" name="_form" value="add" />
                        <div>
                            <label for="add_name" class="block text-sm font-medium text-gray-700 mb-1">Name <span class="text-red-500">*</span></label>
                            <input
                                id="add_name"
                                type="text"
                                name="name"
                                value="<?php echo e(old('_form') === 'add' ? old('name') : ''); ?>"
                                required
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-1 focus:ring-[#5542ff] focus:border-[#5542ff] text-sm"
                            />
                            <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <p class="mt-1 text-sm text-red-600"><?php echo e($message); ?></p>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                        <div class="pt-2 border-t border-gray-100 pb-1">
                            <button type="submit" class="w-full py-2.5 rounded-lg bg-[#5542ff] text-white text-sm font-semibold hover:bg-[#4736d6] transition-colors">Submit</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        
        <div x-show="editOpen" x-cloak class="fixed inset-0 z-[210] overflow-y-auto" style="display: none;">
            <div class="flex items-center justify-center min-h-screen px-4 py-8">
                <div class="fixed inset-0 bg-gray-900/50 z-0" @click="editOpen = false"></div>
                <div class="relative bg-white rounded-xl shadow-xl max-w-md w-full z-10 border border-gray-100" @click.stop>
                    <div class="flex justify-between items-center px-6 pt-5 pb-3 border-b border-gray-100">
                        <h3 class="text-lg font-bold text-[#0a1233]">Edit Expense Type</h3>
                        <button type="button" @click="editOpen = false" class="text-gray-400 hover:text-gray-600 p-1" aria-label="Close">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                        </button>
                    </div>
                    <form method="POST" :action="`<?php echo e($expenseTypesBase); ?>/${editId}`" class="px-6 py-4 space-y-4">
                        <?php echo csrf_field(); ?>
                        <?php echo method_field('PUT'); ?>
                        <input type="hidden" name="_form" value="edit" />
                        <input type="hidden" name="expense_type_id" x-bind:value="editId" />
                        <div>
                            <label for="edit_name" class="block text-sm font-medium text-gray-700 mb-1">Name <span class="text-red-500">*</span></label>
                            <input
                                id="edit_name"
                                type="text"
                                name="name"
                                x-model="editName"
                                required
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-1 focus:ring-[#5542ff] focus:border-[#5542ff] text-sm"
                            />
                            <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <p class="mt-1 text-sm text-red-600"><?php echo e($message); ?></p>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                        <div class="pt-2 border-t border-gray-100 pb-1">
                            <button type="submit" class="w-full py-2.5 rounded-lg bg-[#5542ff] text-white text-sm font-semibold hover:bg-[#4736d6] transition-colors">Submit</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>



        
        <div x-show="deleteOpen" x-cloak class="fixed inset-0 z-[210] overflow-y-auto" style="display: none;">
            <div class="flex items-center justify-center min-h-screen px-4 py-8">
                <div class="fixed inset-0 bg-gray-900/50 z-0" @click="deleteOpen = false"></div>
                <div class="relative bg-white rounded-lg shadow-xl max-w-sm w-full z-10" @click.stop>
                    <div class="px-6 pt-6 pb-4">
                        <h3 class="text-base font-semibold text-gray-900">Confirmation Alert!</h3>
                    </div>
                    <div class="px-6 pb-6">
                        <p class="text-sm text-gray-600">Are you sure to delete this expense type?</p>
                    </div>
                    <div class="px-6 py-4 flex justify-end gap-3 border-t border-gray-200">
                        <button 
                            type="button" 
                            @click="deleteOpen = false" 
                            class="px-6 py-2 rounded-sm bg-slate-900 text-white text-sm font-medium hover:bg-slate-800 transition-colors"
                        >
                            No
                        </button>
                        <button 
                            type="button" 
                            @click="confirmDelete()" 
                            class="px-6 py-2 rounded-sm bg-[#5542ff] text-white text-sm font-medium hover:bg-[#4736d6] transition-colors"
                        >
                            Yes
                        </button>
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
<?php /**PATH C:\laragon\www\GE-System-1.1\resources\views/expense_types/index.blade.php ENDPATH**/ ?>