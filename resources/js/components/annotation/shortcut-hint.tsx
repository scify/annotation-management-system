import { cn } from '@/lib/utils';
import { KeyboardIcon } from 'lucide-react';

interface ShortcutHintProps {
    /** When false, the hint renders nothing (driven by the "Hide Shortcuts" toggle). */
    show: boolean;
    /** Key combo to display, e.g. "F", "Enter", "Ctrl + H". */
    keys: string;
    className?: string;
}

/**
 * Muted keyboard-shortcut hint shown below a control, e.g. " : Enter".
 * A small keyboard glyph precedes the combo, matching the Figma annotation tool.
 */
export function ShortcutHint({ show, keys, className }: ShortcutHintProps) {
    if (!show) return null;

    return (
        <span
            className={cn('flex items-center gap-1 text-xs font-medium text-slate-400', className)}
            aria-hidden="true"
        >
            <KeyboardIcon className="size-3.5 shrink-0" />: {keys}
        </span>
    );
}
