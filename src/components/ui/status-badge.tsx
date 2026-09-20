import { Badge, type Tone } from "./badge";

export type ProblemStatus = "pending" | "verified" | "assigned" | "working" | "resolved" | "rejected";

const statusTone: Record<ProblemStatus, Tone> = {
  pending: "slate",
  verified: "slate",
  assigned: "ink",
  working: "ink",
  resolved: "ink",
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
  const tone: Tone =
    key === "sos" || key === "high" ? "red" : key === "medium" ? "ink" : "slate";
  const label = key === "sos" ? "SOS" : key.charAt(0).toUpperCase() + key.slice(1);
  return (
    <Badge tone={tone} className="uppercase">
      {label}
    </Badge>
  );
}