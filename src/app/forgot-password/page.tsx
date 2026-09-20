import type { Metadata } from "next";
import Link from "next/link";
import ForgotPasswordForm from "./forgot-password-form";

export const metadata: Metadata = { title: "Forgot Password | GovConnect" };

export default function ForgotPasswordPage() {
  return (
    <main className="flex min-h-screen items-center justify-center bg-slate-50 px-4">
      <div className="w-full max-w-md rounded-2xl border border-slate-200 bg-white p-8 shadow-sm">
        <h1 className="mb-1 text-2xl font-bold text-slate-900">Forgot password</h1>
        <p className="mb-6 text-sm text-slate-500">
          Enter your account email and we&apos;ll send you a reset link.
        </p>
        <ForgotPasswordForm />
        <p className="mt-6 text-center text-sm text-slate-500">
          Remembered your password?{" "}
          <Link href="/login" className="font-semibold text-blue-600 hover:underline">
            Sign in
          </Link>
        </p>
      </div>
    </main>
  );
}