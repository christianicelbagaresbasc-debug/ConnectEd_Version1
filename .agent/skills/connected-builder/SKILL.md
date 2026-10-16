---
name: connected-builder
description: Builds, extends, documents, and deploys ConnectEd, the BSAU (Bulacan State Agricultural University) skill-based OJT placement and company verification system, as a Laravel 10 web app pushed to GitHub and deployed to Vercel. Use this skill whenever the task involves ConnectEd, OJT placement, company verification, MOA tracking, OJT Coordinator / Co-Coordinator / University Attorney / Partner Company / ICS Student roles, skill-based opportunity matching, the capstone repository, or deploying this project to Vercel, even if the user does not name the skill.
---

# ConnectEd Builder

You are the lead engineer building **ConnectEd**, an undergraduate IT capstone that must become a real, working, deployable system. Build it in phases, commit often, document every feature in the team's standard format, and stop at the human checkpoints listed below.

## 1. Source of truth and conflict rules

Priority order when instructions conflict:

1. The user's latest message.
2. `AGENTS.md` and the files in `references/` (this skill). These are the **current** spec.
3. The original proposal paper (`docs/source/ConnectEd_Final_Paper.pdf`, if present). It is **outdated**. Use it only for background, terminology, and the business rules it states that are not overridden below.
4. Your own judgment. Every judgment call goes into `docs/ASSUMPTIONS.md` (see section 3).

### What changed since the paper (do NOT follow the paper on these)

| Topic | Paper (old) | Current (use this) |
|---|---|---|
| Stack | Plain PHP, HTML/CSS/JS, MySQL, Apache | **Laravel 10**, Blade + Tailwind + Alpine.js, **PostgreSQL (Neon)** |
| Roles | Student, Company Rep, OJT Administrator | **OC, COC, UA, PC, ICS Student** (see `references/product-spec.md`) |
| MOA | Background concern only | **First-class module**: multi-party tracking, legal notes from UA |
| Devices | Desktop Chrome only | **Mobile, tablet, desktop**, light and dark mode |
| Data | Not specified | **Everything is archived, never hard-deleted** |
| Task management | Absent | OC assigns tasks and students to COCs |
| Student scope | BSIT only | "ICS Students"; program is a config value (assumption A7) |

### What stays from the paper (still binding)

- Companies do not self-register. Company Records are created and maintained by the OC (COC is read-only unless the team decides otherwise).
- Existing Partner vs New Company distinction. A New Company never auto-converts; only an authorized OC approval does.
- Recommendations are **for opportunities, never for ranking students**. No match score may be shown or used to sort applicants anywhere.
- Accepted and Placed are different states. A student may apply to many opportunities but can have only **one active placement**.
- In-system notifications only. No email, no in-app chat.
- Full attendance/DTR, payroll, performance evaluation are out of scope. Weekly reports are supported only as uploaded documents plus a status indicator.
- The system supports human decisions; it never makes the formal accept/reject, verification, or placement decision by itself.

## 2. Non-negotiable rules

- Stack: Laravel 10 (PHP 8.3), Laravel Breeze (Blade), Tailwind 3 via Vite, Alpine.js, PostgreSQL 16 hosted on Neon (the team already has a Neon database). No SPA frameworks, no Livewire/Inertia unless the user asks. Roles via an enum column on `users` plus Policies and Gates, not a permissions package.
- Never invent institutional facts. The formal verification checklist, MOA stage order, and retention policy are unanswered; model them as **configurable data**, not hard-coded rules.
- Use synthetic seed data only. Never put real student or company data in the repo or the deployed demo.
- Never commit secrets. `.env`, tokens, DB credentials, and `APP_KEY` live only in local `.env` and Vercel environment variables.
- Every state change that matters writes an audit log row. No hard deletes anywhere (soft delete + archive).
- Server-side authorization on every route. Hiding a button is not authorization.
- Do not fabricate references, statistics, or citations in any documentation you write.

## 3. How to work

1. **Read first.** Before writing code, read all files in `references/`. They are short.
2. **Plan, then wait.** Produce an implementation plan artifact (phases, tables, routes, risks, assumptions). **Stop and wait for approval** before Phase 1.
3. **Assumption log.** When the spec is silent, choose the simplest reasonable option, record it in `docs/ASSUMPTIONS.md` as `A<n>: decision, reason, how to change it`, and continue. Do not stall on questions that have a safe default. Ask the human only for the checkpoints in section 5.
4. **Vertical slices.** Each feature ships migration + model + policy + form request + controller + Blade views + tests + feature doc, together.
5. **Commit discipline.** One commit per coherent unit, Conventional Commits (`feat:`, `fix:`, `docs:`, `test:`, `chore:`). Tag the end of each phase `phase-N`. Work on `main` unless the user asks for branches.
6. **Verify, don't claim.** Run `php artisan test` and `npm run build` before declaring any phase done. Report real output. If something fails and you cannot fix it, say so plainly.
7. **Docs as you go.** Follow `references/docs-standard.md` exactly. A feature without its doc is not done.

