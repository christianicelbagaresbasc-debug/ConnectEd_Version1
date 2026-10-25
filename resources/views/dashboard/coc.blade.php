<x-app-layout>
    @section('title', 'Co-Coordinator Workspace')

    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-xl font-bold text-text">Co-Coordinator Workspace</h1>
                <p class="text-xs text-text-muted mt-0.5">Assigned cohort supervision, delegated tasks, and weekly report reviews (FR-19, FR-20).</p>
            </div>
            <div>
                <a href="{{ route('tasks.index') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold bg-primary text-white hover:bg-primary-600 transition">
                    <span>View All My Tasks</span>
                </a>
            </div>
        </div>
    </x-slot>

    <div class="space-y-6">
        <!-- KPI Metrics Grid -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
            <div class="bg-surface border border-border rounded-xl p-4 shadow-sm">
                <div class="text-xs font-semibold text-text-muted">Assigned Students</div>
                <div class="text-2xl font-bold text-text mt-1">{{ $assignedStudentsCount }}</div>
                <div class="text-xs text-success font-medium mt-0.5">{{ $assignedPlacedCount }} placed &bull; {{ $assignedUnplacedCount }} seeking</div>
            </div>

            <div class="bg-surface border border-border rounded-xl p-4 shadow-sm">
                <div class="text-xs font-semibold text-text-muted">Tasks Pending</div>
                <div class="text-2xl font-bold text-warning mt-1">{{ $myTaskCounts['todo'] + $myTaskCounts['in_progress'] }}</div>
                <div class="text-xs text-text-muted mt-0.5">{{ $myTaskCounts['done'] }} completed</div>
            </div>

            <div class="bg-surface border border-border rounded-xl p-4 shadow-sm">
                <div class="text-xs font-semibold text-text-muted">Weekly Reports Awaiting Review</div>
                <div class="text-2xl font-bold text-primary mt-1">{{ $pendingWeeklyReports->count() }}</div>
                <div class="text-xs text-text-muted mt-0.5">Assigned cohort submissions</div>
            </div>

            <div class="bg-surface border border-border rounded-xl p-4 shadow-sm">
                <div class="text-xs font-semibold text-text-muted">Supervised MOAs</div>
                <div class="text-2xl font-bold text-text mt-1">{{ $assignedMoas->count() }}</div>
                <div class="text-xs text-text-muted mt-0.5">Active partner agreements</div>
            </div>
        </div>

        <!-- 2-Column: My Delegated Tasks & Weekly Reports Awaiting Review -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- My Delegated Tasks Card -->
            <div class="bg-surface border border-border rounded-xl shadow-sm overflow-hidden flex flex-col justify-between">
                <div class="p-5 border-b border-border flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-bold text-text">My Delegated Tasks</h3>
                        <p class="text-xs text-text-muted">Assigned to you by the OJT Coordinator (FR-19)</p>
                    </div>
                    <a href="{{ route('tasks.index') }}" class="text-xs font-semibold text-primary hover:underline">
                        View All ({{ $myTaskCounts['total'] }}) &rarr;
                    </a>
                </div>

                <div class="divide-y divide-border flex-1">
                    @forelse ($myTasks as $task)
                        <div class="p-4 flex items-center justify-between gap-3 hover:bg-surface-2/40 transition">
                            <div class="space-y-1 min-w-0">
                                <div class="flex items-center gap-2">
                                    <span class="px-1.5 py-0.5 rounded text-[10px] font-semibold border {{ $task->priority->badgeClasses() }}">
                                        {{ $task->priority->label() }}
                                    </span>
                                    @if ($task->due_date)
                                        <span class="text-[11px] text-text-muted">Due {{ $task->due_date->format('M d') }}</span>
                                    @endif
                                </div>
                                <div class="text-xs font-semibold text-text truncate {{ $task->isDone() ? 'line-through text-text-muted' : '' }}">
                                    {{ $task->title }}
                                </div>
                                @if ($task->studentProfile)
                                    <div class="text-[11px] text-text-muted">
                                        Student: {{ $task->studentProfile->user->name }}
                                    </div>
                                @endif
                            </div>

                            <form method="POST" action="{{ route('tasks.update', $task) }}" class="flex-shrink-0">
                                @csrf
                                @method('PATCH')
                                <select 
                                    name="status" 
                                    onchange="this.form.submit()" 
                                    class="py-1 px-2 text-[11px] rounded-lg border border-border bg-surface text-text font-semibold focus:ring-1 focus:ring-primary"
                                >
                                    <option value="todo" {{ $task->status === \App\Enums\TaskStatus::TODO ? 'selected' : '' }}>To Do</option>
                                    <option value="in_progress" {{ $task->status === \App\Enums\TaskStatus::IN_PROGRESS ? 'selected' : '' }}>In Progress</option>
                                    <option value="done" {{ $task->status === \App\Enums\TaskStatus::DONE ? 'selected' : '' }}>Done</option>
                                </select>
                            </form>
                        </div>
                    @empty
                        <div class="py-8 text-center text-xs text-text-muted">
                            No delegated tasks currently assigned.
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Weekly Reports Awaiting Review -->
            <div class="bg-surface border border-border rounded-xl shadow-sm overflow-hidden flex flex-col justify-between">
                <div class="p-5 border-b border-border flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-bold text-text">Weekly Reports Awaiting Review</h3>
                        <p class="text-xs text-text-muted">Pending accomplishment reports from your cohort</p>
                    </div>
                    <a href="{{ route('coordinator.placements.index') }}" class="text-xs font-semibold text-primary hover:underline">
                        Placement Records &rarr;
                    </a>
                </div>

                <div class="divide-y divide-border flex-1">
                    @forelse ($pendingWeeklyReports as $report)
                        <div class="p-4 flex items-center justify-between gap-3 hover:bg-surface-2/40 transition">
                            <div class="space-y-1 min-w-0">
                                <div class="text-xs font-bold text-text truncate">
                                    {{ $report->placement->studentProfile->user->name }}
                                </div>
                                <div class="text-[11px] text-text-muted">
                                    Week of {{ $report->week_start->format('M d, Y') }} &bull; {{ $report->placement->company->name }}
                                </div>
                            </div>

                            <div class="flex items-center gap-2 flex-shrink-0">
                                @if ($report->document)
                                    <a href="{{ route('documents.download', $report->document) }}" class="p-1.5 rounded-lg border border-border bg-surface-2 text-text hover:bg-surface-3 transition text-xs font-medium" title="Download Document">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" />
                                        </svg>
                                    </a>
                                @endif
                                <form method="POST" action="{{ route('coordinator.weekly-reports.review', $report) }}" class="inline">
                                    @csrf
                                    <input type="hidden" name="status" value="reviewed">
                                    <button type="submit" class="px-2.5 py-1 rounded-lg text-xs font-semibold bg-success text-white hover:bg-success/90 transition">
                                        Approve
                                    </button>
                                </form>
                            </div>
                        </div>
                    @empty
                        <div class="py-8 text-center text-xs text-text-muted">
                            All weekly reports in your cohort have been reviewed!
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Supervised Cohort Students Table -->
        <div class="bg-surface border border-border rounded-xl shadow-sm overflow-hidden">
            <div class="p-5 border-b border-border flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-bold text-text">My Supervised Student Cohort</h3>
                    <p class="text-xs text-text-muted">Students assigned to you for ongoing supervision</p>
                </div>
                <a href="{{ route('coordinator.students.index', ['assigned_coc_id' => Auth::id()]) }}" class="text-xs font-semibold text-primary hover:underline">
                    View Full Directory ({{ $assignedStudentsCount }}) &rarr;
                </a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-text divide-y divide-border">
                    <thead class="bg-surface-2 text-text-muted font-semibold uppercase tracking-wider text-[11px]">
                        <tr>
                            <th class="py-3 px-4">Student</th>
                            <th class="py-3 px-4">Student #</th>
                            <th class="py-3 px-4">Program & Year</th>
                            <th class="py-3 px-4">Placement Status</th>
                            <th class="py-3 px-4 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border">
                        @forelse ($assignedStudents as $student)
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
                                    @if ($student->has_active_placement)
                                        <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded text-[11px] font-semibold bg-success/10 text-success border border-success/20">
                                            <span class="w-1.5 h-1.5 rounded-full bg-success"></span>
                                            Placed
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded text-[11px] font-semibold bg-warning/10 text-warning border border-warning/20">
                                            <span class="w-1.5 h-1.5 rounded-full bg-warning"></span>
                                            Seeking
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-right">
                                    <a href="{{ route('coordinator.students.show', $student) }}" class="text-primary font-semibold hover:underline">
                                        Profile &rarr;
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-8 text-center text-xs text-text-muted">
                                    No students currently assigned to your cohort.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
