import { type TaskTypeCardData } from '@/components/project/select-task-type-step';
import {
    DateRangePickerButton,
    type DateRangeValue,
} from '@/components/ui/date-range-picker-button';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { useTranslations } from '@/hooks/use-translations';
import { cn } from '@/lib/utils';
import {
    ChevronLeft,
    ChevronRight,
    Database,
    ExternalLink,
    FileText,
    Info,
    Minus,
    Plus,
} from 'lucide-react';
import { useEffect, useState } from 'react';

// ── Types ─────────────────────────────────────────────────────────────────────

export interface ProjectConfigurationStepProps {
    selectedTaskType: TaskTypeCardData | null;
    selectedDatasetId: number | null;
    shuffleInstances: boolean;
    customizationAnswers: Record<number, string>;
    restrictVisibility: boolean;
    dateRange: DateRangeValue | null;
    onDatasetChange: (id: number) => void;
    onShuffleChange: (enabled: boolean) => void;
    onCustomizationAnswerChange: (id: number, answer: string) => void;
    onVisibilityChange: (restricted: boolean) => void;
    onDateRangeChange: (value: DateRangeValue | null) => void;
}

const PAGE_SIZE = 3;

// ── Toggle switch ─────────────────────────────────────────────────────────────

interface ToggleSwitchProps {
    id: string;
    checked: boolean;
    onChange: (checked: boolean) => void;
    label: string;
}

function ToggleSwitch({ id, checked, onChange, label }: Readonly<ToggleSwitchProps>) {
    return (
        <label htmlFor={id} className="flex cursor-pointer items-center gap-2">
            <span className="relative inline-flex shrink-0">
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
            <span className="text-sm font-medium text-slate-800">{label}</span>
        </label>
    );
}

// ── Left sidebar ──────────────────────────────────────────────────────────────

interface LeftSidebarProps {
    taskType: TaskTypeCardData | null;
}

function LeftSidebar({ taskType }: Readonly<LeftSidebarProps>) {
    const { t } = useTranslations();
    const [descriptionOpen, setDescriptionOpen] = useState(false);

    return (
        <div className="flex w-[291px] shrink-0 flex-col gap-3">
            {/* Task type summary card */}
            <div className="flex flex-col gap-4 rounded-2xl border border-slate-200 bg-slate-100 p-6">
                <FileText className="size-12 text-slate-400" aria-hidden="true" />
                <p className="text-xl leading-9 font-bold text-slate-900">
                    {t('projects.configuration.task_type_label')}
                </p>
                <p className="text-xl leading-9 text-slate-900">{taskType?.title ?? '—'}</p>
            </div>

            {/* View Guidelines */}
            <a
                href={taskType?.guidelines_url ?? '#'}
                target="_blank"
                rel="noopener noreferrer"
                className="bg-brand-blue-700 hover:bg-brand-blue-800 flex h-10 w-full items-center justify-center gap-1.5 rounded-lg transition-colors"
            >
                <span className="text-sm font-semibold text-white">
                    {t('projects.configuration.view_guidelines')}
                </span>
                <ExternalLink className="size-3.5 text-white" aria-hidden="true" />
            </a>

            {/* Description accordion */}
            <div className="flex flex-col gap-1.5">
                <button
                    type="button"
                    onClick={() => setDescriptionOpen((o) => !o)}
                    className="border-brand-blue-400 flex h-10 w-full items-center justify-between rounded-lg border bg-white px-4 text-base font-semibold text-slate-800 hover:cursor-pointer"
                    aria-expanded={descriptionOpen}
                >
                    {t('projects.configuration.description_label')}
                    {descriptionOpen ? (
                        <Minus className="size-5 shrink-0" aria-hidden="true" />
                    ) : (
                        <Plus className="size-5 shrink-0" aria-hidden="true" />
                    )}
                </button>
                {descriptionOpen && (
                    <div className="border-brand-blue-400 rounded-lg border bg-white px-4 py-5">
                        <p className="text-sm leading-5 text-slate-800">
                            {taskType?.description ?? '—'}
                        </p>
                    </div>
                )}
            </div>
        </div>
    );
}

