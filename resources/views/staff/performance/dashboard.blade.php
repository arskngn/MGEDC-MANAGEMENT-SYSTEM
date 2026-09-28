<x-app-layout>
    <div class="space-y-8 p-4 sm:p-8">
        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg flex items-center justify-between" role="alert">
                <span>{{ session('success') }}</span>
                <button type="button" onclick="this.parentElement.remove()" class="text-green-800 hover:text-green-950 font-bold">&times;</button>
            </div>
        @endif

        @if($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg" role="alert">
                <ul class="list-disc list-inside text-sm">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Header Section with Quota Period Selector & Action Buttons -->
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h2 class="text-xl sm:text-2xl font-bold text-[#0a1233] dark:text-gray-100 flex items-center gap-2">
                    <span>Staff Performance Dashboard</span>
                    @if($isAchieved)
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300 border border-emerald-300 dark:border-emerald-700">
                            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            Quota Achieved
                        </span>
                    @endif
                </h2>
                <p class="text-sm text-ink-muted mt-0.5">
                    Overall team quota & individual salesperson contribution ({{ $startDate->format('M d') }} – {{ $endDate->format('M d, Y') }})
                </p>
            </div>

            <!-- Toolbar: Quota Selector + Action Buttons -->
            <div class="flex flex-wrap items-center gap-2.5">
                <!-- Quota Selector -->
                @if(isset($allQuotas) && $allQuotas->count() > 0)
                <form method="GET" action="{{ route('staff.performance.dashboard') }}" class="inline-flex items-center">
                    <div class="relative">
                        <select name="quota_id" onchange="this.form.submit()" 
                                class="text-xs font-semibold border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-900 pl-3 pr-8 py-2 text-gray-700 dark:text-gray-100 focus:ring-1 focus:ring-[#5542ff] focus:border-[#5542ff] appearance-none cursor-pointer shadow-xs">
                            @foreach($allQuotas as $q)
                                <option value="{{ $q->id }}" @selected($quotaId == $q->id)>
                                    {{ $q->name ?: 'Quota (' . $q->start_date->format('M Y') . ')' }}: {{ formatCurrency($q->target_amount) }}
                                </option>
                            @endforeach
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-2.5 text-ink-muted">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </div>
                    </div>
                </form>
                @endif

                <!-- Edit Current Quota Button -->
                @if($overallQuota)
                <button type="button" 
                        onclick="openEditQuotaModal({{ json_encode([
                            'id' => $overallQuota->id,
                            'name' => $overallQuota->name,
                            'target_amount' => (float)$overallQuota->target_amount,
                            'start_date' => $overallQuota->start_date->format('Y-m-d'),
                            'end_date' => $overallQuota->end_date->format('Y-m-d'),
                            'commission_per_staff' => (float)$overallQuota->commission_per_staff,
                            'notes' => $overallQuota->notes,
                        ]) }})"
                        class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-semibold rounded-lg bg-white hover:bg-gray-50 text-gray-700 border border-gray-300 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-200 dark:hover:bg-gray-750 transition-colors shadow-xs">
                    <svg class="w-4 h-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                    Edit Quota
                </button>
                @endif

                <!-- Add Overall Quota Button -->
                <button type="button" onclick="openCreateQuotaModal()"
                        class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-semibold rounded-lg bg-[#5542ff] hover:bg-[#4736d6] text-white shadow-xs transition-colors">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    {{ $overallQuota ? 'Add New Quota' : 'Set Overall Quota' }}
                </button>

                <!-- Print Report Button -->
                <a href="{{ route('staff.performance.export.pdf', array_filter(['quota_id' => $quotaId, 'print' => 1])) }}" target="_blank"
                   class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-semibold rounded-lg bg-red-50 hover:bg-red-100 text-red-700 border border-red-200 dark:bg-red-950/30 dark:border-red-900/40 dark:text-red-300 transition-colors shadow-xs">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                    </svg>
                    Print Report
                </a>
            </div>
        </div>

        <!-- Team Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Overall Target Quota -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-5">
                <div class="flex items-center justify-between">
                    <div class="flex-1">
                        <p class="text-xs text-ink-muted font-bold uppercase tracking-wider">Overall Target Quota</p>
                        <p class="text-2xl font-black text-[#0a1233] dark:text-white mt-1">{{ formatCurrency($targetAmount) }}</p>
                        <p class="text-[11px] text-ink-muted mt-1 truncate">
                            {{ $overallQuota?->name ?: ($startDate->format('M d') . ' – ' . $endDate->format('M d, Y')) }}
                        </p>
                    </div>
                    <div class="p-3 bg-purple-50 dark:bg-purple-900/30 rounded-xl flex-shrink-0">
                        <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Total Team Sales -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-5">
                <div class="flex items-center justify-between">
                    <div class="flex-1">
                        <p class="text-xs text-ink-muted font-bold uppercase tracking-wider">Total Team Sales</p>
                        <p class="text-2xl font-black text-[#0a1233] dark:text-white mt-1">{{ formatCurrency($totalSales) }}</p>
                        <p class="text-[11px] text-ink-muted mt-1">Net sales by {{ $staffPerformance->count() }} sales staff</p>
                    </div>
                    <div class="p-3 bg-blue-50 dark:bg-blue-900/30 rounded-xl flex-shrink-0">
                        <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Quota Progress -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-5">
                <div class="flex items-center justify-between mb-2">
                    <p class="text-xs text-ink-muted font-bold uppercase tracking-wider">Quota Progress</p>
                    <span class="text-xs font-extrabold {{ $isAchieved ? 'text-emerald-600 dark:text-emerald-400' : 'text-[#5542ff]' }}">{{ $progress }}%</span>
                </div>
                <div class="w-full h-2.5 bg-gray-100 dark:bg-gray-700 rounded-full overflow-hidden my-2">
                    <div class="h-full rounded-full transition-all duration-500 {{ $isAchieved ? 'bg-emerald-500' : 'bg-[#5542ff]' }}" 
                         style="width: {{ min($progress, 100) }}%"></div>
                </div>
                <div class="text-[11px] font-bold mt-2">
                    @if($isAchieved)
                        <span class="text-emerald-600 dark:text-emerald-400 flex items-center gap-1">
                            ✓ Target Achieved (+{{ formatCurrency($totalSales - $targetAmount) }})
                        </span>
                    @else
                        <span class="text-amber-600 dark:text-amber-400">
                            {{ formatCurrency($remainingAmount) }} needed to reach quota
                        </span>
                    @endif
                </div>
            </div>

            <!-- Commission Pool Status -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-5">
                <div class="flex items-center justify-between">
                    <div class="flex-1">
                        <p class="text-xs text-ink-muted font-bold uppercase tracking-wider">Commission Per Staff</p>
                        <p class="text-2xl font-black text-emerald-600 dark:text-emerald-400 mt-1">{{ formatCurrency($commissionPerStaff) }}</p>
                        <div class="text-[11px] font-bold mt-1">
                            @if($isAchieved)
                                <span class="text-emerald-600 dark:text-emerald-400">
                                    ✓ Distributed to all staff ({{ formatCurrency($totalCommissionPool) }})
                                </span>
                            @else
                                <span class="text-gray-400">
                                    Pending (Distributed when quota met)
                                </span>
                            @endif
                        </div>
                    </div>
                    <div class="p-3 {{ $isAchieved ? 'bg-emerald-50 dark:bg-emerald-900/30 text-emerald-600' : 'bg-gray-100 dark:bg-gray-700 text-gray-400' }} rounded-xl flex-shrink-0">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Top Contributors Leaderboard -->
        @if($topPerformers->count() > 0)
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
            <div class="p-5 border-b border-gray-50 dark:border-gray-700/50 bg-gray-50/50 dark:bg-gray-800/50 flex items-center justify-between">
                <h3 class="text-base font-bold text-[#0a1233] dark:text-white flex items-center gap-2">
                    <svg class="w-5 h-5 text-yellow-500" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                    </svg>
                    Top Sales Contributors
                </h3>
                <span class="text-xs text-ink-muted">Ranked by actual sales volume</span>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-4 p-5">
                @foreach($topPerformers as $index => $perf)
                <a href="{{ route('staff.performance.show', ['user' => $perf->user->id, 'quota_id' => $quotaId]) }}" 
                   class="relative flex flex-col items-center p-4 rounded-xl border transition-all hover:shadow-md group {{ $index === 0 ? 'bg-yellow-50/60 dark:bg-yellow-900/10 border-yellow-200 dark:border-yellow-700/50 hover:border-yellow-400' : 'bg-white dark:bg-gray-800 border-gray-100 dark:border-gray-700 hover:border-[#5542ff]/50' }}">
                    <div class="absolute -top-2.5 -left-2.5 w-7 h-7 rounded-full flex items-center justify-center font-black text-xs text-white shadow-sm {{ $index === 0 ? 'bg-yellow-500' : ($index === 1 ? 'bg-gray-400' : ($index === 2 ? 'bg-amber-600' : 'bg-blue-500')) }}">
                        {{ $index + 1 }}
                    </div>
                    <div class="h-14 w-14 rounded-full bg-[#5542ff] flex items-center justify-center overflow-hidden border-2 border-white dark:border-gray-700 shadow-sm mb-2.5" style="background: hsl({{ hexdec(substr(md5($perf->user->name), 0, 4)) % 360 }}, 65%, 45%) !important;">
                        @if($perf->user->image)
                            <img src="{{ asset($perf->user->image) }}" class="h-full w-full object-cover">
                        @else
                            <span class="text-lg font-extrabold text-white uppercase">
                                {{ collect(explode(' ', $perf->user->name))->map(fn($n) => strtoupper(substr($n, 0, 1)))->take(2)->implode('') }}
                            </span>
                        @endif
                    </div>
                    <div class="text-center w-full">
                        <div class="font-bold text-sm text-[#0a1233] dark:text-white truncate group-hover:text-[#5542ff] transition-colors">{{ $perf->user->name }}</div>
                        <div class="text-xs font-black text-[#5542ff] dark:text-indigo-400 mt-0.5">{{ formatCurrency($perf->actual_sales) }}</div>
                        <div class="text-[10px] font-bold text-ink-muted uppercase tracking-wider mt-1">{{ $perf->contribution_pct }}% of Team Sales</div>
                    </div>
                </a>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Detailed Salesperson Contribution Table -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
            <div class="p-5 border-b border-gray-100 dark:border-gray-700 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                <div>
                    <h3 class="font-bold text-base text-[#0a1233] dark:text-white">Salesperson Contributions & Commission</h3>
                    <p class="text-xs text-ink-muted">Tracking individual contribution towards the overall quota</p>
                </div>
                <div class="text-xs font-semibold text-ink-muted">
                    Total Team Sales: <strong class="text-[#0a1233] dark:text-white">{{ formatCurrency($totalSales) }}</strong>
                </div>
            </div>

            <div class="overflow-x-auto">
                @if($staffPerformance->count() > 0)
                <table class="w-full text-left border-collapse min-w-[700px]">
                    <thead>
                        <tr class="bg-gray-50 dark:bg-gray-700/50 text-[11px] uppercase tracking-wider text-gray-500 dark:text-gray-300 font-bold border-b border-gray-100 dark:border-gray-700">
                            <th class="px-6 py-3.5 text-center w-14">#</th>
                            <th class="px-6 py-3.5">Salesperson</th>
                            <th class="px-6 py-3.5 text-right">Contributed Sales</th>
                            <th class="px-6 py-3.5 text-center">Share of Team Sales</th>
                            <th class="px-6 py-3.5 text-center">Invoices</th>
                            <th class="px-6 py-3.5 text-right">Commission</th>
                            <th class="px-6 py-3.5 text-center">Status</th>
                            <th class="px-6 py-3.5 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700/50 text-sm">
                        @foreach($staffPerformance as $idx => $perf)
                        <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-700/30 transition-colors">
                            <td class="px-6 py-4 text-center font-bold text-gray-400 text-xs">
                                {{ $idx + 1 }}
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="h-9 w-9 rounded-full bg-[#5542ff] flex items-center justify-center overflow-hidden border border-gray-100 dark:border-gray-700 shadow-xs flex-none" style="background: hsl({{ hexdec(substr(md5($perf->user->name), 0, 4)) % 360 }}, 65%, 45%) !important;">
                                        @if($perf->user->image)
                                            <img src="{{ asset($perf->user->image) }}" class="h-full w-full object-cover">
                                        @else
                                            <span class="text-xs font-extrabold text-white uppercase">
                                                {{ collect(explode(' ', $perf->user->name))->map(fn($n) => strtoupper(substr($n, 0, 1)))->take(2)->implode('') }}
                                            </span>
                                        @endif
                                    </div>
                                    <div>
                                        <div class="font-bold text-[#0a1233] dark:text-white">{{ $perf->user->name }}</div>
                                        <div class="text-xs text-ink-muted">{{ $perf->user->email ?? 'No email' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-right font-black text-[#0a1233] dark:text-white">
                                {{ formatCurrency($perf->actual_sales) }}
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-center gap-2">
                                    <div class="w-24 h-2 bg-gray-100 dark:bg-gray-700 rounded-full overflow-hidden">
                                        <div class="h-full rounded-full bg-[#5542ff]" style="width: {{ min($perf->contribution_pct, 100) }}%"></div>
                                    </div>
                                    <span class="text-xs font-bold text-gray-700 dark:text-gray-300 w-12 text-right">{{ $perf->contribution_pct }}%</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-center font-bold text-gray-600 dark:text-gray-300">
                                {{ $perf->invoices_count }}
                            </td>
                            <td class="px-6 py-4 text-right font-black {{ $isAchieved ? 'text-emerald-600 dark:text-emerald-400' : 'text-gray-400' }}">
                                {{ formatCurrency($perf->commission_earned) }}
                            </td>
                            <td class="px-6 py-4 text-center">
                                @if($isAchieved)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                                        Awarded
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-50 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300 border border-amber-200 dark:border-amber-800">
                                        Pending Quota
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right">
                                <a href="{{ route('staff.performance.show', ['user' => $perf->user->id, 'quota_id' => $quotaId]) }}" 
                                   class="text-[#5542ff] hover:text-[#4736d6] dark:text-indigo-400 font-bold text-xs uppercase tracking-wider hover:underline">
                                    View
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="bg-gray-50/70 dark:bg-gray-800/70 font-bold text-sm border-t border-gray-200 dark:border-gray-700">
                            <td colspan="2" class="px-6 py-4 text-left font-black text-[#0a1233] dark:text-white">
                                Total Team Summary:
                            </td>
                            <td class="px-6 py-4 text-right font-black text-[#0a1233] dark:text-white">
                                {{ formatCurrency($totalSales) }}
                            </td>
                            <td class="px-6 py-4 text-center font-bold text-gray-600 dark:text-gray-300">
                                100%
                            </td>
                            <td class="px-6 py-4 text-center font-bold text-gray-600 dark:text-gray-300">
                                {{ $staffPerformance->sum('invoices_count') }}
                            </td>
                            <td class="px-6 py-4 text-right font-black {{ $isAchieved ? 'text-emerald-600 dark:text-emerald-400' : 'text-gray-400' }}">
                                {{ formatCurrency($totalCommissionPool) }}
                            </td>
                            <td colspan="2" class="px-6 py-4 text-center">
                                <span class="text-xs font-bold {{ $isAchieved ? 'text-emerald-600' : 'text-amber-600' }}">
                                    {{ $isAchieved ? 'Distributed (' . formatCurrency($commissionPerStaff) . ' each)' : 'Pending Quota' }}
                                </span>
                            </td>
                        </tr>
                    </tfoot>
                </table>
                @else
                <div class="p-12 text-center">
                    <h3 class="text-base font-bold text-gray-600 dark:text-gray-300 mb-1">No sales staff found</h3>
                    <p class="text-xs text-gray-500">Ensure active staff members have the role "salesperson" or "staff".</p>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Overall Quota Modal (Add & Edit) -->
    <div id="overallQuotaModal" class="fixed inset-0 z-50 hidden overflow-y-auto" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500/75 dark:bg-black/80 backdrop-blur-xs transition-opacity" onclick="closeOverallQuotaModal()"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
            <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-gray-100 dark:border-gray-700">
                <form id="overallQuotaForm" action="{{ route('staff.performance.overall-quota.store') }}" method="POST">
                    @csrf
                    <div class="p-6">
                        <div class="flex items-center justify-between pb-4 border-b border-gray-100 dark:border-gray-700 mb-5">
                            <div>
                                <h3 class="text-lg font-bold text-[#0a1233] dark:text-white" id="quotaModalTitle">Set Overall Team Quota</h3>
                                <p class="text-xs text-ink-muted mt-0.5">Shared sales goal for all salespersons</p>
                            </div>
                            <button type="button" onclick="closeOverallQuotaModal()" class="text-gray-400 hover:text-gray-600 text-xl font-bold p-1">&times;</button>
                        </div>

                        <div class="space-y-4">
                            <!-- Quota Name / Label -->
                            <div>
                                <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-1.5">Quota Title / Name</label>
                                <input type="text" name="name" id="modal_quota_name" placeholder="e.g. September 2026 Target"
                                       class="w-full text-sm border border-gray-300 dark:border-gray-600 rounded-lg px-3.5 py-2.5 bg-white dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-1 focus:ring-[#5542ff] focus:border-[#5542ff]">
                            </div>

                            <!-- Target Amount -->
                            <div>
                                <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-1.5">Overall Target Amount (₱) *</label>
                                <div class="relative">
                                    <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-gray-400 font-bold text-sm">₱</span>
                                    <input type="number" step="0.01" min="0" name="target_amount" id="modal_target_amount" required placeholder="500000.00"
                                           class="w-full text-sm font-bold pl-8 pr-3.5 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-1 focus:ring-[#5542ff] focus:border-[#5542ff]">
                                </div>
                            </div>

                            <!-- Date Range -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-1.5">Start Date *</label>
                                    <input type="date" name="start_date" id="modal_start_date" required
                                           onchange="document.getElementById('modal_end_date').min = this.value; if(document.getElementById('modal_end_date').value && document.getElementById('modal_end_date').value < this.value) { document.getElementById('modal_end_date').value = this.value; }"
                                           class="w-full text-sm border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 bg-white dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-1 focus:ring-[#5542ff]">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-1.5">End Date *</label>
                                    <input type="date" name="end_date" id="modal_end_date" required
                                           onchange="if(document.getElementById('modal_start_date').value && this.value < document.getElementById('modal_start_date').value) { this.value = document.getElementById('modal_start_date').value; }"
                                           class="w-full text-sm border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 bg-white dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-1 focus:ring-[#5542ff]">
                                </div>
                            </div>

                            <!-- Commission Per Staff -->
                            <div>
                                <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-1.5">Distributed Commission Per Salesperson (₱)</label>
                                <div class="relative">
                                    <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-gray-400 font-bold text-sm">₱</span>
                                    <input type="number" step="0.01" min="0" name="commission_per_staff" id="modal_commission_per_staff" value="5000.00"
                                           class="w-full text-sm font-bold pl-8 pr-3.5 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-1 focus:ring-[#5542ff]">
                                </div>
                                <p class="text-[11px] text-ink-muted mt-1">Every salesperson receives this amount when the overall quota target is reached.</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-gray-50 dark:bg-gray-900/50 px-6 py-4 flex items-center justify-end gap-3 border-t border-gray-100 dark:border-gray-700">
                        <button type="button" onclick="closeOverallQuotaModal()"
                                class="px-4 py-2 text-xs font-semibold text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-lg transition-colors">
                            Cancel
                        </button>
                        <button type="submit" id="quotaSubmitBtn"
                                class="px-4 py-2 text-xs font-bold text-white bg-[#5542ff] hover:bg-[#4736d6] rounded-lg transition-colors shadow-sm">
                            Save Overall Quota
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        const storeQuotaUrl = "{{ route('staff.performance.overall-quota.store') }}";

        function openCreateQuotaModal() {
            const modal = document.getElementById('overallQuotaModal');
            const form = document.getElementById('overallQuotaForm');
            form.action = storeQuotaUrl;

            document.getElementById('quotaModalTitle').innerText = 'Set New Overall Team Quota';
            document.getElementById('modal_quota_name').value = '';
            document.getElementById('modal_target_amount').value = '';
            document.getElementById('modal_start_date').value = "{{ now()->startOfMonth()->format('Y-m-d') }}";
            document.getElementById('modal_end_date').value = "{{ now()->endOfMonth()->format('Y-m-d') }}";
            document.getElementById('modal_commission_per_staff').value = '5000.00';
            document.getElementById('quotaSubmitBtn').innerText = 'Save Overall Quota';

            modal.classList.remove('hidden');
        }

        function openEditQuotaModal(quota) {
            const modal = document.getElementById('overallQuotaModal');
            const form = document.getElementById('overallQuotaForm');
            form.action = `/staff/performance/overall-quota/${quota.id}`;

            document.getElementById('quotaModalTitle').innerText = 'Edit Overall Team Quota';
            document.getElementById('modal_quota_name').value = quota.name || '';
            document.getElementById('modal_target_amount').value = quota.target_amount;
            document.getElementById('modal_start_date').value = quota.start_date;
            document.getElementById('modal_end_date').value = quota.end_date;
            document.getElementById('modal_commission_per_staff').value = quota.commission_per_staff || '5000.00';
            document.getElementById('quotaSubmitBtn').innerText = 'Update Quota';

            modal.classList.remove('hidden');
        }

        function closeOverallQuotaModal() {
            document.getElementById('overallQuotaModal').classList.add('hidden');
        }
    </script>
    @endpush
</x-app-layout>