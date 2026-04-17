import {
	SubProjectListItem,
	type SubProjectListItemData,
} from '@/components/sub-project/sub-project-list-item';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Download } from 'lucide-react';
import { useState } from 'react';

interface ExportTabProps {
	subProjects: SubProjectListItemData[];
}

export function ExportTab({ subProjects }: ExportTabProps) {
	const [selectedIds, setSelectedIds] = useState<Set<number>>(new Set());

	const allSelected = subProjects.length > 0 && selectedIds.size === subProjects.length;
	const selectedCount = selectedIds.size;

	const toggleItem = (id: number) => {
		setSelectedIds((prev) => {
			const next = new Set(prev);
			if (next.has(id)) {
				next.delete(id);
			} else {
				next.add(id);
			}
			return next;
		});
	};

	const handleSelectAll = (checked: boolean) => {
		setSelectedIds(checked ? new Set(subProjects.map((sp) => sp.id)) : new Set());
	};

	return (
		<div
			id="tabpanel-export"
			role="tabpanel"
			aria-labelledby="tab-export"
			className="flex flex-col gap-4"
		>
			{/* Header */}
			<div className="flex items-start justify-between">
				<div className="flex flex-col gap-1">
					<h2 className="text-xl font-medium text-slate-800">Export</h2>
					<p className="text-sm font-bold text-slate-800">{selectedCount} selected</p>
				</div>
				<Button
					disabled={selectedCount === 0}
					className="bg-brand-blue-700 hover:bg-brand-blue-800 h-10 font-semibold text-white disabled:opacity-50"
				>
					<Download className="size-4" aria-hidden="true" />
					Export Results (CSV)
				</Button>
			</div>

			{/* Select all */}
			<label className="flex cursor-pointer items-center gap-2">
				<Checkbox
					checked={allSelected}
					onCheckedChange={handleSelectAll}
					aria-label="Select all subprojects"
				/>
				<span className="text-sm text-slate-700">Select all</span>
			</label>

			{/* Subproject list */}
			<div className="flex flex-col gap-2">
				{subProjects.map((subProject) => {
					const isSelected = selectedIds.has(subProject.id);
					return (
						<div key={subProject.id} className="flex items-center gap-4">
							<label aria-label={`Select ${subProject.name}`}>
								<Checkbox
									checked={isSelected}
									onCheckedChange={() => toggleItem(subProject.id)}
								/>
							</label>
							<div className="min-w-0 flex-1">
								<SubProjectListItem
									subProject={subProject}
									showActions={false}
									className={
										isSelected
											? 'border-brand-blue-100 bg-brand-blue-50 border-4'
											: undefined
									}
								/>
							</div>
						</div>
					);
				})}
			</div>
		</div>
	);
}
