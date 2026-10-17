# ConnectEd Changelog

All notable changes to the ConnectEd system are documented in this file in chronological order by phase.

## [Phase 5: Opportunities, Skills Catalog, Transparent Matching, and Student Directory] - 2026-10-17

### Added
- **IT Skills Catalog & Student Competency Profile (F-011):**
  - Standardized IT skills catalog (`/coordinator/skills`) seeded with 40 realistic IT competencies across Web Development, Programming, Database, Networking, DevOps, Cloud, Security, QA, Design, and Soft Skills (`SkillSeeder`).
  - Catalog management: case-insensitive unique naming (`lower(name)` PostgreSQL index), category filtering, status management (`active`, `pending`, `inactive`), editing, and soft-delete archiving (BR-15).
  - Student skill profile workspace (`/student/profile/skills`) allowing students to claim competencies with proficiency levels (`Beginner`, `Intermediate`, `Advanced`) and update them inline.
  - Student skill suggestion intake (`/student/skills/suggest`) allowing students to propose missing competencies, which enter a coordinator review queue with an attention banner.
  - Student certifications management supporting credentials (title, issuer, issue date) with optional document download links (FR-03).
  - Alphabetical ICS student directory (`/coordinator/students`) and detailed student profile inspector (`/coordinator/students/{id}`) for coordinators and co-coordinators.
- **Opportunity Management & Partner Posting Approvals (F-012):**
  - Centralized OJT opportunity directory (`/opportunities`) with multi-criteria search and filters: role title/description, work arrangement (`onsite`, `remote`, `hybrid`), required/preferred skills, partner company, location, and publication status.
  - OJT posting creation (`/opportunities/create`) and editing (`/opportunities/{id}/edit`) supporting role duties, prerequisites, capacity slots, deadline, and interactive required (2x weight) and preferred (1x weight) skill pickers.
  - Assumption A5 enforcement: Partner Company (PC) postings default to `pending_approval` status, requiring explicit coordinator verification and approval (`/opportunities/{id}/approve`) before becoming visible to students.
  - Student visibility scoping: students are restricted strictly to open opportunities from verified existing partner organizations with available slots and active deadlines.
  - Opportunity soft-deletion archiving into the Compliance Archive Center (BR-15).
- **Transparent Skill-Based Recommendation Engine (F-013):**
  - Pure service matching class (`SkillMatcher`) calculating deterministic match percentages:
    $$\text{score} = \text{round}\left(100 \times \frac{2 \times \text{matched\_required} + 1 \times \text{matched\_preferred}}{2 \times \text{total\_required} + 1 \times \text{total\_preferred}}\right)$$
  - Strict Rule BR-20 implementation (**Non-Ranking of Students & Confidentiality**):
    - Match scores are visible **only** to the student concerned on `/student/recommendations`.
    - Non-student roles (OC, COC, UA, PC) receive immediate HTTP 403 Forbidden responses when attempting to access recommendations.
    - Match scores are never calculated for or displayed on coordinator or partner views, and no query or controller ranks or sorts students by match score.
  - Detailed reasoning breakdown for students: matched skills, missing required skills (2x penalty), and missing preferred skills (1x penalty) with color-coded status badges (`Strong match`, `Good match`, `Partial match`, `Low match`, `No skill data`).
- **Testing & Verification:**
  - Added 28 new automated tests across 5 test classes (7 unit tests and 21 feature tests):
    - `SkillMatcherTest` (7 tests: all matched 100%, none matched 0%, only preferred, no required skills, no skills at all, deduplication, inactive/archived skills ignored).
    - `SkillManagementTest` (8 tests: coordinator catalog display, create skill, unique name validation, update, student suggestion, coordinator approval, archive, non-coordinator forbidden).
    - `OpportunityManagementTest` (7 tests: directory filters, create with skills, PC pending approval per A5, coordinator approval, student verified scoping, update/sync skills, soft delete).
    - `StudentSkillProfileTest` (6 tests: profile display, add skill with proficiency, update proficiency, remove skill, add/remove certification, cross-student modification forbidden).
    - `RecommendationTest` (3 tests: student match score and reasoning breakdown, BR-20 non-student 403 block, BR-20 codebase grep verifying no sorting of students by match score).
    - `StudentDirectoryTest` (4 tests: OC/COC directory access, search and multi-criteria filters, individual profile inspection, non-coordinator forbidden).
  - Full test suite passing: **138 tests passing (558 assertions), 100% pass rate**.
  - Production asset build verified (`npm run build`).

