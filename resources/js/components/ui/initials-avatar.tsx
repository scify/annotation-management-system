import { cn } from '@/lib/utils';
import { Avatar, AvatarFallback } from './avatar';

const SIZE = {
    sm: { avatar: 'size-[22px]', text: 'text-[10px]' },
    md: { avatar: 'size-[29px]', text: 'text-xs' },
} as const;

const VARIANT = {
    default: 'bg-brand-blue-300',
    admin: 'bg-brand-blue-700',
} as const;

interface InitialsAvatarProps {
    initials: string;
    size?: keyof typeof SIZE;
    variant?: keyof typeof VARIANT;
}

export function InitialsAvatar({ initials, size = 'md', variant = 'default' }: InitialsAvatarProps) {
    return (
        <Avatar className={cn('shrink-0', SIZE[size].avatar)}>
            <AvatarFallback
                className={cn('rounded-full font-semibold text-white', VARIANT[variant], SIZE[size].text)}
            >
                {initials}
            </AvatarFallback>
        </Avatar>
    );
}
