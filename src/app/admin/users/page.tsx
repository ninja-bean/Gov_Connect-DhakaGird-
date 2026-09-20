import type { Metadata } from "next";
import { db } from "@/lib/db";
import { requireRole } from "@/lib/auth/guards";
import { runClickAction, banUser, sendWarning } from "@/actions/admin";
import AdminNav from "@/components/admin-nav";
import FlashBanner from "@/components/admin/flash-banner";
import { Container, PageHeader } from "@/components/ui/page-header";
import { Card, CardBody, CardHeader } from "@/components/ui/card";
import { Badge, type Tone } from "@/components/ui/badge";
import { EmptyState } from "@/components/ui/empty-state";
import { Input } from "@/components/ui/controls";
import ConfirmSubmit from "@/components/confirm-submit";

export const metadata: Metadata = { title: "Users | GovConnect Admin" };

const roleTone: Record<string, Tone> = { admin: "red", response: "blue", user: "slate" };

function banLabel(user: { is_banned: boolean; ban_until: string | null }): string {
  if (!user.is_banned) return "Active";
  if (user.ban_until === "permanent") return "Banned permanently";
  if (user.ban_until) return `Banned until ${user.ban_until}`;
  return "Banned";
}

function BanForm({ user, search }: { user: { user_id: number }; search: string }) {
  return (
    <form action={banUser}>
      <input type="hidden" name="userId" value={user.user_id} />
      <input type="hidden" name="back" value={`/admin/users?q=${encodeURIComponent(search)}`} />
      <div className="flex flex-wrap items-center gap-2">
        <Input
          type="number"
          name="banDays"
          min={1}
          max={3650}
          placeholder="Days (1–3650)"
          aria-label="Ban duration in days"
        />
        <label className="flex select-none items-center gap-1.5 text-sm font-medium text-slate-600">
          <input type="checkbox" name="permanent" value="1" className="h-4 w-4 rounded accent-red-600" />
          Permanent
        </label>
        <ConfirmSubmit label="Ban" tone="danger" />
      </div>
    </form>
  );
}

function WarningForm({ user, search }: { user: { user_id: number }; search: string }) {
  return (
    <form action={sendWarning} className="flex flex-wrap items-start gap-2">
      <input type="hidden" name="userId" value={user.user_id} />
      <input type="hidden" name="back" value={`/admin/users?q=${encodeURIComponent(search)}`} />
      <textarea
        name="message"
        required
        minLength={3}
        rows={2}
        placeholder="Warning message…"
        aria-label="Warning message"
        className="min-w-0 flex-1 rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 placeholder:text-slate-400 focus:border-blue-500 focus:outline-2 focus:outline-blue-500"
      />
      <button
        type="submit"
        className="rounded-lg bg-amber-500 px-4 py-2 text-sm font-semibold text-white transition hover:bg-amber-400 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-amber-500"
      >
        Send warning
      </button>
    </form>
  );
}

