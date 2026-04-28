import { type ProjectAnnotatorRowData } from '@/components/annotator/annotators-table';
import { SubprojectAnnotatorsPanel } from '@/components/sub-project/subproject-annotators-panel';
import { type StatusVariant } from '@/components/project/project-card';
import {
	PriorityBadge,
	type SubprojectPriority,
	type SubmissionMode,
	ToggleSwitch,
} from '@/components/sub-project/configuration-step';
import { type DatasetInfo } from '@/components/sub-project/select-dataset-subset-step';
import {
	AnnotationsTab,
	type InstanceAnnotationRow,
} from '@/components/sub-project/annotations-tab';
import {
	DateRangePickerButton,
	formatDateRange,
	type DateRangeValue,
} from '@/components/ui/date-range-picker-button';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import {
	Select,
	SelectContent,
	SelectItem,
	SelectTrigger,
	SelectValue,
} from '@/components/ui/select';
import { Tag } from '@/components/ui/tag';
import { useTranslations } from '@/hooks/use-translations';
import AppLayout from '@/layouts/app-layout';
import { cn } from '@/lib/utils';
import { type BreadcrumbItem } from '@/types';
import { CalendarDate } from '@internationalized/date';
import { Head } from '@inertiajs/react';
import { CircleAlert } from 'lucide-react';
import { useState } from 'react';

// ── Mock data ─────────────────────────────────────────────────────────────────

const MOCK_PROJECT = { id: 1, name: 'Project New Nov_26' };

const MOCK_SUBPROJECT = {
	id: 1,
	name: 'Subproject New Nov_26',
	status: 'slate' as StatusVariant,
	statusLabel: 'Pending',
	progress: 25,
	fromInstance: 57,
	toInstance: 2350,
	shuffle: true,
	priority: 'medium' as SubprojectPriority,
	dateRange: {
		start: new CalendarDate(2026, 11, 23),
		end: new CalendarDate(2026, 12, 15),
	} satisfies DateRangeValue,
	minAnnotationsEnabled: false,
	minAnnotations: 1,
	flexibleBrowsing: false,
	submissionMode: 'auto' as SubmissionMode,
};

const MOCK_DATASET: DatasetInfo = { name: 'Image Dataset B', totalInstances: 10_000 };

const MOCK_ANNOTATORS: ProjectAnnotatorRowData[] = [
	{
		id: 1,
		initials: 'G',
		username: '@ggiannakopulos',
		projects: 23,
		subprojects: 23,
		workload: 85,
		progress: 75,
	},
	{
		id: 2,
		initials: 'N',
		username: '@nellisavrani',
		projects: 12,
		subprojects: 4,
		workload: 30,
		progress: 75,
	},
	{
		id: 3,
		initials: 'A',
		username: '@akosmo',
		projects: 8,
		subprojects: 6,
		workload: 50,
		progress: 60,
	},
];

const MOCK_ANNOTATION_ROWS: InstanceAnnotationRow[] = [
	{
		instanceId: 57,
		annotationProgress: { completed: 2, total: 3 },
		agreement: 'high',
		annotations: [
			{
				id: 1,
				annotation: 'one word',
				assignedTo: { username: '@nellisav', role: 'annotator' },
				annotatedBy: { username: '@ggiannakopoulos', role: 'manager' },
				timestamp: 'Jan 22, 2026 10:30am',
				confidence: 'high',
			},
			{
				id: 2,
				annotation: 'Yes',
				assignedTo: { username: '@nazelipapad', role: 'annotator' },
				annotatedBy: { username: '@akosmo', role: 'manager' },
				timestamp: 'Jan 22, 2026 10:30am',
				confidence: 'low',
			},
		],
	},
	{
		instanceId: 58,
		annotationProgress: { completed: 2, total: 3 },
		agreement: 'high',
		annotations: [],
	},
	{
		instanceId: 59,
		annotationProgress: { completed: 2, total: 3 },
		agreement: 'medium',
		annotations: [],
	},
];

// ── Types ─────────────────────────────────────────────────────────────────────

type TabKey = 'overview' | 'annotators' | 'annotations';

