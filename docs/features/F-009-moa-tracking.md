# F-009 Multi-Party MOA Tracking & Custody Management

| Field | Value |
|---|---|
| Feature ID | F-009 |
| Module | Memorandum of Agreement (MOA) Management |
| Status | Done |
| Phase | 4 |
| Requirements | FR-21, FR-04, FR-23, NFR-01, NFR-03, NFR-07 |
| Business rules | BR-15, BR-16 |
| Roles with access | OC (full CRUD & transfer), COC (view & transfer), UA (view & transfer), PC (view self & transfer custody), Student (view own student MOA / active institutional MOA & transfer custody) |
| Owner (code) | `app/Http/Controllers/MoaController.php`, `app/Http/Controllers/MoaCustodyController.php`, `app/Http/Controllers/MoaVersionController.php`, `app/Models/Moa.php`, `app/Models/MoaTransfer.php`, `app/Models/MoaVersion.php`, `app/Policies/MoaPolicy.php` |

## 1. Purpose
Provides an institutional tracking and document versioning system for Memoranda of Agreement (MOAs). Accommodates both university-wide partnership agreements (`university_company`) and student-specific training agreements (`student_company`). Enforces Rule **BR-16 (Unconstrained Custody Order)**, allowing physical or digital custody of the contract to transfer between any authorized party (`oc`, `coc`, `ua`, `student`, `company`) in whatever sequence real-world paperwork demands. Maintains an immutable, append-only custody timeline (`moa_transfers`) with runtime modification locks, versioned file attachments (`moa_versions`), and soft-deletion lifecycle archiving (BR-15).

## 2. User flow
```mermaid
flowchart TD
    OC([OC drafts new MOA /moas/create]) --> SelectDetails[Select Company, Agreement Type, Dates, Summary]
    SelectDetails --> SetInitialCustody[Assign Initial Custody Party: OC, COC, UA, Student, or Company]
    SetInitialCustody --> SaveMOA[Save MOA Record]
    SaveMOA --> AppendInitTransfer[Log Initial Custody Transfer in moa_transfers]
    AppendInitTransfer --> ViewShow[Open MOA Workspace /moas/{id}]
    
    ViewShow --> CustodyChoice{Custody Action}
    CustodyChoice -- Transfer Custody --> ModalTransfer[Open Transfer Modal: select to_party, user, & note]
    ModalTransfer --> SaveTransfer[Update MOA current_custodian, append immutable moa_transfers row, log audit]
    
    CustodyChoice -- Attach Document Version --> ModalVersion[Select Document & enter version note]
    ModalVersion --> SaveVersion[Increment version_no in moa_versions, set as latestVersion, log audit]
    
    CustodyChoice -- Update Lifecycle Status --> ModalStatus[Select status: draft, under_legal_review, for_signing, notarized, active, expired]
    ModalStatus --> SaveStatus[Update status & log audit]
    
    CustodyChoice -- Soft Delete --> ArchiveMOA[Soft delete MOA: moves to Archive Center, logs audit]
```

## 3. Screens
- `resources/views/moas/index.blade.php` (`/moas`): Centralized MOA directory with LinkedIn-style cards, multi-filters (search by MOA number, company name, summary; status filter; agreement type; custodian party filter), status badges, and custody indicators.
- `resources/views/moas/create.blade.php` (`/moas/create`): Agreement drafting form with dynamic Alpine.js student intern selector for `student_company` contracts and initial custody assignment.
- `resources/views/moas/show.blade.php` (`/moas/show/{moa}`): Full MOA workspace featuring:
  - Header with status badge, custodian highlight, effective dates, and breadcrumbs.
  - Interactive Action Bar with Quick Modals: Custody Transfer (BR-16), Version Attachment, and Status Transition.
  - Immutable Custody Audit Trail timeline with actor badges and notes.
  - Document Version History with download links and uploader metadata.
  - Confidential Legal Notes panel (visible only to OC, COC, UA per BR-18).
