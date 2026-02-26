import { cn } from '@/lib/utils';
import * as React from 'react';

// Plain <hr>/<div> wrapper — removes @radix-ui/react-separator with zero API change.
// The Radix Separator adds role="separator" and aria-orientation which we replicate manually.

interface SeparatorProps extends React.ComponentProps<'div'> {
    orientation?: 'horizontal' | 'vertical';
    decorative?: boolean;
}

function Separator({
    className,
    orientation = 'horizontal',
    decorative = true,
    ...props
}: Readonly<SeparatorProps>) {
    return (
        <div
            data-slot="separator-root"
            role={decorative ? 'none' : 'separator'}
            aria-orientation={decorative ? undefined : orientation}
            className={cn(
                'bg-border shrink-0',
                orientation === 'horizontal' ? 'h-px w-full' : 'h-full w-px',
                className
            )}
            {...props}
        />
    );
}

export { Separator };
