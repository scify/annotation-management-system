import { Avatar, AvatarFallback } from '@/components/ui/avatar';
import { Badge, badgeVariants } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { useTranslations } from '@/hooks/use-translations';
import type { Project, ProjectStatus } from '@/types';
import { formatDateDMY, formatDateDMYShort } from '@/utils/format';
import { Link } from '@inertiajs/react';
import { type VariantProps } from 'class-variance-authority';
import {
    BellRing,
    CircleAlert,
    Container,
    Database,
    FolderDot,
    FolderOpenDot,
    UserRound,
} from 'lucide-react';

export type StatusVariant = Extract<
    NonNullable<VariantProps<typeof badgeVariants>['variant']>,
    'yellow' | 'lime' | 'slate' | 'pink'
>;

/** @deprecated Use Project from @/types instead */
export interface ProjectCardData {
    id: number;
    name: string;
    dateRange: string;
    status: StatusVariant;
    statusLabel: string;
    tags: [string, string];
    subprojects: number;
    annotators: number;
    notifications: number;
    progress: number;
    owner: { initials: string; username: string };
    coManagers: Array<{ initials: string; username: string }>;
}

export const STATUS_VARIANT: Record<ProjectStatus, StatusVariant> = {
    in_progress: 'yellow',
    pending: 'slate',
    completed: 'lime',
};

export function toInitials(username: string): string {
    return username.charAt(0).toUpperCase();
}

export function UserAvatar({ initials }: { initials: string }) {
    return (
        <Avatar className="size-[22px] shrink-0">
            <AvatarFallback className="bg-brand-blue-300 rounded-full text-[10px] font-semibold text-white">
                {initials}
            </AvatarFallback>
        </Avatar>
    );
}

export function ProjectCard({ project }: { project: Project }) {
    const { t } = useTranslations();

    const statusVariant = STATUS_VARIANT[project.status];
    const statusLabel = t(`projects.status.${project.status}`);
    const progress = Math.round(project.project_progress * 100);
    const ownerInitials = toInitials(project.owner_name ?? '?');
    const ownerUsername = project.owner_name ? `@${project.owner_name}` : '—';
    const coManagers = project.co_managers ?? [];
    const visibleCoManagers = coManagers.slice(0, 2);
    const extraCount = coManagers.length - 2;

    return (
        <article className="flex flex-col gap-7 rounded-[16px] border border-slate-200 bg-white p-5">
            {/* Top section */}
            <div className="flex flex-col gap-4">
                {/* Icon + name + date */}
                <div className="flex flex-col gap-3">
                    <div className="flex items-start justify-between">
                        <div className="project-icon flex size-[42px] items-center justify-start rounded-lg bg-transparent">
                            <FolderDot
                                className="text-brand-blue-500 h-[29.75px] w-[39px]"
                                aria-hidden="true"
                            />
                        </div>
                        <Badge variant={statusVariant}>{statusLabel}</Badge>
                    </div>
                    <div>
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
                            {(project.scheduled_at || project.deadline_at) && (
                                <span className="ml-auto text-xs text-slate-400 tabular-nums">
                                    ({formatDateDMYShort(project.scheduled_at)}–
                                    {formatDateDMYShort(project.deadline_at)})
                                </span>
                            )}
                        </div>
                    </div>
                </div>

                {/* Tag chips */}
                {(project.annotation_task_title || project.dataset_name) && (
                    <div className="flex flex-col gap-1">
                        {project.annotation_task_title && (
                            <div className="bg-brand-blue-100 flex h-8 w-fit max-w-full items-center gap-[10px] rounded-lg px-[10px]">
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
                            <div className="bg-brand-blue-100 flex h-8 w-fit max-w-full items-center gap-[10px] rounded-lg px-[10px]">
                                <Database
                                    className="size-5 shrink-0 text-slate-600"
                                    aria-hidden="true"
                                />
                                <span className="min-w-0 truncate text-sm font-medium text-slate-800">
                                    {project.dataset_name}
                                </span>
                            </div>
                        )}
                    </div>
                )}

                {/* Indicator chips: subprojects / annotators / notifications */}
                <div className="flex gap-3">
                    <div
                        className="bg-brand-blue-50 flex h-8 flex-1 items-center justify-center gap-4 rounded-lg px-[10px]"
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
                        className="bg-brand-blue-50 flex h-8 flex-1 items-center justify-center gap-4 rounded-lg px-[10px]"
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
                        className="bg-brand-blue-50 flex h-8 flex-1 items-center justify-center gap-4 rounded-lg px-[10px]"
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

            {/* Bottom section */}
            <div className="flex flex-col gap-4">
                {/* Progress bar */}
                <div className="flex flex-col gap-2">
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

                {/* Owner + Co-managers */}
                <div className="flex gap-7">
                    <div className="flex shrink-0 flex-col gap-2">
                        <span className="text-xs font-semibold text-slate-600">
                            {t('projects.card.owner')}
                        </span>
                        <div className="flex items-center gap-1">
                            <UserAvatar initials={ownerInitials} />
                            <span className="text-[0.75rem] text-slate-600">{ownerUsername}</span>
                        </div>
                    </div>

                    <div className="flex min-w-0 flex-col gap-2">
                        <span className="text-xs font-semibold text-slate-600">
                            {t('projects.card.co_managers')}
                        </span>
                        <div className="flex items-center gap-1">
                            {visibleCoManagers.map((cm) => (
                                <div key={cm.username} className="flex min-w-0 items-center gap-1">
                                    <UserAvatar initials={toInitials(cm.username)} />
                                    <span className="truncate text-[0.75rem] text-slate-600">
                                        @{cm.username}
                                    </span>
                                </div>
                            ))}
                            {extraCount > 0 && (
                                <span className="text-[0.75rem] text-slate-600">+{extraCount}</span>
                            )}
                        </div>
                    </div>
                </div>

                {/* View Project */}
                <Link href={route('projects.show', project.id)} className="w-full">
                    <Button className="hover:bg-brand-blue-800 h-10 w-full font-semibold text-white">
                        {t('projects.card.view_project')}
                    </Button>
                </Link>
            </div>
        </article>
    );
}
