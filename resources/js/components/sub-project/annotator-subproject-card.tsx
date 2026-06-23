import { useTranslations } from '@/hooks/use-translations';
import type { AnnotatorSubProject } from '@/types';
import { formatDateDMY, formatDateDMYShort } from '@/utils/format';
import { Link } from '@inertiajs/react';
import { Container, FolderDot } from 'lucide-react';

export function AnnotatorSubProjectCard({ subProject }: { subProject: AnnotatorSubProject }) {
    const { t } = useTranslations();

    // Auto-submission subprojects have no pending state — only submitted vs not-annotated.
    const hasPending = !subProject.auto_submission;
    const submittedPct = Math.round(subProject.submitted_pct);
    const submittedAndPendingWidth =
        subProject.submitted_and_pending_pct ?? subProject.submitted_pct;

    const progressLabel = hasPending
        ? t('dashboard.annotator.submitted_progress')
        : t('dashboard.annotator.progress');

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
                    <div className="flex h-8 w-fit max-w-full items-center gap-[10px] rounded-lg bg-slate-200 px-[10px]">
                        <Container className="size-5 shrink-0 text-slate-600" aria-hidden="true" />
                        <span className="min-w-0 truncate text-sm font-medium text-slate-800">
                            {subProject.project_name}
                        </span>
                    </div>
                </div>

                {/* Progress */}
                <div className="flex flex-col gap-2">
                    <span className="text-sm font-semibold text-slate-800">
                        {progressLabel} {submittedPct}%
                    </span>
                    {/* Overlay: not-annotated (back) → pending (mid) → submitted (front) */}
                    <div className="relative h-2 w-full">
                        <div className="bg-brand-blue-400 absolute inset-0 rounded-full" />
                        {hasPending && (
                            <div
                                className="bg-brand-yellow-400 absolute inset-y-0 left-0 rounded-full motion-safe:transition-[width] motion-safe:duration-500 motion-safe:ease-out"
                                style={{ width: `${submittedAndPendingWidth}%` }}
                            />
                        )}
                        <div
                            className="bg-brand-blue-800 absolute inset-y-0 left-0 rounded-full motion-safe:transition-[width] motion-safe:duration-500 motion-safe:ease-out"
                            style={{ width: `${subProject.submitted_pct}%` }}
                            role="progressbar"
                            aria-valuenow={submittedPct}
                            aria-valuemin={0}
                            aria-valuemax={100}
                            aria-label={`${progressLabel} ${submittedPct}%`}
                        />
                    </div>
                    {/* Legend */}
                    <div className="flex flex-col gap-1 text-xs font-medium text-slate-800">
                        <div className="flex items-start justify-between">
                            <span className="flex items-center gap-1">
                                <span className="bg-brand-blue-800 size-3 shrink-0 rounded-[2px]" />
                                {t('dashboard.annotator.legend_submitted')} (
                                {subProject.submitted_count})
                            </span>
                            {hasPending && (
                                <span className="flex flex-1 items-center gap-1">
                                    <span className="bg-brand-yellow-400 size-3 shrink-0 rounded-[2px]" />
                                    {t('dashboard.annotator.legend_pending')} (
                                    {subProject.pending_count ?? 0})
                                </span>
                            )}
                        </div>
                        <span className="flex items-center gap-1">
                            <span className="bg-brand-blue-400 size-3 shrink-0 rounded-[2px]" />
                            {t('dashboard.annotator.legend_not_annotated')} (
                            {subProject.not_annotated_count.toLocaleString()})
                        </span>
                    </div>
                </div>
            </div>

            {/* Resume button */}
            <Link
                href={route('annotation-tasks.show', { subProject: subProject.id })}
                className="bg-brand-blue-700 hover:bg-brand-blue-600 focus-visible:outline-brand-blue-700 flex h-10 w-full touch-manipulation items-center justify-center rounded-lg text-base font-semibold text-white transition-colors focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2"
            >
                {t('dashboard.annotator.resume')}
            </Link>
        </article>
    );
}
