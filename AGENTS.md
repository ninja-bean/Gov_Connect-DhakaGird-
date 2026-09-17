# AGENTS.md — Agent Operating Rules (DhakaGrid)

> These rules govern how the coding agent works in this repository. They are mandatory. If a rule and an instruction ever conflict, this file wins; if the conflict is material, stop and ask.

## 1. Mandatory Clarification Rule (owner mandate — non-negotiable)

**Whenever a decision has multiple reasonable options — architecture, library, feature scope, UI treatment, naming, anything — STOP and ask the project owner with explicit options. Never pick one silently and proceed.**

Only genuinely unambiguous work (obvious bug fix, exact instruction) proceeds without asking.

## 2. Read-Before-Work

Before starting **any** task:
1. Read `INIT.md` (architecture, roadmap, conventions) — re-read on every task.
2. Read `DESIGN.md` (design system) before touching any front-end/markup.
3. Check `git status` for a clean working tree; never build on a dirty tree.
4. Confirm the task Issue exists and states the scope + exit criteria.

## 3. Scope Boundaries

- **Login/signup UI (`login.php`, `register.php`, `assets/css/dhakagrid.css`, `assets/partials/sketch_bg.php`): the owner's exclusive custom UI — OUT OF SCOPE.** Do not analyze, reference, reuse, or extend it into dashboard work.
- Every dashboard screen (citizen/admin/response + profile/support pages) uses the single system in `DESIGN.md`.
- Legacy purple admin/response UI is being replaced outright; no residue (grep gate).

## 4. Workflow

One task = one GitHub Issue → one branch → one PR → squash-merge → close.

### Branching
- Branch **per task** (not per phase), from updated `main`.
- Naming: `feat/<id>-<slug>` (e.g. `feat/01.3-core-kernel`); docs/chores: `docs/<slug>` or `chore/<slug>`.
- Keep each PR small and atomic — several small PRs are preferred over one large one.

### Commits
- Conventional Commits: `feat:`, `fix:`, `refactor:`, `docs:`, `test:`, `chore:`.
- Commit messages: imperative, ≤ ~72 chars subject, body explaining *why* when non-obvious.
- One logical change per commit; amend only before the PR is pushed, never after.

### Pull Requests
- Open the PR referencing the Issue (`Closes #N` in body).
- Self-review checklist in the PR body (see §6). Review the PR as a reviewer would after opening it.
- **Squash-merge** every PR; the squash message is the task summary. Close the Issue (gh auto-closes with `Closes #N`). Delete the branch on merge.
- Keep `main` always green: never push directly to `main`; all changes land via merged PRs.

### Motion
- Base every branch on updated `main`. If `main` moved, rebase the feature branch before PR.
- After your PR merges, run `git checkout main && git pull --ff-only`.

## 5. Phase Exit Gates (from INIT.md roadmap)

Before a phase is marked done, and before proceeding to the next phase:
1. `php -l` on every changed PHP file.
2. `vendor/bin/phpunit` green (tests added for services/repositories touched).
3. DESIGN.md conformance checklist (§9) against every screen touched.
4. Manual smoke: log in as each role and exercise the changed flows (see §8 for accounts).
5. Grep gate where applicable (e.g. legacy purple token scan after P4).
6. All PRs for the phase merged and Issues closed; `INIT.md` §13 status table updated.

## 6. PR Self-Review Checklist (paste into PR body)

```
- [ ] Issue linked (`Closes #N`)
- [ ] php -l clean on changed files
- [ ] PHPUnit green (new/updated tests included)
- [ ] DESIGN.md §9 checklist applies and passes
- [ ] No raw SQL in views; PDO prepared statements only
- [ ] All output escaped (no unescaped superglobals/DB values)
- [ ] No new dependencies (or owner approved)
- [ ] Manual smoke for affected roles done
```

## 7. Code & Design Rules

- PSR-4 (`App\` → `src/`), PSR-12. Only `src/` holds SQL (Repositories) and business rules (Services).
- Views/templates: no SQL, no business logic, no inline raw colors — tokens and components from DESIGN.md only.
- Auth guards via `Auth::requireRole(...)` in `src/Core`; roles `user | response | admin`.
- Output escaping at render time; JSON-safe encoding for JS contexts (never `addslashes`).
- No `alert()`/`confirm()`; use the toast system. No `die()` except central bootstrap failure.
- No new runtime dependencies without owner decision. PHPUnit (dev) pre-approved.
- Enums/status/priority constants live in `src/Core/Enums`; magic strings only there.
- File uploads: allowlist extension+MIME, size cap, gitignored storage.
- Motions per DESIGN.md §6 (infinite loops only for active-SOS affordance); honor `prefers-reduced-motion`.

## 8. Local Dev Environment

```
Server:  C:\xampp\php\php.exe -S 127.0.0.1:8080 -t "D:\Projects\Gov_Connect-DhakaGird-"
Database: g1 (MySQL/MariaDB), PDO via config/env (credentials never in code)
Tests:    vendor/bin/phpunit
```

Seed dev accounts for smoke tests (from current DB):

| Role | Email | Password |
| :--- | :--- | :--- |
| Admin | `admin@dhakagrid.gov` | `admin123` |
| Citizen | `rahim@example.com` | `rahim123` |
| Response team | `police@dhakagrid.gov` | `police123` |

## 9. When to Ask vs Proceed

- **Ask (with explicit options):** multiple reasonable paths, naming, whether to add scope, UI treatment questions, dependency choices, anything that changes the roadmap.
- **Proceed:** mechanical/obvious fixes, tasks already specified by this repo's docs and an approved plan.

## 10. Definition of Done (a task is done only when)

- PR merged to `main` (or PR open awaiting owner review), Issue closed, branch deleted.
- Exit-gate steps above all pass.
- Any new conventions discovered are folded back into `INIT.md` / `DESIGN.md` (keep docs truthful).