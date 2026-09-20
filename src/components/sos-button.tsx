"use client";

import { useState } from "react";
import { sosAlert, type SosResult } from "@/actions/problems";

type Step = "idle" | "confirm" | "locating" | "done" | "error";

export default function SosButton() {
  const [open, setOpen] = useState(false);
  const [step, setStep] = useState<Step>("idle");
  const [error, setError] = useState("");

  function reset() {
    setOpen(false);
    setStep("idle");
    setError("");
  }

  function confirm() {
    setStep("locating");
    if (!navigator.geolocation) {
      setError("This browser does not support GPS.");
      setStep("error");
      return;
    }
    navigator.geolocation.getCurrentPosition(
      async (pos) => {
        const result: SosResult = await sosAlert(pos.coords.latitude, pos.coords.longitude);
        if (result.ok) {
          setStep("done");
        } else {
          setError(result.message);
          setStep("error");
        }
      },
      () => {
        setError("GPS access denied. Enable location services and try again.");
        setStep("error");
      },
      { enableHighAccuracy: true, timeout: 10000 },
    );
  }

  return (
    <>
      <button
        type="button"
        onClick={() => {
          setStep("idle");
          setOpen(true);
        }}
        className="flex items-center justify-center gap-2 rounded-xl bg-red-600 px-6 py-4 text-base font-bold text-white shadow-[0_0_15px_rgba(239,68,68,0.4)] transition hover:bg-red-500"
      >
        <span className="text-xl">🚨</span> SEND SOS
      </button>

      {open && (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/70 p-4 backdrop-blur-sm">
          <div className="w-full max-w-sm rounded-2xl bg-white p-8 text-center">
            {step === "confirm" && (
              <>
                <div className="mb-4 text-5xl">🚨</div>
                <h2 className="text-xl font-bold text-slate-900">Emergency SOS</h2>
                <p className="mt-2 text-sm text-slate-500">
                  This will broadcast your <strong>exact GPS location</strong>.
                </p>
                <div className="mt-6 flex gap-3">
                  <button
                    onClick={reset}
                    className="flex-1 rounded-lg border border-slate-300 px-4 py-3 text-sm font-semibold text-slate-700"
                  >
                    Cancel
                  </button>
                  <button
                    onClick={confirm}
                    className="flex-1 rounded-lg bg-red-600 px-4 py-3 text-sm font-bold text-white transition hover:bg-red-500"
                  >
                    Confirm
                  </button>
                </div>
              </>
            )}
            {step === "locating" && (
              <>
                <div className="mb-4 text-5xl">📡</div>
                <p className="text-sm font-medium text-slate-600 animate-pulse">Acquiring GPS satellites…</p>
              </>
            )}
            {step === "done" && (
              <>
                <div className="mb-4 text-5xl">✅</div>
                <h3 className="text-lg font-bold text-slate-900">Location sent!</h3>
                <button
                  onClick={reset}
                  className="mt-6 rounded-lg bg-slate-900 px-8 py-3 text-sm font-semibold text-white"
                >
                  OK
                </button>
              </>
            )}
            {step === "error" && (
              <>
                <div className="mb-4 text-5xl">⚠️</div>
                <p className="text-sm text-red-600">{error}</p>
                <button
                  onClick={reset}
                  className="mt-6 rounded-lg border border-slate-300 px-6 py-2.5 text-sm font-semibold text-slate-700"
                >
                  Close
                </button>
              </>
            )}
          </div>
        </div>
      )}
    </>
  );
}