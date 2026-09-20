"use client";

import { useActionState } from "react";
import { changePassword, type ActionState } from "@/actions/auth";

const fieldCls =
  "w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20";

export default function ChangePasswordForm() {
  const [state, formAction, pending] = useActionState<ActionState, FormData>(
    changePassword,
    null,
  );

  return (
    <form action={formAction} className="space-y-4">
      {state?.message && (
        <p
          className={`rounded-lg px-4 py-3 text-sm ${
            state.message.includes("successfully")
              ? "bg-green-50 text-green-700"
              : "bg-red-50 text-red-700"
          }`}
        >
          {state.message}
        </p>
      )}
      <div>
        <label htmlFor="currentPassword" className="mb-1 block text-sm font-semibold text-slate-700">
          Current password
        </label>
        <input id="currentPassword" name="currentPassword" type="password" required className={fieldCls} autoComplete="current-password" />
      </div>
      <div>
        <label htmlFor="newPassword" className="mb-1 block text-sm font-semibold text-slate-700">
          New password
        </label>
        <input id="newPassword" name="newPassword" type="password" required minLength={6} className={fieldCls} autoComplete="new-password" />
      </div>
      <div>
        <label htmlFor="confirmPassword" className="mb-1 block text-sm font-semibold text-slate-700">
          Confirm new password
        </label>
        <input id="confirmPassword" name="confirmPassword" type="password" required className={fieldCls} autoComplete="new-password" />
      </div>
      <button
        type="submit"
        disabled={pending}
        className="w-full rounded-lg bg-slate-900 px-6 py-3 text-sm font-bold text-white transition hover:bg-slate-700 disabled:opacity-60"
      >
        {pending ? "Updating…" : "Update password"}
      </button>
    </form>
  );
}