import { useAuth } from '@/hooks/use-auth';
import { Input } from '@/components/ui/input';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { useTranslations } from '@/hooks/use-translations';
import AppLayout from '@/layouts/app-layout';
import { cn } from '@/lib/utils';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/react';
import { Search } from 'lucide-react';
import { useMemo, useState } from 'react';
import { AnnotatorRow } from './components/annotator-row';
import { MonitorHistory } from './components/monitor-history';
import type { MonitorAnnotator } from './types';

const MOCK_ANNOTATORS: MonitorAnnotator[] = [
    {
        id: 1,
        username: '@Nazpapad',
        initials: 'N',
        status: 'active',
        activeSubprojects: 23,
        activeProjects: 2,
        remainingWorkload: 72,
        progress: 75,
        projects: [
            {
                id: 1,
                name: 'Project New Nov_26',
                started_at: null,
                completed_at: null,
                scheduled_at: '2026-01-15',
                deadline_at: '2026-02-15',
                is_delayed_to_start: false,
                is_delayed_to_end: false,
                status: 'in_progress',
                owner: '@akosmo',
                coManagers: ['@nellysav', '@nazpapadaki', '@georgiou', '@mariap'],
                overallProgress: 25,
                subprojects: [
                    {
                        id: 1,
                        name: 'Text Annotation Batch March _2026',
                        dateRange: 'Jan 15, 2026 – Feb 28, 2026',
                        remainingWorkload: 100,
                        progress: 100,
                        state: 'in_progress',
                    },
                    {
                        id: 2,
                        name: 'Text Annotation Batch March _2026',
                        dateRange: 'Jan 15, 2026 – Feb 28, 2026',
                        remainingWorkload: 100,
                        progress: 100,
                        state: 'in_progress',
                    },
                    {
                        id: 3,
                        name: 'Text Annotation Batch March _2026',
                        dateRange: 'Jan 15, 2026 – Feb 28, 2026',
                        remainingWorkload: 100,
                        progress: 100,
                        state: 'in_progress',
                    },
                ],
            },
            {
                id: 2,
                name: 'Text annotation – describe the meaning of the word',
                started_at: null,
                completed_at: null,
                scheduled_at: '2026-01-15',
                deadline_at: '2026-02-15',
                is_delayed_to_start: false,
                is_delayed_to_end: false,
                status: 'in_progress',
                owner: '@akosmo',
                coManagers: ['@nellysav', '@nazpapadaki', '@georgiou', '@mariap'],
                overallProgress: 25,
                subprojects: [
                    {
                        id: 4,
                        name: 'Semantic Labelling Batch A',
                        dateRange: 'Jan 20, 2026 – Feb 10, 2026',
                        remainingWorkload: 60,
                        progress: 40,
                        state: 'in_progress',
                    },
                    {
                        id: 5,
                        name: 'Semantic Labelling Batch B',
                        dateRange: 'Jan 20, 2026 – Feb 10, 2026',
                        remainingWorkload: 80,
                        progress: 20,
                        state: 'pending',
                    },
                ],
            },
            {
                restricted: true,
                owner: '@akosmo',
                assignedCount: 5,
                assignedTo: '@Nazpapad',
            },
        ],
    },
    {
        id: 2,
        username: '@NellySav',
        initials: 'N',
        status: 'active',
        activeSubprojects: 12,
        activeProjects: 5,
        remainingWorkload: 74,
        progress: 75,
        projects: [
            {
                id: 3,
                name: 'Audio Transcription Sprint',
                started_at: null,
                completed_at: null,
                scheduled_at: '2026-02-01',
                deadline_at: '2026-03-01',
                is_delayed_to_start: false,
                is_delayed_to_end: false,
                status: 'in_progress',
                owner: '@akosmo',
                coManagers: ['@nazpapadaki'],
                overallProgress: 75,
                subprojects: [
                    {
                        id: 6,
                        name: 'Audio Batch Q1',
                        dateRange: 'Feb 1, 2026 – Feb 15, 2026',
                        remainingWorkload: 70,
                        progress: 80,
                        state: 'in_progress',
                    },
                ],
            },
        ],
    },
    {
        id: 3,
        username: '@fpapastergiou',
        initials: 'F',
        status: 'active',
        activeSubprojects: 23,
        activeProjects: 2,
        remainingWorkload: 52,
        progress: 50,
        projects: [
            {
                id: 4,
                name: 'Image Classification Round 3',
                started_at: null,
                completed_at: null,
                scheduled_at: '2026-01-10',
                deadline_at: '2026-03-10',
                is_delayed_to_start: false,
                is_delayed_to_end: false,
                status: 'in_progress',
                owner: '@akosmo',
                coManagers: ['@nellysav'],
                overallProgress: 50,
                subprojects: [
                    {
                        id: 7,
                        name: 'Image Batch Jan',
                        dateRange: 'Jan 10, 2026 – Feb 10, 2026',
                        remainingWorkload: 50,
                        progress: 50,
                        state: 'in_progress',
                    },
                ],
            },
        ],
    },
    {
        id: 4,
        username: '@vasilisgiannakopolos',
        initials: 'V',
        status: 'active',
        activeSubprojects: 23,
        activeProjects: 2,
        remainingWorkload: 28,
        progress: 25,
        projects: [
            {
                id: 5,
                name: 'Sentiment Analysis Phase 2',
                started_at: null,
                completed_at: null,
                scheduled_at: '2026-01-05',
                deadline_at: '2026-02-28',
                is_delayed_to_start: false,
                is_delayed_to_end: false,
                status: 'in_progress',
                owner: '@akosmo',
                coManagers: ['@nellysav', '@nazpapadaki'],
                overallProgress: 25,
                subprojects: [
                    {
                        id: 8,
                        name: 'Sentiment Batch Alpha',
                        dateRange: 'Jan 5, 2026 – Feb 5, 2026',
                        remainingWorkload: 28,
                        progress: 25,
                        state: 'in_progress',
                    },
                ],
            },
        ],
    },
    {
        id: 5,
        username: '@paulis',
        initials: 'P',
        status: 'active',
        activeSubprojects: 23,
        activeProjects: 2,
        remainingWorkload: 72,
        progress: 75,
        projects: [
            {
                id: 6,
                name: 'NER Annotation Campaign',
                started_at: null,
                completed_at: null,
                scheduled_at: '2026-01-20',
                deadline_at: '2026-03-20',
                is_delayed_to_start: false,
                is_delayed_to_end: false,
                status: 'in_progress',
                owner: '@akosmo',
                coManagers: ['@nellysav'],
                overallProgress: 75,
                subprojects: [
                    {
                        id: 9,
                        name: 'NER Batch Jan',
                        dateRange: 'Jan 20, 2026 – Feb 20, 2026',
                        remainingWorkload: 72,
                        progress: 75,
                        state: 'in_progress',
                    },
                ],
            },
        ],
    },
];

