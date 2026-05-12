const LOCALE = (typeof document !== 'undefined' && document.documentElement.lang) || 'en';

export function formatDate(
    date: string | null,
    options: Intl.DateTimeFormatOptions = {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    }
): string {
    if (!date) return '';
    return new Date(date).toLocaleDateString(LOCALE, options);
}

export function formatRelativeTime(date: string): string {
    const rtf = new Intl.RelativeTimeFormat(LOCALE, { numeric: 'auto' });
    const now = new Date();
    const diff = new Date(date).getTime() - now.getTime();

    const seconds = Math.floor(diff / 1000);
    const minutes = Math.floor(seconds / 60);
    const hours = Math.floor(minutes / 60);
    const days = Math.floor(hours / 24);

    if (Math.abs(days) > 0) return rtf.format(days, 'day');
    if (Math.abs(hours) > 0) return rtf.format(hours, 'hour');
    if (Math.abs(minutes) > 0) return rtf.format(minutes, 'minute');
    return rtf.format(seconds, 'second');
}
