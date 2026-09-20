"use client";

import { useActionState } from "react";
import { submitFeedback, type ActionState } from "@/actions/problems";

const fieldCls =
  "w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20";

export default function FeedbackForm({ problemId }: { problemId: number }) {
  const [state, formAction, pending] = useActionState<ActionState, FormData>(
    submitFeedback,
    null,
  );

  return (
    <form action={formAction} className="space-y-4">
      {state?.message && (
        <p
          className={`rounded-lg px-4 py-3 text-sm ${
            state.message.includes("recorded") || state.message.includes("Thank")
              ? "bg-green-50 text-green-700"
              : "bg-red-50 text-red-700"
          }`}
        >
          {state.message}
        </p>
      )}

      <input type="hidden" name="problemId" value={problemId} />

      <div>
        <label htmlFor="rating" className="mb-1 block text-sm font-semibold text-slate-700">
          Rating
        </label>
        <select id="rating" name="rating" required className={fieldCls}>
          <option value="">Select rating</option>
          {[1, 2, 3, 4, 5].map((n) => (
            <option key={n} value={n}>
              {n} {n === 1 ? "star" : "stars"}
            </option>
          ))}
        </select>
      </div>

      <div>
        <label htmlFor="comment" className="mb-1 block text-sm font-semibold text-slate-700">
          Comment (optional)
        </label>
        <textarea
          id="comment"
          name="comment"
          rows={3}
          placeholder="How did the response team do?"
          className={`${fieldCls} resize-y`}
        />
      </div>

      <button
        type="submit"
        disabled={pending}
        className="rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-blue-500 disabled:opacity-60"
      >
        {pending ? "Submitting…" : "Submit feedback"}
      </button>
    </form>
  );
}