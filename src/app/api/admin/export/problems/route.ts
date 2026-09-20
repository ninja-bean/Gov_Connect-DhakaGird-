import { NextResponse } from "next/server";
import { cookies } from "next/headers";
import { db } from "@/lib/db";
import { decrypt } from "@/lib/auth/session-core";
import { auditLog } from "@/lib/audit";
import { toCsv } from "@/lib/csv";

export const dynamic = "force-dynamic";
export const runtime = "nodejs";

const HEADERS = [
  "ID",
  "Category",
  "Description",
  "Suggestion",
  "Location",
  "Status",
  "Priority",
  "Assigned team",
  "Reporter",
  "Reporter email",
  "Reporter phone",
  "Team report",
  "Created at",
] as const;

export async function GET() {
  const session = await decrypt((await cookies()).get("session")?.value);
  if (!session || session.role !== "admin") {
    return NextResponse.json({ error: "Unauthorized" }, { status: 401 });
  }

  const [problems, teams] = await Promise.all([
    db.problems.findMany({
      where: { deleted_by_admin: false },
      include: { users: { select: { name: true, email: true, phone: true } } },
      orderBy: { created_at: "desc" },
    }),
    db.users.findMany({
      where: { role: "response", status: "active" },
      select: { user_id: true, name: true },
    }),
  ]);

  const teamName = new Map(teams.map((t) => [t.user_id, t.name]));

  const rows = [
    [...HEADERS],
    ...problems.map((p) => [
      p.problem_id,
      p.category,
      p.description ?? "",
      p.suggestion ?? "",
      p.location_name ?? p.location ?? "",
      p.status,
      p.priority,
      p.assigned_to ? (teamName.get(p.assigned_to) ?? `Team #${p.assigned_to}`) : "",
      p.users.name,
      p.users.email,
      p.users.phone ?? "",
      p.report ?? "",
      p.created_at.toISOString().replace("T", " ").slice(0, 19),
    ]),
  ];

  await auditLog({
    actor: { id: session.userId, role: session.role },
    action: "PROBLEMS_EXPORTED",
    message: `Exported ${problems.length} problems to CSV`,
  });

  const stamp = new Date().toISOString().slice(0, 10);

  return new Response("\uFEFF" + toCsv(rows), {
    status: 200,
    headers: {
      "Content-Type": "text/csv; charset=utf-8",
      "Content-Disposition": `attachment; filename="problems-${stamp}.csv"`,
      "Cache-Control": "no-store",
    },
  });
}