import { cn } from '@/lib/utils';
import * as React from 'react';
import {
    Radio as AriaRadio,
    RadioGroup as AriaRadioGroup,
    type RadioGroupProps as AriaRadioGroupProps,
    type RadioProps as AriaRadioProps,
    composeRenderProps,
} from 'react-aria-components';

// RadioGroup — thin wrapper over React Aria's RadioGroup/Radio.
// API adapter: maps to Radix-style names (value, onValueChange) so consumers
// mirror the Select component's ergonomics.

interface RadioGroupProps extends Omit<AriaRadioGroupProps, 'value' | 'onChange'> {
    /** Controlled selected value. */
    value?: string | null;
    onValueChange?: (value: string) => void;
}

function RadioGroup({
    value,
    onValueChange,
    className,
    children,
    ...props
}: Readonly<RadioGroupProps>) {
    return (
        <AriaRadioGroup
            data-slot="radio-group"
            value={value ?? null}
            onChange={(next) => onValueChange?.(next)}
            className={composeRenderProps(className, (cls) => cn('flex gap-4', cls))}
            {...props}
        >
            {children}
        </AriaRadioGroup>
    );
}

function RadioGroupItem({ className, children, ...props }: Readonly<AriaRadioProps>) {
    return (
        <AriaRadio
            data-slot="radio-group-item"
            className={composeRenderProps(className, (cls) =>
                cn(
                    'flex cursor-pointer items-center gap-2 text-sm font-medium text-slate-800 outline-none',
                    cls
                )
            )}
            {...props}
        >
            {(renderProps) => (
                <>
                    <span
                        className={cn(
                            'flex size-4 shrink-0 items-center justify-center rounded-full border bg-white transition-colors',
                            renderProps.isSelected ? 'border-brand-blue-700' : 'border-slate-300',
                            renderProps.isFocusVisible &&
                                'ring-brand-blue-700 ring-2 ring-offset-2'
                        )}
                    >
                        {renderProps.isSelected && (
                            <span className="bg-brand-blue-700 size-2 rounded-full" />
                        )}
                    </span>
                    {typeof children === 'function' ? children(renderProps) : children}
                </>
            )}
        </AriaRadio>
    );
}

export { RadioGroup, RadioGroupItem };
