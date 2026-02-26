import { cn } from '@/lib/utils';
import { CheckIcon, ChevronDownIcon, ChevronUpIcon } from 'lucide-react';
import * as React from 'react';
import {
    Button as AriaButton,
    ListBox,
    ListBoxItem,
    type ListBoxItemProps,
    Popover,
    Select as AriaSelect,
    SelectValue as AriaSelectValue,
    type SelectProps as AriaSelectProps,
    composeRenderProps,
} from 'react-aria-components';

// Select — replaces @radix-ui/react-select with React Aria.
// API adapter: maps Radix prop names (value, onValueChange) to React Aria names
// (selectedKey, onSelectionChange) so consumers need zero changes.

// ── Root ──────────────────────────────────────────────────────────────────────

interface SelectProps<T extends object>
    extends Omit<AriaSelectProps<T>, 'selectedKey' | 'onSelectionChange' | 'defaultSelectedKey'> {
    /** Controlled selected value (Radix compat). */
    value?: string;
    /** Called with the new value string on selection (Radix compat). */
    onValueChange?: (value: string) => void;
    defaultValue?: string;
}

function Select<T extends object>({
    value,
    onValueChange,
    defaultValue,
    children,
    ...props
}: Readonly<SelectProps<T>>) {
    return (
        <AriaSelect
            data-slot="select"
            selectedKey={value}
            onSelectionChange={(key) => onValueChange?.(String(key))}
            defaultSelectedKey={defaultValue}
            {...props}
        >
            {children}
        </AriaSelect>
    );
}

// ── Group (no-op wrapper for API compat) ──────────────────────────────────────

function SelectGroup({ children, ...props }: Readonly<React.ComponentProps<'div'>>) {
    return (
        <div data-slot="select-group" {...props}>
            {children}
        </div>
    );
}

// ── Value ─────────────────────────────────────────────────────────────────────

function SelectValue({
    placeholder,
    className,
    ...props
}: Readonly<React.ComponentProps<typeof AriaSelectValue> & { placeholder?: string }>) {
    return (
        <AriaSelectValue
            data-slot="select-value"
            className={cn('data-[placeholder]:text-muted-foreground', className)}
            {...props}
        >
            {({ selectedText }) => selectedText ?? placeholder}
        </AriaSelectValue>
    );
}

// ── Trigger ───────────────────────────────────────────────────────────────────

function SelectTrigger({
    className,
    children,
    ...props
}: Readonly<Omit<React.ComponentProps<typeof AriaButton>, 'children'> & { children?: React.ReactNode }>) {
    return (
        <AriaButton
            data-slot="select-trigger"
            className={composeRenderProps(className, (cls) =>
                cn(
                    "border-input data-[placeholder]:text-muted-foreground [&_svg:not([class*='text-'])]:text-muted-foreground focus-visible:border-ring focus-visible:ring-ring/50 aria-invalid:ring-destructive/20 dark:aria-invalid:ring-destructive/40 aria-invalid:border-destructive flex h-9 w-full items-center justify-between rounded-md border bg-transparent px-3 py-2 text-sm shadow-xs transition-[color,box-shadow] outline-none focus-visible:ring-[3px] disabled:cursor-not-allowed disabled:opacity-50 [&_svg]:pointer-events-none [&_svg]:shrink-0 [&_svg:not([class*='size-'])]:size-4 [&>span]:line-clamp-1",
                    cls
                )
            )}
            {...props}
        >
            {children}
            <ChevronDownIcon className="size-4 opacity-50" aria-hidden="true" />
        </AriaButton>
    );
}

// ── Content (Popover + ListBox) ───────────────────────────────────────────────

interface SelectContentProps {
    className?: string;
    children?: React.ReactNode;
    position?: 'popper' | 'item-aligned';
}

