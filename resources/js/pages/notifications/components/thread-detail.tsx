import { Avatar, AvatarFallback } from '@/components/ui/avatar';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuLabel,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { useInitials } from '@/hooks/use-initials';
import { useTranslations } from '@/hooks/use-translations';
import { cn } from '@/lib/utils';
import { Check, Info, MessageSquare, Send, TriangleAlert, X } from 'lucide-react';
import { useState } from 'react';
import { useNotificationDate } from '../format-date';
import { SenderRoleTag, SubjectTag } from './notification-tags';
import {
    isActionThread,
    isNoticeThread,
    type NotificationMessage,
    type NotificationThread,
} from '../types';

interface MessageBubbleProps {
    message: NotificationMessage;
    isOwn: boolean;
    /** Overrides the sender username line, e.g. "kevinNash made an announcement:". */
    label?: string;
}

function MessageBubble({ message, isOwn, label }: MessageBubbleProps) {
    const getInitials = useInitials();
    const formatDate = useNotificationDate();

    return (
        <li className={cn('flex w-full flex-col gap-2', isOwn && 'items-end')}>
            <div className={cn('flex w-full items-center justify-between gap-4')}>
                <span className="flex items-center gap-1.5">
                    <Avatar className="size-[22px]">
                        <AvatarFallback className="bg-brand-blue-700 text-xs font-semibold text-white">
                            {getInitials(message.sender_username ?? '')}
                        </AvatarFallback>
                    </Avatar>
                    <span className="text-base font-medium text-slate-800">
                        {label ?? message.sender_username}
                    </span>
                </span>
                <span className="text-sm whitespace-nowrap text-slate-600 tabular-nums">
                    {formatDate(message.datetime, 'detail')}
                </span>
            </div>
            <p
                className={cn(
                    'rounded-lg p-4 text-base whitespace-pre-line',
                    isOwn
                        ? 'bg-brand-blue-700 mr-7 text-white'
                        : 'mx-7 self-stretch bg-slate-100 text-slate-800'
                )}
            >
                {message.body}
            </p>
        </li>
    );
}

interface RecipientsLineProps {
    recipients: string[];
}

/**
 * Shows who the notification was sent to, below the header. A single recipient is
 * shown inline; multiple recipients show the first plus a "+N more" trigger that
 * lists the remaining recipients in a small popover.
 */
function RecipientsLine({ recipients }: RecipientsLineProps) {
    const { t, trans } = useTranslations();

    if (recipients.length === 0) return null;

    const [first, ...rest] = recipients;

    return (
        <p className="text-sm text-slate-600">
            <span className="font-medium text-slate-800">
                {recipients.length === 1
                    ? t('notifications.recipient')
                    : t('notifications.recipients')}
            </span>{' '}
            {first}
            {rest.length > 0 && (
                <>
                    {' '}
                    <DropdownMenu>
                        <DropdownMenuTrigger asChild>
                            <Button
                                variant="link"
                                className="text-brand-blue-700 h-auto p-0 text-sm font-semibold"
                            >
                                {trans('notifications.recipients_more', { count: rest.length })}
                            </Button>
                        </DropdownMenuTrigger>
                        <DropdownMenuContent align="start">
                            {rest.map((recipient) => (
                                <DropdownMenuLabel key={recipient}>{recipient}</DropdownMenuLabel>
                            ))}
                        </DropdownMenuContent>
                    </DropdownMenu>
                </>
            )}
        </p>
    );
}

interface ThreadDetailProps {
    thread: NotificationThread;
    currentUserId: number;
    onReply: (body: string) => void;
    onApprove: () => void;
    onReject: () => void;
}

