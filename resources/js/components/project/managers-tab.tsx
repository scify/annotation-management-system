import { Badge } from '@/components/ui/badge';
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
import { SendMessageDialog } from '@/components/send-message-dialog';
import { useTranslations } from '@/hooks/use-translations';
import { ApiError } from '@/lib/api';
import { cn } from '@/lib/utils';
import { Check, CircleStar, LogOut, Send, TriangleAlert, UserMinus, X } from 'lucide-react';
import { type ReactNode, useState } from 'react';
import { toast } from 'sonner';

export interface ProjectManagerRowData {
    id: number;
    initials: string;
    username: string;
    email: string;
    role: 'owner' | 'co-manager';
    isActive: boolean;
    /** False while the co-manager is invited but has not accepted yet */
    accepted: boolean;
    /** This co-manager has an open request to leave the project */
    requestToLeave: boolean;
    /** This co-manager has been proposed to become the new owner */
    proposedToBecomeOwner: boolean;
    canRequestToLeave: boolean;
    canRemove: boolean;
    canTransferOwnership: boolean;
    canAcceptToBecomeOwner: boolean;
    canAcceptRequestToLeave: boolean;
}

type ManagerDialogType =
    | 'ownership-request'
    | 'leave-request'
    | 'leave-approval'
    | 'transfer-ownership'
    | 'remove'
    | 'send-message'
    | null;

interface ManagersTabProps {
    managers: ProjectManagerRowData[];
    /** Proposes the given user as the project's new owner; resolves once the table has been updated */
    onTransferOwnership: (managerId: number) => Promise<void>;
    /** Accepts a pending ownership proposal addressed to the current user; resolves once the table has been updated */
    onAcceptOwnership: () => Promise<void>;
    /** Declines a pending ownership proposal addressed to the current user; resolves once the table has been updated */
    onRejectOwnership: () => Promise<void>;
    /** Withdraws a pending ownership proposal for the given user; resolves once the table has been updated */
    onCancelOwnership: (managerId: number) => Promise<void>;
    /** Sends the current user's request to leave; resolves once the table has been updated */
    onRequestToLeave: () => Promise<void>;
    /** Withdraws the current user's pending leave request; resolves once the table has been updated */
    onCancelLeaveRequest: () => Promise<void>;
    /** Owner approves the given co-manager's leave request; resolves once the table has been updated */
    onApproveLeave: (managerId: number) => Promise<void>;
    /** Owner rejects the given co-manager's leave request; resolves once the table has been updated */
    onRejectLeave: (managerId: number) => Promise<void>;
    /** Removes the given co-manager from the project; resolves once the table has been updated */
    onRemoveManager: (managerId: number) => Promise<void>;
}

const BLUE_BUTTON_CLASSES =
    'bg-brand-blue-700 hover:bg-brand-blue-800 h-[30px] cursor-pointer rounded-lg px-3.5 text-sm font-semibold text-white disabled:cursor-not-allowed disabled:opacity-50';
const YELLOW_BUTTON_CLASSES =
    'text-brand-blue-900 h-[30px] cursor-pointer rounded-lg bg-yellow-300 px-3.5 text-sm font-semibold hover:bg-yellow-400';

function WarningAlert({ children }: { children: ReactNode }) {
    return (
        <div
            className="mb-6 flex gap-3 rounded-md border border-yellow-500 bg-yellow-50 p-3"
            role="alert"
        >
            <TriangleAlert className="size-[19px] shrink-0 text-yellow-600" aria-hidden="true" />
            <p className="text-xs leading-5 font-medium text-yellow-600">{children}</p>
        </div>
    );
}

/** Disabled action button with a "Requested" note and an Undo link below it */
function RequestedIndicator({ buttonLabel, onUndo }: { buttonLabel: string; onUndo: () => void }) {
    const { t } = useTranslations();

    return (
        <div className="flex flex-col items-center gap-1">
            <button type="button" disabled className={BLUE_BUTTON_CLASSES}>
                {buttonLabel}
            </button>
            <span className="text-xs font-medium text-slate-500">
                {t('projects.managers_tab.requested_label')}{' '}
                <button
                    type="button"
                    onClick={onUndo}
                    className="cursor-pointer font-semibold text-slate-600 underline"
                >
                    {t('projects.managers_tab.undo_label')}
                </button>
            </span>
        </div>
    );
}

