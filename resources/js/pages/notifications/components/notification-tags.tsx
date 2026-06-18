import { Tag } from '@/components/ui/tag';
import { cn } from '@/lib/utils';
import { RolesEnum } from '@/types';
import { RoleBadge } from '@/pages/users/components/shared/role-badge';
import type { NotificationThreadType } from '../types';

const KNOWN_ROLES = Object.values(RolesEnum);

interface SenderRoleTagProps {
    /** Raw global role from the backend (`admin` / `annotation-manager` / `annotator`). */
    role: string;
}

export function SenderRoleTag({ role }: SenderRoleTagProps) {
    if (!KNOWN_ROLES.includes(role as RolesEnum)) return null;

    return <RoleBadge role={role as RolesEnum} />;
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
