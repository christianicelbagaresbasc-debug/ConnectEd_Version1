# Requirements Traceability Matrix

| Requirement ID | Description | Feature doc | Routes | Tests | Status |
|---|---|---|---|---|---|
| FR-01 | Account creation & role provisioning | [F-002](features/F-002-student-registration-and-approval.md), [F-003](features/F-003-staff-provisioning-and-password-management.md) | `/register`, `/coordinator/students/pending`, `/coordinator/staff` | `RegistrationTest`, `StudentRegistrationApprovalTest`, `StaffProvisioningTest` | Done |
| FR-02 | Authentication & role-based access | [F-001](features/F-001-authentication-and-app-shell.md), [F-003](features/F-003-staff-provisioning-and-password-management.md) | `/login`, `/logout`, `/password/forced-change` | `AuthenticationTest`, `ForcedPasswordChangeTest` | Done |
| FR-03 | Student profile, skills & certifications | F-011 | `/profile` | `ProfileTest` | In Progress |
| FR-04 | Controlled document upload & validation | [F-004](features/F-004-document-service-and-presigned-storage.md) | `/documents/uploads`, `/documents/{document}/download` | `DocumentAuthorizationMatrixTest`, `FileValidationTest` | Done |
| FR-05 | Company Records management | [F-006](features/F-006-company-records-management.md) | `/coordinator/companies` | `CompanyManagementTest` | Done |
| FR-06 | Company verification checklist | [F-007](features/F-007-company-verification-checklist.md) | `/coordinator/companies/{id}/verification` | `CompanyVerificationTest` | Done |
| FR-07 | Company directory (Existing vs New) | [F-006](features/F-006-company-records-management.md) | `/coordinator/companies`, `/student/companies` | `CompanyManagementTest`, `CompanySuggestionTest` | Done |
| FR-08 | Opportunity management | F-012 | `/opportunities` | Planned | Planned |
| FR-09 | Opportunity requirements & qualifications | F-012 | `/opportunities` | Planned | Planned |
| FR-10 | Opportunity search & multi-filter | F-013 | `/opportunities` | Planned | Planned |
| FR-11 | Transparent skill-based recommendations | F-013 | `/recommendations` | Planned | Planned |
| FR-12 | Multiple applications per student | F-014 | `/applications` | Planned | Planned |
| FR-13 | Formal application review by OC | F-014 | `/applications/{id}/review` | Planned | Planned |
| FR-14 | Student offer accept/decline | F-015 | `/applications/{id}/student-confirm` | Planned | Planned |
| FR-15 | Placement finalized on dual confirmation | F-015 | `/applications/{id}/oc-confirm` | Planned | Planned |
| FR-16 | Application & placement status tracking | F-016 | `/placements` | Planned | Planned |
| FR-17 | In-system notifications center | F-017 | `/notifications` | Planned | Planned |
| FR-18 | Monitoring views & dashboards | F-018 | `/dashboard` | Planned | Partial (Phase 1 Shell) |
| FR-19 | OC to COC task management | F-019 | `/tasks` | Planned | Planned |
| FR-20 | Student-to-COC cohort assignment | F-019 | `/students/{id}/assign-coc` | Planned | Planned |
| FR-21 | MOA tracking with custody history | [F-009](features/F-009-moa-tracking.md) | `/moas` | `MoaTrackingTest` | Done |
| FR-22 | UA legal notes on MOAs | [F-010](features/F-010-ua-legal-notes.md) | `/attorney/moas` | `MoaLegalReviewTest` | Done |
| FR-23 | Archiving of all records (soft delete) | [F-005](features/F-005-global-archive-and-audit-trail.md) | `/coordinator/archive` | `ArchiveRestoreTest` | Done |
| FR-24 | Student company suggestion intake | [F-008](features/F-008-student-company-suggestion-intake.md) | `/coordinator/company-suggestions`, `/student/company-suggestions` | `CompanySuggestionTest` | Done |
| FR-25 | Append-only audit log viewer | [F-005](features/F-005-global-archive-and-audit-trail.md) | `/coordinator/audit-logs` | `AuditLogTest` | Done |
| FR-26 | Bulk document upload with progress | [F-004](features/F-004-document-service-and-presigned-storage.md) | `/documents/uploads` | `FileValidationTest` | Done |
| FR-27 | Centralized document library | F-020 | `/document-library` | Planned | Planned |
| FR-28 | Single & streamed ZIP bundle download | F-020 | `/document-library/bundle` | Planned | Planned |
| FR-29 | Document versioning & templates | F-020 | `/document-library` | Planned | Planned |
| NFR-01 | Security & privacy hardening | [F-001](features/F-001-authentication-and-app-shell.md) | Global | `AuthenticationTest` | Ongoing |
| NFR-03 | Usability & clear navigation | [F-001](features/F-001-authentication-and-app-shell.md) | Global | `ThemePreferenceTest` | Done |
| NFR-07 | Mobile responsiveness & light/dark modes | [F-001](features/F-001-authentication-and-app-shell.md) | Global | `ThemePreferenceTest` | Done |
| BR-01 | No company self-registration | [F-006](features/F-006-company-records-management.md) | Global | `CompanyManagementTest` | Done |
| BR-02 | In-system notifications only | F-017 | Global | Planned | Planned |
| BR-15 | All records archived, never deleted | [F-001](features/F-001-authentication-and-app-shell.md), [F-005](features/F-005-global-archive-and-audit-trail.md) | Global | `ArchiveRestoreTest`, `CompanyManagementTest`, `MoaTrackingTest` | Done |
| BR-16 | Unconstrained MOA custody order | [F-009](features/F-009-moa-tracking.md) | `/moas/{id}/custody/transfer` | `MoaTrackingTest` | Done |
| BR-17 | OC-only promotion to Existing Partner | [F-006](features/F-006-company-records-management.md), [F-007](features/F-007-company-verification-checklist.md) | `/coordinator/companies/{id}/promote` | `CompanyPromotionRuleTest` | Done |
| BR-18 | UA legal notes internal only | [F-010](features/F-010-ua-legal-notes.md) | `/attorney/moas`, `/moas/{id}` | `MoaLegalReviewTest` | Done |
| BR-19 | Single active placement per student | F-015 | `/placements` | Planned | Planned |
| BR-20 | Recommendations visible to student only | F-013 | `/recommendations` | Planned | Planned |
