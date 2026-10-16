# Security and Privacy Standard

Treat this as a checklist. Each item needs an implementation and, where marked (T), an automated test. Record the result in `docs/backend/security.md`.

## Authentication and sessions
- Laravel default password hashing (bcrypt, cost from config). Minimum length 10, reject common passwords (`Password::min(10)->uncompromised()` only if outbound HTTP is allowed, otherwise `->mixedCase()->numbers()`).
- Login rate limiting (5 attempts per minute per email+IP) and a clear generic error message. (T)
- Regenerate session on login; invalidate on logout. Session driver `database`, lifetime 120 minutes, `SESSION_SECURE_COOKIE=true`, `same_site=lax`, `http_only=true` in production.
- `must_change_password` forces a password change before any other page. (T)
- Users with status `pending` or `suspended` cannot log in, with distinct, non-leaky messages.
- Registration requires acceptance of the privacy notice (store `consented_at` and notice version).

## Authorization
- Every controller action calls `$this->authorize(...)` or uses route middleware plus a Policy. No bare `Route::resource` without policies.
- Scope queries by ownership or assignment (COC sees assigned students only; PC sees only its company). Use global scopes or dedicated query methods, not view-level filtering.
- IDOR tests: for each role, request another user's record by ID and expect 403/404. (T)
- No applicant ranking or score leakage (see BR-20). (T)
- Promotion of a company to Existing Partner is OC-only and requires verification = verified and an executed university-company MOA. (T)

## Input validation and output
- One Form Request per write action; validate enums with PHP backed enums; whitelist `$fillable` on every model (no `$guarded = []`).
- Eloquent or the query builder with bindings only. No raw SQL concatenation.
- Blade `{{ }}` escaping everywhere; `{!! !!}` is banned unless sanitized and justified in a comment.
- CSRF on all state-changing routes; use `@csrf` and `@method`.
- Mass-assignment, open-redirect, and enumeration checks on login, registration, and password flows.

## File uploads
The full design (roles, direct-to-bucket presigned flow, bulk upload, bundles) is in `files-and-documents.md`; the rules below always apply.
- Allowlist: pdf, docx, png, jpg, jpeg (resume, certificates, MOA, verification, reports). Max 5 MB (config). Validate extension, client MIME, and server-detected MIME (`finfo`); reject mismatches.
- Store under random UUID names, never the original name. Keep the original name only as metadata.
- Private disk only. Files are streamed through an authorized controller (policy check on the owning record), never linked directly. Add `Content-Disposition` and `X-Content-Type-Options: nosniff`.
- Record `checksum_sha256`, `mime_type`, `size_bytes`. Never execute or include uploaded content. (T for rejection of a renamed `.php` file.)
- Storage driver is configurable: `local` in development, S3-compatible in production (see `deployment.md`).

## Transport, headers, config
- HTTPS only in production: `URL::forceScheme('https')`, trusted proxies set for Vercel, HSTS header.
- Security headers middleware: `X-Frame-Options: DENY`, `X-Content-Type-Options: nosniff`, `Referrer-Policy: strict-origin-when-cross-origin`, `Permissions-Policy` minimal, and a Content-Security-Policy that allows only self-hosted assets plus Alpine (use the CSP-compatible Alpine build or nonces).
- `APP_DEBUG=false` and `APP_ENV=production` in production. Errors render generic pages; details go to logs.
- `.env` and keys never committed. Add secret scanning to CI (for example gitleaks) and run `composer audit` and `npm audit`.

## Audit logging
- Log: login success/failure counts, account approval/suspension, password resets by OC, role/assignment changes, company classification and verification changes, MOA custody and status changes, UA note creation, application decisions, placement confirmation and status changes, archive/restore, exports.
- Fields: actor, action, subject, changed fields (old/new), timestamp. IP only if `connected.audit_ip` is enabled. Never log passwords, tokens, or file contents.
- Audit tables are append-only: no update/delete routes, and the model blocks updates.

## Privacy (Data Privacy Act of 2012, RA 10173)
- Data minimization: collect only fields in `data-model.md`. No government IDs, no birthdates unless the team justifies them in ASSUMPTIONS.md.
- Privacy notice and consent text page, versioned. Mark it clearly as "draft pending BSAU/DPO review".
- Role-based visibility of personal data; students see only their own data; PC sees applicant data only for its own opportunities and only fields needed for review.
- Provide an OC export of one student's data and an archive (not purge) action, noting that retention rules are pending BSAU policy (A12).
- Demo deployments contain synthetic data only.

## Dependency and supply chain
- Pin versions via lockfiles, commit `composer.lock` and `package-lock.json`.
- CI runs tests, `composer audit`, `npm audit --omit=dev`, and a secret scan on every push.
- Prefer few dependencies. Justify each added package in `docs/backend/dependencies.md`.

## Required security tests (Pest or PHPUnit feature tests)
Login throttle; forced password change; pending user blocked; each policy denies other roles; COC sees only assigned students; PC sees only own applicants and no scores; direct document URL denied to unauthorized users; upload rejects disguised executable; archived record cannot be hard-deleted via any route; audit rows are created for decision endpoints.
