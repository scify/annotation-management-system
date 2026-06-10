import { useTranslations } from '@/hooks/use-translations';
import { type ManagerCreateData, type ManagerEditUser, RolesEnum } from '@/types';
import { Link, useForm } from '@inertiajs/react';
import { ChevronLeft, ChevronRight, LoaderCircle, X } from 'lucide-react';
import { useEffect, useMemo, useState } from 'react';
import { CreateManagerStepper } from './create-manager-stepper';
import { ConnectAnnotatorsStep } from './steps/connect-annotators-step';
import { ConnectProjectsStep } from './steps/connect-projects-step';
import { DatasetsStep } from './steps/datasets-step';
import { PersonalInfoStep } from './steps/personal-info-step';
import { TasksAccessStep } from './steps/tasks-access-step';

export interface CreateManagerFormData {
    name: string;
    username: string;
    email: string;
    password: string;
    password_confirmation: string;
    status: 'active' | 'inactive' | 'pending';
    annotation_task_ids: number[];
    dataset_ids: number[];
    project_ids: number[];
    annotator_ids: number[];
}

interface CreateManagerFormProps {
    managerData: ManagerCreateData;
    user?: ManagerEditUser;
}

const LAST_STEP = 4;

function isPasswordStrong(password: string): boolean {
    return password.length >= 8 && /[a-zA-Z]/.test(password) && /[0-9]/.test(password);
}

const FIELD_TO_STEP: Record<string, number> = {
    name: 0,
    username: 0,
    email: 0,
    password: 0,
    password_confirmation: 0,
    annotation_task_ids: 1,
    dataset_ids: 2,
    project_ids: 3,
    annotator_ids: 4,
};

