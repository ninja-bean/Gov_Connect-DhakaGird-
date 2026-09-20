import type { Metadata } from "next";
import Link from "next/link";
import Image from "next/image";
import { notFound } from "next/navigation";
import { db } from "@/lib/db";
import { requireRole } from "@/lib/auth/guards";
import CitizenNav from "@/components/citizen-nav";
import { Container } from "@/components/ui/page-header";
import { StatusBadge, PriorityBadge } from "@/components/ui/status-badge";
import { Card, CardHeader, CardBody } from "@/components/ui/card";
import FeedbackForm from "./feedback-form";

export const metadata: Metadata = { title: "Report Details | GovConnect" };

function DetailRow({ term, children }: { term: string; children: React.ReactNode }) {
  return (
    <div>
      <dt className="text-sm font-semibold text-slate-500">{term}</dt>
      <dd className="mt-1 text-sm leading-relaxed text-slate-800">{children ?? "—"}</dd>
    </div>
  );
}

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

  const media = problem.media_path?.length ? problem.media_path.split(",").filter(Boolean) : [];

  const assigned = problem.assigned_to
    ? await db.users.findUnique({
        where: { user_id: problem.assigned_to },
        select: { name: true, category: true },
      })
    : null;

  return (
    <div className="min-h-screen bg-slate-50">
      <CitizenNav />
      <main className="py-8">
        <Container className="max-w-3xl">
          <Link
            href="/my-problems"
            className="text-sm font-semibold text-red-600 hover:underline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-red-600"
          >
            ← Back to my reports
          </Link>

          <Card className="mt-4">
            <CardHeader
              title={
                <span className="capitalize">
                  {problem.category === "SOS" ? "🚨 SOS ALERT" : problem.category}
                </span>
              }
              action={
                <span className="flex items-center gap-2">
                  <PriorityBadge priority={problem.priority} />
                  <StatusBadge status={problem.status} />
                </span>
              }
            />
            <CardBody>
              <dl className="space-y-4">
                <DetailRow term="Description">{problem.description}</DetailRow>
                <DetailRow term="Location">{problem.location_name || problem.location}</DetailRow>
                <DetailRow term="Your suggestion">{problem.suggestion}</DetailRow>
                {assigned && (
                  <DetailRow term="Assigned team">
                    {assigned.name}
                    {assigned.category ? ` (${assigned.category})` : ""}
                  </DetailRow>
                )}
                <DetailRow term="Team report">{problem.report}</DetailRow>
                <DetailRow term="Submitted">{problem.created_at.toLocaleString()}</DetailRow>
              </dl>

              {media.length > 0 && (
                <div className="mt-6">
                  <p className="mb-2 text-sm font-semibold text-slate-500">Media</p>
                  <div className="flex flex-wrap gap-3">
                    {media.map((m) => (
                      <Image
                        key={m}
                        src={`/uploads/problems/${m}`}
                        alt="Attached evidence"
                        width={112}
                        height={112}
                        className="h-28 w-28 rounded-lg border border-slate-200 object-cover"
                      />
                    ))}
                  </div>
                </div>
              )}
            </CardBody>
          </Card>

          <Card className="mt-6">
            <CardHeader icon={<span aria-hidden>⭐</span>} title="Feedback" />
            <CardBody>
              {feedback ? (
                <div>
                  <p className="text-lg text-red-600" aria-label={`${feedback.rating} out of 5 stars`}>
                    {"★".repeat(feedback.rating)}
                    <span className="text-slate-300">{"★".repeat(5 - feedback.rating)}</span>
                  </p>
                  {feedback.comment && (
                    <p className="mt-2 text-sm text-slate-600">{feedback.comment}</p>
                  )}
                </div>
              ) : problem.status === "resolved" ? (
                <FeedbackForm problemId={problemId} />
              ) : (
                <p className="text-sm text-slate-500">
                  Feedback is available once this report is resolved.
                </p>
              )}
            </CardBody>
          </Card>
        </Container>
      </main>
    </div>
  );
}