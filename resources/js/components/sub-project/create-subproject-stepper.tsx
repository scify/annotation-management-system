import { cn } from '@/lib/utils';
import { Check } from 'lucide-react';
import { Fragment } from 'react';

export interface CreateSubprojectStep {
	label: string;
}

interface CreateSubprojectStepperProps {
	/** 0-indexed current step */
	currentStep: number;
	steps: CreateSubprojectStep[];
}

export function CreateSubprojectStepper({ currentStep, steps }: CreateSubprojectStepperProps) {
	return (
		<nav aria-label="Create subproject progress" className="mb-4 flex items-start">
			{steps.map((step, index) => {
				const isActive = index === currentStep;
				const isCompleted = index < currentStep;

				return (
					<Fragment key={step.label}>
						{index > 0 && (
							<div
								aria-hidden="true"
								className={cn(
									'mt-5 h-px flex-1',
									isCompleted ? 'bg-brand-blue-700' : 'bg-slate-300'
								)}
							/>
						)}
						<div className="flex flex-col items-center gap-1.5">
							<div
								aria-current={isActive ? 'step' : undefined}
								className={cn(
									'border-brand-blue-700 flex size-10 items-center justify-center rounded-full border',
									isCompleted && 'bg-brand-blue-800',
									isActive && 'bg-brand-blue-100',
									!isActive && !isCompleted && 'bg-white'
								)}
							>
								{isCompleted ? (
									<Check className="size-5 text-white" aria-hidden="true" />
								) : (
									<span className="text-brand-blue-800 text-sm font-semibold">
										{index + 1}
									</span>
								)}
							</div>
							<span
								className={cn(
									'max-w-[120px] text-center text-xs text-slate-800',
									isActive || isCompleted ? 'font-semibold' : 'font-normal'
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
