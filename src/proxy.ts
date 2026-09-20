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

export async function proxy(request: NextRequest) {
  const { pathname } = request.nextUrl;
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