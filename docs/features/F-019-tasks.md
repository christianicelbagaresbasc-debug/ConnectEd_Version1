# F-019 Task Delegation & Student Assignment

| Field | Value |
|---|---|
| Feature ID | F-019 |
| Module | Coordinator / Tasks |
| Status | Done |
| Phase | 7 |
| Requirements | FR-19, NFR-01, NFR-03, NFR-04, NFR-06, NFR-07 |
| Business rules | BR-02, BR-15 |
| Roles with access | OC (Full CRUD, Delegate, Assign Cohorts), COC (View Assigned, Update Status) |
| Owner (code) | `app/Models/Task.php`, `app/Http/Controllers/Coordinator/TaskController.php`, `app/Http/Controllers/Coordinator/StudentAssignmentController.php`, `resources/views/tasks/index.blade.php`, `resources/views/coordinator/students/index.blade.php` |

## 1. Purpose
Provides institutional task delegation and student cohort assignment capabilities:
- **Task Delegation:** Enables the OJT Coordinator (OC) to create structured tasks, assign priorities (low, medium, high), associate context (student profile or company), set deadlines, and delegate them to Co-Coordinators (COCs) or self-assign. COCs can track and toggle statuses (To Do, In Progress, Done).
- **Student Cohort Assignment:** Enables the OC to assign individual students or bulk-assign entire program/year cohorts to specific COCs for focused monitoring and mentorship.

## 2. Delegation & Assignment Architecture
```mermaid
flowchart TD
    OC[OJT Coordinator] -->|Create Task| TaskStore[TaskController@store]
    TaskStore -->|Insert Task Record| DB[(tasks table)]
    TaskStore -->|In-System Notification| NotifyCOC[ConnectEdNotification via Database]
    TaskStore -->|Audit Trail| AuditLog[(audit_logs table)]
    
    COC[Co-Coordinator] -->|View Tasks /tasks| TaskIndex[TaskController@index]
    COC -->|Toggle Status| TaskStatus[TaskController@updateStatus]
    TaskStatus -->|Update status: done| DB
    
    OC -->|Bulk Assign Cohort| BulkAssign[StudentAssignmentController@bulkAssign]
    BulkAssign -->|Update assigned_coc_id| StudentProfiles[(student_profiles table)]
    BulkAssign -->|Audit & Notify| AuditLog
```

## 3. Screens
- `resources/views/tasks/index.blade.php`:
  - Priority badge styling and due date indicators (including "Due Soon" badge).
  - Status tabs: All, To Do, In Progress, Done.
  - Role-scoped filters: Priority, Assignee (for OC), Status.
  - Alpine.js modals for Task Creation and Task Editing.
  - Inline status toggle dropdown with quick completion.
- `resources/views/coordinator/students/index.blade.php`:
  - Student directory with "Assigned Co-Coordinator" column.
  - Multi-select checkboxes with sticky bulk assignment toolbar for assigning cohorts to a COC.
  - Inline single-student COC assignment modal.
  - Co-Coordinator filter dropdown to filter students by assigned mentor.

## 4. Routes
| Method | URI | Route name | Controller@action | Middleware / Policy |
|---|---|---|---|---|
| GET | `/tasks` | `tasks.index` | `TaskController@index` | `auth`, `can:viewAny,App\Models\Task` |
| POST | `/tasks` | `tasks.store` | `TaskController@store` | `auth`, `can:create,App\Models\Task` |
| PUT | `/tasks/{task}` | `tasks.update` | `TaskController@update` | `auth`, `can:update,task` |
| PATCH | `/tasks/{task}/status` | `tasks.status` | `TaskController@updateStatus` | `auth`, `can:updateStatus,task` |
| DELETE | `/tasks/{task}` | `tasks.destroy` | `TaskController@destroy` | `auth`, `can:delete,task` |
| POST | `/coordinator/students/{student}/assign-coc` | `coordinator.students.assign-coc` | `StudentAssignmentController@assign` | `auth`, role: OC |
| POST | `/coordinator/students/bulk-assign-coc` | `coordinator.students.bulk-assign-coc` | `StudentAssignmentController@bulkAssign` | `auth`, role: OC |

## 5. Data & Schema
- Table `tasks`:
  - `id` (bigint, PK)
  - `created_by` (foreign key to `users.id`)
  - `assigned_to` (foreign key to `users.id`)
  - `student_profile_id` (foreign key to `student_profiles.id`, nullable)
  - `company_id` (foreign key to `companies.id`, nullable)
  - `title` (string)
  - `description` (text, nullable)
  - `priority` (enum string: `low`, `medium`, `high`, default: `medium`)
  - `status` (enum string: `todo`, `in_progress`, `done`, default: `todo`)
  - `due_date` (date, nullable)
  - `completed_at` (timestamp, nullable)
  - `created_at`, `updated_at`, `deleted_at` (timestamps, soft deletes per BR-15)
- Table `student_profiles`:
  - `assigned_coc_id` (foreign key to `users.id`, nullable)

## 6. Rules & Validation
- **Role Restrictions:**
  - Only OC can create, edit, delete tasks, and assign students to COCs.
  - COC can view tasks assigned to them and update their status (To Do -> In Progress -> Done).
  - Students, Partner Companies, and University Attorneys have no access to the `/tasks` route or student assignment actions.
- **Rule BR-02 (In-System Notifications):**
  - When a task is delegated or a student cohort is assigned to a COC, an in-system database notification is dispatched to the assigned user.
- **Rule BR-15 (Soft Deletes & Audit):**
  - Tasks utilize soft deletes (`deleted_at`). Every creation, status update, deletion, and student assignment is recorded in the immutable audit log (`audit_logs`).

## 7. Testing
- `tests/Feature/Tasks/TaskManagementTest.php`:
  - `test_oc_can_create_and_delegate_task_to_coc`
  - `test_coc_can_view_assigned_tasks_and_update_status_to_done`
  - `test_student_cannot_access_or_create_tasks`
  - `test_oc_can_assign_single_student_to_coc`
  - `test_oc_can_bulk_assign_student_cohort_to_coc`
