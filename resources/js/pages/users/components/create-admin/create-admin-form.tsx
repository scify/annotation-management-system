import { useTranslations } from '@/hooks/use-translations';
import { ChevronLeft, ChevronRight, X } from 'lucide-react';
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
    onCancel: () => void;
}

const LAST_STEP = 2;

export function CreateAdminForm({ onCancel }: CreateAdminFormProps) {
    const { t } = useTranslations();
    const [currentStep, setCurrentStep] = useState(0);
    const [formData, setFormData] = useState<CreateAdminFormData>({
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
        setFormData((prev) => ({ ...prev, ...updates }));
    }

    function handleNext() {
        if (currentStep < LAST_STEP) {
            setCurrentStep((s) => s + 1);
        }
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
                        data={formData}
                        onChange={(updates) => handleChange(updates)}
                    />
                )}
                {currentStep === 1 && (
                    <ConnectProjectsStep
                        selectedProjectIds={formData.project_ids}
                        onSelectionChange={(ids) => handleChange({ project_ids: ids })}
                    />
                )}
                {currentStep === 2 && (
                    <ConnectAnnotatorsStep
                        selectedAnnotatorIds={formData.annotator_ids}
                        onSelectionChange={(ids) => handleChange({ annotator_ids: ids })}
                    />
                )}
            </div>

            <div className="flex items-center justify-end gap-3">
                <button
                    type="button"
                    onClick={onCancel}
                    className="focus-visible:ring-brand-blue-500 border-brand-blue-500 text-brand-blue-800 hover:bg-brand-blue-50 inline-flex h-10 items-center gap-1.5 rounded-lg border bg-white px-4 text-sm font-semibold hover:cursor-pointer focus-visible:ring-2 focus-visible:outline-none"
                >
                    <X className="h-4 w-4" aria-hidden="true" />
                    {t('users.actions.cancel')}
                </button>

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
                    disabled={currentStep === LAST_STEP}
                    className="focus-visible:ring-brand-blue-700 bg-brand-blue-700 hover:bg-brand-blue-800 inline-flex h-10 items-center gap-1.5 rounded-lg px-4 text-sm font-semibold text-white hover:cursor-pointer focus-visible:ring-2 focus-visible:outline-none disabled:cursor-not-allowed disabled:opacity-40"
                >
                    {currentStep === LAST_STEP
                        ? t('users.actions.create_admin')
                        : t('users.actions.next')}
                    <ChevronRight className="h-4 w-4" aria-hidden="true" />
                </button>
            </div>
        </section>
    );
}