// ── Dataset card ──────────────────────────────────────────────────────────────

type DatasetItem = TaskTypeCardData['datasets'][number];

interface DatasetCardProps {
    dataset: DatasetItem;
    isSelected: boolean;
    onSelect: () => void;
}

function DatasetCard({ dataset, isSelected, onSelect }: Readonly<DatasetCardProps>) {
    const { trans } = useTranslations();

    return (
        <div
            role="radio"
            aria-checked={isSelected}
            tabIndex={0}
            onClick={onSelect}
            onKeyDown={(e) => {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    onSelect();
                }
            }}
            className={cn(
                'focus-visible:ring-brand-blue-700 flex flex-1 cursor-pointer gap-3 rounded-xl border px-3 py-5 transition-colors outline-none focus-visible:ring-2',
                isSelected
                    ? 'border-brand-blue-400 bg-brand-blue-50'
                    : 'border-brand-blue-400 bg-white hover:bg-slate-50'
            )}
        >
            {/* Radio indicator */}
            <span
                aria-hidden="true"
                className={cn(
                    'mt-0.5 flex size-4 shrink-0 items-center justify-center rounded-full border-2',
                    isSelected ? 'border-brand-blue-700' : 'border-slate-300'
                )}
            >
                {isSelected && <span className="bg-brand-blue-700 size-2 rounded-full" />}
            </span>

            <div className="flex flex-col gap-3">
                <Database className="size-9 text-slate-400" aria-hidden="true" />
                <p className="text-base leading-5 font-bold text-slate-800">{dataset.name}</p>
                <p
                    className={cn(
                        'text-sm leading-5 font-semibold',
                        isSelected ? 'text-brand-blue-800' : 'text-brand-blue-700'
                    )}
                >
                    {trans('projects.configuration.dataset_instances', {
                        count: dataset.instances_count.toLocaleString(),
                    })}
                </p>
                <p className="text-sm leading-5 text-slate-500">{dataset.description}</p>
            </div>
        </div>
    );
}

// ── Main component ────────────────────────────────────────────────────────────

