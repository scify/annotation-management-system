import { Input } from '@/components/ui/input';
import {
	Select,
	SelectContent,
	SelectItem,
	SelectTrigger,
	SelectValue,
} from '@/components/ui/select';
import { useTranslations } from '@/hooks/use-translations';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/react';
import { Search } from 'lucide-react';
import { useMemo, useState } from 'react';
import { AnnotatorRow } from './components/annotator-row';
import type { MonitorAnnotator } from './types';

const MOCK_ANNOTATORS: MonitorAnnotator[] = [
	{
		id: 1,
		username: '@Nazpapad',
		initials: 'N',
		status: 'active',
		activeSubprojects: 23,
		activeProjects: 2,
		remainingWorkload: 72,
		progress: 75,
		projects: [
			{
				id: 1,
				name: 'Project New Nov_26',
				dateRange: 'Jan 15, 2026 – Feb 15, 2026',
				status: 'in_progress',
				owner: '@akosmo',
				coManagers: ['@nellysav', '@nazpapadaki', '@georgiou', '@mariap'],
				overallProgress: 25,
				subprojects: [
					{
						id: 1,
						name: 'Text Annotation Batch March _2026',
						dateRange: 'Jan 15, 2026 – Feb 28, 2026',
						remainingWorkload: 100,
						progress: 100,
						state: 'in_progress',
					},
					{
						id: 2,
						name: 'Text Annotation Batch March _2026',
						dateRange: 'Jan 15, 2026 – Feb 28, 2026',
						remainingWorkload: 100,
						progress: 100,
						state: 'in_progress',
					},
					{
						id: 3,
						name: 'Text Annotation Batch March _2026',
						dateRange: 'Jan 15, 2026 – Feb 28, 2026',
						remainingWorkload: 100,
						progress: 100,
						state: 'in_progress',
					},
				],
			},
			{
				id: 2,
				name: 'Text annotation – describe the meaning of the word',
				dateRange: 'Jan 15, 2026 – Feb 15, 2026',
				status: 'in_progress',
				owner: '@akosmo',
				coManagers: ['@nellysav', '@nazpapadaki', '@georgiou', '@mariap'],
				overallProgress: 25,
				subprojects: [
					{
						id: 4,
						name: 'Semantic Labelling Batch A',
						dateRange: 'Jan 20, 2026 – Feb 10, 2026',
						remainingWorkload: 60,
						progress: 40,
						state: 'in_progress',
					},
					{
						id: 5,
						name: 'Semantic Labelling Batch B',
						dateRange: 'Jan 20, 2026 – Feb 10, 2026',
						remainingWorkload: 80,
						progress: 20,
						state: 'pending',
					},
				],
			},
			{
				restricted: true,
				owner: '@akosmo',
				assignedCount: 5,
				assignedTo: '@Nazpapad',
			},
		],
	},
	{
		id: 2,
		username: '@NellySav',
		initials: 'N',
		status: 'active',
		activeSubprojects: 12,
		activeProjects: 5,
		remainingWorkload: 74,
		progress: 75,
		projects: [
			{
				id: 3,
				name: 'Audio Transcription Sprint',
				dateRange: 'Feb 1, 2026 – Mar 1, 2026',
				status: 'in_progress',
				owner: '@akosmo',
				coManagers: ['@nazpapadaki'],
				overallProgress: 75,
				subprojects: [
					{
						id: 6,
						name: 'Audio Batch Q1',
						dateRange: 'Feb 1, 2026 – Feb 15, 2026',
						remainingWorkload: 70,
						progress: 80,
						state: 'in_progress',
					},
				],
			},
		],
	},
	{
		id: 3,
		username: '@fpapastergiou',
		initials: 'F',
		status: 'active',
		activeSubprojects: 23,
		activeProjects: 2,
		remainingWorkload: 52,
		progress: 50,
		projects: [
			{
				id: 4,
				name: 'Image Classification Round 3',
				dateRange: 'Jan 10, 2026 – Mar 10, 2026',
				status: 'in_progress',
				owner: '@akosmo',
				coManagers: ['@nellysav'],
				overallProgress: 50,
				subprojects: [
					{
						id: 7,
						name: 'Image Batch Jan',
						dateRange: 'Jan 10, 2026 – Feb 10, 2026',
						remainingWorkload: 50,
						progress: 50,
						state: 'in_progress',
					},
				],
			},
		],
	},
	{
		id: 4,
		username: '@vasilisgiannakopolos',
		initials: 'V',
		status: 'active',
		activeSubprojects: 23,
		activeProjects: 2,
		remainingWorkload: 28,
		progress: 25,
		projects: [
			{
				id: 5,
				name: 'Sentiment Analysis Phase 2',
				dateRange: 'Jan 5, 2026 – Feb 28, 2026',
				status: 'in_progress',
				owner: '@akosmo',
				coManagers: ['@nellysav', '@nazpapadaki'],
				overallProgress: 25,
				subprojects: [
					{
						id: 8,
						name: 'Sentiment Batch Alpha',
						dateRange: 'Jan 5, 2026 – Feb 5, 2026',
						remainingWorkload: 28,
						progress: 25,
						state: 'in_progress',
					},
				],
			},
		],
	},
	{
		id: 5,
		username: '@paulis',
		initials: 'P',
		status: 'active',
		activeSubprojects: 23,
		activeProjects: 2,
		remainingWorkload: 72,
		progress: 75,
		projects: [
			{
				id: 6,
				name: 'NER Annotation Campaign',
				dateRange: 'Jan 20, 2026 – Mar 20, 2026',
				status: 'in_progress',
				owner: '@akosmo',
				coManagers: ['@nellysav'],
				overallProgress: 75,
				subprojects: [
					{
						id: 9,
						name: 'NER Batch Jan',
						dateRange: 'Jan 20, 2026 – Feb 20, 2026',
						remainingWorkload: 72,
						progress: 75,
						state: 'in_progress',
					},
				],
			},
		],
	},
];

