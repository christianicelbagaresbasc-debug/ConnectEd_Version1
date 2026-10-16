<?php

namespace App\Http\Controllers\Coordinator;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Http\Controllers\Controller;
use App\Models\PartnerAccount;
use App\Models\User;
use App\Services\AuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Enum;
use Illuminate\View\View;

class StaffProvisioningController extends Controller
{
    public function index(): View
    {
        $this->authorize('provisionStaff', User::class);

        $staffUsers = User::whereIn('role', [UserRole::OC, UserRole::COC, UserRole::UA, UserRole::PC])
            ->latest()
            ->paginate(15);

        return view('coordinator.staff.index', compact('staffUsers'));
    }

    public function create(): View
    {
        $this->authorize('provisionStaff', User::class);

        return view('coordinator.staff.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('provisionStaff', User::class);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'role' => ['required', new Enum(UserRole::class)],
            'department' => ['nullable', 'string', 'max:255'],
            'job_title' => ['nullable', 'string', 'max:255'],
            'phone_number' => ['nullable', 'string', 'max:50'],
        ]);

        $roleEnum = $validated['role'] instanceof UserRole 
            ? $validated['role'] 
            : UserRole::tryFrom($validated['role']);

        if ($roleEnum === UserRole::STUDENT) {
            return back()->with('error', 'Students cannot be provisioned via staff management; use student registration.');
        }

        $temporaryPassword = 'Temp_' . Str::random(10) . '2026!';

        $newUser = DB::transaction(function () use ($validated, $temporaryPassword) {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($temporaryPassword),
                'role' => $validated['role'],
                'status' => UserStatus::ACTIVE,
                'must_change_password' => true,
                'approved_by' => Auth::id(),
                'approved_at' => now(),
            ]);

            if ($user->isPc()) {
                PartnerAccount::create([
                    'user_id' => $user->id,
                    'department' => $validated['department'] ?? null,
                    'job_title' => $validated['job_title'] ?? null,
                    'phone_number' => $validated['phone_number'] ?? null,
                ]);
            }

            AuditService::log('staff.provisioned', $user, [
                'role' => $user->role->value,
                'provisioned_by' => Auth::id(),
            ]);

            return $user;
        });

        return redirect()->route('coordinator.staff.index')->with(
            'status',
            "Account provisioned for {$newUser->name} ({$newUser->role->label()}). Temporary password: {$temporaryPassword}"
        );
    }
}
