import type { Metadata } from "next";
import { requireRole } from "@/lib/auth/guards";
import CitizenNav from "@/components/citizen-nav";
import ChangePasswordForm from "./change-password-form";

export const metadata: Metadata = { title: "Change Password | GovConnect" };

export default async function ChangePasswordPage() {
  await requireRole("user");
  return (
    <div className="min-h-screen bg-slate-50">
      <CitizenNav />
      <main className="mx-auto max-w-lg px-6 py-8">
        <div className="rounded-2xl border border-slate-200 bg-white">
          <div className="border-b border-slate-200 p-6">
            <h1 className="text-xl font-bold text-slate-900">Change password</h1>
          </div>
          <div className="p-6">
            <ChangePasswordForm />
          </div>
        </div>
      </main>
    </div>
  );
}