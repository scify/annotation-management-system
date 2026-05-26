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
import { cn, formatDate } from '@/lib/utils';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/react';
import { Search } from 'lucide-react';
import { useMemo, useState } from 'react';
import { AnnotatorRow } from './components/annotator-row';
import { MonitorHistory } from './components/monitor-history';
import type {
    BackendActiveWorkData,
    BackendAnnotator,
    BackendHiddenProject,
    BackendHistoryAnnotator,
    BackendHistoryData,
    BackendProject,
    BackendSubproject,
    HiddenProject,
    HistoryAnnotator,
    HistoryAnnotatorSubproject,
    MonitorAnnotator,
    MonitorProject,
    SubProject,
} from './types';

// ── Adapter: backend → normalized UI types ─────────────────────────────────────

function normalizeSubproject(sp: BackendSubproject): SubProject {
    return {
        id: sp.id,
        name: sp.name,
        dateRange: `${formatDate(sp.scheduled_at)} – ${formatDate(sp.deadline_at)}`,
        remainingWorkload: Math.round(sp.workload * 100),
        progress: Math.round(sp.progress * 100),
        state: sp.status as SubProject['state'],
    };
}

function normalizeProject(p: BackendProject): MonitorProject {
    return {
        id: p.id,
        name: p.name,
        annotation_task_title: p.annotation_task_title,
        dataset_name: p.dataset_name,
        started_at: p.started_at,
        completed_at: p.completed_at,
        scheduled_at: p.scheduled_at,
        deadline_at: p.deadline_at,
        is_delayed_to_start: p.is_delayed_to_start,
        is_delayed_to_end: p.is_delayed_to_end,
        status: p.status as MonitorProject['status'],
        owner: p.owner_name,
        coManagers: p.co_managers.map((cm) => cm.username),
        overallProgress: Math.round(p.project_progress * 100),
        notifications_count: p.notifications_count,
        subprojects: p.subprojects.map(normalizeSubproject),
    };
}

function normalizeHiddenProject(hp: BackendHiddenProject): HiddenProject {
    return {
        restricted: true,
        owner: hp.owner_name,
        assignedCount: hp.active_subprojects_count,
    };
}

function normalizeAnnotator(a: BackendAnnotator): MonitorAnnotator {
    return {
        id: a.id,
        username: a.username,
        initials: a.username[0]?.toUpperCase() ?? '?',
        status: a.status,
        activeSubprojects: a.active_subprojects,
        activeProjects: a.active_projects,
        remainingWorkload: Math.round(a.workload * 100),
        progress: Math.round(a.progress * 100),
        projects: [
            ...a.projects.map(normalizeProject),
            ...a.hidden_projects.map(normalizeHiddenProject),
        ],
    };
}

function normalizeHistoryAnnotator(a: BackendHistoryAnnotator): HistoryAnnotator {
    return {
        id: a.id,
        username: a.username,
        initials: a.username[0]?.toUpperCase() ?? '?',
        status: a.status,
        totalProjects: a.total_projects,
        totalSubprojects: a.total_subprojects,
        totalAnnotations: a.total_annotations,
        totalFlags: a.total_flags,
        // TODO(backend): wire to average_velocity once backend adds it
        averageVelocity: null,
        subprojects: a.subprojects.map(
            (sp): HistoryAnnotatorSubproject => ({
                project: sp.project_name,
                subproject: sp.subproject_name,
                annotations: sp.annotations,
                flags: sp.flags,
                // TODO(backend): wire to velocity once backend adds it
                velocity: null,
                confidence: sp.avg_confidence
                    ? ((sp.avg_confidence[0].toUpperCase() + sp.avg_confidence.slice(1)) as
                          | 'High'
                          | 'Medium'
                          | 'Low')
                    : null,
                dateCompleted: sp.completed_at
                    ? new Date(sp.completed_at).toLocaleDateString('en-GB', {
                          day: 'numeric',
                          month: 'short',
                          year: 'numeric',
                      })
                    : '',
            })
        ),
    };
}

// ── Page component ─────────────────────────────────────────────────────────────

interface Props {
    annotator_progress_tab_data?: BackendActiveWorkData;
    annotator_history_tab_data?: BackendHistoryData;
}

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

type TabKey = 'annotator_progress' | 'annotator_history';
type SortDir = 'asc' | 'desc' | 'none';

