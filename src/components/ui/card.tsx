import type { HTMLAttributes, ReactNode } from "react";

export function Card({ className, ...props }: HTMLAttributes<HTMLDivElement>) {
  return (
    <div
      className={`rounded-2xl border border-slate-200 bg-white ${className ?? ""}`}
      {...props}
    />
  );
}

export function CardHeader({
  title,
  icon,
  action,
  className,
}: {
  title: ReactNode;
  icon?: ReactNode;
  action?: ReactNode;
  className?: string;
}) {
  return (
    <div
      className={`flex flex-wrap items-center justify-between gap-3 border-b border-slate-200 px-6 py-5 ${className ?? ""}`}
    >
      <h2 className="flex items-center gap-2 text-base font-bold text-slate-900">
        {icon}
        {title}
      </h2>
      {action}
    </div>
  );
}

export function CardBody({ className, ...props }: HTMLAttributes<HTMLDivElement>) {
  return <div className={`p-6 ${className ?? ""}`} {...props} />;
}