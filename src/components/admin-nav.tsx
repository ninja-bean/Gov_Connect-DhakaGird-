import AppNav, { type NavLink } from "./app-nav";

export const adminLinks: NavLink[] = [
  { href: "/admin/dashboard", label: "Dashboard" },
  { href: "/profile/change-password", label: "Change Password" },
];

export default function AdminNav() {
  return <AppNav brandHref="/admin/dashboard" links={adminLinks} />;
}