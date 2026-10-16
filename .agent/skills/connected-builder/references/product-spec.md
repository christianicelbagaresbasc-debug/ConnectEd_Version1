# ConnectEd Product Spec (current)

Contents: 1 Purpose · 2 Roles · 3 Permission matrix · 4 Core workflows · 5 State machines · 6 Matching engine · 7 Notifications · 8 Archiving · 9 Dashboards · 10 Requirements (FR/NFR/BR) · 11 Assumptions to log · 12 Out of scope

## 1. Purpose

A web system for Bulacan State Agricultural University (BSAU), Institute of Computer Studies (ICS), that centralizes OJT company discovery, company verification, MOA tracking, skill-based opportunity recommendation, applications, and placement monitoring. It supports the human OJT process; it does not replace coordinator decisions.

## 2. Roles

| Code | Role | How the account is created |
|---|---|---|
| `oc` | OJT Coordinator | Seeded first account; OC can create other OCs |
| `coc` | Co-OJT Coordinator | Provisioned by OC |
| `ua` | University Attorney | Provisioned by OC |
| `pc` | Partner Company representative | Provisioned by OC, linked to one Company Record (Existing Partners only) |
| `student` | ICS Student | Self-registers, then OC approves (assumption A2) |

Role descriptions:
- **OC**: manages and monitors COCs, tasks, students, and company verification. Coordinates with UA on MOAs (university-company and student-company). Makes the formal accept/reject decision. Views and updates placement status.
- **COC**: works only on tasks and students assigned by the OC. Can view assigned students' MOAs and view/update their placement status.
- **UA**: monitors MOA progress, leaves revision notes and legal remarks.
- **PC**: represents an Existing Partner. Manages its company's profile fields the OC allows, its opportunities, and views its applicants.
- **Student**: maintains profile and skills, browses directory, gets recommendations, applies, confirms or declines accepted offers, suggests New Companies.

## 3. Permission matrix

R = read, C = create, U = update, A = archive, D = decide/approve. "own" = own record only. "assigned" = records linked to the COC via assignment.

| Module | OC | COC | UA | PC | Student |
|---|---|---|---|---|---|
| Users and accounts | CRUA, approve students, reset passwords | R assigned students | own | own | own profile |
| Company Records | CRUA | R | R (MOA-linked) | R/U own (limited fields) | R Existing Partners only |
| Verification and checklist | R/U/D | R | R | R own status | sees verified badge only |
| Classification (New to Existing) | D | none | none | none | none |
| MOA records | CRUA, transfer custody | R assigned students | R, notes, legal status | R own | R own |
| UA notes | R | R | CRU | none | none |
| Opportunities | CRUA, publish, close | R | none | C/U own (needs OC approval, A5) | R open ones |
| Skills catalog | CRUA | R | none | R | R, suggest new |
| Applications | R all, D accept/reject | R assigned | none | R applicants to own opps (no score, no ranking), may flag interest | CRU own |
| Placements | confirm, update status | update status (assigned) | none | R own | confirm or decline own |
| Tasks | CRUA, assign | R/U assigned | none | none | none |
| Reports | all | assigned scope | MOA reports | own company | none |
| Documents | see `files-and-documents.md` section 2 for the full role-by-category matrix | | | | |
| Audit log | R | none | none | none | none |

## 4. Core workflows

**Existing Partner workflow (streamlined, in-system):** PC or OC publishes opportunity (OC approves if required) → student sees it in directory and recommendations → student applies (multiple allowed) → OC reviews → OC records Accepted or Rejected → student Accepts (to confirm) or Declines → OC confirms → Placement created → status tracked.

**New Company workflow (administrative, manual contact):** Student finds a company independently and submits a **company suggestion** (name, contact, notes). OC creates a Company Record classified `new_company`, runs verification and the MOA process outside student-facing flow, and records the student's placement manually once the start-authorization email exists. A New Company becomes an Existing Partner only by OC decision after verification = verified and an executed MOA exists. No auto-conversion.

**Official placement marker:** The paper says an email authorizing the student to start OJT identifies the official placement. Record this as `start_authorized_at`, a short reference note, and an optional uploaded copy.

**MOA workflow:** a MOA record has no fixed stage order (coordinator-confirmed pain point). Custody can transfer between OC, COC, UA, student, and company in any order; every transfer is logged. Status is advisory metadata, not a gate, except that Existing Partner promotion requires an `executed` university-company MOA.

## 5. State machines

