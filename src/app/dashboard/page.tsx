import type { Metadata } from "next";
import Link from "next/link";
import Image from "next/image";
import { db } from "@/lib/db";
import { requireRole } from "@/lib/auth/guards";
import { isCurrentlyBanned, SOS_CATEGORY } from "@/lib/problems";
import CitizenNav from "@/components/citizen-nav";
import SosButton from "@/components/sos-button";
import NewsTicker from "@/components/news-ticker";
import WeatherCard from "@/components/weather-card";
import { AnalyticsDoughnut } from "@/components/analytics-doughnut";
import ProblemMap, { type MapMarker } from "@/components/problem-map";
import { Container } from "@/components/ui/page-header";
import { StatCard } from "@/components/ui/stat-card";
import { Card, CardBody, CardHeader } from "@/components/ui/card";
import { StatusBadge } from "@/components/ui/status-badge";
import { ButtonLink } from "@/components/ui/button";
import { EmptyState } from "@/components/ui/empty-state";

const CHART_COLORS = {
  traffic: "#1E293B",
  water: "#3B82F6",
  waste: "#10B981",
  sos: "#EF4444",
  other: "#94A3B8",
} as const;

const TICKER_FALLBACK = [
  "⚡ Grid system operating normally.",
  "📢 Report any issues immediately.",
  "🌧️ Check weather updates before travel.",
];

async function countActiveSos(sinceHours: number): Promise<number> {
  return db.problems.count({
    where: {
      category: SOS_CATEGORY,
      status: "pending",
      created_at: { gte: new Date(Date.now() - sinceHours * 60 * 60 * 1000) },
    },
  });
}

export const metadata: Metadata = { title: "Dashboard | GovConnect" };

export default async function CitizenDashboard() {
  const session = await requireRole("user");

  const [user, reports, mapReports, recent, notice, resolvedFeed, pendingFeed, sosFeed] =
    await Promise.all([
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
      db.problems.findMany({
        where: { status: "resolved" },
        select: { category: true, location_name: true },
        orderBy: { updated_at: "desc" },
        take: 8,
      }),
      db.problems.count({ where: { status: "pending" } }),
      countActiveSos(24),
    ]);

  const total = reports.length;
  const sos = reports.filter((r) => r.category.toUpperCase() === SOS_CATEGORY).length;
  const resolved = reports.filter((r) => r.status === "resolved").length;
  const xp = reports.reduce((acc, r) => acc + (r.status === "resolved" ? 50 : 10), 0);
  const level = Math.floor(xp / 100) + 1;

  const tickerItems: string[] = resolvedFeed.map(
    (r) =>
      `✅ Solved: ${r.category.charAt(0).toUpperCase() + r.category.slice(1)} issue in ${
        r.location_name || "Dhaka area"
      }`,
  );
  tickerItems.push(`⚡ ${pendingFeed} reports pending verification citywide`);
  if (sosFeed > 0) {
    tickerItems.push(`🚨 ${sosFeed} active emergency alerts in last 24 hours`);
  }
  if (tickerItems.length === 0) {
    tickerItems.push(...TICKER_FALLBACK);
  }

  const chartCounts = { traffic: 0, water: 0, waste: 0, sos: 0, other: 0 };
  for (const r of reports) {
    const cat = r.category.toLowerCase();
    if (cat === "traffic") chartCounts.traffic++;
    else if (cat === "water") chartCounts.water++;
    else if (cat === "waste") chartCounts.waste++;
    else if (cat === "sos") chartCounts.sos++;
    else chartCounts.other++;
  }
  const chartSlices = [
    { label: "Traffic", value: chartCounts.traffic, color: CHART_COLORS.traffic },
    { label: "Water", value: chartCounts.water, color: CHART_COLORS.water },
    { label: "Waste", value: chartCounts.waste, color: CHART_COLORS.waste },
    { label: "SOS", value: chartCounts.sos, color: CHART_COLORS.sos },
    { label: "Other", value: chartCounts.other, color: CHART_COLORS.other },
  ];

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
          <NewsTicker items={tickerItems} />

          <div className="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-5">
            <StatCard icon="📋" label="My reports" value={total} tone="blue" />
            <StatCard icon="🚨" label="SOS alerts" value={sos} tone="red" />
            <StatCard icon="✅" label="Resolved" value={resolved} tone="green" />
            <StatCard icon="🏙️" label={`City Watch · Level ${level}`} value={`${xp} XP`} tone="amber" />
            <WeatherCard />
          </div>

          <div className="mt-6 grid gap-4 sm:grid-cols-2">
            <Link
              href="/report"
              className="flex items-center justify-center gap-2 rounded-xl border-2 border-slate-900 bg-white px-6 py-4 text-base font-bold text-slate-900 transition hover:bg-ink hover:text-white focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600"
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
                    <div className="flex size-16 items-center justify-center overflow-hidden rounded-2xl bg-gradient-to-br from-ink to-ink-fade text-2xl text-white">
                      {user.profile_pic ? (
                        <Image
                          src={`/uploads/profile_pics/${user.profile_pic}`}
                          alt={`${user.name}'s avatar`}
                          width={64}
                          height={64}
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

              <Card>
                <CardHeader icon={<span aria-hidden>📊</span>} title="Report Analytics" />
                <CardBody className="pt-4">
                  <AnalyticsDoughnut slices={chartSlices} />
                </CardBody>
              </Card>
            </div>
          </div>
        </Container>
      </main>
    </div>
  );
}