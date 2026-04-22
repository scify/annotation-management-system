import AppLayout from '@/layouts/app-layout';
import { ProjectCard, type ProjectCardData } from '@/components/project/project-card';
import { Avatar, AvatarFallback } from '@/components/ui/avatar';
import {
	Table,
	TableBody,
	TableCell,
	TableHead,
	TableHeader,
	TableRow,
} from '@/components/ui/table';
import { WorkloadGauge } from '@/components/workload-gauge';
import { useTranslations } from '@/hooks/use-translations';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/react';
import { useEffect } from 'react';

interface DashboardProps {
	token?: string;
}

interface AnnotatorRowData {
	id: number;
	initials: string;
	username: string;
	activeProjects: number;
	workload: number;
	progress: number;
}

const MOCK_PROJECTS: ProjectCardData[] = [
	{
		id: 1,
		name: 'Project New Nov_26',
		dateRange: 'Jan 15, 2026 – Feb 28, 2026',
		status: 'yellow',
		statusLabel: 'In Progress',
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
		status: 'lime',
		statusLabel: 'Complete',
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
		status: 'slate',
		statusLabel: 'Pending',
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
		status: 'yellow',
		statusLabel: 'In Progress',
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
		status: 'yellow',
		statusLabel: 'In Progress',
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

const MOCK_ANNOTATORS: AnnotatorRowData[] = [
	{ id: 1, initials: 'A', username: '@akosmo', activeProjects: 23, workload: 85, progress: 75 },
	{ id: 2, initials: 'N', username: '@nellysav', activeProjects: 12, workload: 55, progress: 55 },
	{
		id: 3,
		initials: 'N',
		username: '@nazpapadaki',
		activeProjects: 8,
		workload: 20,
		progress: 30,
	},
	{ id: 4, initials: 'M', username: '@mgiannis', activeProjects: 19, workload: 70, progress: 82 },
	{ id: 5, initials: 'P', username: '@ppetros', activeProjects: 15, workload: 45, progress: 60 },
	{
		id: 6,
		initials: 'E',
		username: '@ekonstantinidis',
		activeProjects: 5,
		workload: 15,
		progress: 20,
	},
	{
		id: 7,
		initials: 'S',
		username: '@sspyridakis',
		activeProjects: 21,
		workload: 92,
		progress: 90,
	},
	{
		id: 8,
		initials: 'D',
		username: '@dpapadopoulos',
		activeProjects: 10,
		workload: 60,
		progress: 45,
	},
	{
		id: 9,
		initials: 'K',
		username: '@kpapadimitriou',
		activeProjects: 3,
		workload: 25,
		progress: 15,
	},
];

export default function Dashboard({ token }: DashboardProps) {
	const { t } = useTranslations();

	useEffect(() => {
		if (token && token !== '') {
			localStorage.setItem('auth_token', token);
		}
	}, [token]);

	const breadcrumbs: BreadcrumbItem[] = [
		{
			title: t('navbar.dashboard'),
			href: '/dashboard',
		},
	];

	return (
		<AppLayout breadcrumbs={breadcrumbs}>
			<Head title="Dashboard" />
			<div className="flex flex-col gap-8 px-6 py-6">
				<h1 className="mb-5 text-slate-800">{t('projects.dashboard.overview_title')}</h1>

				<section aria-labelledby="projects-heading">
					<h2 id="projects-heading" className="page-subtitle mb-5">
						{t('projects.dashboard.active_projects_heading')}
					</h2>
					<div className="grid gap-6 sm:grid-cols-2 xl:grid-cols-3">
						{MOCK_PROJECTS.map((project) => (
							<ProjectCard key={project.id} project={project} />
						))}
					</div>
				</section>

				<section aria-labelledby="annotators-heading">
					<h2 id="annotators-heading" className="page-subtitle mb-5">
						{t('projects.dashboard.annotators_overview_heading')}
					</h2>
					<div className="overflow-hidden rounded-xl">
						<Table>
							<TableHeader>
								<TableRow className="bg-brand-blue-100 hover:bg-brand-blue-100 border-b border-slate-300">
									<TableHead className="pl-12 text-sm font-semibold text-slate-800">
										{t('projects.dashboard.table_username')}
									</TableHead>
									<TableHead className="text-right text-sm font-semibold text-slate-800">
										{t('projects.dashboard.table_active_projects')}
									</TableHead>
									<TableHead className="text-center text-sm font-semibold text-slate-800">
										{t('projects.dashboard.table_remaining_workload')}
									</TableHead>
									<TableHead className="text-center text-sm font-semibold text-slate-800">
										{t('projects.dashboard.table_progress')}
									</TableHead>
								</TableRow>
							</TableHeader>
							<TableBody>
								{MOCK_ANNOTATORS.map((annotator) => (
									<TableRow
										key={annotator.id}
										className="hover:bg-brand-blue-50 h-14 border-b border-slate-300 bg-white"
									>
										<TableCell className="pl-4">
											<div className="flex items-center gap-3">
												<Avatar className="size-[29px] shrink-0">
													<AvatarFallback className="bg-brand-blue-300 rounded-full text-sm font-semibold text-white">
														{annotator.initials}
													</AvatarFallback>
												</Avatar>
												<span className="text-base font-medium text-slate-800">
													{annotator.username}
												</span>
											</div>
										</TableCell>
										<TableCell className="text-right">
											<span className="text-base font-medium text-slate-800">
												{annotator.activeProjects}
											</span>
										</TableCell>
										<TableCell className="text-center">
											<div className="flex justify-center">
												<WorkloadGauge value={annotator.workload} />
											</div>
										</TableCell>
										<TableCell>
											<div className="flex flex-col items-end gap-1.5">
												<span className="text-base font-medium text-slate-800">
													{annotator.progress}%
												</span>
												<div className="bg-brand-blue-100 h-[5px] w-full overflow-hidden rounded-full">
													<div
														className="bg-brand-blue-800 h-full rounded-full motion-safe:transition-[width] motion-safe:duration-500 motion-safe:ease-out"
														style={{ width: `${annotator.progress}%` }}
														role="progressbar"
														aria-valuenow={annotator.progress}
														aria-valuemin={0}
														aria-valuemax={100}
														aria-label={`Progress: ${annotator.progress}%`}
													/>
												</div>
											</div>
										</TableCell>
									</TableRow>
								))}
							</TableBody>
						</Table>
					</div>
				</section>
			</div>
		</AppLayout>
	);
}
