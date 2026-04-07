import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Tag } from '@/components/ui/tag';
import { Link } from '@inertiajs/react';
import { BellRing, FolderDot, FolderOpenDot, UserRound } from 'lucide-react';
import { type ProjectCardData, UserAvatar } from './project-card';

export function ProjectListItem({ project }: { project: ProjectCardData }) {
	const visibleCoManagers = project.coManagers.slice(0, 2);
	const extraCount = project.coManagers.length - 2;

	return (
		<article className="flex flex-col gap-4 rounded-2xl border border-slate-200 bg-white px-5 pt-7 pb-4">
			{/* Row 1: icon + name/date/badge | progress bar */}
			<div className="flex items-start justify-between gap-4">
				<div className="flex min-w-0 flex-1 gap-3">
					<div className="flex size-[42px] shrink-0 items-center justify-start">
						<FolderDot className="text-brand-blue-500" aria-hidden="true" />
					</div>
					<div className="flex min-w-0 flex-col gap-1">
						<p className="text-xl leading-9 font-medium text-slate-800">
							{project.name}
						</p>
						<p className="text-sm text-slate-600">{project.dateRange}</p>
						<Badge variant={project.status}>{project.statusLabel}</Badge>
					</div>
				</div>

				{/* Progress bar — top-right, fixed width */}
				<div className="flex w-[244px] shrink-0 flex-col gap-2">
					<span className="text-sm font-semibold text-slate-800">
						Overall Progress {project.progress}%
					</span>
					<div className="bg-brand-blue-100 h-3 w-full overflow-hidden rounded-full">
						<div
							className="bg-brand-blue-800 h-full rounded-full motion-safe:transition-[width] motion-safe:duration-500 motion-safe:ease-out"
							style={{ width: `${project.progress}%` }}
							role="progressbar"
							aria-valuenow={project.progress}
							aria-valuemin={0}
							aria-valuemax={100}
							aria-label={`Project progress: ${project.progress}%`}
						/>
					</div>
				</div>
			</div>

			{/* Row 2: tags | indicators — indented to align with text */}
			<div className="flex items-center justify-between gap-4 pl-[54px]">
				<div className="flex min-w-0 flex-1 gap-2.5 overflow-hidden">
					{/* Task tag — truncates to fill available space */}
					<Tag className="min-w-0 flex-1 overflow-hidden">
						<strong className="shrink-0 font-bold">Task:</strong>
						<span className="ml-1 min-w-0 truncate">{project.tags[0]}</span>
					</Tag>
					{/* Dataset tag — shrink-0 so it's never squeezed away */}
					<Tag className="shrink-0 overflow-hidden">
						<strong className="shrink-0 font-bold">Dataset:</strong>
						<span className="ml-1 max-w-[120px] truncate">{project.tags[1]}</span>
					</Tag>
				</div>

				{/* Indicator chips */}
				<div className="flex shrink-0 gap-3">
					<div
						className="bg-brand-blue-50 flex h-8 w-[72px] items-center justify-center gap-3 rounded-lg px-[10px]"
						title="Subprojects"
					>
						<FolderOpenDot
							className="size-[18px] shrink-0 text-slate-400"
							aria-hidden="true"
						/>
						<span className="text-base font-medium text-slate-800">
							{project.subprojects}
						</span>
					</div>
					<div
						className="bg-brand-blue-50 flex h-8 w-[72px] items-center justify-center gap-3 rounded-lg px-[10px]"
						title="Annotators"
					>
						<UserRound
							className="size-[18px] shrink-0 text-slate-400"
							aria-hidden="true"
						/>
						<span className="text-base font-medium text-slate-800">
							{project.annotators}
						</span>
					</div>
					<div
						className="bg-brand-blue-50 flex h-8 w-[72px] items-center justify-center gap-3 rounded-lg px-[10px]"
						title="Notifications"
					>
						<BellRing
							className="size-[18px] shrink-0 text-slate-400"
							aria-hidden="true"
						/>
						<span className="text-base font-medium text-slate-800">
							{project.notifications}
						</span>
					</div>
				</div>
			</div>

			{/* Row 3: owner + co-managers | view button — indented */}
			<div className="flex items-start justify-between gap-4 pl-[54px]">
				<div className="flex gap-7">
					<div className="flex flex-col gap-2">
						<span className="text-xs font-semibold text-slate-600">Owner:</span>
						<div className="flex items-center gap-1">
							<UserAvatar initials={project.owner.initials} />
							<span className="text-[0.75rem] text-slate-600">
								{project.owner.username}
							</span>
						</div>
					</div>

					<div className="flex flex-col gap-2">
						<span className="text-xs font-semibold text-slate-600">Co-managers:</span>
						<div className="flex flex-wrap items-center gap-1">
							{visibleCoManagers.map((cm) => (
								<div key={cm.username} className="flex items-center gap-1">
									<UserAvatar initials={cm.initials} />
									<span className="text-[0.75rem] text-slate-600">
										{cm.username}
									</span>
								</div>
							))}
							{extraCount > 0 && (
								<span className="text-[0.75rem] text-slate-600">+{extraCount}</span>
							)}
						</div>
					</div>
				</div>

				<Link href={route('projects.show', project.id)}>
					<Button className="hover:bg-brand-blue-800 h-10 w-[200px] shrink-0 font-semibold text-white">
						View Project
					</Button>
				</Link>
			</div>
		</article>
	);
}
