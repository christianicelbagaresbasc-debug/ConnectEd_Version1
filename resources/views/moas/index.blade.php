<x-app-layout>
    @section('title', 'Memorandum of Agreement (MOA) Tracking')

    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-xl font-bold text-text">MOA Tracking & Custody Management</h1>
                <p class="text-xs text-text-muted mt-0.5">Track multi-party institutional and student-specific partnership agreements across legal review and custody transfers.</p>
            </div>
            @can('create', App\Models\Moa::class)
                <a href="{{ route('moas.create') }}" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-lg text-xs font-semibold bg-primary text-primary-contrast shadow hover:opacity-90 transition">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    <span>Draft New MOA</span>
                </a>
            @endcan
        </div>
    </x-slot>

    <!-- Session Feedback -->
    @if (session('status'))
        <div class="mb-6 p-4 rounded-xl bg-success/10 border border-success/30 text-success text-xs font-medium flex items-center gap-2">
            <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
            </svg>
            <span>{{ session('status') }}</span>
        </div>
    @endif

    <!-- LinkedIn-Style Filter Card -->
    <div class="bg-surface border border-border rounded-xl p-4 sm:p-5 mb-6 shadow-sm">
        <form method="GET" action="{{ route('moas.index') }}" class="space-y-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                <!-- Search Query -->
                <div>
                    <label for="search" class="block text-xs font-semibold text-text-muted mb-1">Search Agreements</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-text-muted">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                            </svg>
                        </div>
                        <input 
                            type="text" 
                            name="search" 
                            id="search" 
                            value="{{ request('search') }}" 
                            placeholder="Company, MOA #, summary..." 
                            class="w-full pl-9 pr-3 py-2 text-xs rounded-lg border border-border bg-surface text-text focus:ring-2 focus:ring-primary focus:border-transparent transition"
                        />
                    </div>
                </div>

                <!-- Status Filter -->
                <div>
                    <label for="status" class="block text-xs font-semibold text-text-muted mb-1">Status</label>
                    <select 
                        name="status" 
                        id="status" 
                        class="w-full py-2 px-3 text-xs rounded-lg border border-border bg-surface text-text focus:ring-2 focus:ring-primary focus:border-transparent transition"
                    >
                        <option value="">All Statuses</option>
                        <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="under_legal_review" {{ request('status') === 'under_legal_review' ? 'selected' : '' }}>Under Legal Review</option>
                        <option value="for_signing_school" {{ request('status') === 'for_signing_school' ? 'selected' : '' }}>For University Signing</option>
                        <option value="for_signing_company" {{ request('status') === 'for_signing_company' ? 'selected' : '' }}>For Company Signing</option>
                        <option value="notarized" {{ request('status') === 'notarized' ? 'selected' : '' }}>Notarized</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active / Executed</option>
                        <option value="expired" {{ request('status') === 'expired' ? 'selected' : '' }}>Expired</option>
                        <option value="terminated" {{ request('status') === 'terminated' ? 'selected' : '' }}>Terminated</option>
                    </select>
                </div>

                <!-- Type Filter -->
                <div>
                    <label for="type" class="block text-xs font-semibold text-text-muted mb-1">Agreement Type</label>
                    <select 
                        name="type" 
                        id="type" 
                        class="w-full py-2 px-3 text-xs rounded-lg border border-border bg-surface text-text focus:ring-2 focus:ring-primary focus:border-transparent transition"
                    >
                        <option value="">All Types</option>
                        <option value="university_company" {{ request('type') === 'university_company' ? 'selected' : '' }}>University-Wide (Umbrella)</option>
                        <option value="student_company" {{ request('type') === 'student_company' ? 'selected' : '' }}>Student-Specific</option>
                    </select>
                </div>

                <!-- Custodian Party Filter -->
                <div>
                    <label for="custodian_party" class="block text-xs font-semibold text-text-muted mb-1">Current Custodian Party</label>
                    <select 
                        name="custodian_party" 
                        id="custodian_party" 
                        class="w-full py-2 px-3 text-xs rounded-lg border border-border bg-surface text-text focus:ring-2 focus:ring-primary focus:border-transparent transition"
                    >
                        <option value="">All Custodians</option>
                        <option value="oc" {{ request('custodian_party') === 'oc' ? 'selected' : '' }}>OJT Coordinator (OC)</option>
                        <option value="coc" {{ request('custodian_party') === 'coc' ? 'selected' : '' }}>Co-Coordinator (COC)</option>
                        <option value="ua" {{ request('custodian_party') === 'ua' ? 'selected' : '' }}>University Attorney (UA)</option>
                        <option value="company" {{ request('custodian_party') === 'company' ? 'selected' : '' }}>Partner Company</option>
                        <option value="student" {{ request('custodian_party') === 'student' ? 'selected' : '' }}>Student</option>
                    </select>
                </div>
            </div>

            <div class="flex items-center justify-between pt-2 border-t border-border">
                <span class="text-xs text-text-muted">Rule BR-16: Unconstrained multi-party custody tracking</span>
                <div class="flex items-center gap-2">
                    @if(request()->hasAny(['search', 'status', 'type', 'custodian_party']))
                        <a href="{{ route('moas.index') }}" class="text-xs font-medium text-text-muted hover:text-text px-3 py-1.5 rounded-lg transition">
                            Reset
                        </a>
                    @endif
                    <button type="submit" class="px-4 py-1.5 rounded-lg text-xs font-semibold bg-surface-2 text-text border border-border hover:bg-surface-3 transition">
                        Filter Agreements
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- MOA Directory Cards Grid -->
    @if ($moas->isEmpty())
        <div class="bg-surface border border-border rounded-xl p-12 text-center">
            <div class="w-12 h-12 mx-auto rounded-full bg-surface-2 flex items-center justify-center text-text-muted mb-3">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                </svg>
            </div>
            <h3 class="text-sm font-semibold text-text">No memorandum of agreements found</h3>
            <p class="text-xs text-text-muted mt-1 max-w-sm mx-auto">No records match your selected criteria. Try adjusting your filters or draft a new MOA.</p>
            @can('create', App\Models\Moa::class)
                <div class="mt-4">
                    <a href="{{ route('moas.create') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold bg-primary text-primary-contrast hover:opacity-90 transition">
                        Draft First MOA
                    </a>
                </div>
            @endcan
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
            @foreach ($moas as $moa)
                <div class="bg-surface border border-border rounded-xl p-5 flex flex-col justify-between hover:border-primary/40 hover:shadow-sm transition">
                    <div>
                        <!-- Header with Company and Type -->
                        <div class="flex items-start justify-between gap-3 mb-2.5">
                            <div class="min-w-0">
                                <h3 class="text-sm font-bold text-text truncate">
                                    <a href="{{ route('moas.show', $moa) }}" class="hover:text-primary transition">
                                        {{ $moa->company->name }}
                                    </a>
                                </h3>
                                <p class="text-[11px] text-text-muted truncate">
                                    {{ $moa->moa_number ?: 'MOA #' . $moa->id }}
                                </p>
                            </div>
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider {{ $moa->type->value === 'university_company' ? 'bg-primary/10 text-primary border border-primary/20' : 'bg-surface-2 text-text-muted border border-border' }}">
                                {{ $moa->type->value === 'university_company' ? 'Umbrella' : 'Individual' }}
                            </span>
                        </div>

                        <!-- Status & Custody Badges -->
                        <div class="flex flex-wrap items-center gap-1.5 mb-3">
                            <!-- Status -->
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold {{ $moa->status->badgeClasses() }}">
                                {{ $moa->status->label() }}
                            </span>

                            <!-- Custodian Party -->
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-semibold {{ $moa->current_custodian_party->badgeClasses() }}">
                                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                                </svg>
                                In Custody: {{ $moa->current_custodian_party->shortLabel() }}
                            </span>
                        </div>

                        <!-- Summary or Student details -->
                        @if ($moa->studentProfile)
                            <div class="text-xs text-text-muted mb-2 flex items-center gap-1.5">
                                <span class="font-medium text-text">Intern:</span>
                                <span class="truncate">{{ $moa->studentProfile->user->name }} ({{ $moa->studentProfile->student_number }})</span>
                            </div>
                        @endif

                        @if ($moa->summary)
                            <p class="text-xs text-text-muted line-clamp-2 mb-3 leading-relaxed">
                                {{ $moa->summary }}
                            </p>
                        @endif

                        <!-- Dates -->
                        <div class="pt-2 border-t border-border flex items-center justify-between text-[11px] text-text-muted">
                            <div>
                                Effective: <strong class="text-text">{{ $moa->effective_date?->format('M d, Y') ?? 'Pending' }}</strong>
                            </div>
                            <div>
                                Expires: <strong class="text-text">{{ $moa->expiry_date?->format('M d, Y') ?? 'N/A' }}</strong>
                            </div>
                        </div>
                    </div>

                    <!-- Footer actions -->
                    <div class="mt-4 pt-3 border-t border-border flex items-center justify-between">
                        @if ($moa->latestVersion?->document)
                            <a href="{{ route('documents.download', $moa->latestVersion->document) }}" class="text-xs text-primary font-medium hover:underline inline-flex items-center gap-1">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" />
                                </svg>
                                v{{ $moa->latestVersion->version_no }} PDF
                            </a>
                        @else
                            <span class="text-[11px] text-text-muted italic">No file attached</span>
                        @endif

                        <div class="flex items-center gap-2">
                            @can('update', $moa)
                                <a href="{{ route('moas.edit', $moa) }}" class="p-1 rounded text-text-muted hover:text-text hover:bg-surface-2 transition" title="Edit MOA Details">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125" />
                                    </svg>
                                </a>
                            @endcan
                            <a href="{{ route('moas.show', $moa) }}" class="px-2.5 py-1 rounded-lg text-xs font-semibold bg-surface-2 text-text border border-border hover:bg-surface-3 transition">
                                Details & Custody
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-6">
            {{ $moas->links() }}
        </div>
    @endif
</x-app-layout>
