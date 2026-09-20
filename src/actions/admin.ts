"use server";

import { revalidatePath } from "next/cache";
import { redirect } from "next/navigation";
import { z } from "zod";
import { db } from "@/lib/db";
import { requireRole } from "@/lib/auth/guards";

const clickActions = [
  "verifyProblem",
  "rejectProblem",
  "deleteProblem",
  "approveTeam",
  "rejectTeam",
  "unbanUser",
  "approveAppeal",
  "rejectAppeal",
] as const;
export type AdminClickAction = (typeof clickActions)[number];

const clickActionSchema = z.object({
  action: z.enum(clickActions),
  problemId: z.coerce.number().int().positive().optional(),
  userId: z.coerce.number().int().positive().optional(),
  requestId: z.coerce.number().int().positive().optional(),
  back: z.string().min(1).max(120).optional(),
});

function flash(backPath: string, kind: "ok" | "err", message: string) {
  const path = safeBack(backPath, "/admin/dashboard");
  const params = new URLSearchParams({ flash: kind, msg: message });
  const sep = path.includes("?") ? "&" : "?";
  redirect(`${path}${sep}${params.toString()}`);
}

function safeBack(value: string | undefined, fallback: string): string {
  const v = value?.trim() ?? "";
  if (!v.startsWith("/")) return fallback;
  return v;
}

export async function runClickAction(formData: FormData): Promise<void> {
  const session = await requireRole("admin");

  const parsed = clickActionSchema.safeParse({
    action: formData.get("action"),
    problemId: formData.get("problemId") || undefined,
    userId: formData.get("userId") || undefined,
    requestId: formData.get("requestId") || undefined,
    back: formData.get("back") || undefined,
  });
  if (!parsed.success) {
    flash("/admin/dashboard", "err", "Invalid request.");
  }
  const { action, problemId, userId, requestId, back } = parsed.data!;
  const backPath = safeBack(back, "/admin/dashboard");

  switch (action) {
    case "verifyProblem": {
      if (!problemId) flash(backPath, "err", "Missing problem id.");
      await db.problems.update({
        where: { problem_id: problemId! },
        data: { status: "verified" },
      });
      revalidatePath(backPath);
      flash(backPath, "ok", "Problem verified.");
      break;
    }
    case "rejectProblem": {
      if (!problemId) flash(backPath, "err", "Missing problem id.");
      await db.problems.update({
        where: { problem_id: problemId! },
        data: { status: "rejected" },
      });
      revalidatePath(backPath);
      flash(backPath, "ok", "Problem rejected.");
      break;
    }
    case "deleteProblem": {
      if (!problemId) flash(backPath, "err", "Missing problem id.");
      deleteProblem(problemId!);
      flash(backPath, "ok", `Complaint #${problemId!} deleted.`);
      break;
    }
    case "approveTeam": {
      if (!userId) flash(backPath, "err", "Missing team id.");
      await db.users.updateMany({
        where: { user_id: userId!, role: "response" },
        data: { status: "active" },
      });
      revalidatePath(backPath);
      flash(backPath, "ok", "Response team approved.");
      break;
    }
    case "rejectTeam": {
      if (!userId) flash(backPath, "err", "Missing team id.");
      await db.users.updateMany({
        where: { user_id: userId!, role: "response" },
        data: { status: "rejected" },
      });
      revalidatePath(backPath);
      flash(backPath, "ok", "Response team rejected.");
      break;
    }
    case "unbanUser": {
      if (!userId) flash(backPath, "err", "Missing user id.");
      await db.$transaction([
        db.users.update({
          where: { user_id: userId! },
          data: { is_banned: false, ban_until: null },
        }),
        db.unban_requests.deleteMany({ where: { user_id: userId! } }),
      ]);
      revalidatePath(backPath);
      flash(backPath, "ok", "User unbanned.");
      break;
    }
    case "approveAppeal": {
      if (!requestId || !userId) flash(backPath, "err", "Missing appeal data.");
      await db.$transaction([
        db.users.update({
          where: { user_id: userId! },
          data: { is_banned: false, ban_until: null },
        }),
        db.unban_requests.update({
          where: { id: requestId! },
          data: { status: "approved", admin_response: "Approved by admin", reviewed_at: new Date() },
        }),
      ]);
      revalidatePath(backPath);
      flash(backPath, "ok", "Appeal approved — user unbanned.");
      break;
    }
    case "rejectAppeal": {
      if (!requestId) flash(backPath, "err", "Missing appeal id.");
      await db.unban_requests.update({
        where: { id: requestId! },
        data: { status: "rejected", admin_response: "Rejected by admin", reviewed_at: new Date() },
      });
      revalidatePath(backPath);
      flash(backPath, "ok", "Appeal rejected.");
      break;
    }
  }
}

