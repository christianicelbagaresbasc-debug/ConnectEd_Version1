<?php

namespace Tests\Feature\Auth;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
        $response->assertSee('Student Registration');
        $response->assertSee('Republic Act No. 10173');
    }

    public function test_student_can_register_into_pending_state(): void
    {
        $response = $this->post('/register', [
            'name' => 'Juan Dela Cruz',
            'email' => 'juan.delacruz@bsau.edu.ph',
            'student_number' => '2023-10042',
            'program' => 'BSIT',
            'year_level' => '4th Year',
            'section' => '4-A',
            'password' => 'SecurePass123!',
            'password_confirmation' => 'SecurePass123!',
            'privacy_consent' => '1',
        ]);

        $response->assertRedirect(route('login'));
        $response->assertSessionHas('status');

        $this->assertGuest();

        $user = User::where('email', 'juan.delacruz@bsau.edu.ph')->first();
        $this->assertNotNull($user);
        $this->assertEquals(UserRole::STUDENT, $user->role);
        $this->assertEquals(UserStatus::PENDING, $user->status);
        $this->assertNull($user->approved_at);

        $this->assertDatabaseHas('student_profiles', [
            'user_id' => $user->id,
            'student_number' => '2023-10042',
            'program' => 'BSIT',
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'student.registered',
            'subject_type' => User::class,
            'subject_id' => $user->id,
        ]);
    }

    public function test_registration_requires_privacy_consent(): void
    {
        $response = $this->post('/register', [
            'name' => 'Maria Santos',
            'email' => 'maria.santos@bsau.edu.ph',
            'student_number' => '2023-10043',
            'program' => 'BSIT',
            'year_level' => '4th Year',
            'password' => 'SecurePass123!',
            'password_confirmation' => 'SecurePass123!',
            // privacy_consent missing
        ]);

        $response->assertSessionHasErrors(['privacy_consent']);
        $this->assertDatabaseMissing('users', ['email' => 'maria.santos@bsau.edu.ph']);
    }

    public function test_pending_student_cannot_log_in_until_approved(): void
    {
        $this->post('/register', [
            'name' => 'Pedro Penduko',
            'email' => 'pedro@bsau.edu.ph',
            'student_number' => '2023-10044',
            'program' => 'BSIT',
            'year_level' => '4th Year',
            'password' => 'SecurePass123!',
            'password_confirmation' => 'SecurePass123!',
            'privacy_consent' => '1',
        ]);

        // Attempt login
        $response = $this->post('/login', [
            'email' => 'pedro@bsau.edu.ph',
            'password' => 'SecurePass123!',
        ]);

        $response->assertSessionHasErrors(['email']);
        $this->assertGuest();
    }
}
