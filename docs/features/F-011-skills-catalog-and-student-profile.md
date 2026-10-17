# F-011 IT Skills Catalog & Student Competency Profile

| Field | Value |
|---|---|
| Feature ID | F-011 |
| Module | Skills & Student Profile |
| Status | Done |
| Phase | 5 |
| Requirements | FR-03, NFR-01, NFR-03, NFR-06, NFR-07 |
| Business rules | BR-15, BR-20 |
| Roles with access | OC (manage catalog, approve suggestions), Student (maintain profile skills & certifications, suggest new skills), COC (view-only directory) |
| Owner (code) | `app/Http/Controllers/Coordinator/SkillController.php`, `app/Http/Controllers/Student/StudentSkillController.php`, `app/Http/Controllers/Student/StudentCertificationController.php`, `app/Http/Controllers/Coordinator/StudentController.php` |

## 1. Purpose
Provides a centralized, standardized IT skills taxonomy for BulSU/BSAU Institute of Computer Studies (ICS). Enables students to maintain their acquired technical and soft skills, proficiency levels (`beginner`, `intermediate`, `advanced`), and external certifications with attached documents. Allows students to suggest missing technical competencies which undergo coordinator approval before joining the standard catalog. Powers the input vectors for the transparent rule-based opportunity recommendation engine (F-013).

## 2. User flow
```mermaid
flowchart TD
    subgraph Student Flow
        S([Student]) --> NavSkills[Open /student/profile/skills]
        NavSkills --> ViewSkills[Review current competencies & certifications]
        ViewSkills --> AddSkillChoice{Skill In Catalog?}
        AddSkillChoice -- Yes --> SelectSkill[Select skill from catalog + choose proficiency]
        SelectSkill --> SaveSkill[Store student_skills record & log audit]
        AddSkillChoice -- No --> SuggestSkill[Submit skill suggestion: name & category]
        SuggestSkill --> PendingSkill[Create skill record: status=pending, suggested_by=student]
        ViewSkills --> UpdateProficiency[Update proficiency level dropdown]
        ViewSkills --> AddCert[Record certification: title, issuer, date, document attachment]
    end

    subgraph Coordinator Flow
        OC([OJT Coordinator]) --> NavCat[Open /coordinator/skills]
        NavCat --> FilterCat[Browse catalog by category, status, search]
        FilterCat --> ReviewPending{Pending Suggestions?}
        ReviewPending -- Yes --> ApproveSkill[Post /coordinator/skills/{id}/approve]
        ApproveSkill --> ActivateSkill[Update skill status to active, log audit]
        FilterCat --> CreateSkill[Add new skill directly: active status]
        FilterCat --> ArchiveSkill[Soft delete skill: moves to Archive Center]
        OC --> ViewStudents[Open /coordinator/students]
        ViewStudents --> FilterStudents[Search by name, student #, program, placement status, skill]
        FilterStudents --> InspectProfile[View student competencies without any ranking scores]
    end
```

## 3. Screens
- `resources/views/coordinator/skills/index.blade.php` (`/coordinator/skills`):
  - Standard catalog management table with search, category filter, and status filter.
  - Alert banner when student-suggested skills are pending review with quick filter.
  - Modals for adding new taxonomy skills and editing existing categories.
  - Quick action to approve pending skills and soft delete (archive) obsolete ones.
- `resources/views/student/profile/skills.blade.php` (`/student/profile/skills`):
  - Acquired competencies card with interactive proficiency dropdowns (`Beginner`, `Intermediate`, `Advanced`).
  - Catalog picker grouped by category (Programming, Web Development, Database, Networking, DevOps, Cloud, Security, Design, Soft Skills).
  - "Suggest a New Skill" intake form for competencies not yet in the official catalog.
  - Student certifications card with issuer, date, and document download link.
- `resources/views/coordinator/students/index.blade.php` (`/coordinator/students`):
  - Alphabetical student directory and cohort monitoring for OC/COC.
  - Filters: search (name, student number, email), program (`BSIT`), year level, placement status, skill.
  - Strict compliance with BR-20: no match scores or ranking columns displayed.
- `resources/views/coordinator/students/show.blade.php` (`/coordinator/students/{studentProfile}`):
  - Comprehensive view of student academic standing, contact info, acquired skills with proficiencies, and certifications.

