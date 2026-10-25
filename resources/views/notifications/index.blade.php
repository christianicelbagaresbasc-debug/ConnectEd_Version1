<x-app-layout>
    @section('title', 'Notifications')

    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-xl font-bold text-text">In-System Notifications</h1>
                <p class="text-xs text-text-muted mt-0.5">Stay updated on your applications, offer decisions, and placement status (F-017, BR-02).</p>
            </div>
            @if($unreadCount > 0)
                <form method="POST" action="{{ route('notifications.read-all') }}">
                    @csrf
                    <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold bg-surface-2 text-primary hover:bg-surface border border-border transition">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                        </svg>
                        <span>Mark all as read</span>
                    </button>
                </form>
            @endif
        </div>
    </x-slot>

    <!-- Feedback Alerts -->
    @if (session('success'))
        <div class="mb-6 p-4 rounded-xl bg-success/10 border border-success/30 text-success text-xs font-medium flex items-center justify-between">
            <div class="flex items-center gap-2">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
                <span>{{ session('success') }}</span>
            </div>
        </div>
    @endif

    <!-- Filter Tabs -->
    <div class="flex items-center gap-2 mb-6 border-b border-border pb-3 text-xs">
        <a 
            href="{{ route('notifications.index') }}" 
            class="px-3 py-1.5 rounded-lg font-medium transition {{ request('filter') !== 'unread' ? 'bg-primary text-primary-contrast font-semibold shadow-sm' : 'text-text-muted hover:text-text hover:bg-surface-2' }}"
        >
            All Notifications
        </a>
        <a 
            href="{{ route('notifications.index', ['filter' => 'unread']) }}" 
            class="px-3 py-1.5 rounded-lg font-medium transition flex items-center gap-1.5 {{ request('filter') === 'unread' ? 'bg-primary text-primary-contrast font-semibold shadow-sm' : 'text-text-muted hover:text-text hover:bg-surface-2' }}"
        >
            <span>Unread Only</span>
            @if($unreadCount > 0)
                <span class="px-1.5 py-0.2 rounded-full text-[10px] {{ request('filter') === 'unread' ? 'bg-primary-contrast/20 text-primary-contrast' : 'bg-accent text-white' }}">
                    {{ $unreadCount }}
                </span>
            @endif
        </a>
    </div>

    <!-- Notifications List -->
    <div class="bg-surface border border-border rounded-xl divide-y divide-border overflow-hidden shadow-sm">
        @forelse($notifications as $notification)
            @php
                $isUnread = is_null($notification->read_at);
                $title = $notification->data['title'] ?? 'Notification';
                $body = $notification->data['body'] ?? '';
                $actionUrl = $notification->data['action_url'] ?? null;
            @endphp
            <div class="p-4 sm:p-5 flex items-start gap-4 hover:bg-surface-2/40 transition {{ $isUnread ? 'bg-primary/5' : '' }}">
                <div class="mt-0.5 flex-shrink-0">
                    @if($isUnread)
                        <span class="w-2.5 h-2.5 rounded-full bg-accent block ring-4 ring-accent/20"></span>
                    @else
                        <span class="w-2.5 h-2.5 rounded-full bg-border block"></span>
                    @endif
                </div>
                <div class="flex-1 min-w-0 space-y-1">
                    <div class="flex items-center justify-between gap-2">
                        <h3 class="text-xs font-bold text-text truncate {{ $isUnread ? 'text-primary' : '' }}">
                            {{ $title }}
                        </h3>
                        <span class="text-[10px] text-text-muted whitespace-nowrap">
                            {{ $notification->created_at->diffForHumans() }}
                        </span>
                    </div>
                    <p class="text-xs text-text-muted leading-relaxed">
                        {{ $body }}
                    </p>
                    @if($actionUrl)
                        <div class="pt-1.5">
                            <form method="POST" action="{{ route('notifications.read', $notification->id) }}" class="inline">
                                @csrf
                                <button type="submit" class="text-xs font-semibold text-primary hover:underline inline-flex items-center gap-1">
                                    <span>View Details</span>
                                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                                    </svg>
                                </button>
                            </form>
                        </div>
                    @endif
                </div>
                @if($isUnread && ! $actionUrl)
                    <form method="POST" action="{{ route('notifications.read', $notification->id) }}" class="flex-shrink-0">
                        @csrf
                        <button type="submit" class="text-[11px] text-text-muted hover:text-text" title="Mark as read">
                            &times;
                        </button>
                    </form>
                @endif
            </div>
        @empty
            <div class="p-12 text-center text-text-muted text-xs">
                <svg class="w-10 h-10 mx-auto text-text-muted/40 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" />
                </svg>
                <p>No notifications found.</p>
            </div>
        @endforelse
    </div>

    @if($notifications->hasPages())
        <div class="mt-4 p-4 bg-surface border border-border rounded-xl">
            {{ $notifications->links() }}
        </div>
    @endif
</x-app-layout>
