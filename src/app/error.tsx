"use client";

import { useEffect } from "react";

export default function Error({
  error,
  reset,
}: {
  error: Error & { digest?: string };
  reset: () => void;
}) {
  useEffect(() => {
    console.error(error);
  }, [error]);

  return (
    <div className="mx-auto w-full max-w-6xl px-4 py-16 text-center sm:px-6 lg:px-8">
      <div className="mx-auto flex size-14 items-center justify-center rounded-full bg-red-100 text-2xl text-red-500">
        ✕
      </div>
      <h1 className="mt-4 text-xl font-bold text-slate-900">Something went wrong</h1>
      <p className="mx-auto mt-2 max-w-md text-sm text-slate-500">
        An unexpected error occurred while loading this page. Try again, and contact support if it
        keeps happening.
      </p>
      <button
        onClick={reset}
        className="mt-6 inline-flex items-center justify-center rounded-lg bg-red-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-red-500 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-red-600"
      >
        Try again
      </button>
    </div>
  );
}