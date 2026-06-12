import { useAppearance } from '@/hooks/use-appearance';
import type { CSSProperties } from 'react';
import { Toaster as Sonner, ToasterProps } from 'sonner';

const Toaster = ({ ...props }: ToasterProps) => {
    // Use the app's own appearance hook — there is no next-themes ThemeProvider,
    // so next-themes' useTheme() always returned "system" and toasts tracked the
    // OS scheme instead of the app's chosen theme. `appearance` is already the
    // 'light' | 'dark' | 'system' shape Sonner's `theme` prop expects.
    const { appearance } = useAppearance();

    return (
        <Sonner
            theme={appearance}
            position="bottom-right"
            richColors
            closeButton
            duration={4000}
            className="toaster group"
            style={
                {
                    '--normal-bg': 'var(--popover)',
                    '--normal-text': 'var(--popover-foreground)',
                    '--normal-border': 'var(--border)',
                } as CSSProperties
            }
            {...props}
        />
    );
};

export { Toaster };
