import { Avatar, AvatarFallback } from '@/components/ui/avatar';
import { Badge, badgeVariants } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Tag } from '@/components/ui/tag';
import { useTranslations } from '@/hooks/use-translations';
import type { Project, ProjectStatus } from '@/types';
import { formatDate } from '@/utils/format';
import { Link } from '@inertiajs/react';
import { type VariantProps } from 'class-variance-authority';
import { BellRing, FolderDot, FolderOpenDot, UserRound } from 'lucide-react';

export type StatusVariant = Extract<
	NonNullable<VariantProps<typeof badgeVariants>['variant']>,
	'yellow' | 'lime' | 'slate' | 'pink'
>;

/** @deprecated Use Project from @/types instead */
export interface ProjectCardData {
	id: number;
	name: string;
	dateRange: string;
	status: StatusVariant;
	statusLabel: string;
	tags: [string, string];
	subprojects: number;
	annotators: number;
	notifications: number;
	progress: number;
	owner: { initials: string; username: string };
	coManagers: Array<{ initials: string; username: string }>;
}

const STATUS_VARIANT: Record<ProjectStatus, StatusVariant> = {
	in_progress: 'yellow',
	pending: 'slate',
	completed: 'lime',
};

function toInitials(username: string): string {
	return username.charAt(0).toUpperCase();
}

function buildDateRange(start: string | null, end: string | null): string {
	const opts: Intl.DateTimeFormatOptions = { year: 'numeric', month: 'short', day: 'numeric' };
	const startStr = formatDate(start, opts);
	if (!startStr) return '';
	const endStr = formatDate(end, opts);
	return endStr ? `${startStr} – ${endStr}` : startStr;
}

export function UserAvatar({ initials }: { initials: string }) {
	return (
		<Avatar className="size-[22px] shrink-0">
			<AvatarFallback className="bg-brand-blue-300 rounded-full text-[10px] font-semibold text-white">
				{initials}
			</AvatarFallback>
		</Avatar>
	);
}

export function ProjectCard({ project }: { project: Project }) {
	const { t } = useTranslations();

	const statusVariant = STATUS_VARIANT[project.status];
	const statusLabel = t(`projects.status.${project.status}`);
	const dateRange = buildDateRange(project.date_range_start, project.date_range_end);
	const progress = Math.round(project.project_progress * 100);
	const ownerInitials = toInitials(project.owner_name ?? '?');
	const ownerUsername = project.owner_name ? `@${project.owner_name}` : '—';
	const coManagers = project.co_managers ?? [];
	const visibleCoManagers = coManagers.slice(0, 2);
	const extraCount = coManagers.length - 2;

	return (
		<article className="flex flex-col gap-7 rounded-[16px] border border-slate-200 bg-white p-5">
			{/* Top section */}
			<div className="flex flex-col gap-4">
				{/* Icon + name + date */}
				<div className="flex flex-col gap-3">
					<div className="flex items-start justify-between">
						<div className="project-icon flex size-[42px] items-center justify-start rounded-lg bg-transparent">
							<FolderDot className="text-brand-blue-500" aria-hidden="true" />
						</div>
						<Badge variant={statusVariant}>{statusLabel}</Badge>
					</div>
					<div>
						<p className="text-xl leading-9 font-medium text-slate-800">
							{project.name}
						</p>
						<p className="text-sm text-slate-600">{dateRange}</p>
					</div>
				</div>

				{/* Tag chips */}
				{(project.annotation_task_title || project.dataset_name) && (
					<div className="flex gap-2.5">
						{project.annotation_task_title && (
							<Tag className="min-w-0 flex-1 truncate">
								{project.annotation_task_title}
							</Tag>
						)}
						{project.dataset_name && (
							<Tag className="shrink-0 whitespace-nowrap">{project.dataset_name}</Tag>
						)}
					</div>
				)}

				{/* Indicator chips: subprojects / annotators / notifications */}
				<div className="flex gap-3">
					<div
						className="bg-brand-blue-50 flex h-8 flex-1 items-center justify-center gap-4 rounded-lg px-[10px]"
						title="Subprojects"
					>
						<FolderOpenDot
							className="size-[18px] shrink-0 text-slate-400"
							aria-hidden="true"
						/>
						<span className="text-base font-medium text-slate-800">
							{project.subprojects_count}
						</span>
					</div>
					<div
						className="bg-brand-blue-50 flex h-8 flex-1 items-center justify-center gap-4 rounded-lg px-[10px]"
						title="Annotators"
					>
						<UserRound
							className="size-[18px] shrink-0 text-slate-400"
							aria-hidden="true"
						/>
						<span className="text-base font-medium text-slate-800">
							{project.annotators_count}
						</span>
					</div>
					<div
						className="bg-brand-blue-50 flex h-8 flex-1 items-center justify-center gap-4 rounded-lg px-[10px]"
						title="Notifications"
					>
						<BellRing
							className="size-[18px] shrink-0 text-slate-400"
							aria-hidden="true"
						/>
						<span className="text-base font-medium text-slate-800">
							{project.notifications_count}
						</span>
					</div>
				</div>
			</div>

			{/* Bottom section */}
			<div className="flex flex-col gap-4">
				{/* Progress bar */}
				<div className="flex flex-col gap-2">
					<span className="text-sm font-semibold text-slate-800">
						{t('projects.card.overall_progress')} {progress}%
					</span>
					<div className="bg-brand-blue-100 h-3 w-full overflow-hidden rounded-full">
						<div
							className="bg-brand-blue-800 h-full rounded-full motion-safe:transition-[width] motion-safe:duration-500 motion-safe:ease-out"
							style={{ width: `${progress}%` }}
							role="progressbar"
							aria-valuenow={progress}
							aria-valuemin={0}
							aria-valuemax={100}
							aria-label={`Project progress: ${progress}%`}
						/>
					</div>
				</div>

				{/* Owner + Co-managers */}
				<div className="flex gap-7">
					<div className="flex flex-col gap-2">
						<span className="text-xs font-semibold text-slate-600">
							{t('projects.card.owner')}
						</span>
						<div className="flex items-center gap-1">
							<UserAvatar initials={ownerInitials} />
							<span className="text-[0.75rem] text-slate-600">{ownerUsername}</span>
						</div>
					</div>

					<div className="flex flex-col gap-2">
						<span className="text-xs font-semibold text-slate-600">
							{t('projects.card.co_managers')}
						</span>
						<div className="flex flex-wrap items-center gap-1">
							{visibleCoManagers.map((cm) => (
								<div key={cm.username} className="flex items-center gap-1">
									<UserAvatar initials={toInitials(cm.username)} />
									<span className="text-[0.75rem] text-slate-600">
										@{cm.username}
									</span>
								</div>
							))}
							{extraCount > 0 && (
								<span className="text-[0.75rem] text-slate-600">+{extraCount}</span>
							)}
						</div>
					</div>
				</div>

				{/* View Project */}
				<Link href={route('projects.show', project.id)}>
					<Button className="hover:bg-brand-blue-800 h-10 font-semibold text-white">
						{t('projects.card.view_project')}
					</Button>
				</Link>
			</div>
		</article>
	);
}
