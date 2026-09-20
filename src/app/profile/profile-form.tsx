"use client";

import { useActionState, useRef, useState } from "react";
import { updateProfile, type ActionState } from "@/actions/profile";
import LocationPicker from "@/components/location-picker";
import { Alert } from "@/components/ui/alert";
import { Field } from "@/components/ui/field";
import { Input } from "@/components/ui/controls";

export default function ProfileForm({
  name,
  email,
  nid,
  dob,
  phone,
  location,
  latitude,
  longitude,
  profilePic,
}: {
  name: string;
  email: string;
  nid: string | null;
  dob: Date | null;
  phone: string | null;
  location: string | null;
  latitude: number | null;
  longitude: number | null;
  profilePic: string | null;
}) {
  const [state, formAction, pending] = useActionState<ActionState, FormData>(updateProfile, null);
  const [preview, setPreview] = useState<string | null>(null);
  const fileRef = useRef<HTMLInputElement>(null);

  const success = state?.message?.includes("successfully");

  return (
    <form action={formAction} encType="multipart/form-data" className="space-y-5">
      {state?.message && <Alert tone={success ? "success" : "error"}>{state.message}</Alert>}
      {state?.errors?._form && <Alert tone="error">{state.errors._form[0]}</Alert>}

      <div className="grid gap-5 sm:grid-cols-2">
        <Field label="Full name">
          <Input value={name} disabled />
        </Field>
        <Field label="Email">
          <Input value={email} disabled />
        </Field>
        <Field label="NID">
          <Input value={nid ?? ""} disabled />
        </Field>
        <Field label="Date of birth">
          <Input value={dob ? dob.toISOString().slice(0, 10) : ""} disabled />
        </Field>
        <Field label="Phone" htmlFor="phone">
          <Input id="phone" name="phone" defaultValue={phone ?? ""} />
        </Field>
        <Field label="Profile picture" htmlFor="profilePic" hint="JPG, PNG, WebP · Max 2MB">
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
              ref={fileRef}
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
      </div>

      <Field label="Location" hint={location ? undefined : "Set your area to help route your reports."}>
        <LocationPicker initialLat={latitude} initialLng={longitude} />
      </Field>

      <button
        type="submit"
        disabled={pending}
        className="rounded-lg bg-slate-900 px-6 py-3 text-sm font-bold text-white transition hover:bg-slate-700 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-slate-900 disabled:opacity-60"
      >
        {pending ? "Saving…" : "Save changes"}
      </button>
    </form>
  );
}