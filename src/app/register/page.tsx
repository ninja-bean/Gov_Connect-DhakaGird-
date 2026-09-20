import type { Metadata } from "next";
import { Outfit } from "next/font/google";
import RegisterCard from "./register-card";

const outfit = Outfit({
  subsets: ["latin"],
  weight: ["300", "400", "500", "600", "700", "800"],
  variable: "--font-outfit",
});

export const metadata: Metadata = { title: "Register | DhakaGrid" };

export default function RegisterPage() {
  return (
    <main className={`${outfit.variable} auth-page`}>
      <RegisterCard />
    </main>
  );
}