<?php

namespace Tests\Feature\Coordinator;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StaffProvisioningTest extends TestCase
{
    use RefreshDatabase;

    protected User $coordinator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->coordinator = User::factory()->create([
            'role' => UserRole::OC,
            'status' => UserStatus::ACTIVE,
            'must_change_password' => false,
        ]);
    }

    public function test_coordinator_can_view_staff_list_and_provision_form(): void
    {
        $response = $this->actingAs($this->coordinator)->get(route('coordinator.staff.index'));
        $response->assertStatus(200);
        $response->assertSee('Manage provisioned accounts');

        $formResponse = $this->actingAs($this->coordinator)->get(route('coordinator.staff.create'));
        $formResponse->assertStatus(200);
        $formResponse->assertSee('Provision Staff or Partner Account');
    }

    public function test_coordinator_can_provision_new_co_coordinator(): void
    {
        $response = $this->actingAs($this->coordinator)->post(route('coordinator.staff.store'), [
            'name' => 'Prof. Co Coordinator',
            'email' => 'coc@bsau.edu.ph',
            'role' => UserRole::COC->value,
            'department' => 'ICS',
        ]);

        $response->assertRedirect(route('coordinator.staff.index'));
        $response->assertSessionHas('status');

        $newUser = User::where('email', 'coc@bsau.edu.ph')->first();
        $this->assertNotNull($newUser);
        $this->assertEquals(UserRole::COC, $newUser->role);
        $this->assertEquals(UserStatus::ACTIVE, $newUser->status);
        $this->assertTrue($newUser->must_change_password);
        $this->assertEquals($this->coordinator->id, $newUser->approved_by);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'staff.provisioned',
            'subject_type' => User::class,
            'subject_id' => $newUser->id,
            'actor_id' => $this->coordinator->id,
        ]);
    }

    public function test_coordinator_can_provision_partner_company_with_profile(): void
    {
        $response = $this->actingAs($this->coordinator)->post(route('coordinator.staff.store'), [
            'name' => 'HR Manager Jane',
            'email' => 'jane@techcorp.com',
            'role' => UserRole::PC->value,
            'job_title' => 'Talent Acquisition Lead',
            'department' => 'Human Resources',
            'phone_number' => '+639171234567',
        ]);

        $response->assertRedirect(route('coordinator.staff.index'));

        $newUser = User::where('email', 'jane@techcorp.com')->first();
        $this->assertNotNull($newUser);
        $this->assertEquals(UserRole::PC, $newUser->role);

        $this->assertDatabaseHas('partner_accounts', [
            'user_id' => $newUser->id,
            'job_title' => 'Talent Acquisition Lead',
            'department' => 'Human Resources',
            'phone_number' => '+639171234567',
        ]);
    }

    public function test_cannot_provision_student_via_staff_form(): void
    {
        $response = $this->actingAs($this->coordinator)->post(route('coordinator.staff.store'), [
            'name' => 'Invalid Student Provision',
            'email' => 'invalid@bsau.edu.ph',
            'role' => UserRole::STUDENT->value,
        ]);

        $response->assertSessionHas('error');
        $this->assertDatabaseMissing('users', ['email' => 'invalid@bsau.edu.ph']);
    }

    public function test_coordinator_can_reset_user_password(): void
    {
        $targetUser = User::factory()->create([
            'role' => UserRole::UA,
            'status' => UserStatus::ACTIVE,
            'must_change_password' => false,
        ]);

        $response = $this->actingAs($this->coordinator)
            ->post(route('coordinator.users.reset-password', $targetUser));

        $response->assertRedirect();
        $response->assertSessionHas('status');

        $targetUser->refresh();
        $this->assertTrue($targetUser->must_change_password);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'user.password_reset',
            'subject_type' => User::class,
            'subject_id' => $targetUser->id,
            'actor_id' => $this->coordinator->id,
        ]);
    }

    public function test_non_coordinator_cannot_provision_staff(): void
    {
        $cocUser = User::factory()->create([
            'role' => UserRole::COC,
            'status' => UserStatus::ACTIVE,
        ]);

        $response = $this->actingAs($cocUser)->get(route('coordinator.staff.index'));
        $response->assertStatus(403);

        $postResponse = $this->actingAs($cocUser)->post(route('coordinator.staff.store'), [
            'name' => 'Attacker',
            'email' => 'attacker@test.com',
            'role' => UserRole::OC->value,
        ]);
        $postResponse->assertStatus(403);
    }
}
