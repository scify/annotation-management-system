import { useTranslations } from '@/hooks/use-translations';
import { cn } from '@/lib/utils';
import { Info, MessageSquare, TriangleAlert } from 'lucide-react';
import { SenderRoleTag, SubjectTag } from './notification-tags';
import {
    isNoticeThread,
    isThreadUnread,
    type NotificationThread,
    type NotificationThreadType,
} from '../types';

const UNREAD_BORDER_STYLES: Record<NotificationThreadType, string> = {
    flag_notification: 'border-l-brand-blue-700',
    instance_related: 'border-l-brand-blue-700',
    generic: 'border-l-brand-blue-700',
    info: 'border-l-yellow-400',
    warning: 'border-l-red-400',
    project_ownership: 'border-l-brand-blue-700',
    project_invitation: 'border-l-brand-blue-700',
    announcement: 'border-l-brand-blue-700',
};

const READ_BORDER_STYLES: Record<NotificationThreadType, string> = {
    flag_notification: 'border-l-brand-blue-100',
    instance_related: 'border-l-brand-blue-100',
    generic: 'border-l-brand-blue-100',
    info: 'border-l-yellow-100',
    warning: 'border-l-red-100',
    project_ownership: 'border-l-brand-blue-100',
    project_invitation: 'border-l-brand-blue-100',
    announcement: 'border-l-brand-blue-100',
};

interface NotificationListItemProps extends React.ComponentProps<'button'> {
    thread: NotificationThread;
    /** Extra classes from the parent, e.g. the selected-state border */
    className?: string;
}

export function NotificationListItem({ thread, className, ...props }: NotificationListItemProps) {
    const { t } = useTranslations();
    const isNotice = isNoticeThread(thread);
    const isUnread = isThreadUnread(thread);
    const lastMessage = thread.notifications[thread.notifications.length - 1];

    if (!lastMessage) return null;

    const Icon = isNotice ? (thread.type === 'warning' ? TriangleAlert : Info) : MessageSquare;

    return (
        <button
            type="button"
            className={cn(
                'focus-visible:ring-brand-blue-700/50 flex w-full cursor-pointer items-start gap-3 rounded-lg border-y border-r border-l-8 border-transparent bg-white p-4 text-left transition-colors outline-none hover:bg-slate-50 focus-visible:ring-[3px]',
                isUnread ? UNREAD_BORDER_STYLES[thread.type] : READ_BORDER_STYLES[thread.type],
                className
            )}
            {...props}
        >
            <span className="relative shrink-0">
                <Icon
                    aria-hidden="true"
                    className={cn(
                        'size-6',
                        thread.type === 'warning' && 'text-red-400',
                        thread.type === 'info' && 'text-yellow-500',
                        !isNotice && 'text-slate-700'
                    )}
                />
                {isUnread && (
                    <span
                        role="status"
                        aria-label={t('notifications.unread')}
                        className="bg-brand-red-700 absolute -top-0.5 -right-0.5 size-2 rounded-full border border-white"
                    />
                )}
            </span>
            <span className="flex min-w-0 flex-1 flex-col gap-2">
                <span className="flex flex-wrap items-center justify-between gap-x-4 gap-y-1">
                    <span className="flex min-w-0 items-center gap-3">
                        {isNotice ? (
                            <span className="truncate text-base font-semibold text-slate-800">
                                {thread.title}
                            </span>
                        ) : (
                            <>
                                <span className="truncate text-base font-semibold text-slate-500">
                                    {lastMessage.sender_username}
                                </span>
                                {lastMessage.sender_role && (
                                    <SenderRoleTag role={lastMessage.sender_role} />
                                )}
                            </>
                        )}
                    </span>
                    <span className="flex shrink-0 items-center gap-4">
                        {thread.subject && <SubjectTag type={thread.type} label={thread.subject} />}
                        <span className="text-sm whitespace-nowrap text-slate-800 tabular-nums">
                            {lastMessage.date}
                        </span>
                    </span>
                </span>
                <span className="truncate text-sm text-slate-500">{lastMessage.body}</span>
            </span>
        </button>
    );
}
