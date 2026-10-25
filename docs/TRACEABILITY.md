# ConnectEd Requirements & Rules Traceability Matrix

| Requirement / Rule | Description | Feature ID | Models / Tables | Primary Routes | Automated Feature Test Suite |
|---|---|---|---|---|---|
| **FR-01** | Multi-Role Authentication & App Shell | F-001 | `User` (`users`), `PasswordReset` | `/login`, `/logout`, `/password/*` | `tests/Feature/Auth/*`, `tests/Feature/ThemePreferenceTest.php` |
| **FR-02** | Student Self-Registration & Approval | F-002 | `User`, `StudentProfile` (`student_profiles`) | `/register`, `/coordinator/students/pending`, `/coordinator/students/{id}/approve` | `tests/Feature/Auth/RegistrationApprovalTest.php` |
| **FR-03** | IT Skills Catalog & Student Skill Profile | F-011 | `Skill`, `StudentSkill`, `StudentCertification` | `/coordinator/skills`, `/student/profile/skills` | `tests/Feature/Skills/SkillManagementTest.php`, `tests/Feature/Student/StudentSkillProfileTest.php` |
| **FR-04** | Company Records & 6-Point Verification Checklist | F-006, F-007 | `Company` (`companies`), `CompanyVerificationItem` | `/coordinator/companies`, `/coordinator/companies/{id}/verification` | `tests/Feature/Companies/CompanyManagementTest.php`, `tests/Feature/Companies/CompanyVerificationTest.php` |
| **FR-05** | Configurable Verification Checklist Items | F-007 | `CompanyVerificationItem` | `/coordinator/companies/{id}/verification/items/{itemId}` | `tests/Feature/Companies/CompanyVerificationTest.php` |
| **FR-06** | Student Company Suggestion Intake | F-008 | `CompanySuggestion` (`company_suggestions`) | `/student/company-suggestions`, `/coordinator/company-suggestions` | `tests/Feature/Companies/CompanySuggestionTest.php` |
| **FR-07** | Company Partner Accounts Provisioning | F-003 | `PartnerAccount` (`partner_accounts`), `User` | `/coordinator/staff`, `/coordinator/companies/{id}/account` | `tests/Feature/Auth/StaffProvisioningTest.php` |
| **FR-08** | Opportunity Management & Directory | F-012 | `Opportunity` (`opportunities`), `OpportunitySkill` | `/opportunities`, `/opportunities/create`, `/opportunities/{id}` | `tests/Feature/Opportunities/OpportunityManagementTest.php` |
| **FR-09** | Coordinator Review & Opportunity Approval | F-012 | `Opportunity` | `/opportunities/{id}/approve` | `tests/Feature/Opportunities/OpportunityManagementTest.php` |
| **FR-10** | Student Opportunity Directory Browsing | F-012 | `Opportunity`, `Company` | `/opportunities` | `tests/Feature/Opportunities/OpportunityManagementTest.php` |
| **FR-11** | Transparent Skill-Based Recommendation Engine | F-013 | `SkillMatcher` service, `Opportunity`, `StudentSkill` | `/student/recommendations` | `tests/Feature/Student/RecommendationTest.php` |
| **FR-12** | Student Internship Application Workflow | F-014 | `Application` (`applications`) | `/student/opportunities/{id}/apply`, `/student/applications` | `tests/Feature/Applications/ApplicationWorkflowTest.php` |
| **FR-13** | Company Applicant Review & Shortlisting | F-014 | `Application` | `/company/opportunities/{id}/applications`, `/company/applications/{id}/status` | `tests/Feature/Applications/ApplicationWorkflowTest.php` |
| **FR-14** | Dual-Confirmation Offer Finalization | F-015 | `Application`, `Placement` (`placements`) | `/student/applications/{id}/confirm`, `/coordinator/applications/{id}/confirm` | `tests/Feature/Placements/PlacementConfirmationTest.php` |
| **FR-15** | Placement Authorization & Lifecycle Progression | F-015 | `Placement` | `/coordinator/placements/{id}/authorize`, `/coordinator/placements/{id}/status` | `tests/Feature/Placements/PlacementConfirmationTest.php` |
| **FR-16** | Placement Monitoring & Weekly Narrative Reports | F-016 | `WeeklyReport` (`weekly_reports`), `Document` | `/student/placements/{id}/reports`, `/coordinator/weekly-reports/{id}/review` | `tests/Feature/Placements/WeeklyReportTest.php` |
| **FR-17** | In-System Notification Center | F-017 | `Notification` (`notifications`) | `/notifications`, `/notifications/{id}/read`, `/notifications/read-all` | `tests/Feature/Notifications/NotificationCenterTest.php` |
| **FR-18** | Role Landing Dashboards & Operational Hubs | F-018 | `DashboardController`, all models | `/dashboard` | `tests/Feature/Dashboards/RoleDashboardTest.php` |
| **FR-19** | Coordinator Task Delegation & Student Assignment | F-019 | `Task` (`tasks`), `StudentProfile` | `/tasks`, `/coordinator/students/{id}/assign-coc`, `/coordinator/students/bulk-assign-coc` | `tests/Feature/Tasks/TaskManagementTest.php` |
| **FR-20** | Unified Document Library & Compliance Matrix | F-020 | `Document` (`documents`), `DocumentService` | `/document-library`, `/document-library/completeness`, `/document-library/bundle` | `tests/Feature/Documents/DocumentLibraryTest.php` |
| **FR-21** | Multi-Party MOA Tracking & Custody Transfers | F-009 | `Moa` (`moas`), `MoaTransfer` (`moa_transfers`) | `/moas`, `/moas/{id}/custody/transfer` | `tests/Feature/Moa/MoaTrackingTest.php` |
| **FR-22** | University Attorney Legal Review & Notes | F-010 | `Moa`, `MoaNote` (`moa_notes`), `MoaVersion` | `/attorney/moas`, `/moas/{id}/notes`, `/moas/{id}/clear` | `tests/Feature/Moa/MoaLegalReviewTest.php` |
| **FR-23** | Global Soft-Delete Archive & Restoration | F-005 | `Archivable` trait, all soft-deletable models | `/coordinator/archive`, `/coordinator/archive/{model}/{id}/restore` | `tests/Feature/System/GlobalArchiveTest.php` |
| **FR-24** | Institutional OJT Reports & Streaming CSV Exports | F-021 | `ReportController` | `/reports`, `/reports/placements/csv`, `/reports/companies/csv`, `/reports/skills/csv` | `tests/Feature/Reports/ReportExportTest.php` |
| **FR-25** | Immutable System Audit Logging | F-005 | `AuditLog` (`audit_logs`), `AuditService` | `/coordinator/audit-logs` | `tests/Feature/System/AuditLoggingTest.php` |
| **FR-26** | Secure Direct-to-Bucket Document Uploads | F-004 | `Document`, `DocumentService` | `/documents/uploads`, `/documents/{id}/download` | `tests/Feature/Documents/DocumentAuthorizationMatrixTest.php` |
| **FR-27** | File Integrity & Magic Byte Validation | F-004 | `Document`, `DocumentValidationService` | `/documents/uploads` | `tests/Feature/Documents/FileValidationTest.php` |
| **FR-28** | Batch Document ZIP Container Streaming | F-020 | `DocumentLibraryController` | `/document-library/bundle` | `tests/Feature/Documents/DocumentLibraryTest.php` |
| **FR-29** | Forced Password Change & Security Session Hardening | F-001, F-003 | `EnsurePasswordIsChanged` middleware | `/password/change`, `SecurityHeaders` middleware | `tests/Feature/Auth/PasswordManagementTest.php`, `tests/Feature/Security/SecurityHardeningTest.php` |

