# DESIGN.md — DhakaGrid Design System

> Single source of truth for every **dashboard** screen (citizen, admin, response, and their profile/support pages). Derived **only** from the elevated citizen dashboard UI (`user_dashboard.php`). The **login/signup UI is out of scope — never analyzed, referenced, or reused here.**

## 0. System Rules

1. One design language for all dashboard screens. No per-page re-declaration of tokens.
2. Implemented under `assets/app/` (`tokens.css`, `base.css`, `components.css`, `utilities.css`) + `templates/` partials.
3. **Legacy purple admin/response UI is being replaced outright.** After Phase 4 a grep gate must find **zero** raw legacy tokens (`#667eea`, `#764ba2`, `#f3e8ff`, `#ede9fe`, `--accent2`).
4. No theme toggle. Light theme only (dark mode = stretch add, only after Phase 5).
5. Motion is restraint: transition states only, no infinite decoration (the old SOS pulse / XP shimmer are gone).
6. Contrast targets WCAG AA (4.5:1 body, 3:1 large/bold).

## 1. Color Tokens

Base palette — the slate + blue scheme from the elevated citizen dashboard.

| Token | Hex | Use |
| :--- | :--- | :--- |
| `--bg` | `#F8FAFC` | page background |
| `--surface` | `#FFFFFF` | cards, topbar, sidebar, tables |
| `--surface-muted` | `#F1F5F9` | hover fills, code/badge wash, skeleton |
| `--surface-strong` | `#E2E8F0` | pressed fills, track bars |
| `--border` | `#E2E8F0` | default 1px borders |
| `--border-strong` | `#CBD5E1` | emphasized borders, inputs on hover |
| `--ink` | `#0F172A` | primary text, headings |
| `--ink-muted` | `#64748B` | secondary text |
| `--ink-faint` | `#94A3B8` | placeholders, meta, disabled |
| `--primary` | `#0F172A` | brand anchor (dark), solid buttons |
| `--accent` | `#3B82F6` | interactive accent, links, focus |
| `--accent-strong` | `#2563EB` | accent hover/active |
| `--accent-wash` | `#EFF6FF` | accent-tinted backgrounds |
| `--danger` | `#EF4444` | destructive, SOS |
| `--warning` | `#F59E0B` | warnings, pending |
| `--success` | `#10B981` | resolved, positive |
| `--info` | `#06B6D4` | working/in-progress states |

### Status map (problem lifecycle)

| Status | bg | fg | border |
| :--- | :--- | :--- | :--- |
| pending | `#FFF7ED` | `#9A3412` | `#FDBA74` |
| verified | `#EFF6FF` | `#1D4ED8` | `#93C5FD` |
| assigned | `#F5F3FF` | `#5B21B6` | `#C4B5FD` |
| working | `#ECFEFF` | `#0E7490` | `#67E8F9` |
| resolved | `#ECFDF5` | `#047857` | `#6EE7B7` |
| rejected | `#FEF2F2` | `#B91C1C` | `#FCA5A5` |

(Indigo/violet here is a status hue, not the legacy purple theme.)

### Priority map

| Priority | bg | fg | border | dot |
| :--- | :--- | :--- | :--- | :--- |
| low | `#F1F5F9` | `#475569` | `#E2E8F0` | `#94A3B8` |
| medium | `#FEF3C7` | `#92400E` | `#FDE68A` | `#F59E0B` |
| high | `#FFF7ED` | `#C2410C` | `#FDBA74` | `#F97316` |
| sos | `#FEF2F2` | `#B91C1C` | `#FCA5A5` | `#DC2626` + pulse only while active |

## 2. Typography

Font: **Outfit** (300–800). Base 16px, line-height 1.5, letter-spacing `-0.01em` on display/title.

| Token | Size | Weight | Used for |
| :--- | :--- | :--- | :--- |
| `--type-display` | 2.5rem | 800 | hero numbers, KPI values, empty-state icons |
| `--type-title` | 2rem | 800 | page title, dashboard headline |
| `--type-h1` | 1.5rem | 700 | section headers |
| `--type-h2` | 1.25rem | 700 | card titles |
| `--type-h3` | 1.05rem | 700 | card subheads, panel titles |
| `--type-body` | 0.95rem | 400–500 | default text |
| `--type-small` | 0.85rem | 400–600 | secondary text, table cells |
| `--type-caption` | 0.78rem | 600 `uppercase` `ls .06em` | labels, table headers |
| `--type-micro` | 0.72rem | 600 | timestamps, meta, badges |

Line lengths capped (~75ch) for description text; numbers use `font-variant-numeric: tabular-nums`.

