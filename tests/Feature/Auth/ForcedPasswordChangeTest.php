<?php

namespace Tests\Feature\Auth;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ForcedPasswordChangeTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_with_must_change_password_is_redirected_to_forced_change_screen(): void
    {
        $user = User::factory()->create([
            'role' => UserRole::COC,
            'status' => UserStatus::ACTIVE,
            'must_change_password' => true,
        ]);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertRedirect(route('password.forced-change'));
    }

    public function test_forced_password_change_screen_can_be_rendered(): void
    {
        $user = User::factory()->create([
            'role' => UserRole::PC,
            'status' => UserStatus::ACTIVE,
            'must_change_password' => true,
        ]);

        $response = $this->actingAs($user)->get(route('password.forced-change'));

        $response->assertStatus(200);
        $response->assertSee('Change Your Password');
        $response->assertSee('Current Temporary Password');
    }

    public function test_user_cannot_update_with_incorrect_current_password(): void
    {
        $user = User::factory()->create([
            'role' => UserRole::COC,
            'status' => UserStatus::ACTIVE,
            'password' => Hash::make('CorrectCurrent1!'),
            'must_change_password' => true,
        ]);

        $response = $this->actingAs($user)->post(route('password.forced-update'), [
            'current_password' => 'WrongPassword123!',
            'password' => 'NewSecurePass2026!',
            'password_confirmation' => 'NewSecurePass2026!',
        ]);

        $response->assertSessionHasErrors(['current_password']);
        $this->assertTrue($user->fresh()->must_change_password);
    }

    public function test_user_can_successfully_update_password_and_access_dashboard(): void
    {
        $user = User::factory()->create([
            'role' => UserRole::COC,
            'status' => UserStatus::ACTIVE,
            'password' => Hash::make('CurrentTemp123!'),
            'must_change_password' => true,
        ]);

        $response = $this->actingAs($user)->post(route('password.forced-update'), [
            'current_password' => 'CurrentTemp123!',
            'password' => 'NewBrandPass2026!',
            'password_confirmation' => 'NewBrandPass2026!',
        ]);

        $response->assertRedirect(route('dashboard'));
        $response->assertSessionHas('status');

        $user->refresh();
        $this->assertFalse($user->must_change_password);
        $this->assertTrue(Hash::check('NewBrandPass2026!', $user->password));

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'user.password_changed',
            'subject_type' => User::class,
            'subject_id' => $user->id,
            'actor_id' => $user->id,
        ]);

        // User can now directly view dashboard
        $dashRes = $this->actingAs($user)->get('/dashboard');
        $dashRes->assertStatus(200);
    }
}
