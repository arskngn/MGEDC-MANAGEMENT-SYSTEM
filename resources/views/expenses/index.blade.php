<x-app-layout>
    <style media="print">
    html, body {
        background: #ffffff !important;
        color: #374151 !important;
        font-family: DejaVu Sans, Arial, sans-serif !important;
    }

    /* Hide everything by default */
    body * {
        visibility: hidden;
    }
    
    /* Reveal the table container and all its children */
    .overflow-x-auto, .overflow-x-auto * {
        visibility: visible;
    }
    
    /* Position the table container at the top left of the printed page */
    .overflow-x-auto {
        position: absolute;
        left: 0;
        top: 0;
        width: 100% !important;
        min-width: 0 !important;
        background: #ffffff !important;
        margin: 0;
        padding: 0;
        overflow: visible !important;
    }

    table {
        min-width: 0 !important;
        width: 100% !important;
        background: #ffffff !important;
        border-collapse: collapse !important;
    }

    /* Header styling aligned with PDF */
    thead tr {
        background: #ffffff !important;
        color: #6b7280 !important;
    }

    thead th {
        font-size: 8px !important;
        font-weight: bold !important;
        color: #6b7280 !important;
        text-transform: uppercase !important;
        letter-spacing: 0.3px !important;
        padding: 6px 8px !important;
        border-bottom: 1.5px solid #374151 !important;
        background: #ffffff !important;
    }

    tbody td {
        font-size: 9px !important;
        padding: 6px 8px !important;
        color: #374151 !important;
        border-bottom: 1px solid #f3f4f6 !important;
        background: #ffffff !important;
    }

    /* Hide the non-selected rows */
    .print-hidden { 
        display: none !important; 
    }

    /* Hide the Action and Checkbox columns */
    th.checkbox-column, td.checkbox-column,
    th:first-child, td:first-child,
    th:last-child, td:last-child {
        display: none !important;
    }

    /* Ensure backgrounds print correctly */
    * {
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }

    @page { size: portrait; margin: 10mm; }
