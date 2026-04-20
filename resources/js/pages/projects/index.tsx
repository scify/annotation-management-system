import { ProjectList } from '@/components/project/project-list';
import { type ProjectCardData } from '@/components/project/project-card';
import { Button } from '@/components/ui/button';
import { useTranslations } from '@/hooks/use-translations';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/react';
import { Plus } from 'lucide-react';

interface Props {
	projects?: ProjectCardData[];
}

const MOCK_PROJECTS: ProjectCardData[] = [
	{
		id: 1,
		name: 'Project New Nov_26',
		dateRange: 'Jan 15, 2026 – Feb 28, 2026',
		status: 'yellow',
		statusLabel: 'In Progress',
		tags: ["Explore word's meaning in medieval textes", 'Text Dataset B'],
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
		status: 'pink',
		statusLabel: 'Overdue',
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

const breadcrumbs: BreadcrumbItem[] = [
	{
		title: 'Projects',
		href: '/projects',
	},
];

export default function ProjectsIndex({ projects }: Props) {
	const { t } = useTranslations();
	const displayProjects = projects ?? MOCK_PROJECTS;

	return (
		<AppLayout breadcrumbs={breadcrumbs}>
			<Head title={t('projects.title')} />
			<div className="flex flex-col gap-8 px-6 py-6">
				{/* Page header */}
				<div className="flex items-center justify-between">
					<h1 className="text-slate-800">{t('projects.title')}</h1>
					<Button className="hover:bg-brand-blue-800 h-10 font-semibold text-white">
						<Plus className="size-4" aria-hidden="true" />
						{t('projects.create_button')}
					</Button>
				</div>

				{/* Filter placeholder */}
				<p className="font-medium text-slate-800">{t('projects.filter')}</p>

				{/* Project list */}
				<ProjectList projects={displayProjects} />
			</div>
		</AppLayout>
	);
}
