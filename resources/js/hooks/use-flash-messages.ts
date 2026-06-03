import { type SharedData } from '@/types';
import { router, usePage } from '@inertiajs/react';
import { useEffect, useRef } from 'react';
import { toast } from 'sonner';

function showFlashToasts(flash: SharedData['flash'], errors?: Record<string, string>) {
    if (flash.success) toast.success(flash.success);
    if (flash.error) toast.error(flash.error);
    if (flash.warning) toast.warning(flash.warning);
    if (flash.info) toast.info(flash.info);
    Object.values(errors ?? {})
        .filter(Boolean)
        .forEach((msg) => toast.error(msg));
}

export function useFlashMessages() {
    const { flash, errors } = usePage<SharedData>().props;

    // Capture values in refs so the mount-only effect below has no reactive deps
    const mountFlashRef = useRef(flash);
    const mountErrorsRef = useRef(errors);

    // Fire once on mount for flash present on the initial server-rendered page
    useEffect(() => {
        showFlashToasts(mountFlashRef.current, mountErrorsRef.current);
    }, []);

    // Fire on every subsequent Inertia visit — handles repeated same-string flashes
    useEffect(() => {
        return router.on('success', (event) => {
            const { flash: f, errors: e } = event.detail.page.props as unknown as SharedData;
            showFlashToasts(f, e);
        });
    }, []);
}
