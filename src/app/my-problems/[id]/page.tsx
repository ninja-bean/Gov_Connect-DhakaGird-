import type { Metadata } from "next";
import Link from "next/link";
import { notFound } from "next/navigation";
import { db } from "@/lib/db";
import { requireRole } from "@/lib/auth/guards";
import CitizenNav from "@/components/citizen-nav";
import FeedbackForm from "./feedback-form";

export const metadata: Metadata = { title: "Report Details | GovConnect" };

const badge = (status: string) => {
  const map: Record<string, string> = {
    pending: "bg-amber-100 text-amber-700",
    "in-progress": "bg-blue-100 text-blue-700",
    resolved: "bg-green-100 text-green-700",
    rejected: "bg-red-100 text-red-700",
  };
  return `rounded-md px-3 py-1 text-xs font-bold uppercase ${map[status] ?? "bg-slate-100 text-slate-600"}`;
};

export default async function ProblemDetailPage({
  params,
}: {
  params: Promise<{ id: string }>;
}) {
  const session = await requireRole("user");
  const { id } = await params;
  const problemId = Number(id);

  const problem = await db.problems.findUnique({
    where: { problem_id: problemId },
  });
  if (!problem || problem.user_id !== session.userId) {
    notFound();
  }

  const feedback = await db.feedbacks.findFirst({
    where: { problem_id: problemId, user_id: session.userId },
  });

  const media = problem.media_path?.length
    ? problem.media_path.split(",").filter(Boolean)
    : [];

  const assigned = problem.assigned_to
    ? await db.users.findUnique({
        where: { user_id: problem.assigned_to },
        select: { name: true, category: true },
      })
    : null;

  return (
    <div className="min-h-screen bg-slate-50">
      <CitizenNav />
      <main className="mx-auto max-w-3xl px-6 py-8">
        <Link href="/my-problems" className="text-sm font-semibold text-blue-600 hover:underline">
          ← Back to my reports
        </Link>

        <div className="mt-4 rounded-2xl border border-slate-200 bg-white p-6">
          <div className="flex items-center justify-between gap-4">
            <h1 className="text-xl font-bold capitalize text-slate-900">
              {problem.category === "SOS" ? "🚨 SOS ALERT" : problem.category}
            </h1>
            <span className={badge(problem.status)}>{problem.status}</span>
          </div>

          <dl className="mt-6 space-y-4 text-sm">
            <div>
              <dt className="font-semibold text-slate-500">Description</dt>
              <dd className="mt-1 leading-relaxed text-slate-800">
                {problem.description ?? "—"}
              </dd>
            </div>
            <div>
              <dt className="font-semibold text-slate-500">Location</dt>
              <dd className="mt-1 text-slate-800">{problem.location_name || problem.location || "—"}</dd>
            </div>
            {problem.suggestion && (
              <div>
                <dt className="font-semibold text-slate-500">Your suggestion</dt>
                <dd className="mt-1 text-slate-800">{problem.suggestion}</dd>
              </div>
            )}
            <div>
              <dt className="font-semibold text-slate-500">Priority</dt>
              <dd className="mt-1 capitalize text-slate-800">{problem.priority}</dd>
            </div>
            {assigned && (
              <div>
                <dt className="font-semibold text-slate-500">Assigned team</dt>
                <dd className="mt-1 capitalize text-slate-800">
                  {assigned.name}
                  {assigned.category ? ` (${assigned.category})` : ""}
                </dd>
              </div>
            )}
            {problem.report && (
              <div>
                <dt className="font-semibold text-slate-500">Team report</dt>
                <dd className="mt-1 leading-relaxed text-slate-800">{problem.report}</dd>
              </div>
            )}
            <div>
              <dt className="font-semibold text-slate-500">Submitted</dt>
              <dd className="mt-1 text-slate-800">{problem.created_at.toLocaleString()}</dd>
            </div>
          </dl>

          {media.length > 0 && (
            <div className="mt-6">
              <p className="mb-2 text-sm font-semibold text-slate-500">Media</p>
              <div className="flex flex-wrap gap-3">
                {media.map((m) => (
                  <img
                    key={m}
                    src={`/uploads/problems/${m}`}
                    alt="Problem"
                    className="h-28 w-28 rounded-lg border border-slate-200 object-cover"
                  />
                ))}
              </div>
            </div>
          )}
        </div>

        <div className="mt-6 rounded-2xl border border-slate-200 bg-white p-6">
          <h2 className="mb-4 font-bold text-slate-900">Feedback</h2>
          {feedback ? (
            <div>
              <p className="text-amber-500">{"★".repeat(feedback.rating)}
                <span className="text-slate-300">{"★".repeat(5 - feedback.rating)}</span>
              </p>
              {feedback.comment && <p className="mt-2 text-sm text-slate-600">{feedback.comment}</p>}
            </div>
          ) : problem.status === "resolved" ? (
            <FeedbackForm problemId={problemId} />
          ) : (
            <p className="text-sm text-slate-500">
              Feedback is available once this report is resolved.
            </p>
          )}
        </div>
      </main>
    </div>
  );
}