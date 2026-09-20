import Link from "next/link";
import LogoutButton from "@/components/logout-button";

const links = [
  { href: "/dashboard", label: "Dashboard" },
  { href: "/report", label: "Submit Report" },
  { href: "/my-problems", label: "My Reports" },
  { href: "/profile", label: "Profile" },
  { href: "/profile/change-password", label: "Change Password" },
  { href: "/appeal", label: "Appeal" },
];

export default function CitizenNav() {
  return (
    <header className="sticky top-0 z-40 border-b border-slate-200 bg-white">
      <div className="mx-auto flex max-w-6xl flex-wrap items-center justify-between gap-3 px-6 py-4">
        <Link href="/dashboard" className="text-lg font-extrabold text-slate-900">
          GovConnect <span className="text-blue-600">DhakaGrid</span>
        </Link>
        <nav className="flex flex-wrap items-center gap-1 text-sm font-semibold text-slate-600">
          {links.map((l) => (
            <Link
              key={l.href}
              href={l.href}
              className="rounded-lg px-3 py-2 transition hover:bg-slate-100 hover:text-slate-900"
            >
              {l.label}
            </Link>
          ))}
          <LogoutButton label="Logout" />
        </nav>
      </div>
    </header>
  );
}