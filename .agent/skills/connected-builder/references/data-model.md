# Data Model

PostgreSQL 16 (Neon). All tables have `id` (bigint), `created_at`, `updated_at`, and `deleted_at` (soft delete) unless marked **no soft delete**. Foreign keys are real constraints with `restrictOnDelete` (nothing cascades a delete, because nothing is hard-deleted). Use enums as string columns validated by PHP backed enums, not database ENUM types, so changes need no table alteration.

Document each table's meaning in its feature doc. Draw the ERD in Mermaid in `docs/backend/erd.md`.

## Identity

**users**: name, email (unique), password, role (`oc|coc|ua|pc|student`), status (`pending|active|suspended`), must_change_password (bool), approved_by (nullable FK users), approved_at, last_login_at, remember_token.

**student_profiles**: user_id (unique FK), student_number (unique), program, year_level, section, contact_number, address (optional, minimal), bio, resume_document_id (nullable), portfolio_url, assigned_coc_id (nullable FK users), school_year, has_active_placement (bool, derived and kept in sync inside placement transactions).

**partner_accounts**: user_id (unique FK), company_id (FK). Links a `pc` user to one company.

## Skills

**skills**: name (unique, case-insensitive), category (e.g. Programming, Database, Networking, Design, Soft Skill), status (`active|pending|inactive`), suggested_by (nullable FK users).

**student_skills**: student_profile_id, skill_id, proficiency (`beginner|intermediate|advanced`, nullable). Unique (student_profile_id, skill_id).

**student_certifications**: student_profile_id, title, issuer, issued_at, document_id (nullable).

## Companies and verification

**companies**: name, classification (`new_company|existing_partner`), verification_status (`pending|under_review|verified|rejected|revoked`), is_active (bool), industry, address, city, contact_person, contact_email, contact_phone, website, description, bsit_relevance_note, source (`oc_created|student_suggestion`), promoted_by (nullable FK), promoted_at.

**company_suggestions**: student_profile_id, company_name, contact_details, notes, status (`submitted|converted|dismissed`), company_id (nullable), handled_by, handled_at.

**verification_requirements**: label, description, stage (nullable string, unknown by default), is_active, sort_order.

**company_verification_items**: company_id, verification_requirement_id, status (`missing|submitted|accepted|rejected`), document_id (nullable), remark, reviewed_by, reviewed_at. Unique (company_id, verification_requirement_id).

**company_verification_logs** (**no soft delete**, append-only): company_id, from_status, to_status, remark, actor_id, created_at.

## MOA

**moas**: company_id, student_profile_id (nullable; set for `student_company`), type (`university_company|student_company`), status, current_custodian_party (`oc|coc|ua|student|company`), current_custodian_user_id (nullable), effective_date, expiry_date, summary, school_year.

**moa_versions**: moa_id, version_no, document_id, uploaded_by, note.

**moa_transfers** (**no soft delete**, append-only): moa_id, from_party, to_party, actor_id, note, created_at.

**moa_notes**: moa_id, author_id (UA, OC, or COC), type (`revision|legal_remark|general`), body, resolved_at (nullable), resolved_by.

## Opportunities

**opportunities**: company_id, title, description, qualifications (text), slots_total, slots_filled (derived), location, work_arrangement (`onsite|remote|hybrid`), deadline, status (`draft|pending_approval|open|closed|archived`), created_by, approved_by, approved_at, school_year.

**opportunity_skills**: opportunity_id, skill_id, requirement_level (`required|preferred`). Unique (opportunity_id, skill_id).

## Applications and placements

**applications**: student_profile_id, opportunity_id, status, cover_note, submitted_at, decided_by, decided_at, decision_remark, student_confirmed_at, oc_confirmed_at, withdrawn_reason, school_year. Unique (student_profile_id, opportunity_id) among non-archived rows; enforce in app and with a unique index including `deleted_at` handling.

**application_status_logs** (**no soft delete**, append-only): application_id, from_status, to_status, actor_id (nullable for system), remark, created_at.

**placements**: student_profile_id, company_id, opportunity_id (nullable for New Company manual placements), application_id (nullable), status (`placed|authorized|ongoing|completed|terminated`), start_authorized_at, start_authorization_note, start_authorization_document_id (nullable), start_date, end_date, termination_reason, confirmed_by, school_year, is_active (bool, true for placed/authorized/ongoing).

## Work management

**tasks**: title, description, created_by (OC), assigned_to (COC), due_date, status (`todo|in_progress|done`), related_student_profile_id (nullable), related_company_id (nullable), completed_at.

## Cross-cutting

**documents** (see `files-and-documents.md` section 8 for the added versioning, review, and batch columns): owner_user_id, documentable_type, documentable_id (polymorphic), category (`resume|certification|moa|verification|authorization|weekly_report|other`), original_name, stored_path, disk, mime_type, size_bytes, checksum_sha256, validation_status (`ok|rejected`), uploaded_by.

**weekly_reports**: placement_id, week_start, document_id, status (`submitted|reviewed|flagged`), reviewed_by, remark. Uploads only (assumption A9).

**notifications**: Laravel's default `notifications` table.

**audit_logs** (**no soft delete**, append-only): actor_id (nullable), action (dotted verb, e.g. `company.promoted`), subject_type, subject_id, changes (JSONB, old and new values of the fields that matter, never passwords or file contents), ip_address (nullable; leave null unless config `connected.audit_ip` is true), created_at.

**sessions**, **cache**, **cache_locks**, **jobs**, **failed_jobs**, **password_reset_tokens**: framework tables. Sessions and cache use the database driver (required on Vercel).

## PostgreSQL-specific rules

- Use **partial unique indexes** (Postgres supports them) instead of app-only checks where a rule must hold at the database level:
  - one active placement per student: unique on `placements(student_profile_id) WHERE is_active = true AND deleted_at IS NULL`
  - one application per student per opportunity: unique on `applications(student_profile_id, opportunity_id) WHERE deleted_at IS NULL`
  - one skill name regardless of case: unique on `lower(name)` for `skills`
  Create these with `DB::statement` in migrations. Keep the app-level `lockForUpdate()` transaction as well.
- Use `jsonb` for JSON columns, `timestampTz` is not required (app timezone is `Asia/Manila`, store UTC).
- Always run migrations through Neon's **direct** (non-pooled) connection string.

## Indexes worth adding

users(role,status); student_profiles(assigned_coc_id), (school_year); companies(classification, verification_status); opportunities(status, deadline), (company_id); applications(student_profile_id, status), (opportunity_id, status); placements(student_profile_id, is_active); moas(company_id, status), (current_custodian_party); audit_logs(subject_type, subject_id), (actor_id, created_at).

## Seeders

- `RoleSeeder`/`UserSeeder`: one demo user per role. Password from env `SEED_DEFAULT_PASSWORD` (fail loudly if unset in production). Never hard-code passwords.
- `SkillSeeder`: about 40 realistic IT skills across categories.
- `VerificationRequirementSeeder`: the five coordinator-named items.
- `DemoDataSeeder` (guarded by `APP_ENV` != production unless `ALLOW_DEMO_SEED=true`): about 10 synthetic students, 6 companies in mixed states, 12 opportunities, a few applications, MOAs in different custody positions. All clearly fake names and `@example.test` emails.
