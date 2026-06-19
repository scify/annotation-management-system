import { AnnotatorsTable } from '@/components/annotator/annotators-table';
import { type ProjectAnnotatorRowData } from '@/components/annotator/annotators-table';
import { ProjectDialog } from '@/components/project/project-dialog';
import { Button } from '@/components/ui/button';
import { useTranslations } from '@/hooks/use-translations';
import { apiFetchWithFlash } from '@/lib/api';
import { router } from '@inertiajs/react';
import { Plus, UserMinus } from 'lucide-react';
import { useEffect, useState } from 'react';

interface AnnotatorsTabProps {
    annotators?: ProjectAnnotatorRowData[];
    projectId: number;
    projectStatus: 'pending' | 'in_progress' | 'completed';
}

export function AnnotatorsTab({ annotators = [], projectId, projectStatus }: AnnotatorsTabProps) {
    const { t, trans } = useTranslations();
    const [flaggingState, setFlaggingState] = useState<Record<number, boolean>>(() =>
        Object.fromEntries(annotators.map((a) => [a.id, a.allow_flagging ?? false]))
    );
    const [annotatorToRemove, setAnnotatorToRemove] = useState<ProjectAnnotatorRowData | null>(
        null
    );
    const [removing, setRemoving] = useState(false);

    useEffect(() => {
        setFlaggingState(
            Object.fromEntries(annotators.map((a) => [a.id, a.allow_flagging ?? false]))
        );
    }, [annotators]);

    function handleAllowFlaggingChange(id: number, enabled: boolean) {
        setFlaggingState((prev) => ({ ...prev, [id]: enabled }));
        // Optimistic toggle; the JSON response keeps us off a full page reload. On
        // failure apiFetchWithFlash toasts the error and we revert the switch.
        apiFetchWithFlash(route('projects.toggle-can-flag'), {
            method: 'POST',
            body: JSON.stringify({ project_id: projectId, annotator_id: id }),
        }).catch(() => setFlaggingState((prev) => ({ ...prev, [id]: !enabled })));
    }

    function handleRemoveRequest(id: number) {
        const annotator = annotatorsWithFlagging.find((a) => a.id === id);
        if (annotator) setAnnotatorToRemove(annotator);
    }

    function handleConfirmRemove() {
        if (!annotatorToRemove) return;
        setRemoving(true);
        apiFetchWithFlash(route('projects.annotators.detach', [projectId, annotatorToRemove.id]), {
            method: 'DELETE',
        })
            // Refresh the annotators list (we're on the project show page).
            .then(() => router.reload())
            .catch(() => {})
            .finally(() => {
                setRemoving(false);
                setAnnotatorToRemove(null);
            });
    }

    const annotatorsWithFlagging = annotators.map((a) => ({
        ...a,
        allow_flagging: flaggingState[a.id] ?? a.allow_flagging ?? false,
        can_be_removed: projectStatus === 'pending' && (a.can_be_removed ?? false),
    }));

    const visibleAnnotators = annotatorsWithFlagging;

    return (
        <div
            id="tabpanel-annotators"
            role="tabpanel"
            aria-labelledby="tab-annotators"
            className="flex flex-col gap-6"
        >
            <div className="flex items-center justify-between">
                <h2 className="page-subtitle">{t('projects.annotators_tab.title')}</h2>
                <Button
                    className="hover:bg-brand-blue-800 h-10 font-semibold text-white disabled:cursor-not-allowed disabled:opacity-50"
                    onPress={() => router.visit(route('projects.annotators.add', projectId))}
                >
                    <Plus className="size-4" aria-hidden="true" />
                    {t('projects.annotators_tab.add_annotators')}
                </Button>
            </div>
            <AnnotatorsTable
                mode="remove"
                annotators={visibleAnnotators}
                canRemoveAnnotator={true}
                onAnnotatorRemoved={handleRemoveRequest}
                onAllowFlaggingChange={handleAllowFlaggingChange}
            />

            <ProjectDialog
                open={annotatorToRemove !== null}
                onClose={() => setAnnotatorToRemove(null)}
                icon={<UserMinus />}
                title={t('projects.annotators_tab.remove_dialog_title')}
                description={trans('projects.annotators_tab.remove_dialog_description', {
                    username: annotatorToRemove?.name ?? '',
                })}
                cancelLabel={t('projects.create.cancel')}
                actionLabel={t('projects.annotators_tab.remove_confirm')}
                loading={removing}
                onAction={handleConfirmRemove}
            />
        </div>
    );
}
