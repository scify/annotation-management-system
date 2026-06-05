import { AnnotatorsTable } from '@/components/annotator/annotators-table';
import { type ProjectAnnotatorRowData } from '@/components/annotator/annotators-table';
import { ProjectDialog } from '@/components/project/project-dialog';
import { Button } from '@/components/ui/button';
import { useTranslations } from '@/hooks/use-translations';
import { router } from '@inertiajs/react';
import { Plus, UserMinus } from 'lucide-react';
import { useEffect, useState } from 'react';

const MOCK_ANNOTATORS: ProjectAnnotatorRowData[] = [
    {
        id: 1,
        name: 'George Giannakopoulos',
        active_projects_count: 23,
        active_subprojects_count: 23,
        workload: 0.85,
        annotator_progress: 0.75,
    },
    {
        id: 2,
        name: 'Fotini Papastergiou',
        active_projects_count: 23,
        active_subprojects_count: 2,
        workload: 0.2,
        annotator_progress: 0.75,
    },
    {
        id: 3,
        name: 'Nelly Savrani',
        active_projects_count: 23,
        active_subprojects_count: 7,
        workload: 0.92,
        annotator_progress: 0.75,
    },
];

interface AnnotatorsTabProps {
    annotators?: ProjectAnnotatorRowData[];
    projectId: number;
    projectStatus: 'pending' | 'in_progress' | 'completed';
    /** Called after an annotator is successfully removed */
    onAnnotatorRemoved?: (id: number) => void;
}

export function AnnotatorsTab({
    annotators = MOCK_ANNOTATORS,
    projectId,
    projectStatus,
    onAnnotatorRemoved,
}: AnnotatorsTabProps) {
    const { t, trans } = useTranslations();
    const canEdit = projectStatus === 'pending';

    const [flaggingState, setFlaggingState] = useState<Record<number, boolean>>(() =>
        Object.fromEntries(annotators.map((a) => [a.id, a.allow_flagging ?? false]))
    );
    const [annotatorToRemove, setAnnotatorToRemove] = useState<ProjectAnnotatorRowData | null>(
        null
    );
    const [removedIds, setRemovedIds] = useState<Set<number>>(new Set());

    useEffect(() => {
        setFlaggingState(
            Object.fromEntries(annotators.map((a) => [a.id, a.allow_flagging ?? false]))
        );
    }, [annotators]);

    function handleAllowFlaggingChange(id: number, enabled: boolean) {
        setFlaggingState((prev) => ({ ...prev, [id]: enabled }));
        router.post(
            route('projects.toggle-can-flag'),
            { project_id: projectId, annotator_id: id },
            {
                preserveScroll: true,
                onError: () => setFlaggingState((prev) => ({ ...prev, [id]: !enabled })),
            }
        );
    }

    function handleRemoveRequest(id: number) {
        const annotator = annotatorsWithFlagging.find((a) => a.id === id);
        if (annotator) setAnnotatorToRemove(annotator);
    }

    function handleConfirmRemove() {
        if (annotatorToRemove) {
            setRemovedIds((prev) => new Set([...prev, annotatorToRemove.id]));
            onAnnotatorRemoved?.(annotatorToRemove.id);
            setAnnotatorToRemove(null);
        }
    }

    const annotatorsWithFlagging = annotators.map((a) => ({
        ...a,
        allow_flagging: flaggingState[a.id] ?? a.allow_flagging ?? false,
    }));

    const visibleAnnotators = annotatorsWithFlagging.filter((a) => !removedIds.has(a.id));

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
                    disabled={!canEdit}
                    onPress={() => router.visit(route('projects.annotators.add', projectId))}
                >
                    <Plus className="size-4" aria-hidden="true" />
                    {t('projects.annotators_tab.add_annotators')}
                </Button>
            </div>
            <AnnotatorsTable
                mode="remove"
                annotators={visibleAnnotators}
                canRemoveAnnotator={canEdit}
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
                onAction={handleConfirmRemove}
            />
        </div>
    );
}