function StatusBadge({ manager }: { manager: ProjectManagerRowData }) {
    const { t } = useTranslations();

    if (!manager.accepted) {
        return <Badge variant="slate">{t('projects.managers_tab.status_pending')}</Badge>;
    }
    if (manager.isActive) {
        return <Badge variant="lime">{t('projects.managers_tab.status_active')}</Badge>;
    }
    return <Badge variant="slate">{t('projects.managers_tab.status_inactive')}</Badge>;
}

interface OwnershipCellProps {
    manager: ProjectManagerRowData;
    onAcceptOwnership: () => void;
    onTransfer: () => void;
    onUndoTransfer: () => void;
}

function OwnershipCell({
    manager,
    onAcceptOwnership,
    onTransfer,
    onUndoTransfer,
}: OwnershipCellProps) {
    const { t } = useTranslations();

    if (manager.canAcceptToBecomeOwner) {
        return (
            <button type="button" onClick={onAcceptOwnership} className={YELLOW_BUTTON_CLASSES}>
                {t('projects.managers_tab.ownership_request_button')}
            </button>
        );
    }
    if (manager.proposedToBecomeOwner) {
        return (
            <RequestedIndicator
                buttonLabel={t('projects.managers_tab.transfer_button')}
                onUndo={onUndoTransfer}
            />
        );
    }
    if (manager.canTransferOwnership) {
        return (
            <button
                type="button"
                onClick={onTransfer}
                disabled={!manager.accepted}
                className={BLUE_BUTTON_CLASSES}
            >
                {t('projects.managers_tab.transfer_button')}
            </button>
        );
    }
    return null;
}

interface ActionsCellProps {
    manager: ProjectManagerRowData;
    onLeaveApproval: () => void;
    onLeaveRequest: () => void;
    onUndoLeaveRequest: () => void;
    onRemove: () => void;
}

function ActionsCell({
    manager,
    onLeaveApproval,
    onLeaveRequest,
    onUndoLeaveRequest,
    onRemove,
}: ActionsCellProps) {
    const { t } = useTranslations();

    return (
        <div className="flex items-center justify-center gap-2">
            {manager.canAcceptRequestToLeave && (
                <button type="button" onClick={onLeaveApproval} className={YELLOW_BUTTON_CLASSES}>
                    {t('projects.managers_tab.leave_request_button')}
                </button>
            )}
            {manager.canRequestToLeave &&
                (manager.requestToLeave ? (
                    <RequestedIndicator
                        buttonLabel={t('projects.managers_tab.leave_button')}
                        onUndo={onUndoLeaveRequest}
                    />
                ) : (
                    <button type="button" onClick={onLeaveRequest} className={BLUE_BUTTON_CLASSES}>
                        {t('projects.managers_tab.leave_button')}
                    </button>
                ))}
            {manager.canRemove && (
                <button type="button" onClick={onRemove} className={BLUE_BUTTON_CLASSES}>
                    {t('projects.managers_tab.remove_button')}
                </button>
            )}
        </div>
    );
}

