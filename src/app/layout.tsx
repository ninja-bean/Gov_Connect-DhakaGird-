import type { Metadata } from "next";
import { Poppins } from "next/font/google";
import "./globals.css";

const poppins = Poppins({
  subsets: ["latin"],
  weight: ["400", "500", "600", "700"],
  variable: "--font-poppins",
});

export const metadata: Metadata = {
  title: "GovConnect | DhakaGrid",
  description:
    "Citizen-centric platform bridging people and government — report issues, request help, and track responses.",
};

export default function RootLayout({
  children,
}: Readonly<{ children: React.ReactNode }>) {
  return (
    <html lang="en" className={poppins.variable}>
      <body className="antialiased" style={{ fontFamily: "var(--font-poppins), sans-serif" }}>
        {children}
      </body>
    </html>
  );
}