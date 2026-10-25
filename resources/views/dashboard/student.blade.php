<x-app-layout>
    @section('title', 'Student Intern Hub')

    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-xl font-bold text-text">Welcome back, {{ Auth::user()->name }}</h1>
                <p class="text-xs text-text-muted mt-0.5">
                    {{ $studentProfile->program ?? 'BSIT' }} &bull; Year {{ $studentProfile->year_level ?? '4' }} &bull; Sec {{ $studentProfile->section ?? 'A' }} &bull; Student # {{ $studentProfile->student_number ?? 'N/A' }}
                </p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('student.recommendations.index') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold bg-primary text-white hover:bg-primary-600 transition">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09ZM18.259 8.715 18 9.75l-.259-1.035a3.375 3.375 0 0 0-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 0 0 2.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 0 0 2.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 0 0-2.456 2.456ZM16.894 20.567 16.5 21.75l-.394-1.183a2.25 2.25 0 0 0-1.423-1.423L13.5 18.75l1.183-.394a2.25 2.25 0 0 0 1.423-1.423l.394-1.183.394 1.183a2.25 2.25 0 0 0 1.423 1.423l1.183.394-1.183.394a2.25 2.25 0 0 0-1.423 1.423Z" />
                    </svg>
                    <span>Skill Recommendations</span>
                </a>
            </div>
        </div>
    </x-slot>

    <div class="space-y-6">
        <!-- Placement Status Hero Banner -->
        @if ($activePlacement)
            <div class="bg-gradient-to-r from-success/15 via-success/10 to-transparent border border-success/30 rounded-2xl p-6 shadow-sm">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div class="space-y-2">
                        <div class="flex items-center gap-2">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-success text-white">
                                <span class="w-1.5 h-1.5 rounded-full bg-white animate-pulse"></span>
                                Active Placement Confirmed
                            </span>
                            <span class="text-xs text-text-muted">Rule BR-19: Single Active Placement Enforced</span>
                        </div>
                        <h2 class="text-xl font-bold text-text">
                            {{ $activePlacement->company->name }}
                        </h2>
                        <p class="text-xs text-text-muted">
                            {{ $activePlacement->opportunity->title ?? 'OJT Intern' }} &bull; Authorized Start: {{ $activePlacement->start_date?->format('M d, Y') ?? 'Confirmed' }}
                        </p>
                    </div>

                    <div class="flex items-center gap-2 flex-shrink-0">
                        <a href="{{ route('student.placements.show', $activePlacement) }}" class="px-4 py-2 rounded-xl text-xs font-semibold bg-success text-white hover:bg-success/90 transition shadow-sm">
                            My Placement Hub &rarr;
                        </a>
                    </div>
                </div>
            </div>
        @else
            <div class="bg-surface border border-warning/30 rounded-2xl p-6 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-warning/5">
                <div class="space-y-1">
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-warning"></span>
                        <span class="text-xs font-bold uppercase tracking-wider text-warning">Seeking OJT Placement</span>
                    </div>
                    <h2 class="text-lg font-bold text-text">No active placement finalized yet</h2>
                    <p class="text-xs text-text-muted max-w-xl">
                        Explore opportunities matched transparently against your registered skills, or browse verified partner companies to submit applications.
                    </p>
                </div>

                <div class="flex items-center gap-2 flex-shrink-0">
                    <a href="{{ route('opportunities.index') }}" class="px-4 py-2 rounded-xl text-xs font-semibold bg-primary text-white hover:bg-primary-600 transition shadow-sm">
                        Browse Opportunities &rarr;
                    </a>
                </div>
            </div>
        @endif

        <!-- Profile Completeness Meter Card -->
        <div class="bg-surface border border-border rounded-xl p-5 shadow-sm space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                <div>
                    <h3 class="text-sm font-bold text-text">Profile Readiness & Verification</h3>
                    <p class="text-xs text-text-muted">Complete profile information ensures maximum match accuracy and coordinator approval.</p>
                </div>
                <div class="text-right">
                    <span class="text-lg font-bold text-primary">{{ $completenessScore }}%</span>
                    <span class="text-xs text-text-muted">Complete</span>
                </div>
            </div>

            <!-- Progress Bar -->
            <div class="w-full h-2.5 bg-surface-2 rounded-full overflow-hidden border border-border/50">
                <div 
                    class="h-full bg-primary transition-all duration-500 rounded-full {{ $completenessScore >= 80 ? 'bg-success' : ($completenessScore >= 50 ? 'bg-primary' : 'bg-warning') }}"
                    style="width: {{ $completenessScore }}%"
                ></div>
            </div>

            <!-- Items Checklist Grid -->
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3 pt-1">
                @foreach ($completenessItems as $label => $isComplete)
                    <div class="p-2.5 rounded-lg border {{ $isComplete ? 'bg-success/5 border-success/20 text-success' : 'bg-surface-2 border-border text-text-muted' }} text-xs flex items-center gap-2">
                        @if ($isComplete)
                            <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                            </svg>
                        @else
                            <div class="w-4 h-4 rounded-full border-2 border-text-muted/40 flex-shrink-0"></div>
                        @endif
                        <span class="font-medium truncate">{{ $label }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- If Unplaced: Top Skill Matches (Rule BR-20) -->
        @if (!$activePlacement && $recommendations->isNotEmpty())
            <div class="bg-surface border border-border rounded-xl shadow-sm p-5 space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-border">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="p-1 rounded-md bg-primary/10 text-primary">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09ZM18.259 8.715 18 9.75l-.259-1.035a3.375 3.375 0 0 0-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 0 0 2.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 0 0 2.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 0 0-2.456 2.456ZM16.894 20.567 16.5 21.75l-.394-1.183a2.25 2.25 0 0 0-1.423-1.423L13.5 18.75l1.183-.394a2.25 2.25 0 0 0 1.423-1.423l.394-1.183.394 1.183a2.25 2.25 0 0 0 1.423 1.423l1.183.394-1.183.394a2.25 2.25 0 0 0-1.423 1.423Z" />
                                </svg>
                            </span>
                            <h3 class="text-sm font-bold text-text">Top Skill-Match Recommendations</h3>
                        </div>
                        <p class="text-xs text-text-muted mt-0.5">Computed on the fly based on your acquired skills. Match scores are strictly private to you (BR-20).</p>
                    </div>
                    <a href="{{ route('student.recommendations.index') }}" class="text-xs font-semibold text-primary hover:underline">
                        View All Matches &rarr;
                    </a>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    @foreach ($recommendations as $rec)
                        @php
                            $opp = $rec['opportunity'];
                            $match = $rec['match'];
                        @endphp
                        <div class="p-4 rounded-xl border border-border bg-surface-2/40 flex flex-col justify-between space-y-3">
                            <div class="space-y-1.5">
                                <div class="flex items-center justify-between gap-2">
                                    <span class="text-xs font-bold text-text truncate">{{ $opp->company->name }}</span>
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold border {{ $match['badge_classes'] }}">
                                        {{ $match['score'] !== null ? $match['score'].'%' : 'No reqs' }} Match
                                    </span>
                                </div>
                                <h4 class="text-sm font-bold text-text line-clamp-1">{{ $opp->title }}</h4>
                                <div class="text-[11px] text-text-muted">
                                    {{ $opp->work_arrangement->label() }} &bull; {{ $opp->location }}
                                </div>
                            </div>

                            <div class="pt-2 border-t border-border flex items-center justify-between">
                                <span class="text-[11px] text-text-muted">
                                    {{ $match['matched_skills']->count() }} skills match
                                </span>
                                <a href="{{ route('opportunities.show', $opp) }}" class="text-xs font-semibold text-primary hover:underline">
                                    View & Apply &rarr;
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- My Active Applications Pipeline -->
        <div class="bg-surface border border-border rounded-xl shadow-sm overflow-hidden">
            <div class="p-5 border-b border-border flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-bold text-text">My Applications Pipeline</h3>
                    <p class="text-xs text-text-muted">Status of submitted opportunity applications (FR-12, FR-16)</p>
                </div>
                <a href="{{ route('student.applications.index') }}" class="text-xs font-semibold text-primary hover:underline">
                    View All Applications &rarr;
                </a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-text divide-y divide-border">
                    <thead class="bg-surface-2 text-text-muted font-semibold uppercase tracking-wider text-[11px]">
                        <tr>
                            <th class="py-3 px-4">Opportunity</th>
                            <th class="py-3 px-4">Company</th>
                            <th class="py-3 px-4">Submitted</th>
                            <th class="py-3 px-4">Application Status</th>
                            <th class="py-3 px-4 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border">
                        @forelse ($myApplications as $app)
                            <tr class="hover:bg-surface-2/40 transition">
                                <td class="py-3 px-4 font-bold text-text">
                                    {{ $app->opportunity->title }}
                                </td>
                                <td class="py-3 px-4 text-text-muted">
                                    {{ $app->opportunity->company->name }}
                                </td>
                                <td class="py-3 px-4 text-text-muted">
                                    {{ $app->created_at->format('M d, Y') }}
                                </td>
                                <td class="py-3 px-4">
                                    <span class="px-2 py-0.5 rounded text-[11px] font-semibold border {{ $app->status->badgeClasses() }}">
                                        {{ $app->status->label() }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-right">
                                    <a href="{{ route('student.applications.show', $app) }}" class="text-primary font-semibold hover:underline">
                                        View Details &rarr;
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-8 text-center text-xs text-text-muted">
                                    You have not submitted any applications yet.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
