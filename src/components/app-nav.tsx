"use client";

import Link from "next/link";
import { usePathname } from "next/navigation";
import LogoutButton from "./logout-button";
import ThemeToggle from "./theme-toggle";

export type NavLink = { href: string; label: string; exact?: boolean };

export default function AppNav({
  brandHref,
  links,
  logoutLabel = "Log out",
}: {
  brandHref: string;
  links: NavLink[];
  logoutLabel?: string;
}) {
  const pathname = usePathname();

  return (
    <header className="sticky top-0 z-40 border-b border-slate-200 bg-white/90 backdrop-blur">
      <nav
        aria-label="Primary"
        className="mx-auto flex max-w-6xl flex-wrap items-center justify-between gap-3 px-4 py-4 sm:px-6 lg:px-8"
      >
        <Link href={brandHref} className="text-lg font-extrabold text-slate-900">
          GovConnect <span className="text-blue-600">DhakaGrid</span>
        </Link>
        <div className="flex flex-wrap items-center gap-1 text-sm font-semibold text-slate-600">
          {links.map((l) => {
            const active = l.exact ? pathname === l.href : pathname.startsWith(l.href);
            return (
              <Link
                key={l.href}
                href={l.href}
                aria-current={active ? "page" : undefined}
                className={`rounded-lg px-3 py-2 transition focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600 ${
                  active
                    ? "bg-blue-50 text-blue-700"
                    : "hover:bg-slate-100 hover:text-slate-900"
                }`}
              >
                {l.label}
              </Link>
            );
          })}
          <ThemeToggle />
          <LogoutButton label={logoutLabel} />
        </div>
      </nav>
    </header>
  );
}