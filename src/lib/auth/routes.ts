import type { Role } from "@/lib/auth/session-core";

export const ROLE_HOME: Record<Role, string> = {
  user: "/dashboard",
  response: "/response/dashboard",
  admin: "/admin/dashboard",
};

export function homePathFor(role: Role): string {
  return ROLE_HOME[role];
}