export function CreateManagerForm({ managerData, user }: CreateManagerFormProps) {
    const { t } = useTranslations();
    const [currentStep, setCurrentStep] = useState(0);
    const isEditing = user !== undefined;

    const form = useForm<CreateManagerFormData>({
        name: user?.name ?? '',
        username: user?.username ?? '',
        email: user?.email ?? '',
        password: '',
        password_confirmation: '',
        status: user?.status ?? 'pending',
        annotation_task_ids: user?.annotation_task_ids ?? [],
        dataset_ids: user?.dataset_ids ?? [],
        project_ids: user?.project_ids ?? [],
        annotator_ids: user?.annotator_ids ?? [],
    });

    const steps = [
        { label: t('users.steps.personal_info') },
        { label: t('users.steps.tasks_access') },
        { label: t('users.steps.datasets') },
        { label: t('users.steps.connect_projects') },
        { label: t('users.steps.connect_annotators') },
    ];

    useEffect(() => {
        const errorKeys = Object.keys(form.errors);
        if (errorKeys.length === 0) return;
        const firstStep = errorKeys.reduce(
            (min, field) => Math.min(min, FIELD_TO_STEP[field] ?? 0),
            Infinity
        );
        if (Number.isFinite(firstStep)) setCurrentStep(firstStep);
    }, [form.errors]);

    const stepsWithErrors = [
        ...new Set(Object.keys(form.errors).map((field) => FIELD_TO_STEP[field] ?? 0)),
    ];

    const allProjectsList = managerData.all_projects ?? managerData.my_projects;
    const lockedAnnotatorIds = useMemo(
        () => [
            ...new Set(
                allProjectsList
                    .filter((p) => form.data.project_ids.includes(p.id))
                    .flatMap((p) => p.annotators ?? [])
            ),
        ],
        [form.data.project_ids, allProjectsList]
    );

    function handleChange(updates: Partial<CreateManagerFormData>) {
        form.setData({ ...form.data, ...updates });
    }

    function isStepValid(step: number): boolean {
        switch (step) {
            case 0: {
                const personalInfoFilled =
                    form.data.name.trim() !== '' &&
                    form.data.username.trim() !== '' &&
                    form.data.email.trim() !== '';
                const passwordValid = isEditing
                    ? form.data.password === '' ||
                      (isPasswordStrong(form.data.password) &&
                          form.data.password === form.data.password_confirmation)
                    : isPasswordStrong(form.data.password) &&
                      form.data.password_confirmation !== '' &&
                      form.data.password === form.data.password_confirmation;
                return personalInfoFilled && passwordValid;
            }
            default:
                return true;
        }
    }

    function handleNext() {
        if (!isStepValid(currentStep)) return;

        if (currentStep === LAST_STEP) {
            form.transform((data) => {
                const payload: Record<string, unknown> = {
                    ...data,
                    type: RolesEnum.ANNOTATION_MANAGER,
                    annotator_ids: [...new Set([...data.annotator_ids, ...lockedAnnotatorIds])],
                };
                if (!payload.password) {
                    delete payload.password;
                    delete payload.password_confirmation;
                }
                return payload;
            });
            if (isEditing) {
                form.put(route('users.update', user.id));
            } else {
                form.post(route('users.store'));
            }
            return;
        }

        setCurrentStep((s) => s + 1);
    }

    function handleBack() {
        if (currentStep > 0) {
            setCurrentStep((s) => s - 1);
        }
    }

    const title = isEditing ? t('users.actions.edit_manager') : t('users.actions.create_manager');

    return (
        <section aria-label={title} className="flex flex-col gap-6">
            <h1 className="text-3xl font-light text-slate-800">{title}</h1>

            <CreateManagerStepper
                currentStep={currentStep}
                steps={steps}
                stepsWithErrors={stepsWithErrors}
                onStepClick={isEditing ? setCurrentStep : undefined}
            />

            <div>
                {currentStep === 0 && (
                    <PersonalInfoStep
                        data={form.data}
                        onChange={(updates) => handleChange(updates)}
                        errors={form.errors}
                        isEditing={isEditing}
                    />
                )}
                {currentStep === 1 && (
                    <TasksAccessStep
                        annotationTasks={managerData.annotation_tasks}
                        selectedIds={form.data.annotation_task_ids}
                        onSelectionChange={(ids) => handleChange({ annotation_task_ids: ids })}
                    />
                )}
                {currentStep === 2 && (
                    <DatasetsStep
                        taskTypes={managerData.annotation_tasks.filter((tt) =>
                            form.data.annotation_task_ids.includes(tt.id)
                        )}
                        selectedDatasetIds={form.data.dataset_ids}
                        onSelectionChange={(ids) => handleChange({ dataset_ids: ids })}
                    />
                )}
                {currentStep === 3 && (
                    <ConnectProjectsStep
                        projects={managerData.all_projects ?? managerData.my_projects}
                        myProjects={managerData.my_projects}
                        selectedProjectIds={form.data.project_ids}
                        onSelectionChange={(ids) => handleChange({ project_ids: ids })}
                        showMineToggle={!!managerData.all_projects?.length}
                    />
                )}
                {currentStep === 4 && (
                    <ConnectAnnotatorsStep
                        annotators={managerData.all_annotators ?? managerData.my_annotators}
                        myAnnotators={managerData.my_annotators}
                        selectedAnnotatorIds={form.data.annotator_ids}
                        onSelectionChange={(ids) => handleChange({ annotator_ids: ids })}
                        lockedAnnotatorIds={lockedAnnotatorIds}
                        showMineToggle={!!managerData.all_annotators?.length}
                    />
                )}
            </div>

            <div className="flex items-center justify-end gap-3">
                {!isStepValid(currentStep) && (
                    <p role="alert" className="mr-auto text-sm text-slate-500">
                        {currentStep === 0 &&
                            (form.data.password !== '' &&
                            form.data.password_confirmation !== '' &&
                            form.data.password !== form.data.password_confirmation
                                ? t('users.validation.password_mismatch')
                                : form.data.password !== '' && !isPasswordStrong(form.data.password)
                                  ? t('users.validation.password_weak')
                                  : t('users.steps.personal_info_hint'))}
                    </p>
                )}
                <Link
                    href={route('users.index')}
                    className="focus-visible:ring-brand-blue-500 border-brand-blue-500 text-brand-blue-800 hover:bg-brand-blue-50 inline-flex h-10 items-center gap-1.5 rounded-lg border bg-white px-4 text-sm font-semibold focus-visible:ring-2 focus-visible:outline-none"
                >
                    <X className="h-4 w-4" aria-hidden="true" />
                    {t('users.actions.cancel')}
                </Link>

                <button
                    type="button"
                    onClick={handleBack}
                    disabled={currentStep === 0}
                    className="focus-visible:ring-brand-yellow-300 bg-brand-yellow-300 text-brand-blue-900 hover:bg-brand-yellow-400 inline-flex h-10 items-center gap-1.5 rounded-lg px-4 text-sm font-semibold hover:cursor-pointer focus-visible:ring-2 focus-visible:outline-none disabled:cursor-not-allowed disabled:opacity-40"
                >
                    <ChevronLeft className="h-4 w-4" aria-hidden="true" />
                    {t('users.actions.back')}
                </button>

                <button
                    type="button"
                    onClick={handleNext}
                    disabled={!isStepValid(currentStep) || form.processing}
                    className="focus-visible:ring-brand-blue-700 bg-brand-blue-700 hover:bg-brand-blue-800 inline-flex h-10 items-center gap-1.5 rounded-lg px-4 text-sm font-semibold text-white hover:cursor-pointer focus-visible:ring-2 focus-visible:outline-none disabled:cursor-not-allowed disabled:opacity-40"
                >
                    {form.processing && (
                        <LoaderCircle className="h-4 w-4 animate-spin" aria-hidden="true" />
                    )}
                    {currentStep === LAST_STEP
                        ? isEditing
                            ? t('users.actions.update')
                            : t('users.actions.create_manager')
                        : t('users.actions.next')}
                    {!form.processing && <ChevronRight className="h-4 w-4" aria-hidden="true" />}
                </button>
            </div>
        </section>
    );
}
