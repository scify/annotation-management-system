import { buttonVariants } from '@/components/ui/button';
import { cn } from '@/lib/utils';
import { type VariantProps } from 'class-variance-authority';
import * as React from 'react';
import { Link as AriaLink, type LinkProps as AriaLinkProps } from 'react-aria-components';

export interface LinkProps extends AriaLinkProps, VariantProps<typeof buttonVariants> {
    className?: string;
}

/**
 * Link component backed by React Aria.
 * Uses RouterProvider (wired to Inertia's router in app.tsx/ssr.tsx) for internal navigation.
 *
 * With `variant` prop → renders styled as a button (replaces `<Button asChild><InertiaLink>`).
 * Without `variant` → renders as a plain accessible anchor.
 */
function Link({ className, variant, size, ...props }: Readonly<LinkProps>) {
    return (
        <AriaLink
            className={cn(variant ? buttonVariants({ variant, size }) : '', className)}
            {...props}
        />
    );
}

export { Link };
