import type { Metadata } from "next";
import { db } from "@/lib/db";
import { requireRole } from "@/lib/auth/guards";
import ResponseNav from "@/components/response-nav";
import ProblemActionForm from "@/components/problem-action-form";
import ProblemMap, { type MapMarker } from "@/components/problem-map";
import { Container, PageHeader } from "@/components/ui/page-header";
import { StatCard } from "@/components/ui/stat-card";
import { Card, CardBody, CardHeader } from "@/components/ui/card";
import { StatusBadge, PriorityBadge } from "@/components/ui/status-badge";
import { Badge } from "@/components/ui/badge";
import { EmptyState } from "@/components/ui/empty-state";
import { ButtonLink } from "@/components/ui/button";

export const metadata: Metadata = { title: "Response Dashboard | GovConnect" };

const GROUPS = [
  { key: "sos", label: "🚨 SOS Emergencies", tone: "text-red-600" },
  { key: "high", label: "🔥 High Priority", tone: "text-orange-600" },
  { key: "medium", label: "⚠️ Medium Priority", tone: "text-blue-600" },
  { key: "low", label: "ℹ️ Low Priority", tone: "text-slate-600" },
] as const;

function isSos(priority: string, description: string | null): boolean {
  const p = priority.toLowerCase();
  const d = (description ?? "").toLowerCase();
  return p === "sos" || d.includes("sos");
}

