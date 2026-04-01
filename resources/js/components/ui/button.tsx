import { cn } from '@/lib/utils';
import { cva, type VariantProps } from 'class-variance-authority';
import * as React from 'react';
import { Button as AriaButton, composeRenderProps } from 'react-aria-components';

const buttonVariants = cva(
    "inline-flex items-center justify-center whitespace-nowrap rounded-lg text-sm font-semibold transition-[color,box-shadow] disabled:pointer-events-none [&_svg]:pointer-events-none [&_svg:not([class*='size-'])]:size-4 [&_svg]:shrink-0 outline-none focus-visible:border-ring focus-visible:ring-ring/50 focus-visible:ring-[3px] aria-invalid:ring-destructive/20 dark:aria-invalid:ring-destructive/40 aria-invalid:border-destructive hover:cursor-pointer",
    {
        variants: {
            variant: {
                default:
                    'bg-primary text-primary-foreground shadow-xs hover:bg-brand-blue-800 active:bg-brand-blue-800 disabled:bg-brand-blue-200 disabled:text-white disabled:opacity-100',
                destructive:
                    'bg-destructive text-white shadow-xs hover:bg-destructive/90 disabled:opacity-50 focus-visible:ring-destructive/20 dark:focus-visible:ring-destructive/40',
                outline:
                    'border border-input bg-background shadow-xs hover:bg-accent hover:text-accent-foreground disabled:opacity-50',
                secondary:
                    'bg-brand-yellow-300 text-brand-blue-900 shadow-xs hover:bg-brand-yellow-400 active:bg-brand-yellow-400 disabled:bg-brand-yellow-200 disabled:text-brand-blue-300 disabled:opacity-100',
                ghost: 'hover:bg-accent hover:text-accent-foreground disabled:opacity-50',
                link: 'text-primary underline-offset-4 hover:underline disabled:opacity-50',
            },
            size: {
                default: 'h-9 gap-1 px-3.5',
                sm: 'h-[30px] gap-0.5 px-3.5 text-sm',
                lg: 'h-10 gap-1 px-3.5 text-base',
                xl: 'h-12 gap-2 px-8 text-lg',
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
