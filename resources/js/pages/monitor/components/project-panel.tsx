import { WorkloadGauge } from '@/components/workload-gauge';
import { useTranslations } from '@/hooks/use-translations';
import { cn } from '@/lib/utils';
import { ChevronDown, ChevronUp, FolderOpen, Lock } from 'lucide-react';
import { useState } from 'react';
import type { HiddenProject, MonitorProject, SubProject } from '../types';

// ── Helpers ───────────────────────────────────────────────────────────────────

function StatusBadge({ status }: { status: MonitorProject['status'] }) {
	const { t } = useTranslations();
	const label =
		status === 'in_progress'
			? t('monitor.in_progress')
			: status === 'completed'
				? t('monitor.completed')
				: t('monitor.pending');
	return (
		<span
			className={cn(
				'inline-flex h-[22px] items-center rounded px-2 py-px text-xs font-semibold',
				status === 'in_progress' && 'border border-amber-400 bg-amber-50 text-amber-600',
				status === 'completed' && 'border border-green-500 bg-green-50 text-green-600',
				status === 'pending' && 'border border-slate-300 bg-slate-100 text-slate-500'
			)}
		>
			{label}
		</span>
	);
}

function StateBadge({ state }: { state: SubProject['state'] }) {
	const { t } = useTranslations();
	const label =
		state === 'in_progress'
			? t('monitor.in_progress')
			: state === 'completed'
				? t('monitor.completed')
				: t('monitor.pending');
	return (
		<span
			className={cn(
				'inline-flex h-[22px] items-center rounded px-2 py-px text-xs font-semibold',
				state === 'in_progress' && 'border border-amber-400 bg-amber-50 text-amber-600',
				state === 'completed' && 'border border-green-500 bg-green-50 text-green-600',
				state === 'pending' && 'border border-slate-300 bg-slate-100 text-slate-500'
			)}
		>
			{label}
		</span>
	);
}

/** Small 22 px avatar circle showing the first letter of the username (after @). */
function MiniAvatar({ username }: { username: string }) {
	const initial = username.startsWith('@')
		? username[1].toUpperCase()
		: username[0].toUpperCase();
	return (
		<span
			className="bg-brand-blue-300 inline-flex size-[22px] shrink-0 items-center justify-center rounded-full text-[10px] font-semibold text-white"
			aria-hidden="true"
		>
			{initial}
		</span>
	);
}

// ── Main component ─────────────────────────────────────────────────────────────

interface ProjectPanelProps {
	project: MonitorProject | HiddenProject;
}

