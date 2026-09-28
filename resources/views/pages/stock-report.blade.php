<x-app-layout>
    <style media="print">
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
        width: 100%;
        margin: 0;
        padding: 0;
    }

    /* Hide the non-selected rows */
    .print-hidden { 
        display: none !important; 
    }

    /* Hide the Action and Checkbox columns */
    th:first-child, td:first-child {
        display: none !important;
    }

    /* Ensure backgrounds print correctly */
    * {
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }

    @page { size: auto; margin: 10mm; }
</style>
    <div x-data="{
            selectedItems: [],
            selectAll: false,
            showPrintMessage: false,
            bulkOpen: false,
            actionTop: '0px',
            actionRight: '0px',
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
                window.print();
            },
            toggleAction($event) {
                this.bulkOpen = !this.bulkOpen;
                if (this.bulkOpen) {
                    const r = $event.currentTarget.getBoundingClientRect();
                    this.actionTop = (r.bottom + 4) + 'px';
                    this.actionRight = (window.innerWidth - r.right) + 'px';
                }
            }
        }"
        @keydown.escape.window="bulkOpen = false"
        @keydown.window.ctrl.p.prevent="printSelected()"
        @keydown.window.ctrl.a.prevent="selectAll = true; toggleSelectAll()"
        @scroll.window="if (bulkOpen) bulkOpen = false"
        class="space-y-4">
        <!-- Header with Title and Action Button (Hidden until filtered) -->
        <div class="flex justify-between items-center">
            <h2 class="text-2xl font-bold text-gray-900">Product Stock Report</h2>
            
            <!-- Action Dropdown - Only visible when results are shown -->
            @if($stockData->isNotEmpty())
                <div class="inline-flex">
                    <button type="button" @click.stop="toggleAction($event)" class="inline-flex items-center px-4 py-2 bg-[#5542ff] border border-[#5542ff] text-white rounded-md hover:bg-[#4535e8] text-sm font-semibold shadow-sm">
                        Action
                        <svg class="h-4 w-4 ml-2 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <template x-teleport="body">
                        <div
                            x-show="bulkOpen"
                            @click.outside="bulkOpen = false"
                            x-cloak
                            class="fixed w-52 bg-white rounded-lg shadow-xl border border-gray-100 py-1 z-[200]"
                            :style="{ top: actionTop, right: actionRight }"
                        >
                            @if(auth()->user()->hasPermission('Download Stock Report PDF'))
                                <a href="{{ route('reports.stock.export', ['format' => 'pdf', ...request()->query()]) }}" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 border-b">
                                    <svg class="h-4 w-4 mr-2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                    </svg>
                                    Download PDF
                                </a>
                            @endif
                            <button type="button" @click="printSelected()" class="w-full flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 text-left">
                                <svg class="h-4 w-4 mr-2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" /></svg>
                                Print Selected
                            </button>
                            @if(auth()->user()->hasPermission('Download Stock Report CSV'))
                                <a href="{{ route('reports.stock.export', ['format' => 'csv', ...request()->query()]) }}" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                                    <svg class="h-4 w-4 mr-2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    Download CSV
                                </a>
                            @endif
                        </div>
                    </template>
                </div>
            @endif
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="text-sm text-gray-700">
                The stock report shows the current total stock for all products. Warehouse-specific filtering has been removed.
            </div>
        </div>

        <!-- Results Table -->
        @if($stockData->isNotEmpty())
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-blue-600 text-white">
                            <th class="checkbox-column px-6 py-4 text-left font-semibold w-10">
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="checkbox" x-model="selectAll" @change="toggleSelectAll" class="rounded border-gray-300 text-[#5542ff] focus:ring-[#5542ff]">
                                    <span class="sr-only">Select All</span>
                                </label>
                            </th>
                                            <th class="px-6 py-4 text-left font-semibold">S.N</th>
                                    <th class="px-6 py-4 text-left font-semibold">Name</th>
                                    <th class="px-6 py-4 text-left font-semibold">SKU</th>
                                    <th class="px-6 py-4 text-left font-semibold">Category</th>
                                    <th class="px-6 py-4 text-left font-semibold">Brand</th>
                                    <th class="px-6 py-4 text-right font-semibold">Stock</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach($stockData as $index => $item)
                                <tr :class="{ 'print-hidden': !selectedItems.includes(String($index)) }" class="hover:bg-gray-50 transition-colors">
                                    <td class="checkbox-column px-6 py-4 align-middle">
                                        <input type="checkbox" value="{{ $index }}" x-model="selectedItems" class="row-checkbox rounded border-gray-300 text-[#5542ff] focus:ring-[#5542ff]">
                                    </td>
                                    <td class="px-6 py-4 font-semibold text-gray-900">{{ $index + 1 }}</td>
                                        <td class="px-6 py-4 font-medium text-gray-900">{{ $item['product_name'] }}</td>
                                        <td class="px-6 py-4 text-gray-700">{{ $item['sku'] }}</td>
                                        <td class="px-6 py-4 text-gray-700">
                                            @php
                                                $product = \App\Models\Product::find($item['product_id']);
                                            @endphp
                                            {{ $product?->category?->name ?? '-' }}
                                        </td>
                                        <td class="px-6 py-4 text-gray-700">
                                            {{ $product?->brand?->name ?? '-' }}
                                        </td>
                                        <td class="px-6 py-4 text-right font-semibold text-gray-900">
                                            {{ number_format($item['stock_quantity'], 0) }} {{ $item['unit'] }}
                                        </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @else
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-12">
                <div class="text-center">
                    <svg class="w-12 h-12 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 21l-4.35-4.35M11 19a8 8 0 100-16 8 8 0 000 16z" />
                    </svg>
                    <p class="text-gray-500 font-medium">No stock data available at this time.</p>
                </div>
            </div>
        @endif

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
