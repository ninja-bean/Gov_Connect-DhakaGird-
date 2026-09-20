"use server";

import { revalidatePath } from "next/cache";
import { z } from "zod";
import { db } from "@/lib/db";
import { requireRole } from "@/lib/auth/guards";
import { fieldErrors, type ActionState } from "@/lib/action-state";
import { inDhaka } from "@/lib/problems";
import { saveProfilePicture, PROFILE_PIC_MAX_BYTES } from "@/lib/uploads";

export type { ActionState };

export type TeamActionState = ActionState;

const TEAM_CATEGORIES = ["police", "medical", "fire", "gov"] as const;

const startWorkSchema = z.object({
  problemId: z.coerce.number().int().positive(),
  members: z.coerce.number().int().min(1),
});

const resolveSchema = z.object({
  problemId: z.coerce.number().int().positive(),
  report: z.string().trim().min(10, "Please describe what your team did."),
});

const teamProfileSchema = z.object({
  name: z.string().trim().min(2, "Team name is required."),
  category: z.enum(TEAM_CATEGORIES),
  inchargeName: z.string().trim().min(2, "In-charge name is required."),
  inchargeEmail: z.email("Please enter a valid email.").trim(),
  inchargePhone: z.string().trim().min(5, "In-charge phone is required."),
  employeeNumber: z.coerce.number().int().min(1, "Team size must be at least 1.").max(500),
  location: z.string().trim().min(2, "Station location is required."),
  latitude: z.coerce.number(),
  longitude: z.coerce.number(),
});

export async function startWorking(
  _prevState: TeamActionState,
  formData: FormData,
): Promise<TeamActionState> {
  const session = await requireRole("response");

  const parsed = startWorkSchema.safeParse({
    problemId: formData.get("problemId"),
    members: formData.get("members"),
  });
  if (!parsed.success) {
    return { errors: fieldErrors(parsed.error.flatten()) };
  }
  const { problemId, members } = parsed.data;

  const team = await db.users.findUniqueOrThrow({
    where: { user_id: session.userId },
    select: { total_members: true, busy_members: true },
  });
  const available = team.total_members - team.busy_members;

  if (available <= 0) {
    return { message: "No members available!" };
  }
  if (members > available) {
    return {
      message: `Not enough available members! Only ${available} member(s) free.`,
    };
  }

  const problem = await db.problems.findFirst({
    where: { problem_id: problemId, assigned_to: session.userId },
    select: { problem_id: true, status: true },
  });
  if (!problem) {
    return { message: "This problem was not found or is no longer assigned to you." };
  }
  if (problem.status === "working") {
    return { message: "Your team is already working on this problem." };
  }
  if (problem.status === "resolved") {
    return { message: "This problem has already been resolved." };
  }

  const newBusy = Math.min(team.total_members, team.busy_members + members);

  await db.$transaction([
    db.problems.update({
      where: { problem_id: problemId },
      data: { status: "working", working_members: members },
    }),
    db.users.update({
      where: { user_id: session.userId },
      data: { busy_members: newBusy },
    }),
  ]);

  revalidatePath("/response/dashboard");
  return { message: "Problem marked as in progress." };
}

export async function resolveProblem(
  _prevState: TeamActionState,
  formData: FormData,
): Promise<TeamActionState> {
  const session = await requireRole("response");

  const parsed = resolveSchema.safeParse({
    problemId: formData.get("problemId"),
    report: formData.get("report"),
  });
  if (!parsed.success) {
    return { message: "Please write a report before resolving (at least 10 characters)." };
  }
  const { problemId, report } = parsed.data;

  const team = await db.users.findUniqueOrThrow({
    where: { user_id: session.userId },
    select: { busy_members: true },
  });

  const problem = await db.problems.findFirst({
    where: { problem_id: problemId, assigned_to: session.userId },
    select: { problem_id: true, status: true, working_members: true },
  });
  if (!problem) {
    return { message: "This problem was not found or is no longer assigned to you." };
  }
  if (problem.status !== "working") {
    return { message: "Only a problem your team is working on can be resolved." };
  }

  const released = problem.working_members;
  const newBusy = Math.max(0, team.busy_members - released);

  await db.$transaction([
    db.problems.update({
      where: { problem_id: problemId },
      data: { status: "resolved", working_members: 0, report },
    }),
    db.users.update({
      where: { user_id: session.userId },
      data: { busy_members: newBusy },
    }),
  ]);

  revalidatePath("/response/dashboard");
  return { message: "Problem resolved successfully." };
}

export async function updateTeamProfile(
  _prevState: TeamActionState,
  formData: FormData,
): Promise<TeamActionState> {
  const session = await requireRole("response");

  const parsed = teamProfileSchema.safeParse({
    name: formData.get("name"),
    category: formData.get("category"),
    inchargeName: formData.get("inchargeName"),
    inchargeEmail: formData.get("inchargeEmail"),
    inchargePhone: formData.get("inchargePhone"),
    employeeNumber: formData.get("employeeNumber"),
    location: formData.get("location"),
    latitude: formData.get("latitude"),
    longitude: formData.get("longitude"),
  });
  if (!parsed.success) {
    return { errors: fieldErrors(parsed.error.flatten()) };
  }

  const data = parsed.data;

  if (!inDhaka(data.latitude, data.longitude)) {
    return { message: "Location must be within Dhaka city limits." };
  }

  let profilePic: string | undefined;
  const file = formData.get("profilePic");
  if (file instanceof File && file.size > 0) {
    try {
      if (file.size > PROFILE_PIC_MAX_BYTES) {
        return { message: "Profile picture too large (max 2MB)." };
      }
      profilePic = await saveProfilePicture(file);
    } catch (e) {
      return { message: e instanceof Error ? e.message : "Failed to upload photo." };
    }
  }

  await db.users.update({
    where: { user_id: session.userId },
    data: {
      name: data.name,
      category: data.category,
      incharge_name: data.inchargeName,
      incharge_email: data.inchargeEmail,
      incharge_phone: data.inchargePhone,
      employee_number: String(data.employeeNumber),
      total_members: data.employeeNumber,
      location: data.location,
      latitude: data.latitude,
      longitude: data.longitude,
      ...(profilePic ? { profile_pic: profilePic } : {}),
    },
  });

  revalidatePath("/response/profile");
  revalidatePath("/response/dashboard");
  return { message: "Team profile updated successfully." };
}