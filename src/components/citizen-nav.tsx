import AppNav, { type NavLink } from "./app-nav";

const links: NavLink[] = [
  { href: "/dashboard", label: "Dashboard" },
  { href: "/report", label: "Submit Report" },
  { href: "/my-problems", label: "My Reports" },
  { href: "/profile", label: "Profile" },
  { href: "/profile/change-password", label: "Change Password" },
  { href: "/appeal", label: "Appeal" },
];

export default function CitizenNav() {
  return <AppNav brandHref="/dashboard" links={links} />;
}