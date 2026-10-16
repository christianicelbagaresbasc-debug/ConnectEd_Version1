# F-008 Student Company Suggestion Intake & Coordinator Conversion

| Field | Value |
|---|---|
| Feature ID | F-008 |
| Module | Student Lifecycle & Company Outreach |
| Status | Done |
| Phase | 3 |
| Requirements | FR-04, FR-06, FR-16, NFR-01, NFR-03, NFR-07 |
| Business rules | BR-01, BR-02, BR-15, BR-17, BR-20 |
| Roles with access | Student (submit suggestion, view own suggestions), OC (view queue, convert to company, dismiss), COC (view queue) |
| Owner (code) | `app/Http/Controllers/Student/StudentCompanySuggestionController.php`, `app/Http/Controllers/Coordinator/CompanySuggestionController.php`, `app/Models/CompanySuggestion.php`, `app/Policies/CompanySuggestionPolicy.php` |

## 1. Purpose
Empowers BSIT students to recommend prospective Host Training Establishments (HTEs) where they wish to intern. The OJT Coordinator (OC) manages a dedicated intake queue to review student submissions, dismiss ineligible employers with feedback, or convert qualified suggestions into official `new_company` records. Conversion automatically initializes the 5-point verification checklist to commence legal accreditation.

## 2. User flow
```mermaid
flowchart TD
    Student([Student visits /student/company-suggestions/create]) --> FillForm[Enter Company Name, Contact Details, Reason / BSIT Fit]
    FillForm --> SubmitSuggestion[Submit Suggestion]
    SubmitSuggestion --> SaveSuggestion[Save company_suggestions status=submitted]
    SaveSuggestion --> AuditSubmit[Log company_suggestion.submitted in audit_logs]
    AuditSubmit --> StudentList[Redirect to /student/company-suggestions with confirmation]
    
    OC([OC visits /coordinator/company-suggestions]) --> ViewQueue[Filter & Browse Student Suggestions Queue]
    ViewQueue --> OCDecision{Coordinator Decision}
    
    OCDecision -- Convert to Company --> OpenConvertModal[Open Convert Modal with Pre-filled Company Name & Notes]
    OpenConvertModal --> ProvideMetadata[Add Industry, City, Address, Contact, BSIT Fit]
    ProvideMetadata --> ExecuteConversion[Execute Transaction: Create Company, Auto-seed 5 Checklist Items, Mark status=converted]
    ExecuteConversion --> AuditConvert[Log company_suggestion.converted & company.created in audit_logs]
    AuditConvert --> OpenCompany[Redirect to Company Profile /coordinator/companies/{id}]
    
    OCDecision -- Dismiss --> OpenDismissModal[Open Dismiss Modal]
    OpenDismissModal --> EnterReason[Provide Dismissal Reason e.g., Out of zone]
    EnterReason --> ExecuteDismiss[Mark status=dismissed, Append dismissal note]
    ExecuteDismiss --> AuditDismiss[Log company_suggestion.dismissed in audit_logs]
    
    StudentCheck([Student checks /student/company-suggestions]) --> ViewStatus[View updated status: Submitted, Converted, or Dismissed]
```

## 3. Screens
- `resources/views/student/suggestions/create.blade.php` (`/student/company-suggestions/create`): Student suggestion intake form with institutional guidance on how verification works.
- `resources/views/student/suggestions/index.blade.php` (`/student/company-suggestions`): Student's personal suggestion tracking dashboard with status badges and links to converted partner profiles.
- `resources/views/coordinator/suggestions/index.blade.php` (`/coordinator/company-suggestions`): Coordinator intake queue with search, status filtering, conversion modal, and dismissal dialog.

## 4. Routes
| Method | URI | Route name | Controller@action | Middleware / Policy |
|---|---|---|---|---|
| GET | `/student/company-suggestions` | `student.suggestions.index` | `StudentCompanySuggestionController@index` | `auth`, `verified`, `CompanySuggestionPolicy@viewAny` |
| GET | `/student/company-suggestions/create` | `student.suggestions.create` | `StudentCompanySuggestionController@create` | `auth`, `verified`, `CompanySuggestionPolicy@create` |
| POST | `/student/company-suggestions` | `student.suggestions.store` | `StudentCompanySuggestionController@store` | `auth`, `verified`, `CompanySuggestionPolicy@create` |
| GET | `/coordinator/company-suggestions` | `coordinator.suggestions.index` | `CompanySuggestionController@index` | `auth`, `verified`, `CompanySuggestionPolicy@viewAny` |
| POST | `/coordinator/company-suggestions/{suggestion}/convert` | `coordinator.suggestions.convert` | `CompanySuggestionController@convert` | `auth`, `verified`, `CompanySuggestionPolicy@handle` |
| POST | `/coordinator/company-suggestions/{suggestion}/dismiss` | `coordinator.suggestions.dismiss` | `CompanySuggestionController@dismiss` | `auth`, `verified`, `CompanySuggestionPolicy@handle` |

## 5. Data
- **`company_suggestions`**: Student suggestion submissions (`student_profile_id`, `company_name`, `contact_details`, `notes`, `status`, `company_id`, `handled_by`, `handled_at`).
- **`companies`**: Created upon suggestion conversion with `source = student_suggestion`.
- **`company_verification_items`**: Automatically populated upon conversion.
- **`audit_logs`**: Permanent event logging for `company_suggestion.submitted`, `company_suggestion.converted`, and `company_suggestion.dismissed`.

## 6. Rules and validation
- Only active authenticated students with an approved student profile can submit suggestions.
- Once a suggestion is converted or dismissed, it cannot be converted or dismissed again.
- Suggestion conversion creates a `new_company` with `pending` verification status; promotion to Existing Partner still requires full verification under Rule BR-17.

## 7. Testing
- `tests/Feature/Companies/CompanySuggestionTest.php`:
  - `test_student_can_submit_company_suggestion`
  - `test_student_can_view_own_suggestions`
  - `test_coordinator_can_view_suggestions_queue`
  - `test_coordinator_can_convert_suggestion_to_new_company`
  - `test_coordinator_can_dismiss_suggestion_with_reason`
  - `test_student_directory_only_shows_verified_or_existing_partners`
