import { cn } from '@/lib/utils';
import { CheckIcon, ChevronRightIcon, CircleIcon } from 'lucide-react';
import * as React from 'react';
import {
    Button as AriaButton,
    Menu,
    MenuItem,
    type MenuItemProps,
    MenuTrigger,
    Popover,
    Separator,
    composeRenderProps,
} from 'react-aria-components';

// DropdownMenu — replaces @radix-ui/react-dropdown-menu with React Aria.
//
// Design:
//   • DropdownMenu         → MenuTrigger (manages open/close state)
//   • DropdownMenuTrigger  → first child of MenuTrigger (AriaButton or asChild clone)
//   • DropdownMenuContent  → Popover + Menu
//   • DropdownMenuItem     → MenuItem (onAction = Radix onSelect compat)
//   • DropdownMenuLabel    → disabled MenuItem styled as a header
//   • DropdownMenuGroup    → Section wrapper
//   • DropdownMenuSeparator → Separator
//
// DropdownMenuTrigger asChild: the child element is passed straight through;
// React Aria's MenuTrigger wires its first AriaButton child as the trigger via
// ButtonContext, so our Button wrapper (which renders AriaButton) is picked up
// automatically without needing an explicit wrapper component.

// ── Root ──────────────────────────────────────────────────────────────────────

function DropdownMenu({ ...props }: Readonly<React.ComponentProps<typeof MenuTrigger>>) {
    return <MenuTrigger data-slot="dropdown-menu" {...props} />;
}

// ── Trigger ───────────────────────────────────────────────────────────────────

function DropdownMenuPortal({ children }: Readonly<{ children?: React.ReactNode }>) {
    return <>{children}</>;
}

function DropdownMenuTrigger({
    asChild,
    children,
    ...props
}: Readonly<React.ComponentProps<typeof AriaButton> & { asChild?: boolean }>) {
    // asChild: render the child directly — it must be (or render) an AriaButton
    // so MenuTrigger can pick it up via ButtonContext.
    if (asChild) {
        return <>{children}</>;
    }
    return (
        <AriaButton data-slot="dropdown-menu-trigger" {...props}>
            {children}
        </AriaButton>
    );
}

// ── Content ───────────────────────────────────────────────────────────────────

function DropdownMenuContent({
    className,
    sideOffset = 4,
    align,
    children,
    ...props
}: Readonly<
    React.ComponentProps<typeof Popover> & {
        sideOffset?: number;
        align?: 'start' | 'center' | 'end';
    }
>) {
    const placement =
        align === 'start' ? 'bottom start' : align === 'center' ? 'bottom' : 'bottom end';

    return (
        <Popover
            data-slot="dropdown-menu-content"
            offset={sideOffset}
            placement={placement}
            className={composeRenderProps(className, (cls) =>
                cn(
                    'bg-popover text-popover-foreground z-50 min-w-[8rem] overflow-hidden rounded-md border p-1 shadow-md',
                    'data-[entering]:animate-in data-[entering]:fade-in-0 data-[entering]:zoom-in-95',
                    'data-[exiting]:animate-out data-[exiting]:fade-out-0 data-[exiting]:zoom-out-95',
                    'data-[placement=bottom]:slide-in-from-top-2 data-[placement=left]:slide-in-from-right-2 data-[placement=right]:slide-in-from-left-2 data-[placement=top]:slide-in-from-bottom-2',
                    cls
                )
            )}
            {...props}
        >
            <Menu className="outline-none">{children}</Menu>
        </Popover>
    );
}

// ── Group (Section wrapper) ───────────────────────────────────────────────────

function DropdownMenuGroup({ children }: Readonly<React.ComponentProps<'div'>>) {
    // React Aria's Section requires specific children; wrap in a plain div
    // and let the items be direct children of the Menu via pass-through.
    return <>{children}</>;
}

// ── Item ──────────────────────────────────────────────────────────────────────

function DropdownMenuItem({
    className,
    inset,
    variant = 'default',
    onSelect,
    onAction,
    children,
    ...props
}: Readonly<
    MenuItemProps & {
        inset?: boolean;
        variant?: 'default' | 'destructive';
        /** Radix compat alias for onAction. */
        onSelect?: () => void;
        className?: string;
        children?: React.ReactNode;
    }
>) {
    return (
        <MenuItem
            data-slot="dropdown-menu-item"
            data-inset={inset}
            data-variant={variant}
            onAction={onAction ?? onSelect}
            className={composeRenderProps(className, (cls) =>
                cn(
                    "focus:bg-accent focus:text-accent-foreground data-[variant=destructive]:text-destructive-foreground data-[variant=destructive]:focus:bg-destructive/10 dark:data-[variant=destructive]:focus:bg-destructive/40 [&_svg:not([class*='text-'])]:text-muted-foreground relative flex cursor-default items-center gap-2 rounded-sm px-2 py-1.5 text-sm outline-hidden select-none data-[disabled]:pointer-events-none data-[disabled]:opacity-50 data-[inset]:pl-8 [&_svg]:pointer-events-none [&_svg]:shrink-0 [&_svg:not([class*='size-'])]:size-4 hover:cursor-pointer",
                    cls
                )
            )}
            {...props}
        >
            {children}
        </MenuItem>
    );
}

// ── Checkbox Item ─────────────────────────────────────────────────────────────

