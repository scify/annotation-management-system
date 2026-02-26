import { cn } from '@/lib/utils';
import * as React from 'react';

// Native <label> replacement — removes @radix-ui/react-label with zero API change.
// The Radix Label added no behaviour beyond a native <label>; this is a direct drop-in.

function Label({ className, ...props }: Readonly<React.ComponentProps<'label'>>) {
    return (
        <label
            data-slot="label"
            className={cn(
                'text-sm leading-none font-medium select-none group-data-[disabled=true]:pointer-events-none group-data-[disabled=true]:opacity-50 peer-disabled:cursor-not-allowed peer-disabled:opacity-50',
                className
            )}
            {...props}
        />
    );
}

export { Label };
