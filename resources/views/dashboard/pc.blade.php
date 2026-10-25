<x-app-layout>
    @section('title', 'Partner Company Portal')

    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-xl font-bold text-text">{{ $company->name ?? 'Partner Company' }} Portal</h1>
                <p class="text-xs text-text-muted mt-0.5">OJT opportunity management, applicant evaluations, and intern supervision.</p>
            </div>
            <div>
                <a href="{{ route('opportunities.create') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold bg-primary text-white hover:bg-primary-600 transition">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    <span>Post New Opportunity</span>
                </a>
            </div>
        </div>
    </x-slot>

    <!-- BR-20 Transparency Reminder Banner -->
    <div class="mb-6 p-4 rounded-xl bg-surface border border-border text-xs text-text-muted flex items-start gap-3 shadow-sm">
        <div class="w-7 h-7 rounded-lg bg-surface-2 flex items-center justify-center text-text-muted flex-shrink-0 mt-0.5">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
            </svg>
        </div>
        <div>
            <strong class="text-text font-semibold">Institutional Rule BR-20:</strong>
            Student applications are listed in the order they were submitted. Match scores are strictly private to students and are never visible or used to rank applicants.
        </div>
    </div>

    <div class="space-y-6">
        <!-- KPI Metrics Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="bg-surface border border-border rounded-xl p-4 shadow-sm">
                <div class="text-xs font-semibold text-text-muted">Active Opportunities</div>
                <div class="text-2xl font-bold text-text mt-1">{{ $activeOpeningsCount }}</div>
                <div class="text-xs text-text-muted mt-0.5">{{ $opportunities->count() }} total listings posted</div>
            </div>

            <div class="bg-surface border border-border rounded-xl p-4 shadow-sm">
                <div class="text-xs font-semibold text-text-muted">Total Applicants</div>
                <div class="text-2xl font-bold text-primary mt-1">{{ $totalApplicationsCount }}</div>
                <div class="text-xs text-text-muted mt-0.5">{{ $pendingApplicants->count() }} awaiting formal review</div>
            </div>

            <div class="bg-surface border border-border rounded-xl p-4 shadow-sm">
                <div class="text-xs font-semibold text-text-muted">Active Placed Interns</div>
                <div class="text-2xl font-bold text-success mt-1">{{ $activeInterns->count() }}</div>
                <div class="text-xs text-success font-medium mt-0.5">Currently training in your organization</div>
            </div>
        </div>

        <!-- 2-Column: Opportunities & Slots + Applicants Awaiting Review -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Opportunities Overview -->
            <div class="bg-surface border border-border rounded-xl shadow-sm overflow-hidden flex flex-col justify-between">
                <div class="p-5 border-b border-border flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-bold text-text">Posted OJT Opportunities</h3>
                        <p class="text-xs text-text-muted">Openings and application numbers</p>
                    </div>
                    <a href="{{ route('opportunities.index') }}" class="text-xs font-semibold text-primary hover:underline">
                        Manage All &rarr;
                    </a>
                </div>

                <div class="divide-y divide-border flex-1">
                    @forelse ($opportunities->take(5) as $opp)
                        <div class="p-4 flex items-center justify-between gap-3 hover:bg-surface-2/40 transition">
                            <div class="space-y-1 min-w-0">
                                <div class="flex items-center gap-2">
                                    <span class="px-1.5 py-0.5 rounded text-[10px] font-semibold border {{ $opp->status->badgeClasses() }}">
                                        {{ $opp->status->label() }}
                                    </span>
                                    <span class="text-[11px] text-text-muted">{{ $opp->work_arrangement->label() }}</span>
                                </div>
                                <div class="text-xs font-bold text-text truncate">
                                    {{ $opp->title }}
                                </div>
                                <div class="text-[11px] text-text-muted">
                                    {{ $opp->slots_available }} slots &bull; {{ $opp->applications_count }} applicants
                                </div>
                            </div>

                            <a href="{{ route('opportunities.show', $opp) }}" class="p-2 rounded-lg text-text-muted hover:text-text hover:bg-surface-2 transition flex-shrink-0">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                                </svg>
                            </a>
                        </div>
                    @empty
                        <div class="py-8 text-center text-xs text-text-muted">
                            No opportunities posted yet. Click "Post New Opportunity" above to recruit interns.
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Applicants Awaiting Review -->
            <div class="bg-surface border border-border rounded-xl shadow-sm overflow-hidden flex flex-col justify-between">
                <div class="p-5 border-b border-border flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-bold text-text">Applicants Awaiting Review</h3>
                        <p class="text-xs text-text-muted">New submissions ordered chronologically (BR-20)</p>
                    </div>
                    <a href="{{ route('company.applications.index') }}" class="text-xs font-semibold text-primary hover:underline">
                        Review All &rarr;
                    </a>
                </div>

                <div class="divide-y divide-border flex-1">
                    @forelse ($pendingApplicants as $app)
                        <div class="p-4 flex items-center justify-between gap-3 hover:bg-surface-2/40 transition">
                            <div class="space-y-1 min-w-0">
                                <div class="text-xs font-bold text-text truncate">
                                    {{ $app->studentProfile->user->name }}
                                </div>
                                <div class="text-[11px] text-text-muted truncate">
                                    For: {{ $app->opportunity->title }}
                                </div>
                                <div class="text-[10px] text-text-muted">
                                    Submitted {{ $app->created_at->diffForHumans() }}
                                </div>
                            </div>

                            <a href="{{ route('company.applications.show', $app) }}" class="px-2.5 py-1 rounded-lg text-xs font-semibold bg-primary text-white hover:bg-primary-600 transition flex-shrink-0">
                                Evaluate
                            </a>
                        </div>
                    @empty
                        <div class="py-8 text-center text-xs text-text-muted">
                            No applications currently awaiting review.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Active Interns Roster -->
        <div class="bg-surface border border-border rounded-xl shadow-sm overflow-hidden">
            <div class="p-5 border-b border-border flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-bold text-text">Active Intern Roster</h3>
                    <p class="text-xs text-text-muted">Students currently undertaking OJT training with your organization</p>
                </div>
                <a href="{{ route('company.placements.index') }}" class="text-xs font-semibold text-primary hover:underline">
                    View Placement Details &rarr;
                </a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-text divide-y divide-border">
                    <thead class="bg-surface-2 text-text-muted font-semibold uppercase tracking-wider text-[11px]">
                        <tr>
                            <th class="py-3 px-4">Student Intern</th>
                            <th class="py-3 px-4">Student #</th>
                            <th class="py-3 px-4">Position / Opportunity</th>
                            <th class="py-3 px-4">Start Date</th>
                            <th class="py-3 px-4 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border">
                        @forelse ($activeInterns as $placement)
                            <tr class="hover:bg-surface-2/40 transition">
                                <td class="py-3 px-4 font-semibold text-text">
                                    {{ $placement->studentProfile->user->name }}
                                </td>
                                <td class="py-3 px-4 font-mono text-[11px] text-text font-semibold">
                                    {{ $placement->studentProfile->student_number }}
                                </td>
                                <td class="py-3 px-4 text-text-muted">
                                    {{ $placement->opportunity->title ?? 'OJT Trainee' }}
                                </td>
                                <td class="py-3 px-4 text-text-muted">
                                    {{ $placement->start_date?->format('M d, Y') ?? 'Pending formal start' }}
                                </td>
                                <td class="py-3 px-4 text-right">
                                    <a href="{{ route('company.placements.show', $placement) }}" class="text-primary font-semibold hover:underline">
                                        View &rarr;
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-8 text-center text-xs text-text-muted">
                                    No active placed interns currently recorded.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
