/**
 * Frontend mirror of the backend notification serialization
 * (`NotificationService::getMyNotifications()`), so swapping mock data
 * for live props later is mechanical.
 */

/** Mirrors `App\Enums\NotificationThreadTypeEnum`. */
export type NotificationThreadType =
    | 'flag_notification'
    | 'instance_related'
    | 'generic'
    | 'info'
    | 'warning'
    | 'project_ownership'
    | 'project_invitation'
    | 'announcement';

/** Display role of a message sender within a project context. */
export type NotificationSenderRole = 'annotator' | 'manager' | 'owner';

/** Mirrors `App\Models\Notification` with the appended sender/date attributes. */
export interface NotificationMessage {
    id: number;
    notification_thread_id: number;
    sender_user_id: number | null;
    recipient_user_id: number;
    body: string;
    is_read: boolean;
    sender_username: string | null;
    sender_role: NotificationSenderRole | null;
    date: string;
}

/** Mirrors `App\Models\QuickLink` (hidden ids/timestamps stripped server-side). */
export interface NotificationQuickLink {
    label: string;
    url: string;
}

/** Mirrors `App\Models\NotificationThread` with loaded relations. */
export interface NotificationThread {
    id: number;
    type: NotificationThreadType;
    /** Tag label shown on the card, e.g. "Instance#2" or "Ownership". Hidden when null. */
    subject: string | null;
    /** Heading for info/warning notices. Hidden when null. */
    title: string | null;
    is_accepted: boolean | null;
    is_rejected: boolean | null;
    notifications: NotificationMessage[];
    quick_links: NotificationQuickLink[];
}

/** Notices are read in place — they have no conversation or reply box. */
export function isNoticeThread(thread: NotificationThread): boolean {
    return thread.type === 'info' || thread.type === 'warning';
}

/** Threads that ask the recipient to approve/reject, while no decision was made yet. */
export function isPendingActionThread(thread: NotificationThread): boolean {
    return (
        (thread.type === 'project_ownership' || thread.type === 'project_invitation') &&
        thread.is_accepted === null &&
        thread.is_rejected === null
    );
}

export function isThreadUnread(thread: NotificationThread): boolean {
    return thread.notifications.some((message) => !message.is_read);
}
