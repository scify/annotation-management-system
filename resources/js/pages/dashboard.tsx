import AppLayout from '@/layouts/app-layout';
import { ProjectCard } from '@/components/project/project-card';
import { WorkloadGauge } from '@/components/workload-gauge';
import {
	Table,
	TableBody,
	TableCell,
	TableHead,
	TableHeader,
	TableRow,
} from '@/components/ui/table';
import { useTranslations } from '@/hooks/use-translations';
import type { Annotator, BreadcrumbItem, Project } from '@/types';
import { Head } from '@inertiajs/react';

interface Props {
	my_projects: Project[];
	all_projects?: Project[];
	my_annotators: Annotator[];
	all_annotators?: Annotator[];
}

export default function Dashboard({
	my_projects,
	all_projects,
	my_annotators,
	all_annotators,
}: Props) {
	const projects = all_projects ?? my_projects;
	const annotators = all_annotators ?? my_annotators;
	const { t } = useTranslations();

	const breadcrumbs: BreadcrumbItem[] = [
		{
			title: t('navbar.dashboard'),
			href: '/dashboard',
		},
	];

	return (
		<AppLayout breadcrumbs={breadcrumbs}>
			<Head title="Dashboard" />
			<div className="flex flex-col gap-8 px-6 py-6">
				<h1 className="mb-5 text-slate-800">{t('projects.dashboard.overview_title')}</h1>

				<section aria-labelledby="projects-heading">
					<h2 id="projects-heading" className="page-subtitle mb-5">
						{t('projects.dashboard.active_projects_heading')}
					</h2>
					<div className="grid gap-6 sm:grid-cols-2 xl:grid-cols-3">
						{projects.map((project) => (
							<ProjectCard key={project.id} project={project} />
						))}
					</div>
				</section>

				<section aria-labelledby="annotators-heading">
					<h2 id="annotators-heading" className="page-subtitle mb-5">
						{t('projects.dashboard.annotators_overview_heading')}
					</h2>
					<div className="overflow-hidden rounded-xl">
						<Table>
							<TableHeader>
								<TableRow className="bg-brand-blue-100 hover:bg-brand-blue-100 border-b border-slate-300">
									<TableHead className="pl-12 text-sm font-semibold text-slate-800">
										{t('projects.dashboard.table_username')}
									</TableHead>
									<TableHead className="text-right text-sm font-semibold text-slate-800">
										{t('projects.dashboard.table_active_projects')}
									</TableHead>
									<TableHead className="text-center text-sm font-semibold text-slate-800">
										{t('projects.dashboard.table_remaining_workload')}
									</TableHead>
									<TableHead className="text-center text-sm font-semibold text-slate-800">
										{t('projects.dashboard.table_progress')}
									</TableHead>
								</TableRow>
							</TableHeader>
							<TableBody>
								{annotators.length === 0 ? (
									<TableRow>
										<TableCell
											colSpan={4}
											className="py-10 text-center text-sm text-slate-400"
										>
											{t('projects.dashboard.no_annotators')}
										</TableCell>
									</TableRow>
								) : (
									annotators.map((annotator) => {
										const workloadPct = Math.round(annotator.workload * 100);
										const progressPct = Math.round(
											annotator.annotator_progress * 100
										);
										return (
											<TableRow key={annotator.id}>
												<TableCell className="pl-12 text-sm text-slate-800">
													{annotator.name}
												</TableCell>
												<TableCell className="text-right text-sm text-slate-800">
													{annotator.active_projects_count}
												</TableCell>
												<TableCell className="px-6">
													<div className="flex justify-center">
														<WorkloadGauge value={workloadPct} />
													</div>
												</TableCell>
												<TableCell className="px-6">
													<div className="flex flex-col gap-1">
														<span className="text-right text-xs font-semibold text-slate-800 tabular-nums">
															{progressPct}%
														</span>
														<div className="bg-brand-blue-100 h-3 w-full overflow-hidden rounded-full">
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
											</TableRow>
										);
									})
								)}
							</TableBody>
						</Table>
					</div>
				</section>
			</div>
		</AppLayout>
	);
}
