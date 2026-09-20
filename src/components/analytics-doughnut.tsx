const RADIUS = 54;
const CIRCUMFERENCE = 2 * Math.PI * RADIUS;
const STROKE = 22;

export type AnalyticsSlice = {
  label: string;
  value: number;
  color: string;
};

function Doughnut({ slices }: { slices: AnalyticsSlice[] }) {
  const total = slices.reduce((sum, s) => sum + s.value, 0);
  if (total === 0) {
    return (
      <circle
        cx={75}
        cy={75}
        r={RADIUS}
        fill="none"
        stroke="#E2E8F0"
        strokeWidth={STROKE}
        transform="rotate(-90 75 75)"
      />
    );
  }

  const arcs: React.ReactNode[] = [];
  let offset = 0;
  for (const slice of slices) {
    if (slice.value === 0) continue;
    const length = (slice.value / total) * CIRCUMFERENCE;
    arcs.push(
      <circle
        key={slice.label}
        cx={75}
        cy={75}
        r={RADIUS}
        fill="none"
        stroke={slice.color}
        strokeWidth={STROKE}
        strokeDasharray={`${length} ${CIRCUMFERENCE - length}`}
        strokeDashoffset={-offset}
        transform="rotate(-90 75 75)"
      />,
    );
    offset += length;
  }
  return <>{arcs}</>;
}

export function AnalyticsDoughnut({ slices }: { slices: AnalyticsSlice[] }) {
  return (
    <div className="flex items-center gap-6">
      <svg
        viewBox="0 0 150 150"
        className="size-36 shrink-0"
        role="img"
        aria-label="Distribution of your reports by category"
      >
        <circle cx={75} cy={75} r={RADIUS} fill="none" stroke="#EEF2F7" strokeWidth={STROKE} />
        <Doughnut slices={slices} />
      </svg>
      <ul className="min-w-0 flex-1 space-y-2 text-sm">
        {slices.map((slice) => (
          <li key={slice.label} className="flex items-center gap-2">
            <span
              className="size-2.5 shrink-0 rounded-full"
              style={{ backgroundColor: slice.color }}
              aria-hidden
            />
            <span className="font-medium text-slate-700">{slice.label}</span>
            <span className="ml-auto font-bold text-slate-900">{slice.value}</span>
          </li>
        ))}
      </ul>
    </div>
  );
}