import type { Metadata } from "next";
import { db } from "@/lib/db";
import { requireRole } from "@/lib/auth/guards";
import { isCurrentlyBanned } from "@/lib/problems";
import CitizenNav from "@/components/citizen-nav";
import { Container, PageHeader } from "@/components/ui/page-header";
import { Card, CardBody } from "@/components/ui/card";
import { ButtonLink } from "@/components/ui/button";
import { EmptyState } from "@/components/ui/empty-state";
import ReportForm from "./report-form";

export const metadata: Metadata = { title: "Submit Report | GovConnect" };

export default async function ReportPage() {
  const session = await requireRole("user");
  const user = await db.users.findUniqueOrThrow({
    where: { user_id: session.userId },
    select: { is_banned: true, ban_until: true },
  });

  const banned = isCurrentlyBanned(user);

  return (
    <div className="min-h-screen bg-slate-50">
      <CitizenNav />
      <main className="py-8">
        <Container className="max-w-3xl">
          <PageHeader
            title={banned ? "Account Restricted" : "Submit New Problem"}
            description={
              banned
                ? "Your account is currently restricted."
                : "Report a problem in your area and track it until it is resolved."
            }
          />
          <Card className="mt-6">
            <CardBody>
              {banned ? (
                <EmptyState
                  icon="🚫"
                  title="Your account has been restricted."
                  description="You cannot submit new reports while your account is banned."
                  action={
                    <ButtonLink href="/appeal" variant="danger">
                      Lodge an appeal
                    </ButtonLink>
                  }
                />
              ) : (
                <ReportForm />
              )}
            </CardBody>
          </Card>
        </Container>
      </main>
    </div>
  );
}