"use client";

import { useActionState } from "react";
import { changePassword, type ActionState } from "@/actions/auth";
import { Alert } from "@/components/ui/alert";
import { Field } from "@/components/ui/field";
import { Input } from "@/components/ui/controls";

export default function ChangePasswordForm() {
  const [state, formAction, pending] = useActionState<ActionState, FormData>(
    changePassword,
    null,
  );

  const success = state?.message?.includes("successfully");

  return (
    <form action={formAction} className="space-y-4">
      {state?.message && <Alert tone={success ? "success" : "error"}>{state.message}</Alert>}
      {state?.errors?._form && <Alert tone="error">{state.errors._form[0]}</Alert>}

      <Field label="Current password" htmlFor="currentPassword" required error={state?.errors?.currentPassword?.[0]}>
        <Input id="currentPassword" name="currentPassword" type="password" required autoComplete="current-password" />
      </Field>
      <Field label="New password" htmlFor="newPassword" required error={state?.errors?.newPassword?.[0]}>
        <Input id="newPassword" name="newPassword" type="password" required minLength={6} autoComplete="new-password" />
      </Field>
      <Field label="Confirm new password" htmlFor="confirmPassword" required error={state?.errors?.confirmPassword?.[0]}>
        <Input id="confirmPassword" name="confirmPassword" type="password" required autoComplete="new-password" />
      </Field>

      <button
        type="submit"
        disabled={pending}
        className="w-full rounded-lg bg-ink px-6 py-3 text-sm font-bold text-white transition hover:bg-ink-soft focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-slate-900 disabled:opacity-60"
      >
        {pending ? "Updating…" : "Update password"}
      </button>
    </form>
  );
}