import { cn } from '@/lib/utils';
import { XIcon } from 'lucide-react';
import * as React from 'react';
import { Modal, ModalOverlay } from 'react-aria-components';

// Dialog — replaces @radix-ui/react-dialog with React Aria's Modal/ModalOverlay.
//
// State management is handled entirely by our Dialog component (not by React Aria's
// DialogTrigger), which lets us keep the shadcn API:
//   • Uncontrolled: <Dialog>…<DialogTrigger/>…<DialogContent/></Dialog>
//   • Controlled:   <Dialog open={open} onOpenChange={setOpen}>…<DialogContent/></Dialog>
//
// React Aria's ModalOverlay provides portal rendering, focus trap, scroll lock,
// Escape-to-close, and click-outside-to-dismiss — all the accessibility heavy lifting.

// ── Context ──────────────────────────────────────────────────────────────────

interface DialogContextValue {
    isOpen: boolean;
    openDialog: () => void;
    closeDialog: () => void;
}

const DialogContext = React.createContext<DialogContextValue>({
    isOpen: false,
    openDialog: () => {},
    closeDialog: () => {},
});

// ── Root ──────────────────────────────────────────────────────────────────────

function Dialog({
    open: controlledOpen,
    onOpenChange,
    defaultOpen = false,
    children,
}: Readonly<{
    open?: boolean;
    onOpenChange?: (open: boolean) => void;
    defaultOpen?: boolean;
    children?: React.ReactNode;
}>) {
    const [uncontrolledOpen, setUncontrolledOpen] = React.useState(defaultOpen);
    const isControlled = controlledOpen !== undefined;
    const isOpen = isControlled ? (controlledOpen ?? false) : uncontrolledOpen;

    const openDialog = React.useCallback(() => {
        if (!isControlled) setUncontrolledOpen(true);
        onOpenChange?.(true);
    }, [isControlled, onOpenChange]);

    const closeDialog = React.useCallback(() => {
        if (!isControlled) setUncontrolledOpen(false);
        onOpenChange?.(false);
    }, [isControlled, onOpenChange]);

    return (
        <DialogContext.Provider value={{ isOpen, openDialog, closeDialog }}>
            {children}
        </DialogContext.Provider>
    );
}

// ── Trigger ───────────────────────────────────────────────────────────────────

function DialogTrigger({
    asChild,
    children,
    ...props
}: Readonly<React.ComponentProps<'button'> & { asChild?: boolean }>) {
    const { openDialog } = React.useContext(DialogContext);

    if (asChild && React.isValidElement(children)) {
        const child = children as React.ReactElement<{
            onClick?: React.MouseEventHandler;
        }>;
        return React.cloneElement(child, {
            onClick: (e: React.MouseEvent) => {
                child.props.onClick?.(e);
                openDialog();
            },
        } as object);
    }

    return (
        <button data-slot="dialog-trigger" type="button" onClick={openDialog} {...props}>
            {children}
        </button>
    );
}

// ── Portal / Overlay shims (kept for API compat, no-ops here) ─────────────────

function DialogPortal({ children }: Readonly<{ children?: React.ReactNode }>) {
    return <>{children}</>;
}

function DialogOverlay({ className: _className, ...props }: Readonly<React.ComponentProps<'div'>>) {
    // Overlay rendering is handled inside DialogContent → ModalOverlay.
    // This component exists only for API compatibility; render nothing independently.
    void _className;
    void props;
    return null;
}

// ── Close ─────────────────────────────────────────────────────────────────────

function DialogClose({
    asChild,
    children,
    ...props
}: Readonly<React.ComponentProps<'button'> & { asChild?: boolean }>) {
    const { closeDialog } = React.useContext(DialogContext);

    if (asChild && React.isValidElement(children)) {
        const child = children as React.ReactElement<{
            onClick?: React.MouseEventHandler;
        }>;
        return React.cloneElement(child, {
            onClick: (e: React.MouseEvent) => {
                child.props.onClick?.(e);
                closeDialog();
            },
        } as object);
    }

    return (
        <button data-slot="dialog-close" type="button" onClick={closeDialog} {...props}>
            {children}
        </button>
    );
}

// ── Content ───────────────────────────────────────────────────────────────────

function DialogContent({
    className,
    children,
    ...props
}: Readonly<React.ComponentProps<'div'>>) {
    const { isOpen, closeDialog } = React.useContext(DialogContext);

    return (
        <ModalOverlay
            isOpen={isOpen}
            onOpenChange={(open) => {
                if (!open) closeDialog();
            }}
            isDismissable
            className={cn(
                'fixed inset-0 z-50 bg-black/80',
                'data-[entering]:animate-in data-[entering]:fade-in-0',
                'data-[exiting]:animate-out data-[exiting]:fade-out-0'
            )}
        >
            <Modal
                className={cn(
                    'bg-background fixed top-[50%] left-[50%] z-50 grid w-full max-w-[calc(100%-2rem)] translate-x-[-50%] translate-y-[-50%] gap-4 rounded-lg border p-6 shadow-lg duration-200 sm:max-w-lg',
                    'data-[entering]:animate-in data-[entering]:fade-in-0 data-[entering]:zoom-in-95',
                    'data-[exiting]:animate-out data-[exiting]:fade-out-0 data-[exiting]:zoom-out-95',
                    className
                )}
            >
                {/* Re-provide DialogContext inside the portal so close works */}
                <DialogContext.Consumer>
                    {(ctx) => (
                        <DialogContext.Provider value={ctx}>
                            <div data-slot="dialog-content" {...props}>
                                {children}
                                <button
                                    type="button"
                                    onClick={ctx.closeDialog}
                                    className="ring-offset-background focus:ring-ring absolute top-4 right-4 rounded-xs opacity-70 transition-opacity hover:opacity-100 focus:ring-2 focus:ring-offset-2 focus:outline-hidden disabled:pointer-events-none [&_svg]:pointer-events-none [&_svg]:shrink-0 [&_svg:not([class*='size-'])]:size-4"
                                >
                                    <XIcon />
                                    <span className="sr-only">Close</span>
                                </button>
                            </div>
                        </DialogContext.Provider>
                    )}
                </DialogContext.Consumer>
            </Modal>
        </ModalOverlay>
    );
}

// ── Subcomponents (pure layout, no Radix dependency) ─────────────────────────

function DialogHeader({ className, ...props }: Readonly<React.ComponentProps<'div'>>) {
    return (
        <div
            data-slot="dialog-header"
            className={cn('flex flex-col gap-2 text-center sm:text-left', className)}
            {...props}
        />
    );
}

function DialogFooter({ className, ...props }: Readonly<React.ComponentProps<'div'>>) {
    return (
        <div
            data-slot="dialog-footer"
            className={cn('flex flex-col-reverse gap-2 sm:flex-row sm:justify-end', className)}
            {...props}
        />
    );
}

function DialogTitle({ className, ...props }: Readonly<React.ComponentProps<'h2'>>) {
    return (
        <h2
            data-slot="dialog-title"
            className={cn('text-lg leading-none font-semibold', className)}
            {...props}
        />
    );
}

function DialogDescription({ className, ...props }: Readonly<React.ComponentProps<'p'>>) {
    return (
        <p
            data-slot="dialog-description"
            className={cn('text-muted-foreground text-sm', className)}
            {...props}
        />
    );
}

export {
    Dialog,
    DialogClose,
    DialogContent,
    DialogContext,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogOverlay,
    DialogPortal,
    DialogTitle,
    DialogTrigger,
};
