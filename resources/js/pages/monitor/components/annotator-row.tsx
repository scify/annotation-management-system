import { WorkloadGauge } from '@/components/workload-gauge';
import { useTranslations } from '@/hooks/use-translations';
import { cn } from '@/lib/utils';
import { ChevronDown, ChevronUp } from 'lucide-react';
import { useState } from 'react';
import type { MonitorAnnotator } from '../types';
import { ProjectPanel } from './project-panel';

interface AnnotatorRowProps {
	annotator: MonitorAnnotator;
}

// Shared grid template — header and row must use the same column widths.
const GRID_COLS = 'grid-cols-[52px_194px_150px_1fr_1fr_156px_195px_56px]';

export function AnnotatorRow({ annotator }: AnnotatorRowProps) {
	const [expanded, setExpanded] = useState(false);
	const { t } = useTranslations();

	return (
		<div role="row" className="border-b border-slate-300 last:border-b-0">
			{/* ── Summary row ──────────────────────────────────────────────── */}
			<div className={cn('grid h-[54px] items-center bg-white', GRID_COLS)}>
				{/* Avatar */}
				<div role="cell" className="flex h-full items-center justify-center">
					<div
						className="bg-brand-blue-300 flex size-[29px] items-center justify-center rounded-full text-xs font-semibold text-white"
						aria-hidden="true"
					>
						{annotator.initials}
					</div>
				</div>

				{/* Username */}
				<div role="cell" className="flex h-full items-center pl-2">
					<span className="truncate text-base font-bold text-slate-800">
						{annotator.username}
					</span>
				</div>

				{/* Status badge */}
				<div role="cell" className="flex h-full items-center justify-center">
					<span
						className={cn(
							'inline-flex h-[22px] w-[100px] items-center justify-center rounded border text-xs font-semibold',
							annotator.status === 'active'
								? 'border-green-500 bg-green-50 text-green-600'
								: 'border-slate-300 bg-slate-100 text-slate-500'
						)}
					>
						{annotator.status === 'active'
							? t('monitor.active')
							: t('monitor.inactive')}
					</span>
				</div>

				{/* Active Subprojects */}
				<div role="cell" className="flex h-full items-center justify-end pr-4">
					<span className="text-base font-bold text-slate-800">
						{annotator.activeSubprojects}
					</span>
				</div>

				{/* Active Projects */}
				<div role="cell" className="flex h-full items-center justify-end pr-4">
					<span className="text-base font-bold text-slate-800">
						{annotator.activeProjects}
					</span>
				</div>

				{/* Rem. Workload gauge */}
				<div role="cell" className="flex h-full items-center justify-center">
					<WorkloadGauge value={annotator.remainingWorkload} />
				</div>

				{/* Progress % + bar */}
				<div
					role="cell"
					className="flex h-full flex-col items-end justify-center gap-1 px-3"
				>
					<span className="text-base font-bold text-slate-800">
						{annotator.progress}%
					</span>
					<div
						className="bg-brand-blue-100 h-[5px] w-full overflow-hidden rounded-full"
						role="progressbar"
						aria-valuenow={annotator.progress}
						aria-valuemin={0}
						aria-valuemax={100}
						aria-label={`${annotator.username} progress: ${annotator.progress}%`}
					>
						<div
							className="bg-brand-blue-800 h-full rounded-full motion-safe:transition-[width] motion-safe:duration-500 motion-safe:ease-out"
							style={{ width: `${annotator.progress}%` }}
						/>
					</div>
				</div>

				{/* Expand/collapse toggle */}
				<div role="cell" className="flex h-full items-center justify-center">
					<button
						type="button"
						onClick={() => setExpanded((prev) => !prev)}
						aria-expanded={expanded}
						aria-label={expanded ? t('monitor.collapse_row') : t('monitor.expand_row')}
						className="focus-visible:ring-ring flex size-8 cursor-pointer items-center justify-center rounded-md text-slate-400 transition-colors hover:bg-slate-100 focus-visible:ring-2 focus-visible:outline focus-visible:outline-2"
					>
						{expanded ? (
							<ChevronUp className="h-4 w-4" aria-hidden="true" />
						) : (
							<ChevronDown className="h-4 w-4" aria-hidden="true" />
						)}
					</button>
				</div>
			</div>

			{/* ── Expanded project panels ───────────────────────────────────── */}
			{expanded && (
				<div className="bg-brand-blue-50 pt-3">
					{annotator.projects.map((project, idx) => (
						<ProjectPanel
							key={'restricted' in project ? `restricted-${idx}` : project.id}
							project={project}
						/>
					))}
				</div>
			)}
		</div>
	);
}
