import type { Metadata } from "next";
import { requireRole } from "@/lib/auth/guards";
import CitizenNav from "@/components/citizen-nav";
import { db } from "@/lib/db";
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
      <main className="mx-auto max-w-3xl px-6 py-8">
        <div className="rounded-2xl border border-slate-200 bg-white">
          <div className="border-b border-slate-200 p-6">
            <h1 className="text-xl font-bold text-slate-900">My Profile</h1>
          </div>
          <div className="p-6">
            <div className="mb-6 flex items-center gap-5">
              <div className="flex h-24 w-24 items-center justify-center overflow-hidden rounded-full border-4 border-slate-200 bg-slate-100 text-3xl">
                {user.profile_pic ? (
                  <img
                    src={`/uploads/profile_pics/${user.profile_pic}`}
                    alt="You"
                    className="h-full w-full object-cover"
                  />
                ) : (
                  "👤"
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
          </div>
        </div>
      </main>
    </div>
  );
}