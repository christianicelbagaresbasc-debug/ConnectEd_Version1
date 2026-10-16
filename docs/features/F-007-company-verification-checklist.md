# F-007 6-Point Company Verification Checklist

| Field | Value |
|---|---|
| Feature ID | F-007 |
| Module | Verification & Legal Compliance |
| Status | Done |
| Phase | 3 |
| Requirements | FR-04, FR-05, FR-16, NFR-01, NFR-03, NFR-07 |
| Business rules | BR-01, BR-02, BR-15, BR-17, BR-20 |
| Roles with access | OC (review items, update overall status), COC / UA (view only), PC (view self), Student (no direct access to checklist) |
| Owner (code) | `app/Http/Controllers/Coordinator/CompanyVerificationController.php`, `app/Models/CompanyVerificationItem.php`, `app/Models/CompanyVerificationLog.php`, `app/Models/VerificationRequirement.php` |

## 1. Purpose
Implements the BSAU ICS 6-point company verification checklist system. Evaluates statutory business registration (SEC/DTI), municipal licenses (Mayor's Permit), BIR compliance (Form 2303), internship training plans with mentorship capability, and workplace safety standards. Tracks item-level review states (`missing`, `submitted`, `accepted`, `rejected`), attaches uploaded supporting documents, computes completion percentages, and enforces an append-only verification audit log with required coordinator remarks for all status transitions.

## 2. User flow
```mermaid
flowchart TD
    OC([OC visits /coordinator/companies/{id}/verification]) --> ViewChecklist[Review 6-Point Checklist Items & Scorecard]
    ViewChecklist --> ActionChoice{Action}
    
    ActionChoice -- Review Item --> OpenItemReview[Click 'Review Item']
    OpenItemReview --> SetItemStatus[Select status: missing, submitted, accepted, rejected]
    SetItemStatus --> AddComplianceNote[Input Compliance Remarks & Attach Document]
    AddComplianceNote --> SaveItem[Save Item: reviewed_by=OC_id, reviewed_at=now]
    SaveItem --> AuditItemLog[Log company_verification_item.reviewed in audit_logs]
    AuditItemLog --> RefreshScorecard[Recalculate verificationProgressPercentage]
    
    ActionChoice -- Transition Overall Status --> SelectOverallStatus[Select new verification_status: pending, under_review, verified, rejected, revoked]
    SelectOverallStatus --> InputMandatoryRemark[Input Mandatory Audit Remark min 5 chars]
    InputMandatoryRemark --> SubmitDecision[Submit Overall Verification Decision]
    SubmitDecision --> DBTransaction[Database Transaction]
    DBTransaction --> UpdateCompanyStatus[Update company verification_status]
    DBTransaction --> AppendVerifLog[Insert immutable company_verification_logs row]
    DBTransaction --> AppendAuditLog[Insert company.verification_status_changed in audit_logs]
    
    ActionChoice -- Promote Verified Company --> CheckEligible{Eligible under BR-17?}
    CheckEligible -- Yes --> FormallyPromote[Promote to Existing Partner]
```

## 3. Screens
- `resources/views/coordinator/companies/verification.blade.php` (`/coordinator/companies/{company}/verification`): Dedicated checklist review workplace with overall decision form, compliance scorecard progress bar, item review expanders with document links, and append-only event log table.
- `resources/views/coordinator/companies/show.blade.php` (`/coordinator/companies/{company}`): Profile view showing checklist summary scorecard and audit trail.

## 4. Routes
| Method | URI | Route name | Controller@action | Middleware / Policy |
|---|---|---|---|---|
| GET | `/coordinator/companies/{company}/verification` | `coordinator.companies.verification` | `CompanyVerificationController@show` | `auth`, `verified`, `CompanyPolicy@view` |
| POST | `/coordinator/companies/{company}/verification/items/{item}` | `coordinator.companies.verification.items.update` | `CompanyVerificationController@updateItem` | `auth`, `verified`, `CompanyPolicy@verify` |
| POST | `/coordinator/companies/{company}/verification/status` | `coordinator.companies.verification.status.update` | `CompanyVerificationController@updateStatus` | `auth`, `verified`, `CompanyPolicy@verify` |

## 5. Data
- **`verification_requirements`**: Configurable statutory checklist definitions (`label`, `description`, `stage`, `is_mandatory`, `sort_order`, `is_active`).
- **`company_verification_items`**: Company checklist instances (`company_id`, `verification_requirement_id`, `document_id`, `status`, `remark`, `reviewed_by`, `reviewed_at`).
- **`company_verification_logs`**: Append-only log table protected against runtime updates and deletes (`company_id`, `from_status`, `to_status`, `remark`, `actor_id`, `created_at`).
- **`audit_logs`**: Permanent event logging for `company_verification_item.reviewed` and `company.verification_status_changed`.

## 6. Rules and validation
- Overall status update requires a non-empty `remark` with at least 5 characters.
- Only OJT Coordinators (OC) can verify checklist items and transition verification status (Institutional Rule A3: COC cannot decide verification).
- `company_verification_logs` records cannot be updated or deleted under any circumstance (enforced at model level).
- Completion percentage reflects the ratio of `accepted` items to total active requirements.

## 7. Testing
- `tests/Feature/Companies/CompanyVerificationTest.php`:
  - `test_coordinator_can_view_verification_checklist_page`
  - `test_coordinator_can_review_and_verify_checklist_item`
  - `test_coordinator_can_transition_overall_verification_status`
  - `test_overall_status_update_requires_a_minimum_remark`
  - `test_non_coordinator_cannot_update_verification`
