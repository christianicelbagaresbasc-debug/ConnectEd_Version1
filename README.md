# ConnectEd Builder Kit

What this is: a skill + always-on rules + kickoff prompt that direct Google Antigravity to build ConnectEd, push it to GitHub, and deploy a temporary demo to Vercel.

## Contents

```
AGENTS.md                                   always-on project rules (project root)
KICKOFF_PROMPT.md                           the prompt to paste into Antigravity
.agent/skills/connected-builder/
  SKILL.md                                  workflow, 10 phases, guardrails
  references/product-spec.md                roles, permissions, state machines, matching, FR/BR
  references/data-model.md                  tables and seeders
  references/ui-ux.md                       tokens, light/dark, responsive, components
  references/security.md                    security and privacy checklist and tests
  references/files-and-documents.md         upload/download roles, bulk upload, library, bundles
  references/deployment.md                  GitHub + Vercel (Docker/FrankenPHP) + fallbacks
  references/docs-standard.md               documentation format (feature doc template)
```

## Install

1. Create an empty folder for the project and open it in Antigravity.
2. Copy this kit's contents into the folder root (keep the `.agent/` folder name exactly).
3. Copy the old paper to `docs/source/ConnectEd_Final_Paper.pdf` (optional, background only).
4. Restart the agent session so it detects the skill.
5. Paste the prompt from `KICKOFF_PROMPT.md`.

Skills can also be installed globally in `~/.gemini/antigravity/skills/`, but keeping it in the repo means teammates and the deployed repo carry the same spec.

## Decisions the team should confirm (the agent will assume defaults and log them)

1. **Partner Company (PC) and ICS Student sections of the brief are blank.** The skill defines both roles from the paper plus sensible defaults (A1, A2).
2. **Laravel 10** is kept because the brief says so, but it is past its upstream security-support window. Fine for a capstone demo; plan an upgrade if it will run for real.
3. **Program scope**: the brief says "ICS Students", the paper says BSIT only. Config value, default BSIT (A7).
4. **Opportunity approval**: PC-posted opportunities wait for OC approval (A5).
5. **Placement auto-withdraws a student's other applications** (A6).
6. **No email means no email password reset.** OC resets passwords (A8).
7. **Verification checklist and MOA stage order are unknown**, so they are configurable data.
8. **Matching scores are visible only to the student**, never to staff or companies, to honor the "no ranking students" rule.

## Things only humans can do

Create the GitHub repository, the Neon connection strings (pooled and direct), the private S3-compatible bucket, log in to Vercel, and add environment variables. The skill tells the agent to stop and ask at exactly these points.

## Honest risks

- Vercel's container support for PHP is new (official guide dated September 2026). Phase 1 deploys a skeleton first to test this. Fallbacks are in `deployment.md`.
- Agent-built systems need your review. Run the tests, click through every role, and read the audit and permission code before presenting to a panel.
