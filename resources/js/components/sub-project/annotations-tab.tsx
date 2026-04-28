import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import {
	Select,
	SelectContent,
	SelectItem,
	SelectTrigger,
	SelectValue,
} from '@/components/ui/select';
import {
	Table,
	TableBody,
	TableCell,
	TableHead,
	TableHeader,
	TableRow,
} from '@/components/ui/table';
import { useTranslations } from '@/hooks/use-translations';
import { cn } from '@/lib/utils';
import { ChevronDown, ChevronUp, Search } from 'lucide-react';
import { Fragment, useMemo, useState } from 'react';

// ── Types ─────────────────────────────────────────────────────────────────────

export type AgreementLevel = 'high' | 'medium' | 'low';
export type UserRole = 'annotator' | 'manager';

export interface AnnotationEntry {
	id: number;
	annotation: string;
	assignedTo: { username: string; role: UserRole };
	annotatedBy: { username: string; role: UserRole };
	timestamp: string;
	confidence: AgreementLevel;
}

export interface InstanceAnnotationRow {
	instanceId: number;
	annotationProgress: { completed: number; total: number };
	agreement: AgreementLevel;
	annotations: AnnotationEntry[];
}

// ── Agreement level ordering for sorting ──────────────────────────────────────

const AGREEMENT_ORDER: Record<AgreementLevel, number> = { low: 0, medium: 1, high: 2 };

// ── Badge helpers ─────────────────────────────────────────────────────────────

function AgreementBadge({ level }: { level: AgreementLevel }) {
	const { t } = useTranslations();
	const styles: Record<AgreementLevel, string> = {
		high: 'bg-green-50 border-green-500 text-green-600',
		medium: 'bg-yellow-50 border-yellow-400 text-yellow-600',
		low: 'bg-rose-100 border-rose-400 text-rose-600',
	};
	return (
		<span
			className={cn(
				'inline-flex h-[22px] w-[100px] items-center justify-center rounded border px-2 text-xs font-semibold',
				styles[level]
			)}
		>
			{t(`sub-projects.annotations.agreement_${level}`)}
		</span>
	);
}

function RoleBadge({ role }: { role: UserRole }) {
	const { t } = useTranslations();
	const styles: Record<UserRole, string> = {
		annotator: 'bg-brand-blue-50 border-brand-blue-200 text-brand-blue-600',
		manager: 'bg-sky-50 border-sky-300 text-sky-600',
	};
	return (
		<span
			className={cn(
				'inline-flex h-[22px] w-[100px] items-center justify-center rounded border px-2 text-xs font-semibold',
				styles[role]
			)}
		>
			{t(`sub-projects.annotations.role_${role}`)}
		</span>
	);
}

// ── Main component ────────────────────────────────────────────────────────────

interface AnnotationsTabProps {
	annotations: InstanceAnnotationRow[];
}

