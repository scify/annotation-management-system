import { AnnotationTaskModeDialog } from '@/pages/annotation-task/components/annotation-task-mode-dialog';
import { useTranslations } from '@/hooks/use-translations';
import type { AnnotatorSubProject } from '@/types';
import { formatDateDMY, formatDateDMYShort } from '@/utils/format';
import { Container, FolderDot } from 'lucide-react';
import { useMemo, useState } from 'react';

/**
 * Deterministic mock of the submitted / pending / not-annotated breakdown.
 *
 * TODO(backend): replace with real counts once the annotation progress per
 * subproject is exposed by the API. Derived from the subproject id so the
 * values stay stable across renders.
 */
function useMockProgress(subProject: AnnotatorSubProject) {
    return useMemo(() => {
        const { id, first_instance_index, last_instance_index } = subProject;
        const total = Math.max(last_instance_index - first_instance_index + 1, 1);

        const submittedRatio = 0.2 + ((id * 37) % 45) / 100; // 0.20–0.65
        const pendingRatio = 0.05 + ((id * 13) % 20) / 100; // 0.05–0.25

        const submitted = Math.round(total * submittedRatio);
        const pending = Math.round(total * pendingRatio);
        const notAnnotated = Math.max(total - submitted - pending, 0);

        return {
            submitted,
            pending,
            notAnnotated,
            submittedPct: Math.round((submitted / total) * 100),
            submittedWidth: (submitted / total) * 100,
            submittedAndPendingWidth: Math.min(((submitted + pending) / total) * 100, 100),
        };
    }, [subProject]);
}

export function AnnotatorSubProjectCard({ subProject }: { subProject: AnnotatorSubProject }) {
    const { t } = useTranslations();
    const progress = useMockProgress(subProject);
    const [modeDialogOpen, setModeDialogOpen] = useState(false);

    const browsingLabel = subProject.flexible
        ? t('sub-projects.configuration.flexible_browsing_label')
        : t('sub-projects.configuration.strict_browsing_label');

    return (
        <article className="flex flex-col gap-7 rounded-[16px] border border-slate-200 bg-white p-5">
            {/* Top section */}
            <div className="flex flex-col gap-5">
                {/* Icon + browsing badge */}
                <div className="flex items-start justify-between">
                    <div className="flex size-[42px] items-center justify-start">
                        <FolderDot
                            className="text-brand-blue-500 h-[29.75px] w-[39px]"
                            aria-hidden="true"
                        />
                    </div>
                    <span className="bg-brand-blue-100 flex h-8 items-center rounded-lg px-[10px] text-sm font-medium text-slate-800">
                        {browsingLabel}
                    </span>
                </div>

                {/* Title + dates + task tag */}
                <div className="flex flex-col gap-2">
                    <p className="text-xl leading-9 font-medium text-slate-800">
                        {subProject.name}
                    </p>
                    <div className="flex items-center gap-1 text-sm">
                        <span className="text-slate-800">
                            {subProject.started_at
                                ? formatDateDMY(subProject.started_at)
                                : t('projects.card.open')}
                        </span>
                        <span className="text-slate-500">–</span>
                        <span className="text-slate-800">
                            {subProject.completed_at
                                ? formatDateDMY(subProject.completed_at)
                                : t('projects.card.ongoing')}
                        </span>
                        {(subProject.scheduled_at || subProject.deadline_at) && (
                            <span className="ml-auto text-xs text-slate-400 tabular-nums">
                                ({formatDateDMYShort(subProject.scheduled_at)}–
                                {formatDateDMYShort(subProject.deadline_at)})
                            </span>
                        )}
                    </div>
                    {subProject.annotation_task_title && (
                        <div className="flex h-8 w-fit max-w-full items-center gap-[10px] rounded-lg bg-slate-200 px-[10px]">
                            <Container
                                className="size-5 shrink-0 text-slate-600"
                                aria-hidden="true"
                            />
                            <span className="min-w-0 truncate text-sm font-medium text-slate-800">
                                {subProject.annotation_task_title}
                            </span>
                        </div>
                    )}
                </div>

                {/* Progress (mocked) */}
                <div className="flex flex-col gap-2">
                    <span className="text-sm font-semibold text-slate-800">
                        {t('dashboard.annotator.submitted_progress')} {progress.submittedPct}%
                    </span>
                    {/* 3-segment overlay: not-annotated (back) → pending (mid) → submitted (front) */}
                    <div className="relative h-2 w-full">
                        <div className="bg-brand-blue-400 absolute inset-0 rounded-full" />
                        <div
                            className="bg-brand-yellow-400 absolute inset-y-0 left-0 rounded-full motion-safe:transition-[width] motion-safe:duration-500 motion-safe:ease-out"
                            style={{ width: `${progress.submittedAndPendingWidth}%` }}
                        />
                        <div
                            className="bg-brand-blue-800 absolute inset-y-0 left-0 rounded-full motion-safe:transition-[width] motion-safe:duration-500 motion-safe:ease-out"
                            style={{ width: `${progress.submittedWidth}%` }}
                            role="progressbar"
                            aria-valuenow={progress.submittedPct}
                            aria-valuemin={0}
                            aria-valuemax={100}
                            aria-label={`${t('dashboard.annotator.submitted_progress')} ${progress.submittedPct}%`}
                        />
                    </div>
                    {/* Legend */}
                    <div className="flex flex-col gap-1 text-xs font-medium text-slate-800">
                        <div className="flex items-start justify-between">
                            <span className="flex items-center gap-1">
                                <span className="bg-brand-blue-800 size-3 shrink-0 rounded-[2px]" />
                                {t('dashboard.annotator.legend_submitted')} ({progress.submitted})
                            </span>
                            <span className="flex flex-1 items-center gap-1">
                                <span className="bg-brand-yellow-400 size-3 shrink-0 rounded-[2px]" />
                                {t('dashboard.annotator.legend_pending')} ({progress.pending})
                            </span>
                        </div>
                        <span className="flex items-center gap-1">
                            <span className="bg-brand-blue-400 size-3 shrink-0 rounded-[2px]" />
                            {t('dashboard.annotator.legend_not_annotated')} (
                            {progress.notAnnotated.toLocaleString()})
                        </span>
                    </div>
                </div>
            </div>

            {/* Resume button */}
            <button
                type="button"
                onClick={() => setModeDialogOpen(true)}
                className="bg-brand-blue-700 hover:bg-brand-blue-600 focus-visible:outline-brand-blue-700 flex h-10 w-full touch-manipulation items-center justify-center rounded-lg text-base font-semibold text-white transition-colors focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2"
            >
                {t('dashboard.annotator.resume')}
            </button>

            <AnnotationTaskModeDialog
                open={modeDialogOpen}
                onOpenChange={setModeDialogOpen}
                subProjectId={subProject.id}
            />
        </article>
    );
}
