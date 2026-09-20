import "server-only";
import { db } from "@/lib/db";

export type AuditActor = { id: number; role: string };

/**
 * Persists an audit event to the shared `logs` table. Failures never bubble up:
 * audit trails must not break the primary user flow.
 */
export async function auditLog(input: {
  actor: AuditActor | null;
  action: string;
  message: string;
  problemId?: number | null;
}): Promise<void> {
  try {
    await db.logs.create({
      data: {
        user_id: input.actor?.id ?? null,
        problem_id: input.problemId ?? null,
        notification_type: input.action,
        message: input.message,
      },
    });
  } catch {
    // Best-effort audit.
  }
}