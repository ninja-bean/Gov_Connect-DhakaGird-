"use client";

import { useActionState, useRef } from "react";
import { reportProblem, type ActionState } from "@/actions/problems";
import LocationPicker from "@/components/location-picker";
import { Alert } from "@/components/ui/alert";
import { Field } from "@/components/ui/field";
import { Input, Select, Textarea } from "@/components/ui/controls";

export default function ReportForm() {
  const [state, formAction, pending] = useActionState<ActionState, FormData>(
    reportProblem,
    null,
  );
  const fileInfo = useRef<HTMLParagraphElement>(null);

  const success = state?.message?.includes("Report submitted");

  return (
    <form action={formAction} encType="multipart/form-data" className="space-y-5">
      {state?.message && <Alert tone={success ? "success" : "error"}>{state.message}</Alert>}
      {state?.errors?._form && <Alert tone="error">{state.errors._form[0]}</Alert>}

      <Field label="Problem category" htmlFor="category" required hint="Choose the category that best describes your issue.">
        <Select id="category" name="category" required defaultValue="">
          <option value="" disabled>
            Select a category
          </option>
          <option value="police">Police</option>
          <option value="medical">Medical</option>
          <option value="fire">Fire</option>
          <option value="gov">Government</option>
          <option value="other">Other</option>
        </Select>
      </Field>

      <Field label="Location" hint="We need your location to route this to the correct department." error={state?.errors?.latitude?.[0]}>
        <LocationPicker />
      </Field>

      <Field
        label="Problem description"
        htmlFor="description"
        required
        hint="Be specific — include an address or landmark if possible."
        error={state?.errors?.description?.[0]}
      >
        <Textarea
          id="description"
          name="description"
          required
          minLength={10}
          rows={5}
          placeholder="Describe the issue in detail… e.g. The streetlight near House #12 has been broken for 3 days causing safety concerns at night."
        />
      </Field>

      <Field label="Suggestion (optional)" htmlFor="suggestion">
        <Input id="suggestion" name="suggestion" placeholder="Any suggestions for solving this issue?" />
      </Field>

      <Field
        label="Supporting media (optional)"
        htmlFor="media"
        hint="Allowed: JPG, PNG, WebP · Max 10MB per file"
      >
        <label
          htmlFor="media"
          className="block w-full cursor-pointer rounded-xl border-2 border-dashed border-slate-300 bg-slate-50 px-6 py-8 text-center transition hover:border-blue-500 hover:bg-blue-50/30"
        >
          <p className="text-sm font-semibold text-slate-700">Upload images</p>
          <p className="mt-1 text-xs text-slate-400">JPG, PNG, WebP · Max 10MB per file</p>
        </label>
        <input
          id="media"
          name="media"
          type="file"
          multiple
          accept="image/jpeg,image/png,image/webp"
          className="hidden"
          onChange={(e) => {
            const files = e.target.files;
            if (fileInfo.current) {
              fileInfo.current.textContent =
                files && files.length > 0 ? `${files.length} file(s) selected` : "No files selected";
            }
          }}
        />
        <p ref={fileInfo} className="mt-2 text-sm text-slate-500">
          No files selected
        </p>
      </Field>

      <button
        type="submit"
        disabled={pending}
        className="w-full rounded-xl bg-slate-900 px-6 py-4 text-base font-bold text-white transition hover:bg-slate-700 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-slate-900 disabled:opacity-60"
      >
        {pending ? "Submitting…" : "Submit Report"}
      </button>
    </form>
  );
}