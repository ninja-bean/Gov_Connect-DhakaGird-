import type { Metadata } from "next";
import Link from "next/link";
import { db } from "@/lib/db";
import { requireRole } from "@/lib/auth/guards";
import { isCurrentlyBanned, SOS_CATEGORY } from "@/lib/problems";
import CitizenNav from "@/components/citizen-nav";
import SosButton from "@/components/sos-button";
import ProblemMap, { type MapMarker } from "@/components/problem-map";
import { Container } from "@/components/ui/page-header";
import { StatCard } from "@/components/ui/stat-card";
import { Card, CardBody, CardHeader } from "@/components/ui/card";
import { StatusBadge } from "@/components/ui/status-badge";
import { ButtonLink } from "@/components/ui/button";
import { EmptyState } from "@/components/ui/empty-state";

export const metadata: Metadata = { title: "Dashboard | GovConnect" };

export default async function CitizenDashboard() {
  const session = await requireRole("user");

  const [user, reports, mapReports, recent, notice] = await Promise.all([
    db.users.findUniqueOrThrow({
      where: { user_id: session.userId },
      select: { name: true, is_banned: true, ban_until: true, profile_pic: true },
    }),
    db.problems.findMany({
      where: { user_id: session.userId },
      select: { category: true, status: true },
    }),
    db.problems.findMany({
      where: {
        latitude: { not: null },
        longitude: { not: null },
        status: { notIn: ["resolved", "rejected"] },
      },
      select: { latitude: true, longitude: true, category: true, status: true, created_at: true },
      orderBy: { created_at: "desc" },
      take: 100,
    }),
    db.problems.findMany({
      where: { user_id: session.userId },
      orderBy: { created_at: "desc" },
      take: 6,
    }),
    db.warnings.findFirst({
      where: { OR: [{ user_id: null }, { user_id: session.userId }] },
      orderBy: { created_at: "desc" },
    }),
  ]);

  const total = reports.length;
  const sos = reports.filter((r) => r.category.toUpperCase() === SOS_CATEGORY).length;
  const resolved = reports.filter((r) => r.status === "resolved").length;
  const xp = reports.reduce((acc, r) => acc + (r.status === "resolved" ? 50 : 10), 0);
  const level = Math.floor(xp / 100) + 1;

  const markers: MapMarker[] = mapReports
    .filter((r) => r.latitude !== null && r.longitude !== null)
    .map((r) => ({
      lat: Number(r.latitude),
      lng: Number(r.longitude),
      category: r.category,
      status: r.status,
      createdAt: r.created_at.toISOString(),
    }));

  const banned = isCurrentlyBanned(user);

  return (
    <div className="min-h-screen bg-slate-50">
      <CitizenNav />
      {banned && (
        <Container className="pt-6">
          <div className="flex flex-col items-start gap-3 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-800 sm:flex-row sm:items-center sm:justify-between">
            <span>
              <strong>Account restricted.</strong> You cannot submit new reports.
            </span>
            <ButtonLink href="/appeal" variant="danger" className="shrink-0">
              Lodge appeal
            </ButtonLink>
          </div>
        </Container>
      )}

      <main className="py-8">
        <Container>
          <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <StatCard icon="📋" label="My reports" value={total} tone="blue" />
            <StatCard icon="🚨" label="SOS alerts" value={sos} tone="red" />
            <StatCard icon="✅" label="Resolved" value={resolved} tone="green" />
            <StatCard icon="🏙️" label={`City Watch · Level ${level}`} value={`${xp} XP`} tone="amber" />
          </div>

          <div className="mt-6 grid gap-4 sm:grid-cols-2">
            <Link
              href="/report"
              className="flex items-center justify-center gap-2 rounded-xl border-2 border-slate-900 bg-white px-6 py-4 text-base font-bold text-slate-900 transition hover:bg-slate-900 hover:text-white focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600"
            >
              ✏️ Submit Report
            </Link>
            <SosButton />
          </div>

          <div className="mt-8 grid gap-6 lg:grid-cols-3">
            <div className="space-y-6 lg:col-span-2">
              <Card>
                <CardHeader
                  icon={<span aria-hidden>🗺️</span>}
                  title="Live Grid Map"
                  action={
                    <span className="flex items-center gap-2 text-xs font-semibold text-emerald-600">
                      <span className="h-2 w-2 rounded-full bg-emerald-500" /> Online
                    </span>
                  }
                />
                <CardBody className="pt-4">
                  <ProblemMap markers={markers} height={400} />
                </CardBody>
              </Card>

              <Card>
                <CardHeader icon={<span aria-hidden>🕒</span>} title="Recent activity" />
                {recent.length === 0 ? (
                  <EmptyState icon="🗂️" title="No recent reports" description="Submit your first report to get started." />
                ) : (
                  <ul className="divide-y divide-slate-100">
                    {recent.map((r) => {
                      const isSos = r.category.toUpperCase() === SOS_CATEGORY;
                      return (
                        <li
                          key={r.problem_id}
                          className={`flex flex-wrap items-center justify-between gap-3 px-6 py-4 ${isSos ? "bg-red-50/60" : ""}`}
                        >
                          <div>
                            <p className="font-bold text-slate-900">
                              {isSos ? "🚨 SOS ALERT" : r.category}
                            </p>
                            <p className="text-xs text-slate-500">{r.created_at.toLocaleString()}</p>
                          </div>
                          <StatusBadge status={r.status} />
                        </li>
                      );
                    })}
                  </ul>
                )}
              </Card>
            </div>

            <div className="space-y-6">
              <Card className="border-l-4 border-l-amber-500 bg-amber-50">
                <CardBody>
                  <p className="mb-2 flex items-center gap-2 font-extrabold text-amber-900">
                    📢 GOV NOTICE
                  </p>
                  <p className="text-sm leading-relaxed text-amber-900">
                    {notice?.message ??
                      "Regular grid maintenance is scheduled today. Please report any unexpected outages immediately."}
                  </p>
                  <p className="mt-3 text-xs text-amber-900/70">
                    Issued{" "}
                    {notice ? notice.created_at.toLocaleDateString() : new Date().toLocaleDateString()}
                  </p>
                </CardBody>
              </Card>

              <Card>
                <CardBody>
                  <div className="flex items-center gap-4">
                    <div className="flex size-16 items-center justify-center overflow-hidden rounded-2xl bg-gradient-to-br from-slate-900 to-slate-600 text-2xl text-white">
                      {user.profile_pic ? (
                        <img
                          src={`/uploads/profile_pics/${user.profile_pic}`}
                          alt={`${user.name}'s avatar`}
                          className="h-full w-full object-cover"
                        />
                      ) : (
                        <span aria-hidden>👤</span>
                      )}
                    </div>
                    <div>
                      <p className="text-xl font-extrabold text-slate-900">{user.name}</p>
                      <p className="text-sm font-semibold text-slate-500">🏙️ City Watcher</p>
                    </div>
                  </div>
                  <div className="mt-5 h-2.5 w-full overflow-hidden rounded-full bg-slate-100">
                    <div
                      className="h-full rounded-full bg-gradient-to-r from-blue-500 to-indigo-500"
                      style={{ width: `${Math.min(100, xp / 10)}%` }}
                    />
                  </div>
                  <div className="mt-2 flex justify-between text-xs font-bold text-slate-500">
                    <span>{xp} XP</span>
                    <span>Next: Grid Guardian</span>
                  </div>
                </CardBody>
              </Card>
            </div>
          </div>
        </Container>
      </main>
    </div>
  );
}