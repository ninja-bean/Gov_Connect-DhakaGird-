import type { Metadata } from "next";
import { db } from "@/lib/db";
import { requireRole } from "@/lib/auth/guards";
import { runClickAction, assignProblem } from "@/actions/admin";
import AdminNav from "@/components/admin-nav";
import FlashBanner from "@/components/admin/flash-banner";
import { Container, PageHeader } from "@/components/ui/page-header";
import { Card, CardBody, CardHeader } from "@/components/ui/card";
import { StatusBadge, PriorityBadge } from "@/components/ui/status-badge";
import { Select } from "@/components/ui/controls";
import { EmptyState } from "@/components/ui/empty-state";
import type { ProblemStatus } from "@/components/ui/status-badge";

export const metadata: Metadata = { title: "Problems | GovConnect Admin" };

const PRIORITIES = ["low", "medium", "high", "sos"] as const;

type Row = Awaited<ReturnType<typeof loadRows>>[number] & { teamsForCategory: Teams };
type Teams = Awaited<ReturnType<typeof loadTeams>>;

function loadRows() {
  return db.problems.findMany({
    where: { deleted_by_admin: false },
    include: {
      users: { select: { name: true, email: true, phone: true } },
      feedbacks: {
        include: { users: { select: { name: true } } },
        orderBy: { created_at: "desc" },
      },
    },
    orderBy: { created_at: "desc" },
  });
}

function loadTeams() {
  return db.users.findMany({
    where: { role: "response", status: "active" },
    select: { user_id: true, name: true, category: true, total_members: true, busy_members: true },
  });
}

function QuickActionForm({
  action,
  problemId,
  label,
  className = "",
  confirm,
}: {
  action: "verifyProblem" | "rejectProblem" | "deleteProblem";
  problemId: number;
  label: string;
  className?: string;
  confirm?: boolean;
}) {
  return (
    <form
      action={runClickAction}
      onSubmit={
        confirm
          ? (e) => {
              if (!window.confirm("Delete this complaint? This moves it to the archive.")) {
                e.preventDefault();
              }
            }
          : undefined
      }
    >
      <input type="hidden" name="action" value={action} />
      <input type="hidden" name="problemId" value={problemId} />
      <input type="hidden" name="back" value="/admin/problems" />
      <button type="submit" className={className}>
        {label}
      </button>
    </form>
  );
}

