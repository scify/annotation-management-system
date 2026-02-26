import { toggleVariants } from '@/components/ui/toggle';
import { cn } from '@/lib/utils';
import { type VariantProps } from 'class-variance-authority';
import * as React from 'react';
import { ToggleButton, ToggleButtonGroup, composeRenderProps } from 'react-aria-components';

// Context lets ToggleGroupItem inherit variant/size from the parent group —
// matching the original Radix implementation's cascading behaviour.

const ToggleGroupContext = React.createContext<VariantProps<typeof toggleVariants>>({
    size: 'default',
    variant: 'default',
});

function ToggleGroup({
    className,
    variant,
    size,
    children,
    ...props
}: React.ComponentProps<typeof ToggleButtonGroup> & VariantProps<typeof toggleVariants>) {
    const contextValue = React.useMemo(() => ({ variant, size }), [variant, size]);

    return (
        <ToggleGroupContext.Provider value={contextValue}>
            <ToggleButtonGroup
                data-slot="toggle-group"
                data-variant={variant}
                data-size={size}
                className={composeRenderProps(className, (cls) =>
                    cn(
                        'group/toggle-group flex items-center rounded-md data-[variant=outline]:shadow-xs',
                        cls
                    )
                )}
                {...props}
            >
                {children}
            </ToggleButtonGroup>
        </ToggleGroupContext.Provider>
    );
}

function ToggleGroupItem({
    className,
    children,
    variant,
    size,
    ...props
}: React.ComponentProps<typeof ToggleButton> & VariantProps<typeof toggleVariants>) {
    const context = React.useContext(ToggleGroupContext);

    return (
        <ToggleButton
            data-slot="toggle-group-item"
            data-variant={context.variant ?? variant}
            data-size={context.size ?? size}
            className={composeRenderProps(className, (cls) =>
                cn(
                    toggleVariants({
                        variant: context.variant ?? variant,
                        size: context.size ?? size,
                    }),
                    'min-w-0 shrink-0 rounded-none shadow-none first:rounded-l-md last:rounded-r-md focus:z-10 focus-visible:z-10 data-[variant=outline]:border-l-0 data-[variant=outline]:first:border-l',
                    cls
                )
            )}
            {...props}
        >
            {children}
        </ToggleButton>
    );
}

export { ToggleGroup, ToggleGroupItem };
