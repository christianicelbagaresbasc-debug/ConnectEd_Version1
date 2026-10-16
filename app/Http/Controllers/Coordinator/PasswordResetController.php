<?php

namespace App\Http\Controllers\Coordinator;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class PasswordResetController extends Controller
{
    /**
     * Coordinator resets a user's password to a temporary password and forces password change on next login.
     */
    public function reset(User $user): RedirectResponse
    {
        $this->authorize('resetPassword', User::class);

        $temporaryPassword = 'Reset_' . Str::random(10) . '2026!';

        $user->update([
            'password' => Hash::make($temporaryPassword),
            'must_change_password' => true,
        ]);

        AuditService::log('user.password_reset', $user, [
            'reset_by' => Auth::id(),
            'target_user' => $user->email,
        ]);

        return back()->with(
            'status',
            "Password for {$user->name} ({$user->email}) has been reset. Temporary password: {$temporaryPassword}"
        );
    }
}
