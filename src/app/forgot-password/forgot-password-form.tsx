"use client";

import { useActionState } from "react";
import { forgotPassword, type ActionState } from "@/actions/auth";
import { Alert } from "@/components/ui/alert";
import { Field } from "@/components/ui/field";
import { Input } from "@/components/ui/controls";

export default function ForgotPasswordForm() {
  const [state, formAction, pending] = useActionState<ActionState, FormData>(
    forgotPassword,
    null,
  );

  const success =
    state?.message != null &&
    !state.message.includes("No account") &&
    !state.message.includes("enter");

  return (
    <form action={formAction} className="space-y-4">
      {state?.message && (
        <Alert tone={success ? "success" : "error"}>{state.message}</Alert>
      )}

      <Field label="Email" htmlFor="email" required hint="We'll send a reset link to this address.">
        <Input id="email" name="email" type="email" autoComplete="email" required />
      </Field>

      <button
        type="submit"
        disabled={pending}
        className="w-full rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-blue-500 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600 disabled:opacity-60"
      >
        {pending ? "Sending…" : "Send reset link"}
      </button>
    </form>
  );
}