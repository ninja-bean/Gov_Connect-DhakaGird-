# Architecture

Deep dive into how GovConnect DhakaGrid works under the hood. Written for engineers
onboarding to the codebase, and as living documentation kept in sync with the code.

## 1. System Overview

```
                    ┌────────────────────────────────────────────────┐
 Browser            │ Next.js 16 (App Router, Turbopack)             │
 ────── HTTP ─────► │                                                │
                    │  src/proxy.ts  (middleware):                   │
                    │   • session decrypt + role redirects           │
                    │   • rate limiting for /login, /register        │
                    │        │                                       │
                    │        ▼                                       │
                    │  Server Components  Server Actions  Routes     │
                    │  (pages, streaming)  (src/actions/)  (src/app/api)
                    │        │                                       │
                    │        ▼                                       │
                    │  Prisma Client ──────► MySQL (existing schema) │
                    └────────────────────────────────────────────────┘
```

All page data is fetched in Server Components and passed down as props. Mutations are
server actions. There are **no client-side data fetches** for first-party data; client
components are limited to interaction layers (maps, forms, theme, confirmations).

## 2. Request Lifecycle

1. **Proxy (`src/proxy.ts`)** runs first. It:
   - decrypts the `session` cookie (HS256 JWT) if present;
   - redirects logged-in users away from guest-only pages (`/`, `/login`, `/register`,
     `/forgot-password`);
   - enforces role prefixes: `/admin` → `admin`, `/response` → `response`,
     `/dashboard` → `user`; missing session → `/login`, wrong role → the role home;
   - rate-limits credential POSTs (see §Security).
2. **Server Component** reads the resolved `session` and calls `db` (Prisma) directly, or
   re-verifies with `requireRole(...)` (duplicated check because `/api` is excluded from the
   proxy matcher for self-containedness).
3. **Server Action** (`src/actions/*`) re-authenticates (never trusts the caller), validates
   input with Zod, performs the mutation inside a transaction where needed, writes an audit
   entry, and returns a typed `ActionState` (`{ ok, message, errors?, … }`).
4. **Client** renders the result; forms use `useActionState`, and the response is fed back
   through the same `ActionState` shape via `fieldErrors`/`serverActionState` helpers.

## 3. Authentication & Authorization

- **Session**: a JWT (`{ userId, role, name }`) signed with `HS256` using
  `AUTH_SECRET` (`src/lib/auth/session-core.ts`). Cookie name `session`, TTL 7 days.
  The payload is validated on every proxy run and every protected page/action.
  `decrypt()` rejects tokens with a missing/incorrect signature or wrong shape.
- **Passwords**: bcrypt (cost 10) via `src/lib/auth/password.ts`. A platform password
  policy (≥8 chars, upper, lower, digit, symbol) is enforced on register and password
  change, surfaced as typed field errors.
- **Guards** (`src/lib/auth/guards.ts`): `requireSession()` and `requireRole(...)`
  redirect unauthenticated/unpermitted users.
- **Action safety**: server actions always re-check the session and role themselves —
  the proxy is an optimization, not the trust boundary.

## 4. Rate Limiting (Proxy)

In-memory sliding-window limiter keyed by client IP (`x-forwarded-for` first hop, else
`x-real-ip`):

| Endpoint | Limit |
| --- | --- |
| `POST /login` | 8 requests / 60 s / IP |
| `POST /register` | 4 requests / 60 s / IP |

On overflow, the proxy 307-redirects to `/login?flash=err&msg=…` and sets
`Retry-After: 60`. The login page renders the flash through the existing `Alert` UI.
The in-memory design is deliberately simple for a single-tier deployment; production
deployments are expected to add a CDN/edge limiter in front.

## 5. Database (Prisma + MySQL)

Schema lives in `prisma/schema.prisma` and maps to the original MySQL database. The
generated client is emitted to `src/generated/prisma` and is **never edited by hand**
(regenerate with `npm run prisma:generate`).

Key models:

| Model | Purpose |
| --- | --- |
| `users` | Citizens, response teams, admins. Role/status enums, banishment fields, team staffing counters. |
| `problems` | Complaints/SOS requests with location (name + lat/lng), status ⊆ `pending, verified, assigned, working, resolved, rejected`, priority, assignment, report, media, soft-delete flag. |
| `feedbacks` | Ratings + comments on resolved problems. |
| `logs` | **Audit trail** — every meaningful action writes here via `src/lib/audit.ts`. |
| `warnings` | Admin warnings sent to citizens. |
| `unban_requests` | Citizen appeals with review lifecycle. |
| `deleted_problems` | Archived snapshot of deleted complaints (denormalized, preserves history). |

**Quirks to respect:**
- `users.updated_at` **does not exist** in the live schema — never select/update it.
- `problems.updated_at` exists but is not exposed in mutations in this rewrite.
- Use Prisma-typed reads only; do not hand-write SQL except `SELECT 1` health probes.

## 6. Audit Trail

`auditLog({ actor, action, message, problemId? })` (`src/lib/audit.ts`) writes into the
existing `logs` table (`user_id`, `problem_id`, `notification_type`, `message`,
`created_at`). It is **best-effort**: failures are swallowed so auditing never breaks the
primary flow. Actions are dotted names, e.g. `AUTH_LOGIN`, `AUTH_FAILED`, `AUTH_BLOCKED`,
`PROBLEM_VERIFIED`, `PROBLEM_ASSIGNED`, `USER_BANNED`, `SOS_TRIGGERED`,
`RESPONSE_RESOLVED`, `PROBLEMS_EXPORTED`.

## 7. Theming (Dark Mode)

Two-layer design:

