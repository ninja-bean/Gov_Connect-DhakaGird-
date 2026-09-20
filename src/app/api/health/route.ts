import { NextResponse } from "next/server";
import { db } from "@/lib/db";
import pkg from "../../../../package.json";

export const dynamic = "force-dynamic";
export const runtime = "nodejs";

export async function GET() {
  let dbOk = false;
  try {
    await db.$queryRawUnsafe("SELECT 1");
    dbOk = true;
  } catch {
    dbOk = false;
  }

  return NextResponse.json(
    {
      ok: dbOk,
      service: "GovConnect DhakaGrid",
      db: dbOk ? "up" : "down",
      uptimeSeconds: Math.floor(process.uptime()),
      version: pkg.version,
      timestamp: new Date().toISOString(),
    },
    dbOk ? { status: 200 } : { status: 503 },
  );
}