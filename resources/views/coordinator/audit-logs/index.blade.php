<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-xl font-bold text-text flex items-center gap-2">
                    <svg class="w-6 h-6 text-primary" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25ZM6.75 12h.008v.008H6.75V12Zm0 3h.008v.008H6.75V15Zm0 3h.008v.008H6.75V18Z" />
                    </svg>
                    System Audit Trail
                </h1>
                <p class="text-xs text-text-muted mt-0.5">
                    Immutable security and compliance log. All administrative actions and critical data changes are permanently recorded.
                </p>
            </div>
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20">
                    <svg class="w-2 h-2 rounded-full bg-emerald-500" viewBox="0 0 8 8" fill="currentColor"></svg>
                    Append-Only Guaranteed
                </span>
            </div>
        </div>
    </x-slot>

    <!-- Filters Card (LinkedIn-style) -->
    <div class="mb-6 bg-surface border border-border rounded-card p-4 shadow-sm" x-data="{ expanded: false }">
        <form method="GET" action="{{ route('coordinator.audit-logs.index') }}" class="space-y-4">
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <!-- Action Search -->
                <div>
                    <label for="action" class="block text-xs font-semibold text-text uppercase tracking-wider mb-1">
                        Action Type
                    </label>
                    <input 
                        type="text" 
                        name="action" 
                        id="action" 
                        value="{{ request('action') }}" 
                        placeholder="e.g. student.approved, user.login" 
                        class="w-full text-xs rounded-lg border-border bg-surface-2 text-text focus:ring-primary focus:border-primary px-3 py-2"
                    />
                </div>

                <!-- Date Filter -->
                <div>
                    <label for="date" class="block text-xs font-semibold text-text uppercase tracking-wider mb-1">
                        Log Date
                    </label>
                    <input 
                        type="date" 
                        name="date" 
                        id="date" 
                        value="{{ request('date') }}" 
                        class="w-full text-xs rounded-lg border-border bg-surface-2 text-text focus:ring-primary focus:border-primary px-3 py-2"
                    />
                </div>

                <!-- Actor ID Filter -->
                <div>
                    <label for="actor_id" class="block text-xs font-semibold text-text uppercase tracking-wider mb-1">
                        Actor User ID
                    </label>
                    <input 
                        type="number" 
                        name="actor_id" 
                        id="actor_id" 
                        value="{{ request('actor_id') }}" 
                        placeholder="e.g. 1" 
                        class="w-full text-xs rounded-lg border-border bg-surface-2 text-text focus:ring-primary focus:border-primary px-3 py-2"
                    />
                </div>
            </div>

            <div class="flex items-center justify-end gap-2 pt-2 border-t border-border">
                @if (request()->hasAny(['action', 'date', 'actor_id']))
                    <a 
                        href="{{ route('coordinator.audit-logs.index') }}" 
                        class="px-3 py-1.5 rounded-lg text-xs font-medium text-text-muted hover:text-text hover:bg-surface-2 transition"
                    >
                        Reset Filters
                    </a>
                @endif
                <button 
                    type="submit" 
                    class="px-4 py-1.5 rounded-lg text-xs font-semibold bg-primary text-primary-contrast hover:bg-primary-hover transition shadow-sm"
                >
                    Apply Filter
                </button>
            </div>
        </form>
    </div>

    <!-- Audit Logs Table & Modal Container -->
    <div x-data="{ modalOpen: false, modalContent: null, modalTitle: '' }">
        <div class="bg-surface border border-border rounded-card shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-surface-2 border-b border-border text-text-muted uppercase font-bold tracking-wider">
                        <tr>
                            <th class="py-3 px-4">Timestamp</th>
                            <th class="py-3 px-4">Actor</th>
                            <th class="py-3 px-4">Action</th>
                            <th class="py-3 px-4">Target Entity</th>
                            <th class="py-3 px-4">IP Address</th>
                            <th class="py-3 px-4 text-right">Payload</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border font-mono">
                        @forelse ($logs as $log)
                            <tr class="hover:bg-surface-2/50 transition">
                                <td class="py-3 px-4 text-text-muted whitespace-nowrap font-sans">
                                    <div class="font-semibold text-text">{{ $log->created_at->format('M d, Y') }}</div>
                                    <div class="text-[10px]">{{ $log->created_at->format('h:i:s A') }}</div>
                                </td>
                                <td class="py-3 px-4 font-sans">
                                    @if ($log->actor)
                                        <div class="font-semibold text-text">{{ $log->actor->name }}</div>
                                        <div class="text-[10px] text-text-muted">{{ $log->actor->role->label() }} (ID: {{ $log->actor_id }})</div>
                                    @else
                                        <span class="text-text-muted italic">System / Anonymous</span>
                                    @endif
                                </td>
                                <td class="py-3 px-4">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold bg-primary/10 text-primary border border-primary/20">
                                        {{ $log->action }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-text-muted">
                                    @if ($log->subject_type)
                                        <div>{{ class_basename($log->subject_type) }} #{{ $log->subject_id }}</div>
                                    @else
                                        <span class="text-text-muted italic">—</span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-text-muted text-[11px]">
                                    {{ $log->ip_address ?? '127.0.0.1' }}
                                </td>
                                <td class="py-3 px-4 text-right font-sans">
                                    @if (!empty($log->changes))
                                        <button 
                                            type="button" 
                                            @click="modalTitle = 'Audit Event: {{ $log->action }}'; modalContent = {{ json_encode($log->changes, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}; modalOpen = true"
                                            class="inline-flex items-center gap-1 px-2.5 py-1 rounded text-xs font-medium text-text bg-surface-2 hover:bg-surface border border-border transition"
                                        >
                                            <svg class="w-3.5 h-3.5 text-text-muted" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M17.25 6.75 22.5 12l-5.25 5.25m-10.5 0L1.5 12l5.25-5.25m7.5-3-4.5 16.5" />
                                            </svg>
                                            View JSON
                                        </button>
                                    @else
                                        <span class="text-text-muted text-[11px] italic">No payload</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-12 text-center text-text-muted font-sans">
                                    <svg class="w-10 h-10 mx-auto text-text-muted/50 mb-3" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25ZM6.75 12h.008v.008H6.75V12Zm0 3h.008v.008H6.75V15Zm0 3h.008v.008H6.75V18Z" />
                                    </svg>
                                    <div class="font-semibold text-text text-sm">No Audit Logs Recorded</div>
                                    <p class="text-xs mt-1">There are no audit events matching the selected filter criteria.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($logs->hasPages())
                <div class="p-4 border-t border-border bg-surface-2">
                    {{ $logs->links() }}
                </div>
            @endif
        </div>

        <!-- JSON Context Modal -->
        <div 
            x-show="modalOpen" 
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 z-50 overflow-y-auto bg-black/60 flex items-center justify-center p-4"
            style="display: none;"
            @keydown.escape.window="modalOpen = false"
        >
            <div 
                @click.outside="modalOpen = false" 
                class="bg-surface border border-border rounded-card max-w-2xl w-full p-6 shadow-xl space-y-4"
            >
                <div class="flex items-center justify-between pb-3 border-b border-border">
                    <h3 class="text-sm font-bold text-text flex items-center gap-2">
                        <svg class="w-4 h-4 text-primary" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17.25 6.75 22.5 12l-5.25 5.25m-10.5 0L1.5 12l5.25-5.25m7.5-3-4.5 16.5" />
                        </svg>
                        <span x-text="modalTitle"></span>
                    </h3>
                    <button 
                        type="button" 
                        @click="modalOpen = false" 
                        class="p-1 rounded text-text-muted hover:text-text hover:bg-surface-2"
                    >
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="bg-surface-2 rounded-lg p-4 font-mono text-xs overflow-x-auto max-h-96 border border-border">
                    <pre x-text="JSON.stringify(modalContent, null, 2)" class="text-text"></pre>
                </div>

                <div class="flex justify-end pt-2">
                    <button 
                        type="button" 
                        @click="modalOpen = false" 
                        class="px-4 py-2 rounded-lg text-xs font-semibold bg-surface-2 text-text hover:bg-surface border border-border transition"
                    >
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
