import { AnnotatorsTable } from '@/components/annotator/annotators-table';
import { type ProjectAnnotatorRowData } from '@/components/annotator/annotators-table';
import { CreateSubprojectStepper } from '@/components/sub-project/create-subproject-stepper';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import {
	Select,
	SelectContent,
	SelectItem,
	SelectTrigger,
	SelectValue,
} from '@/components/ui/select';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/react';
import { ChevronLeft, ChevronRight, Search } from 'lucide-react';
import { useMemo, useState } from 'react';

const STEPS = [
	{ label: 'Select Annotators' },
	{ label: 'Select Dataset Subset' },
	{ label: 'Configurations' },
];

const MOCK_PROJECT = { id: 1, name: 'Project New Nov_26' };

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
		username: '@ggiannakopulos',
		projects: 23,
		subprojects: 23,
		workload: 85,
		progress: 75,
	},
	{
		id: 3,
		initials: 'N',
		username: '@nellisavrani',
		projects: 12,
		subprojects: 4,
		workload: 30,
		progress: 75,
	},
	{
		id: 4,
		initials: 'G',
		username: '@ggiannakopulos',
		projects: 23,
		subprojects: 23,
		workload: 85,
		progress: 75,
	},
	{
		id: 5,
		initials: 'G',
		username: '@ggiannakopulos',
		projects: 23,
		subprojects: 23,
		workload: 85,
		progress: 75,
	},
];

interface Props {
	project?: { id: number; name: string };
	/** Available annotators — falls back to mock data */
	annotators?: ProjectAnnotatorRowData[];
}

