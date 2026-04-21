import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import {
	Table,
	TableBody,
	TableCell,
	TableHead,
	TableHeader,
	TableRow,
} from '@/components/ui/table';
import { ProjectDialog } from '@/components/project/project-dialog';
import { UserTableCell } from '@/components/project/user-table-cell';
import { useTranslations } from '@/hooks/use-translations';
import { Check, CircleStar, LogOut, Mail, Send, TriangleAlert, X } from 'lucide-react';
import { useState } from 'react';
import { toast } from 'sonner';

export interface ProjectManagerRowData {
	id: number;
	initials: string;
	username: string;
	email: string;
	role: 'owner' | 'co-manager';
	hasOwnershipRequest: boolean;
}

type ManagerDialogType = 'ownership-request' | 'leave-request' | 'send-message' | null;

const MOCK_MANAGERS: ProjectManagerRowData[] = [
	{
		id: 1,
		initials: 'A',
		username: '@akosmo',
		email: 'akosmo@scify.org',
		role: 'co-manager',
		hasOwnershipRequest: true,
	},
	{
		id: 2,
		initials: 'G',
		username: '@ggiannakopulos',
		email: 'ggiana@scify.org',
		role: 'owner',
		hasOwnershipRequest: false,
	},
	{
		id: 3,
		initials: 'G',
		username: '@fpapastegiou',
		email: 'fpapast@scify.org',
		role: 'co-manager',
		hasOwnershipRequest: false,
	},
];

interface ManagersTabProps {
	initialManagers?: ProjectManagerRowData[];
	/** Called after ownership is successfully transferred to a new manager */
	onOwnershipChanged?: (newOwnerId: number) => void;
	/** Called after a leave request is successfully submitted */
	onLeaveRequested?: () => void;
}

