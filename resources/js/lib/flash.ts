import { toast } from 'sonner';

// Per-severity auto-dismiss durations (ms). Errors and warnings linger longer so
// they aren't gone before the user has read them; success/info clear quickly.
export const DURATION = { success: 4000, info: 4000, warning: 6000, error: 8000 } as const;

/**
 * The flash fields a response may carry — either Inertia shared props
 * (`SharedData['flash']`) or the body of a JSON mutation response.
 */
export type FlashPayload = {
    success?: string | null;
    error?: string | null;
    warning?: string | null;
    info?: string | null;
};

/** Surfaces flash fields (and validation errors) as Sonner toasts. */
export function showFlashToasts(flash: FlashPayload, errors?: Record<string, string>) {
    if (flash.success) toast.success(flash.success, { duration: DURATION.success });
    if (flash.error) toast.error(flash.error, { duration: DURATION.error });
    if (flash.warning) toast.warning(flash.warning, { duration: DURATION.warning });
    if (flash.info) toast.info(flash.info, { duration: DURATION.info });
    Object.values(errors ?? {})
        .filter(Boolean)
        .forEach((msg) => toast.error(msg, { duration: DURATION.error }));
}