---

## [Phase 4: Multi-Party MOA Tracking, Version History, and Attorney Review] - 2026-10-16

### Added
- **Multi-Party MOA Tracking & Version History (F-009):**
  - Centralized institutional MOA directory (`/moas`) supporting both umbrella university-wide partnerships (`university_company`) and student-specific training agreements (`student_company`).
  - Multi-criteria directory filters: keyword search (MOA number, company name, summary), lifecycle status, agreement type, and current custodian party.
  - Comprehensive agreement drafting view (`/moas/create`) with dynamic Alpine.js student intern selector and initial custody assignment.
  - Full-featured MOA workspace (`/moas/{id}`) displaying lifecycle status badges, current holding party highlight, effective dates, and interactive modal dialogs for custody transfers, version attachments, and status updates.
  - Document versioning (`moa_versions`) enabling sequential version tracking (`version_no`), document storage integration, and uploader attribution.
  - Strict Rule BR-16 implementation (**Unconstrained Custody Order**): Physical or digital custody of MOA documents may transfer between any authorized party (`oc`, `coc`, `ua`, `student`, `company`) in whatever sequence real-world paperwork demands; no rigid linear progression is enforced.
  - Strictly append-only `moa_transfers` table recording transfer provenance (sender, recipient, actor, timestamp, note) with runtime Eloquent protection against modification and deletion.
  - Agreement metadata editing (`/moas/{id}/edit`) and soft-deletion archiving into the Compliance Archive Center (BR-15).
- **University Attorney (UA) Legal Review Queue & Confidential Notes (F-010):**
  - Dedicated legal counsel review queue (`/attorney/moas`) providing metric counters for pending legal audits, active high-risk legal remarks, and total institutional contracts.
  - Queue filters toggling between action-required contracts (under legal review or with unresolved remarks) and complete institutional contract audits.
  - Strict Rule BR-18 implementation (**Confidentiality of Legal Notes**):
    - Legal remarks, revision directives, and risk level classifications (`high`, `medium`, `low`) are strictly confidential internal work product of BSAU legal counsel and coordinators (`oc`, `coc`, `ua`).
    - Server-side authorization in `MoaPolicy::viewInternalNotes` and `MoaPolicy::addInternalNote` completely blocks Partner Company (PC) and Student views; internal remarks never leak into HTML or API responses.
    - Non-university users attempting to access legal endpoints receive immediate HTTP 403 Forbidden responses.
  - Inline revision resolution tracking (`/attorney/moas/{id}/notes/{note}/resolve`) recording resolving attorney/coordinator and timestamp.
  - Formal attorney clearance workflow (`/attorney/moas/{id}/clear`) transitioning agreement status to execution phases with permanent audit notes.
- **Testing & Verification:**
  - Added 15 new automated tests across 2 feature test classes:
    - `MoaTrackingTest` (9 tests: directory filters, create with initial custody, unique MOA number validation, BR-16 unconstrained custody flow, transfer deletion block, version incrementing, metadata update, soft-delete archiving, non-OC authorization rules).
    - `MoaLegalReviewTest` (6 tests: attorney queue display, non-legal 403 blocks, internal notes creation, strict BR-18 confidentiality verification, revision resolution, formal attorney clearance).
  - Full test suite passing: **103 tests passing (412 assertions), 100% pass rate**.
  - Frontend production build verified (`npm run build`).

---

## [Phase 3: Company Records, 6-Point Verification Checklist, and Student Suggestions] - 2026-10-16

### Added
- **Company Records Management (F-006):**
  - Centralized Host Training Establishment (HTE) directory (`/coordinator/companies`) with LinkedIn-style cards, search (name, industry, city), classification and status filter dropdowns, and active toggle.
  - OJT Coordinator exclusive company registration form (`/coordinator/companies/create`) with curriculum alignment notes (BSIT fit) and contact details.
  - Automatic initialization of all active verification checklist requirements in `missing` state upon company creation.
  - LinkedIn-inspired company profile card (`/coordinator/companies/{id}`) with hero banner, contact info, curriculum fit notes, associated partner accounts, and verification audit timeline.
  - Company profile metadata editing (`/coordinator/companies/{id}/edit`) and soft-deletion with automatic logging in Archive Center (BR-15).
  - Strict Rule BR-17 implementation: New Companies can only become Existing Partners when `verification_status` is `verified` and the promotion is executed by explicit human OC decision; automated promotion is blocked.
  - Student Partner Companies directory (`/student/companies`) restricting student visibility strictly to verified organizations and active existing partners (BR-20).
