import type { Metadata } from "next";
import Link from "next/link";
import { db } from "@/lib/db";
import { requireRole } from "@/lib/auth/guards";
import { SOS_CATEGORY } from "@/lib/problems";
import CitizenNav from "@/components/citizen-nav";
import { Container, PageHeader } from "@/components/ui/page-header";
import { StatusBadge, PriorityBadge } from "@/components/ui/status-badge";
import { Card } from "@/components/ui/card";
import { EmptyState } from "@/components/ui/empty-state";
import { ButtonLink } from "@/components/ui/button";

export const metadata: Metadata = { title: "My Reports | GovConnect" };

export default async function MyProblemsPage() {
  const session = await requireRole("user");
  const problems = await db.problems.findMany({
    where: { user_id: session.userId },
    orderBy: { created_at: "desc" },
  });

  return (
    <div className="min-h-screen bg-slate-50">
      <CitizenNav />
      <main className="py-8">
        <Container>
          <PageHeader
            title="My reports"
            description="Everything you've reported, in one place — track progress and leave feedback."
          />
          {problems.length === 0 ? (
            <Card className="mt-6">
              <EmptyState
                icon="🗂️"
                title="No reports yet"
                description="Use “Submit Report” to share a problem in your community."
                action={<ButtonLink href="/report">Submit Report</ButtonLink>}
              />
            </Card>
          ) : (
            <ul className="mt-6 space-y-4">
              {problems.map((p) => {
                const isSos = p.category.toUpperCase() === SOS_CATEGORY;
                return (
                  <li key={p.problem_id}>
                    <Link
                      href={`/my-problems/${p.problem_id}`}
                      className="block rounded-2xl border border-slate-200 bg-white p-5 transition hover:border-red-400 hover:shadow-sm focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-red-600"
                    >
                      <div className="flex flex-wrap items-start justify-between gap-4">
                        <div className="min-w-0">
                          <p className="font-bold capitalize text-slate-900">
                            {isSos ? "🚨 SOS ALERT" : p.category}
                          </p>
                          <p className="mt-0.5 truncate text-sm text-slate-500">
                            {p.location_name || p.location || "Dhaka"}
                          </p>
                          <p className="text-xs text-slate-400">{p.created_at.toLocaleString()}</p>
                        </div>
                        <div className="flex shrink-0 flex-col items-end gap-1.5">
                          <StatusBadge status={p.status} />
                          <PriorityBadge priority={p.priority} />
                        </div>
                      </div>
                      {p.description && (
                        <p className="mt-3 line-clamp-2 text-sm text-slate-600">{p.description}</p>
                      )}
                    </Link>
                  </li>
                );
              })}
            </ul>
          )}
        </Container>
      </main>
    </div>
  );
}