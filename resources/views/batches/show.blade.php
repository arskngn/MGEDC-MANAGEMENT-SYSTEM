<x-app-layout>
    <div
        class="space-y-6"
        x-data="{
            statusModalOpen: false,
            selectedStatus: '{{ $batch->status }}',
            editOpen: false,
            disposeOpen: false,
            disposeQty: 0,
            maxDisposeQty: {{ $batch->quantity_available }},
        }"
        @keydown.escape.window="statusModalOpen = false; editOpen = false; disposeOpen = false"
    >
        {{-- Page Header --}}
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <a href="{{ route('batches.index') }}" class="p-2 bg-white border border-gray-200 rounded-lg text-gray-400 hover:text-[#5542ff] hover:border-[#5542ff] transition-all">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                </a>
                <div>
                    <h2 class="text-xl font-bold text-[#0a1233]">Batch Details: {{ $batch->batch_number }}</h2>
                    <p class="text-sm text-gray-500">Product: <span class="font-semibold text-[#5542ff]">{{ $batch->product->name }}</span></p>
                </div>
            </div>

            @php
                $statusClasses = [
                    'active'      => 'bg-green-100 text-green-800 border-green-200',
                    'expired'     => 'bg-red-100 text-red-800 border-red-200',
                    'depleted'    => 'bg-gray-100 text-gray-800 border-gray-200',
                    'hold'        => 'bg-amber-100 text-amber-800 border-amber-200',
                    'quarantined' => 'bg-purple-100 text-purple-800 border-purple-200',
                ];
            @endphp
            <span class="px-4 py-1.5 rounded-full text-sm font-bold border {{ $statusClasses[$batch->status] ?? $statusClasses['active'] }}">
                {{ ucfirst($batch->status) }}
            </span>
        </div>

        {{-- Three Column Cards --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- Inventory Overview Card --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-4">
                <h3 class="text-sm font-bold text-gray-400 uppercase tracking-wider">Inventory Overview</h3>

                <div class="grid grid-cols-2 gap-4">
                    <div class="p-4 bg-blue-50 rounded-lg border border-blue-100">
                        <p class="text-xs text-blue-600 font-bold uppercase">Available</p>
                        <p class="text-2xl font-black text-blue-800">{{ number_format($batch->quantity_available, 2) }}</p>
                    </div>
                    <div class="p-4 bg-purple-50 rounded-lg border border-purple-100">
                        <p class="text-xs text-purple-600 font-bold uppercase">Total Received</p>
                        <p class="text-2xl font-black text-purple-800">{{ number_format($batch->quantity, 2) }}</p>
                    </div>
                </div>

                <div class="space-y-3 pt-2">
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Quantity Sold</span>
                        <span class="font-bold text-gray-800">{{ number_format($batch->quantity_sold, 2) }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Quantity Returned</span>
                        <span class="font-bold text-gray-800">{{ number_format($batch->quantity_returned, 2) }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Quantity Adjusted</span>
                        <span class="font-bold text-gray-800">{{ number_format($batch->quantity_adjusted, 2) }}</span>
                    </div>
                </div>

                <div class="pt-4 border-t border-gray-100">
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        <div class="bg-[#5542ff] h-2 rounded-full" style="width: {{ $batch->percentageUsed() }}%"></div>
                    </div>
                    <p class="text-[10px] text-gray-400 mt-1 font-bold uppercase tracking-tighter">{{ number_format($batch->percentageUsed(), 1) }}% Utilized</p>
                </div>
            </div>

            {{-- Logistics & Storage Card --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-4">
                <h3 class="text-sm font-bold text-gray-400 uppercase tracking-wider">Logistics &amp; Storage</h3>

                <div class="space-y-4">
                    <div class="flex items-start gap-3">
                        <div class="p-2 bg-gray-50 rounded-lg text-gray-400">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                        </div>
                        <div>
                            <p class="text-xs text-gray-400 font-bold uppercase">Important Dates</p>
                            <div class="grid grid-cols-2 gap-x-6 gap-y-2 mt-1">
                                <div>
                                    <p class="text-[10px] text-gray-400 font-bold">CREATED</p>
                                    <p class="text-xs font-bold text-gray-800">{{ $batch->created_at?->format('M d, Y') ?? '—' }}</p>
                                </div>
                                @if($batch->expiry_date)
                                <div>
                                    <p class="text-[10px] text-gray-400 font-bold">EXPIRY</p>
                                    <p class="text-xs font-bold text-gray-800">{{ $batch->expiry_date->format('M d, Y') }}</p>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-4">
                <h3 class="text-sm font-bold text-gray-400 uppercase tracking-wider">Quick Actions</h3>

                <div class="grid grid-cols-1 gap-3">
                    {{-- Edit Batch Info --}}
                    <button
                        type="button"
                        @click="editOpen = true"
                        class="flex items-center justify-center gap-2 px-4 py-2.5 bg-white border border-gray-200 text-gray-700 rounded-lg font-bold hover:bg-gray-50 transition-all text-sm"
                    >
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                        Edit Batch Info
                    </button>

                    {{-- Stock Disposal --}}
                    @if($batch->quantity_available > 0)
                    <button
                        type="button"
                        @click="maxDisposeQty = {{ $batch->quantity_available }}; disposeOpen = true"
                        class="flex items-center justify-center gap-2 px-4 py-2.5 bg-orange-600 text-white rounded-lg font-bold hover:bg-orange-700 transition-all text-sm shadow-sm"
                    >
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                        Stock Disposal
                    </button>
                    @else
                    <button
                        type="button"
                        disabled
                        title="No available stock to dispose"
                        class="flex items-center justify-center gap-2 px-4 py-2.5 bg-gray-100 text-gray-400 rounded-lg font-bold text-sm cursor-not-allowed"
                    >
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                        Stock Disposal
                    </button>
                    @endif

                    @if($batch->purchase_id)
                    <a href="{{ route('purchases.edit', $batch->purchase_id) }}" class="flex items-center justify-center gap-2 px-4 py-2.5 bg-blue-50 text-blue-700 rounded-lg font-bold hover:bg-blue-100 transition-all text-sm">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" /></svg>
                        View Original Purchase
                    </a>
                    @endif
                </div>
            </div>
        </div>

        {{-- Stock Transaction Logs --}}
        <div class="space-y-4">
            <h3 class="text-sm font-bold text-gray-400 uppercase tracking-wider">Stock Transaction Logs</h3>

            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <table class="w-full text-left">
                    <thead class="bg-gray-50 border-b border-gray-100">
                        <tr>
                            <th class="px-6 py-3 text-[10px] font-black text-gray-400 uppercase tracking-widest">Date</th>
                            <th class="px-6 py-3 text-[10px] font-black text-gray-400 uppercase tracking-widest">Type</th>
                            <th class="px-6 py-3 text-[10px] font-black text-gray-400 uppercase tracking-widest text-center">Qty Change</th>
                            <th class="px-6 py-3 text-[10px] font-black text-gray-400 uppercase tracking-widest text-center">Balance</th>
                            <th class="px-6 py-3 text-[10px] font-black text-gray-400 uppercase tracking-widest">Reference</th>
                            <th class="px-6 py-3 text-[10px] font-black text-gray-400 uppercase tracking-widest">Notes</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($batch->stockLedgerEntries as $entry)
                        <tr class="hover:bg-gray-50/50 transition-colors">
                            <td class="px-6 py-4 text-sm text-gray-600">{{ $entry->created_at->format('M d, Y') }}</td>
                            <td class="px-6 py-4">
                                @php
                                    $typeClasses = [
                                        'received'        => 'text-green-600',
                                        'sold'            => 'text-blue-600',
                                        'returned'        => 'text-purple-600',
                                        'adjusted'        => 'text-orange-600',
                                        'expired_writeoff'=> 'text-red-600',
                                        'purchase_return' => 'text-amber-600',
                                    ];
                                @endphp
                                <span class="text-xs font-black uppercase {{ $typeClasses[$entry->transaction_type] ?? 'text-gray-600' }}">
                                    {{ str_replace('_', ' ', $entry->transaction_type) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm font-bold text-center {{ $entry->quantity_change > 0 ? 'text-green-600' : 'text-red-600' }}">
                                {{ ($entry->quantity_change > 0 ? '+' : '') . number_format($entry->quantity_change, 2) }}
                            </td>
                            <td class="px-6 py-4 text-sm font-mono text-center text-gray-700">
                                {{ number_format($entry->running_balance, 2) }}
                            </td>
                            <td class="px-6 py-4 text-xs text-gray-400 font-mono">
                                {{ $entry->reference_type ? class_basename($entry->reference_type) . ' #' . $entry->reference_id : '—' }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500">{{ $entry->notes ?? '—' }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-gray-400 italic">No transaction logs recorded yet.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- ===================== MODALS ===================== --}}

        {{-- Edit Batch Info Modal --}}
        <div x-show="editOpen" x-cloak class="fixed inset-0 z-[210] overflow-y-auto" style="display: none;">
            <div class="flex items-center justify-center min-h-screen px-4 py-8">
                <div class="fixed inset-0 bg-gray-900/50 z-0" @click="editOpen = false"></div>
                <div class="relative bg-white rounded-xl shadow-xl max-w-lg w-full p-6 z-10 border border-gray-100" @click.stop>
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-lg font-bold text-[#0a1233]">Edit Batch Info</h3>
                        <button type="button" @click="editOpen = false" class="text-gray-400 hover:text-gray-600">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                        </button>
                    </div>
                    <form action="{{ route('batches.update', $batch) }}" method="POST" class="space-y-4">
                        @csrf
                        @method('PUT')

                        <div class="space-y-1">
                            <label class="text-xs font-bold text-gray-700 uppercase">Batch Number</label>
                            <input type="text" name="batch_number" value="{{ $batch->batch_number }}" required
                                class="w-full text-sm border-gray-300 rounded-md focus:ring-[#5542ff] focus:border-[#5542ff]">
                        </div>

                        <div class="space-y-1">
                            <label class="text-xs font-bold text-gray-700 uppercase">Status</label>
                            <select name="status" required class="w-full text-sm border-gray-300 rounded-md focus:ring-[#5542ff] focus:border-[#5542ff]">
                                @foreach(\App\Models\ProductBatch::getStatusOptions() as $value => $label)
                                    <option value="{{ $value }}" {{ $batch->status === $value ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="space-y-1">
                            <label class="text-xs font-bold text-gray-700 uppercase">Expiry Date</label>
                            <input type="date" name="expiry_date" min="{{ date('Y-m-d') }}" value="{{ $batch->expiry_date?->format('Y-m-d') }}"
                                class="w-full text-sm border-gray-300 rounded-md focus:ring-[#5542ff] focus:border-[#5542ff]">
                        </div>

                        <div class="pt-4">
                            <button type="submit" class="w-full py-2.5 bg-[#5542ff] text-white rounded-md font-bold hover:bg-[#4736d6] transition-colors shadow-sm">
                                Save Changes
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Stock Disposal Modal --}}
        <div x-show="disposeOpen" x-cloak class="fixed inset-0 z-[210] overflow-y-auto" style="display: none;">
            <div class="flex items-center justify-center min-h-screen px-4 py-8">
                <div class="fixed inset-0 bg-gray-900/50 z-0" @click="disposeOpen = false"></div>
                <div class="relative bg-white rounded-xl shadow-xl max-w-lg w-full p-6 z-10 border border-gray-100" @click.stop>
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-lg font-bold text-[#0a1233]">Stock Disposal / Adjustment</h3>
                        <button type="button" @click="disposeOpen = false" class="text-gray-400 hover:text-gray-600">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                        </button>
                    </div>
                    <form action="{{ route('batches.dispose', $batch) }}" method="POST" class="space-y-4">
                        @csrf

                        <div class="space-y-1">
                            <label class="text-xs font-bold text-gray-700 uppercase">Quantity to Dispose</label>
                            <div class="relative">
                                <input type="number" name="quantity_disposed" required step="0.01" min="0.01"
                                    :max="maxDisposeQty" x-model="disposeQty"
                                    class="w-full text-sm border-gray-300 rounded-md focus:ring-[#5542ff] focus:border-[#5542ff]">
                                <span class="absolute right-3 top-2 text-xs text-gray-400">Max: <span x-text="maxDisposeQty"></span></span>
                            </div>
                        </div>

                        <div class="space-y-1">
                            <label class="text-xs font-bold text-gray-700 uppercase">Reason Type</label>
                            <select name="disposal_type" required class="w-full text-sm border-gray-300 rounded-md focus:ring-[#5542ff] focus:border-[#5542ff]">
                                <option value="expired">Expired Stock</option>
                                <option value="damaged_in_storage">Damaged in Storage</option>
                                <option value="spoiled">Spoiled / Degraded</option>
                                <option value="recall">Batch Recall</option>
                            </select>
                        </div>

                        <div class="space-y-1">
                            <label class="text-xs font-bold text-gray-700 uppercase">Disposal Method</label>
                            <input type="text" name="disposal_method" placeholder="e.g. Incineration, Return to Supplier"
                                class="w-full text-sm border-gray-300 rounded-md focus:ring-[#5542ff] focus:border-[#5542ff]">
                        </div>

                        <div class="space-y-1">
                            <label class="text-xs font-bold text-gray-700 uppercase">Reason Notes</label>
                            <textarea name="reason_notes" rows="3"
                                class="w-full text-sm border-gray-300 rounded-md focus:ring-[#5542ff] focus:border-[#5542ff]"></textarea>
                        </div>

                        <div class="pt-4">
                            <button type="submit" class="w-full py-2.5 bg-orange-600 text-white rounded-md font-bold hover:bg-orange-700 transition-colors shadow-sm">
                                Confirm Disposal
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>
</x-app-layout>
