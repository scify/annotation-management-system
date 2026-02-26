import { cn } from '@/lib/utils';
import { ChevronRight, MoreHorizontal } from 'lucide-react';
import * as React from 'react';

// Breadcrumb — removes @radix-ui/react-slot from BreadcrumbLink.
// asChild is replaced with the same cloneElement shim used in Button/Badge.

function Breadcrumb({ ...props }: Readonly<React.ComponentProps<'nav'>>) {
    return <nav aria-label="breadcrumb" data-slot="breadcrumb" {...props} />;
}

function BreadcrumbList({ className, ...props }: Readonly<React.ComponentProps<'ol'>>) {
    return (
        <ol
            data-slot="breadcrumb-list"
            className={cn(
                'text-muted-foreground flex flex-wrap items-center gap-1.5 text-sm break-words sm:gap-2.5',
                className
            )}
            {...props}
        />
    );
}

function BreadcrumbItem({ className, ...props }: Readonly<React.ComponentProps<'li'>>) {
    return (
        <li
            data-slot="breadcrumb-item"
            className={cn('inline-flex items-center gap-1.5', className)}
            {...props}
        />
    );
}

function BreadcrumbLink({
    asChild,
    className,
    children,
    ...props
}: Readonly<
    React.ComponentProps<'a'> & {
        asChild?: boolean;
    }
>) {
    if (asChild) {
        const child = React.Children.only(children as React.ReactElement<{ className?: string }>);
        return React.cloneElement(child, {
            ...props,
            className: cn('hover:text-foreground transition-colors', child.props.className, className),
        } as object);
    }

    return (
        <a
            data-slot="breadcrumb-link"
            className={cn('hover:text-foreground transition-colors', className)}
            {...props}
        >
            {children}
        </a>
    );
}

function BreadcrumbPage({ className, ...props }: Readonly<React.ComponentProps<'span'>>) {
    return (
        <span
            data-slot="breadcrumb-page"
            aria-current="page"
            className={cn('text-foreground font-normal', className)}
            {...props}
        />
    );
}

function BreadcrumbSeparator({
    children,
    className,
    ...props
}: Readonly<React.ComponentProps<'li'>>) {
    return (
        <li
            data-slot="breadcrumb-separator"
            role="presentation"
            aria-hidden="true"
            className={cn('[&>svg]:size-3.5', className)}
            {...props}
        >
            {children ?? <ChevronRight />}
        </li>
    );
}

function BreadcrumbEllipsis({ className, ...props }: Readonly<React.ComponentProps<'span'>>) {
    return (
        <span
            data-slot="breadcrumb-ellipsis"
            role="presentation"
            aria-hidden="true"
            className={cn('flex size-9 items-center justify-center', className)}
            {...props}
        >
            <MoreHorizontal className="size-4" />
            <span className="sr-only">More</span>
        </span>
    );
}

export {
    Breadcrumb,
    BreadcrumbEllipsis,
    BreadcrumbItem,
    BreadcrumbLink,
    BreadcrumbList,
    BreadcrumbPage,
    BreadcrumbSeparator,
};
