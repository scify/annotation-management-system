import { type SharedData } from '@/types';
import { router, usePage } from '@inertiajs/react';
import { useEffect, useRef } from 'react';
import { toast } from 'sonner';

// Per-severity auto-dismiss durations (ms). Errors and warnings linger longer so
// they aren't gone before the user has read them; success/info clear quickly.
const DURATION = { success: 4000, info: 4000, warning: 6000, error: 8000 } as const;

function showFlashToasts(flash: SharedData['flash'], errors?: Record<string, string>) {
    if (flash.success) toast.success(flash.success, { duration: DURATION.success });
    if (flash.error) toast.error(flash.error, { duration: DURATION.error });
    if (flash.warning) toast.warning(flash.warning, { duration: DURATION.warning });
    if (flash.info) toast.info(flash.info, { duration: DURATION.info });
    Object.values(errors ?? {})
        .filter(Boolean)
        .forEach((msg) => toast.error(msg, { duration: DURATION.error }));
}

// Set by the success listener before the incoming component mounts on
// cross-page navigation, preventing the mount effect from showing a duplicate.
let successToastShown = false;

export function useFlashMessages() {
    const { flash, errors } = usePage<SharedData>().props;

    // Capture values in refs so the mount-only effect has no reactive deps.
    const mountFlashRef = useRef(flash);
    const mountErrorsRef = useRef(errors);

    // Handles the initial page load — no success event precedes the first mount.
    useEffect(() => {
        if (!successToastShown) {
            showFlashToasts(mountFlashRef.current, mountErrorsRef.current);
        }
        successToastShown = false;
    }, []);

    // Handles every navigation — same-page and cross-page alike.
    // 'success' fires on every completed Inertia request, including same-component
    // revisits where 'navigate' would not fire. The module-level flag above
    // prevents a duplicate toast on cross-page navigation where both this handler
    // (old component) and the mount effect (new component) would otherwise run.
    useEffect(() => {
        return router.on('success', (event) => {
            successToastShown = true;
            const { flash: f, errors: e } = event.detail.page.props as unknown as SharedData;
            showFlashToasts(f, e ?? {});
        });
    }, []);
}
