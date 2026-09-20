# GovConnect – DhakaGrid

A citizen-centric civic platform that connects people with local government: citizens
file infrastructure complaints, request help, and track how authorities respond.
Authorities verify reports, assign them to response teams, and resolve them — end to end.

This repository is the **Next.js 16 (App Router) rewrite** of the original PHP application,
built with production-grade engineering practices: typed server actions, Prisma ORM over a
MySQL schema, JWT session auth, proxy-level rate limiting, a full audit trail, security
headers with a strict CSP, dark mode, an automated test suite, and CI on every push.

> The original application lives in [`legacy-php/`](legacy-php/). It is kept for parity
> reference only and is excluded from linting, builds, and type checking.

---

## Features

| Area | Highlights |
| --- | --- |
| **Citizen flow** | Submit reports (category, description, location via map picker or GPS, photos), emergency SOS alerts, live grid map, response tracking with status badges, feedback with ratings, unban appeals, city-watch XP levels. |
| **Response teams** | Team dashboard for assigned problems, in-progress / resolved workflow, team profile management. |
| **Admin** | Problem review, verify / reject / delete-with-archive, assign to teams with priority, team approvals, citizen ban & warnings, unban appeal handling, **CSV export** of all complaints, activity insights dashboard. |
| **Account & auth** | Register / login, password change, profile editing, role-based access (citizen / response / admin), signed JWT session cookies (7 days). |
| **UI / UX** | Class-based **dark mode** with token remapping, two-step confirm on destructive actions, reduced-motion support, WCAG-minded labels and focus states, consistent shared UI kit. |

## Tech Stack

- **Next.js 16** (App Router, Turbopack, React 19) — server components + server actions + route handlers
- **Tailwind CSS v4** (CSS-first configuration, `@theme` design tokens)
- **Prisma Client 6** over **MySQL 8** (generated client in `src/generated/prisma`)
- **jose** (HS256 JWT sessions), **bcryptjs** (password hashing, cost 10)
- **Zod 4** (validated server-action inputs)
- **Leaflet + OpenStreetMap** (mapping), **Open-Meteo** (weather), **Nominatim** (reverse geocoding)
- **Vitest 5 + Testing Library** (unit + component tests), **ESLint 9** (flat config), **GitHub Actions CI**

## Getting Started

### Prerequisites

- Node.js 22+
- MySQL 8 (this repo talks to an existing schema; see `prisma/schema.prisma`)

### Setup

```bash
npm install        # runs `prisma generate` via postinstall
cp .env.example .env
```

`.env` needs at minimum:

```
DATABASE_URL="mysql://USER:PASSWORD@127.0.0.1:3306/g1"
AUTH_SECRET="a-long-random-string"
```

- `DATABASE_URL` — connection string for the MySQL database.
- `AUTH_SECRET` — secret used to sign session JWTs. **Never commit a real value.**

### Run

```bash
npm run dev            # development server (Turbopack)
npm run build          # production build
npm start              # production server
```

### Scripts

| Script | Purpose |
| --- | --- |
| `npm run dev` | Next.js development server |
| `npm run build` | Production build |
| `npm start` | Production server |
| `npm run typecheck` | `tsc --noEmit` |
| `npm run lint` / `lint:fix` | ESLint (flat config) |
| `npm test` / `npm run test:run` | Vitest (watch / one-shot, **26 tests — 6 files**) |
| `npm run prisma:generate` | Regenerate the Prisma client |
| `npm run prisma:push` | Push `prisma/schema.prisma` to the DB |

### Demo accounts

| Role | Credentials |
| --- | --- |
| Admin | `admin@dhakagrid.gov` / `Admin@123` |
| Citizen | `Rahim@…` / `User@123` |
| Response team | `police@dhakagrid.gov` / `Team@123` |

---

## Project Structure

```
src/
├── proxy.ts                  # Next 16 proxy: auth redirects + login/register rate limiting
├── app/                      # App Router
│   ├── layout.tsx            # Root layout: fonts, theme provider, no-FOUC theme script
│   ├── globals.css           # Tailwind v4 tokens, dark theme remapping, reduced motion
│   ├── api/                  # Route handlers
│   │   ├── health/           #   GET /api/health — DB probe, uptime, version
│   │   └── admin/export/     #   GET /api/admin/export/problems — admin-only CSV export
│   ├── (marketing) page.tsx  # Landing page
│   ├── login|register|forgot-password/
│   ├── dashboard|report|my-problems|appeal|profile/
│   ├── admin/                # problems, teams, users, appeals, dashboard
│   └── response/             # dashboard, profile
├── actions/                  # Server actions (typed, Zod-validated) — auth, admin, problems, response, profile
├── components/               # Shared UI kit + feature components (theme, confirm, maps, doughnut, …)
├── lib/                      # Auth (session, guards, routes, password policy), audit log, csv, db, env, problems
├── generated/prisma/         # Generated Prisma client (do not edit)
```

The existing database schema is documented in detail in [`docs/ARCHITECTURE.md`](docs/ARCHITECTURE.md).

## Engineering Highlights

- **Security-first** — bcrypt (cost 10) password hashing with an enforced password policy,
  HS256-signed JWT session cookies, `SameSite` session handling, role-guarded routes and
  server actions, proxy-level sliding-window rate limiting on `/login` (8/min/IP) and
  `/register` (4/min/IP), a best-effort **audit trail** for every meaningful action, and
  response headers including a strict Content-Security-Policy.
- **Quality gates** — ESLint (0 errors / 0 warnings), `tsc` type checking, 26 Vitest tests,
  and a production build all run in CI for every push (`.github/workflows/ci.yml`).
- **Accessible + pleasant UI** — dark mode with WCAG-conscious contrast, two-step confirm
  buttons for destructive admin actions (no surprise deletes), `prefers-reduced-motion`
  support, keyboard-visible focus states, and descriptive `aria` labels.
- **Operational visibility** — `/api/health` returns service + database status, uptime, and
  version; admins can download every complaint as a properly escaped CSV (BOM for Excel,
  formula-injection guards).

## License

Academic project. See the paper/report in `docs/` for the original design description.