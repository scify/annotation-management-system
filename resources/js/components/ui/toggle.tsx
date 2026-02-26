import { cn } from '@/lib/utils';
import { cva, type VariantProps } from 'class-variance-authority';
import * as React from 'react';
import { ToggleButton, composeRenderProps } from 'react-aria-components';

// React Aria's ToggleButton uses data-selected when pressed/active.
// Tailwind class changed from data-[state=on]: to data-[selected]: to match.

const toggleVariants = cva(
    "inline-flex items-center justify-center gap-2 rounded-md text-sm font-medium hover:bg-muted hover:text-muted-foreground disabled:pointer-events-none disabled:opacity-50 data-[selected]:bg-accent data-[selected]:text-accent-foreground [&_svg]:pointer-events-none [&_svg:not([class*='size-'])]:size-4 [&_svg]:shrink-0 focus-visible:border-ring focus-visible:ring-ring/50 focus-visible:ring-[3px] outline-none transition-[color,box-shadow] aria-invalid:ring-destructive/20 dark:aria-invalid:ring-destructive/40 aria-invalid:border-destructive",
    {
        variants: {
            variant: {
                default: 'bg-transparent',
                outline:
                    'border border-input bg-transparent shadow-xs hover:bg-accent hover:text-accent-foreground',
            },
            size: {
                default: 'h-9 px-2 min-w-9',
                sm: 'h-8 px-1.5 min-w-8',
                lg: 'h-10 px-2.5 min-w-10',
            },
        },
        defaultVariants: {
            variant: 'default',
            size: 'default',
        },
    }
);

function Toggle({
    className,
    variant,
    size,
    ...props
}: React.ComponentProps<typeof ToggleButton> & VariantProps<typeof toggleVariants>) {
    return (
        <ToggleButton
            data-slot="toggle"
            className={composeRenderProps(className, (cls) =>
                cn(toggleVariants({ variant, size }), cls)
            )}
            {...props}
        />
    );
}

export { Toggle, toggleVariants };
