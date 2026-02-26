import { cn } from '@/lib/utils';
import * as React from 'react';
import {
    OverlayArrow,
    Tooltip as AriaTooltip,
    TooltipTrigger as AriaTooltipTrigger,
    composeRenderProps,
    type TooltipProps as AriaTooltipProps,
} from 'react-aria-components';

// React Aria Tooltip.
// TooltipProvider is kept as a no-op wrapper for API compatibility — React Aria's
// TooltipTrigger manages its own delay context so no global provider is needed.

function TooltipProvider({
    children,
}: Readonly<{ children?: React.ReactNode; delayDuration?: number; skipDelayDuration?: number }>) {
    return <>{children}</>;
}

// Tooltip wraps AriaTooltipTrigger so the existing pattern
//   <Tooltip><TooltipTrigger>…</TooltipTrigger><TooltipContent>…</TooltipContent></Tooltip>
// keeps working without consumer changes.
function Tooltip({
    delay = 0,
    children,
    ...props
}: React.ComponentProps<typeof AriaTooltipTrigger>) {
    return (
        <AriaTooltipTrigger delay={delay} {...props}>
            {children}
        </AriaTooltipTrigger>
    );
}

function TooltipTrigger({
    children,
    ...props
}: Readonly<React.ComponentProps<'span'>>) {
    // The trigger must be a React Aria-compatible interactive element.
    // We forward all props onto the child if it's a single element, otherwise wrap in span.
    if (React.isValidElement(children)) {
        return React.cloneElement(children as React.ReactElement<object>, props);
    }
    return <span {...props}>{children}</span>;
}

function TooltipContent({
    className,
    sideOffset: _sideOffset = 4,
    children,
    ...props
}: Omit<AriaTooltipProps, 'children'> & { sideOffset?: number; className?: string; children?: React.ReactNode }) {
    return (
        <AriaTooltip
            offset={_sideOffset}
            className={composeRenderProps(className, (cls) =>
                cn(
                    'bg-primary text-primary-foreground z-50 max-w-sm rounded-md px-3 py-1.5 text-xs',
                    'data-[entering]:animate-in data-[entering]:fade-in-0 data-[entering]:zoom-in-95',
                    'data-[exiting]:animate-out data-[exiting]:fade-out-0 data-[exiting]:zoom-out-95',
                    'data-[placement=bottom]:slide-in-from-top-2 data-[placement=left]:slide-in-from-right-2 data-[placement=right]:slide-in-from-left-2 data-[placement=top]:slide-in-from-bottom-2',
                    cls
                )
            )}
            {...props}
        >
            <OverlayArrow>
                <svg
                    width={8}
                    height={8}
                    viewBox="0 0 8 8"
                    className="fill-primary"
                    aria-hidden="true"
                >
                    <path d="M0 0 L4 4 L8 0" />
                </svg>
            </OverlayArrow>
            {children}
        </AriaTooltip>
    );
}

export { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger };
