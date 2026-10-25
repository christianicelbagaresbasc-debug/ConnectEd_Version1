<x-app-layout>
    @section('title', 'Institutional Document Library')

    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-xl font-bold text-text">Institutional Document Library</h1>
                <p class="text-xs text-text-muted mt-0.5">Central repository for student credentials, training agreements, and verification files (FR-27, FR-28).</p>
            </div>
            @if (in_array(Auth::user()->role->value, ['oc', 'coc']))
                <div class="flex items-center gap-3">
                    <a href="{{ route('document-library.completeness') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold bg-surface-2 text-text border border-border hover:bg-surface-3 transition">
                        <svg class="w-4 h-4 text-text-muted" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                        </svg>
                        <span>Completeness Matrix</span>
                    </a>
                </div>
            @endif
        </div>
    </x-slot>

    <!-- Category Filter Tabs -->
    <div class="mb-6 overflow-x-auto pb-1">
        <div class="flex items-center gap-2 min-w-max">
            @php
                $currentCat = request('category', 'all');
                $categories = [
                    'all' => ['label' => 'All Documents', 'count' => $counts['all']],
                    'resume' => ['label' => 'Resumes', 'count' => $counts['resume']],
                    'certification' => ['label' => 'Certifications', 'count' => $counts['certification']],
                    'moa' => ['label' => 'MOA Agreements', 'count' => $counts['moa']],
                    'weekly_report' => ['label' => 'Weekly Reports', 'count' => $counts['weekly_report']],
                    'verification' => ['label' => 'Company Verification', 'count' => $counts['verification']],
                    'template' => ['label' => 'Templates', 'count' => $counts['template']],
                ];
            @endphp

            @foreach ($categories as $catKey => $catData)
                <a 
                    href="{{ route('document-library.index', array_merge(request()->except('category', 'page'), ['category' => $catKey])) }}"
                    class="px-3.5 py-1.5 rounded-xl text-xs font-semibold transition border flex items-center gap-2 {{ $currentCat === $catKey ? 'bg-primary text-white border-primary shadow-sm' : 'bg-surface text-text-muted border-border hover:bg-surface-2 hover:text-text' }}"
                >
                    <span>{{ $catData['label'] }}</span>
                    <span class="px-1.5 py-0.2 rounded-full text-[10px] {{ $currentCat === $catKey ? 'bg-white/20 text-white' : 'bg-surface-2 text-text-muted' }}">
                        {{ $catData['count'] }}
                    </span>
                </a>
            @endforeach
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="bg-surface border border-border rounded-xl p-4 sm:p-5 mb-6 shadow-sm">
        <form method="GET" action="{{ route('document-library.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
            <input type="hidden" name="category" value="{{ request('category', 'all') }}">

            <!-- Search -->
            <div>
                <label for="search" class="block text-xs font-semibold text-text-muted mb-1">Search File Name</label>
                <input 
                    type="text" 
                    name="search" 
                    id="search" 
                    value="{{ request('search') }}" 
                    placeholder="Search by file name..." 
                    class="w-full py-2 px-3 text-xs rounded-lg border border-border bg-surface text-text focus:ring-2 focus:ring-primary focus:border-transparent transition"
                />
            </div>

            <!-- Validation Status -->
            <div>
                <label for="validation_status" class="block text-xs font-semibold text-text-muted mb-1">Validation Status</label>
                <select 
                    name="validation_status" 
                    id="validation_status" 
                    class="w-full py-2 px-3 text-xs rounded-lg border border-border bg-surface text-text focus:ring-2 focus:ring-primary focus:border-transparent transition"
                >
                    <option value="all">All Validation States</option>
                    <option value="ok" {{ request('validation_status') === 'ok' ? 'selected' : '' }}>Verified Valid (Ok)</option>
                    <option value="rejected" {{ request('validation_status') === 'rejected' ? 'selected' : '' }}>Validation Rejected</option>
                </select>
            </div>

            <!-- Review Status -->
            <div>
                <label for="review_status" class="block text-xs font-semibold text-text-muted mb-1">Review Status</label>
                <select 
                    name="review_status" 
                    id="review_status" 
                    class="w-full py-2 px-3 text-xs rounded-lg border border-border bg-surface text-text focus:ring-2 focus:ring-primary focus:border-transparent transition"
                >
                    <option value="all">All Review States</option>
                    <option value="unreviewed" {{ request('review_status') === 'unreviewed' ? 'selected' : '' }}>Unreviewed</option>
                    <option value="accepted" {{ request('review_status') === 'accepted' ? 'selected' : '' }}>Accepted</option>
                    <option value="revision_requested" {{ request('review_status') === 'revision_requested' ? 'selected' : '' }}>Revision Requested</option>
                    <option value="rejected" {{ request('review_status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                </select>
            </div>

            <div class="flex items-end gap-2">
                <button type="submit" class="w-full py-2 px-4 rounded-lg text-xs font-semibold bg-surface-2 text-text border border-border hover:bg-surface-3 transition">
                    Filter
                </button>
                @if (request()->hasAny(['search', 'validation_status', 'review_status']))
                    <a href="{{ route('document-library.index', ['category' => request('category', 'all')]) }}" class="py-2 px-3 rounded-lg text-xs font-semibold text-text-muted hover:text-text transition">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Document Table & Multi-Select Bundle Downloader (FR-28, Assumption A19) -->
    <div x-data="{ 
        selected: [], 
        toggleAll(e) {
            if (e.target.checked) {
                this.selected = {{ json_encode($documents->pluck('id')) }};
            } else {
                this.selected = [];
            }
        }
    }" class="space-y-4">

        <!-- Floating / Sticky ZIP Bundle Toolbar -->
        <div 
            x-show="selected.length > 0" 
            x-cloak 
            class="bg-primary-50 dark:bg-primary-950/40 border border-primary-200 dark:border-primary-800 rounded-xl p-3 flex flex-col sm:flex-row items-center justify-between gap-3 shadow-sm transition"
        >
            <div class="flex items-center gap-2 text-xs font-semibold text-primary-900 dark:text-primary-200">
                <span class="w-6 h-6 rounded-full bg-primary text-white text-[11px] font-bold flex items-center justify-center" x-text="selected.length"></span>
                <span>documents selected (max 50 files / 100 MB per batch)</span>
            </div>

            <form method="POST" action="{{ route('document-library.bundle') }}" class="flex items-center gap-2">
                @csrf
                <template x-for="id in selected" :key="id">
                    <input type="hidden" name="document_ids[]" :value="id">
                </template>
                <button 
                    type="submit" 
                    class="px-4 py-1.5 rounded-lg text-xs font-semibold bg-primary text-white hover:bg-primary-600 transition flex items-center gap-2 shadow-sm"
                >
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" />
                    </svg>
                    <span>Download Selected (ZIP Bundle)</span>
                </button>
            </form>
        </div>

        @if ($errors->has('bundle'))
            <div class="p-3 rounded-xl bg-danger/10 border border-danger/20 text-xs text-danger font-semibold">
                {{ $errors->first('bundle') }}
            </div>
        @endif

        <!-- Document List Table -->
        <div class="bg-surface border border-border rounded-xl shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-text divide-y divide-border">
                    <thead class="bg-surface-2 text-text-muted font-semibold uppercase tracking-wider text-[11px]">
                        <tr>
                            <th class="py-3 px-3 w-8">
                                <input 
                                    type="checkbox" 
                                    @change="toggleAll" 
                                    class="rounded border-border text-primary focus:ring-primary" 
                                    title="Select All on Page"
                                />
                            </th>
                            <th class="py-3 px-4">Document / File</th>
                            <th class="py-3 px-4">Category</th>
                            <th class="py-3 px-4">Owner / Uploader</th>
                            <th class="py-3 px-4">Validation</th>
                            <th class="py-3 px-4">Review Status</th>
                            <th class="py-3 px-4">Size & Date</th>
                            <th class="py-3 px-4 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border">
                        @forelse ($documents as $doc)
                            <tr class="hover:bg-surface-2/40 transition">
                                <td class="py-3 px-3">
                                    <input 
                                        type="checkbox" 
                                        :value="{{ $doc->id }}" 
                                        x-model="selected" 
                                        class="rounded border-border text-primary focus:ring-primary"
                                    />
                                </td>
                                <td class="py-3 px-4">
                                    <div class="flex items-center gap-2.5">
                                        <div class="p-1.5 rounded-lg bg-surface-2 text-text-muted flex-shrink-0">
                                            @php
                                                $ext = strtolower(pathinfo($doc->original_name, PATHINFO_EXTENSION));
                                            @endphp
                                            <span class="font-mono text-[10px] font-bold uppercase">{{ $ext }}</span>
                                        </div>
                                        <div class="min-w-0">
                                            <div class="font-semibold text-text truncate max-w-xs" title="{{ $doc->original_name }}">
                                                {{ $doc->original_name }}
                                            </div>
                                            <div class="text-[10px] font-mono text-text-muted truncate max-w-xs">
                                                SHA: {{ substr($doc->checksum_sha256, 0, 12) }}...
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3 px-4">
                                    <span class="px-2 py-0.5 rounded text-[11px] font-semibold bg-surface-2 border border-border uppercase">
                                        {{ str_replace('_', ' ', $doc->category) }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-text-muted">
                                    <div class="font-medium text-text">{{ $doc->owner?->name ?? 'System' }}</div>
                                    <div class="text-[10px] text-text-muted">by {{ $doc->uploader?->name ?? 'Uploader' }}</div>
                                </td>
                                <td class="py-3 px-4">
                                    @if ($doc->validation_status === 'ok')
                                        <span class="inline-flex items-center gap-1 text-[11px] text-success font-semibold">
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                                            </svg>
                                            Verified
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 text-[11px] text-danger font-semibold">
                                            Rejected
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3 px-4">
                                    @php
                                        $reviewBadge = match ($doc->review_status) {
                                            'accepted' => 'bg-success/10 text-success border-success/20',
                                            'revision_requested' => 'bg-warning/10 text-warning border-warning/20',
                                            'rejected' => 'bg-danger/10 text-danger border-danger/20',
                                            default => 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300 border-slate-200 dark:border-slate-700',
                                        };
                                    @endphp
                                    <span class="px-2 py-0.5 rounded text-[10px] font-semibold border {{ $reviewBadge }}">
                                        {{ ucfirst(str_replace('_', ' ', $doc->review_status)) }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-text-muted text-[11px]">
                                    <div>{{ $doc->human_readable_size }}</div>
                                    <div class="text-[10px]">{{ $doc->created_at->format('M d, Y') }}</div>
                                </td>
                                <td class="py-3 px-4 text-right">
                                    <a 
                                        href="{{ route('documents.download', $doc) }}" 
                                        class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-semibold bg-surface-2 border border-border text-text hover:bg-surface-3 transition"
                                        title="Download Single File"
                                    >
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" />
                                        </svg>
                                        <span>Download</span>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="py-12 text-center text-text-muted">
                                    <svg class="w-10 h-10 text-text-muted mx-auto mb-3" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                                    </svg>
                                    <div class="text-sm font-semibold text-text">No documents found</div>
                                    <p class="text-xs text-text-muted mt-1">No uploaded files match the selected filter criteria.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($documents->hasPages())
                <div class="p-4 border-t border-border">
                    {{ $documents->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
