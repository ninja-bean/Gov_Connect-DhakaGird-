import "server-only";
import { SignJWT, jwtVerify } from "jose";
import { env } from "@/lib/env";

export type Role = "user" | "response" | "admin";

export type SessionPayload = {
  userId: number;
  role: Role;
  name: string;
};

export const SESSION_COOKIE = "session";
export const SESSION_TTL_MS = 7 * 24 * 60 * 60 * 1000; // 7 days

const secretKey = new TextEncoder().encode(env.authSecret);

export async function encrypt(
  payload: SessionPayload,
  expiresAt: Date,
): Promise<string> {
  return new SignJWT({ ...payload })
    .setProtectedHeader({ alg: "HS256" })
    .setIssuedAt()
    .setExpirationTime(Math.floor(expiresAt.getTime() / 1000))
    .sign(secretKey);
}

export async function decrypt(
  token: string | undefined,
): Promise<SessionPayload | null> {
  if (!token) return null;
  try {
    const { payload } = await jwtVerify(token, secretKey, {
      algorithms: ["HS256"],
    });
    if (
      typeof payload.userId !== "number" ||
      typeof payload.role !== "string" ||
      typeof payload.name !== "string"
    ) {
      return null;
    }
    return {
      userId: payload.userId,
      role: payload.role as Role,
      name: payload.name,
    } satisfies SessionPayload;
  } catch {
    return null;
  }
}