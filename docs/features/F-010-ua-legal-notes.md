# F-010 University Attorney (UA) Legal Review Queue & Confidential Notes

| Field | Value |
|---|---|
| Feature ID | F-010 |
| Module | Memorandum of Agreement (MOA) Legal Counsel Review |
| Status | Done |
| Phase | 4 |
| Requirements | FR-22, NFR-01, NFR-03, NFR-07 |
| Business rules | BR-18 |
| Roles with access | UA (full review & clearance), OC / COC (internal staff view & comment); PC and Student strictly forbidden |
| Owner (code) | `app/Http/Controllers/Attorney/MoaLegalReviewController.php`, `app/Models/MoaNote.php`, `app/Policies/MoaPolicy.php`, `resources/views/attorney/index.blade.php` |

## 1. Purpose
Provides a dedicated legal review queue and advisory annotation workspace for the University Attorney (UA). Supports formal evaluation of partner agreements prior to university execution. Enforces Rule **BR-18 (Strict Confidentiality of Legal Notes)**: all remarks, revision directives, and risk level classifications (`high`, `medium`, `low`) logged by legal counsel or coordinators are strictly internal to university personnel (OC, COC, UA) and must never leak to Partner Company (PC) or Student views. Enables resolving individual revision directives and issuing formal legal clearances for agreement execution.

## 2. User flow
```mermaid
flowchart TD
    UA([University Attorney logs in]) --> OpenQueue[Open Legal Queue /attorney/moas]
    OpenQueue --> FilterQueue{Filter Queue}
    FilterQueue -- Pending / In Review --> ShowUnresolved[Display MOAs under_legal_review or with unresolved notes]
    FilterQueue -- All Contracts --> ShowAll[Display all institutional and student MOAs for legal audit]
    
    ShowUnresolved --> SelectMOA[Select MOA Record to Review]
    SelectMOA --> OpenWorkspace[Open MOA Workspace /moas/{id}]
    
    OpenWorkspace --> NoteAction{Legal Review Action}
    
    NoteAction -- Add Legal Remark --> SubmitRemark[Submit note: type=legal_remark, risk_level=high/medium/low, body]
    SubmitRemark --> SaveRemark[Save in moa_notes, append audit log moa_note.created]
    
    NoteAction -- Directive for Revision --> SubmitRevision[Submit note: type=revision, risk_level, directive details]
    SubmitRevision --> SaveRevision[Save in moa_notes as unresolved]
    
    NoteAction -- Mark Directive Resolved --> ResolveNote[Post /attorney/moas/{id}/notes/{noteId}/resolve]
    ResolveNote --> UpdateNote[Set resolved_at=now, resolved_by=UA_id, log audit]
    
    NoteAction -- Issue Formal Legal Clearance --> IssueClearance[Post /attorney/moas/{id}/clear: select target_status and remark]
    IssueClearance --> LogClearanceNote[Create clearance MoaNote & update MOA status to for_signing_school or for_signing_company]
```

## 3. Screens
- `resources/views/attorney/index.blade.php` (`/attorney/moas`): Dedicated legal review dashboard for the University Attorney, featuring:
  - Metric counters for pending reviews, active high-risk legal remarks, and total institutional contracts.
  - Queue toggle buttons (Default: Action Required vs. All MOAs).
  - Search bar across MOA numbers, company names, and agreement summaries.
  - Card list indicating latest document version, current custodian, unresolved revision count, and direct link to review workspace.
- `resources/views/moas/show.blade.php` (`/moas/{moa}`): Protected Internal Legal & Revision Notes section:
  - Guarded by `@can('viewInternalNotes', $moa)` ensuring complete invisibility to Student and Partner Company roles (BR-18).
  - Risk-level badges (`High Risk`, `Medium Risk`, `Low Risk`).
  - Resolution indicators showing timestamp and resolving attorney/coordinator.
  - Inline modal form to log legal remarks or revisions.
  - Attorney Formal Clearance modal triggering status transition upon approval.

## 4. Routes
| Method | URI | Route name | Controller@action | Middleware / Policy |
|---|---|---|---|---|
| GET | `/attorney/moas` | `attorney.moas.index` | `MoaLegalReviewController@index` | `auth`, `verified`, `MoaPolicy@viewLegalQueue` |
| POST | `/attorney/moas/{moa}/notes` | `attorney.moas.notes.store` | `MoaLegalReviewController@storeNote` | `auth`, `verified`, `MoaPolicy@addInternalNote` |
| POST / PATCH | `/attorney/moas/{moa}/notes/{note}/resolve` | `attorney.moas.notes.resolve` | `MoaLegalReviewController@resolveNote` | `auth`, `verified`, `MoaPolicy@resolveNote` |
| POST | `/attorney/moas/{moa}/clear` | `attorney.moas.clear` | `MoaLegalReviewController@clearReview` | `auth`, `verified`, `MoaPolicy@update` |

## 5. Data
- **`moa_notes`**: Internal remarks and revision directives (`moa_id`, `author_id`, `type`, `risk_level`, `body`, `resolved_at`, `resolved_by`, `created_at`, `updated_at`, `deleted_at`).
- **`audit_logs`**: Audit trail for `moa_note.created`, `moa_note.resolved`, and `moa.legal_cleared`.

## 6. Rules and validation
- **BR-18 (Confidentiality of Legal Notes):**
  - Legal remarks and risk evaluations are confidential internal work product of BSAU legal counsel and coordinators.
  - Server-side authorization in `MoaPolicy::viewInternalNotes` and `MoaPolicy::addInternalNote` restricts access to `oc`, `coc`, and `ua` users.
  - Responses rendered for `student` and `pc` users never include `moa_notes` content, risk badges, or legal review controls.
  - Non-university users attempting to post to `/attorney/moas/*` receive immediate HTTP 403 Forbidden responses.
- Risk levels are validated against `MoaRiskLevel` (`low`, `medium`, `high`).
- Legal review clearance requires a mandatory explanation of approval findings (min 5 characters).

## 7. Testing
- `tests/Feature/Moa/MoaLegalReviewTest.php`:
  - `test_attorney_can_view_legal_review_queue`
  - `test_non_legal_roles_cannot_access_attorney_queue`
  - `test_attorney_and_coordinator_can_store_internal_notes`
  - `test_confidentiality_of_legal_notes_br18`
  - `test_attorney_and_coordinator_can_resolve_notes`
  - `test_attorney_can_formally_clear_legal_review`
