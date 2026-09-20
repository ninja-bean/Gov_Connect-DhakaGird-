import { NextResponse, type NextRequest } from "next/server";
import { decrypt } from "@/lib/auth/session-core";
import { homePathFor } from "@/lib/auth/routes";

const isGuestOnly = (pathname: string) =>
  pathname === "/" ||
  pathname === "/login" ||
  pathname === "/register" ||
  pathname === "/forgot-password";

const needsRole = (pathname: string): "user" | "response" | "admin" | null => {
  if (pathname.startsWith("/response")) return "response";
  if (pathname.startsWith("/admin")) return "admin";
  if (pathname.startsWith("/dashboard")) return "user";
  return null;
};

/**
 * In-memory sliding-window rate limiter for credential submission.
 * Deliberately in-memory: single-tier deployments get a useful first line of
 * defence, and production deployments layer a CDN/edge limiter on top.
 */
const LOGIN_WINDOW_MS = 60_000;
const LOGIN_MAX_ATTEMPTS = 8;
const REGISTER_WINDOW_MS = 60_000;
const REGISTER_MAX_ATTEMPTS = 4;

const buckets: Map<string, { windowStart: number; hits: number }> = new Map();

function permit(key: string, windowMs: number, max: number): boolean {
  const now = Date.now();
  const bucket = buckets.get(key);
  if (!bucket || now - bucket.windowStart >= windowMs) {
    buckets.set(key, { windowStart: now, hits: 1 });
    return true;
  }
  if (bucket.hits >= max) {
    return false;
  }
  bucket.hits += 1;
  return true;
}

function clientIp(request: NextRequest): string {
  const forwarded = request.headers.get("x-forwarded-for");
  if (forwarded) return forwarded.split(",")[0].trim();
  return request.headers.get("x-real-ip") ?? "unknown";
}

function rateLimitedResponse(request: NextRequest): NextResponse {
  const url = new URL(request.nextUrl);
  url.pathname = "/login";
  url.searchParams.set("flash", "err");
  url.searchParams.set(
    "msg",
    "Too many attempts. Please wait a minute before trying again.",
  );
  const response = NextResponse.redirect(url);
  response.headers.set("Retry-After", String(LOGIN_WINDOW_MS / 1000));
  return response;
}

export async function proxy(request: NextRequest) {
  const { pathname } = request.nextUrl;

  if (request.method === "POST") {
    if (pathname === "/login") {
      if (!permit(clientIp(request), LOGIN_WINDOW_MS, LOGIN_MAX_ATTEMPTS)) {
        return rateLimitedResponse(request);
      }
    }
    if (pathname === "/register") {
      if (!permit(clientIp(request), REGISTER_WINDOW_MS, REGISTER_MAX_ATTEMPTS)) {
        return rateLimitedResponse(request);
      }
    }
  }

  const session = await decrypt(request.cookies.get("session")?.value);
  const role = session?.role;

  if (isGuestOnly(pathname)) {
    if (role) {
      return NextResponse.redirect(new URL(homePathFor(role), request.nextUrl));
    }
    return NextResponse.next();
  }

  const required = needsRole(pathname);
  if (required) {
    if (!role) {
      return NextResponse.redirect(new URL("/login", request.nextUrl));
    }
    if (role !== required) {
      return NextResponse.redirect(new URL(homePathFor(role), request.nextUrl));
    }
  }

  return NextResponse.next();
}

export const config = {
  matcher: ["/((?!api|_next/static|_next/image|favicon\\.ico|.*\\..*).*)"],
};