function DropdownMenuCheckboxItem({
    className,
    children,
    checked,
    onSelect,
    onAction,
    ...props
}: Readonly<
    MenuItemProps & {
        checked?: boolean;
        onSelect?: () => void;
        className?: string;
        children?: React.ReactNode;
    }
>) {
    return (
        <MenuItem
            data-slot="dropdown-menu-checkbox-item"
            onAction={onAction ?? onSelect}
            className={composeRenderProps(className, (cls) =>
                cn(
                    'focus:bg-accent focus:text-accent-foreground relative flex cursor-default items-center gap-2 rounded-sm py-1.5 pr-2 pl-8 text-sm outline-hidden select-none data-[disabled]:pointer-events-none data-[disabled]:opacity-50 [&_svg]:pointer-events-none [&_svg]:shrink-0 [&_svg:not([class*=size-])]:size-4',
                    cls
                )
            )}
            {...props}
        >
            <span className="pointer-events-none absolute left-2 flex size-3.5 items-center justify-center">
                {checked && <CheckIcon className="size-4" />}
            </span>
            {children}
        </MenuItem>
    );
}

// ── Radio Group / Item (simplified) ──────────────────────────────────────────

function DropdownMenuRadioGroup({ children }: Readonly<React.ComponentProps<'div'>>) {
    return <>{children}</>;
}

function DropdownMenuRadioItem({
    className,
    children,
    onSelect,
    onAction,
    ...props
}: Readonly<
    MenuItemProps & {
        onSelect?: () => void;
        className?: string;
        children?: React.ReactNode;
    }
>) {
    return (
        <MenuItem
            data-slot="dropdown-menu-radio-item"
            onAction={onAction ?? onSelect}
            className={composeRenderProps(className, (cls) =>
                cn(
                    'focus:bg-accent focus:text-accent-foreground relative flex cursor-default items-center gap-2 rounded-sm py-1.5 pr-2 pl-8 text-sm outline-hidden select-none data-[disabled]:pointer-events-none data-[disabled]:opacity-50 [&_svg]:pointer-events-none [&_svg]:shrink-0 [&_svg:not([class*=size-])]:size-4',
                    cls
                )
            )}
            {...props}
        >
            <span className="pointer-events-none absolute left-2 flex size-3.5 items-center justify-center">
                <CircleIcon className="size-2 fill-current" />
            </span>
            {children}
        </MenuItem>
    );
}

// ── Label (non-interactive header) ───────────────────────────────────────────

function DropdownMenuLabel({
    className,
    inset,
    children,
    ...props
}: Readonly<
    MenuItemProps & {
        inset?: boolean;
        className?: string;
        children?: React.ReactNode;
    }
>) {
    // Rendered as a disabled, pointer-events-none MenuItem acting as a visual label.
    // This keeps it inside the Menu collection (required by React Aria) while
    // preventing interaction. Consumers wanting section semantics should use
    // DropdownMenuGroup with a visible header instead.
    return (
        <MenuItem
            data-slot="dropdown-menu-label"
            data-inset={inset}
            isDisabled
            className={composeRenderProps(className, (cls) =>
                cn(
                    'px-2 py-1.5 text-sm font-medium pointer-events-none opacity-100 data-[inset]:pl-8',
                    cls
                )
            )}
            {...props}
        >
            {children}
        </MenuItem>
    );
}

// ── Separator ─────────────────────────────────────────────────────────────────

function DropdownMenuSeparator({ className, ...props }: Readonly<React.ComponentProps<typeof Separator>>) {
    return (
        <Separator
            data-slot="dropdown-menu-separator"
            className={cn('bg-border -mx-1 my-1 h-px', className)}
            {...props}
        />
    );
}

// ── Shortcut ──────────────────────────────────────────────────────────────────

function DropdownMenuShortcut({ className, ...props }: Readonly<React.ComponentProps<'span'>>) {
    return (
        <span
            data-slot="dropdown-menu-shortcut"
            className={cn('text-muted-foreground ml-auto text-xs tracking-widest', className)}
            {...props}
        />
    );
}

// ── Sub menu (simplified — no React Aria submenu support yet) ─────────────────

function DropdownMenuSub({ children }: Readonly<{ children?: React.ReactNode }>) {
    return <>{children}</>;
}

function DropdownMenuSubTrigger({
    className,
    inset,
    children,
    ...props
}: Readonly<React.ComponentProps<'button'> & { inset?: boolean }>) {
    return (
        <button
            data-slot="dropdown-menu-sub-trigger"
            data-inset={inset}
            className={cn(
                'focus:bg-accent focus:text-accent-foreground flex cursor-default items-center rounded-sm px-2 py-1.5 text-sm outline-hidden select-none data-[inset]:pl-8',
                className
            )}
            {...props}
        >
            {children}
            <ChevronRightIcon className="ml-auto size-4" />
        </button>
    );
}

function DropdownMenuSubContent({
    className,
    ...props
}: Readonly<React.ComponentProps<'div'>>) {
    return (
        <div
            data-slot="dropdown-menu-sub-content"
            className={cn(
                'bg-popover text-popover-foreground z-50 min-w-[8rem] overflow-hidden rounded-md border p-1 shadow-lg',
                className
            )}
            {...props}
        />
    );
}

export {
    DropdownMenu,
    DropdownMenuCheckboxItem,
    DropdownMenuContent,
    DropdownMenuGroup,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuPortal,
    DropdownMenuRadioGroup,
    DropdownMenuRadioItem,
    DropdownMenuSeparator,
    DropdownMenuShortcut,
    DropdownMenuSub,
    DropdownMenuSubContent,
    DropdownMenuSubTrigger,
    DropdownMenuTrigger,
};
