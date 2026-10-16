# Deployment: GitHub and Vercel (temporary demo)

Contents: 1 What is different about Vercel · 2 GitHub · 3 Files to add · 4 Environment variables · 5 Database and storage · 6 Deploy steps · 7 Laravel 10 specifics · 8 Troubleshooting · 9 Fallbacks · 10 Teardown

## 1. What is different about Vercel

Vercel has no built-in PHP runtime. Method used here: Vercel's documented **Laravel on Vercel with Docker (FrankenPHP)** approach, a container-backed function (guide published 2026-09-03: `https://vercel.com/kb/guide/laravel-php-with-docker`). Re-read that page at the start of Phase 1 because Vercel's container support is new and may have changed. Consequences the app must respect:

- The container filesystem is **not durable**. No local uploads, no file sessions, no file cache. Use an external DB for sessions/cache and an S3-compatible bucket for uploads.
- Instances scale out, so there is no shared memory. `CACHE_DRIVER=database`, `SESSION_DRIVER=database`, `QUEUE_CONNECTION=sync`. No queue workers or `schedule:work`. Anything scheduled uses Vercel Cron hitting an authenticated route (not needed for the MVP).
- Do **not** run `php artisan config:cache` during the image build (env values arrive at runtime). Caching events and views at build time is fine.
- Do **not** run migrations at container start. Run them as a separate step against the remote database.
- Logs go to stderr: `LOG_CHANNEL=stderr`.
- The database is hosted on **Neon (PostgreSQL)** (see section 5). Use Neon's pooled connection string at runtime because many short-lived function instances open many connections. Neon computes may scale to zero, so the first request after idle can be slow; mention this in the demo notes.

If the container runtime is unavailable on the account's plan, use the fallbacks in section 9 and tell the human.

## 2. GitHub

1. Check `gh auth status`. If `gh` is unavailable or not logged in, stop and ask the human to create an empty repository named `connected` (private is fine) and provide the remote URL. Never request or store tokens in files or chat.
2. `git init`, set `main`, add a complete `.gitignore` (Laravel default plus `.env*`, `.vercel`, `node_modules`, `vendor`, `public/build`, `storage/*.key`, `*.sql`, IDE folders).
3. Commit `.agent/skills/connected-builder/`, `AGENTS.md`, and `docs/` so the project is self-describing.
4. First push after Phase 1. Push after every phase and tag (`phase-1`, `phase-2`, ...).
5. Add `.github/workflows/ci.yml`: PHP 8.3, composer install, copy `.env.example`, key generate, PostgreSQL service container, `php artisan test`, `npm ci && npm run build`, `composer audit`, `npm audit --omit=dev`, and a secret scan. Keep it under about 5 minutes.
6. Protect nothing fancy: a README with setup, demo accounts (names only, not passwords), and a link to the live URL is enough.

## 3. Files to add (adapt from the Vercel guide, verified against the current page)

**`Dockerfile.vercel`** (Laravel 10 needs PHP 8.1 to 8.3, so use 8.3, and a Node stage for Vite assets):

```dockerfile
FROM node:20-bookworm-slim AS assets
WORKDIR /app
COPY package.json package-lock.json ./
RUN npm ci
COPY resources ./resources
COPY app ./app
COPY public ./public
COPY vite.config.js tailwind.config.js postcss.config.js ./
RUN npm run build

FROM dunglas/frankenphp:1-php8.3-bookworm AS base
RUN cp "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini" \
    && install-php-extensions intl zip pdo_pgsql bcmath gd exif
WORKDIR /app

FROM base AS dependencies
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --no-progress --no-scripts --optimize-autoloader --prefer-dist
COPY . .
RUN mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views storage/logs bootstrap/cache \
    && composer dump-autoload --no-dev --classmap-authoritative

FROM base AS runtime
COPY --from=dependencies --chown=www-data:www-data /app /app
COPY --from=assets --chown=www-data:www-data /app/public/build /app/public/build
COPY --chown=www-data:www-data Caddyfile /etc/frankenphp/Caddyfile
RUN setcap CAP_NET_BIND_SERVICE=+eip /usr/local/bin/frankenphp \
    && chown -R www-data:www-data /config/caddy /data/caddy
RUN php artisan event:cache || true && php artisan view:cache || true
ENV PORT=80 APP_ENV=production APP_DEBUG=false LOG_CHANNEL=stderr
USER www-data
CMD ["frankenphp", "run", "--config", "/etc/frankenphp/Caddyfile"]
```

