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
import { CircleMinus } from 'lucide-react';

export interface ProjectAnnotatorRowData {
	id: number;
	initials: string;
	username: string;
	projects: number;
	subprojects: number;
	workload: number;
	progress: number;
}

type AnnotatorsTableProps =
	| {
			mode: 'remove';
			annotators: ProjectAnnotatorRowData[];
			/** Called when the remove button for a row is clicked */
			onAnnotatorRemoved?: (id: number) => void;
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
							{t('projects.annotators_tab.table_progress')}
						</TableHead>
						{mode === 'remove' && (
							<TableHead className="text-center text-sm font-semibold text-slate-800">
								{t('projects.annotators_tab.table_action')}
							</TableHead>
						)}
					</TableRow>
				</TableHeader>
				<TableBody>
					{annotators.map((annotator) => (
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
											aria-label={`Select ${annotator.username}`}
										/>
									</label>
								</TableCell>
							)}
							<TableCell className="pl-4">
								<UserTableCell
									initials={annotator.initials}
									username={annotator.username}
									showMessageButton={mode === 'remove'}
								/>
							</TableCell>
							<TableCell className="text-right">
								<span className="text-base font-medium text-slate-800">
									{annotator.projects}
								</span>
							</TableCell>
							<TableCell className="text-right">
								<span className="text-base font-medium text-slate-800">
									{annotator.subprojects}
								</span>
							</TableCell>
							<TableCell className="text-center">
								<div className="flex justify-center">
									<WorkloadGauge value={annotator.workload} />
								</div>
							</TableCell>
							<TableCell>
								<div className="flex flex-col items-end gap-1.5">
									<span className="text-base font-medium text-slate-800">
										{annotator.progress}%
									</span>
									<div className="bg-brand-blue-100 h-[5px] w-full overflow-hidden rounded-full">
										<div
											className="bg-brand-blue-800 h-full rounded-full motion-safe:transition-[width] motion-safe:duration-500 motion-safe:ease-out"
											style={{ width: `${annotator.progress}%` }}
											role="progressbar"
											aria-valuenow={annotator.progress}
											aria-valuemin={0}
											aria-valuemax={100}
											aria-label={`Progress: ${annotator.progress}%`}
										/>
									</div>
								</div>
							</TableCell>
							{mode === 'remove' && (
								<TableCell className="text-center">
									<Button
										variant="ghost"
										size="icon"
										className="bg-brand-blue-50 text-brand-blue-700 hover:bg-brand-blue-100 hover:text-brand-blue-700 size-11 rounded-lg"
										aria-label={`Remove ${annotator.username} from project`}
										onClick={() => props.onAnnotatorRemoved?.(annotator.id)}
									>
										<CircleMinus className="size-6" aria-hidden="true" />
									</Button>
								</TableCell>
							)}
						</TableRow>
					))}
				</TableBody>
			</Table>
		</div>
	);
}
