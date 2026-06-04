import { Checkbox } from '@/components/ui/checkbox';
import { useTranslations } from '@/hooks/use-translations';
import { cn } from '@/lib/utils';
import { ChevronDown, SlidersHorizontal } from 'lucide-react';
import { useEffect, useRef, useState } from 'react';

export type AnnotatorFilterState = {
    statuses: string[];
};

interface AnnotatorFilterPanelProps {
    statusOptions: string[];
    selected: AnnotatorFilterState;
    onToggle: (value: string) => void;
    onClear: () => void;
    hasActiveFilters: boolean;
}

export function AnnotatorFilterPanel({
    statusOptions,
    selected,
    onToggle,
    hasActiveFilters,
}: AnnotatorFilterPanelProps) {
    const { t } = useTranslations();
    const [isOpen, setIsOpen] = useState(false);
    const wrapperRef = useRef<HTMLDivElement>(null);

    useEffect(() => {
        const handleOutsideClick = (e: MouseEvent) => {
            if (wrapperRef.current && !wrapperRef.current.contains(e.target as Node)) {
                setIsOpen(false);
            }
        };
        document.addEventListener('mousedown', handleOutsideClick);
        return () => document.removeEventListener('mousedown', handleOutsideClick);
    }, []);

    const activeCount = selected.statuses.length;
    const ns = 'users.select_annotators' as const;
    const triggerLabel =
        activeCount > 0 ? `${t(`${ns}.filter_button`)} (${activeCount})` : t(`${ns}.filter_button`);

    return (
        <div ref={wrapperRef} className="relative">
            <button
                type="button"
                onClick={() => setIsOpen((o) => !o)}
                aria-expanded={isOpen}
                aria-haspopup="true"
                className={cn(
                    'flex h-9 min-w-62.5 items-center justify-between rounded-lg border bg-white px-4 text-sm transition-colors hover:cursor-pointer hover:bg-slate-50',
                    isOpen || hasActiveFilters ? 'border-brand-blue-500' : 'border-slate-200',
                    hasActiveFilters ? 'font-semibold text-slate-800' : 'font-medium text-slate-800'
                )}
            >
                <span className="flex items-center gap-2">
                    <SlidersHorizontal
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
                    <div className="flex flex-col gap-1 px-4 py-4">
                        <p className="pb-1 text-base font-semibold text-slate-800">
                            {t(`${ns}.filter_status_section`)}
                        </p>
                        <div role="group" aria-label={t(`${ns}.filter_status_section`)}>
                            {statusOptions.map((item) => {
                                const checked = selected.statuses.includes(item);
                                const id = `annotator-filter-status-${item}`;
                                return (
                                    <div
                                        key={item}
                                        className="flex min-h-[40px] items-center gap-2 rounded px-2 py-1.5 hover:bg-slate-50"
                                    >
                                        <Checkbox
                                            id={id}
                                            checked={checked}
                                            onCheckedChange={() => onToggle(item)}
                                        />
                                        <label
                                            htmlFor={id}
                                            className={cn(
                                                'min-w-0 flex-1 cursor-pointer text-base text-slate-800 select-none',
                                                checked ? 'font-semibold' : 'font-normal'
                                            )}
                                        >
                                            {item}
                                        </label>
                                    </div>
                                );
                            })}
                        </div>
                    </div>
                </div>
            )}
        </div>
    );
}
