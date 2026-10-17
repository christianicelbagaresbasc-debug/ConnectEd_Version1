# F-012 Opportunity Management & Partner Posting Approvals

| Field | Value |
|---|---|
| Feature ID | F-012 |
| Module | Opportunities Management |
| Status | Done |
| Phase | 5 |
| Requirements | FR-08, FR-09, FR-10, NFR-01, NFR-03, NFR-06, NFR-07 |
| Business rules | BR-15, BR-17, BR-20 |
| Roles with access | OC (full create, edit, approve, archive), PC (post opportunities for own company, requiring OC approval per A5), COC/UA (view all), Student (view open opportunities from verified partners only) |
| Owner (code) | `app/Http/Controllers/OpportunityController.php`, `app/Models/Opportunity.php`, `app/Models/OpportunitySkill.php`, `app/Policies/OpportunityPolicy.php`, `resources/views/opportunities/` |

## 1. Purpose
Empowers the OJT Coordinator (OC) and provisioned Partner Companies (PC) to publish and manage internship openings. Enforces Assumption **A5**: partner company postings require coordinator verification and approval before becoming visible to students. Provides multi-criteria search (title, company, location, work arrangement, skills, status) and tracks available intern slots versus total capacity. Serves as the supply side for the transparent student recommendation engine.

## 2. User flow
```mermaid
flowchart TD
    subgraph Partner Company Flow
        PC([Partner Company User]) --> CreateOpp[Navigate to /opportunities/create]
        CreateOpp --> FillDetails[Provide role title, duties, slots, arrangement, deadline, skills]
        FillDetails --> SubmitPC[Submit posting]
        SubmitPC --> StatusPending[Set status=pending_approval per Assumption A5]
        StatusPending --> AuditLogPC[Log audit: opportunity.created]
    end

    subgraph Coordinator Approval Flow
        OC([OJT Coordinator]) --> ReviewQueue[Open /opportunities with filter status=pending_approval]
        ReviewQueue --> InspectOpp[View opportunity details /opportunities/{id}]
        InspectOpp --> ApproveAction{Approve Posting?}
        ApproveAction -- Yes --> SubmitApprove[Post /opportunities/{id}/approve]
        SubmitApprove --> SetOpen[Update status=open, approved_by=OC, approved_at=now]
        SetOpen --> AuditLogApprove[Log audit: opportunity.approved]
        ApproveAction -- Edit Needed --> EditOpp[Update details or skill requirements]
    end

    subgraph Student Flow
        Student([ICS Student]) --> BrowseDirectory[Open /opportunities]
        BrowseDirectory --> ScopedQuery[Scoped query: only open opportunities from active verified existing partners]
        ScopedQuery --> FilterOpp[Filter by work arrangement, skill, location, company]
        FilterOpp --> ViewDetails[Read role duties, prerequisites, required & preferred skills]
    end
```

## 3. Screens
- `resources/views/opportunities/index.blade.php` (`/opportunities`):
  - Multi-criteria filter bar: keyword search, work arrangement (`onsite`, `remote`, `hybrid`), skill selector, partner organization, location, and publication status.
  - Responsive opportunity cards displaying host company, verified partner badge, work arrangement, available vs total slots, deadline badge, and preview chips of required/preferred skills.
  - Role-scoped: students see only open opportunities from verified partners; staff and companies see status-appropriate records.
- `resources/views/opportunities/create.blade.php` (`/opportunities/create`):
  - Form with company selector (dropdown for OC; locked for PC), role title, description, qualifications.
  - Logistics inputs: total slots, work arrangement, deadline, deployment location.
  - Interactive skill requirement selectors: Required Skills (2x weight) and Preferred Skills (1x weight).
- `resources/views/opportunities/show.blade.php` (`/opportunities/{opportunity}`):
  - Full opportunity profile with description, prerequisites, host company details, and logistics snapshot.
  - Clearly separated competency breakdown (Required Skills vs Preferred Skills).
  - Quick action toolbar: "Approve & Publish" (for OC when pending), "Edit", and "Archive".
- `resources/views/opportunities/edit.blade.php` (`/opportunities/{opportunity}/edit`):
  - Form to update role specifications, capacity slots, deadline, and re-sync competency requirements.

## 4. Routes
| Method | URI | Route name | Controller@action | Middleware / Policy |
|---|---|---|---|---|
| GET | `/opportunities` | `opportunities.index` | `OpportunityController@index` | `auth`, `verified`, `OpportunityPolicy@viewAny` |
| GET | `/opportunities/create` | `opportunities.create` | `OpportunityController@create` | `auth`, `verified`, `OpportunityPolicy@create` |
| POST | `/opportunities` | `opportunities.store` | `OpportunityController@store` | `auth`, `verified`, `OpportunityPolicy@create` |
| GET | `/opportunities/{opportunity}` | `opportunities.show` | `OpportunityController@show` | `auth`, `verified`, `OpportunityPolicy@view` |
| GET | `/opportunities/{opportunity}/edit` | `opportunities.edit` | `OpportunityController@edit` | `auth`, `verified`, `OpportunityPolicy@update` |
| PUT | `/opportunities/{opportunity}` | `opportunities.update` | `OpportunityController@update` | `auth`, `verified`, `OpportunityPolicy@update` |
| POST | `/opportunities/{opportunity}/approve` | `opportunities.approve` | `OpportunityController@approve` | `auth`, `verified`, `OpportunityPolicy@approve` |
| DELETE | `/opportunities/{opportunity}` | `opportunities.destroy` | `OpportunityController@destroy` | `auth`, `verified`, `OpportunityPolicy@delete` |

## 5. Data
- **`opportunities`**: Internship listings (`company_id`, `title`, `description`, `qualifications`, `slots_total`, `slots_filled`, `location`, `work_arrangement`: `onsite|remote|hybrid`, `deadline`, `status`: `draft|pending_approval|open|closed|archived`, `created_by`, `approved_by`, `approved_at`, `school_year`, timestamps, soft deletes).
- **`opportunity_skills`**: Competency requirements (`opportunity_id`, `skill_id`, `requirement_level`: `required|preferred`, timestamps, soft deletes).
  - Unique constraint on `(opportunity_id, skill_id)`.
- **`audit_logs`**: Logs `opportunity.created`, `opportunity.updated`, `opportunity.approved`, `opportunity.archived`.

## 6. Rules and validation
- **Assumption A5**: Opportunities posted by Partner Companies default to `pending_approval` status (`config('connected.opportunity_requires_approval', true)`).
- **Student Visibility Scoping**: Students can only access opportunities where `status == open`, deadline has not passed, available slots > 0, and the company has `classification == existing_partner` and `verification_status == verified`.
- Opportunities enforce soft delete; archived records are preserved for the school year.
- Required and preferred skills must exist in the active `skills` catalog.

## 7. Testing
- `tests/Feature/Opportunities/OpportunityManagementTest.php`:
  - `test_coordinator_can_view_opportunities_index`
  - `test_coordinator_can_create_and_publish_opportunity_with_skills`
  - `test_partner_company_posting_defaults_to_pending_approval_under_a5`
  - `test_coordinator_can_approve_pending_opportunity`
  - `test_student_can_only_view_open_opportunities_from_verified_existing_partners`
  - `test_coordinator_can_update_opportunity_and_sync_skills`
  - `test_coordinator_can_soft_delete_opportunity`
