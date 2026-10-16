# ConnectEd Assumption Log

This document records architectural, operational, and institutional assumptions made during the design and implementation of ConnectEd Version 1. Each entry documents the decision, the rationale, and the procedure to adjust it when institutional policy or requirements become finalized.

---

### A1: Partner Company (PC) Permissions
- **Decision:** Partner Companies can edit selected profile fields (contact person, bio, phone, address, website), create and manage their own internship opportunities, and review student applicants without seeing any match scores or student rankings. Formal acceptance/rejection decisions are made by the OJT Coordinator (OC).
- **Reason:** Aligns with the core capstone principle that human administrators retain authority over student endorsements and placements, preventing unauthorized exploitation or unfair filtering of applicants.
- **How to change:** Modify `app/Policies/CompanyPolicy.php`, `app/Policies/ApplicationPolicy.php`, and the PC permission matrix in `config/connected.php`.

---

### A2: Student Registration and Account Approval
- **Decision:** Students self-register by providing their student number, program, year level, section, email, and password, and consenting to the privacy notice. New student accounts are set to `status = 'pending'` and require OC review and approval before login is permitted.
- **Reason:** Prevents unauthorized or non-BSAU individuals from accessing company partner listings, student portfolios, and internship records.
- **How to change:** Toggle `config('connected.student_registration_requires_approval')` to `false` if instant activation or institutional SSO/LDAP is adopted.

---

### A3: Co-OJT Coordinator (COC) Delegated Scope
- **Decision:** COCs can only view and manage students, tasks, and MOAs explicitly assigned to them by the OC. COCs cannot formally verify companies, promote New Companies to Existing Partners, or make final application acceptance decisions.
- **Reason:** The OJT Coordinator maintains single-point institutional accountability, while COCs provide operational support for student cohorts.
- **How to change:** Update `app/Policies/` and assignment middleware checks in `app/Http/Controllers/`.

---

### A4: University Attorney (UA) Internal Legal Notes
- **Decision:** UA notes and revision requests are internal institutional communication visible to the OC, COCs, and UA only. The OC is responsible for communicating any necessary revisions to students or partner companies.
- **Reason:** Protects privileged legal communications, prevents confusion between external partners and legal counsel, and centralizes correspondence through the OJT office.
- **How to change:** Update `MoaNotePolicy` and the MOA view components.

---

### A5: Opportunity Approval Workflow
- **Decision:** Opportunities posted by Partner Companies enter `status = 'pending_approval'` and require OC approval before becoming visible to students. OC can post immediately approved opportunities.
- **Reason:** Ensures internship job descriptions, safety conditions, and learning competencies align with BSAU ICS curriculum standards before students apply.
- **How to change:** Set `config('connected.opportunity_requires_approval')` to `false` in `config/connected.php`.

---

### A6: Placement Finalization Auto-Withdrawal
- **Decision:** When a student confirms an accepted offer and the OC confirms the placement, creating an active `Placement` record, all other pending and under-review applications for that student are automatically set to `withdrawn` with the system note `"Placement finalized"`.
- **Reason:** Enforces the strict rule that a student may only hold one active placement at a time (BR-19), releasing unused opportunities to other classmates.
- **How to change:** Modify the placement finalization transaction in `app/Services/PlacementService.php`.

---

### A7: Academic Program Scope
- **Decision:** The system defaults to `['BSIT']` (Bachelor of Science in Information Technology) under the Institute of Computer Studies (ICS). The program list is stored as a configuration array.
- **Reason:** The original research paper focused specifically on BSIT students, but the revised brief references "ICS Students".
- **How to change:** Add additional degree programs (e.g., `'BSIS'`, `'BSCS'`) to `config('connected.allowed_programs')`.

---

