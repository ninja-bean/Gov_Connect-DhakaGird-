import AppNav, { type NavLink } from "./app-nav";

export const responseLinks: NavLink[] = [
  { href: "/response/dashboard", label: "Dashboard" },
  { href: "/response/profile", label: "Team Profile" },
  { href: "/profile/change-password", label: "Change Password" },
];

export default function ResponseNav() {
  return <AppNav brandHref="/response/dashboard" links={responseLinks} />;
}