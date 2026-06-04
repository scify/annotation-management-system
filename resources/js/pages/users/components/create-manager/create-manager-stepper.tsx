import { cn } from '@/lib/utils';
import { Check } from 'lucide-react';
import { Fragment } from 'react';

export interface CreateManagerStep {
    label: string;
}

interface CreateManagerStepperProps {
    currentStep: number;
    steps: CreateManagerStep[];
    stepsWithErrors?: number[];
    ariaLabel?: string;
}

export function CreateManagerStepper({
    currentStep,
    steps,
    stepsWithErrors = [],
    ariaLabel = 'Create manager progress',
}: CreateManagerStepperProps) {
    return (
        <nav aria-label={ariaLabel} className="mb-6 flex items-center">
            {steps.map((step, index) => {
                const isActive = index === currentStep;
                const isCompleted = index < currentStep;
                const hasError = stepsWithErrors.includes(index);

                return (
                    <Fragment key={step.label}>
                        {index > 0 && (
                            <div
                                aria-hidden="true"
                                className={cn(
                                    'h-px flex-1',
                                    isCompleted && !hasError ? 'bg-brand-blue-700' : 'bg-slate-300'
                                )}
                            />
                        )}
                        <div className="flex items-center gap-2">
                            <div
                                aria-current={isActive ? 'step' : undefined}
                                className={cn(
                                    'flex size-10 shrink-0 items-center justify-center rounded-full border',
                                    hasError
                                        ? 'border-red-600 bg-red-600'
                                        : cn(
                                              'border-brand-blue-700',
                                              isCompleted && 'bg-brand-blue-800',
                                              isActive && 'bg-brand-blue-100',
                                              !isActive && !isCompleted && 'bg-white'
                                          )
                                )}
                            >
                                {hasError ? (
                                    <span
                                        className="text-sm font-bold text-white"
                                        aria-label="has errors"
                                    >
                                        !
                                    </span>
                                ) : isCompleted ? (
                                    <Check className="size-5 text-white" aria-hidden="true" />
                                ) : (
                                    <span className="text-brand-blue-800 text-sm font-semibold">
                                        {index + 1}
                                    </span>
                                )}
                            </div>
                            <span
                                className={cn(
                                    'text-sm whitespace-nowrap',
                                    hasError
                                        ? 'font-semibold text-red-600'
                                        : cn(
                                              'text-slate-800',
                                              isActive || isCompleted
                                                  ? 'font-semibold'
                                                  : 'font-normal'
                                          )
                                )}
                            >
                                {step.label}
                            </span>
                        </div>
                    </Fragment>
                );
            })}
        </nav>
    );
}