## 3. Spacing, Radius, Shadow

**Spacing** — 4px grid: `4 8 12 16 20 24 32 40 48 64` (`--sp-1…--sp-10`). Section rhythm 32–40px; card internal padding **24px** (16px on compact rows); sidebar sections 24px.

**Radii** — `--radius-sm: 8px` (controls, pills-inset), `--radius-md: 12px` (inputs, buttons, badges), `--radius-lg: 16px` (cards, panels, topbar), `--radius-xl: 20px` (modals/drawers), `--radius-pill: 999px` (pills, avatars, media).

**Shadows** — `--shadow-flat: 0 1px 2px rgba(15,23,42,.05)` (default), `--shadow-card: 0 1px 3px rgba(15,23,42,.08)`, `--shadow-elevated: 0 8px 20px rgba(15,23,42,.10)` (hover, topbar), `--shadow-overlay: 0 24px 48px rgba(15,23,42,.18)` (modal/drawer). Default cards: surface + 1px `--border`, shadow `--shadow-card`.

## 4. Layout & Navigation

- **Topbar** — sticky, 72px, `--surface`, bottom `--border`. Left: mobile hamburger + page breadcrumb/title. Right: notifications bell, user chip (avatar + name + role), sign-out.
- **Slim sidebar** — 240px, `--surface`, scrollable, role-aware sections, item = icon + label, `border-radius: var(--radius-md)` hover `surface-muted`, active = `--accent-wash` bg + `--accent-strong` text + 3px left accent bar.
  - **Citizen:** Overview · Submit Report · My Activity · Profile
  - **Response team:** Dashboard · Assigned · Team Profile
  - **Admin:** Overview · Moderation · Approvals · Deleted (audit) · Find & Ban · Team Management
- **Mobile (≤ 900px):** sidebar → overlay drawer with backdrop (slide-in 250ms, focus must enter drawer, Esc closes); topbar action right-side collapses to icons.
- Content column: `max-width 1280px`, gutter 24px, `gap 24px` on grids.
- Footer: muted, `--type-small`, centered or inline row.

## 5. Components

### Buttons
| Variant | bg | text | border | Use |
| :--- | :--- | :--- | :--- | :--- |
| primary | `--primary` | #fff | – | main CTA |
| accent | `--accent` | #fff | – | primary interaction (submit, save) |
| outline | transparent | `--primary` | `--border-strong` | secondary action |
| ghost | transparent | `--ink-muted` | – | toolbar, low-emphasis (hover `surface-muted`) |
| danger | `--danger` | #fff | – | destructive, SOS |
| icon | ghost square, 36–40px | – | – | toolbar icon button |

Sizes: `sm` 32px h / `md` 40px h / `lg` 48px h. All `radius-md`, `font-weight 600`, `font 0.95rem`. Focus-visible ring. Disabled = `opacity .55, cursor not-allowed`. **SOS button:** `danger` filled + a subtle `box-shadow` pulse (0.8s, loop allowed *only* for this active-state affordance).

