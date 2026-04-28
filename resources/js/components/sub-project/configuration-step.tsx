import {
	DateRangePickerButton,
	type DateRangeValue,
} from '@/components/ui/date-range-picker-button';
import { Input } from '@/components/ui/input';
import {
	Select,
	SelectContent,
	SelectItem,
	SelectTrigger,
	SelectValue,
} from '@/components/ui/select';
import { useTranslations } from '@/hooks/use-translations';
import { cn } from '@/lib/utils';
import { ArrowDown, ArrowUp, CircleAlert, Ellipsis } from 'lucide-react';

export type SubprojectPriority = 'low' | 'medium' | 'high';
export type SubmissionMode = 'auto' | 'manual';

export interface ConfigurationStepProps {
	priority: SubprojectPriority | null;
	dateRange: DateRangeValue | null;
	minAnnotationsEnabled: boolean;
	minAnnotations: number;
	annotatorCount: number;
	flexibleBrowsing: boolean;
	submissionMode: SubmissionMode;
	onPriorityChange: (value: SubprojectPriority) => void;
	onDateRangeChange: (value: DateRangeValue | null) => void;
	onMinAnnotationsEnabledChange: (value: boolean) => void;
	onMinAnnotationsChange: (value: number) => void;
	onFlexibleBrowsingChange: (value: boolean) => void;
	onSubmissionModeChange: (value: SubmissionMode) => void;
}

// ── Priority icon badge ───────────────────────────────────────────────────────

interface PriorityBadgeProps {
	priority: SubprojectPriority;
	size?: 'sm' | 'md';
}

export function PriorityBadge({ priority, size = 'md' }: Readonly<PriorityBadgeProps>) {
	const config: Record<SubprojectPriority, { bg: string; icon: React.ReactNode }> = {
		low: {
			bg: 'bg-brand-lime-500',
			icon: <ArrowDown className="size-3.5 text-white" aria-hidden="true" />,
		},
		medium: {
			bg: 'bg-brand-orange-500',
			icon: <Ellipsis className="size-3.5 text-white" aria-hidden="true" />,
		},
		high: {
			bg: 'bg-brand-red-600',
			icon: <ArrowUp className="size-3.5 text-white" aria-hidden="true" />,
		},
	};

	const { bg, icon } = config[priority];

	return (
		<span
			className={cn(
				'flex items-center justify-center rounded-md',
				bg,
				size === 'md' ? 'size-6' : 'size-5'
			)}
			aria-hidden="true"
		>
			{icon}
		</span>
	);
}

// ── Toggle switch ─────────────────────────────────────────────────────────────

interface ToggleSwitchProps {
	id: string;
	checked: boolean;
	onChange: (checked: boolean) => void;
	label: string;
	description?: string;
}

export function ToggleSwitch({
	id,
	checked,
	onChange,
	label,
	description,
}: Readonly<ToggleSwitchProps>) {
	return (
		<label htmlFor={id} className="flex cursor-pointer items-start gap-3">
			{/* Pill track */}
			<span className="relative mt-0.5 inline-flex shrink-0">
				<input
					id={id}
					type="checkbox"
					role="switch"
					aria-checked={checked}
					checked={checked}
					onChange={(e) => onChange(e.target.checked)}
					className="peer sr-only"
				/>
				<span
					aria-hidden="true"
					className={cn(
						'flex h-6 w-11 items-center rounded-full border-2 border-transparent transition-colors',
						'peer-focus-visible:ring-brand-blue-700/30 peer-focus-visible:ring-4',
						checked ? 'bg-brand-blue-700' : 'bg-slate-200'
					)}
				>
					<span
						className={cn(
							'size-4 rounded-full bg-white shadow-sm transition-transform',
							checked ? 'translate-x-5' : 'translate-x-1'
						)}
					/>
				</span>
			</span>
			<span className="flex flex-col gap-0.5">
				<span className="text-sm font-semibold text-slate-800">{label}</span>
				{description && <span className="text-sm text-slate-500">{description}</span>}
			</span>
		</label>
	);
}

// ── Main component ────────────────────────────────────────────────────────────