type SortDir = 'asc' | 'desc' | 'none';

const GRID_COLS = 'grid-cols-[52px_194px_150px_1fr_1fr_156px_195px_56px]';

export default function MonitorIndex() {
	const { t } = useTranslations();

	const breadcrumbs: BreadcrumbItem[] = [
		{ title: t('navbar.dashboard'), href: '/dashboard' },
		{ title: t('monitor.page_title'), href: route('monitor.index') },
	];

	const [search, setSearch] = useState('');
	const [sortNameDir, setSortNameDir] = useState<SortDir>('none');
	const [sortWorkloadDir, setSortWorkloadDir] = useState<SortDir>('none');
	const [lastSort, setLastSort] = useState<'name' | 'workload' | null>(null);

	const filtered = useMemo(() => {
		let result = [...MOCK_ANNOTATORS];

		if (search.trim()) {
			const q = search.toLowerCase();
			result = result.filter((a) => a.username.toLowerCase().includes(q));
		}

		if (lastSort === 'name' && sortNameDir !== 'none') {
			result.sort((a, b) =>
				sortNameDir === 'asc'
					? a.username.localeCompare(b.username)
					: b.username.localeCompare(a.username)
			);
		} else if (lastSort === 'workload' && sortWorkloadDir !== 'none') {
			result.sort((a, b) =>
				sortWorkloadDir === 'asc'
					? a.remainingWorkload - b.remainingWorkload
					: b.remainingWorkload - a.remainingWorkload
			);
		}

		return result;
	}, [search, sortNameDir, sortWorkloadDir, lastSort]);

	return (
		<AppLayout breadcrumbs={breadcrumbs}>
			<Head title={t('monitor.page_title')} />

			<div className="px-6 py-6">
				<hgroup className="mb-6">
					<h1 className="font-heading text-3xl font-bold text-slate-800">
						{t('monitor.page_title')}
					</h1>
				</hgroup>

				{/* Filter bar */}
				<div className="mb-4 flex items-center justify-between gap-4">
					<div className="flex gap-4">
						<Select
							value={sortNameDir}
							onValueChange={(v) => {
								setSortNameDir(v as SortDir);
								setLastSort('name');
							}}
						>
							<SelectTrigger className="w-[200px]">
								<SelectValue />
							</SelectTrigger>
							<SelectContent>
								<SelectItem value="none">{t('monitor.sort_by_name')}</SelectItem>
								<SelectItem value="asc">{t('monitor.sort_name_asc')}</SelectItem>
								<SelectItem value="desc">{t('monitor.sort_name_desc')}</SelectItem>
							</SelectContent>
						</Select>

						<Select
							value={sortWorkloadDir}
							onValueChange={(v) => {
								setSortWorkloadDir(v as SortDir);
								setLastSort('workload');
							}}
						>
							<SelectTrigger className="w-[200px]">
								<SelectValue />
							</SelectTrigger>
							<SelectContent>
								<SelectItem value="none">
									{t('monitor.sort_by_workload')}
								</SelectItem>
								<SelectItem value="asc">
									{t('monitor.sort_workload_asc')}
								</SelectItem>
								<SelectItem value="desc">
									{t('monitor.sort_workload_desc')}
								</SelectItem>
							</SelectContent>
						</Select>
					</div>

					<div className="relative w-72">
						<Search
							className="pointer-events-none absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-slate-400"
							aria-hidden="true"
						/>
						<Input
							type="search"
							placeholder={t('monitor.search_placeholder')}
							value={search}
							onChange={(e) => setSearch(e.target.value)}
							className="pl-9"
							aria-label={t('monitor.search_placeholder')}
						/>
					</div>
				</div>

				{/* Table */}
				<div
					role="table"
					aria-label={t('monitor.page_title')}
					className="overflow-hidden rounded-xl border border-slate-300"
				>
					{/* Header */}
					<div role="rowgroup">
						<div
							role="row"
							className={`bg-brand-blue-100 grid items-center rounded-tl-xl rounded-tr-xl border-b border-slate-300 ${GRID_COLS}`}
						>
							<div
								role="columnheader"
								className="col-span-2 py-2.5 pl-4 text-sm font-semibold text-slate-800"
							>
								{t('monitor.col_username')}
							</div>
							<div
								role="columnheader"
								className="py-2.5 text-center text-sm font-semibold text-slate-800"
							>
								{t('monitor.col_status')}
							</div>
							<div
								role="columnheader"
								className="py-2.5 pr-4 text-right text-sm font-semibold text-slate-800"
							>
								{t('monitor.col_active_subprojects')}
							</div>
							<div
								role="columnheader"
								className="py-2.5 pr-4 text-right text-sm font-semibold text-slate-800"
							>
								{t('monitor.col_active_projects')}
							</div>
							<div
								role="columnheader"
								className="py-2.5 text-center text-sm font-semibold text-slate-800"
							>
								{t('monitor.col_rem_workload')}
							</div>
							<div
								role="columnheader"
								className="py-2.5 text-center text-sm font-semibold text-slate-800"
							>
								{t('monitor.col_progress')}
							</div>
							<div role="columnheader" className="sr-only">
								{t('monitor.expand_row')}
							</div>
						</div>
					</div>

					{/* Body */}
					<div role="rowgroup">
						{filtered.length === 0 ? (
							<div className="py-12 text-center text-sm text-slate-500">
								{search ? `No annotators matching "${search}"` : 'No annotators.'}
							</div>
						) : (
							filtered.map((annotator) => (
								<AnnotatorRow key={annotator.id} annotator={annotator} />
							))
						)}
					</div>
				</div>
			</div>
		</AppLayout>
	);
}