</style>
    <div
        x-data="{
            selectedItems: [],
            selectAll: false,
            showPrintMessage: false,
            toggleSelectAll() {
                if (this.selectAll) {
                    this.selectedItems = Array.from(document.querySelectorAll('.row-checkbox')).map(cb => cb.value);
                } else {
                    this.selectedItems = [];
                }
            },
            printSelected() {
                if (this.selectedItems.length === 0) {
                    this.showPrintMessage = true;
                    setTimeout(() => { this.showPrintMessage = false; }, 3000);
                    return;
                }
                const ids = this.selectedItems.join(',');
                window.open('{{ route('expenses.export.pdf') }}?ids=' + encodeURIComponent(ids) + '&print=true', '_blank');
            },
            openMoreId: null,
            dropdownTop: '0px',
            dropdownRight: '0px',
            addOpen: false,
            editOpen: false,
            importOpen: false,
            editId: null,
            toggleMore(id, $event) {
                if (this.openMoreId === id) {
                    this.openMoreId = null;
                    return;
                }
                const r = $event.currentTarget.getBoundingClientRect();
                this.dropdownTop = (r.bottom + 4) + 'px';
                this.dropdownRight = (window.innerWidth - r.right) + 'px';
                this.openMoreId = id;
            },
            openEdit(id) {
                this.editId = id;
                this.editOpen = true;
                this.openMoreId = null;
            },
        }"
        @keydown.escape.window="openMoreId = null; addOpen = false; editOpen = false; importOpen = false"
        @keydown.window.ctrl.p.prevent="printSelected()"
        @keydown.window.ctrl.a.prevent="selectAll = true; toggleSelectAll()"
        @scroll.window="if (openMoreId) openMoreId = null"
        class="space-y-4"
    >
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="text-lg sm:text-xl font-bold text-[#0a1233] shrink-0">All Expenses</h2>

            <div class="flex flex-col sm:flex-row sm:items-center gap-2.5 w-full sm:w-auto">
                <form
                    method="GET"
                    action="{{ route('expenses.index') }}"
                    x-data="purchaseDateRange({ dateFrom: @js(request('date_from')), dateTo: @js(request('date_to')) })"
                    x-ref="filterForm"
                    @keydown.escape.window="escapeClose()"
                    class="w-full sm:flex sm:flex-row sm:items-center sm:gap-2 sm:w-auto"
                >
                    <input type="hidden" name="date_from" x-model="dateFrom" />
                    <input type="hidden" name="date_to" x-model="dateTo" />

                    <div class="flex items-center gap-2 sm:contents">
                        {{-- Search --}}
                        <div class="relative flex flex-1 sm:w-[240px] sm:flex-none">
                            <input
                                type="text"
                                name="search"
                                value="{{ request('search') }}"
                                placeholder="Search..."
                                class="w-full pl-3 pr-8 py-2 border border-[#5542ff]/40 rounded-l-xl sm:rounded-l-md focus:outline-none focus:ring-1 focus:ring-[#5542ff] focus:border-[#5542ff] text-sm bg-white placeholder-gray-400"
                            />
                            @if(request('search'))
                                <a href="{{ request()->fullUrlWithQuery(['search' => null]) }}" class="absolute right-9 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 text-xs px-1 h-4 w-4 flex items-center justify-center" title="Clear search">&#x2715;</a>
                            @endif
                            <button type="submit" class="px-3 gradient-bg text-white rounded-r-xl sm:rounded-r-md hover:opacity-90 transition-colors flex items-center justify-center shrink-0" title="Search">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                            </button>
                        </div>

                        @include('components.date-range-picker')
                    </div>
                </form>

                <div class="flex items-center gap-2 shrink-0 justify-end">
                    @if(auth()->user()->hasPermission('Store Expense'))
                        <button type="button" @click="addOpen = true" class="inline-flex items-center justify-center px-3 sm:px-4 py-2 bg-white border border-[#5542ff] text-[#5542ff] rounded-xl sm:rounded-md hover:bg-[#5542ff]/5 transition-colors text-sm font-semibold whitespace-nowrap">
                            <svg class="h-4 w-4 sm:mr-1.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                            <span class="hidden sm:inline">Add New</span>
                            <span class="sm:hidden">Add</span>
                        </button>
                    @endif

                    @if(auth()->user()->hasPermission('Download Expense PDF') || auth()->user()->hasPermission('Download Expense CSV') || auth()->user()->hasPermission('Import Expenses'))
                        <div class="relative">
                            <button type="button" @click="openMoreId === 'action' ? openMoreId = null : openMoreId = 'action'" class="inline-flex items-center px-4 py-2 bg-[#5542ff] border border-[#5542ff] text-white rounded-xl sm:rounded-md hover:bg-[#4535e8] text-sm font-semibold shadow-sm">
                                Action
                                <svg class="h-4 w-4 ml-1 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                            </button>
                            <div
                                x-show="openMoreId === 'action'"
                                @click.outside="openMoreId = null"
                                x-cloak
                                class="absolute right-0 mt-1 w-48 bg-white rounded-lg shadow-lg border border-gray-100 z-[200] py-1"
                            >
                                @if(auth()->user()->hasPermission('Download Expense PDF'))
                                    <a href="{{ route('expenses.export.pdf', request()->query()) }}" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 border-b border-gray-100">
                                        <svg class="h-4 w-4 mr-2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" /></svg>
                                        Download PDF
                                    </a>
                                @endif
                                <button type="button" @click="printSelected()" class="w-full flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 text-left">
                                    <svg class="h-4 w-4 mr-2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" /></svg>
                                    Print Selected
                                </button>
                                @if(auth()->user()->hasPermission('Download Expense CSV'))
                                    <a href="{{ route('expenses.export.csv', request()->query()) }}" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 border-b border-gray-100">
                                        <svg class="h-4 w-4 mr-2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                                        Download CSV
                                    </a>
                                @endif
                                @if(auth()->user()->hasPermission('Import Expenses'))
                                    <button type="button" @click="importOpen = true; openMoreId = null" class="w-full flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                                        <svg class="h-4 w-4 mr-2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>
                                        Import CSV
                                    </button>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Active Filters Chips --}}
        @if(request()->hasAny(['search', 'date_from', 'date_to']))
            <div class="flex flex-wrap items-center gap-2 px-1 py-1">
                <span class="text-xs font-semibold text-gray-500">Active Filters:</span>

                @if(request('search'))
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium bg-[#5542ff]/10 text-[#5542ff] border border-[#5542ff]/20">
                        Search: "{{ request('search') }}"
                        <a href="{{ request()->fullUrlWithQuery(['search' => null]) }}" class="hover:text-red-500 font-bold ml-1" title="Remove">&times;</a>
                    </span>
                @endif

                @if(request('date_from') || request('date_to'))
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium bg-[#5542ff]/10 text-[#5542ff] border border-[#5542ff]/20">
                        Date: {{ request('date_from', '…') }} – {{ request('date_to', '…') }}
                        <a href="{{ request()->fullUrlWithQuery(['date_from' => null, 'date_to' => null]) }}" class="hover:text-red-500 font-bold ml-1" title="Remove">&times;</a>
                    </span>
                @endif

                <a href="{{ route('expenses.index') }}" class="text-xs text-red-600 hover:underline font-semibold ml-1">Reset All Filters</a>
            </div>
        @endif

        @if(session('success'))
            <div class="rounded-lg bg-green-50 border border-green-200 text-green-800 px-4 py-3 text-sm">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="rounded-lg bg-red-50 border border-red-200 text-red-800 px-4 py-3 text-sm">{{ session('error') }}</div>
        @endif

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full border-collapse min-w-[640px]">
                    <thead>
                        <tr class="gradient-bg text-white">
                            <th class="checkbox-column px-4 py-3.5 text-[11px] font-bold uppercase tracking-wide w-10">
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="checkbox" x-model="selectAll" @change="toggleSelectAll" class="rounded border-gray-300 text-[#5542ff] focus:ring-[#5542ff]">
                                    <span class="sr-only">Select All</span>
                                </label>
                            </th>
                            <th class="px-4 py-4 text-[11px] font-bold uppercase tracking-wide text-left">S.N.</th>
                            <th class="px-4 py-4 text-[11px] font-bold uppercase tracking-wide text-left">Reason</th>
                            <th class="px-4 py-4 text-[11px] font-bold uppercase tracking-wide text-left">Date</th>
                            <th class="px-4 py-4 text-[11px] font-bold uppercase tracking-wide text-left">Amount</th>
                            <th class="px-4 py-4 text-[11px] font-bold uppercase tracking-wide text-left">Note</th>
                            <th class="px-4 py-4 text-[11px] font-bold uppercase tracking-wide text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse($expenses as $expense)
                            @php
                                $sn = ($expenses->currentPage() - 1) * $expenses->perPage() + $loop->iteration;
                            @endphp
                            <tr :class="{ 'print-hidden': !selectedItems.includes(String('{{ $expense->id }}')) }" class="hover:bg-gray-50/90 transition-colors bg-white">

                                <td class="checkbox-column px-4 py-3.5 align-middle">
                                    <input type="checkbox" value="{{ $expense->id }}" x-model="selectedItems" class="row-checkbox rounded border-gray-300 text-[#5542ff] focus:ring-[#5542ff]">
                                </td>
                                <td class="px-4 py-4 text-sm text-gray-800 align-middle">{{ $sn }}</td>
                                <td class="px-4 py-4 align-middle text-left">
                                    <span class="text-sm font-semibold text-gray-900">{{ $expense->expenseType->name }}</span>
                                </td>
                                <td class="px-4 py-4 align-middle text-left">
                                    <span class="text-sm text-gray-700">{{ $expense->date->format('d M, Y') }}</span>
                                </td>
                                <td class="px-4 py-4 align-middle text-left">
                                    <span class="text-sm font-semibold text-gray-900">{{ formatCurrency($expense->amount) }}</span>
                                </td>
                                <td class="px-4 py-4 align-middle text-left">
                                    <span class="text-sm text-gray-700">{{ $expense->description ?? '-' }}</span>
                                </td>
                                <td class="px-4 py-4 align-middle text-right">
                                    <div class="flex flex-wrap items-center justify-end gap-2">
                                        @if(auth()->user()->hasPermission('Store Expense'))
                                            <button
                                                type="button"
                                                @click="openEdit({{ $expense->id }})"
                                                class="inline-flex items-center justify-center px-2.5 py-1.5 bg-white border border-[#5542ff] text-[#5542ff] rounded-md hover:bg-[#5542ff]/5 text-[11px] font-bold transition-colors"
                                            >
                                                <svg class="h-3.5 w-3.5 mr-1 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                </svg>
                                                Edit
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-12 text-center text-gray-500">No expenses found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($expenses->hasPages())
                <div class="px-4 py-3 bg-gray-50 border-t border-gray-100">
                    {{ $expenses->links() }}
                </div>
            @endif
        </div>

        {{-- Add expense modal --}}
        <div x-show="addOpen" x-cloak class="fixed inset-0 z-[210] overflow-y-auto" style="display: none;">
            <div class="flex items-center justify-center min-h-screen px-4 py-8">
                <div class="fixed inset-0 bg-gray-900/50 z-0" @click="addOpen = false"></div>
                <div class="relative bg-white rounded-lg shadow-xl max-w-md w-full z-10" @click.stop>
                    <div class="px-6 pt-6 pb-4 border-b border-gray-100">
                        <h3 class="text-base font-semibold text-gray-900">Add New Expense</h3>
                    </div>
                    <form method="POST" action="{{ route('expenses.store') }}" class="px-6 py-6 space-y-4">
                        @csrf
                        <div>
                            <label for="expense_type_id" class="block text-sm font-medium text-gray-700 mb-1">Type <span class="text-red-500">*</span></label>
                            <select
                                id="expense_type_id"
                                name="expense_type_id"
                                required
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-1 focus:ring-[#5542ff] focus:border-[#5542ff] text-sm bg-white"
                            >
                                <option value="">Select One</option>
                                @foreach($expenseTypes as $type)
                                    <option value="{{ $type->id }}">{{ $type->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="date" class="block text-sm font-medium text-gray-700 mb-1">Date of Expense <span class="text-red-500">*</span></label>
                            <input
                                id="date"
                                type="date"
                                name="date"
                                value="{{ now()->format('Y-m-d') }}"
                                required
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-1 focus:ring-[#5542ff] focus:border-[#5542ff] text-sm"
                            />
                        </div>

                        <div>
                            <label for="amount" class="block text-sm font-medium text-gray-700 mb-1">Amount <span class="text-red-500">*</span></label>
                            <div class="relative">
                                <span class="absolute left-3 top-2 text-gray-600 font-medium">{{ currencySymbol() }}</span>
                                <input
                                    id="amount"
                                    type="number"
                                    name="amount"
                                    step="0.01"
                                    min="0"
                                    placeholder="500"
                                    required
                                    class="w-full pl-7 pr-3 py-2 border border-gray-300 rounded-lg focus:ring-1 focus:ring-[#5542ff] focus:border-[#5542ff] text-sm"
                                />
                            </div>
                        </div>

                        <div>
                            <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Note</label>
                            <textarea
                                id="description"
                                name="description"
                                rows="2"
                                placeholder="Utility for Office !"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-1 focus:ring-[#5542ff] focus:border-[#5542ff] text-sm resize-none"
                            ></textarea>
                        </div>

                        <div class="pt-2 border-t border-gray-100 flex justify-end gap-3">
                            <button
                                type="button"
                                @click="addOpen = false"
                                class="px-4 py-2 rounded-lg border border-gray-300 text-gray-700 text-sm font-medium hover:bg-gray-50 transition-colors"
                            >
                                Cancel
                            </button>
                            <button type="submit" class="px-4 py-2 rounded-lg bg-[#5542ff] text-white text-sm font-medium hover:bg-[#4736d6] transition-colors">
                                Submit
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Edit expense modal --}}
        <div x-show="editOpen" x-cloak class="fixed inset-0 z-[210] overflow-y-auto" style="display: none;">
            <div class="flex items-center justify-center min-h-screen px-4 py-8">
                <div class="fixed inset-0 bg-gray-900/50 z-0" @click="editOpen = false"></div>
                <div class="relative bg-white rounded-lg shadow-xl max-w-md w-full z-10" @click.stop>
                    <div class="px-6 pt-6 pb-4 border-b border-gray-100">
                        <h3 class="text-base font-semibold text-gray-900">Edit Expense Info</h3>
                    </div>
                    <form id="editExpenseForm" :action="`/expenses/${editId}`" method="POST" class="px-6 py-6 space-y-4">
                        @csrf
                        @method('PUT')
                        <div>
                            <label for="expense_type_id_edit" class="block text-sm font-medium text-gray-700 mb-1">Type <span class="text-red-500">*</span></label>
                            <select
                                id="expense_type_id_edit"
                                name="expense_type_id"
                                required
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-1 focus:ring-[#5542ff] focus:border-[#5542ff] text-sm bg-white"
                            >
                                <option value="">Select One</option>
                                @foreach($expenseTypes as $type)
                                    <option value="{{ $type->id }}">{{ $type->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="date_edit" class="block text-sm font-medium text-gray-700 mb-1">Date of Expense <span class="text-red-500">*</span></label>
                            <input
                                id="date_edit"
                                type="date"
                                name="date"
                                required
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-1 focus:ring-[#5542ff] focus:border-[#5542ff] text-sm"
                            />
                        </div>

                        <div>
                            <label for="amount_edit" class="block text-sm font-medium text-gray-700 mb-1">Amount <span class="text-red-500">*</span></label>
                            <div class="relative">
                                <span class="absolute left-3 top-2 text-gray-600 font-medium">{{ currencySymbol() }}</span>
                                <input
                                    id="amount_edit"
                                    type="number"
                                    name="amount"
                                    step="0.01"
                                    min="0"
                                    placeholder="500"
                                    required
                                    class="w-full pl-7 pr-3 py-2 border border-gray-300 rounded-lg focus:ring-1 focus:ring-[#5542ff] focus:border-[#5542ff] text-sm"
                                />
                            </div>
                        </div>

                        <div>
                            <label for="description_edit" class="block text-sm font-medium text-gray-700 mb-1">Note</label>
                            <textarea
                                id="description_edit"
                                name="description"
                                rows="2"
                                placeholder="Utility for Office !"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-1 focus:ring-[#5542ff] focus:border-[#5542ff] text-sm resize-none"
                            ></textarea>
                        </div>

                        <div class="pt-2 border-t border-gray-100 flex justify-end gap-3">
                            <button
                                type="button"
                                @click="editOpen = false"
                                class="px-4 py-2 rounded-lg border border-gray-300 text-gray-700 text-sm font-medium hover:bg-gray-50 transition-colors"
                            >
                                Cancel
                            </button>
                            <button type="submit" class="px-4 py-2 rounded-lg bg-[#5542ff] text-white text-sm font-medium hover:bg-[#4736d6] transition-colors">
                                Submit
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Import expense modal --}}
        <div x-show="importOpen" x-cloak class="fixed inset-0 z-[210] overflow-y-auto" style="display: none;">
            <div class="flex items-center justify-center min-h-screen px-4 py-8">
                <div class="fixed inset-0 bg-gray-900/50 z-0" @click="importOpen = false"></div>
                <div class="relative bg-white rounded-xl shadow-xl max-w-lg w-full z-10 border border-gray-100" @click.stop>
                    <div class="flex justify-between items-center px-6 pt-5 pb-3 border-b border-gray-100">
                        <h3 class="text-lg font-bold text-[#0a1233]">Import Expense</h3>
                        <button type="button" @click="importOpen = false" class="text-gray-400 hover:text-gray-600 p-1" aria-label="Close">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                        </button>
                    </div>
                    <form method="POST" action="{{ route('expenses.import') }}" enctype="multipart/form-data" class="px-6 py-4 space-y-4">
                        @csrf
                        <input type="hidden" name="_form" value="import" />
                        
                        <div class="rounded-lg bg-yellow-50 border border-yellow-200 px-4 py-3 text-yellow-800 text-sm space-y-1.5">
                            <ul class="list-disc list-inside space-y-1 text-xs">
                                <li>Format your CSV the same way as the sample file below.</li>
                                <li>Valid fields: <em>expense_type, date_of_expense, amount, note</em></li>
                                <li>All fields are required except note.</li>
                                <li>When an error occurs download the error file and correct the incorrect cells and import that file again through format.</li>
                            </ul>
                        </div>

                        <div>
                            <label for="csv_file" class="block text-sm font-medium text-gray-700 mb-2">Select File <span class="text-red-500">*</span></label>
                            <div class="relative border-2 border-dashed border-gray-300 rounded-lg p-4 hover:border-[#5542ff] transition-colors">
                                <input
                                    id="csv_file"
                                    type="file"
                                    name="csv_file"
                                    accept=".csv"
                                    required
                                    class="hidden"
                                    onchange="document.querySelector('[data-file-name]').textContent = this.files?.[0]?.name || 'No file chosen';"
                                />
                                <label for="csv_file" class="cursor-pointer flex flex-col items-center gap-2">
                                    <svg class="h-6 w-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    <div class="text-sm">
                                        <span class="font-medium text-[#5542ff]">Click to choose file</span>
                                        <span class="text-gray-600"> or drag and drop</span>
                                    </div>
                                    <span class="text-xs text-gray-500">CSV format</span>
                                </label>
                            </div>
                            <p class="mt-2 text-xs text-gray-500" data-file-name>No file chosen</p>
                            @error('csv_file')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="text-sm text-gray-600">
                            <p class="mb-2">Download sample template:</p>
                            <a href="{{ route('expenses.import.sample') }}" class="inline-flex items-center text-[#5542ff] hover:underline font-medium">
                                <svg class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                </svg>
                                expense.csv
                            </a>
                        </div>

                        <div class="pt-2 border-t border-gray-100 pb-1">
                            <button type="submit" class="w-full py-2.5 rounded-lg bg-[#5542ff] text-white text-sm font-semibold hover:bg-[#4736d6] transition-colors">Import</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    <script>
        // Load expense data when edit modal opens
        document.addEventListener('alpine:init', () => {
            const root = document.querySelector('[x-data*="openMoreId"]');
            if (root) {
                const alpineInstance = root.__x_instance;
                
                // Watch for editOpen changes
                const originalOpenEdit = alpineInstance.openEdit;
                alpineInstance.openEdit = function(id) {
                    originalOpenEdit.call(this, id);
                    
                    // Fetch expense data
                    fetch(`/expenses/${id}/edit`)
                        .then(response => response.json())
                        .then(data => {
                            document.querySelector('#expense_type_id_edit').value = data.expense_type_id;
                            document.querySelector('#date_edit').value = data.date;
                            document.querySelector('#amount_edit').value = data.amount;
                            document.querySelector('#description_edit').value = data.description || '';
                            
                            // Update form action
                            document.querySelector('#editExpenseForm').action = `/expenses/${id}`;
                        });
                };
            }
        });
    </script>

        <!-- Print Warning Modal -->
        <div x-show="showPrintMessage" x-cloak class="fixed inset-0 z-[500] flex items-center justify-center">
            <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" @click="showPrintMessage = false"></div>
            <div class="relative bg-white rounded-xl shadow-xl max-w-sm w-full mx-4 p-6 z-10 text-center">
                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-yellow-100 mb-4">
                    <svg class="h-6 w-6 text-yellow-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                </div>
                <h3 class="text-lg font-bold text-gray-900 mb-2">Please select records</h3>
                <p class="text-sm text-gray-500 mb-6">Please select at least one record to print.</p>
                <button @click="showPrintMessage = false" class="w-full bg-[#5542ff] hover:bg-[#4736d6] text-white font-bold py-2 px-4 rounded-lg transition-colors">OK</button>
            </div>
        </div>
    </div>
</x-app-layout>