export function ConfigurationStep({
	priority,
	dateRange,
	minAnnotationsEnabled,
	minAnnotations,
	annotatorCount,
	flexibleBrowsing,
	submissionMode,
	onPriorityChange,
	onDateRangeChange,
	onMinAnnotationsEnabledChange,
	onMinAnnotationsChange,
	onFlexibleBrowsingChange,
	onSubmissionModeChange,
}: Readonly<ConfigurationStepProps>) {
	const { t, trans } = useTranslations();

	return (
		<section aria-labelledby="step-config-heading" className="flex flex-col gap-5">
			<h2 id="step-config-heading" className="sr-only">
				{t('sub-projects.configuration.heading')}
			</h2>

			<div className="flex gap-x-36">
				{/* ── Left column ────────────────────────────────────── */}
				<div className="flex w-1/4 shrink-0 flex-col gap-6">
					{/* Priority */}
					<div className="flex flex-col gap-3">
						<h3 className="text-xl font-semibold text-slate-800">
							{t('sub-projects.configuration.priority_label')}
						</h3>

						<Select
							aria-label={t('sub-projects.configuration.priority_label')}
							value={priority ?? undefined}
							onValueChange={(v) => onPriorityChange(v as SubprojectPriority)}
						>
							<SelectTrigger
								aria-label={t('sub-projects.configuration.priority_label')}
								className="h-10 w-full gap-2 border-slate-200 px-3 hover:cursor-pointer [&>span]:!flex [&>span]:!overflow-visible"
							>
								{priority ? (
									<span className="flex flex-1 items-center gap-2">
										<PriorityBadge priority={priority} size="sm" />
										<span className="text-sm font-medium text-slate-800">
											{t(`sub-projects.configuration.priority_${priority}`)}
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
											className="text-sm hover:cursor-pointer"
										/>
									</span>
								)}
							</SelectTrigger>
							<SelectContent className="w-72">
								{(['low', 'medium', 'high'] as SubprojectPriority[]).map((p) => (
									<SelectItem
										key={p}
										value={p}
										className="py-2.5 pr-8 pl-3 hover:cursor-pointer"
									>
										<span className="flex items-center gap-3">
											<PriorityBadge priority={p} />
											<span className="text-sm font-medium text-slate-800">
												{t(`sub-projects.configuration.priority_${p}`)}
											</span>
										</span>
									</SelectItem>
								))}
							</SelectContent>
						</Select>
					</div>

					{/* Timeframe */}
					<div className="flex flex-col gap-3">
						<h3 className="text-xl font-semibold text-slate-800">
							{t('sub-projects.configuration.timeframe_label')}
						</h3>

						<DateRangePickerButton
							value={dateRange}
							onChange={onDateRangeChange}
							placeholder={t('sub-projects.configuration.timeframe_placeholder')}
							aria-label={t('sub-projects.configuration.timeframe_label')}
						/>
					</div>

					{/* Requirements */}
					<div className="flex flex-col gap-3">
						<h3 className="text-xl font-semibold text-slate-800">
							{t('sub-projects.configuration.requirements_label')}
						</h3>

						<ToggleSwitch
							id="min-annotations-toggle"
							checked={minAnnotationsEnabled}
							onChange={onMinAnnotationsEnabledChange}
							label={t('sub-projects.configuration.min_annotations_label')}
							description={trans(
								'sub-projects.configuration.min_annotations_placeholder',
								{ max: annotatorCount }
							)}
						/>

						<div
							className={cn(
								'transition-opacity',
								!minAnnotationsEnabled && 'pointer-events-none opacity-50'
							)}
						>
							<Input
								id="min-annotations-count"
								type="number"
								inputMode="numeric"
								min={1}
								max={annotatorCount || undefined}
								value={minAnnotationsEnabled ? minAnnotations : ''}
								placeholder={
									minAnnotationsEnabled
										? trans(
												'sub-projects.configuration.min_annotations_placeholder',
												{ max: annotatorCount }
											)
										: t('sub-projects.configuration.min_annotations_inactive')
								}
								disabled={!minAnnotationsEnabled}
								onChange={(e) => onMinAnnotationsChange(Number(e.target.value))}
								aria-label={t(
									'sub-projects.configuration.min_annotations_placeholder'
								)}
								className="h-10 bg-white px-3"
							/>
						</div>
					</div>
				</div>

				{/* ── Right column ───────────────────────────────────── */}
				<div className="flex flex-1 flex-col gap-3">
					<h3 className="text-xl font-semibold text-slate-800">
						{t('sub-projects.configuration.browsing_label')}
					</h3>

					<ToggleSwitch
						id="flexible-browsing-toggle"
						checked={flexibleBrowsing}
						onChange={onFlexibleBrowsingChange}
						label={t('sub-projects.configuration.flexible_browsing_label')}
						description={t('sub-projects.configuration.flexible_browsing_description')}
					/>

					{/* Submission mode radio cards */}
					<fieldset
						className={cn(
							'mt-2 flex flex-col gap-3 transition-opacity',
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
									name="submission-mode"
									value={mode}
									checked={submissionMode === mode}
									onChange={() => onSubmissionModeChange(mode)}
									disabled={!flexibleBrowsing}
									className="accent-brand-blue-700 mt-0.5 size-4 shrink-0"
								/>
								<span className="text-sm font-medium text-slate-800">
									{t(`sub-projects.configuration.submission_${mode}`)}
								</span>
							</label>
						))}
					</fieldset>
				</div>
			</div>
		</section>
	);
}
