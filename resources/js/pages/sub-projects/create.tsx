import { type ProjectAnnotatorRowData } from '@/components/annotator/annotators-table';
import { ProjectDialog } from '@/components/project/project-dialog';
import {
	ConfigurationStep,
	type SubprojectPriority,
	type SubmissionMode,
} from '@/components/sub-project/configuration-step';
import { CreateSubprojectStepper } from '@/components/sub-project/create-subproject-stepper';
import { SelectAnnotatorsStep } from '@/components/annotator/select-annotators-step';
import { SelectDatasetSubsetStep } from '@/components/sub-project/select-dataset-subset-step';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { useTranslations } from '@/hooks/use-translations';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { type DateRangeValue } from '@/components/ui/date-range-picker-button';
import { Head, router } from '@inertiajs/react';
import { ChevronLeft, ChevronRight, FolderDot } from 'lucide-react';
import { useState } from 'react';

const MOCK_PROJECT = { id: 1, name: 'Project New Nov_26' };

const MOCK_DATASET = { name: 'Text Dataset B', totalInstances: 10_000, previousEndInstance: 56 };

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
		initials: 'G',
		username: '@ggiannakopulos',
		projects: 23,
		subprojects: 23,
		workload: 85,
		progress: 75,
	},
	{
		id: 3,
		initials: 'N',
		username: '@nellisavrani',
		projects: 12,
		subprojects: 4,
		workload: 30,
		progress: 75,
	},
	{
		id: 4,
		initials: 'G',
		username: '@ggiannakopulos',
		projects: 23,
		subprojects: 23,
		workload: 85,
		progress: 75,
	},
	{
		id: 5,
		initials: 'G',
		username: '@ggiannakopulos',
		projects: 23,
		subprojects: 23,
		workload: 85,
		progress: 75,
	},
];

interface Props {
	project?: { id: number; name: string };
	/** Available annotators — falls back to mock data */
	annotators?: ProjectAnnotatorRowData[];
	dataset?: { name: string; totalInstances: number; previousEndInstance?: number };
}

