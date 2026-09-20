"use client";

import { useActionState } from "react";
import { forgotPassword, type ActionState } from "@/actions/auth";

export default function ForgotPasswordForm() {
  const [state, formAction, pending] = useActionState<ActionState, FormData>(
    forgotPassword,
    null,
  );

  return (
    <form action={formAction} className="space-y-4">
      {state?.message && (
        <p
          className={`rounded-lg px-4 py-3 text-sm ${
            state.message.includes("No account") || state.message.includes("enter")
              ? "bg-red-50 text-red-700"
              : "bg-green-50 text-green-700"
          }`}
        >
          {state.message}
        </p>
      )}

      <div>
        <label htmlFor="email" className="mb-1 block text-sm font-medium text-slate-700">
          Email
        </label>
        <input
          id="email"
          name="email"
          type="email"
          autoComplete="email"
          required
          className="w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20"
        />
      </div>

      <button
        type="submit"
        disabled={pending}
        className="w-full rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-blue-500 disabled:opacity-60"
      >
        {pending ? "Sending…" : "Send reset link"}
      </button>
    </form>
  );
}