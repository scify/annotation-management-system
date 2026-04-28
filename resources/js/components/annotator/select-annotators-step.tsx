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
import { useTranslations } from '@/hooks/use-translations';
import { Search } from 'lucide-react';
import { useMemo, useState } from 'react';

interface SelectAnnotatorsStepProps {
	annotators: ProjectAnnotatorRowData[];
	selectedIds: Set<number>;
	onSelectionChange: (id: number, checked: boolean) => void;
	onSelectAllChange: (ids: number[], checked: boolean) => void;
	/** @default 'sub-projects' */
	translationNamespace?: 'sub-projects' | 'projects';
}

export function SelectAnnotatorsStep({
	annotators,
	selectedIds,
	onSelectionChange,
	onSelectAllChange,
	translationNamespace = 'sub-projects',
}: SelectAnnotatorsStepProps) {
	const { t, trans } = useTranslations();
	const [sortByName, setSortByName] = useState('');
	const [sortByWorkload, setSortByWorkload] = useState('');
	const [search, setSearch] = useState('');

	const ns = `${translationNamespace}.select_annotators` as const;

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
					{t(`${ns}.heading`)}
				</h2>
				<p className="text-sm font-semibold text-slate-800">
					{trans(`${ns}.selected_count`, {
						count: selectedIds.size,
					})}
				</p>
			</hgroup>

			{/* Filters row */}
			<div className="flex items-end gap-4">
				<div className="flex flex-col gap-1">
					<span className="text-sm font-medium text-slate-700">
						{t(`${ns}.sort_by_name`)}
					</span>
					<Select
						aria-label={t(`${ns}.sort_by_name`)}
						value={sortByName}
						onValueChange={setSortByName}
					>
						<SelectTrigger className="h-10 w-[180px] bg-white px-4">
							<SelectValue placeholder={t(`${ns}.sort_by_name`)} />
						</SelectTrigger>
						<SelectContent>
							<SelectItem value="asc">{t(`${ns}.sort_asc_name`)}</SelectItem>
							<SelectItem value="desc">{t(`${ns}.sort_desc_name`)}</SelectItem>
						</SelectContent>
					</Select>
				</div>

				<div className="flex flex-col gap-1">
					<span className="text-sm font-medium text-slate-700">
						{t(`${ns}.sort_by_workload`)}
					</span>
					<Select
						aria-label={t(`${ns}.sort_by_workload`)}
						value={sortByWorkload}
						onValueChange={setSortByWorkload}
					>
						<SelectTrigger className="h-10 w-[180px] bg-white px-4">
							<SelectValue placeholder={t(`${ns}.sort_by_workload`)} />
						</SelectTrigger>
						<SelectContent>
							<SelectItem value="asc">{t(`${ns}.sort_asc_workload`)}</SelectItem>
							<SelectItem value="desc">{t(`${ns}.sort_desc_workload`)}</SelectItem>
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
						placeholder={t(`${ns}.search_placeholder`)}
						value={search}
						onChange={(e) => setSearch(e.target.value)}
						className="w-[220px] pr-9 pl-4"
						aria-label={t(`${ns}.search_placeholder`)}
					/>
				</div>
			</div>

			{/* Select all */}
			<label className="flex cursor-pointer items-center gap-2">
				<Checkbox
					checked={allFilteredSelected}
					onCheckedChange={handleSelectAll}
					aria-label={t(`${ns}.select_all`)}
				/>
				<span className="text-sm text-slate-700">{t(`${ns}.select_all`)}</span>
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