const GRID_COLS = 'grid-cols-[52px_194px_150px_1fr_1fr_156px_195px_56px]';

export default function MonitorIndex({
    annotator_progress_tab_data,
    annotator_history_tab_data,
}: Props) {
    const { t } = useTranslations();
    const isAnnotationManager = useAuth().isAnnotationManager();

    const breadcrumbs: BreadcrumbItem[] = [
        { title: t('navbar.dashboard'), href: '/dashboard' },
        { title: t('monitor.page_title'), href: route('monitor.index') },
    ];

    const initialTab: TabKey =
        annotator_progress_tab_data !== undefined ? 'annotator_progress' : 'annotator_history';
    const [activeTab, setActiveTab] = useState<TabKey>(initialTab);
    const [showOnlyMine, setShowOnlyMine] = useState(false);
    const [search, setSearch] = useState('');
    const [sortNameDir, setSortNameDir] = useState<SortDir>('none');
    const [sortWorkloadDir, setSortWorkloadDir] = useState<SortDir>('none');
    const [lastSort, setLastSort] = useState<'name' | 'workload' | null>(null);

    function handleTabChange(tab: TabKey) {
        setActiveTab(tab);
        const routeName =
            tab === 'annotator_progress'
                ? 'monitor.annotator-progress'
                : 'monitor.annotator-history';
        router.visit(route(routeName), { preserveScroll: true });
    }

    const annotators = useMemo(() => {
        const raw = showOnlyMine
            ? (annotator_progress_tab_data?.my_annotators ?? [])
            : (annotator_progress_tab_data?.all_annotators ?? []);
        return raw.map(normalizeAnnotator);
    }, [annotator_progress_tab_data, showOnlyMine]);

    const historyAnnotators = useMemo(() => {
        const raw = showOnlyMine
            ? (annotator_history_tab_data?.my_annotators ?? [])
            : (annotator_history_tab_data?.all_annotators ?? []);
        return raw.map(normalizeHistoryAnnotator);
    }, [annotator_history_tab_data, showOnlyMine]);

    const filtered = useMemo(() => {
        let result = [...annotators];

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
    }, [annotators, search, sortNameDir, sortWorkloadDir, lastSort]);

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
                            className="flex h-[50px] w-[500px] items-center rounded-lg border border-slate-200 bg-white px-1.5 py-1"
                        >
                            <button
                                role="tab"
                                aria-selected={activeTab === 'annotator_progress'}
                                aria-controls="monitor-annotator-progress"
                                onClick={() => handleTabChange('annotator_progress')}
                                className={cn(
                                    'flex h-10 flex-1 items-center justify-center border-r border-slate-200 px-3 text-sm transition-colors hover:cursor-pointer',
                                    activeTab === 'annotator_progress'
                                        ? 'bg-white font-semibold text-slate-800'
                                        : 'bg-slate-100 font-medium text-slate-500'
                                )}
                            >
                                {t('monitor.tab_annotator_progress')}
                            </button>
                            <button
                                role="tab"
                                aria-selected={activeTab === 'annotator_history'}
                                aria-controls="monitor-history"
                                onClick={() => handleTabChange('annotator_history')}
                                className={cn(
                                    'flex h-10 flex-1 items-center justify-center px-3 text-sm transition-colors hover:cursor-pointer',
                                    activeTab === 'annotator_history'
                                        ? 'bg-white font-semibold text-slate-800'
                                        : 'bg-slate-100 font-medium text-slate-500'
                                )}
                            >
                                {t('monitor.tab_annotator_history')}
                            </button>
                        </div>
                        <SectionToggle
                            checked={showOnlyMine}
                            onChange={setShowOnlyMine}
                            label={
                                showOnlyMine
                                    ? t('monitor.show_only_mine')
                                    : t('monitor.show_all_annotators')
                            }
                        />
                    </div>
                )}

                {activeTab === 'annotator_progress' || isAnnotationManager ? (
                    <div id="monitor-annotator-progress" role="tabpanel">
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
                                    value={sortWorkloadDir}
                                    className="bg-white"
                                    aria-label={t('monitor.sort_by_workload')}
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
                        <MonitorHistory annotators={historyAnnotators} />
                    </div>
                )}
            </div>
        </AppLayout>
    );
}