export function AnnotationsTab({ annotations }: AnnotationsTabProps) {
	const { t, trans } = useTranslations();

	const [expandedIds, setExpandedIds] = useState<Set<number>>(new Set());
	const [sortByProgress, setSortByProgress] = useState('');
	const [sortByAgreement, setSortByAgreement] = useState('');
	const [search, setSearch] = useState('');

	function toggleRow(id: number) {
		setExpandedIds((prev) => {
			const next = new Set(prev);
			if (next.has(id)) {
				next.delete(id);
			} else {
				next.add(id);
			}
			return next;
		});
	}

	const filteredAndSorted = useMemo(() => {
		let rows = [...annotations];

		if (search.trim()) {
			rows = rows.filter((r) => String(r.instanceId).includes(search.trim()));
		}

		if (sortByProgress === 'asc') {
			rows.sort(
				(a, b) =>
					a.annotationProgress.completed / a.annotationProgress.total -
					b.annotationProgress.completed / b.annotationProgress.total
			);
		} else if (sortByProgress === 'desc') {
			rows.sort(
				(a, b) =>
					b.annotationProgress.completed / b.annotationProgress.total -
					a.annotationProgress.completed / a.annotationProgress.total
			);
		}

		if (sortByAgreement === 'asc') {
			rows.sort((a, b) => AGREEMENT_ORDER[a.agreement] - AGREEMENT_ORDER[b.agreement]);
		} else if (sortByAgreement === 'desc') {
			rows.sort((a, b) => AGREEMENT_ORDER[b.agreement] - AGREEMENT_ORDER[a.agreement]);
		}

		return rows;
	}, [annotations, search, sortByProgress, sortByAgreement]);

	return (
		<section aria-labelledby="annotations-heading" className="flex flex-col gap-6">
			{/* ── Section heading + controls ────────────────────────── */}
			<div className="flex flex-col gap-6">
				<h2 id="annotations-heading" className="text-xl font-medium text-slate-800">
					{t('sub-projects.annotations.heading')}
				</h2>

				<div className="flex items-center justify-between gap-3">
					{/* Sort controls */}
					<div className="flex gap-3">
						<Select
							aria-label={t('sub-projects.annotations.sort_by_progress')}
							value={sortByProgress}
							onValueChange={setSortByProgress}
						>
							<SelectTrigger className="h-10 w-[200px] bg-white px-4">
								<SelectValue
									placeholder={t('sub-projects.annotations.sort_by_progress')}
								/>
							</SelectTrigger>
							<SelectContent>
								<SelectItem value="asc">
									{t('sub-projects.annotations.sort_asc')}
								</SelectItem>
								<SelectItem value="desc">
									{t('sub-projects.annotations.sort_desc')}
								</SelectItem>
							</SelectContent>
						</Select>

						<Select
							aria-label={t('sub-projects.annotations.sort_by_agreement')}
							value={sortByAgreement}
							onValueChange={setSortByAgreement}
						>
							<SelectTrigger className="h-10 w-[200px] bg-white px-4">
								<SelectValue
									placeholder={t('sub-projects.annotations.sort_by_agreement')}
								/>
							</SelectTrigger>
							<SelectContent>
								<SelectItem value="asc">
									{t('sub-projects.annotations.sort_asc')}
								</SelectItem>
								<SelectItem value="desc">
									{t('sub-projects.annotations.sort_desc')}
								</SelectItem>
							</SelectContent>
						</Select>
					</div>

					{/* Search */}
					<div className="relative">
						<Search
							className="pointer-events-none absolute top-1/2 right-3 size-4 -translate-y-1/2 text-slate-400"
							aria-hidden="true"
						/>
						<Input
							type="search"
							placeholder={t('sub-projects.annotations.search_placeholder')}
							value={search}
							onChange={(e) => setSearch(e.target.value)}
							aria-label={t('sub-projects.annotations.search_placeholder')}
							className="w-[294px] pr-9 pl-4"
						/>
					</div>
				</div>
			</div>

			{/* ── Table ─────────────────────────────────────────────── */}
			<div className="overflow-hidden rounded-xl">
				<Table>
					<TableHeader>
						<TableRow className="bg-brand-blue-100 hover:bg-brand-blue-100 border-b border-slate-300">
							<TableHead className="w-[120px] text-center text-sm font-semibold text-slate-800">
								{t('sub-projects.annotations.col_instance')}
							</TableHead>
							<TableHead className="text-center text-sm font-semibold text-slate-800">
								{t('sub-projects.annotations.col_progress')}
							</TableHead>
							<TableHead className="text-center text-sm font-semibold text-slate-800">
								{t('sub-projects.annotations.col_agreement')}
							</TableHead>
							<TableHead className="w-[279px] text-center text-sm font-semibold text-slate-800">
								{t('sub-projects.annotations.col_action')}
							</TableHead>
							{/* Expand chevron column — no heading */}
							<TableHead className="w-[55px]" />
						</TableRow>
					</TableHeader>

					<TableBody>
						{filteredAndSorted.map((row) => {
							const isExpanded = expandedIds.has(row.instanceId);
							const rowBg = isExpanded ? 'bg-white' : 'bg-brand-blue-50';

							return (
								<Fragment key={row.instanceId}>
									{/* ── Instance summary row ─────── */}
									<TableRow className={cn(rowBg, 'border-b border-slate-300')}>
										<TableCell className="h-14 text-center text-base font-medium text-slate-800">
											#{row.instanceId}
										</TableCell>
										<TableCell className="h-14 text-center text-base font-medium text-slate-800">
											<span className="font-bold">
												{row.annotationProgress.completed}
											</span>
											<span className="font-normal">
												{' '}
												{trans('sub-projects.annotations.progress_label', {
													completed: '',
													total: row.annotationProgress.total,
												}).trim()}
											</span>
										</TableCell>
										<TableCell className="h-14 text-center">
											<AgreementBadge level={row.agreement} />
										</TableCell>
										<TableCell className="h-14 text-center">
											<Button
												size="sm"
												className="bg-brand-blue-700 hover:bg-brand-blue-800 h-[30px] text-white"
											>
												{t('sub-projects.annotations.go_to_instance')}
											</Button>
										</TableCell>
										<TableCell className="h-14 text-center">
											<button
												type="button"
												onClick={() => toggleRow(row.instanceId)}
												aria-expanded={isExpanded}
												aria-label={`${isExpanded ? 'Collapse' : 'Expand'} instance #${row.instanceId}`}
												className={cn(
													'flex size-10 cursor-pointer items-center justify-center rounded-lg transition-colors',
													isExpanded
														? 'hover:bg-slate-100'
														: 'bg-brand-blue-50 hover:bg-brand-blue-100'
												)}
											>
												{isExpanded ? (
													<ChevronUp
														className="size-5 text-slate-600"
														aria-hidden="true"
													/>
												) : (
													<ChevronDown
														className="size-5 text-slate-600"
														aria-hidden="true"
													/>
												)}
											</button>
										</TableCell>
									</TableRow>

									{/* ── Expanded annotation rows ─── */}
									{isExpanded && (
										<TableRow className="bg-white">
											{/*
											 * Single spanning cell isolates inner layout from the
											 * outer table's column-width calculations — prevents
											 * columns shifting when inner content is wider/narrower.
											 */}
											<TableCell colSpan={5} className="p-0">
												{/* Sub-header */}
												<div className="flex h-10 items-center border-b border-slate-200">
													<span className="w-1/5 shrink-0 px-2 text-center text-sm font-semibold text-slate-400">
														{t(
															'sub-projects.annotations.col_annotation'
														)}
													</span>
													<span className="w-1/5 shrink-0 px-2 text-sm font-semibold text-slate-400">
														{t(
															'sub-projects.annotations.col_assigned_to'
														)}
													</span>
													<span className="w-1/5 shrink-0 px-2 text-sm font-semibold text-slate-400">
														{t(
															'sub-projects.annotations.col_annotated_by'
														)}
													</span>
													<span className="w-1/5 shrink-0 px-2 text-sm font-semibold text-slate-400">
														{t(
															'sub-projects.annotations.col_timestamp'
														)}
													</span>
													<span className="w-1/5 shrink-0 px-2 text-center text-sm font-semibold text-slate-400">
														{t(
															'sub-projects.annotations.col_confidence'
														)}
													</span>
												</div>

												{/* One row per annotation entry */}
												{row.annotations.map((entry) => (
													<div
														key={entry.id}
														className="flex min-h-[90px] items-start border-b border-slate-200 pt-4 pb-4"
													>
														<span className="w-1/5 shrink-0 px-2 text-center text-base font-medium text-slate-800">
															{entry.annotation}
														</span>
														<div className="flex w-1/5 shrink-0 flex-col gap-2 px-2">
															<span className="text-base font-medium whitespace-nowrap text-slate-800">
																{entry.assignedTo.username}
															</span>
															<RoleBadge
																role={entry.assignedTo.role}
															/>
														</div>
														<div className="flex w-1/5 shrink-0 flex-col gap-2 px-2">
															<span className="text-base font-medium whitespace-nowrap text-slate-800">
																{entry.annotatedBy.username}
															</span>
															<RoleBadge
																role={entry.annotatedBy.role}
															/>
														</div>
														<span className="w-1/5 shrink-0 px-2 text-base font-medium text-slate-800">
															{entry.timestamp}
														</span>
														<span className="flex w-1/5 shrink-0 justify-center px-2">
															<AgreementBadge
																level={entry.confidence}
															/>
														</span>
													</div>
												))}
											</TableCell>
										</TableRow>
									)}
								</Fragment>
							);
						})}
					</TableBody>
				</Table>
			</div>
		</section>
	);
}
