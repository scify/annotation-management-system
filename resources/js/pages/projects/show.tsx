import { type SubProjectListItemData } from '@/components/sub-project/sub-project-list-item';
import { Tag } from '@/components/ui/tag';
import { useTranslations } from '@/hooks/use-translations';
import AppLayout from '@/layouts/app-layout';
import { cn } from '@/lib/utils';
import { AnnotatorsTab } from '@/components/project/annotators-tab';
import { ExportTab } from '@/components/project/export-tab';
import { ManagersTab } from '@/components/project/managers-tab';
import { SubprojectsTab } from '@/components/project/subprojects-tab';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/react';
import { useState } from 'react';

interface ProjectShowData {
	id: number;
	name: string;
	tags: [string, string];
	progress: number;
	subProjects: SubProjectListItemData[];
}

interface Props {
	project?: ProjectShowData;
}

type TabKey = 'subprojects' | 'annotators' | 'managers' | 'export';

const MOCK_PROJECT: ProjectShowData = {
	id: 1,
	name: 'Project New Nov_26',
	tags: ["Explore word's meaning in medieval textes vol2", 'Text Dataset B'],
	progress: 25,
	subProjects: [
		{
			id: 1,
			name: 'SubProject New Nov_26',
			instancesRange: '200-1050',
			dateRange: 'Jan 15, 2026 – Feb 28, 2026',
			status: 'slate',
			statusLabel: 'Pending',
			progress: 75,
			annotators: 3,
			notifications: 1,
		},
		{
			id: 2,
			name: 'SubProject Test for Annotators',
			instancesRange: '200-1050',
			dateRange: 'Jan 15, 2026 – Feb 28, 2026',
			status: 'lime',
			statusLabel: 'Complete',
			progress: 75,
			annotators: 3,
			notifications: 1,
		},
		{
			id: 3,
			name: 'Overall Annotation',
			instancesRange: '200-1050',
			dateRange: 'Jan 15, 2026 – Feb 28, 2026',
			status: 'slate',
			statusLabel: 'Pending',
			progress: 75,
			annotators: 3,
			notifications: 1,
		},
	],
};

export default function ProjectShow({ project }: Props) {
	const { t } = useTranslations();
	const displayProject = project ?? MOCK_PROJECT;
	const [activeTab, setActiveTab] = useState<TabKey>('subprojects');

	const tabs: { key: TabKey; label: string; count?: number }[] = [
		{
			key: 'subprojects',
			label: t('projects.show.tab_subprojects'),
			count: displayProject.subProjects.length,
		},
		{ key: 'annotators', label: t('projects.show.tab_annotators'), count: 3 },
		{ key: 'managers', label: t('projects.show.tab_managers'), count: 3 },
		{ key: 'export', label: t('projects.show.tab_export') },
	];

	const breadcrumbs: BreadcrumbItem[] = [
		{ title: 'Projects', href: route('projects.index') },
		{ title: displayProject.name, href: route('projects.show', displayProject.id) },
	];

	return (
		<AppLayout breadcrumbs={breadcrumbs}>
			<Head title={displayProject.name} />
			<div className="flex flex-col gap-4 px-6 py-6">
				{/* Project title */}
				<h1 className="text-slate-800">{displayProject.name}</h1>

				{/* Tags */}
				<div className="flex flex-wrap gap-3">
					<Tag>
						<strong className="font-bold">{t('projects.show.tag_task')}</strong>
						<span className="ml-1">{displayProject.tags[0]}</span>
					</Tag>
					<Tag>
						<strong className="font-bold">{t('projects.show.tag_dataset')}</strong>
						<span className="ml-1">{displayProject.tags[1]}</span>
					</Tag>
				</div>

				{/* Overall progress bar */}
				<div className="flex flex-col gap-2">
					<span className="text-sm font-semibold text-slate-800">
						{t('projects.show.overall_progress')} {displayProject.progress}%
					</span>
					<div className="bg-brand-blue-100 h-3 w-full overflow-hidden rounded-full">
						<div
							className="bg-brand-blue-800 h-full rounded-full motion-safe:transition-[width] motion-safe:duration-500 motion-safe:ease-out"
							style={{ width: `${displayProject.progress}%` }}
							role="progressbar"
							aria-valuenow={displayProject.progress}
							aria-valuemin={0}
							aria-valuemax={100}
							aria-label={`Overall project progress: ${displayProject.progress}%`}
						/>
					</div>
				</div>

				{/* Tab strip */}
				<div
					className="flex h-[50px] overflow-hidden rounded-lg border border-slate-200 bg-white px-1.5 py-1"
					role="tablist"
					aria-label="Project sections"
				>
					{tabs.map((tab) => (
						<button
							key={tab.key}
							type="button"
							role="tab"
							aria-selected={activeTab === tab.key}
							aria-controls={`tabpanel-${tab.key}`}
							id={`tab-${tab.key}`}
							onClick={() => setActiveTab(tab.key)}
							className={cn(
								'flex flex-1 cursor-pointer items-center justify-center border-x border-slate-200 px-3 text-sm transition-colors',
								activeTab === tab.key
									? 'bg-slate-100 font-semibold text-slate-800'
									: 'bg-white font-medium text-slate-500 hover:bg-slate-50'
							)}
						>
							{tab.label}
							{tab.count !== undefined ? ` (${tab.count})` : ''}
						</button>
					))}
				</div>

				{/* Tab panels */}
				{activeTab === 'subprojects' && (
					<SubprojectsTab
						subProjects={displayProject.subProjects}
						onSubprojectCreated={() =>
							router.visit(route('projects.subprojects.create', displayProject.id))
						}
					/>
				)}
				{activeTab === 'annotators' && <AnnotatorsTab />}
				{activeTab === 'managers' && <ManagersTab />}
				{activeTab === 'export' && <ExportTab subProjects={displayProject.subProjects} />}
			</div>
		</AppLayout>
	);
}
