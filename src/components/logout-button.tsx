"use client";

import { useTransition } from "react";
import { logout } from "@/actions/auth";

export default function LogoutButton({ label = "Log out" }: { label?: string }) {
  const [pending, startTransition] = useTransition();

  return (
    <button
      type="button"
      disabled={pending}
      onClick={() => startTransition(() => logout())}
      className="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-100 disabled:opacity-60"
    >
      {pending ? "Logging out…" : label}
    </button>
  );
}