<x-app-layout>
    @section('title', 'Tasks & Workload Delegation')

    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-xl font-bold text-text">Tasks & Workload Delegation</h1>
                <p class="text-xs text-text-muted mt-0.5">Task creation, coordinator delegation, and cohort supervision tracking (FR-19).</p>
            </div>
            @if (Auth::user()->isOc())
                <div class="flex items-center gap-3" x-data>
                    <button 
                        @click="$dispatch('open-create-task')"
                        type="button" 
                        class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-semibold bg-primary text-white shadow-sm hover:bg-primary-600 transition"
                    >
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                        </svg>
                        <span>Create & Delegate Task</span>
                    </button>
                </div>
            @endif
        </div>
    </x-slot>

    <div x-data="{ 
        createModal: false, 
        editModal: false, 
        activeTask: null,
        openEdit(task) {
            this.activeTask = task;
            this.editModal = true;
        }
    }" 
    @open-create-task.window="createModal = true"
    class="space-y-6">

        <!-- Status Filter Tabs & Stats -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
            <a href="{{ route('tasks.index', array_merge(request()->except('status', 'page'), ['status' => 'all'])) }}" 
               class="p-4 rounded-xl border transition {{ !request('status') || request('status') === 'all' ? 'bg-primary-50/50 dark:bg-primary-950/20 border-primary-300 dark:border-primary-800' : 'bg-surface border-border hover:bg-surface-2' }}">
                <div class="text-xs font-semibold text-text-muted">All Tasks</div>
                <div class="text-2xl font-bold text-text mt-1">{{ $counts['all'] }}</div>
            </a>
            <a href="{{ route('tasks.index', array_merge(request()->except('status', 'page'), ['status' => 'todo'])) }}" 
               class="p-4 rounded-xl border transition {{ request('status') === 'todo' ? 'bg-slate-100 dark:bg-slate-800 border-slate-300 dark:border-slate-700' : 'bg-surface border-border hover:bg-surface-2' }}">
                <div class="text-xs font-semibold text-text-muted">To Do</div>
                <div class="text-2xl font-bold text-slate-700 dark:text-slate-300 mt-1">{{ $counts['todo'] }}</div>
            </a>
            <a href="{{ route('tasks.index', array_merge(request()->except('status', 'page'), ['status' => 'in_progress'])) }}" 
               class="p-4 rounded-xl border transition {{ request('status') === 'in_progress' ? 'bg-warning/10 border-warning/30' : 'bg-surface border-border hover:bg-surface-2' }}">
                <div class="text-xs font-semibold text-warning">In Progress</div>
                <div class="text-2xl font-bold text-warning mt-1">{{ $counts['in_progress'] }}</div>
            </a>
            <a href="{{ route('tasks.index', array_merge(request()->except('status', 'page'), ['status' => 'done'])) }}" 
               class="p-4 rounded-xl border transition {{ request('status') === 'done' ? 'bg-success/10 border-success/30' : 'bg-surface border-border hover:bg-surface-2' }}">
                <div class="text-xs font-semibold text-success">Completed</div>
                <div class="text-2xl font-bold text-success mt-1">{{ $counts['done'] }}</div>
            </a>
        </div>

        <!-- Filter Bar -->
        <div class="bg-surface border border-border rounded-xl p-4 shadow-sm">
            <form method="GET" action="{{ route('tasks.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                <input type="hidden" name="status" value="{{ request('status', 'all') }}">

                <!-- Search -->
                <div>
                    <label for="search" class="block text-xs font-semibold text-text-muted mb-1">Search Tasks</label>
                    <input 
                        type="text" 
                        name="search" 
                        id="search" 
                        value="{{ request('search') }}" 
                        placeholder="Search title or details..." 
                        class="w-full py-2 px-3 text-xs rounded-lg border border-border bg-surface text-text focus:ring-2 focus:ring-primary focus:border-transparent transition"
                    />
                </div>

                <!-- Priority -->
                <div>
                    <label for="priority" class="block text-xs font-semibold text-text-muted mb-1">Priority</label>
                    <select 
                        name="priority" 
                        id="priority" 
                        class="w-full py-2 px-3 text-xs rounded-lg border border-border bg-surface text-text focus:ring-2 focus:ring-primary focus:border-transparent transition"
                    >
                        <option value="all">All Priorities</option>
                        <option value="high" {{ request('priority') === 'high' ? 'selected' : '' }}>High Priority</option>
                        <option value="medium" {{ request('priority') === 'medium' ? 'selected' : '' }}>Medium Priority</option>
                        <option value="low" {{ request('priority') === 'low' ? 'selected' : '' }}>Low Priority</option>
                    </select>
                </div>

                <!-- Assignee (if OC) -->
                @if (Auth::user()->isOc())
                    <div>
                        <label for="assigned_to" class="block text-xs font-semibold text-text-muted mb-1">Assigned Coordinator</label>
                        <select 
                            name="assigned_to" 
                            id="assigned_to" 
                            class="w-full py-2 px-3 text-xs rounded-lg border border-border bg-surface text-text focus:ring-2 focus:ring-primary focus:border-transparent transition"
                        >
                            <option value="all">All Coordinators</option>
                            @foreach ($cocs as $coc)
                                <option value="{{ $coc->id }}" {{ (string) request('assigned_to') === (string) $coc->id ? 'selected' : '' }}>
                                    {{ $coc->name }} ({{ $coc->role->label() }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                @else
                    <div></div>
                @endif

                <div class="flex items-end gap-2">
                    <button type="submit" class="w-full py-2 px-4 rounded-lg text-xs font-semibold bg-surface-2 text-text border border-border hover:bg-surface-3 transition">
                        Filter
                    </button>
                    @if (request()->hasAny(['search', 'priority', 'assigned_to']))
                        <a href="{{ route('tasks.index', ['status' => request('status', 'all')]) }}" class="py-2 px-3 rounded-lg text-xs font-semibold text-text-muted hover:text-text transition">
                            Reset
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Task List -->
        <div class="bg-surface border border-border rounded-xl shadow-sm overflow-hidden">
            <div class="divide-y divide-border">
                @forelse ($tasks as $task)
                    <div class="p-5 flex flex-col md:flex-row md:items-center justify-between gap-4 hover:bg-surface-2/40 transition">
                        <div class="space-y-2 flex-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="px-2 py-0.5 rounded text-[11px] font-semibold border {{ $task->priority->badgeClasses() }}">
                                    {{ $task->priority->label() }} Priority
                                </span>
                                <span class="px-2 py-0.5 rounded text-[11px] font-semibold border {{ $task->status->badgeClasses() }}">
                                    {{ $task->status->label() }}
                                </span>
                                @if ($task->due_date)
                                    @php
                                        $isOverdue = !$task->isDone() && $task->due_date->isPast();
                                        $isDueSoon = !$task->isDone() && !$isOverdue && $task->due_date->diffInDays(now()) <= 3;
                                    @endphp
                                    <span class="inline-flex items-center gap-1 text-[11px] font-medium {{ $isOverdue ? 'text-danger font-semibold' : ($isDueSoon ? 'text-warning font-semibold' : 'text-text-muted') }}">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                        </svg>
                                        Due {{ $task->due_date->format('M d, Y') }}
                                        @if ($isOverdue) (Overdue) @elseif ($isDueSoon) (Due soon) @endif
                                    </span>
                                @endif
                            </div>

                            <h3 class="text-sm font-bold text-text {{ $task->isDone() ? 'line-through text-text-muted' : '' }}">
                                {{ $task->title }}
                            </h3>

                            @if ($task->description)
                                <p class="text-xs text-text-muted line-clamp-2 max-w-3xl">
                                    {{ $task->description }}
                                </p>
                            @endif

                            <div class="flex flex-wrap items-center gap-4 text-xs text-text-muted pt-1">
                                <div class="flex items-center gap-1.5">
                                    <span class="font-medium text-text">Assignee:</span>
                                    <span>{{ $task->assignee?->name ?? 'Unassigned' }}</span>
                                </div>
                                <div class="flex items-center gap-1.5">
                                    <span class="font-medium text-text">Delegated by:</span>
                                    <span>{{ $task->creator?->name ?? 'System' }}</span>
                                </div>
                                @if ($task->studentProfile)
                                    <div class="flex items-center gap-1.5">
                                        <span class="font-medium text-text">Student:</span>
                                        <span class="font-mono text-primary font-semibold">{{ $task->studentProfile->user->name }} ({{ $task->studentProfile->student_number }})</span>
                                    </div>
                                @endif
                                @if ($task->company)
                                    <div class="flex items-center gap-1.5">
                                        <span class="font-medium text-text">Company:</span>
                                        <span class="font-semibold text-text">{{ $task->company->name }}</span>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Status Toggle & Action Controls -->
                        <div class="flex items-center gap-2.5 self-start md:self-center flex-shrink-0 pt-2 md:pt-0">
                            <!-- Quick Status Update Form -->
                            <form method="POST" action="{{ route('tasks.update', $task) }}" class="inline">
                                @csrf
                                @method('PATCH')
                                <select 
                                    name="status" 
                                    onchange="this.form.submit()" 
                                    class="py-1.5 px-2.5 text-xs rounded-lg border border-border bg-surface text-text font-semibold focus:ring-2 focus:ring-primary transition"
                                >
                                    <option value="todo" {{ $task->status === \App\Enums\TaskStatus::TODO ? 'selected' : '' }}>To Do</option>
                                    <option value="in_progress" {{ $task->status === \App\Enums\TaskStatus::IN_PROGRESS ? 'selected' : '' }}>In Progress</option>
                                    <option value="done" {{ $task->status === \App\Enums\TaskStatus::DONE ? 'selected' : '' }}>Mark Done</option>
                                </select>
                            </form>

                            @if (Auth::user()->isOc())
                                <button 
                                    @click="openEdit({{ json_encode($task) }})"
                                    type="button" 
                                    class="p-2 rounded-lg text-text-muted hover:text-text hover:bg-surface-2 transition" 
                                    title="Edit Task"
                                >
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                                    </svg>
                                </button>
                                <form method="POST" action="{{ route('tasks.destroy', $task) }}" onsubmit="return confirm('Archive this task?');" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-2 rounded-lg text-text-muted hover:text-danger hover:bg-danger/10 transition" title="Delete Task">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                        </svg>
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="py-12 text-center">
                        <svg class="w-10 h-10 text-text-muted mx-auto mb-3" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                        </svg>
                        <div class="text-sm font-semibold text-text">No tasks found</div>
                        <p class="text-xs text-text-muted mt-1">There are no tasks matching the selected filters.</p>
                    </div>
                @endforelse
            </div>

            @if ($tasks->hasPages())
                <div class="p-4 border-t border-border">
                    {{ $tasks->links() }}
                </div>
            @endif
        </div>

        <!-- Create Task Modal (OC only) -->
        @if (Auth::user()->isOc())
            <div 
                x-show="createModal" 
                x-cloak 
                class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm"
                @keydown.escape.window="createModal = false"
            >
                <div 
                    @click.outside="createModal = false"
                    class="bg-surface border border-border rounded-2xl p-6 w-full max-w-lg shadow-xl space-y-4 max-h-[90vh] overflow-y-auto"
                >
                    <div class="flex items-center justify-between pb-3 border-b border-border">
                        <h2 class="text-base font-bold text-text">Create & Delegate Task</h2>
                        <button @click="createModal = false" class="text-text-muted hover:text-text">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <form method="POST" action="{{ route('tasks.store') }}" class="space-y-4">
                        @csrf
                        <div>
                            <label class="block text-xs font-semibold text-text-muted mb-1">Task Title <span class="text-danger">*</span></label>
                            <input 
                                type="text" 
                                name="title" 
                                required 
                                placeholder="e.g., Audit MOA submission for Group 4" 
                                class="w-full py-2 px-3 text-xs rounded-lg border border-border bg-surface text-text focus:ring-2 focus:ring-primary focus:border-transparent transition"
                            />
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-text-muted mb-1">Description / Instructions</label>
                            <textarea 
                                name="description" 
                                rows="3" 
                                placeholder="Detail instructions, expectations, and checklist items..." 
                                class="w-full py-2 px-3 text-xs rounded-lg border border-border bg-surface text-text focus:ring-2 focus:ring-primary focus:border-transparent transition"
                            ></textarea>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-text-muted mb-1">Assign To <span class="text-danger">*</span></label>
                                <select 
                                    name="assigned_to" 
                                    required 
                                    class="w-full py-2 px-3 text-xs rounded-lg border border-border bg-surface text-text focus:ring-2 focus:ring-primary focus:border-transparent transition"
                                >
                                    @foreach ($cocs as $coc)
                                        <option value="{{ $coc->id }}">{{ $coc->name }} ({{ $coc->role->label() }})</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-text-muted mb-1">Priority <span class="text-danger">*</span></label>
                                <select 
                                    name="priority" 
                                    required 
                                    class="w-full py-2 px-3 text-xs rounded-lg border border-border bg-surface text-text focus:ring-2 focus:ring-primary focus:border-transparent transition"
                                >
                                    <option value="low">Low</option>
                                    <option value="medium" selected>Medium</option>
                                    <option value="high">High</option>
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-text-muted mb-1">Due Date</label>
                            <input 
                                type="date" 
                                name="due_date" 
                                class="w-full py-2 px-3 text-xs rounded-lg border border-border bg-surface text-text focus:ring-2 focus:ring-primary focus:border-transparent transition"
                            />
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-text-muted mb-1">Related Student (Optional)</label>
                                <select 
                                    name="related_student_profile_id" 
                                    class="w-full py-2 px-3 text-xs rounded-lg border border-border bg-surface text-text focus:ring-2 focus:ring-primary focus:border-transparent transition"
                                >
                                    <option value="">None</option>
                                    @foreach ($students as $student)
                                        <option value="{{ $student->id }}">{{ $student->user->name }} ({{ $student->student_number }})</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-text-muted mb-1">Related Company (Optional)</label>
                                <select 
                                    name="related_company_id" 
                                    class="w-full py-2 px-3 text-xs rounded-lg border border-border bg-surface text-text focus:ring-2 focus:ring-primary focus:border-transparent transition"
                                >
                                    <option value="">None</option>
                                    @foreach ($companies as $comp)
                                        <option value="{{ $comp->id }}">{{ $comp->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="pt-3 border-t border-border flex justify-end gap-2">
                            <button 
                                type="button" 
                                @click="createModal = false" 
                                class="px-4 py-2 rounded-xl text-xs font-semibold text-text-muted hover:text-text transition"
                            >
                                Cancel
                            </button>
                            <button 
                                type="submit" 
                                class="px-4 py-2 rounded-xl text-xs font-semibold bg-primary text-white hover:bg-primary-600 transition"
                            >
                                Create Task
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Edit Task Modal -->
            <div 
                x-show="editModal" 
                x-cloak 
                class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm"
                @keydown.escape.window="editModal = false"
            >
                <div 
                    @click.outside="editModal = false"
                    class="bg-surface border border-border rounded-2xl p-6 w-full max-w-lg shadow-xl space-y-4 max-h-[90vh] overflow-y-auto"
                >
                    <div class="flex items-center justify-between pb-3 border-b border-border">
                        <h2 class="text-base font-bold text-text">Edit Task</h2>
                        <button @click="editModal = false" class="text-text-muted hover:text-text">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <form 
                        :action="'{{ url('/tasks') }}/' + (activeTask ? activeTask.id : '')" 
                        method="POST" 
                        class="space-y-4"
                    >
                        @csrf
                        @method('PATCH')

                        <div>
                            <label class="block text-xs font-semibold text-text-muted mb-1">Task Title <span class="text-danger">*</span></label>
                            <input 
                                type="text" 
                                name="title" 
                                required 
                                :value="activeTask?.title"
                                class="w-full py-2 px-3 text-xs rounded-lg border border-border bg-surface text-text focus:ring-2 focus:ring-primary focus:border-transparent transition"
                            />
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-text-muted mb-1">Description</label>
                            <textarea 
                                name="description" 
                                rows="3" 
                                :value="activeTask?.description"
                                class="w-full py-2 px-3 text-xs rounded-lg border border-border bg-surface text-text focus:ring-2 focus:ring-primary focus:border-transparent transition"
                            ></textarea>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-text-muted mb-1">Assign To <span class="text-danger">*</span></label>
                                <select 
                                    name="assigned_to" 
                                    required 
                                    class="w-full py-2 px-3 text-xs rounded-lg border border-border bg-surface text-text focus:ring-2 focus:ring-primary focus:border-transparent transition"
                                >
                                    @foreach ($cocs as $coc)
                                        <option value="{{ $coc->id }}" :selected="activeTask?.assigned_to === {{ $coc->id }}">{{ $coc->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-text-muted mb-1">Priority <span class="text-danger">*</span></label>
                                <select 
                                    name="priority" 
                                    required 
                                    class="w-full py-2 px-3 text-xs rounded-lg border border-border bg-surface text-text focus:ring-2 focus:ring-primary focus:border-transparent transition"
                                >
                                    <option value="low" :selected="activeTask?.priority === 'low'">Low</option>
                                    <option value="medium" :selected="activeTask?.priority === 'medium'">Medium</option>
                                    <option value="high" :selected="activeTask?.priority === 'high'">High</option>
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-text-muted mb-1">Status <span class="text-danger">*</span></label>
                                <select 
                                    name="status" 
                                    required 
                                    class="w-full py-2 px-3 text-xs rounded-lg border border-border bg-surface text-text focus:ring-2 focus:ring-primary focus:border-transparent transition"
                                >
                                    <option value="todo" :selected="activeTask?.status === 'todo'">To Do</option>
                                    <option value="in_progress" :selected="activeTask?.status === 'in_progress'">In Progress</option>
                                    <option value="done" :selected="activeTask?.status === 'done'">Done</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-text-muted mb-1">Due Date</label>
                                <input 
                                    type="date" 
                                    name="due_date" 
                                    :value="activeTask?.due_date ? activeTask.due_date.substring(0, 10) : ''"
                                    class="w-full py-2 px-3 text-xs rounded-lg border border-border bg-surface text-text focus:ring-2 focus:ring-primary focus:border-transparent transition"
                                />
                            </div>
                        </div>

                        <div class="pt-3 border-t border-border flex justify-end gap-2">
                            <button 
                                type="button" 
                                @click="editModal = false" 
                                class="px-4 py-2 rounded-xl text-xs font-semibold text-text-muted hover:text-text transition"
                            >
                                Cancel
                            </button>
                            <button 
                                type="submit" 
                                class="px-4 py-2 rounded-xl text-xs font-semibold bg-primary text-white hover:bg-primary-600 transition"
                            >
                                Save Changes
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @endif
    </div>
</x-app-layout>
