import type { InputHTMLAttributes, SelectHTMLAttributes, TextareaHTMLAttributes } from "react";

export const controlClass =
  "w-full rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm text-slate-900 " +
  "placeholder:text-slate-400 transition " +
  "focus:border-red-500 focus:outline-none focus:ring-2 focus:ring-red-500/20 " +
  "disabled:cursor-not-allowed disabled:bg-slate-50 disabled:text-slate-500";

export function Input({ className = "", ...props }: InputHTMLAttributes<HTMLInputElement>) {
  return <input className={`${controlClass} ${className}`} {...props} />;
}

export function Textarea({ className = "", ...props }: TextareaHTMLAttributes<HTMLTextAreaElement>) {
  return <textarea className={`${controlClass} resize-y leading-relaxed ${className}`} {...props} />;
}

export function Select({ className = "", ...props }: SelectHTMLAttributes<HTMLSelectElement>) {
  return (
    <select
      className={`${controlClass} cursor-pointer appearance-none bg-[url('data:image/svg+xml;charset=utf-8,%3Csvg%20xmlns=%22http://www.w3.org/2000/svg%22%20width=%2212%22%20height=%2212%22%20viewBox=%220%200%2012%2012%22%3E%3Cpath%20fill=%22%2364748B%22%20d=%22M6%209L1%204h10z%22/%3E%3C/svg%3E')] bg-[position:right_1rem_center] bg-no-repeat pr-10 ${className}`}
      {...props}
    />
  );
}