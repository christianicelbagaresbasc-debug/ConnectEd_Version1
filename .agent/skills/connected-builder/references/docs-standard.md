# Documentation Standard

Goal: every feature documented in one identical format, so any teammate can find a specific feature fast and the paper's chapters (system design, implementation, testing) can be written straight from the repo. The team's documentation lead (Princess) may adjust the template later. Until then, use this one exactly.

## Folder layout

```
docs/
  INDEX.md                 master index: feature table with links, status, FR/BR IDs
  ASSUMPTIONS.md           A1, A2, ... decisions made without confirmation
  CHANGELOG.md             per phase, human-readable
  DEPLOYMENT.md            how the live demo is deployed and torn down
  TRACEABILITY.md          FR/NFR/BR -> feature doc -> routes -> tests
  features/
    F-001-authentication.md
    F-002-student-registration-and-approval.md
    ...
  frontend/
    ux-workflows.md        per-role journeys + Mermaid flows
    ui-references.md       design principles, tokens, team-added references
    components.md          reusable component catalog
  backend/
    erd.md                 Mermaid ERD
    security.md            security checklist results
    dependencies.md        why each package exists
    api-routes.md          route table (method, URI, name, middleware, policy)
  source/
    ConnectEd_Final_Paper.pdf   (outdated paper, background only)
```

## File naming

`F-<three digits>-<kebab-case-feature-name>.md`. IDs never change or get reused. Archived features stay in the index with status `Retired`.

## Feature document template (copy exactly)

```markdown
# F-000 Feature Name

| Field | Value |
|---|---|
| Feature ID | F-000 |
| Module | e.g. Companies and Verification |
| Status | Planned / In progress / Done / Retired |
| Phase | 1-9 |
| Requirements | FR-00, NFR-00 |
| Business rules | BR-00 |
| Roles with access | OC, COC, UA, PC, Student (state the exact rights) |
| Owner (code) | folder or namespace |

## 1. Purpose
One short paragraph: what problem this feature solves and for whom.

## 2. User flow
Numbered steps per role. Add a Mermaid flowchart or sequence diagram when there is branching.

## 3. Screens
List each Blade view with its route name and a one-line description. Screenshot path under `docs/screenshots/F-000/`.

## 4. Routes
| Method | URI | Route name | Controller@action | Middleware / Policy |
|---|---|---|---|---|

## 5. Data
Tables and columns touched, relationships, status values, and which fields are audited.

## 6. Rules and validation
Business rules enforced, validation rules (reference the Form Request class), state transitions allowed, and who may perform them.

## 7. Notifications and audit
Which notifications are sent and to whom; which audit actions are written.

## 8. Edge cases and errors
What happens on invalid input, concurrency, missing documents, archived records, unauthorized access.

## 9. Tests
List test files and test names that prove sections 6 and 8. Include the command to run them.

## 10. Known limitations and assumptions
Link to A-numbers in ASSUMPTIONS.md. State honestly what is not done.

## 11. Change history
| Date | Change | Commit |
|---|---|---|
```

## INDEX.md format

A single table: `Feature ID | Name | Module | Roles | FR/BR | Phase | Status | Doc link | Primary route`. Sorted by ID. Update in the same commit as the feature.

## Writing rules

- Plain, precise English. Present tense. No marketing language.
- Facts only about what the code actually does. If something is planned but not built, mark it Planned.
- Never invent references, statistics, or institutional policies. Where BSAU policy is unknown, write "pending BSAU validation".
- Mermaid for all diagrams so they render on GitHub: flowcharts for workflows, `erDiagram` for the ERD, `stateDiagram-v2` for status lifecycles (company verification, MOA, application, placement).
- Each phase ends with a `CHANGELOG.md` entry and an updated INDEX and TRACEABILITY.
- Screenshots: take them from the running app in both light and dark where the UI differs. Name them `F-000-<screen>-<light|dark>.png`.

## TRACEABILITY.md format

`Requirement ID | Description | Feature doc | Routes | Tests | Status`. Every FR, NFR, and BR in `product-spec.md` appears at least once. Mark gaps honestly.
