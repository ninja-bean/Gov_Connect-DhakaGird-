import Link from "next/link";

export default function Home() {
  return (
    <main className="min-h-screen bg-slate-50 text-slate-900">
      <section className="flex min-h-screen items-center justify-center bg-slate-900 px-6">
        <div className="max-w-xl text-center text-white">
          <h1 className="text-4xl font-bold tracking-tight md:text-5xl">
            Welcome to GovConnect
          </h1>
          <p className="mt-4 text-lg text-slate-300">
            Empowering citizens to connect with their government, report issues,
            and request help — all in one place.
          </p>
          <div className="mt-8 flex justify-center gap-4">
            <Link
              href="/login"
              className="rounded-lg bg-blue-600 px-6 py-3 font-semibold text-white transition hover:bg-blue-500"
            >
              Login
            </Link>
            <Link
              href="/register"
              className="rounded-lg border border-white/40 px-6 py-3 font-semibold text-white transition hover:bg-white/10"
            >
              Register
            </Link>
          </div>
        </div>
      </section>
    </main>
  );
}