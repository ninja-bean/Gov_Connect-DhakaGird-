import "server-only";
import { mkdir, writeFile } from "node:fs/promises";
import path from "node:path";
import { randomUUID } from "node:crypto";

export const ALLOWED_IMAGE_TYPES = new Set([
  "image/jpeg",
  "image/png",
  "image/webp",
]);
export const PROBLEM_MEDIA_MAX_BYTES = 10 * 1024 * 1024;
export const PROFILE_PIC_MAX_BYTES = 2 * 1024 * 1024;

async function saveFile(file: File, subdir: string, maxBytes: number): Promise<string> {
  const dir = path.join(process.cwd(), "public", "uploads", subdir);
  await mkdir(dir, { recursive: true });
  const ext = (file.name.split(".").pop() ?? "bin").toLowerCase();
  const name = `${Date.now()}_${randomUUID().slice(0, 8)}.${ext}`;
  await writeFile(path.join(dir, name), Buffer.from(await file.arrayBuffer()));
  return name;
}

export async function saveProblemMedia(files: File[]): Promise<string[]> {
  const names: string[] = [];
  for (const file of files) {
    if (file.size === 0) continue;
    if (file.size > PROBLEM_MEDIA_MAX_BYTES) {
      throw new Error(`"${file.name}" is too large (max 10MB per file).`);
    }
    if (!ALLOWED_IMAGE_TYPES.has(file.type)) {
      throw new Error(`"${file.name}" is not supported. Only JPG, PNG and WebP.`);
    }
    names.push(await saveFile(file, "problems", PROBLEM_MEDIA_MAX_BYTES));
  }
  return names;
}

export async function saveProfilePicture(
  file: File,
): Promise<string> {
  if (file.size > PROFILE_PIC_MAX_BYTES) {
    throw new Error("Profile picture too large (max 2MB).");
  }
  if (!ALLOWED_IMAGE_TYPES.has(file.type)) {
    throw new Error("Unsupported image format. Use JPG, PNG or WebP.");
  }
  return saveFile(file, "profile_pics", PROFILE_PIC_MAX_BYTES);
}