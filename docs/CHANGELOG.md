# ConnectEd Changelog

All notable changes to the ConnectEd system are documented in this file in chronological order by phase.

---

## [Phase 1: Foundation and Walking Skeleton Deploy] - 2026-10-16

### Added
- **Core Framework Foundation:** Scaffolded Laravel 10.50.3 on PHP 8.3/8.5 with Laravel Breeze (Blade).
- **Identity & Roles Schema:**
  - Extended `users` table with `role` (`oc`, `coc`, `ua`, `pc`, `student`), `status` (`pending`, `active`, `suspended`), `must_change_password`, `approved_by`, `approved_at`, `last_login_at`, and `SoftDeletes`.
  - Created PHP 8.3 backed enums `App\Enums\UserRole` and `App\Enums\UserStatus` with human-readable labels.
  - Implemented `UserSeeder` with synthetic accounts for all 5 roles plus pending student state.
  - Added migrations for database-backed sessions and database cache tables for ephemeral container hosting.
- **Design System & LinkedIn-Inspired UI:**
  - Configured `@fontsource/inter` and `@fontsource/poppins` self-hosted typography with zero runtime CDN calls.
  - Defined full CSS custom property tokens (`--bg`, `--surface`, `--primary`, `--accent`, `--border`, etc.) with class-based light/dark theme switching.
  - Implemented blocking theme script in `<head>` to permanently eliminate flash of unstyled theme (FOUC).
  - Built interactive 3-state `ThemeToggle` component (Light, Dark, System) with media query listeners.
  - Built responsive application shell layout (`resources/views/layouts/app.blade.php`) featuring fixed left sidebar, tablet rail, mobile off-canvas drawer, topbar search, and notification bell counter.
  - Built institutional landing page (`resources/views/welcome.blade.php`) and accessible sign-in view (`resources/views/auth/login.blade.php`) with demo account quick-fill credentials.
- **Security & Quality:**
  - Enforced rate-limiting (5 attempts/minute) on login.
  - Blocked pending and suspended users with distinct, secure error messaging.
  - Updated `TrustProxies` middleware to trust all proxies for Vercel edge container routing.
  - Enforced HTTPS in production via `AppServiceProvider::boot()`.
  - Added `/health` smoke-test endpoint returning JSON status.
- **Cloud & Deployment Infrastructure:**
  - Configured `Dockerfile.vercel` using multi-stage Node 20 asset bundling and FrankenPHP 1 (PHP 8.3).
  - Added `Caddyfile`, `vercel.json` container service specification, and `.dockerignore`.
  - Added `.github/workflows/ci.yml` running PHP 8.3, PostgreSQL 16 container, Vite asset build, test runner, and security audits.
  - Configured S3 flysystem driver (`league/flysystem-aws-s3-v3`) with dual local/S3 `documents` disk configuration.
- **Testing:**
  - 31 automated tests passing covering `/health`, authentication lifecycle, throttle defense, pending/suspended lockout, soft-delete verification, and theme preference rendering.
