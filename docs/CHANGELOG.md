# ConnectEd Changelog

All notable changes to the ConnectEd system are documented in this file in chronological order by phase.

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
