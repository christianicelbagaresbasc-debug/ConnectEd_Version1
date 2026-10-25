# F-021 Institutional OJT Reports & RFC 4180 Streaming CSV Exports

| Field | Value |
|---|---|
| Feature ID | F-021 |
| Module | Reports |
| Status | Done |
| Phase | 7 |
| Requirements | FR-21, NFR-01, NFR-03, NFR-04, NFR-06, NFR-07 |
| Business rules | BR-15, BR-20 |
| Roles with access | OC, COC |
| Owner (code) | `app/Http/Controllers/ReportController.php`, `resources/views/reports/*.blade.php` |

## 1. Purpose
Provides institutional OJT reporting and auditing capabilities for accreditation and CHED/university compliance:
- **Central Reports Hub (`/reports`):** Overview metrics across placements, partner enterprises, and curriculum competencies, with direct preview cards and one-click export actions.
- **Placements Summary Report (`/reports/placements`):** Comprehensive record of placed student interns, host partner organizations, authorized start/end dates, confirmed coordinators, and school year cohorts.
- **Company Accreditation Report (`/reports/companies`):** Audit of partner companies, corporate sector, 6-point verification completion, executed MOA status, and active intern counts.
- **Skill Distribution & Demand Report (`/reports/skills`):** Curriculum and industry alignment matrix comparing student-acquired skills against requirements specified in partner company opportunities.
- **RFC 4180 Streaming CSV Exports:** Low-memory streaming responses generating UTF-8 CSV exports with standardized quoting, headers, and formatted timestamps.

## 2. Streaming CSV Export Architecture
```mermaid
flowchart LR
    User[Coordinator (OC/COC)] --> RequestCSV[GET /reports/{type}/csv]
    RequestCSV --> AuthCheck{Role is OC or COC?}
    AuthCheck -->|No| Reject[403 Forbidden]
    AuthCheck -->|Yes| StreamResponse[StreamedResponse via fopen php://output]
    
    StreamResponse --> Headers[Write RFC 4180 CSV Header Row]
    StreamResponse --> ChunkQuery[Query database in chunks of 100]
    ChunkQuery --> FormatRow[Format fields, dates, and sanitized strings]
    FormatRow --> WriteRow[fputcsv to output handle]
    WriteRow --> MoreData{More rows?}
    MoreData -->|Yes| ChunkQuery
    MoreData -->|No| FlushEnd[Flush and close stream]
```

## 3. Screens
- `resources/views/reports/index.blade.php`:
  - Institutional summary statistics cards: Total Cohort Students, Placement Rate %, Accredited Partner Companies, Cataloged Skills.
  - 3-card report module grid with descriptions and dual actions ("View Report" and "Export CSV").
- `resources/views/reports/placements.blade.php`:
  - Placements table with filters: School Year, Status.
  - Columns: Student Name, Student #, Program, Company, Opportunity Title, Status, Authorized Start, End Date, Confirmed By.
  - "Export CSV" button in header.
- `resources/views/reports/companies.blade.php`:
  - Companies table with filters: Classification, Sector.
  - Columns: Company Name, Classification, Verification Status, Sector, Contact Person, Executed MOA, Active Interns.
  - "Export CSV" button in header.
- `resources/views/reports/skills.blade.php`:
  - Skills table with filters: Category, Status.
  - Columns: Skill Name, Category, Status, Students Possessing Skill, Opportunities Requiring Skill, Alignment Ratio.
  - "Export CSV" button in header.

## 4. Routes
| Method | URI | Route name | Controller@action | Middleware / Policy |
|---|---|---|---|---|
| GET | `/reports` | `reports.index` | `ReportController@index` | `auth`, role: OC/COC |
| GET | `/reports/placements` | `reports.placements` | `ReportController@placements` | `auth`, role: OC/COC |
| GET | `/reports/placements/csv` | `reports.placements.csv` | `ReportController@exportPlacementsCsv` | `auth`, role: OC/COC |
| GET | `/reports/companies` | `reports.companies` | `ReportController@companies` | `auth`, role: OC/COC |
| GET | `/reports/companies/csv` | `reports.companies.csv` | `ReportController@exportCompaniesCsv` | `auth`, role: OC/COC |
| GET | `/reports/skills` | `reports.skills` | `ReportController@skills` | `auth`, role: OC/COC |
| GET | `/reports/skills/csv` | `reports.skills.csv` | `ReportController@exportSkillsCsv` | `auth`, role: OC/COC |

## 5. Rules & Data Specifications
- **RFC 4180 Compliance:**
  - Standardized comma separation with CRLF line breaks.
  - Fields containing spaces, commas, or quotes are quoted using PHP's native `fputcsv`.
  - Streamed directly to `php://output` to prevent memory exhaustion on large cohorts.
- **Rule BR-20 (No Ranking or Scoring):**
  - Placement and applicant reporting lists are ordered chronologically or alphabetically by student number. No student is scored or ranked against others in reports.
- **Access Authorization:**
  - Strictly restricted to OJT Coordinator (`oc`) and Co-OJT Coordinator (`coc`).
  - Students, Partner Companies, and University Attorneys receive a `403 Forbidden` response.

## 6. Testing
- `tests/Feature/Reports/ReportExportTest.php`:
  - `test_coordinator_can_access_reports_hub`
  - `test_student_cannot_access_reports`
  - `test_placements_report_and_csv_export`
  - `test_companies_report_and_csv_export`
  - `test_skills_report_and_csv_export`
