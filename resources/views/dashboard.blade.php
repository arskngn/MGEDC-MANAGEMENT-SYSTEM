<x-app-layout>
    <div x-data="dashboard({
        summary: {{ json_encode($summary) }},
        transactions: {{ json_encode($transactions) }},
        allProducts: {{ json_encode($allProducts ?? []) }},
        categoryAnalytics: {{ json_encode($categoryAnalytics ?? []) }},
        settings: {
            currency_symbol: '{{ $settings->currency_symbol ?? '$' }}',
            currency: '{{ $settings->currency ?? 'USD' }}',
            currency_format: '{{ $settings->currency_format ?? 'both' }}'
        }
    })" class="space-y-4">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 pb-2 border-b border-gray-100 dark:border-gray-700/60">
            <div>
                <h2 class="text-2xl font-extrabold text-[#0a1233] dark:text-gray-100 tracking-tight">System Dashboard</h2>
                <p class="text-xs text-ink-muted mt-0.5">Overview of real-time inventory levels, sales activity, and performance metrics.</p>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <!-- Total Products -->
            <a href="{{ route('products.all') }}" class="bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700/50 rounded-2xl flex items-center p-6 hover:shadow-lg hover:-translate-y-0.5 transition-all duration-300 group bg-gradient-to-br from-blue-50/20 to-white dark:from-gray-800/10 dark:to-gray-800">
                <div class="flex-shrink-0 border border-blue-100 dark:border-blue-900/30 rounded-xl p-3 bg-blue-50/50 dark:bg-blue-950/20">
                    <svg class="h-6 w-6 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0a2 2 0 012 2v4a2 2 0 01-2 2H6a2 2 0 01-2-2v-4a2 2 0 012-2m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                    </svg>
                </div>
                <div class="ml-5 flex-1">
                    <div class="text-xs font-bold text-ink-muted uppercase tracking-wider">Total Products</div>
                    <div class="text-2xl font-black text-[#0a1233] dark:text-gray-100 mt-1" x-text="summary.total_products"></div>
                </div>
                <div class="ml-auto">
                    <svg class="h-5 w-5 text-gray-300 group-hover:text-blue-500 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </div>
            </a>

            <!-- Total Customers -->
            <a href="{{ route('customers.index') }}" class="bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700/50 rounded-2xl flex items-center p-6 hover:shadow-lg hover:-translate-y-0.5 transition-all duration-300 group bg-gradient-to-br from-green-50/20 to-white dark:from-gray-800/10 dark:to-gray-800">
                <div class="flex-shrink-0 border border-green-100 dark:border-green-900/30 rounded-xl p-3 bg-green-50/50 dark:bg-green-950/20">
                    <svg class="h-6 w-6 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                </div>
                <div class="ml-5 flex-1">
                    <div class="text-xs font-bold text-ink-muted uppercase tracking-wider">Total Customers</div>
                    <div class="text-2xl font-black text-[#0a1233] dark:text-gray-100 mt-1" x-text="summary.total_customers"></div>
                </div>
                <div class="ml-auto">
                    <svg class="h-5 w-5 text-gray-300 group-hover:text-green-500 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </div>
            </a>

            <!-- Total Suppliers -->
            <a href="{{ route('suppliers.index') }}" class="bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700/50 rounded-2xl flex items-center p-6 hover:shadow-lg hover:-translate-y-0.5 transition-all duration-300 group bg-gradient-to-br from-purple-50/20 to-white dark:from-gray-800/10 dark:to-gray-800">
                <div class="flex-shrink-0 border border-purple-100 dark:border-purple-900/30 rounded-xl p-3 bg-purple-50/50 dark:bg-purple-950/20">
                    <svg class="h-6 w-6 text-purple-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                </div>
                <div class="ml-5 flex-1">
                    <div class="text-xs font-bold text-ink-muted uppercase tracking-wider">Total Suppliers</div>
                    <div class="text-2xl font-black text-[#0a1233] dark:text-gray-100 mt-1" x-text="summary.total_suppliers"></div>
                </div>
                <div class="ml-auto">
                    <svg class="h-5 w-5 text-gray-300 group-hover:text-purple-500 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </div>
            </a>

            <!-- Total Categories -->
            <a href="{{ route('categories.index') }}" class="bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700/50 rounded-2xl flex items-center p-6 hover:shadow-lg hover:-translate-y-0.5 transition-all duration-300 group bg-gradient-to-br from-orange-50/20 to-white dark:from-gray-800/10 dark:to-gray-800">
                <div class="flex-shrink-0 border border-orange-100 dark:border-orange-900/30 rounded-xl p-3 bg-orange-50/50 dark:bg-orange-950/20">
                    <svg class="h-6 w-6 text-orange-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                    </svg>
                </div>
                <div class="ml-5 flex-1">
                    <div class="text-xs font-bold text-ink-muted uppercase tracking-wider">Total Categories</div>
                    <div class="text-2xl font-black text-[#0a1233] dark:text-gray-100 mt-1" x-text="summary.total_categories"></div>
                </div>
                <div class="ml-auto">
                    <svg class="h-5 w-5 text-gray-300 group-hover:text-orange-500 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </div>
            </a>
        </div>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
            <!-- Sales Card -->
            <div class="bg-white dark:bg-gray-800 shadow-sm border border-gray-100 dark:border-gray-700 rounded-2xl p-6 flex flex-col justify-between">
                <div>
                    <h3 class="text-base font-extrabold text-gray-800 dark:text-gray-100 mb-5 pb-3 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                        <span>Sales Performance</span>
                        <span class="text-xs font-semibold px-2 py-0.5 rounded-full bg-green-50 dark:bg-green-950/30 text-green-700 dark:text-green-400">Live Tracker</span>
                    </h3>
                    
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <!-- Total Sales -->
                        <a href="{{ route('sales.all') }}" class="p-3.5 border border-gray-100 dark:border-gray-700 rounded-xl hover:border-blue-400/30 hover:shadow-md hover:shadow-blue-400/5 transition-all duration-300 group flex items-start justify-between bg-gray-50/30 dark:bg-gray-800/50">
                            <div class="space-y-1">
                                <div class="text-[10px] font-bold text-ink-muted uppercase tracking-wider">Gross Sales</div>
                                <div class="text-lg font-black text-ink-heading" x-text="formatCurrency(transactions.sales.total_amount)"></div>
                                <div class="text-[11px] text-ink-muted" x-text="transactions.sales.count + ' Sales Orders'"></div>
                            </div>
                            <div class="p-2 bg-blue-50 dark:bg-blue-900/20 rounded-lg text-blue-600 dark:text-blue-400 group-hover:scale-110 transition-transform">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                            </div>
                        </a>

                        <!-- Total Sales Returns -->
                        <a href="{{ route('sales.return') }}" class="p-3.5 border border-gray-100 dark:border-gray-700 rounded-xl hover:border-red-500/30 hover:shadow-md hover:shadow-red-500/5 transition-all duration-300 group flex items-start justify-between bg-gray-50/30 dark:bg-gray-800/50">
                            <div class="space-y-1">
                                <div class="text-[10px] font-bold text-ink-muted uppercase tracking-wider">Sales Returns</div>
                                <div class="text-lg font-black text-ink-heading" x-text="formatCurrency(transactions.sale_returns.total_amount)"></div>
                                <div class="text-[11px] text-ink-muted" x-text="transactions.sale_returns.count + ' Returns'"></div>
                            </div>
                            <div class="p-2 bg-red-50 dark:bg-red-900/20 rounded-lg text-red-600 dark:text-red-400 group-hover:scale-110 transition-transform">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                </svg>
                            </div>
                        </a>

                        <!-- Total Sales Due -->
                        <a href="{{ route('sales.all') }}" class="p-3.5 border border-gray-100 dark:border-gray-700 rounded-xl hover:border-amber-500/30 hover:shadow-md hover:shadow-amber-500/5 transition-all duration-300 group flex items-start justify-between bg-gray-50/30 dark:bg-gray-800/50">
                            <div class="space-y-1">
                                <div class="text-[10px] font-bold text-ink-muted uppercase tracking-wider">Total Sales Due</div>
                                <div class="text-lg font-black text-ink-heading" x-text="formatCurrency(transactions.sales.total_due)"></div>
                                <div class="text-[11px] text-ink-muted">Unpaid Receivables</div>
                            </div>
                            <div class="p-2 bg-amber-50 dark:bg-amber-900/20 rounded-lg text-amber-600 dark:text-amber-400 group-hover:scale-110 transition-transform">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                        </a>
                    </div>
                </div>

                <!-- Highlight Net Sales Banner -->
                <a href="{{ route('sales.all') }}" class="mt-4 p-4 rounded-xl border border-dashed border-amber-300 dark:border-amber-700/60 bg-gradient-to-r from-amber-50 to-orange-50/30 dark:from-amber-950/10 dark:to-orange-950/5 flex items-center justify-between group hover:shadow-md hover:border-amber-400 transition-all duration-300">
                    <div class="flex items-center gap-3">
                        <div class="p-2.5 bg-amber-500 text-white rounded-xl shadow-sm shadow-amber-500/20">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div>
                            <div class="text-xs font-bold text-ink-muted uppercase tracking-wider">Net Sales</div>
                            <div class="text-[10px] text-ink-muted">Gross sales value minus returns</div>
                        </div>
                    </div>
                    <div class="text-right">
                        <div class="text-xl font-black text-ink-heading group-hover:scale-105 transition-transform" x-text="formatCurrency(transactions.sales_net.total_amount)"></div>
                    </div>
                </a>
            </div>

            <!-- Purchases Card -->
            <div class="bg-white dark:bg-gray-800 shadow-sm border border-gray-100 dark:border-gray-700 rounded-2xl p-6 flex flex-col justify-between">
                <div>
                    <h3 class="text-base font-extrabold text-gray-800 dark:text-gray-100 mb-5 pb-3 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                        <span>Purchase Activity</span>
                        <span class="text-xs font-semibold px-2 py-0.5 rounded-full bg-blue-50 dark:bg-blue-950/30 text-blue-700 dark:text-blue-400">Inventory In</span>
                    </h3>
                    
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <!-- Total Purchases -->
                        <a href="{{ route('purchases.all') }}" class="p-3.5 border border-gray-100 dark:border-gray-700 rounded-xl hover:border-emerald-500/30 hover:shadow-md hover:shadow-emerald-500/5 transition-all duration-300 group flex items-start justify-between bg-gray-50/30 dark:bg-gray-800/50">
                            <div class="space-y-1">
                                <div class="text-[10px] font-bold text-ink-muted uppercase tracking-wider">Gross Purchases</div>
                                <div class="text-lg font-black text-ink-heading" x-text="formatCurrency(transactions.purchases.total_amount)"></div>
                                <div class="text-[11px] text-ink-muted" x-text="transactions.purchases.count + ' Purchases'"></div>
                            </div>
                            <div class="p-2 bg-emerald-50 dark:bg-emerald-900/20 rounded-lg text-emerald-600 dark:text-emerald-400 group-hover:scale-110 transition-transform">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                                </svg>
                            </div>
                        </a>

                        <!-- Total Purchase Returns -->
                        <a href="{{ route('purchases.return') }}" class="p-3.5 border border-gray-100 dark:border-gray-700 rounded-xl hover:border-red-500/30 hover:shadow-md hover:shadow-red-500/5 transition-all duration-300 group flex items-start justify-between bg-gray-50/30 dark:bg-gray-800/50">
                            <div class="space-y-1">
                                <div class="text-[10px] font-bold text-ink-muted uppercase tracking-wider">Purchase Returns</div>
                                <div class="text-lg font-black text-ink-heading" x-text="formatCurrency(transactions.purchase_returns.total_amount)"></div>
                                <div class="text-[11px] text-ink-muted" x-text="transactions.purchase_returns.count + ' Returns'"></div>
                            </div>
                            <div class="p-2 bg-red-50 dark:bg-red-900/20 rounded-lg text-red-600 dark:text-red-400 group-hover:scale-110 transition-transform">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                </svg>
                            </div>
                        </a>

                        <!-- Total Purchase Due -->
                        <a href="{{ route('purchases.all') }}" class="p-3.5 border border-gray-100 dark:border-gray-700 rounded-xl hover:border-orange-500/30 hover:shadow-md hover:shadow-orange-500/5 transition-all duration-300 group flex items-start justify-between bg-gray-50/30 dark:bg-gray-800/50">
                            <div class="space-y-1">
                                <div class="text-[10px] font-bold text-ink-muted uppercase tracking-wider">Purchase Due</div>
                                <div class="text-lg font-black text-ink-heading" x-text="formatCurrency(transactions.purchases.total_due)"></div>
                                <div class="text-[11px] text-ink-muted">Unpaid Payables</div>
                            </div>
                            <div class="p-2 bg-orange-50 dark:bg-orange-900/20 rounded-lg text-orange-600 dark:text-orange-400 group-hover:scale-110 transition-transform">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                            </div>
                        </a>
                    </div>
                </div>

                <!-- Highlight Net Purchases Banner -->
                <a href="{{ route('purchases.all') }}" class="mt-4 p-4 rounded-xl border border-dashed border-orange-300 dark:border-orange-700/60 bg-gradient-to-r from-orange-50 to-red-50/30 dark:from-orange-950/10 dark:to-red-950/5 flex items-center justify-between group hover:shadow-md hover:border-orange-400 transition-all duration-300">
                    <div class="flex items-center gap-3">
                        <div class="p-2.5 bg-orange-500 text-white rounded-xl shadow-sm shadow-orange-500/20">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2" />
                            </svg>
                        </div>
                        <div>
                            <div class="text-xs font-bold text-ink-muted uppercase tracking-wider">Net Purchases</div>
                            <div class="text-[10px] text-ink-muted">Gross purchases value minus returns</div>
                        </div>
                    </div>
                    <div class="text-right">
                        <div class="text-xl font-black text-ink-heading group-hover:scale-105 transition-transform" x-text="formatCurrency(transactions.purchases_net.total_amount)"></div>
                    </div>
                </a>
            </div>
        </div>

        <!-- Net Profit / Income Summary Banner -->
        <div class="bg-white dark:bg-gray-800 shadow-sm border border-gray-100 dark:border-gray-700 rounded-2xl p-5 flex flex-col sm:flex-row items-center justify-between gap-4">
            <div class="flex items-center gap-3.5">
                <div class="p-3 rounded-2xl bg-emerald-500 text-white shadow-md shadow-emerald-500/20 flex-shrink-0">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div>
                    <h4 class="text-sm font-extrabold text-gray-800 dark:text-gray-100 uppercase tracking-wider">Estimated Net Income / Profit</h4>
                    <p class="text-xs text-ink-muted mt-0.5">Calculated as Net Sales − COGS (Cost of Goods Sold) − Total Expenses</p>
                </div>
            </div>
            <div class="flex items-center gap-6 text-right w-full sm:w-auto justify-between sm:justify-end">
                <div>
                    <div class="text-[10px] font-bold text-ink-muted uppercase tracking-wider">Total Expenses</div>
                    <div class="text-sm font-extrabold text-ink-heading" x-text="formatCurrency(transactions.total_expenses)"></div>
                </div>
                <div>
                    <div class="text-[10px] font-bold text-ink-muted uppercase tracking-wider">Net Profit</div>
                    <div class="text-2xl font-black text-ink-heading" x-text="formatCurrency(transactions.net_profit)"></div>
                </div>
            </div>
        </div>

        <!-- Reports -->
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
            <!-- Sales & Sales Return Report -->
            <div class="bg-white dark:bg-gray-800 shadow-sm border border-gray-100 dark:border-gray-700 rounded-2xl p-6 relative">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6">
                    <h3 class="text-base font-extrabold text-gray-800 dark:text-gray-100 mb-4 sm:mb-0">Sales &amp; Returns Analytics</h3>
                    <div class="flex items-center space-x-2">
                        <div class="relative z-[50]" x-data="purchaseDateRange({ 
                            dateFrom: '{{ \Carbon\Carbon::now()->startOfMonth()->format('Y-m-d') }}',
                            dateTo: '{{ \Carbon\Carbon::now()->format('Y-m-d') }}',
                            onSubmit: function(start, end) { $dispatch('fetch-chart1', { start: start, end: end }); }
                        })" @fetch-chart1.window="if($event.detail.start) fetchChartData('chart1', $event.detail.start, $event.detail.end)">
                            @include('components.date-range-picker')
                        </div>
                        <div x-data="{ open: false }" class="relative">
                            <button @click="open = !open" @click.away="open = false" class="p-2 border dark:border-gray-700 rounded bg-gray-50 dark:bg-gray-700 hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors">
                                <svg class="w-4 h-4 text-gray-600 dark:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"></path></svg>
                            </button>
                            <div x-show="open" class="absolute right-0 mt-2 w-48 bg-white dark:bg-gray-800 rounded-md shadow-lg z-50 border dark:border-gray-700" style="display: none;">
                                <div class="py-1">
                                    <button @click="printChart('chart1'); open = false" class="block w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700">Print</button>
                                    <button @click="downloadPdf('chart1', 'Sales_SalesReturn_Report'); open = false" class="block w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700">Download PDF</button>
                                    <button @click="downloadCsv('chart1', 'Sales_SalesReturn_Report'); open = false" class="block w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700">Download CSV</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div id="chart1" class="min-h-[300px]"></div>
            </div>

            <!-- Purchases & Purchase Return Report -->
            <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6 relative">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6">
                    <h3 class="text-lg font-bold text-gray-800 dark:text-gray-100 mb-4 sm:mb-0">Purchases & Purchase Return Report</h3>
                    <div class="flex items-center space-x-2">
                        <div class="relative z-[40]" x-data="purchaseDateRange({ 
                            dateFrom: '{{ \Carbon\Carbon::now()->startOfMonth()->format('Y-m-d') }}',
                            dateTo: '{{ \Carbon\Carbon::now()->format('Y-m-d') }}',
                            onSubmit: function(start, end) { $dispatch('fetch-chart2', { start: start, end: end }); }
                        })" @fetch-chart2.window="if($event.detail.start) fetchChartData('chart2', $event.detail.start, $event.detail.end)">
                            @include('components.date-range-picker')
                        </div>
                        <div x-data="{ open: false }" class="relative">
                            <button @click="open = !open" @click.away="open = false" class="p-2 border dark:border-gray-700 rounded bg-gray-50 dark:bg-gray-700 hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors">
                                <svg class="w-4 h-4 text-gray-600 dark:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"></path></svg>
                            </button>
                            <div x-show="open" class="absolute right-0 mt-2 w-48 bg-white dark:bg-gray-800 rounded-md shadow-lg z-50 border dark:border-gray-700" style="display: none;">
                                <div class="py-1">
                                    <button @click="printChart('chart2'); open = false" class="block w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700">Print</button>
                                    <button @click="downloadPdf('chart2', 'Purchases_PurchaseReturn_Report'); open = false" class="block w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700">Download PDF</button>
                                    <button @click="downloadCsv('chart2', 'Purchases_PurchaseReturn_Report'); open = false" class="block w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700">Download CSV</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div id="chart2" class="min-h-[300px]"></div>
            </div>
        </div>

        <!-- Product Peak Sales Tracker Card -->
        <div class="bg-white dark:bg-gray-800 shadow-sm border border-gray-100 dark:border-gray-700 rounded-2xl p-6 relative">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
                <div>
                    <h3 class="text-base font-extrabold text-gray-800 dark:text-gray-100 flex items-center gap-2">
                        <span class="inline-block w-2.5 h-2.5 rounded-full bg-[#4634ff]"></span>
                        <span>Product Sales Performance &amp; Peak Date Tracker</span>
                    </h3>
                    <p class="text-xs text-ink-muted mt-0.5">Select a product to view its sales performance and identify its highest single-day peak date.</p>
                </div>

                <div class="flex flex-wrap items-center gap-3 w-full md:w-auto">
                    <!-- Date Range Picker Component -->
                    <div class="relative z-[35]" x-data="purchaseDateRange({ 
                        dateFrom: '{{ \Carbon\Carbon::now()->startOfMonth()->format('Y-m-d') }}',
                        dateTo: '{{ \Carbon\Carbon::now()->format('Y-m-d') }}',
                        onSubmit: function(start, end) { $dispatch('fetch-product-breakdown-range', { start: start, end: end }); }
                    })" @fetch-product-breakdown-range.window="if($event.detail.start) { breakdownStartDate = $event.detail.start; breakdownEndDate = $event.detail.end; fetchProductBreakdown(selectedProductIdForBreakdown, $event.detail.start, $event.detail.end); }">
                        @include('components.date-range-picker')
                    </div>

                    <!-- Product Select -->
                    <div class="w-full md:w-72">
                        <select x-model="selectedProductIdForBreakdown" @change="fetchProductBreakdown($event.target.value, breakdownStartDate, breakdownEndDate)"
                            class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900 text-ink-body px-3.5 py-2 text-xs font-bold focus:ring-[#4634ff] focus:border-[#4634ff] cursor-pointer">
                            <option value="">— Select Product —</option>
                            @foreach(($allProducts ?? []) as $p)
                                <option value="{{ $p->id }}">{{ $p->name }}{{ !empty($p->sku) ? ' (' . $p->sku . ')' : '' }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Print / Export Dropdown -->
                    <div x-data="{ open: false }" class="relative">
                        <button @click="open = !open" @click.away="open = false" 
                            title="Report Options"
                            class="p-2 border border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50/50 dark:bg-gray-900 hover:bg-gray-100 dark:hover:bg-gray-800 text-gray-600 dark:text-gray-300 transition-colors shadow-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"></path>
                            </svg>
                        </button>
                        <div x-show="open" class="absolute right-0 mt-2 w-48 bg-white dark:bg-gray-800 rounded-xl shadow-lg z-50 border border-gray-100 dark:border-gray-700 py-1" style="display: none;">
                            <button @click="printProductSalesPerformancePdf(); open = false" class="flex items-center gap-2 w-full text-left px-4 py-2 text-xs font-semibold text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700">
                                <svg class="w-3.5 h-3.5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                                </svg>
                                Print Report
                            </button>
                            <button @click="downloadProductSalesPerformancePdf(); open = false" class="flex items-center gap-2 w-full text-left px-4 py-2 text-xs font-semibold text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700">
                                <svg class="w-3.5 h-3.5 text-rose-600 dark:text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                </svg>
                                Download PDF
                            </button>
                            <button @click="downloadProductSalesPerformanceCsv(); open = false" class="flex items-center gap-2 w-full text-left px-4 py-2 text-xs font-semibold text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700">
                                <svg class="w-3.5 h-3.5 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                Download CSV
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <template x-if="productBreakdownData && productBreakdownData.product_name">
                <div class="space-y-5">
                    <!-- Peak Metrics Summary -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                        <!-- Peak Sales Date -->
                        <div class="p-4 bg-white dark:bg-gray-800/90 border border-gray-100 dark:border-gray-700/70 rounded-xl shadow-sm hover:shadow-md hover:border-amber-300 dark:hover:border-amber-700/60 transition-all duration-300 group flex flex-col justify-between">
                            <div class="flex items-center justify-between gap-2">
                                <span class="text-[11px] font-bold text-ink-muted uppercase tracking-wider">Peak Sales Date</span>
                                <div class="p-2 bg-amber-50 dark:bg-amber-950/40 text-amber-600 dark:text-amber-400 rounded-lg group-hover:scale-110 transition-transform">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                </div>
                            </div>
                            <div class="mt-3">
                                <div class="text-xl font-black text-gray-900 dark:text-white tracking-tight" x-text="productBreakdownData.peak_date ? formatDateLabel(productBreakdownData.peak_date) : 'No Sales Recorded'"></div>
                                <div class="text-xs text-amber-600 dark:text-amber-400 mt-1 font-medium" x-text="productBreakdownData.peak_quantity > 0 ? (formatNumber(productBreakdownData.peak_quantity) + ' sold on peak date') : 'No volume recorded'"></div>
                            </div>
                        </div>

                        <!-- Peak Day Revenue -->
                        <div class="p-4 bg-white dark:bg-gray-800/90 border border-gray-100 dark:border-gray-700/70 rounded-xl shadow-sm hover:shadow-md hover:border-emerald-300 dark:hover:border-emerald-700/60 transition-all duration-300 group flex flex-col justify-between">
                            <div class="flex items-center justify-between gap-2">
                                <span class="text-[11px] font-bold text-ink-muted uppercase tracking-wider">Peak Day Revenue</span>
                                <div class="p-2 bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 rounded-lg group-hover:scale-110 transition-transform">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                                    </svg>
                                </div>
                            </div>
                            <div class="mt-3">
                                <div class="text-xl font-black text-gray-900 dark:text-white tracking-tight" x-text="formatCurrency(productBreakdownData.peak_amount)"></div>
                                <div class="text-xs text-ink-muted mt-1 font-medium">Highest single-day sales value</div>
                            </div>
                        </div>

                        <!-- Total Qty Sold -->
                        <div class="p-4 bg-white dark:bg-gray-800/90 border border-gray-100 dark:border-gray-700/70 rounded-xl shadow-sm hover:shadow-md hover:border-blue-300 dark:hover:border-blue-700/60 transition-all duration-300 group flex flex-col justify-between">
                            <div class="flex items-center justify-between gap-2">
                                <span class="text-[11px] font-bold text-ink-muted uppercase tracking-wider">Cumulative Qty Sold</span>
                                <div class="p-2 bg-blue-50 dark:bg-blue-950/40 text-blue-600 dark:text-blue-400 rounded-lg group-hover:scale-110 transition-transform">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                    </svg>
                                </div>
                            </div>
                            <div class="mt-3">
                                <div class="text-xl font-black text-gray-900 dark:text-white tracking-tight" x-text="formatNumber(productBreakdownData.total_quantity)"></div>
                                <div class="text-xs text-ink-muted mt-1 font-medium">Total quantity sold in range</div>
                            </div>
                        </div>

                        <!-- Total Revenue -->
                        <div class="p-4 bg-white dark:bg-gray-800/90 border border-gray-100 dark:border-gray-700/70 rounded-xl shadow-sm hover:shadow-md hover:border-purple-300 dark:hover:border-purple-700/60 transition-all duration-300 group flex flex-col justify-between">
                            <div class="flex items-center justify-between gap-2">
                                <span class="text-[11px] font-bold text-ink-muted uppercase tracking-wider">Cumulative Revenue</span>
                                <div class="p-2 bg-purple-50 dark:bg-purple-950/40 text-purple-600 dark:text-purple-400 rounded-lg group-hover:scale-110 transition-transform">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                            </div>
                            <div class="mt-3">
                                <div class="text-xl font-black text-gray-900 dark:text-white tracking-tight" x-text="formatCurrency(productBreakdownData.total_amount)"></div>
                                <div class="text-xs text-ink-muted mt-1 font-medium">Total sales revenue in range</div>
                            </div>
                        </div>
                    </div>

                    <!-- Performance Chart -->
                    <div class="border border-gray-100 dark:border-gray-700 rounded-xl p-4 bg-gray-50/20 dark:bg-gray-800/50">
                        <h4 class="text-xs font-extrabold text-ink-body uppercase tracking-wider mb-2" x-text="'SALES PERFORMANCE — ' + productBreakdownData.product_name"></h4>
                        <div id="productBreakdownChart" class="min-h-[250px]"></div>
                    </div>
                </div>
            </template>

            <template x-if="!selectedProductIdForBreakdown">
                <div class="py-10 text-center text-ink-muted italic border-2 border-dashed border-gray-100 dark:border-gray-700 rounded-xl">
                    <svg class="h-8 w-8 mx-auto mb-2 text-[#4634ff] opacity-40" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" /></svg>
                    <p class="text-xs font-semibold text-ink-muted">Select a product from the dropdown above to view its highest sales date &amp; daily performance.</p>
                </div>
            </template>
        </div>

        <!-- Additional Reports (Top Selling & Payments) -->
        <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
            <!-- Top Selling Products -->
            <div class="bg-white dark:bg-gray-800 shadow-sm border border-gray-100 dark:border-gray-700/80 rounded-2xl p-5 md:p-6 relative" id="topSellingWidget">
                <!-- Header -->
                    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 mb-5 pb-4 border-b border-gray-100 dark:border-gray-750">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-amber-500/10 dark:bg-amber-500/15 border border-amber-500/20 flex items-center justify-center text-amber-600 dark:text-amber-400 shadow-xs shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                                </svg>
                            </div>
                            <div>
                                <div class="flex items-center gap-2">
                                    <h3 class="text-base font-extrabold text-gray-800 dark:text-gray-100 tracking-tight">Top Selling Products</h3>
                                    <span class="text-[11px] font-semibold px-2 py-0.5 rounded-full bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300" x-text="(topSellingProducts.length || 0) + ' items'"></span>
                                </div>
                                <p class="text-xs text-ink-muted mt-0.5">Highest volume and revenue contributors in selected period</p>
                            </div>
                        </div>

                        <div class="flex items-center gap-2 w-full sm:w-auto justify-end">
                            <div class="relative z-[30]" x-data="purchaseDateRange({ 
                                dateFrom: '{{ \Carbon\Carbon::now()->startOfMonth()->format('Y-m-d') }}',
                                dateTo: '{{ \Carbon\Carbon::now()->format('Y-m-d') }}',
                                onSubmit: function(start, end) { $dispatch('fetch-top-selling', { start: start, end: end }); }
                            })" @fetch-top-selling.window="if($event.detail.start) fetchTopSelling($event.detail.start, $event.detail.end)">
                                @include('components.date-range-picker')
                            </div>

                            <div x-data="{ open: false }" class="relative shrink-0">
                                <button @click="open = !open" @click.away="open = false" class="p-2 border border-gray-200 dark:border-gray-700 rounded-lg bg-gray-50/80 dark:bg-gray-700/80 hover:bg-gray-100 dark:hover:bg-gray-600 text-gray-600 dark:text-gray-300 transition-colors shadow-2xs" title="Export & Print Options">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"></path></svg>
                                </button>
                                <div x-show="open" x-cloak class="absolute right-0 mt-2 w-48 bg-white dark:bg-gray-800 rounded-xl shadow-xl z-50 border border-gray-100 dark:border-gray-700 py-1.5 transition-all text-xs font-semibold">
                                    <button @click="printTopSelling(); open = false" class="w-full flex items-center gap-2.5 px-3.5 py-2 text-left text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700/60 transition-colors">
                                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                                        Print Table
                                    </button>
                                    <button @click="downloadTopSellingPdf(); open = false" class="w-full flex items-center gap-2.5 px-3.5 py-2 text-left text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700/60 transition-colors">
                                        <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                                        Download PDF
                                    </button>
                                    <button @click="downloadTopSellingCsv(); open = false" class="w-full flex items-center gap-2.5 px-3.5 py-2 text-left text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700/60 transition-colors border-t border-gray-100 dark:border-gray-700/60">
                                        <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                        Download CSV
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Table with clean subtle header & row states -->
                    <div class="overflow-hidden border border-gray-100 dark:border-gray-700 rounded-xl shadow-2xs">
                        <div class="overflow-x-auto min-h-[300px] max-h-[370px]">
                            <table class="w-full text-sm text-left text-ink-muted border-collapse" id="topSellingTable">
                                <thead class="!bg-gray-50/95 dark:!bg-gray-800/95 !text-gray-500 dark:!text-gray-400 sticky top-0 z-10 border-b border-gray-200/80 dark:border-gray-700 backdrop-blur-xs">
                                    <tr class="!bg-gray-50/95 dark:!bg-gray-800/95 text-[11px] font-bold uppercase tracking-wider">
                                        <th scope="col" class="!bg-transparent !text-gray-500 dark:!text-gray-400 !text-shadow-none p-3.5 w-4 text-center">
                                            <div class="flex items-center justify-center">
                                                <input id="checkbox-all-products" type="checkbox" @change="toggleAllProducts($event.target.checked)" class="w-4 h-4 text-[#4634ff] bg-white border-gray-300 rounded focus:ring-1 focus:ring-[#4634ff] dark:focus:ring-offset-gray-800 dark:bg-gray-700 dark:border-gray-600 cursor-pointer">
                                                <label for="checkbox-all-products" class="sr-only">Select All Products</label>
                                            </div>
                                        </th>
                                        <th scope="col" class="!bg-transparent !text-gray-500 dark:!text-gray-400 !text-shadow-none px-4 py-3 whitespace-nowrap text-center w-14">Rank</th>
                                        <th scope="col" class="!bg-transparent !text-gray-500 dark:!text-gray-400 !text-shadow-none px-4 py-3 whitespace-nowrap">Product</th>
                                        <th scope="col" class="!bg-transparent !text-gray-500 dark:!text-gray-400 !text-shadow-none px-4 py-3 whitespace-nowrap text-right">Qty Sold</th>
                                        <th scope="col" class="!bg-transparent !text-gray-500 dark:!text-gray-400 !text-shadow-none px-4 py-3 whitespace-nowrap text-right">Total Amount</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 dark:divide-gray-700/70">
                                    <template x-for="(product, index) in topSellingProducts" :key="product.product_id">
                                        <tr class="bg-white dark:bg-gray-800 hover:bg-gray-50/80 dark:hover:bg-gray-750 transition-colors group cursor-pointer"
                                            :class="selectedProductIdForBreakdown === product.product_id ? 'bg-indigo-50/30 dark:bg-indigo-950/20' : ''"
                                            @click="selectedProductIdForBreakdown = product.product_id; fetchProductBreakdown(product.product_id)">
                                            <td class="w-4 p-3.5 text-center" @click.stop>
                                                <div class="flex items-center justify-center">
                                                    <input :id="'checkbox-product-'+product.product_id" type="checkbox" :value="product.product_id" x-model="selectedProducts" class="w-4 h-4 text-[#4634ff] bg-white border-gray-300 rounded focus:ring-1 focus:ring-[#4634ff] dark:focus:ring-offset-gray-800 dark:bg-gray-700 dark:border-gray-600 product-checkbox cursor-pointer">
                                                    <label :for="'checkbox-product-'+product.product_id" class="sr-only">Select</label>
                                                </div>
                                            </td>
                                            <td class="px-4 py-3 text-center whitespace-nowrap">
                                                <template x-if="index === 0">
                                                    <span class="inline-flex items-center justify-center w-7 h-7 rounded-lg bg-amber-500/10 text-amber-700 dark:text-amber-400 font-black text-xs ring-1 ring-amber-500/25 shadow-2xs">
                                                        #1
                                                    </span>
                                                </template>
                                                <template x-if="index === 1">
                                                    <span class="inline-flex items-center justify-center w-7 h-7 rounded-lg bg-slate-500/10 text-slate-700 dark:text-slate-300 font-black text-xs ring-1 ring-slate-500/25 shadow-2xs">
                                                        #2
                                                    </span>
                                                </template>
                                                <template x-if="index === 2">
                                                    <span class="inline-flex items-center justify-center w-7 h-7 rounded-lg bg-amber-700/10 text-amber-800 dark:text-amber-300 font-black text-xs ring-1 ring-amber-700/25 shadow-2xs">
                                                        #3
                                                    </span>
                                                </template>
                                                <template x-if="index > 2">
                                                    <span class="inline-flex items-center justify-center w-7 h-7 rounded-lg bg-gray-50 dark:bg-gray-800/80 text-gray-500 dark:text-gray-400 text-xs font-semibold border border-gray-200 dark:border-gray-700" x-text="'#' + (index + 1)"></span>
                                                </template>
                                            </td>
                                            <td class="px-4 py-3">
                                                <div class="flex items-center gap-2">
                                                    <div class="font-bold text-gray-900 dark:text-gray-100 group-hover:text-[#4634ff] transition-colors leading-snug line-clamp-1" x-text="product.product_name"></div>
                                                    <template x-if="selectedProductIdForBreakdown === product.product_id">
                                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold bg-[#4634ff]/10 text-[#4634ff] dark:bg-[#4634ff]/20">Active</span>
                                                    </template>
                                                </div>
                                                <div class="text-[11px] font-mono text-gray-400 dark:text-gray-500 mt-0.5" x-text="'SKU: ' + (product.sku || 'N/A')"></div>
                                            </td>
                                            <td class="px-4 py-3 text-right whitespace-nowrap">
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-bold bg-gray-100 dark:bg-gray-700/70 text-gray-800 dark:text-gray-200" x-text="formatNumber(product.total_quantity)"></span>
                                            </td>
                                            <td class="px-4 py-3 text-right whitespace-nowrap font-bold text-gray-900 dark:text-white" x-text="formatCurrency(product.total_amount)"></td>
                                        </tr>
                                    </template>
                                    <tr x-show="topSellingProducts.length === 0">
                                        <td colspan="5" class="px-6 py-14 text-center">
                                            <div class="max-w-xs mx-auto">
                                                <svg class="h-8 w-8 mx-auto text-gray-300 dark:text-gray-600 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0a2 2 0 012 2v4a2 2 0 01-2 2H6a2 2 0 01-2-2v-4a2 2 0 012-2m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                                                </svg>
                                                <p class="text-xs font-medium text-gray-500 dark:text-gray-400">No sales transactions found for the selected date range.</p>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
            </div>

             <!-- Payments Report -->
             <div class="bg-white dark:bg-gray-800 shadow-sm border border-gray-100 dark:border-gray-700 rounded-2xl p-6 relative">
                 <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6">
                     <h3 class="text-base font-extrabold text-gray-800 dark:text-gray-100 mb-4 sm:mb-0">Payments Flow</h3>
                     <div class="flex items-center space-x-2">
                         <div class="relative z-[20]" x-data="purchaseDateRange({ 
                             dateFrom: '{{ \Carbon\Carbon::now()->startOfMonth()->format('Y-m-d') }}',
                             dateTo: '{{ \Carbon\Carbon::now()->format('Y-m-d') }}',
                             onSubmit: function(start, end) { $dispatch('fetch-chart3', { start: start, end: end }); }
                         })" @fetch-chart3.window="if($event.detail.start) fetchPaymentChartData($event.detail.start, $event.detail.end)">
                             @include('components.date-range-picker')
                         </div>
                         <div x-data="{ open: false }" class="relative">
                             <button @click="open = !open" @click.away="open = false" class="p-2 border dark:border-gray-700 rounded bg-gray-50 dark:bg-gray-700 hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors">
                                 <svg class="w-4 h-4 text-gray-600 dark:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"></path></svg>
                             </button>
                             <div x-show="open" class="absolute right-0 mt-2 w-48 bg-white dark:bg-gray-800 rounded-md shadow-lg z-50 border dark:border-gray-700" style="display: none;">
                                 <div class="py-1">
                                     <button @click="printChart('chart3'); open = false" class="block w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700">Print</button>
                                     <button @click="downloadPdf('chart3', 'Payments_Report'); open = false" class="block w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700">Download PDF</button>
                                     <button @click="downloadCsv('chart3', 'Payments_Report'); open = false" class="block w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700">Download CSV</button>
                                 </div>
                             </div>
                         </div>
                     </div>
                 </div>
                 <div id="chart3" class="min-h-[300px]"></div>
             </div>
         </div>

         <!-- Alerts: Stock Level & Expiry -->
         <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
              <!-- Stock Alerts -->
              <div class="flex flex-col h-full bg-white dark:bg-gray-800 shadow-sm border border-gray-100 dark:border-gray-700/80 rounded-2xl p-5 md:p-6 transition-all"
                   x-data="{
                       search: '',
                       filter: 'all',
                       matchesFilter(item) {
                           const matchSearch = item.name.toLowerCase().includes(this.search.toLowerCase()) || (item.sku && item.sku.toLowerCase().includes(this.search.toLowerCase()));
                           if (!matchSearch) return false;
                           if (this.filter === 'out') return item.current_stock <= 0;
                           if (this.filter === 'low') return item.current_stock > 0;
                           return true;
                       }
                   }">
                  <!-- Header -->
                  <div class="flex flex-wrap items-center justify-between gap-3 mb-4 pb-3 border-b border-gray-100 dark:border-gray-700/60">
                      <div class="flex items-center gap-3">
                          <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-rose-500/10 to-red-500/20 dark:from-rose-500/20 dark:to-red-500/30 border border-rose-200/60 dark:border-rose-800/40 flex items-center justify-center text-rose-600 dark:text-rose-400 shadow-sm shrink-0">
                              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                              </svg>
                          </div>
                          <div>
                              <div class="flex items-center gap-2">
                                  <h3 class="text-base font-extrabold text-gray-800 dark:text-gray-100 tracking-tight">Stock Level Alerts</h3>
                                  <span class="relative flex h-2.5 w-2.5">
                                      <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-rose-400 opacity-75"></span>
                                      <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-rose-500"></span>
                                  </span>
                              </div>
                              <p class="text-xs text-ink-muted mt-0.5">
                                  <span class="font-bold text-rose-600 dark:text-rose-400">{{ $stockAlerts['count'] ?? 0 }} product(s)</span> requiring inventory attention
                              </p>
                          </div>
                      </div>

                      <a href="{{ route('products.all') }}" class="inline-flex items-center gap-1 text-xs font-bold text-rose-600 hover:text-rose-700 dark:text-rose-400 dark:hover:text-rose-300 hover:underline transition-colors">
                          <span>View all</span>
                          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                          </svg>
                      </a>
                  </div>

                  @php
                      $items = $stockAlerts['items'] ?? [];
                      $outOfStockCount = collect($items)->where('current_stock', '<=', 0)->count();
                      $lowStockCount = collect($items)->where('current_stock', '>', 0)->count();
                  @endphp

                  @if(!empty($items))
                      <!-- Controls: Search & Filter Tabs -->
                      <div class="flex flex-wrap items-center justify-between gap-2.5 mb-3.5">
                          <!-- Filter Chips -->
                          <div class="flex items-center gap-1.5 p-1 bg-gray-100/80 dark:bg-gray-900/60 rounded-xl border border-gray-200/50 dark:border-gray-700/50 text-xs">
                              <button type="button" @click="filter = 'all'"
                                      :class="filter === 'all' ? 'bg-white dark:bg-gray-800 text-gray-900 dark:text-white shadow-xs font-bold' : 'text-ink-muted hover:text-gray-800 dark:hover:text-gray-200 font-medium'"
                                      class="px-2.5 py-1 rounded-lg transition-all">
                                  All ({{ count($items) }})
                              </button>
                              <button type="button" @click="filter = 'out'"
                                      :class="filter === 'out' ? 'bg-rose-500 text-white shadow-xs font-bold' : 'text-ink-muted hover:text-rose-600 dark:hover:text-rose-400 font-medium'"
                                      class="px-2.5 py-1 rounded-lg transition-all flex items-center gap-1">
                                  <span class="w-1.5 h-1.5 rounded-full bg-rose-400" :class="filter === 'out' ? 'bg-white' : ''"></span>
                                  Out of stock ({{ $outOfStockCount }})
                              </button>
                              <button type="button" @click="filter = 'low'"
                                      :class="filter === 'low' ? 'bg-amber-500 text-white shadow-xs font-bold' : 'text-ink-muted hover:text-amber-600 dark:hover:text-amber-400 font-medium'"
                                      class="px-2.5 py-1 rounded-lg transition-all flex items-center gap-1">
                                  <span class="w-1.5 h-1.5 rounded-full bg-amber-400" :class="filter === 'low' ? 'bg-white' : ''"></span>
                                  Low stock ({{ $lowStockCount }})
                              </button>
                          </div>

                          <!-- Quick Search Input -->
                          <div class="relative flex-1 min-w-[140px] max-w-[200px]">
                              <input type="text" x-model="search" placeholder="Search item or SKU..."
                                     class="w-full text-xs py-1.5 pl-7 pr-2.5 bg-gray-50 dark:bg-gray-900/50 border border-gray-200 dark:border-gray-700/80 rounded-xl focus:ring-2 focus:ring-rose-500/20 focus:border-rose-500 dark:text-gray-200 placeholder-gray-400 dark:placeholder-gray-500 transition-all" />
                              <svg class="w-3.5 h-3.5 text-gray-400 absolute left-2 top-2.5 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                              </svg>
                          </div>
                      </div>
                  @endif

                  <!-- Items List Container -->
                  <div class="flex-1 min-h-[380px] max-h-[480px] space-y-2.5 overflow-y-auto pr-1 [&::-webkit-scrollbar]:w-1.5 [&::-webkit-scrollbar-track]:bg-transparent [&::-webkit-scrollbar-thumb]:bg-gray-200 dark:[&::-webkit-scrollbar-thumb]:bg-gray-700 [&::-webkit-scrollbar-thumb]:rounded-full hover:[&::-webkit-scrollbar-thumb]:bg-gray-300 dark:hover:[&::-webkit-scrollbar-thumb]:bg-gray-600 transition-colors">
                      @foreach(($stockAlerts['items'] ?? []) as $item)
                          @php
                              $isOutOfStock = $item['current_stock'] <= 0;
                              $alertQty = max(1, $item['alert_quantity']);
                              $percentage = min(100, max(0, ($item['current_stock'] / $alertQty) * 100));
                          @endphp
                          <div x-show="matchesFilter({{ json_encode($item) }})"
                               x-transition:enter="transition ease-out duration-150"
                               x-transition:enter-start="opacity-0 transform scale-95"
                               x-transition:enter-end="opacity-100 transform scale-100"
                               class="group relative flex flex-col sm:flex-row sm:items-center justify-between p-3.5 rounded-xl border transition-all duration-200 gap-3 {{ $isOutOfStock ? 'bg-gradient-to-r from-rose-50/70 via-rose-50/30 to-transparent dark:from-rose-950/30 dark:via-rose-950/10 dark:to-transparent border-rose-200/80 dark:border-rose-900/40 hover:border-rose-400 dark:hover:border-rose-700' : 'bg-gradient-to-r from-amber-50/60 via-amber-50/20 to-transparent dark:from-amber-950/20 dark:via-amber-950/5 dark:to-transparent border-amber-200/70 dark:border-amber-900/30 hover:border-amber-400 dark:hover:border-amber-700' }}">
                              
                              <!-- Left Details -->
                              <div class="flex items-start gap-3 flex-1 min-w-0">
                                  <!-- Badge Icon -->
                                  <div class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0 mt-0.5 {{ $isOutOfStock ? 'bg-rose-100 dark:bg-rose-900/40 text-rose-600 dark:text-rose-400' : 'bg-amber-100 dark:bg-amber-900/40 text-amber-600 dark:text-amber-400' }}">
                                      @if($isOutOfStock)
                                          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                          </svg>
                                      @else
                                          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01"/>
                                          </svg>
                                      @endif
                                  </div>

                                  <div class="flex-1 min-w-0">
                                      <div class="flex items-center gap-2 flex-wrap">
                                          <a href="{{ route('products.edit', $item['id']) }}" class="font-bold text-sm text-gray-800 dark:text-gray-100 hover:text-rose-600 dark:hover:text-rose-400 truncate transition-colors" title="{{ $item['name'] }}">
                                              {{ $item['name'] }}
                                          </a>
                                          @if($isOutOfStock)
                                              <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-extrabold bg-rose-100 text-rose-700 dark:bg-rose-950 dark:text-rose-300 border border-rose-200 dark:border-rose-800/60">
                                                  Out of Stock
                                              </span>
                                          @else
                                              <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-extrabold bg-amber-100 text-amber-700 dark:bg-amber-950 dark:text-amber-300 border border-amber-200 dark:border-amber-800/60">
                                                  Low Stock
                                              </span>
                                          @endif
                                      </div>

                                      <div class="flex items-center gap-2 mt-1">
                                          <span class="inline-flex items-center font-mono text-[10px] text-ink-muted bg-gray-100 dark:bg-gray-700/60 px-1.5 py-0.5 rounded border border-gray-200/60 dark:border-gray-600/50">
                                              SKU: {{ $item['sku'] ?? 'N/A' }}
                                          </span>
                                      </div>
                                  </div>
                              </div>

                              <!-- Right Stock Stats & Quick Restock -->
                              <div class="flex sm:flex-col items-center sm:items-end justify-between border-t sm:border-t-0 pt-2 sm:pt-0 border-gray-100 dark:border-gray-700/40 gap-2 shrink-0">
                                  <div class="text-left sm:text-right">
                                      <div class="text-sm font-black {{ $isOutOfStock ? 'text-rose-600 dark:text-rose-400' : 'text-amber-600 dark:text-amber-400' }}">
                                          {{ $item['current_stock'] }} in stock
                                      </div>
                                      <div class="text-[10px] text-ink-muted">
                                          Alert limit: <span class="font-semibold text-gray-600 dark:text-gray-300">{{ $item['alert_quantity'] }}</span>
                                      </div>
                                  </div>

                                  <!-- Quick Restock Action -->
                                  <a href="{{ route('purchases.create') }}" class="inline-flex items-center gap-1 px-2.5 py-1 text-[11px] font-bold rounded-lg bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 border border-gray-200 dark:border-gray-700 shadow-2xs hover:bg-gray-50 dark:hover:bg-gray-700 hover:text-rose-600 dark:hover:text-rose-400 transition-all active:scale-95">
                                      <svg class="w-3 h-3 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                                      </svg>
                                      <span>Restock</span>
                                  </a>
                              </div>
                          </div>
                      @endforeach

                      @if(empty($stockAlerts['items']))
                          <div class="flex flex-col items-center justify-center py-8 text-center bg-emerald-50/30 dark:bg-emerald-950/10 rounded-xl border border-emerald-100/60 dark:border-emerald-900/30">
                              <div class="w-12 h-12 rounded-full bg-emerald-100 dark:bg-emerald-900/40 text-emerald-600 dark:text-emerald-400 flex items-center justify-center mb-2">
                                  <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                  </svg>
                              </div>
                              <h4 class="text-sm font-bold text-gray-800 dark:text-gray-100">All Stock Levels Healthy</h4>
                              <p class="text-xs text-ink-muted mt-1 max-w-[240px]">No products are currently below their alert quantity threshold.</p>
                          </div>
                      @endif
                  </div>
              </div>

             <!-- Products per Category Analytics (Chart) -->
             <div class="flex flex-col h-full bg-white dark:bg-gray-800 shadow-sm border border-gray-100 dark:border-gray-700/80 rounded-2xl p-5 md:p-6 transition-all">
                 <!-- Header -->
                 <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 mb-5 pb-4 border-b border-gray-100 dark:border-gray-750">
                     <div class="flex items-center gap-3">
                         <div class="w-10 h-10 rounded-xl bg-indigo-500/10 dark:bg-indigo-500/15 border border-indigo-500/20 flex items-center justify-center text-[#4634ff] dark:text-indigo-400 shadow-xs shrink-0">
                             <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                             </svg>
                         </div>
                         <div>
                             <div class="flex items-center gap-2">
                                 <h3 class="text-base font-extrabold text-gray-800 dark:text-gray-100 tracking-tight">Products per Category</h3>
                                 <template x-if="categoryAnalytics.total_categories">
                                     <span class="text-[11px] font-semibold px-2 py-0.5 rounded-full bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300" x-text="(categoryAnalytics.total_categories || 0) + ' categories'"></span>
                                 </template>
                             </div>
                             <p class="text-xs text-ink-muted mt-0.5">Compare category volume and revenue over time</p>
                         </div>
                     </div>

                     <div class="flex items-center gap-2 w-full sm:w-auto justify-end flex-wrap">
                         @if(Route::has('categories.index'))
                             <a href="{{ route('categories.index') }}" class="text-xs font-bold text-gray-500 hover:text-[#4634ff] dark:text-gray-400 dark:hover:text-indigo-400 transition-colors px-2 py-1.5 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700/50">Manage</a>
                         @endif
                         <!-- Print Report Button -->
                         <button type="button" @click="printCategoryPerformancePdf()"
                             class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50/80 dark:bg-gray-700/80 hover:bg-gray-100 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 transition-colors shadow-2xs">
                             <svg class="w-3.5 h-3.5 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                             </svg>
                             Print Report
                         </button>
                     </div>
                 </div>

                 <!-- Unified Filter & Summary Bar -->
                 <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
                     <!-- Date Range + Category Filter inline -->
                     <div class="flex flex-wrap items-center gap-2.5">
                         <div class="relative z-[15]" x-data="purchaseDateRange({
                             dateFrom: '{{ \Carbon\Carbon::now()->startOfMonth()->format('Y-m-d') }}',
                             dateTo: '{{ \Carbon\Carbon::now()->format('Y-m-d') }}',
                             onSubmit: function(start, end) { $dispatch('fetch-category-chart', { start: start, end: end }); }
                         })" @fetch-category-chart.window="if($event.detail.start){ categoryStartDate=$event.detail.start; categoryEndDate=$event.detail.end; fetchCategoryChart(); }">
                             @include('components.date-range-picker')
                         </div>

                         <!-- Inline Category Dropdown -->
                         <div class="relative min-w-[170px] sm:min-w-[200px]">
                             <select id="categorySelectDropdown"
                                 x-model="selectedCategoryFilter"
                                 @change="onCategorySelectChange()"
                                 class="w-full text-xs font-semibold bg-white dark:bg-gray-900 text-gray-700 dark:text-gray-200 border border-gray-200 dark:border-gray-700 rounded-md px-3 py-2 pr-8 focus:ring-1 focus:ring-[#4634ff] focus:border-[#4634ff] outline-none shadow-2xs transition-all cursor-pointer">
                                 <option value="ALL">All Categories</option>
                                 <template x-for="item in (categoryAnalytics.all_items || [])" :key="item.id ?? 'uncategorized'">
                                     <option :value="(item.id !== null && item.id !== undefined) ? String(item.id) : 'uncategorized'"
                                         x-text="item.name + (item.total_sold_qty > 0 ? ' (' + formatNumber(item.total_sold_qty) + ')' : '')">
                                     </option>
                                 </template>
                             </select>
                         </div>

                         <div x-show="categoryLoading" class="inline-flex items-center gap-1 text-xs text-[#4634ff] font-semibold" style="display: none;">
                             <svg class="animate-spin h-3.5 w-3.5" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                             <span>Updating…</span>
                         </div>
                     </div>

                     <!-- Summary metric badges -->
                     <div class="flex items-center gap-2 flex-wrap">
                         <span class="text-[11px] font-semibold text-ink-muted bg-gray-50 dark:bg-gray-700/60 border border-gray-100 dark:border-gray-700 px-2.5 py-1 rounded-lg">
                             <span x-text="categoryAnalytics.total_products || 0" class="font-bold text-gray-800 dark:text-gray-200"></span> products
                         </span>
                         <template x-if="categoryAnalytics.total_period_sales > 0">
                             <span class="text-[11px] font-bold text-emerald-700 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-950/40 px-2.5 py-1 rounded-lg border border-emerald-200/60 dark:border-emerald-800/50"
                                 x-text="settings.currency_symbol + Number(categoryAnalytics.total_period_sales).toLocaleString(undefined,{minimumFractionDigits:2,maximumFractionDigits:2}) + ' sales'">
                             </span>
                         </template>
                     </div>
                 </div>

                 <!-- Chart -->
                 <div class="relative flex-1">
                     <div id="categoryChart" class="min-h-[290px]"></div>
                     <div x-show="!categoryAnalytics.all_items || categoryAnalytics.all_items.length === 0" class="absolute inset-0 flex items-center justify-center">
                         <div class="text-sm text-gray-400 italic text-center py-12">
                             <svg class="h-8 w-8 mx-auto mb-2 text-gray-300 dark:text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" /></svg>
                             No category data found.
                         </div>
                     </div>
                 </div>

                 <!-- Category Stats Chips (Clean list below chart) -->
                 <div class="mt-3 pt-3 border-t border-gray-100 dark:border-gray-700/60 flex flex-wrap gap-2" x-show="selectedCategoryItems().length > 0">
                     <template x-for="item in selectedCategoryItems()" :key="item.id ?? 'uncategorized'">
                         <div class="inline-flex items-center gap-2 text-xs px-2.5 py-1.5 rounded-lg bg-gray-50/80 dark:bg-gray-800/70 border border-gray-150 dark:border-gray-700 shadow-2xs">
                             <span class="font-bold text-gray-800 dark:text-gray-200" x-text="item.name"></span>
                             <span class="text-[11px] text-ink-muted" x-text="item.products_count + ' pdt'"></span>
                             <template x-if="item.total_sold_qty > 0">
                                 <span class="text-[11px] font-bold text-emerald-600 dark:text-emerald-400" x-text="formatNumber(item.total_sold_qty) + ' sold'"></span>
                             </template>
                             <span class="text-[11px] font-bold text-gray-900 dark:text-white"
                                 x-text="formatCurrency(item.total_sales_amount)">
                             </span>
                         </div>
                     </template>
                 </div>
             </div>
         </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <script>
        function dashboard(initialData) {
            return {
                summary: initialData.summary,
                transactions: initialData.transactions,
                settings: initialData.settings,
                allProducts: initialData.allProducts || [],
                categoryAnalytics: initialData.categoryAnalytics || {},
                categoryStartDate: '{{ \Carbon\Carbon::now()->startOfMonth()->format('Y-m-d') }}',
                categoryEndDate: '{{ \Carbon\Carbon::now()->format('Y-m-d') }}',
                categoryLoading: false,
                categoryChart: null,
                selectedCategoryIds: [],
                selectedCategoryFilter: 'ALL',
                breakdownStartDate: null,
                breakdownEndDate: null,
                selectedProductIdForBreakdown: '',
                productBreakdownData: null,
                productBreakdownChart: null,
                chart1: null,
                chart2: null,
                chart3: null,
                topSellingProducts: [],
                selectedProducts: [],
                allProductsSelected: false,

                init() {
                    // Listen for real-time system updates
                    window.addEventListener('system:update', (event) => {
                        const data = event.detail;
                        console.log('Dashboard reacting to system update:', data);
                        
                        // Only refresh if the change affects dashboard metrics
                        const relevantModels = ['Sale', 'Purchase', 'Customer', 'Supplier', 'Category', 'Product', 'SaleReturn', 'PurchaseReturn'];
                        if (relevantModels.includes(data.model)) {
                            this.refreshStats();
                        }
                    });

                    this.initCharts();
                    this.fetchChartData('chart1');
                    this.fetchChartData('chart2');
                    this.fetchTopSelling();
                    this.fetchPaymentChartData();
                    this.$nextTick(() => { this.initCategoryChart(); });
                },

                // ---- Category Chart ----

                initCategoryChart() {
                    const el = document.querySelector('#categoryChart');
                    if (!el) return;
                    const isDark = document.documentElement.classList.contains('dark');
                    const textColor = isDark ? '#9ca3af' : '#6b7280';

                    const opts = {
                        series: [
                            { name: 'Qty Sold', data: [] },
                            { name: 'Sales Amount', data: [] }
                        ],
                        chart: {
                            type: 'area',
                            height: 320,
                            toolbar: { show: false },
                            foreColor: textColor,
                            animations: { enabled: true, easing: 'easeinout', speed: 400 }
                        },
                        stroke: {
                            curve: 'smooth',
                            width: [2.5, 2.5]
                        },
                        fill: {
                            type: 'gradient',
                            gradient: {
                                shadeIntensity: 1,
                                opacityFrom: 0.35,
                                opacityTo: 0.05,
                                stops: [0, 95, 100]
                            }
                        },
                        dataLabels: { enabled: false },
                        colors: ['#4634ff', '#10B981'],
                        xaxis: {
                            categories: [],
                            labels: {
                                style: { colors: textColor, fontWeight: '700', fontSize: '11px' }
                            }
                        },
                        yaxis: [
                            {
                                min: 0,
                                forceNiceScale: true,
                                title: { text: 'Qty Sold', style: { color: '#4634ff', fontWeight: '700' } },
                                labels: { formatter: (v) => Math.round(v), style: { colors: textColor } }
                            },
                            {
                                min: 0,
                                forceNiceScale: true,
                                opposite: true,
                                title: { text: 'Sales Amount', style: { color: '#10B981', fontWeight: '700' } },
                                labels: { formatter: (v) => this.formatCurrencyShort(v), style: { colors: textColor } }
                            }
                        ],
                        legend: {
                            position: 'bottom',
                            horizontalAlign: 'center',
                            fontWeight: '700',
                            fontSize: '12px',
                            markers: { radius: 6 }
                        },
                        grid: { borderColor: isDark ? '#374151' : '#f3f4f6', strokeDashArray: 4 },
                        tooltip: {
                            shared: true,
                            intersect: false,
                            y: [
                                { formatter: (val) => Number(val || 0).toLocaleString() + ' units' },
                                { formatter: (val) => this.formatCurrency(val) }
                            ]
                        }
                    };

                    this.categoryChart = new ApexCharts(el, opts);
                    this.categoryChart.render();

                    // Default: select all categories
                    const allItems = this.categoryAnalytics.all_items || [];
                    this.selectedCategoryIds = allItems.map(i => i.id);
                    this.fetchCategoryChart();
                },

                updateCategoryChart() {
                    this.fetchCategoryTimelineData();
                },

                fetchCategoryTimelineData() {
                    if (!this.categoryChart) return;
                    const params = new URLSearchParams();
                    params.set('category_id', this.selectedCategoryFilter);
                    if (this.categoryStartDate) params.set('start_date', this.categoryStartDate);
                    if (this.categoryEndDate) params.set('end_date', this.categoryEndDate);

                    fetch('{{ route('dashboard.category-breakdown-data') }}?' + params.toString(), {
                        headers: { 'X-Requested-With': 'XMLHttpRequest' }
                    })
                    .then(r => r.json())
                    .then(data => {
                        const categories = (data.labels || []).map(l => {
                            if (l && l.length === 10 && l.includes('-')) {
                                const parts = l.split('-');
                                const date = new Date(parts[0], parts[1] - 1, parts[2]);
                                return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
                            }
                            return l;
                        });

                        const qtys = data.quantities || [];
                        const amounts = data.amounts || [];

                        const maxQty = Math.max(...qtys, 10);
                        const maxAmount = Math.max(...amounts, 100);

                        const isDark = document.documentElement.classList.contains('dark');
                        const textColor = isDark ? '#9ca3af' : '#6b7280';

                        this.categoryChart.updateOptions({
                            chart: {
                                type: 'area'
                            },
                            stroke: {
                                show: true,
                                curve: 'smooth',
                                width: [2.5, 2.5]
                            },
                            fill: {
                                type: 'gradient',
                                gradient: {
                                    shadeIntensity: 1,
                                    opacityFrom: 0.35,
                                    opacityTo: 0.05,
                                    stops: [0, 95, 100]
                                }
                            },
                            markers: {
                                size: categories.length <= 15 ? 4 : 2,
                                strokeWidth: 2,
                                hover: { size: 6 }
                            },
                            xaxis: {
                                categories: categories,
                                labels: {
                                    style: { colors: textColor, fontWeight: '700', fontSize: '11px' },
                                    rotate: categories.length > 15 ? -45 : 0
                                }
                            },
                            yaxis: [
                                {
                                    min: 0,
                                    max: Math.ceil(maxQty * 1.15),
                                    forceNiceScale: true,
                                    title: { text: 'Qty Sold', style: { color: '#4634ff', fontWeight: '700' } },
                                    labels: { formatter: (v) => Math.round(v), style: { colors: textColor } }
                                },
                                {
                                    min: 0,
                                    max: Math.ceil(maxAmount * 1.15),
                                    forceNiceScale: true,
                                    opposite: true,
                                    title: { text: 'Sales Amount', style: { color: '#10B981', fontWeight: '700' } },
                                    labels: { formatter: (v) => this.formatCurrencyShort(v), style: { colors: textColor } }
                                }
                            ]
                        });

                        this.categoryChart.updateSeries([
                            { name: 'Qty Sold', data: qtys },
                            { name: 'Sales Amount', data: amounts }
                        ]);
                    })
                    .catch(err => console.error('Error fetching category timeline:', err));
                },

                fetchCategoryChart() {
                    this.categoryLoading = true;
                    const params = new URLSearchParams();
                    if (this.categoryStartDate) params.set('start_date', this.categoryStartDate);
                    if (this.categoryEndDate) params.set('end_date', this.categoryEndDate);

                    fetch('{{ route('dashboard.category-performance-data') }}?' + params.toString(), {
                        headers: { 'X-Requested-With': 'XMLHttpRequest' }
                    })
                    .then(r => r.json())
                    .then(data => {
                        this.categoryAnalytics = data;
                        this.categoryLoading = false;
                        // keep only existing valid ids, or re-select all
                        const allItems = data.all_items || [];
                        const validIds = allItems.map(i => i.id);
                        if (this.selectedCategoryFilter === 'ALL') {
                            this.selectedCategoryIds = [...validIds];
                        } else {
                            this.onCategorySelectChange();
                        }
                        this.updateCategoryChart();
                    })
                    .catch(() => { this.categoryLoading = false; });
                },

                selectedCategoryItems() {
                    const all = this.categoryAnalytics.all_items || [];
                    return all.filter(i => this.isCategorySelected(i.id));
                },

                isCategorySelected(id) {
                    const key = id === null ? null : id;
                    return this.selectedCategoryIds.some(x => x === key);
                },

                onCategorySelectChange() {
                    const allItems = this.categoryAnalytics.all_items || [];
                    if (this.selectedCategoryFilter === 'ALL') {
                        this.selectedCategoryIds = allItems.map(i => i.id);
                    } else if (this.selectedCategoryFilter === 'uncategorized') {
                        this.selectedCategoryIds = [null];
                    } else {
                        const numericId = Number(this.selectedCategoryFilter);
                        this.selectedCategoryIds = [numericId];
                    }
                    this.updateCategoryChart();
                },

                toggleCategoryId(id) {
                    const key = id === null ? null : id;
                    const idx = this.selectedCategoryIds.findIndex(x => x === key);
                    if (idx >= 0) {
                        this.selectedCategoryIds.splice(idx, 1);
                    } else {
                        this.selectedCategoryIds.push(key);
                    }
                    this.updateCategoryChart();
                },

                toggleAllCategories() {
                    const allItems = this.categoryAnalytics.all_items || [];
                    if (this.selectedCategoryIds.length === allItems.length) {
                        this.selectedCategoryIds = [];
                    } else {
                        this.selectedCategoryIds = allItems.map(i => i.id);
                    }
                    this.updateCategoryChart();
                },

                // Kept for backward compat / PDF
                fetchCategoryPerformance() {
                    this.fetchCategoryChart();
                },

                printCategoryPerformancePdf() {
                    const params = new URLSearchParams();
                    if (this.categoryStartDate) params.set('start_date', this.categoryStartDate);
                    if (this.categoryEndDate) params.set('end_date', this.categoryEndDate);
                    
                    if (this.selectedCategoryFilter && this.selectedCategoryFilter !== 'ALL') {
                        params.set('category_id', this.selectedCategoryFilter);
                    } else if (this.selectedCategoryIds && this.selectedCategoryIds.length > 0) {
                        const ids = this.selectedCategoryIds.filter(id => id !== null).join(',');
                        if (ids) params.set('category_ids', ids);
                    }

                    const url = '{{ route('dashboard.category-performance.export.pdf') }}?' + params.toString();
                    window.open(url, '_blank');
                },



                getChartOptions(colors, categories, series1, series2, name1, name2) {
                    return {
                        series: [{
                            name: name1,
                            data: series1
                        }, {
                            name: name2,
                            data: series2
                        }],
                        chart: {
                            type: 'bar',
                            height: 300,
                            toolbar: { show: false }
                        },
                        colors: colors,
                        plotOptions: {
                            bar: {
                                horizontal: false,
                                columnWidth: '55%',
                                borderRadius: 5,
                                borderRadiusApplication: 'end'
                            },
                        },
                        dataLabels: { enabled: false },
                        stroke: { show: true, width: 2, colors: ['transparent'] },
                        xaxis: { 
                            categories: categories,
                            labels: {
                                formatter: function(val) {
                                    if(!val) return '';
                                    if(val.length === 10 && val.includes('-')) {
                                        const date = new Date(val);
                                        return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
                                    }
                                    return val;
                                }
                            }
                        },
                        yaxis: {
                            labels: {
                                formatter: (val) => { return this.formatCurrencyShort(val) }
                            }
                        },
                        fill: { opacity: 1 },
                        tooltip: {
                            y: {
                                formatter: (val) => { return this.formatCurrency(val) }
                            }
                        }
                    };
                },

                getLineChartOptions(colors, categories, series1, series2, name1, name2) {
                    return {
                        series: [{ name: name1, data: series1 }, { name: name2, data: series2 }],
                        chart: { type: 'line', height: 300, toolbar: { show: false } },
                        colors: colors,
                        stroke: { curve: 'straight', width: 2 },
                        markers: { size: 4, colors: ['#fff'], strokeColors: colors, strokeWidth: 2 },
                        xaxis: { 
                            categories: categories,
                            labels: {
                                formatter: function(val) {
                                    if(!val) return '';
                                    if(val.length === 10 && val.includes('-')) {
                                        const date = new Date(val);
                                        return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
                                    }
                                    return val;
                                }
                            }
                        },
                        yaxis: {
                            labels: { formatter: (val) => { return this.formatCurrencyShort(val) } }
                        },
                        tooltip: {
                            y: { formatter: (val) => { return this.formatCurrency(val) } }
                        }
                    };
                },

                initCharts() {
                    this.chart1 = new ApexCharts(document.querySelector("#chart1"), this.getChartOptions(['#3B82F6', '#EF4444'], [], [], [], 'Sales', 'Sales Returns'));
                    this.chart1.render();

                    this.chart2 = new ApexCharts(document.querySelector("#chart2"), this.getChartOptions(['#10B981', '#EF4444'], [], [], [], 'Purchases', 'Purchase Returns'));
                    this.chart2.render();

                    this.chart3 = new ApexCharts(document.querySelector("#chart3"), this.getLineChartOptions(['#EF4444', '#3B82F6'], [], [], [], 'Payment Sent', 'Payment Received'));
                    this.chart3.render();
                },

                async fetchChartData(chartId, startDate = null, endDate = null) {
                    if (!startDate) {
                        const today = new Date();
                        const start = new Date(today.getFullYear(), today.getMonth(), 1);
                        startDate = `${start.getFullYear()}-${String(start.getMonth() + 1).padStart(2, '0')}-${String(start.getDate()).padStart(2, '0')}`;
                        endDate = `${today.getFullYear()}-${String(today.getMonth() + 1).padStart(2, '0')}-${String(today.getDate()).padStart(2, '0')}`;
                    }

                    try {
                        const response = await axios.get(`{{ route('dashboard.chart-data') }}?start_date=${startDate}&end_date=${endDate}`);
                        const data = response.data;

                        const categories = data.labels || [];
                        
                        if (chartId === 'chart1') {
                            // Chart1 now shows Sales and Sales Returns
                            this.chart1.updateSeries([{
                                name: 'Sales',
                                data: data.sales
                            }, {
                                name: 'Sales Returns',
                                data: data.sales_returns
                            }]);
                            this.chart1.updateOptions({ xaxis: { categories: categories } });
                        }

                        if (chartId === 'chart2') {
                            // Chart2 now shows Purchases and Purchase Returns
                            this.chart2.updateSeries([{
                                name: 'Purchases',
                                data: data.purchases
                            }, {
                                name: 'Purchase Returns',
                                data: data.purchases_returns
                            }]);
                            this.chart2.updateOptions({ xaxis: { categories: categories } });
                        }
                        
                    } catch (error) {
                        console.error('Failed to fetch chart data:', error);
                    }
                },

                async fetchPaymentChartData(startDate = null, endDate = null) {
                    if (!startDate) {
                        const today = new Date();
                        const start = new Date(today.getFullYear(), today.getMonth(), 1);
                        startDate = `${start.getFullYear()}-${String(start.getMonth() + 1).padStart(2, '0')}-${String(start.getDate()).padStart(2, '0')}`;
                        endDate = `${today.getFullYear()}-${String(today.getMonth() + 1).padStart(2, '0')}-${String(today.getDate()).padStart(2, '0')}`;
                    }
                    try {
                        const response = await axios.get(`{{ route('dashboard.payment-chart-data') }}?start_date=${startDate}&end_date=${endDate}`);
                        const data = response.data;
                        const categories = data.labels || [];
                        this.chart3.updateSeries([{
                            name: 'Payment Sent',
                            data: data.payments_sent
                        }, {
                            name: 'Payment Received',
                            data: data.payments_received
                        }]);
                        this.chart3.updateOptions({ xaxis: { categories: categories } });
                    } catch (error) {
                        console.error('Failed to fetch payment chart data:', error);
                    }
                },

                async fetchTopSelling(startDate = null, endDate = null) {
                    if (!startDate) {
                        const today = new Date();
                        const start = new Date(today.getFullYear(), today.getMonth(), 1);
                        startDate = `${start.getFullYear()}-${String(start.getMonth() + 1).padStart(2, '0')}-${String(start.getDate()).padStart(2, '0')}`;
                        endDate = `${today.getFullYear()}-${String(today.getMonth() + 1).padStart(2, '0')}-${String(today.getDate()).padStart(2, '0')}`;
                    }
                    try {
                        const response = await axios.get(`{{ route('dashboard.top-selling-data') }}?start_date=${startDate}&end_date=${endDate}`);
                        this.topSellingProducts = response.data || [];
                        this.selectedProducts = [];
                        this.allProductsSelected = false;
                        const cbAll = document.getElementById('checkbox-all-products');
                        if (cbAll) cbAll.checked = false;

                        // Auto-select #1 top product if none is selected yet
                        if (this.topSellingProducts.length > 0 && !this.selectedProductIdForBreakdown) {
                            this.selectedProductIdForBreakdown = this.topSellingProducts[0].product_id;
                            this.fetchProductBreakdown(this.selectedProductIdForBreakdown);
                        }
                    } catch (error) {
                        console.error('Failed to fetch top selling products:', error);
                    }
                },

                formatDateLabel(str) {
                    if (!str) return 'N/A';
                    try {
                        const parts = str.split('-');
                        if (parts.length === 3) {
                            const d = new Date(parts[0], parts[1] - 1, parts[2]);
                            return d.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
                        }
                        return str;
                    } catch(e) { return str; }
                },

                async fetchProductBreakdown(productId, startDate = null, endDate = null) {
                    if (!productId) {
                        this.productBreakdownData = null;
                        return;
                    }
                    startDate = startDate || this.breakdownStartDate;
                    endDate = endDate || this.breakdownEndDate;

                    let url = `{{ route('dashboard.top-selling-breakdown') }}?product_id=${productId}`;
                    if (startDate && endDate) {
                        url += `&start_date=${startDate}&end_date=${endDate}`;
                    }

                    try {
                        const response = await axios.get(url);
                        this.productBreakdownData = response.data;

                        this.$nextTick(() => {
                            const chartEl = document.querySelector("#productBreakdownChart");
                            if (!chartEl) return;

                            const categories = (this.productBreakdownData.labels || []).map(l => {
                                if (l && l.length === 10 && l.includes('-')) {
                                    const parts = l.split('-');
                                    const date = new Date(parts[0], parts[1] - 1, parts[2]);
                                    return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
                                }
                                return l;
                            });

                            if (!this.productBreakdownChart) {
                                const options = {
                                    series: [{
                                        name: 'Qty Sold',
                                        data: this.productBreakdownData.quantities || []
                                    }, {
                                        name: 'Sales Amount',
                                        data: this.productBreakdownData.amounts || []
                                    }],
                                    chart: { type: 'bar', height: 260, toolbar: { show: false } },
                                    colors: ['#4634ff', '#10B981'],
                                    plotOptions: { bar: { columnWidth: '50%', borderRadius: 5 } },
                                    dataLabels: { enabled: false },
                                    xaxis: { categories: categories },
                                    yaxis: [
                                        { title: { text: 'Qty' }, labels: { formatter: (v) => Math.round(v) } },
                                        { opposite: true, title: { text: 'Amount' }, labels: { formatter: (v) => this.formatCurrencyShort(v) } }
                                    ],
                                    tooltip: {
                                        y: [
                                            { formatter: (val) => val + ' units' },
                                            { formatter: (val) => this.formatCurrency(val) }
                                        ]
                                    }
                                };
                                this.productBreakdownChart = new ApexCharts(chartEl, options);
                                this.productBreakdownChart.render();
                            } else {
                                this.productBreakdownChart.updateSeries([{
                                    name: 'Qty Sold',
                                    data: this.productBreakdownData.quantities || []
                                }, {
                                    name: 'Sales Amount',
                                    data: this.productBreakdownData.amounts || []
                                }]);
                                this.productBreakdownChart.updateOptions({ xaxis: { categories: categories } });
                            }
                        });
                    } catch (error) {
                        console.error('Failed to fetch product breakdown:', error);
                    }
                },

                toggleAllProducts(checked) {
                    this.allProductsSelected = checked;
                    if (checked) {
                        this.selectedProducts = this.topSellingProducts.map(p => p.product_id.toString());
                    } else {
                        this.selectedProducts = [];
                    }
                },

                async refreshStats() {
                    try {
                        const response = await axios.get("{{ route('dashboard.stats') }}");
                        this.summary = response.data.summary;
                        this.transactions = response.data.transactions;
                        this.fetchChartData('chart1');
                        this.fetchChartData('chart2');
                        this.fetchTopSelling();
                        this.fetchPaymentChartData();
                    } catch (error) {
                        console.error('Failed to refresh stats:', error);
                    }
                },

                formatCurrency(amount) {
                    if (!amount) amount = 0;
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
                },

                formatCurrencyShort(amount) {
                    if (!amount) amount = 0;
                    const { currency_symbol } = this.settings;
                    if(amount >= 1000) {
                        return currency_symbol + (amount / 1000).toFixed(0) + 'k';
                    }
                    return currency_symbol + amount.toFixed(0);
                },

                formatNumber(val) {
                    if (!val) return '0';
                    return Number(val).toLocaleString('en-US');
                },
                
                async printChart(elementId) {
                    // Use client-side report generator to open a new tab and print (matches Top Selling behavior)
                    await this._generateReport(elementId, null, 'print');
                },

                async downloadPdf(elementId, filename) {
                    // Use client-side report generator to open a new tab and show print/pdf dialog
                    await this._generateReport(elementId, filename || null, 'pdf');
                },

                // Internal: build a simple, colorless report and either print or download as PDF
                async _generateReport(elementId, filename = null, mode = 'pdf') {
                    // determine date range from the nearby date-range picker
                    const { startDate: start, endDate: end } = this._findDateRangeFor(elementId);
                    const sDate = new Date(start + 'T00:00:00');
                    const eDate = new Date(end + 'T00:00:00');
                    const diffDays = Math.ceil((eDate - sDate) / (1000 * 60 * 60 * 24));

                    try {
                        const titleMap = {
                            chart1: 'Sales & Sales Return Report',
                            chart2: 'Purchases & Purchase Return Report',
                            chart3: 'Payments Report'
                        };

                        const resolveMainRef = (item, i, dataObj) => {
                            return dataObj.invoice_main?.[i] ?? dataObj.invoice?.[i] ?? dataObj.invoices?.[i] ?? dataObj.invoice_numbers?.[i] ?? dataObj.invoice_nos?.[i] ?? dataObj.ids?.[i] ?? dataObj.main_ids?.[i] ?? dataObj.sale_invoice?.[i] ?? dataObj.purchase_invoice?.[i] ?? dataObj.ref?.[i] ?? dataObj.refs?.[i] ?? '';
                        };
                        const resolveReturnRef = (item, i, dataObj) => {
                            return dataObj.return_ids?.[i] ?? dataObj.invoice_return?.[i] ?? dataObj.invoice_returns?.[i] ?? dataObj.return_invoice?.[i] ?? dataObj.return_invoices?.[i] ?? dataObj.refs_return?.[i] ?? dataObj.ids_return?.[i] ?? dataObj.sale_return_invoice?.[i] ?? dataObj.purchase_return_invoice?.[i] ?? dataObj.ref_return?.[i] ?? dataObj.refs?.[i] ?? '';
                        };

                        const monthlySections = [];

                        if (diffDays > 31) {
                            // Multi-month range: iterate month by month between start and end date
                            let cursor = new Date(sDate.getFullYear(), sDate.getMonth(), 1);
                            while (cursor <= eDate) {
                                const ms = new Date(cursor.getFullYear(), cursor.getMonth(), 1);
                                const me = new Date(cursor.getFullYear(), cursor.getMonth() + 1, 0);
                                const msBound = ms < sDate ? sDate : ms;
                                const meBound = me > eDate ? eDate : me;
                                const msStr = `${msBound.getFullYear()}-${String(msBound.getMonth()+1).padStart(2,'0')}-${String(msBound.getDate()).padStart(2,'0')}`;
                                const meStr = `${meBound.getFullYear()}-${String(meBound.getMonth()+1).padStart(2,'0')}-${String(meBound.getDate()).padStart(2,'0')}`;

                                const monthTitle = ms.toLocaleString('en-US', { month: 'long', year: 'numeric' });
                                const rangeStr = `${msStr} to ${meStr}`;

                                let data;
                                if (elementId === 'chart3') {
                                    const resp = await axios.get(`{{ route('dashboard.payment-chart-data') }}?start_date=${msStr}&end_date=${meStr}`);
                                    data = resp.data;
                                } else {
                                    const resp = await axios.get(`{{ route('dashboard.chart-data') }}?start_date=${msStr}&end_date=${meStr}`);
                                    data = resp.data;
                                }

                                const rows = [];
                                (data.labels || []).forEach((label, i) => {
                                    const mainRef = resolveMainRef(label, i, data);
                                    const returnRef = resolveReturnRef(label, i, data);
                                    if (elementId === 'chart3') {
                                        rows.push({
                                            label: label,
                                            mainRef: mainRef,
                                            sent: Number(data.payments_sent?.[i] ?? 0),
                                            returnRef: returnRef,
                                            received: Number(data.payments_received?.[i] ?? 0)
                                        });
                                    } else if (elementId === 'chart1') {
                                        rows.push({
                                            label: label,
                                            mainRef: mainRef,
                                            sales: Number(data.sales?.[i] ?? 0),
                                            returnRef: returnRef,
                                            returns: Number(data.sales_returns?.[i] ?? 0)
                                        });
                                    } else {
                                        rows.push({
                                            label: label,
                                            mainRef: mainRef,
                                            purchases: Number(data.purchases?.[i] ?? 0),
                                            returnRef: returnRef,
                                            returns: Number(data.purchases_returns?.[i] ?? 0)
                                        });
                                    }
                                });

                                monthlySections.push({
                                    monthTitle: monthTitle,
                                    rangeStr: rangeStr,
                                    rows: rows
                                });

                                cursor = new Date(cursor.getFullYear(), cursor.getMonth() + 1, 1);
                            }
                        } else {
                            // Single period (<= 31 days)
                            let data;
                            if (elementId === 'chart3') {
                                const resp = await axios.get(`{{ route('dashboard.payment-chart-data') }}?start_date=${start}&end_date=${end}`);
                                data = resp.data;
                            } else {
                                const resp = await axios.get(`{{ route('dashboard.chart-data') }}?start_date=${start}&end_date=${end}`);
                                data = resp.data;
                            }

                            const monthTitle = sDate.toLocaleString('en-US', { month: 'long', year: 'numeric' });
                            const rangeStr = `${start} to ${end}`;
                            const rows = [];
                            (data.labels || []).forEach((label, i) => {
                                const mainRef = resolveMainRef(label, i, data);
                                const returnRef = resolveReturnRef(label, i, data);
                                if (elementId === 'chart3') {
                                    rows.push({
                                        label: label,
                                        mainRef: mainRef,
                                        sent: Number(data.payments_sent?.[i] ?? 0),
                                        returnRef: returnRef,
                                        received: Number(data.payments_received?.[i] ?? 0)
                                    });
                                } else if (elementId === 'chart1') {
                                    rows.push({
                                        label: label,
                                        mainRef: mainRef,
                                        sales: Number(data.sales?.[i] ?? 0),
                                        returnRef: returnRef,
                                        returns: Number(data.sales_returns?.[i] ?? 0)
                                    });
                                } else {
                                    rows.push({
                                        label: label,
                                        mainRef: mainRef,
                                        purchases: Number(data.purchases?.[i] ?? 0),
                                        returnRef: returnRef,
                                        returns: Number(data.purchases_returns?.[i] ?? 0)
                                    });
                                }
                            });

                            monthlySections.push({
                                monthTitle: monthTitle,
                                rangeStr: rangeStr,
                                rows: rows
                            });
                        }

                        const container = document.createElement('div');
                        container.style.fontFamily = 'DejaVu Sans, sans-serif';
                        container.style.color = '#000';

                        const thStyle = 'border:1px solid #000;text-align:left;font-weight:700;padding:4px 6px;';
                        const tdStyle = 'border:1px solid #000;padding:4px 6px;';

                        let grandTotalMain = 0;
                        let grandTotalReturn = 0;

                        monthlySections.forEach((section, si) => {
                            const sectionDiv = document.createElement('div');
                            sectionDiv.className = 'month-section';
                            sectionDiv.style.pageBreakAfter = (si < monthlySections.length - 1) ? 'always' : 'auto';
                            sectionDiv.style.marginBottom = '16px';

                            const header = document.createElement('div');
                            header.innerHTML = `<h2 style="margin:0 0 4px 0; font-size:14px; font-weight:700;">${titleMap[elementId] || filename || ''}</h2><div style="font-size:11px; margin-bottom:4px; font-weight:700;">${section.monthTitle}</div><div style="font-size:11px; margin-bottom:8px;">Period: ${section.rangeStr}</div>`;
                            sectionDiv.appendChild(header);

                            const table = document.createElement('table');
                            table.setAttribute('cellpadding', '4');
                            table.style.width = '100%';
                            table.style.borderCollapse = 'collapse';
                            table.style.fontSize = '11px';

                            const thead = document.createElement('thead');
                            const trHead = document.createElement('tr');
                            if (elementId === 'chart3') {
                                trHead.innerHTML = `<th style="${thStyle}">Date</th><th style="${thStyle}">Invoice No.</th><th style="${thStyle}">Payment Sent</th><th style="${thStyle}">Invoice No. (Received)</th><th style="${thStyle}">Payment Received</th>`;
                            } else if (elementId === 'chart1') {
                                trHead.innerHTML = `<th style="${thStyle}">Date</th><th style="${thStyle}">Invoice (Sales)</th><th style="${thStyle}">Sales</th><th style="${thStyle}">Invoice (Sales Return)</th><th style="${thStyle}">Sales Returns</th>`;
                            } else {
                                trHead.innerHTML = `<th style="${thStyle}">Date</th><th style="${thStyle}">Invoice (Purchase)</th><th style="${thStyle}">Purchases</th><th style="${thStyle}">Invoice (Purchase Return)</th><th style="${thStyle}">Purchase Returns</th>`;
                            }
                            thead.appendChild(trHead);
                            table.appendChild(thead);

                            const tbody = document.createElement('tbody');
                            let monthMain = 0;
                            let monthReturn = 0;

                            section.rows.forEach(r => {
                                const row = document.createElement('tr');
                                if (elementId === 'chart3') {
                                    monthMain += r.sent;
                                    monthReturn += r.received;
                                    row.innerHTML = `<td style="${tdStyle}">${r.label}</td><td style="${tdStyle}">${r.mainRef}</td><td style="${tdStyle}">${this.formatNumber(r.sent)}</td><td style="${tdStyle}">${r.returnRef}</td><td style="${tdStyle}">${this.formatNumber(r.received)}</td>`;
                                } else if (elementId === 'chart1') {
                                    monthMain += r.sales;
                                    monthReturn += r.returns;
                                    row.innerHTML = `<td style="${tdStyle}">${r.label}</td><td style="${tdStyle}">${r.mainRef}</td><td style="${tdStyle}">${this.formatNumber(r.sales)}</td><td style="${tdStyle}">${r.returnRef}</td><td style="${tdStyle}">${this.formatNumber(r.returns)}</td>`;
                                } else {
                                    monthMain += r.purchases;
                                    monthReturn += r.returns;
                                    row.innerHTML = `<td style="${tdStyle}">${r.label}</td><td style="${tdStyle}">${r.mainRef}</td><td style="${tdStyle}">${this.formatNumber(r.purchases)}</td><td style="${tdStyle}">${r.returnRef}</td><td style="${tdStyle}">${this.formatNumber(r.returns)}</td>`;
                                }
                                tbody.appendChild(row);
                            });

                            grandTotalMain += monthMain;
                            grandTotalReturn += monthReturn;

                            table.appendChild(tbody);
                            sectionDiv.appendChild(table);

                            // Month summary
                            const summaryDiv = document.createElement('div');
                            summaryDiv.style.marginTop = '8px';
                            summaryDiv.style.textAlign = 'right';
                            summaryDiv.style.fontWeight = '700';
                            summaryDiv.style.fontSize = '11px';

                            if (elementId === 'chart1') {
                                summaryDiv.innerHTML = `${section.monthTitle} Sales: ${this.formatCurrency(monthMain)} | Sales Returns: ${this.formatCurrency(monthReturn)}`;
                            } else if (elementId === 'chart2') {
                                summaryDiv.innerHTML = `${section.monthTitle} Purchases: ${this.formatCurrency(monthMain)} | Purchase Returns: ${this.formatCurrency(monthReturn)}`;
                            } else {
                                summaryDiv.innerHTML = `${section.monthTitle} Payment Sent: ${this.formatCurrency(monthMain)} | Payment Received: ${this.formatCurrency(monthReturn)}`;
                            }
                            sectionDiv.appendChild(summaryDiv);

                            container.appendChild(sectionDiv);
                        });

                        // Grand total summary on the last page if multiple sections
                        if (monthlySections.length > 1) {
                            const grandDiv = document.createElement('div');
                            grandDiv.style.marginTop = '16px';
                            grandDiv.style.textAlign = 'right';
                            grandDiv.style.fontWeight = '700';
                            grandDiv.style.fontSize = '12px';
                            grandDiv.style.borderTop = '2px solid #000';
                            grandDiv.style.paddingTop = '8px';

                            if (elementId === 'chart1') {
                                grandDiv.innerHTML = `<div>Grand Total Sales: ${this.formatCurrency(grandTotalMain)}</div><div>Grand Total Sales Returns: ${this.formatCurrency(grandTotalReturn)}</div>`;
                            } else if (elementId === 'chart2') {
                                grandDiv.innerHTML = `<div>Grand Total Purchases: ${this.formatCurrency(grandTotalMain)}</div><div>Grand Total Purchase Returns: ${this.formatCurrency(grandTotalReturn)}</div>`;
                            } else {
                                grandDiv.innerHTML = `<div>Grand Total Payment Sent: ${this.formatCurrency(grandTotalMain)}</div><div>Grand Total Payment Received: ${this.formatCurrency(grandTotalReturn)}</div>`;
                            }
                            container.appendChild(grandDiv);
                        }

                        // Open report in a new tab/window
                        const newWin = window.open('', '_blank');
                        const style = `<style>
                            body{font-family:DejaVu Sans, sans-serif;color:#000;margin:16px;font-size:11px}
                            table{width:100%;border-collapse:collapse;margin-bottom:8px;font-size:11px}
                            td,th{border:1px solid #000;padding:4px 6px;text-align:left}
                            thead th{font-weight:700}
                            .month-section{page-break-after:always;margin-bottom:16px}
                            .month-section:last-child{page-break-after:auto}
                            @media print {
                                .month-section{page-break-after:always}
                                .month-section:last-child{page-break-after:auto}
                            }
                        </style>`;
                        newWin.document.write(`<html><head><title>${titleMap[elementId] || 'Report'}</title>${style}</head><body>`);
                        newWin.document.write(container.innerHTML);
                        newWin.document.write('</body></html>');
                        newWin.document.close();

                        // Trigger print dialog
                        setTimeout(() => {
                            try { newWin.focus(); } catch (e) {}
                            if (mode === 'print' || mode === 'pdf') {
                                newWin.print();
                            }
                        }, 600);

                    } catch (error) {
                        console.error('Failed to generate report:', error);
                        alert('Failed to generate report.');
                    }
                },

                _findDateRangeFor(elementId) {
                    const today = new Date();
                    const defaultStart = `${today.getFullYear()}-${String(today.getMonth()+1).padStart(2,'0')}-01`;
                    const defaultEnd = `${today.getFullYear()}-${String(today.getMonth()+1).padStart(2,'0')}-${String(today.getDate()).padStart(2,'0')}`;
                    let el = document.getElementById(elementId);
                    if (!el) return { startDate: defaultStart, endDate: defaultEnd };

                    try {
                        // 1. Try to find Alpine component data directly
                        let card = el.closest('.relative, .p-6, .rounded-2xl, .rounded-lg') || el.parentElement;
                        if (card) {
                            const dpEl = card.querySelector('[x-data]');
                            if (dpEl && window.Alpine && window.Alpine.$data) {
                                const d = window.Alpine.$data(dpEl);
                                if (d && d.dateFrom && d.dateTo) {
                                    return { startDate: d.dateFrom, endDate: d.dateTo };
                                }
                            }
                        }

                        // 2. Try to find date-range input nearby and parse value
                        let node = el;
                        for (let i = 0; i < 6 && node; i++) {
                            const input = node.querySelector ? (node.querySelector('input[placeholder="Start Date - End Date"]') || node.querySelector('input[readonly]')) : null;
                            if (input && input.value && input.value.includes(' - ')) {
                                const parts = input.value.split(' - ').map(s => s.trim());
                                const pFrom = parts[0].split('/');
                                const pTo = parts[1].split('/');
                                if (pFrom.length === 3 && pTo.length === 3) {
                                    const start = `${pFrom[2]}-${pFrom[0].padStart(2,'0')}-${pFrom[1].padStart(2,'0')}`;
                                    const end = `${pTo[2]}-${pTo[0].padStart(2,'0')}-${pTo[1].padStart(2,'0')}`;
                                    return { startDate: start, endDate: end };
                                }
                            }
                            node = node.parentElement;
                        }
                    } catch (e) {
                        console.error('Error in _findDateRangeFor:', e);
                    }

                    return {
                        startDate: defaultStart,
                        endDate: defaultEnd
                    };
                },

                async downloadCsv(elementId, filename) {
                    const today = new Date();
                    const start = new Date(today.getFullYear(), today.getMonth(), 1);
                    const defaultStart = `${start.getFullYear()}-${String(start.getMonth() + 1).padStart(2, '0')}-${String(start.getDate()).padStart(2, '0')}`;
                    const defaultEnd = `${today.getFullYear()}-${String(today.getMonth() + 1).padStart(2, '0')}-${String(today.getDate()).padStart(2, '0')}`;

                    const { startDate, endDate } = this._findDateRangeFor(elementId);
                    
                    try {
                        const esc = (s) => '"' + String(s ?? '').replace(/"/g, '""') + '"';
                        const aDate = (s) => new Date(s + 'T00:00:00');
                        const diffDays = Math.ceil((aDate(endDate) - aDate(startDate)) / (1000*60*60*24));

                        let csv = '';

                        // If the range spans more than 31 days, generate per-month CSV with daily rows by requesting day-grouped data per month
                        if (diffDays > 31) {
                            // iterate months between start and end
                            const s = new Date(startDate + 'T00:00:00');
                            const e = new Date(endDate + 'T00:00:00');
                            let cursor = new Date(s.getFullYear(), s.getMonth(), 1);
                            while (cursor <= e) {
                                const ms = new Date(cursor.getFullYear(), cursor.getMonth(), 1);
                                const me = new Date(cursor.getFullYear(), cursor.getMonth() + 1, 0);
                                const msStr = `${ms.getFullYear()}-${String(ms.getMonth()+1).padStart(2,'0')}-01`;
                                const meBound = me > e ? e : me;
                                const meStr = `${meBound.getFullYear()}-${String(meBound.getMonth()+1).padStart(2,'0')}-${String(meBound.getDate()).padStart(2,'0')}`;

                                // header for the month
                                const monthTitle = ms.toLocaleString('en-US', { month: 'long', year: 'numeric' });
                                if (elementId === 'chart3') {
                                    csv += `"${monthTitle}"\n"Date","Invoice (Sent)","Payment Sent","Invoice (Received)","Payment Received"\n`;
                                    const resp = await axios.get(`{{ route('dashboard.payment-chart-data') }}?start_date=${msStr}&end_date=${meStr}`);
                                    const data = resp.data;
                                    data.labels.forEach((label, i) => {
                                        const invMain = data.invoice_main?.[i] ?? '';
                                        const invReturn = data.invoice_return?.[i] ?? '';
                                        const paymentsSent = data.payments_sent?.[i] ?? 0;
                                        const paymentsReceived = data.payments_received?.[i] ?? 0;
                                        csv += `${esc(label)},${esc(invMain)},${paymentsSent},${esc(invReturn)},${paymentsReceived}\n`;
                                    });
                                } else {
                                    csv += `"${monthTitle}"\n"Date","Invoice","Amount","Return Invoice","Return Amount"\n`;
                                    const resp = await axios.get(`{{ route('dashboard.chart-data') }}?start_date=${msStr}&end_date=${meStr}`);
                                    const data = resp.data;
                                    if (elementId === 'chart1') {
                                        data.labels.forEach((label, i) => {
                                            const invMain = data.invoice_main?.[i] ?? '';
                                            const invReturn = data.invoice_return?.[i] ?? '';
                                            const sales = data.sales?.[i] ?? 0;
                                            const salesReturns = data.sales_returns?.[i] ?? 0;
                                            csv += `${esc(label)},${esc(invMain)},${sales},${esc(invReturn)},${salesReturns}\n`;
                                        });
                                    } else {
                                        data.labels.forEach((label, i) => {
                                            const invMain = data.invoice_main?.[i] ?? '';
                                            const invReturn = data.invoice_return?.[i] ?? '';
                                            const purchases = data.purchases?.[i] ?? 0;
                                            const purchasesReturns = data.purchases_returns?.[i] ?? 0;
                                            csv += `${esc(label)},${esc(invMain)},${purchases},${esc(invReturn)},${purchasesReturns}\n`;
                                        });
                                    }
                                }

                                // advance to next month
                                cursor = new Date(cursor.getFullYear(), cursor.getMonth() + 1, 1);
                            }
                        } else {
                            // small range: single request covers it
                            if (elementId === 'chart3') {
                                const response = await axios.get(`{{ route('dashboard.payment-chart-data') }}?start_date=${startDate}&end_date=${endDate}`);
                                const data = response.data;
                                csv += 'Date,Invoice (Sent),Payment Sent,Invoice (Received),Payment Received\n';
                                data.labels.forEach((label, i) => {
                                    const invMain = data.invoice_main?.[i] ?? '';
                                    const invReturn = data.invoice_return?.[i] ?? '';
                                    const paymentsSent = data.payments_sent?.[i] ?? 0;
                                    const paymentsReceived = data.payments_received?.[i] ?? 0;
                                    csv += `${esc(label)},${esc(invMain)},${paymentsSent},${esc(invReturn)},${paymentsReceived}\n`;
                                });
                            } else {
                                const response = await axios.get(`{{ route('dashboard.chart-data') }}?start_date=${startDate}&end_date=${endDate}`);
                                const data = response.data;
                                if(elementId === 'chart1') {
                                    csv += 'Date,Invoice (Sales),Sales,Invoice (Sales Return),Sales Returns\n';
                                    data.labels.forEach((label, i) => {
                                        const invMain = data.invoice_main?.[i] ?? '';
                                        const invReturn = data.invoice_return?.[i] ?? '';
                                        const sales = data.sales?.[i] ?? 0;
                                        const salesReturns = data.sales_returns?.[i] ?? 0;
                                        csv += `${esc(label)},${esc(invMain)},${sales},${esc(invReturn)},${salesReturns}\n`;
                                    });
                                } else {
                                    csv += 'Date,Invoice (Purchase),Purchases,Invoice (Purchase Return),Purchase Returns\n';
                                    data.labels.forEach((label, i) => {
                                        const invMain = data.invoice_main?.[i] ?? '';
                                        const invReturn = data.invoice_return?.[i] ?? '';
                                        const purchases = data.purchases?.[i] ?? 0;
                                        const purchasesReturns = data.purchases_returns?.[i] ?? 0;
                                        csv += `${esc(label)},${esc(invMain)},${purchases},${esc(invReturn)},${purchasesReturns}\n`;
                                    });
                                }
                            }
                        }
                        
                        const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
                        const link = document.createElement('a');
                        const url = URL.createObjectURL(blob);
                        link.setAttribute('href', url);
                        link.setAttribute('download', `${filename}.csv`);
                        link.style.visibility = 'hidden';
                        document.body.appendChild(link);
                        link.click();
                        document.body.removeChild(link);
                    } catch (error) {
                         console.error('Failed to download CSV:', error);
                    }
                },

                _getSelectedTopSellingRows() {
                    const selectedIds = this.selectedProducts.map(id => String(id));
                    if (selectedIds.length === 0) {
                        return [];
                    }
                    return this.topSellingProducts.filter(p => selectedIds.includes(String(p.product_id)));
                },

                _buildTopSellingReportHtml(rows, title = 'Top Selling Products Report') {
                    let html = `<h2 style="margin:0 0 8px 0">${title}</h2>`;
                    html += `<table border="1" cellpadding="5" cellspacing="0" style="width:100%; text-align:left; border-collapse:collapse; font-family:sans-serif;"><thead><tr><th>Rank</th><th>Product</th><th>SKU</th><th>Qty Sold</th><th>Total Amount</th></tr></thead><tbody>`;
                    rows.forEach((p, i) => {
                        html += `<tr><td>${i + 1}</td><td>${p.product_name || 'N/A'}</td><td>${p.sku || ''}</td><td>${this.formatNumber(p.total_quantity)}</td><td>${this.formatCurrency(p.total_amount)}</td></tr>`;
                    });
                    html += `</tbody></table>`;

                    if (rows.length > 0) {
                        const totalQty = rows.reduce((sum, p) => sum + Number(p.total_quantity || 0), 0);
                        const totalAmount = rows.reduce((sum, p) => sum + Number(p.total_amount || 0), 0);
                        html += `<div style="margin-top:12px; text-align:right; font-weight:700;">Total Qty: ${this.formatNumber(totalQty)}<br>Total Amount: ${this.formatCurrency(totalAmount)}</div>`;
                    }

                    return html;
                },

                _requireTopSellingSelection() {
                    if (this.selectedProducts.length === 0) {
                        alert('Please select at least one product first.');
                        return false;
                    }
                    return true;
                },

                async printTopSelling() {
                    const selectedRows = this._getSelectedTopSellingRows();
                    if (selectedRows.length === 0 && this.selectedProducts.length === 0) {
                        alert('Please select at least one product first.');
                        return;
                    }

                    const { startDate, endDate } = this._findDateRangeFor('topSellingWidget');
                    const ids = selectedRows.length > 0
                        ? selectedRows.map(p => p.product_id).join(',')
                        : this.topSellingProducts.map(p => p.product_id).join(',');
                    const url = `{{ route('dashboard.top-selling.export.pdf') }}?ids=${encodeURIComponent(ids)}&print=true&start_date=${encodeURIComponent(startDate)}&end_date=${encodeURIComponent(endDate)}`;
                    window.open(url, '_blank');
                },

                downloadTopSellingPdf() {
                    const selectedRows = this._getSelectedTopSellingRows();
                    if (selectedRows.length === 0 && this.selectedProducts.length === 0) {
                        alert('Please select at least one product first.');
                        return;
                    }

                    const { startDate, endDate } = this._findDateRangeFor('topSellingWidget');
                    const ids = selectedRows.length > 0
                        ? selectedRows.map(p => p.product_id).join(',')
                        : this.topSellingProducts.map(p => p.product_id).join(',');
                    const url = `{{ route('dashboard.top-selling.export.pdf') }}?ids=${encodeURIComponent(ids)}&start_date=${encodeURIComponent(startDate)}&end_date=${encodeURIComponent(endDate)}`;
                    window.location.href = url;
                },

                downloadTopSellingCsv() {
                    const selectedRows = this._getSelectedTopSellingRows();
                    if (selectedRows.length === 0 && this.selectedProducts.length === 0) {
                        alert('Please select at least one product first.');
                        return;
                    }

                    const rows = selectedRows.length > 0 ? selectedRows : this.topSellingProducts;
                    let csv = 'Rank,Product Name,SKU,Qty Sold,Total Amount\n';
                    rows.forEach((p, i) => {
                        csv += `${i + 1},"${(p.product_name || '').replace(/"/g, '""')}","${(p.sku || '').replace(/"/g, '""')}",${p.total_quantity},${p.total_amount}\n`;
                    });
                    const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
                    const link = document.createElement('a');
                    link.setAttribute('href', URL.createObjectURL(blob));
                    link.setAttribute('download', `Top_Selling_Products_${selectedRows.length > 0 ? 'Selected' : 'All'}.csv`);
                    link.style.visibility = 'hidden';
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
                },

                printProductSalesPerformancePdf() {
                    if (!this.selectedProductIdForBreakdown) {
                        alert('Please select a product first.');
                        return;
                    }

                    const params = new URLSearchParams();
                    params.set('product_id', this.selectedProductIdForBreakdown);
                    if (this.breakdownStartDate) params.set('start_date', this.breakdownStartDate);
                    if (this.breakdownEndDate) params.set('end_date', this.breakdownEndDate);
                    params.set('print', 'true');

                    const url = `{{ route('dashboard.product-sales-performance.export.pdf') }}?` + params.toString();
                    window.open(url, '_blank');
                },

                downloadProductSalesPerformancePdf() {
                    if (!this.selectedProductIdForBreakdown) {
                        alert('Please select a product first.');
                        return;
                    }

                    const params = new URLSearchParams();
                    params.set('product_id', this.selectedProductIdForBreakdown);
                    if (this.breakdownStartDate) params.set('start_date', this.breakdownStartDate);
                    if (this.breakdownEndDate) params.set('end_date', this.breakdownEndDate);

                    const url = `{{ route('dashboard.product-sales-performance.export.pdf') }}?` + params.toString();
                    window.location.href = url;
                },

                downloadProductSalesPerformanceCsv() {
                    if (!this.selectedProductIdForBreakdown || !this.productBreakdownData) {
                        alert('Please select a product first.');
                        return;
                    }

                    const data = this.productBreakdownData;
                    const labels = data.labels || [];
                    const quantities = data.quantities || [];
                    const amounts = data.amounts || [];
                    const prodName = data.product_name || 'Product';

                    let csv = `Product Sales Performance Report - ${prodName}\n`;
                    csv += `Date Range:,"${this.breakdownStartDate || 'All Time'} to ${this.breakdownEndDate || 'Present'}"\n`;
                    csv += `Peak Sales Date:,"${data.peak_date || 'N/A'}"\n`;
                    csv += `Peak Day Revenue:,"${data.peak_amount || 0}"\n`;
                    csv += `Cumulative Qty Sold:,"${data.total_quantity || 0}"\n`;
                    csv += `Cumulative Revenue:,"${data.total_amount || 0}"\n\n`;
                    csv += 'Date,Quantity Sold,Sales Amount,Effective Unit Price,Is Peak Day\n';

                    labels.forEach((date, i) => {
                        const qty = quantities[i] || 0;
                        const amt = amounts[i] || 0;
                        const unitPrice = qty > 0 ? (amt / qty).toFixed(2) : '0.00';
                        const isPeak = (data.peak_date && date === data.peak_date) ? 'YES' : 'NO';
                        csv += `"${date}",${qty},${amt},${unitPrice},"${isPeak}"\n`;
                    });

                    const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
                    const link = document.createElement('a');
                    link.setAttribute('href', URL.createObjectURL(blob));
                    link.setAttribute('download', `Product_Sales_Performance_${prodName.replace(/[^a-zA-Z0-9_-]/g, '_')}.csv`);
                    link.style.visibility = 'hidden';
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
                }
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            window.addEventListener('sale-created', function() {
                setTimeout(function() {
                    window.location.reload();
                }, 1000);
            });
        });
    </script>
    @endpush
</x-app-layout>
