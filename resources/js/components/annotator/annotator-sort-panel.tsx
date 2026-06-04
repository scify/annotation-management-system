import { useTranslations } from '@/hooks/use-translations';
import { cn } from '@/lib/utils';
import { ArrowUpDown, ChevronDown } from 'lucide-react';
import { useEffect, useRef, useState } from 'react';

export type AnnotatorSortKey =
    | 'username'
    | 'total_projects'
    | 'total_subprojects'
    | 'total_annotations'
    | 'total_flags'
    | 'status';

export type AnnotatorSortState = Record<AnnotatorSortKey, '' | 'asc' | 'desc'>;

export const DEFAULT_ANNOTATOR_SORT_STATE: AnnotatorSortState = {
    username: '',
    total_projects: '',
    total_subprojects: '',
    total_annotations: '',
    total_flags: '',
    status: '',
};

interface SortSectionConfig {
    key: AnnotatorSortKey;
    labelKey: string;
    ascLabel: string;
    descLabel: string;
}

function RadioCircle({ checked }: { checked: boolean }) {
    return (
        <span
            aria-hidden="true"
            className={cn(
                'flex size-4 shrink-0 items-center justify-center rounded-full border-2 transition-colors',
                checked ? 'border-brand-blue-700 bg-brand-blue-700' : 'border-slate-400 bg-white'
            )}
        >
            {checked && <span className="size-2 rounded-full bg-white" />}
        </span>
    );
}

interface RadioItemProps {
    groupName: string;
    value: string;
    checked: boolean;
    label: string;
    onChange: () => void;
}

function RadioItem({ groupName, value, checked, label, onChange }: RadioItemProps) {
    return (
        <label className="flex min-h-[40px] cursor-pointer items-center gap-2 rounded px-3 hover:bg-slate-50">
            <input
                type="radio"
                name={groupName}
                value={value}
                checked={checked}
                onChange={onChange}
                className="sr-only"
            />
            <RadioCircle checked={checked} />
            <span
                className={cn(
                    'flex-1 text-base text-slate-800',
                    checked ? 'font-semibold' : 'font-normal'
                )}
            >
                {label}
            </span>
        </label>
    );
}

interface AnnotatorSortPanelProps {
    state: AnnotatorSortState;
    onChange: (state: AnnotatorSortState) => void;
    hasActiveSort: boolean;
    onClear: () => void;
}

export function AnnotatorSortPanel({ state, onChange, hasActiveSort }: AnnotatorSortPanelProps) {
    const { t } = useTranslations();
    const [isOpen, setIsOpen] = useState(false);
    const wrapperRef = useRef<HTMLDivElement>(null);
    const ns = 'users.select_annotators' as const;

    useEffect(() => {
        const handleOutsideClick = (e: MouseEvent) => {
            if (wrapperRef.current && !wrapperRef.current.contains(e.target as Node)) {
                setIsOpen(false);
            }
        };
        document.addEventListener('mousedown', handleOutsideClick);
        return () => document.removeEventListener('mousedown', handleOutsideClick);
    }, []);

    const sections: SortSectionConfig[] = [
        {
            key: 'username',
            labelKey: `${ns}.table_username`,
            ascLabel: t(`${ns}.sort_asc_name`),
            descLabel: t(`${ns}.sort_desc_name`),
        },
        {
            key: 'total_projects',
            labelKey: `${ns}.table_total_projects`,
            ascLabel: t(`${ns}.sort_asc_workload`),
            descLabel: t(`${ns}.sort_desc_workload`),
        },
        {
            key: 'total_subprojects',
            labelKey: `${ns}.table_total_subprojects`,
            ascLabel: t(`${ns}.sort_asc_workload`),
            descLabel: t(`${ns}.sort_desc_workload`),
        },
        {
            key: 'total_annotations',
            labelKey: `${ns}.table_total_annotations`,
            ascLabel: t(`${ns}.sort_asc_workload`),
            descLabel: t(`${ns}.sort_desc_workload`),
        },
        {
            key: 'total_flags',
            labelKey: `${ns}.table_total_flags`,
            ascLabel: t(`${ns}.sort_asc_workload`),
            descLabel: t(`${ns}.sort_desc_workload`),
        },
        {
            key: 'status',
            labelKey: 'users.labels.status',
            ascLabel: t(`${ns}.sort_asc_name`),
            descLabel: t(`${ns}.sort_desc_name`),
        },
    ];

    const activeCount = Object.values(state).filter((v) => v !== '').length;
    const triggerLabel =
        activeCount > 0 ? `${t(`${ns}.sort_button`)} (${activeCount})` : t(`${ns}.sort_button`);

    return (
        <div ref={wrapperRef} className="relative">
            <button
                type="button"
                onClick={() => setIsOpen((o) => !o)}
                aria-expanded={isOpen}
                aria-haspopup="true"
                className={cn(
                    'flex h-9 min-w-62.5 items-center justify-between rounded-lg border bg-white px-4 text-sm transition-colors hover:cursor-pointer hover:bg-slate-50',
                    isOpen || hasActiveSort ? 'border-brand-blue-500' : 'border-slate-200',
                    hasActiveSort ? 'font-semibold text-slate-800' : 'font-medium text-slate-800'
                )}
            >
                <span className="flex items-center gap-2">
                    <ArrowUpDown
                        className="size-[18px] shrink-0 text-slate-600"
                        aria-hidden="true"
                    />
                    {triggerLabel}
                </span>
                <ChevronDown
                    className={cn(
                        'size-4 text-slate-500 transition-transform duration-200',
                        isOpen && 'rotate-180'
                    )}
                    aria-hidden="true"
                />
            </button>

            {isOpen && (
                <div className="absolute top-full left-0 z-50 mt-1 w-[280px] overflow-hidden rounded-lg border border-slate-200 bg-white shadow-lg">
                    {sections.map((section, idx) => (
                        <fieldset
                            key={section.key}
                            className={cn(
                                'px-4 py-4',
                                idx < sections.length - 1 && 'border-b border-slate-200'
                            )}
                        >
                            <legend className="field-legend mb-1 text-base font-semibold text-slate-800">
                                {t(section.labelKey)}
                            </legend>
                            <RadioItem
                                groupName={`sort-${section.key}`}
                                value="asc"
                                checked={state[section.key] === 'asc'}
                                label={section.ascLabel}
                                onChange={() => onChange({ ...state, [section.key]: 'asc' })}
                            />
                            <RadioItem
                                groupName={`sort-${section.key}`}
                                value="desc"
                                checked={state[section.key] === 'desc'}
                                label={section.descLabel}
                                onChange={() => onChange({ ...state, [section.key]: 'desc' })}
                            />
                            <RadioItem
                                groupName={`sort-${section.key}`}
                                value=""
                                checked={state[section.key] === ''}
                                label={t(`${ns}.sort_not_selected`)}
                                onChange={() => onChange({ ...state, [section.key]: '' })}
                            />
                        </fieldset>
                    ))}
                </div>
            )}
        </div>
    );
}
