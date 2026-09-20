import "server-only";
import { cookies } from "next/headers";
import {
  SESSION_COOKIE,
  SESSION_TTL_MS,
  decrypt,
  encrypt,
  type SessionPayload,
} from "@/lib/auth/session-core";

const cookieOptions = {
  httpOnly: true,
  secure: process.env.NODE_ENV === "production",
  sameSite: "lax" as const,
  path: "/",
};

export async function createSession(payload: SessionPayload): Promise<void> {
  const expiresAt = new Date(Date.now() + SESSION_TTL_MS);
  const token = await encrypt(payload, expiresAt);
  const store = await cookies();
  store.set(SESSION_COOKIE, token, { ...cookieOptions, expires: expiresAt });
}

export async function getSession(): Promise<SessionPayload | null> {
  const store = await cookies();
  return decrypt(store.get(SESSION_COOKIE)?.value);
}

export async function deleteSession(): Promise<void> {
  const store = await cookies();
  store.delete(SESSION_COOKIE);
}