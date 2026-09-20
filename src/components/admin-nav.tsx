import AppNav, { type NavLink } from "./app-nav";

export const adminLinks: NavLink[] = [
  { href: "/admin/dashboard", label: "Overview" },
  { href: "/admin/problems", label: "Problems" },
  { href: "/admin/teams", label: "Teams" },
  { href: "/admin/appeals", label: "Unban Appeals" },
  { href: "/admin/users", label: "Users" },
  { href: "/profile/change-password", label: "Change Password" },
];

export default function AdminNav() {
  return <AppNav brandHref="/admin/dashboard" links={adminLinks} />;
}