import { InitialsAvatar } from '@/components/ui/initials-avatar';
import { Input } from '@/components/ui/input';
import { STATUS_VARIANT, toInitials } from '@/components/project/project-card';
import { Badge } from '@/components/ui/badge';
import { useTranslations } from '@/hooks/use-translations';
import { cn } from '@/lib/utils';
import type { Project } from '@/types';
import { formatDateDMY } from '@/utils/format';
import {
    ArrowUpDown,
    BellRing,
    Check,
    ChevronDown,
    CircleAlert,
    Container,
    Database,
    FolderDot,
    FolderOpenDot,
    ListFilter,
    Search,
    UserRound,
} from 'lucide-react';
import { useMemo, useState } from 'react';

export const MOCK_PROJECT_ANNOTATORS: Record<number, number[]> = {
    1: [1, 2],
    2: [2, 3],
    3: [3, 4],
    4: [4, 5],
};

interface SelectableProjectItemProps {
    project: Project;
    isSelected: boolean;
    onToggle: () => void;
}

function SelectableProjectItem({
    project,
    isSelected,
    onToggle,
}: Readonly<SelectableProjectItemProps>) {
    const { t } = useTranslations();

    const statusVariant = STATUS_VARIANT[project.status];
    const statusLabel = t(`projects.status.${project.status}`);
    const progress = Math.round(project.project_progress * 100);
    const ownerInitials = toInitials(project.owner_name ?? '?');
    const ownerUsername = project.owner_name ? `${project.owner_name}` : '—';
    const coManagers = project.co_managers ?? [];
    const visibleCoManagers = coManagers.slice(0, 2);
    const extraCount = coManagers.length - 2;

    return (
        <div
            role="checkbox"
            aria-checked={isSelected}
            aria-label={project.name}
            tabIndex={0}
            onClick={onToggle}
            onKeyDown={(e) => {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    onToggle();
                }
            }}
            className="focus-visible:ring-brand-blue-700 flex cursor-pointer items-start gap-4 rounded-xl outline-none focus-visible:ring-2"
        >
            {/* External checkbox — vertically aligned with project name */}
            <span className="mt-8 flex shrink-0 items-center justify-center">
                <span
                    aria-hidden="true"
                    className={cn(
                        'flex size-[18px] shrink-0 items-center justify-center rounded border-2',
                        isSelected
                            ? 'border-brand-blue-700 bg-brand-blue-700'
                            : 'border-slate-300 bg-white'
                    )}
                >
                    {isSelected && <Check className="size-3 text-white" strokeWidth={3} />}
                </span>
            </span>

            {/* Card — no "View Project" action */}
            <article
                className={cn(
                    'flex flex-1 flex-col gap-4 rounded-2xl border-4 px-[17px] pt-[25px] pb-[13px] transition-colors',
                    isSelected
                        ? 'border-brand-blue-100 bg-brand-blue-50'
                        : 'border-transparent bg-white'
                )}
            >
                {/* Row 1: icon + name/date/badge | progress bar */}
                <div className="flex items-start justify-between gap-4">
                    <div className="flex min-w-0 flex-1 gap-3">
                        <div className="flex size-[42px] shrink-0 items-center justify-start">
                            <FolderDot
                                className="text-brand-blue-500 h-[29.75px] w-[39px]"
                                aria-hidden="true"
                            />
                        </div>
                        <div className="flex min-w-0 flex-col gap-1">
                            <p className="text-xl leading-9 font-medium text-slate-800">
                                {project.name}
                            </p>
                            <div className="flex items-center gap-1 text-sm">
                                <span className="text-slate-800">
                                    {formatDateDMY(project.started_at)}
                                </span>
                                {project.is_delayed_to_start && (
                                    <CircleAlert
                                        className="size-[15px] shrink-0 text-red-500"
                                        aria-label="Delayed"
                                    />
                                )}
                                <span className="text-slate-500">–</span>
                                <span className="text-slate-800">
                                    {project.completed_at
                                        ? formatDateDMY(project.completed_at)
                                        : t('projects.card.ongoing')}
                                </span>
                                {project.is_delayed_to_end && (
                                    <CircleAlert
                                        className="size-[15px] shrink-0 text-red-500"
                                        aria-label="Overdue"
                                    />
                                )}
                            </div>
                            <Badge variant={statusVariant}>{statusLabel}</Badge>
                        </div>
                    </div>

                    {/* Progress bar */}
                    <div className="flex w-[244px] shrink-0 flex-col gap-2">
                        <span className="text-sm font-semibold text-slate-800">
                            {t('projects.card.overall_progress')} {progress}%
                        </span>
                        <div className="bg-brand-blue-100 h-3 w-full overflow-hidden rounded-full">
                            <div
                                className="bg-brand-blue-800 h-full rounded-full motion-safe:transition-[width] motion-safe:duration-500 motion-safe:ease-out"
                                style={{ width: `${progress}%` }}
                                role="progressbar"
                                aria-valuenow={progress}
                                aria-valuemin={0}
                                aria-valuemax={100}
                                aria-label={`Project progress: ${progress}%`}
                            />
                        </div>
                    </div>
                </div>

                {/* Row 2: task/dataset chips | indicator counts */}
                <div className="flex items-center justify-between gap-4 pl-[54px]">
                    <div className="flex min-w-0 gap-2.5 overflow-hidden">
                        {project.annotation_task_title && (
                            <div className="bg-brand-blue-100 flex h-8 min-w-0 flex-1 items-center gap-[10px] rounded-lg px-[10px]">
                                <Container
                                    className="size-5 shrink-0 text-slate-600"
                                    aria-hidden="true"
                                />
                                <span className="min-w-0 truncate text-sm font-medium text-slate-800">
                                    {project.annotation_task_title}
                                </span>
                            </div>
                        )}
                        {project.dataset_name && (
                            <div className="bg-brand-blue-100 flex h-8 shrink-0 items-center gap-[10px] rounded-lg px-[10px]">
                                <Database
                                    className="size-5 shrink-0 text-slate-600"
                                    aria-hidden="true"
                                />
                                <span className="truncate text-sm font-medium text-slate-800">
                                    {project.dataset_name}
                                </span>
                            </div>
                        )}
                    </div>

                    <div className="flex shrink-0 gap-3">
                        <div
                            className="bg-brand-blue-50 flex h-8 w-[72px] items-center justify-center gap-3 rounded-lg px-[10px]"
                            title="Subprojects"
                        >
                            <FolderOpenDot
                                className="size-[18px] shrink-0 text-slate-400"
                                aria-hidden="true"
                            />
                            <span className="text-base font-medium text-slate-800">
                                {project.subprojects_count}
                            </span>
                        </div>
                        <div
                            className="bg-brand-blue-50 flex h-8 w-[72px] items-center justify-center gap-3 rounded-lg px-[10px]"
                            title="Annotators"
                        >
                            <UserRound
                                className="size-[18px] shrink-0 text-slate-400"
                                aria-hidden="true"
                            />
                            <span className="text-base font-medium text-slate-800">
                                {project.annotators_count}
                            </span>
                        </div>
                        <div
                            className="bg-brand-blue-50 flex h-8 w-[72px] items-center justify-center gap-3 rounded-lg px-[10px]"
                            title="Notifications"
                        >
                            <BellRing
                                className="size-[18px] shrink-0 text-slate-400"
                                aria-hidden="true"
                            />
                            <span className="text-base font-medium text-slate-800">
                                {project.notifications_count}
                            </span>
                        </div>
                    </div>
                </div>

                {/* Row 3: owner + co-managers (no "View Project" button) */}
                <div className="pl-[54px]">
                    <div className="flex gap-7">
                        <div className="flex shrink-0 flex-col gap-2">
                            <span className="text-xs font-semibold text-slate-600">
                                {t('projects.card.owner')}
                            </span>
                            <div className="flex items-center gap-1">
                                <InitialsAvatar initials={ownerInitials} size="sm" />
                                <span className="text-[0.75rem] text-slate-600">
                                    {ownerUsername}
                                </span>
                            </div>
                        </div>

                        <div className="flex min-w-0 flex-col gap-2">
                            <span className="text-xs font-semibold text-slate-600">
                                {t('projects.card.co_managers')}
                            </span>
                            <div className="flex items-center gap-1">
                                {visibleCoManagers.map((cm) => (
                                    <div key={cm.id} className="flex min-w-0 items-center gap-1">
                                        <InitialsAvatar
                                            initials={toInitials(cm.username)}
                                            size="sm"
                                        />
                                        <span className="truncate text-[0.75rem] text-slate-600">
                                            {cm.username}
                                        </span>
                                    </div>
                                ))}
                                {extraCount > 0 && (
                                    <span className="text-[0.75rem] text-slate-600">
                                        +{extraCount}
                                    </span>
                                )}
                            </div>
                        </div>
                    </div>
                </div>
            </article>
        </div>
    );
}

