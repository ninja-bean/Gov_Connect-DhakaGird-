"use client";

import { useActionState, useState } from "react";
import Link from "next/link";
import { login, type ActionState } from "@/actions/auth";
import { Fa, type FaIconName } from "@/components/auth/fa";
import {
  SketchBackground,
  type SkyTheme,
} from "@/components/auth/sketch-background";

const TABS: Array<{
  value: "user" | "response" | "admin";
  label: string;
  icon: FaIconName;
  theme: SkyTheme;
}> = [
  { value: "user", label: "Citizen", icon: "user", theme: "day" },
  { value: "response", label: "Response", icon: "truckMedical", theme: "sunset" },
  { value: "admin", label: "Admin", icon: "shieldHalved", theme: "night" },
];

const FORM_META: Record<
  "user" | "response" | "admin",
  {
    idLabel: string;
    idIcon: FaIconName;
    idPlaceholder: string;
    idType: string;
    secretLabel: string;
    secretIcon: FaIconName;
    secretPlaceholder: string;
    submitLabel: string;
    submitIcon: FaIconName;
    tone?: "red" | "blue";
  }
> = {
  user: {
    idLabel: "Email Address",
    idIcon: "envelope",
    idPlaceholder: "Enter your email",
    idType: "email",
    secretLabel: "Password",
    secretIcon: "lock",
    secretPlaceholder: "Enter your password",
    submitLabel: "Sign In",
    submitIcon: "rightToBracket",
  },
  response: {
    idLabel: "Unit Email Address",
    idIcon: "idBadge",
    idPlaceholder: "Enter unit identifier",
    idType: "text",
    secretLabel: "Security Key",
    secretIcon: "key",
    secretPlaceholder: "Enter security key",
    submitLabel: "Deploy Unit",
    submitIcon: "truckMedical",
    tone: "red",
  },
  admin: {
    idLabel: "Admin ID",
    idIcon: "userShield",
    idPlaceholder: "Enter admin code",
    idType: "text",
    secretLabel: "System Password",
    secretIcon: "shieldHalved",
    secretPlaceholder: "Enter system password",
    submitLabel: "Access Terminal",
    submitIcon: "terminal",
    tone: "blue",
  },
};

export default function LoginCard({ initialFlash }: { initialFlash?: string }) {
  const [state, formAction, pending] = useActionState<ActionState, FormData>(
    login,
    initialFlash ? { message: initialFlash } : null,
  );
  const [tab, setTab] = useState<(typeof TABS)[number]["value"]>("user");

  const meta = FORM_META[tab];
  const theme = TABS.find((t) => t.value === tab)!.theme;
  const message = state?.message;

  return (
    <div className="auth-shell flex items-center justify-center px-4 py-10">
      <SketchBackground theme={theme} />

      <div className="auth-card w-[90%] max-w-[400px]">
        <div className="mb-6 flex flex-col items-center border-b-2 border-slate-200 pb-5 text-center">
          <div className="auth-logo">
            <Fa name="networkWired" style={{ fontSize: "1.9rem" }} />
          </div>
          <h1 className="auth-title">DhakaGrid</h1>
          <p className="auth-subtitle">City Management Portal</p>
        </div>

        <div className="auth-tabs" role="tablist" aria-label="Account type">
          {TABS.map((t) => (
            <button
              key={t.value}
              type="button"
              role="tab"
              aria-selected={tab === t.value}
              className={`auth-tab ${tab === t.value ? "active" : ""}`}
              onClick={() => setTab(t.value)}
            >
              <Fa name={t.icon} />
              <span>{t.label}</span>
            </button>
          ))}
        </div>

        <form
          action={formAction}
          key={tab}
          className="auth-fade space-y-[13px]"
        >
          <input type="hidden" name="role" value={tab} />

          {message ? (
            <div className="auth-error">
              <Fa name="triangleExclamation" />
              <span>{message}</span>
            </div>
          ) : null}

          <div>
            <label className="auth-label" htmlFor="auth-id">
              <Fa name={meta.idIcon} />
              {meta.idLabel}
            </label>
            <input
              id="auth-id"
              name="email"
              type={meta.idType}
              className="auth-input"
              placeholder={meta.idPlaceholder}
              autoComplete="email"
              required
            />
          </div>

          <div>
            <label className="auth-label" htmlFor="auth-secret">
              <Fa name={meta.secretIcon} />
              {meta.secretLabel}
            </label>
            <input
              id="auth-secret"
              name="password"
              type="password"
              className="auth-input"
              placeholder={meta.secretPlaceholder}
              autoComplete="current-password"
              required
            />
          </div>

          <button
            type="submit"
            disabled={pending}
            className={`auth-submit ${meta.tone ?? ""}`}
          >
            <Fa name={meta.submitIcon} />
            {pending ? "Signing in…" : meta.submitLabel}
          </button>
        </form>

        <div className="auth-footer">
          <Link href="/register" className="auth-link">
            <Fa name="userPlus" />
            Create New Account
          </Link>
        </div>
      </div>
    </div>
  );
}