**Company classification:** `new_company` → `existing_partner` (OC only; requires verification `verified` and an executed MOA). `existing_partner` → `new_company` is not allowed; use `inactive` flag instead.

**Verification status:** `pending` → `under_review` → `verified` | `rejected`; `verified` → `revoked` (OC, reason required). Each status change stores reviewer, timestamp, and remark.

**Verification checklist:** table-driven (`verification_requirements`). Seed with the items the coordinator named: MOA; proof of business (SEC registration, DTI registration, or municipal business certificate); Internship Contract; Certificate of Completion; Signed Accomplishment Reports. Mark each with `stage` = null (unknown). OC can add, edit, reorder, or deactivate items. Do not hard-code which items are mandatory.

**MOA status:** `draft`, `in_review`, `legal_review`, `revision_requested`, `for_signature`, `executed`, `expired`, `cancelled`. Any transition allowed for OC; UA may set `legal_review`, `revision_requested`, `for_signature`. Types: `university_company`, `student_company`.

**Application status:** `pending`, `under_review`, `accepted`, `declined`, `rejected`, `withdrawn`, `placed`.

| From | To | Actor |
|---|---|---|
| pending | under_review | OC |
| pending, under_review | accepted, rejected | OC |
| pending, under_review, accepted | withdrawn | Student |
| accepted | declined | Student |
| accepted | placed | system, only after student_confirmed_at and oc_confirmed_at are both set |

Meanings: Accepted = formally accepted for the next stage (OC decision). Declined = student refused an accepted offer (no placement). Rejected = turned down by OC. Withdrawn = student pulled out. Placed = finalized.

**Placement status:** `placed` → `authorized` (start email recorded) → `ongoing` → `completed` | `terminated` (reason required). OC and assigned COC may update.

**Single active placement rule:** at most one placement in (`placed`, `authorized`, `ongoing`) per student. Enforce inside a DB transaction with `lockForUpdate()` on the student row, plus a test that simulates two concurrent confirmations. On placement creation, all other open applications of that student become `withdrawn` with system reason "placement finalized" and the student is notified (assumption A6).

## 6. Matching engine (transparent, rule-based, no ML)

Inputs: student's skills; opportunity's skills, each marked `required` or `preferred`.

```
weighted_matched = 2 * matched_required + 1 * matched_preferred
weighted_total   = 2 * total_required   + 1 * total_preferred
score = round(100 * weighted_matched / weighted_total)    // null if weighted_total = 0
label: >=80 "Strong match", 50-79 "Good match", 1-49 "Partial match", 0 "Low match", null "No skill data"
```

Always show the reasoning: matched skills, missing required skills, missing preferred skills. Skills are matched by standardized `skill_id`, never by free text.

Implement as a pure service class `SkillMatcher` with unit tests covering: all matched, none matched, only preferred matched, no required skills, no skills at all, duplicate skill rows, inactive/archived skills ignored.

Hard rules: the score is shown only to the student viewing their own recommendations. It is **never** shown on applicant lists, never used in `ORDER BY` for any list of students, and never exposed to PC, COC, UA, or OC views of applicants. Add a test that greps views/controllers for violations if practical.

Recommendations list: open opportunities from Existing Partners with verified company status, deadline not passed, slots available, sorted by score descending for that student only. Filters: location, work arrangement, skill, company.

## 7. Notifications (in-system only)

Use Laravel database notifications. Bell icon with unread count in the topbar, page with filters, mark read/all read, refresh on navigation (no websockets, no email).

Events: account approved; verification status changed; MOA custody transferred or status changed; UA note added (to OC, COC); opportunity approved/closed; application status changed; accepted offer awaiting student confirmation; placement created/status changed; task assigned; student assigned to COC.

## 8. Archiving ("all information will be archived")

- Every business table uses `SoftDeletes`. The UI says "Archive", never "Delete". No `forceDelete` anywhere in app code.
- OC has an Archive area to view and restore archived records per module.
- Uploaded files are never removed from storage by the app.
- Records carry `school_year` (e.g. "2026-2027") where relevant. The current school year is a config value so past batches stay browsable.
- Retention periods are unknown; record in ASSUMPTIONS.md as pending BSAU policy.

## 9. Dashboards

- **OC**: students without a company (primary widget, with filters), pending student approvals, verification queue, MOAs by status and custodian, applications awaiting decision, placements by status, task overview, attendance visibility **placeholder card** marked "not in current scope".
- **COC**: assigned students and their placement status, assigned tasks, MOAs of assigned students.
- **UA**: MOAs awaiting legal review, recent notes, MOA progress by status.
- **PC**: own opportunities, applicants count and list (no scores), slots filled.
- **Student**: profile completeness, recommendations, my applications, placement status, notifications.

