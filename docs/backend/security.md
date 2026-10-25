# ConnectEd Security & Privacy Architecture Audit

| Metric | Status | Reference |
|---|---|---|
| Review Date | October 2026 | `references/security.md` |
| Security Lead | ConnectEd Capstone Development Team | BSAU Institute of Computer Studies |
| Status | Passed (100% Implemented & Tested) | Hardened for Production Deployment |

---

## 1. Authentication & Sessions
- **Password Hashing:** Uses Laravel default bcrypt hashing. Validated with strong password requirements (`Password::min(10)->mixedCase()->numbers()`).
- **Login Rate Limiting:** Enforces `RateLimiter` throttle (5 attempts per minute per email+IP) returning HTTP 429 and generic error feedback. (T: `tests/Feature/Auth/AuthenticationTest.php`)
- **Session Security:** Session regenerated on login (`$request->session()->regenerate()`), invalidated on logout (`$request->session()->invalidate()`).
  - Production configurations: `SESSION_SECURE_COOKIE=true`, `SESSION_SAME_SITE=lax`, `SESSION_HTTP_ONLY=true`.
- **Forced Password Change:** `EnsurePasswordIsChanged` middleware intercepts any authenticated request if `user.must_change_password === true`, forcing redirection to `/password/change`. (T: `tests/Feature/Auth/PasswordManagementTest.php`)
- **Account Status Guard:** Users in `pending_approval` or `suspended` status are rejected at login with distinct, secure, non-leaky messages. (T: `tests/Feature/Auth/RegistrationApprovalTest.php`)
- **Privacy Notice & Consent:** Student self-registration requires explicit acceptance of the BSAU Data Privacy Notice; recorded with `consented_at` timestamp.

---

## 2. Server-Side Authorization & RBAC
- **Strict Role-Based Access Control:** Built via native PHP 8.3 `UserRole` enum (`oc`, `coc`, `ua`, `pc`, `student`), paired with dedicated Laravel Policies (`CompanyPolicy`, `MoaPolicy`, `OpportunityPolicy`, `ApplicationPolicy`, `PlacementPolicy`, `TaskPolicy`, `WeeklyReportPolicy`).
- **No Client-Side Security Reliance:** Every controller action enforces `$this->authorize(...)` or explicit route authorization middleware. Hiding UI elements is never used as an authorization mechanism.
- **Insecure Direct Object Reference (IDOR) Protection:**
  - Students cannot view or mutate other students' applications, placements, weekly reports, or private documents. (T: `tests/Feature/Security/SecurityHardeningTest.php`)
  - Partner companies can only access applicants who applied to opportunities owned by their enterprise.
  - Non-coordinators receive HTTP 403 Forbidden on administrative endpoints.
- **Rule BR-20 (Anti-Ranking & Score Isolation):**
  - Match scores are calculated dynamically in memory by `SkillMatcher` and exposed only to the student concerned (`/student/recommendations`).
  - No query or controller sorts, filters, or presents students to companies or coordinators based on match scores. (T: `tests/Feature/Student/RecommendationTest.php`)

---

## 3. Input Validation & Injection Defense
- **Form Requests:** Every state-modifying action uses a dedicated `FormRequest` with explicit validation rules and PHP backed enums.
- **Mass-Assignment Protection:** Every Eloquent model contains an explicit `$fillable` allowlist. `$guarded = []` is strictly prohibited across the codebase.
- **SQL Injection Prevention:** 100% of queries utilize Eloquent ORM or parameterized PDO bindings. Raw string concatenation is forbidden.
- **Cross-Site Scripting (XSS):**
  - Blade `{{ $variable }}` output escaping is strictly used. `{!! !!}` is banned across all application templates.
  - Custom error templates (`resources/views/errors/*.blade.php`) escape error messages.
- **Cross-Site Request Forgery (CSRF):** All POST, PUT, PATCH, and DELETE routes require `@csrf` and `@method` tokens enforced by `VerifyCsrfToken` middleware.

---

