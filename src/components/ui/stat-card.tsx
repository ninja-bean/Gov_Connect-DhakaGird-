import type { ReactNode } from "react";

const accent = {
  blue: "text-blue-600",
  green: "text-emerald-600",
  red: "text-red-600",
  amber: "text-amber-600",
  slate: "text-slate-600",
} as const;

export function StatCard({
  icon,
  label,
  value,
  tone = "slate",
}: {
  icon: ReactNode;
  label: string;
  value: ReactNode;
  tone?: keyof typeof accent;
}) {
  return (
    <div className="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
      <div className="flex items-center justify-between gap-3">
        <p className="text-sm font-medium text-slate-500">{label}</p>
        <span className={`text-lg ${accent[tone]}`} aria-hidden>
          {icon}
        </span>
      </div>
      <p className="mt-2 text-2xl font-bold text-slate-900">{value}</p>
    </div>
  );
}