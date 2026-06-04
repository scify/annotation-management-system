import { useTranslations } from '@/hooks/use-translations';
import { type AdminCreateData, RolesEnum } from '@/types';
import { Link, useForm } from '@inertiajs/react';
import { ChevronLeft, ChevronRight, LoaderCircle, X } from 'lucide-react';
import { useState } from 'react';
import { CreateManagerStepper } from '../create-manager/create-manager-stepper';
import { ConnectAnnotatorsStep } from '../create-manager/steps/connect-annotators-step';
import { ConnectProjectsStep } from '../create-manager/steps/connect-projects-step';
import { PersonalInfoStep } from '../create-manager/steps/personal-info-step';

export interface CreateAdminFormData {
    name: string;
    username: string;
    email: string;
    password: string;
    password_confirmation: string;
    status: 'active' | 'inactive' | 'pending';
    project_ids: number[];
    annotator_ids: number[];
}

interface CreateAdminFormProps {
    adminData: AdminCreateData;
}

const LAST_STEP = 2;

export function CreateAdminForm({ adminData }: CreateAdminFormProps) {
    const { t } = useTranslations();
    const [currentStep, setCurrentStep] = useState(0);

    const form = useForm<CreateAdminFormData>({
        name: '',
        username: '',
        email: '',
        password: '',
        password_confirmation: '',
        status: 'active',
        project_ids: [],
        annotator_ids: [],
    });

    const steps = [
        { label: t('users.steps.personal_info') },
        { label: t('users.steps.connect_projects') },
        { label: t('users.steps.connect_annotators') },
    ];

    function handleChange(updates: Partial<CreateAdminFormData>) {
        form.setData({ ...form.data, ...updates });
    }

    function isStepValid(step: number): boolean {
        switch (step) {
            case 0:
                return (
                    form.data.name.trim() !== '' &&
                    form.data.username.trim() !== '' &&
                    form.data.email.trim() !== '' &&
                    form.data.password !== '' &&
                    form.data.password_confirmation !== ''
                );
            case 1:
                return form.data.project_ids.length >= 1;
            case 2:
                return form.data.annotator_ids.length >= 1;
            default:
                return true;
        }
    }

    function handleNext() {
        if (!isStepValid(currentStep)) return;

        if (currentStep === LAST_STEP) {
            form.transform((data) => ({ ...data, type: RolesEnum.ADMIN }));
            form.post(route('users.store'));
            return;
        }

        setCurrentStep((s) => s + 1);
    }

    function handleBack() {
        if (currentStep > 0) {
            setCurrentStep((s) => s - 1);
        }
    }

    return (
        <section aria-label={t('users.actions.create_admin')} className="flex flex-col gap-6">
            <h1 className="text-3xl font-light text-slate-800">
                {t('users.actions.create_admin')}
            </h1>

            <CreateManagerStepper currentStep={currentStep} steps={steps} />

            <div>
                {currentStep === 0 && (
                    <PersonalInfoStep
                        data={form.data}
                        onChange={(updates) => handleChange(updates)}
                    />
                )}
                {currentStep === 1 && (
                    <ConnectProjectsStep
                        projects={adminData.all_projects}
                        myProjects={adminData.my_projects}
                        selectedProjectIds={form.data.project_ids}
                        onSelectionChange={(ids) => handleChange({ project_ids: ids })}
                    />
                )}
                {currentStep === 2 && (
                    <ConnectAnnotatorsStep
                        annotators={adminData.all_annotators}
                        myAnnotators={adminData.my_annotators}
                        selectedAnnotatorIds={form.data.annotator_ids}
                        onSelectionChange={(ids) => handleChange({ annotator_ids: ids })}
                        lockedAnnotatorIds={[]}
                    />
                )}
            </div>

            <div className="flex items-center justify-end gap-3">
                {!isStepValid(currentStep) && (
                    <p role="alert" className="mr-auto text-sm text-slate-500">
                        {currentStep === 0 && t('users.steps.personal_info_hint')}
                        {currentStep === 1 && t('users.connect_projects.min_one_required')}
                        {currentStep === 2 && t('users.select_annotators.min_one_required')}
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
                        ? t('users.actions.create_admin')
                        : t('users.actions.next')}
                    {!form.processing && <ChevronRight className="h-4 w-4" aria-hidden="true" />}
                </button>
            </div>
        </section>
    );
}