export function ThreadDetail({
    thread,
    currentUserId,
    onReply,
    onApprove,
    onReject,
}: ThreadDetailProps) {
    const { t, trans } = useTranslations();
    const formatDate = useNotificationDate();
    const [replyBody, setReplyBody] = useState('');
    const isNotice = isNoticeThread(thread);
    const isDecided = thread.response === 'accepted' || thread.response === 'rejected';
    const firstMessage = thread.notifications[0];

    if (!firstMessage) return null;

    const handleReplySubmit = (event: React.FormEvent<HTMLFormElement>) => {
        event.preventDefault();
        const body = replyBody.trim();
        if (!body) return;
        onReply(body);
        setReplyBody('');
    };

    if (isNotice) {
        const NoticeIcon = thread.type === 'warning' ? TriangleAlert : Info;

        return (
            <article className="border-brand-blue-700 flex flex-col gap-6 rounded-lg border bg-white p-6">
                <div className="flex items-start justify-between gap-4">
                    <div className="flex items-center gap-3">
                        <NoticeIcon
                            aria-hidden="true"
                            className={cn(
                                'size-6 shrink-0',
                                thread.type === 'warning' ? 'text-red-400' : 'text-yellow-500'
                            )}
                        />
                        <h2 className="text-lg font-semibold text-slate-800">{thread.title}</h2>
                    </div>
                    <span className="text-sm whitespace-nowrap text-slate-600 tabular-nums">
                        {formatDate(firstMessage.datetime, 'detail')}
                    </span>
                </div>
                <RecipientsLine recipients={thread.recipients} />
                <p className="text-base whitespace-pre-line text-slate-800">{firstMessage.body}</p>
            </article>
        );
    }

    return (
        <article className="border-brand-blue-700 flex flex-col gap-6 rounded-lg border bg-white p-6">
            <div className="flex items-start justify-between gap-4">
                <div className="flex min-w-0 items-center gap-3">
                    <MessageSquare aria-hidden="true" className="size-6 shrink-0 text-slate-700" />
                    <h2 className="truncate text-lg font-semibold text-slate-800">
                        {thread.title}
                    </h2>
                    {firstMessage.sender_role && <SenderRoleTag role={firstMessage.sender_role} />}
                </div>
                {thread.top_right && <SubjectTag type={thread.type} label={thread.top_right} />}
            </div>

            <RecipientsLine recipients={thread.recipients} />

            {thread.quick_links.length > 0 && (
                <div className="flex flex-wrap items-center gap-x-8 gap-y-2 border-b border-slate-200 pb-4">
                    <span className="text-sm font-medium text-slate-800">
                        {t('notifications.quick_links')}
                    </span>
                    {thread.quick_links.map((link) => (
                        // Native anchor (not Inertia <Link>): Inertia intercepts plain
                        // left-clicks and ignores target="_blank", so it would open in the
                        // same tab. A real anchor lets the browser open a new tab.
                        <a
                            key={link.label}
                            href={link.url}
                            target="_blank"
                            rel="noopener noreferrer"
                            className="focus-visible:ring-brand-blue-700/50 rounded-sm text-sm font-semibold text-slate-800 underline outline-none hover:text-slate-600 focus-visible:ring-[3px]"
                        >
                            {link.label}
                        </a>
                    ))}
                </div>
            )}

            <ul className="flex flex-col gap-6">
                {thread.notifications.map((message) => (
                    <MessageBubble
                        key={message.id}
                        message={message}
                        isOwn={message.sender_user_id === currentUserId}
                        label={
                            thread.type === 'announcement'
                                ? trans('notifications.messages.announcement', {
                                      username: message.sender_username ?? '',
                                  })
                                : undefined
                        }
                    />
                ))}
            </ul>

            {isActionThread(thread) ? (
                isDecided ? (
                    <div
                        role="status"
                        className={cn(
                            'flex items-center justify-end gap-1.5 text-sm font-semibold',
                            thread.response === 'accepted' ? 'text-green-600' : 'text-red-500'
                        )}
                    >
                        {thread.response === 'accepted' ? (
                            <>
                                <Check aria-hidden="true" className="size-4" />
                                {t('notifications.accepted')}
                            </>
                        ) : (
                            <>
                                <X aria-hidden="true" className="size-4" />
                                {t('notifications.rejected')}
                            </>
                        )}
                    </div>
                ) : (
                    <div className="flex items-center justify-end gap-3">
                        <Button
                            variant="secondary"
                            size="sm"
                            className="min-w-[100px]"
                            onPress={onReject}
                        >
                            <X aria-hidden="true" />
                            {t('notifications.reject')}
                        </Button>
                        <Button size="sm" className="min-w-[100px]" onPress={onApprove}>
                            <Check aria-hidden="true" />
                            {t('notifications.approve')}
                        </Button>
                    </div>
                )
            ) : thread.allowed_to_reply ? (
                <form className="flex flex-col items-end gap-4" onSubmit={handleReplySubmit}>
                    <div className="flex w-full flex-col gap-1.5">
                        <label
                            htmlFor={`reply-${thread.id}`}
                            className="px-2.5 text-sm font-semibold text-slate-800"
                        >
                            {t('notifications.reply')}
                        </label>
                        <textarea
                            id={`reply-${thread.id}`}
                            name="reply"
                            value={replyBody}
                            placeholder={t('notifications.reply_placeholder')}
                            onChange={(event) => setReplyBody(event.target.value)}
                            onKeyDown={(event) => {
                                if ((event.metaKey || event.ctrlKey) && event.key === 'Enter') {
                                    event.currentTarget.form?.requestSubmit();
                                }
                            }}
                            className="focus:border-brand-blue-500 focus-visible:border-ring focus-visible:ring-ring/50 min-h-[96px] w-full rounded-lg border border-slate-200 bg-white px-3.5 py-3 text-base text-slate-800 outline-none placeholder:text-slate-400 focus:shadow-[0_0_0_3px_#cbd5e1] focus-visible:shadow-none focus-visible:ring-[3px]"
                        />
                    </div>
                    <Button type="submit" size="sm" className="min-w-[100px]">
                        {t('notifications.send_reply')}
                        <Send aria-hidden="true" />
                    </Button>
                </form>
            ) : null}
        </article>
    );
}
