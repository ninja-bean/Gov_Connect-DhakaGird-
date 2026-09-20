import type { Metadata } from "next";
import { requireSession } from "@/lib/auth/guards";
import RoleNav from "@/components/role-nav";
import { Container, PageHeader } from "@/components/ui/page-header";
import { Card, CardBody } from "@/components/ui/card";
import ChangePasswordForm from "./change-password-form";

export const metadata: Metadata = { title: "Change Password | GovConnect" };

export default async function ChangePasswordPage() {
  const session = await requireSession();
  return (
    <div className="min-h-screen bg-slate-50">
      <RoleNav role={session.role} />
      <main className="py-8">
        <Container className="max-w-lg">
          <PageHeader title="Change password" description="Use a strong password you don't reuse elsewhere." />
          <Card className="mt-6">
            <CardBody>
              <ChangePasswordForm />
            </CardBody>
          </Card>
        </Container>
      </main>
    </div>
  );
}