"use client";

import { useRef, useState } from "react";
import { useActionState } from "react";
import Link from "next/link";
import { register, type ActionState } from "@/actions/auth";
import { Fa } from "@/components/auth/fa";
import {
  SketchBackground,
  type SkyTheme,
} from "@/components/auth/sketch-background";

type Role = "user" | "response";

const TOTAL_STEPS: Record<Role, number> = { user: 2, response: 4 };

const ROLE_SPAN: Record<Role, string> = {
  user: "Join DhakaGrid to report and track city issues",
  response: "Set up your team account for admin approval",
};

function Field({
  label,
  name,
  error,
  children,
}: {
  label: string;
  name: string;
  error?: string;
  children: React.ReactNode;
}) {
  return (
    <div className="mb-[18px]">
      <label className="auth-label" id={`f-${name}`}>
        {label}
      </label>
      {children}
      {error ? <p className="text-[0.72rem] font-medium text-red-600">{error}</p> : null}
    </div>
  );
}

export default function RegisterCard() {
  const [state, formAction, pending] = useActionState<ActionState, FormData>(
    register,
    null,
  );
  const [role, setRole] = useState<Role>("user");
  const [step, setStep] = useState(1);
  const [showPassword, setShowPassword] = useState(false);
  const formRef = useRef<HTMLFormElement>(null);

  const theme: SkyTheme = role === "user" ? "day" : "sunset";
  const errors = state?.errors;

  function validateSection() {
    const form = formRef.current;
    if (!form) return true;
    const section = form.querySelector<HTMLElement>(`[data-step="${step}"]`);
    if (!section) return true;
    const controls = Array.from(
      section.querySelectorAll<HTMLInputElement | HTMLSelectElement>(
        "input, select",
      ),
    );
    for (const el of controls) {
      if (!el.checkValidity()) {
        el.reportValidity();
        return false;
      }
    }
    return true;
  }

  function nextStep() {
    if (validateSection() && step < TOTAL_STEPS[role]) {
      setStep(step + 1);
    }
  }

  function prevStep() {
    if (step > 1) setStep(step - 1);
  }

  function switchRole(next: Role) {
    setRole(next);
    setStep(1);
  }

  return (
    <div className="auth-shell flex items-center justify-center px-4 py-10">
      <SketchBackground theme={theme} />

      <div className="relative z-10 w-full max-w-[640px]">
        <div className="auth-steps">
          {Array.from({ length: TOTAL_STEPS[role] }, (_, i) => (
            <span
              key={i}
              className={`auth-dot ${i + 1 === step ? "active" : ""}`}
            />
          ))}
        </div>

        <div className="auth-card">
          <div className="mb-8 mt-2 text-center">
            <div className="auth-step-head mt-5 justify-center">
              {role === "user" ? "Create Your Account" : "Register Response Team"}
            </div>
            <p className="auth-subtitle">{ROLE_SPAN[role]}</p>
          </div>

          <div className="flex justify-center">
            <div className="auth-role-toggle" role="tablist" aria-label="Registration type">
              <button
                type="button"
                className={`auth-role-btn ${role === "user" ? "active" : ""}`}
                onClick={() => switchRole("user")}
              >
                <Fa name="user" />
                <span>Citizen</span>
              </button>
              <button
                type="button"
                className={`auth-role-btn ${role === "response" ? "active" : ""}`}
                onClick={() => switchRole("response")}
              >
                <Fa name="truckFast" />
                <span>Response Team</span>
              </button>
            </div>
          </div>

          {role === "response" ? (
            <div className="auth-info">
              <Fa name="circleInfo" />
              <div className="auth-info-text">
                Response Team accounts require admin approval. Your account
                status will show as <strong>Pending</strong> until it&apos;s
                reviewed.
              </div>
            </div>
          ) : null}

          {state?.message ? (
            <div className="auth-msg success" role="status">
              <Fa name="circleCheck" />
              <span>{state.message}</span>
            </div>
          ) : null}
          {errors?._form?.[0] ? (
            <div className="auth-msg error" role="alert">
              <Fa name="circleExclamation" />
              <span>{errors._form[0]}</span>
            </div>
          ) : null}

          <form
            ref={formRef}
            action={formAction}
            className="text-left"
            onSubmit={(e) => {
              if (step < TOTAL_STEPS[role]) {
                e.preventDefault();
                nextStep();
              }
            }}
          >
            <input type="hidden" name="role" value={role} />

            {role === "user" ? (
              <>
                <section data-step="1" hidden={step !== 1} className="auth-fade-step">
                  <div className="auth-step-head">
                    <Fa name="idCard" /> Personal Information
                  </div>

                  <Field label="Full Name" name="name" error={errors?.name?.[0]}>
                    <input type="text" name="name" className="auth-input" placeholder="Enter your full name" required />
                  </Field>

                  <div className="grid gap-x-4 sm:grid-cols-2">
                    <Field label="Email Address" name="email" error={errors?.email?.[0]}>
                      <input type="email" name="email" className="auth-input" placeholder="you@example.com" required />
                    </Field>
                    <Field label="Phone Number" name="phone">
                      <input type="text" name="phone" className="auth-input" placeholder="01XXXXXXXXX" />
                    </Field>
                  </div>

                  <div className="grid gap-x-4 sm:grid-cols-2">
                    <Field label="National ID (NID)" name="nid">
                      <input type="text" name="nid" className="auth-input" placeholder="NID number" />
                    </Field>
                    <Field label="Date of Birth" name="dob">
                      <input type="date" name="dob" className="auth-input" />
                    </Field>
                  </div>

                  <div className="auth-nav">
                    <button type="button" className="auth-btn auth-btn-primary" onClick={nextStep}>
                      Continue <Fa name="arrowRight" />
                    </button>
                  </div>
                </section>

                <section data-step="2" hidden={step !== 2} className="auth-fade-step">
                  <div className="auth-step-head">
                    <Fa name="lock" /> Address &amp; Security
                  </div>

                  <Field label="Permanent Address" name="location">
                    <input type="text" name="location" className="auth-input" placeholder="House, Road, Area, City" required />
                  </Field>

                  <Field label="Password" name="password" error={errors?.password?.[0]}>
                    <div className="auth-pw">
                      <input
                        id="userPassword"
                        type={showPassword ? "text" : "password"}
                        name="password"
                        className="auth-input"
                        placeholder="Create a password"
                        required
                      />
                      <button type="button" className="auth-pw-toggle" onClick={() => setShowPassword(!showPassword)} aria-label="Toggle password visibility">
                        <Fa name={showPassword ? "eyeSlash" : "eye"} />
                      </button>
                    </div>
                    <div className="auth-hint">
                      Use at least 8 characters with letters and numbers
                    </div>
                  </Field>

                  <Field label="Confirm Password" name="confirmPassword" error={errors?.confirmPassword?.[0]}>
                    <input type="password" name="confirmPassword" className="auth-input" placeholder="Re-enter your password" required />
                  </Field>

                  <div className="auth-nav">
                    <button type="button" className="auth-btn auth-btn-secondary" onClick={prevStep}>
                      <Fa name="arrowLeft" /> Back
                    </button>
                    <button type="submit" disabled={pending} className="auth-btn auth-btn-primary">
                      <Fa name="check" /> {pending ? "Creating…" : "Create Account"}
                    </button>
                  </div>
                </section>
              </>
            ) : (
              <>
                <section data-step="1" hidden={step !== 1} className="auth-fade-step">
                  <div className="auth-step-head">
                    <Fa name="buildingShield" /> Team Information
                  </div>

                  <Field label="Team Name" name="name" error={errors?.name?.[0]}>
                    <input type="text" name="name" className="auth-input" placeholder="e.g. Gulshan Fire Unit 3" required />
                  </Field>

                  <div className="grid gap-x-4 sm:grid-cols-2">
                    <Field label="Category" name="category">
                      <select name="category" className="auth-select" required defaultValue="">
                        <option value="" disabled>
                          Select category
                        </option>
                        <option value="police">Police</option>
                        <option value="medical">Medical</option>
                        <option value="fire">Fire</option>
                        <option value="gov">Government</option>
                      </select>
                    </Field>
                    <Field label="Station Code" name="identification">
                      <input type="text" name="identification" className="auth-input" placeholder="Station identifier" required />
                    </Field>
                  </div>

                  <div className="grid gap-x-4 sm:grid-cols-2">
                    <Field label="Team Location / Address" name="location">
                      <input type="text" name="location" className="auth-input" placeholder="Station address" required />
                    </Field>
                    <Field label="Number of Employees" name="employeeNumber">
                      <input type="number" name="employeeNumber" className="auth-input" placeholder="e.g. 12" min="1" />
                    </Field>
                  </div>

                  <div className="auth-nav">
                    <button type="button" className="auth-btn auth-btn-primary" onClick={nextStep}>
                      Continue <Fa name="arrowRight" />
                    </button>
                  </div>
                </section>

                <section data-step="2" hidden={step !== 2} className="auth-fade-step">
                  <div className="auth-step-head">
                    <Fa name="addressBook" /> Official Team Contact
                  </div>

                  <div className="grid gap-x-4 sm:grid-cols-2">
                    <Field label="Official Team Phone" name="phone">
                      <input type="text" name="phone" className="auth-input" placeholder="Team phone number" required />
                    </Field>
                    <Field label="Official Team Email" name="email" error={errors?.email?.[0]}>
                      <input type="email" name="email" className="auth-input" placeholder="team@example.com" required />
                    </Field>
                  </div>

                  <div className="auth-nav">
                    <button type="button" className="auth-btn auth-btn-secondary" onClick={prevStep}>
                      <Fa name="arrowLeft" /> Back
                    </button>
                    <button type="button" className="auth-btn auth-btn-primary" onClick={nextStep}>
                      Continue <Fa name="arrowRight" />
                    </button>
                  </div>
                </section>

                <section data-step="3" hidden={step !== 3} className="auth-fade-step">
                  <div className="auth-step-head">
                    <Fa name="userTie" /> Incharge Details
                  </div>

                  <div className="grid gap-x-4 sm:grid-cols-2">
                    <Field label="Incharge Name" name="inchargeName">
                      <input type="text" name="inchargeName" className="auth-input" placeholder="Full name" required />
                    </Field>
                    <Field label="Incharge ID" name="inchargeId">
                      <input type="text" name="inchargeId" className="auth-input" placeholder="Employee ID" required />
                    </Field>
                  </div>

                  <div className="grid gap-x-4 sm:grid-cols-2">
                    <Field label="Incharge Email" name="inchargeEmail">
                      <input type="email" name="inchargeEmail" className="auth-input" placeholder="incharge@example.com" required />
                    </Field>
                    <Field label="Incharge Phone" name="inchargePhone">
                      <input type="text" name="inchargePhone" className="auth-input" placeholder="Contact number" required />
                    </Field>
                  </div>

                  <div className="auth-nav">
                    <button type="button" className="auth-btn auth-btn-secondary" onClick={prevStep}>
                      <Fa name="arrowLeft" /> Back
                    </button>
                    <button type="button" className="auth-btn auth-btn-primary" onClick={nextStep}>
                      Continue <Fa name="arrowRight" />
                    </button>
                  </div>
                </section>

                <section data-step="4" hidden={step !== 4} className="auth-fade-step">
                  <div className="auth-step-head">
                    <Fa name="lock" /> Account Security
                  </div>

                  <Field label="Password" name="password" error={errors?.password?.[0]}>
                    <div className="auth-pw">
                      <input
                        id="responsePassword"
                        type={showPassword ? "text" : "password"}
                        name="password"
                        className="auth-input"
                        placeholder="Create a password"
                        required
                      />
                      <button type="button" className="auth-pw-toggle" onClick={() => setShowPassword(!showPassword)} aria-label="Toggle password visibility">
                        <Fa name={showPassword ? "eyeSlash" : "eye"} />
                      </button>
                    </div>
                    <div className="auth-hint">
                      Use at least 8 characters with letters and numbers
                    </div>
                  </Field>

                  <Field label="Confirm Password" name="confirmPassword" error={errors?.confirmPassword?.[0]}>
                    <input type="password" name="confirmPassword" className="auth-input" placeholder="Re-enter your password" required />
                  </Field>

                  <div className="auth-nav">
                    <button type="button" className="auth-btn auth-btn-secondary" onClick={prevStep}>
                      <Fa name="arrowLeft" /> Back
                    </button>
                    <button type="submit" disabled={pending} className="auth-btn auth-btn-primary">
                      <Fa name="check" /> {pending ? "Submitting…" : "Submit Application"}
                    </button>
                  </div>
                </section>
              </>
            )}
          </form>
        </div>

        <div className="auth-footer text-center">
          <span className="text-[0.85rem] text-slate-500">
            Already have an account?{" "}
            <Link href="/login" className="auth-link">
              <Fa name="userPlus" /> Sign in
            </Link>
          </span>
        </div>
      </div>
    </div>
  );
}