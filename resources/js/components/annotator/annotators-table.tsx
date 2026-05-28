import { ToggleSwitch } from '@/components/ui/toggle-switch';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { WorkloadGauge } from '@/components/workload-gauge';
import { useTranslations } from '@/hooks/use-translations';
import { UserTableCell } from '@/components/project/user-table-cell';
import { CircleMinus, Info, Mail } from 'lucide-react';

export interface ProjectAnnotatorRowData {
    id: number;
    name: string;
    annotator_progress: number;
    active_projects_count: number;
    active_subprojects_count: number;
    workload: number;
    annotator_flags?: number;
    allow_flagging?: boolean;
}

type AnnotatorsTableProps =
    | {
          mode: 'remove';
          annotators: ProjectAnnotatorRowData[];
          /** Called when the remove button for a row is clicked */
          onAnnotatorRemoved?: (id: number) => void;
          /** Called when the Allow Flagging toggle is changed for a row */
          onAllowFlaggingChange?: (id: number, enabled: boolean) => void;
          /** Called when the message button for a row is clicked */
          onMessageAnnotator?: (id: number) => void;
      }
    | {
          mode: 'selectable';
          annotators: ProjectAnnotatorRowData[];
          selectedIds: Set<number>;
          onSelectionChange: (id: number, checked: boolean) => void;
      };

