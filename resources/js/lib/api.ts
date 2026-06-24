import { type FlashPayload, showFlashToasts } from '@/lib/flash';

/**
 * Typed error thrown by apiFetch when the server returns a non-2xx status.
 * The message comes from the API's `{ error: '...' }` response body.
 * The optional code is a machine-readable slug from `{ code: '...' }` for
 * callers that need to branch on specific failure reasons.
 */
export class ApiError extends Error {
    constructor(
        readonly status: number,
        message: string,
        readonly code?: string
    ) {
        super(message);
        this.name = 'ApiError';
    }
}

/**
 * Reads the XSRF-TOKEN cookie that Laravel sets on every response.
 * The cookie value is URL-encoded — decodeURIComponent gives us the raw token.
 */
function xsrfToken(): string {
    const match = document.cookie.match(/(?:^|;\s*)XSRF-TOKEN=([^;]+)/);
    return match ? decodeURIComponent(match[1]) : '';
}

type ErrorBody = { error?: string; code?: string };

/**
 * Thin fetch wrapper for the internal REST API (`/api/v1/*`).
 *
 * - Attaches `X-XSRF-TOKEN` so Laravel's CSRF middleware accepts the request.
 * - Sends session cookies (`credentials: 'include'`) so Sanctum can
 *   authenticate the Inertia SPA via its existing session.
 * - Throws `ApiError` for non-2xx responses, using the API's `{ error }` body.
 *
 * @example
 * const info = await apiFetch<UserInfoResponse>('/api/v1/user/info');
 *
 * @example
 * try {
 *   await apiFetch('/api/v1/some/resource', { method: 'POST', body: JSON.stringify(payload) });
 * } catch (e) {
 *   if (e instanceof ApiError) toast.error(e.message);
 * }
 */
export async function apiFetch<T = unknown>(path: string, options: RequestInit = {}): Promise<T> {
    const response = await fetch(path, {
        ...options,
        credentials: 'include',
        headers: {
            Accept: 'application/json',
            'Content-Type': 'application/json',
            'X-XSRF-TOKEN': xsrfToken(),
            ...options.headers,
        },
    });

    if (!response.ok) {
        const body = (await response.json().catch((): ErrorBody => ({}))) as ErrorBody;
        throw new ApiError(response.status, body.error ?? response.statusText, body.code);
    }

    return response.json() as Promise<T>;
}

/**
 * `apiFetch` variant that surfaces flash messages as toasts — the JSON-response
 * counterpart to the Inertia flash mechanism in `useFlashMessages`. On success it
 * toasts any `success`/`error`/`warning`/`info` field in the response body; on
 * failure it toasts the `ApiError` message and rethrows so callers can roll back
 * optimistic UI.
 */
export async function apiFetchWithFlash<T = unknown>(
    path: string,
    options: RequestInit = {}
): Promise<T> {
    try {
        const body = await apiFetch<T>(path, options);
        showFlashToasts(body as FlashPayload);
        return body;
    } catch (error) {
        if (error instanceof ApiError) showFlashToasts({ error: error.message });
        throw error;
    }
}