export function ManagersTab({
	initialManagers = MOCK_MANAGERS,
	onOwnershipChanged,
	onLeaveRequested,
}: ManagersTabProps) {
	const { t, trans } = useTranslations();
	const [managers, setManagers] = useState<ProjectManagerRowData[]>(initialManagers);
	const [dialogType, setDialogType] = useState<ManagerDialogType>(null);
	const [dialogManager, setDialogManager] = useState<ProjectManagerRowData | null>(null);
	const [messageText, setMessageText] = useState('');

	const openDialog = (type: Exclude<ManagerDialogType, null>, manager: ProjectManagerRowData) => {
		setDialogType(type);
		setDialogManager(manager);
	};

	const closeDialog = () => {
		setDialogType(null);
		setDialogManager(null);
		setMessageText('');
	};

	const handleApproveOwnership = () => {
		if (!dialogManager) return;
		setManagers((prev) =>
			prev.map((m) => {
				if (m.id === dialogManager.id)
					return { ...m, role: 'owner' as const, hasOwnershipRequest: false };
				if (m.role === 'owner') return { ...m, role: 'co-manager' as const };
				return m;
			})
		);
		onOwnershipChanged?.(dialogManager.id);
		closeDialog();
	};

	const handleSendLeaveRequest = () => {
		toast.success('Leave request sent successfully.');
		onLeaveRequested?.();
		closeDialog();
	};

	const handleSendMessage = () => {
		toast.success(`Message sent to ${dialogManager?.username ?? 'manager'}.`);
		closeDialog();
	};

	return (
		<>
			<div
				id="tabpanel-managers"
				role="tabpanel"
				aria-labelledby="tab-managers"
				className="flex flex-col gap-6"
			>
				<div className="flex items-center justify-between">
					<h2 className="page-subtitle">{t('projects.managers_tab.title')}</h2>
					<div className="flex items-center gap-4">
						<Input
							id="invite-email"
							type="email"
							placeholder={t('projects.managers_tab.invite_placeholder')}
							className="w-48"
							aria-label={t('projects.managers_tab.invite_placeholder')}
						/>
						<Button className="bg-brand-blue-700 hover:bg-brand-blue-800 h-10 px-4 font-semibold text-white">
							{t('projects.managers_tab.invite_button')}
						</Button>
					</div>
				</div>
				<div className="overflow-hidden rounded-xl">
					<Table>
						<TableHeader>
							<TableRow className="bg-brand-blue-100 hover:bg-brand-blue-100 border-b border-slate-300">
								<TableHead className="pl-4 text-sm font-semibold text-slate-800">
									{t('projects.managers_tab.table_username')}
								</TableHead>
								<TableHead className="text-center text-sm font-semibold text-slate-800">
									{t('projects.managers_tab.table_role')}
								</TableHead>
								<TableHead className="text-center text-sm font-semibold text-slate-800">
									{t('projects.managers_tab.table_ownership')}
								</TableHead>
								<TableHead className="text-center text-sm font-semibold text-slate-800">
									{t('projects.managers_tab.table_actions')}
								</TableHead>
							</TableRow>
						</TableHeader>
						<TableBody>
							{managers.map((manager) => (
								<TableRow
									key={manager.id}
									className="hover:bg-brand-blue-50 h-[76px] border-b border-slate-300 bg-white"
								>
									<TableCell className="pl-4">
										<UserTableCell
											initials={manager.initials}
											username={manager.username}
											email={manager.email}
											onMessage={() => openDialog('send-message', manager)}
										/>
									</TableCell>
									<TableCell className="text-center">
										{manager.role === 'owner' ? (
											<span className="inline-flex h-[22px] w-[122px] items-center justify-center rounded border border-purple-300 bg-purple-50 px-2 py-px text-center text-xs font-semibold text-purple-600">
												{t('projects.managers_tab.role_owner')}
											</span>
										) : (
											<span className="inline-flex h-[22px] w-[122px] items-center justify-center rounded border border-cyan-300 px-2 py-px text-center text-xs font-semibold text-cyan-600">
												{t('projects.managers_tab.role_co_manager')}
											</span>
										)}
									</TableCell>
									<TableCell className="text-center">
										{manager.hasOwnershipRequest && (
											<button
												type="button"
												onClick={() =>
													openDialog('ownership-request', manager)
												}
												className="text-brand-blue-900 h-[30px] cursor-pointer rounded-lg bg-yellow-300 px-3.5 text-sm font-semibold hover:bg-yellow-400"
											>
												{t(
													'projects.managers_tab.ownership_request_button'
												)}
											</button>
										)}
									</TableCell>
									<TableCell className="text-center">
										<button
											type="button"
											onClick={() => openDialog('leave-request', manager)}
											className="bg-brand-blue-700 hover:bg-brand-blue-800 h-[30px] cursor-pointer rounded-lg px-3.5 text-sm font-semibold text-white"
										>
											{t('projects.managers_tab.leave_button')}
										</button>
									</TableCell>
								</TableRow>
							))}
						</TableBody>
					</Table>
				</div>
			</div>

			{/* ── Ownership Request dialog ─────────────────────────────────── */}
			<ProjectDialog
				open={dialogType === 'ownership-request'}
				onClose={closeDialog}
				icon={<CircleStar />}
				title={t('projects.managers_tab.dialog_ownership_title')}
				description={
					<>
						<p>
							{trans('projects.managers_tab.dialog_ownership_transferred', {
								username: dialogManager?.username ?? '',
							})}
						</p>
						<p className="mt-[14px]">
							{t('projects.managers_tab.dialog_ownership_accept')}
						</p>
					</>
				}
				cancelLabel={t('projects.managers_tab.dialog_ownership_reject')}
				cancelIcon={<X className="size-4" aria-hidden="true" />}
				actionLabel={t('projects.managers_tab.dialog_ownership_approve')}
				actionIcon={<Check className="size-4" aria-hidden="true" />}
				onAction={handleApproveOwnership}
				actionStyle="highlighted"
			/>

			{/* ── Leave Request dialog ──────────────────────────────────────── */}
			<ProjectDialog
				open={dialogType === 'leave-request'}
				onClose={closeDialog}
				icon={<LogOut />}
				title={t('projects.managers_tab.dialog_leave_title')}
				description={
					<>
						<p>{t('projects.managers_tab.dialog_leave_description')}</p>
						<p className="mt-[14px]">
							{t('projects.managers_tab.dialog_leave_confirm')}
						</p>
					</>
				}
				actionLabel={t('projects.managers_tab.dialog_leave_send')}
				actionIcon={<Send className="size-4" aria-hidden="true" />}
				onAction={handleSendLeaveRequest}
				actionStyle="highlighted"
			>
				<div
					className="mb-6 flex gap-3 rounded-md border border-yellow-500 bg-yellow-50 p-3"
					role="alert"
				>
					<TriangleAlert
						className="size-[19px] shrink-0 text-yellow-600"
						aria-hidden="true"
					/>
					<p className="text-xs leading-5 font-medium text-yellow-600">
						{t('projects.managers_tab.dialog_leave_warning')}
					</p>
				</div>
			</ProjectDialog>

			{/* ── Send Message dialog ───────────────────────────────────────── */}
			<ProjectDialog
				open={dialogType === 'send-message'}
				onClose={closeDialog}
				icon={<Mail />}
				title={t('projects.managers_tab.dialog_message_title')}
				description={trans('projects.managers_tab.dialog_message_write', {
					username: dialogManager?.username ?? '',
				})}
				actionLabel={t('projects.managers_tab.dialog_message_send')}
				actionIcon={<Send className="size-4" aria-hidden="true" />}
				onAction={handleSendMessage}
				actionStyle="standard"
			>
				<textarea
					name={'message_' + dialogManager?.id}
					id={'message_' + dialogManager?.id}
					className="focus:border-brand-blue-500 mb-6 h-[120px] w-full resize-none rounded-lg border border-slate-200 bg-white px-3.5 py-3 text-base text-slate-800 placeholder:text-slate-400 focus:shadow-[0_0_0_3px_#cbd5e1] focus:outline-none"
					placeholder={t('projects.managers_tab.dialog_message_placeholder')}
					value={messageText}
					onChange={(e) => setMessageText(e.target.value)}
					aria-label={trans('projects.managers_tab.dialog_message_write', {
						username: dialogManager?.username ?? 'manager',
					})}
				/>
			</ProjectDialog>
		</>
	);
}