interface ConnectProjectsStepProps {
    projects: Project[];
    myProjects: Project[];
    selectedProjectIds: number[];
    onSelectionChange: (ids: number[]) => void;
}

export function ConnectProjectsStep({
    projects,
    myProjects,
    selectedProjectIds,
    onSelectionChange,
}: ConnectProjectsStepProps) {
    const { t, trans } = useTranslations();
    const [searchQuery, setSearchQuery] = useState('');
    const [showOnlyMine, setShowOnlyMine] = useState(false);

    const source = showOnlyMine ? myProjects : projects;

    const filtered = useMemo(
        () => source.filter((p) => p.name.toLowerCase().includes(searchQuery.toLowerCase())),
        [source, searchQuery]
    );

    const allSelected =
        filtered.length > 0 && filtered.every((p) => selectedProjectIds.includes(p.id));

    function toggleProject(id: number) {
        onSelectionChange(
            selectedProjectIds.includes(id)
                ? selectedProjectIds.filter((x) => x !== id)
                : [...selectedProjectIds, id]
        );
    }

    function toggleAll() {
        if (allSelected) {
            const filteredIds = new Set(filtered.map((p) => p.id));
            onSelectionChange(selectedProjectIds.filter((id) => !filteredIds.has(id)));
        } else {
            onSelectionChange([...new Set([...selectedProjectIds, ...filtered.map((p) => p.id)])]);
        }
    }

    return (
        <div className="flex flex-col gap-4">
            {/* Heading row */}
            <div className="flex items-start justify-between gap-4">
                <div className="flex flex-col gap-0.5">
                    <h2 className="text-xl font-medium text-slate-800">
                        {t('users.connect_projects.heading')}
                    </h2>
                    <p className="text-sm text-slate-500">
                        {trans('users.connect_projects.selected', {
                            count: selectedProjectIds.length,
                        })}
                    </p>
                </div>

                <label className="flex cursor-pointer items-center gap-2 text-sm text-slate-700">
                    <button
                        type="button"
                        role="switch"
                        aria-checked={showOnlyMine}
                        onClick={() => setShowOnlyMine((v) => !v)}
                        className={cn(
                            'focus-visible:ring-brand-blue-700 relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors focus-visible:ring-2 focus-visible:outline-none',
                            showOnlyMine ? 'bg-brand-blue-700' : 'bg-slate-200'
                        )}
                    >
                        <span
                            className={cn(
                                'pointer-events-none inline-block size-5 rounded-full bg-white shadow-lg transition-transform',
                                showOnlyMine ? 'translate-x-5' : 'translate-x-0'
                            )}
                        />
                    </button>
                    {t('users.connect_projects.show_only_mine')}
                </label>
            </div>

            {/* Filter/search bar */}
            <div className="flex items-center gap-3">
                {/* TODO: wire filter/sort when backend connected */}
                <button
                    type="button"
                    disabled
                    className="flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm text-slate-700 opacity-60"
                >
                    <ListFilter className="size-4 shrink-0 text-slate-500" aria-hidden="true" />
                    {t('users.connect_projects.filter')}
                    <ChevronDown className="size-4 shrink-0 text-slate-400" aria-hidden="true" />
                </button>
                <button
                    type="button"
                    disabled
                    className="flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm text-slate-700 opacity-60"
                >
                    <ArrowUpDown className="size-4 shrink-0 text-slate-500" aria-hidden="true" />
                    {t('users.connect_projects.sorting')}
                    <ChevronDown className="size-4 shrink-0 text-slate-400" aria-hidden="true" />
                </button>

                <div className="relative ml-auto w-72">
                    <Search
                        className="absolute top-1/2 left-3 size-4 -translate-y-1/2 text-slate-400"
                        aria-hidden="true"
                    />
                    <Input
                        type="search"
                        name="project-search"
                        placeholder={t('users.connect_projects.search_placeholder')}
                        value={searchQuery}
                        onChange={(e) => setSearchQuery(e.target.value)}
                        className="pl-9"
                        aria-label={t('users.connect_projects.search_placeholder')}
                    />
                </div>
            </div>

            {/* Select all */}
            <button
                type="button"
                onClick={toggleAll}
                className="flex w-fit items-center gap-3 text-sm text-slate-700"
            >
                <span
                    aria-hidden="true"
                    className={cn(
                        'flex size-[18px] shrink-0 items-center justify-center rounded border-2',
                        allSelected
                            ? 'border-brand-blue-700 bg-brand-blue-700'
                            : 'border-slate-300 bg-white'
                    )}
                >
                    {allSelected && <Check className="size-3 text-white" strokeWidth={3} />}
                </span>
                {t('users.connect_projects.select_all')}
            </button>

            {/* Project list */}
            {filtered.length === 0 ? (
                <div className="flex items-center justify-center rounded-2xl border border-slate-200 bg-white p-14">
                    <p className="text-sm text-slate-400">
                        {t('users.connect_projects.no_projects')}
                    </p>
                </div>
            ) : (
                <div
                    role="group"
                    aria-label={t('users.connect_projects.heading')}
                    className="flex flex-col gap-3"
                >
                    {filtered.map((project) => (
                        <SelectableProjectItem
                            key={project.id}
                            project={project}
                            isSelected={selectedProjectIds.includes(project.id)}
                            onToggle={() => toggleProject(project.id)}
                        />
                    ))}
                </div>
            )}
        </div>
    );
}
