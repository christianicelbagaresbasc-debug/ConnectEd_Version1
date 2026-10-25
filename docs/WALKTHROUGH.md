# ConnectEd System Walkthrough & Evaluation Guide

**Bulacan State Agricultural University (BSAU) &bull; Institute of Computer Studies (ICS)**  
**Skill-Based OJT Placement and Company Verification System**

---

## 1. System Overview & Architecture

ConnectEd is an institutional web platform designed for Bulacan State Agricultural University (BSAU) to streamline On-the-Job Training (OJT) operations. It provides centralized partner enterprise verification, multi-party Memorandum of Agreement (MOA) tracking, transparent skill-based internship recommendations, concurrency-safe student placements, narrative weekly report monitoring, and institutional CHED/accreditation reporting.

### Technology Stack
- **Application Core:** Laravel 10 on PHP 8.3
- **App Shell & Styling:** Laravel Breeze (Blade templates) + Tailwind CSS 3 via Vite + Alpine.js
- **Persistence:** PostgreSQL 16 on Neon (Local: SQLite)
- **Document Management:** Custom `DocumentService` with MIME/magic-byte integrity checks and direct-to-bucket S3 compatibility
- **Deployment Model:** Containerized FrankenPHP Caddy server targeted for Vercel Serverless / Cloud Hosting

---

## 2. Five Institutional User Personas

All accounts are pre-seeded with synthetic test data. Default password for all seed accounts: **`Password123!`**

| Role | Name | Email | Password | Primary Console |
|---|---|---|---|---|
| **OJT Coordinator (OC)** | Dr. Elena Santos | `coordinator@bsau.edu.ph` | `Password123!` | `/dashboard` (OC Command Center) |
| **Co-OJT Coordinator (COC)** | Prof. Marcus Reyes | `cocoordinator@bsau.edu.ph` | `Password123!` | `/dashboard` (Cohort Workspace) |
| **University Attorney (UA)** | Atty. Ramon Morales | `attorney@bsau.edu.ph` | `Password123!` | `/dashboard` (Legal Vetting Console) |
| **Partner Company (PC)** | Nexus Software Labs | `partner@company.com` | `Password123!` | `/dashboard` (Host Company Portal) |
| **ICS Student** | Juan Dela Cruz | `student@bsau.edu.ph` | `Password123!` | `/dashboard` (Student Intern Hub) |

---

## 3. End-to-End Role Walkthroughs

### 1. OJT Coordinator (OC) &mdash; `coordinator@bsau.edu.ph`
1. **Command Center (`/dashboard`):**
   - Review high-level metrics: Total Students, Placed %, Unplaced Students, Pending Verifications, Active Placements.
   - **"Students Without a Company" Widget:** Direct access to unplaced students, filterable by academic program, with inline Co-Coordinator delegation.
   - **MOA Pipeline Status:** Real-time distribution across Draft, Under Review, Legal Review, Signing, and Executed.
   - **Task Feed & Scope Boundary Card:** Urgent administrative tasks and clear notification that Daily Time Record (DTR) / Attendance is out of current capstone scope.
