import "server-only";
import bcrypt from "bcryptjs";

const COST = 10;

/** Minimum complexity the platform enforces for new passwords. */
export const PASSWORD_POLICY = {
  minLength: 8,
  upper: true,
  lower: true,
  digit: true,
  symbol: true,
} as const;

/**
 * Validates a password against the platform policy. Returns an error message
 * when the password is too weak, or null when it passes.
 */
export function passwordStrengthError(password: string): string | null {
  if (password.length < PASSWORD_POLICY.minLength) {
    return `Password must be at least ${PASSWORD_POLICY.minLength} characters.`;
  }
  if (PASSWORD_POLICY.upper && !/[A-Z]/.test(password)) {
    return "Password must include an uppercase letter.";
  }
  if (PASSWORD_POLICY.lower && !/[a-z]/.test(password)) {
    return "Password must include a lowercase letter.";
  }
  if (PASSWORD_POLICY.digit && !/\d/.test(password)) {
    return "Password must include a number.";
  }
  if (PASSWORD_POLICY.symbol && !/[^A-Za-z0-9]/.test(password)) {
    return "Password must include a symbol (e.g. !@#$%).";
  }
  return null;
}

export function hashPassword(password: string): Promise<string> {
  return bcrypt.hash(password, COST);
}

export function verifyPassword(
  password: string,
  hash: string,
): Promise<boolean> {
  return bcrypt.compare(password, hash);
}