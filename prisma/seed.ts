/* Database seed: citizens + response teams with real Dhaka locations.
   Run via `npm run db:seed` (tsx runner). Reuses one bcrypt hash for
   the shared demo password (Seed@123) so verifyPassword works unchanged. */
import "dotenv/config";
import bcrypt from "bcryptjs";
import { PrismaClient } from "../src/generated/prisma/client";

const prisma = new PrismaClient();

const DEMO_PASSWORD = "Seed@123";
const COST = 10;

const CITIZENS = [
  ["Mohammad Rahman", "mohammad.rahman.dg@gmail.com", "01710000001", "Dhanmondi, Dhaka"],
  ["Rahim Uddin", "rahim.uddin.dg@gmail.com", "01710000002", "Mirpur, Dhaka"],
  ["Karim Mia", "karim.mia.dg@gmail.com", "01710000003", "Motijheel, Dhaka"],
  ["Jahangir Alam", "jahangir.alam.dg@gmail.com", "01710000004", "Gulshan, Dhaka"],
  ["Nasrin Akter", "nasrin.akter.dg@gmail.com", "01710000005", "Banani, Dhaka"],
  ["Shahidul Islam", "shahidul.islam.dg@gmail.com", "01710000006", "Uttara, Dhaka"],
  ["Fatema Begum", "fatema.begum.dg@gmail.com", "01710000007", "Khilgaon, Dhaka"],
  ["Abul Kalam Azad", "abul.kalam.dg@gmail.com", "01710000008", "Mohammadpur, Dhaka"],
  ["Rashida Khatun", "rashida.khatun.dg@gmail.com", "01710000009", "Lalbagh, Dhaka"],
  ["Shafiqur Rahman", "shafiqur.rahman.dg@gmail.com", "01710000010", "Tejgaon, Dhaka"],
  ["Tahmina Akter", "tahmina.akter.dg@gmail.com", "01710000011", "Ramna, Dhaka"],
  ["Mizanur Rahman", "mizanur.rahman.dg@gmail.com", "01710000012", "Pallabi, Dhaka"],
  ["Sabina Yasmin", "sabina.yasmin.dg@gmail.com", "01710000013", "Kotwali, Dhaka"],
  ["Kamrul Hasan", "kamrul.hasan.dg@gmail.com", "01710000014", "Shyamoli, Dhaka"],
  ["Shirin Sultana", "shirin.sultana.dg@gmail.com", "01710000015", "Mohakhali, Dhaka"],
  ["Asaduzzaman Khan", "asaduzzaman.dg@gmail.com", "01710000016", "Agargaon, Dhaka"],
  ["Rokeya Begum", "rokeya.begum.dg@gmail.com", "01710000017", "Malibagh, Dhaka"],
  ["Emdadul Haque", "emdadul.haque.dg@gmail.com", "01710000018", "Rampura, Dhaka"],
  ["Moumita Chowdhury", "moumita.chowdhury.dg@gmail.com", "01710000019", "Banasree, Dhaka"],
  ["Tanjina Akter", "tanjina.akter.dg@gmail.com", "01710000020", "Rayer Bazar, Dhaka"],
  ["Mahmudul Islam", "mahmudul.islam.dg@gmail.com", "01710000021", "Shahbagh, Dhaka"],
  ["Jannatul Ferdous", "jannatul.ferdous.dg@gmail.com", "01710000022", "New Market, Dhaka"],
  ["Sohel Rana", "sohel.rana.dg@gmail.com", "01710000023", "Kadamtali, Dhaka"],
  ["Nazma Parvin", "nazma.parvin.dg@gmail.com", "01710000024", "Hazaribagh, Dhaka"],
  ["Tarek Aziz", "tarek.aziz.dg@gmail.com", "01710000025", "Green Road, Dhaka"],
] as const;

type Station = [name: string, incharge: string, phone: string, members: number, busy: number];

