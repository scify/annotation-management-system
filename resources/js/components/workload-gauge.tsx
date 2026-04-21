export function WorkloadGauge({ value }: { value: number }) {
	const TOTAL = 20;
	const fill = Math.round((value / 100) * TOTAL);
	const color = value <= 30 ? '#7ccf00' : value <= 80 ? '#e17100' : '#e7000b';
	const cx = 34,
		cy = 35,
		r1 = 20,
		r2 = 28;

	return (
		<svg
			width="68"
			height="35"
			viewBox="0 0 68 35"
			aria-label={`Workload: ${value}%`}
			role="img"
		>
			{Array.from({ length: TOTAL }).map((_, i) => {
				const angle = Math.PI - (i / (TOTAL - 1)) * Math.PI;
				const round = (n: number) => Math.round(n * 1e6) / 1e6;
				const x1 = round(cx + r1 * Math.cos(angle));
				const y1 = round(cy - r1 * Math.sin(angle));
				const x2 = round(cx + r2 * Math.cos(angle));
				const y2 = round(cy - r2 * Math.sin(angle));
				return (
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
				);
			})}
		</svg>
	);
}
