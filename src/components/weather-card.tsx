const DHAKA_LAT = 23.8103;
const DHAKA_LNG = 90.4125;

const WEATHER_URL = `https://api.open-meteo.com/v1/forecast?latitude=${DHAKA_LAT}&longitude=${DHAKA_LNG}&current=temperature_2m,is_day,weather_code&timezone=auto`;

type WeatherData = {
  current?: {
    temperature_2m?: number;
    is_day?: number;
    weather_code?: number;
  };
};

function weatherEmoji(code: number | undefined, isDay: boolean): string {
  if (code === 0) return isDay ? "☀️" : "🌙";
  if (code !== undefined && code <= 3) return isDay ? "⛅" : "☁️";
  if (code !== undefined && code >= 51 && code <= 67) return "🌧️";
  if (code !== undefined && code >= 80) return "⛈️";
  return "🌤️";
}

export default async function WeatherCard() {
  let temp: number | null = null;
  let icon = "⚠️";
  let description = "Weather unavailable";

  try {
    const res = await fetch(WEATHER_URL, { next: { revalidate: 300 } });
    if (res.ok) {
      const data = (await res.json()) as WeatherData;
      if (data.current?.temperature_2m !== undefined) {
        temp = Math.round(data.current.temperature_2m);
        const isDay = (data.current.is_day ?? 1) === 1;
        icon = weatherEmoji(data.current.weather_code, isDay);
        description = "Dhaka, BD";
      }
    }
  } catch {
    // fall back to the unavailable state
  }

  return (
    <div className="flex items-center justify-between gap-3 rounded-2xl bg-slate-900 p-5 text-white shadow-sm">
      <div>
        <p className="text-3xl font-extrabold leading-none">{temp === null ? "--°C" : `${temp}°C`}</p>
        <p className="mt-2 text-sm font-medium text-slate-400">Dhaka</p>
        <p className="text-xs text-slate-500">{description}</p>
      </div>
      <span className="text-4xl" aria-hidden>
        {icon}
      </span>
    </div>
  );
}