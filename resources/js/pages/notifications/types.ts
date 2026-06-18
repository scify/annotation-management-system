/**
 * Frontend mirror of the backend notification serialization
 * (`NotificationService::getMyNotifications()`), captured in
 * `storage/app/private/notifications-index-data.json`.
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
    | 'project_request_to_leave'
    | 'announcement';

/** Decision state of an action thread, mirrors `App\Enums\NotificationThreadResponseEnum`. */
export type NotificationThreadResponse = 'accepted' | 'rejected' | 'unreplied';

/** Mirrors `App\Models\Notification` with the appended sender/date attributes. */
export interface NotificationMessage {
    id: number;
    notification_thread_id: number;
    sender_user_id: number | null;
    body: string;
    sender_username: string | null;
    /**
     * Raw role string from the backend (the sender's *global* role, e.g.
     * `annotation-manager` / `admin`). Normalized to a display role by
     * `SenderRoleTag`. See `tasks/notifications-backend-gaps.md`.
     */
    sender_role: string | null;
    /** Raw `Y-m-d H:i:s` from the backend; format via `formatNotificationDate`. */
    datetime: string;
}

/** Mirrors `App\Models\QuickLink` (hidden ids/timestamps stripped server-side). */
export interface NotificationQuickLink {
    label: string;
    url: string;
}

/** Mirrors `App\Models\NotificationThread` with loaded relations + appended attributes. */
export interface NotificationThread {
    id: number;
    type: NotificationThreadType;
    /** Heading: sender username for conversations, notice title for info/warning. */
    title: string | null;
    /** Latest message datetime (`Y-m-d H:i:s`). */
    datetime: string;
    /** Thread-level read flag (true when all of the user's messages are read). */
    is_read: boolean;
    /** Whether the recipient may reply (generic/flag/instance threads). */
    allowed_to_reply: boolean;
    /** Username of the last responder, shown as a preview prefix. Null for single-message threads. */
    replied_by: string | null;
    /** Tag label shown top-right on the card, e.g. "Instance#2" or "Ownership". Hidden when null. */
    top_right: string | null;
    /** Usernames the notification was sent to, shown in the detail panel. */
    recipients: string[];
    notifications: NotificationMessage[];
    quick_links: NotificationQuickLink[];
    /**
     * Decision state for project_ownership / project_invitation /
     * project_request_to_leave threads. Only present on those types. Updated
     * optimistically on approve/reject — no
     * persistence endpoint yet (see `tasks/notifications-backend-gaps.md`).
     */
    response?: NotificationThreadResponse;
}

/** Notices are read in place — they have no conversation or reply box. */
export function isNoticeThread(thread: NotificationThread): boolean {
    return thread.type === 'info' || thread.type === 'warning';
}

/** Threads that ask the recipient to approve/reject (ownership / invitation / request to leave). */
export function isActionThread(thread: NotificationThread): boolean {
    return (
        thread.type === 'project_ownership' ||
        thread.type === 'project_invitation' ||
        thread.type === 'project_request_to_leave'
    );
}

export function isThreadUnread(thread: NotificationThread): boolean {
    return !thread.is_read;
}
