<?php

namespace Tests\Feature\Student;

use App\Enums\SkillProficiency;
use App\Enums\SkillStatus;
use App\Enums\UserRole;
use App\Models\Skill;
use App\Models\StudentCertification;
use App\Models\StudentProfile;
use App\Models\StudentSkill;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentSkillProfileTest extends TestCase
{
    use RefreshDatabase;

    protected User $student;
    protected StudentProfile $studentProfile;
    protected User $otherStudent;
    protected StudentProfile $otherStudentProfile;
    protected Skill $skill;

    protected function setUp(): void
    {
        parent::setUp();

        $this->student = User::factory()->create([
            'role' => UserRole::STUDENT,
            'status' => 'active',
            'must_change_password' => false,
        ]);
        $this->studentProfile = StudentProfile::create([
            'user_id' => $this->student->id,
            'student_number' => '2023-0001',
            'program' => 'BSIT',
            'year_level' => '3rd Year',
            'consented_at' => now(),
            'privacy_notice_version' => '1.0',
        ]);

        $this->otherStudent = User::factory()->create([
            'role' => UserRole::STUDENT,
            'status' => 'active',
            'must_change_password' => false,
        ]);
        $this->otherStudentProfile = StudentProfile::create([
            'user_id' => $this->otherStudent->id,
            'student_number' => '2023-0002',
            'program' => 'BSIT',
            'year_level' => '3rd Year',
            'consented_at' => now(),
            'privacy_notice_version' => '1.0',
        ]);

        $this->skill = Skill::create([
            'name' => 'Laravel',
            'category' => 'Web Development',
            'status' => SkillStatus::ACTIVE,
        ]);
    }

    public function test_student_can_view_skills_profile(): void
    {
        StudentSkill::create([
            'student_profile_id' => $this->studentProfile->id,
            'skill_id' => $this->skill->id,
            'proficiency' => SkillProficiency::INTERMEDIATE,
        ]);

        $response = $this->actingAs($this->student)
            ->get(route('student.profile.skills'));

        $response->assertOk()
            ->assertViewIs('student.profile.skills')
            ->assertSee('Laravel')
            ->assertSee('Intermediate');
    }

    public function test_student_can_add_skill_from_catalog(): void
    {
        $response = $this->actingAs($this->student)
            ->post(route('student.profile.skills.store'), [
                'skill_id' => $this->skill->id,
                'proficiency' => 'advanced',
            ]);

        $response->assertRedirect(route('student.profile.skills'));

        $this->assertDatabaseHas('student_skills', [
            'student_profile_id' => $this->studentProfile->id,
            'skill_id' => $this->skill->id,
            'proficiency' => 'advanced',
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'student_skill.added',
            'subject_id' => $this->studentProfile->id,
        ]);
    }

    public function test_student_can_update_skill_proficiency(): void
    {
        $studentSkill = StudentSkill::create([
            'student_profile_id' => $this->studentProfile->id,
            'skill_id' => $this->skill->id,
            'proficiency' => SkillProficiency::BEGINNER,
        ]);

        $response = $this->actingAs($this->student)
            ->put(route('student.profile.skills.update', $studentSkill), [
                'proficiency' => 'advanced',
            ]);

        $response->assertRedirect(route('student.profile.skills'));

        $studentSkill->refresh();
        $this->assertEquals(SkillProficiency::ADVANCED, $studentSkill->proficiency);
    }

    public function test_student_can_remove_skill_from_profile(): void
    {
        $studentSkill = StudentSkill::create([
            'student_profile_id' => $this->studentProfile->id,
            'skill_id' => $this->skill->id,
            'proficiency' => SkillProficiency::INTERMEDIATE,
        ]);

        $response = $this->actingAs($this->student)
            ->delete(route('student.profile.skills.destroy', $studentSkill));

        $response->assertRedirect(route('student.profile.skills'));

        $this->assertSoftDeleted('student_skills', ['id' => $studentSkill->id]);
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'student_skill.removed',
            'subject_id' => $this->studentProfile->id,
        ]);
    }

    public function test_student_can_add_and_remove_certifications(): void
    {
        $addResponse = $this->actingAs($this->student)
            ->post(route('student.profile.certifications.store'), [
                'title' => 'AWS Certified Cloud Practitioner',
                'issuer' => 'Amazon Web Services',
                'issued_at' => '2026-05-10',
            ]);

        $addResponse->assertRedirect(route('student.profile.skills'));

        $cert = StudentCertification::where('title', 'AWS Certified Cloud Practitioner')->first();
        $this->assertNotNull($cert);
        $this->assertEquals($this->studentProfile->id, $cert->student_profile_id);

        // Remove cert
        $deleteResponse = $this->actingAs($this->student)
            ->delete(route('student.profile.certifications.destroy', $cert));

        $deleteResponse->assertRedirect(route('student.profile.skills'));
        $this->assertSoftDeleted('student_certifications', ['id' => $cert->id]);
    }

    public function test_student_cannot_modify_another_students_skills_or_certifications(): void
    {
        $otherStudentSkill = StudentSkill::create([
            'student_profile_id' => $this->otherStudentProfile->id,
            'skill_id' => $this->skill->id,
            'proficiency' => SkillProficiency::BEGINNER,
        ]);

        $otherCert = StudentCertification::create([
            'student_profile_id' => $this->otherStudentProfile->id,
            'title' => 'Cisco CCNA',
            'issuer' => 'Cisco',
        ]);

        $updateSkillResponse = $this->actingAs($this->student)
            ->put(route('student.profile.skills.update', $otherStudentSkill), [
                'proficiency' => 'advanced',
            ]);
        $updateSkillResponse->assertForbidden();

        $deleteCertResponse = $this->actingAs($this->student)
            ->delete(route('student.profile.certifications.destroy', $otherCert));
        $deleteCertResponse->assertForbidden();
    }
}
