import type { ReactNode } from "react";

export type AlertTone = "success" | "info" | "warning" | "error";

const tones: Record<AlertTone, string> = {
  success: "border-emerald-200 bg-emerald-50 text-emerald-800",
  info: "border-blue-200 bg-blue-50 text-blue-800",
  warning: "border-amber-200 bg-amber-50 text-amber-800",
  error: "border-red-200 bg-red-50 text-red-800",
};

const icons: Record<AlertTone, string> = {
  success: "✓",
  info: "ℹ",
  warning: "!",
  error: "✕",
};

export function Alert({
  tone = "info",
  className = "",
  children,
}: {
  tone?: AlertTone;
  className?: string;
  children: ReactNode;
}) {
  return (
    <div
      role={tone === "error" ? "alert" : "status"}
      aria-live="polite"
      className={`flex items-start gap-2.5 rounded-xl border px-4 py-3 text-sm font-medium ${tones[tone]} ${className}`}
    >
      <span aria-hidden className="mt-px font-bold">
        {icons[tone]}
      </span>
      <span className="min-w-0 flex-1">{children}</span>
    </div>
  );
}