export function AnnotatorsTable(props: AnnotatorsTableProps) {
    const { mode, annotators } = props;
    const { t } = useTranslations();

    return (
        <div className="overflow-hidden rounded-xl">
            <Table>
                <TableHeader>
                    <TableRow className="bg-brand-blue-100 hover:bg-brand-blue-100 border-b border-slate-300">
                        {mode === 'selectable' && (
                            <TableHead className="w-10 pl-4">
                                <span className="sr-only">Select</span>
                            </TableHead>
                        )}
                        <TableHead className="pl-4 text-sm font-semibold text-slate-800">
                            {t('projects.annotators_tab.table_username')}
                        </TableHead>
                        <TableHead className="text-right text-sm font-semibold text-slate-800">
                            {t('projects.annotators_tab.table_projects')}
                        </TableHead>
                        <TableHead className="text-right text-sm font-semibold text-slate-800">
                            {t('projects.annotators_tab.table_subprojects')}
                        </TableHead>
                        <TableHead className="text-center text-sm font-semibold text-slate-800">
                            {t('projects.annotators_tab.table_workload')}
                        </TableHead>
                        <TableHead className="text-center text-sm font-semibold text-slate-800">
                            {mode === 'remove'
                                ? t('projects.annotators_tab.table_subproject_progress')
                                : t('projects.annotators_tab.table_progress')}
                        </TableHead>
                        {mode === 'remove' && (
                            <>
                                <TableHead className="text-center text-sm font-semibold text-slate-800">
                                    {t('projects.annotators_tab.table_flags')}
                                </TableHead>
                                <TableHead className="text-center text-sm font-semibold text-slate-800">
                                    <span className="inline-flex items-center gap-1">
                                        {t('projects.annotators_tab.table_allow_flagging')}
                                        <Info
                                            className="size-3.5 text-slate-400"
                                            aria-hidden="true"
                                        />
                                    </span>
                                </TableHead>
                                <TableHead className="text-center text-sm font-semibold text-slate-800">
                                    {t('projects.annotators_tab.table_actions')}
                                </TableHead>
                            </>
                        )}
                    </TableRow>
                </TableHeader>
                <TableBody>
                    {annotators.map((annotator) => {
                        const initials = annotator.name
                            .split(' ')
                            .map((w) => w[0] ?? '')
                            .join('')
                            .slice(0, 2)
                            .toUpperCase();
                        const workloadPct = Math.round(annotator.workload * 100);
                        const progressPct = Math.round(annotator.annotator_progress * 100);

                        return (
                            <TableRow
                                key={annotator.id}
                                className="hover:bg-brand-blue-50 h-14 border-b border-slate-300 bg-white"
                            >
                                {mode === 'selectable' && (
                                    <TableCell className="pl-4">
                                        <label className="flex cursor-pointer">
                                            <Checkbox
                                                checked={props.selectedIds.has(annotator.id)}
                                                onCheckedChange={(checked) =>
                                                    props.onSelectionChange(annotator.id, checked)
                                                }
                                                aria-label={`Select ${annotator.name}`}
                                                name={`annotator-${annotator.id}`}
                                            />
                                        </label>
                                    </TableCell>
                                )}
                                <TableCell className="pl-4">
                                    <UserTableCell
                                        initials={initials}
                                        username={annotator.name}
                                        showMessageButton={false}
                                    />
                                </TableCell>
                                <TableCell className="text-right">
                                    <span className="text-base font-medium text-slate-800">
                                        {annotator.active_projects_count}
                                    </span>
                                </TableCell>
                                <TableCell className="text-right">
                                    <span className="text-base font-medium text-slate-800">
                                        {annotator.active_subprojects_count}
                                    </span>
                                </TableCell>
                                <TableCell className="text-center">
                                    <div className="flex justify-center">
                                        <WorkloadGauge value={workloadPct} />
                                    </div>
                                </TableCell>
                                <TableCell>
                                    <div className="flex flex-col items-end gap-1.5">
                                        <span className="text-base font-medium text-slate-800">
                                            {progressPct}%
                                        </span>
                                        <div className="bg-brand-blue-100 h-[5px] w-full overflow-hidden rounded-full">
                                            <div
                                                className="bg-brand-blue-800 h-full rounded-full motion-safe:transition-[width] motion-safe:duration-500 motion-safe:ease-out"
                                                style={{ width: `${progressPct}%` }}
                                                role="progressbar"
                                                aria-valuenow={progressPct}
                                                aria-valuemin={0}
                                                aria-valuemax={100}
                                                aria-label={`Progress: ${progressPct}%`}
                                            />
                                        </div>
                                    </div>
                                </TableCell>
                                {mode === 'remove' && (
                                    <>
                                        <TableCell className="text-center">
                                            <span className="text-base font-medium text-slate-800">
                                                {annotator.annotator_flags ?? 0}
                                            </span>
                                        </TableCell>
                                        <TableCell className="text-center">
                                            <div className="flex justify-center">
                                                <ToggleSwitch
                                                    id={`allow-flagging-${annotator.id}`}
                                                    checked={annotator.allow_flagging ?? false}
                                                    onChange={(enabled) =>
                                                        props.onAllowFlaggingChange?.(
                                                            annotator.id,
                                                            enabled
                                                        )
                                                    }
                                                    ariaLabel={`Allow flagging for ${annotator.name}`}
                                                />
                                            </div>
                                        </TableCell>
                                        <TableCell className="text-center">
                                            <div className="flex items-center justify-center gap-2">
                                                <Button
                                                    variant="ghost"
                                                    size="icon"
                                                    className="bg-brand-blue-50 text-brand-blue-700 hover:bg-brand-blue-100 hover:text-brand-blue-700 size-11 rounded-lg"
                                                    aria-label={`Remove ${annotator.name} from subproject`}
                                                    onClick={() =>
                                                        props.onAnnotatorRemoved?.(annotator.id)
                                                    }
                                                >
                                                    <CircleMinus
                                                        className="size-6"
                                                        aria-hidden="true"
                                                    />
                                                </Button>
                                                <Button
                                                    variant="ghost"
                                                    size="icon"
                                                    className="bg-brand-blue-50 text-brand-blue-700 hover:bg-brand-blue-100 hover:text-brand-blue-700 size-11 rounded-lg"
                                                    aria-label={`Send message to ${annotator.name}`}
                                                    onClick={() =>
                                                        props.onMessageAnnotator?.(annotator.id)
                                                    }
                                                >
                                                    <Mail className="size-6" aria-hidden="true" />
                                                </Button>
                                            </div>
                                        </TableCell>
                                    </>
                                )}
                            </TableRow>
                        );
                    })}
                </TableBody>
            </Table>
        </div>
    );
}