const TEAMS: Record<string, Station[]> = {
  police: [
    ["Dhanmondi Police Response Unit", "Md. Nurul Huda", "01720000001", 24, 3],
    ["Mirpur Police Response Unit", "Shah Alam", "01720000002", 28, 4],
    ["Motijheel Police Response Unit", "Abdur Rouf", "01720000003", 26, 2],
    ["Gulshan Police Response Unit", "Mahbubur Rahman", "01720000004", 30, 5],
    ["Banani Police Response Unit", "Golam Mostafa", "01720000005", 22, 3],
    ["Uttara Police Response Unit", "Anisur Rahman", "01720000006", 32, 6],
    ["Ramna Police Response Unit", "Faruk Hossain", "01720000007", 25, 2],
    ["Tejgaon Police Response Unit", "Shaheen Akhter", "01720000008", 24, 1],
    ["Khilgaon Police Response Unit", "Jashim Uddin", "01720000009", 23, 4],
    ["Mohammadpur Police Response Unit", "Rafiqul Islam", "01720000010", 26, 3],
    ["Lalbagh Police Response Unit", "Moshiur Rahman", "01720000011", 25, 2],
    ["Kotwali Police Response Unit", "Delwar Hossain", "01720000012", 27, 3],
    ["Rampura Police Response Unit", "Salam Mia", "01720000013", 21, 1],
    ["Nakhalpara Police Response Unit", "Habibur Rahman", "01720000014", 20, 2],
    ["Shyamoli Police Response Unit", "Nazmul Huda", "01720000015", 22, 3],
    ["Pallabi Police Response Unit", "Nur Alam", "01720000016", 24, 2],
    ["Kafrul Police Response Unit", "Sajedul Islam", "01720000017", 21, 1],
    ["Badda Police Response Unit", "Khairul Bashar", "01720000018", 26, 4],
    ["Hatirjheel Police Response Unit", "Rashedul Islam", "01720000019", 18, 2],
    ["Agargaon Police Response Unit", "Tofazzal Hossain", "01720000020", 19, 1],
    ["Shahbagh Police Response Unit", "Mozammel Hoque", "01720000021", 22, 2],
  ],
  fire: [
    ["Baridhara Fire Station", "Abdul Mannan", "01730000001", 30, 6],
    ["Mohammadpur Fire Station", "KM Habib", "01730000002", 32, 8],
    ["Uttara Fire Station", "Altaf Hossain", "01730000003", 35, 5],
    ["Motijheel Fire Station", "Jahangir Kabir", "01730000004", 34, 7],
    ["Gulshan Fire Station", "Manzur Alam", "01730000005", 33, 6],
    ["Mirpur Fire Station", "Ruhul Amin", "01730000006", 36, 9],
    ["Tejgaon Fire Station", "Badrul Alam", "01730000007", 31, 4],
    ["Dhanmondi Fire Station", "Shahidul Hoque", "01730000008", 29, 5],
    ["Khilgaon Fire Station", "Sirajul Islam", "01730000009", 28, 3],
    ["Banani Fire Station", "Mozahar Ali", "01730000010", 30, 5],
    ["Lalbagh Fire Station", "Azizul Hoque", "01730000011", 30, 6],
    ["Hazaribagh Fire Station", "Farid Uddin", "01730000012", 27, 5],
    ["Shyampur Fire Station", "Qazi Ruhul Amin", "01730000013", 33, 7],
    ["Ramna Fire Station", "Shafiqul Alam", "01730000014", 29, 4],
    ["Narayanganj Fire Station", "Sattar Khan", "01730000015", 34, 8],
    ["Savar Fire Station", "Israfil Alam", "01730000016", 32, 6],
    ["Cantonment Fire Station", "Abdur Rashid", "01730000017", 35, 5],
    ["Pallabi Fire Station", "Saidul Islam", "01730000018", 30, 4],
    ["Kuril Fire Station", "Nazrul Islam", "01730000019", 28, 3],
    ["Tongi Fire Station", "Motaleb Hossain", "01730000020", 29, 5],
    ["Bashundhara Fire Station", "Rustom Ali", "01730000021", 31, 6],
  ],
  medical: [
    ["Dhaka Medical College Hospital", "Dr. Bishwajit Bhowmik", "01740000001", 48, 12],
    ["Sir Salimullah Medical College", "Dr. KM Shahidul Islam", "01740000002", 45, 10],
    ["Shaheed Suhrawardy Medical College", "Dr. Momenul Haque", "01740000003", 44, 9],
    ["Bangabandhu Sheikh Mujib Medical University", "Dr. Rownak Jahan", "01740000004", 52, 14],
    ["Mugda Medical College Hospital", "Dr. AFM Shafiuddin", "01740000005", 40, 8],
    ["Kurmitola General Hospital", "Dr. Quazi Ashik Ali", "01740000006", 38, 7],
    ["Dhaka Shishu Hospital", "Dr. AKM Shafiqur Rahman", "01740000007", 36, 6],
    ["Institute of Child Health", "Dr. Sahana Yasmin", "01740000008", 35, 5],
    ["National Heart Foundation Hospital", "Dr. Samiul Haque", "01740000009", 39, 7],
    ["Sher-e-Bangla Medical College", "Dr. Abu Bakar Siddique", "01740000010", 46, 11],
    ["Holy Family Red Crescent Hospital", "Dr. Hasina Akhter", "01740000011", 41, 8],
    ["Birdem General Hospital", "Dr. Mohammad Seraj", "01740000012", 42, 9],
    ["Dhaka Uttara Eye Hospital", "Dr. Mahmudul Hasan", "01740000013", 30, 4],
    ["National Institute of Neurosciences", "Dr. Selina Begum", "01740000014", 37, 6],
    ["Dhaka Medical Assistant Training Hospital", "Dr. Rezaul Karim", "01740000015", 33, 5],
    ["Azimpur Maternity Hospital", "Dr. Farhana Rahman", "01740000016", 31, 5],
    ["NITOR Hospital", "Dr. Shahin Alam", "01740000017", 36, 7],
    ["Cancer Hospital Mohakhali", "Dr. Nasima Akhter", "01740000018", 34, 6],
    ["Dhaka Dental College", "Dr. Ashraful Haque", "01740000019", 28, 3],
    ["US Bangla Medical Specialist Hospital", "Dr. Kamal Hossain", "01740000020", 39, 8],
    ["Ichamati General Hospital", "Dr. Shamim Ahmed", "01740000021", 32, 5],
  ],
  gov: [
    ["Dhaka North City Corporation - Waste Cell", "Md. Mofizur Rahman", "01750000001", 40, 10],
    ["Dhaka South City Corporation - Drainage", "S.M. Sajjadur Rahman", "01750000002", 42, 9],
    ["Rajdhani Unnayan Kartripakkha (RAJUK)", "Md. Nurul Hydar", "01750000003", 38, 6],
    ["Dhaka Water Supply & Sewerage Authority", "Saeed Ahmed", "01750000004", 44, 11],
    ["BRTA Dhaka Reflection Office", "Golam Mortaza", "01750000005", 35, 5],
    ["Dhaka Electric Supply Company (DESCO)", "Md. Kamaluddin", "01750000006", 39, 8],
    ["Dhaka Power Distribution Company (DPDC)", "Masudur Rahman", "01750000007", 41, 9],
    ["Titas Gas Transmission Co.", "Mizanur Rahman Chowdhury", "01750000008", 36, 6],
    ["Department of Environment (DoE)", "Abdullah Al Mamun", "01750000009", 30, 4],
    ["Bangladesh Post Office - Dhaka GPO", "Md. Jamil Uddin", "01750000010", 27, 3],
    ["Dhaka Metropolitan Police - Traffic", "Sohel Rana", "01750000011", 45, 12],
    ["Fire Service Civil Defence HQ", "Md. Ullah", "01750000012", 50, 10],
    ["Dhaka City Corporation - Street Lighting", "A.K.M. Zahirul Islam", "01750000013", 33, 6],
    ["Telephone Shilpa Sangstha (TSS)", "Md. Rafiqul Islam", "01750000014", 24, 3],
    ["City Center Waste Management", "Abu Taher", "01750000015", 26, 4],
    ["Dhaka Metropolitan Roads Maintenance", "Mahfuzur Rahman", "01750000016", 32, 7],
    ["Rajdhani Park & Recreation Division", "Shibli Sadiq", "01750000017", 28, 4],
    ["DSCC Parks & Green Space", "Nazma Begum", "01750000018", 25, 3],
    ["Dhaka Water Drainage Pump Station", "Harun Or Rashid", "01750000019", 29, 5],
    ["National Housing Authority Dhaka", "Shahidul Alam", "01750000020", 31, 5],
    ["Dhaka City Traffic Signage", "Rafiqul Bashar", "01750000021", 30, 6],
  ],
};

