import { ProjectActionsMenu } from '@/components/project/project-actions-menu';
import { ProjectStatusBadge } from '@/components/project/project-status-badge';
import { useTranslations } from '@/hooks/use-translations';
import type { Project } from '@/types';
import { formatDateDMY, formatDateDMYShort } from '@/utils/format';
import {
    BellRing,
    CircleAlert,
    Container,
    Database,
    FolderDot,
    FolderOpenDot,
    UserRound,
} from 'lucide-react';
import { InitialsAvatar } from '@/components/ui/initials-avatar';
import { toInitials } from './project-card';

export function ProjectListItem({ project }: { project: Project }) {
    const { t } = useTranslations();

    const progress = Math.round(project.project_progress * 100);
    const ownerInitials = toInitials(project.owner_name ?? '?');
    const ownerUsername = project.owner_name ? `${project.owner_name}` : '—';
    const coManagers = project.co_managers ?? [];
    const visibleCoManagers = coManagers.slice(0, 2);
    const extraCount = coManagers.length - 2;

    return (
        <article className="flex flex-col gap-4 rounded-2xl border border-slate-200 bg-white px-5 pt-7 pb-4">
            {/* Row 1: icon + name/date/progress | status badge + actions */}
            <div className="flex items-start justify-between gap-4">
                <div className="flex min-w-0 flex-1 gap-3">
                    <div className="flex size-[42px] shrink-0 items-center justify-start">
                        <FolderDot
                            className="text-brand-blue-500 h-[29.75px] w-[39px]"
                            aria-hidden="true"
                        />
                    </div>
                    <div className="flex min-w-0 flex-1 flex-col gap-2">
                        <div className="flex min-w-0 flex-col gap-1">
                            <p className="text-xl leading-9 font-medium text-slate-800">
                                {project.name}
                            </p>
                            <div className="flex w-full max-w-[360px] items-center gap-1 text-sm">
                                <span className="text-slate-800">
                                    {project.started_at
                                        ? formatDateDMY(project.started_at)
                                        : t('projects.card.open')}
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
                                {(project.scheduled_at || project.deadline_at) && (
                                    <span className="ml-5 text-xs text-slate-400 tabular-nums">
                                        ({formatDateDMYShort(project.scheduled_at)}–
                                        {formatDateDMYShort(project.deadline_at)})
                                    </span>
                                )}
                            </div>
                        </div>

                        {/* Progress bar — below name/date */}
                        <div className="flex w-full max-w-[526px] flex-col gap-2">
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
                </div>

                {/* Status badge + actions — top-right */}
                <div className="flex shrink-0 items-start gap-2">
                    <ProjectStatusBadge status={project.status} />
                    <ProjectActionsMenu project={project} />
                </div>
            </div>

            {/* Row 2: tags | indicators — indented to align with text */}
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

                {/* Indicator chips */}
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

            {/* Row 3: owner + co-managers — inline, indented */}
            <div className="flex items-center gap-7 pl-[54px]">
                <div className="flex shrink-0 items-center gap-2">
                    <span className="text-xs font-semibold text-slate-600">
                        {t('projects.card.owner')}
                    </span>
                    <div className="flex items-center gap-1">
                        <InitialsAvatar initials={ownerInitials} size="sm" />
                        <span className="text-[0.75rem] text-slate-600">{ownerUsername}</span>
                    </div>
                </div>

                <div className="flex min-w-0 items-center gap-2">
                    <span className="text-xs font-semibold text-slate-600">
                        {t('projects.card.co_managers')}
                    </span>
                    <div className="flex items-center gap-1">
                        {visibleCoManagers.map((cm) => (
                            <div key={cm.id} className="flex min-w-0 items-center gap-1">
                                <InitialsAvatar initials={toInitials(cm.username)} size="sm" />
                                <span className="truncate text-[0.75rem] text-slate-600">
                                    {cm.username}
                                </span>
                            </div>
                        ))}
                        {extraCount > 0 && (
                            <span className="text-[0.75rem] text-slate-600">+{extraCount}</span>
                        )}
                    </div>
                </div>
            </div>
        </article>
    );
}