export function ProjectPanel({ project }: ProjectPanelProps) {
	const [subprojectsExpanded, setSubprojectsExpanded] = useState(false);
	const { t } = useTranslations();

	// ── Hidden / restricted project ──────────────────────────────────────────
	if ('restricted' in project) {
		return (
			<div className="mx-4 mb-3 flex items-center gap-3 rounded-lg border border-slate-200 bg-slate-50 px-4 py-3">
				<Lock className="h-5 w-5 shrink-0 text-slate-400" aria-hidden="true" />
				<span className="text-sm font-medium text-slate-800">
					[{t('monitor.hidden_project')}]
				</span>
				<span className="inline-flex h-[22px] items-center rounded border border-slate-300 bg-slate-100 px-2 py-px text-xs font-semibold text-slate-500">
					{t('monitor.restricted')}
				</span>
				<div className="flex items-center gap-1.5">
					<span className="text-xs font-semibold text-slate-500">
						{t('monitor.owner')}:
					</span>
					<MiniAvatar username={project.owner} />
					<span className="text-xs text-slate-500">{project.owner}</span>
				</div>
				<span className="ml-auto text-sm font-medium text-slate-800">
					{project.assignedCount} {t('monitor.subprojects_assigned_to')}{' '}
					{project.assignedTo}
				</span>
			</div>
		);
	}

	// ── Normal project card ───────────────────────────────────────────────────
	const visibleCoManagers = project.coManagers.slice(0, 2);
	const extraCount = project.coManagers.length - 2;

	return (
		<div className="mx-4 mb-3 overflow-hidden rounded-lg border border-slate-200 bg-white">
			{/* ── Card header ─────────────────────────────────────────────── */}
			<div className="px-4 pt-3 pb-2">
				<div className="flex items-start gap-2">
					{/* Left: icon + name block */}
					<div className="flex flex-1 items-start gap-2">
						<FolderOpen
							className="text-brand-blue-700 mt-0.5 h-5 w-5 shrink-0"
							aria-hidden="true"
						/>
						<div className="flex flex-col gap-1">
							{/* Row 1: project name */}
							<p className="text-base font-medium text-slate-800">
								<span className="font-normal text-slate-500">
									{t('monitor.project_label')}:{' '}
								</span>
								{project.name}
							</p>
							{/* Row 2: date + status badge */}
							<div className="flex items-center gap-2">
								<span className="text-sm text-slate-400">{project.dateRange}</span>
								<StatusBadge status={project.status} />
							</div>
						</div>
					</div>

					{/* Right: overall progress text + bar */}
					<div className="flex w-[210px] shrink-0 flex-col items-end gap-1.5">
						<span className="text-sm font-medium text-slate-800">
							{t('monitor.overall_progress')} {project.overallProgress}%
						</span>
						<div
							className="bg-brand-blue-100 h-[5px] w-full overflow-hidden rounded-full"
							role="progressbar"
							aria-valuenow={project.overallProgress}
							aria-valuemin={0}
							aria-valuemax={100}
							aria-label={`${t('monitor.overall_progress')} ${project.overallProgress}%`}
						>
							<div
								className="bg-brand-blue-800 h-full rounded-full motion-safe:transition-[width] motion-safe:duration-500 motion-safe:ease-out"
								style={{ width: `${project.overallProgress}%` }}
							/>
						</div>
					</div>

					{/* Expand/collapse button */}
					<button
						type="button"
						onClick={() => setSubprojectsExpanded((prev) => !prev)}
						aria-expanded={subprojectsExpanded}
						aria-label={
							subprojectsExpanded
								? t('monitor.collapse_subprojects')
								: t('monitor.expand_subprojects')
						}
						className="focus-visible:ring-ring ml-2 flex size-7 shrink-0 cursor-pointer items-center justify-center rounded-md text-slate-400 transition-colors hover:bg-slate-100 focus-visible:ring-2 focus-visible:outline focus-visible:outline-2"
					>
						{subprojectsExpanded ? (
							<ChevronUp className="h-4 w-4" aria-hidden="true" />
						) : (
							<ChevronDown className="h-4 w-4" aria-hidden="true" />
						)}
					</button>
				</div>

				{/* Row 3: Owner + Co-managers (indented to align with text) */}
				<div className="mt-1.5 flex items-center gap-7 pl-7">
					{/* Owner */}
					<div className="flex items-center gap-2">
						<span className="text-xs font-semibold text-slate-500">
							{t('monitor.owner')}:
						</span>
						<div className="flex items-center gap-1">
							<MiniAvatar username={project.owner} />
							<span className="text-xs text-slate-500">{project.owner}</span>
						</div>
					</div>

					{/* Co-managers */}
					{project.coManagers.length > 0 && (
						<div className="flex items-center gap-2">
							<span className="text-xs font-semibold text-slate-500">
								{t('monitor.co_managers')}:
							</span>
							<div className="flex items-center gap-1.5">
								{visibleCoManagers.map((cm) => (
									<div key={cm} className="flex items-center gap-1">
										<MiniAvatar username={cm} />
										<span className="text-xs text-slate-500">{cm}</span>
									</div>
								))}
								{extraCount > 0 && (
									<span className="text-xs font-medium text-slate-500">
										+{extraCount}
									</span>
								)}
							</div>
						</div>
					)}
				</div>
			</div>

			{/* ── Subprojects ──────────────────────────────────────────────── */}
			{subprojectsExpanded && project.subprojects.length > 0 && (
				<div className="border-t border-slate-200">
					{/* Column header */}
					<div className="grid h-10 grid-cols-[1fr_156px_169px_158px] items-center border-b border-slate-200 bg-slate-50 px-4">
						<span className="text-xs font-semibold text-slate-500">
							{t('monitor.subproject')}
						</span>
						<span className="text-center text-xs font-semibold text-slate-500">
							{t('monitor.col_rem_workload')}
						</span>
						<span className="pr-2 text-right text-xs font-semibold text-slate-500">
							{t('monitor.col_progress')}
						</span>
						<span className="text-center text-xs font-semibold text-slate-500">
							{t('monitor.state')}
						</span>
					</div>

					{/* Subproject cards */}
					<div className="flex flex-col gap-2 bg-white p-4">
						{project.subprojects.map((sp) => (
							<div
								key={sp.id}
								className="grid grid-cols-[1fr_156px_169px_158px] items-center rounded-2xl border border-slate-200 bg-slate-50 px-5 py-3"
							>
								{/* Name + date stacked */}
								<div className="flex items-start gap-2">
									<FolderOpen
										className="mt-0.5 h-4 w-4 shrink-0 text-slate-400"
										aria-hidden="true"
									/>
									<div>
										<p className="text-sm font-medium text-slate-800">
											{sp.name}
										</p>
										<p className="text-xs text-slate-400">{sp.dateRange}</p>
									</div>
								</div>

								{/* Workload gauge */}
								<div className="flex justify-center">
									<WorkloadGauge value={sp.remainingWorkload} />
								</div>

								{/* Progress % + bar */}
								<div className="flex flex-col items-end gap-1 pr-2">
									<span className="text-sm font-medium text-slate-800">
										{sp.progress}%
									</span>
									<div
										className="bg-brand-blue-100 h-[5px] w-full overflow-hidden rounded-full"
										role="progressbar"
										aria-valuenow={sp.progress}
										aria-valuemin={0}
										aria-valuemax={100}
										aria-label={`${sp.name} ${sp.progress}%`}
									>
										<div
											className="bg-brand-blue-800 h-full rounded-full motion-safe:transition-[width] motion-safe:duration-500 motion-safe:ease-out"
											style={{ width: `${sp.progress}%` }}
										/>
									</div>
								</div>

								{/* State badge */}
								<div className="flex justify-center">
									<StateBadge state={sp.state} />
								</div>
							</div>
						))}
					</div>
				</div>
			)}
		</div>
	);
}
