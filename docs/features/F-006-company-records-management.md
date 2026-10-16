# F-006 Company Records Management

| Field | Value |
|---|---|
| Feature ID | F-006 |
| Module | Company & Host Training Establishment (HTE) Management |
| Status | Done |
| Phase | 3 |
| Requirements | FR-04, FR-06, FR-16, NFR-01, NFR-03, NFR-07 |
| Business rules | BR-01, BR-02, BR-15, BR-17, BR-20 |
| Roles with access | OC (full CRUD & promotion), COC / UA (view all), PC (view & edit self), Student (view active verified & partners only) |
| Owner (code) | `app/Http/Controllers/Coordinator/CompanyController.php`, `app/Models/Company.php`, `app/Policies/CompanyPolicy.php` |

## 1. Purpose
Provides a centralized institutional directory of Host Training Establishments (HTEs). Companies never self-register (BR-01); records are created by the OJT Coordinator (OC) or converted from student suggestions. Tracks classification (`new_company` vs. `existing_partner`), verification status, academic curriculum fit (BSIT relevance), and associated partner accounts. Supports multi-criteria filtering, soft-deletion into the Archive Center (BR-15), and formal human promotion to Existing Partner (BR-17).

## 2. User flow
```mermaid
flowchart TD
    OC([OC visits /coordinator/companies]) --> ViewDirectory[Browse Company Directory with Multi-Filters: Search, Classification, Status, Active]
    ViewDirectory --> ActionChoice{Choose Action}
    
    ActionChoice -- Create Record --> OpenCreateForm[Open /coordinator/companies/create]
    OpenCreateForm --> InputDetails[Input Name, Industry, Contact, Address, BSIT Fit]
    InputDetails --> SubmitRecord[Submit Company Record]
    SubmitRecord --> AutoSeed[Auto-Seed 5 Active Verification Checklist Items in 'missing' state]
    AutoSeed --> LogAuditInit[Append Initial Verification Log & Audit Event]
    LogAuditInit --> ViewProfile[Redirect to Company Profile /coordinator/companies/{id}]
    
    ActionChoice -- Update Record --> EditProfile[Open /coordinator/companies/{id}/edit]
    EditProfile --> SaveUpdates[Save Metadata Changes & Log Audit Event]
    
    ActionChoice -- Promote to Existing Partner --> CheckEligibility{canBePromoted? New Company AND Verified?}
    CheckEligibility -- Yes --> PromoteConfirm[OC Confirms Formal Promotion Decision]
    PromoteConfirm --> UpdateClassification[Set classification=existing_partner, promoted_by=OC_id, promoted_at=now]
    UpdateClassification --> AuditPromote[Log company.promoted in audit_logs]
    CheckEligibility -- No --> BlockPromote[Promotion Locked: BR-17 Rule Enforced]
    
    ActionChoice -- Soft Delete --> ArchiveCompany[Soft Delete Company: Moves to Archive Center, Logs Audit]
```

## 3. Screens
- `resources/views/coordinator/companies/index.blade.php` (`/coordinator/companies`): Directory with LinkedIn-style cards, search input (name, industry, city), classification and status filter dropdowns, active switch, and checklist progress indicators.
- `resources/views/coordinator/companies/create.blade.php` (`/coordinator/companies/create`): Company registration form with institutional guidance banner explaining checklist auto-initialization.
- `resources/views/coordinator/companies/show.blade.php` (`/coordinator/companies/{company}`): Comprehensive LinkedIn-inspired profile card with hero banner, BR-17 promotion controls, curriculum alignment notes, linked partner accounts, and verification audit timeline.
- `resources/views/coordinator/companies/edit.blade.php` (`/coordinator/companies/{company}/edit`): Company metadata editing view.
- `resources/views/student/companies/index.blade.php` (`/student/companies`): Student-facing directory displaying only verified HTEs and active existing partners (BR-20).
- `resources/views/student/companies/show.blade.php` (`/student/companies/{company}`): Student-facing verified company profile.

## 4. Routes
| Method | URI | Route name | Controller@action | Middleware / Policy |
|---|---|---|---|---|
| GET | `/coordinator/companies` | `coordinator.companies.index` | `CompanyController@index` | `auth`, `verified`, `CompanyPolicy@viewAny` |
| GET | `/coordinator/companies/create` | `coordinator.companies.create` | `CompanyController@create` | `auth`, `verified`, `CompanyPolicy@create` |
| POST | `/coordinator/companies` | `coordinator.companies.store` | `CompanyController@store` | `auth`, `verified`, `CompanyPolicy@create` |
| GET | `/coordinator/companies/{company}` | `coordinator.companies.show` | `CompanyController@show` | `auth`, `verified`, `CompanyPolicy@view` |
| GET | `/coordinator/companies/{company}/edit` | `coordinator.companies.edit` | `CompanyController@edit` | `auth`, `verified`, `CompanyPolicy@update` |
| PUT | `/coordinator/companies/{company}` | `coordinator.companies.update` | `CompanyController@update` | `auth`, `verified`, `CompanyPolicy@update` |
| DELETE | `/coordinator/companies/{company}` | `coordinator.companies.destroy` | `CompanyController@destroy` | `auth`, `verified`, `CompanyPolicy@delete` |
| POST | `/coordinator/companies/{company}/promote` | `coordinator.companies.promote` | `CompanyController@promote` | `auth`, `verified`, `CompanyPolicy@promote` |
| GET | `/student/companies` | `student.companies.index` | `StudentCompanyController@index` | `auth`, `verified`, `CompanyPolicy@viewAny` |
| GET | `/student/companies/{company}` | `student.companies.show` | `StudentCompanyController@show` | `auth`, `verified`, `CompanyPolicy@view` |

## 5. Data
- **`companies`**: Primary HTE record (`name`, `classification`, `verification_status`, `is_active`, `industry`, `address`, `city`, `contact_person`, `contact_email`, `contact_phone`, `website`, `description`, `bsit_relevance_note`, `source`, `promoted_by`, `promoted_at`).
- **`company_verification_items`**: Auto-populated child records linking the company to active requirements.
- **`company_verification_logs`**: Append-only status transition log.
- **`audit_logs`**: Immutable audit records for `company.created`, `company.updated`, `company.archived`, and `company.promoted`.

## 6. Rules and validation
- Company name must be unique in `companies`.
- Soft-deletion only; physical hard deletion is prohibited (BR-15).
- A New Company can only become an Existing Partner when `verification_status` is `verified` and the promotion is executed by explicit OC human decision (BR-17).
- Students can only view companies that are active and either verified or existing partners (BR-20).

## 7. Testing
- `tests/Feature/Companies/CompanyManagementTest.php`:
  - `test_coordinator_can_view_companies_index_with_filters`
  - `test_coordinator_can_create_company_record_and_auto_populates_checklist`
  - `test_non_coordinator_cannot_create_company`
  - `test_company_name_must_be_unique`
  - `test_coordinator_can_update_company_profile`
  - `test_coordinator_can_soft_delete_company`
- `tests/Feature/Companies/CompanyPromotionRuleTest.php`:
  - `test_unverified_new_company_cannot_be_promoted_to_existing_partner`
  - `test_verified_new_company_can_be_promoted_by_coordinator`
  - `test_existing_partner_cannot_be_promoted_again`
  - `test_non_coordinator_cannot_promote_company`
