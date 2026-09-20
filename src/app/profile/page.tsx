import type { Metadata } from "next";
import Image from "next/image";
import { requireRole } from "@/lib/auth/guards";
import CitizenNav from "@/components/citizen-nav";
import { db } from "@/lib/db";
import { Container, PageHeader } from "@/components/ui/page-header";
import { Card, CardBody } from "@/components/ui/card";
import ProfileForm from "./profile-form";

export const metadata: Metadata = { title: "My Profile | GovConnect" };

export default async function ProfilePage() {
  const session = await requireRole("user");
  const user = await db.users.findUniqueOrThrow({
    where: { user_id: session.userId },
  });

  return (
    <div className="min-h-screen bg-slate-50">
      <CitizenNav />
      <main className="py-8">
        <Container className="max-w-3xl">
          <PageHeader
            title="My profile"
            description="Your identity is verified on GovConnect. Core details are locked for safety."
          />
          <Card className="mt-6">
            <CardBody>
              <div className="mb-6 flex flex-wrap items-center gap-5">
                <div className="flex size-24 items-center justify-center overflow-hidden rounded-full border-4 border-slate-200 bg-slate-100 text-3xl">
                  {user.profile_pic ? (
                    <Image
                      src={`/uploads/profile_pics/${user.profile_pic}`}
                      alt={`${user.name}'s avatar`}
                      width={96}
                      height={96}
                      className="h-full w-full object-cover"
                    />
                  ) : (
                    <span aria-hidden>👤</span>
                  )}
                </div>
                <div>
                  <h2 className="text-xl font-extrabold text-slate-900">{user.name}</h2>
                  <p className="text-sm text-slate-500">{user.email}</p>
                  <p className="text-sm text-slate-500">
                    {user.nid ? `NID: ${user.nid}` : "No NID on file"}
                  </p>
                </div>
              </div>
              <ProfileForm
                name={user.name}
                email={user.email}
                nid={user.nid}
                dob={user.dob}
                phone={user.phone}
                location={user.location}
                latitude={user.latitude ? Number(user.latitude) : null}
                longitude={user.longitude ? Number(user.longitude) : null}
                profilePic={user.profile_pic}
              />
            </CardBody>
          </Card>
        </Container>
      </main>
    </div>
  );
}