function SelectContent({ className, children, position = 'popper' }: Readonly<SelectContentProps>) {
    return (
        <Popover
            data-slot="select-content"
            className={cn(
                'bg-popover text-popover-foreground relative z-50 max-h-96 min-w-[8rem] overflow-hidden rounded-md border shadow-md',
                'data-[entering]:animate-in data-[entering]:fade-in-0 data-[entering]:zoom-in-95',
                'data-[exiting]:animate-out data-[exiting]:fade-out-0 data-[exiting]:zoom-out-95',
                'data-[placement=bottom]:slide-in-from-top-2 data-[placement=left]:slide-in-from-right-2 data-[placement=right]:slide-in-from-left-2 data-[placement=top]:slide-in-from-bottom-2',
                position === 'popper' &&
                    'data-[placement=bottom]:translate-y-1 data-[placement=left]:-translate-x-1 data-[placement=right]:translate-x-1 data-[placement=top]:-translate-y-1',
                className
            )}
        >
            <ListBox className="p-1 outline-none">{children}</ListBox>
        </Popover>
    );
}

// ── Label ─────────────────────────────────────────────────────────────────────

function SelectLabel({ className, ...props }: Readonly<React.ComponentProps<'span'>>) {
    return (
        <span
            data-slot="select-label"
            className={cn('px-2 py-1.5 text-sm font-medium', className)}
            {...props}
        />
    );
}

// ── Item ──────────────────────────────────────────────────────────────────────

function SelectItem({
    className,
    children,
    value,
    ...props
}: Readonly<Omit<ListBoxItemProps, 'value'> & { value?: string; className?: string; children?: React.ReactNode }>) {
    return (
        <ListBoxItem
            data-slot="select-item"
            id={value}
            textValue={typeof children === 'string' ? children : undefined}
            className={composeRenderProps(className, (cls) =>
                cn(
                    "focus:bg-accent focus:text-accent-foreground [&_svg:not([class*='text-'])]:text-muted-foreground relative flex w-full cursor-default items-center gap-2 rounded-sm py-1.5 pr-8 pl-2 text-sm outline-hidden select-none data-[disabled]:pointer-events-none data-[disabled]:opacity-50 [&_svg]:pointer-events-none [&_svg]:shrink-0 [&_svg:not([class*='size-'])]:size-4",
                    cls
                )
            )}
            {...props}
        >
            {({ isSelected }) => (
                <>
                    <span className="absolute right-2 flex size-3.5 items-center justify-center">
                        {isSelected && <CheckIcon className="size-4" />}
                    </span>
                    {children}
                </>
            )}
        </ListBoxItem>
    );
}

// ── Separator ─────────────────────────────────────────────────────────────────

function SelectSeparator({ className, ...props }: Readonly<React.ComponentProps<'div'>>) {
    return (
        <div
            data-slot="select-separator"
            className={cn('bg-border pointer-events-none -mx-1 my-1 h-px', className)}
            {...props}
        />
    );
}

// ── Scroll buttons (no-ops; React Aria's ListBox handles scroll natively) ──────

function SelectScrollUpButton({ className, ...props }: Readonly<React.ComponentProps<'div'>>) {
    return (
        <div
            data-slot="select-scroll-up-button"
            className={cn('flex cursor-default items-center justify-center py-1', className)}
            {...props}
        >
            <ChevronUpIcon className="size-4" />
        </div>
    );
}

function SelectScrollDownButton({ className, ...props }: Readonly<React.ComponentProps<'div'>>) {
    return (
        <div
            data-slot="select-scroll-down-button"
            className={cn('flex cursor-default items-center justify-center py-1', className)}
            {...props}
        >
            <ChevronDownIcon className="size-4" />
        </div>
    );
}

export {
    Select,
    SelectContent,
    SelectGroup,
    SelectItem,
    SelectLabel,
    SelectScrollDownButton,
    SelectScrollUpButton,
    SelectSeparator,
    SelectTrigger,
    SelectValue,
};