export function ProjectConfigurationStep({
    selectedTaskType,
    selectedDatasetId,
    shuffleInstances,
    customizationAnswers,
    restrictVisibility,
    dateRange,
    onDatasetChange,
    onShuffleChange,
    onCustomizationAnswerChange,
    onVisibilityChange,
    onDateRangeChange,
}: ProjectConfigurationStepProps) {
    const { t } = useTranslations();
    const datasets = selectedTaskType?.datasets ?? [];
    const customizationOptions = selectedTaskType?.customization_options ?? [];
    const hasAnyCustomization = customizationOptions.length > 0;

    const [page, setPage] = useState(0);

    useEffect(() => {
        setPage(0);
    }, [selectedTaskType?.id]);

    const totalPages = Math.ceil(datasets.length / PAGE_SIZE);
    const visibleDatasets = datasets.slice(page * PAGE_SIZE, (page + 1) * PAGE_SIZE);

    return (
        <div className="flex gap-6">
            {/* Left sidebar */}
            <LeftSidebar taskType={selectedTaskType} />

            {/* Right content */}
            <div className="flex flex-1 flex-col gap-4">
                {/* Dataset card */}
                <section
                    aria-labelledby="dataset-heading"
                    className="flex flex-col gap-4 rounded-2xl border border-slate-200 bg-white px-6 pt-7 pb-3"
                >
                    <h2 id="dataset-heading" className="text-lg font-semibold text-slate-800">
                        {t('projects.configuration.dataset_section')}
                    </h2>

                    <div
                        role="radiogroup"
                        aria-label={t('projects.configuration.dataset_section')}
                        className="flex gap-3"
                    >
                        {visibleDatasets.map((ds) => (
                            <DatasetCard
                                key={ds.id}
                                dataset={ds}
                                isSelected={selectedDatasetId === ds.id}
                                onSelect={() => onDatasetChange(ds.id)}
                            />
                        ))}
                    </div>

                    {/* Shuffle toggle */}
                    <div className="flex items-center gap-1">
                        <ToggleSwitch
                            id="shuffle-instances"
                            checked={shuffleInstances}
                            onChange={onShuffleChange}
                            label={t('projects.configuration.shuffle_instances')}
                        />
                        <span title={t('projects.configuration.shuffle_instances_hint')}>
                            <Info className="size-4 text-slate-400" aria-hidden="true" />
                        </span>
                    </div>

                    {/* Dataset pagination */}
                    <div className="flex items-center justify-between">
                        <button
                            type="button"
                            onClick={() => setPage((p) => Math.max(0, p - 1))}
                            disabled={page === 0}
                            aria-label="Previous datasets"
                            className={cn(
                                'bg-brand-blue-50 flex size-[30px] items-center justify-center rounded-lg transition-opacity',
                                page === 0 && 'opacity-30'
                            )}
                        >
                            <ChevronLeft
                                className="text-brand-blue-700 size-4"
                                aria-hidden="true"
                            />
                        </button>
                        <button
                            type="button"
                            onClick={() => setPage((p) => Math.min(totalPages - 1, p + 1))}
                            disabled={page >= totalPages - 1}
                            aria-label="Next datasets"
                            className={cn(
                                'bg-brand-blue-50 flex size-[30px] items-center justify-center rounded-lg transition-opacity',
                                page >= totalPages - 1 && 'opacity-30'
                            )}
                        >
                            <ChevronRight
                                className="text-brand-blue-700 size-4"
                                aria-hidden="true"
                            />
                        </button>
                    </div>
                </section>

                {/* Date Range card */}
                <section
                    aria-labelledby="date-range-heading"
                    className="flex flex-col gap-2 rounded-2xl border border-slate-200 bg-white px-6 pt-7 pb-6"
                >
                    <h2 id="date-range-heading" className="text-lg font-semibold text-slate-800">
                        {t('projects.configuration.date_range_section')}
                    </h2>
                    <DateRangePickerButton
                        value={dateRange}
                        onChange={onDateRangeChange}
                        placeholder={t('projects.configuration.date_range_placeholder')}
                        aria-label={t('projects.configuration.date_range_section')}
                        className="w-full"
                    />
                </section>

                {/* Annotation card — only shown when there are customization options */}
                {hasAnyCustomization && (
                    <section
                        aria-labelledby="annotation-heading"
                        className="flex flex-col gap-11 rounded-2xl border border-slate-200 bg-white px-6 pt-7 pb-6"
                    >
                        <h2
                            id="annotation-heading"
                            className="text-lg font-semibold text-slate-800"
                        >
                            {t('projects.configuration.annotation_section')}
                        </h2>

                        <p className="text-sm leading-5 text-slate-800">
                            {selectedTaskType?.description ?? '—'}
                        </p>

                        {customizationOptions.map((option) => (
                            <div key={option.id} className="flex flex-col gap-4">
                                <p className="text-base font-semibold text-black">
                                    {option.question}
                                </p>
                                <Select
                                    aria-label={option.question}
                                    value={customizationAnswers[option.id] ?? option.answers[0]}
                                    onValueChange={(answer) =>
                                        onCustomizationAnswerChange(option.id, answer)
                                    }
                                >
                                    <SelectTrigger className="w-44">
                                        <SelectValue />
                                    </SelectTrigger>
                                    <SelectContent>
                                        {option.answers.map((answer) => (
                                            <SelectItem key={answer} value={answer}>
                                                {answer}
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                            </div>
                        ))}
                    </section>
                )}

                {/* Project Visibility card */}
                <section
                    aria-labelledby="visibility-heading"
                    className="flex flex-col gap-6 rounded-2xl border border-slate-200 bg-white px-6 pt-7 pb-6"
                >
                    <h2 id="visibility-heading" className="text-lg font-semibold text-slate-800">
                        {t('projects.configuration.visibility_section')}
                    </h2>
                    <p className="text-sm leading-5 text-slate-800">
                        {t('projects.configuration.visibility_description')}
                    </p>
                    <ToggleSwitch
                        id="restrict-visibility"
                        checked={restrictVisibility}
                        onChange={onVisibilityChange}
                        label={t('projects.configuration.visibility_toggle_label')}
                    />
                </section>
            </div>
        </div>
    );
}
