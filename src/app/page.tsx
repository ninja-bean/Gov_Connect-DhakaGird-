import Link from "next/link";

const features = [
  {
    icon: "📢",
    title: "Report Complaints",
    description: "Easily file complaints about local issues like road damage, sanitation, or services.",
  },
  {
    icon: "🤝",
    title: "Ask for Help",
    description: "Request emergency assistance or help from local authorities in just a few clicks.",
  },
  {
    icon: "📊",
    title: "Track Responses",
    description: "Stay informed on how your issues are being addressed, and get real-time updates.",
  },
];

const team = [
  { name: "Md Injabin Alam", role: "Full Stack developer", bio: "Crafted the user interface and designed an efficient database architecture for the platform." },
  { name: "Md. Al Shahariyar", role: "Planner & Backend Expert", bio: "Planned the project architecture and developed core backend functionality to make everything work seamlessly." },
  { name: "Manisha Choudhury", role: "Presenter & Data Analytics", bio: "Specialized in presentation, data analysis, and visualization of government and citizen engagement data." },
  { name: "Binita Gope", role: "PHP Expert", bio: "Focused on PHP development and integration to ensure smooth and efficient system operation." },
];

function initials(name: string) {
  return name
    .replace(/\./g, "")
    .split(" ")
    .map((part) => part.charAt(0))
    .slice(0, 2)
    .join("")
    .toUpperCase();
}

export default function Home() {
  return (
    <main className="min-h-screen bg-slate-50 text-slate-900">
      <section className="flex min-h-screen items-center justify-center bg-ink px-6">
        <div className="max-w-xl text-center text-white">
          <h1 className="text-4xl font-bold tracking-tight md:text-5xl">
            Welcome to GovConnect
          </h1>
          <p className="mt-4 text-lg text-slate-300">
            Empowering citizens to connect with their government, report issues,
            and request help — all in one place.
          </p>
          <div className="mt-8 flex justify-center gap-4">
            <Link
              href="/login"
              className="rounded-lg bg-red-600 px-6 py-3 font-semibold text-white transition hover:bg-red-500"
            >
              Login
            </Link>
            <Link
              href="/register"
              className="rounded-lg border border-white/40 px-6 py-3 font-semibold text-white transition hover:bg-white/10"
            >
              Register
            </Link>
          </div>
        </div>
      </section>

      <section className="py-16">
        <div className="mx-auto max-w-4xl px-6 text-center">
          <h2 className="text-3xl font-bold">About the Project</h2>
          <p className="mx-auto mt-4 max-w-2xl text-lg leading-relaxed text-slate-600">
            <strong>GovConnect</strong> is a citizen-centric platform that bridges the gap between
            people and the government. Citizens can raise complaints, request help, and share
            feedback directly through this system, while authorities can respond and track
            community needs efficiently.
          </p>
          <div className="mt-10 grid gap-6 sm:grid-cols-3">
            {features.map((feature) => (
              <div key={feature.title} className="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <div className="text-4xl">{feature.icon}</div>
                <h3 className="mt-4 text-lg font-bold">{feature.title}</h3>
                <p className="mt-2 text-sm leading-relaxed text-slate-600">{feature.description}</p>
              </div>
            ))}
          </div>
        </div>
      </section>

      <section className="bg-white py-16">
        <div className="mx-auto max-w-5xl px-6">
          <h2 className="text-center text-3xl font-bold">Meet Our Team</h2>
          <p className="mt-4 text-center text-lg text-slate-600">
            The passionate developers and designers behind <strong>GovConnect</strong>.
          </p>
          <div className="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
            {team.map((member) => (
              <div key={member.name} className="rounded-2xl border border-slate-200 bg-slate-50 p-6 text-center shadow-sm">
                <div className="mx-auto flex size-24 items-center justify-center rounded-full bg-gradient-to-br from-ink to-ink-fade text-2xl font-bold text-white">
                  {initials(member.name)}
                </div>
                <h3 className="mt-4 font-bold">{member.name}</h3>
                <p className="mt-1 text-sm font-semibold text-red-600">{member.role}</p>
                <p className="mt-3 text-sm leading-relaxed text-slate-600">{member.bio}</p>
              </div>
            ))}
          </div>
        </div>
      </section>

      <footer className="bg-ink py-8 text-center text-sm text-slate-300">
        &copy; {new Date().getFullYear()} GovConnect — Built with ❤️ by the GovConnect Team.
      </footer>
    </main>
  );
}