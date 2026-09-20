"use client";

import { useActionState } from "react";
import Link from "next/link";
import { forgotPassword, type ActionState } from "@/actions/auth";
import { Fa } from "@/components/auth/fa";
import { SketchBackground } from "@/components/auth/sketch-background";

export default function ForgotPasswordCard() {
  const [state, formAction, pending] = useActionState<ActionState, FormData>(
    forgotPassword,
    null,
  );

  const message = state?.message;
  const isError =
    message == null ||
    !message.startsWith("Password reset link sent");

  return (
    <div className="auth-shell flex items-center justify-center px-4 py-10">
      <SketchBackground theme="day" />

      <div className="auth-card w-[90%] max-w-[400px]">
        <div className="mb-6 flex flex-col items-center border-b-2 border-slate-200 pb-5 text-center">
          <div className="auth-logo">
            <Fa name="lock" style={{ fontSize: "1.9rem" }} />
          </div>
          <h1 className="auth-title">Forgot Password</h1>
          <p className="auth-subtitle">
            Enter your account email to receive a reset link
          </p>
        </div>

        {message ? (
          <div className={`auth-msg ${isError ? "error" : "success"} mb-4`}>
            <Fa name={isError ? "circleExclamation" : "circleCheck"} />
            <span>{message}</span>
          </div>
        ) : null}

        <form action={formAction} className="space-y-[13px]">
          <div>
            <label className="auth-label">
              <Fa name="envelope" /> Email Address
            </label>
            <input
              type="email"
              name="email"
              className="auth-input"
              placeholder="you@example.com"
              autoComplete="email"
              required
            />
          </div>
          <div className="auth-hint -mt-2">
            We&apos;ll send a reset link to this address.
          </div>

          <button type="submit" disabled={pending} className="auth-submit">
            <Fa name="paperPlane" />
            {pending ? "Sending…" : "Send Reset Link"}
          </button>
        </form>

        <div className="auth-footer">
          <Link href="/login" className="auth-link">
            <Fa name="arrowLeft" /> Back to Sign in
          </Link>
        </div>
      </div>
    </div>
  );
}