export default function CreateSubproject({ project, annotators, dataset }: Props) {
	const { t } = useTranslations();
	const displayProject = project ?? MOCK_PROJECT;
	const displayAnnotators = annotators ?? MOCK_ANNOTATORS;
	const displayDataset = dataset ?? MOCK_DATASET;

	const STEPS = [
		{ label: t('sub-projects.select_annotators.heading') },
		{ label: t('sub-projects.select_dataset.select_subset_heading') },
		{ label: t('sub-projects.create.step_configurations') },
	];

	const [currentStep, setCurrentStep] = useState(0);
	const [selectedAnnotatorIds, setSelectedAnnotatorIds] = useState<Set<number>>(new Set());
	const [fromInstance, setFromInstance] = useState(
		() => (displayDataset.previousEndInstance ?? 0) + 1
	);
	const [toInstance, setToInstance] = useState(displayDataset.totalInstances);
	const [shuffle, setShuffle] = useState(true);

	// Step 3 — Configuration
	const [priority, setPriority] = useState<SubprojectPriority | null>(null);
	const [dateRange, setDateRange] = useState<DateRangeValue | null>(null);
	const [minAnnotationsEnabled, setMinAnnotationsEnabled] = useState(false);
	const [minAnnotations, setMinAnnotations] = useState(1);
	const [flexibleBrowsing, setFlexibleBrowsing] = useState(false);
	const [submissionMode, setSubmissionMode] = useState<SubmissionMode>('auto');

	const [confirmOpen, setConfirmOpen] = useState(false);
	const [subprojectName, setSubprojectName] = useState('');

	const breadcrumbs: BreadcrumbItem[] = [
		{ title: t('projects.title'), href: route('projects.index') },
		{ title: displayProject.name, href: route('projects.show', displayProject.id) },
		{
			title: t('sub-projects.create.heading'),
			href: route('projects.subprojects.create', displayProject.id),
		},
	];

	function handleSelectAllChange(ids: number[], checked: boolean) {
		setSelectedAnnotatorIds((prev) => {
			const next = new Set(prev);
			ids.forEach((id) => (checked ? next.add(id) : next.delete(id)));
			return next;
		});
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

	function handleNext() {
		if (currentStep < STEPS.length - 1) {
			setCurrentStep((s) => s + 1);
		} else {
			setConfirmOpen(true);
		}
	}

	return (
		<AppLayout breadcrumbs={breadcrumbs}>
			<Head title={t('sub-projects.create.page_title')} />
			<div className="flex w-full max-w-5xl flex-col gap-8 px-6 py-6">
				<h1 className="text-slate-800">{t('sub-projects.create.heading')}</h1>

				<CreateSubprojectStepper currentStep={currentStep} steps={STEPS} />

				{/* Step content */}
				{currentStep === 0 && (
					<SelectAnnotatorsStep
						annotators={displayAnnotators}
						selectedIds={selectedAnnotatorIds}
						onSelectionChange={handleSelectionChange}
						onSelectAllChange={handleSelectAllChange}
					/>
				)}

				{currentStep === 1 && (
					<SelectDatasetSubsetStep
						dataset={displayDataset}
						fromInstance={fromInstance}
						toInstance={toInstance}
						shuffle={shuffle}
						onFromInstanceChange={setFromInstance}
						onToInstanceChange={setToInstance}
						onShuffleChange={setShuffle}
					/>
				)}

				{currentStep === 2 && (
					<ConfigurationStep
						priority={priority}
						dateRange={dateRange}
						minAnnotationsEnabled={minAnnotationsEnabled}
						minAnnotations={minAnnotations}
						annotatorCount={selectedAnnotatorIds.size}
						flexibleBrowsing={flexibleBrowsing}
						submissionMode={submissionMode}
						onPriorityChange={setPriority}
						onDateRangeChange={setDateRange}
						onMinAnnotationsEnabledChange={setMinAnnotationsEnabled}
						onMinAnnotationsChange={setMinAnnotations}
						onFlexibleBrowsingChange={setFlexibleBrowsing}
						onSubmissionModeChange={setSubmissionMode}
					/>
				)}

				{/* Action bar */}
				<div className="flex items-center justify-end gap-3 border-t border-slate-200 pt-4">
					{currentStep === 0 && selectedAnnotatorIds.size === 0 && (
						<p role="alert" className="mr-auto text-sm text-slate-500">
							{t('sub-projects.select_annotators.min_one_required')}
						</p>
					)}
					{currentStep === 2 && (!priority || !dateRange) && (
						<p role="alert" className="mr-auto text-sm text-slate-500">
							{t('sub-projects.configuration.priority_and_timeframe_required')}
						</p>
					)}
					<Button
						variant="outline"
						onClick={() => router.visit(route('projects.show', displayProject.id))}
					>
						{t('sub-projects.create.cancel')}
					</Button>
					<Button
						variant="outline"
						className="border-brand-yellow-400 text-brand-yellow-600 hover:bg-brand-yellow-50"
						isDisabled={currentStep === 0}
						onClick={() => setCurrentStep((s) => Math.max(0, s - 1))}
					>
						<ChevronLeft className="size-4" aria-hidden="true" />
						{t('sub-projects.create.back')}
					</Button>
					<Button
						className="hover:bg-brand-blue-800 bg-brand-blue-700 text-white"
						isDisabled={
							(currentStep === 0 && selectedAnnotatorIds.size === 0) ||
							(currentStep === 2 && (!priority || !dateRange))
						}
						onClick={handleNext}
					>
						{currentStep === STEPS.length - 1
							? t('sub-projects.create.create_action')
							: t('sub-projects.create.next')}
						<ChevronRight className="size-4" aria-hidden="true" />
					</Button>
				</div>

				<ProjectDialog
					open={confirmOpen}
					onClose={() => setConfirmOpen(false)}
					icon={<FolderDot />}
					title={t('sub-projects.create.heading')}
					description={t('sub-projects.create.dialog_description')}
					cancelLabel={t('sub-projects.create.back')}
					actionLabel={t('sub-projects.create.create_action')}
					onAction={() => {
						setConfirmOpen(false);
						// TODO: submit with subprojectName
					}}
				>
					<Input
						type="text"
						value={subprojectName}
						onChange={(e) => setSubprojectName(e.target.value)}
						placeholder={t('sub-projects.create.dialog_name_placeholder')}
						className="mb-12 h-10 bg-white px-3 py-3"
						aria-label={t('sub-projects.create.dialog_description')}
					/>
				</ProjectDialog>
			</div>
		</AppLayout>
	);
}
