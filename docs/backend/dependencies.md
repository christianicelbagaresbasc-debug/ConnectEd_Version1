# ConnectEd Backend Package Justifications

Every dependency added to ConnectEd is vetted for security, performance, licensing (MIT/BSD/OFL), and necessity.

| Package | Version | License | Justification |
|---|---|---|---|
| `laravel/framework` | `^10.50` | MIT | Core web framework powering HTTP routing, Eloquent ORM, Blade rendering, policies, and transactions. |
| `laravel/breeze` | `^1.29` | MIT | Lightweight authentication scaffolding providing Blade views, session authentication, and CSRF protection. |
| `laravel/sanctum` | `^3.3` | MIT | Provides secure cookie-based session guarding and API token management. |
| `league/flysystem-aws-s3-v3` | `^3.35` | MIT | Driver enabling S3-compatible cloud storage (Cloudflare R2, AWS S3, Supabase) for private document vaults. |
| `@fontsource/inter` | `^5.0` | OFL | Self-hosted Inter font files providing WCAG-compliant body text without third-party CDN tracking. |
| `@fontsource/poppins` | `^5.0` | OFL | Self-hosted Poppins font files for institutional headings. |
| `@tailwindcss/forms` | `^0.5` | MIT | Consistent form input styling reset across Chrome, Safari, Edge, and Firefox. |
| `alpinejs` | `^3.4` | MIT | Lightweight reactive UI library handling sidebar drawers, modals, dropdowns, and theme toggling without SPA framework bloat. |
