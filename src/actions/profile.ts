"use server";

import { db } from "@/lib/db";
import { requireRole } from "@/lib/auth/guards";
import { reverseGeocode } from "@/lib/problems";
import { saveProfilePicture } from "@/lib/uploads";
import { fieldErrors, type ActionState } from "@/lib/action-state";
import { z } from "zod";
import { unlink } from "node:fs/promises";
import path from "node:path";

export type { ActionState };

const profileSchema = z.object({
  phone: z.string().max(50).optional().default(""),
  location: z.string().max(255).optional().default(""),
  latitude: z.string().regex(/^\s*-?\d+(\.\d+)?\s*$/, {
    error: "Invalid latitude.",
  }).optional().default(""),
  longitude: z.string().regex(/^\s*-?\d+(\.\d+)?\s*$/, {
    error: "Invalid longitude.",
  }).optional().default(""),
});

export async function updateProfile(
  _prevState: ActionState,
  formData: FormData,
): Promise<ActionState> {
  const session = await requireRole("user");

  const parsed = profileSchema.safeParse({
    phone: formData.get("phone"),
    location: formData.get("location"),
    latitude: formData.get("latitude"),
    longitude: formData.get("longitude"),
  });
  if (!parsed.success) {
    return { errors: fieldErrors(parsed.error.flatten()) };
  }

  let latitude: number | null = null;
  let longitude: number | null = null;
  if (parsed.data.latitude && parsed.data.longitude) {
    latitude = Number(parsed.data.latitude);
    longitude = Number(parsed.data.longitude);
  }

  let locationText = parsed.data.location;
  if (latitude !== null && longitude !== null && /^\s*-?\d+\.?\d*\s*,/.test(locationText) === false) {
    const resolved = await reverseGeocode(latitude, longitude);
    if (resolved) locationText = resolved;
    else locationText = `${latitude.toFixed(6)}, ${longitude.toFixed(6)}`;
  }

  const now = await db.users.findUnique({
    where: { user_id: session.userId },
    select: { profile_pic: true },
  });

  let profilePicName: string | null = null;
  const file = formData.get("profilePic");
  if (file instanceof File && file.size > 0) {
    try {
      profilePicName = await saveProfilePicture(file);
    } catch (e) {
      return { message: e instanceof Error ? e.message : "Profile picture upload failed." };
    }
  }

  await db.users.update({
    where: { user_id: session.userId },
    data: {
      phone: parsed.data.phone || null,
      location: locationText || null,
      latitude,
      longitude,
      ...(profilePicName ? { profile_pic: profilePicName } : {}),
    },
  });

  if (profilePicName && now?.profile_pic) {
    const oldPath = path.join(process.cwd(), "public", "uploads", "profile_pics", now.profile_pic);
    try {
      await unlink(oldPath);
    } catch {
      // ignore missing file
    }
  }

  return { message: "Profile updated successfully." };
}