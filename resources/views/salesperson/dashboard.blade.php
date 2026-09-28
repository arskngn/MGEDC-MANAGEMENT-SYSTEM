<x-app-layout>
    <div x-data="salespersonDashboard({
        summary: {{ json_encode($summary) }},
        todaySales: {{ json_encode($todaySales ?? []) }}
    })" class="space-y-6 slide-up">
        <!-- Quick Actions -->
        <div class="grid grid-cols-2 gap-4">
            <a href="{{ route('sales.create') }}" class="bg-white dark:bg-gray-800 rounded-2xl p-6 card-shadow text-center hover:shadow-lg transition-all active:scale-95">
                <div class="w-12 h-12 gradient-bg rounded-full flex items-center justify-center mx-auto mb-3 shadow-md">
                    <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                </div>
                <h3 class="font-bold text-gray-800 dark:text-gray-100">New Sale</h3>
                <p class="text-xs text-ink-muted mt-1">Create a sale quickly</p>
            </a>
            
            <a href="{{ route('customers.index') }}" class="bg-white dark:bg-gray-800 rounded-2xl p-6 card-shadow text-center hover:shadow-lg transition-all active:scale-95">
                <div class="w-12 h-12 bg-green-100 dark:bg-green-900/30 rounded-full flex items-center justify-center mx-auto mb-3 shadow-xs">
                    <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                    </svg>
                </div>
                <h3 class="font-bold text-gray-800 dark:text-gray-100">Customers</h3>
                <p class="text-xs text-ink-muted mt-1">Manage customers</p>
            </a>
        </div>

        <!-- Overall Quota & Team Performance Card -->
        <div class="space-y-3">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <h2 class="font-bold text-gray-800 dark:text-gray-100 text-lg">Overall Quota & Commission</h2>
                </div>
                @if($isAchieved)
                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300 border border-emerald-300 dark:border-emerald-700">
                        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        Quota Achieved
                    </span>
                @else
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-amber-50 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300 border border-amber-200 dark:border-amber-800">
                        {{ $startDate->format('M d') }} – {{ $endDate->format('M d, Y') }}
                    </span>
                @endif
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 card-shadow space-y-4">
                <!-- Team Quota Overview Stats -->
                <div class="grid grid-cols-2 gap-3 pb-3 border-b border-gray-100 dark:border-gray-700/60">
                    <div class="p-3 bg-gray-50 dark:bg-gray-700/40 rounded-xl">
                        <p class="text-[11px] font-bold text-ink-muted uppercase tracking-wider">Overall Target</p>
                        <p class="text-base sm:text-lg font-extrabold text-[#0a1233] dark:text-white mt-0.5">
                            {{ formatCurrency($targetAmount) }}
                        </p>
                        <p class="text-[10px] text-ink-muted mt-0.5">
                            Team Target
                        </p>
                    </div>

                    <div class="p-3 bg-gray-50 dark:bg-gray-700/40 rounded-xl">
                        <p class="text-[11px] font-bold text-ink-muted uppercase tracking-wider">Team Sales</p>
                        <p class="text-base sm:text-lg font-extrabold text-blue-600 dark:text-blue-400 mt-0.5">
                            {{ formatCurrency($totalTeamSales) }}
                        </p>
                        <p class="text-[10px] text-ink-muted mt-0.5">
                            {{ $overallProgress }}% completed
                        </p>
                    </div>
                </div>

                <!-- Progress Bar -->
                <div>
                    <div class="flex justify-between items-center text-xs mb-1.5">
                        <span class="font-semibold text-ink-muted">Quota Progress</span>
                        <span class="font-bold text-[#0a1233] dark:text-white">{{ $overallProgress }}%</span>
                    </div>
                    <div class="w-full h-2.5 bg-gray-100 dark:bg-gray-700 rounded-full overflow-hidden">
                        <div class="h-full rounded-full transition-all duration-500 {{ $isAchieved ? 'bg-emerald-500' : 'bg-[#5542ff]' }}" style="width: {{ min($overallProgress, 100) }}%"></div>
                    </div>
                    @if(!$isAchieved && $remainingAmount > 0)
                        <p class="text-[11px] text-ink-muted text-right mt-1">
                            {{ formatCurrency($remainingAmount) }} needed to unlock ₱5,000 commission
                        </p>
                    @endif
                </div>

                <!-- My Contribution & Commission Highlight -->
                <div class="p-3.5 rounded-xl bg-purple-50/60 dark:bg-purple-950/20 border border-purple-100 dark:border-purple-800/40 space-y-2.5">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-purple-950 dark:text-purple-200">My Sales Contribution</span>
                        <span class="text-xs font-extrabold text-purple-700 dark:text-purple-300">
                            {{ formatCurrency($userPerf->actual_sales) }} <span class="text-[11px] font-normal text-purple-600/80">({{ $userPerf->contribution_pct }}% of team)</span>
                        </span>
                    </div>

                    <div class="flex items-center justify-between pt-2 border-t border-purple-100/80 dark:border-purple-800/40">
                        <div>
                            <span class="text-[10px] font-bold text-ink-muted uppercase tracking-wider block">Commission</span>
                            <span class="text-sm font-extrabold {{ $isAchieved ? 'text-emerald-600 dark:text-emerald-400' : 'text-gray-500 dark:text-gray-400' }}">
                                {{ formatCurrency($userPerf->commission_earned) }}
                            </span>
                        </div>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider {{ $isAchieved ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300 border border-emerald-200' : 'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-300 border border-amber-200' }}">
                            {{ $userPerf->commission_status }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Today's Stats Cards -->
        <div class="space-y-3">
            <h2 class="font-bold text-gray-800 dark:text-gray-100 text-lg">Today's Performance</h2>
            <div class="grid grid-cols-2 gap-3">
                <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 card-shadow">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs text-ink-muted">Sales Made</p>
                            <p class="text-2xl font-bold gradient-text mt-1" x-text="todaySales.count || 0">0</p>
                        </div>
                        <div class="w-10 h-10 bg-orange-100 dark:bg-orange-900/30 rounded-full flex items-center justify-center">
                            <svg class="w-5 h-5 text-orange-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                            </svg>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 card-shadow">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs text-ink-muted">Total Amount</p>
                            <p class="text-lg font-bold gradient-text mt-1" x-text="formatCurrency(todaySales.total_amount || 0)">$0.00</p>
                        </div>
                        <div class="w-10 h-10 bg-green-100 dark:bg-green-900/30 rounded-full flex items-center justify-center">
                            <svg class="w-5 h-5 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.347 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Access -->
        <div class="space-y-3">
            <div class="flex items-center justify-between">
                <h2 class="font-bold text-gray-800 dark:text-gray-100 text-lg">Quick Access</h2>
                <a href="{{ route('products.all') }}" class="text-orange-500 text-sm font-medium">View All</a>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-2xl card-shadow overflow-hidden">
                <a href="{{ route('products.all') }}" class="flex items-center space-x-4 px-5 py-4 border-b border-gray-100 dark:border-gray-700 last:border-0 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                    <div class="w-10 h-10 bg-blue-100 dark:bg-blue-900/30 rounded-full flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                        </svg>
                    </div>
                    <div class="flex-1">
                        <p class="font-medium text-gray-800 dark:text-gray-100">Browse Products</p>
                        <p class="text-xs text-ink-muted">View available stock</p>
                    </div>
                    <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </a>
                
                <a href="{{ route('sales.all') }}" class="flex items-center space-x-4 px-5 py-4 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                    <div class="w-10 h-10 bg-purple-100 dark:bg-purple-900/30 rounded-full flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5 text-purple-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                        </svg>
                    </div>
                    <div class="flex-1">
                        <p class="font-medium text-gray-800 dark:text-gray-100">My Sales History</p>
                        <p class="text-xs text-ink-muted">View past transactions</p>
                    </div>
                    <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </a>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function salespersonDashboard(initialData) {
            return {
                summary: initialData.summary,
                todaySales: initialData.todaySales || { count: 0, total_amount: 0 },
                settings: {
                    currency_symbol: '{{ $settings->currency_symbol ?? '$' }}',
                    currency: '{{ $settings->currency ?? 'USD' }}',
                    currency_format: '{{ $settings->currency_format ?? 'both' }}'
                },

                formatCurrency(amount) {
                    const formatted = new Intl.NumberFormat('en-US', {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    }).format(amount);

                    const { currency_symbol, currency, currency_format } = this.settings;

                    switch (currency_format) {
                        case 'symbol':
                            return currency_symbol + formatted;
                        case 'text':
                            return formatted + ' ' + currency;
                        case 'both':
                        default:
                            return currency_symbol + formatted + ' ' + currency;
                    }
                }
            }
        }
    </script>
    @endpush
</x-app-layout>