- **6-Point Company Verification Checklist (F-007):**
  - Configurable statutory checklist requirements table (`verification_requirements`) seeded with 5 BSAU ICS criteria: Proof of Business Registration, Memorandum of Agreement (MOA), Internship Training Plan & Contract, Certificate of Completion Format, and Signed Accomplishment Report Commitment.
  - Dedicated coordinator verification workspace (`/coordinator/companies/{id}/verification`) with compliance scorecard, item review expanders, and overall decision bar.
  - Item-level checklist reviews (`missing`, `submitted`, `accepted`, `rejected`) with reviewer attribution and compliance remarks.
  - Overall company verification status transitions (`pending`, `under_review`, `verified`, `rejected`, `revoked`) requiring mandatory coordinator audit remarks (min 5 chars).
  - Strictly append-only `company_verification_logs` table protected against runtime modification and deletion to preserve regulatory compliance integrity.
- **Student Company Suggestion Intake & Conversion (F-008):**
  - Student suggestion intake form (`/student/company-suggestions/create`) and personal tracking dashboard (`/student/company-suggestions`) with status badges (`Submitted`, `Converted`, `Dismissed`).
  - Coordinator suggestions intake queue (`/coordinator/company-suggestions`) with keyword search and status filter.
  - Suggestion conversion modal allowing coordinators to pre-fill company details, create official `new_company` record, and auto-initialize the verification checklist.
  - Dismissal modal with custom coordinator feedback explaining reasons for ineligibility.
- **Testing & Verification:**
  - Added 21 new automated tests across 4 feature test classes:
    - `CompanyManagementTest` (6 tests: index filters, creation & checklist auto-seed, non-OC block, unique name validation, edit profile, soft delete).
    - `CompanyVerificationTest` (5 tests: checklist page rendering, item review & acceptance, overall status transition, remark validation, non-OC block).
    - `CompanyPromotionRuleTest` (4 tests: unverified promotion block, verified promotion success, existing partner block, non-coordinator block).
    - `CompanySuggestionTest` (6 tests: student submission, student view own, coordinator queue, conversion to company, dismissal with reason, student directory visibility rule).
  - Full test suite passing: **88 tests passing (321 assertions), 100% pass rate**.
  - Frontend production build verified (`npm run build`).

---

## [Phase 2: Accounts, RBAC, Audit, Archive, and Document Service] - 2026-10-16

### Added
- **Student Registration & Lifecycle (F-002):**
  - Self-registration with student number uniqueness, program validation (`BSIT`), and mandatory Data Privacy Act of 2012 (RA 10173) consent dialog.
  - Accounts enter `pending` status without authenticating; unauthorized login attempts blocked.
  - OJT Coordinator approval queue (`/coordinator/students/pending`) with live search by name, student number, and degree program filter.
  - One-click approval transitions account to `active` and records approval audit trail.
  - Modal rejection captures auditor remark, soft-deletes user record to Archive Center, and logs `student.rejected`.
- **Staff Provisioning & Credential Security (F-003):**
  - OJT Coordinator exclusive provisioning (`/coordinator/staff/create`) for `coc`, `ua`, and `pc` roles; blocks direct student creation.
  - Partner Company profile generation (`partner_accounts`) storing representative department, job title, and phone number.
  - Automatic cryptographically secure temporary password generation with `must_change_password = true`.
  - Global `EnsurePasswordIsChanged` middleware enforcing password change before accessing any dashboard/protected routes.
  - Password change screen (`/password/forced-change`) validating current password and enforcing BSAU 10+ character complexity with numbers and mixed case.
  - Coordinator password reset utility (`/coordinator/users/{user}/reset-password`) generating temporary credentials and requiring rotation on next login.
