import type { Metadata } from "next";
import { db } from "@/lib/db";
import { requireRole } from "@/lib/auth/guards";
import { runClickAction } from "@/actions/admin";
import AdminNav from "@/components/admin-nav";
import FlashBanner from "@/components/admin/flash-banner";
import { Container, PageHeader } from "@/components/ui/page-header";
import { Card, CardBody, CardHeader } from "@/components/ui/card";
import { Badge, type Tone } from "@/components/ui/badge";
import { EmptyState } from "@/components/ui/empty-state";

export const metadata: Metadata = { title: "Unban Appeals | GovConnect Admin" };

const statusTone: Record<string, Tone> = {
  pending: "amber",
  approved: "green",
  rejected: "red",
  reviewed: "blue",
};

function AppealActionForm({
  action,
  requestId,
  userId,
  label,
  className,
}: {
  action: "approveAppeal" | "rejectAppeal";
  requestId: number;
  userId: number;
  label: string;
  className: string;
}) {
  return (
    <form action={runClickAction}>
      <input type="hidden" name="action" value={action} />
      <input type="hidden" name="requestId" value={requestId} />
      <input type="hidden" name="userId" value={userId} />
      <input type="hidden" name="back" value="/admin/appeals" />
      <button type="submit" className={className}>
        {label}
      </button>
    </form>
  );
}

export default async function AdminAppealsPage({
  searchParams,
}: {
  searchParams: Promise<{ flash?: string; msg?: string }>;
}) {
  await requireRole("admin");
  const sp = await searchParams;

  const requests = await db.unban_requests.findMany({
    include: { users: { select: { name: true, email: true, phone: true } } },
    orderBy: [{ status: "asc" }, { created_at: "desc" }],
  });

  return (
    <div className="min-h-screen bg-slate-50">
      <AdminNav />
      <main className="py-8">
        <Container>
          <PageHeader
            title="Unban Appeals"
            description="Review citizen appeals. Approving unbans the account and closes the appeal."
          />
          <FlashBanner ok={sp.flash === "ok" ? sp.msg : undefined} err={sp.flash === "err" ? sp.msg : undefined} />

          <Card className="mt-6">
            <CardBody>
              {requests.length === 0 ? (
                <EmptyState
                  icon="📭"
                  title="No appeals"
                  description="Citizen appeals will appear here."
                />
              ) : (
                <ul className="space-y-4">
                  {requests.map((r) => (
                    <li key={r.id} className="rounded-xl border border-slate-200 p-5">
                      <div className="flex flex-wrap items-center justify-between gap-3">
                        <div>
                          <p className="font-semibold text-slate-900">
                            {r.users.name}
                            <span className="ml-2 text-sm font-normal text-slate-400">
                              {r.users.email}
                            </span>
                          </p>
                          <p className="text-xs text-slate-400">{r.created_at.toLocaleString()}</p>
                        </div>
                        <Badge tone={statusTone[r.status] ?? "slate"}>
                          {r.status.charAt(0).toUpperCase() + r.status.slice(1)}
                        </Badge>
                      </div>

                      <p className="mt-3 rounded-lg bg-slate-50 p-3 text-sm text-slate-700">
                        {r.message ?? r.reason ?? "No reason provided."}
                      </p>

                      {r.admin_response && (
                        <p className="mt-2 rounded-lg bg-blue-50 p-3 text-sm text-blue-800">
                          <span className="font-semibold">Admin response:</span> {r.admin_response}
                        </p>
                      )}

                      {r.status === "pending" && (
                        <div className="mt-4 flex flex-wrap gap-3">
                          <AppealActionForm
                            action="approveAppeal"
                            requestId={r.id}
                            userId={r.user_id}
                            label="✅ Approve & unban"
                            className="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-emerald-500 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-600"
                          />
                          <AppealActionForm
                            action="rejectAppeal"
                            requestId={r.id}
                            userId={r.user_id}
                            label="❌ Reject"
                            className="rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-red-500 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-red-600"
                          />
                        </div>
                      )}
                    </li>
                  ))}
                </ul>
              )}
            </CardBody>
          </Card>
        </Container>
      </main>
    </div>
  );
}