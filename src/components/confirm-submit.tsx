"use client";

import { useEffect, useRef, useState } from "react";
import type { ButtonHTMLAttributes, MouseEvent } from "react";

const ARMED_TIMEOUT_MS = 4000;

const toneClasses: Record<"danger" | "secondary", string> = {
  danger: "bg-red-600 text-white hover:bg-red-500 active:bg-red-700",
  secondary: "bg-ink text-white hover:bg-ink-soft active:bg-ink/80",
};

type ConfirmSubmitProps = {
  label: string;
  confirmLabel?: string;
  tone?: "danger" | "secondary";
  className?: string;
} & Omit<ButtonHTMLAttributes<HTMLButtonElement>, "children" | "className">;

export default function ConfirmSubmit({
  label,
  confirmLabel = "Click again to confirm",
  tone = "secondary",
  className = "",
  ...rest
}: ConfirmSubmitProps) {
  const [armed, setArmed] = useState(false);
  const timerRef = useRef<number | null>(null);

  useEffect(() => {
    return () => {
      if (timerRef.current) window.clearTimeout(timerRef.current);
    };
  }, []);

  function handleClick(e: MouseEvent<HTMLButtonElement>) {
    if (!armed) {
      e.preventDefault();
      setArmed(true);
      timerRef.current = window.setTimeout(() => setArmed(false), ARMED_TIMEOUT_MS);
    } else if (timerRef.current) {
      window.clearTimeout(timerRef.current);
    }
  }

  return (
    <button
      type="submit"
      onClick={handleClick}
      aria-label={armed ? confirmLabel : label}
      title={armed ? confirmLabel : label}
      className={`rounded-lg px-4 py-2 text-sm font-semibold transition focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-red-600 ${toneClasses[tone]} ${className}`}
      {...rest}
    >
      {armed ? `${confirmLabel} ⚠️` : label}
    </button>
  );
}