1. **Design tokens** in `src/app/globals.css` remap the Tailwind v4 neutral palette
   (`--color-slate-50…900`, `--color-white`) onto CSS variables that change under
   `:root.dark`. Text and surface colors therefore flip **everywhere** with no
   per-component `dark:` variants — eliminating class-bloat and drift.
2. **Fixed-dark ink surfaces**: `--color-ink`, `--color-ink-soft`, `--color-ink-fade`
   are constants (not theme-remapped) used for dark buttons, the hero, ticker, weather card
   and avatar gradients so they stay dark with white/`slate-300` text in both themes.

Themes are **class-based**: `<html class="dark">`. A tiny inline script in the root layout
(`src/app/layout.tsx`) applies the stored preference before paint (no FOUC); a
`useSyncExternalStore`-backed provider (`src/components/theme-provider.tsx`) keeps React
and the DOM in sync and persists to `localStorage["govconnect-theme"]`
(default: system preference). `globals.css` also includes a global
`prefers-reduced-motion` reduction.

## 8. Destructive Action Safety

Damaging admin forms (delete/reject problem, reject team, ban user, reject appeal) render a
**two-step confirm button** (`src/components/confirm-submit.tsx`): first click arms
(label → "Click again to confirm ⚠️", auto-disarms after 4 s), second click submits. The
server action never receives a request on the first click, so accidental destructive
submissions are structurally impossible — no `window.confirm`/native dialogs anywhere.

## 9. API Route Handlers

Route handlers live under `src/app/api/` and are **excluded from the proxy matcher**
(`/((?!api|…).*)`), so they authenticate internally:

- **`GET /api/health`** — self-contained DB probe (`SELECT 1`), returns `{ ok, db, uptimeSeconds, version, timestamp }` (503 when the DB is down). Used for uptime/monitoring.
- **`GET /api/admin/export/problems`** — admin-session only (401 otherwise). Streams every
  active complaint as `text/csv` with a `Content-Disposition: attachment` filename dated by
  day. Values are escaped via `src/lib/csv.ts` (RFC-4180 quoting); a UTF-8 BOM is prepended
  for Excel; cells that could trigger spreadsheet formula injection are neutralized.
  Each export is recorded in the audit trail (`PROBLEMS_EXPORTED`).

## 10. Security Headers & CSP

Set globally in `next.config.ts` (`src` config) and verified at runtime:

- `X-Content-Type-Options: nosniff`
- `X-Frame-Options: DENY`
- `Referrer-Policy: strict-origin-when-cross-origin`
- `Permissions-Policy` disables camera/mic/payment/usb/battery; `geolocation=(self)` is
  kept because the location picker and SOS flow use `navigator.geolocation`.
- `Content-Security-Policy` restricts sources to self + the specific third-party hosts the
  app actually talks to (Open-Meteo, Nominatim, OpenStreetMap tiles, CARTO basemaps).
  Inline scripts appear under `script-src` as `'unsafe-inline'` because Next.js App Router
  injects inline RSC bootstrap payloads; this is same-origin content only.

Keep CSP hosts in sync whenever new external fetches are added (map tiles, weather, etc.).

## 11. Testing & CI

- **Vitest 5** (`vitest.config.mts`, jsdom + Testing Library, `globals: true`). Alias
  `server-only` → `vitest/server-only.ts` stub because `server-only` throws outside the RSC
  runtime. `resolve.tsconfigPaths` native option replaces `vite-tsconfig-paths`.
- 26 tests across 6 files: `lib/problems.test.ts` (12), `lib/csv.test.ts` (5),
  `lib/action-state.test.ts` (3), `components/analytics-doughnut.test.tsx` (2),
  `components/confirm-submit.test.tsx` (2), `components/theme-toggle.test.tsx` (2).
- **CI** (`.github/workflows/ci.yml`): Node 22 `npm ci` → `lint` → `typecheck` →
  `test:run` → `build`, using a dummy `DATABASE_URL` (Prisma generate doesn't connect).

## 12. Golden Rules of Interface Design

The rebuild applies Nielsen/Schneiderman golden rules throughout:

| Rule | Where |
| --- | --- |
| Consistency & standards | Shared UI kit (`components/ui/*`), token-driven palette, uniform paddings/radii/badges. |
| Prevent errors | Two-step confirm for destructive actions; required-field validation; policy feedback before submit. |
| Recognition over recall | Persistent role-appropriate nav, familiar icons, flash banners after redirects. |
| User control & freedom | Clear exits (logout), cancel paths, recoverable archive instead of hard delete. |
| Visibility of system status | Status badges, live "Online" grid indicator, ticker, optimistic transitions, loading states. |
| Minimalist design | Dense data tables, content-first layouts, restrained color use. |
| Help & documentation | Placeholder examples in search/badges, descriptive labels/aria, this documentation. |

## 13. Known Trade-offs & Next Steps

- In-memory rate limiter resets on restart and is not horizontally shareable — move to
  Redis/edge storage for multi-instance production.
- `'unsafe-inline'` scripts in CSP are a pragmatic trade-off for App Router bootstrapping;
  nonce-based CSP is the long-term hardening step.
- No pagination on the admin problems list yet (SQL `LIMIT/OFFSET` or cursor); CSV export
  is the current bulk-escape hatch.
- `form-action 'self'` and same-origin-only fetches keep the app single-tenant focused.

## 14. Files To Know First

- `src/proxy.ts` — auth + rate limiting
- `src/app/layout.tsx` & `src/app/globals.css` — theme + global chrome
- `src/lib/auth/*` — sessions, guards, password policy
- `src/actions/*` — every mutation lives here
- `src/lib/audit.ts`, `src/lib/csv.ts` — cross-cutting concerns
- `prisma/schema.prisma` — the data model
- `.github/workflows/ci.yml` — quality gates