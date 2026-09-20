const AUTH_SECRET_DEV = "dev-only-change-me-in-production";

export const env = {
  databaseUrl: process.env.DATABASE_URL,
  authSecret: process.env.AUTH_SECRET ?? AUTH_SECRET_DEV,
} as const;