import type { Metadata } from "next";
import { Outfit } from "next/font/google";
import ForgotPasswordCard from "./forgot-password-card";

const outfit = Outfit({
  subsets: ["latin"],
  weight: ["300", "400", "500", "700"],
  variable: "--font-outfit",
});

export const metadata: Metadata = { title: "Forgot Password | DhakaGrid" };

export default function ForgotPasswordPage() {
  return (
    <main className={`${outfit.variable} auth-page`}>
      <ForgotPasswordCard />
    </main>
  );
}