---

## Business Rules Traceability

| Rule ID | Description | Enforcing Component | Automated Verification Test |
|---|---|---|---|
| **BR-01** | Companies never self-register; created by OC | `CompanyController@store`, `CompanyPolicy` | `tests/Feature/Companies/CompanyManagementTest.php` |
| **BR-02** | In-system database notifications only (no email/SMS) | `ConnectEdNotification::via() => ['database']` | `tests/Feature/Notifications/NotificationCenterTest.php` |
| **BR-03** | University Attorney acts on MOAs only in `under_legal_review` | `MoaLegalReviewController`, `MoaPolicy` | `tests/Feature/Moa/MoaLegalReviewTest.php` |
| **BR-04** | Students only browse open opportunities from verified partners | `OpportunityController@index`, query scope | `tests/Feature/Opportunities/OpportunityManagementTest.php` |
| **BR-05** | No outbound external mail or chat subsystems | Architectural constraint & `NotificationCenterTest` | `tests/Feature/Notifications/NotificationCenterTest.php` |
| **BR-06** | Student skill suggestions require OC approval | `StudentSkillController@suggest`, `SkillController@approve` | `tests/Feature/Skills/SkillManagementTest.php` |
| **BR-07** | Company suggests entered by student require OC approval | `StudentCompanySuggestionController@store` | `tests/Feature/Companies/CompanySuggestionTest.php` |
| **BR-08** | Company promotion to Existing Partner requires verified status & executed MOA | `CompanyController@promote`, `CompanyPolicy` | `tests/Feature/Companies/CompanyManagementTest.php` |
| **BR-09** | Unplaced students without company highlighted on OC dashboard | `DashboardController@ocDashboard` unplaced query | `tests/Feature/Dashboards/RoleDashboardTest.php` |
| **BR-10** | Opportunity capacity automatically decrements and closes when filled | `PlacementConfirmationService` | `tests/Feature/Placements/PlacementConfirmationTest.php` |
| **BR-11** | Dual confirmation required to establish active placement | `StudentApplicationController@confirm`, `PlacementConfirmationService` | `tests/Feature/Placements/PlacementConfirmationTest.php` |
| **BR-12** | Automatic withdrawal of competing pending applications upon placement | `PlacementConfirmationService::finalizePlacement` | `tests/Feature/Placements/PlacementConfirmationTest.php` |
| **BR-13** | Weekly report upload requires ongoing/active placement | `StudentWeeklyReportController@store`, `WeeklyReportPolicy` | `tests/Feature/Placements/WeeklyReportTest.php` |
| **BR-14** | Reviewing weekly reports requires formal approval or revision remarks | `WeeklyReportReviewController@review` | `tests/Feature/Placements/WeeklyReportTest.php` |
| **BR-15** | Nothing is hard deleted; soft deletes and audit logging everywhere | `SoftDeletes` on models, `AuditService` | `tests/Feature/System/GlobalArchiveTest.php`, `tests/Feature/System/AuditLoggingTest.php` |
| **BR-16** | Unconstrained MOA custody transfer order | `MoaCustodyController@transfer` | `tests/Feature/Moa/MoaTrackingTest.php` |
| **BR-17** | 6-Point statutory verification items check before partner accreditation | `CompanyVerificationController` | `tests/Feature/Companies/CompanyVerificationTest.php` |
| **BR-18** | Confidentiality of University Attorney legal notes | `MoaNotePolicy`, `MoaLegalReviewController` | `tests/Feature/Moa/MoaLegalReviewTest.php` |
| **BR-19** | Strict single active placement concurrency per student | `has_active_placement` atomic guard, `PlacementConfirmationService` | `tests/Feature/Placements/PlacementConfirmationTest.php` |
| **BR-20** | Match scores strictly visible only to the student; no ranking of students | `RecommendationController`, `OpportunityController`, `ReportController` | `tests/Feature/Student/RecommendationTest.php`, `tests/Feature/Dashboards/RoleDashboardTest.php` |
