"use client";

import { useActionState, useState } from "react";
import { register, type ActionState } from "@/actions/auth";
import { Alert } from "@/components/ui/alert";
import { Field } from "@/components/ui/field";
import { Input } from "@/components/ui/controls";

export default function RegisterForm() {
  const [state, formAction, pending] = useActionState<ActionState, FormData>(register, null);
  const [role, setRole] = useState<"user" | "response">("user");

  return (
    <form action={formAction} className="space-y-4">
      <div className="grid grid-cols-2 gap-1 rounded-xl bg-slate-100 p-1">
        {(["user", "response"] as const).map((r) => (
          <button
            key={r}
            type="button"
            onClick={() => setRole(r)}
            aria-pressed={role === r}
            className={`rounded-lg px-3 py-2 text-sm font-semibold transition focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600 ${
              role === r
                ? "bg-white text-slate-900 shadow-sm"
                : "text-slate-500 hover:text-slate-700"
            }`}
          >
            {r === "user" ? "Citizen" : "Response Team"}
          </button>
        ))}
      </div>

      {state?.message && <Alert tone="success">{state.message}</Alert>}
      {state?.errors?._form && <Alert tone="error">{state.errors._form[0]}</Alert>}
      <input type="hidden" name="role" value={role} />

      <div className="grid gap-4 sm:grid-cols-2">
        <Field label="Full name" htmlFor="name" required error={state?.errors?.name?.[0]}>
          <Input id="name" name="name" required />
        </Field>
        <Field label="Email" htmlFor="email" required error={state?.errors?.email?.[0]}>
          <Input id="email" name="email" type="email" required />
        </Field>
      </div>

      <div className="grid gap-4 sm:grid-cols-2">
        <Field label="Password" htmlFor="password" required error={state?.errors?.password?.[0]}>
          <Input id="password" name="password" type="password" required />
        </Field>
        <Field label="Confirm password" htmlFor="confirmPassword" required error={state?.errors?.confirmPassword?.[0]}>
          <Input id="confirmPassword" name="confirmPassword" type="password" required />
        </Field>
      </div>

      {role === "user" ? (
        <section className="space-y-4 rounded-xl border border-slate-200 p-4">
          <p className="text-sm font-semibold text-slate-700">Citizen details</p>
          <div className="grid gap-4 sm:grid-cols-2">
            <Field label="Phone" htmlFor="phone">
              <Input id="phone" name="phone" />
            </Field>
            <Field label="NID number" htmlFor="nid" hint="Prevents duplicate reporting">
              <Input id="nid" name="nid" />
            </Field>
            <Field label="Date of birth" htmlFor="dob">
              <Input id="dob" name="dob" type="date" />
            </Field>
            <Field label="Your area / location" htmlFor="location">
              <Input id="location" name="location" />
            </Field>
          </div>
        </section>
      ) : (
        <section className="space-y-4 rounded-xl border border-slate-200 p-4">
          <p className="text-sm font-semibold text-slate-700">
            Response team details{" "}
            <span className="font-normal text-slate-400">(requires admin approval)</span>
          </p>
          <div className="grid gap-4 sm:grid-cols-2">
            <Field label="Category" htmlFor="category" hint="police, fire, medical, gov">
              <Input id="category" name="category" />
            </Field>
            <Field label="Location / thana" htmlFor="location">
              <Input id="location" name="location" />
            </Field>
            <Field label="Phone" htmlFor="phone" required>
              <Input id="phone" name="phone" required />
            </Field>
            <Field label="Employee number" htmlFor="employeeNumber">
              <Input id="employeeNumber" name="employeeNumber" />
            </Field>
          </div>
          <p className="text-sm font-semibold text-slate-700">In-charge officer</p>
          <div className="grid gap-4 sm:grid-cols-2">
            <Field label="In-charge name" htmlFor="inchargeName">
              <Input id="inchargeName" name="inchargeName" />
            </Field>
            <Field label="In-charge ID" htmlFor="inchargeId">
              <Input id="inchargeId" name="inchargeId" />
            </Field>
            <Field label="In-charge email" htmlFor="inchargeEmail">
              <Input id="inchargeEmail" name="inchargeEmail" type="email" />
            </Field>
            <Field label="In-charge phone" htmlFor="inchargePhone">
              <Input id="inchargePhone" name="inchargePhone" />
            </Field>
          </div>
          <Field
            label="Identification details"
            htmlFor="identification"
            hint="Service certificate no. / license info"
          >
            <Input id="identification" name="identification" />
          </Field>
        </section>
      )}

      <button
        type="submit"
        disabled={pending}
        className="w-full rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-blue-500 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600 disabled:opacity-60"
      >
        {pending ? "Creating account…" : "Create account"}
      </button>
    </form>
  );
}