## 10. Requirements

Keep the paper's IDs where meaning is unchanged. Use these IDs in docs, tests, and commit messages.

| ID | Requirement |
|---|---|
| FR-01 | Account creation: student self-registration (OC approval); provisioned accounts for staff and PC; no company self-registration |
| FR-02 | Authentication and role-based access |
| FR-03 | Student profile: personal, academic, skills, certifications, resume/CV, portfolio |
| FR-04 | Controlled document upload and validation |
| FR-05 | Company Records managed by authorized staff; PC access only for provisioned partners |
| FR-06 | Company verification with configurable checklist and status |
| FR-07 | Company directory distinguishing Existing Partners from New Companies |
| FR-08 | Opportunity management (OC, and PC with approval) |
| FR-09 | Opportunity requirements: skills (required/preferred), qualifications, slots, location, work arrangement, deadline |
| FR-10 | Opportunity search and filter |
| FR-11 | Transparent skill-based recommendation |
| FR-12 | Multiple applications per student |
| FR-13 | Application review and formal accept/reject by OC |
| FR-14 | Student accepts or declines an accepted opportunity |
| FR-15 | Placement finalized only after student and OC confirmation |
| FR-16 | Status tracking for applications and placements |
| FR-17 | In-system notifications |
| FR-18 | Monitoring views, students without a company, reports |
| FR-19 | Task management: OC creates and assigns tasks to COCs |
| FR-20 | Student-to-COC assignment |
| FR-21 | MOA tracking with custody history and version uploads |
| FR-22 | UA revision and legal notes on MOAs |
| FR-23 | Archiving of all records, archive browser and restore |
| FR-24 | Company suggestion by students (New Company intake) |
| FR-25 | Audit log viewer for OC |
| FR-26 | Bulk upload (multi-file, progress, retry) with role-based categories |
| FR-27 | Document library with filters, review status, completeness views |
| FR-28 | Authorized single and bundle (ZIP) download |
| FR-29 | Document versioning and template library |
| NFR-01 | Security (see `security.md`) |
| NFR-02 | Privacy: Data Privacy Act of 2012 (RA 10173) principles, consent, minimization |
| NFR-03 | Usability: clear navigation, understandable status labels |
| NFR-04 | Performance: common pages respond quickly at intended load; paginate and eager-load |
| NFR-05 | Reliability: transactions for application and placement changes |
| NFR-06 | Maintainability: modular code, Form Requests, Policies, Services, tests |
| NFR-07 | Compatibility: responsive for mobile, tablet, desktop; current Chrome, Edge, Firefox, Safari; light and dark mode |

Business rules: BR-01 to BR-14 from the paper remain valid except where this spec overrides them. Additional rules: BR-15 all records archived not deleted; BR-16 MOA order is not enforced; BR-17 only OC may promote a company to Existing Partner; BR-18 UA notes are internal (OC, COC, UA); BR-19 one active placement per student; BR-20 matching scores are visible only to the student concerned.

## 11. Assumptions to log in `docs/ASSUMPTIONS.md` at the start

- A1: PC permissions as in the matrix (profile fields limited by OC, opportunities, applicant view with no scores). Change if the team decides PCs may also record interest/decline.
- A2: Students self-register with student number and program, then OC approves. Alternative: OC bulk import.
- A3: COC cannot decide verification, classification, or accept/reject.
- A4: UA notes are internal; OC relays outcomes to students and companies.
- A5: PC-created opportunities require OC approval before going live (`config('connected.opportunity_requires_approval')`, default true).
- A6: Placement auto-withdraws a student's other open applications.
- A7: Allowed programs are config (`config('connected.allowed_programs')`, default `['BSIT']`). The text brief says "ICS Students"; confirm whether other ICS programs are in scope.
- A8: No email, so no email password reset. OC resets passwords; the user must change it at next login.
- A9: Weekly reports are uploaded documents plus a status flag only.
- A10: Student company suggestions are a simple form; OC converts them to Company Records.
- A11: Verification checklist is data, seeded with the five coordinator-named items.
- A12: Retention periods pending BSAU policy; nothing is purged.

## 12. Out of scope

Applicant ranking; email or SMS; in-app chat; attendance/DTR; payroll or allowance; performance evaluation; native mobile app; machine-learning recommendation; company self-registration; OAuth/SSO; two-factor authentication.
