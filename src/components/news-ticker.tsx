"use client";

import { useEffect, useState } from "react";

const FALLBACK_ITEMS = [
  "⚡ Grid system operating normally.",
  "📢 Report any issues immediately.",
  "🌧️ Check weather updates before travel.",
];

export default function NewsTicker({ items }: { items: string[] }) {
  const [index, setIndex] = useState(0);
  const [fading, setFading] = useState(false);

  const messages = items.length > 0 ? items : FALLBACK_ITEMS;

  useEffect(() => {
    if (messages.length < 2) return;
    const id = setInterval(() => {
      setFading(true);
      setTimeout(() => {
        setIndex((i) => (i + 1) % messages.length);
        setFading(false);
      }, 600);
    }, 4500);
    return () => clearInterval(id);
  }, [messages.length]);

  return (
    <div className="flex items-center gap-4 overflow-hidden rounded-2xl bg-slate-900 px-5 py-4 text-white sm:px-6">
      <span className="shrink-0 rounded-full bg-red-600 px-3 py-1 text-xs font-extrabold tracking-widest text-white">
        LIVE FEED
      </span>
      <div className="relative h-7 flex-1 overflow-hidden" aria-live="polite">
        <p
          key={index}
          className={`absolute inset-0 flex items-center text-sm font-medium text-slate-100 transition-all duration-500 sm:text-base ${
            fading ? "translate-y-6 opacity-0" : "translate-y-0 opacity-100"
          }`}
        >
          {messages[index]}
        </p>
      </div>
    </div>
  );
}