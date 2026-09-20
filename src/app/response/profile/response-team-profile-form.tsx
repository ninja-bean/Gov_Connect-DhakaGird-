"use client";

import { useActionState, useState } from "react";
import { updateTeamProfile, type TeamActionState } from "@/actions/response";
import LocationPicker from "@/components/location-picker";
import { Alert } from "@/components/ui/alert";
import { Field } from "@/components/ui/field";
import { Input, Select } from "@/components/ui/controls";

const categories = ["police", "medical", "fire", "gov"] as const;

export default function ResponseTeamProfileForm({
  name,
  category,
  inchargeName,
  inchargeEmail,
  inchargePhone,
  employeeNumber,
  location,
  latitude,
  longitude,
  profilePic,
}: {
  name: string;
  category: string;
  inchargeName: string;
  inchargeEmail: string;
  inchargePhone: string;
  employeeNumber: string;
  location: string;
  latitude: number | null;
  longitude: number | null;
  profilePic: string | null;
}) {
  const [state, formAction, pending] = useActionState<TeamActionState, FormData>(
    updateTeamProfile,
    null,
  );
  const [preview, setPreview] = useState<string | null>(null);

  const success = state?.message?.includes("successfully");

  return (
    <form action={formAction} encType="multipart/form-data" className="space-y-5">
      {state?.message && <Alert tone={success ? "success" : "error"}>{state.message}</Alert>}
      {state?.errors?._form && <Alert tone="error">{state.errors._form[0]}</Alert>}

      <div className="grid gap-5 sm:grid-cols-2">
        <Field label="Team name" htmlFor="name" required error={state?.errors?.name?.[0]}>
          <Input id="name" name="name" defaultValue={name} required />
        </Field>
        <Field label="Station category" htmlFor="category" required>
          <Select id="category" name="category" required defaultValue={category}>
            {categories.map((c) => (
              <option key={c} value={c}>
                {c.charAt(0).toUpperCase() + c.slice(1)}
              </option>
            ))}
          </Select>
        </Field>
        <Field label="In-charge name" htmlFor="inchargeName" required error={state?.errors?.inchargeName?.[0]}>
          <Input id="inchargeName" name="inchargeName" defaultValue={inchargeName} required />
        </Field>
        <Field label="In-charge email" htmlFor="inchargeEmail" required error={state?.errors?.inchargeEmail?.[0]}>
          <Input id="inchargeEmail" name="inchargeEmail" type="email" defaultValue={inchargeEmail} required />
        </Field>
        <Field label="In-charge phone" htmlFor="inchargePhone" required error={state?.errors?.inchargePhone?.[0]}>
          <Input id="inchargePhone" name="inchargePhone" defaultValue={inchargePhone} required />
        </Field>
        <Field label="Team size (available members)" htmlFor="employeeNumber" required error={state?.errors?.employeeNumber?.[0]}>
          <Input
            id="employeeNumber"
            name="employeeNumber"
            type="number"
            min={1}
            max={500}
            defaultValue={employeeNumber || "1"}
            required
          />
        </Field>
      </div>

      <Field
        label="Profile picture"
        htmlFor="profilePic"
        hint="Allowed: JPG, PNG, WebP · Max 2MB"
      >
        <div className="flex items-center gap-3">
          {profilePic && (
            <img
              src={`/uploads/profile_pics/${profilePic}`}
              alt="Current"
              className="size-10 rounded-full border border-slate-200 object-cover"
            />
          )}
          {preview && (
            <img
              src={preview}
              alt="Preview"
              className="size-10 rounded-full border border-slate-200 object-cover"
            />
          )}
          <input
            id="profilePic"
            name="profilePic"
            type="file"
            accept="image/jpeg,image/png,image/webp"
            className="block w-full text-sm text-slate-500 file:mr-3 file:rounded-lg file:border-0 file:bg-slate-900 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-white"
            onChange={(e) => {
              const f = e.target.files?.[0];
              if (!f) return;
              setPreview(URL.createObjectURL(f));
            }}
          />
        </div>
      </Field>

      <Field
        label="Station location"
        hint={location ? undefined : "Pick your station on the map to help route problems."}
        error={state?.errors?.latitude?.[0] ?? state?.errors?.longitude?.[0]}
      >
        <LocationPicker initialLat={latitude} initialLng={longitude} />
      </Field>

      <button
        type="submit"
        disabled={pending}
        className="rounded-lg bg-slate-900 px-6 py-3 text-sm font-bold text-white transition hover:bg-slate-700 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-slate-900 disabled:opacity-60"
      >
        {pending ? "Saving…" : "Save profile"}
      </button>
    </form>
  );
}