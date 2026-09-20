import type { Metadata } from "next";
import { db } from "@/lib/db";
import { requireRole } from "@/lib/auth/guards";
import { isCurrentlyBanned } from "@/lib/problems";
import CitizenNav from "@/components/citizen-nav";
import AppealForm from "./appeal-form";

export const metadata: Metadata = { title: "Appeal | GovConnect" };

export default async function AppealPage() {
  const session = await requireRole("user");

  const [user, appeals] = await Promise.all([
    db.users.findUniqueOrThrow({
      where: { user_id: session.userId },
      select: { is_banned: true, ban_until: true },
    }),
    db.unban_requests.findMany({
      where: { user_id: session.userId },
      orderBy: { created_at: "desc" },
    }),
  ]);

  const banned = isCurrentlyBanned(user);

  return (
    <div className="min-h-screen bg-slate-50">
      <CitizenNav />
      <main className="mx-auto max-w-2xl px-6 py-8">
        <div className="rounded-2xl border border-slate-200 bg-white">
          <div className="border-b border-slate-200 p-6">
            <h1 className="text-xl font-bold text-slate-900">Account appeal</h1>
          </div>
          <div className="p-6">
            {banned ? (
              <>
                <p className="mb-4 text-sm text-slate-600">
                  Your account is currently restricted. Submit an appeal to ask the
                  administrators to review your case.
                </p>
                <AppealForm />
              </>
            ) : (
              <p className="rounded-lg bg-slate-50 px-4 py-3 text-sm text-slate-700">
                Your account is active — no appeal needed.
              </p>
            )}

            {appeals.length > 0 && (
              <div className="mt-8">
                <h2 className="mb-3 font-bold text-slate-900">Previous appeals</h2>
                <ul className="space-y-3">
                  {appeals.map((a) => (
                    <li key={a.id} className="rounded-xl border border-slate-200 p-4">
                      <div className="flex items-center justify-between gap-3">
                        <span className="text-sm font-semibold capitalize text-slate-800">
                          {a.status}
                        </span>
                        <span className="text-xs text-slate-400">
                          {a.created_at.toLocaleString()}
                        </span>
                      </div>
                      <p className="mt-2 text-sm text-slate-600">{a.message ?? a.reason}</p>
                      {a.admin_response && (
                        <p className="mt-2 rounded-lg bg-slate-50 p-3 text-xs text-slate-500">
                          <strong>Admin response:</strong> {a.admin_response}
                        </p>
                      )}
                    </li>
                  ))}
                </ul>
              </div>
            )}
          </div>
        </div>
      </main>
    </div>
  );
}