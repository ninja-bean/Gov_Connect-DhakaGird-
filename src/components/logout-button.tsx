"use client";

import { useTransition } from "react";
import { logout } from "@/actions/auth";
import { Button } from "@/components/ui/button";

export default function LogoutButton({ label = "Log out" }: { label?: string }) {
  const [pending, startTransition] = useTransition();

  return (
    <Button
      type="button"
      variant="secondary"
      size="sm"
      disabled={pending}
      onClick={() => startTransition(() => logout())}
    >
      {pending ? "Logging out…" : label}
    </Button>
  );
}