function ProblemCard({ p, teamName }: { p: Row; teamName?: string }) {
  const status = p.status as ProblemStatus;
  return (
    <li className="rounded-xl border border-slate-200 p-5">
      <div className="flex flex-wrap items-center justify-between gap-3">
        <div>
          <p className="font-bold capitalize text-slate-900">
            #{p.problem_id} — {p.category}
          </p>
          <p className="text-xs text-slate-400">{p.created_at.toLocaleString()}</p>
        </div>
        <div className="flex items-center gap-2">
          <PriorityBadge priority={p.priority} />
          <StatusBadge status={status} />
        </div>
      </div>

      <p className="mt-3 text-sm leading-relaxed text-slate-700">{p.description}</p>
      {p.suggestion && (
        <p className="mt-2 text-sm text-slate-600">
          <span className="font-semibold">Suggestion:</span> {p.suggestion}
        </p>
      )}
      {p.report && (
        <p className="mt-2 text-sm text-slate-600">
          <span className="font-semibold">Team report:</span> {p.report}
        </p>
      )}

      <div className="mt-3 grid gap-3 sm:grid-cols-2">
        <div className="rounded-lg bg-slate-50 p-3 text-sm text-slate-600">
          <span className="font-semibold">Location:</span> {p.location_name || p.location || "Dhaka"}
        </div>
        <div className="rounded-lg bg-slate-50 p-3 text-sm text-slate-600">
          <span className="font-semibold">Reporter:</span> {p.users.name} · {p.users.email}
          {p.users.phone ? ` · ${p.users.phone}` : ""}
        </div>
        {teamName && (
          <div className="rounded-lg bg-slate-50 p-3 text-sm text-slate-600 sm:col-span-2">
            <span className="font-semibold">Assigned team:</span> {teamName}
          </div>
        )}
      </div>

      {p.feedbacks.length > 0 && (
        <div className="mt-3 space-y-2 rounded-lg border border-blue-100 bg-blue-50 p-3">
          <p className="text-xs font-bold uppercase tracking-wide text-blue-700">User feedback</p>
          {p.feedbacks.map((f) => (
            <div key={f.feedback_id} className="text-sm text-slate-700">
              <p>
                <span className="font-semibold">{f.users?.name ?? "Citizen"}</span> —{" "}
                <span className="text-amber-500">
                  {"★".repeat(f.rating)}
                  <span className="text-slate-300">{"★".repeat(5 - f.rating)}</span>
                </span>
              </p>
              {f.comment && <p className="mt-0.5 text-slate-600">{f.comment}</p>}
            </div>
          ))}
        </div>
      )}

      <div className="mt-4 flex flex-wrap items-center gap-3 border-t border-slate-100 pt-4">
        {status === "pending" && (
          <QuickActionForm
            action="verifyProblem"
            problemId={p.problem_id}
            label="✅ Accept"
            className="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-emerald-500 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-600"
          />
        )}
        {status === "pending" && (
          <QuickActionForm
            action="rejectProblem"
            problemId={p.problem_id}
            label="❌ Reject"
            className="rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-red-500 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-red-600"
          />
        )}
        {status === "verified" && <AssignForm p={p} />}
        <QuickActionForm
          action="deleteProblem"
          problemId={p.problem_id}
          label="🗑 Delete"
          confirm
          className="rounded-lg border border-red-200 px-4 py-2 text-sm font-semibold text-red-600 transition hover:bg-red-50 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-red-600"
        />
      </div>
    </li>
  );
}

function AssignForm({ p }: { p: Row }) {
  const teams = p.teamsForCategory;
  return (
    <form action={assignProblem} className="flex flex-wrap items-center gap-2">
      <input type="hidden" name="problemId" value={p.problem_id} />
      <Select name="priority" required aria-label="Priority" className="w-auto">
        <option value="">Priority…</option>
        {PRIORITIES.map((pr) => (
          <option key={pr} value={pr}>
            {pr.toUpperCase()}
          </option>
        ))}
      </Select>
      <Select name="assignedTo" required aria-label="Assign team" className="w-auto">
        <option value="">Assign team…</option>
        {teams.map((t) => (
          <option key={t.user_id} value={t.user_id}>
            {t.name} ({t.total_members - t.busy_members} free)
          </option>
        ))}
      </Select>
      <button
        type="submit"
        className="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-blue-500 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600"
      >
        ✔ Assign
      </button>
    </form>
  );
}

