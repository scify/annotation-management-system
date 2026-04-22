import {
	ProjectActiveFilters,
	type ActiveFilterTag,
} from '@/components/project/project-active-filters';
import { type ProjectCardData } from '@/components/project/project-card';
import {
	ProjectFilterPanel,
	type FilterState,
	type FilterSectionKey,
} from '@/components/project/project-filter-panel';
import { ProjectList } from '@/components/project/project-list';
import {
	ProjectSortPanel,
	DEFAULT_SORT_STATE,
	type SortState,
} from '@/components/project/project-sort-panel';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { useTranslations } from '@/hooks/use-translations';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/react';
import { Plus, Search } from 'lucide-react';
import { useMemo, useState } from 'react';

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

const breadcrumbs: BreadcrumbItem[] = [
	{
		title: 'Projects',
		href: '/projects',
	},
];

export default function ProjectsIndex({ projects }: Props) {
	const { t, trans } = useTranslations();
	const allProjects = projects ?? MOCK_PROJECTS;

	const [searchQuery, setSearchQuery] = useState('');
	const [filters, setFilters] = useState<FilterState>({ tasks: [], datasets: [], states: [] });
	const [sortState, setSortState] = useState<SortState>(DEFAULT_SORT_STATE);

	const filterSections = useMemo(
		() => [
			{
				key: 'tasks' as const,
				label: t('projects.filter_task_section'),
				items: [...new Set(allProjects.map((p) => p.tags[0]))],
				searchable: true,
			},
			{
				key: 'datasets' as const,
				label: t('projects.filter_dataset_section'),
				items: [...new Set(allProjects.map((p) => p.tags[1]))],
				searchable: true,
			},
			{
				key: 'states' as const,
				label: t('projects.filter_state_section'),
				items: [...new Set(allProjects.map((p) => p.statusLabel))],
				searchable: false,
			},
		],
		[allProjects, t]
	);

	const toggleFilter = (section: FilterSectionKey, value: string) =>
		setFilters((prev) => {
			const current = prev[section];
			return {
				...prev,
				[section]: current.includes(value)
					? current.filter((v) => v !== value)
					: [...current, value],
			};
		});

	const clearFilters = () => setFilters({ tasks: [], datasets: [], states: [] });

	const hasActiveFilters =
		filters.tasks.length > 0 || filters.datasets.length > 0 || filters.states.length > 0;

	const activeTags = useMemo((): ActiveFilterTag[] => {
		const tags: ActiveFilterTag[] = [];
		const is = t('projects.filter_tag_is');

		if (filters.tasks.length > 0)
			tags.push({
				id: 'task',
				label: `${t('projects.filter_task_section')} ${is} (${filters.tasks.length})`,
				value: filters.tasks.join(', '),
				onRemove: () => setFilters((prev) => ({ ...prev, tasks: [] })),
			});

		if (filters.datasets.length > 0)
			tags.push({
				id: 'dataset',
				label: `${t('projects.filter_dataset_section')} ${is} (${filters.datasets.length})`,
				value: filters.datasets.join(', '),
				onRemove: () => setFilters((prev) => ({ ...prev, datasets: [] })),
			});

		if (filters.states.length > 0)
			tags.push({
				id: 'state',
				label: `${t('projects.filter_state_section')} ${is} (${filters.states.length})`,
				value: filters.states.join(', '),
				onRemove: () => setFilters((prev) => ({ ...prev, states: [] })),
			});

		if (sortState.progress !== '')
			tags.push({
				id: 'sort-progress',
				label: t('projects.sort_progress_section'),
				value:
					sortState.progress === 'ascending'
						? t('projects.sort_ascending')
						: t('projects.sort_descending'),
				onRemove: () => setSortState((prev) => ({ ...prev, progress: '' })),
			});

		if (sortState.dateCreated !== '')
			tags.push({
				id: 'sort-date-created',
				label: t('projects.sort_date_created_section'),
				value:
					sortState.dateCreated === 'recent_first'
						? t('projects.sort_recent_first')
						: t('projects.sort_older_first'),
				onRemove: () => setSortState((prev) => ({ ...prev, dateCreated: '' })),
			});

		if (sortState.dueDate !== '')
			tags.push({
				id: 'sort-due-date',
				label: t('projects.sort_due_date_section'),
				value:
					sortState.dueDate === 'recent_first'
						? t('projects.sort_recent_first')
						: t('projects.sort_older_first'),
				onRemove: () => setSortState((prev) => ({ ...prev, dueDate: '' })),
			});

		return tags;
	}, [filters, sortState, t]);

	const displayedProjects = useMemo(() => {
		let result = allProjects;

		if (searchQuery) {
			const q = searchQuery.toLowerCase();
			result = result.filter((p) => p.name.toLowerCase().includes(q));
		}
		if (filters.tasks.length > 0) {
			result = result.filter((p) => filters.tasks.includes(p.tags[0]));
		}
		if (filters.datasets.length > 0) {
			result = result.filter((p) => filters.datasets.includes(p.tags[1]));
		}
		if (filters.states.length > 0) {
			result = result.filter((p) => filters.states.includes(p.statusLabel));
		}

		const hasSort =
			sortState.progress !== '' || sortState.dateCreated !== '' || sortState.dueDate !== '';

		if (hasSort) {
			result = [...result].sort((a, b) => {
				if (sortState.progress !== '') {
					const diff = a.progress - b.progress;
					if (diff !== 0) return sortState.progress === 'ascending' ? diff : -diff;
				}
				if (sortState.dateCreated !== '') {
					const aDate = new Date(a.dateRange.split('–')[0].trim());
					const bDate = new Date(b.dateRange.split('–')[0].trim());
					const diff = aDate.getTime() - bDate.getTime();
					if (diff !== 0) return sortState.dateCreated === 'recent_first' ? -diff : diff;
				}
				if (sortState.dueDate !== '') {
					const aDate = new Date(a.dateRange.split('–')[1].trim());
					const bDate = new Date(b.dateRange.split('–')[1].trim());
					const diff = aDate.getTime() - bDate.getTime();
					if (diff !== 0) return sortState.dueDate === 'recent_first' ? -diff : diff;
				}
				return 0;
			});
		}

		return result;
	}, [allProjects, searchQuery, filters, sortState]);

	return (
		<AppLayout breadcrumbs={breadcrumbs}>
			<Head title={t('projects.title')} />
			<div className="flex flex-col gap-6 px-6 py-6">
				{/* Page header */}
				<div className="flex items-center justify-between">
					<h1 className="text-slate-800">{t('projects.title')}</h1>
					<Button className="hover:bg-brand-blue-800 h-10 font-semibold text-white">
						<Plus className="size-4" aria-hidden="true" />
						{t('projects.create_button')}
					</Button>
				</div>

				{/* Controls row: filter/sort triggers on the left, count + search on the right */}
				<div className="flex items-start gap-4">
					{/* Left: floating dropdown triggers */}
					<div className="flex w-56 shrink-0 flex-col gap-10">
						<ProjectFilterPanel
							sections={filterSections}
							selected={filters}
							onToggle={toggleFilter}
							onClear={clearFilters}
							hasActiveFilters={hasActiveFilters}
						/>
						<ProjectSortPanel
							state={sortState}
							onChange={setSortState}
							hasActiveSort={
								sortState.progress !== '' ||
								sortState.dateCreated !== '' ||
								sortState.dueDate !== ''
							}
							onClear={() => setSortState(DEFAULT_SORT_STATE)}
						/>
					</div>

					{/* Right: active tags + count + search + project list */}
					<div className="flex min-w-0 flex-1 flex-col gap-4">
						<ProjectActiveFilters tags={activeTags} />

						<div className="flex items-center justify-between gap-4">
							<p className="text-base font-medium text-slate-800">
								{trans('projects.projects_count', {
									count: displayedProjects.length,
								})}
							</p>
							<div className="relative w-64">
								<Search
									className="absolute top-1/2 left-3 size-4 -translate-y-1/2 text-slate-400"
									aria-hidden="true"
								/>
								<Input
									type="search"
									placeholder={t('projects.search_placeholder')}
									value={searchQuery}
									onChange={(e) => setSearchQuery(e.target.value)}
									className="pl-9"
									aria-label={t('projects.search_placeholder')}
								/>
							</div>
						</div>

						<ProjectList projects={displayedProjects} />
					</div>
				</div>
			</div>
		</AppLayout>
	);
}
