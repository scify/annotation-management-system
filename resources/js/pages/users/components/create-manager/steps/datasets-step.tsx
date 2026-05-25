import { type TaskTypeCardData } from '@/components/project/select-task-type-step';
import { useTranslations } from '@/hooks/use-translations';
import { cn } from '@/lib/utils';
import { Check, ChevronDown, ChevronUp, Container, Database } from 'lucide-react';
import { useState } from 'react';

type DatasetEntry = TaskTypeCardData['datasets'][number];

interface DatasetCardProps {
    dataset: DatasetEntry;
    isSelected: boolean;
    onToggle: () => void;
}

function DatasetCard({ dataset, isSelected, onToggle }: Readonly<DatasetCardProps>) {
    const { trans } = useTranslations();

    return (
        <div
            role="checkbox"
            aria-checked={isSelected}
            tabIndex={0}
            onClick={onToggle}
            onKeyDown={(e) => {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    onToggle();
                }
            }}
            className="focus-visible:ring-brand-blue-700 bg-brand-blue-50 relative flex cursor-pointer flex-col gap-3 rounded-xl border border-slate-200 p-5 transition-colors outline-none hover:bg-slate-50 focus-visible:ring-2"
        >
            <div className="flex items-start gap-3">
                <span
                    aria-hidden="true"
                    className={cn(
                        'mt-0.5 flex size-[18px] shrink-0 items-center justify-center rounded border-2',
                        isSelected
                            ? 'border-brand-blue-700 bg-brand-blue-700'
                            : 'border-slate-300 bg-white'
                    )}
                >
                    {isSelected && <Check className="size-3 text-white" strokeWidth={3} />}
                </span>
                <Database className="size-9 shrink-0 text-slate-400" aria-hidden="true" />
            </div>

            <div className="flex flex-col gap-1.5">
                <p className="text-base leading-snug font-medium text-slate-800">{dataset.name}</p>
                <p className="text-brand-blue-700 text-sm font-semibold">
                    {trans('users.datasets.instances', {
                        count: dataset.instances_count.toLocaleString(),
                    })}
                </p>
                <p className="line-clamp-2 text-sm leading-5 text-slate-500">
                    {dataset.description}
                </p>
            </div>
        </div>
    );
}

interface AccordionSectionProps {
    taskType: TaskTypeCardData;
    isExpanded: boolean;
    onToggleExpand: () => void;
    selectedDatasetIds: number[];
    onToggleDataset: (id: number) => void;
}

function AccordionSection({
    taskType,
    isExpanded,
    onToggleExpand,
    selectedDatasetIds,
    onToggleDataset,
}: Readonly<AccordionSectionProps>) {
    const headerId = `task-type-header-${taskType.id}`;
    const panelId = `task-type-panel-${taskType.id}`;

    return (
        <div className="overflow-hidden rounded-xl border border-slate-200 bg-white">
            <button
                type="button"
                id={headerId}
                aria-expanded={isExpanded}
                aria-controls={panelId}
                onClick={onToggleExpand}
                className="focus-visible:ring-brand-blue-700 flex w-full items-center gap-3 px-5 py-4 text-left hover:bg-slate-50 focus-visible:ring-2 focus-visible:outline-none"
            >
                <Container className="size-5 shrink-0 text-slate-400" aria-hidden="true" />
                <span className="flex-1 text-sm font-semibold text-slate-800">
                    {taskType.title}
                </span>
                {isExpanded ? (
                    <ChevronUp className="size-5 shrink-0 text-slate-400" aria-hidden="true" />
                ) : (
                    <ChevronDown className="size-5 shrink-0 text-slate-400" aria-hidden="true" />
                )}
            </button>

            {isExpanded && (
                <div
                    id={panelId}
                    role="group"
                    aria-labelledby={headerId}
                    className="grid grid-cols-4 gap-4 border-t border-slate-200 p-5"
                >
                    {taskType.datasets.map((dataset) => (
                        <DatasetCard
                            key={dataset.id}
                            dataset={dataset}
                            isSelected={selectedDatasetIds.includes(dataset.id)}
                            onToggle={() => onToggleDataset(dataset.id)}
                        />
                    ))}
                </div>
            )}
        </div>
    );
}

interface DatasetsStepProps {
    taskTypes: TaskTypeCardData[];
    selectedDatasetIds: number[];
    onSelectionChange: (ids: number[]) => void;
}

export function DatasetsStep({
    taskTypes,
    selectedDatasetIds,
    onSelectionChange,
}: DatasetsStepProps) {
    const { t } = useTranslations();
    const [expandedIds, setExpandedIds] = useState<Set<number>>(
        () => new Set(taskTypes.map((tt) => tt.id))
    );

    function toggleExpand(id: number) {
        setExpandedIds((prev) => {
            const next = new Set(prev);
            if (next.has(id)) {
                next.delete(id);
            } else {
                next.add(id);
            }
            return next;
        });
    }

    function toggleDataset(id: number) {
        onSelectionChange(
            selectedDatasetIds.includes(id)
                ? selectedDatasetIds.filter((x) => x !== id)
                : [...selectedDatasetIds, id]
        );
    }

    return (
        <div className="flex flex-col gap-4">
            <h2 className="text-xl font-medium text-slate-800">{t('users.datasets.heading')}</h2>

            {taskTypes.length === 0 ? (
                <div className="flex items-center justify-center rounded-xl border border-slate-200 bg-white p-14">
                    <p className="text-sm text-slate-400">{t('users.datasets.no_task_types')}</p>
                </div>
            ) : (
                <div className="flex flex-col gap-3">
                    {taskTypes.map((tt) => (
                        <AccordionSection
                            key={tt.id}
                            taskType={tt}
                            isExpanded={expandedIds.has(tt.id)}
                            onToggleExpand={() => toggleExpand(tt.id)}
                            selectedDatasetIds={selectedDatasetIds}
                            onToggleDataset={toggleDataset}
                        />
                    ))}
                </div>
            )}
        </div>
    );
}
