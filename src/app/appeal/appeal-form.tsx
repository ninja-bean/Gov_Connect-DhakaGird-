"use client";

import { useActionState } from "react";
import { submitUnbanAppeal, type ActionState } from "@/actions/problems";

const fieldCls =
  "w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20";

export default function AppealForm() {
  const [state, formAction, pending] = useActionState<ActionState, FormData>(
    submitUnbanAppeal,
    null,
  );

  return (
    <form action={formAction} className="space-y-4">
      {state?.message && (
        <p
          className={`rounded-lg px-4 py-3 text-sm ${
            state.message.includes("submitted")
              ? "bg-green-50 text-green-700"
              : "bg-red-50 text-red-700"
          }`}
        >
          {state.message}
        </p>
      )}
      <div>
        <label htmlFor="message" className="mb-1 block text-sm font-semibold text-slate-700">
          Reason for your appeal
        </label>
        <textarea
          id="message"
          name="message"
          required
          rows={4}
          placeholder="Explain why your ban should be reviewed…"
          className={`${fieldCls} resize-y`}
        />
      </div>
      <button
        type="submit"
        disabled={pending}
        className="w-full rounded-lg bg-blue-600 px-6 py-3 text-sm font-bold text-white transition hover:bg-blue-500 disabled:opacity-60"
      >
        {pending ? "Submitting…" : "Submit appeal"}
      </button>
    </form>
  );
}