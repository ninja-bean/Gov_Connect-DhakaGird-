"use client";

import { useActionState } from "react";
import { submitUnbanAppeal, type ActionState } from "@/actions/problems";
import { Alert } from "@/components/ui/alert";
import { Field } from "@/components/ui/field";
import { Textarea } from "@/components/ui/controls";

export default function AppealForm() {
  const [state, formAction, pending] = useActionState<ActionState, FormData>(
    submitUnbanAppeal,
    null,
  );

  const success = state?.message?.includes("submitted");

  return (
    <form action={formAction} className="space-y-4">
      {state?.message && <Alert tone={success ? "success" : "error"}>{state.message}</Alert>}
      {state?.errors?._form && <Alert tone="error">{state.errors._form[0]}</Alert>}

      <Field label="Reason for your appeal" htmlFor="message" required>
        <Textarea
          id="message"
          name="message"
          required
          rows={4}
          placeholder="Explain why your ban should be reviewed…"
        />
      </Field>

      <button
        type="submit"
        disabled={pending}
        className="w-full rounded-lg bg-blue-600 px-6 py-3 text-sm font-bold text-white transition hover:bg-blue-500 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600 disabled:opacity-60"
      >
        {pending ? "Submitting…" : "Submit appeal"}
      </button>
    </form>
  );
}