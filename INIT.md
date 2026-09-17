# INIT.md — DhakaGrid Architecture Reference

> **This is the canonical project reference.** Read this file in full before starting any task. It is re-read and followed for every future change. Concurrent docs: [`DESIGN.md`](DESIGN.md) (UI/design system) and [`AGENTS.md`](AGENTS.md) (agent operating rules).

## 1. Project Overview

**DhakaGrid** (formerly GovConnect) is a city-management and emergency-response ecosystem connecting **citizens**, **response teams** (fire / police / medical / government), and **admins** in Dhaka.

Feature surface:

- Geospatial incident reporting (Leaflet map picker + reverse geocoding)
- One-click **SOS** with real GPS capture
- Media evidence uploads on reports
- Gamified citizen engagement (`XP` levels; "City Watcher" → "Grid Guardian")
- Government notices / warnings
- Moderation: report verify/reject/assign, user ban/unban with appeals
- Response-team member-capacity coupling (`total_members` / `busy_members`)
- Notifications (built on the `logs` table)

## 2. Tech Stack

| Layer | Choice | Notes |
| :--- | :--- | :--- |
| Language | PHP **>= 8.1** | Uses modern features (`str_contains`, typed params, null coalescing) |
| DB access | PDO + MySQL/MariaDB | Prepared statements only. MySQL 10.4+ / MariaDB shown in dev |
| Autoloading | Composer, **PSR-4** (`App\` → `src/`) | No framework. Only `composer` dev dep is PHPUnit |
| Front-end | Vanilla JS + CSS custom properties | No build step, no npm, no framework |
| Mapping | Leaflet + OpenStreetMap/Carto tiles | Tile language consistent with the light theme |
| Charts | Chart.js | Doughnut for categories; line for trends |
| Fonts/icons | Google Fonts (Outfit), Font Awesome 6 | CDN |

**Hard rule:** no new runtime dependencies without an explicit decision from the project owner. PHPUnit (dev) is pre-approved.

## 3. Repository Layout

Target structure (current status in §13):

```
├── public/                      # (FUTURE — Option B, out of scope now; see §11)
├── src/
│   ├── Core/                    # bootstrap, Config, Session, Auth, Redirect, View
│   ├── Http/                    # request/response helpers, CSRF token helpers
│   ├── Services/                # domain services: Report, Sos, Stats, News, Notification, Moderation, TeamCapacity
│   ├── Repositories/            # data access: ProblemRepository, UserRepository, FeedbackRepository, WarningRepository, UnbanRepository
│   └── Dto/                     # plain data structures passed to views
├── assets/
│   ├── css/                     # (login/signup-only stylesheets — OUT OF SCOPE UI)
│   ├── app/                     # DESIGN.md system: tokens.css, base.css, components.css, utilities.css
│   └── js/                      # core.js + per-screen modules
├── templates/                   # shared partials (app_header, app_footer, sidebar, flash, cards, map_card)
├── config/                      # config/env.php (or .env) — credentials NEVER hardcoded
├── *.{php}                      # top-level entry "controller scripts" (thin only) — see §5
├── docs/                        # generated notes, DB schema reference
├── tests/                       # PHPUnit (Unit/ and Integration/)
├── vendor/                      # composer (gitignored)
├── composer.json
├── DESIGN.md / INIT.md / AGENTS.md
```

## 4. Environment & Setup

### 4.1 Prerequisites
- PHP >= 8.1 with `pdo_mysql`
- MySQL/MariaDB server
- Composer
- Network access (Leaflet tiles, Nominatim reverse geocoding, Open-Meteo, CDNs)

### 4.2 Dev database
- Database name: `g1`
- Tables: `users`, `problems`, `feedbacks`, `logs`, `warnings`, `unban_requests`, `deleted_problems`
- Seed dev accounts (documented in `AGENTS.md`): admin, citizen, response-team demo users.

### 4.3 Config
Credentials live in `config/env.php` (loaded via `src/Core/Config`). Never store passwords in code or commit them.

### 4.4 Running locally
```
composer install
php -S 127.0.0.1:8080 -t "D:\Projects\Gov_Connect-DhakaGird-"
```

### 4.5 Tests
```
vendor/bin/phpunit
```
Tests run on every phase exit gate (`AGENTS.md`).

## 5. Request / Data Flow

Current model: **top-level controller scripts** (preserved URLs), thin. All non-trivial logic lives in `src/`.

```
HTTP request
  → entry script (user_dashboard.php, admin_dashboard.php, …)
    → bootstrap (session, config, auth guard via src/Core/Auth)
    → Service layer (domain rules: validation, geocoding, member capacity, XP, notifications)
      → Repository layer (SQL via PDO prepared statements ONLY)
    → View assembly (templates/ partials + DESIGN.md components)
  → HTML response
```

Rules:
- Controller scripts contain: `require bootstrap`, role guard, argument extraction, service calls, `View::render()`.
- Views NEVER contain SQL, business rules, or raw user data without escaping.
- Repositories are the only place SQL lives. SQL is always prepared. No `->query()` with interpolated user input.
- Services own transactions and multi-step writes (e.g. SOS insert + log insert).

## 6. Conventions

### 6.1 PHP
- PSR-12 style, `App\` namespace, PSR-4 autoload.
- No raw superglobals inside services — values are passed in (HTTP concerns live in entry scripts / Http helpers).
- All public endpoints declare their role guard: `Auth::requireRole('user')` etc.

### 6.2 Roles & auth
Roles: `user | response | admin`. Sessions hold `user_id`, `role`, `name`. `dashboard.php` routes by role to the three dashboards. Do not open the platform gate further: ban status blocks login at the DB/SSO check.

### 6.3 Flash & toast
One system: session flash → normalized toast partial. The legacy four-system mess (`flash_success` / `success` / `?submitted=` / JS `alert()`) is being removed — **no new `alert()`/`confirm()`**.

### 6.4 Status & priority enums (magic-string constants)
Define once in `src/Core/Enums` (PHP 8.1 enums) and reference everywhere — never bare strings in SQL or markup:
- Problem status: `pending | verified | assigned | working | resolved | rejected`
- Priority: `low | medium | high | sos`
- User status: `pending | active | rejected`
- User role: `user | response | admin`

### 6.5 Output escaping
Escape at render time (`htmlspecialchars`, `e()` helper for echo). No raw `$_POST`/DB values in markup. JS-string contexts use a JSON-safe encoder, never `addslashes`.

### 6.6 File uploads
Allowlist by extension AND MIME; size cap; store under gitignored dirs (`uploads/`, `profile_pics/`, `problems/`); never trust the client filename for the stored path.

### 6.7 Mapping & geocoding
Dhaka bounds constant `23.65–23.90 / 90.30–90.55` defined **once**. Reverse geocoding via Nominatim lives in one Geocode service (currently duplicated in 3+ files — dedupe). A shared marker-color legend is defined in DESIGN.md and used by every map.

## 7. Design System Boundary

- `assets/css/*` and the login/register pages are the **owner's exclusive custom UI** — OUT OF SCOPE: do not analyze, reference, extend, or reuse it in dashboards.
- All dashboard screens (citizen, admin, response and their profile/support pages) use **one design system** defined in `DESIGN.md` and implemented under `assets/app/`.
- The legacy purple admin/response UI is **being replaced outright**; zero residue (grep gate, §10). No theme toggle survives.

## 8. Roadmap (Phased Execution)

Each phase = **tasks**, each task = one GitHub Issue → one branch → one squash-merged PR (details in AGENTS.md). Exit gates must pass before the next phase/task begins.

| Phase | Tasks (abridged) | Exit gate |
| :--- | :--- | :--- |
| **P1 Foundations** | `01.1` composer+autoload · `01.2` config/env · `01.3` bootstrap/Session/Auth/Redirect · `01.4` `.gitignore` + junk-file removal · `01.5` SOS single path · `01.6` PHPUnit infra & smoke tests · `01.7` smoke gate (lint, tests, 3-role login 200) | composer install clean · full `php -l` · tests green · all roles login |
| **P2 Design system** | `02.1` tokens · `02.2` base ariables · `02.3` components · `02.4` core.js · `02.5` inventory page (`design.php`) | inventory page passes DESIGN.md checklist · dashboards link `assets/app/` with no markup regressions |
| **P3 Citizen dashboard** | `03.1` repos+services · `03.2` app shell (topbar+sidebar) · `03.3` activity table (paged/sort/filter + states) · `03.4` analytics/KPIs · `03.5` notifications from `logs` · `03.6` full restyle (XP card reborn, no shimmer/pulse) · `03.7` support pages adopt shell | service unit tests · DESIGN.md checklist · manual smoke on seed data |
| **P4 Admin & response migration** | `04.1` admin data (pagination/search, moderation service, unban consolidated, feedback trio merged) · `04.2` admin screens restyled · `04.3` response pipeline + consistent map legend + toasts · `04.4` purple purge gate | zero legacy purple tokens in `grep` · DESIGN.md checklist · full 3-role smoke |
| **P5 Polish & docs** | `05.1` a11y · `05.2` responsive (mobile sidebar overlay) · `05.3` motion rules + kill remaining alert/confirm · `05.4` finalize docs, README rewrite, fresh screenshots | a11y checklist · mobile smoke · README matches reality |

Gamification is Tier-3 (last defended): notifications-on-real-data and analytics/KPIs take priority if time runs short.

## 9. Non-Negotiable Working Rule (Owner Mandate)

**Whenever a decision has multiple reasonable options (architecture, library, feature scope, UI treatment, naming) — STOP and ask the owner with explicit options.** Never guess or silently pick a default. Genuinely unambiguous bug fixes proceed without asking.

## 10. Quality Gates

1. `php -l` on every modified file.
2. `vendor/bin/phpunit` green (services & repositories have unit coverage).
3. DESIGN.md conformance ran against the screen(s) touched.
4. Manual smoke: log in as each role; exercise the changed flows.
5. `grep` gate for legacy purple tokens (`#667eea`, `#764ba2`, `#f3e8ff`, `--accent2`, …) after P4.

## 11. Explicitly Out of Scope (documented, not built)

**Option B — Front controller / clean URLs.** A future path: move all entry points under `public/`, single `public/index.php` router with `GET/POST /dashboard`, `/admin/problems`, etc. Deferred deliberately:
- Current URLs are preserved by design (Option A, locked) to keep the app bootable at every step.
- Introducing the router now would duplicate routing and slow the design-system work without functional gain.
- If adopted later, it is a mechanical phase: repoint entry scripts to route definitions + add static asset serving; test surface already exists.

Also out of scope: dark mode (documented stretch add, only after Phase 5 — never a blocker), real-time websockets, chat, third-party auth.

## 12. Data Model Notes

- `problems`: `user_id`, `category`, `description`, `suggestion`, `location/location_name`, `lat/lng`, `status`, `priority`, `assigned_to`, `working_members`, `media_path`, `report`, timestamps, `deleted_by_admin` soft-delete flag.
- `deleted_problems` is an **audit snapshot** table used by moderation views (soft-delete keeps `problems` intact).
- `logs`: notification/journal rows with `notification_type` — the foundation for the notifications feature (P3.5).
- `feedbacks`: per-problem ratings + comments (joined for admin card).
- `warnings`: broadcast/notice table (`user_id IS NULL` = citywide) + targeted warnings.
- `unban_requests`: appeal workflow (consolidated into a single flow in P4).

## 13. Migration Status (living table — update as work lands)

| Area | Status |
| :--- | :--- |
| Baseline commit (auth/password fixes, login/register styling) | ✅ `097d389` → folded into squash #5 |
| Phase 0 analysis | ✅ |
| INIT.md / DESIGN.md / AGENTS.md | ✅ |
| P1 Foundations | ✅ `01.1` #9 · `01.2` #11 · `01.3` #13 · `01.4` #15 · `01.5` #17 · `01.6` #19 · `01.7` smoke gate ✔ |
| P2 Design system | pending |
| P3 Citizen dashboard | pending |
| P4 Admin & response migration | pending |
| P5 Polish & docs | pending |