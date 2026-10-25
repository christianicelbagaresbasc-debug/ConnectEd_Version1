<x-app-layout>
    @section('title', 'OJT Coordinator Command Center')

    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-xl font-bold text-text">OJT Coordinator Command Center</h1>
                <p class="text-xs text-text-muted mt-0.5">Institutional supervision, placement monitoring, and partner accreditation (FR-18).</p>
            </div>
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold bg-primary/10 text-primary border border-primary/20">
                    <span class="w-2 h-2 rounded-full bg-primary animate-pulse"></span>
                    <span>SY {{ config('connected.current_school_year', '2026-2027') }} Active</span>
                </span>
            </div>
        </div>
    </x-slot>

    <div class="space-y-6">
        <!-- Top Metrics KPI Grid -->
        <div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Total & Placed -->
            <div class="bg-surface border border-border rounded-xl p-4 shadow-sm flex flex-col justify-between">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-text-muted">Total Cohort</span>
                    <span class="p-1.5 rounded-lg bg-primary/10 text-primary">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
                        </svg>
                    </span>
                </div>
                <div class="mt-3">
                    <div class="text-2xl font-bold text-text">{{ $totalStudentsCount }}</div>
                    <div class="text-xs text-success font-medium mt-0.5">
                        {{ $placedStudentsCount }} placed ({{ $totalStudentsCount > 0 ? round(($placedStudentsCount / $totalStudentsCount) * 100) : 0 }}%)
                    </div>
                </div>
            </div>

            <!-- Students Without a Company (Priority Widget) -->
            <div class="bg-surface border border-warning/30 rounded-xl p-4 shadow-sm flex flex-col justify-between bg-warning/5">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-warning">Seeking Placement</span>
                    <span class="p-1.5 rounded-lg bg-warning/20 text-warning">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
                        </svg>
                    </span>
                </div>
                <div class="mt-3">
                    <div class="text-2xl font-bold text-warning">{{ $unplacedCount }}</div>
                    <div class="text-xs text-text-muted mt-0.5">Needs placement intervention</div>
                </div>
            </div>

            <!-- Pending Approvals & Verifications -->
            <div class="bg-surface border border-border rounded-xl p-4 shadow-sm flex flex-col justify-between">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-text-muted">Pending Action</span>
                    <span class="p-1.5 rounded-lg bg-danger/10 text-danger">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25ZM6.75 12h.008v.008H6.75V12Zm0 3h.008v.008H6.75V15Zm0 3h.008v.008H6.75V18Z" />
                        </svg>
                    </span>
                </div>
                <div class="mt-3">
                    <div class="text-2xl font-bold text-text">{{ $pendingRegistrationsCount + $pendingVerificationsCount }}</div>
                    <div class="text-xs text-text-muted mt-0.5">
                        {{ $pendingRegistrationsCount }} student accounts &bull; {{ $pendingVerificationsCount }} companies
                    </div>
                </div>
            </div>

            <!-- Active MOAs & Placements -->
            <div class="bg-surface border border-border rounded-xl p-4 shadow-sm flex flex-col justify-between">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-text-muted">Legal & Placement</span>
                    <span class="p-1.5 rounded-lg bg-success/10 text-success">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z" />
                        </svg>
                    </span>
                </div>
                <div class="mt-3">
                    <div class="text-2xl font-bold text-text">{{ $moaBreakdown['executed'] }}</div>
                    <div class="text-xs text-text-muted mt-0.5">
                        Executed MOAs &bull; {{ $activePlacementsCount }} active interns
                    </div>
                </div>
            </div>
        </div>

        <!-- PRIMARY WIDGET: Students Without a Company (FR-18) -->
        <div class="bg-surface border border-border rounded-xl shadow-sm overflow-hidden">
            <div class="p-5 border-b border-border flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-warning"></span>
                        <h2 class="text-base font-bold text-text">Students Without a Company</h2>
                    </div>
                    <p class="text-xs text-text-muted mt-0.5">Priority cohort monitoring of unplaced ICS students requiring opportunity assignment (FR-18).</p>
                </div>
                <!-- Quick Filter Form -->
                <form method="GET" action="{{ route('dashboard') }}" class="flex items-center gap-2">
                    <select 
                        name="program" 
                        onchange="this.form.submit()" 
                        class="py-1.5 px-3 text-xs rounded-lg border border-border bg-surface text-text font-medium focus:ring-1 focus:ring-primary"
                    >
                        <option value="">All Programs</option>
                        @foreach ($allowedPrograms as $prog)
                            <option value="{{ $prog }}" {{ request('program') === $prog ? 'selected' : '' }}>{{ $prog }}</option>
                        @endforeach
                    </select>
                    <input 
                        type="text" 
                        name="search" 
                        value="{{ request('search') }}" 
                        placeholder="Search student #..." 
                        class="py-1.5 px-3 text-xs rounded-lg border border-border bg-surface text-text focus:ring-1 focus:ring-primary"
                    />
                    <button type="submit" class="py-1.5 px-3 rounded-lg text-xs font-semibold bg-surface-2 text-text border border-border hover:bg-surface-3 transition">
                        Filter
                    </button>
                </form>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-text divide-y divide-border">
                    <thead class="bg-surface-2 text-text-muted font-semibold uppercase tracking-wider text-[11px]">
                        <tr>
                            <th class="py-3 px-4">Student</th>
                            <th class="py-3 px-4">Student #</th>
                            <th class="py-3 px-4">Program & Year</th>
                            <th class="py-3 px-4">Assigned Co-Coordinator</th>
                            <th class="py-3 px-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border">
                        @forelse ($unplacedStudents as $student)
                            <tr class="hover:bg-surface-2/40 transition">
                                <td class="py-3 px-4 font-semibold text-text">
                                    {{ $student->user->name }}
                                </td>
                                <td class="py-3 px-4 font-mono text-[11px] text-text font-semibold">
                                    {{ $student->student_number }}
                                </td>
                                <td class="py-3 px-4 text-text-muted">
                                    {{ $student->program }} &bull; {{ $student->year_level }}
                                </td>
                                <td class="py-3 px-4">
                                    @if ($student->assignedCoc)
                                        <span class="inline-flex items-center gap-1 font-medium text-text">
                                            <span class="w-1.5 h-1.5 rounded-full bg-primary"></span>
                                            {{ $student->assignedCoc->name }}
                                        </span>
                                    @else
                                        <span class="text-text-muted italic text-[11px]">Unassigned</span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-right">
                                    <a href="{{ route('coordinator.students.show', $student) }}" class="text-primary font-semibold hover:underline">
                                        View Profile &rarr;
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-8 text-center text-text-muted">
                                    <svg class="w-8 h-8 text-success mx-auto mb-2" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                    </svg>
                                    <span class="font-semibold text-text">All active students are currently placed!</span>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($unplacedStudents->hasPages())
                <div class="p-3 border-t border-border">
                    {{ $unplacedStudents->links() }}
                </div>
            @endif
        </div>

        <!-- 2-Column Operational Grid: MOA Tracking & Tasks Delegation -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- MOA Pipeline Status -->
            <div class="bg-surface border border-border rounded-xl p-5 shadow-sm space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-border">
                    <div>
                        <h3 class="text-sm font-bold text-text">MOA Agreement Pipeline</h3>
                        <p class="text-xs text-text-muted">Current status of institutional training agreements</p>
                    </div>
                    <a href="{{ route('moas.index') }}" class="text-xs font-semibold text-primary hover:underline">
                        View All MOAs &rarr;
                    </a>
                </div>

                <div class="grid grid-cols-3 gap-3">
                    <div class="p-3 rounded-lg bg-surface-2 border border-border text-center">
                        <div class="text-[11px] font-semibold text-text-muted">Draft / Review</div>
                        <div class="text-lg font-bold text-text mt-1">{{ $moaBreakdown['draft'] + $moaBreakdown['under_review'] }}</div>
                    </div>
                    <div class="p-3 rounded-lg bg-warning/10 border border-warning/20 text-center">
                        <div class="text-[11px] font-semibold text-warning">Legal Review</div>
                        <div class="text-lg font-bold text-warning mt-1">{{ $moaBreakdown['under_legal_review'] }}</div>
                    </div>
                    <div class="p-3 rounded-lg bg-success/10 border border-success/20 text-center">
                        <div class="text-[11px] font-semibold text-success">Executed</div>
                        <div class="text-lg font-bold text-success mt-1">{{ $moaBreakdown['executed'] }}</div>
                    </div>
                </div>
            </div>

            <!-- Task Delegation Overview -->
            <div class="bg-surface border border-border rounded-xl p-5 shadow-sm space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-border">
                    <div>
                        <h3 class="text-sm font-bold text-text">Task Delegation & Workload</h3>
                        <p class="text-xs text-text-muted">OC-to-COC delegation queue (FR-19)</p>
                    </div>
                    <a href="{{ route('tasks.index') }}" class="text-xs font-semibold text-primary hover:underline">
                        Manage Tasks &rarr;
                    </a>
                </div>

                <div class="grid grid-cols-3 gap-3">
                    <div class="p-3 rounded-lg bg-surface-2 border border-border text-center">
                        <div class="text-[11px] font-semibold text-text-muted">To Do</div>
                        <div class="text-lg font-bold text-text mt-1">{{ $taskStats['todo'] }}</div>
                    </div>
                    <div class="p-3 rounded-lg bg-warning/10 border border-warning/20 text-center">
                        <div class="text-[11px] font-semibold text-warning">In Progress</div>
                        <div class="text-lg font-bold text-warning mt-1">{{ $taskStats['in_progress'] }}</div>
                    </div>
                    <div class="p-3 rounded-lg bg-success/10 border border-success/20 text-center">
                        <div class="text-[11px] font-semibold text-success">Done</div>
                        <div class="text-lg font-bold text-success mt-1">{{ $taskStats['done'] }}</div>
                    </div>
                </div>

                @if ($recentTasks->isNotEmpty())
                    <div class="space-y-2 pt-1">
                        <div class="text-xs font-semibold text-text-muted">Upcoming Delegated Tasks:</div>
                        @foreach ($recentTasks as $task)
                            <div class="flex items-center justify-between p-2 rounded-lg bg-surface-2 text-xs">
                                <span class="font-medium text-text truncate max-w-[200px]">{{ $task->title }}</span>
                                <span class="text-text-muted">Assigned to: {{ $task->assignee?->name ?? 'Unassigned' }}</span>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        <!-- 2-Column: Attendance Policy Scope Notice & Recent Audit Logs -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Attendance Visibility Placeholder Card (Per Product Spec Section 9) -->
            <div class="bg-surface border border-dashed border-border rounded-xl p-5 shadow-sm space-y-3">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="p-1.5 rounded-lg bg-surface-2 text-text-muted">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                            </svg>
                        </span>
                        <h3 class="text-sm font-bold text-text">DTR & Attendance Tracking</h3>
                    </div>
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-surface-2 text-text-muted border border-border">
                        Not in current scope
                    </span>
                </div>
                <p class="text-xs text-text-muted leading-relaxed">
                    Per BSAU capstone specifications, full biometric / daily time recording (DTR), clock-in/out, and payroll modules are excluded from ConnectEd's operational boundaries. Intern progress is monitored through verified weekly accomplishment narratives in the Document Library.
                </p>
                <div class="pt-2">
                    <a href="{{ route('coordinator.placements.index') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-primary hover:underline">
                        <span>Review Weekly Narrative Reports</span>
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                        </svg>
                    </a>
                </div>
            </div>

            <!-- Recent System Audit Log -->
            <div class="bg-surface border border-border rounded-xl p-5 shadow-sm space-y-3">
                <div class="flex items-center justify-between pb-2 border-b border-border">
                    <h3 class="text-sm font-bold text-text">Recent System Audit Trail</h3>
                    <a href="{{ route('coordinator.audit-logs.index') }}" class="text-xs font-semibold text-primary hover:underline">
                        Full Audit Log &rarr;
                    </a>
                </div>

                <div class="space-y-2">
                    @forelse ($recentAudits as $audit)
                        <div class="flex items-center justify-between text-xs py-1 border-b border-border/50 last:border-none">
                            <div class="truncate max-w-[280px]">
                                <span class="font-mono text-primary font-semibold">{{ $audit->action }}</span>
                                <span class="text-text-muted">&bull; {{ $audit->actor?->name ?? 'System' }}</span>
                            </div>
                            <span class="text-text-muted text-[11px] flex-shrink-0">{{ $audit->created_at->diffForHumans() }}</span>
                        </div>
                    @empty
                        <div class="text-xs text-text-muted py-4 text-center">No recent audit logs.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
