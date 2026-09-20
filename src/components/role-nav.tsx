import CitizenNav from "./citizen-nav";
import ResponseNav from "./response-nav";
import AdminNav from "./admin-nav";

export default function RoleNav({ role }: { role: "user" | "response" | "admin" }) {
  if (role === "response") return <ResponseNav />;
  if (role === "admin") return <AdminNav />;
  return <CitizenNav />;
}