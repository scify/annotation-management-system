import { Avatar, AvatarFallback } from '@/components/ui/avatar';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/react';
import { BellRing, FolderOpen, Folders, Users } from 'lucide-react';
import { useEffect } from 'react';

interface DashboardProps {
	token?: string;
}

interface ProjectCardData {
	id: number;
	name: string;
	dateRange: string;
	tags: [string, string];
	subprojects: number;
	annotators: number;
	notifications: number;
	progress: number;
	owner: { initials: string; username: string };
	coManagers: Array<{ initials: string; username: string }>;
}

const MOCK_PROJECTS: ProjectCardData[] = [
	{
		id: 1,
		name: 'Project New Nov_26',
		dateRange: 'Jan 15, 2026 – Feb 28, 2026',
		tags: ["Explore word's meaning in m…", 'Text Dataset B'],
		subprojects: 3,
		annotators: 3,
		notifications: 3,
		progress: 75,
		owner: { initials: 'A', username: '@akosmo' },
		coManagers: [
			{ initials: 'N', username: '@nellysav' },
			{ initials: 'N', username: '@nazpapadaki' },
			{ initials: 'M', username: '@mgiannis' },
			{ initials: 'P', username: '@ppetros' },
		],
	},
	{
		id: 2,
		name: 'Medieval Texts Vol. 1',
		dateRange: 'Feb 1, 2026 – Mar 15, 2026',
		tags: ['Manuscript annotation', 'Latin Corpus A'],
		subprojects: 2,
		annotators: 5,
		notifications: 1,
		progress: 40,
		owner: { initials: 'N', username: '@nellysav' },
		coManagers: [{ initials: 'A', username: '@akosmo' }],
	},
	{
		id: 3,
		name: 'Sentiment Analysis Q1',
		dateRange: 'Jan 20, 2026 – Apr 30, 2026',
		tags: ['Sentiment labeling', 'News Dataset C'],
		subprojects: 4,
		annotators: 8,
		notifications: 0,
		progress: 90,
		owner: { initials: 'P', username: '@ppetros' },
		coManagers: [
			{ initials: 'A', username: '@akosmo' },
			{ initials: 'N', username: '@nellysav' },
		],
	},
	{
		id: 4,
		name: 'Named Entity Tagging',
		dateRange: 'Mar 1, 2026 – Mar 31, 2026',
		tags: ['NER annotation', 'Bio Medical Set'],
		subprojects: 1,
		annotators: 2,
		notifications: 2,
		progress: 25,
		owner: { initials: 'M', username: '@mgiannis' },
		coManagers: [{ initials: 'N', username: '@nazpapadaki' }],
	},
	{
		id: 5,
		name: 'Image Labelling Batch3',
		dateRange: 'Feb 15, 2026 – May 15, 2026',
		tags: ['Object detection', 'Visual Dataset D'],
		subprojects: 3,
		annotators: 6,
		notifications: 4,
		progress: 60,
		owner: { initials: 'N', username: '@nazpapadaki' },
		coManagers: [
			{ initials: 'P', username: '@ppetros' },
			{ initials: 'M', username: '@mgiannis' },
			{ initials: 'A', username: '@akosmo' },
		],
	},
];

function UserAvatar({ initials }: { initials: string }) {
	return (
		<Avatar className="size-[22px] shrink-0">
			<AvatarFallback className="rounded-full bg-[#a8baed] text-[10px] font-semibold text-white">
				{initials}
			</AvatarFallback>
		</Avatar>
	);
}

