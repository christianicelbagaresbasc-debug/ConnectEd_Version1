# ConnectEd Feature Master Index

| Feature ID | Name | Module | Roles | FR/BR | Phase | Status | Doc link | Primary route |
|---|---|---|---|---|---|---|---|---|
| F-001 | Authentication & App Shell | Identity & Shell | OC, COC, UA, PC, Student | FR-01, FR-02, BR-01, BR-02, BR-15 | 1 | Done | [F-001](features/F-001-authentication-and-app-shell.md) | `/login` |
| F-002 | Student Registration & Approval | Identity & Accounts | OC, Student | FR-01, FR-02, BR-01 | 2 | Done | [F-002](features/F-002-student-registration-and-approval.md) | `/coordinator/students/pending` |
| F-003 | Staff Provisioning & Password Resets | Identity & Accounts | OC, COC, UA, PC | FR-01, FR-02 | 2 | Done | [F-003](features/F-003-staff-provisioning-and-password-management.md) | `/coordinator/staff` |
| F-004 | Document Upload & Presigned Storage | Documents & Storage | All Roles | FR-04, FR-26, FR-28 | 2 | Done | [F-004](features/F-004-document-service-and-presigned-storage.md) | `/documents/uploads` |
| F-005 | Global Archive & Audit Trail | System & Compliance | OC | FR-23, FR-25, BR-15 | 2 | Done | [F-005](features/F-005-global-archive-and-audit-trail.md) | `/coordinator/audit-logs` |
| F-006 | Company Records Management | Companies | OC, COC, PC, Student | FR-04, FR-06, FR-16, BR-01, BR-17, BR-20 | 3 | Done | [F-006](features/F-006-company-records-management.md) | `/coordinator/companies` |
| F-007 | Company Verification Checklist | Companies | OC, COC, UA, PC | FR-04, FR-05, BR-17 | 3 | Done | [F-007](features/F-007-company-verification-checklist.md) | `/coordinator/companies/{id}/verification` |
| F-008 | Student Company Suggestion Intake | Companies | Student, OC, COC | FR-04, FR-06, BR-01 | 3 | Done | [F-008](features/F-008-student-company-suggestion-intake.md) | `/coordinator/company-suggestions` |
| F-009 | Multi-Party MOA Tracking | MOA | OC, COC, UA, PC, Student | FR-21, BR-16 | 4 | Planned | [F-009](features/F-009-moa-tracking.md) | `/moas` |
| F-010 | UA Legal Review & Notes | MOA | UA, OC, COC | FR-22, BR-18 | 4 | Planned | [F-010](features/F-010-ua-legal-notes.md) | `/moas/{id}/notes` |
| F-011 | IT Skills Catalog & Student Profile | Skills | OC, Student | FR-03 | 5 | Planned | [F-011](features/F-011-skills-catalog.md) | `/profile/skills` |
| F-012 | Opportunity Management & Approval | Opportunities | OC, PC | FR-08, FR-09 | 5 | Planned | [F-012](features/F-012-opportunity-management.md) | `/opportunities` |
| F-013 | Skill Matching Recommendation Engine | Opportunities | Student | FR-10, FR-11, BR-20 | 5 | Planned | [F-013](features/F-013-matching-engine.md) | `/recommendations` |
| F-014 | Application Workflow & OC Decisions | Applications | Student, OC, PC | FR-12, FR-13 | 6 | Planned | [F-014](features/F-014-applications.md) | `/applications` |
| F-015 | Student Offer Confirmation & Placement | Placements | Student, OC | FR-14, FR-15, BR-19 | 6 | Planned | [F-015](features/F-015-placements.md) | `/placements` |
| F-016 | Placement Monitoring & Weekly Reports | Placements | OC, COC, Student | FR-16 | 6 | Planned | [F-016](features/F-016-placement-monitoring.md) | `/placements/{id}` |
| F-017 | In-System Notification Center | Notifications | All Roles | FR-17 | 6 | Planned | [F-017](features/F-017-notifications.md) | `/notifications` |
| F-018 | Role Landing Dashboards & Widgets | Dashboards | All Roles | FR-18 | 7 | Planned | [F-018](features/F-018-dashboards.md) | `/dashboard` |
| F-019 | Task Delegation & Student Assignment | Work Management | OC, COC | FR-19, FR-20 | 7 | Planned | [F-019](features/F-019-tasks.md) | `/tasks` |
| F-020 | Document Library & Bundle Downloads | Documents | All Roles | FR-27, FR-28, FR-29 | 7 | Planned | [F-020](features/F-020-document-library.md) | `/document-library` |