interface SubprojectData {
	id: number;
	name: string;
	status: StatusVariant;
	statusLabel: string;
	progress: number;
	fromInstance: number;
	toInstance: number;
	shuffle: boolean;
	priority: SubprojectPriority | null;
	dateRange: DateRangeValue | null;
	minAnnotationsEnabled: boolean;
	minAnnotations: number;
	flexibleBrowsing: boolean;
	submissionMode: SubmissionMode;
}

interface Props {
	project?: { id: number; name: string };
	subproject?: SubprojectData;
	dataset?: DatasetInfo;
	annotators?: ProjectAnnotatorRowData[];
	annotationRows?: InstanceAnnotationRow[];
}

// ── Page ──────────────────────────────────────────────────────────────────────

export default function EditSubproject({
	project,
	subproject,
	dataset,
	annotators,
	annotationRows,
}: Props) {
	const { t } = useTranslations();

	const displayProject = project ?? MOCK_PROJECT;
	const displaySubproject = subproject ?? MOCK_SUBPROJECT;
	const displayDataset = dataset ?? MOCK_DATASET;
	const displayAnnotators = annotators ?? MOCK_ANNOTATORS;
	const displayAnnotationRows = annotationRows ?? MOCK_ANNOTATION_ROWS;

	// ── Form state (pre-populated from subproject) ────────────────────────────
	const [name, setName] = useState(displaySubproject.name);
	const [fromInstance, setFromInstance] = useState(displaySubproject.fromInstance);
	const [toInstance, setToInstance] = useState(displaySubproject.toInstance);
	const [shuffle, setShuffle] = useState(displaySubproject.shuffle);
	const [priority, setPriority] = useState<SubprojectPriority | null>(displaySubproject.priority);
	const [dateRange, setDateRange] = useState<DateRangeValue | null>(displaySubproject.dateRange);
	const [minAnnotationsEnabled, setMinAnnotationsEnabled] = useState(
		displaySubproject.minAnnotationsEnabled
	);
	const [minAnnotations, setMinAnnotations] = useState(displaySubproject.minAnnotations);
	const [flexibleBrowsing, setFlexibleBrowsing] = useState(displaySubproject.flexibleBrowsing);
	const [submissionMode, setSubmissionMode] = useState<SubmissionMode>(
		displaySubproject.submissionMode
	);

	// ── Annotators tab state ──────────────────────────────────────────────────
	const initialAnnotatorIds = new Set(displayAnnotators.map((a) => a.id));
	const [selectedAnnotatorIds, setSelectedAnnotatorIds] =
		useState<Set<number>>(initialAnnotatorIds);

	// ── Tabs ──────────────────────────────────────────────────────────────────
	const [activeTab, setActiveTab] = useState<TabKey>('overview');

	const tabs: { key: TabKey; label: string }[] = [
		{ key: 'overview', label: t('sub-projects.edit.tab_overview_settings') },
		{ key: 'annotators', label: t('sub-projects.edit.tab_annotators') },
		{ key: 'annotations', label: t('sub-projects.edit.tab_annotations') },
	];

	const breadcrumbs: BreadcrumbItem[] = [
		{ title: t('projects.title'), href: route('projects.index') },
		{ title: displayProject.name, href: route('projects.show', displayProject.id) },
		{ title: displaySubproject.name, href: '#' },
	];

	const scheduledFor = formatDateRange(dateRange);

	function handleSave() {
		// TODO: submit form via Inertia
	}

	function handleSelectionChange(id: number, checked: boolean) {
		setSelectedAnnotatorIds((prev) => {
			const next = new Set(prev);
			if (checked) {
				next.add(id);
			} else {
				next.delete(id);
			}
			return next;
		});
	}

	return (
		<AppLayout breadcrumbs={breadcrumbs}>
			<Head title={t('sub-projects.edit.page_title')} />

			<div className="flex flex-col gap-4 px-6 py-6">
				{/* ── Subproject header ─────────────────────────────────── */}
				<div className="flex flex-col gap-3">
					<div className="flex flex-wrap items-center gap-3">
						<h1 className="text-3xl font-light text-slate-800">{name}</h1>
						<Badge variant={displaySubproject.status}>
							{displaySubproject.statusLabel}
						</Badge>
					</div>

					{/* Metadata tags */}
					<div className="flex flex-wrap gap-3">
						<Tag>
							<strong className="font-bold">
								{t('sub-projects.edit.scheduled_for')}
							</strong>
							<span className="ml-1">{scheduledFor ?? '—'}</span>
						</Tag>
						<Tag>
							<strong className="font-bold">
								{t('sub-projects.edit.date_started')}
							</strong>
							<span className="ml-1">{t('sub-projects.edit.not_started')}</span>
						</Tag>
						<Tag>
							<strong className="font-bold">
								{t('sub-projects.edit.date_completed')}
							</strong>
							<span className="ml-1">{t('sub-projects.edit.not_completed')}</span>
						</Tag>
					</div>

					{/* Overall progress bar */}
					<div className="flex flex-col gap-2">
						<span className="text-sm font-semibold text-slate-800">
							{t('sub-projects.edit.overall_progress')} {displaySubproject.progress}%
						</span>
						<div className="bg-brand-blue-100 h-3 w-full overflow-hidden rounded-full">
							<div
								className="bg-brand-blue-800 h-full rounded-full motion-safe:transition-[width] motion-safe:duration-500 motion-safe:ease-out"
								style={{ width: `${displaySubproject.progress}%` }}
								role="progressbar"
								aria-valuenow={displaySubproject.progress}
								aria-valuemin={0}
								aria-valuemax={100}
								aria-label={`${t('sub-projects.edit.overall_progress')} ${displaySubproject.progress}%`}
							/>
						</div>
					</div>
				</div>

				{/* ── Tab strip ─────────────────────────────────────────── */}
				<div
					className="flex h-[50px] overflow-hidden rounded-lg border border-slate-200 bg-white px-1.5 py-1"
					role="tablist"
					aria-label={t('sub-projects.edit.tab_aria_label')}
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
									? 'bg-white font-semibold text-slate-800'
									: 'bg-slate-50 font-medium text-slate-500 hover:bg-slate-100'
							)}
						>
							{tab.label}
						</button>
					))}
				</div>

				{/* ── Overview & Settings panel ─────────────────────────── */}
				{activeTab === 'overview' && (
					<section
						id="tabpanel-overview"
						role="tabpanel"
						aria-labelledby="tab-overview"
						className="flex flex-col gap-5"
					>
						{/* Section header */}
						<div className="flex items-center justify-between">
							<h2 className="text-xl font-medium text-slate-800">
								{t('sub-projects.edit.section_overview')}
							</h2>
							<Button
								className="bg-brand-blue-700 hover:bg-brand-blue-800 text-white"
								onClick={handleSave}
							>
								{t('sub-projects.edit.save_changes')}
							</Button>
						</div>

						{/* Two-column card layout */}
						<div className="grid grid-cols-2 gap-7">
							{/* ── Left card ──────────────────────────────── */}
							<div className="flex flex-col gap-8 rounded-2xl border border-slate-200 bg-white px-11 py-5">
								{/* Subproject name */}
								<div className="flex flex-col gap-2">
									<h3 className="text-lg font-semibold text-slate-800">
										{t('sub-projects.edit.name_label')}
									</h3>
									<Input
										type="text"
										value={name}
										onChange={(e) => setName(e.target.value)}
										aria-label={t('sub-projects.edit.name_label')}
										className="h-10 bg-white px-3"
									/>
								</div>

								{/* Dataset */}
								<div className="flex flex-col gap-2">
									<h3 className="text-lg font-semibold text-slate-800">
										{t('sub-projects.edit.dataset_label')}
									</h3>
									<div className="flex flex-wrap items-center gap-2">
										<Tag>{displayDataset.name}</Tag>
										<label className="flex cursor-pointer items-center gap-2">
											<Checkbox
												checked={shuffle}
												onCheckedChange={(v) => setShuffle(Boolean(v))}
												aria-label={t(
													'sub-projects.select_dataset.shuffle_on'
												)}
											/>
											<span className="text-sm font-medium text-slate-900">
												{t('sub-projects.select_dataset.shuffle_on')}
											</span>
										</label>
									</div>
									<div className="flex gap-5">
										<div className="flex flex-1 flex-col gap-1.5">
											<label
												htmlFor="edit-from-instance"
												className="px-2.5 text-sm font-semibold text-slate-800"
											>
												{t('sub-projects.select_dataset.from_instance')}
											</label>
											<Input
												id="edit-from-instance"
												type="number"
												inputMode="numeric"
												min={1}
												value={fromInstance}
												onChange={(e) =>
													setFromInstance(Number(e.target.value))
												}
												className="h-10 bg-white px-2.5"
											/>
										</div>
										<div className="flex flex-1 flex-col gap-1.5">
											<label
												htmlFor="edit-to-instance"
												className="px-2.5 text-sm font-semibold text-slate-800"
											>
												{t('sub-projects.select_dataset.to_instance')}
											</label>
											<Input
												id="edit-to-instance"
												type="number"
												inputMode="numeric"
												min={fromInstance + 1}
												max={displayDataset.totalInstances}
												value={toInstance}
												onChange={(e) =>
													setToInstance(Number(e.target.value))
												}
												className="h-10 bg-white px-2.5"
											/>
										</div>
									</div>
								</div>

								{/* Date range */}
								<div className="flex flex-col gap-2">
									<h3 className="text-lg font-semibold text-slate-800">
										{t('sub-projects.configuration.timeframe_label')}
									</h3>
									<DateRangePickerButton
										value={dateRange}
										onChange={setDateRange}
										placeholder={t(
											'sub-projects.configuration.timeframe_placeholder'
										)}
										aria-label={t('sub-projects.configuration.timeframe_label')}
									/>
								</div>

								{/* Priority */}
								<div className="flex flex-col gap-2">
									<h3 className="text-lg font-semibold text-slate-800">
										{t('sub-projects.configuration.priority_label')}
									</h3>
									<Select
										aria-label={t('sub-projects.configuration.priority_label')}
										value={priority ?? undefined}
										onValueChange={(v) => setPriority(v as SubprojectPriority)}
									>
										<SelectTrigger
											aria-label={t(
												'sub-projects.configuration.priority_label'
											)}
											className="h-10 w-full gap-2 border-slate-200 px-3 hover:cursor-pointer [&>span]:!flex [&>span]:!overflow-visible"
										>
											{priority ? (
												<span className="flex flex-1 items-center gap-2">
													<PriorityBadge priority={priority} size="sm" />
													<span className="text-sm font-medium text-slate-800">
														{t(
															`sub-projects.configuration.priority_${priority}`
														)}
													</span>
												</span>
											) : (
												<span className="flex flex-1 items-center gap-2">
													<CircleAlert
														className="size-4 text-slate-800"
														aria-hidden="true"
													/>
													<SelectValue
														placeholder={t(
															'sub-projects.configuration.priority_placeholder'
														)}
														className="text-sm"
													/>
												</span>
											)}
										</SelectTrigger>
										<SelectContent className="w-72">
											{(
												['low', 'medium', 'high'] as SubprojectPriority[]
											).map((p) => (
												<SelectItem
													key={p}
													value={p}
													className="py-2.5 pr-8 pl-3 hover:cursor-pointer"
												>
													<span className="flex items-center gap-3">
														<PriorityBadge priority={p} />
														<span className="text-sm font-medium text-slate-800">
															{t(
																`sub-projects.configuration.priority_${p}`
															)}
														</span>
													</span>
												</SelectItem>
											))}
										</SelectContent>
									</Select>
								</div>
							</div>

							{/* ── Right card ─────────────────────────────── */}
							<div className="flex flex-col gap-8 rounded-2xl border border-slate-200 bg-white px-11 py-5">
								{/* Requirements */}
								<div className="flex flex-col gap-2">
									<h3 className="text-lg font-semibold text-slate-800">
										{t('sub-projects.configuration.requirements_label')}
									</h3>
									<ToggleSwitch
										id="edit-min-annotations"
										checked={minAnnotationsEnabled}
										onChange={setMinAnnotationsEnabled}
										label={t(
											'sub-projects.configuration.min_annotations_label'
										)}
										description={t(
											'sub-projects.configuration.min_annotations_description'
										)}
									/>
									<div
										className={cn(
											'transition-opacity',
											!minAnnotationsEnabled &&
												'pointer-events-none opacity-50'
										)}
									>
										<Input
											id="edit-min-annotations-count"
											type="number"
											inputMode="numeric"
											min={1}
											max={selectedAnnotatorIds.size || undefined}
											value={minAnnotationsEnabled ? minAnnotations : ''}
											placeholder={
												minAnnotationsEnabled
													? String(selectedAnnotatorIds.size)
													: t(
															'sub-projects.configuration.min_annotations_inactive'
														)
											}
											disabled={!minAnnotationsEnabled}
											onChange={(e) =>
												setMinAnnotations(Number(e.target.value))
											}
											aria-label={t(
												'sub-projects.configuration.min_annotations_label'
											)}
											className="h-10 bg-white px-3"
										/>
									</div>
								</div>

								{/* Browsing and Submission */}
								<div className="flex flex-col gap-5">
									<h3 className="text-lg font-semibold text-slate-800">
										{t('sub-projects.configuration.browsing_label')}
									</h3>
									<ToggleSwitch
										id="edit-flexible-browsing"
										checked={flexibleBrowsing}
										onChange={setFlexibleBrowsing}
										label={t(
											'sub-projects.configuration.flexible_browsing_label'
										)}
										description={t(
											'sub-projects.configuration.flexible_browsing_description'
										)}
									/>
									<fieldset
										className={cn(
											'flex flex-col gap-3 transition-opacity',
											!flexibleBrowsing && 'pointer-events-none opacity-50'
										)}
										aria-disabled={!flexibleBrowsing}
										disabled={!flexibleBrowsing}
									>
										<legend className="sr-only">
											{t('sub-projects.configuration.browsing_label')}
										</legend>
										{(['auto', 'manual'] as SubmissionMode[]).map((mode) => (
											<label
												key={mode}
												className={cn(
													'flex cursor-pointer items-start gap-3 rounded-xl border p-5 transition-colors',
													submissionMode === mode
														? 'border-brand-blue-700 bg-brand-blue-50'
														: 'border-brand-blue-200 hover:bg-brand-blue-50/50 bg-white'
												)}
											>
												<input
													type="radio"
													name="edit-submission-mode"
													value={mode}
													checked={submissionMode === mode}
													onChange={() => setSubmissionMode(mode)}
													disabled={!flexibleBrowsing}
													className="accent-brand-blue-700 mt-0.5 size-4 shrink-0"
												/>
												<span className="flex flex-col gap-1">
													<span className="text-sm font-medium text-slate-800">
														{t(
															`sub-projects.configuration.submission_${mode}`
														)}
													</span>
													<span className="text-sm text-slate-500">
														{t(
															`sub-projects.configuration.submission_${mode}_description`
														)}
													</span>
												</span>
											</label>
										))}
									</fieldset>
								</div>
							</div>
						</div>
					</section>
				)}

				{/* ── Annotators panel ──────────────────────────────────── */}
				{activeTab === 'annotators' && (
					<section
						id="tabpanel-annotators"
						role="tabpanel"
						aria-labelledby="tab-annotators"
					>
						<SubprojectAnnotatorsPanel
							annotators={displayAnnotators.filter((a) =>
								selectedAnnotatorIds.has(a.id)
							)}
							onAnnotatorRemoved={(id) => handleSelectionChange(id, false)}
						/>
					</section>
				)}

				{/* ── Annotations panel ────────────────────────────────── */}
				{activeTab === 'annotations' && (
					<section
						id="tabpanel-annotations"
						role="tabpanel"
						aria-labelledby="tab-annotations"
					>
						<AnnotationsTab annotations={displayAnnotationRows} />
					</section>
				)}
			</div>
		</AppLayout>
	);
}
