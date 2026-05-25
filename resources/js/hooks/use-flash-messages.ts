import { type SharedData } from '@/types';
import { usePage } from '@inertiajs/react';
import { useEffect } from 'react';
import { toast } from 'sonner';

export function useFlashMessages() {
    const { flash, errors } = usePage<SharedData>().props;
    const { success, error, warning, info } = flash;

    useEffect(() => {
        if (success) toast.success(success);
        if (error) toast.error(error);
        if (warning) toast.warning(warning);
        if (info) toast.info(info);
    }, [success, error, warning, info]);

    // Stable string dep: join sorted values with null byte (cannot appear in validation messages)
    const errorKey = Object.values(errors ?? {})
        .sort((a, b) => a.localeCompare(b))
        .join('\0');
    useEffect(() => {
        errorKey
            .split('\0')
            .filter(Boolean)
            .forEach((msg) => toast.error(msg));
    }, [errorKey]);
}