export default async function AdminUsersPage({
  searchParams,
}: {
  searchParams: Promise<{ flash?: string; msg?: string; q?: string }>;
}) {
  await requireRole("admin");
  const sp = await searchParams;
  const q = (sp.q ?? "").trim();

  const where =
    q.length > 0
      ? {
          role: ("user" as const),
          OR: [{ email: { contains: q } }, { phone: { contains: q } }],
        }
      : { role: ("user" as const) };

  const [users, warnings, bannedCount] = await Promise.all([
    db.users.findMany({
      where,
      orderBy: { created_at: "desc" },
      take: 50,
    }),
    db.warnings.findMany({ orderBy: { created_at: "desc" }, take: 20 }),
    db.users.count({ where: { is_banned: true } }),
  ]);

  const adminIds = Array.from(
    new Set(warnings.map((w) => w.admin_id).filter((x): x is number => x !== null)),
  );
  const adminNames = new Map<number, string>();
  if (adminIds.length > 0) {
    const admins = await db.users.findMany({
      where: { user_id: { in: adminIds } },
      select: { user_id: true, name: true },
    });
    for (const a of admins) adminNames.set(a.user_id, a.name);
  }

  return (
    <div className="min-h-screen bg-slate-50">
      <AdminNav />
      <main className="py-8">
        <Container>
          <PageHeader
            title="User Management"
            description="Search citizens, ban or unban accounts, and send warnings."
          />
          <FlashBanner ok={sp.flash === "ok" ? sp.msg : undefined} err={sp.flash === "err" ? sp.msg : undefined} />

          <form action="/admin/users" method="get" className="mt-6 flex flex-wrap items-center gap-2">
            <Input
              type="search"
              name="q"
              defaultValue={q}
              placeholder="Search by email or phone (e.g. pranto@gmail.com or 017…) "
              className="max-w-xl flex-1"
            />
            <button
              type="submit"
              className="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-blue-500 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600"
            >
              Search
            </button>
            {q && (
              <a href="/admin/users" className="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-600 transition hover:bg-slate-100">
                Clear
              </a>
            )}
          </form>

          <div className="mt-2 rounded-lg bg-slate-100 px-4 py-2 text-sm text-slate-600">
            {users.length} result{q.length ? ` for "${q}"` : ""} · {bannedCount} banned account{bannedCount === 1 ? "" : "s"}
          </div>

          <Card className="mt-6">
            <CardHeader title="👥 Citizens" />
            <CardBody>
              {users.length === 0 ? (
                <EmptyState
                  icon="🔍"
                  title="No citizens found"
                  description={q ? `Nothing matched "${q}".` : "No citizen accounts yet."}
                />
              ) : (
                <ul className="space-y-4">
                  {users.map((u) => (
                    <li key={u.user_id} className="rounded-xl border border-slate-200 p-5">
                      <div className="flex flex-wrap items-center justify-between gap-3">
                        <div>
                          <p className="font-semibold text-slate-900">{u.name}</p>
                          <p className="text-sm text-slate-500">
                            {u.email} {u.phone ? ` · ${u.phone}` : ""}
                          </p>
                        </div>
                        <div className="flex items-center gap-2">
                          <Badge tone={u.is_banned ? "red" : "green"}>{banLabel(u)}</Badge>
                          <Badge tone={roleTone[u.role] ?? "slate"}>{u.role}</Badge>
                        </div>
                      </div>

                      <div className="mt-4 grid gap-3 lg:grid-cols-2">
                        {u.is_banned ? (
                          <form action={runClickAction}>
                            <input type="hidden" name="action" value="unbanUser" />
                            <input type="hidden" name="userId" value={u.user_id} />
                            <input type="hidden" name="back" value={`/admin/users?q=${encodeURIComponent(q)}`} />
                            <button
                              type="submit"
                              className="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-emerald-500 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-600"
                            >
                              ✅ Unban
                            </button>
                          </form>
                        ) : (
                          <BanForm user={u} search={q} />
                        )}
                        <WarningForm user={u} search={q} />
                      </div>
                    </li>
                  ))}
                </ul>
              )}
            </CardBody>
          </Card>

          <Card className="mt-6">
            <CardHeader icon={<span aria-hidden>⚠️</span>} title="Recent warnings" />
            <CardBody>
              {warnings.length === 0 ? (
                <p className="text-sm text-slate-500">No warnings have been sent yet.</p>
              ) : (
                <ul className="space-y-2">
                  {warnings.map((w) => (
                    <li key={w.id} className="rounded-lg border border-slate-200 px-4 py-3 text-sm">
                      <div className="flex flex-wrap items-center justify-between gap-2">
                        <span className="font-semibold text-slate-700">{w.message}</span>
                        <span className="text-xs text-slate-400">{w.created_at.toLocaleString()}</span>
                      </div>
                      <p className="mt-1 text-xs text-slate-400">
                        Warning for user #{w.user_id} by {w.admin_id ? adminNames.get(w.admin_id) ?? "Admin" : "System"}
                      </p>
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