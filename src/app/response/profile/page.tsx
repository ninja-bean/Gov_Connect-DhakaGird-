import type { Metadata } from "next";
import { db } from "@/lib/db";
import { requireRole } from "@/lib/auth/guards";
import ResponseNav from "@/components/response-nav";
import { Container, PageHeader } from "@/components/ui/page-header";
import { Card, CardBody } from "@/components/ui/card";
import ResponseTeamProfileForm from "./response-team-profile-form";

export const metadata: Metadata = { title: "Team Profile | GovConnect" };

export default async function ResponseProfilePage() {
  const session = await requireRole("response");
  const team = await db.users.findUniqueOrThrow({
    where: { user_id: session.userId },
  });

  return (
    <div className="min-h-screen bg-slate-50">
      <ResponseNav />
      <main className="py-8">
        <Container className="max-w-3xl">
          <PageHeader
            title="Team Profile"
            description="These details are shown to citizens and used to route problems to your unit."
          />
          <Card className="mt-6">
            <CardBody>
              <div className="mb-6 flex flex-wrap items-center gap-5">
                <div className="flex size-24 items-center justify-center overflow-hidden rounded-full border-4 border-slate-200 bg-slate-100 text-3xl">
                  {team.profile_pic ? (
                    <img
                      src={`/uploads/profile_pics/${team.profile_pic}`}
                      alt={`${team.name} avatar`}
                      className="h-full w-full object-cover"
                    />
                  ) : (
                    <span aria-hidden>🛡️</span>
                  )}
                </div>
                <div>
                  <h2 className="text-xl font-extrabold capitalize text-slate-900">{team.name}</h2>
                  <p className="text-sm capitalize text-slate-500">
                    {team.category} response unit · {team.location ?? "Dhaka"}
                  </p>
                  <p className="text-sm text-slate-500">
                    {team.total_members} members · {team.total_members - team.busy_members} available
                  </p>
                </div>
              </div>
              <ResponseTeamProfileForm
                name={team.name}
                category={team.category ?? "police"}
                inchargeName={team.incharge_name ?? ""}
                inchargeEmail={team.incharge_email ?? ""}
                inchargePhone={team.incharge_phone ?? ""}
                employeeNumber={
                  team.total_members > 0
                    ? String(team.total_members)
                    : team.employee_number ?? ""
                }
                location={team.location ?? ""}
                latitude={team.latitude ? Number(team.latitude) : null}
                longitude={team.longitude ? Number(team.longitude) : null}
                profilePic={team.profile_pic}
              />
            </CardBody>
          </Card>
        </Container>
      </main>
    </div>
  );
}