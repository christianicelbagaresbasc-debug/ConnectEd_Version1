# Files and Documents Module

Contents: 1 Why this needs its own design · 2 Who can upload and download what · 3 Upload flow (single and bulk) · 4 Download flow · 5 Document library · 6 Review and versioning · 7 Limits · 8 Data model additions · 9 Tests · 10 Known limitations

## 1. Why this needs its own design

Some roles move many files: the OC handles verification papers, MOAs, and start-authorization emails for many students and companies; PCs submit company proofs; students submit resumes, certificates, MOAs, and weekly reports; COCs review reports. The deployment host (Vercel) has request-size and duration limits and no durable disk. So files must go **browser to private S3-compatible bucket directly**, and the app only authorizes, records, and verifies them. Build one reusable `DocumentService` and use it everywhere (profile, verification, MOA, placement, weekly reports). No feature may implement its own upload code.

## 2. Who can upload and download what

U = upload, D = download, R = review (accept/reject with remark), X = archive. "own" = own records; "assigned" = assigned students; "own company" = the PC's company.

| Category | OC | COC | UA | PC | Student |
|---|---|---|---|---|---|
| `resume`, `certification` | U/D/R/X any | D assigned | none | D only for applicants to own opportunities | U/D/X own |
| `verification` (SEC, DTI, business certificate, contract, completion cert) | U/D/R/X any | D | D | U/D own company | none |
| `moa` (all versions) | U/D/R/X any | D assigned students' | U/D (legal-reviewed versions) | U/D own company's | U/D own student-company MOA |
| `authorization` (start-OJT email copy) | U/D/X any | U/D assigned | none | D own placements | D own |
| `weekly_report` | U/D/R/X any | U/D/R assigned | none | D own placements | U/D own |
| `template` (blank MOA, forms, guides) | U/D/X | D | D | D | D |
| `other` | U/D/X any | by task | none | none | own |

Rules: every download and upload passes through a Policy keyed on the **owning record**, never just the document ID. Students never see other students' files. PC never sees resumes of students who did not apply to its company. UA never sees student personal documents. Downloads of sensitive categories by someone other than the owner write an audit row `document.downloaded`.

## 3. Upload flow (single and bulk)

Direct-to-storage with presigned URLs:

1. **Request slots**: browser sends metadata only, `POST /documents/uploads` with an array of `{name, size, mime, category, documentable_type, documentable_id}` (max `connected.upload_batch_max`, default 20 files). Server authorizes each against section 2, validates extension, declared MIME, size, and per-user quota, creates `documents` rows with `validation_status = pending` and a random `storage_key`, and returns one short-lived (about 10 minutes) presigned PUT URL per file plus a `batch_id`.
2. **Upload**: browser PUTs each file straight to the bucket. Parallelism of 3, per-file progress bar and retry, cancel button, and a visible list of successes and failures. Configure bucket **CORS** to allow PUT from the app origin only.
3. **Confirm**: browser calls `POST /documents/uploads/{batch}/confirm`. Server, per file: checks the object exists, size matches and is within limit, reads the first bytes to check the **magic number** against the allowed types (pdf, docx, png, jpg), then sets `validation_status = ok` or `rejected` with a reason. Rejected objects are marked unusable and are never served. Unconfirmed `pending` rows older than 1 hour become `expired`.
4. The record that owns the file only displays files with `validation_status = ok`.

Because presigned PUT cannot enforce size on every provider, **always verify size after upload**. If the chosen storage cannot do presigned URLs, fall back to server-side upload for small files only (config `connected.direct_upload=false`, max 4 MB) and tell the human.

Local development uses the same flow against the `local` disk through a signed route so the code path stays identical.

UI: a reusable dropzone component supporting drag and drop, multi-select, paste, mobile camera or file picker, per-category hints (allowed types and limits), and client-side pre-checks that mirror server rules (the server remains authoritative). Works on phones.

## 4. Download flow

- `GET /documents/{id}/download`: policy check, then **redirect to a short-lived (60 seconds) presigned GET URL** with `Content-Disposition: attachment` and the original file name. Do not proxy large files through the PHP function.
- PDF inline preview is optional: only for `application/pdf`, in a sandboxed iframe, via the same presigned route.
- **Bundle download** ("Download all" for a student, company, or MOA, and "Download selected" in the library): server streams a ZIP using a streaming zip library, files named `<category>/<original-name>`. Cap per bundle with config (`connected.bundle_max_files` default 50, `connected.bundle_max_mb` default 100). If the cap is exceeded, split into several bundles and say so in the UI. Test with realistic sizes against the live deployment because function duration limits apply. If streaming is unreliable on Vercel, fall back to a page of individual presigned links and record this in ASSUMPTIONS.md.

## 5. Document library

A page for OC (all), COC (assigned scope), PC (own company), and student (own), reusing one table component:

- Columns: file name, category, owner/record, uploaded by, date, size, review status, version.
- Filters: category, owner (student or company), school year, review status, date range, uploader. Search by file name. Sort and paginate server-side (25 per page, up to 100).
- Bulk actions with checkboxes and "select all on this page": download selected (bundle), archive selected (OC), mark reviewed (OC/COC where allowed).
- **Completeness views**: per student ("resume present, MOA missing") and per company ("verification checklist 3 of 5 submitted") with clear missing items. These reuse `verification_requirements` and the placement/MOA requirements as data.
- Mobile: card layout with the same actions.

## 6. Review and versioning

- Re-uploading to the same slot creates a **new version** (`version_group_id`, `version_no`, `is_current`). Old versions stay and remain downloadable by roles allowed to see history. Nothing is deleted from storage.
- Review status per document: `pending_review`, `accepted`, `rejected` with remark, `reviewed_by`, `reviewed_at`. Accepting a verification document updates the matching `company_verification_items` row; rejecting notifies the uploader (in-system) with the remark.
- Archive instead of delete (soft delete plus `archived_at`); OC can restore.

## 7. Limits (all configurable in `config/connected.php`)

Allowed types: pdf, docx, png, jpg/jpeg. Max file 10 MB (resume 5 MB). Max 20 files per batch. Per-user storage quota: students 50 MB, PC 200 MB, staff unlimited by default. Rate limit slot requests (for example 30 per minute per user). Show remaining quota in the UI.

## 8. Data model additions

Extend `documents` with: `storage_key`, `upload_batch_id` (FK `document_batches`), `version_group_id`, `version_no`, `is_current`, `review_status`, `review_remark`, `reviewed_by`, `reviewed_at`, `school_year`, `expires_at` (nullable, for documents like permits with expiry). New table **document_batches** (user_id, status, file_count, created_at, confirmed_at). Index documents on (`documentable_type`, `documentable_id`), (`category`, `review_status`), (`school_year`), (`version_group_id`, `is_current`).

## 9. Tests (required)

Role-by-category matrix test generated from the table in section 2 (every allowed and denied pair). Student cannot request a slot for another student's record. PC cannot download a non-applicant's resume. UA cannot download student resumes. Tampered batch ID rejected. Oversized object rejected on confirm. Renamed `.php` or mismatched magic bytes rejected. Batch of 20 files creates 20 rows and confirms correctly. Expired pending rows are marked expired. Re-upload creates version 2 and keeps version 1. Download link expires. Bundle cap enforced. Audit row written for non-owner download of sensitive categories.

## 10. Known limitations (write these in the feature doc)

No antivirus scanning (not practical on this hosting; mitigated by type allowlist, magic-byte check, attachment-only downloads, and private storage; consider provider-side scanning later). Bundle size is capped by serverless duration. Retention and expiry rules are pending BSAU policy (A12). No in-browser document editing.
