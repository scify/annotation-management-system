const TOTAL = 20;
const CX = 34;
const CY = 35;
const R1 = 20;
const R2 = 28;

const round = (n: number) => Math.round(n * 1e6) / 1e6;

const TICKS = Array.from({ length: TOTAL }, (_, i) => {
    const angle = Math.PI - (i / (TOTAL - 1)) * Math.PI;
    return {
        x1: round(CX + R1 * Math.cos(angle)),
        y1: round(CY - R1 * Math.sin(angle)),
        x2: round(CX + R2 * Math.cos(angle)),
        y2: round(CY - R2 * Math.sin(angle)),
    };
});

export function WorkloadGauge({ value }: { value: number }) {
    const fill = Math.round((value / 100) * TOTAL);
    const color = value <= 30 ? '#7ccf00' : value <= 80 ? '#e17100' : '#e7000b';

    return (
        <svg
            width="68"
            height="35"
            viewBox="0 0 68 35"
            aria-label={`Workload: ${value}%`}
            role="img"
        >
            {TICKS.map(({ x1, y1, x2, y2 }, i) => (
                <line
                    key={i}
                    x1={x1}
                    y1={y1}
                    x2={x2}
                    y2={y2}
                    stroke={i < fill ? color : '#e2e8f0'}
                    strokeWidth="2.5"
                    strokeLinecap="round"
                />
            ))}
        </svg>
    );
}
