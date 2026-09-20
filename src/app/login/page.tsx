import type { Metadata } from "next";
import Link from "next/link";
import LoginForm from "./login-form";

export const metadata: Metadata = { title: "Login | GovConnect" };

export default function LoginPage() {
  return (
    <main className="flex min-h-screen items-center justify-center bg-slate-50 px-4 py-10">
      <div className="w-full max-w-md rounded-2xl border border-slate-200 bg-white p-8 shadow-sm">
        <div className="mb-6 text-center">
          <h1 className="text-2xl font-bold tracking-tight text-slate-900">Welcome back</h1>
          <p className="mt-1 text-sm text-slate-500">Sign in to GovConnect</p>
        </div>
        <LoginForm />
        <p className="mt-6 text-center text-sm text-slate-500">
          Don&apos;t have an account?{" "}
          <Link href="/register" className="font-semibold text-blue-600 hover:underline">
            Register
          </Link>
        </p>
        <p className="mt-2 text-center text-sm text-slate-500">
          <Link href="/forgot-password" className="hover:underline">
            Forgot password?
          </Link>
        </p>
      </div>
    </main>
  );
}