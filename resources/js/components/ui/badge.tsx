import { cn } from '@/lib/utils';
import { cva, type VariantProps } from 'class-variance-authority';
import * as React from 'react';

// Badge — removes @radix-ui/react-slot; uses the same cloneElement asChild shim
// as Button so the API surface is unchanged.

const badgeVariants = cva(
    'inline-flex items-center justify-center rounded-md border px-2 py-0.5 text-xs font-medium w-fit whitespace-nowrap shrink-0 [&>svg]:size-3 gap-1 [&>svg]:pointer-events-none focus-visible:border-ring focus-visible:ring-ring/50 focus-visible:ring-[3px] aria-invalid:ring-destructive/20 dark:aria-invalid:ring-destructive/40 aria-invalid:border-destructive transition-[color,box-shadow] overflow-auto',
    {
        variants: {
            variant: {
                default:
                    'border-transparent bg-primary text-primary-foreground [a&]:hover:bg-primary/90',
                secondary:
                    'border-transparent bg-secondary text-secondary-foreground [a&]:hover:bg-secondary/90',
                destructive:
                    'border-transparent bg-destructive text-white [a&]:hover:bg-destructive/90 focus-visible:ring-destructive/20 dark:focus-visible:ring-destructive/40',
                outline: 'text-foreground [a&]:hover:bg-accent [a&]:hover:text-accent-foreground',
            },
        },
        defaultVariants: {
            variant: 'default',
        },
    }
);

function Badge({
    className,
    variant,
    asChild = false,
    children,
    ...props
}: Readonly<
    React.ComponentProps<'span'> & VariantProps<typeof badgeVariants> & { asChild?: boolean }
>) {
    if (asChild) {
        const child = React.Children.only(children as React.ReactElement<{ className?: string }>);
        return React.cloneElement(child, {
            className: cn(badgeVariants({ variant }), className, child.props.className),
        } as object);
    }

    return (
        <span
            data-slot="badge"
            className={cn(badgeVariants({ variant }), className)}
            {...props}
        >
            {children}
        </span>
    );
}

export { Badge, badgeVariants };