2. **Company Records & Accreditation (`/companies`):**
   - Browse existing accredited partners vs. new unverified companies.
   - Access **6-Point Statutory Verification Checklist** (SEC, Mayor's Permit, DTI, BIR 2303, PhilGEPS, Company Profile).
   - Promote verified companies to Existing Partner status (**Rule BR-08** requires 100% verified statutory items + active executed MOA).
3. **Multi-Party MOA Tracking (`/moas`):**
   - Manage university-wide umbrella MOAs and student-specific agreements.
   - Execute unconstrained physical custody transfers (**Rule BR-16**).
4. **Student Cohort Assignment (`/coordinator/students`):**
   - Assign individual students or bulk-assign entire program/year cohorts to specific Co-Coordinators.
5. **Institutional Reports & Exports (`/reports`):**
   - View summary metrics and stream RFC 4180 compliant CSV exports for Placements, Company Accreditation, and Curriculum Skill Alignment.

---

### 2. Co-OJT Coordinator (COC) &mdash; `cocoordinator@bsau.edu.ph`
1. **Co-Coordinator Workspace (`/dashboard`):**
   - View assigned student cohort count and active placement progress.
   - **Assigned Task Checklist (`/tasks`):** Interactive task checklist with instant status toggles (`To Do` &rarr; `In Progress` &rarr; `Done`).
2. **Weekly Narrative Report Review (`/placements/{id}`):**
   - Download student-submitted weekly reports.
   - Formally approve or flag reports for revision with audit remarks (**Rule BR-14**).

---

### 3. University Attorney (UA) &mdash; `attorney@bsau.edu.ph`
1. **Legal Console (`/dashboard`):**
   - View priority queue of MOAs in `under_legal_review` status.
2. **Legal Vetting & Notes (`/attorney/moas` & `/moas/{id}`):**
   - Review agreement version drafts and terms.
   - Log confidential legal remarks and risk notes (**Rule BR-18**).
   - Issue formal legal review clearance, transitioning agreement to university signing.

---

### 4. Partner Company (PC) &mdash; `partner@company.com`
1. **Company Portal (`/dashboard`):**
   - View company accreditation standing and active internship openings.
2. **Opportunity Management (`/opportunities`):**
   - Create internship postings with required and preferred technical competencies, location, work arrangement, and allocated intern slots.
3. **Applicant Review & Decision (`/company/applications`):**
   - Review incoming student applications.
   - **Rule BR-20 Compliance:** Candidates are listed chronologically by submission time. **No match scores or student rankings are visible to the company.**
   - Mark applications under review, accept, or reject candidates.

---

### 5. ICS Student &mdash; `student@bsau.edu.ph`
1. **Intern Hub (`/dashboard`):**
   - **Placement Hero Banner:** If unplaced, displays "Seeking OJT Placement"; if placed, displays host company and placement hub link (**Rule BR-19**).
   - **Profile Readiness Meter:** 0-100% checklist tracking basic academic profile, contact details, skills, resume, and data privacy consent.
   - **Top Skill Recommendations:** Live matched opportunities showing matched skills and match score percentage (**visible strictly to the student**).
2. **Skills Profile (`/student/profile/skills`):**
   - Register acquired IT competencies and proficiency levels.
   - Suggest unlisted technologies for coordinator review.
3. **Opportunities & Application (`/opportunities`):**
   - Browse open opportunities from verified existing partner companies (**Rule BR-04**).
   - Submit formal applications with cover notes and uploaded resumes.
4. **Offer Confirmation & Placement (`/student/applications`):**
   - Receive company acceptance offers.
   - Confirm offer &mdash; triggers atomic placement finalization, creates active placement record, and automatically withdraws competing pending applications (**Rule BR-12**).
5. **Weekly Report Submissions (`/student/placements/{id}`):**
   - Upload weekly narrative reports with magic-byte validation and SHA-256 integrity checksums.

---

## 4. Key Hard Rules Enforced Across the System

| Rule ID | Core Constraint | Implementation Guard |
|---|---|---|
| **BR-01** | Companies never self-register | Created and maintained exclusively by OC |
| **BR-02** | In-system notifications only | `ConnectEdNotification::via() => ['database']` (No email/SMS) |
| **BR-15** | Soft deletes and audit everywhere | `SoftDeletes` trait + immutable `audit_logs` table |
| **BR-19** | Single active placement concurrency | Atomic database guard (`has_active_placement`) + automatic application withdrawal |
| **BR-20** | Match scores strictly private to student | Computed on the fly; never sorted or presented to companies/staff |
| **A19** | Batch ZIP bundle limits | Capped at 50 files and 100 MB per batch |

---

## 5. Verification & Test Metrics

- **Total Test Cases:** **194 automated feature tests**
- **Test Assertions:** **787 assertions** (100% passing)
- **Frontend Bundle:** Clean Vite compilation (`npm run build`)
- **Linting & Code Style:** Laravel Pint clean (`./vendor/bin/pint --test`)
- **Security Check:** Zero NPM vulnerabilities; security headers attached; custom 403, 404, 419, and 500 error pages.
