import {
    DateRangePickerButton,
    type DateRangeValue,
} from '@/components/ui/date-range-picker-button';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger } from '@/components/ui/select';
import { ToggleSwitch } from '@/components/ui/toggle-switch';
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
    const { t } = useTranslations();

    return (
        <section aria-labelledby="step-config-heading" className="flex flex-col gap-5">
            <h2 id="step-config-heading" className="sr-only">
                {t('sub-projects.configuration.heading')}
            </h2>

            <div className="rounded-2xl border border-slate-200 bg-white pt-5 pb-6">
                <div className="flex justify-center gap-24 px-6">
                    {/* ── Left column ────────────────────────────────────── */}
                    <div className="flex w-1/4 shrink-0 flex-col gap-7">
                        {/* Priority */}
                        <div className="flex flex-col gap-3">
                            <h3 className="text-lg font-semibold text-slate-800">
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
                                                {t(
                                                    `sub-projects.configuration.priority_${priority}`
                                                )}
                                            </span>
                                        </span>
                                    ) : (
                                        <span className="flex flex-1 items-center gap-2">
                                            <CircleAlert
                                                className="size-4 text-slate-400"
                                                aria-hidden="true"
                                            />
                                            <span className="text-sm text-slate-500">
                                                {t(
                                                    'sub-projects.configuration.priority_placeholder'
                                                )}
                                            </span>
                                        </span>
                                    )}
                                </SelectTrigger>
                                <SelectContent className="w-72">
                                    {(['low', 'medium', 'high'] as SubprojectPriority[]).map(
                                        (p) => (
                                            <SelectItem
                                                key={p}
                                                value={p}
                                                className="py-2.5 pr-8 pl-3 hover:cursor-pointer"
                                            >
                                                <span className="flex items-center gap-3">
                                                    <PriorityBadge priority={p} />
                                                    <span className="text-sm font-medium text-slate-800">
                                                        {t(
                                                            `sub-projects.configuration.priority_${p}`
                                                        )}
                                                    </span>
                                                </span>
                                            </SelectItem>
                                        )
                                    )}
                                </SelectContent>
                            </Select>
                        </div>

                        {/* Timeframe */}
                        <div className="flex flex-col gap-3">
                            <h3 className="text-lg font-semibold text-slate-800">
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
                            <h3 className="text-lg font-semibold text-slate-800">
                                {t('sub-projects.configuration.requirements_label')}
                            </h3>

                            <ToggleSwitch
                                id="min-annotations-toggle"
                                checked={minAnnotationsEnabled}
                                onChange={onMinAnnotationsEnabledChange}
                                label={t('sub-projects.configuration.min_annotations_label')}
                                description={t(
                                    'sub-projects.configuration.min_annotations_description'
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
                                        !minAnnotationsEnabled
                                            ? t(
                                                  'sub-projects.configuration.min_annotations_inactive'
                                              )
                                            : undefined
                                    }
                                    disabled={!minAnnotationsEnabled}
                                    onChange={(e) => onMinAnnotationsChange(Number(e.target.value))}
                                    aria-label={t(
                                        'sub-projects.configuration.min_annotations_label'
                                    )}
                                    className="h-10 bg-white px-3"
                                />
                            </div>
                        </div>
                    </div>

                    {/* ── Right column ───────────────────────────────────── */}
                    <div className="flex flex-1 flex-col gap-5">
                        <h3 className="text-lg font-semibold text-slate-800">
                            {t('sub-projects.configuration.browsing_label')}
                        </h3>

                        <ToggleSwitch
                            id="flexible-browsing-toggle"
                            checked={flexibleBrowsing}
                            onChange={onFlexibleBrowsingChange}
                            label={t('sub-projects.configuration.flexible_browsing_label')}
                            description={t(
                                'sub-projects.configuration.flexible_browsing_description'
                            )}
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
            </div>
        </section>
    );
}
