"use client";

import { useActionState } from "react";
import { submitFeedback, type ActionState } from "@/actions/problems";
import { Alert } from "@/components/ui/alert";
import { Field } from "@/components/ui/field";
import { Select, Textarea } from "@/components/ui/controls";

export default function FeedbackForm({ problemId }: { problemId: number }) {
  const [state, formAction, pending] = useActionState<ActionState, FormData>(
    submitFeedback,
    null,
  );

  const success = state?.message?.includes("recorded") || state?.message?.includes("Thank");

  return (
    <form action={formAction} className="space-y-4">
      {state?.message && <Alert tone={success ? "success" : "error"}>{state.message}</Alert>}
      <input type="hidden" name="problemId" value={problemId} />

      <Field label="Rating" htmlFor="rating" required>
        <Select id="rating" name="rating" required defaultValue="">
          <option value="" disabled>
            Select rating
          </option>
          {[1, 2, 3, 4, 5].map((n) => (
            <option key={n} value={n}>
              {n} {n === 1 ? "star" : "stars"}
            </option>
          ))}
        </Select>
      </Field>

      <Field label="Comment (optional)" htmlFor="comment">
        <Textarea
          id="comment"
          name="comment"
          rows={3}
          placeholder="How did the response team do?"
        />
      </Field>

      <button
        type="submit"
        disabled={pending}
        className="rounded-lg bg-red-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-red-500 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-red-600 disabled:opacity-60"
      >
        {pending ? "Submitting…" : "Submit feedback"}
      </button>
    </form>
  );
}