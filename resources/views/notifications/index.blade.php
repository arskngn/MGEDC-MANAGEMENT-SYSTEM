<x-app-layout>
    <div class="space-y-6">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-100">Notifications Center</h2>
                <p class="text-sm text-ink-muted mt-1">Manage your system alerts and customer communications.</p>
            </div>
            <div class="flex items-center gap-3">
                <button type="button"
                        onclick="markAllNotificationsAsRead()"
                        class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-bold rounded-lg shadow-sm transition-all flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    Mark All as Read
                </button>
                <button type="button"
                        onclick="deleteAllNotifications()"
                        class="px-4 py-2 bg-red-50 dark:bg-red-900/20 text-red-600 dark:text-red-300 hover:bg-red-100 dark:hover:bg-red-900/30 text-sm font-bold rounded-lg transition-all flex items-center border border-red-100 dark:border-red-700">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    Delete All
                </button>
            </div>
        </div>

        <!-- System Alerts (Expiration, Stock, etc.) -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="px-6 py-4 bg-gray-50 dark:bg-gray-900/50 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                <h3 class="text-sm font-extrabold text-gray-700 dark:text-gray-200 uppercase tracking-wider flex items-center">
                    <svg class="w-4 h-4 mr-2 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2 0 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" /></svg>
                    System Alerts
                </h3>
                <span class="bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-300 text-[11px] font-bold px-2 py-0.5 rounded-full">{{ $notifications->total() }} total</span>
            </div>
            
            <div class="divide-y divide-gray-100 dark:divide-gray-700" id="full-notification-list">
                @forelse($notifications as $notification)
                    <div class="relative p-5 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors flex items-start gap-4 {{ $notification->is_read ? 'opacity-75' : ($notification->severity === 'danger' ? 'bg-red-50/50 dark:bg-red-900/20' : ($notification->severity === 'warning' ? 'bg-amber-50/50 dark:bg-amber-900/20' : 'bg-blue-50/50 dark:bg-blue-900/20')) }}" 
                         data-notification-id="{{ $notification->id }}" 
                         data-is-read="{{ $notification->is_read ? 'true' : 'false' }}">
                        
                        <!-- Status Indicator -->
                        <div class="flex-shrink-0 pt-1">
                            @if(!$notification->is_read)
                                <div class="w-2.5 h-2.5 bg-blue-600 dark:bg-blue-400 rounded-full animate-pulse" data-unread-indicator></div>
                            @endif
                        </div>

                        <!-- Content -->
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2">
                                <p class="font-bold text-ink-heading {{ $notification->is_read ? 'opacity-60' : '' }}">{{ $notification->title }}</p>
                                @if($notification->severity !== 'info')
                                    <span class="text-[10px] font-bold uppercase px-1.5 py-0.5 rounded {{ $notification->severity === 'danger' ? 'bg-red-100 dark:bg-red-900/40 text-red-600 dark:text-red-300' : 'bg-amber-100 dark:bg-amber-900/40 text-amber-600 dark:text-amber-300' }}">
                                        {{ $notification->severity }}
                                    </span>
                                @endif
                            </div>
                            <p class="text-gray-600 dark:text-gray-300 text-sm mt-1 leading-relaxed {{ $notification->is_read ? 'opacity-60' : '' }}">{{ $notification->message }}</p>
                            
                            <div class="flex items-center gap-4 mt-3">
                                <span class="text-ink-muted text-[11px] flex items-center">
                                    <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    {{ $notification->created_at->diffForHumans() }}
                                </span>
                                @if($notification->type)
                                    <span class="text-[10px] font-bold text-ink-muted uppercase tracking-widest bg-gray-100 dark:bg-gray-700 px-2 py-0.5 rounded">
                                        {{ str_replace('_', ' ', $notification->type) }}
                                    </span>
                                @endif
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="flex items-center gap-2 opacity-0 group-hover:opacity-100 lg:opacity-100 transition-opacity">
                            @if(!$notification->is_read)
                                <button onclick="markAsRead('{{ $notification->id }}', this)" 
                                        class="p-2 text-blue-600 dark:text-blue-400 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded-lg transition-colors"
                                        title="Mark as read"
                                        data-mark-read-btn>
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                </button>
                            @endif
                            <button onclick="deleteNotification('{{ $notification->id }}', this)" 
                                    class="p-2 text-red-400 hover:text-red-600 dark:hover:text-red-300 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors"
                                    title="Delete notification">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        </div>
                    </div>
                @empty
                    <div class="p-12 text-center">
                        <div class="text-gray-200 dark:text-gray-700 mb-4">
                            <svg class="w-20 h-20 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0a2 2 0 01-2 2H6a2 2 0 01-2-2m16 0l-8 4-8-4m8 4v6" />
                            </svg>
                        </div>
                        <h4 class="text-ink-muted font-bold">No system alerts found</h4>
                        <p class="text-ink-muted text-sm mt-1">You're all caught up!</p>
                    </div>
                @endforelse
            </div>

            @if($notifications->hasPages())
                <div class="px-6 py-4 bg-gray-50 dark:bg-gray-900/50 border-t border-gray-100 dark:border-gray-700">
                    {{ $notifications->links() }}
                </div>
            @endif
        </div>
    </div>

    @push('scripts')
    <script>
        // Use global functions from app.blade.php but provide page-specific overrides if needed
        window.markAllNotificationsAsRead = function() {
            fetch('{{ route("notifications.mark-all-as-read") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                },
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload(); 
                }
            });
        };

        window.deleteAllNotifications = async function() {
            const confirmed = await showDeleteConfirm('all notifications');
            if (!confirmed) return;
            
            fetch('{{ route("notifications.delete-all") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                },
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                }
            });
        };
    </script>
    @endpush
</x-app-layout>