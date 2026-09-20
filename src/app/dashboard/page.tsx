import type { Metadata } from "next";
import Link from "next/link";
import { db } from "@/lib/db";
import { requireRole } from "@/lib/auth/guards";
import { isCurrentlyBanned, SOS_CATEGORY } from "@/lib/problems";
import CitizenNav from "@/components/citizen-nav";
import SosButton from "@/components/sos-button";
import ProblemMap, { type MapMarker } from "@/components/problem-map";

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
  const xp = reports.reduce(
    (acc, r) => acc + (r.status === "resolved" ? 50 : 10),
    0,
  );
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
        <div className="mx-auto mt-6 max-w-6xl px-6">
          <div className="flex items-center justify-between gap-4 rounded-xl border border-red-300 bg-red-50 p-4 text-sm text-red-700">
            <span>
              <strong>Account restricted.</strong> You cannot submit new reports.
            </span>
            <Link href="/appeal" className="rounded-lg bg-red-600 px-4 py-2 font-semibold text-white">
              Lodge appeal
            </Link>
          </div>
        </div>
      )}

      <main className="mx-auto max-w-6xl px-6 py-8">
        <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
          <div className="rounded-2xl border border-slate-200 bg-white p-6">
            <p className="text-sm text-slate-500">My reports</p>
            <p className="mt-1 text-3xl font-bold text-slate-900">{total}</p>
          </div>
          <div className="rounded-2xl border border-slate-200 bg-white p-6">
            <p className="text-sm text-slate-500">SOS alerts</p>
            <p className="mt-1 text-3xl font-bold text-red-600">{sos}</p>
          </div>
          <div className="rounded-2xl border border-slate-200 bg-white p-6">
            <p className="text-sm text-slate-500">Resolved</p>
            <p className="mt-1 text-3xl font-bold text-green-600">{resolved}</p>
          </div>
          <div className="rounded-2xl border border-slate-200 bg-white p-6">
            <p className="text-sm text-slate-500">City Watch · Level {level}</p>
            <p className="mt-1 text-3xl font-bold text-blue-600">{xp} XP</p>
          </div>
        </div>

        <div className="mt-6 grid gap-4 sm:grid-cols-2">
          <Link
            href="/report"
            className="flex items-center justify-center gap-2 rounded-xl border-2 border-slate-900 bg-white px-6 py-5 text-base font-bold text-slate-900 transition hover:bg-slate-900 hover:text-white"
          >
            ✏️ Submit Report
          </Link>
          <SosButton />
        </div>

        <div className="mt-8 grid gap-6 lg:grid-cols-3">
          <div className="lg:col-span-2">
            <div className="rounded-2xl border border-slate-200 bg-white p-5">
              <div className="mb-4 flex items-center justify-between">
                <h2 className="font-bold text-slate-900">Live Grid Map</h2>
                <span className="flex items-center gap-2 text-xs font-semibold text-green-600">
                  <span className="h-2 w-2 rounded-full bg-green-500" /> Online
                </span>
              </div>
              <ProblemMap markers={markers} height={400} />
            </div>

            <div className="mt-6 rounded-2xl border border-slate-200 bg-white">
              <h2 className="border-b border-slate-200 p-5 font-bold text-slate-900">Recent activity</h2>
              {recent.length === 0 ? (
                <p className="p-8 text-center text-sm text-slate-500">No recent reports.</p>
              ) : (
                <ul>
                  {recent.map((r) => {
                    const isSos = r.category.toUpperCase() === SOS_CATEGORY;
                    return (
                      <li
                        key={r.problem_id}
                        className={`flex items-center justify-between gap-4 border-b border-slate-100 p-4 last:border-0 ${
                          isSos ? "bg-red-50/60" : ""
                        }`}
                      >
                        <div>
                          <p className="font-bold text-slate-900">
                            {isSos ? "🚨 SOS ALERT" : r.category}
                          </p>
                          <p className="text-xs text-slate-500">
                            {r.created_at.toLocaleString()}
                          </p>
                        </div>
                        <span
                          className={`rounded-md px-3 py-1 text-xs font-bold uppercase ${
                            isSos
                              ? "bg-red-100 text-red-700"
                              : "bg-slate-100 text-slate-600"
                          }`}
                        >
                          {r.status}
                        </span>
                      </li>
                    );
                  })}
                </ul>
              )}
            </div>
          </div>

          <div className="space-y-6">
            <div className="rounded-xl border-l-4 border-amber-500 bg-amber-50 p-5 text-amber-900">
              <p className="mb-2 flex items-center gap-2 font-extrabold">📢 GOV NOTICE</p>
              <p className="text-sm leading-relaxed">
                {notice?.message ??
                  "Regular grid maintenance is scheduled today. Please report any unexpected outages immediately."}
              </p>
              <p className="mt-3 text-xs opacity-70">
                Issued {notice ? notice.created_at.toLocaleDateString() : new Date().toLocaleDateString()}
              </p>
            </div>

            <div className="rounded-2xl border border-slate-200 bg-white p-6">
              <div className="flex items-center gap-4">
                <div className="flex h-16 w-16 items-center justify-center overflow-hidden rounded-2xl bg-gradient-to-br from-slate-900 to-slate-600 text-2xl text-white">
                  {user.profile_pic ? (
                    <img src={`/uploads/profile_pics/${user.profile_pic}`} alt="You" className="h-full w-full object-cover" />
                  ) : (
                    "👤"
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
            </div>
          </div>
        </div>
      </main>
    </div>
  );
}