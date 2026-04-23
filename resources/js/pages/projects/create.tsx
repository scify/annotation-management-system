import { ProjectConfigurationStep } from '@/components/project/configuration-step';
import { ProjectDialog } from '@/components/project/project-dialog';
import { MOCK_TASK_TYPES, SelectTaskTypeStep } from '@/components/project/select-task-type-step';
import { CreateSubprojectStepper } from '@/components/sub-project/create-subproject-stepper';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { useTranslations } from '@/hooks/use-translations';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/react';
import { ChevronLeft, ChevronRight, FolderDot } from 'lucide-react';
import { useState } from 'react';

export default function CreateProject() {
	const { t } = useTranslations();

	const STEPS = [
		{ label: t('projects.create.step_select_task_type') },
		{ label: t('projects.create.step_configurations') },
		{ label: t('projects.create.step_select_annotators') },
		{ label: t('projects.create.step_add_co_managers') },
	];

	const breadcrumbs: BreadcrumbItem[] = [
		{ title: t('projects.title'), href: route('projects.index') },
		{ title: t('projects.create.heading'), href: route('projects.create') },
	];

	const [currentStep, setCurrentStep] = useState(0);
	const [selectedTaskTypeId, setSelectedTaskTypeId] = useState<number | null>(null);
	const [confirmOpen, setConfirmOpen] = useState(false);
	const [projectName, setProjectName] = useState('');

	// Step 2 — configuration state
	const [selectedDatasetId, setSelectedDatasetId] = useState<number | null>(null);
	const [shuffleInstances, setShuffleInstances] = useState(true);
	const [allowMarkConfidence, setAllowMarkConfidence] = useState<'yes' | 'no'>('yes');
	const [allowNotSureAnswer, setAllowNotSureAnswer] = useState<'yes' | 'no'>('no');
	const [restrictVisibility, setRestrictVisibility] = useState(false);

	const selectedTaskType = MOCK_TASK_TYPES.find((tt) => tt.id === selectedTaskTypeId) ?? null;

	function handleNext() {
		if (currentStep < STEPS.length - 1) {
			setCurrentStep((s) => s + 1);
		} else {
			setConfirmOpen(true);
		}
	}

	const isNextDisabled =
		(currentStep === 0 && selectedTaskTypeId === null) ||
		(currentStep === 1 && selectedDatasetId === null);

	return (
		<AppLayout breadcrumbs={breadcrumbs}>
			<Head title={t('projects.create.page_title')} />
			<div className="flex w-full max-w-5xl flex-col gap-8 px-6 py-6">
				<h1 className="text-slate-800">{t('projects.create.heading')}</h1>

				<CreateSubprojectStepper
					currentStep={currentStep}
					steps={STEPS}
					ariaLabel="Create project progress"
				/>

				{currentStep === 0 && (
					<SelectTaskTypeStep
						selectedId={selectedTaskTypeId}
						onSelectionChange={setSelectedTaskTypeId}
					/>
				)}

				{currentStep === 1 && (
					<ProjectConfigurationStep
						selectedTaskType={selectedTaskType}
						selectedDatasetId={selectedDatasetId}
						shuffleInstances={shuffleInstances}
						allowMarkConfidence={allowMarkConfidence}
						allowNotSureAnswer={allowNotSureAnswer}
						restrictVisibility={restrictVisibility}
						onDatasetChange={setSelectedDatasetId}
						onShuffleChange={setShuffleInstances}
						onAllowMarkConfidenceChange={setAllowMarkConfidence}
						onAllowNotSureAnswerChange={setAllowNotSureAnswer}
						onVisibilityChange={setRestrictVisibility}
					/>
				)}

				{currentStep > 1 && (
					<div className="flex h-48 items-center justify-center rounded-xl border border-dashed border-slate-300 text-sm text-slate-400">
						{STEPS[currentStep]?.label} — coming soon
					</div>
				)}

				{/* Action bar */}
				<div className="flex items-center justify-end gap-3 border-t border-slate-200 pt-4">
					{currentStep === 0 && selectedTaskTypeId === null && (
						<p role="alert" className="mr-auto text-sm text-slate-500">
							{t('projects.select_task_type.min_one_required')}
						</p>
					)}
					{currentStep === 1 && selectedDatasetId === null && (
						<p role="alert" className="mr-auto text-sm text-slate-500">
							{t('projects.configuration.min_one_dataset_required')}
						</p>
					)}
					<Button variant="outline" onClick={() => router.visit(route('projects.index'))}>
						{t('projects.create.cancel')}
					</Button>
					<Button
						variant="outline"
						className="border-brand-yellow-400 text-brand-yellow-600 hover:bg-brand-yellow-50"
						isDisabled={currentStep === 0}
						onClick={() => setCurrentStep((s) => Math.max(0, s - 1))}
					>
						<ChevronLeft className="size-4" aria-hidden="true" />
						{t('projects.create.back')}
					</Button>
					<Button
						className="hover:bg-brand-blue-800 bg-brand-blue-700 text-white"
						isDisabled={isNextDisabled}
						onClick={handleNext}
					>
						{currentStep === STEPS.length - 1
							? t('projects.create.create_action')
							: t('projects.create.next')}
						<ChevronRight className="size-4" aria-hidden="true" />
					</Button>
				</div>

				<ProjectDialog
					open={confirmOpen}
					onClose={() => setConfirmOpen(false)}
					icon={<FolderDot />}
					title={t('projects.create.heading')}
					description={t('projects.create.dialog_description')}
					cancelLabel={t('projects.create.back')}
					actionLabel={t('projects.create.create_action')}
					onAction={() => {
						setConfirmOpen(false);
						// TODO: submit form with projectName and selectedTaskTypeId
					}}
				>
					<Input
						type="text"
						value={projectName}
						onChange={(e) => setProjectName(e.target.value)}
						placeholder={t('projects.create.dialog_name_placeholder')}
						className="mb-12 h-10 bg-white px-3 py-3"
						aria-label={t('projects.create.dialog_description')}
					/>
				</ProjectDialog>
			</div>
		</AppLayout>
	);
}
