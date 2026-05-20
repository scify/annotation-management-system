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

const MOCK_HISTORY: HistoryAnnotator[] = [
    {
        id: 1,
        username: '@Nazpapad',
        initials: 'N',
        status: 'active',
        totalProjects: 4,
        totalSubprojects: 12,
        totalAnnotations: 1340,
        totalFlags: 23,
        averageVelocity: 112,
        subprojects: [
            {
                project: 'Project New Nov_26',
                subproject: 'Text Annotation Batch March 2026',
                annotations: 450,
                flags: 8,
                velocity: 120,
                confidence: 'High',
                dateCompleted: 'Mar 15, 2026',
            },
            {
                project: 'Project New Nov_26',
                subproject: 'Semantic Labelling Batch A',
                annotations: 390,
                flags: 5,
                velocity: 105,
                confidence: 'Medium',
                dateCompleted: 'Feb 28, 2026',
            },
            {
                project: 'Text annotation – meaning of words',
                subproject: 'Batch Alpha',
                annotations: 500,
                flags: 10,
                velocity: 110,
                confidence: 'High',
                dateCompleted: 'Jan 20, 2026',
            },
        ],
    },
    {
        id: 2,
        username: '@nellysav',
        initials: 'NE',
        status: 'active',
        totalProjects: 3,
        totalSubprojects: 9,
        totalAnnotations: 980,
        totalFlags: 14,
        averageVelocity: 98,
        subprojects: [
            {
                project: 'Project New Nov_26',
                subproject: 'Text Annotation Batch March 2026',
                annotations: 320,
                flags: 4,
                velocity: 95,
                confidence: 'Medium',
                dateCompleted: 'Mar 15, 2026',
            },
            {
                project: 'Sentiment Analysis Q1',
                subproject: 'Batch 1',
                annotations: 660,
                flags: 10,
                velocity: 100,
                confidence: 'High',
                dateCompleted: 'Feb 10, 2026',
            },
        ],
    },
    {
        id: 3,
        username: '@akosmo',
        initials: 'AK',
        status: 'inactive',
        totalProjects: 2,
        totalSubprojects: 5,
        totalAnnotations: 560,
        totalFlags: 31,
        averageVelocity: 72,
        subprojects: [
            {
                project: 'Text annotation – meaning of words',
                subproject: 'Batch Beta',
                annotations: 310,
                flags: 18,
                velocity: 68,
                confidence: 'Low',
                dateCompleted: 'Jan 31, 2026',
            },
            {
                project: 'Sentiment Analysis Q1',
                subproject: 'Batch 2',
                annotations: 250,
                flags: 13,
                velocity: 76,
                confidence: 'Medium',
                dateCompleted: 'Feb 20, 2026',
            },
        ],
    },
    {
        id: 4,
        username: '@georgiou',
        initials: 'G',
        status: 'active',
        totalProjects: 5,
        totalSubprojects: 14,
        totalAnnotations: 2100,
        totalFlags: 9,
        averageVelocity: 150,
        subprojects: [
            {
                project: 'Project New Nov_26',
                subproject: 'Semantic Labelling Batch B',
                annotations: 700,
                flags: 2,
                velocity: 155,
                confidence: 'High',
                dateCompleted: 'Mar 1, 2026',
            },
            {
                project: 'Sentiment Analysis Q1',
                subproject: 'Batch 3',
                annotations: 800,
                flags: 4,
                velocity: 148,
                confidence: 'High',
                dateCompleted: 'Mar 10, 2026',
            },
            {
                project: 'NER Dataset Labelling',
                subproject: 'Sprint 1',
                annotations: 600,
                flags: 3,
                velocity: 147,
                confidence: 'High',
                dateCompleted: 'Feb 5, 2026',
            },
        ],
    },
    {
        id: 5,
        username: '@mariap',
        initials: 'MP',
        status: 'active',
        totalProjects: 3,
        totalSubprojects: 7,
        totalAnnotations: 870,
        totalFlags: 17,
        averageVelocity: 88,
        subprojects: [
            {
                project: 'NER Dataset Labelling',
                subproject: 'Sprint 2',
                annotations: 430,
                flags: 9,
                velocity: 90,
                confidence: 'Medium',
                dateCompleted: 'Feb 18, 2026',
            },
            {
                project: 'Text annotation – meaning of words',
                subproject: 'Batch Gamma',
                annotations: 440,
                flags: 8,
                velocity: 86,
                confidence: 'Medium',
                dateCompleted: 'Mar 5, 2026',
            },
        ],
    },
];

export function MonitorHistory() {
    const { t } = useTranslations();
    const [search, setSearch] = useState('');
    const [sortNameDir, setSortNameDir] = useState<SortDir>('none');
    const [sortVelocityDir, setSortVelocityDir] = useState<SortDir>('none');
    const [lastSort, setLastSort] = useState<LastSort>('name');

    const filtered = useMemo(() => {
        let list = [...MOCK_HISTORY];

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
            list.sort((a, b) =>
                sortVelocityDir === 'asc'
                    ? a.averageVelocity - b.averageVelocity
                    : b.averageVelocity - a.averageVelocity
            );
        }

        return list;
    }, [search, sortNameDir, sortVelocityDir, lastSort]);

    return (
        <>
            {/* Filter bar */}
            <div className="mb-4 flex items-center justify-between gap-4">
                <div className="flex gap-4">
                    <Select
                        className="bg-white"
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
                aria-label={t('monitor.tab_history')}
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
                    {filtered.map((annotator) => (
                        <HistoryAnnotatorRow key={annotator.id} annotator={annotator} />
                    ))}
                </div>
            </div>
        </>
    );
}
