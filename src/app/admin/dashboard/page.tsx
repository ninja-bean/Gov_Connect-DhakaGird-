import type { Metadata } from "next";
import { requireRole } from "@/lib/auth/guards";
import LogoutButton from "@/components/logout-button";

export const metadata: Metadata = { title: "Admin Dashboard | GovConnect" };

export default async function AdminDashboard() {
  const session = await requireRole("admin");

  return (
    <div className="min-h-screen bg-slate-50">
      <header className="border-b border-slate-200 bg-white">
        <div className="mx-auto flex max-w-5xl items-center justify-between px-6 py-4">
          <div>
            <p className="text-sm text-slate-500">Administrator</p>
            <h1 className="text-xl font-bold text-slate-900">{session.name}</h1>
          </div>
          <LogoutButton />
        </div>
      </header>
      <main className="mx-auto max-w-5xl px-6 py-16 text-center text-sm text-slate-400">
        Admin dashboard is coming in the next step.
      </main>
    </div>
  );
}