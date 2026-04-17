import { AnnotatorsTable } from '@/components/annotator/annotators-table';
import { type ProjectAnnotatorRowData } from '@/components/annotator/annotators-table';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import {
	Select,
	SelectContent,
	SelectItem,
	SelectTrigger,
	SelectValue,
} from '@/components/ui/select';
import { Search } from 'lucide-react';
import { useMemo, useState } from 'react';

interface SelectAnnotatorsStepProps {
	annotators: ProjectAnnotatorRowData[];
	selectedIds: Set<number>;
	onSelectionChange: (id: number, checked: boolean) => void;
	onSelectAllChange: (ids: number[], checked: boolean) => void;
}

export function SelectAnnotatorsStep({
	annotators,
	selectedIds,
	onSelectionChange,
	onSelectAllChange,
}: SelectAnnotatorsStepProps) {
	const [sortByName, setSortByName] = useState('');
	const [sortByWorkload, setSortByWorkload] = useState('');
	const [search, setSearch] = useState('');

	const filteredAnnotators = useMemo(() => {
		let result = [...annotators];

		if (search.trim()) {
			const query = search.toLowerCase();
			result = result.filter((a) => a.username.toLowerCase().includes(query));
		}

		if (sortByName === 'asc') result.sort((a, b) => a.username.localeCompare(b.username));
		if (sortByName === 'desc') result.sort((a, b) => b.username.localeCompare(a.username));
		if (sortByWorkload === 'asc') result.sort((a, b) => a.workload - b.workload);
		if (sortByWorkload === 'desc') result.sort((a, b) => b.workload - a.workload);

		return result;
	}, [annotators, search, sortByName, sortByWorkload]);

	const allFilteredSelected =
		filteredAnnotators.length > 0 && filteredAnnotators.every((a) => selectedIds.has(a.id));

	function handleSelectAll(checked: boolean) {
		onSelectAllChange(
			filteredAnnotators.map((a) => a.id),
			checked
		);
	}

	return (
		<section aria-labelledby="step-heading" className="flex flex-col gap-5">
			<hgroup>
				<h2 id="step-heading" className="page-subtitle">
					Select Annotators
				</h2>
				<p className="text-sm font-semibold text-slate-800">{selectedIds.size} selected</p>
			</hgroup>

			{/* Filters row */}
			<div className="flex items-end gap-4">
				<div className="flex flex-col gap-1">
					<span className="text-sm font-medium text-slate-700">Sort by Name</span>
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
					<span className="text-sm font-medium text-slate-700">Sort by Workload</span>
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
				selectedIds={selectedIds}
				onSelectionChange={onSelectionChange}
			/>
		</section>
	);
}
