<?php

namespace Tests\Feature\Skills;

use App\Enums\SkillStatus;
use App\Enums\UserRole;
use App\Models\Skill;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SkillManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $coordinator;
    protected User $student;
    protected User $coCoordinator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->coordinator = User::factory()->create([
            'role' => UserRole::OC,
            'status' => 'active',
            'must_change_password' => false,
        ]);

        $this->coCoordinator = User::factory()->create([
            'role' => UserRole::COC,
            'status' => 'active',
            'must_change_password' => false,
        ]);

        $this->student = User::factory()->create([
            'role' => UserRole::STUDENT,
            'status' => 'active',
            'must_change_password' => false,
        ]);
    }

    public function test_coordinator_can_view_skills_catalog(): void
    {
        Skill::create(['name' => 'Laravel', 'category' => 'Web Development', 'status' => SkillStatus::ACTIVE]);
        Skill::create(['name' => 'Docker', 'category' => 'DevOps', 'status' => SkillStatus::PENDING]);

        $response = $this->actingAs($this->coordinator)
            ->get(route('coordinator.skills.index'));

        $response->assertOk()
            ->assertViewIs('coordinator.skills.index')
            ->assertSee('Laravel')
            ->assertSee('Docker');
    }

    public function test_coordinator_can_create_skill(): void
    {
        $response = $this->actingAs($this->coordinator)
            ->post(route('coordinator.skills.store'), [
                'name' => 'PostgreSQL',
                'category' => 'Database',
                'status' => 'active',
            ]);

        $response->assertRedirect(route('coordinator.skills.index'));

        $this->assertDatabaseHas('skills', [
            'name' => 'PostgreSQL',
            'category' => 'Database',
            'status' => 'active',
            'suggested_by' => $this->coordinator->id,
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'skill.created',
            'actor_id' => $this->coordinator->id,
        ]);
    }

    public function test_skill_name_must_be_unique(): void
    {
        Skill::create(['name' => 'Python', 'category' => 'Programming', 'status' => SkillStatus::ACTIVE]);

        $response = $this->actingAs($this->coordinator)
            ->post(route('coordinator.skills.store'), [
                'name' => 'Python',
                'category' => 'Programming',
                'status' => 'active',
            ]);

        $response->assertSessionHasErrors(['name']);
    }

    public function test_coordinator_can_update_skill(): void
    {
        $skill = Skill::create(['name' => 'Vue', 'category' => 'Frontend', 'status' => SkillStatus::ACTIVE]);

        $response = $this->actingAs($this->coordinator)
            ->put(route('coordinator.skills.update', $skill), [
                'name' => 'Vue.js 3',
                'category' => 'Web Development',
                'status' => 'active',
            ]);

        $response->assertRedirect(route('coordinator.skills.index'));

        $this->assertDatabaseHas('skills', [
            'id' => $skill->id,
            'name' => 'Vue.js 3',
            'category' => 'Web Development',
        ]);
    }

    public function test_student_can_suggest_skill_which_enters_pending_status(): void
    {
        $response = $this->actingAs($this->student)
            ->post(route('student.skills.suggest'), [
                'name' => 'Kubernetes',
                'category' => 'DevOps',
            ]);

        $response->assertRedirect(route('student.profile.skills'));

        $this->assertDatabaseHas('skills', [
            'name' => 'Kubernetes',
            'category' => 'DevOps',
            'status' => 'pending',
            'suggested_by' => $this->student->id,
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'skill.suggested',
            'actor_id' => $this->student->id,
        ]);
    }

    public function test_coordinator_can_approve_pending_skill(): void
    {
        $skill = Skill::create([
            'name' => 'Golang',
            'category' => 'Programming',
            'status' => SkillStatus::PENDING,
            'suggested_by' => $this->student->id,
        ]);

        $response = $this->actingAs($this->coordinator)
            ->post(route('coordinator.skills.approve', $skill));

        $response->assertRedirect(route('coordinator.skills.index'));

        $this->assertDatabaseHas('skills', [
            'id' => $skill->id,
            'status' => 'active',
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'skill.approved',
            'actor_id' => $this->coordinator->id,
        ]);
    }

    public function test_coordinator_can_soft_delete_skill(): void
    {
        $skill = Skill::create(['name' => 'COBOL', 'category' => 'Legacy', 'status' => SkillStatus::ACTIVE]);

        $response = $this->actingAs($this->coordinator)
            ->delete(route('coordinator.skills.destroy', $skill));

        $response->assertRedirect(route('coordinator.skills.index'));

        $this->assertSoftDeleted('skills', ['id' => $skill->id]);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'skill.archived',
            'actor_id' => $this->coordinator->id,
        ]);
    }

    public function test_non_coordinator_cannot_manage_skills_catalog(): void
    {
        $response = $this->actingAs($this->coCoordinator)
            ->post(route('coordinator.skills.store'), [
                'name' => 'Rust',
                'category' => 'Programming',
                'status' => 'active',
            ]);

        $response->assertForbidden();

        $skill = Skill::create(['name' => 'C#', 'category' => 'Programming', 'status' => SkillStatus::ACTIVE]);

        $updateResponse = $this->actingAs($this->student)
            ->put(route('coordinator.skills.update', $skill), [
                'name' => 'C# .NET',
                'category' => 'Programming',
                'status' => 'active',
            ]);

        $updateResponse->assertForbidden();
    }
}
