import Link from "next/link";
import type { AnchorHTMLAttributes, ButtonHTMLAttributes, ReactNode } from "react";

type Variant = "primary" | "dark" | "secondary" | "danger" | "success" | "ghost";
type Size = "sm" | "md" | "lg";

const base =
  "inline-flex items-center justify-center gap-2 rounded-lg font-semibold transition " +
  "focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600 " +
  "disabled:pointer-events-none disabled:opacity-60";

const variants: Record<Variant, string> = {
  primary: "bg-blue-600 text-white hover:bg-blue-500 active:bg-blue-700",
  dark: "bg-ink text-white hover:bg-ink-soft active:bg-ink/80",
  secondary:
    "border border-slate-300 bg-white text-slate-700 hover:bg-slate-50 active:bg-slate-100",
  danger: "bg-red-600 text-white hover:bg-red-500 active:bg-red-700",
  success: "bg-emerald-600 text-white hover:bg-emerald-500 active:bg-emerald-700",
  ghost: "text-slate-600 hover:bg-slate-100 hover:text-slate-900",
};

const sizes: Record<Size, string> = {
  sm: "px-3 py-1.5 text-sm",
  md: "px-4 py-2.5 text-sm",
  lg: "px-6 py-3 text-base",
};

function classes(variant: Variant, size: Size, className?: string) {
  return [base, variants[variant], sizes[size], className].filter(Boolean).join(" ");
}

type CommonProps = {
  variant?: Variant;
  size?: Size;
  pending?: boolean;
  loadingLabel?: string;
  children: ReactNode;
  className?: string;
};

type ButtonAsButton = CommonProps &
  Omit<ButtonHTMLAttributes<HTMLButtonElement>, "className" | keyof CommonProps>;

type ButtonAsLink = CommonProps &
  Omit<AnchorHTMLAttributes<HTMLAnchorElement>, "className" | "href" | keyof CommonProps> & {
    href: string;
  };

export function Button({ variant = "primary", size = "md", className, ...props }: ButtonAsButton) {
  return <button className={classes(variant, size, className)} {...props} />;
}

export function ButtonLink({
  variant = "primary",
  size = "md",
  className,
  href,
  children,
  ...props
}: ButtonAsLink) {
  return (
    <Link href={href} className={classes(variant, size, className)} {...props}>
      {children}
    </Link>
  );
}

export function LoadingButton({
  variant = "primary",
  size = "md",
  pending = false,
  loadingLabel = "Loading…",
  children,
  className,
  disabled,
  ...props
}: CommonProps & Omit<ButtonHTMLAttributes<HTMLButtonElement>, "className" | keyof CommonProps>) {
  return (
    <button
      className={classes(variant, size, className)}
      disabled={disabled || pending}
      aria-busy={pending}
      {...props}
    >
      {pending ? loadingLabel : children}
    </button>
  );
}