function SectionToggle({
    checked,
    onChange,
    label,
}: {
    checked: boolean;
    onChange: (checked: boolean) => void;
    label: string;
}) {
    return (
        <div className="flex items-center gap-2">
            <button
                role="switch"
                type="button"
                aria-checked={checked}
                onClick={() => onChange(!checked)}
                className={cn(
                    'focus-visible:ring-brand-blue-700 relative h-6 w-11 shrink-0 cursor-pointer rounded-full transition-colors hover:cursor-pointer focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:outline-none',
                    checked ? 'bg-brand-blue-700' : 'bg-slate-300'
                )}
            >
                <span
                    className={cn(
                        'absolute top-0.5 left-0.5 h-5 w-5 rounded-full bg-white shadow-sm motion-safe:transition-transform motion-safe:duration-200',
                        checked ? 'translate-x-5' : 'translate-x-0'
                    )}
                />
            </button>
            <span className="text-sm font-medium text-slate-800">{label}</span>
        </div>
    );
}

type TabKey = 'active_work' | 'history';
type SortDir = 'asc' | 'desc' | 'none';

const GRID_COLS = 'grid-cols-[52px_194px_150px_1fr_1fr_156px_195px_56px]';

export default function MonitorIndex() {
    const { t } = useTranslations();
    const isAnnotationManager = useAuth().isAnnotationManager();

    const breadcrumbs: BreadcrumbItem[] = [
        { title: t('navbar.dashboard'), href: '/dashboard' },
        { title: t('monitor.page_title'), href: route('monitor.index') },
    ];

    const [activeTab, setActiveTab] = useState<TabKey>('active_work');
    const [showOnlyMine, setShowOnlyMine] = useState(false);
    const [search, setSearch] = useState('');
    const [sortNameDir, setSortNameDir] = useState<SortDir>('none');
    const [sortWorkloadDir, setSortWorkloadDir] = useState<SortDir>('none');
    const [lastSort, setLastSort] = useState<'name' | 'workload' | null>(null);

    const filtered = useMemo(() => {
        let result = [...MOCK_ANNOTATORS];

        if (search.trim()) {
            const q = search.toLowerCase();
            result = result.filter((a) => a.username.toLowerCase().includes(q));
        }

        if (lastSort === 'name' && sortNameDir !== 'none') {
            result.sort((a, b) =>
                sortNameDir === 'asc'
                    ? a.username.localeCompare(b.username)
                    : b.username.localeCompare(a.username)
            );
        } else if (lastSort === 'workload' && sortWorkloadDir !== 'none') {
            result.sort((a, b) =>
                sortWorkloadDir === 'asc'
                    ? a.remainingWorkload - b.remainingWorkload
                    : b.remainingWorkload - a.remainingWorkload
            );
        }

        return result;
    }, [search, sortNameDir, sortWorkloadDir, lastSort]);

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={t('monitor.page_title')} />

            <div className="px-6 py-6">
                <hgroup className="mb-6">
                    <h1 className="font-heading text-3xl font-bold text-slate-800">
                        {t('monitor.page_title')}
                    </h1>
                </hgroup>

                {/* Tabs + Toggle row */}
                {!isAnnotationManager && (
                    <div className="mb-6 flex items-center justify-between">
                        <div
                            role="tablist"
                            aria-label={t('monitor.page_title')}
                            className="flex h-[50px] w-[390px] items-center rounded-lg border border-slate-200 bg-white px-1.5 py-1"
                        >
                            <button
                                role="tab"
                                aria-selected={activeTab === 'active_work'}
                                aria-controls="monitor-active-work"
                                onClick={() => setActiveTab('active_work')}
                                className={cn(
                                    'flex h-10 flex-1 items-center justify-center border-r border-slate-200 px-3 text-sm transition-colors hover:cursor-pointer',
                                    activeTab === 'active_work'
                                        ? 'bg-white font-semibold text-slate-800'
                                        : 'bg-slate-100 font-medium text-slate-500'
                                )}
                            >
                                {t('monitor.tab_active_work')}
                            </button>
                            <button
                                role="tab"
                                aria-selected={activeTab === 'history'}
                                aria-controls="monitor-history"
                                onClick={() => setActiveTab('history')}
                                className={cn(
                                    'flex h-10 flex-1 items-center justify-center px-3 text-sm transition-colors hover:cursor-pointer',
                                    activeTab === 'history'
                                        ? 'bg-white font-semibold text-slate-800'
                                        : 'bg-slate-100 font-medium text-slate-500'
                                )}
                            >
                                {t('monitor.tab_history')}
                            </button>
                        </div>
                        <SectionToggle
                            checked={showOnlyMine}
                            onChange={setShowOnlyMine}
                            label={t('monitor.show_only_mine')}
                        />
                    </div>
                )}

                {activeTab === 'active_work' || isAnnotationManager ? (
                    <div id="monitor-active-work" role="tabpanel">
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
                                    value={sortWorkloadDir}
                                    className="bg-white"
                                    onValueChange={(v) => {
                                        setSortWorkloadDir(v as SortDir);
                                        setLastSort('workload');
                                    }}
                                >
                                    <SelectTrigger className="hover:cursor-pointer">
                                        <SelectValue />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="none" className="hover:cursor-pointer">
                                            {t('monitor.sort_by_workload')}
                                        </SelectItem>
                                        <SelectItem value="asc" className="hover:cursor-pointer">
                                            {t('monitor.sort_workload_asc')}
                                        </SelectItem>
                                        <SelectItem value="desc" className="hover:cursor-pointer">
                                            {t('monitor.sort_workload_desc')}
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
                            aria-label={t('monitor.page_title')}
                            className="overflow-hidden rounded-xl border border-slate-300"
                        >
                            {/* Header */}
                            <div role="rowgroup">
                                <div
                                    role="row"
                                    className={`bg-brand-blue-100 grid items-center rounded-tl-xl rounded-tr-xl border-b border-slate-300 ${GRID_COLS}`}
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
                                        {t('monitor.col_active_subprojects')}
                                    </div>
                                    <div
                                        role="columnheader"
                                        className="py-2.5 pr-4 text-right text-sm font-semibold text-slate-800"
                                    >
                                        {t('monitor.col_active_projects')}
                                    </div>
                                    <div
                                        role="columnheader"
                                        className="py-2.5 text-center text-sm font-semibold text-slate-800"
                                    >
                                        {t('monitor.col_rem_workload')}
                                    </div>
                                    <div
                                        role="columnheader"
                                        className="py-2.5 text-center text-sm font-semibold text-slate-800"
                                    >
                                        {t('monitor.col_progress')}
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
                                        {search
                                            ? `No annotators matching "${search}"`
                                            : 'No annotators.'}
                                    </div>
                                ) : (
                                    filtered.map((annotator) => (
                                        <AnnotatorRow key={annotator.id} annotator={annotator} />
                                    ))
                                )}
                            </div>
                        </div>
                    </div>
                ) : (
                    <div id="monitor-history" role="tabpanel">
                        <MonitorHistory />
                    </div>
                )}
            </div>
        </AppLayout>
    );
}
