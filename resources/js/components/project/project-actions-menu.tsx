import { ProjectDialog } from '@/components/project/project-dialog';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { useTranslations } from '@/hooks/use-translations';
import { apiFetchWithFlash } from '@/lib/api';
import type { Project } from '@/types';
import { router } from '@inertiajs/react';
import { MoreVertical, Trash2 } from 'lucide-react';
import { useState } from 'react';

interface ProjectActionsMenuProps {
    project: Pick<Project, 'id' | 'name' | 'status'>;
}

export function ProjectActionsMenu({ project }: ProjectActionsMenuProps) {
    const { t, trans } = useTranslations();
    const [confirmDelete, setConfirmDelete] = useState(false);
    const [deleting, setDeleting] = useState(false);

    function changeStatus(status: 'in_progress' | 'completed') {
        apiFetchWithFlash(route('projects.change-status'), {
            method: 'POST',
            body: JSON.stringify({ project_id: project.id, status }),
        })
            // Reload the current page (this menu appears on both the list and show
            // pages); the error toast, if any, is shown by apiFetchWithFlash.
            .then(() => router.reload())
            .catch(() => {});
    }

    function handleConfirmDelete() {
        setDeleting(true);
        apiFetchWithFlash(route('projects.destroy', project.id), { method: 'DELETE' })
            // Matches the old redirect target.
            .then(() => router.visit(route('projects.index')))
            .catch(() => {})
            .finally(() => {
                setDeleting(false);
                setConfirmDelete(false);
            });
    }

    return (
        <>
            <DropdownMenu>
                <DropdownMenuTrigger asChild>
                    <Button
                        variant="ghost"
                        size="icon"
                        className="size-[44px] shrink-0"
                        aria-label={t('projects.card.actions_label')}
                    >
                        <MoreVertical className="size-5" aria-hidden="true" />
                    </Button>
                </DropdownMenuTrigger>
                <DropdownMenuContent align="end" className="w-44">
                    <DropdownMenuItem
                        onSelect={() => router.visit(route('projects.show', project.id))}
                    >
                        {t('projects.card.action_view_edit')}
                    </DropdownMenuItem>
                    {project.status === 'in_progress' && (
                        <DropdownMenuItem onSelect={() => changeStatus('completed')}>
                            {t('projects.card.action_set_completed')}
                        </DropdownMenuItem>
                    )}
                    {project.status === 'pending' && (
                        <>
                            <DropdownMenuItem onSelect={() => changeStatus('in_progress')}>
                                {t('projects.card.action_set_in_progress')}
                            </DropdownMenuItem>
                            <DropdownMenuItem onSelect={() => setConfirmDelete(true)}>
                                {t('projects.card.action_delete')}
                            </DropdownMenuItem>
                        </>
                    )}
                </DropdownMenuContent>
            </DropdownMenu>

            <ProjectDialog
                open={confirmDelete}
                onClose={() => setConfirmDelete(false)}
                icon={<Trash2 />}
                title={t('projects.card.delete_dialog_title')}
                description={trans('projects.card.delete_dialog_description', {
                    name: project.name,
                })}
                cancelLabel={t('projects.create.cancel')}
                actionLabel={t('projects.card.delete_confirm')}
                loading={deleting}
                onAction={handleConfirmDelete}
            />
        </>
    );
}
