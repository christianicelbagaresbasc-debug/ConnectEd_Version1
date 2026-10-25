<?php

namespace App\Http\Controllers\Auth;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Http\Controllers\Controller;
use App\Models\StudentProfile;
use App\Models\User;
use App\Services\AuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        $allowedPrograms = config('connected.allowed_programs', ['BSIT']);

        return view('auth.register', compact('allowedPrograms'));
    }

    /**
     * Handle an incoming student registration request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $allowedPrograms = config('connected.allowed_programs', ['BSIT']);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'student_number' => ['required', 'string', 'max:50', 'unique:student_profiles,student_number'],
            'program' => ['required', 'string', Rule::in($allowedPrograms)],
            'year_level' => ['required', 'string', 'max:50'],
            'section' => ['nullable', 'string', 'max:20'],
            'password' => ['required', 'confirmed', Password::min(10)->mixedCase()->numbers()],
            'privacy_consent' => ['accepted'],
        ], [
            'privacy_consent.accepted' => 'You must agree to the Data Privacy Notice (RA 10173) to register.',
        ]);

        $user = DB::transaction(function () use ($validated) {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'role' => UserRole::STUDENT,
                'status' => UserStatus::PENDING,
                'must_change_password' => false,
                'approved_at' => null,
            ]);

            StudentProfile::create([
                'user_id' => $user->id,
                'student_number' => $validated['student_number'],
                'program' => $validated['program'],
                'year_level' => $validated['year_level'],
                'section' => $validated['section'] ?? null,
                'school_year' => config('connected.current_school_year', '2026-2027'),
                'has_active_placement' => false,
                'consented_at' => now(),
                'privacy_notice_version' => 'v1.0-draft',
            ]);

            AuditService::log('student.registered', $user, [
                'student_number' => $validated['student_number'],
                'program' => $validated['program'],
            ], $user);

            return $user;
        });

        return redirect()->route('login')->with(
            'status',
            'Registration submitted successfully! Your account is pending coordinator approval before you can log in.'
        );
    }
}
