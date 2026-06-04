import { type SharedData } from '@/types';
import { usePage } from '@inertiajs/react';
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

    // Capture values in refs so the mount-only effect below has no reactive deps.
    // AppLayout uses a per-page layout pattern (each page renders its own AppLayout
    // instance), so this component remounts on every Inertia navigation — the
    // mount effect fires once per page and covers both initial load and navigations.
    const mountFlashRef = useRef(flash);
    const mountErrorsRef = useRef(errors);

    useEffect(() => {
        showFlashToasts(mountFlashRef.current, mountErrorsRef.current);
    }, []);
}
