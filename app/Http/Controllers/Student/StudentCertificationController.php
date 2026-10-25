<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\StudentCertification;
use App\Services\AuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StudentCertificationController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $user = Auth::user();
        $studentProfile = $user->studentProfile;

        if (! $studentProfile) {
            abort(403);
        }

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'issuer' => ['required', 'string', 'max:255'],
            'issued_at' => ['nullable', 'date'],
            'document_id' => ['nullable', 'exists:documents,id'],
        ]);

        $cert = StudentCertification::create([
            'student_profile_id' => $studentProfile->id,
            'title' => $validated['title'],
            'issuer' => $validated['issuer'],
            'issued_at' => $validated['issued_at'] ?? null,
            'document_id' => $validated['document_id'] ?? null,
        ]);

        AuditService::log('student_certification.added', $studentProfile, [
            'certification_id' => $cert->id,
            'title' => $cert->title,
        ]);

        return redirect()->route('student.profile.skills')->with('status', 'Certification added successfully.');
    }

    public function destroy(StudentCertification $certification): RedirectResponse
    {
        $user = Auth::user();
        $studentProfile = $user->studentProfile;

        if (! $studentProfile || $certification->student_profile_id !== $studentProfile->id) {
            abort(403);
        }

        $title = $certification->title;
        $certification->delete();

        AuditService::log('student_certification.removed', $studentProfile, [
            'title' => $title,
        ]);

        return redirect()->route('student.profile.skills')->with('status', "Certification '{$title}' removed.");
    }
}
