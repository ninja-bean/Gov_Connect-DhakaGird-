"use client";

import { useActionState, useState } from "react";
import { login, type ActionState } from "@/actions/auth";
import { Alert } from "@/components/ui/alert";
import { Field } from "@/components/ui/field";
import { Input } from "@/components/ui/controls";

const TABS = [
  { value: "user", label: "Citizen" },
  { value: "response", label: "Response Team" },
] as const;

export default function LoginForm() {
  const [state, formAction, pending] = useActionState<ActionState, FormData>(login, null);
  const [tab, setTab] = useState<(typeof TABS)[number]["value"]>("user");

  return (
    <form action={formAction} className="space-y-4">
      <div className="grid grid-cols-2 gap-1 rounded-xl bg-slate-100 p-1">
        {TABS.map((t) => (
          <button
            key={t.value}
            type="button"
            onClick={() => setTab(t.value)}
            aria-pressed={tab === t.value}
            className={`rounded-lg px-3 py-2 text-sm font-semibold transition focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600 ${
              tab === t.value
                ? "bg-white text-slate-900 shadow-sm"
                : "text-slate-500 hover:text-slate-700"
            }`}
          >
            {t.label}
          </button>
        ))}
      </div>
      <input type="hidden" name="role" value={tab} />

      {state?.message && <Alert tone="error">{state.message}</Alert>}

      <Field label="Email" htmlFor="email" required error={state?.errors?.email?.[0]}>
        <Input id="email" name="email" type="email" autoComplete="email" required />
      </Field>

      <Field label="Password" htmlFor="password" required error={state?.errors?.password?.[0]}>
        <Input id="password" name="password" type="password" autoComplete="current-password" required />
      </Field>

      <button
        type="submit"
        disabled={pending}
        className="w-full rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-blue-500 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600 disabled:opacity-60"
      >
        {pending ? "Signing in…" : "Sign in"}
      </button>
    </form>
  );
}