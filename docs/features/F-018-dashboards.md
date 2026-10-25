# F-018 Role Landing Dashboards

| Field | Value |
|---|---|
| Feature ID | F-018 |
| Module | Dashboards |
| Status | Done |
| Phase | 7 |
| Requirements | FR-18, NFR-01, NFR-03, NFR-04, NFR-06, NFR-07 |
| Business rules | BR-02, BR-19, BR-20 |
| Roles with access | OC, COC, UA, PC, Student |
| Owner (code) | `app/Http/Controllers/DashboardController.php`, `resources/views/dashboard/*.blade.php` |

## 1. Purpose
Provides tailored, role-specific operational dashboards upon login (`/dashboard`):
- **OJT Coordinator (OC):** Primary operational hub featuring "Students Without a Company" widget, pending verifications, MOA lifecycle breakdown, pending applications, active placement count, task overview, audit log stream, and a scoped out-of-scope attendance tracking card.
- **Co-OJT Coordinator (COC):** Supervised cohort management, pending weekly report reviews, assigned task checklist with inline status toggles, and student-specific MOAs.
- **University Attorney (UA):** Legal vetting console displaying agreements queued under legal review, notes history, executed agreements, and quick access to legal clearances.
- **Partner Company (PC):** Host company portal with open opportunities, intern slots, active intern roster, and applicant submissions ordered chronologically (strictly without match scores or rankings per BR-20).
- **ICS Student:** Personal intern hub featuring placement status, profile readiness meter, top skill recommendations (scores strictly private per BR-20), and application pipeline.

## 2. Architecture & Role Dispatch
```mermaid
flowchart TD
    UserLogin([Authenticated User]) --> DashboardRoute[GET /dashboard]
    DashboardRoute --> Controller[DashboardController@index]
    Controller --> Dispatch{User Role?}
    Dispatch -->|OC| OCDash[ocDashboard -> resources/views/dashboard/oc.blade.php]
    Dispatch -->|COC| COCDash[cocDashboard -> resources/views/dashboard/coc.blade.php]
    Dispatch -->|UA| UADash[uaDashboard -> resources/views/dashboard/ua.blade.php]
    Dispatch -->|PC| PCDash[pcDashboard -> resources/views/dashboard/pc.blade.php]
    Dispatch -->|STUDENT| StudDash[studentDashboard -> resources/views/dashboard/student.blade.php]
```

## 3. Screens
- `resources/views/dashboard/oc.blade.php`:
  - Operational metrics cards: Total Students, Placed %, Unplaced Students, Pending Registrations, Pending Verifications, Active Placements.
  - "Students Without a Company" primary operational table with program and search filters and inline COC delegation.
  - Institutional MOA pipeline breakdown bar (Draft, Under Review, Legal Review, Signing, Executed).
  - Tasks summary and urgent delegation feed.
  - Attendance & DTR placeholder card with explicit "Not in current scope" badge.
  - Recent audit events stream.
- `resources/views/dashboard/coc.blade.php`:
  - Supervised cohort count cards and student list.
  - Assigned task checklist with inline status toggle buttons (To Do, In Progress, Done).
  - Pending weekly reports review queue.
- `resources/views/dashboard/ua.blade.php`:
  - Legal review queue with custodian party indicators and note counts.
  - Agreement execution metrics and quick links to review screens.
- `resources/views/dashboard/pc.blade.php`:
  - Partner enterprise portal banner with company accreditation status.
  - Opportunity openings and allocated intern slots.
  - Applicant review table ordered chronologically (strictly adhering to Rule BR-20).
  - Active intern roster.
- `resources/views/dashboard/student.blade.php`:
  - Placement status banner (or Seeking Placement notice).
  - Profile readiness & completeness meter (0-100%).
  - Top skill-match recommendations with matched competencies and private match scores.
  - My active applications pipeline with status indicators.

## 4. Routes
| Method | URI | Route name | Controller@action | Middleware / Policy |
|---|---|---|---|---|
| GET | `/dashboard` | `dashboard` | `DashboardController@index` | `auth`, `verified` |

## 5. Rules & Business Logic
- **Rule BR-20 (Matching Visibility & Non-Ranking):**
  - Partner companies and coordinators never see match scores or ranked student candidate lists. On the PC dashboard, applicants are strictly ordered chronologically by submission timestamp.
  - Match scores are displayed only to the student concerned on their own dashboard.
- **Rule BR-19 (Single Active Placement):**
  - If a student has an active placement confirmed, the dashboard displays their host company and placement hub, hiding seeking-placement callouts.
- **Scope Boundary Note:**
  - Daily Time Record (DTR) and attendance tracking are explicitly flagged as out-of-scope for the capstone system, reflected by a read-only explanatory card on the OC dashboard.

## 6. Testing
- `tests/Feature/Dashboards/RoleDashboardTest.php`:
  - `test_unauthenticated_user_is_redirected_to_login`
  - `test_oc_dashboard_renders_unplaced_students_widget_and_attendance_scope_card`
  - `test_coc_dashboard_displays_assigned_students_and_tasks`
  - `test_ua_dashboard_displays_moas_under_legal_review`
  - `test_pc_dashboard_displays_company_portal_without_student_rankings`
  - `test_student_dashboard_displays_profile_completeness_and_placement_or_recommendations`
