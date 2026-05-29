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

interface BackendAnnotator {
    id: number;
    username: string;
    status?: 'active' | 'inactive' | 'pending';
    active_projects_count: number;
    active_subprojects_count: number;
    annotator_progress: number;
    workload: number;
}

interface BackendSubsetData {
    dataset_id: number;
    dataset_name: string;
    size: number;
    previous_subset_last_index: number | null;
    from_instance: number;
    to_instance: number;
}

interface Props {
    project_data: { project_id: number; name: string };
    annotators_data: BackendAnnotator[];
    subset_data: BackendSubsetData;
    created_subproject_name?: string | null;
}

export default function CreateSubproject({
    project_data,
    annotators_data,
    subset_data,
    created_subproject_name,
}: Props) {
    const { t, trans } = useTranslations();
    const isSuccess = !!created_subproject_name;

    const displayAnnotators: ProjectAnnotatorRowData[] = annotators_data.map((a) => ({
        ...a,
        name: a.username,
    }));

    const displayDataset = {
        name: subset_data.dataset_name,
        totalInstances: subset_data.size,
        previousEndInstance: subset_data.previous_subset_last_index ?? undefined,
    };

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
    const [datasetId] = useState(subset_data.dataset_id);

    // Step 3 — Configuration
    const [priority, setPriority] = useState<SubprojectPriority | null>(null);
    const [dateRange, setDateRange] = useState<DateRangeValue | null>(null);
    const [minAnnotationsEnabled, setMinAnnotationsEnabled] = useState(false);
    const [minAnnotations, setMinAnnotations] = useState(1);
    const [flexibleBrowsing, setFlexibleBrowsing] = useState(false);
    const [submissionMode, setSubmissionMode] = useState<SubmissionMode>('auto');

    const [confirmOpen, setConfirmOpen] = useState(isSuccess);
    const [subprojectName, setSubprojectName] = useState('');
    const [processing, setProcessing] = useState(false);
    const [serverErrors, setServerErrors] = useState<Record<string, string>>({});

    const breadcrumbs: BreadcrumbItem[] = [
        { title: t('projects.title'), href: route('projects.index') },
        { title: project_data.name, href: route('projects.show', project_data.project_id) },
        {
            title: t('sub-projects.create.heading'),
            href: route('projects.subprojects.create', project_data.project_id),
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

    function handleDialogClose() {
        if (isSuccess) {
            router.visit(route('projects.show', project_data.project_id));
        } else {
            setConfirmOpen(false);
        }
    }

    function handleSubmit() {
        setServerErrors({});
        router.post(
            route('projects.subprojects.store', project_data.project_id),
            {
                name: subprojectName.trim(),
                annotator_ids: Array.from(selectedAnnotatorIds),
                shuffle,
                from_instance: fromInstance,
                to_instance: toInstance,
                dataset_id: datasetId,
                priority,
                scheduled_at: dateRange?.start?.toString() ?? null,
                deadline_at: dateRange?.end?.toString() ?? null,
                is_flexible: flexibleBrowsing,
                requires_confirmation: flexibleBrowsing ? submissionMode === 'manual' : null,
                minimum_annotations: minAnnotationsEnabled ? minAnnotations : null,
            },
            {
                preserveState: true,
                preserveScroll: true,
                onStart: () => setProcessing(true),
                onFinish: () => setProcessing(false),
                onError: (errors) => setServerErrors(errors),
            }
        );
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
                        onClick={() =>
                            router.visit(route('projects.show', project_data.project_id))
                        }
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
                    onClose={handleDialogClose}
                    icon={<FolderDot />}
                    title={t('sub-projects.create.heading')}
                    description={
                        isSuccess ? undefined : t('sub-projects.create.dialog_description')
                    }
                    cancelLabel={t('sub-projects.create.back')}
                    hideCancelButton={isSuccess}
                    actionLabel={
                        isSuccess
                            ? t('sub-projects.create.dialog_go_to_subprojects')
                            : t('sub-projects.create.create_action')
                    }
                    actionDisabled={!isSuccess && (subprojectName.trim() === '' || processing)}
                    loading={processing}
                    onAction={
                        isSuccess
                            ? () => router.visit(route('projects.show', project_data.project_id))
                            : handleSubmit
                    }
                >
                    {isSuccess ? (
                        <div
                            role="status"
                            aria-live="polite"
                            className="mb-8 rounded-md border border-slate-400 bg-slate-50 p-3 text-sm font-medium text-slate-700"
                        >
                            {trans('sub-projects.create.dialog_success_message', {
                                name: created_subproject_name ?? '',
                            })}
                        </div>
                    ) : (
                        <>
                            <Input
                                type="text"
                                value={subprojectName}
                                onChange={(e) => setSubprojectName(e.target.value)}
                                placeholder={t('sub-projects.create.dialog_name_placeholder')}
                                className="mb-12 h-10 bg-white px-3 py-3"
                                aria-label={t('sub-projects.create.dialog_description')}
                                aria-invalid={!!serverErrors.name}
                            />
                            {serverErrors.name && (
                                <p role="alert" className="text-sm text-red-600">
                                    {serverErrors.name}
                                </p>
                            )}
                        </>
                    )}
                </ProjectDialog>
            </div>
        </AppLayout>
    );
}
