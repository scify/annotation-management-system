import { InitialsAvatar } from '@/components/ui/initials-avatar';
import { useTranslations } from '@/hooks/use-translations';
import { cn } from '@/lib/utils';
import { ChevronDown, ChevronUp } from 'lucide-react';
import { useState } from 'react';
import type { HistoryAnnotator, HistoryAnnotatorSubproject } from '../types';

const STATUS_STYLES: Record<HistoryAnnotator['status'], string> = {
    active: 'border-green-500 bg-green-50 text-green-600',
    inactive: 'border-red-700 bg-red-50 text-red-700',
    pending: 'border-slate-400 bg-slate-100 text-slate-500',
};

const HISTORY_GRID_COLS = 'grid-cols-[52px_211px_133px_154px_154px_155px_154px_167px_64px]';

type Confidence = NonNullable<HistoryAnnotatorSubproject['confidence']>;

const CONFIDENCE_CLASSES: Record<Confidence, string> = {
    High: 'border-green-500 bg-green-50 text-green-600',
    Medium: 'border-amber-400 bg-amber-50 text-amber-600',
    Low: 'border-red-400 bg-red-50 text-red-600',
};

interface HistoryAnnotatorRowProps {
    annotator: HistoryAnnotator;
}

export function HistoryAnnotatorRow({ annotator }: HistoryAnnotatorRowProps) {
    const [expanded, setExpanded] = useState(false);
    const { t } = useTranslations();

    return (
        <div role="row" className="border-b border-slate-300 last:border-b-0">
            {/* ── Summary row ──────────────────────────────────────────────── */}
            <div className={cn('grid h-[54px] items-center bg-white', HISTORY_GRID_COLS)}>
                {/* Avatar */}
                <div role="cell" className="flex h-full items-center justify-center">
                    <InitialsAvatar initials={annotator.initials} />
                </div>

                {/* Username */}
                <div role="cell" className="flex h-full items-center pl-2">
                    <span className="truncate text-base font-medium text-slate-800">
                        {annotator.username}
                    </span>
                </div>

                {/* Status badge */}
                <div role="cell" className="flex h-full items-center justify-center">
                    <span
                        className={cn(
                            'inline-flex h-[22px] w-[100px] items-center justify-center rounded border text-xs font-semibold',
                            STATUS_STYLES[annotator.status]
                        )}
                    >
                        {t(`monitor.${annotator.status}`)}
                    </span>
                </div>

                {/* Total Projects */}
                <div role="cell" className="flex h-full items-center justify-end pr-4">
                    <span className="text-base font-medium text-slate-800 tabular-nums">
                        {annotator.totalProjects}
                    </span>
                </div>

                {/* Total Subprojects */}
                <div role="cell" className="flex h-full items-center justify-end pr-4">
                    <span className="text-base font-medium text-slate-800 tabular-nums">
                        {annotator.totalSubprojects}
                    </span>
                </div>

                {/* Total Annotations */}
                <div role="cell" className="flex h-full items-center justify-end pr-4">
                    <span className="text-base font-medium text-slate-800 tabular-nums">
                        {annotator.totalAnnotations}
                    </span>
                </div>

                {/* Total Flags */}
                <div role="cell" className="flex h-full items-center justify-end pr-4">
                    <span className="text-base font-medium text-slate-800 tabular-nums">
                        {annotator.totalFlags}
                    </span>
                </div>

                {/* Average Velocity */}
                <div role="cell" className="flex h-full items-center justify-center">
                    {annotator.averageVelocity != null ? (
                        <span className="text-base font-medium text-slate-800 tabular-nums">
                            {annotator.averageVelocity}
                        </span>
                    ) : (
                        <span className="bg-brand-blue-100 rounded-sm px-1.5 py-1 text-xs font-medium whitespace-nowrap text-slate-600">
                            {t('monitor.not_available_yet')}
                        </span>
                    )}
                </div>

                {/* Expand/collapse */}
                <div role="cell" className="flex h-full items-center justify-center">
                    <button
                        type="button"
                        onClick={() => setExpanded((prev) => !prev)}
                        aria-expanded={expanded}
                        aria-label={expanded ? t('monitor.collapse_row') : t('monitor.expand_row')}
                        className="focus-visible:ring-ring flex size-8 cursor-pointer items-center justify-center rounded-md text-slate-400 transition-colors hover:bg-slate-100 focus-visible:ring-2 focus-visible:outline focus-visible:outline-2"
                    >
                        {expanded ? (
                            <ChevronUp className="h-4 w-4" aria-hidden="true" />
                        ) : (
                            <ChevronDown className="h-4 w-4" aria-hidden="true" />
                        )}
                    </button>
                </div>
            </div>

            {/* ── Expanded sub-table ────────────────────────────────────────── */}
            {expanded && (
                <div>
                    {/* Sub-header */}
                    <div
                        className="flex h-[30px] items-center bg-slate-200 px-3 text-xs font-semibold text-slate-600"
                        role="row"
                    >
                        <span className="flex-1 truncate">{t('monitor.project_label')}</span>
                        <span className="flex-1 truncate">{t('monitor.subproject')}</span>
                        <span className="w-[115px] text-right">{t('monitor.col_annotations')}</span>
                        <span className="w-[115px] text-right">{t('monitor.col_flags')}</span>
                        <span className="w-[145px] text-center">{t('monitor.col_velocity')}</span>
                        <span className="w-[145px] text-center">
                            {t('monitor.col_avg_confidence')}
                        </span>
                        <span className="w-[126px] text-center">
                            {t('monitor.col_date_completed')}
                        </span>
                    </div>

                    {/* Sub-data rows */}
                    {annotator.subprojects.map((sp, idx) => (
                        <div
                            key={idx}
                            role="row"
                            className="flex h-[36px] items-center border-b border-slate-300 bg-white px-3 last:border-b-0"
                        >
                            <span className="flex-1 truncate text-xs font-normal text-slate-800">
                                {sp.project}
                            </span>
                            <span className="flex-1 truncate text-xs font-normal text-slate-800">
                                {sp.subproject}
                            </span>
                            <span className="w-[115px] text-right text-xs font-medium text-slate-800 tabular-nums">
                                {sp.annotations}
                            </span>
                            <span className="w-[115px] text-right text-xs font-medium text-slate-800 tabular-nums">
                                {sp.flags}
                            </span>
                            <span className="flex w-[145px] items-center justify-center">
                                {sp.velocity != null ? (
                                    <span className="text-xs font-medium text-slate-800 tabular-nums">
                                        {sp.velocity}
                                    </span>
                                ) : (
                                    <span className="bg-brand-blue-100 rounded-sm px-1.5 py-1 text-xs font-medium whitespace-nowrap text-slate-600">
                                        {t('monitor.not_available_yet')}
                                    </span>
                                )}
                            </span>
                            <span className="flex w-[145px] items-center justify-center">
                                {sp.confidence !== null ? (
                                    <span
                                        className={cn(
                                            'inline-flex h-[20px] min-w-[52px] items-center justify-center rounded border px-2 text-xs font-semibold',
                                            CONFIDENCE_CLASSES[sp.confidence]
                                        )}
                                    >
                                        {t(`monitor.confidence_${sp.confidence.toLowerCase()}`)}
                                    </span>
                                ) : (
                                    <span className="bg-brand-blue-100 rounded-sm px-1.5 py-1 text-xs font-medium whitespace-nowrap text-slate-600">
                                        {t('monitor.not_available_yet')}
                                    </span>
                                )}
                            </span>
                            <span className="w-[126px] text-center text-xs font-medium text-slate-800">
                                {sp.dateCompleted || '—'}
                            </span>
                        </div>
                    ))}
                </div>
            )}
        </div>
    );
}