function ProjectCard({ project }: { project: ProjectCardData }) {
	const visibleCoManagers = project.coManagers.slice(0, 2);
	const extraCount = project.coManagers.length - 2;

	return (
		<article className="flex flex-col gap-7 rounded-[16px] border border-[#e2e8f0] bg-white p-5">
			{/* Top section */}
			<div className="flex flex-col gap-4">
				{/* Icon + name + date */}
				<div className="flex flex-col gap-3">
					<div className="flex size-[42px] items-center justify-center rounded-lg bg-[#f2f5fd]">
						<FolderOpen className="size-6 text-[#4d6fd1]" aria-hidden="true" />
					</div>
					<div>
						<p className="text-xl leading-9 font-medium text-[#1e293b]">
							{project.name}
						</p>
						<p className="text-sm text-[#475569]">{project.dateRange}</p>
					</div>
				</div>

				{/* Tag chips */}
				<div className="flex gap-2.5">
					<span className="flex h-8 min-w-0 flex-1 items-center truncate rounded-md bg-[#d9e1f8] px-[10px] text-sm font-medium text-[#1e293b]">
						{project.tags[0]}
					</span>
					<span className="flex h-8 shrink-0 items-center rounded-md bg-[#d9e1f8] px-[10px] text-sm font-medium whitespace-nowrap text-[#1e293b]">
						{project.tags[1]}
					</span>
				</div>

				{/* Indicator chips: subprojects / annotators / notifications */}
				<div className="flex gap-3">
					<div
						className="flex h-8 flex-1 items-center justify-center gap-4 rounded-lg bg-[#f2f5fd] px-[10px]"
						title="Subprojects"
					>
						<Folders
							className="size-[18px] shrink-0 text-[#475569]"
							aria-hidden="true"
						/>
						<span className="text-base font-medium text-[#1e293b]">
							{project.subprojects}
						</span>
					</div>
					<div
						className="flex h-8 flex-1 items-center justify-center gap-4 rounded-lg bg-[#f2f5fd] px-[10px]"
						title="Annotators"
					>
						<Users className="size-[18px] shrink-0 text-[#475569]" aria-hidden="true" />
						<span className="text-base font-medium text-[#1e293b]">
							{project.annotators}
						</span>
					</div>
					<div
						className="flex h-8 flex-1 items-center justify-center gap-4 rounded-lg bg-[#f2f5fd] px-[10px]"
						title="Notifications"
					>
						<BellRing
							className="size-[18px] shrink-0 text-[#475569]"
							aria-hidden="true"
						/>
						<span className="text-base font-medium text-[#1e293b]">
							{project.notifications}
						</span>
					</div>
				</div>
			</div>

			{/* Bottom section */}
			<div className="flex flex-col gap-4">
				{/* Progress bar */}
				<div className="flex flex-col gap-2">
					<span className="text-sm font-semibold text-[#1e293b]">
						Overall Progress {project.progress}%
					</span>
					<div className="h-3 w-full overflow-hidden rounded-full bg-[#d9e1f8]">
						<div
							className="h-full rounded-full bg-[#3d5bb3] motion-safe:transition-[width] motion-safe:duration-500 motion-safe:ease-out"
							style={{ width: `${project.progress}%` }}
							role="progressbar"
							aria-valuenow={project.progress}
							aria-valuemin={0}
							aria-valuemax={100}
							aria-label={`Project progress: ${project.progress}%`}
						/>
					</div>
				</div>

				{/* Owner + Co-managers */}
				<div className="flex gap-7">
					<div className="flex flex-col gap-2">
						<span className="text-xs font-semibold text-[#475569]">Owner:</span>
						<div className="flex items-center gap-1">
							<UserAvatar initials={project.owner.initials} />
							<span className="text-[0.75rem] text-[#475569]">
								{project.owner.username}
							</span>
						</div>
					</div>

					<div className="flex flex-col gap-2">
						<span className="text-xs font-semibold text-[#475569]">Co-managers:</span>
						<div className="flex flex-wrap items-center gap-1">
							{visibleCoManagers.map((cm) => (
								<div key={cm.username} className="flex items-center gap-1">
									<UserAvatar initials={cm.initials} />
									<span className="text-[0.75rem] text-[#475569]">
										{cm.username}
									</span>
								</div>
							))}
							{extraCount > 0 && (
								<span className="text-[0.75rem] text-[#475569]">+{extraCount}</span>
							)}
						</div>
					</div>
				</div>

				{/* View Project */}
				<Button className="h-10 w-full bg-[#4d6fd1] font-semibold text-white hover:bg-[#3d5bb3]">
					View Project
				</Button>
			</div>
		</article>
	);
}

export default function Dashboard({ token }: DashboardProps) {
	useEffect(() => {
		if (token && token !== '') {
			localStorage.setItem('auth_token', token);
		}
	}, [token]);

	const breadcrumbs: BreadcrumbItem[] = [
		{
			title: 'Dashboard',
			href: '/dashboard',
		},
	];

	return (
		<AppLayout breadcrumbs={breadcrumbs}>
			<Head title="Dashboard" />
			<div className="flex flex-col gap-8 px-6 py-6">
				<h1 className="text-2xl font-semibold text-[#1e293b]">Dashboard Overview</h1>

				<section aria-labelledby="projects-heading">
					<h2 id="projects-heading" className="mb-4 text-sm font-medium text-[#475569]">
						Active Annotation Projects
					</h2>
					<div className="grid gap-6 sm:grid-cols-2 xl:grid-cols-3">
						{MOCK_PROJECTS.map((project) => (
							<ProjectCard key={project.id} project={project} />
						))}
					</div>
				</section>
			</div>
		</AppLayout>
	);
}
