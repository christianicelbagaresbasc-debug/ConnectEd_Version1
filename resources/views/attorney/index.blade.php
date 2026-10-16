<x-app-layout>
    @section('title', 'University Attorney Legal Review Queue')

    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-xl font-bold text-text">University Attorney Legal Review Queue</h1>
                <p class="text-xs text-text-muted mt-0.5">Audit statutory covenants, assess liability risk, log revision directives, and clear partnership MOAs.</p>
            </div>
            <a href="{{ route('moas.index') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold bg-surface-2 text-text border border-border hover:bg-surface-3 transition">
                &larr; View All MOAs Directory
            </a>
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

    <!-- Guidance Banner -->
    <div class="mb-6 p-4 rounded-xl bg-indigo-500/10 border border-indigo-500/20 flex items-start gap-3">
        <svg class="w-5 h-5 text-indigo-600 dark:text-indigo-400 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m0-10.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.75c0 5.592 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.57-.598-3.75h-.002A11.959 11.959 0 0 1 12 2.964Z" />
        </svg>
        <div class="text-xs text-text leading-relaxed">
            <strong class="font-semibold text-indigo-600 dark:text-indigo-400">Legal Counsel Authority & Confidentiality (Rule BR-18):</strong>
            As University Attorney, your legal risk ratings and revision directives are internal to university leadership (UA, OC, COC) and are strictly shielded from company and student access. Clearing an agreement authorizes execution by the University President or Authorized Representative.
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="bg-surface border border-border rounded-xl p-4 sm:p-5 mb-6 shadow-sm">
        <form method="GET" action="{{ route('attorney.moas.index') }}" class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            <div class="sm:col-span-2 relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-text-muted">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                    </svg>
                </div>
                <input 
                    type="text" 
                    name="search" 
                    value="{{ request('search') }}" 
                    placeholder="Search by company or MOA number..." 
                    class="w-full pl-9 pr-3 py-2 text-xs rounded-lg border border-border bg-surface text-text focus:ring-2 focus:ring-primary focus:border-transparent transition"
                />
            </div>

            <div>
                <select 
                    name="queue" 
                    onchange="this.form.submit()" 
                    class="w-full py-2 px-3 text-xs rounded-lg border border-border bg-surface text-text focus:ring-2 focus:ring-primary focus:border-transparent transition"
                >
                    <option value="pending" {{ request('queue') !== 'all' ? 'selected' : '' }}>Pending Legal Review / Open Revisions</option>
                    <option value="all" {{ request('queue') === 'all' ? 'selected' : '' }}>All MOAs</option>
                </select>
            </div>
        </form>
    </div>

    <!-- Review Queue Table -->
    @if ($moas->isEmpty())
        <div class="bg-surface border border-border rounded-xl p-12 text-center">
            <div class="w-12 h-12 mx-auto rounded-full bg-surface-2 flex items-center justify-center text-text-muted mb-3">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
            </div>
            <h3 class="text-sm font-semibold text-text">No agreements pending legal review</h3>
            <p class="text-xs text-text-muted mt-1 max-w-sm mx-auto">All submitted memorandum agreements have been processed or no open revision requests exist.</p>
        </div>
    @else
        <div class="space-y-4">
            @foreach ($moas as $moa)
                <div class="bg-surface border border-border rounded-xl p-5 shadow-sm hover:border-primary/30 transition">
                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                        <div class="flex-1 min-w-0">
                            <div class="flex flex-wrap items-center gap-2 mb-1.5">
                                <h3 class="text-base font-bold text-text truncate">
                                    <a href="{{ route('moas.show', $moa) }}" class="hover:text-primary transition">
                                        {{ $moa->company->name }}
                                    </a>
                                </h3>
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider {{ $moa->status->badgeClasses() }}">
                                    {{ $moa->status->label() }}
                                </span>
                                <span class="px-2 py-0.5 rounded text-[10px] font-semibold {{ $moa->current_custodian_party->badgeClasses() }}">
                                    In Custody: {{ $moa->current_custodian_party->shortLabel() }}
                                </span>
                            </div>

                            <p class="text-xs text-text-muted mb-2">
                                {{ $moa->moa_number ?: 'MOA #' . $moa->id }} &bull; {{ $moa->type->label() }}
                                @if ($moa->studentProfile)
                                    &bull; Intern: <strong class="text-text">{{ $moa->studentProfile->user->name }}</strong>
                                @endif
                            </p>

                            @if ($moa->summary)
                                <p class="text-xs text-text-muted line-clamp-2 mb-2 leading-relaxed">
                                    {{ $moa->summary }}
                                </p>
                            @endif

                            <!-- Open revisions and files -->
                            <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-text-muted">
                                @php
                                    $openRevisions = $moa->unresolvedNotes->count();
                                @endphp
                                <div>
                                    Open Revisions: 
                                    @if ($openRevisions > 0)
                                        <span class="font-bold text-danger">{{ $openRevisions }} pending</span>
                                    @else
                                        <span class="text-success font-medium">None</span>
                                    @endif
                                </div>

                                @if ($moa->latestVersion?->document)
                                    <div>
                                        Latest Draft: 
                                        <a href="{{ route('documents.download', $moa->latestVersion->document) }}" class="text-primary font-medium hover:underline">
                                            v{{ $moa->latestVersion->version_no }} ({{ $moa->latestVersion->document->original_filename }})
                                        </a>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Action Controls -->
                        <div class="flex items-center gap-2 flex-shrink-0">
                            <a href="{{ route('moas.show', $moa) }}" class="px-4 py-2 rounded-lg text-xs font-semibold bg-primary text-primary-contrast shadow hover:opacity-90 transition">
                                Conduct Legal Review &rarr;
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