export function ManagersTab({
    managers,
    onTransferOwnership,
    onAcceptOwnership,
    onRejectOwnership,
    onCancelOwnership,
    onRequestToLeave,
    onCancelLeaveRequest,
    onApproveLeave,
    onRejectLeave,
    onRemoveManager,
}: ManagersTabProps) {
    const { t, trans } = useTranslations();
    const [dialogType, setDialogType] = useState<ManagerDialogType>(null);
    const [dialogManager, setDialogManager] = useState<ProjectManagerRowData | null>(null);
    const [transferring, setTransferring] = useState(false);
    const [ownershipAction, setOwnershipAction] = useState<'accept' | 'reject' | null>(null);
    const [submitting, setSubmitting] = useState(false);
    const [leaveApprovalAction, setLeaveApprovalAction] = useState<'approve' | 'reject' | null>(
        null
    );

    const openDialog = (type: Exclude<ManagerDialogType, null>, manager: ProjectManagerRowData) => {
        setDialogType(type);
        setDialogManager(manager);
    };

    const closeDialog = () => {
        setDialogType(null);
        setDialogManager(null);
    };

    const handleApproveOwnership = async () => {
        setOwnershipAction('accept');
        try {
            await onAcceptOwnership();
            toast.success(t('projects.managers_tab.ownership_accepted'));
            closeDialog();
        } catch {
            toast.error(t('projects.messages.generic_error'));
        } finally {
            setOwnershipAction(null);
        }
    };

    const handleRejectOwnership = async () => {
        setOwnershipAction('reject');
        try {
            await onRejectOwnership();
            toast.success(t('projects.managers_tab.ownership_rejected'));
            closeDialog();
        } catch {
            toast.error(t('projects.messages.generic_error'));
        } finally {
            setOwnershipAction(null);
        }
    };

    const handleSendLeaveRequest = async () => {
        setSubmitting(true);
        try {
            await onRequestToLeave();
            toast.success(t('projects.managers_tab.leave_request_sent'));
            closeDialog();
        } catch (e) {
            toast.error(e instanceof ApiError ? e.message : t('projects.messages.generic_error'));
        } finally {
            setSubmitting(false);
        }
    };

    const handleUndoLeaveRequest = async () => {
        try {
            await onCancelLeaveRequest();
            toast.success(t('projects.managers_tab.leave_request_cancelled'));
        } catch (e) {
            toast.error(e instanceof ApiError ? e.message : t('projects.messages.generic_error'));
        }
    };

    const handleApproveLeave = async () => {
        if (!dialogManager) return;
        setLeaveApprovalAction('approve');
        try {
            await onApproveLeave(dialogManager.id);
            toast.success(t('projects.managers_tab.leave_approved'));
            closeDialog();
        } catch (e) {
            toast.error(e instanceof ApiError ? e.message : t('projects.messages.generic_error'));
        } finally {
            setLeaveApprovalAction(null);
        }
    };

    const handleRejectLeave = async () => {
        if (!dialogManager) return;
        setLeaveApprovalAction('reject');
        try {
            await onRejectLeave(dialogManager.id);
            toast.success(t('projects.managers_tab.leave_rejected'));
            closeDialog();
        } catch (e) {
            toast.error(e instanceof ApiError ? e.message : t('projects.messages.generic_error'));
        } finally {
            setLeaveApprovalAction(null);
        }
    };

    const handleConfirmTransfer = async () => {
        if (!dialogManager) return;
        setTransferring(true);
        try {
            await onTransferOwnership(dialogManager.id);
            toast.success(t('projects.managers_tab.transfer_proposed'));
            closeDialog();
        } catch (e) {
            toast.error(e instanceof ApiError ? e.message : t('projects.messages.generic_error'));
        } finally {
            setTransferring(false);
        }
    };

    const handleUndoTransfer = async (managerId: number) => {
        try {
            await onCancelOwnership(managerId);
            toast.success(t('projects.managers_tab.transfer_cancelled'));
        } catch (e) {
            toast.error(e instanceof ApiError ? e.message : t('projects.messages.generic_error'));
        }
    };

    const handleConfirmRemove = async () => {
        if (!dialogManager) return;
        setSubmitting(true);
        try {
            await onRemoveManager(dialogManager.id);
            toast.success(t('projects.messages.manager_removed'));
            closeDialog();
        } catch (e) {
            toast.error(e instanceof ApiError ? e.message : t('projects.messages.generic_error'));
        } finally {
            setSubmitting(false);
        }
    };

    const handleSendMessage = (_message: string) => {
        console.log('Sending message:', _message);
        toast.success(`Message sent to ${dialogManager?.username ?? 'manager'}.`);
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
                                    {t('projects.managers_tab.table_status')}
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
                                        <div className={cn(!manager.accepted && 'opacity-50')}>
                                            <UserTableCell
                                                initials={manager.initials}
                                                username={manager.username}
                                                email={manager.email}
                                                onMessage={() =>
                                                    openDialog('send-message', manager)
                                                }
                                            />
                                        </div>
                                    </TableCell>
                                    <TableCell className="text-center">
                                        {manager.role === 'owner' ? (
                                            <span className="inline-flex h-[22px] items-center justify-center rounded border border-purple-300 bg-purple-50 px-2 py-px text-center text-xs font-semibold text-purple-600">
                                                {t('projects.managers_tab.role_owner')}
                                            </span>
                                        ) : (
                                            <span
                                                className={cn(
                                                    'inline-flex h-[22px] items-center justify-center rounded border border-cyan-300 px-2 py-px text-center text-xs font-semibold text-cyan-600',
                                                    !manager.accepted && 'opacity-50'
                                                )}
                                            >
                                                {t('projects.managers_tab.role_co_manager')}
                                            </span>
                                        )}
                                    </TableCell>
                                    <TableCell className="text-center">
                                        <StatusBadge manager={manager} />
                                    </TableCell>
                                    <TableCell className="text-center">
                                        <OwnershipCell
                                            manager={manager}
                                            onAcceptOwnership={() =>
                                                openDialog('ownership-request', manager)
                                            }
                                            onTransfer={() =>
                                                openDialog('transfer-ownership', manager)
                                            }
                                            onUndoTransfer={() =>
                                                void handleUndoTransfer(manager.id)
                                            }
                                        />
                                    </TableCell>
                                    <TableCell className="text-center">
                                        <ActionsCell
                                            manager={manager}
                                            onLeaveApproval={() =>
                                                openDialog('leave-approval', manager)
                                            }
                                            onLeaveRequest={() =>
                                                openDialog('leave-request', manager)
                                            }
                                            onUndoLeaveRequest={() => void handleUndoLeaveRequest()}
                                            onRemove={() => openDialog('remove', manager)}
                                        />
                                    </TableCell>
                                </TableRow>
                            ))}
                        </TableBody>
                    </Table>
                </div>
            </div>

            {/* ── Ownership Request dialog (accept / reject proposed ownership) ── */}
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
                onCancel={() => void handleRejectOwnership()}
                cancelLoading={ownershipAction === 'reject'}
                actionLabel={t('projects.managers_tab.dialog_ownership_approve')}
                actionIcon={<Check className="size-4" aria-hidden="true" />}
                onAction={() => void handleApproveOwnership()}
                loading={ownershipAction === 'accept'}
                actionStyle="highlighted"
            />

            {/* ── Leave Request dialog (send my own request) ────────────────── */}
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
                onAction={() => void handleSendLeaveRequest()}
                loading={submitting}
                actionStyle="highlighted"
            >
                <WarningAlert>{t('projects.managers_tab.dialog_leave_warning')}</WarningAlert>
            </ProjectDialog>

            {/* ── Leave Request approval dialog (owner approves/rejects) ────── */}
            <ProjectDialog
                open={dialogType === 'leave-approval'}
                onClose={closeDialog}
                icon={<LogOut />}
                title={t('projects.managers_tab.dialog_leave_approval_title')}
                description={
                    <>
                        <p>
                            {trans('projects.managers_tab.dialog_leave_approval_description', {
                                username: dialogManager?.username ?? '',
                            })}
                        </p>
                        <p className="mt-[14px]">
                            {t('projects.managers_tab.dialog_leave_approval_question')}
                        </p>
                    </>
                }
                cancelLabel={t('projects.managers_tab.dialog_ownership_reject')}
                cancelIcon={<X className="size-4" aria-hidden="true" />}
                onCancel={() => void handleRejectLeave()}
                cancelLoading={leaveApprovalAction === 'reject'}
                actionLabel={t('projects.managers_tab.dialog_ownership_approve')}
                actionIcon={<Check className="size-4" aria-hidden="true" />}
                onAction={() => void handleApproveLeave()}
                loading={leaveApprovalAction === 'approve'}
                actionStyle="highlighted"
            >
                <WarningAlert>
                    {trans('projects.managers_tab.dialog_leave_approval_warning', {
                        username: dialogManager?.username ?? '',
                    })}
                </WarningAlert>
            </ProjectDialog>

            {/* ── Transfer Ownership dialog ─────────────────────────────────── */}
            <ProjectDialog
                open={dialogType === 'transfer-ownership'}
                onClose={closeDialog}
                icon={<CircleStar />}
                title={t('projects.managers_tab.dialog_transfer_title')}
                description={trans('projects.managers_tab.dialog_transfer_description', {
                    username: dialogManager?.username ?? '',
                })}
                actionLabel={t('projects.managers_tab.dialog_transfer_send')}
                actionIcon={<Send className="size-4" aria-hidden="true" />}
                onAction={() => void handleConfirmTransfer()}
                loading={transferring}
                actionStyle="highlighted"
            >
                <WarningAlert>{t('projects.managers_tab.dialog_transfer_warning')}</WarningAlert>
            </ProjectDialog>

            {/* ── Remove co-manager dialog ──────────────────────────────────── */}
            <ProjectDialog
                open={dialogType === 'remove'}
                onClose={closeDialog}
                icon={<UserMinus />}
                title={t('projects.managers_tab.remove_dialog_title')}
                description={trans('projects.managers_tab.remove_dialog_description', {
                    username: dialogManager?.username ?? '',
                })}
                cancelLabel={t('projects.create.cancel')}
                actionLabel={t('projects.managers_tab.remove_confirm')}
                onAction={() => void handleConfirmRemove()}
                loading={submitting}
            >
                <WarningAlert>
                    {trans('projects.managers_tab.remove_dialog_warning', {
                        username: dialogManager?.username ?? '',
                    })}
                </WarningAlert>
            </ProjectDialog>

            {/* ── Send Message dialog ───────────────────────────────────────── */}
            <SendMessageDialog
                open={dialogType === 'send-message'}
                onClose={closeDialog}
                targetName={dialogManager?.username ?? ''}
                onSend={handleSendMessage}
            />
        </>
    );
}
