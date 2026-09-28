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
    <div class="space-y-8 p-4 sm:p-8">
        <?php if(session('success')): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg flex items-center justify-between" role="alert">
                <span><?php echo e(session('success')); ?></span>
                <button type="button" onclick="this.parentElement.remove()" class="text-green-800 font-bold">&times;</button>
            </div>
        <?php endif; ?>

        <!-- Header Section -->
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-start gap-4">
                <a href="<?php echo e(route('staff.performance.dashboard', ['quota_id' => $quotaId])); ?>" class="p-2 bg-white dark:bg-gray-800 rounded-lg shadow-xs border border-gray-200 dark:border-gray-700 text-ink-muted hover:text-[#0a1233] dark:hover:text-white transition-colors flex-shrink-0 mt-1">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                </a>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-3 flex-wrap">
                        <h2 class="text-2xl sm:text-3xl font-bold text-[#0a1233] dark:text-white break-words"><?php echo e($user->name); ?>'s Performance</h2>
                        <?php if($isAchieved): ?>
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300 border border-emerald-300 dark:border-emerald-700">
                                <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                Quota Achieved
                            </span>
                        <?php endif; ?>
                    </div>
                    <p class="text-sm text-ink-muted mt-1">
                        Overall Team Quota Contribution (<?php echo e($startDate->format('M d')); ?> – <?php echo e($endDate->format('M d, Y')); ?>)
                    </p>
                </div>
            </div>

            <!-- Quota Period Selector -->
            <?php if(isset($allQuotas) && $allQuotas->count() > 0): ?>
            <div class="flex items-center gap-2 self-start sm:self-auto">
                <form method="GET" action="<?php echo e(route('staff.performance.show', $user->id)); ?>">
                    <input type="hidden" name="sales_year" value="<?php echo e($salesYear); ?>">
                    <div class="relative">
                        <select name="quota_id" onchange="this.form.submit()" 
                                class="text-xs font-semibold border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-900 pl-3 pr-8 py-2 text-gray-700 dark:text-gray-100 focus:ring-1 focus:ring-[#5542ff] focus:border-[#5542ff] appearance-none cursor-pointer shadow-xs">
                            <?php $__currentLoopData = $allQuotas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $q): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($q->id); ?>" <?php if($quotaId == $q->id): echo 'selected'; endif; ?>>
                                    <?php echo e($q->name ?: 'Quota (' . $q->start_date->format('M Y') . ')'); ?>: <?php echo e(formatCurrency($q->target_amount)); ?>

                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-2.5 text-ink-muted">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </div>
                    </div>
                </form>
            </div>
            <?php endif; ?>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Left Column: User Profile & Status Card -->
            <div class="space-y-6">
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-6 flex flex-col items-center text-center">
                    <div class="h-20 w-20 rounded-full bg-[#5542ff] flex items-center justify-center overflow-hidden border-4 border-gray-50 dark:border-gray-700 shadow-md mb-3" style="background: hsl(<?php echo e(hexdec(substr(md5($user->name), 0, 4)) % 360); ?>, 65%, 45%) !important;">
                        <?php if($user->image): ?>
                            <img src="<?php echo e(asset($user->image)); ?>" class="h-full w-full object-cover">
                        <?php else: ?>
                            <span class="text-2xl font-extrabold text-white uppercase">
                                <?php echo e(collect(explode(' ', $user->name))->map(fn($n) => strtoupper(substr($n, 0, 1)))->take(2)->implode('')); ?>

                            </span>
                        <?php endif; ?>
                    </div>
                    <h3 class="text-lg font-bold text-[#0a1233] dark:text-white"><?php echo e($user->name); ?></h3>
                    <p class="text-xs text-ink-muted capitalize"><?php echo e($user->role->name ?? 'Salesperson'); ?></p>
                    <div class="mt-2 flex items-center gap-1.5">
                        <span class="w-2.5 h-2.5 rounded-full <?php echo e($user->isOnline() ? 'bg-green-500' : 'bg-gray-300'); ?>"></span>
                        <span class="text-[11px] font-semibold text-ink-muted uppercase tracking-wider"><?php echo e($user->isOnline() ? 'Online' : 'Offline'); ?></span>
                    </div>

                    <!-- Compact Team Ranking -->
                    <div class="w-full mt-5 pt-5 border-t border-gray-100 dark:border-gray-700 space-y-3">
                        <div class="flex justify-between items-center text-xs">
                            <span class="text-ink-muted font-medium">Team Sales Rank</span>
                            <span class="font-bold text-[#0a1233] dark:text-white">#<?php echo e($teamRank); ?> <span class="text-gray-400 font-normal">/ <?php echo e($totalStaff); ?></span></span>
                        </div>
                        <div class="w-full bg-gray-100 dark:bg-gray-700 rounded-full h-1.5 overflow-hidden">
                            <div class="bg-blue-600 h-full rounded-full" style="width: <?php echo e($totalStaff > 0 ? (($totalStaff - $teamRank + 1) / $totalStaff) * 100 : 0); ?>%"></div>
                        </div>
                        <div class="grid grid-cols-2 gap-2 text-center pt-1">
                            <div class="p-2.5 bg-gray-50 dark:bg-gray-700/40 rounded-lg">
                                <span class="text-[10px] font-bold text-gray-400 uppercase block">Share of Team</span>
                                <span class="text-sm font-extrabold text-[#0a1233] dark:text-white mt-0.5 block"><?php echo e($userPerf->contribution_pct); ?>%</span>
                            </div>
                            <div class="p-2.5 bg-gray-50 dark:bg-gray-700/40 rounded-lg">
                                <span class="text-[10px] font-bold text-gray-400 uppercase block">Invoices</span>
                                <span class="text-sm font-extrabold text-[#0a1233] dark:text-white mt-0.5 block"><?php echo e($userPerf->invoices_count); ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Commission Status Card -->
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="p-2.5 bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 rounded-xl">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div>
                            <h4 class="font-bold text-[#0a1233] dark:text-white text-sm">Commission</h4>
                            <p class="text-[11px] text-ink-muted">Distributed equally upon overall quota achievement</p>
                        </div>
                    </div>

                    <div class="p-4 rounded-xl <?php echo e($isAchieved ? 'bg-emerald-50 dark:bg-emerald-950/20 border border-emerald-200 dark:border-emerald-800' : 'bg-gray-50 dark:bg-gray-700/30 border border-gray-100 dark:border-gray-700'); ?> text-center">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-gray-400 block">Commission Earned</span>
                        <span class="text-2xl font-black <?php echo e($isAchieved ? 'text-emerald-600 dark:text-emerald-400' : 'text-gray-400'); ?> mt-1 block">
                            <?php echo e(formatCurrency($userPerf->commission_earned)); ?>

                        </span>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold uppercase tracking-wider mt-2.5 <?php echo e($isAchieved ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300' : 'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-300'); ?>">
                            <?php echo e($userPerf->commission_status); ?>

                        </span>
                    </div>
                </div>
            </div>

            <!-- Right Column: Overall Quota Context & Salesperson Breakdown -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Overall Quota Status Card -->
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                    <div class="p-5 sm:p-6 border-b border-gray-100 dark:border-gray-700/80 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 bg-gray-50/40 dark:bg-gray-800/40">
                        <div class="flex items-center gap-3">
                            <div class="p-2.5 bg-purple-50 dark:bg-purple-900/40 rounded-xl text-purple-600 dark:text-purple-300">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="font-bold text-[#0a1233] dark:text-white text-base">Overall Team Quota Status</h3>
                                <p class="text-xs text-ink-muted">Coverage: <?php echo e($startDate->format('M d')); ?> &ndash; <?php echo e($endDate->format('M d, Y')); ?></p>
                            </div>
                        </div>

                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider border <?php echo e($isAchieved ? 'bg-emerald-100 text-emerald-700 border-emerald-200 dark:bg-emerald-900/30 dark:text-emerald-300 dark:border-emerald-800' : 'bg-blue-100 text-blue-700 border-blue-200 dark:bg-blue-900/30 dark:text-blue-300 dark:border-blue-800'); ?>">
                            <?php echo e($isAchieved ? 'Quota Met' : $overallProgress . '% Completed'); ?>

                        </span>
                    </div>

                    <div class="p-5 sm:p-6 space-y-5">
                        <!-- Key Stats in 3 columns -->
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div class="p-4 bg-gray-50/70 dark:bg-gray-700/30 rounded-xl border border-gray-100 dark:border-gray-700">
                                <span class="text-ink-muted block text-[11px] font-bold uppercase tracking-wider">Overall Target Quota</span>
                                <span class="font-extrabold text-[#0a1233] dark:text-white text-xl mt-1 block"><?php echo e(formatCurrency($targetAmount)); ?></span>
                            </div>
                            <div class="p-4 bg-gray-50/70 dark:bg-gray-700/30 rounded-xl border border-gray-100 dark:border-gray-700">
                                <span class="text-ink-muted block text-[11px] font-bold uppercase tracking-wider">Total Team Sales</span>
                                <span class="font-extrabold text-blue-600 dark:text-blue-400 text-xl mt-1 block"><?php echo e(formatCurrency($totalSales)); ?></span>
                            </div>
                            <div class="p-4 bg-gray-50/70 dark:bg-gray-700/30 rounded-xl border border-gray-100 dark:border-gray-700">
                                <span class="text-ink-muted block text-[11px] font-bold uppercase tracking-wider">Salesperson's Contribution</span>
                                <span class="font-extrabold text-[#5542ff] dark:text-indigo-400 text-xl mt-1 block"><?php echo e(formatCurrency($userPerf->actual_sales)); ?></span>
                            </div>
                        </div>

                        <!-- Overall Quota Progress Bar -->
                        <div class="space-y-1.5 pt-1">
                            <div class="flex justify-between text-xs font-semibold">
                                <span class="text-ink-muted">Team Quota Progress</span>
                                <span class="text-[#0a1233] dark:text-white font-bold"><?php echo e($overallProgress); ?>% (<?php echo e(formatCurrency($totalSales)); ?> of <?php echo e(formatCurrency($targetAmount)); ?>)</span>
                            </div>
                            <div class="w-full h-3 bg-gray-100 dark:bg-gray-700 rounded-full overflow-hidden">
                                <div class="h-full rounded-full transition-all duration-500 <?php echo e($isAchieved ? 'bg-emerald-500' : 'bg-[#5542ff]'); ?>" style="width: <?php echo e(min($overallProgress, 100)); ?>%"></div>
                            </div>
                            <?php if(!$isAchieved && $remainingAmount > 0): ?>
                                <p class="text-[11px] text-ink-muted text-right">
                                    <?php echo e(formatCurrency($remainingAmount)); ?> needed for the team to achieve quota and unlock commission
                                </p>
                            <?php endif; ?>
                        </div>

                        <!-- Individual Contribution Share Breakdown -->
                        <div class="p-4 rounded-xl bg-purple-50/50 dark:bg-purple-950/20 border border-purple-100 dark:border-purple-800/40">
                            <div class="flex items-center justify-between text-xs font-bold mb-2">
                                <span class="text-purple-950 dark:text-purple-200"><?php echo e($user->name); ?>'s Share of Team Sales</span>
                                <span class="text-purple-700 dark:text-purple-300 font-extrabold text-sm"><?php echo e($userPerf->contribution_pct); ?>%</span>
                            </div>
                            <div class="w-full h-2.5 bg-purple-100 dark:bg-purple-900/50 rounded-full overflow-hidden">
                                <div class="h-full bg-purple-600 rounded-full transition-all duration-500" style="width: <?php echo e(min($userPerf->contribution_pct, 100)); ?>%"></div>
                            </div>
                            <div class="flex justify-between items-center text-[11px] text-ink-muted mt-2">
                                <span>Invoiced Sales: <strong><?php echo e(formatCurrency($userPerf->actual_sales)); ?></strong></span>
                                <span>Share of Target Quota: <strong><?php echo e($userPerf->quota_share_pct); ?>%</strong></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Annual Sales History Overview -->
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                    <div class="p-5 sm:p-6 border-b border-gray-50 dark:border-gray-700 flex flex-col sm:flex-row sm:justify-between sm:items-center gap-3">
                        <div>
                            <h3 class="font-bold text-[#0a1233] dark:text-white text-base">Annual Sales Overview</h3>
                            <p class="text-xs text-ink-muted mt-0.5">Monthly sales volume for year <?php echo e($salesYear); ?></p>
                        </div>
                        <div class="relative w-full sm:w-auto">
                            <select onchange="window.location.href='<?php echo e(route('staff.performance.show', $user->id)); ?>?sales_year=' + this.value + '&quota_id=<?php echo e($quotaId ?? ''); ?>'" 
                                    class="w-full sm:w-auto text-xs font-semibold border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-900 pl-3 pr-8 py-1.5 text-gray-700 dark:text-gray-100 focus:ring-1 focus:ring-[#5542ff] focus:border-[#5542ff] appearance-none cursor-pointer shadow-2xs">
                                <?php $__currentLoopData = $availableYears; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $year): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($year); ?>" <?php echo e($year == $salesYear ? 'selected' : ''); ?>><?php echo e($year); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-2.5 text-ink-muted">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </div>
                        </div>
                    </div>
                    <div class="p-5 sm:p-6">
                        <div class="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-6 gap-3">
                            <?php $__currentLoopData = range(1, 12); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $month): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php
                                    $monthData = $salesHistory->firstWhere('month', $month);
                                    $total = $monthData ? (float)$monthData->total : 0;
                                    $monthName = DateTime::createFromFormat('!m', $month)->format('M');
                                ?>
                                <div class="flex flex-col items-center p-3 rounded-lg border <?php echo e($total > 0 ? 'border-indigo-100 bg-indigo-50/30 dark:border-indigo-800 dark:bg-indigo-900/20' : 'border-gray-100 bg-gray-50/30 dark:border-gray-700 dark:bg-gray-800/30'); ?>">
                                    <span class="text-[10px] font-bold text-gray-400 uppercase"><?php echo e($monthName); ?></span>
                                    <span class="text-xs font-bold text-[#0a1233] dark:text-white mt-1"><?php echo e(formatCurrency($total)); ?></span>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
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
<?php endif; ?><?php /**PATH C:\laragon\www\GE-System-1.1\resources\views/staff/performance/show.blade.php ENDPATH**/ ?>