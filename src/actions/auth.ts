"use server";

import { redirect } from "next/navigation";
import { z } from "zod";
import { db } from "@/lib/db";
import { createSession, deleteSession } from "@/lib/auth/session";
import { requireSession } from "@/lib/auth/guards";
import { hashPassword, verifyPassword } from "@/lib/auth/password";
import { homePathFor } from "@/lib/auth/routes";
import type { Role } from "@/lib/auth/session-core";

export type ActionState = {
  message?: string;
  errors?: Record<string, string[]>;
} | null;

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
      .min(6, { error: "Password must be at least 6 characters long." }),
    confirmPassword: z.string(),
  })
  .refine((v) => v.password === v.confirmPassword, {
    message: "Passwords do not match.",
    path: ["confirmPassword"],
  });

function fieldErrors(formatted: {
  fieldErrors: Record<string, string[]>;
  formErrors: string[];
}): Record<string, string[]> {
  if (formatted.formErrors.length > 0) {
    return { _form: formatted.formErrors };
  }
  return formatted.fieldErrors;
}

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
    return { message: "Invalid login credentials." };
  }

  if (user.is_banned) {
    return { message: "Your account has been suspended." };
  }

  const role = user.role as Role;
  if (role === "response" && user.status !== "active") {
    return {
      message: "Your Response Team account is still pending admin approval.",
    };
  }

  await createSession({ userId: user.user_id, role, name: user.name });
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

  const existing = await db.users.findUnique({ where: { email } });
  if (existing) {
    return { message: "This email is already registered." };
  }

  try {
    const hashed = await hashPassword(password);

    if (role === "user") {
      await db.users.create({
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
      await createSession({
        userId: (
          await db.users.findUniqueOrThrow({ where: { email } })
        ).user_id,
        role: "user",
        name,
      });
      redirect(homePathFor("user"));
    }

    await db.users.create({
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

  await db.users.update({
    where: { user_id: session.userId },
    data: { password: await hashPassword(next) },
  });

  return { message: "Password updated successfully!" };
}

export async function logout(): Promise<void> {
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