export default function CreateSubproject({ project, annotators }: Props) {
	const displayProject = project ?? MOCK_PROJECT;
	const displayAnnotators = annotators ?? MOCK_ANNOTATORS;

	const [currentStep, setCurrentStep] = useState(0);
	const [selectedAnnotatorIds, setSelectedAnnotatorIds] = useState<Set<number>>(new Set());
	const [sortByName, setSortByName] = useState('');
	const [sortByWorkload, setSortByWorkload] = useState('');
	const [search, setSearch] = useState('');

	const breadcrumbs: BreadcrumbItem[] = [
		{ title: 'Projects', href: route('projects.index') },
		{ title: displayProject.name, href: route('projects.show', displayProject.id) },
		{
			title: 'Create Subproject',
			href: route('projects.subprojects.create', displayProject.id),
		},
	];

	const filteredAnnotators = useMemo(() => {
		let result = [...displayAnnotators];

		if (search.trim()) {
			const query = search.toLowerCase();
			result = result.filter((a) => a.username.toLowerCase().includes(query));
		}

		if (sortByName === 'asc') result.sort((a, b) => a.username.localeCompare(b.username));
		if (sortByName === 'desc') result.sort((a, b) => b.username.localeCompare(a.username));
		if (sortByWorkload === 'asc') result.sort((a, b) => a.workload - b.workload);
		if (sortByWorkload === 'desc') result.sort((a, b) => b.workload - a.workload);

		return result;
	}, [displayAnnotators, search, sortByName, sortByWorkload]);

	const allFilteredSelected =
		filteredAnnotators.length > 0 &&
		filteredAnnotators.every((a) => selectedAnnotatorIds.has(a.id));

	function handleSelectAll(checked: boolean) {
		setSelectedAnnotatorIds((prev) => {
			const next = new Set(prev);
			filteredAnnotators.forEach((a) => (checked ? next.add(a.id) : next.delete(a.id)));
			return next;
		});
	}

	function handleSelectionChange(id: number, checked: boolean) {
		setSelectedAnnotatorIds((prev) => {
			const next = new Set(prev);
			if (checked) {
				next.add(id);
			} else {
				next.delete(id);
			}
			return next;
		});
	}

	function handleNext() {
		if (currentStep < STEPS.length - 1) {
			setCurrentStep((s) => s + 1);
		}
		// TODO: submit on final step
	}

	return (
		<AppLayout breadcrumbs={breadcrumbs}>
			<Head title="Create Subproject" />
			<div className="flex flex-col gap-6 px-6 py-6">
				<h1 className="text-slate-800">Create Subproject</h1>

				<CreateSubprojectStepper currentStep={currentStep} steps={STEPS} />

				{/* Step content */}
				{currentStep === 0 && (
					<section aria-labelledby="step-heading" className="flex flex-col gap-5">
						<hgroup>
							<h2 id="step-heading" className="page-subtitle">
								Select Annotators
							</h2>
							<p className="text-sm font-semibold text-slate-800">
								{selectedAnnotatorIds.size} selected
							</p>
						</hgroup>

						{/* Filters row */}
						<div className="flex items-end gap-4">
							<div className="flex flex-col gap-1">
								<span className="text-sm font-medium text-slate-700">
									Sort by Name
								</span>
								<Select value={sortByName} onValueChange={setSortByName}>
									<SelectTrigger className="h-10 w-[180px] bg-white px-4">
										<SelectValue placeholder="Sort by Name" />
									</SelectTrigger>
									<SelectContent>
										<SelectItem value="asc">A → Z</SelectItem>
										<SelectItem value="desc">Z → A</SelectItem>
									</SelectContent>
								</Select>
							</div>

							<div className="flex flex-col gap-1">
								<span className="text-sm font-medium text-slate-700">
									Sort by Workload
								</span>
								<Select value={sortByWorkload} onValueChange={setSortByWorkload}>
									<SelectTrigger className="h-10 w-[180px] bg-white px-4">
										<SelectValue placeholder="Sort by Workload" />
									</SelectTrigger>
									<SelectContent>
										<SelectItem value="asc">Low → High</SelectItem>
										<SelectItem value="desc">High → Low</SelectItem>
									</SelectContent>
								</Select>
							</div>

							<div className="relative ml-auto">
								<Search
									className="pointer-events-none absolute top-1/2 right-3 size-4 -translate-y-1/2 text-slate-400"
									aria-hidden="true"
								/>
								<Input
									type="search"
									placeholder="Search Annotators…"
									value={search}
									onChange={(e) => setSearch(e.target.value)}
									className="w-[220px] pr-9 pl-4"
									aria-label="Search annotators"
								/>
							</div>
						</div>

						{/* Select all */}
						<label className="flex cursor-pointer items-center gap-2">
							<Checkbox
								checked={allFilteredSelected}
								onCheckedChange={handleSelectAll}
								aria-label="Select all annotators"
							/>
							<span className="text-sm text-slate-700">Select all</span>
						</label>

						<AnnotatorsTable
							mode="selectable"
							annotators={filteredAnnotators}
							selectedIds={selectedAnnotatorIds}
							onSelectionChange={handleSelectionChange}
						/>
					</section>
				)}

				{currentStep === 1 && (
					<section aria-labelledby="step-heading" className="flex flex-col gap-4">
						<h2 id="step-heading" className="page-subtitle">
							Select Dataset Subset
						</h2>
						<p className="text-slate-500">Dataset subset selection coming soon…</p>
					</section>
				)}

				{currentStep === 2 && (
					<section aria-labelledby="step-heading" className="flex flex-col gap-4">
						<h2 id="step-heading" className="page-subtitle">
							Configurations
						</h2>
						<p className="text-slate-500">Configuration options coming soon…</p>
					</section>
				)}

				{/* Action bar */}
				<div className="flex items-center justify-end gap-3 border-t border-slate-200 pt-4">
					<Button
						variant="outline"
						onClick={() => router.visit(route('projects.show', displayProject.id))}
					>
						Cancel
					</Button>
					<Button
						variant="outline"
						className="border-brand-yellow-400 text-brand-yellow-600 hover:bg-brand-yellow-50"
						isDisabled={currentStep === 0}
						onClick={() => setCurrentStep((s) => Math.max(0, s - 1))}
					>
						<ChevronLeft className="size-4" aria-hidden="true" />
						Back
					</Button>
					<Button
						className="hover:bg-brand-blue-800 bg-brand-blue-700 text-white"
						onClick={handleNext}
					>
						Next
						<ChevronRight className="size-4" aria-hidden="true" />
					</Button>
				</div>
			</div>
		</AppLayout>
	);
}
