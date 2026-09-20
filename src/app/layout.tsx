import type { Metadata } from "next";
import { Poppins } from "next/font/google";
import { ThemeProvider } from "@/components/theme-provider";
import "./globals.css";

const THEME_STORAGE_KEY = "govconnect-theme";

const noFlashScript = `try{var t=localStorage.getItem("${THEME_STORAGE_KEY}");if(t==="dark"||(!t&&window.matchMedia("(prefers-color-scheme: dark)").matches)){document.documentElement.classList.add("dark")}}catch(e){}`;

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
    <html lang="en" className={poppins.variable} suppressHydrationWarning>
      <body className="antialiased" style={{ fontFamily: "var(--font-poppins), sans-serif" }}>
        <script dangerouslySetInnerHTML={{ __html: noFlashScript }} />
        <ThemeProvider>{children}</ThemeProvider>
      </body>
    </html>
  );
}