export default async function ResponseDashboard() {
  const session = await requireRole("response");

  const [team, assigned] = await Promise.all([
    db.users.findUniqueOrThrow({
      where: { user_id: session.userId },
      select: {
        name: true,
        category: true,
        total_members: true,
        busy_members: true,
      },
    }),
    db.problems.findMany({
      where: { assigned_to: session.userId },
      include: { users: { select: { name: true, email: true, phone: true } } },
      orderBy: { created_at: "desc" },
    }),
  ]);

  const deletedProblems = await db.problems
    .findMany({
      where: { assigned_to: session.userId },
      select: { problem_id: true },
    })
    .then((ids) =>
      db.deleted_problems.findMany({
        where: { problem_id: { in: ids.map((r) => r.problem_id) } },
        orderBy: { deleted_at: "desc" },
      }),
    );

  const available = team.total_members - team.busy_members;

  const groups = {
    sos: assigned.filter((p) => isSos(p.priority, p.description)),
    high: assigned.filter(
      (p) => p.priority.toLowerCase() === "high" && !isSos(p.priority, p.description),
    ),
    medium: assigned.filter(
      (p) => p.priority.toLowerCase() === "medium" && !isSos(p.priority, p.description),
    ),
    low: assigned.filter((p) => !["sos", "high", "medium"].includes(p.priority.toLowerCase())),
  };

  const markers: MapMarker[] = assigned
    .filter((p) => p.latitude !== null && p.longitude !== null)
    .map((p) => ({
      lat: Number(p.latitude),
      lng: Number(p.longitude),
      category: p.category,
      status: p.status,
      createdAt: p.created_at.toISOString(),
    }));

  const working = assigned.filter((p) => p.status === "working").length;
  const resolved = assigned.filter((p) => p.status === "resolved").length;
  const sosCount = groups.sos.length;

  return (
    <div className="min-h-screen bg-slate-50">
      <ResponseNav />
      <main className="py-8">
        <Container>
          <PageHeader
            title="Response Dashboard"
            description={`${team.name} — ${team.category} response unit`}
            action={
              <div className="flex items-center gap-2">
                <Badge tone="green">{available} members available</Badge>
                <ButtonLink href="/response/profile" variant="secondary" size="sm">
                  Team Profile
                </ButtonLink>
              </div>
            }
          />

          <div className="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <StatCard icon="👷" label="Available members" value={available} tone="green" />
            <StatCard icon="🚨" label="SOS emergencies" value={sosCount} tone="red" />
            <StatCard icon="🔧" label="Working now" value={working} tone="blue" />
            <StatCard icon="✅" label="Resolved" value={resolved} tone="slate" />
          </div>

          <Card className="mt-6">
            <CardHeader
              icon={<span aria-hidden>🗺️</span>}
              title="Assigned Problems Map"
              action={
                <span className="flex items-center gap-2 text-xs font-semibold text-emerald-600">
                  <span className="h-2 w-2 rounded-full bg-emerald-500" /> Live
                </span>
              }
            />
            <CardBody className="pt-4">
              {markers.length === 0 ? (
                <div className="flex h-64 items-center justify-center rounded-xl border border-dashed border-slate-300 text-sm text-slate-400">
                  No located problems assigned yet.
                </div>
              ) : (
                <ProblemMap markers={markers} height={420} />
              )}
            </CardBody>
          </Card>

          <div className="mt-6 space-y-6">
            {GROUPS.map(({ key, label, tone }) => {
              const items = groups[key];
              return (
                <Card key={key}>
                  <CardHeader
                    title={
                      <span className={`flex items-center gap-2 ${tone}`}>
                        {label}
                        <span className="rounded-full bg-slate-100 px-2 py-0.5 text-xs font-bold text-slate-600">
                          {items.length}
                        </span>
                      </span>
                    }
                  />
                  <CardBody>
                    {items.length === 0 ? (
                      <EmptyState
                        icon="📭"
                        title={`No ${key} problems assigned`}
                        description="This section stays empty until problems in this priority are assigned to your team."
                      />
                    ) : (
                      <ul className="space-y-4">
                        {items.map((p) => (
                          <li
                            key={p.problem_id}
                            className="rounded-xl border border-slate-200 p-5"
                          >
                            <div className="flex flex-wrap items-center justify-between gap-3">
                              <div>
                                <p className="font-bold capitalize text-slate-900">
                                  #{p.problem_id} — {p.category}
                                </p>
                                <p className="text-xs text-slate-400">
                                  {p.created_at.toLocaleString()}
                                </p>
                              </div>
                              <div className="flex items-center gap-2">
                                <PriorityBadge priority={p.priority} />
                                <StatusBadge status={p.status} />
                              </div>
                            </div>

                            <p className="mt-3 text-sm leading-relaxed text-slate-700">
                              {p.description}
                            </p>

                            <div className="mt-3 grid gap-3 sm:grid-cols-2">
                              {p.suggestion && (
                                <div className="rounded-lg bg-slate-50 p-3 text-sm text-slate-600">
                                  <span className="font-semibold">Suggestion:</span> {p.suggestion}
                                </div>
                              )}
                              <div className="rounded-lg bg-slate-50 p-3 text-sm text-slate-600">
                                <span className="font-semibold">Location:</span>{" "}
                                {p.location_name || p.location || "Dhaka"}
                              </div>
                              <div className="rounded-lg bg-slate-50 p-3 text-sm text-slate-600 sm:col-span-2">
                                <span className="font-semibold">Reported by:</span> {p.users.name}{" "}
                                · {p.users.email}
                                {p.users.phone ? ` · ${p.users.phone}` : ""}
                              </div>
                            </div>

                            {p.status === "working" && p.working_members > 0 && (
                              <p className="mt-3 inline-flex items-center gap-1.5 rounded-full bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-700 ring-1 ring-inset ring-blue-200">
                                👷 {p.working_members} member(s) working
                              </p>
                            )}

                            {p.status !== "resolved" && (
                              <div className="mt-4 border-t border-slate-100 pt-4">
                                <ProblemActionForm
                                  problemId={p.problem_id}
                                  mode={p.status === "working" ? "resolve" : "start"}
                                  available={available}
                                  membersAssignable={available}
                                />
                              </div>
                            )}

                            {p.status === "resolved" && p.report && (
                              <div className="mt-4 rounded-lg border border-emerald-200 bg-emerald-50 p-3 text-sm text-emerald-800">
                                <span className="font-semibold">Team report:</span> {p.report}
                              </div>
                            )}
                          </li>
                        ))}
                      </ul>
                    )}
                  </CardBody>
                </Card>
              );
            })}
          </div>

          <Card className="mt-6">
            <CardHeader icon={<span aria-hidden>🗑️</span>} title="Deleted Complaints" />
            <CardBody>
              <details>
                <summary className="cursor-pointer text-sm font-semibold text-blue-600 hover:underline">
                  Show deleted complaints ({deletedProblems.length})
                </summary>
                {deletedProblems.length === 0 ? (
                  <p className="mt-4 text-sm text-slate-500">No deleted complaints yet.</p>
                ) : (
                  <div className="mt-4 overflow-x-auto">
                    <table className="w-full min-w-[640px] border-collapse text-left text-sm">
                      <thead>
                        <tr className="border-b border-slate-200 text-xs font-semibold uppercase tracking-wide text-slate-500">
                          <th className="px-3 py-2">ID</th>
                          <th className="px-3 py-2">Category</th>
                          <th className="px-3 py-2">Description</th>
                          <th className="px-3 py-2">Location</th>
                          <th className="px-3 py-2">User</th>
                          <th className="px-3 py-2">Deleted</th>
                        </tr>
                      </thead>
                      <tbody className="divide-y divide-slate-100">
                        {deletedProblems.map((d) => (
                          <tr key={d.del_id}>
                            <td className="px-3 py-2 font-semibold text-slate-700">
                              #{d.problem_id}
                            </td>
                            <td className="px-3 py-2 capitalize text-slate-700">{d.category}</td>
                            <td className="max-w-xs px-3 py-2 text-slate-600">{d.description}</td>
                            <td className="px-3 py-2 text-slate-600">{d.location}</td>
                            <td className="px-3 py-2 text-slate-600">
                              {d.user_name ?? "Unavailable"}
                            </td>
                            <td className="px-3 py-2 text-xs text-slate-400">
                              {d.deleted_at.toLocaleString()}
                            </td>
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