### Cards
- **Base card:** surface, 1px border, `radius-lg`, padding 24px, optional header row (h3 + actions) divided by `--border`.
- **Stat card:** big display value + caption label; optional delta chip (▲ ``` +` success / `▼ danger).
- **List row:** 16px padding, hover `surface-muted`, divided by `--border`, leading status/priority pill or icon.

### Badges / Pills
Status & priority pills use the §1 maps: `padding 4px 10px`, `radius-pill`, `type-micro 700`, border 1px of the map border. SOS pills may carry a `--danger` dot when active.

### Forms
- Inputs/selects/textareas: white, 1px `--border`, `radius-md`, padding 12px 16px, `--type-body`/600 weight on text. Hover → `--border-strong`; focus → accent border + 3px `--accent` focus ring (offset 2px); error → `--danger` border + red helper (16px, `0.85rem`); disabled → `surface-muted`.
- File input: drop-zone style card (dashed accent border) with preview thumbnails and per-file remove.
- Labels: `--type-small` 600 + required marker `--danger`; helper/meta `0.85rem` `--ink-faint`.
- Selects styled with custom chevron; option list inherits theme.

### Tables
- Header row: `--type-caption`, `surface-muted`.
- Rows: hover `surface-muted`, 1px `--border` dividers. Cells pad 12–14px 16px.
- Row actions: icon ghost buttons + contextual menu.
- Sortable/filterable headers (P3+): clickable, text `--accent-strong` when active, arrows `--ink-faint`.
- Pagination footer: page chips + count caption; loading state = dimmed table + skeleton rows.
- Empty state: centered icon (44px, `--ink-faint`), title `h2`, hint `small`, optional CTA.

### Modal & Drawer
- Overlay: `rgba(15,23,42,.45)` + blur 4px; focused-scoped content; Esc/top-right X close; `radius-xl`, `padding 28px`, `shadow-overlay`.
- Modal for confirmations/destructive (danger intent: `danger` confirm button). Drawer (right, 420px) for detail panes.

### Toasts
- Stack bottom-right; auto-dismiss 4s; error persists until closed. Icon + message + close. `success`/`error`/`info` via semantic maps; `--shadow-overlay`, 1px border, `radius-md`. Accessible via `role="status"`/`alert`.

### Tabs (admin)
Underline style: active = `--accent-strong` text + 3px accent bottom bar; scrollable on overflow (`overflow-x:auto`, thin scrollbar).

### Map card
- Card chrome identical to others; controls visually flat; fixed aspect (e.g. 400–460px); a shared **legend chip row** (always-on) mapping marker color → meaning, identical across all three dashboards:
  - `--danger` dot = SOS · `--warning` = high/active · `--accent` = assigned · `--success` = resolved · `--ink-faint` = pending/low

### Notice / broadcast
Simplified to a bordered banner card (left accent bar 4px `--warning`), title row + body; not a different "card family".

### Activity / ticker
Replaced by an **activity feed** (list rows, `aria-live="polite"`, newest first) or a static summary strip — no auto-rotating ticker. If a live strip is kept, it must be `aria-live` and motion-gated.

### XP / gamification card
Uses the standard card + stat-row components: avatar chip, name, rank pill, progress bar (track `--surface-strong`, fill gradient **only** accent→indigo, static — no shimmer), stats row. This is a *composition*, not a new component.

## 6. Motion Rules

| Intent | Duration | Curve |
| :--- | :--- | :--- |
| micro (hover, focus, color) | 150ms | ease-out |
| standard (show/hide, accordion) | 250ms | `cubic-bezier(.4,0,.2,1)` |
| large (modal, drawer, sidebar) | 350ms | `cubic-bezier(.4,0,.2,1)` |

- Animate `opacity`/`transform`, never layout-affecting props except where unavoidable.
- Infinite loops: reserved for the active SOS affordance only.
- Honour `prefers-reduced-motion: reduce` → disable all non-essential animation/loops.

## 7. Accessibility Baseline (all screens)

- Focus: visible `--accent` ring on every interactive control.
- Contrast: AA per §0.6; status/pill pairs pre-approved by the maps above.
- Semantics: landmarks (`header/nav/main/footer`), one `h1` per page, headings hierarchical, tables with `th scope`.
- Live regions: toasts `role=status`; feeds `aria-live=polite`; alert-type errors `role=alert`.
- Modals/drawers: focus trap, return focus to trigger, `aria-modal + labelledby`.
- Touch targets ≥ 40px; keyboard-operable menus/sort/tabs; `alt` on all images (map markers get a text legend thanks to the legend chip row).

## 8. Applying to Admin & Response (migration rules)

- Replace headline/theme entirely (no dark gradient, no theme toggle, no purple accents).
- Same topbar + sidebar shell, same tokens, same components.
- Admin tabs → tabs component; moderation/problem lists → tables component; deleted-complaints → table; unban flow → one screen.
- Response: stacking cards → **priority pipeline** columns (SOS → High → Medium → Low) using the priority map; member-capacity guards stay but errors surface as toasts, not `alert()`.
- Both: consistent map legend (§5), no emoji-only actions (✅ ❌ replaced by icon+text buttons from the button matrix).

## 9. Component Inventory Checklist

Used for the P2 inventory page (`design.php`) and as the per-PR conformance checklist:

- [ ] Colors: every raw hex in screens resolves to a token (grep-able).
- [ ] Typography: only `--type-*` sizes used; caption style for table headers.
- [ ] Radii: only `--radius-*`; shadows only `--shadow-*`.
- [ ] Buttons: from matrix only; focus ring present; disabled handled.
- [ ] Cards: base/stat/list-row only; consistent 24px padding.
- [ ] Badges: status/priority maps only; no inline `background:#…`.
- [ ] Forms: all controls styled; labels + helper + error + disabled + focus.
- [ ] Tables: header/hover/pagination/empty; loading state present.
- [ ] Modal/drawer/toast: semantics + focus behavior.
- [ ] Shell: correct sidebar items per role; mobile drawer works; footer consistent.
- [ ] Map: legend chip row present on every map.
- [ ] Motion: matches §6; no infinite loops except SOS affordance; reduced-motion honoured.
- [ ] No legacy purple tokens anywhere (grep gate).