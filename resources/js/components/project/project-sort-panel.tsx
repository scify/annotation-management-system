import { useTranslations } from '@/hooks/use-translations';
import { cn } from '@/lib/utils';
import { ArrowUpDown, ChevronDown } from 'lucide-react';
import { useEffect, useRef, useState } from 'react';

export type SortOption = 'name_asc' | 'name_desc' | 'progress_high' | 'progress_low' | null;

interface ProjectSortPanelProps {
	value: SortOption;
	onChange: (value: SortOption) => void;
}

export function ProjectSortPanel({ value, onChange }: ProjectSortPanelProps) {
	const { t } = useTranslations();
	const [isOpen, setIsOpen] = useState(false);
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

	const options: { key: NonNullable<SortOption>; label: string }[] = [
		{ key: 'name_asc', label: t('projects.sort_name_asc') },
		{ key: 'name_desc', label: t('projects.sort_name_desc') },
		{ key: 'progress_high', label: t('projects.sort_progress_high') },
		{ key: 'progress_low', label: t('projects.sort_progress_low') },
	];

	const select = (key: NonNullable<SortOption>) => {
		onChange(value === key ? null : key);
		setIsOpen(false);
	};

	return (
		<div ref={wrapperRef} className="relative">
			<button
				type="button"
				onClick={() => setIsOpen((o) => !o)}
				aria-expanded={isOpen}
				aria-haspopup="listbox"
				className={cn(
					'flex h-10 w-full items-center justify-between rounded-lg border bg-white px-4 text-sm font-medium text-slate-800 transition-colors hover:bg-slate-50',
					isOpen || value ? 'border-brand-blue-500' : 'border-slate-200'
				)}
			>
				<span className="flex items-center gap-2">
					<ArrowUpDown
						className="size-[18px] shrink-0 text-slate-600"
						aria-hidden="true"
					/>
					{t('projects.sort_button')}
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
				onClick={() => onChange(null)}
				disabled={!value}
				className="enabled:bg-brand-yellow-400 enabled:text-brand-blue-700 enabled:hover:bg-brand-yellow-300 mt-3 h-10 w-full rounded-lg text-sm font-semibold transition-colors disabled:cursor-not-allowed disabled:bg-slate-200 disabled:text-white"
			>
				{t('projects.filter_clear')}
			</button>

			{isOpen && (
				<div
					role="listbox"
					aria-label={t('projects.sort_button')}
					className="absolute top-full left-0 z-50 mt-1 w-[220px] overflow-hidden rounded-lg border border-slate-200 bg-white shadow-lg"
				>
					{options.map((option) => (
						<button
							key={option.key}
							type="button"
							role="option"
							aria-selected={value === option.key}
							onClick={() => select(option.key)}
							className={cn(
								'flex min-h-[40px] w-full items-center px-4 py-2 text-left text-base transition-colors hover:bg-slate-50',
								value === option.key
									? 'text-brand-blue-700 font-semibold'
									: 'font-normal text-slate-800'
							)}
						>
							{option.label}
						</button>
					))}
				</div>
			)}
		</div>
	);
}
