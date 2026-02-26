import { cn } from '@/lib/utils';
import { cva, type VariantProps } from 'class-variance-authority';
import * as React from 'react';
import { Button as AriaButton, composeRenderProps } from 'react-aria-components';

const buttonVariants = cva(
    "inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-md text-sm font-medium transition-[color,box-shadow] disabled:pointer-events-none disabled:opacity-50 [&_svg]:pointer-events-none [&_svg:not([class*='size-'])]:size-4 [&_svg]:shrink-0 outline-none focus-visible:border-ring focus-visible:ring-ring/50 focus-visible:ring-[3px] aria-invalid:ring-destructive/20 dark:aria-invalid:ring-destructive/40 aria-invalid:border-destructive hover:cursor-pointer",
    {
        variants: {
            variant: {
                default: 'bg-primary text-primary-foreground shadow-xs hover:bg-primary/90',
                destructive:
                    'bg-destructive text-white shadow-xs hover:bg-destructive/90 focus-visible:ring-destructive/20 dark:focus-visible:ring-destructive/40',
                outline:
                    'border border-input bg-background shadow-xs hover:bg-accent hover:text-accent-foreground',
                secondary: 'bg-secondary text-secondary-foreground shadow-xs hover:bg-secondary/80',
                ghost: 'hover:bg-accent hover:text-accent-foreground',
                link: 'text-primary underline-offset-4 hover:underline',
            },
            size: {
                default: 'h-9 px-4 py-2 has-[>svg]:px-3',
                sm: 'h-8 rounded-md px-3 has-[>svg]:px-2.5',
                lg: 'h-10 rounded-md px-6 has-[>svg]:px-4',
                xl: 'h-12 rounded-md px-8 has-[>svg]:px-6 text-lg',
                icon: 'size-9',
            },
        },
        defaultVariants: {
            variant: 'default',
            size: 'default',
        },
    }
);

type ButtonProps = React.ComponentProps<typeof AriaButton> &
    VariantProps<typeof buttonVariants> & {
        /** Render the child element instead of a <button>, merging button styles onto it. */
        asChild?: boolean;
        /** HTML `disabled` alias — forwarded as React Aria's `isDisabled`. */
        disabled?: boolean;
    };

function Button({
    className,
    variant,
    size,
    asChild = false,
    children,
    disabled,
    isDisabled,
    ...props
}: ButtonProps) {
    // asChild: merge button styles onto the single child element (cloneElement shim).
    // This replaces the @radix-ui/react-slot Slot primitive.
    if (asChild) {
        const child = React.Children.only(children as React.ReactElement<{ className?: string }>);
        return React.cloneElement(child, {
            ...props,
            className: cn(buttonVariants({ variant, size }), className, child.props.className),
        } as object);
    }

    return (
        <AriaButton
            data-slot="button"
            isDisabled={disabled ?? isDisabled}
            className={composeRenderProps(className, (cls) =>
                cn(buttonVariants({ variant, size }), cls)
            )}
            {...props}
        >
            {children}
        </AriaButton>
    );
}

export { Button, buttonVariants };
