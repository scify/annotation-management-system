/**
 * Typed error thrown by apiFetch when the server returns a non-2xx status.
 * The message comes from the API's `{ error: '...' }` response body.
 */
export class ApiError extends Error {
	constructor(
		readonly status: number,
		message: string
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

type ErrorBody = { error?: string };

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
		throw new ApiError(response.status, body.error ?? response.statusText);
	}

	return response.json() as Promise<T>;
}
