import {
    ProjectActiveFilters,
    type ActiveFilterTag,
} from '@/components/project/project-active-filters';
import {
    ProjectFilterPanel,
    type FilterState,
    type FilterSectionKey,
} from '@/components/project/project-filter-panel';
import {
    DEFAULT_SORT_STATE,
    ProjectSortPanel,
    type SortState,
} from '@/components/project/project-sort-panel';
import { Input } from '@/components/ui/input';
import { useTranslations } from '@/hooks/use-translations';
import { cn } from '@/lib/utils';
import type { Project } from '@/types';
import { Search } from 'lucide-react';
import { useMemo, useState } from 'react';

interface FilterableProjectListProps {
    projects: Project[];
    myProjects?: Project[];
    showMineToggle?: boolean;
    mineToggleLabel?: string | ((checked: boolean) => string);
    renderItem: (project: Project) => React.ReactNode;
    /** Rendered between the toolbar and the active-filter tags (e.g. a count row). */
    renderAfterToolbar?: (displayedProjects: Project[]) => React.ReactNode;
    /** Rendered between the active-filter tags and the project list (e.g. a "Select all" button). */
    renderBeforeList?: (displayedProjects: Project[]) => React.ReactNode;
    listClassName?: string;
    listAriaLabel?: string;
    searchPlaceholder?: string;
    emptyLabel?: string;
}

export function FilterableProjectList({
    projects,
    myProjects,
    showMineToggle = false,
    mineToggleLabel,
    renderItem,
    renderAfterToolbar,
    renderBeforeList,
    listClassName,
    listAriaLabel,
    searchPlaceholder,
    emptyLabel,
}: FilterableProjectListProps) {
    const { t } = useTranslations();
    const [searchQuery, setSearchQuery] = useState('');
    const [filters, setFilters] = useState<FilterState>({ tasks: [], datasets: [], states: [] });
    const [sortState, setSortState] = useState<SortState>(DEFAULT_SORT_STATE);
    const [showOnlyMine, setShowOnlyMine] = useState(false);

    const source = showOnlyMine && myProjects ? myProjects : projects;

    const filterSections = useMemo(
        (): { key: FilterSectionKey; label: string; items: string[]; searchable: boolean }[] => [
            {
                key: 'tasks',
                label: t('projects.filter_task_section'),
                items: [
                    ...new Set(
                        source.flatMap((p) =>
                            p.annotation_task_title ? [p.annotation_task_title] : []
                        )
                    ),
                ],
                searchable: true,
            },
            {
                key: 'datasets',
                label: t('projects.filter_dataset_section'),
                items: [
                    ...new Set(source.flatMap((p) => (p.dataset_name ? [p.dataset_name] : []))),
                ],
                searchable: true,
            },
            {
                key: 'states',
                label: t('projects.filter_state_section'),
                items: [...new Set(source.map((p) => t(`projects.status.${p.status}`)))],
                searchable: false,
            },
        ],
        [source, t]
    );

    const displayedProjects = useMemo(() => {
        let result = source;

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
    }, [source, searchQuery, filters, sortState, t]);

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

    const hasActiveFilters =
        filters.tasks.length > 0 || filters.datasets.length > 0 || filters.states.length > 0;
    const hasActiveSort =
        sortState.progress !== '' || sortState.dateCreated !== '' || sortState.dueDate !== '';

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

    const resolvedMineLabel =
        typeof mineToggleLabel === 'function'
            ? mineToggleLabel(showOnlyMine)
            : (mineToggleLabel ?? '');

    return (
        <div className="flex flex-col gap-4">
            {/* Toolbar: filter + sort + optional mine toggle + search */}
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
                    hasActiveSort={hasActiveSort}
                    onClear={() => setSortState(DEFAULT_SORT_STATE)}
                />
                <div className="flex-1" />
                {showMineToggle && (
                    <div className="flex items-center gap-2">
                        <button
                            type="button"
                            role="switch"
                            aria-checked={showOnlyMine}
                            onClick={() => handleToggleMine(!showOnlyMine)}
                            className={cn(
                                'focus-visible:ring-brand-blue-700 relative h-6 w-11 shrink-0 cursor-pointer rounded-full transition-colors focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:outline-none',
                                showOnlyMine ? 'bg-brand-blue-700' : 'bg-slate-300'
                            )}
                        >
                            <span
                                className={cn(
                                    'absolute top-0.5 left-0.5 h-5 w-5 rounded-full bg-white shadow-sm motion-safe:transition-transform motion-safe:duration-200',
                                    showOnlyMine ? 'translate-x-5' : 'translate-x-0'
                                )}
                            />
                        </button>
                        <span className="text-sm font-medium text-slate-800">
                            {resolvedMineLabel}
                        </span>
                    </div>
                )}
                <div className="relative w-64">
                    <Search
                        className="absolute top-1/2 left-3 size-4 -translate-y-1/2 text-slate-400"
                        aria-hidden="true"
                    />
                    <Input
                        type="search"
                        name="project-search"
                        placeholder={searchPlaceholder ?? t('projects.search_placeholder')}
                        value={searchQuery}
                        onChange={(e) => setSearchQuery(e.target.value)}
                        className="pl-9"
                        aria-label={searchPlaceholder ?? t('projects.search_placeholder')}
                    />
                </div>
            </div>

            {renderAfterToolbar?.(displayedProjects)}

            <ProjectActiveFilters tags={activeTags} />

            {renderBeforeList?.(displayedProjects)}

            {displayedProjects.length === 0 ? (
                <div className="flex items-center justify-center rounded-2xl border border-slate-200 bg-white p-14">
                    <p className="text-sm text-slate-400">
                        {emptyLabel ?? t('projects.no_projects')}
                    </p>
                </div>
            ) : (
                <div
                    role={listAriaLabel ? 'group' : undefined}
                    aria-label={listAriaLabel}
                    className={listClassName ?? 'flex flex-col gap-6'}
                >
                    {displayedProjects.map((project) => (
                        <div key={project.id}>{renderItem(project)}</div>
                    ))}
                </div>
            )}
        </div>
    );
}
