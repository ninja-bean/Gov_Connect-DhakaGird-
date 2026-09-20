import "server-only";

export const PROBLEM_CATEGORIES = ["police", "medical", "fire", "gov", "other"] as const;
export type ProblemCategory = (typeof PROBLEM_CATEGORIES)[number];

export const SOS_CATEGORY = "SOS";

export const DHAKA_BOUNDS = {
  minLat: 23.65,
  maxLat: 23.9,
  minLng: 90.3,
  maxLng: 90.55,
} as const;

export function inDhaka(lat: number, lng: number): boolean {
  return (
    lat >= DHAKA_BOUNDS.minLat &&
    lat <= DHAKA_BOUNDS.maxLat &&
    lng >= DHAKA_BOUNDS.minLng &&
    lng <= DHAKA_BOUNDS.maxLng
  );
}

type BanInfo = { is_banned: boolean; ban_until: string | null };

export function isCurrentlyBanned(user: BanInfo): boolean {
  if (user.is_banned) return true;
  const until = user.ban_until;
  if (!until) return false;
  if (until.toLowerCase() === "permanent") return true;
  const t = Date.parse(until);
  if (Number.isNaN(t)) return false;
  return t > Date.now();
}

export async function reverseGeocode(
  lat: number,
  lng: number,
): Promise<string | null> {
  try {
    const url = `https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=${encodeURIComponent(
      lat,
    )}&lon=${encodeURIComponent(lng)}`;
    const res = await fetch(url, {
      headers: { "User-Agent": "GovConnect/1.0" },
      signal: AbortSignal.timeout(8000),
    });
    if (!res.ok) return null;
    const data = (await res.json()) as { display_name?: string };
    return data.display_name ?? null;
  } catch {
    return null;
  }
}

export function resolveLocation(
  locationText: string | null,
  lat: number,
  lng: number,
): string {
  return (
    locationText?.trim() ||
    `${lat.toFixed(6)}, ${lng.toFixed(6)}`
  );
}