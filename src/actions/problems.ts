"use server";

import { z } from "zod";
import { db } from "@/lib/db";
import { requireRole } from "@/lib/auth/guards";
import {
  inDhaka,
  isCurrentlyBanned,
  PROBLEM_CATEGORIES,
  resolveLocation,
  reverseGeocode,
  SOS_CATEGORY,
} from "@/lib/problems";
import { saveProblemMedia } from "@/lib/uploads";
import {
  fieldErrors,
  type ActionState,
} from "@/lib/action-state";
import { auditLog } from "@/lib/audit";

export type { ActionState };

const reportSchema = z.object({
  category: z.enum(PROBLEM_CATEGORIES),
  description: z
    .string()
    .min(10, {
      error: "Please describe the issue with at least 10 characters.",
    })
    .max(2000),
  suggestion: z.string().max(255).optional(),
  latitude: z.coerce.number(),
  longitude: z.coerce.number(),
});

export async function reportProblem(
  _prevState: ActionState,
  formData: FormData,
): Promise<ActionState> {
  const session = await requireRole("user");

  const parsed = reportSchema.safeParse({
    category: formData.get("category"),
    description: formData.get("description"),
    suggestion: formData.get("suggestion") || undefined,
    latitude: formData.get("latitude"),
    longitude: formData.get("longitude"),
  });

  if (!parsed.success) {
    return { errors: fieldErrors(parsed.error.flatten()) };
  }

  const user = await db.users.findUnique({
    where: { user_id: session.userId },
    select: { is_banned: true, ban_until: true },
  });
  if (!user || isCurrentlyBanned(user)) {
    return { message: "Your account is banned and cannot submit reports." };
  }

  const { category, description, suggestion, latitude, longitude } = parsed.data;

  if (!Number.isFinite(latitude) || !Number.isFinite(longitude)) {
    return { message: "Please pick a valid location." };
  }
  if (latitude === 0 && longitude === 0) {
    return { message: "Please set a location using the map buttons." };
  }

  if (!inDhaka(latitude, longitude)) {
    return { message: "Reports are only accepted inside Dhaka city." };
  }

  const locationName = resolveLocation(
    null,
    latitude,
    longitude,
  );
  const resolved = await reverseGeocode(latitude, longitude);
  const location = resolved ?? locationName;

  let media: string[] = [];
  try {
    media = await saveProblemMedia(
      Array.from(formData.getAll("media")).filter((f): f is File => f instanceof File),
    );
  } catch (e) {
    return { message: e instanceof Error ? e.message : "Media upload failed." };
  }

  const created = await db.problems.create({
    data: {
      user_id: session.userId,
      category,
      description,
      suggestion: suggestion || null,
      location,
      location_name: location,
      latitude,
      longitude,
      status: "pending",
      priority: "medium",
      media_path: media.length > 0 ? media.join(",") : null,
    },
  });

  await auditLog({
    actor: { id: session.userId, role: session.role },
    action: "REPORT_CREATED",
    message: `Problem reported (${category})`,
    problemId: created.problem_id,
  });

  return { message: "Report submitted successfully." };
}

export type SosResult = { ok: true } | { ok: false; message: string };

export async function sosAlert(latitude: number, longitude: number): Promise<SosResult> {
  try {
    const session = await requireRole("user");
    if (!inDhaka(latitude, longitude)) {
      return { ok: false, message: "SOS alerts are only accepted inside Dhaka." };
    }
    const user = await db.users.findUnique({
      where: { user_id: session.userId },
      select: { is_banned: true, ban_until: true },
    });
    if (!user || isCurrentlyBanned(user)) {
      return { ok: false, message: "Your account is banned and cannot send SOS." };
    }

    const location = (await reverseGeocode(latitude, longitude)) ?? `${latitude.toFixed(6)}, ${longitude.toFixed(6)}`;

    const problem = await db.problems.create({
      data: {
        user_id: session.userId,
        category: SOS_CATEGORY,
        description: "EMERGENCY SOS ALERT - Immediate assistance required",
        location,
        location_name: location,
        latitude,
        longitude,
        status: "pending",
        priority: "high",
      },
    });

    await auditLog({
      actor: { id: session.userId, role: session.role },
      action: "SOS_TRIGGERED",
      message: "Emergency SOS alert triggered",
      problemId: problem.problem_id,
    });

    return { ok: true };
  } catch {
    return { ok: false, message: "Failed to send SOS alert. Please try again." };
  }
}

export async function submitFeedback(
  _prevState: ActionState,
  formData: FormData,
): Promise<ActionState> {
  const session = await requireRole("user");

  const problemId = Number(formData.get("problemId"));
  const rating = Number(formData.get("rating"));
  const comment = String(formData.get("comment") ?? "").trim();

  if (!Number.isInteger(problemId) || problemId <= 0) {
    return { message: "Invalid problem." };
  }
  if (!Number.isInteger(rating) || rating < 1 || rating > 5) {
    return { message: "Rating must be between 1 and 5." };
  }

  const problem = await db.problems.findUnique({ where: { problem_id: problemId } });
  if (!problem || problem.user_id !== session.userId) {
    return { message: "Problem not found or not yours." };
  }
  if (problem.status !== "resolved") {
    return { message: "Feedback is allowed only after the issue is resolved." };
  }

  const existing = await db.feedbacks.findFirst({
    where: { problem_id: problemId, user_id: session.userId },
  });
  if (existing) {
    return { message: "You already submitted feedback for this report." };
  }

  await db.feedbacks.create({
    data: {
      problem_id: problemId,
      user_id: session.userId,
      rating,
      comment: comment || null,
    },
  });
  await auditLog({
    actor: { id: session.userId, role: session.role },
    action: "FEEDBACK_SUBMITTED",
    message: `Feedback (${rating}/5) for problem #${problemId}`,
    problemId,
  });

  return { message: "Thank you — your feedback has been recorded." };
}

export async function submitUnbanAppeal(
  _prevState: ActionState,
  formData: FormData,
): Promise<ActionState> {
  const session = await requireRole("user");

  const message = String(formData.get("message") ?? "").trim();
  if (!message) {
    return { message: "Please provide a reason for your unban request." };
  }

  const pending = await db.unban_requests.findFirst({
    where: { user_id: session.userId, status: "pending" },
  });
  if (pending) {
    return { message: "You already have a pending unban request." };
  }

  await db.unban_requests.create({
    data: {
      user_id: session.userId,
      message,
      reason: message,
    },
  });
  await auditLog({
    actor: { id: session.userId, role: session.role },
    action: "UNBAN_APPEAL_SUBMITTED",
    message: "Unban appeal submitted",
  });

  return { message: "Your unban request has been submitted for admin review." };
}