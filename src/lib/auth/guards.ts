import "server-only";
import { cache } from "react";
import { redirect } from "next/navigation";
import { getSession } from "@/lib/auth/session";
import { homePathFor } from "@/lib/auth/routes";
import type { Role, SessionPayload } from "@/lib/auth/session-core";

export const requireSession = cache(async (): Promise<SessionPayload> => {
  const session = await getSession();
  if (!session) {
    redirect("/login");
  }
  return session;
});

export const requireRole = cache(
  async (...allowed: Role[]): Promise<SessionPayload> => {
    const session = await requireSession();
    if (!allowed.includes(session.role)) {
      redirect(homePathFor(session.role));
    }
    return session;
  },
);