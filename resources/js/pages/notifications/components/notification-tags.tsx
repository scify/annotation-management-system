import { Tag } from '@/components/ui/tag';
import { useTranslations } from '@/hooks/use-translations';
import { cn } from '@/lib/utils';
import type { NotificationSenderRole, NotificationThreadType } from '../types';

const SENDER_ROLE_STYLES: Record<NotificationSenderRole, string> = {
    annotator: 'border-brand-blue-300 bg-brand-blue-50 text-brand-blue-600',
    manager: 'border-sky-300 bg-sky-50 text-sky-600',
    owner: 'border-purple-300 bg-purple-50 text-purple-600',
};

/**
 * The backend currently sends the sender's *global* role; the design wants the
 * *project-contextual* role (annotator/manager/owner). Best-effort map until the
 * backend exposes the contextual role — see `tasks/notifications-backend-gaps.md`.
 */
function normalizeSenderRole(role: string): NotificationSenderRole | null {
    switch (role) {
        case 'annotator':
            return 'annotator';
        case 'annotation-manager':
        case 'manager':
            return 'manager';
        case 'owner':
            return 'owner';
        default:
            return null;
    }
}

interface SenderRoleTagProps {
    role: string;
}

export function SenderRoleTag({ role }: SenderRoleTagProps) {
    const { t } = useTranslations();
    const displayRole = normalizeSenderRole(role);

    if (!displayRole) return null;

    return (
        <span
            className={cn(
                'inline-flex h-[22px] shrink-0 items-center rounded border px-2 py-px text-xs font-semibold',
                SENDER_ROLE_STYLES[displayRole]
            )}
        >
            {t(`notifications.roles.${displayRole}`)}
        </span>
    );
}

const SUBJECT_TAG_STYLES: Record<NotificationThreadType, string> = {
    flag_notification: 'bg-brand-red-200',
    instance_related: 'bg-brand-blue-100',
    generic: 'bg-brand-blue-100',
    info: 'bg-yellow-200',
    warning: 'bg-brand-red-200',
    project_ownership: 'bg-yellow-200',
    project_invitation: 'bg-yellow-200',
    announcement: 'bg-purple-200',
};

interface SubjectTagProps {
    type: NotificationThreadType;
    label: string;
}

export function SubjectTag({ type, label }: SubjectTagProps) {
    return (
        <Tag className={cn('h-[26px] shrink-0 rounded-lg text-xs', SUBJECT_TAG_STYLES[type])}>
            {label}
        </Tag>
    );
}
