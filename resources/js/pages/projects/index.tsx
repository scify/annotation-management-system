import {
    ProjectActiveFilters,
    type ActiveFilterTag,
} from '@/components/project/project-active-filters';
import {
    ProjectFilterPanel,
    type FilterState,
    type FilterSectionKey,
} from '@/components/project/project-filter-panel';
import { ProjectList } from '@/components/project/project-list';
import {
    ProjectSortPanel,
    DEFAULT_SORT_STATE,
    type SortState,
} from '@/components/project/project-sort-panel';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { useTranslations } from '@/hooks/use-translations';
import AppLayout from '@/layouts/app-layout';
import { cn } from '@/lib/utils';
import { type BreadcrumbItem, type PageProps, type Project, RolesEnum } from '@/types';
import { Head, router, usePage } from '@inertiajs/react';
import { Plus, Search } from 'lucide-react';
import { useMemo, useState } from 'react';

interface DataFilter {
    tasks_filter: Array<{ id: number; title: string }>;
    datasets_filter: Array<{ id: number; name: string }>;
}

interface Props {
    all_projects?: Project[];
    my_projects: Project[];
    all_data_filter?: DataFilter;
    my_data_filter: DataFilter;
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
                    'focus-visible:ring-brand-blue-700 relative h-6 w-11 shrink-0 cursor-pointer rounded-full transition-colors focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:outline-none',
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

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Projects',
        href: '/projects',
    },
];