async function main() {
  const totalAccounts =
    CITIZENS.length + Object.values(TEAMS).reduce((acc, t) => acc + t.length, 0);
  console.log(`Seeding ${totalAccounts} accounts (${DEMO_PASSWORD})…`);

  const hash = await bcrypt.hash(DEMO_PASSWORD, COST);

  let created = 0;
  let skipped = 0;

  for (const [name, email, phone, location] of CITIZENS) {
    const existing = await prisma.users.findUnique({ where: { email } });
    if (existing) {
      skipped++;
      continue;
    }
    await prisma.users.create({
      data: {
        name,
        email,
        phone,
        location,
        password: hash,
        role: "user",
        status: "active",
      },
    });
    created++;
  }

  for (const [category, stations] of Object.entries(TEAMS)) {
    for (const [name, incharge, phone, totalMembers, busyMembers] of stations) {
      const email = `${category.toLowerCase()}${phone.slice(-6)}@dhakagrid.gov`;
      const existing = await prisma.users.findUnique({ where: { email } });
      if (existing) {
        skipped++;
        continue;
      }
      await prisma.users.create({
        data: {
          name,
          email,
          phone,
          location: "Dhaka",
          password: hash,
          role: "response",
          status: "active",
          category,
          incharge_name: incharge,
          incharge_phone: phone,
          identification: `${category}-${phone.slice(-4)}`,
          employee_number: String(totalMembers),
          total_members: totalMembers,
          busy_members: busyMembers,
        },
      });
      created++;
    }
  }

  await prisma.$disconnect();
  console.log(`Done. Created ${created}, already present ${skipped}.`);
  console.log(`Sign-in: any seeded email + "${DEMO_PASSWORD}"`);
}

main().catch((err) => {
  console.error(err);
  process.exitCode = 1;
});