## 4. File Upload & Document Security
- **Strict Allowlist:** Permitted extensions are strictly limited to `pdf`, `docx`, `png`, `jpg`, `jpeg`. Maximum file size 5 MB.
- **Magic Byte & MIME Verification:** `DocumentValidationService` checks server-detected MIME type via `finfo` against file contents, immediately rejecting disguised executable or script files (e.g., PHP files disguised as `.pdf` or `.png`). (T: `tests/Feature/Documents/FileValidationTest.php`)
- **Secure File Storage:**
  - Files are stored on a private storage disk with randomly generated UUID keys, never under the user's original filename.
  - Direct public access is disabled. Downloads are streamed through `DocumentService::streamDownload()` after policy authorization.
  - Streaming responses include `Content-Disposition: attachment` and `X-Content-Type-Options: nosniff`.
- **Integrity Checksums:** Every uploaded file computes and records `checksum_sha256`, `size_bytes`, and `mime_type`.
- **Batch ZIP Archive Safeguards (Assumption A19):**
  - Batch archive downloads are capped at 50 files and 100 MB per batch. (T: `tests/Feature/Documents/DocumentLibraryTest.php`)

---

## 5. Transport, Headers & Environment Hardening
- **Security Headers Middleware (`App\Http\Middleware\SecurityHeaders`):**
  - `X-Frame-Options: DENY` (Clickjacking prevention)
  - `X-Content-Type-Options: nosniff` (MIME sniffing prevention)
  - `Referrer-Policy: strict-origin-when-cross-origin`
  - `Permissions-Policy: camera=(), microphone=(), geolocation=()`
  - `Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval'; style-src 'self' 'unsafe-inline'; img-src 'self' data: https:; font-src 'self' data:; connect-src 'self'; frame-ancestors 'none';`
  - `Strict-Transport-Security: max-age=31536000; includeSubDomains` (Active over HTTPS / production)
- **HTTPS Enforcement:** `AppServiceProvider::boot()` automatically enforces `URL::forceScheme('https')` in production environments.
- **Reverse Proxy Trust:** `TrustProxies` configured with `Request::HEADER_X_FORWARDED_AWS_ELB` and wildcard support for Vercel edge container proxies.
- **Error Obfuscation:** `APP_DEBUG=false` in production. Custom generic error views (`403.blade.php`, `404.blade.php`, `419.blade.php`, `500.blade.php`) ensure database structure or technical stack traces are never leaked.

---

## 6. Immutable Audit Logging (Rule BR-15)
- **Audit Service (`App\Services\AuditService`):**
  - Records every critical state change across accounts, verification, MOA transfers, applications, placements, weekly reports, and document bundles.
  - Capture schema: `actor_id`, `actor_role`, `action`, `subject_type`, `subject_id`, `details` (JSON with old/new states), `created_at`.
  - Passwords, access tokens, and file binary payloads are strictly excluded from audit payloads.
- **Append-Only Integrity:** The `audit_logs` table has no delete or update routes. `AuditLog` model prevents modification once created.

---

## 7. Data Privacy (RA 10173 - Data Privacy Act of 2012)
- **Data Minimization:** ConnectEd collects only essential academic and contact identifiers (student number, academic program, year/section, email, skills). Sensitive personal information (national IDs, biometric data) is omitted.
- **Student Privacy & Ownership:**
  - Students can review their profile, uploaded resumes, and certifications at any time.
  - Partner companies receive access only to submitted applicants for open opportunities; they cannot browse or search unapplied student profiles.
- **Synthetic Data Integrity:**
  - The repository and seeders contain 100% synthetic mock data (`@bsau.edu.ph`, mock Philippine companies). No real institutional or personal data is stored in version control.

---

## 8. Dependency & Vulnerability Review
- **NPM Dependencies:** `npm audit --omit=dev` ran with **0 vulnerabilities**.
- **Composer Dependencies:**
  - Running on stable Laravel 10 LTS (`10.48.29`) on PHP 8.3.
  - Documented advisories in `laravel/framework` reviewed:
    1. *TokenGuard authentication bypass*: Mitigated. ConnectEd uses session cookies only; TokenGuard API tokens are not utilized.
    2. *XSS in Debug Page*: Mitigated. `APP_DEBUG=false` in production; custom error templates render generic error pages without stack traces.
    3. *Temporary Signed URL Path Confusion*: Mitigated. Direct download routes validate session authentication and Eloquent policy authorization before streaming.
    4. *CRLF injection in default email rule*: Mitigated. Rule BR-02 strictly limits notifications to in-system database storage; no email sending is enabled.
