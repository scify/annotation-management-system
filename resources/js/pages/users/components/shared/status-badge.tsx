import { useTranslations } from '@/hooks/use-translations';
import { cn } from '@/lib/utils';

type StatusBadgeStatus = 'active' | 'inactive' | 'pending';

interface StatusBadgeProps {
    status: StatusBadgeStatus;
}

const styles: Record<StatusBadgeStatus, string> = {
    active: 'border-green-500 bg-green-50 text-green-600',
    inactive: 'border-red-700 bg-red-50 text-red-700',
    pending: 'border-slate-400 bg-slate-100 text-slate-500',
};

export function StatusBadge({ status }: StatusBadgeProps) {
    const { t } = useTranslations();

    return (
        <span
            className={cn(
                'inline-flex h-[22px] items-center rounded border px-2 py-px text-xs font-semibold',
                styles[status]
            )}
        >
            {t(`users.status.${status}`)}
        </span>
    );
}
