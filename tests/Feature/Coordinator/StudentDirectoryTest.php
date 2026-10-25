<?php

namespace Tests\Feature\Coordinator;

use App\Enums\SkillStatus;
use App\Enums\UserRole;
use App\Models\Skill;
use App\Models\StudentProfile;
use App\Models\StudentSkill;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentDirectoryTest extends TestCase
{
    use RefreshDatabase;

    protected User $coordinator;

    protected User $coCoordinator;

    protected User $studentA;

    protected User $studentB;

    protected StudentProfile $profileA;

    protected StudentProfile $profileB;

    protected Skill $skill;

    protected function setUp(): void
    {
        parent::setUp();

        $this->coordinator = User::factory()->create([
            'name' => 'Dr. Coordinator',
            'role' => UserRole::OC,
            'status' => 'active',
            'must_change_password' => false,
        ]);

        $this->coCoordinator = User::factory()->create([
            'name' => 'Prof. Co-Coordinator',
            'role' => UserRole::COC,
            'status' => 'active',
            'must_change_password' => false,
        ]);

        $this->studentA = User::factory()->create([
            'name' => 'Alice Dela Cruz',
            'email' => 'alice@example.test',
            'role' => UserRole::STUDENT,
            'status' => 'active',
            'must_change_password' => false,
        ]);
        $this->profileA = StudentProfile::create([
            'user_id' => $this->studentA->id,
            'student_number' => '2023-1001',
            'program' => 'BSIT',
            'year_level' => '3rd Year',
            'has_active_placement' => false,
            'consented_at' => now(),
            'privacy_notice_version' => '1.0',
        ]);

        $this->studentB = User::factory()->create([
            'name' => 'Bob Santos',
            'email' => 'bob@example.test',
            'role' => UserRole::STUDENT,
            'status' => 'active',
            'must_change_password' => false,
        ]);
        $this->profileB = StudentProfile::create([
            'user_id' => $this->studentB->id,
            'student_number' => '2023-1002',
            'program' => 'BSIT',
            'year_level' => '4th Year',
            'has_active_placement' => true,
            'consented_at' => now(),
            'privacy_notice_version' => '1.0',
        ]);

        $this->skill = Skill::create(['name' => 'Laravel', 'category' => 'Web Development', 'status' => SkillStatus::ACTIVE]);
        StudentSkill::create([
            'student_profile_id' => $this->profileA->id,
            'skill_id' => $this->skill->id,
        ]);
    }

    public function test_coordinator_and_co_coordinator_can_view_student_directory(): void
    {
        // OC access
        $ocResponse = $this->actingAs($this->coordinator)
            ->get(route('coordinator.students.index'));

        $ocResponse->assertOk()
            ->assertViewIs('coordinator.students.index')
            ->assertSee('Alice Dela Cruz')
            ->assertSee('Bob Santos');

        // COC access
        $cocResponse = $this->actingAs($this->coCoordinator)
            ->get(route('coordinator.students.index'));

        $cocResponse->assertOk();
    }

    public function test_coordinator_can_filter_students(): void
    {
        // 1. Search by name
        $searchResponse = $this->actingAs($this->coordinator)
            ->get(route('coordinator.students.index', ['search' => 'Alice']));
        $searchResponse->assertSee('Alice Dela Cruz')
            ->assertDontSee('Bob Santos');

        // 2. Filter by placement status (placed)
        $placedResponse = $this->actingAs($this->coordinator)
            ->get(route('coordinator.students.index', ['placement_status' => 'placed']));
        $placedResponse->assertSee('Bob Santos')
            ->assertDontSee('Alice Dela Cruz');

        // 3. Filter by skill
        $skillResponse = $this->actingAs($this->coordinator)
            ->get(route('coordinator.students.index', ['skill_id' => $this->skill->id]));
        $skillResponse->assertSee('Alice Dela Cruz')
            ->assertDontSee('Bob Santos');
    }

    public function test_coordinator_can_view_individual_student_profile(): void
    {
        $response = $this->actingAs($this->coordinator)
            ->get(route('coordinator.students.show', $this->profileA));

        $response->assertOk()
            ->assertViewIs('coordinator.students.show')
            ->assertSee('Alice Dela Cruz')
            ->assertSee('2023-1001')
            ->assertSee('Laravel');
    }

    public function test_non_coordinator_cannot_access_student_directory(): void
    {
        $studentUser = $this->studentA;
        $this->actingAs($studentUser)
            ->get(route('coordinator.students.index'))
            ->assertForbidden();

        $pcUser = User::factory()->create(['role' => UserRole::PC]);
        $this->actingAs($pcUser)
            ->get(route('coordinator.students.index'))
            ->assertForbidden();
    }
}
