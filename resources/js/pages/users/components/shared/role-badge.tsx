import { useTranslations } from '@/hooks/use-translations';
import { cn } from '@/lib/utils';
import { RolesEnum } from '@/types';

interface RoleBadgeProps {
    role: RolesEnum;
}

const ROLE_STYLES: Record<RolesEnum, string> = {
    [RolesEnum.ANNOTATION_MANAGER]: 'border-sky-300 bg-sky-50 text-sky-600',
    [RolesEnum.ADMIN]: 'border-fuchsia-300 bg-fuchsia-50 text-fuchsia-600',
    [RolesEnum.ANNOTATOR]: 'border-brand-blue-300 bg-brand-blue-50 text-brand-blue-600',
};

export function RoleBadge({ role }: RoleBadgeProps) {
    const { t } = useTranslations();

    return (
        <span
            className={cn(
                'inline-flex h-[22px] items-center rounded border px-2 py-px text-xs font-semibold',
                ROLE_STYLES[role]
            )}
        >
            {t(`roles.${role}`)}
        </span>
    );
}
