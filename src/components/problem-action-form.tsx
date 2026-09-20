"use client";

import { useActionState } from "react";
import {
  startWorking,
  resolveProblem,
  type TeamActionState,
} from "@/actions/response";
import { Alert } from "@/components/ui/alert";
import { Field } from "@/components/ui/field";
import { Input, Textarea } from "@/components/ui/controls";

export default function ProblemActionForm({
  problemId,
  mode,
  available,
  membersAssignable,
}: {
  problemId: number;
  mode: "start" | "resolve";
  available: number;
  membersAssignable: number;
}) {
  const [state, formAction, pending] = useActionState<TeamActionState, FormData>(
    mode === "start" ? startWorking : resolveProblem,
    null,
  );

  const success = state?.message?.includes("successfully") || state?.message?.includes("marked");

  return (
    <form action={formAction} className="space-y-3">
      {state?.message && <Alert tone={success ? "success" : "error"}>{state.message}</Alert>}
      {state?.errors?._form && <Alert tone="error">{state.errors._form[0]}</Alert>}

      <input type="hidden" name="problemId" value={problemId} />

      {mode === "start" ? (
        <>
          <Field label="Team members to assign" htmlFor={`members-${problemId}`} hint={`${available} available`}>
            <Input
              id={`members-${problemId}`}
              name="members"
              type="number"
              min={1}
              max={membersAssignable}
              defaultValue={membersAssignable > 0 ? Math.min(2, membersAssignable) : 1}
              required
            />
          </Field>
          <button
            type="submit"
            disabled={pending || available <= 0}
            className="w-full rounded-lg bg-red-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-red-500 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-red-600 disabled:opacity-60"
          >
            {pending ? "Updating…" : "Start working"}
          </button>
        </>
      ) : (
        <>
          <Field label="Work report" htmlFor={`report-${problemId}`} required>
            <Textarea
              id={`report-${problemId}`}
              name="report"
              rows={3}
              required
              minLength={10}
              placeholder="What did your team do to resolve this?"
            />
          </Field>
          <button
            type="submit"
            disabled={pending}
            className="w-full rounded-lg bg-ink px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-ink-soft focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-ink disabled:opacity-60"
          >
            {pending ? "Submitting…" : "Resolve problem"}
          </button>
        </>
      )}
    </form>
  );
}