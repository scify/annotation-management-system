import { cn } from '@/lib/utils';

interface TagProps {
    children: React.ReactNode;
    className?: string;
}

export function Tag({ children, className }: TagProps) {
    return (
        <span
            className={cn(
                'bg-brand-blue-100 flex h-8 items-center rounded-md px-[10px] text-sm font-medium text-slate-800',
                className,
            )}
        >
            {children}
        </span>
    );
}