### A8: Password Resets Without Email Service
- **Decision:** Because outbound email/SMS is explicitly out of scope, self-service email password resets are disabled. Users who forget their password request a reset through the OJT Coordinator, who generates a temporary password and flags `must_change_password = true`, requiring the user to set a new password on their next login.
- **Reason:** Adheres strictly to the "no email / in-system only" constraint (BR-05).
- **How to change:** Enable standard Laravel Breeze notification mailers and configure SMTP/Resend/Mailgun in `.env` if email infrastructure is provisioned in the future.

---

### A9: Weekly Reports Document-Only Handling
- **Decision:** Weekly reports are submitted as uploaded documents (PDF/DOCX) with a status indicator (`submitted`, `reviewed`, `flagged`). No daily time record (DTR) clock-in/out, hour counters, or payroll calculations are performed.
- **Reason:** DTR, timecard logging, and biometric attendance are confirmed out of scope by the capstone specification.
- **How to change:** Expand `weekly_reports` into a full attendance/DTR module if authorized.

---

### A10: Student Company Suggestion Intake
- **Decision:** Students can submit a simple form proposing an unlisted company (`company_name`, `contact_person`, `contact_details`, `notes`). The OC reviews the suggestion, initiates external contact, and can convert the suggestion into a `new_company` Company Record.
- **Reason:** Companies never self-register; student suggestions allow students to find their own OJT hosts while keeping vetting under the OC.
- **How to change:** Update `CompanySuggestionController` and `app/Services/CompanyService.php`.

---

