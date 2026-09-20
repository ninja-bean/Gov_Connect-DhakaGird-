"use client";

import { useActionState, useState } from "react";
import { register, type ActionState } from "@/actions/auth";

const fieldCls =
  "w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20";

function Field(props: {
  label: string;
  name: string;
  type?: string;
  required?: boolean;
  hint?: string;
}) {
  return (
    <div>
      <label htmlFor={props.name} className="mb-1 block text-sm font-medium text-slate-700">
        {props.label}
        {props.required && <span className="text-red-500"> *</span>}
      </label>
      <input
        id={props.name}
        name={props.name}
        type={props.type ?? "text"}
        required={props.required}
        className={fieldCls}
      />
      {props.hint && <p className="mt-1 text-xs text-slate-400">{props.hint}</p>}
    </div>
  );
}

export default function RegisterForm() {
  const [state, formAction, pending] = useActionState<ActionState, FormData>(
    register,
    null,
  );
  const [role, setRole] = useState<"user" | "response">("user");

  return (
    <form action={formAction} className="space-y-4">
      <div className="grid grid-cols-2 gap-1 rounded-lg bg-slate-100 p-1">
        {(["user", "response"] as const).map((r) => (
          <button
            key={r}
            type="button"
            onClick={() => setRole(r)}
            className={`rounded-md px-3 py-2 text-sm font-semibold transition ${
              role === r
                ? "bg-white text-slate-900 shadow-sm"
                : "text-slate-500 hover:text-slate-700"
            }`}
          >
            {r === "user" ? "Citizen" : "Response Team"}
          </button>
        ))}
      </div>

      {state?.message && (
        <p className="rounded-lg bg-blue-50 px-4 py-3 text-sm text-blue-700">
          {state.message}
        </p>
      )}
      {state?.errors?._form && (
        <p className="rounded-lg bg-red-50 px-4 py-3 text-sm text-red-700">
          {state.errors._form[0]}
        </p>
      )}

      <input type="hidden" name="role" value={role} />

      <div className="grid gap-4 sm:grid-cols-2">
        <Field label="Full name" name="name" required />
        <Field label="Email" name="email" type="email" required />
      </div>

      <div className="grid gap-4 sm:grid-cols-2">
        <Field label="Password" name="password" type="password" required />
        <Field label="Confirm password" name="confirmPassword" type="password" required />
      </div>

      {role === "user" ? (
        <section className="space-y-4 rounded-xl border border-slate-200 p-4">
          <p className="text-sm font-semibold text-slate-700">Citizen details</p>
          <div className="grid gap-4 sm:grid-cols-2">
            <Field label="Phone" name="phone" />
            <Field label="NID number" name="nid" hint="Prevents duplicate reporting" />
            <Field label="Date of birth" name="dob" type="date" />
            <Field label="Your area / location" name="location" />
          </div>
        </section>
      ) : (
        <section className="space-y-4 rounded-xl border border-slate-200 p-4">
          <p className="text-sm font-semibold text-slate-700">
            Response team details <span className="font-normal text-slate-400">(requires admin approval)</span>
          </p>
          <div className="grid gap-4 sm:grid-cols-2">
            <Field label="Category" name="category" hint="police, fire, medical, gov" />
            <Field label="Location / thana" name="location" />
            <Field label="Phone" name="phone" required />
            <Field label="Employee number" name="employeeNumber" />
          </div>
          <p className="text-sm font-semibold text-slate-700">In-charge officer</p>
          <div className="grid gap-4 sm:grid-cols-2">
            <Field label="In-charge name" name="inchargeName" />
            <Field label="In-charge ID" name="inchargeId" />
            <Field label="In-charge email" name="inchargeEmail" type="email" />
            <Field label="In-charge phone" name="inchargePhone" />
          </div>
          <Field label="Identification details" name="identification" hint="Service certificate no. / license info" />
        </section>
      )}

      <button
        type="submit"
        disabled={pending}
        className="w-full rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-blue-500 disabled:opacity-60"
      >
        {pending ? "Creating account…" : "Create account"}
      </button>
    </form>
  );
}