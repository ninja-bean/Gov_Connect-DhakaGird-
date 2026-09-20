import type { Metadata } from "next";
import { Outfit } from "next/font/google";
import { use } from "react";
import LoginCard from "./login-card";

const outfit = Outfit({
  subsets: ["latin"],
  weight: ["300", "400", "500", "700"],
  variable: "--font-outfit",
});

export const metadata: Metadata = { title: "Login | DhakaGrid" };

export default function LoginPage({
  searchParams,
}: {
  searchParams: Promise<Record<string, string | string[] | undefined>>;
}) {
  const sp = use(searchParams);
  const flashMsg =
    sp.flash === "err" && typeof sp.msg === "string" ? sp.msg : undefined;

  return (
    <main className={`${outfit.variable} auth-page`}>
      <LoginCard initialFlash={flashMsg} />
    </main>
  );
}