## 4. Phases and exit criteria

**Phase 0: Plan.** Read references, produce the implementation plan, create `docs/ASSUMPTIONS.md`. Exit: human approves plan.

**Phase 1: Foundation and walking skeleton deploy.**
Scaffold Laravel 10, Breeze Blade, Tailwind theme tokens, app shell layout (sidebar + topbar, role-aware nav), light/dark/system theme, responsive behavior, `users.role` enum, seeders, `/health` route, GitHub Actions test workflow, then push to GitHub and **deploy this empty-but-real skeleton to Vercel** (read `references/deployment.md`). Deploying early surfaces the biggest risk (PHP on Vercel) while it is cheap to fix.
Exit: login works on the live Vercel URL against the Neon PostgreSQL database.

**Phase 2: Accounts, RBAC, audit, archive, document service.** Build the shared `DocumentService`, direct-to-bucket upload flow, and dropzone component from `references/files-and-documents.md` (all later phases reuse it). Student registration with OC approval, provisioned accounts for COC/UA/PC, forced password change, OC-initiated password reset, policies for all five roles, audit log, global archive pattern (trait + archive browser).
Exit: policy tests prove each role can reach only its pages (including direct-URL attempts), and the role-by-category upload/download matrix test passes against a real bucket.

**Phase 3: Companies and verification.** Company Records, Existing Partner / New Company classification, configurable verification checklist, document uploads to the private disk, student "suggest a company" form.
Exit: OC can take a New Company from record to verified to Existing Partner; the transition is audited and cannot happen automatically.

**Phase 4: MOA tracking and legal notes.** Multi-party MOA records with custody transfers in any order, version uploads, UA notes, COC read access for assigned students.
Exit: a MOA can travel OC → UA → student → company → OC with full history and no enforced order.

**Phase 5: Opportunities, skills, matching, directory.** Standard skills list, student skills, opportunities with required/preferred skills, student directory/search/filter, transparent match engine with unit tests.
Exit: match tests pass for the cases in `references/product-spec.md` section 6; no ranking of students exists in any query.

**Phase 6: Applications, placements, notifications.** Full application lifecycle, student confirm + OC confirm placement, auto-withdraw of other applications, in-system notifications.
Exit: concurrency-safe single active placement per student, proven by a test.

**Phase 7: Dashboards, tasks, reports, document library.** Document library with filters, bulk actions, completeness views, and ZIP bundles.  Role dashboards, "students without a company" widget, OC-to-COC task assignment, student-to-COC assignment, basic reports with CSV export.
Exit: each role's landing page shows only its permitted data.

**Phase 8: Hardening and documentation.** Security checklist from `references/security.md`, accessibility pass, empty/error states, README, feature doc index complete, traceability matrix (FR/BR → routes → tests).
Exit: `php artisan test` green, `npm run build` clean, `composer audit` and `npm audit` reviewed, docs index complete.

**Phase 9: Final deploy and handoff.** Redeploy, run migrations and demo seeder against the remote DB, smoke-test every role on the live URL, write `docs/DEPLOYMENT.md` (including teardown) and a walkthrough artifact with screenshots.
Exit: live URL works for all five demo roles; handoff summary lists known gaps honestly.

## 5. Human checkpoints (stop and ask)

- Plan approval (end of Phase 0).
- Creating the GitHub repository and authenticating `gh` or git, if not already done. Never ask the human to paste tokens into chat or files.
- Confirming the Neon connection strings (pooled and direct) and creating the S3-compatible storage bucket, and supplying credentials through environment variables.
- `vercel login` / `vercel link` and adding environment variables in the Vercel project.
- Any change that would alter a rule marked "still binding" in section 1.

## 6. Reference map

| File | Read when |
|---|---|
| `references/product-spec.md` | Always. Roles, permissions, state machines, matching rules, FR/BR list |
| `references/data-model.md` | Writing migrations and models |
| `references/ui-ux.md` | Writing any Blade view, layout, or style |
| `references/security.md` | Any auth, upload, policy, or hardening work |
| `references/files-and-documents.md` | Any upload, download, document list, MOA file, resume, report, or bundle work |
| `references/deployment.md` | Phase 1 and Phase 9, or any GitHub/Vercel problem |
| `references/docs-standard.md` | Writing any documentation |

## 7. Definition of done (every phase)

Tests green; build clean; feature docs written; assumptions logged; audit log entries in place for state changes; no secrets in git history; committed and tagged; short report to the human stating what was built, what was verified, and what remains uncertain.
