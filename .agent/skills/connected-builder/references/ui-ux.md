# UI / UX Standard

Stack: Blade + Tailwind 3 (Vite) + Alpine.js. No CDN dependencies at runtime (self-host fonts with `@fontsource`, icons as inline SVG via a Blade component set such as Heroicons). Follow the `frontend-design` skill if available: pick a deliberate look, avoid generic template-looking output.

## Design direction

Institutional but modern: calm, data-dense where staff work, friendly and guided where students work. Restrained color, strong typography, generous whitespace, no decorative clutter. Palette is drawn from the BSAU/ICS logo (teal, orange, warm grey). Frontend owners on the team (Ishen, Lawrence) may refine tokens later, so **all colors, radii, and spacing must come from CSS variables / Tailwind theme tokens**, never hard-coded in views.

## Tokens

Define in `resources/css/app.css` as CSS variables and map them in `tailwind.config.js` (`darkMode: 'class'`).

| Token | Light | Dark | Use |
|---|---|---|---|
| `--bg` | #F6F7F8 | #0E1417 | page background |
| `--surface` | #FFFFFF | #151D21 | cards, tables |
| `--surface-2` | #EEF1F2 | #1C262B | subtle panels, table header |
| `--border` | #DDE2E5 | #2A363C | dividers |
| `--text` | #14202A | #E8EEF1 | body text |
| `--text-muted` | #5B6B76 | #93A4AF | secondary text |
| `--primary` | #0F7C80 | #2BB3B8 | buttons, links, active nav |
| `--primary-contrast` | #FFFFFF | #07282A | text on primary |
| `--accent` | #C2410C | #FB923C | highlights, important CTAs, notifications dot |
| `--success` | #15803D | #4ADE80 | verified, placed |
| `--warning` | #B45309 | #FBBF24 | pending, expiring |
| `--danger` | #B91C1C | #F87171 | rejected, destructive |
| `--info` | #1D4ED8 | #60A5FA | informational |

Check contrast: body text and interactive text must reach WCAG AA (4.5:1). Verify the primary/accent pairings in both themes before finishing Phase 1. Orange is for accents and large text; use the darker `--accent` value when it carries small text.

Typography: headings Poppins (600), body Inter (400/500), both via `@fontsource`. Base 16px, line-height 1.5, scale 12/14/16/18/24/30/36. Tabular numbers for counts and dates in tables.

Shape and space: radius 8px (cards 12px), 4px spacing scale, soft shadow only in light mode; dark mode relies on borders and surface steps.

## Theme behavior

Three-state toggle: System (default), Light, Dark. Persist the choice in `localStorage` and apply the `dark` class from a tiny inline script in `<head>` **before** first paint to avoid a flash. Respect `prefers-color-scheme` when in System mode and react to changes. The toggle lives in the topbar and in guest layouts.

## Layout and responsiveness

Mobile-first. Breakpoints: base (under 640px, phones), `sm` 640, `md` 768 (tablets), `lg` 1024 (laptops), `xl` 1280.

- **Desktop (lg+)**: fixed left sidebar (role-aware), topbar with search, notification bell, theme toggle, user menu.
- **Tablet (md)**: collapsible sidebar (icon rail or drawer), two-column content where useful.
- **Phone**: sidebar becomes a drawer; primary actions stay reachable; tables become stacked cards with the key fields and a "details" action; forms are single column; tap targets at least 44px; sticky primary action bar for long forms.
- No horizontal page scroll at any width. Wide tables scroll inside their own container only when a card layout is not suitable.
- Test at 360, 768, 1024, and 1440 px widths in both themes, and attach screenshots to the Phase 1 and Phase 8 walkthroughs.

## Navigation per role

- **OC**: Dashboard, Students, Companies, Verification, MOAs, Opportunities, Applications, Placements, Tasks, Co-Coordinators, Skills, Reports, Archive, Audit Log, Settings.
- **COC**: Dashboard, My Students, My Tasks, MOAs, Placements, Notifications.
- **UA**: Dashboard, MOAs (queue), Notes, Notifications.
- **PC**: Dashboard, Company Profile, Opportunities, Applicants, Notifications.
- **Student**: Dashboard, Profile and Skills, Directory (Existing Partners), Recommendations, My Applications, Placement, Suggest a Company, Notifications.

## Components to build once and reuse

App shell, page header (title, breadcrumbs, actions), card, stat tile, data table with sort/filter/pagination and mobile card mode, status badge (single component mapping every status to color + icon + text so color is never the only signal), form fields with inline errors, file dropzone with type/size hints, skill chip input with typeahead, stepper (application and MOA timeline), timeline (audit and custody history), empty state, confirm modal for consequential actions, toast, skeleton loader, notification dropdown, theme toggle.

Status badge mapping lives in one PHP/Blade place and covers: verification, classification, MOA, application, placement, task, opportunity statuses.

## UX rules

- Every list has loading, empty, and error states with a next action.
- Destructive or consequential actions (accept, reject, confirm placement, promote to Existing Partner, revoke verification) require a confirm modal that states the effect, and ask for a remark where the spec requires one.
- Show *why* things are the way they are: match results show matched and missing skills; a New Company shows a plain-language note that application happens outside the system.
- Labels in plain language (for example "Waiting for your confirmation", not "accepted_pending_student").
- Forms validate server-side and re-render with old input and field-level messages; add light client hints only.
- Accessibility: semantic HTML landmarks, labels tied to inputs, visible focus ring, keyboard-operable modals and menus, `aria-live` for toasts, alt text, respect `prefers-reduced-motion`.
- Keep motion minimal: short transitions on hover/focus, drawer, modal.
- Dates in `Asia/Manila`, format like "16 Oct 2026". Numbers with thousands separators.

## User experience workflow documentation (Princess's docs, frontend side)

Create `docs/frontend/ux-workflows.md` with, per role, the primary journey as a numbered step list plus a Mermaid flowchart (login to goal), and `docs/frontend/ui-references.md` listing the design principles above and any reference screens the team adds later. Do not invent reference links; leave a clearly marked "Team to add" section.
