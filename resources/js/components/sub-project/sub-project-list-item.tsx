import { ProjectDialog } from '@/components/project/project-dialog';
import { type StatusVariant } from '@/components/project/project-card';
import {
    PriorityBadge,
    type SubprojectPriority,
} from '@/components/sub-project/configuration-step';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { Tag } from '@/components/ui/tag';
import { useTranslations } from '@/hooks/use-translations';
import { apiFetchWithFlash } from '@/lib/api';
import { cn } from '@/lib/utils';
import { router } from '@inertiajs/react';
import { BellRing, FolderOpenDot, MoreVertical, Trash2, UserRound } from 'lucide-react';
import { useState } from 'react';

export interface SubProjectListItemData {
    id: number;
    name: string;
    instancesRange: string;
    dateRange: string;
    status: StatusVariant;
    statusLabel: string;
    subprojectStatus: 'in_progress' | 'pending' | 'completed';
    priority?: SubprojectPriority;
    progress: number;
    annotators: number;
    notifications: number;
}

interface SubProjectListItemProps {
    subProject: SubProjectListItemData;
    /** Project ID used to build the edit route */
    projectId?: number;
    className?: string;
    showActions?: boolean;
}

export function SubProjectListItem({
    subProject,
    projectId,
    className,
    showActions = true,
}: SubProjectListItemProps) {
    const { t, trans } = useTranslations();
    const [confirmDelete, setConfirmDelete] = useState(false);
    const [deleting, setDeleting] = useState(false);

    // No local status state lives here, so once the change persists we reload the
    // page props to refresh the badge — apiFetchWithFlash surfaces the success/error
    // toast (and keeps the error body inspectable, unlike an Inertia 302 redirect).
    function changeStatus(status: 'in_progress' | 'completed') {
        apiFetchWithFlash(route('sub-projects.change-status'), {
            method: 'POST',
            body: JSON.stringify({ sub_project_id: subProject.id, status }),
        })
            .then(() => router.reload())
            .catch(() => {});
    }

    function handleConfirmDelete() {
        if (!projectId) return;
        setDeleting(true);
        apiFetchWithFlash(
            route('projects.subprojects.destroy', {
                projectId,
                subprojectId: subProject.id,
            }),
            { method: 'DELETE' }
        )
            // Matches the old redirect target; the error toast (if any) is shown by
            // apiFetchWithFlash.
            .then(() => router.visit(route('projects.show', projectId)))
            .catch(() => {})
            .finally(() => {
                setDeleting(false);
                setConfirmDelete(false);
            });
    }

    return (
        <>
            <article
                className={cn(
                    'flex flex-col gap-3 rounded-2xl border border-slate-200 bg-white px-5 pt-4 pb-5',
                    className
                )}
            >
                {/* Row 1: icon + name/instances/date | status badge | 3-dot menu */}
                <div className="flex items-start justify-between pr-2">
                    <div className="flex min-w-0 flex-1 gap-[14px]">
                        <div className="flex size-[42px] shrink-0 items-center justify-start">
                            <FolderOpenDot className="text-brand-blue-500" aria-hidden="true" />
                        </div>
                        <div className="flex min-w-0 flex-col gap-0">
                            <div className="flex flex-wrap items-start gap-2">
                                <p className="text-lg leading-9 font-medium text-slate-800">
                                    {subProject.name}
                                </p>
                                <Tag className="shrink-0 self-center">
                                    {t('sub-projects.list_item.instances')}{' '}
                                    {subProject.instancesRange}
                                </Tag>
                            </div>
                            <p className="text-sm text-slate-600" suppressHydrationWarning>
                                {subProject.dateRange}
                            </p>
                        </div>
                    </div>

                    <div className="flex shrink-0 items-center gap-3 pl-14">
                        {subProject.priority && (
                            <PriorityBadge priority={subProject.priority} size="sm" />
                        )}
                        <Badge variant={subProject.status}>{subProject.statusLabel}</Badge>

                        {showActions && (
                            <DropdownMenu>
                                <DropdownMenuTrigger asChild>
                                    <Button
                                        variant="ghost"
                                        size="icon"
                                        className="size-[44px] shrink-0"
                                        aria-label={t('sub-projects.list_item.actions_label')}
                                    >
                                        <MoreVertical className="size-5" aria-hidden="true" />
                                    </Button>
                                </DropdownMenuTrigger>
                                <DropdownMenuContent align="end" className="w-44">
                                    <DropdownMenuItem
                                        onSelect={() => {
                                            if (projectId) {
                                                router.visit(
                                                    route('projects.subprojects.edit', {
                                                        projectId,
                                                        subprojectId: subProject.id,
                                                    })
                                                );
                                            }
                                        }}
                                    >
                                        {t('sub-projects.list_item.action_view_edit')}
                                    </DropdownMenuItem>
                                    {subProject.subprojectStatus === 'in_progress' && (
                                        <DropdownMenuItem
                                            onSelect={() => changeStatus('completed')}
                                        >
                                            {t('sub-projects.list_item.action_set_completed')}
                                        </DropdownMenuItem>
                                    )}
                                    {subProject.subprojectStatus === 'pending' && (
                                        <>
                                            <DropdownMenuItem
                                                onSelect={() => changeStatus('in_progress')}
                                            >
                                                {t('sub-projects.list_item.action_set_in_progress')}
                                            </DropdownMenuItem>
                                            <DropdownMenuItem
                                                onSelect={() => setConfirmDelete(true)}
                                            >
                                                {t('sub-projects.list_item.action_delete')}
                                            </DropdownMenuItem>
                                        </>
                                    )}
                                    <DropdownMenuItem isDisabled>
                                        {t('sub-projects.list_item.action_test')}
                                    </DropdownMenuItem>
                                </DropdownMenuContent>
                            </DropdownMenu>
                        )}
                    </div>
                </div>

                {/* Row 2: progress bar | indicators — indented to align with text */}
                <div className="flex items-end gap-4 pl-[56px]">
                    <div className="flex min-w-0 flex-1 flex-col gap-2">
                        <span className="text-sm font-semibold text-slate-800">
                            {trans('sub-projects.list_item.progress')} {subProject.progress}%
                        </span>
                        <div className="bg-brand-blue-100 h-2 w-full overflow-hidden rounded-full">
                            <div
                                className="bg-brand-blue-800 h-full rounded-full motion-safe:transition-[width] motion-safe:duration-500 motion-safe:ease-out"
                                style={{ width: `${subProject.progress}%` }}
                                role="progressbar"
                                aria-valuenow={subProject.progress}
                                aria-valuemin={0}
                                aria-valuemax={100}
                                aria-label={`Subproject progress: ${subProject.progress}%`}
                            />
                        </div>
                    </div>

                    <div className="flex shrink-0 gap-3">
                        <div
                            className="bg-brand-blue-50 flex h-8 w-[72px] items-center justify-center gap-3 rounded-lg px-[10px]"
                            title="Annotators"
                        >
                            <UserRound
                                className="size-[18px] shrink-0 text-slate-400"
                                aria-hidden="true"
                            />
                            <span className="text-base font-medium text-slate-800">
                                {subProject.annotators}
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
                                {subProject.notifications}
                            </span>
                        </div>
                    </div>
                </div>
            </article>

            <ProjectDialog
                open={confirmDelete}
                onClose={() => setConfirmDelete(false)}
                icon={<Trash2 />}
                title={t('sub-projects.list_item.delete_dialog_title')}
                description={trans('sub-projects.list_item.delete_dialog_description', {
                    name: subProject.name,
                })}
                cancelLabel={t('projects.create.cancel')}
                actionLabel={t('sub-projects.list_item.delete_confirm')}
                loading={deleting}
                onAction={handleConfirmDelete}
            />
        </>
    );
}
