import { type ProjectAnnotatorRowData } from '@/components/annotator/annotators-table';
import { AnnotatorsTable } from '@/components/annotator/annotators-table';
import { ProjectDialog } from '@/components/project/project-dialog';
import { SendMessageDialog } from '@/components/send-message-dialog';
import { Button } from '@/components/ui/button';
import { useTranslations } from '@/hooks/use-translations';
import { apiFetchWithFlash } from '@/lib/api';
import { router } from '@inertiajs/react';
import { Plus, UserMinus } from 'lucide-react';
import { useState } from 'react';

interface SubprojectAnnotatorsPanelProps {
    annotators: ProjectAnnotatorRowData[];
    canManageAnnotators?: boolean;
    projectId: number;
    subprojectId: number;
}

export function SubprojectAnnotatorsPanel({
    annotators,
    canManageAnnotators = true,
    projectId,
    subprojectId,
}: SubprojectAnnotatorsPanelProps) {
    const { t, trans } = useTranslations();
    const [annotatorToRemove, setAnnotatorToRemove] = useState<ProjectAnnotatorRowData | null>(
        null
    );
    const [messageAnnotator, setMessageAnnotator] = useState<ProjectAnnotatorRowData | null>(null);
    const [removing, setRemoving] = useState(false);
    const [flaggingState, setFlaggingState] = useState<Record<number, boolean>>(() =>
        Object.fromEntries(annotators.map((a) => [a.id, a.allow_flagging ?? false]))
    );

    function handleRemoveRequest(id: number) {
        const annotator = annotators.find((a) => a.id === id);
        if (annotator) setAnnotatorToRemove(annotator);
    }

    function handleMessageRequest(id: number) {
        const annotator = annotators.find((a) => a.id === id);
        if (annotator) setMessageAnnotator(annotator);
    }

    function handleConfirmRemove() {
        if (!annotatorToRemove) return;
        setRemoving(true);
        apiFetchWithFlash(
            route('projects.subprojects.annotators.detach', [
                projectId,
                subprojectId,
                annotatorToRemove.id,
            ]),
            { method: 'DELETE' }
        )
            // Refresh the annotators table — we're on the edit page; the error toast
            // (if any) is already shown by apiFetchWithFlash.
            .then(() => router.reload())
            .catch(() => {})
            .finally(() => {
                setRemoving(false);
                setAnnotatorToRemove(null);
            });
    }

    function handleAllowFlaggingChange(id: number, enabled: boolean) {
        setFlaggingState((prev) => ({ ...prev, [id]: enabled }));
    }

    const annotatorsWithFlagging = annotators.map((a) => ({
        ...a,
        allow_flagging: flaggingState[a.id] ?? a.allow_flagging ?? false,
    }));

    return (
        <div className="flex flex-col gap-6">
            <div className="flex items-center justify-between">
                <h2 className="page-subtitle">{t('projects.annotators_tab.title')}</h2>
                {canManageAnnotators && (
                    <Button
                        className="hover:bg-brand-blue-800 h-10 font-semibold text-white"
                        onPress={() =>
                            router.visit(
                                route('projects.subprojects.annotators.add', [
                                    projectId,
                                    subprojectId,
                                ])
                            )
                        }
                    >
                        <Plus className="size-4" aria-hidden="true" />
                        {t('projects.annotators_tab.add_annotators')}
                    </Button>
                )}
            </div>

            <AnnotatorsTable
                mode="remove"
                annotators={annotatorsWithFlagging}
                canRemoveAnnotator={true}
                onAnnotatorRemoved={handleRemoveRequest}
                onAllowFlaggingChange={handleAllowFlaggingChange}
                onMessageAnnotator={handleMessageRequest}
            />

            <ProjectDialog
                open={annotatorToRemove !== null}
                onClose={() => setAnnotatorToRemove(null)}
                icon={<UserMinus />}
                title={t('sub-projects.annotators_panel.remove_dialog_title')}
                description={trans('sub-projects.annotators_panel.remove_dialog_description', {
                    username: annotatorToRemove?.name ?? '',
                })}
                cancelLabel={t('sub-projects.create.cancel')}
                actionLabel={t('sub-projects.annotators_panel.remove_confirm')}
                loading={removing}
                onAction={handleConfirmRemove}
            />

            <SendMessageDialog
                open={messageAnnotator !== null}
                onClose={() => setMessageAnnotator(null)}
                targetName={messageAnnotator?.name ?? ''}
                recipientUserId={messageAnnotator?.id ?? 0}
            />
        </div>
    );
}
