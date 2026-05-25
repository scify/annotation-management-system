import { useTranslations } from '@/hooks/use-translations';
import { cn } from '@/lib/utils';

interface StatusBadgeProps {
    status: 'active' | 'inactive';
}

export function StatusBadge({ status }: StatusBadgeProps) {
    const { t } = useTranslations();

    return (
        <span
            className={cn(
                'inline-flex h-[22px] items-center rounded border px-2 py-px text-xs font-semibold',
                status === 'active'
                    ? 'border-green-500 bg-green-50 text-green-600'
                    : 'border-red-700 bg-red-50 text-red-700'
            )}
        >
            {status === 'active' ? t('users.status.active') : t('users.status.inactive')}
        </span>
    );
}