### A11: Dynamic Verification Checklist
- **Decision:** The company verification checklist is stored in the `verification_requirements` database table and seeded with the 5 coordinator-specified documents (MOA, Business Registration Proof [SEC/DTI/Mayor's Permit], Internship Contract, Certificate of Completion, and Signed Accomplishment Reports). Stages are unconstrained (`stage = null`) so documents can be submitted and reviewed in any order.
- **Reason:** Institutional requirements evolve across semesters; hardcoded schema columns would break flexibility.
- **How to change:** The OC can add, reorder, edit, or deactivate checklist items via the Verification Checklist admin settings.

---

### A12: Long-Term Data Retention and Archiving
- **Decision:** All tables implement `SoftDeletes`. Hard deletion (`forceDelete`) is strictly disabled in app code. No records or uploaded files are permanently purged.
- **Reason:** Fulfills the requirement that "all information will be archived", preserving student compliance records and legal agreements for accreditation audits.
- **How to change:** When BSAU issues an official document retention policy (e.g., 5-year retention under CHED/DPA guidelines), create an artisan command `connected:purge-expired-archives` governed by policy.

---

### A13: Framework Lifecycle (Laravel 10 / PHP 8.3)
- **Decision:** Laravel 10 on PHP 8.3 is retained as specified by the capstone prompt.
- **Reason:** Meets project specification and maintains full compatibility with FrankenPHP containerization on Vercel and Neon PostgreSQL.
- **How to change:** Perform standard Laravel upgrade steps (`composer update "laravel/framework:^11.0"`) post-capstone evaluation.

---

### A14: Default School Year
- **Decision:** Records are tagged with `school_year` defaulting to `"2026-2027"`. The active school year is controlled by `config('connected.current_school_year')`.
- **Reason:** Enables archiving past academic batches while keeping active students and placements segmented.
- **How to change:** Update `CURRENT_SCHOOL_YEAR` in `.env` or settings.

---

### A15: File Storage Strategy (Local Dev vs Cloud S3)
- **Decision:** A unified `DocumentService` handles direct-to-storage presigned uploads. In local development, a signed local URL controller mocks presigned PUT/GET behavior without requiring an AWS account. In production/staging, it switches to S3-compatible cloud storage (Cloudflare R2, Supabase, or AWS S3).
- **Reason:** Guarantees that local developer workflows and unit tests run 100% reliably offline without third-party cloud costs, while production deployment to Vercel/FrankenPHP uploads directly to durable storage without memory or request timeout issues.
- **How to change:** Set `FILESYSTEM_DISK=s3` and fill S3 environment variables in `.env`.

---

### A16: Authentication Rate Limiting
- **Decision:** Failed login attempts are throttled to 5 attempts per minute per email/IP combination, displaying a non-leaky lockout notification.
- **Reason:** Protects student and staff credentials from brute-force dictionary attacks.
- **How to change:** Adjust throttle limits in `app/Http/Requests/Auth/LoginRequest.php`.

---

### A17: Concurrency Control on Placement Finalization
- **Decision:** Placement finalization employs database transactions with row-level pessimistic locking (`lockForUpdate()`) on the student record alongside a partial unique database index (`WHERE is_active = true AND deleted_at IS NULL`).
- **Reason:** Prevents race conditions where simultaneous confirmations could create dual active placements.
- **How to change:** Modifiable in `app/Services/PlacementService.php`.

---

### A18: UI Accessibility and Zero Theme Flash
- **Decision:** The interface provides a 3-way theme toggle (Light / Dark / System) with an inline blocking head script that inspects `localStorage` and `prefers-color-scheme` before DOM rendering, eliminating FOUC (flash of unstyled content). Color pairings meet WCAG AA (4.5:1).
- **Reason:** Ensures accessibility for nighttime studying and staff data entry across devices.
- **How to change:** Modify `resources/views/layouts/app.blade.php` and `resources/css/app.css`.

---

### A19: Zip Bundle Download Limits
- **Decision:** Bulk document bundle downloads (ZIP) stream up to 50 files or 100 MB per batch. Requests exceeding this threshold are chunked.
- **Reason:** Protects serverless and container runtimes against memory exhaustion and function timeout limits.
- **How to change:** Modify `config('connected.bundle_max_files')` and `config('connected.bundle_max_mb')`.

---

### A20: LinkedIn-Inspired UI/UX & Layout Architecture
- **Decision:** The UI/UX architecture takes design inspiration from modern professional platforms (such as LinkedIn): clean card-based opportunity cards, clear profile layouts (hero banner, education, skills chips, portfolio, certifications), sticky navigation topbar with unread notification badge, role-tailored sidebars, and high-contrast readable typography (Inter + Poppins), while maintaining BSAU ICS color palette and dual light/dark themes.
- **Reason:** Provides students and coordinators with an instantly familiar, intuitive, and professional interface that reduces onboarding friction.
- **How to change:** Customize Blade layout templates in `resources/views/layouts/` and theme variables in `resources/css/app.css`.

---

### A21: Universal Multi-Criteria Filter Systems
- **Decision:** Every table, directory, and document repository across all 5 roles incorporates comprehensive, server-side paginated filtering (e.g., student filters by skills/mode/location/date; coordinator filters by placement/checklist status/assigned staff; company filters by opportunity/applicant status; document library filters by category/owner/date).
- **Reason:** Critical for high usability when managing large cohorts of students and multiple partner companies.
- **How to change:** Filter parameters handled via scoped query builders in Eloquent models and reusable Blade filter bar components.

---

### A22: Recommendation Engine & Company Requirement Alignment
- **Decision:** The recommendation algorithm matches opportunities to students based on required skills (weighted x2), preferred skills (weighted x1), and recency of posting. For companies, applicant profiles clearly display skill breakdowns matching the company's posted requirements; however, to uphold strict institutional anti-bias rules (BR-20), applicants are never numerically ranked or auto-sorted against each other for company or staff views.
- **Reason:** Ensures students discover the "latest and best-fit" internships while protecting equitable, holistic evaluation of applicants by human coordinators.
- **How to change:** Governed by `app/Services/SkillMatcher.php`.

---

### A23: Code Originality & Open-Source Licensing Compliance
- **Decision:** All codebase components are 100% original, bespoke implementations built on permissible, standard open-source foundations (Laravel 10 [MIT], Tailwind CSS [MIT], Alpine.js [MIT], Fontsource [OFL/MIT]). No copyrighted, scraped, or unauthorized proprietary code is used.
- **Reason:** Ensures legal, academic, and ethical integrity for an official BSAU capstone project.
- **How to change:** Maintained via strict dependency auditing (`composer audit`, `npm audit`).