Notes: the `view:cache` and `event:cache` lines must run as a user that can write `bootstrap/cache`; if they fail, move them before `USER www-data` and fix ownership. Do not set `SESSION_DRIVER`, `CACHE_DRIVER`, or `QUEUE_CONNECTION` in the image; set them in Vercel env. If Vite's `build` needs PHP files for Tailwind class detection, the `COPY app ./app` line covers it.

**`Caddyfile`**:

```
:{$PORT:80} {
    root * /app/public
    encode zstd gzip
    php_server {
        try_files {path} index.php
    }
}
```

**`vercel.json`**:

```json
{
  "$schema": "https://openapi.vercel.sh/vercel.json",
  "services": {
    "app": { "root": ".", "entrypoint": "Dockerfile.vercel", "runtime": "container" }
  },
  "rewrites": [{ "source": "/(.*)", "destination": { "service": "app" } }]
}
```

**`.dockerignore`**: `.git`, `.env`, `.env.*` (but keep `!.env.example`), `node_modules`, `tests`, `vendor`, `storage/logs/*`, `public/build`, `.vercel`.

Add a `/health` route returning `{"status":"ok"}` (no DB access) for smoke tests.

## 4. Environment variables (set in Vercel project settings, never in git)

Required: `APP_NAME=ConnectEd`, `APP_ENV=production`, `APP_DEBUG=false`, `APP_KEY` (generate with `php artisan key:generate --show`; keep it stable), `APP_URL` (the Vercel URL, https), `APP_TIMEZONE=Asia/Manila`, `LOG_CHANNEL=stderr`, `DB_CONNECTION=pgsql`, `DB_HOST` (Neon **pooled** host, the one containing `-pooler`), `DB_PORT=5432`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`, `DB_SSLMODE=require`. Keep a separate `DB_HOST_DIRECT` value (non-pooled host) only on the machine that runs migrations, never needed at runtime, `SESSION_DRIVER=database`, `SESSION_SECURE_COOKIE=true`, `CACHE_DRIVER=database`, `QUEUE_CONNECTION=sync`, `FILESYSTEM_DISK=documents` (or whichever name the code uses), `SEED_DEFAULT_PASSWORD` (only when seeding), `ALLOW_DEMO_SEED`.

Storage (S3-compatible): `AWS_ACCESS_KEY_ID`, `AWS_SECRET_ACCESS_KEY`, `AWS_DEFAULT_REGION`, `AWS_BUCKET`, `AWS_ENDPOINT`, `AWS_USE_PATH_STYLE_ENDPOINT`. Keep `.env.example` updated with every key (empty values) and a comment for each.

In `config/database.php` the `pgsql` connection already reads `sslmode`; set `'sslmode' => env('DB_SSLMODE', 'prefer')`. If Neon requires the endpoint ID in the connection options and you see "endpoint ID is not specified" errors, check Neon's current Laravel/PHP documentation for the supported fix (for example passing the full connection string via `DB_URL`) and follow that.

## 5. Database and storage (human steps)

The human creates these; the agent writes the code and tells them exactly which values to put where.

- **PostgreSQL on Neon** (already created by the team): the human gives the agent the *names* of the pooled and direct connection values to set (never the secrets themselves in chat). Free-tier limits change, so check Neon's current plan page rather than assuming. Create a separate Neon **branch** for the demo if branching is available, so tests never touch demo data.
- **Object storage**: any S3-compatible bucket (for example Cloudflare R2, Supabase Storage S3 API, AWS S3). Keep the bucket **private** and configure its **CORS** to allow `PUT` (and `GET`) from the app origin only, because browsers upload straight to it. Vercel Blob is also an option but Laravel has no first-party driver for it, so S3-compatible is simpler. Install `league/flysystem-aws-s3-v3` and define a private `documents` disk that is `local` when `APP_ENV=local`.

## 6. Deploy steps

1. Local check: build the image and run `vercel dev -L --listen 8080` with `.env.local` exported as the guide describes; curl `/health` and `/`.
2. Human: `npm i -g vercel`, `vercel login`, `vercel link` (or import the GitHub repo in the Vercel dashboard; keep `Dockerfile.vercel`, `Caddyfile`, `vercel.json` at the project root).
3. Human: add the environment variables (`vercel env add NAME`).
4. Migrate and seed from a trusted machine against the Neon **direct** connection, one time per schema change:
   `DB_HOST=<neon direct host> DB_DATABASE=... DB_USERNAME=... DB_PASSWORD=... DB_SSLMODE=require php artisan migrate --force` then, for the demo only, `ALLOW_DEMO_SEED=true SEED_DEFAULT_PASSWORD=... php artisan db:seed --force`.
5. `vercel deploy` (preview) then `vercel deploy --prod` once smoke tests pass. Connecting the GitHub repo gives preview deployments per push.
6. Smoke test on the live URL: `/health`, login as each of the five demo roles, upload and download a document, create an application, open the notification bell, toggle dark mode on a phone-width viewport.
7. Write `docs/DEPLOYMENT.md` with the exact steps used, the list of env var **names**, and the teardown steps.

## 7. Laravel 10 specifics

- Laravel 10 uses `CACHE_DRIVER` (not `CACHE_STORE`). Breeze for Laravel 10 is the 1.x line; let Composer resolve it.
- Trust the proxy: in `app/Http/Middleware/TrustProxies.php` set `protected $proxies = '*';` and keep the default forwarded headers, so generated URLs and secure cookies work behind Vercel.
- In `AppServiceProvider::boot()`: `if (app()->environment('production')) { URL::forceScheme('https'); }`.
- Laravel 10 is past its security-support window upstream. Record this in `docs/ASSUMPTIONS.md` (A13: "Team chose Laravel 10; upgrade path to a supported release is a later task") and keep dependencies updated within the 10.x line, running `composer audit`.

## 8. Troubleshooting

| Symptom | Likely cause and fix |
|---|---|
| 502 | FrankenPHP not listening on `$PORT`; keep `:{$PORT:80}` in the Caddyfile |
| "No application encryption key" | `APP_KEY` missing in Vercel env; add, redeploy |
| Logged out on every request | session driver is `array`/`file`; use `database` and make sure `sessions` table is migrated |
| Mixed content or http URLs | proxies not trusted or `forceScheme` missing |
| 419 CSRF on every POST | cookies not persisting: `APP_URL`, `SESSION_SECURE_COOKIE`, `SESSION_DOMAIN` mismatch |
| Styles missing | Vite build stage did not copy `public/build`; check the `assets` stage |
| DB connection refused or SSL error | wrong host, `DB_SSLMODE` not `require`, endpoint ID missing, or compute suspended (retry once). Prepared-statement errors through the pooler: test with the direct host to confirm, then ask the human before changing connection mode |
| Too many connections | confirm the pooled host is in use, avoid N+1 queries |
| Uploads vanish | still using the local disk; switch to the S3 disk |

## 9. Fallbacks (say so to the human before switching)

1. **Community runtime** `vercel-php` with `api/index.php` and a `vercel.json` build entry. Older, works with Composer auto-install, needs cache paths pointed at `/tmp` (`APP_CONFIG_CACHE=/tmp/config.php`, etc.) and may require a newer Laravel/PHP than the project uses. Check the project's README for supported PHP versions before relying on it.
2. **Another Docker host** (Render, Railway, Fly.io) using the same Dockerfile minus Vercel specifics. Keep the app portable so this is a config change, not a rewrite.

## 10. Teardown (temporary deployment)

Document in `docs/DEPLOYMENT.md`: remove the Vercel project, delete the database and bucket, rotate or delete any credentials used, and confirm no real personal data was ever loaded. The repository stays.
