import type { Metadata } from "next";
import { db } from "@/lib/db";
import { requireRole } from "@/lib/auth/guards";
import { runClickAction } from "@/actions/admin";
import AdminNav from "@/components/admin-nav";
import FlashBanner from "@/components/admin/flash-banner";
import { Container, PageHeader } from "@/components/ui/page-header";
import { Card, CardBody, CardHeader } from "@/components/ui/card";
import { Badge } from "@/components/ui/badge";
import { EmptyState } from "@/components/ui/empty-state";

export const metadata: Metadata = { title: "Teams | GovConnect Admin" };

function TeamActionForm({
  action,
  userId,
  label,
  className,
}: {
  action: "approveTeam" | "rejectTeam";
  userId: number;
  label: string;
  className: string;
}) {
  return (
    <form action={runClickAction}>
      <input type="hidden" name="action" value={action} />
      <input type="hidden" name="userId" value={userId} />
      <input type="hidden" name="back" value="/admin/teams" />
      <button type="submit" className={className}>
        {label}
      </button>
    </form>
  );
}

export default async function AdminTeamsPage({
  searchParams,
}: {
  searchParams: Promise<{ flash?: string; msg?: string }>;
}) {
  await requireRole("admin");
  const sp = await searchParams;

  const teams = await db.users.findMany({
    where: { role: "response" },
    orderBy: [{ status: "asc" }, { created_at: "desc" }],
  });

  const pending = teams.filter((t) => t.status === "pending");
  const active = teams.filter((t) => t.status === "active");
  const rejected = teams.filter((t) => t.status === "rejected");

  return (
    <div className="min-h-screen bg-slate-50">
      <AdminNav />
      <main className="py-8">
        <Container>
          <PageHeader
            title="Response Teams"
            description="Approve new response units and review existing ones."
          />
          <FlashBanner ok={sp.flash === "ok" ? sp.msg : undefined} err={sp.flash === "err" ? sp.msg : undefined} />

          <div className="mt-6 space-y-6">
            <Card>
              <CardHeader title="⏳ Pending approvals" />
              <CardBody>
                {pending.length === 0 ? (
                  <EmptyState
                    icon="✅"
                    title="No pending approvals"
                    description="New response teams will show up here for review."
                  />
                ) : (
                  <ul className="space-y-4">
                    {pending.map((t) => (
                      <li key={t.user_id} className="rounded-xl border border-slate-200 p-5">
                        <div className="flex flex-wrap items-center justify-between gap-3">
                          <div>
                            <p className="font-bold capitalize text-slate-900">{t.name}</p>
                            <p className="text-sm text-slate-500">
                              {t.email} {t.phone ? ` · ${t.phone}` : ""}
                            </p>
                          </div>
                          <Badge tone="amber">{t.category ?? "—"} response</Badge>
                        </div>
                        <dl className="mt-3 grid gap-2 text-sm sm:grid-cols-2">
                          <div className="rounded-lg bg-slate-50 p-3 text-slate-600">
                            <dt className="font-semibold">Location</dt>
                            <dd>{t.location ?? "—"}</dd>
                          </div>
                          <div className="rounded-lg bg-slate-50 p-3 text-slate-600">
                            <dt className="font-semibold">Team size</dt>
                            <dd>{t.employee_number ?? t.total_members ?? "—"}</dd>
                          </div>
                          <div className="rounded-lg bg-slate-50 p-3 text-slate-600">
                            <dt className="font-semibold">In-charge</dt>
                            <dd>
                              {t.incharge_name ?? "—"}
                              {t.incharge_phone ? ` · ${t.incharge_phone}` : ""}
                            </dd>
                          </div>
                          <div className="rounded-lg bg-slate-50 p-3 text-slate-600">
                            <dt className="font-semibold">Identification</dt>
                            <dd>{t.identification ?? "—"}</dd>
                          </div>
                        </dl>
                        <div className="mt-4 flex flex-wrap gap-3">
                          <TeamActionForm
                            action="approveTeam"
                            userId={t.user_id}
                            label="✅ Approve"
                            className="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-emerald-500 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-600"
                          />
                          <TeamActionForm
                            action="rejectTeam"
                            userId={t.user_id}
                            label="❌ Reject"
                            className="rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-red-500 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-red-600"
                          />
                        </div>
                      </li>
                    ))}
                  </ul>
                )}
              </CardBody>
            </Card>

            <Card>
              <CardHeader title="🛡️ Active teams" />
              <CardBody>
                {active.length === 0 ? (
                  <p className="text-sm text-slate-500">No active response teams yet.</p>
                ) : (
                  <ul className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    {active.map((t) => (
                      <li key={t.user_id} className="rounded-xl border border-slate-200 p-5">
                        <p className="font-bold capitalize text-slate-900">{t.name}</p>
                        <p className="mt-1 text-sm capitalize text-slate-500">{t.category}</p>
                        <p className="mt-2 text-xs text-slate-400">{t.location ?? "Dhaka"}</p>
                        <p className="mt-3 text-sm font-semibold text-slate-700">
                          {t.busy_members}/{t.total_members} members busy
                        </p>
                      </li>
                    ))}
                  </ul>
                )}
              </CardBody>
            </Card>

            {rejected.length > 0 && (
              <Card>
                <CardHeader title="🗂️ Rejected requests" />
                <CardBody>
                  <ul className="space-y-2">
                    {rejected.map((t) => (
                      <li key={t.user_id} className="flex items-center justify-between gap-3 rounded-lg border border-slate-200 px-4 py-3 text-sm">
                        <span className="font-semibold text-slate-700">
                          {t.name} <span className="font-normal text-slate-400">· {t.email}</span>
                        </span>
                        <Badge tone="red">Rejected</Badge>
                      </li>
                    ))}
                  </ul>
                </CardBody>
              </Card>
            )}
          </div>
        </Container>
      </main>
    </div>
  );
}