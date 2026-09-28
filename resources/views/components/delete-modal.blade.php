{{-- 
    Reusable Delete Confirmation Modal
    
    Usage: Include once per page, then trigger via Alpine.js:
    
    In x-data: 
        deleteOpen: false,
        deleteAction: '',
        deleteName: '',
        openDelete(action, name) {
            this.deleteAction = action;
            this.deleteName = name;
            this.deleteOpen = true;
        },
    
    On delete button:
        @click="openDelete('{{ route('xxx.destroy', $item) }}', '{{ $item->name }}')"
--}}

<div x-show="deleteOpen" x-cloak class="fixed inset-0 z-[220] overflow-y-auto" style="display: none;">
    <div class="flex items-center justify-center min-h-screen px-4 py-8">
        <div class="fixed inset-0 bg-gray-900/50 z-0" @click="deleteOpen = false"></div>
        <div class="relative bg-white dark:bg-gray-800 rounded-xl shadow-xl max-w-sm w-full z-10 border border-gray-100 dark:border-gray-700" @click.stop
             x-transition:enter="ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="ease-in duration-150"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95">
            <div class="px-6 pt-6 pb-2 text-center">
                <div class="mx-auto flex items-center justify-center h-14 w-14 rounded-full bg-red-100 dark:bg-red-900/30 mb-4">
                    <svg class="h-7 w-7 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                    </svg>
                </div>
                <h3 class="text-lg font-bold text-[#0a1233] dark:text-gray-100 mb-1">Confirm Delete</h3>
                <p class="text-sm text-ink-muted">
                    Are you sure you want to delete
                    <span class="font-semibold text-ink-body" x-text="deleteName"></span>?
                </p>
                <p class="text-xs text-ink-muted mt-1">This action cannot be undone.</p>
            </div>
            <div class="px-6 pb-6 pt-4 flex gap-3">
                <button type="button" @click="deleteOpen = false"
                    class="flex-1 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 text-sm font-semibold text-ink-body hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                    Cancel
                </button>
                <form method="POST" :action="deleteAction" class="flex-1">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                        class="w-full py-2.5 rounded-lg bg-red-600 text-white text-sm font-semibold hover:bg-red-700 transition-colors">
                        Delete
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
