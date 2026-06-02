import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { useTranslations } from '@/hooks/use-translations';
import { type AnnotatorSelectOption } from '@/types';
import { Search } from 'lucide-react';
import { useMemo, useState } from 'react';
import { StatusBadge } from '../../shared/status-badge';

interface ConnectAnnotatorsStepProps {
    annotators: AnnotatorSelectOption[];
    myAnnotators: AnnotatorSelectOption[];
    selectedAnnotatorIds: number[];
    onSelectionChange: (ids: number[]) => void;
    lockedAnnotatorIds?: number[];
}

export function ConnectAnnotatorsStep({
    annotators,
    myAnnotators,
    selectedAnnotatorIds,
    onSelectionChange,
    lockedAnnotatorIds,
}: ConnectAnnotatorsStepProps) {
    const { t, trans } = useTranslations();
    const [sortByName, setSortByName] = useState('');
    const [search, setSearch] = useState('');
    const [showMyOnly, setShowMyOnly] = useState(false);

    const ns = 'users.select_annotators' as const;
    const selectedIds = new Set(selectedAnnotatorIds);
    const lockedSet = new Set(lockedAnnotatorIds ?? []);
    const source = showMyOnly ? myAnnotators : annotators;

    const filteredAnnotators = useMemo(() => {
        let result = [...source];

        if (search.trim()) {
            const query = search.toLowerCase();
            result = result.filter(
                (a) =>
                    a.username.toLowerCase().includes(query) || a.name.toLowerCase().includes(query)
            );
        }

        if (sortByName === 'asc') result.sort((a, b) => a.username.localeCompare(b.username));
        if (sortByName === 'desc') result.sort((a, b) => b.username.localeCompare(a.username));

        return result;
    }, [source, search, sortByName]);

    const selectableAnnotators = filteredAnnotators.filter((a) => !lockedSet.has(a.id));
    const allSelected =
        selectableAnnotators.length > 0 && selectableAnnotators.every((a) => selectedIds.has(a.id));
    const totalSelectedCount = new Set([...selectedAnnotatorIds, ...(lockedAnnotatorIds ?? [])])
        .size;

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
        <section aria-labelledby="step-heading" className="flex flex-col gap-5">
            <hgroup>
                <h2 id="step-heading" className="page-subtitle">
                    {t(`${ns}.heading`)}
                </h2>
                <p className="text-sm font-semibold text-slate-800">
                    {trans(`${ns}.selected_count`, { count: totalSelectedCount })}
                </p>
            </hgroup>

            {/* Filters */}
            <div className="flex items-center gap-4">
                <Select
                    aria-label={t(`${ns}.sort_by_name`)}
                    value={sortByName}
                    onValueChange={setSortByName}
                >
                    <SelectTrigger className="h-10 bg-white px-4">
                        <SelectValue placeholder={t(`${ns}.sort_by_name`)} />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value="asc">{t(`${ns}.sort_asc_name`)}</SelectItem>
                        <SelectItem value="desc">{t(`${ns}.sort_desc_name`)}</SelectItem>
                    </SelectContent>
                </Select>

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
                        {showMyOnly
                            ? t(`${ns}.show_my_annotators`)
                            : t(`${ns}.show_all_annotators`)}
                    </span>
                </label>

                <div className="relative ml-auto">
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
                            <TableHead className="text-sm font-semibold text-slate-800">
                                {t('users.labels.name')}
                            </TableHead>
                            <TableHead className="text-center text-sm font-semibold text-slate-800">
                                {t('users.labels.status')}
                            </TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        {filteredAnnotators.length === 0 ? (
                            <TableRow className="bg-white hover:bg-white">
                                <TableCell
                                    colSpan={4}
                                    className="py-10 text-center text-sm text-slate-400"
                                >
                                    {t('users.empty.annotators')}
                                </TableCell>
                            </TableRow>
                        ) : (
                            filteredAnnotators.map((annotator) => {
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
                                        <TableCell className="text-sm text-slate-800">
                                            {annotator.name}
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
        </section>
    );
}
