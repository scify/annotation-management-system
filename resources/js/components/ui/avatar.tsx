import { cn } from '@/lib/utils';
import * as React from 'react';

// Custom Avatar — removes @radix-ui/react-avatar.
// Avatar provides a context that tracks image load state so AvatarFallback only
// shows when the image fails or has no src (matching Radix's original behaviour).

type AvatarState = 'idle' | 'loading' | 'loaded' | 'error';

const AvatarContext = React.createContext<{
    state: AvatarState;
    setState: React.Dispatch<React.SetStateAction<AvatarState>>;
}>({ state: 'idle', setState: () => {} });

function Avatar({ className, ...props }: Readonly<React.ComponentProps<'span'>>) {
    const [state, setState] = React.useState<AvatarState>('idle');

    return (
        <AvatarContext.Provider value={{ state, setState }}>
            <span
                data-slot="avatar"
                className={cn('relative flex size-8 shrink-0 overflow-hidden rounded-full', className)}
                {...props}
            />
        </AvatarContext.Provider>
    );
}

interface AvatarImageProps extends React.ComponentProps<'img'> {
    src?: string;
}

function AvatarImage({ className, src, alt = '', onLoad, onError, ...props }: Readonly<AvatarImageProps>) {
    const { setState } = React.useContext(AvatarContext);

    React.useEffect(() => {
        setState(src ? 'loading' : 'error');
    }, [src, setState]);

    if (!src) return null;

    return (
        <img
            data-slot="avatar-image"
            src={src}
            alt={alt}
            onLoad={(e) => {
                setState('loaded');
                onLoad?.(e);
            }}
            onError={(e) => {
                setState('error');
                onError?.(e);
            }}
            className={cn('aspect-square size-full', className)}
            {...props}
        />
    );
}

function AvatarFallback({ className, ...props }: Readonly<React.ComponentProps<'span'>>) {
    const { state } = React.useContext(AvatarContext);

    if (state === 'loaded') return null;

    return (
        <span
            data-slot="avatar-fallback"
            className={cn(
                'bg-muted flex size-full items-center justify-center rounded-full',
                className
            )}
            {...props}
        />
    );
}

export { Avatar, AvatarFallback, AvatarImage };
