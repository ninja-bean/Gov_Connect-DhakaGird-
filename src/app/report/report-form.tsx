"use client";

import { useActionState, useRef } from "react";
import { reportProblem, type ActionState } from "@/actions/problems";
import LocationPicker from "@/components/location-picker";

const fieldCls =
  "w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20";

export default function ReportForm() {
  const [state, formAction, pending] = useActionState<ActionState, FormData>(
    reportProblem,
    null,
  );
  const fileInfo = useRef<HTMLParagraphElement>(null);

  return (
    <form action={formAction} encType="multipart/form-data" className="space-y-5">
      {state?.message && (
        <p
          className={`rounded-lg px-4 py-3 text-sm ${
            state.message.includes("Report submitted")
              ? "bg-green-50 text-green-700"
              : "bg-red-50 text-red-700"
          }`}
        >
          {state.message}
        </p>
      )}
      {state?.errors?._form && (
        <p className="rounded-lg bg-red-50 px-4 py-3 text-sm text-red-700">
          {state.errors._form[0]}
        </p>
      )}

      <div>
        <label htmlFor="category" className="mb-1 block text-sm font-semibold text-slate-700">
          Problem category
        </label>
        <select id="category" name="category" required className={fieldCls}>
          <option value="">Select a category</option>
          <option value="police">Police</option>
          <option value="medical">Medical</option>
          <option value="fire">Fire</option>
          <option value="gov">Government</option>
          <option value="other">Other</option>
        </select>
        <p className="mt-1 text-xs text-slate-400">
          Choose the category that best describes your issue.
        </p>
        {state?.errors?.category && (
          <p className="mt-1 text-xs text-red-600">{state.errors.category[0]}</p>
        )}
      </div>

      <div>
        <label className="mb-1 block text-sm font-semibold text-slate-700">Location</label>
        <LocationPicker />
        {state?.errors?.latitude && (
          <p className="mt-1 text-xs text-red-600">{state.errors.latitude[0]}</p>
        )}
        <p className="mt-1 text-xs text-slate-400">
          We need your location to route this to the correct department.
        </p>
      </div>

      <div>
        <label htmlFor="description" className="mb-1 block text-sm font-semibold text-slate-700">
          Problem description
        </label>
        <textarea
          id="description"
          name="description"
          required
          minLength={10}
          rows={5}
          placeholder="Describe the issue in detail… e.g. The streetlight near House #12 has been broken for 3 days causing safety concerns at night."
          className={`${fieldCls} resize-y leading-relaxed`}
        />
        {state?.errors?.description && (
          <p className="mt-1 text-xs text-red-600">{state.errors.description[0]}</p>
        )}
      </div>

      <div>
        <label htmlFor="suggestion" className="mb-1 block text-sm font-semibold text-slate-700">
          Suggestion (optional)
        </label>
        <input
          id="suggestion"
          name="suggestion"
          className={fieldCls}
          placeholder="Any suggestions for solving this issue?"
        />
      </div>

      <div>
        <label htmlFor="media" className="mb-1 block text-sm font-semibold text-slate-700">
          Supporting media (optional)
        </label>
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
      </div>

      <button
        type="submit"
        disabled={pending}
        className="w-full rounded-xl bg-slate-900 px-6 py-4 text-base font-bold text-white transition hover:bg-slate-700 disabled:opacity-60"
      >
        {pending ? "Submitting…" : "Submit Report"}
      </button>
    </form>
  );
}