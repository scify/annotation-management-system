import { cn } from '@/lib/utils';
import { CheckIcon } from 'lucide-react';
import * as React from 'react';

// Styled native checkbox — maintains the same API as the previous Radix implementation
// while removing the @radix-ui/react-checkbox dependency.
// Uses a visually hidden <input> + a sibling indicator span for full browser compatibility.
// Tailwind's `peer-checked:[&_svg]:opacity-100` targets descendent SVGs of a peer sibling —
// see https://tailwindcss.com/docs/hover-focus-and-other-states#using-arbitrary-variants

interface CheckboxProps extends Omit<React.ComponentProps<'input'>, 'type' | 'size'> {
    /** Controlled checked state (mirrors Radix API). */
    checked?: boolean;
    /** Uncontrolled default state. */
    defaultChecked?: boolean;
    /** Called when the checked state changes (mirrors Radix onCheckedChange). */
    onCheckedChange?: (checked: boolean) => void;
}

function Checkbox({
    className,
    checked,
    defaultChecked,
    onCheckedChange,
    onChange,
    ...props
}: Readonly<CheckboxProps>) {
    const handleChange = (e: React.ChangeEvent<HTMLInputElement>) => {
        onChange?.(e);
        onCheckedChange?.(e.target.checked);
    };

    return (
        <span className="relative inline-flex size-4 shrink-0">
            <input
                type="checkbox"
                data-slot="checkbox"
                checked={checked}
                defaultChecked={defaultChecked}
                onChange={handleChange}
                // peer + sr-only: invisible but still focusable/clickable
                className={cn('peer sr-only', className)}
                {...props}
            />
            {/* Visual indicator — peer-checked:* applies when the sibling input is checked.
                peer-checked:[&_svg]:opacity-100 targets the SVG descendant via Tailwind's
                arbitrary modifier, generating: .peer:checked ~ .this-element svg { opacity:1 } */}
            <span
                aria-hidden="true"
                className={cn(
                    'pointer-events-none flex size-4 items-center justify-center rounded-[4px] border border-input shadow-xs transition-shadow',
                    'peer-checked:bg-primary peer-checked:text-primary-foreground peer-checked:border-primary',
                    'peer-checked:[&_svg]:opacity-100',
                    'peer-focus-visible:border-ring peer-focus-visible:ring-ring/50 peer-focus-visible:ring-[3px]',
                    'peer-disabled:cursor-not-allowed peer-disabled:opacity-50',
                    'peer-aria-invalid:ring-destructive/20 dark:peer-aria-invalid:ring-destructive/40 peer-aria-invalid:border-destructive'
                )}
            >
                <CheckIcon className="size-3.5 opacity-0 transition-opacity" />
            </span>
        </span>
    );
}

export { Checkbox };
