import AppLayout from '@/layouts/app-layout';
import { ProjectCard, type ProjectCardData } from '@/components/project-card';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/react';
import { useEffect } from 'react';

interface DashboardProps {
	token?: string;
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
				<h1 className="mb-5 text-slate-800">Dashboard Overview</h1>

				<section aria-labelledby="projects-heading">
					<h2 id="projects-heading" className="page-subtitle mb-5">
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
