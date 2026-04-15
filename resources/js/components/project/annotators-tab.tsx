import { Button } from '@/components/ui/button';
import {
	Table,
	TableBody,
	TableCell,
	TableHead,
	TableHeader,
	TableRow,
} from '@/components/ui/table';
import { WorkloadGauge } from '@/components/workload-gauge';
import { UserTableCell } from '@/components/project/user-table-cell';
import { CircleMinus, Plus } from 'lucide-react';

export interface ProjectAnnotatorRowData {
	id: number;
	initials: string;
	username: string;
	projects: number;
	subprojects: number;
	workload: number;
	progress: number;
}

const MOCK_ANNOTATORS: ProjectAnnotatorRowData[] = [
	{
		id: 1,
		initials: 'G',
		username: '@ggiannakopulos',
		projects: 23,
		subprojects: 23,
		workload: 85,
		progress: 75,
	},
	{
		id: 2,
		initials: 'G',
		username: '@fpapastergiou',
		projects: 23,
		subprojects: 2,
		workload: 20,
		progress: 75,
	},
	{
		id: 3,
		initials: 'G',
		username: '@ggiannakopulos',
		projects: 23,
		subprojects: 7,
		workload: 92,
		progress: 75,
	},
];

interface AnnotatorsTabProps {
	annotators?: ProjectAnnotatorRowData[];
	/** Called after an annotator is successfully removed */
	onAnnotatorRemoved?: (id: number) => void;
}

export function AnnotatorsTab({
	annotators = MOCK_ANNOTATORS,
	onAnnotatorRemoved,
}: AnnotatorsTabProps) {
	return (
		<div
			id="tabpanel-annotators"
			role="tabpanel"
			aria-labelledby="tab-annotators"
			className="flex flex-col gap-6"
		>
			<div className="flex items-center justify-between">
				<h2 className="page-subtitle">Annotators</h2>
				<Button className="hover:bg-brand-blue-800 h-10 font-semibold text-white">
					<Plus className="size-4" aria-hidden="true" />
					Add Annotator
				</Button>
			</div>
			<div className="overflow-hidden rounded-xl">
				<Table>
					<TableHeader>
						<TableRow className="bg-brand-blue-100 hover:bg-brand-blue-100 border-b border-slate-300">
							<TableHead className="pl-4 text-sm font-semibold text-slate-800">
								Username
							</TableHead>
							<TableHead className="text-right text-sm font-semibold text-slate-800">
								Projects
							</TableHead>
							<TableHead className="text-right text-sm font-semibold text-slate-800">
								Subprojects
							</TableHead>
							<TableHead className="text-center text-sm font-semibold text-slate-800">
								Remain.Workload
							</TableHead>
							<TableHead className="text-center text-sm font-semibold text-slate-800">
								Progress
							</TableHead>
							<TableHead className="text-center text-sm font-semibold text-slate-800">
								Action
							</TableHead>
						</TableRow>
					</TableHeader>
					<TableBody>
						{annotators.map((annotator) => (
							<TableRow
								key={annotator.id}
								className="hover:bg-brand-blue-50 h-14 border-b border-slate-300 bg-white"
							>
								<TableCell className="pl-4">
									<UserTableCell
										initials={annotator.initials}
										username={annotator.username}
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
								<TableCell className="text-center">
									<Button
										variant="ghost"
										size="icon"
										className="bg-brand-blue-50 text-brand-blue-700 hover:bg-brand-blue-100 hover:text-brand-blue-700 size-11 rounded-lg"
										aria-label={`Remove ${annotator.username} from project`}
										onClick={() => onAnnotatorRemoved?.(annotator.id)}
									>
										<CircleMinus className="size-6" aria-hidden="true" />
									</Button>
								</TableCell>
							</TableRow>
						))}
					</TableBody>
				</Table>
			</div>
		</div>
	);
}
