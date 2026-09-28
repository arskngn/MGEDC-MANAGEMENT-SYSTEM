<x-app-layout>
    <div class="space-y-4">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <h2 class="text-2xl font-bold text-gray-900">{{ $title }}</h2>
        </div>

        <!-- Filters (aligned to Sales Reports filter card design) -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xs border border-gray-100 dark:border-gray-700/60 p-4 sm:p-5">
            <form id="filter-form" action="" method="GET" class="flex flex-wrap items-center gap-3"
                x-data="purchaseDateRange({ dateFrom: @js(request('date_from')), dateTo: @js(request('date_to')) })"
                x-ref="filterForm"
            >
                <input type="hidden" name="date_from" x-model="dateFrom" />
                <input type="hidden" name="date_to" x-model="dateTo" />
                <!-- Type Filter -->
                <div class="relative shrink-0 w-full sm:w-auto">
                    <select name="type" onchange="this.form.submit()"
                            class="text-sm border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-900 pl-3 pr-8 py-2 text-gray-700 dark:text-gray-200 focus:ring-1 focus:ring-[#5542ff] focus:border-[#5542ff] appearance-none cursor-pointer w-full sm:w-[180px]">
                        <option value="">All Types</option>
                        <option value="CREATED" @selected(request('type') === 'CREATED')>Created</option>
                        <option value="UPDATED" @selected(request('type') === 'UPDATED')>Updated</option>
                        <option value="DELETED" @selected(request('type') === 'DELETED')>Deleted</option>
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
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" @selected(request('by') == $user->id)>{{ $user->name }}</option>
                        @endforeach
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-2.5 text-ink-muted">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </div>
                </div>

                <!-- Date Range Picker (using shared component — same as Sales) -->
                <div class="shrink-0" @keydown.escape.window="escapeClose()">
                    @include('components.date-range-picker')
                </div>

                <!-- Sort Toggle (matches Sales pattern) -->
                <input type="hidden" name="sort" id="sort-input" value="{{ request('sort', 'desc') }}">
                <button type="button" onclick="toggleSort()"
                        class="inline-flex items-center justify-center h-[38px] w-[38px] border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-900 hover:bg-gray-50 dark:hover:bg-gray-750 transition-colors shrink-0"
                        title="Toggle Sort Order">
                    @if(request('sort', 'desc') === 'asc')
                        <svg class="h-4 w-4 text-ink-muted" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4h13M3 8h9m-9 4h6m4 0l4-4m0 0l4 4m-4-4v12m0 0l-4-4m4 4l4-4" />
                        </svg>
                    @else
                        <svg class="h-4 w-4 text-ink-muted" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4h13M3 8h9m-9 4h9m5-4v12m0 0l-4-4m4 4l4-4" />
                        </svg>
                    @endif
                </button>
            </form>

            {{-- Active Filters Chips (matching Sales reports pattern) --}}
            @if(request()->hasAny(['type', 'by', 'date_from', 'date_to']) || request('sort') && request('sort') !== 'desc')
                <div class="flex flex-wrap items-center gap-2 mt-3 pt-3 border-t border-gray-100 dark:border-gray-700/60">
                    <span class="text-xs font-semibold text-ink-muted">Active Filters:</span>

                    @if(request('type'))
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium bg-[#5542ff]/10 text-[#5542ff] dark:text-indigo-300 border border-[#5542ff]/20">
                            Type: {{ request('type') }}
                            <a href="{{ request()->fullUrlWithQuery(['type' => null]) }}" class="hover:text-red-500 font-bold ml-1" title="Remove">&times;</a>
                        </span>
                    @endif

                    @if(request('by'))
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium bg-[#5542ff]/10 text-[#5542ff] dark:text-indigo-300 border border-[#5542ff]/20">
                            By: {{ $users->firstWhere('id', request('by'))?->name ?? 'User #'.request('by') }}
                            <a href="{{ request()->fullUrlWithQuery(['by' => null]) }}" class="hover:text-red-500 font-bold ml-1" title="Remove">&times;</a>
                        </span>
                    @endif

                    @if(request('date_from') || request('date_to'))
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium bg-[#5542ff]/10 text-[#5542ff] dark:text-indigo-300 border border-[#5542ff]/20">
                            Date: {{ request('date_from', '…') }} – {{ request('date_to', '…') }}
                            <a href="{{ request()->fullUrlWithQuery(['date_from' => null, 'date_to' => null]) }}" class="hover:text-red-500 font-bold ml-1" title="Remove">&times;</a>
                        </span>
                    @endif

                    @if(request('sort') && request('sort') !== 'desc')
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium bg-[#5542ff]/10 text-[#5542ff] dark:text-indigo-300 border border-[#5542ff]/20">
                            Sort: {{ strtoupper(request('sort')) }}
                            <a href="{{ request()->fullUrlWithQuery(['sort' => null]) }}" class="hover:text-red-500 font-bold ml-1" title="Remove">&times;</a>
                        </span>
                    @endif

                    <a href="{{ route(request()->route()->getName()) }}" class="text-xs text-red-600 dark:text-red-400 hover:underline font-semibold ml-1">Reset All Filters</a>
                </div>
            @endif
        </div>

        <!-- Activity Logs Table -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xs border border-gray-100 dark:border-gray-700/60 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="gradient-bg text-white">
                            @switch($reportType)
                                @case('purchase')
                                @case('purchase-return')
                                @case('sale')
                                @case('sale-return')
                                    <th class="px-6 py-4 text-left font-semibold">Invoice No.</th>
                                @break
                                @case('product')
                                    <th class="px-6 py-4 text-left font-semibold">Product Name</th>
                                    <th class="px-6 py-4 text-left font-semibold">SKU</th>
                                @break
                                @case('customer')
                                    <th class="px-6 py-4 text-left font-semibold">Name</th>
                                    <th class="px-6 py-4 text-left font-semibold">Mobile</th>
                                @break
                                @case('customer-payment')
                                    <th class="px-6 py-4 text-left font-semibold">TRX No.</th>
                                    <th class="px-6 py-4 text-left font-semibold">Customer</th>
                                    <th class="px-6 py-4 text-left font-semibold">Amount</th>
                                @break
                                @case('supplier')
                                    <th class="px-6 py-4 text-left font-semibold">Name</th>
                                    <th class="px-6 py-4 text-left font-semibold">Mobile</th>
                                @break
                                @case('supplier-payment')
                                    <th class="px-6 py-4 text-left font-semibold">TRX No.</th>
                                    <th class="px-6 py-4 text-left font-semibold">Supplier</th>
                                    <th class="px-6 py-4 text-left font-semibold">Amount</th>
                                @break
                                @case('adjustment')
                                @case('transfer')
                                    <th class="px-6 py-4 text-left font-semibold">Tracking No.</th>
                                @break
                                @case('expense')
                                    <th class="px-6 py-4 text-left font-semibold">Expense Reason</th>
                                    <th class="px-6 py-4 text-left font-semibold">Amount</th>
                                @break
                            @endswitch
                            <th class="px-6 py-4 text-left font-semibold">Type</th>
                            <th class="px-6 py-4 text-left font-semibold">By</th>
                            <th class="px-6 py-4 text-left font-semibold">Time</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse($activities as $index => $activity)
                            <tr class="hover:bg-gray-50 transition-colors">
                                @switch($reportType)
                                    @case('purchase')
                                    @case('sale')
                                        @php
                                            $model = $models[$activity->model_id] ?? null;
                                        @endphp
                                        <td class="px-6 py-4 text-blue-600 font-medium">
                                            {{ $model?->invoice_no ?? 'N/A' }}
                                        </td>
                                    @break
                                    @case('purchase-return')
                                    @case('sale-return')
                                        @php
                                            $model = $models[$activity->model_id] ?? null;
                                        @endphp
                                        <td class="px-6 py-4 text-blue-600 font-medium">
                                            {{ $model?->return_invoice_no ?? $model?->invoice_no ?? 'N/A' }}
                                        </td>
                                    @break
                                    @case('product')
                                        @php
                                            $model = $models[$activity->model_id] ?? null;
                                        @endphp
                                        <td class="px-6 py-4 text-gray-700">
                                            {{ $model?->name ?? 'N/A' }}
                                        </td>
                                        <td class="px-6 py-4 text-gray-700">
                                            {{ $model?->sku ?? 'N/A' }}
                                        </td>
                                    @break
                                    @case('customer')
                                        @php
                                            $model = $models[$activity->model_id] ?? null;
                                        @endphp
                                        <td class="px-6 py-4 text-gray-700">
                                            {{ $model?->name ?? 'N/A' }}
                                        </td>
                                        <td class="px-6 py-4 text-gray-700">
                                            {{ $model?->phone ?? $model?->mobile ?? 'N/A' }}
                                        </td>
                                    @break
                                    @case('customer-payment')
                                        @php
                                            $model = $models[$activity->model_id] ?? null;
                                        @endphp
                                        <td class="px-6 py-4 text-blue-600 font-medium">
                                            #{{ $model?->id ?? 'N/A' }}
                                        </td>
                                        <td class="px-6 py-4 text-gray-700">
                                            {{ $model?->sale?->customer?->name ?? 'N/A' }}
                                        </td>
                                        <td class="px-6 py-4 text-right text-gray-700 font-medium">
                                            {{ number_format($model?->amount ?? 0, 2) }}
                                        </td>
                                    @break
                                    @case('supplier')
                                        @php
                                            $model = $models[$activity->model_id] ?? null;
                                        @endphp
                                        <td class="px-6 py-4 text-gray-700">
                                            {{ $model?->name ?? 'N/A' }}
                                        </td>
                                        <td class="px-6 py-4 text-gray-700">
                                            {{ $model?->phone ?? $model?->mobile ?? 'N/A' }}
                                        </td>
                                    @break
                                    @case('supplier-payment')
                                        @php
                                            $model = $models[$activity->model_id] ?? null;
                                        @endphp
                                        <td class="px-6 py-4 text-blue-600 font-medium">
                                            #{{ $model?->id ?? 'N/A' }}
                                        </td>
                                        <td class="px-6 py-4 text-gray-700">
                                            {{ $model?->purchase?->supplier?->name ?? 'N/A' }}
                                        </td>
                                        <td class="px-6 py-4 text-right text-gray-700 font-medium">
                                            {{ number_format($model?->amount ?? 0, 2) }}
                                        </td>
                                    @break
                                    @case('adjustment')
                                    @case('transfer')
                                        @php
                                            $model = $models[$activity->model_id] ?? null;
                                        @endphp
                                        <td class="px-6 py-4 text-blue-600 font-medium">
                                            {{ $model?->tracking_no ?? 'N/A' }}
                                        </td>
                                    @break
                                    @case('expense')
                                        @php
                                            $model = $models[$activity->model_id] ?? null;
                                        @endphp
                                        <td class="px-6 py-4 text-gray-700">
                                            {{ $model?->description ?? $model?->reason ?? $model?->expenseType?->name ?? 'N/A' }}
                                        </td>
                                        <td class="px-6 py-4 text-right text-gray-700 font-medium">
                                            {{ number_format($model?->amount ?? 0, 2) }}
                                        </td>
                                    @break
                                @endswitch
                                
                                <td class="px-6 py-4 text-gray-700">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold {{ strtoupper($activity->action) === 'CREATED' ? 'bg-green-100 text-green-800' : (strtoupper($activity->action) === 'UPDATED' ? 'bg-blue-100 text-blue-800' : 'bg-red-100 text-red-800') }}">
                                        {{ strtoupper($activity->action) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-gray-700">
                                    {{ $activity->user?->name ?? 'System' }}
                                </td>
                                <td class="px-6 py-4 text-gray-700">
                                    {{ formatDateTime($activity->created_at) }}
                                </td>
                            </tr>
                        @empty
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
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($activities->hasPages())
                <div class="px-4 py-3 bg-gray-50 border-t border-gray-100">
                    {{ $activities->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>

<script>
function toggleSort() {
    const sortInput = document.getElementById('sort-input');
    sortInput.value = sortInput.value === 'asc' ? 'desc' : 'asc';
    document.getElementById('filter-form').submit();
}
</script>
