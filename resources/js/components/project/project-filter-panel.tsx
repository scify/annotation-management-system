import { Checkbox } from '@/components/ui/checkbox';
import { useTranslations } from '@/hooks/use-translations';
import { cn } from '@/lib/utils';
import { ChevronDown, Search, SlidersHorizontal } from 'lucide-react';
import { useEffect, useRef, useState } from 'react';

export type FilterState = {
	tasks: string[];
	datasets: string[];
	states: string[];
};

export type FilterSectionKey = keyof FilterState;

interface FilterSection {
	key: FilterSectionKey;
	label: string;
	items: string[];
	searchable: boolean;
}

interface ProjectFilterPanelProps {
	sections: FilterSection[];
	selected: FilterState;
	onToggle: (section: FilterSectionKey, value: string) => void;
	onClear: () => void;
	hasActiveFilters: boolean;
}

export function ProjectFilterPanel({
	sections,
	selected,
	onToggle,
	onClear,
	hasActiveFilters,
}: ProjectFilterPanelProps) {
	const { t } = useTranslations();
	const [isOpen, setIsOpen] = useState(false);
	const [searches, setSearches] = useState<Partial<Record<FilterSectionKey, string>>>({});
	const wrapperRef = useRef<HTMLDivElement>(null);

	useEffect(() => {
		const handleOutsideClick = (e: MouseEvent) => {
			if (wrapperRef.current && !wrapperRef.current.contains(e.target as Node)) {
				setIsOpen(false);
			}
		};
		document.addEventListener('mousedown', handleOutsideClick);
		return () => document.removeEventListener('mousedown', handleOutsideClick);
	}, []);

	const updateSearch = (key: FilterSectionKey, value: string) =>
		setSearches((prev) => ({ ...prev, [key]: value }));

	const visibleItems = (section: FilterSection) => {
		const q = searches[section.key]?.toLowerCase() ?? '';
		return q ? section.items.filter((item) => item.toLowerCase().includes(q)) : section.items;
	};

	const activeCount = Object.values(selected).reduce((sum, arr) => sum + arr.length, 0);
	const triggerLabel =
		activeCount > 0 ? `${t('projects.filter')} (${activeCount})` : t('projects.filter');

	return (
		<div ref={wrapperRef} className="relative">
			<button
				type="button"
				onClick={() => setIsOpen((o) => !o)}
				aria-expanded={isOpen}
				aria-haspopup="true"
				className={cn(
					'flex h-10 w-full items-center justify-between rounded-lg border bg-white px-4 text-sm transition-colors hover:bg-slate-50',
					isOpen || hasActiveFilters ? 'border-brand-blue-500' : 'border-slate-200',
					hasActiveFilters ? 'font-semibold text-slate-800' : 'font-medium text-slate-800'
				)}
			>
				<span className="flex items-center gap-2">
					<SlidersHorizontal
						className="size-[18px] shrink-0 text-slate-600"
						aria-hidden="true"
					/>
					{triggerLabel}
				</span>
				<ChevronDown
					className={cn(
						'size-4 text-slate-500 transition-transform duration-200',
						isOpen && 'rotate-180'
					)}
					aria-hidden="true"
				/>
			</button>

			<button
				type="button"
				onClick={onClear}
				disabled={!hasActiveFilters}
				className="enabled:bg-brand-yellow-400 enabled:text-brand-blue-700 enabled:hover:bg-brand-yellow-300 mt-3 h-10 w-full rounded-lg text-sm font-semibold transition-colors hover:cursor-pointer disabled:cursor-not-allowed disabled:bg-slate-200 disabled:text-white"
			>
				{t('projects.filter_clear')}
			</button>

			{isOpen && (
				<div className="absolute top-full left-0 z-50 mt-1 w-[400px] overflow-hidden rounded-lg border border-slate-200 bg-white shadow-lg">
					{sections.map((section, idx) => (
						<div
							key={section.key}
							className={cn(
								'flex flex-col gap-1 px-4 py-4',
								idx < sections.length - 1 && 'border-b border-slate-200'
							)}
						>
							<p className="pb-1 text-base font-semibold text-slate-800">
								{section.label}
							</p>

							{section.searchable && (
								<div className="mb-1 flex h-[46px] items-center gap-2 border-b border-slate-300 px-2">
									<input
										type="text"
										placeholder={t('projects.filter_search')}
										value={searches[section.key] ?? ''}
										onChange={(e) => updateSearch(section.key, e.target.value)}
										className="min-w-0 flex-1 bg-transparent text-base text-slate-800 outline-none placeholder:text-slate-400"
										aria-label={`${t('projects.filter_search')} ${section.label}`}
									/>
									<Search
										className="size-4 shrink-0 text-slate-400"
										aria-hidden="true"
									/>
								</div>
							)}

							<div role="group" aria-label={section.label}>
								{visibleItems(section).map((item) => {
									const checked = selected[section.key].includes(item);
									const id = `filter-${section.key}-${item}`;
									return (
										<div
											key={item}
											className="flex min-h-[40px] items-center gap-2 rounded px-2 py-1.5 hover:bg-slate-50"
										>
											<Checkbox
												id={id}
												checked={checked}
												onCheckedChange={() => onToggle(section.key, item)}
											/>
											<label
												htmlFor={id}
												className={cn(
													'min-w-0 flex-1 cursor-pointer truncate text-base text-slate-800 select-none',
													checked ? 'font-semibold' : 'font-normal'
												)}
											>
												{item}
											</label>
										</div>
									);
								})}
							</div>
						</div>
					))}
				</div>
			)}
		</div>
	);
}
