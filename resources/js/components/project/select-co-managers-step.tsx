import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import {
	Table,
	TableBody,
	TableCell,
	TableHead,
	TableHeader,
	TableRow,
} from '@/components/ui/table';
import { UserTableCell } from '@/components/project/user-table-cell';
import { useTranslations } from '@/hooks/use-translations';
import { cn } from '@/lib/utils';

export interface CoManagerCandidateRowData {
	id: number;
	initials: string;
	username: string;
	name: string;
	role: 'admin' | 'manager';
}

interface SelectCoManagersStepProps {
	candidates: CoManagerCandidateRowData[];
	selectedIds: Set<number>;
	inviteEmail: string;
	onSelectionChange: (id: number, checked: boolean) => void;
	onSelectAllChange: (ids: number[], checked: boolean) => void;
	onInviteEmailChange: (email: string) => void;
	onInvite: () => void;
}

const ROLE_BADGE_CLASSES: Record<CoManagerCandidateRowData['role'], string> = {
	admin: 'border-fuchsia-300 bg-fuchsia-50 text-fuchsia-600',
	manager: 'border-sky-300 bg-sky-50 text-sky-600',
};

export function SelectCoManagersStep({
	candidates,
	selectedIds,
	inviteEmail,
	onSelectionChange,
	onSelectAllChange,
	onInviteEmailChange,
	onInvite,
}: SelectCoManagersStepProps) {
	const { t, trans } = useTranslations();

	const allIds = candidates.map((c) => c.id);
	const allSelected = allIds.length > 0 && allIds.every((id) => selectedIds.has(id));

	return (
		<section aria-labelledby="step-co-managers-heading" className="flex flex-col gap-5">
			<hgroup>
				<h2 id="step-co-managers-heading" className="page-subtitle">
					{t('projects.select_co_managers.heading')}
				</h2>
				<p
					className="text-sm font-semibold text-slate-800"
					role="status"
					aria-live="polite"
				>
					{trans('projects.select_co_managers.selected_count', {
						count: selectedIds.size,
					})}
				</p>
			</hgroup>

			<div className="flex items-center justify-between gap-4">
				<label className="flex cursor-pointer items-center gap-2">
					<Checkbox
						checked={allSelected}
						onCheckedChange={(checked) => onSelectAllChange(allIds, checked)}
						aria-label={t('projects.select_co_managers.select_all')}
					/>
					<span className="text-sm text-slate-700">
						{t('projects.select_co_managers.select_all')}
					</span>
				</label>

				<div className="flex items-center gap-3">
					<Input
						type="email"
						value={inviteEmail}
						onChange={(e) => onInviteEmailChange(e.target.value)}
						placeholder={t('projects.select_co_managers.email_placeholder')}
						className="h-10 w-56"
						aria-label={t('projects.select_co_managers.email_placeholder')}
					/>
					<Button
						className="bg-brand-blue-700 hover:bg-brand-blue-800 h-10 px-4 font-semibold text-white"
						onClick={onInvite}
					>
						{t('projects.select_co_managers.invite_button')}
					</Button>
				</div>
			</div>

			<div className="overflow-hidden rounded-xl">
				<Table>
					<TableHeader>
						<TableRow className="bg-brand-blue-100 hover:bg-brand-blue-100 border-b border-slate-300">
							<TableHead className="w-12" />
							<TableHead className="text-sm font-semibold text-slate-800">
								{t('projects.select_co_managers.table_username')}
							</TableHead>
							<TableHead className="text-sm font-semibold text-slate-800">
								{t('projects.select_co_managers.table_name')}
							</TableHead>
							<TableHead className="text-sm font-semibold text-slate-800">
								{t('projects.select_co_managers.table_role')}
							</TableHead>
						</TableRow>
					</TableHeader>
					<TableBody>
						{candidates.map((candidate) => (
							<TableRow
								key={candidate.id}
								className="hover:bg-brand-blue-50 h-[76px] cursor-pointer border-b border-slate-300 bg-white"
								onClick={() =>
									onSelectionChange(candidate.id, !selectedIds.has(candidate.id))
								}
							>
								<TableCell className="w-12 pl-4">
									<Checkbox
										checked={selectedIds.has(candidate.id)}
										onCheckedChange={(checked) =>
											onSelectionChange(candidate.id, checked)
										}
										aria-label={`Select ${candidate.username}`}
										onClick={(e) => e.stopPropagation()}
									/>
								</TableCell>
								<TableCell className="pl-4">
									<UserTableCell
										initials={candidate.initials}
										username={candidate.username}
										showMessageButton={false}
									/>
								</TableCell>
								<TableCell className="text-sm font-medium text-slate-800">
									{candidate.name}
								</TableCell>
								<TableCell>
									<span
										className={cn(
											'inline-flex items-center rounded border px-2.5 py-0.5 text-xs font-semibold',
											ROLE_BADGE_CLASSES[candidate.role]
										)}
									>
										{t(`projects.select_co_managers.role_${candidate.role}`)}
									</span>
								</TableCell>
							</TableRow>
						))}
					</TableBody>
				</Table>
			</div>
		</section>
	);
}
