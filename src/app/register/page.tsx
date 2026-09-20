import type { Metadata } from "next";
import Link from "next/link";
import RegisterForm from "./register-form";

export const metadata: Metadata = { title: "Register | GovConnect" };

export default function RegisterPage() {
  return (
    <main className="flex min-h-screen items-center justify-center bg-slate-50 px-4 py-10">
      <div className="w-full max-w-xl rounded-2xl border border-slate-200 bg-white p-8 shadow-sm">
        <div className="mb-6 text-center">
          <h1 className="text-2xl font-bold tracking-tight text-slate-900">Create an account</h1>
          <p className="mt-1 text-sm text-slate-500">
            Join GovConnect as a citizen or response team
          </p>
        </div>
        <RegisterForm />
        <p className="mt-6 text-center text-sm text-slate-500">
          Already have an account?{" "}
          <Link href="/login" className="font-semibold text-blue-600 hover:underline">
            Sign in
          </Link>
        </p>
      </div>
    </main>
  );
}