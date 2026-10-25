<?php

namespace App\Http\Controllers\Coordinator;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\StudentProfile;
use App\Models\Task;
use App\Models\User;
use App\Notifications\ConnectEdNotification;
use App\Services\AuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Enum;
use Illuminate\View\View;

class TaskController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Task::class);

        $user = Auth::user();

        $query = Task::with([
            'creator',
            'assignee',
            'studentProfile.user',
            'company',
        ]);

        if ($user->role === UserRole::COC) {
            $query->where(function ($q) use ($user) {
                $q->where('assigned_to', $user->id)
                    ->orWhere('created_by', $user->id);
            });
        } elseif ($request->filled('assigned_to') && $request->input('assigned_to') !== 'all') {
            $query->where('assigned_to', $request->input('assigned_to'));
        }

        if ($request->filled('status') && $request->input('status') !== 'all') {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('priority') && $request->input('priority') !== 'all') {
            $query->where('priority', $request->input('priority'));
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $tasks = $query->orderByRaw('CASE WHEN status = \'done\' THEN 1 ELSE 0 END')
            ->orderBy('due_date', 'asc')
            ->orderBy('created_at', 'desc')
            ->paginate(15)
            ->withQueryString();

        $baseCountQuery = Task::query();
        if ($user->role === UserRole::COC) {
            $baseCountQuery->where(function ($q) use ($user) {
                $q->where('assigned_to', $user->id)
                    ->orWhere('created_by', $user->id);
            });
        }

        $counts = [
            'all' => (clone $baseCountQuery)->count(),
            'todo' => (clone $baseCountQuery)->where('status', TaskStatus::TODO->value)->count(),
            'in_progress' => (clone $baseCountQuery)->where('status', TaskStatus::IN_PROGRESS->value)->count(),
            'done' => (clone $baseCountQuery)->where('status', TaskStatus::DONE->value)->count(),
        ];

        $cocs = User::whereIn('role', [UserRole::COC, UserRole::OC])
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $students = StudentProfile::with('user')
            ->whereHas('user', fn ($q) => $q->where('is_active', true))
            ->orderBy('student_number')
            ->get();

        $companies = Company::where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('tasks.index', compact('tasks', 'counts', 'cocs', 'students', 'companies'));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Task::class);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'assigned_to' => ['required', 'exists:users,id'],
            'due_date' => ['nullable', 'date'],
            'priority' => ['required', new Enum(TaskPriority::class)],
            'related_student_profile_id' => ['nullable', 'exists:student_profiles,id'],
            'related_company_id' => ['nullable', 'exists:companies,id'],
        ]);

        $task = Task::create([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'created_by' => Auth::id(),
            'assigned_to' => $validated['assigned_to'],
            'due_date' => $validated['due_date'] ?? null,
            'priority' => $validated['priority'],
            'status' => TaskStatus::TODO,
            'related_student_profile_id' => $validated['related_student_profile_id'] ?? null,
            'related_company_id' => $validated['related_company_id'] ?? null,
        ]);

        AuditService::log('task.created', $task, [
            'title' => $task->title,
            'assigned_to' => $task->assigned_to,
            'priority' => $task->priority->value,
            'due_date' => $task->due_date?->toDateString(),
        ]);

        $assignee = User::find($task->assigned_to);
        if ($assignee && $assignee->id !== Auth::id()) {
            $assignee->notify(new ConnectEdNotification(
                title: 'New Task Assigned',
                body: "You have been assigned a task: {$task->title}",
                actionUrl: route('tasks.index'),
                type: 'task'
            ));
        }

        return redirect()->route('tasks.index')->with('status', 'Task successfully created and delegated.');
    }

    public function update(Request $request, Task $task): RedirectResponse
    {
        $this->authorize('update', $task);

        $user = Auth::user();

        if ($user->role === UserRole::OC) {
            $validated = $request->validate([
                'title' => ['sometimes', 'required', 'string', 'max:255'],
                'description' => ['nullable', 'string', 'max:2000'],
                'assigned_to' => ['sometimes', 'required', 'exists:users,id'],
                'due_date' => ['nullable', 'date'],
                'priority' => ['sometimes', 'required', new Enum(TaskPriority::class)],
                'status' => ['required', new Enum(TaskStatus::class)],
                'related_student_profile_id' => ['nullable', 'exists:student_profiles,id'],
                'related_company_id' => ['nullable', 'exists:companies,id'],
            ]);

            $oldStatus = $task->status;
            $newStatus = TaskStatus::from($validated['status']);

            $completedAt = $task->completed_at;
            if ($newStatus === TaskStatus::DONE && $oldStatus !== TaskStatus::DONE) {
                $completedAt = now();
            } elseif ($newStatus !== TaskStatus::DONE) {
                $completedAt = null;
            }

            $task->update(array_merge($validated, ['completed_at' => $completedAt]));

            AuditService::log('task.updated', $task, [
                'old_status' => $oldStatus->value,
                'new_status' => $newStatus->value,
            ]);
        } else {
            // COC updating task status
            $validated = $request->validate([
                'status' => ['required', new Enum(TaskStatus::class)],
            ]);

            $oldStatus = $task->status;
            $newStatus = TaskStatus::from($validated['status']);

            $completedAt = $task->completed_at;
            if ($newStatus === TaskStatus::DONE && $oldStatus !== TaskStatus::DONE) {
                $completedAt = now();
            } elseif ($newStatus !== TaskStatus::DONE) {
                $completedAt = null;
            }

            $task->update([
                'status' => $newStatus,
                'completed_at' => $completedAt,
            ]);

            AuditService::log('task.status_changed', $task, [
                'old_status' => $oldStatus->value,
                'new_status' => $newStatus->value,
            ]);

            if ($newStatus === TaskStatus::DONE && $task->creator && $task->creator->id !== $user->id) {
                $task->creator->notify(new ConnectEdNotification(
                    title: 'Task Completed',
                    body: "{$user->name} marked task completed: {$task->title}",
                    actionUrl: route('tasks.index'),
                    type: 'task'
                ));
            }
        }

        return redirect()->route('tasks.index')->with('status', 'Task updated successfully.');
    }

    public function destroy(Task $task): RedirectResponse
    {
        $this->authorize('delete', $task);

        $taskTitle = $task->title;
        $task->delete();

        AuditService::log('task.deleted', $task, ['title' => $taskTitle]);

        return redirect()->route('tasks.index')->with('status', 'Task removed successfully.');
    }
}
