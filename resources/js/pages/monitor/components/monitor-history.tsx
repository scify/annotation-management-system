import { Input } from '@/components/ui/input';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { useTranslations } from '@/hooks/use-translations';
import { useMemo, useState } from 'react';
import { Search } from 'lucide-react';
import type { HistoryAnnotator } from '../types';
import { HistoryAnnotatorRow } from './history-annotator-row';

type SortDir = 'none' | 'asc' | 'desc';
type LastSort = 'name' | 'velocity';

const HISTORY_GRID_COLS = 'grid-cols-[52px_211px_133px_154px_154px_155px_154px_167px_64px]';

interface MonitorHistoryProps {
    annotators: HistoryAnnotator[];
}

export function MonitorHistory({ annotators }: MonitorHistoryProps) {
    const { t } = useTranslations();
    const [search, setSearch] = useState('');
    const [sortNameDir, setSortNameDir] = useState<SortDir>('none');
    const [sortVelocityDir, setSortVelocityDir] = useState<SortDir>('none');
    const [lastSort, setLastSort] = useState<LastSort>('name');

    const filtered = useMemo(() => {
        let list = [...annotators];

        if (search.trim()) {
            const q = search.trim().toLowerCase();
            list = list.filter((a) => a.username.toLowerCase().includes(q));
        }

        if (lastSort === 'name' && sortNameDir !== 'none') {
            list.sort((a, b) =>
                sortNameDir === 'asc'
                    ? a.username.localeCompare(b.username)
                    : b.username.localeCompare(a.username)
            );
        } else if (lastSort === 'velocity' && sortVelocityDir !== 'none') {
            list.sort((a, b) => {
                if (a.averageVelocity === null) return 1;
                if (b.averageVelocity === null) return -1;
                return sortVelocityDir === 'asc'
                    ? a.averageVelocity - b.averageVelocity
                    : b.averageVelocity - a.averageVelocity;
            });
        }

        return list;
    }, [annotators, search, sortNameDir, sortVelocityDir, lastSort]);

    return (
        <>
            {/* Filter bar */}
            <div className="mb-4 flex items-center justify-between gap-4">
                <div className="flex gap-4">
                    <Select
                        className="bg-white"
                        aria-label={t('monitor.sort_by_name')}
                        value={sortNameDir}
                        onValueChange={(v) => {
                            setSortNameDir(v as SortDir);
                            setLastSort('name');
                        }}
                    >
                        <SelectTrigger className="hover:cursor-pointer">
                            <SelectValue />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="none" className="hover:cursor-pointer">
                                {t('monitor.sort_by_name')}
                            </SelectItem>
                            <SelectItem value="asc" className="hover:cursor-pointer">
                                {t('monitor.sort_name_asc')}
                            </SelectItem>
                            <SelectItem value="desc" className="hover:cursor-pointer">
                                {t('monitor.sort_name_desc')}
                            </SelectItem>
                        </SelectContent>
                    </Select>

                    <Select
                        className="bg-white"
                        aria-label={t('monitor.sort_by_velocity')}
                        value={sortVelocityDir}
                        onValueChange={(v) => {
                            setSortVelocityDir(v as SortDir);
                            setLastSort('velocity');
                        }}
                    >
                        <SelectTrigger className="hover:cursor-pointer">
                            <SelectValue />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="none" className="hover:cursor-pointer">
                                {t('monitor.sort_by_velocity')}
                            </SelectItem>
                            <SelectItem value="asc" className="hover:cursor-pointer">
                                {t('monitor.sort_velocity_asc')}
                            </SelectItem>
                            <SelectItem value="desc" className="hover:cursor-pointer">
                                {t('monitor.sort_velocity_desc')}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                </div>

                <div className="relative w-72">
                    <Search
                        className="pointer-events-none absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-slate-400"
                        aria-hidden="true"
                    />
                    <Input
                        type="search"
                        name="search"
                        placeholder={t('monitor.search_placeholder')}
                        value={search}
                        onChange={(e) => setSearch(e.target.value)}
                        className="pl-9"
                        aria-label={t('monitor.search_placeholder')}
                    />
                </div>
            </div>

            {/* Table */}
            <div
                role="table"
                aria-label={t('monitor.tab_annotator_history')}
                className="overflow-hidden rounded-xl border border-slate-300"
            >
                {/* Header */}
                <div role="rowgroup">
                    <div
                        role="row"
                        className={`bg-brand-blue-100 grid items-center rounded-tl-xl rounded-tr-xl border-b border-slate-300 ${HISTORY_GRID_COLS}`}
                    >
                        <div
                            role="columnheader"
                            className="col-span-2 py-2.5 pl-4 text-sm font-semibold text-slate-800"
                        >
                            {t('monitor.col_username')}
                        </div>
                        <div
                            role="columnheader"
                            className="py-2.5 text-center text-sm font-semibold text-slate-800"
                        >
                            {t('monitor.col_status')}
                        </div>
                        <div
                            role="columnheader"
                            className="py-2.5 pr-4 text-right text-sm font-semibold text-slate-800"
                        >
                            {t('monitor.col_total_projects')}
                        </div>
                        <div
                            role="columnheader"
                            className="py-2.5 pr-4 text-right text-sm font-semibold text-slate-800"
                        >
                            {t('monitor.col_total_subprojects')}
                        </div>
                        <div
                            role="columnheader"
                            className="py-2.5 pr-4 text-right text-sm font-semibold text-slate-800"
                        >
                            {t('monitor.col_total_annotations')}
                        </div>
                        <div
                            role="columnheader"
                            className="py-2.5 pr-4 text-right text-sm font-semibold text-slate-800"
                        >
                            {t('monitor.col_total_flags')}
                        </div>
                        <div
                            role="columnheader"
                            className="py-2.5 text-center text-sm font-semibold text-slate-800"
                        >
                            {t('monitor.col_avg_velocity')}
                        </div>
                        <div role="columnheader" className="sr-only">
                            {t('monitor.expand_row')}
                        </div>
                    </div>
                </div>

                {/* Body */}
                <div role="rowgroup">
                    {filtered.length === 0 ? (
                        <div className="py-12 text-center text-sm text-slate-500">
                            {search ? `No annotators matching "${search}"` : 'No annotators.'}
                        </div>
                    ) : (
                        filtered.map((annotator) => (
                            <HistoryAnnotatorRow key={annotator.id} annotator={annotator} />
                        ))
                    )}
                </div>
            </div>
        </>
    );
}
