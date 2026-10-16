# AGENTS.md: ConnectEd

ConnectEd is the BSAU (Bulacan State Agricultural University, Institute of Computer Studies) skill-based OJT placement and company verification system. It is an undergraduate capstone that must become a real, working, deployed system.

**Always use the `connected-builder` skill** (`.agent/skills/connected-builder/SKILL.md`) for any work in this repository and read its `references/` before coding.

## Stack (fixed)
Laravel 10 on PHP 8.3, Laravel Breeze (Blade), Tailwind 3 + Vite, Alpine.js, PostgreSQL 16 on Neon. Hosting target: GitHub + Vercel (container/FrankenPHP).

## Roles
OC (OJT Coordinator), COC (Co-OJT Coordinator), UA (University Attorney), PC (Partner Company), Student (ICS Student).

## Hard rules
- Companies never self-register. A New Company becomes an Existing Partner only by OC decision. No automatic conversion.
- The system recommends opportunities to students. It never ranks or scores students for companies. Match scores are visible only to the student concerned.
- Humans make formal decisions (accept/reject, verify, place). The system records them.
- Nothing is hard-deleted. Archive with soft deletes and audit every important state change.
- In-system notifications only. No email, chat, attendance/DTR, or payroll.
- Server-side authorization on every route. Synthetic data only. No secrets in git.
- Do not invent institutional policy, citations, or statistics. Unknown BSAU details are configurable data and are logged in `docs/ASSUMPTIONS.md`.
- Every feature ships with tests and a feature doc in the format of `references/docs-standard.md`.
- Run `php artisan test` and `npm run build` before saying a phase is done, and report real results.

## Working style
Plan first and wait for approval, then build in phases with small Conventional Commits. Stop at the human checkpoints listed in the skill.
