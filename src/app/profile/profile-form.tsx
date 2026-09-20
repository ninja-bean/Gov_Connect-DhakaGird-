"use client";

import { useActionState, useRef, useState } from "react";
import { updateProfile, type ActionState } from "@/actions/profile";
import LocationPicker from "@/components/location-picker";

const fieldCls =
  "w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20";

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
  const [state, formAction, pending] = useActionState<ActionState, FormData>(
    updateProfile,
    null,
  );
  const [preview, setPreview] = useState<string | null>(null);
  const fileRef = useRef<HTMLInputElement>(null);

  return (
    <form action={formAction} encType="multipart/form-data" className="space-y-5">
      {state?.message && (
        <p
          className={`rounded-lg px-4 py-3 text-sm ${
            state.message.includes("successfully")
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

      <div className="grid gap-5 sm:grid-cols-2">
        <div>
          <label className="mb-1 block text-sm font-semibold text-slate-600">
            Full name <span className="font-normal text-slate-400">(locked)</span>
          </label>
          <input value={name} disabled className={`${fieldCls} opacity-70`} />
        </div>
        <div>
          <label className="mb-1 block text-sm font-semibold text-slate-600">
            Email <span className="font-normal text-slate-400">(locked)</span>
          </label>
          <input value={email} disabled className={`${fieldCls} opacity-70`} />
        </div>
        <div>
          <label className="mb-1 block text-sm font-semibold text-slate-600">
            NID <span className="font-normal text-slate-400">(locked)</span>
          </label>
          <input value={nid ?? ""} disabled className={`${fieldCls} opacity-70`} />
        </div>
        <div>
          <label className="mb-1 block text-sm font-semibold text-slate-600">
            Date of birth <span className="font-normal text-slate-400">(locked)</span>
          </label>
          <input
            value={dob ? dob.toISOString().slice(0, 10) : ""}
            disabled
            className={`${fieldCls} opacity-70`}
          />
        </div>
        <div>
          <label htmlFor="phone" className="mb-1 block text-sm font-semibold text-slate-700">
            Phone
          </label>
          <input id="phone" name="phone" defaultValue={phone ?? ""} className={fieldCls} />
        </div>
        <div>
          <label
            htmlFor="profilePic"
            className="mb-1 flex items-center gap-2 text-sm font-semibold text-slate-700"
          >
            Profile picture
            {preview && (
              <img
                src={preview}
                alt="Preview"
                className="h-10 w-10 rounded-full border border-slate-200 object-cover"
              />
            )}
            {(preview === null && profilePic) && (
              <img
                src={`/uploads/profile_pics/${profilePic}`}
                alt="Current"
                className="h-10 w-10 rounded-full border border-slate-200 object-cover"
              />
            )}
          </label>
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
      </div>

      <div>
        <label className="mb-1 block text-sm font-semibold text-slate-700">Location</label>
        <LocationPicker initialLat={latitude} initialLng={longitude} />
        {!location && (
          <p className="mt-1 text-xs text-slate-400">Set your area to help route your reports.</p>
        )}
      </div>

      <button
        type="submit"
        disabled={pending}
        className="rounded-lg bg-slate-900 px-6 py-3 text-sm font-bold text-white transition hover:bg-slate-700 disabled:opacity-60"
      >
        {pending ? "Saving…" : "Save changes"}
      </button>
    </form>
  );
}