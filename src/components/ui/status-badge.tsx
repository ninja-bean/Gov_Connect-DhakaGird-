import { Badge, type Tone } from "./badge";

export type ProblemStatus = "pending" | "verified" | "assigned" | "working" | "resolved" | "rejected";

const statusTone: Record<ProblemStatus, Tone> = {
  pending: "amber",
  verified: "blue",
  assigned: "teal",
  working: "blue",
  resolved: "green",
  rejected: "red",
};

const statusLabel: Record<ProblemStatus, string> = {
  pending: "Pending",
  verified: "Verified",
  assigned: "Assigned",
  working: "Working",
  resolved: "Resolved",
  rejected: "Rejected",
};

export function StatusBadge({ status }: { status: string }) {
  const key = (status || "pending").toLowerCase() as ProblemStatus;
  return <Badge tone={statusTone[key] ?? "slate"}>{statusLabel[key] ?? status}</Badge>;
}

export function PriorityBadge({ priority }: { priority: string }) {
  const key = (priority || "low").toLowerCase();
  const tone: Tone = key === "sos" ? "red" : key === "high" ? "orange" : key === "medium" ? "blue" : "slate";
  const label = key === "sos" ? "SOS" : key.charAt(0).toUpperCase() + key.slice(1);
  return (
    <Badge tone={tone} className="uppercase">
      {label}
    </Badge>
  );
}