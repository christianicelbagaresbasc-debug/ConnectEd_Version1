# ConnectEd UI / UX Design System & Tokens

**Style Direction:** Modern institutional, high data density for coordinators, clean card feeds for students, inspired by LinkedIn's structured professionalism.

---

## 1. Color Tokens (CSS Variables)

Defined in `resources/css/app.css` and mapped in `tailwind.config.js`:

| Token | Light Value | Dark Value | Purpose |
|---|---|---|---|
| `--bg` | `#F6F7F8` | `#0E1417` | Main page canvas background |
| `--surface` | `#FFFFFF` | `#151D21` | Cards, modals, sidebars, tables |
| `--surface-2` | `#EEF1F2` | `#1C262B` | Table headers, muted panels, chips |
| `--border` | `#DDE2E5` | `#2A363C` | Subtle dividers and card borders |
| `--text` | `#14202A` | `#E8EEF1` | Primary readable text (WCAG AA) |
| `--text-muted` | `#5B6B76` | `#93A4AF` | Secondary labels, timestamps, hints |
| `--primary` | `#0F7C80` | `#2BB3B8` | Brand teal: primary buttons, active tabs |
| `--primary-contrast` | `#FFFFFF` | `#07282A` | High-contrast text on primary |
| `--accent` | `#C2410C` | `#FB923C` | BSAU orange: notification dots, CTAs |
| `--success` | `#15803D` | `#4ADE80` | Verified status, active placements |
| `--warning` | `#B45309` | `#FBBF24` | Pending approval, expiring items |
| `--danger` | `#B91C1C` | `#F87171` | Rejected items, destructive actions |
| `--info` | `#1D4ED8` | `#60A5FA` | Announcements, informational tags |

---

## 2. Typography

- **Headings:** Poppins (Semi-bold 600) self-hosted via `@fontsource/poppins`.
- **Body & Data:** Inter (Regular 400, Medium 500, Semi-bold 600) self-hosted via `@fontsource/inter`. Tabular figures used for numbers and timestamps.
- **Scale:** Base 16px, line-height 1.5. Sizes: 12px (micro), 14px (body/table), 16px (subhead), 18px (card title), 24px (h2), 30px (h1), 36px (hero).

---

## 3. Layout & Responsiveness

- **Mobile (<640px):** Single column card stacking, bottom or topbar action access, sidebar transforms into an off-canvas slide-out drawer with backdrop blur.
- **Tablet (640px–1024px):** Collapsible rail navigation, 2-column card grids.
- **Desktop (>1024px):** Fixed left 256px sidebar, sticky topbar with global search and notification indicator, 3-column dashboard grid.

---

## 4. Theme System

- **Zero-FOUC Implementation:** An inline script runs synchronously inside `<head>` before CSS parsing to inspect `localStorage.getItem('connected_theme')` and `window.matchMedia('(prefers-color-scheme: dark)')`, applying `.dark` immediately without white/dark flashes.
- **3-State Toggle:** Built using Alpine.js in `resources/views/components/theme-toggle.blade.php`.
