import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
	DropdownMenu,
	DropdownMenuContent,
	DropdownMenuItem,
	DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { Tag } from '@/components/ui/tag';
import { type StatusVariant } from '@/components/project/project-card';
import { BellRing, FolderOpenDot, MoreVertical, UserRound } from 'lucide-react';

export interface SubProjectListItemData {
	id: number;
	name: string;
	instancesRange: string;
	dateRange: string;
	status: StatusVariant;
	statusLabel: string;
	progress: number;
	annotators: number;
	notifications: number;
}

export function SubProjectListItem({ subProject }: { subProject: SubProjectListItemData }) {
	return (
		<article className="flex flex-col gap-3 rounded-2xl border border-slate-200 bg-white px-5 pt-4 pb-5">
			{/* Row 1: icon + name/instances/date | status badge | 3-dot menu */}
			<div className="flex items-start justify-between pr-2">
				<div className="flex min-w-0 flex-1 gap-[14px]">
					<div className="flex size-[42px] shrink-0 items-center justify-start">
						<FolderOpenDot className="text-brand-blue-500" aria-hidden="true" />
					</div>
					<div className="flex min-w-0 flex-col gap-0">
						<div className="flex flex-wrap items-start gap-2">
							<p className="text-lg leading-9 font-medium text-slate-800">
								{subProject.name}
							</p>
							<Tag className="shrink-0 self-center">
								Instances: {subProject.instancesRange}
							</Tag>
						</div>
						<p className="text-sm text-slate-600">{subProject.dateRange}</p>
					</div>
				</div>

				<div className="flex shrink-0 items-center gap-3 pl-14">
					<Badge variant={subProject.status}>{subProject.statusLabel}</Badge>

					<DropdownMenu>
						<DropdownMenuTrigger asChild>
							<Button
								variant="ghost"
								size="icon"
								className="size-[44px] shrink-0"
								aria-label="Subproject actions"
							>
								<MoreVertical className="size-5" aria-hidden="true" />
							</Button>
						</DropdownMenuTrigger>
						<DropdownMenuContent align="end" className="w-44">
							<DropdownMenuItem>View / Edit</DropdownMenuItem>
							<DropdownMenuItem>Test</DropdownMenuItem>
							<DropdownMenuItem>Clone</DropdownMenuItem>
							<DropdownMenuItem>Set as In Progress</DropdownMenuItem>
						</DropdownMenuContent>
					</DropdownMenu>
				</div>
			</div>

			{/* Row 2: progress bar | indicators — indented to align with text */}
			<div className="flex items-end gap-4 pl-[56px]">
				<div className="flex min-w-0 flex-1 flex-col gap-2">
					<span className="text-sm font-semibold text-slate-800">
						Progress {subProject.progress}%
					</span>
					<div className="bg-brand-blue-100 h-2 w-full overflow-hidden rounded-full">
						<div
							className="bg-brand-blue-800 h-full rounded-full motion-safe:transition-[width] motion-safe:duration-500 motion-safe:ease-out"
							style={{ width: `${subProject.progress}%` }}
							role="progressbar"
							aria-valuenow={subProject.progress}
							aria-valuemin={0}
							aria-valuemax={100}
							aria-label={`Subproject progress: ${subProject.progress}%`}
						/>
					</div>
				</div>

				<div className="flex shrink-0 gap-3">
					<div
						className="bg-brand-blue-50 flex h-8 w-[72px] items-center justify-center gap-3 rounded-lg px-[10px]"
						title="Annotators"
					>
						<UserRound
							className="size-[18px] shrink-0 text-slate-400"
							aria-hidden="true"
						/>
						<span className="text-base font-medium text-slate-800">
							{subProject.annotators}
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
							{subProject.notifications}
						</span>
					</div>
				</div>
			</div>
		</article>
	);
}
