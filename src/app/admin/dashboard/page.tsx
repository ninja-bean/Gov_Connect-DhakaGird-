import type { Metadata } from "next";
import { db } from "@/lib/db";
import { requireRole } from "@/lib/auth/guards";
import AdminNav from "@/components/admin-nav";
import FlashBanner from "@/components/admin/flash-banner";
import ProblemMap, { type MapMarker } from "@/components/problem-map";
import { Container, PageHeader } from "@/components/ui/page-header";
import { StatCard } from "@/components/ui/stat-card";
import { Card, CardBody, CardHeader } from "@/components/ui/card";
import { StatusBadge } from "@/components/ui/status-badge";
import { ButtonLink } from "@/components/ui/button";

export const metadata: Metadata = { title: "Admin Overview | GovConnect" };

export default async function AdminDashboard({
  searchParams,
}: {
  searchParams: Promise<{ flash?: string; msg?: string }>;
}) {
  const session = await requireRole("admin");
  const sp = await searchParams;

  const [problems, teams, bannedUsers] = await Promise.all([
    db.problems.findMany({
      where: { deleted_by_admin: false },
      select: {
        problem_id: true,
        category: true,
        status: true,
        priority: true,
        latitude: true,
        longitude: true,
        description: true,
        created_at: true,
      },
      orderBy: { created_at: "desc" },
    }),
    db.users.findMany({
      where: { role: "response" },
      select: { user_id: true, status: true },
    }),
    db.users.count({ where: { is_banned: true } }),
  ]);

  const pendingReview = problems.filter((p) => p.status === "pending").length;
  const awaitingAssignment = problems.filter((p) => p.status === "verified").length;
  const active = problems.filter((p) => ["assigned", "working"].includes(p.status));
  const activeCount = active.length;
  const resolvedCount = problems.filter((p) => p.status === "resolved").length;
  const pendingTeams = teams.filter((t) => t.status === "pending").length;
  const activeTeams = teams.filter((t) => t.status === "active").length;

  const markers: MapMarker[] = problems
    .filter((p) => p.latitude !== null && p.longitude !== null)
    .map((p) => ({
      lat: Number(p.latitude),
      lng: Number(p.longitude),
      category: p.category,
      status: p.status,
      createdAt: p.created_at.toISOString(),
    }));

  const needsAction = [...problems]
    .filter((p) => p.status === "pending" || p.status === "verified")
    .slice(0, 8);

  return (
    <div className="min-h-screen bg-slate-50">
      <AdminNav />
      <main className="py-8">
        <Container>
          <PageHeader
            title="Admin Overview"
            description={`Welcome back, ${session.name}`}
          />
          <FlashBanner ok={sp.flash === "ok" ? sp.msg : undefined} err={sp.flash === "err" ? sp.msg : undefined} />
          <div className="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <StatCard icon="📋" label="Total problems" value={problems.length} tone="blue" />
            <StatCard icon="🔍" label="Pending review" value={pendingReview} tone="amber" />
            <StatCard icon="🧩" label="Awaiting assignment" value={awaitingAssignment} tone="blue" />
            <StatCard icon="🔧" label="Active responses" value={activeCount} tone="green" />
          </div>
          <div className="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <StatCard icon="✅" label="Resolved" value={resolvedCount} tone="slate" />
            <StatCard icon="🛡️" label="Active teams" value={activeTeams} tone="green" />
            <StatCard icon="⏳" label="Pending approvals" value={pendingTeams} tone="amber" />
            <StatCard icon="🚫" label="Banned users" value={bannedUsers} tone="red" />
          </div>

          <div className="mt-6 grid gap-6 lg:grid-cols-3">
            <Card className="lg:col-span-2">
              <CardHeader
                icon={<span aria-hidden>🗺️</span>}
                title="Problem Map Overview"
                action={
                  <span className="flex items-center gap-2 text-xs font-semibold text-emerald-600">
                    <span className="h-2 w-2 rounded-full bg-emerald-500" /> Live
                  </span>
                }
              />
              <CardBody className="pt-4">
                {markers.length === 0 ? (
                  <div className="flex h-64 items-center justify-center rounded-xl border border-dashed border-slate-300 text-sm text-slate-400">
                    No located problems yet.
                  </div>
                ) : (
                  <ProblemMap markers={markers} height={420} />
                )}
              </CardBody>
            </Card>

            <Card>
              <CardHeader
                icon={<span aria-hidden>🚨</span>}
                title="Needs attention"
                action={
                  <ButtonLink href="/admin/problems" variant="secondary" size="sm">
                    Manage
                  </ButtonLink>
                }
              />
              <CardBody className="p-3">
                {needsAction.length === 0 ? (
                  <p className="px-3 py-8 text-center text-sm text-slate-400">
                    No problems waiting for review or assignment.
                  </p>
                ) : (
                  <ul className="divide-y divide-slate-100">
                    {needsAction.map((p) => (
                      <li key={p.problem_id} className="flex items-center justify-between gap-3 px-3 py-3">
                        <div>
                          <p className="text-sm font-semibold capitalize text-slate-800">
                            #{p.problem_id} — {p.category}
                          </p>
                          <p className="text-xs text-slate-400">{p.created_at.toLocaleString()}</p>
                        </div>
                        <StatusBadge status={p.status} />
                      </li>
                    ))}
                  </ul>
                )}
              </CardBody>
            </Card>
          </div>
        </Container>
      </main>
    </div>
  );
}