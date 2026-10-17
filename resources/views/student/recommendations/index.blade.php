<x-app-layout>
    @section('title', 'Best-Fit OJT Recommendations')

    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-xl font-bold text-text">Best-Fit OJT Recommendations</h1>
                <p class="text-xs text-text-muted mt-0.5">
                    Transparent, rule-based internship matching computed for your skill competencies (FR-11, F-013).
                </p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('student.profile.skills') }}" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg text-xs font-semibold bg-surface-2 text-text border border-border hover:bg-surface-3 transition">
                    <svg class="w-4 h-4 text-text-muted" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L6.832 19.82a4.5 4.5 0 0 1-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 0 1 1.13-1.897L16.863 4.487Zm0 0L19.5 7.125" />
                    </svg>
                    <span>Update My Skills ({{ $studentSkills->count() }})</span>
                </a>
            </div>
        </div>
    </x-slot>

    <!-- BR-20 Transparency & Confidentiality Banner -->
    <div class="mb-6 p-4 rounded-xl bg-primary/5 border border-primary/20 text-xs text-text flex items-start gap-3">
        <div class="w-8 h-8 rounded-lg bg-primary/10 flex items-center justify-center text-primary flex-shrink-0 mt-0.5">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
            </svg>
        </div>
        <div class="flex-1 leading-relaxed">
            <span class="font-bold text-text">Transparent, Student-Only Recommendation Policy (Rule BR-20):</span>
            <span class="text-text-muted">
                Match percentages are calculated deterministically (Required skills &times; 2 + Preferred skills &times; 1) and are <strong>strictly private to you</strong>.
                ConnectEd recommends opportunities to students, but <em>never ranks or sorts student applicants for companies or coordinators</em>.
            </span>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="bg-surface border border-border rounded-xl p-4 sm:p-5 mb-6 shadow-sm">
        <form method="GET" action="{{ route('student.recommendations.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
            <!-- Search -->
            <div class="lg:col-span-2">
                <label for="search" class="block text-xs font-semibold text-text-muted mb-1">Search Roles or Companies</label>
                <input 
                    type="text" 
                    name="search" 
                    id="search" 
                    value="{{ request('search') }}" 
                    placeholder="Search opportunity title or description..." 
                    class="w-full py-2 px-3 text-xs rounded-lg border border-border bg-surface text-text focus:ring-2 focus:ring-primary focus:border-transparent transition"
                />
            </div>

            <!-- Match Tier Filter -->
            <div>
                <label for="match_level" class="block text-xs font-semibold text-text-muted mb-1">Match Level</label>
                <select 
                    name="match_level" 
                    id="match_level" 
                    class="w-full py-2 px-3 text-xs rounded-lg border border-border bg-surface text-text focus:ring-2 focus:ring-primary focus:border-transparent transition"
                >
                    <option value="">All Match Levels</option>
                    <option value="Strong match" {{ request('match_level') === 'Strong match' ? 'selected' : '' }}>Strong Match (&ge; 80%)</option>
                    <option value="Good match" {{ request('match_level') === 'Good match' ? 'selected' : '' }}>Good Match (50% - 79%)</option>
                    <option value="Partial match" {{ request('match_level') === 'Partial match' ? 'selected' : '' }}>Partial Match (1% - 49%)</option>
                    <option value="Low match" {{ request('match_level') === 'Low match' ? 'selected' : '' }}>Low Match (0%)</option>
                </select>
            </div>

            <!-- Work Arrangement -->
            <div>
                <label for="work_arrangement" class="block text-xs font-semibold text-text-muted mb-1">Work Arrangement</label>
                <select 
                    name="work_arrangement" 
                    id="work_arrangement" 
                    class="w-full py-2 px-3 text-xs rounded-lg border border-border bg-surface text-text focus:ring-2 focus:ring-primary focus:border-transparent transition"
                >
                    <option value="">All Arrangements</option>
                    <option value="onsite" {{ request('work_arrangement') === 'onsite' ? 'selected' : '' }}>Onsite</option>
                    <option value="remote" {{ request('work_arrangement') === 'remote' ? 'selected' : '' }}>Remote</option>
                    <option value="hybrid" {{ request('work_arrangement') === 'hybrid' ? 'selected' : '' }}>Hybrid</option>
                </select>
            </div>

            <!-- Partner Company -->
            <div>
                <label for="company_id" class="block text-xs font-semibold text-text-muted mb-1">Partner Organization</label>
                <select 
                    name="company_id" 
                    id="company_id" 
                    class="w-full py-2 px-3 text-xs rounded-lg border border-border bg-surface text-text focus:ring-2 focus:ring-primary focus:border-transparent transition"
                >
                    <option value="">All Partners</option>
                    @foreach ($companies as $comp)
                        <option value="{{ $comp->id }}" {{ (string) request('company_id') === (string) $comp->id ? 'selected' : '' }}>
                            {{ $comp->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="sm:col-span-2 lg:col-span-5 flex items-center justify-end gap-2 pt-2 border-t border-border">
                @if (request()->hasAny(['search', 'match_level', 'work_arrangement', 'company_id']))
                    <a href="{{ route('student.recommendations.index') }}" class="px-3 py-1.5 rounded-lg text-xs font-medium text-text-muted hover:text-text transition">
                        Reset Filters
                    </a>
                @endif
                <button type="submit" class="px-4 py-1.5 rounded-lg text-xs font-semibold bg-surface-2 text-text border border-border hover:bg-surface-3 transition">
                    Filter Recommendations
                </button>
            </div>
        </form>
    </div>

    <!-- Recommendations Feed -->
    <div class="space-y-4 pb-12">
        @forelse ($recommendations as $item)
            @php
                $opp = $item['opportunity'];
                $match = $item['match'];
            @endphp
            <div class="bg-surface border border-border rounded-xl p-5 shadow-sm hover:border-primary/40 transition">
                <div class="flex flex-col md:flex-row md:items-start justify-between gap-4">
                    <!-- Left: Opportunity Metadata -->
                    <div class="flex-1 space-y-2">
                        <div class="flex items-center gap-2">
                            <span class="text-xs font-bold text-primary">{{ $opp->company->name }}</span>
                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-semibold bg-success/10 text-success border border-success/20">
                                Verified Partner
                            </span>
                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium bg-surface-2 text-text-muted border border-border">
                                {{ ucfirst($opp->work_arrangement->value) }}
                            </span>
                        </div>

                        <h2 class="text-base font-bold text-text">
                            <a href="{{ route('opportunities.show', $opp) }}" class="hover:text-primary transition">
                                {{ $opp->title }}
                            </a>
                        </h2>

                        <p class="text-xs text-text-muted line-clamp-2 leading-relaxed">
                            {{ $opp->description }}
                        </p>

                        <div class="flex flex-wrap items-center gap-3 text-[11px] text-text-muted pt-1">
                            @if ($opp->location)
                                <span class="flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" />
                                    </svg>
                                    {{ $opp->location }}
                                </span>
                            @endif
                            <span>{{ $opp->slotsAvailable() }} / {{ $opp->slots_total }} slots available</span>
                            @if ($opp->deadline)
                                <span>Deadline: {{ $opp->deadline->format('M d, Y') }}</span>
                            @endif
                        </div>
                    </div>

                    <!-- Right: Match Score Card -->
                    <div class="md:w-60 flex-shrink-0 bg-surface-2/40 border border-border rounded-xl p-4 flex flex-col items-center text-center justify-center">
                        <div class="text-[11px] font-bold uppercase tracking-wider text-text-muted mb-1">Your Match Score</div>
                        @if ($match['score'] !== null)
                            <div class="text-3xl font-black text-text tracking-tight">
                                {{ $match['score'] }}%
                            </div>
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold mt-1 {{ $match['badge_classes'] }}">
                                {{ $match['label'] }}
                            </span>
                            <div class="text-[10px] text-text-muted mt-2">
                                {{ $match['matched_required_count'] }}/{{ $match['total_required_count'] }} required &bull; {{ $match['matched_preferred_count'] }}/{{ $match['total_preferred_count'] }} preferred
                            </div>
                        @else
                            <div class="text-xs font-semibold text-text-muted py-2">
                                No skill requirements posted
                            </div>
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-medium {{ $match['badge_classes'] }}">
                                {{ $match['label'] }}
                            </span>
                        @endif

                        <div class="mt-3 w-full">
                            <a href="{{ route('opportunities.show', $opp) }}" class="block w-full py-1.5 px-3 text-xs font-semibold text-center rounded-lg bg-primary text-primary-contrast shadow hover:opacity-90 transition">
                                View Opening
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Transparent Reasoning Breakdown (Matched vs Missing Skills) -->
                <div class="mt-4 pt-4 border-t border-border grid grid-cols-1 sm:grid-cols-3 gap-3 text-xs">
                    <!-- Matched Skills -->
                    <div>
                        <div class="text-[10px] font-bold uppercase tracking-wider text-success mb-1 flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                            </svg>
                            <span>Matched Skills ({{ $match['matched_skills']->count() }})</span>
                        </div>
                        <div class="flex flex-wrap gap-1">
                            @forelse ($match['matched_skills'] as $mSkill)
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-success/10 text-success border border-success/20">
                                    {{ $mSkill->name }}
                                </span>
                            @empty
                                <span class="text-[11px] text-text-muted italic">None yet</span>
                            @endforelse
                        </div>
                    </div>

                    <!-- Missing Required Skills -->
                    <div>
                        <div class="text-[10px] font-bold uppercase tracking-wider text-danger mb-1 flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
                            </svg>
                            <span>Missing Required ({{ $match['missing_required']->count() }})</span>
                        </div>
                        <div class="flex flex-wrap gap-1">
                            @forelse ($match['missing_required'] as $mReq)
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-medium bg-danger/10 text-danger border border-danger/20">
                                    {{ $mReq->name }}
                                </span>
                            @empty
                                <span class="text-[11px] text-text-muted italic">All required satisfied!</span>
                            @endforelse
                        </div>
                    </div>

                    <!-- Missing Preferred Skills -->
                    <div>
                        <div class="text-[10px] font-bold uppercase tracking-wider text-text-muted mb-1 flex items-center gap-1">
                            <span>Missing Preferred ({{ $match['missing_preferred']->count() }})</span>
                        </div>
                        <div class="flex flex-wrap gap-1">
                            @forelse ($match['missing_preferred'] as $mPref)
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-medium bg-surface-2 text-text-muted border border-border">
                                    {{ $mPref->name }}
                                </span>
                            @empty
                                <span class="text-[11px] text-text-muted italic">None</span>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="bg-surface border border-border rounded-xl p-12 text-center shadow-sm">
                <svg class="w-12 h-12 text-text-muted mx-auto mb-3" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09ZM18.259 8.715 18 9.75l-.259-1.035a3.375 3.375 0 0 0-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 0 0 2.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 0 0 2.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 0 0-2.456 2.456ZM16.894 20.567 16.5 21.75l-.394-1.183a2.25 2.25 0 0 0-1.423-1.423L13.5 18.75l1.183-.394a2.25 2.25 0 0 0 1.423-1.423l.394-1.183.394 1.183a2.25 2.25 0 0 0 1.423 1.423l1.183.394-1.183.394a2.25 2.25 0 0 0-1.423 1.423Z" />
                </svg>
                <h3 class="text-sm font-bold text-text">No recommendations available</h3>
                <p class="text-xs text-text-muted mt-1 max-w-sm mx-auto">
                    Ensure you have skills added to your profile, or adjust your search filters to find opportunities.
                </p>
                <div class="mt-4">
                    <a href="{{ route('student.profile.skills') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-xs font-semibold bg-primary text-primary-contrast shadow hover:opacity-90 transition">
                        Update My Skills Profile
                    </a>
                </div>
            </div>
        @endforelse
    </div>
</x-app-layout>