export default async function AdminProblemsPage({
  searchParams,
}: {
  searchParams: Promise<{ flash?: string; msg?: string }>;
}) {
  await requireRole("admin");
  const sp = await searchParams;

  const [problems, teams, deleted] = await Promise.all([
    loadRows(),
    loadTeams(),
    db.deleted_problems.findMany({ orderBy: { deleted_at: "desc" } }),
  ]);

  const teamNames = new Map(
    problems
      .filter((p) => p.assigned_to !== null)
      .map((p) => [p.assigned_to as number, ""]),
  );
  if (teamNames.size > 0) {
    const assigned = await db.users.findMany({
      where: { user_id: { in: [...teamNames.keys()] } },
      select: { user_id: true, name: true },
    });
    for (const t of assigned) teamNames.set(t.user_id, t.name);
  }

  const teamsByCategory = new Map<string, Teams>();
  for (const t of teams) {
    const key = t.category ?? "";
    if (!teamsByCategory.has(key)) teamsByCategory.set(key, []);
    teamsByCategory.get(key)!.push(t);
  }

  const rows: Row[] = problems.map((p) => ({
    ...p,
    teamsForCategory: teamsByCategory.get(p.category) ?? [],
  }));

  const groups: { title: string; tone: string; items: typeof rows }[] = [
    { title: "🔍 Pending review", tone: "amber", items: rows.filter((r) => r.status === "pending") },
    { title: "🧩 Verified — awaiting assignment", tone: "blue", items: rows.filter((r) => r.status === "verified") },
    { title: "🔧 In progress", tone: "green", items: rows.filter((r) => ["assigned", "working"].includes(r.status)) },
    { title: "✅ Resolved & rejected", tone: "slate", items: rows.filter((r) => ["resolved", "rejected"].includes(r.status)) },
  ];

  return (
    <div className="min-h-screen bg-slate-50">
      <AdminNav />
      <main className="py-8">
        <Container>
          <PageHeader
            title="Problem Management"
            description="Verify reports, assign them to teams, and archive complaints."
          />
          <FlashBanner ok={sp.flash === "ok" ? sp.msg : undefined} err={sp.flash === "err" ? sp.msg : undefined} />

          <div className="mt-6 space-y-6">
            {groups.map((g) => (
              <Card key={g.title}>
                <CardHeader
                  title={
                    <span className={`capitalize ${g.tone === "amber" ? "text-amber-700" : g.tone === "blue" ? "text-blue-700" : g.tone === "green" ? "text-emerald-700" : "text-slate-600"}`}>
                      {g.title}
                    </span>
                  }
                />
                <CardBody>
                  {g.items.length === 0 ? (
                    <EmptyState icon="🗂️" title="Nothing here" description="No problems match this stage." />
                  ) : (
                    <ul className="space-y-4">
                      {g.items.map((p) => (
                        <ProblemCard key={p.problem_id} p={p} teamName={p.assigned_to ? teamNames.get(p.assigned_to) : undefined} />
                      ))}
                    </ul>
                  )}
                </CardBody>
              </Card>
            ))}
          </div>

          <Card className="mt-6">
            <CardHeader icon={<span aria-hidden>🗑️</span>} title="Archived (deleted) complaints" />
            <CardBody>
              <details>
                <summary className="cursor-pointer text-sm font-semibold text-blue-600 hover:underline">
                  Show archives ({deleted.length})
                </summary>
                {deleted.length === 0 ? (
                  <p className="mt-4 text-sm text-slate-500">No archived complaints yet.</p>
                ) : (
                  <div className="mt-4 overflow-x-auto">
                    <table className="w-full min-w-[640px] border-collapse text-left text-sm">
                      <thead>
                        <tr className="border-b border-slate-200 text-xs font-semibold uppercase tracking-wide text-slate-500">
                          <th className="px-3 py-2">ID</th>
                          <th className="px-3 py-2">Category</th>
                          <th className="px-3 py-2">Description</th>
                          <th className="px-3 py-2">Status</th>
                          <th className="px-3 py-2">User</th>
                          <th className="px-3 py-2">Deleted</th>
                        </tr>
                      </thead>
                      <tbody className="divide-y divide-slate-100">
                        {deleted.map((d) => (
                          <tr key={d.del_id}>
                            <td className="px-3 py-2 font-semibold text-slate-700">#{d.problem_id}</td>
                            <td className="px-3 py-2 capitalize text-slate-700">{d.category}</td>
                            <td className="max-w-xs px-3 py-2 text-slate-600">{d.description}</td>
                            <td className="px-3 py-2">
                              <StatusBadge status={d.status ?? "pending"} />
                            </td>
                            <td className="px-3 py-2 text-slate-600">{d.user_name}</td>
                            <td className="px-3 py-2 text-xs text-slate-400">{d.deleted_at.toLocaleString()}</td>
                          </tr>
                        ))}
                      </tbody>
                    </table>
                  </div>
                )}
              </details>
            </CardBody>
          </Card>
        </Container>
      </main>
    </div>
  );
}