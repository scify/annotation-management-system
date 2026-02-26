import { cn } from '@/lib/utils';
import { XIcon } from 'lucide-react';
import * as React from 'react';
import { Modal, ModalOverlay } from 'react-aria-components';
import {
    Dialog,
    DialogClose,
    DialogContext,
    DialogTrigger,
} from '@/components/ui/dialog';

// Sheet — side-drawer variant of Dialog.
// Re-uses the Dialog state management (same open/close/trigger pattern) but
// renders as a side panel instead of a centred modal.

// Re-export Dialog primitives under Sheet names for API compat.
const Sheet = Dialog;
const SheetTrigger = DialogTrigger;
const SheetClose = DialogClose;

function SheetPortal({ children }: Readonly<{ children?: React.ReactNode }>) {
    return <>{children}</>;
}

interface SheetContentProps extends React.ComponentProps<'div'> {
    side?: 'top' | 'right' | 'bottom' | 'left';
}

function SheetContent({
    side = 'right',
    className,
    children,
    ...props
}: Readonly<SheetContentProps>) {
    const ctx = React.useContext(DialogContext);

    return (
        <ModalOverlay
            isOpen={ctx.isOpen}
            onOpenChange={(open) => {
                if (!open) ctx.closeDialog();
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
                    'bg-background fixed z-50 flex flex-col gap-4 shadow-lg transition ease-in-out',
                    'data-[entering]:duration-500 data-[exiting]:duration-300',
                    side === 'right' &&
                        'data-[entering]:animate-in data-[entering]:slide-in-from-right data-[exiting]:animate-out data-[exiting]:slide-out-to-right inset-y-0 right-0 h-full w-3/4 border-l sm:max-w-sm',
                    side === 'left' &&
                        'data-[entering]:animate-in data-[entering]:slide-in-from-left data-[exiting]:animate-out data-[exiting]:slide-out-to-left inset-y-0 left-0 h-full w-3/4 border-r sm:max-w-sm',
                    side === 'top' &&
                        'data-[entering]:animate-in data-[entering]:slide-in-from-top data-[exiting]:animate-out data-[exiting]:slide-out-to-top inset-x-0 top-0 h-auto border-b',
                    side === 'bottom' &&
                        'data-[entering]:animate-in data-[entering]:slide-in-from-bottom data-[exiting]:animate-out data-[exiting]:slide-out-to-bottom inset-x-0 bottom-0 h-auto border-t',
                    className
                )}
            >
                <DialogContext.Provider value={ctx}>
                    <div data-slot="sheet-content" {...props}>
                        {children}
                        <button
                            type="button"
                            onClick={ctx.closeDialog}
                            className="ring-offset-background focus:ring-ring data-[state=open]:bg-secondary absolute top-4 right-4 rounded-xs opacity-70 transition-opacity hover:opacity-100 focus:ring-2 focus:ring-offset-2 focus:outline-hidden disabled:pointer-events-none"
                        >
                            <XIcon className="size-4" />
                            <span className="sr-only">Close</span>
                        </button>
                    </div>
                </DialogContext.Provider>
            </Modal>
        </ModalOverlay>
    );
}

function SheetHeader({ className, ...props }: Readonly<React.ComponentProps<'div'>>) {
    return (
        <div
            data-slot="sheet-header"
            className={cn('flex flex-col gap-1.5 p-4', className)}
            {...props}
        />
    );
}

function SheetFooter({ className, ...props }: Readonly<React.ComponentProps<'div'>>) {
    return (
        <div
            data-slot="sheet-footer"
            className={cn('mt-auto flex flex-col gap-2 p-4', className)}
            {...props}
        />
    );
}

function SheetTitle({ className, ...props }: Readonly<React.ComponentProps<'h2'>>) {
    return (
        <h2
            data-slot="sheet-title"
            className={cn('text-foreground font-semibold', className)}
            {...props}
        />
    );
}

function SheetDescription({ className, ...props }: Readonly<React.ComponentProps<'p'>>) {
    return (
        <p
            data-slot="sheet-description"
            className={cn('text-muted-foreground text-sm', className)}
            {...props}
        />
    );
}

export {
    Sheet,
    SheetClose,
    SheetContent,
    SheetDescription,
    SheetFooter,
    SheetHeader,
    SheetPortal,
    SheetTitle,
    SheetTrigger,
};
