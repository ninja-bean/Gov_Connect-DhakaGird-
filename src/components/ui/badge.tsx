import type { ReactNode } from "react";

export type Tone = "slate" | "ink" | "red";

const tones: Record<Tone, string> = {
  slate: "bg-slate-100 text-slate-700 ring-slate-300",
  ink: "bg-ink text-white ring-ink",
  red: "bg-red-50 text-red-700 ring-red-200",
};

export function Badge({
  tone = "slate",
  className = "",
  children,
}: {
  tone?: Tone;
  className?: string;
  children: ReactNode;
}) {
  return (
    <span
      className={`inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-semibold ring-1 ring-inset ${tones[tone]} ${className}`}
    >
      {children}
    </span>
  );
}