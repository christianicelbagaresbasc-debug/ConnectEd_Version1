<?php

namespace App\Http\Controllers\Coordinator;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\StudentProfile;
use App\Models\User;
use App\Notifications\ConnectEdNotification;
use App\Services\AuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StudentAssignmentController extends Controller
{
    public function assign(Request $request, StudentProfile $student): RedirectResponse
    {
        $user = Auth::user();
        if ($user->role !== UserRole::OC) {
            abort(403, 'Only the OJT Coordinator can assign students to Co-Coordinators.');
        }

        $validated = $request->validate([
            'assigned_coc_id' => ['nullable', 'exists:users,id'],
        ]);

        $cocId = $validated['assigned_coc_id'] ?? null;
        if ($cocId) {
            $coc = User::findOrFail($cocId);
            if (! in_array($coc->role, [UserRole::COC, UserRole::OC])) {
                return back()->withErrors(['assigned_coc_id' => 'The selected user is not a coordinator.']);
            }
        }

        $oldCocId = $student->assigned_coc_id;
        $student->update(['assigned_coc_id' => $cocId]);

        AuditService::log('student.coc_assigned', $student, [
            'student_number' => $student->student_number,
            'old_coc_id' => $oldCocId,
            'new_coc_id' => $cocId,
        ]);

        if ($cocId && $cocId !== $user->id) {
            $coc = User::find($cocId);
            $student->loadMissing('user');
            $studentName = $student->user?->name ?? 'Student';
            $coc?->notify(new ConnectEdNotification(
                title: 'Student Assigned to Cohort',
                body: "{$studentName} ({$student->student_number}) has been assigned to your supervision cohort.",
                actionUrl: route('coordinator.students.index'),
                type: 'assignment'
            ));
        }

        return back()->with('status', 'Student assignment updated successfully.');
    }

    public function bulkAssign(Request $request): RedirectResponse
    {
        $user = Auth::user();
        if ($user->role !== UserRole::OC) {
            abort(403, 'Only the OJT Coordinator can assign students in bulk.');
        }

        $validated = $request->validate([
            'student_ids' => ['required', 'array', 'min:1'],
            'student_ids.*' => ['required', 'exists:student_profiles,id'],
            'assigned_coc_id' => ['required', 'exists:users,id'],
        ]);

        $coc = User::findOrFail($validated['assigned_coc_id']);
        if (! in_array($coc->role, [UserRole::COC, UserRole::OC])) {
            return back()->withErrors(['assigned_coc_id' => 'The selected user is not a coordinator.']);
        }

        $count = count($validated['student_ids']);
        StudentProfile::whereIn('id', $validated['student_ids'])
            ->update(['assigned_coc_id' => $coc->id]);

        AuditService::log('student.coc_bulk_assigned', null, [
            'count' => $count,
            'student_ids' => $validated['student_ids'],
            'assigned_coc_id' => $coc->id,
        ]);

        if ($coc->id !== $user->id) {
            $coc->notify(new ConnectEdNotification(
                title: 'Batch Cohort Assigned',
                body: "A cohort of {$count} students has been assigned to your supervision.",
                actionUrl: route('coordinator.students.index'),
                type: 'assignment'
            ));
        }

        return back()->with('status', "Successfully assigned {$count} students to {$coc->name}.");
    }
}
