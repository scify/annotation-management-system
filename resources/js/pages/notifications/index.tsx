import { Button } from '@/components/ui/button';
import { ToggleSwitch } from '@/components/ui/toggle-switch';
import { useTranslations } from '@/hooks/use-translations';
import AppLayout from '@/layouts/app-layout';
import { type PageProps } from '@/types';
import { Head, usePage } from '@inertiajs/react';
import { useState } from 'react';
import { NotificationListItem } from './components/notification-list-item';
import { ThreadDetail } from './components/thread-detail';
import { createMockThreads } from './mock-data';
import { isThreadUnread, type NotificationMessage, type NotificationThread } from './types';

// The `threads` prop sent by NotificationController is intentionally ignored
// for now — the backend returns an empty collection. The page runs on mock
// data (see ./mock-data.ts) until the real notification flow lands.
export default function NotificationsIndex() {
    const { t } = useTranslations();
    const { auth } = usePage<PageProps>().props;
    const [threads, setThreads] = useState<NotificationThread[]>(() =>
        createMockThreads(auth.user.id, auth.user.name)
    );
    const [selectedThreadId, setSelectedThreadId] = useState<number | null>(null);
    const [showUnreadOnly, setShowUnreadOnly] = useState(false);

    const selectedThread = threads.find((thread) => thread.id === selectedThreadId) ?? null;
    // Keep the selected thread visible while the unread filter is on, even
    // though opening it just marked it as read — removing it mid-interaction
    // would be jarring.
    const visibleThreads = showUnreadOnly
        ? threads.filter((thread) => isThreadUnread(thread) || thread.id === selectedThreadId)
        : threads;

    const updateThread = (
        threadId: number,
        updater: (thread: NotificationThread) => NotificationThread
    ) => {
        setThreads((current) =>
            current.map((thread) => (thread.id === threadId ? updater(thread) : thread))
        );
    };

    const setThreadRead = (threadId: number, isRead: boolean) => {
        updateThread(threadId, (thread) => ({
            ...thread,
            notifications: thread.notifications.map((message) =>
                // Own messages always stay read — only incoming ones toggle.
                message.sender_user_id === auth.user.id ? message : { ...message, is_read: isRead }
            ),
        }));
    };

    const handleSelect = (threadId: number) => {
        setSelectedThreadId(threadId);
        setThreadRead(threadId, true);
    };

    const handleMarkSelectedUnread = () => {
        if (selectedThreadId === null) return;
        setThreadRead(selectedThreadId, false);
        setSelectedThreadId(null);
    };

    const handleMarkAllRead = () => {
        setThreads((current) =>
            current.map((thread) => ({
                ...thread,
                notifications: thread.notifications.map((message) => ({
                    ...message,
                    is_read: true,
                })),
            }))
        );
    };

    const handleReply = (body: string) => {
        if (selectedThreadId === null) return;
        const nextId =
            Math.max(...threads.flatMap((thread) => thread.notifications.map((m) => m.id))) + 1;
        const reply: NotificationMessage = {
            id: nextId,
            notification_thread_id: selectedThreadId,
            sender_user_id: auth.user.id,
            recipient_user_id: 0,
            body,
            is_read: true,
            sender_username: auth.user.name,
            sender_role: null,
            date: new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }),
        };
        updateThread(selectedThreadId, (thread) => ({
            ...thread,
            notifications: [...thread.notifications, reply],
        }));
    };

    const handleDecision = (isAccepted: boolean) => {
        if (selectedThreadId === null) return;
        updateThread(selectedThreadId, (thread) => ({
            ...thread,
            is_accepted: isAccepted,
            is_rejected: !isAccepted,
        }));
    };

    return (
        <AppLayout
            breadcrumbs={[{ title: t('notifications.title'), href: route('notifications.index') }]}
        >
            <Head title={t('notifications.index_page_title')} />
            <div className="flex flex-col gap-6 p-6">
                <div className="flex flex-wrap items-center justify-between gap-4">
                    <div className="flex items-center gap-4">
                        <h1 className="text-3xl font-light text-slate-800">
                            {t('notifications.title')}
                        </h1>
                        <ToggleSwitch
                            id="show-unread-toggle"
                            checked={showUnreadOnly}
                            onChange={setShowUnreadOnly}
                            ariaLabel={t('notifications.show_unread')}
                            stateLabels={{
                                on: t('notifications.show_unread'),
                                off: t('notifications.show_all'),
                            }}
                        />
                    </div>
                    <div className="flex items-center gap-3">
                        <Button
                            variant="secondary"
                            size="sm"
                            className="min-w-[135px]"
                            disabled={selectedThreadId === null}
                            onPress={handleMarkSelectedUnread}
                        >
                            {t('notifications.mark_as_unread')}
                        </Button>
                        <Button size="sm" className="min-w-[135px]" onPress={handleMarkAllRead}>
                            {t('notifications.mark_all_as_read')}
                        </Button>
                    </div>
                </div>

                <div className="grid grid-cols-1 items-start gap-5 lg:grid-cols-2">
                    {visibleThreads.length === 0 ? (
                        <p className="flex items-center justify-center rounded-lg border border-dashed border-slate-300 bg-white p-10 text-center text-slate-500">
                            {showUnreadOnly
                                ? t('notifications.empty_unread')
                                : t('notifications.empty_list')}
                        </p>
                    ) : (
                        <ul className="flex flex-col gap-3">
                            {visibleThreads.map((thread) => (
                                <li key={thread.id}>
                                    <NotificationListItem
                                        thread={thread}
                                        aria-current={
                                            thread.id === selectedThreadId ? 'true' : undefined
                                        }
                                        className={
                                            thread.id === selectedThreadId
                                                ? 'border-brand-blue-700 border-l-brand-blue-700'
                                                : undefined
                                        }
                                        onClick={() => handleSelect(thread.id)}
                                    />
                                </li>
                            ))}
                        </ul>
                    )}

                    <div className="lg:sticky lg:top-6">
                        {selectedThread ? (
                            <ThreadDetail
                                key={selectedThread.id}
                                thread={selectedThread}
                                currentUserId={auth.user.id}
                                onReply={handleReply}
                                onApprove={() => handleDecision(true)}
                                onReject={() => handleDecision(false)}
                            />
                        ) : (
                            <p className="hidden items-center justify-center rounded-lg border border-dashed border-slate-300 p-10 text-center text-slate-500 lg:flex">
                                {t('notifications.select_notification_hint')}
                            </p>
                        )}
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}