export default function ProjectsIndex({
    all_projects,
    my_projects,
    all_data_filter,
    my_data_filter,
}: Props) {
    const { t, trans } = useTranslations();
    const { auth } = usePage<PageProps>().props;
    const isAnnotationManager = auth.user.role === RolesEnum.ANNOTATION_MANAGER;

    const [searchQuery, setSearchQuery] = useState('');
    const [filters, setFilters] = useState<FilterState>({ tasks: [], datasets: [], states: [] });
    const [sortState, setSortState] = useState<SortState>(DEFAULT_SORT_STATE);
    const [showOnlyMine, setShowOnlyMine] = useState(false);

    const projects = !showOnlyMine && all_projects ? all_projects : my_projects;
    const dataFilter = !showOnlyMine && all_data_filter ? all_data_filter : my_data_filter;

    const filterSections = useMemo(
        () => [
            {
                key: 'tasks' as const,
                label: t('projects.filter_task_section'),
                items: dataFilter.tasks_filter.map((f) => f.title),
                searchable: true,
            },
            {
                key: 'datasets' as const,
                label: t('projects.filter_dataset_section'),
                items: dataFilter.datasets_filter.map((f) => f.name),
                searchable: true,
            },
            {
                key: 'states' as const,
                label: t('projects.filter_state_section'),
                items: [...new Set(projects.map((p) => t(`projects.status.${p.status}`)))],
                searchable: false,
            },
        ],
        [dataFilter, projects, t]
    );

    const toggleFilter = (section: FilterSectionKey, value: string) =>
        setFilters((prev) => {
            const current = prev[section];
            return {
                ...prev,
                [section]: current.includes(value)
                    ? current.filter((v) => v !== value)
                    : [...current, value],
            };
        });

    const clearFilters = () => setFilters({ tasks: [], datasets: [], states: [] });

    const handleToggleMine = (value: boolean) => {
        setShowOnlyMine(value);
        clearFilters();
    };

    const hasActiveFilters =
        filters.tasks.length > 0 || filters.datasets.length > 0 || filters.states.length > 0;

    const activeTags = useMemo((): ActiveFilterTag[] => {
        const tags: ActiveFilterTag[] = [];
        const is = t('projects.filter_tag_is');

        if (filters.tasks.length > 0)
            tags.push({
                id: 'task',
                label: `${t('projects.filter_task_section')} ${is} (${filters.tasks.length})`,
                value: filters.tasks.join(', '),
                onRemove: () => setFilters((prev) => ({ ...prev, tasks: [] })),
            });

        if (filters.datasets.length > 0)
            tags.push({
                id: 'dataset',
                label: `${t('projects.filter_dataset_section')} ${is} (${filters.datasets.length})`,
                value: filters.datasets.join(', '),
                onRemove: () => setFilters((prev) => ({ ...prev, datasets: [] })),
            });

        if (filters.states.length > 0)
            tags.push({
                id: 'state',
                label: `${t('projects.filter_state_section')} ${is} (${filters.states.length})`,
                value: filters.states.join(', '),
                onRemove: () => setFilters((prev) => ({ ...prev, states: [] })),
            });

        if (sortState.progress !== '')
            tags.push({
                id: 'sort-progress',
                label: t('projects.sort_progress_section'),
                value:
                    sortState.progress === 'ascending'
                        ? t('projects.sort_ascending')
                        : t('projects.sort_descending'),
                onRemove: () => setSortState((prev) => ({ ...prev, progress: '' })),
            });

        if (sortState.dateCreated !== '')
            tags.push({
                id: 'sort-date-created',
                label: t('projects.sort_date_created_section'),
                value:
                    sortState.dateCreated === 'recent_first'
                        ? t('projects.sort_recent_first')
                        : t('projects.sort_older_first'),
                onRemove: () => setSortState((prev) => ({ ...prev, dateCreated: '' })),
            });

        if (sortState.dueDate !== '')
            tags.push({
                id: 'sort-due-date',
                label: t('projects.sort_due_date_section'),
                value:
                    sortState.dueDate === 'recent_first'
                        ? t('projects.sort_recent_first')
                        : t('projects.sort_older_first'),
                onRemove: () => setSortState((prev) => ({ ...prev, dueDate: '' })),
            });

        return tags;
    }, [filters, sortState, t]);

    const displayedProjects: Project[] = useMemo(() => {
        let result = projects;

        if (searchQuery) {
            const q = searchQuery.toLowerCase();
            result = result.filter((p) => p.name.toLowerCase().includes(q));
        }
        if (filters.tasks.length > 0) {
            result = result.filter((p) => filters.tasks.includes(p.annotation_task_title ?? ''));
        }
        if (filters.datasets.length > 0) {
            result = result.filter((p) => filters.datasets.includes(p.dataset_name ?? ''));
        }
        if (filters.states.length > 0) {
            result = result.filter((p) =>
                filters.states.includes(t(`projects.status.${p.status}`))
            );
        }

        const hasSort =
            sortState.progress !== '' || sortState.dateCreated !== '' || sortState.dueDate !== '';

        if (hasSort) {
            result = [...result].sort((a, b) => {
                if (sortState.progress !== '') {
                    const diff = a.project_progress - b.project_progress;
                    if (diff !== 0) return sortState.progress === 'ascending' ? diff : -diff;
                }
                if (sortState.dateCreated !== '') {
                    const diff =
                        new Date(a.started_at ?? '').getTime() -
                        new Date(b.started_at ?? '').getTime();
                    if (diff !== 0) return sortState.dateCreated === 'recent_first' ? -diff : diff;
                }
                if (sortState.dueDate !== '') {
                    const diff =
                        new Date(a.deadline_at ?? '').getTime() -
                        new Date(b.deadline_at ?? '').getTime();
                    if (diff !== 0) return sortState.dueDate === 'recent_first' ? -diff : diff;
                }
                return 0;
            });
        }

        return result;
    }, [projects, searchQuery, filters, sortState, t]);

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={t('projects.title')} />
            <div className="flex flex-col gap-6 px-6 py-6">
                {/* Page header */}
                <div className="flex items-center justify-between">
                    <h1 className="text-slate-800">{t('projects.title')}</h1>
                    <Button
                        className="hover:bg-brand-blue-800 bg-brand-blue-700 h-10 font-semibold text-white"
                        onClick={() => router.visit(route('projects.create'))}
                    >
                        <Plus className="size-4" aria-hidden="true" />
                        {t('projects.create_button')}
                    </Button>
                </div>

                {/* Toolbar: filter + sort on the left, toggle on the right */}
                <div className="flex flex-wrap items-center gap-3">
                    <ProjectFilterPanel
                        sections={filterSections}
                        selected={filters}
                        onToggle={toggleFilter}
                        onClear={clearFilters}
                        hasActiveFilters={hasActiveFilters}
                    />
                    <ProjectSortPanel
                        state={sortState}
                        onChange={setSortState}
                        hasActiveSort={
                            sortState.progress !== '' ||
                            sortState.dateCreated !== '' ||
                            sortState.dueDate !== ''
                        }
                        onClear={() => setSortState(DEFAULT_SORT_STATE)}
                    />
                    <div className="flex-1" />
                    {!isAnnotationManager && (
                        <SectionToggle
                            checked={showOnlyMine}
                            onChange={handleToggleMine}
                            label={t('projects.show_only_mine')}
                        />
                    )}
                </div>

                {/* Count + search row */}
                <div className="flex items-center justify-between gap-4">
                    <p className="text-base font-medium text-slate-800">
                        {trans('projects.projects_count', { count: displayedProjects.length })}
                    </p>
                    <div className="relative w-64">
                        <Search
                            className="absolute top-1/2 left-3 size-4 -translate-y-1/2 text-slate-400"
                            aria-hidden="true"
                        />
                        <Input
                            type="search"
                            placeholder={t('projects.search_placeholder')}
                            value={searchQuery}
                            onChange={(e) => setSearchQuery(e.target.value)}
                            className="pl-9"
                            aria-label={t('projects.search_placeholder')}
                        />
                    </div>
                </div>

                {/* Active filter/sort tags */}
                <ProjectActiveFilters tags={activeTags} />

                <ProjectList projects={displayedProjects} />
            </div>
        </AppLayout>
    );
}
