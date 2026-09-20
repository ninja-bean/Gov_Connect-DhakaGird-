import type { Metadata } from "next";
import { requireRole } from "@/lib/auth/guards";
import AdminNav from "@/components/admin-nav";
import { Container, PageHeader } from "@/components/ui/page-header";
import { Card, CardBody } from "@/components/ui/card";
import { EmptyState } from "@/components/ui/empty-state";

export const metadata: Metadata = { title: "Admin Dashboard | GovConnect" };

export default async function AdminDashboard() {
  const session = await requireRole("admin");

  return (
    <div className="min-h-screen bg-slate-50">
      <AdminNav />
      <main className="py-8">
        <Container>
          <PageHeader
            title="Admin Dashboard"
            description={`Welcome back, ${session.name}`}
          />
          <Card className="mt-6">
            <CardBody>
              <EmptyState
                icon="🛠️"
                title="Admin tools are coming next"
                description="Team approvals, problem assignment, ban management and appeals review will live here."
              />
            </CardBody>
          </Card>
        </Container>
      </main>
    </div>
  );
}