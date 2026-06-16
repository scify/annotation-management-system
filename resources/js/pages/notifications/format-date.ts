import { useTranslations } from '@/hooks/use-translations';
import type { SharedData } from '@/types';
import { usePage } from '@inertiajs/react';

/**
 * The backend serializes notification datetimes as raw `Y-m-d H:i:s`. The
 * mockups show contextual formats:
 *   - list:   "17:35" (same day) / "11/03/26" (older)
 *   - detail: "Today at 17:35" (same day) / "March 28, 2026" (older)
 */
export type NotificationDateVariant = 'list' | 'detail';

function parseBackendDate(datetime: string): Date {
    // Treat the space-separated `Y-m-d H:i:s` as local time.
    return new Date(datetime.replace(' ', 'T'));
}

function isSameDay(a: Date, b: Date): boolean {
    return (
        a.getFullYear() === b.getFullYear() &&
        a.getMonth() === b.getMonth() &&
        a.getDate() === b.getDate()
    );
}

export function formatNotificationDate(
    datetime: string,
    variant: NotificationDateVariant,
    locale: string,
    todayLabel: string
): string {
    const date = parseBackendDate(datetime);
    if (Number.isNaN(date.getTime())) return datetime;

    const time = new Intl.DateTimeFormat(locale, {
        hour: '2-digit',
        minute: '2-digit',
    }).format(date);

    if (isSameDay(date, new Date())) {
        return variant === 'detail' ? `${todayLabel} ${time}` : time;
    }

    if (variant === 'detail') {
        return new Intl.DateTimeFormat(locale, {
            year: 'numeric',
            month: 'long',
            day: 'numeric',
        }).format(date);
    }

    return new Intl.DateTimeFormat(locale, {
        year: '2-digit',
        month: '2-digit',
        day: '2-digit',
    }).format(date);
}

/** Hook that binds the formatter to the active locale and `today at` label. */
export function useNotificationDate(): (
    datetime: string,
    variant: NotificationDateVariant
) => string {
    const { app } = usePage<SharedData>().props;
    const { t } = useTranslations();
    const todayLabel = t('notifications.today_at');

    return (datetime, variant) => formatNotificationDate(datetime, variant, app.locale, todayLabel);
}
