import type { Metadata } from "next";
import Link from "next/link";
import { db } from "@/lib/db";
import { requireRole } from "@/lib/auth/guards";
import { SOS_CATEGORY } from "@/lib/problems";
import CitizenNav from "@/components/citizen-nav";

export const metadata: Metadata = { title: "My Reports | GovConnect" };

const badge = (status: string) => {
  const map: Record<string, string> = {
    pending: "bg-amber-100 text-amber-700",
    "in-progress": "bg-blue-100 text-blue-700",
    resolved: "bg-green-100 text-green-700",
    rejected: "bg-red-100 text-red-700",
  };
  return `rounded-md px-3 py-1 text-xs font-bold uppercase ${map[status] ?? "bg-slate-100 text-slate-600"}`;
};

export default async function MyProblemsPage() {
  const session = await requireRole("user");
  const problems = await db.problems.findMany({
    where: { user_id: session.userId },
    orderBy: { created_at: "desc" },
  });

  return (
    <div className="min-h-screen bg-slate-50">
      <CitizenNav />
      <main className="mx-auto max-w-4xl px-6 py-8">
        <h1 className="mb-6 text-2xl font-bold text-slate-900">My reports</h1>
        {problems.length === 0 ? (
          <div className="rounded-2xl border border-dashed border-slate-300 bg-white p-10 text-center text-sm text-slate-500">
            No reports yet — use “Submit Report” to share a problem.
          </div>
        ) : (
          <ul className="space-y-4">
            {problems.map((p) => {
              const isSos = p.category.toUpperCase() === SOS_CATEGORY;
              return (
                <li key={p.problem_id}>
                  <Link
                    href={`/my-problems/${p.problem_id}`}
                    className="block rounded-2xl border border-slate-200 bg-white p-5 transition hover:border-blue-400 hover:shadow-sm"
                  >
                    <div className="flex items-center justify-between gap-4">
                      <div>
                        <p className="font-bold capitalize text-slate-900">
                          {isSos ? "🚨 SOS ALERT" : p.category}
                        </p>
                        <p className="mt-0.5 text-sm text-slate-500">
                          {p.location_name || p.location || "Dhaka"}
                        </p>
                        <p className="text-xs text-slate-400">
                          {p.created_at.toLocaleString()}
                        </p>
                      </div>
                      <div className="flex flex-col items-end gap-1">
                        <span className={badge(p.status)}>{p.status}</span>
                        <span className="text-xs capitalize text-slate-400">
                          {p.priority} priority
                        </span>
                      </div>
                    </div>
                    {p.description && (
                      <p className="mt-3 line-clamp-2 text-sm text-slate-600">
                        {p.description}
                      </p>
                    )}
                  </Link>
                </li>
              );
            })}
          </ul>
        )}
      </main>
    </div>
  );
}