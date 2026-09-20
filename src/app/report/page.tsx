import type { Metadata } from "next";
import Link from "next/link";
import { db } from "@/lib/db";
import { requireRole } from "@/lib/auth/guards";
import { isCurrentlyBanned } from "@/lib/problems";
import CitizenNav from "@/components/citizen-nav";
import ReportForm from "./report-form";

export const metadata: Metadata = { title: "Submit Report | GovConnect" };

export default async function ReportPage() {
  const session = await requireRole("user");
  const user = await db.users.findUniqueOrThrow({
    where: { user_id: session.userId },
    select: { is_banned: true, ban_until: true },
  });

  return (
    <div className="min-h-screen bg-slate-50">
      <CitizenNav />
      <main className="mx-auto max-w-3xl px-6 py-8">
        <div className="rounded-2xl border border-slate-200 bg-white">
          <div className="border-b border-slate-200 p-6">
            <h1 className="text-xl font-bold text-slate-900">
              {isCurrentlyBanned(user) ? "Account Restricted" : "Submit New Problem"}
            </h1>
          </div>
          <div className="p-6">
            {isCurrentlyBanned(user) ? (
              <div className="rounded-xl border-2 border-red-300 bg-red-50 p-8 text-center">
                <p className="text-lg font-bold text-red-800">Your account has been restricted.</p>
                <p className="mt-2 text-sm text-red-700">
                  You cannot submit new reports while your account is banned.
                </p>
                <Link
                  href="/appeal"
                  className="mt-5 inline-block rounded-lg bg-red-600 px-6 py-2.5 font-semibold text-white"
                >
                  Lodge an appeal
                </Link>
              </div>
            ) : (
              <ReportForm />
            )}
          </div>
        </div>
      </main>
    </div>
  );
}