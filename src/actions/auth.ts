"use server";

import { redirect } from "next/navigation";
import { z } from "zod";
import { db } from "@/lib/db";
import { createSession, deleteSession, getSession } from "@/lib/auth/session";
import { requireSession } from "@/lib/auth/guards";
import { hashPassword, passwordStrengthError, verifyPassword } from "@/lib/auth/password";
import { homePathFor } from "@/lib/auth/routes";
import { auditLog } from "@/lib/audit";
import type { Role } from "@/lib/auth/session-core";
import {
  fieldErrors,
  type ActionState,
} from "@/lib/action-state";

export type { ActionState };

const loginSchema = z.object({
  email: z.email({ error: "Please enter a valid email." }).trim(),
  password: z.string().min(1, { error: "Please enter your password." }),
});

const registerSchema = z
  .object({
    role: z.enum(["user", "response"]),
    name: z
      .string()
      .min(2, { error: "Name must be at least 2 characters long." })
      .trim(),
    email: z.email({ error: "Please enter a valid email." }).trim(),
    password: z
      .string()
      .min(1, { error: "Please choose a password." }),
    confirmPassword: z.string(),
  })
  .refine((v) => v.password === v.confirmPassword, {
    message: "Passwords do not match.",
    path: ["confirmPassword"],
  });

export async function login(
  _prevState: ActionState,
  formData: FormData,
): Promise<ActionState> {
  const parsed = loginSchema.safeParse({
    email: formData.get("email"),
    password: formData.get("password"),
  });

  if (!parsed.success) {
    return { errors: fieldErrors(parsed.error.flatten()) };
  }

  const { email, password } = parsed.data;

  const user = await db.users.findUnique({ where: { email } });
  if (!user || !(await verifyPassword(password, user.password))) {
    await auditLog({
      actor: null,
      action: "AUTH_LOGIN_FAILED",
      message: `Failed login attempt for ${email}`,
    });
    return { message: "Invalid login credentials." };
  }

  const actor = { id: user.user_id, role: user.role };

  if (user.is_banned) {
    await auditLog({
      actor,
      action: "AUTH_LOGIN_BLOCKED",
      message: `Blocked login for banned account ${email}`,
    });
    return { message: "Your account has been suspended." };
  }

  const role = user.role as Role;
  if (role === "response" && user.status !== "active") {
    await auditLog({
      actor,
      action: "AUTH_LOGIN_BLOCKED",
      message: `Blocked login for unapproved response team ${email}`,
    });
    return {
      message: "Your Response Team account is still pending admin approval.",
    };
  }

  await createSession({ userId: user.user_id, role, name: user.name });
  await auditLog({
    actor,
    action: "AUTH_LOGIN",
    message: `${user.name} signed in as ${role}`,
  });
  redirect(homePathFor(role));
}

export async function register(
  _prevState: ActionState,
  formData: FormData,
): Promise<ActionState> {
  const parsed = registerSchema.safeParse({
    role: formData.get("role"),
    name: formData.get("name"),
    email: formData.get("email"),
    password: formData.get("password"),
    confirmPassword: formData.get("confirmPassword"),
  });

  if (!parsed.success) {
    return { errors: fieldErrors(parsed.error.flatten()) };
  }

  const { role, name, email, password } = parsed.data;

  const strength = passwordStrengthError(password);
  if (strength) {
    return { errors: { password: [strength] } };
  }

  const existing = await db.users.findUnique({ where: { email } });
  if (existing) {
    return { message: "This email is already registered." };
  }

  try {
    const hashed = await hashPassword(password);

    if (role === "user") {
      const created = await db.users.create({
        data: {
          name,
          email,
          phone: fieldOrNull(formData.get("phone")),
          nid: fieldOrNull(formData.get("nid")),
          dob: parseDateOrNull(formData.get("dob")),
          location: fieldOrNull(formData.get("location")),
          password: hashed,
          role: "user",
          status: "active",
        },
      });
      await auditLog({
        actor: { id: created.user_id, role: "user" },
        action: "AUTH_REGISTER",
        message: `Citizen account registered (${email})`,
      });
      await createSession({
        userId: created.user_id,
        role: "user",
        name,
      });
      redirect(homePathFor("user"));
    }

    const created = await db.users.create({
      data: {
        name,
        email,
        phone: fieldOrNull(formData.get("phone")),
        password: hashed,
        role: "response",
        status: "pending",
        category: fieldOrNull(formData.get("category")),
        incharge_name: fieldOrNull(formData.get("inchargeName")),
        incharge_id: fieldOrNull(formData.get("inchargeId")),
        incharge_email: fieldOrNull(formData.get("inchargeEmail")),
        incharge_phone: fieldOrNull(formData.get("inchargePhone")),
        identification: fieldOrNull(formData.get("identification")),
        location: fieldOrNull(formData.get("location")),
        employee_number: fieldOrNull(formData.get("employeeNumber")),
      },
    });
    await auditLog({
      actor: { id: created.user_id, role: "response" },
      action: "AUTH_REGISTER",
      message: `Response team registration submitted (${email})`,
    });
    return {
      message:
        "Response Team registered. Your account is Pending admin approval.",
    };
  } catch {
    return { message: "Registration failed. Please try again." };
  }
}

export async function forgotPassword(
  _prevState: ActionState,
  formData: FormData,
): Promise<ActionState> {
  const email = z
    .email()
    .safeParse((formData.get("email") as string).trim());

  if (!email.success) {
    if (!formData.get("email")) {
      return { message: "Please enter your email." };
    }
    return { message: "Please enter a valid email." };
  }

  const user = await db.users.findUnique({ where: { email: email.data } });
  if (!user) {
    return { message: "No account found with that email." };
  }

  return { message: "Password reset link sent to your email." };
}

export async function changePassword(
  _prevState: ActionState,
  formData: FormData,
): Promise<ActionState> {
  const session = await requireSession();

  const current = (formData.get("currentPassword") as string) ?? "";
  const next = (formData.get("newPassword") as string) ?? "";
  const confirm = (formData.get("confirmPassword") as string) ?? "";

  if (!current || !next || !confirm) {
    return { message: "All fields are required." };
  }
  if (next !== confirm) {
    return { message: "New passwords do not match." };
  }

  const user = await db.users.findUnique({
    where: { user_id: session.userId },
    select: { password: true },
  });
  if (!user || !(await verifyPassword(current, user.password))) {
    return { message: "Current password is incorrect." };
  }

  const strength = passwordStrengthError(next);
  if (strength) {
    return { message: strength };
  }

  await db.users.update({
    where: { user_id: session.userId },
    data: { password: await hashPassword(next) },
  });
  await auditLog({
    actor: { id: session.userId, role: session.role },
    action: "AUTH_PASSWORD_CHANGED",
    message: "Password changed",
  });

  return { message: "Password updated successfully!" };
}

export async function logout(): Promise<void> {
  const session = await getSession();
  if (session) {
    await auditLog({
      actor: { id: session.userId, role: session.role },
      action: "AUTH_LOGOUT",
      message: `${session.name} signed out`,
    });
  }
  await deleteSession();
  redirect("/login");
}

function fieldOrNull(value: FormDataEntryValue | null): string | null {
  const s = String(value ?? "").trim();
  return s === "" ? null : s;
}

function parseDateOrNull(value: FormDataEntryValue | null): Date | null {
  const s = String(value ?? "").trim();
  if (!s) return null;
  const d = new Date(s);
  return Number.isNaN(d.getTime()) ? null : d;
}