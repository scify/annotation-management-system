import {
    AnnotatorFilterPanel,
    type AnnotatorFilterState,
} from '@/components/annotator/annotator-filter-panel';
import {
    AnnotatorSortPanel,
    DEFAULT_ANNOTATOR_SORT_STATE,
    type AnnotatorSortState,
} from '@/components/annotator/annotator-sort-panel';
import {
    ProjectActiveFilters,
    type ActiveFilterTag,
} from '@/components/project/project-active-filters';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { useTranslations } from '@/hooks/use-translations';
import { cn } from '@/lib/utils';
import { type AnnotatorSelectOption } from '@/types';
import { Search } from 'lucide-react';
import { useMemo, useState } from 'react';
import { StatusBadge } from '../../pages/users/components/shared/status-badge';

interface FilterableAnnotatorListProps {
    annotators: AnnotatorSelectOption[];
    myAnnotators: AnnotatorSelectOption[];
    selectedAnnotatorIds: number[];
    onSelectionChange: (ids: number[]) => void;
    lockedAnnotatorIds?: number[];
    showMineToggle?: boolean;
}

export function FilterableAnnotatorList({
    annotators,
    myAnnotators,
    selectedAnnotatorIds,
    onSelectionChange,
    lockedAnnotatorIds,
    showMineToggle = true,
}: FilterableAnnotatorListProps) {
    const { t } = useTranslations();
    const [search, setSearch] = useState('');
    const [filterState, setFilterState] = useState<AnnotatorFilterState>({ statuses: [] });
    const [sortState, setSortState] = useState<AnnotatorSortState>(DEFAULT_ANNOTATOR_SORT_STATE);
    const [showMyOnly, setShowMyOnly] = useState(false);

    const ns = 'users.select_annotators' as const;
    const selectedIds = new Set(selectedAnnotatorIds);
    const lockedSet = new Set(lockedAnnotatorIds ?? []);
    const source = showMyOnly ? myAnnotators : annotators;

    const statusOptions = useMemo(
        () => [t('users.status.active'), t('users.status.pending'), t('users.status.inactive')],
        [t]
    );

    const displayedAnnotators = useMemo(() => {
        let result = [...source];

        if (search.trim()) {
            const q = search.toLowerCase();
            result = result.filter(
                (a) => a.username.toLowerCase().includes(q) || a.name.toLowerCase().includes(q)
            );
        }

        if (filterState.statuses.length > 0) {
            result = result.filter((a) =>
                filterState.statuses.includes(t(`users.status.${a.status}`))
            );
        }

        const activeSorts = (
            Object.entries(sortState) as [keyof AnnotatorSortState, string][]
        ).filter(([, dir]) => dir !== '');

        if (activeSorts.length > 0) {
            result = [...result].sort((a, b) => {
                for (const [col, dir] of activeSorts) {
                    let diff = 0;
                    if (col === 'username') {
                        diff = a.username.localeCompare(b.username);
                    } else if (col === 'total_projects') {
                        diff = (a.total_projects ?? 0) - (b.total_projects ?? 0);
                    } else if (col === 'total_subprojects') {
                        diff = (a.total_subprojects ?? 0) - (b.total_subprojects ?? 0);
                    } else if (col === 'total_annotations') {
                        diff = (a.total_annotations ?? 0) - (b.total_annotations ?? 0);
                    } else if (col === 'total_flags') {
                        diff = (a.total_flags ?? 0) - (b.total_flags ?? 0);
                    } else if (col === 'status') {
                        diff = a.status.localeCompare(b.status);
                    }
                    if (diff !== 0) return dir === 'asc' ? diff : -diff;
                }
                return 0;
            });
        }

        return result;
    }, [source, search, filterState, sortState, t]);

    const activeTags = useMemo((): ActiveFilterTag[] => {
        const tags: ActiveFilterTag[] = [];
        const is = t(`${ns}.filter_tag_is`);

        if (filterState.statuses.length > 0)
            tags.push({
                id: 'status',
                label: `${t(`${ns}.filter_status_section`)} ${is} (${filterState.statuses.length})`,
                value: filterState.statuses.join(', '),
                onRemove: () => setFilterState({ statuses: [] }),
            });

        const sortSectionLabels: Record<keyof AnnotatorSortState, string> = {
            username: t(`${ns}.table_username`),
            total_projects: t(`${ns}.table_total_projects`),
            total_subprojects: t(`${ns}.table_total_subprojects`),
            total_annotations: t(`${ns}.table_total_annotations`),
            total_flags: t(`${ns}.table_total_flags`),
            status: t('users.labels.status'),
        };

        const sortDirLabel = (col: keyof AnnotatorSortState, dir: 'asc' | 'desc'): string => {
            const isText = col === 'username' || col === 'status';
            if (isText) {
                return dir === 'asc' ? t(`${ns}.sort_asc_name`) : t(`${ns}.sort_desc_name`);
            }
            return dir === 'asc' ? t(`${ns}.sort_asc_workload`) : t(`${ns}.sort_desc_workload`);
        };

        for (const [col, dir] of Object.entries(sortState) as [
            keyof AnnotatorSortState,
            string,
        ][]) {
            if (dir !== '') {
                tags.push({
                    id: `sort-${col}`,
                    label: sortSectionLabels[col],
                    value: sortDirLabel(col, dir as 'asc' | 'desc'),
                    onRemove: () => setSortState((prev) => ({ ...prev, [col]: '' })),
                });
            }
        }

        return tags;
    }, [filterState, sortState, t, ns]);

    const hasActiveFilters = filterState.statuses.length > 0;
    const hasActiveSort = Object.values(sortState).some((v) => v !== '');

    const handleToggleMine = (value: boolean) => {
        setShowMyOnly(value);
        setFilterState({ statuses: [] });
        setSortState(DEFAULT_ANNOTATOR_SORT_STATE);
    };

    const selectableAnnotators = displayedAnnotators.filter((a) => !lockedSet.has(a.id));
    const allSelected =
        selectableAnnotators.length > 0 && selectableAnnotators.every((a) => selectedIds.has(a.id));

    function handleSingleChange(id: number, checked: boolean) {
        const next = new Set(selectedAnnotatorIds);
        if (checked) next.add(id);
        else next.delete(id);
        onSelectionChange([...next]);
    }

    function handleSelectAll(checked: boolean) {
        const ids = selectableAnnotators.map((a) => a.id);
        if (checked) {
            onSelectionChange([...new Set([...selectedAnnotatorIds, ...ids])]);
        } else {
            const toRemove = new Set(ids);
            onSelectionChange(selectedAnnotatorIds.filter((id) => !toRemove.has(id)));
        }
    }

    return (
        <div className="flex flex-col gap-4">
            {/* Toolbar */}
            <div className="flex flex-wrap items-center gap-3">
                <AnnotatorFilterPanel
                    statusOptions={statusOptions}
                    selected={filterState}
                    onToggle={(value) =>
                        setFilterState((prev) => ({
                            statuses: prev.statuses.includes(value)
                                ? prev.statuses.filter((s) => s !== value)
                                : [...prev.statuses, value],
                        }))
                    }
                    onClear={() => setFilterState({ statuses: [] })}
                    hasActiveFilters={hasActiveFilters}
                />
                <AnnotatorSortPanel
                    state={sortState}
                    onChange={setSortState}
                    hasActiveSort={hasActiveSort}
                    onClear={() => setSortState(DEFAULT_ANNOTATOR_SORT_STATE)}
                />
                <div className="flex-1" />
                {showMineToggle && (
                    <div className="flex items-center gap-2">
                        <button
                            type="button"
                            role="switch"
                            aria-checked={showMyOnly}
                            onClick={() => handleToggleMine(!showMyOnly)}
                            className={cn(
                                'focus-visible:ring-brand-blue-700 relative h-6 w-11 shrink-0 cursor-pointer rounded-full transition-colors focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:outline-none',
                                showMyOnly ? 'bg-brand-blue-700' : 'bg-slate-300'
                            )}
                        >
                            <span
                                className={cn(
                                    'absolute top-0.5 left-0.5 h-5 w-5 rounded-full bg-white shadow-sm motion-safe:transition-transform motion-safe:duration-200',
                                    showMyOnly ? 'translate-x-5' : 'translate-x-0'
                                )}
                            />
                        </button>
                        <span className="text-sm font-medium text-slate-800">
                            {showMyOnly
                                ? t(`${ns}.show_my_annotators`)
                                : t(`${ns}.show_all_annotators`)}
                        </span>
                    </div>
                )}
                <div className="relative">
                    <Search
                        className="pointer-events-none absolute top-1/2 right-3 size-4 -translate-y-1/2 text-slate-400"
                        aria-hidden="true"
                    />
                    <Input
                        type="search"
                        name="annotators_search"
                        placeholder={t(`${ns}.search_placeholder`)}
                        value={search}
                        onChange={(e) => setSearch(e.target.value)}
                        className="pr-9 pl-4"
                        aria-label={t(`${ns}.search_placeholder`)}
                    />
                </div>
            </div>

            {/* Active filter/sort tags */}
            <ProjectActiveFilters tags={activeTags} />

            {/* Select all */}
            <label className="flex cursor-pointer items-center gap-2">
                <Checkbox
                    checked={allSelected}
                    onCheckedChange={handleSelectAll}
                    aria-label={t(`${ns}.select_all`)}
                />
                <span className="text-sm text-slate-700">{t(`${ns}.select_all`)}</span>
            </label>

            {/* Table */}
            <div className="overflow-hidden rounded-xl">
                <Table>
                    <TableHeader>
                        <TableRow className="bg-brand-blue-100 hover:bg-brand-blue-100 border-b border-slate-300">
                            <TableHead className="w-10 pl-4">
                                <span className="sr-only">Select</span>
                            </TableHead>
                            <TableHead className="pl-2 text-sm font-semibold text-slate-800">
                                {t(`${ns}.table_username`)}
                            </TableHead>
                            <TableHead className="text-center text-sm font-semibold text-slate-800">
                                {t(`${ns}.table_total_projects`)}
                            </TableHead>
                            <TableHead className="text-center text-sm font-semibold text-slate-800">
                                {t(`${ns}.table_total_subprojects`)}
                            </TableHead>
                            <TableHead className="text-center text-sm font-semibold text-slate-800">
                                {t(`${ns}.table_total_annotations`)}
                            </TableHead>
                            <TableHead className="text-center text-sm font-semibold text-slate-800">
                                {t(`${ns}.table_total_flags`)}
                            </TableHead>
                            <TableHead className="text-center text-sm font-semibold text-slate-800">
                                {t(`${ns}.table_velocity`)}
                            </TableHead>
                            <TableHead className="text-center text-sm font-semibold text-slate-800">
                                {t('users.labels.status')}
                            </TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        {displayedAnnotators.length === 0 ? (
                            <TableRow className="bg-white hover:bg-white">
                                <TableCell
                                    colSpan={8}
                                    className="py-10 text-center text-sm text-slate-400"
                                >
                                    {t('users.empty.annotators')}
                                </TableCell>
                            </TableRow>
                        ) : (
                            displayedAnnotators.map((annotator) => {
                                const isLocked = lockedSet.has(annotator.id);
                                const isSelected = isLocked || selectedIds.has(annotator.id);

                                return (
                                    <TableRow
                                        key={annotator.id}
                                        className={`h-14 border-b border-slate-300 ${isSelected ? 'bg-brand-blue-50 hover:bg-brand-blue-50' : 'hover:bg-brand-blue-50 bg-white'}`}
                                    >
                                        <TableCell className="pl-4">
                                            <label
                                                className={`flex ${isLocked ? 'cursor-default' : 'cursor-pointer'}`}
                                            >
                                                <Checkbox
                                                    checked={isSelected}
                                                    disabled={isLocked}
                                                    onCheckedChange={
                                                        isLocked
                                                            ? undefined
                                                            : (checked) =>
                                                                  handleSingleChange(
                                                                      annotator.id,
                                                                      checked
                                                                  )
                                                    }
                                                    aria-label={`Select ${annotator.username}`}
                                                />
                                            </label>
                                        </TableCell>
                                        <TableCell className="pl-2">
                                            <div className="flex items-center gap-2">
                                                <div className="bg-brand-blue-700 flex size-[29px] shrink-0 items-center justify-center rounded-full">
                                                    <span className="text-sm font-semibold text-white">
                                                        {annotator.username.charAt(0).toUpperCase()}
                                                    </span>
                                                </div>
                                                <span className="text-base font-medium text-slate-800">
                                                    @{annotator.username}
                                                </span>
                                            </div>
                                        </TableCell>
                                        <TableCell className="text-center text-sm text-slate-800 tabular-nums">
                                            {annotator.total_projects ?? '—'}
                                        </TableCell>
                                        <TableCell className="text-center text-sm text-slate-800 tabular-nums">
                                            {annotator.total_subprojects ?? '—'}
                                        </TableCell>
                                        <TableCell className="text-center text-sm text-slate-800 tabular-nums">
                                            {annotator.total_annotations ?? '—'}
                                        </TableCell>
                                        <TableCell className="text-center text-sm text-slate-800 tabular-nums">
                                            {annotator.total_flags ?? '—'}
                                        </TableCell>
                                        <TableCell className="text-center text-sm text-slate-500 tabular-nums">
                                            —
                                        </TableCell>
                                        <TableCell className="text-center">
                                            <StatusBadge status={annotator.status} />
                                        </TableCell>
                                    </TableRow>
                                );
                            })
                        )}
                    </TableBody>
                </Table>
            </div>
        </div>
    );
}
