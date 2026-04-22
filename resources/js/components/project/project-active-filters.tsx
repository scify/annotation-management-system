import { X } from 'lucide-react';

export interface ActiveFilterTag {
	id: string;
	label: string;
	value: string;
	onRemove: () => void;
}

interface ProjectActiveFiltersProps {
	tags: ActiveFilterTag[];
}

export function ProjectActiveFilters({ tags }: ProjectActiveFiltersProps) {
	if (tags.length === 0) return null;

	return (
		<div className="flex flex-wrap gap-2" role="list" aria-label="Active filters">
			{tags.map((tag) => (
				<span
					key={tag.id}
					role="listitem"
					className="bg-brand-blue-100 flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-sm text-black"
				>
					<span>{tag.label}:</span>
					<span className="max-w-120 truncate font-bold">{tag.value}</span>
					<button
						type="button"
						onClick={tag.onRemove}
						aria-label={`Remove ${tag.label} filter`}
						className="hover:bg-brand-blue-200 ml-0.5 cursor-pointer rounded-full p-0.5 text-slate-500 transition-colors hover:text-black"
					>
						<X className="size-3.5" aria-hidden="true" />
					</button>
				</span>
			))}
		</div>
	);
}
