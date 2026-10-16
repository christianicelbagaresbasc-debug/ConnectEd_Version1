<?php

namespace App\Http\Controllers\Coordinator;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class StudentApprovalController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('approve', User::class);

        $query = User::where('role', UserRole::STUDENT)
            ->where('status', UserStatus::PENDING)
            ->with('studentProfile')
            ->latest();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhereHas('studentProfile', function ($sq) use ($search) {
                      $sq->where('student_number', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('program')) {
            $query->whereHas('studentProfile', function ($sq) use ($request) {
                $sq->where('program', $request->input('program'));
            });
        }

        $pendingStudents = $query->paginate(15)->withQueryString();
        $allowedPrograms = config('connected.allowed_programs', ['BSIT']);

        return view('coordinator.students.pending', compact('pendingStudents', 'allowedPrograms'));
    }

    public function approve(User $user): RedirectResponse
    {
        $this->authorize('approve', User::class);

        if (!$user->isStudent() || !$user->isPending()) {
            return back()->with('error', 'Only pending student accounts can be approved.');
        }

        $user->update([
            'status' => UserStatus::ACTIVE,
            'approved_by' => Auth::id(),
            'approved_at' => now(),
        ]);

        AuditService::log('student.approved', $user, [
            'approved_by' => Auth::id(),
            'approved_at' => now()->toISOString(),
        ]);

        return back()->with('status', "Student account for {$user->name} has been approved.");
    }

    public function reject(Request $request, User $user): RedirectResponse
    {
        $this->authorize('approve', User::class);

        if (!$user->isStudent()) {
            return back()->with('error', 'Only student accounts can be rejected through this action.');
        }

        $reason = $request->input('reason', 'Application rejected by OJT Coordinator.');

        $user->update(['status' => UserStatus::SUSPENDED]);
        $user->delete(); // Soft delete

        AuditService::log('student.rejected', $user, [
            'reason' => $reason,
        ]);

        return back()->with('status', "Student account for {$user->name} has been rejected and archived.");
    }
}
