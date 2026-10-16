<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-xl font-bold text-text flex items-center gap-2">
                    <svg class="w-6 h-6 text-primary" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m20.25 7.5-.625 10.632a2.25 2.25 0 0 1-2.247 2.118H6.622a2.25 2.25 0 0 1-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z" />
                    </svg>
                    Archive Center
                </h1>
                <p class="text-xs text-text-muted mt-0.5">
                    ConnectEd strictly preserves all business records with soft deletes. Restore archived users and documents here.
                </p>
            </div>
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20">
                    <svg class="w-2 h-2 rounded-full bg-emerald-500" viewBox="0 0 8 8" fill="currentColor"></svg>
                    Audit Trail Active
                </span>
            </div>
        </div>
    </x-slot>

    <!-- Flash Status Messages -->
    @if (session('status'))
        <div class="mb-6 p-4 rounded-card bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-500/20 text-emerald-700 dark:text-emerald-300 text-sm flex items-center gap-3">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
            </svg>
            <span>{{ session('status') }}</span>
        </div>
    @endif

    <!-- Tab Pill Filter Navigation (LinkedIn-style) -->
    <div class="mb-6 border-b border-border">
        <nav class="flex space-x-6" aria-label="Archive Tabs">
            <a 
                href="{{ route('coordinator.archive.index', ['tab' => 'users']) }}" 
                class="pb-3 text-sm font-semibold transition border-b-2 flex items-center gap-2 {{ $tab === 'users' ? 'border-primary text-primary' : 'border-transparent text-text-muted hover:text-text' }}"
            >
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
                </svg>
                Archived Accounts
            </a>
            <a 
                href="{{ route('coordinator.archive.index', ['tab' => 'documents']) }}" 
                class="pb-3 text-sm font-semibold transition border-b-2 flex items-center gap-2 {{ $tab === 'documents' ? 'border-primary text-primary' : 'border-transparent text-text-muted hover:text-text' }}"
            >
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                </svg>
                Archived Documents
            </a>
        </nav>
    </div>

    <!-- Content Table Card -->
    <div class="bg-surface border border-border rounded-card shadow-sm overflow-hidden">
        @if ($tab === 'users')
            <!-- Users Tab Table -->
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-surface-2 border-b border-border text-text-muted uppercase font-bold tracking-wider">
                        <tr>
                            <th class="py-3 px-4">User</th>
                            <th class="py-3 px-4">Role</th>
                            <th class="py-3 px-4">Status Before Deletion</th>
                            <th class="py-3 px-4">Archived At</th>
                            <th class="py-3 px-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border">
                        @forelse ($archivedItems as $user)
                            <tr class="hover:bg-surface-2/50 transition">
                                <td class="py-3 px-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-full bg-primary/10 text-primary flex items-center justify-center font-bold text-xs flex-shrink-0">
                                            {{ strtoupper(substr($user->name, 0, 2)) }}
                                        </div>
                                        <div>
                                            <div class="font-semibold text-text">{{ $user->name }}</div>
                                            <div class="text-text-muted">{{ $user->email }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3 px-4">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold bg-surface-2 border border-border text-text">
                                        {{ $user->role->label() }}
                                    </span>
                                </td>
                                <td class="py-3 px-4">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold bg-danger/10 text-danger border border-danger/20">
                                        {{ $user->status->label() }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-text-muted">
                                    <div>{{ $user->deleted_at->format('M d, Y') }}</div>
                                    <div class="text-[10px]">{{ $user->deleted_at->format('h:i A') }}</div>
                                </td>
                                <td class="py-3 px-4 text-right">
                                    <form method="POST" action="{{ route('coordinator.archive.restore', ['type' => 'users', 'id' => $user->id]) }}" class="inline" onsubmit="return confirm('Are you sure you want to restore this user account?');">
                                        @csrf
                                        <button 
                                            type="submit" 
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold bg-primary text-primary-contrast hover:bg-primary-hover transition shadow-sm"
                                        >
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 15 3 9m0 0 6-6M3 9h12a6 6 0 0 1 0 12h-3" />
                                            </svg>
                                            Restore Account
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-12 text-center text-text-muted">
                                    <svg class="w-10 h-10 mx-auto text-text-muted/50 mb-3" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m20.25 7.5-.625 10.632a2.25 2.25 0 0 1-2.247 2.118H6.622a2.25 2.25 0 0 1-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z" />
                                    </svg>
                                    <div class="font-semibold text-text text-sm">No Archived Accounts</div>
                                    <p class="text-xs mt-1">There are currently no soft-deleted user records in the system.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        @else
            <!-- Documents Tab Table -->
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-surface-2 border-b border-border text-text-muted uppercase font-bold tracking-wider">
                        <tr>
                            <th class="py-3 px-4">Document</th>
                            <th class="py-3 px-4">Category</th>
                            <th class="py-3 px-4">Owner / Uploader</th>
                            <th class="py-3 px-4">File Size</th>
                            <th class="py-3 px-4">Archived At</th>
                            <th class="py-3 px-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border">
                        @forelse ($archivedItems as $doc)
                            <tr class="hover:bg-surface-2/50 transition">
                                <td class="py-3 px-4">
                                    <div class="flex items-center gap-2">
                                        <svg class="w-4 h-4 text-primary flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                                        </svg>
                                        <span class="font-semibold text-text truncate max-w-xs">{{ $doc->original_name }}</span>
                                    </div>
                                </td>
                                <td class="py-3 px-4">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold bg-surface-2 border border-border text-text">
                                        {{ str_replace('_', ' ', ucwords($doc->category, '_')) }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-text-muted">
                                    <div>Owner: {{ $doc->owner?->name ?? 'System' }}</div>
                                    <div class="text-[10px]">By: {{ $doc->uploader?->name ?? 'Unknown' }}</div>
                                </td>
                                <td class="py-3 px-4 text-text-muted">
                                    {{ number_format($doc->file_size_bytes / 1024, 1) }} KB
                                </td>
                                <td class="py-3 px-4 text-text-muted">
                                    <div>{{ $doc->deleted_at->format('M d, Y') }}</div>
                                    <div class="text-[10px]">{{ $doc->deleted_at->format('h:i A') }}</div>
                                </td>
                                <td class="py-3 px-4 text-right">
                                    <form method="POST" action="{{ route('coordinator.archive.restore', ['type' => 'documents', 'id' => $doc->id]) }}" class="inline" onsubmit="return confirm('Are you sure you want to restore this archived document?');">
                                        @csrf
                                        <button 
                                            type="submit" 
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold bg-primary text-primary-contrast hover:bg-primary-hover transition shadow-sm"
                                        >
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 15 3 9m0 0 6-6M3 9h12a6 6 0 0 1 0 12h-3" />
                                            </svg>
                                            Restore File
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-12 text-center text-text-muted">
                                    <svg class="w-10 h-10 mx-auto text-text-muted/50 mb-3" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                                    </svg>
                                    <div class="font-semibold text-text text-sm">No Archived Documents</div>
                                    <p class="text-xs mt-1">There are currently no soft-deleted document files in the repository.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        @endif

        @if ($archivedItems->hasPages())
            <div class="p-4 border-t border-border bg-surface-2">
                {{ $archivedItems->links() }}
            </div>
        @endif
    </div>
</x-app-layout>
