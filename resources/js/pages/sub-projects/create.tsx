import { type ProjectAnnotatorRowData } from '@/components/annotator/annotators-table';
import { CreateSubprojectStepper } from '@/components/sub-project/create-subproject-stepper';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/app-layout';
import { SelectAnnotatorsStep } from '@/components/sub-project/select-annotators-step';
import { SelectDatasetSubsetStep } from '@/components/sub-project/select-dataset-subset-step';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/react';
import { ChevronLeft, ChevronRight } from 'lucide-react';
import { useState } from 'react';

const STEPS = [
	{ label: 'Select Annotators' },
	{ label: 'Select Dataset Subset' },
	{ label: 'Configurations' },
];

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
	const displayProject = project ?? MOCK_PROJECT;
	const displayAnnotators = annotators ?? MOCK_ANNOTATORS;
	const displayDataset = dataset ?? MOCK_DATASET;

	const [currentStep, setCurrentStep] = useState(0);
	const [selectedAnnotatorIds, setSelectedAnnotatorIds] = useState<Set<number>>(new Set());
	const [fromInstance, setFromInstance] = useState(
		() => (displayDataset.previousEndInstance ?? 0) + 1
	);
	const [toInstance, setToInstance] = useState(displayDataset.totalInstances);
	const [shuffle, setShuffle] = useState(true);

	const breadcrumbs: BreadcrumbItem[] = [
		{ title: 'Projects', href: route('projects.index') },
		{ title: displayProject.name, href: route('projects.show', displayProject.id) },
		{
			title: 'Create Subproject',
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
		}
		// TODO: submit on final step
	}

	return (
		<AppLayout breadcrumbs={breadcrumbs}>
			<Head title="Create Subproject" />
			<div className="flex flex-col gap-6 px-6 py-6">
				<h1 className="text-slate-800">Create Subproject</h1>

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
					<section aria-labelledby="step-heading" className="flex flex-col gap-4">
						<h2 id="step-heading" className="page-subtitle">
							Configurations
						</h2>
						<p className="text-slate-500">Configuration options coming soon…</p>
					</section>
				)}

				{/* Action bar */}
				<div className="flex items-center justify-end gap-3 border-t border-slate-200 pt-4">
					<Button
						variant="outline"
						onClick={() => router.visit(route('projects.show', displayProject.id))}
					>
						Cancel
					</Button>
					<Button
						variant="outline"
						className="border-brand-yellow-400 text-brand-yellow-600 hover:bg-brand-yellow-50"
						isDisabled={currentStep === 0}
						onClick={() => setCurrentStep((s) => Math.max(0, s - 1))}
					>
						<ChevronLeft className="size-4" aria-hidden="true" />
						Back
					</Button>
					<Button
						className="hover:bg-brand-blue-800 bg-brand-blue-700 text-white"
						onClick={handleNext}
					>
						Next
						<ChevronRight className="size-4" aria-hidden="true" />
					</Button>
				</div>
			</div>
		</AppLayout>
	);
}
