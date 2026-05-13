import { AnnotatorsTable } from '@/components/annotator/annotators-table';
import { type ProjectAnnotatorRowData } from '@/components/annotator/annotators-table';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { useTranslations } from '@/hooks/use-translations';
import { Search } from 'lucide-react';
import { useMemo, useState } from 'react';

interface SelectAnnotatorsStepProps {
    annotators: ProjectAnnotatorRowData[];
    /** When present (even as []), shows the "show only my annotators" toggle */
    myAnnotators?: ProjectAnnotatorRowData[];
    selectedIds: Set<number>;
    onSelectionChange: (id: number, checked: boolean) => void;
    onSelectAllChange: (ids: number[], checked: boolean) => void;
    /** @default 'sub-projects' */
    translationNamespace?: 'sub-projects' | 'projects';
}

export function SelectAnnotatorsStep({
    annotators,
    myAnnotators,
    selectedIds,
    onSelectionChange,
    onSelectAllChange,
    translationNamespace = 'sub-projects',
}: SelectAnnotatorsStepProps) {
    const { t, trans } = useTranslations();
    const [sortByName, setSortByName] = useState('');
    const [sortByWorkload, setSortByWorkload] = useState('');
    const [search, setSearch] = useState('');
    const [showMyOnly, setShowMyOnly] = useState(false);

    const ns = `${translationNamespace}.select_annotators` as const;
    const hasMyAnnotatorsToggle = myAnnotators !== undefined;
    const baseAnnotators = hasMyAnnotatorsToggle && showMyOnly ? myAnnotators : annotators;

    const filteredAnnotators = useMemo(() => {
        let result = [...baseAnnotators];

        if (search.trim()) {
            const query = search.toLowerCase();
            result = result.filter((a) => a.name.toLowerCase().includes(query));
        }

        if (sortByName === 'asc') result.sort((a, b) => a.name.localeCompare(b.name));
        if (sortByName === 'desc') result.sort((a, b) => b.name.localeCompare(a.name));
        if (sortByWorkload === 'asc') result.sort((a, b) => a.workload - b.workload);
        if (sortByWorkload === 'desc') result.sort((a, b) => b.workload - a.workload);

        return result;
    }, [baseAnnotators, search, sortByName, sortByWorkload]);

    const allFilteredSelected =
        filteredAnnotators.length > 0 && filteredAnnotators.every((a) => selectedIds.has(a.id));

    function handleSelectAll(checked: boolean) {
        onSelectAllChange(
            filteredAnnotators.map((a) => a.id),
            checked
        );
    }

    return (
        <section aria-labelledby="step-heading" className="flex flex-col gap-5">
            <hgroup>
                <h2 id="step-heading" className="page-subtitle">
                    {t(`${ns}.heading`)}
                </h2>
                <p className="text-sm font-semibold text-slate-800">
                    {trans(`${ns}.selected_count`, {
                        count: selectedIds.size,
                    })}
                </p>
            </hgroup>

            {/* Filters row */}
            <div className="flex items-end gap-4">
                <div className="flex flex-col gap-1">
                    <span className="text-sm font-medium text-slate-700">
                        {t(`${ns}.sort_by_name`)}
                    </span>
                    <Select
                        aria-label={t(`${ns}.sort_by_name`)}
                        value={sortByName}
                        onValueChange={setSortByName}
                    >
                        <SelectTrigger className="h-10 w-[180px] bg-white px-4">
                            <SelectValue placeholder={t(`${ns}.sort_by_name`)} />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="asc">{t(`${ns}.sort_asc_name`)}</SelectItem>
                            <SelectItem value="desc">{t(`${ns}.sort_desc_name`)}</SelectItem>
                        </SelectContent>
                    </Select>
                </div>

                <div className="flex flex-col gap-1">
                    <span className="text-sm font-medium text-slate-700">
                        {t(`${ns}.sort_by_workload`)}
                    </span>
                    <Select
                        aria-label={t(`${ns}.sort_by_workload`)}
                        value={sortByWorkload}
                        onValueChange={setSortByWorkload}
                    >
                        <SelectTrigger className="h-10 w-[180px] bg-white px-4">
                            <SelectValue placeholder={t(`${ns}.sort_by_workload`)} />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="asc">{t(`${ns}.sort_asc_workload`)}</SelectItem>
                            <SelectItem value="desc">{t(`${ns}.sort_desc_workload`)}</SelectItem>
                        </SelectContent>
                    </Select>
                </div>

                {hasMyAnnotatorsToggle && (
                    <label className="flex cursor-pointer items-center gap-2">
                        <span className="relative inline-flex shrink-0">
                            <input
                                type="checkbox"
                                role="switch"
                                aria-checked={showMyOnly}
                                checked={showMyOnly}
                                onChange={(e) => setShowMyOnly(e.target.checked)}
                                className="peer sr-only"
                            />
                            <span
                                aria-hidden="true"
                                className={`peer-focus-visible:ring-brand-blue-700/30 flex h-6 w-11 items-center rounded-full border-2 border-transparent transition-colors peer-focus-visible:ring-4 ${showMyOnly ? 'bg-brand-blue-700' : 'bg-slate-200'}`}
                            >
                                <span
                                    className={`size-4 rounded-full bg-white shadow-sm transition-transform ${showMyOnly ? 'translate-x-5' : 'translate-x-1'}`}
                                />
                            </span>
                        </span>
                        <span className="text-sm font-medium text-slate-800">
                            {t(`${ns}.show_my_annotators`)}
                        </span>
                    </label>
                )}

                <div className="relative ml-auto">
                    <Search
                        className="pointer-events-none absolute top-1/2 right-3 size-4 -translate-y-1/2 text-slate-400"
                        aria-hidden="true"
                    />
                    <Input
                        type="search"
                        placeholder={t(`${ns}.search_placeholder`)}
                        value={search}
                        onChange={(e) => setSearch(e.target.value)}
                        className="w-[220px] pr-9 pl-4"
                        aria-label={t(`${ns}.search_placeholder`)}
                    />
                </div>
            </div>

            {/* Select all */}
            <label className="flex cursor-pointer items-center gap-2">
                <Checkbox
                    checked={allFilteredSelected}
                    onCheckedChange={handleSelectAll}
                    aria-label={t(`${ns}.select_all`)}
                />
                <span className="text-sm text-slate-700">{t(`${ns}.select_all`)}</span>
            </label>

            <AnnotatorsTable
                mode="selectable"
                annotators={filteredAnnotators}
                selectedIds={selectedIds}
                onSelectionChange={onSelectionChange}
            />
        </section>
    );
}
