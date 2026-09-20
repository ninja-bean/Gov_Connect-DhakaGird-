import type { Metadata } from "next";
import { requireRole } from "@/lib/auth/guards";
import LogoutButton from "@/components/logout-button";

export const metadata: Metadata = { title: "Response Dashboard | GovConnect" };

export default async function ResponseDashboard() {
  const session = await requireRole("response");

  return (
    <div className="min-h-screen bg-slate-50">
      <header className="border-b border-slate-200 bg-white">
        <div className="mx-auto flex max-w-5xl items-center justify-between px-6 py-4">
          <div>
            <p className="text-sm text-slate-500">Response team</p>
            <h1 className="text-xl font-bold text-slate-900">{session.name}</h1>
          </div>
          <LogoutButton />
        </div>
      </header>
      <main className="mx-auto max-w-5xl px-6 py-16 text-center text-sm text-slate-400">
        Response team dashboard is coming in the next step.
      </main>
    </div>
  );
}