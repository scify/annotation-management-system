import { useTranslations } from '@/hooks/use-translations';
import { cn } from '@/lib/utils';
import { ArrowUpDown, ChevronDown } from 'lucide-react';
import { useEffect, useRef, useState } from 'react';

export type SortState = {
	progress: '' | 'ascending' | 'descending';
	dateCreated: '' | 'recent_first' | 'older_first';
	dueDate: '' | 'recent_first' | 'older_first';
};

export const DEFAULT_SORT_STATE: SortState = {
	progress: '',
	dateCreated: '',
	dueDate: '',
};

interface ProjectSortPanelProps {
	state: SortState;
	onChange: (state: SortState) => void;
	hasActiveSort: boolean;
	onClear: () => void;
}

function RadioCircle({ checked }: { checked: boolean }) {
	return (
		<span
			aria-hidden="true"
			className={cn(
				'flex size-4 shrink-0 items-center justify-center rounded-full border-2 transition-colors',
				checked ? 'border-brand-blue-700 bg-brand-blue-700' : 'border-slate-400 bg-white'
			)}
		>
			{checked && <span className="size-2 rounded-full bg-white" />}
		</span>
	);
}

interface RadioItemProps {
	groupName: string;
	value: string;
	checked: boolean;
	label: string;
	onChange: () => void;
}

function RadioItem({ groupName, value, checked, label, onChange }: RadioItemProps) {
	return (
		<label className="flex min-h-[40px] cursor-pointer items-center gap-2 rounded px-3 hover:bg-slate-50">
			<input
				type="radio"
				name={groupName}
				value={value}
				checked={checked}
				onChange={onChange}
				className="sr-only"
			/>
			<RadioCircle checked={checked} />
			<span
				className={cn(
					'flex-1 text-base text-slate-800',
					checked ? 'font-semibold' : 'font-normal'
				)}
			>
				{label}
			</span>
		</label>
	);
}

export function ProjectSortPanel({
	state,
	onChange,
	hasActiveSort,
	onClear,
}: ProjectSortPanelProps) {
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

	const activeCount = Object.values(state).filter((v) => v !== '').length;

	const triggerLabel =
		activeCount > 0
			? `${t('projects.sort_button')} (${activeCount})`
			: t('projects.sort_button');

	return (
		<div ref={wrapperRef} className="relative">
			<button
				type="button"
				onClick={() => setIsOpen((o) => !o)}
				aria-expanded={isOpen}
				aria-haspopup="true"
				className={cn(
					'flex h-10 w-full items-center justify-between rounded-lg border bg-white px-4 text-sm transition-colors hover:bg-slate-50',
					isOpen || hasActiveSort ? 'border-brand-blue-500' : 'border-slate-200',
					hasActiveSort ? 'font-semibold text-slate-800' : 'font-medium text-slate-800'
				)}
			>
				<span className="flex items-center gap-2">
					<ArrowUpDown
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
				disabled={!hasActiveSort}
				className="enabled:bg-brand-yellow-400 enabled:text-brand-blue-700 enabled:hover:bg-brand-yellow-300 mt-3 h-10 w-full rounded-lg text-sm font-semibold transition-colors hover:cursor-pointer disabled:cursor-not-allowed disabled:bg-slate-200 disabled:text-white"
			>
				{t('projects.filter_clear')}
			</button>

			{isOpen && (
				<div className="absolute top-full left-0 z-50 mt-1 w-[260px] overflow-hidden rounded-lg border border-slate-200 bg-white shadow-lg">
					{/* Progress */}
					<fieldset className="border-b border-slate-200 px-4 py-4">
						<legend className="field-legend mb-2 text-base font-semibold text-slate-800">
							{t('projects.sort_progress_section')}
						</legend>
						<RadioItem
							groupName="sort-progress"
							value="ascending"
							checked={state.progress === 'ascending'}
							label={t('projects.sort_ascending')}
							onChange={() => onChange({ ...state, progress: 'ascending' })}
						/>
						<RadioItem
							groupName="sort-progress"
							value="descending"
							checked={state.progress === 'descending'}
							label={t('projects.sort_descending')}
							onChange={() => onChange({ ...state, progress: 'descending' })}
						/>
						<RadioItem
							groupName="sort-progress"
							value=""
							checked={state.progress === ''}
							label={t('projects.sort_not_selected')}
							onChange={() => onChange({ ...state, progress: '' })}
						/>
					</fieldset>

					{/* Date Created */}
					<fieldset className="border-b border-slate-200 px-4 py-4">
						<legend className="field-legend mb-1 text-base font-semibold text-slate-800">
							{t('projects.sort_date_created_section')}
						</legend>
						<RadioItem
							groupName="sort-date-created"
							value="recent_first"
							checked={state.dateCreated === 'recent_first'}
							label={t('projects.sort_recent_first')}
							onChange={() => onChange({ ...state, dateCreated: 'recent_first' })}
						/>
						<RadioItem
							groupName="sort-date-created"
							value="older_first"
							checked={state.dateCreated === 'older_first'}
							label={t('projects.sort_older_first')}
							onChange={() => onChange({ ...state, dateCreated: 'older_first' })}
						/>
						<RadioItem
							groupName="sort-date-created"
							value=""
							checked={state.dateCreated === ''}
							label={t('projects.sort_not_selected')}
							onChange={() => onChange({ ...state, dateCreated: '' })}
						/>
					</fieldset>

					{/* Due Date */}
					<fieldset className="px-4 py-4">
						<legend className="field-legend mb-1 text-base font-semibold text-slate-800">
							{t('projects.sort_due_date_section')}
						</legend>
						<RadioItem
							groupName="sort-due-date"
							value="recent_first"
							checked={state.dueDate === 'recent_first'}
							label={t('projects.sort_recent_first')}
							onChange={() => onChange({ ...state, dueDate: 'recent_first' })}
						/>
						<RadioItem
							groupName="sort-due-date"
							value="older_first"
							checked={state.dueDate === 'older_first'}
							label={t('projects.sort_older_first')}
							onChange={() => onChange({ ...state, dueDate: 'older_first' })}
						/>
						<RadioItem
							groupName="sort-due-date"
							value=""
							checked={state.dueDate === ''}
							label={t('projects.sort_not_selected')}
							onChange={() => onChange({ ...state, dueDate: '' })}
						/>
					</fieldset>
				</div>
			)}
		</div>
	);
}
