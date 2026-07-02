import { useTranslations } from '@/hooks/use-translations';
import type { AnnotationData } from '@/types';
import { FlagIcon, InfoIcon } from 'lucide-react';

interface AnnotationSidebarProps {
    /** Free navigation enabled — was `mode === 'flexible'`. */
    canNavigate: boolean;
    data: AnnotationData;
    /** Whether the "Submit All Pending" action is offered (manual submission). */
    canSubmitAllPending: boolean;
    /** Inert in the mock — submits all pending annotations. */
    onSubmitAllPending?: () => void;
}

/**
 * The annotation-tool sidebar from the Figma mockups. Replaces the standard
 * AppSidebar on this page (and has no collapse toggle). Sections: Description,
 * Annotation Progress (stats + multi-segment bar), and Flagged Instances; a
 * "Submit All Pending" action is added when manual submission is enabled.
 */
export function AnnotationSidebar({
    canNavigate,
    data,
    canSubmitAllPending,
    onSubmitAllPending,
}: AnnotationSidebarProps) {
    const { t, trans } = useTranslations();
    const { progress, flagged } = data;

    const secondary = canNavigate ? progress.pending : progress.thisSession;
    const secondaryLabel = canNavigate ? t('annotation.pending') : t('annotation.this_session');

    const submittedWidth = (progress.submitted / progress.totalInstances) * 100;
    const submittedAndSecondaryWidth = Math.min(
        ((progress.submitted + secondary) / progress.totalInstances) * 100,
        100
    );

    return (
        <aside className="from-brand-blue-700 to-brand-blue-950 flex h-screen w-[268px] shrink-0 flex-col gap-6 overflow-y-auto rounded-tr-[20px] rounded-br-[20px] bg-gradient-to-t px-5 py-6 text-white">
            {/* Description */}
            <section className="border-brand-blue-500 flex flex-col gap-2 rounded-xl border px-3 py-6">
                <h2 className="text-base font-semibold text-white">
                    {t('annotation.description')}
                </h2>
                <p className="text-sm whitespace-pre-line text-white/90">{data.description}</p>
            </section>

            {/* Annotation progress */}
            <section className="flex flex-col gap-4">
                <div className="bg-brand-blue-100 flex items-center justify-center rounded-2xl p-[10px]">
                    <span className="text-base font-semibold text-slate-800">
                        {t('annotation.annotation_progress')}
                    </span>
                </div>

                <p className="text-sm font-medium text-white">
                    {trans('annotation.instances_count', {
                        done: progress.submitted,
                        total: progress.totalInstances.toLocaleString(),
                    })}
                </p>

                <div className="flex flex-col gap-3">
                    <StatRow label={t('annotation.submitted')} value={progress.submitted} />
                    <StatRow label={secondaryLabel} value={secondary} />
                </div>

                {/* Progress label + 3-segment bar */}
                <div className="flex flex-col gap-2">
                    <span className="text-sm font-medium text-white">
                        {trans(
                            canNavigate ? 'annotation.submitted_progress' : 'annotation.progress',
                            { pct: progress.submittedPct }
                        )}
                    </span>
                    <div className="relative h-2 w-full" role="presentation">
                        <div className="absolute inset-0 rounded-full bg-white" />
                        <div
                            className="bg-brand-yellow-400 absolute inset-y-0 left-0 rounded-full"
                            style={{ width: `${submittedAndSecondaryWidth}%` }}
                        />
                        <div
                            className="bg-brand-blue-400 absolute inset-y-0 left-0 rounded-full"
                            style={{ width: `${submittedWidth}%` }}
                            role="progressbar"
                            aria-valuenow={progress.submittedPct}
                            aria-valuemin={0}
                            aria-valuemax={100}
                            aria-label={t('annotation.annotation_progress')}
                        />
                    </div>
                    {/* Legend */}
                    <div className="mt-1 flex flex-col gap-1 text-xs font-medium text-white">
                        <LegendEntry
                            color="bg-brand-blue-400"
                            label={`${t('annotation.submitted')} (${progress.submitted.toLocaleString()})`}
                        />
                        <LegendEntry
                            color="bg-brand-yellow-400"
                            label={`${secondaryLabel} (${secondary})`}
                        />
                        <LegendEntry
                            color="bg-white"
                            label={`${t('annotation.not_annotated')} (${progress.notAnnotated.toLocaleString()})`}
                        />
                    </div>
                </div>

                {canSubmitAllPending && (
                    <button
                        type="button"
                        onClick={onSubmitAllPending}
                        className="bg-brand-yellow-400 focus-visible:outline-brand-yellow-400 flex h-10 w-full touch-manipulation items-center justify-center rounded-lg text-sm font-semibold text-slate-800 transition-colors hover:brightness-95 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2"
                    >
                        {t('annotation.submit_all_pending')}
                    </button>
                )}
            </section>

            {/* Flagged instances */}
            <section className="mt-auto flex flex-col gap-3">
                <h2 className="flex items-center justify-center gap-1.5 text-sm font-bold text-white">
                    <FlagIcon className="size-4 shrink-0" aria-hidden="true" />
                    {t('annotation.flagged_instances')}
                </h2>
                <div className="bg-brand-blue-100/20 flex items-center justify-center rounded-full px-3 py-1.5">
                    <span className="text-sm font-semibold text-white">
                        {trans('annotation.total_replied', {
                            total: flagged.total,
                            replied: flagged.replied,
                        })}
                    </span>
                </div>
            </section>
        </aside>
    );
}

function StatRow({ label, value }: { label: string; value: number }) {
    return (
        <div className="flex items-center gap-[10px]">
            <div className="flex items-center gap-1">
                <span className="text-sm font-medium text-white">{label}</span>
                <InfoIcon className="size-[18px] shrink-0 text-white/70" aria-hidden="true" />
            </div>
            <span className="ml-auto text-sm font-bold text-white tabular-nums">
                {value.toLocaleString()}
            </span>
        </div>
    );
}

function LegendEntry({ color, label }: { color: string; label: string }) {
    return (
        <span className="flex items-center gap-1">
            <span className={`${color} size-3 shrink-0 rounded-[2px]`} />
            {label}
        </span>
    );
}