- **Document Service & Presigned Direct Storage (F-004):**
  - Direct S3/R2 presigned PUT upload architecture avoiding web server memory bottlenecks.
  - Two-step upload: slot reservation (`/documents/uploads`) -> direct upload -> batch integrity confirmation (`/documents/uploads/{batch}/confirm`).
  - Pre-upload validation on file extension allowlist (`.pdf`, `.docx`, `.png`, `.jpg`, `.jpeg`) and size limits (5MB for resumes, 10MB generic).
  - Post-upload server-side stream verification: magic-byte file signature inspection and full SHA256 checksum computation.
  - Role-based authorization matrix (`DocumentPolicy`, `DocumentService::canUpload`, `canDownload`) restricting access by category and ownership.
  - Reusable Alpine.js drag-and-drop component (`x-dropzone`) with progress bars, file inspection, and batch confirmation.
  - Local development mock signed routes (`/documents/upload/direct`, `/documents/{document}/serve`) simulating S3 presigned operations.
- **Compliance Archive Center & Immutable Audit Trail (F-005):**
  - Strictly append-only `audit_logs` table (no soft deletes) with runtime model boot protections throwing `\RuntimeException` on any update or delete attempts.
  - Automated `AuditService::sanitizeChanges` stripping passwords, tokens, and sensitive keys.
  - Coordinator audit trail viewer (`/coordinator/audit-logs`) with multi-criteria filtering by action, actor user ID, and date, plus interactive Alpine JSON viewer modal.
  - Centralized Archive Center (`/coordinator/archive`) with tabbed navigation for soft-deleted accounts and documents with single-click restoration and audit logging.
- **Testing & Verification:**
  - Added 36 new automated tests across 7 feature test classes (`RegistrationTest`, `StudentRegistrationApprovalTest`, `StaffProvisioningTest`, `ForcedPasswordChangeTest`, `AuditLogTest`, `ArchiveRestoreTest`, `DocumentAuthorizationMatrixTest`, `FileValidationTest`).
  - All 67 automated tests passing with 219 assertions.

---

## [Phase 1: Foundation and Walking Skeleton Deploy] - 2026-10-16

### Added
- **Core Framework Foundation:** Scaffolded Laravel 10.50.3 on PHP 8.3/8.5 with Laravel Breeze (Blade).
- **Identity & Roles Schema:**
  - Extended `users` table with `role` (`oc`, `coc`, `ua`, `pc`, `student`), `status` (`pending`, `active`, `suspended`), `must_change_password`, `approved_by`, `approved_at`, `last_login_at`, and `SoftDeletes`.
  - Created PHP 8.3 backed enums `App\Enums\UserRole` and `App\Enums\UserStatus` with human-readable labels.
  - Implemented `UserSeeder` with synthetic accounts for all 5 roles plus pending student state.
  - Added migrations for database-backed sessions and database cache tables for ephemeral container hosting.
- **Design System & LinkedIn-Inspired UI:**
  - Configured `@fontsource/inter` and `@fontsource/poppins` self-hosted typography with zero runtime CDN calls.
  - Defined full CSS custom property tokens (`--bg`, `--surface`, `--primary`, `--accent`, `--border`, etc.) with class-based light/dark theme switching.
  - Implemented blocking theme script in `<head>` to permanently eliminate flash of unstyled theme (FOUC).
  - Built interactive 3-state `ThemeToggle` component (Light, Dark, System) with media query listeners.
  - Built responsive application shell layout (`resources/views/layouts/app.blade.php`) featuring fixed left sidebar, tablet rail, mobile off-canvas drawer, topbar search, and notification bell counter.
  - Built institutional landing page (`resources/views/welcome.blade.php`) and accessible sign-in view (`resources/views/auth/login.blade.php`) with demo account quick-fill credentials.
- **Security & Quality:**
  - Enforced rate-limiting (5 attempts/minute) on login.
  - Blocked pending and suspended users with distinct, secure error messaging.
  - Updated `TrustProxies` middleware to trust all proxies for Vercel edge container routing.
  - Enforced HTTPS in production via `AppServiceProvider::boot()`.
  - Added `/health` smoke-test endpoint returning JSON status.
- **Cloud & Deployment Infrastructure:**
  - Configured `Dockerfile.vercel` using multi-stage Node 20 asset bundling and FrankenPHP 1 (PHP 8.3).
  - Added `Caddyfile`, `vercel.json` container service specification, and `.dockerignore`.
  - Added `.github/workflows/ci.yml` running PHP 8.3, PostgreSQL 16 container, Vite asset build, test runner, and security audits.
  - Configured S3 flysystem driver (`league/flysystem-aws-s3-v3`) with dual local/S3 `documents` disk configuration.
- **Testing:**
  - 31 automated tests passing covering `/health`, authentication lifecycle, throttle defense, pending/suspended lockout, soft-delete verification, and theme preference rendering.