async function deleteProblem(problemId: number) {
  const problem = await db.problems.findFirst({
    where: { problem_id: problemId, deleted_by_admin: false },
  });
  if (!problem) {
    flash("/admin/problems", "err", "Problem not found or already deleted.");
  }
  const user = await db.users.findUnique({
    where: { user_id: problem!.user_id },
    select: { name: true, email: true, phone: true },
  });
  await db.$transaction([
    db.problems.update({
      where: { problem_id: problemId },
      data: { deleted_by_admin: true },
    }),
    db.deleted_problems.create({
      data: {
        problem_id: problemId,
        user_id: problem!.user_id,
        user_name: user?.name ?? "Unknown (User Deleted)",
        user_email: user?.email ?? "",
        user_phone: user?.phone ?? "",
        category: problem!.category,
        description: problem!.description,
        suggestion: problem!.suggestion,
        location: problem!.location ?? undefined,
        status: problem!.status,
        priority: problem!.priority,
        report: problem!.report ?? undefined,
      },
    }),
  ]);
}

const PRIORITIES = ["low", "medium", "high", "sos"] as const;

const assignSchema = z.object({
  problemId: z.coerce.number().int().positive(),
  priority: z.enum(PRIORITIES),
  assignedTo: z.coerce.number().int().positive(),
});

export async function assignProblem(formData: FormData): Promise<void> {
  await requireRole("admin");

  const parsed = assignSchema.safeParse({
    problemId: formData.get("problemId"),
    priority: formData.get("priority"),
    assignedTo: formData.get("assignedTo"),
  });
  if (!parsed.success) {
    flash("/admin/problems", "err", "Missing fields for assignment.");
  }
  const { problemId, priority, assignedTo } = parsed.data!;

  const problem = await db.problems.findUnique({
    where: { problem_id: problemId },
    select: { category: true },
  });
  if (!problem) {
    flash("/admin/problems", "err", "Problem not found.");
  }

  const team = await db.users.findFirst({
    where: {
      user_id: assignedTo,
      role: "response",
      status: "active",
      category: problem!.category,
    },
    select: { user_id: true },
  });
  if (!team) {
    flash("/admin/problems", "err", "Selected team is not an active matching team.");
  }

  await db.problems.update({
    where: { problem_id: problemId },
    data: { priority, assigned_to: assignedTo, status: "assigned" },
  });

  revalidatePath("/admin/problems");
  revalidatePath("/admin/dashboard");
  flash("/admin/problems", "ok", "Problem assigned.");
}

const banSchema = z
  .object({
    userId: z.coerce.number().int().positive(),
    banDays: z.coerce.number().int().min(1).max(3650).optional(),
    permanent: z
      .string()
      .optional()
      .transform((v) => v === "1"),
  })
  .refine((v) => v.permanent || (v.banDays ?? 0) > 0, {
    message: "Provide a ban duration or choose permanent.",
  });

export async function banUser(formData: FormData): Promise<void> {
  await requireRole("admin");

  const parsed = banSchema.safeParse({
    userId: formData.get("userId"),
    banDays: formData.get("banDays") || undefined,
    permanent: formData.get("permanent") || undefined,
  });
  const back = safeBack(formData.get("back") as string, "/admin/users");
  if (!parsed.success) {
    flash(back, "err", "Provide a ban duration (1–3650 days) or permanent.");
  }

  const { userId, banDays, permanent } = parsed.data!;
  const banUntil = permanent
    ? "permanent"
    : localDateTime(new Date(Date.now() + (banDays ?? 7) * 24 * 60 * 60 * 1000));

  await db.users.update({
    where: { user_id: userId },
    data: { is_banned: true, ban_until: banUntil },
  });

  revalidatePath(back);
  flash(back, "ok", "User banned.");
}

const warningSchema = z.object({
  userId: z.coerce.number().int().positive(),
  message: z.string().trim().min(3, "Warning message is too short."),
});

export async function sendWarning(formData: FormData): Promise<void> {
  const session = await requireRole("admin");

  const parsed = warningSchema.safeParse({
    userId: formData.get("userId"),
    message: formData.get("message"),
  });
  const back = safeBack(formData.get("back") as string, "/admin/users");
  if (!parsed.success) {
    flash(back, "err", "Please write a warning message.");
  }

  await db.warnings.create({
    data: {
      user_id: parsed.data!.userId,
      admin_id: session.userId,
      message: parsed.data!.message,
    },
  });

  revalidatePath(back);
  flash(back, "ok", "Warning sent.");
}

function localDateTime(date: Date): string {
  const pad = (n: number) => String(n).padStart(2, "0");
  return `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(date.getDate())} ${pad(
    date.getHours(),
  )}:${pad(date.getMinutes())}:${pad(date.getSeconds())}`;
}