- `resources/views/moas/edit.blade.php` (`/moas/{moa}/edit`): Agreement metadata and validity period update form.

## 4. Routes
| Method | URI | Route name | Controller@action | Middleware / Policy |
|---|---|---|---|---|
| GET | `/moas` | `moas.index` | `MoaController@index` | `auth`, `verified`, `MoaPolicy@viewAny` |
| GET | `/moas/create` | `moas.create` | `MoaController@create` | `auth`, `verified`, `MoaPolicy@create` |
| POST | `/moas` | `moas.store` | `MoaController@store` | `auth`, `verified`, `MoaPolicy@create` |
| GET | `/moas/{moa}` | `moas.show` | `MoaController@show` | `auth`, `verified`, `MoaPolicy@view` |
| GET | `/moas/{moa}/edit` | `moas.edit` | `MoaController@edit` | `auth`, `verified`, `MoaPolicy@update` |
| PUT | `/moas/{moa}` | `moas.update` | `MoaController@update` | `auth`, `verified`, `MoaPolicy@update` |
| DELETE | `/moas/{moa}` | `moas.destroy` | `MoaController@destroy` | `auth`, `verified`, `MoaPolicy@delete` |
| POST | `/moas/{moa}/status` | `moas.status.update` | `MoaController@updateStatus` | `auth`, `verified`, `MoaPolicy@update` |
| POST | `/moas/{moa}/custody/transfer` | `moas.custody.transfer` | `MoaCustodyController@transfer` | `auth`, `verified`, `MoaPolicy@transferCustody` |
| POST | `/moas/{moa}/versions` | `moas.versions.store` | `MoaVersionController@store` | `auth`, `verified`, `MoaPolicy@update` |

## 5. Data
- **`moas`**: Core agreement record (`company_id`, `student_profile_id`, `moa_number`, `type`, `status`, `current_custodian_party`, `current_custodian_user_id`, `effective_date`, `expiry_date`, `summary`, `school_year`, `created_by`).
- **`moa_versions`**: Document version link (`moa_id`, `version_no`, `document_id`, `uploaded_by`, `note`).
- **`moa_transfers`**: Strictly append-only custody transfer log (`moa_id`, `from_party`, `to_party`, `from_user_id`, `to_user_id`, `actor_id`, `note`, `created_at`). No soft-deletes; locked against modifications at runtime.
- **`audit_logs`**: Permanent audit entries for `moa.created`, `moa.updated`, `moa.archived`, `moa.custody_transferred`, `moa.version_uploaded`, and `moa.status_changed`.

## 6. Rules and validation
- **BR-16 (Unconstrained Custody Order):** Physical/digital custody may be assigned or transferred to any party (`oc`, `coc`, `ua`, `student`, `company`) in any order determined by humans. The system records the transfer without enforcing an artificial linear workflow.
- **BR-15 (Archival Integrity):** MOA records use soft deletes; physical deletion is prohibited. Custody transfer records (`moa_transfers`) are permanent and append-only; Eloquent model event listeners prevent `updating` and `deleting` with a `\RuntimeException`.
- Unique constraint enforced on `moa_number`.
- Validity date verification requires `expiry_date` to be on or after `effective_date`.

## 7. Testing
- `tests/Feature/Moa/MoaTrackingTest.php`:
  - `test_coordinator_can_view_moas_index_with_filters`
  - `test_coordinator_can_create_moa_with_initial_custody`
  - `test_moa_number_must_be_unique`
  - `test_unconstrained_custody_transfer_flow_br16`
  - `test_custody_transfer_cannot_be_deleted`
  - `test_uploading_new_document_version_increments_version_number`
  - `test_coordinator_can_update_moa_metadata`
  - `test_coordinator_can_soft_delete_moa`
  - `test_student_and_non_coordinator_authorization_rules`
