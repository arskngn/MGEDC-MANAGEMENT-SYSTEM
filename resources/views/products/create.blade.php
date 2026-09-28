<x-app-layout>
    <div class="max-w-4xl mx-auto space-y-6"
        x-data="{
            hasExpiry: {{ old('has_expiry') ? 'true' : 'false' }},
            requiresBatch: {{ old('requires_batch_tracking', old('has_expiry') ? '1' : '0') ? 'true' : 'false' }},
        }"
    >
        <div class="flex justify-between items-center">
            <h2 class="text-xl font-bold text-[#0a1233] dark:text-white">Add Product</h2>
            <a href="{{ route('products.all') }}" class="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                <svg class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                Back
            </a>
        </div>

        <form action="{{ route('products.store') }}" method="POST" class="bg-gray-50/80 dark:bg-gray-800/60 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 space-y-6">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-ink-body mb-1">Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name') }}" required class="w-full rounded-lg border border-gray-300/80 dark:border-gray-600 bg-gray-100 dark:bg-gray-900 px-3 py-2 text-sm text-ink-heading placeholder:text-gray-400 dark:placeholder:text-gray-500 focus:bg-white dark:focus:bg-gray-800 focus:ring-1 focus:ring-[#5542ff] focus:border-[#5542ff] transition-colors" />
                    @error('name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-ink-body mb-1">Category <span class="text-red-500">*</span></label>
                    <select name="category_id" required class="w-full rounded-lg border border-gray-300/80 dark:border-gray-600 bg-gray-100 dark:bg-gray-900 px-3 py-2 text-sm text-ink-heading focus:bg-white dark:focus:bg-gray-800 focus:ring-1 focus:ring-[#5542ff] focus:border-[#5542ff] transition-colors">
                        <option value="">Select One</option>
                        @foreach($categories as $c)
                            <option value="{{ $c->id }}" @selected(old('category_id') == $c->id)>{{ $c->name }}</option>
                        @endforeach
                    </select>
                    @error('category_id')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-ink-body mb-1">Brand <span class="text-red-500">*</span></label>
                    <select name="brand_id" required class="w-full rounded-lg border border-gray-300/80 dark:border-gray-600 bg-gray-100 dark:bg-gray-900 px-3 py-2 text-sm text-ink-heading focus:bg-white dark:focus:bg-gray-800 focus:ring-1 focus:ring-[#5542ff] focus:border-[#5542ff] transition-colors">
                        <option value="">Select One</option>
                        @foreach($brands as $b)
                            <option value="{{ $b->id }}" @selected(old('brand_id') == $b->id)>{{ $b->name }}</option>
                        @endforeach
                    </select>
                    @error('brand_id')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-ink-body mb-1">SKU <span class="text-red-500">*</span></label>
                    <input type="text" name="sku" value="{{ old('sku') }}" required class="w-full rounded-lg border border-gray-300/80 dark:border-gray-600 bg-gray-100 dark:bg-gray-900 px-3 py-2 text-sm text-ink-heading placeholder:text-gray-400 dark:placeholder:text-gray-500 focus:bg-white dark:focus:bg-gray-800 focus:ring-1 focus:ring-[#5542ff] focus:border-[#5542ff] transition-colors" />
                    @error('sku')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-ink-body mb-1">Unit (UoM) <span class="text-red-500">*</span></label>
                    <select name="unit_id" required class="w-full rounded-lg border border-gray-300/80 dark:border-gray-600 bg-gray-100 dark:bg-gray-900 px-3 py-2 text-sm text-ink-heading focus:bg-white dark:focus:bg-gray-800 focus:ring-1 focus:ring-[#5542ff] focus:border-[#5542ff] transition-colors">
                        <option value="">Select One</option>
                        @foreach($units as $u)
                            <option value="{{ $u->id }}" @selected(old('unit_id') == $u->id)>{{ $u->name }} ({{ $u->short_name }})</option>
                        @endforeach
                    </select>
                    @error('unit_id')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-ink-body mb-1">Alert Quantity <span class="text-red-500">*</span></label>
                    <input type="number" name="alert_quantity" value="{{ old('alert_quantity', 0) }}" step="0.0001" min="0" required class="w-full rounded-lg border border-gray-300/80 dark:border-gray-600 bg-gray-100 dark:bg-gray-900 px-3 py-2 text-sm text-ink-heading focus:bg-white dark:focus:bg-gray-800 focus:ring-1 focus:ring-[#5542ff] focus:border-[#5542ff] transition-colors" />
                    @error('alert_quantity')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-ink-body mb-1">Price <span class="text-red-500">*</span></label>
                    <input type="number" name="fixed_price" value="{{ old('fixed_price', '0.00') }}" step="0.01" min="0" required class="w-full rounded-lg border border-gray-300/80 dark:border-gray-600 bg-gray-100 dark:bg-gray-900 px-3 py-2 text-sm text-ink-heading focus:bg-white dark:focus:bg-gray-800 focus:ring-1 focus:ring-[#5542ff] focus:border-[#5542ff] transition-colors" />
                    @error('fixed_price')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-ink-body mb-1">Note</label>
                    <textarea name="note" rows="4" class="w-full rounded-lg border border-gray-300/80 dark:border-gray-600 bg-gray-100 dark:bg-gray-900 px-3 py-2 text-sm text-ink-heading placeholder:text-gray-400 dark:placeholder:text-gray-500 focus:bg-white dark:focus:bg-gray-800 focus:ring-1 focus:ring-[#5542ff] focus:border-[#5542ff] transition-colors resize-y min-h-[100px]" placeholder="Optional">{{ old('note') }}</textarea>
                    @error('note')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                {{-- Tracking Settings --}}
                <div class="md:col-span-2 pt-4">
                    <div class="flex items-center space-x-2 mb-4">
                        <h3 class="text-md font-semibold text-ink-body">Tracking Settings</h3>
                        <div class="h-px flex-1 bg-gray-200 dark:bg-gray-700"></div>
                    </div>

                    <div class="space-y-4">
                        {{-- Has Expiry --}}
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

                        {{-- Batch Tracking (auto-enabled when has_expiry) --}}
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

                        {{-- Expiry Settings (shown only when has_expiry is checked) --}}
                        <div x-show="hasExpiry" x-collapse class="ml-8 pl-4 border-l-2 border-[#5542ff]/20 dark:border-[#5542ff]/30 space-y-4 pt-2">
                            <div>
                                <label class="block text-sm font-medium text-ink-body mb-1">
                                    Shelf Life
                                    <span class="text-xs text-gray-400 font-normal">(days)</span>
                                </label>
                                <input type="number" name="shelf_life_days" value="{{ old('shelf_life_days') }}" min="1" placeholder="e.g. 365"
                                    class="w-full max-w-xs rounded-lg border border-gray-300/80 dark:border-gray-600 bg-gray-100 dark:bg-gray-900 px-3 py-2 text-sm text-ink-heading focus:bg-white dark:focus:bg-gray-800 focus:ring-1 focus:ring-[#5542ff] focus:border-[#5542ff] transition-colors" />
                                <p class="text-xs text-ink-muted mt-1">Optional. If set, expiry date auto-calculates from manufacture date.</p>
                                @error('shelf_life_days')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-ink-body mb-1">
                                    Alert Before Expiry
                                    <span class="text-xs text-gray-400 font-normal">(days)</span>
                                </label>
                                <input type="number" name="expiry_alert_days" value="{{ old('expiry_alert_days', 30) }}" min="1" placeholder="30"
                                    class="w-full max-w-xs rounded-lg border border-gray-300/80 dark:border-gray-600 bg-gray-100 dark:bg-gray-900 px-3 py-2 text-sm text-ink-heading focus:bg-white dark:focus:bg-gray-800 focus:ring-1 focus:ring-[#5542ff] focus:border-[#5542ff] transition-colors" />
                                <p class="text-xs text-ink-muted mt-1">System will send notification this many days before the product expires.</p>
                                @error('expiry_alert_days')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
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
</x-app-layout>