## 4. Routes
| Method | URI | Route name | Controller@action | Middleware / Policy |
|---|---|---|---|---|
| GET | `/coordinator/skills` | `coordinator.skills.index` | `Coordinator\SkillController@index` | `auth`, `verified`, `SkillPolicy@viewAny` |
| POST | `/coordinator/skills` | `coordinator.skills.store` | `Coordinator\SkillController@store` | `auth`, `verified`, `SkillPolicy@create` |
| PUT | `/coordinator/skills/{skill}` | `coordinator.skills.update` | `Coordinator\SkillController@update` | `auth`, `verified`, `SkillPolicy@update` |
| POST | `/coordinator/skills/{skill}/approve` | `coordinator.skills.approve` | `Coordinator\SkillController@approve` | `auth`, `verified`, `SkillPolicy@update` |
| DELETE | `/coordinator/skills/{skill}` | `coordinator.skills.destroy` | `Coordinator\SkillController@destroy` | `auth`, `verified`, `SkillPolicy@delete` |
| GET | `/coordinator/students` | `coordinator.students.index` | `Coordinator\StudentController@index` | `auth`, `verified`, `StudentProfilePolicy@viewAny` |
| GET | `/coordinator/students/{studentProfile}` | `coordinator.students.show` | `Coordinator\StudentController@show` | `auth`, `verified`, `StudentProfilePolicy@view` |
| GET | `/student/profile/skills` | `student.profile.skills` | `Student\StudentSkillController@index` | `auth`, `verified` (Student role) |
| POST | `/student/profile/skills` | `student.profile.skills.store` | `Student\StudentSkillController@store` | `auth`, `verified` (Student role) |
| PUT | `/student/profile/skills/{studentSkill}` | `student.profile.skills.update` | `Student\StudentSkillController@update` | `auth`, `verified` (Student role) |
| DELETE | `/student/profile/skills/{studentSkill}` | `student.profile.skills.destroy` | `Student\StudentSkillController@destroy` | `auth`, `verified` (Student role) |
| POST | `/student/skills/suggest` | `student.skills.suggest` | `Student\StudentSkillController@suggest` | `auth`, `verified` (Student role) |
| POST | `/student/profile/certifications` | `student.profile.certifications.store` | `Student\StudentCertificationController@store` | `auth`, `verified` (Student role) |
| DELETE | `/student/profile/certifications/{certification}` | `student.profile.certifications.destroy` | `Student\StudentCertificationController@destroy` | `auth`, `verified` (Student role) |

## 5. Data
- **`skills`**: Standard taxonomy (`name`, `category`, `status`: `active|pending|inactive`, `suggested_by`, `created_at`, `updated_at`, `deleted_at`).
  - Partial unique index on PostgreSQL: `LOWER(name)`.
- **`student_skills`**: Pivot between student profile and skill (`student_profile_id`, `skill_id`, `proficiency`: `beginner|intermediate|advanced`, timestamps, soft deletes).
- **`student_certifications`**: Student credentials (`student_profile_id`, `title`, `issuer`, `issued_at`, `document_id`, timestamps, soft deletes).
- **`audit_logs`**: Logs `skill.created`, `skill.updated`, `skill.approved`, `skill.archived`, `skill.suggested`, `student_skill.added`, `student_skill.removed`, `student_certification.added`, `student_certification.removed`.

## 6. Rules and validation
- Unique skill names (case-insensitive) across the catalog.
- Skill statuses restricted to backed enum `SkillStatus` (`active`, `pending`, `inactive`).
- Student proficiency restricted to backed enum `SkillProficiency` (`beginner`, `intermediate`, `advanced`).
- Soft delete everywhere: skills, student skills, and certifications are soft deleted with `deleted_at`.
- Coordinators see all active students ordered alphabetically, never sorted by score or match algorithm (BR-20).

## 7. Testing
- `tests/Feature/Skills/SkillManagementTest.php`:
  - `test_coordinator_can_view_skills_catalog`
  - `test_coordinator_can_create_skill`
  - `test_skill_name_must_be_unique`
  - `test_coordinator_can_update_skill`
  - `test_student_can_suggest_skill_which_enters_pending_status`
  - `test_coordinator_can_approve_pending_skill`
  - `test_coordinator_can_soft_delete_skill`
  - `test_non_coordinator_cannot_manage_skills_catalog`
- `tests/Feature/Student/StudentSkillProfileTest.php`:
  - `test_student_can_view_skills_profile`
  - `test_student_can_add_skill_from_catalog`
  - `test_student_can_update_skill_proficiency`
  - `test_student_can_remove_skill_from_profile`
  - `test_student_can_add_and_remove_certifications`
  - `test_student_cannot_modify_another_students_skills_or_certifications`
- `tests/Feature/Coordinator/StudentDirectoryTest.php`:
  - `test_coordinator_and_co_coordinator_can_view_student_directory`
  - `test_coordinator_can_filter_students`
  - `test_coordinator_can_view_individual_student_profile`
  - `test_non_coordinator_cannot_access_student_directory`
