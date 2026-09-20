import type { Metadata } from "next";
import { cache } from "react";
import { db } from "@/lib/db";
import { requireRole } from "@/lib/auth/guards";
import LogoutButton from "@/components/logout-button";

export const metadata: Metadata = { title: "Dashboard | GovConnect" };

const loadStats = cache(async (userId: number) => {
  const [problems, total, active, user] = await Promise.all([
    db.problems.findMany({
      where: { user_id: userId },
      orderBy: { created_at: "desc" },
      take: 5,
    }),
    db.problems.count({ where: { user_id: userId } }),
    db.problems.count({ where: { user_id: userId, status: { not: "resolved" } } }),
    db.users.findUniqueOrThrow({
      where: { user_id: userId },
      select: { name: true },
    }),
  ]);
  return { problems, total, active, name: user.name };
});

export default async function CitizenDashboard() {
  const session = await requireRole("user");
  const { problems, total, active, name } = await loadStats(session.userId);

  return (
    <div className="min-h-screen bg-slate-50">
      <header className="border-b border-slate-200 bg-white">
        <div className="mx-auto flex max-w-5xl items-center justify-between px-6 py-4">
          <div>
            <p className="text-sm text-slate-500">Welcome back,</p>
            <h1 className="text-xl font-bold text-slate-900">{name}</h1>
          </div>
          <LogoutButton />
        </div>
      </header>

      <main className="mx-auto max-w-5xl px-6 py-8">
        <div className="grid gap-4 sm:grid-cols-3">
          <div className="rounded-2xl border border-slate-200 bg-white p-6">
            <p className="text-sm text-slate-500">Total reports</p>
            <p className="mt-1 text-3xl font-bold text-slate-900">{total}</p>
          </div>
          <div className="rounded-2xl border border-slate-200 bg-white p-6">
            <p className="text-sm text-slate-500">In progress</p>
            <p className="mt-1 text-3xl font-bold text-amber-600">{active}</p>
          </div>
          <div className="rounded-2xl border border-slate-200 bg-white p-6">
            <p className="text-sm text-slate-500">Resolved</p>
            <p className="mt-1 text-3xl font-bold text-green-600">
              {total - active}
            </p>
          </div>
        </div>

        <section className="mt-8">
          <h2 className="mb-3 text-lg font-semibold text-slate-900">
            Recent reports
          </h2>
          {problems.length === 0 ? (
            <div className="rounded-2xl border border-dashed border-slate-300 bg-white p-8 text-center text-sm text-slate-500">
              No reports yet — submit your first problem to get help.
            </div>
          ) : (
            <ul className="space-y-3">
              {problems.map((p) => (
                <li
                  key={p.problem_id}
                  className="rounded-2xl border border-slate-200 bg-white p-5"
                >
                  <div className="flex items-center justify-between gap-4">
                    <h3 className="font-semibold capitalize text-slate-900">{p.category}</h3>
                    <span className="rounded-full bg-slate-100 px-3 py-1 text-xs font-medium capitalize text-slate-600">
                      {p.status}
                    </span>
                  </div>
                  <p className="mt-2 text-sm text-slate-600">{p.description}</p>
                </li>
              ))}
            </ul>
          )}
        </section>
      </main>
    </div>
  );
}