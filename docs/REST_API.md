# REST API

The Annotation Management System exposes a versioned REST API at `/api/v1/`. It supports two client types:

- **Inertia SPA** — authenticated via the existing session cookie, no tokens required.
- **External integrations** — authenticated via a Bearer token issued to a service account.

---

## Authentication

### Inertia SPA (session cookie)

Inertia pages that call `/api/v1/*` endpoints are authenticated automatically via the session cookie established at login. No token management is needed.

Use the `apiFetch` helper from `@/lib/api`:

```ts
import { apiFetch, ApiError } from '@/lib/api';

// GET — no setup required
const info = await apiFetch<UserInfoResponse>('/api/v1/user/info');

// POST / PUT / PATCH / DELETE — CSRF token is attached automatically
try {
    await apiFetch('/api/v1/some/resource', {
        method: 'POST',
        body: JSON.stringify(payload),
    });
} catch (e) {
    if (e instanceof ApiError) toast.error(e.message);
}
```

`apiFetch` handles:
- Attaching `X-XSRF-TOKEN` from Laravel's `XSRF-TOKEN` cookie (required for state-changing requests)
- Sending credentials so the session cookie reaches the server
- Throwing `ApiError(status, message)` for non-2xx responses, using the API's `{ error }` body

### External integrations (Bearer token)

Third-party tools and scripts authenticate with a Bearer token issued to a service account user:

```http
Authorization: Bearer <token>
```

Tokens inherit the full permissions of the user they belong to. There are no additional scopes. Access is controlled by assigning the appropriate role to the service account.

An invalid or revoked token returns `401 Unauthorized`.

---

## Getting a Token (Server Access Required)

Token management is intentionally CLI-only — no HTTP endpoint for issuance. Server (SSH) access is the authorization gate.

### Onboarding a new integration

**Step 1** — Create a service account via the admin UI or seeder, and assign a role.

**Step 2** — Issue a token on the server:

```bash
php artisan api:token:create admin@scify.org --name=my-app
```

The plain-text token is printed **once**. Copy it immediately — it cannot be retrieved again.

```text
  ⚠  Store this token securely — it will NOT be shown again.

  Token ID : 4
  Name     : my-app
  User     : admin@scify.org

  Plain-text token (copy now):
  4|abc123...

  To revoke: php artisan api:token:revoke 4
```

**Step 3** — Pass the token to the third party. They use it as `Authorization: Bearer <token>`.

### Token management commands

| Command | Description |
|---|---|
| `php artisan api:token:create {email} [--name=] [--abilities=*]` | Issue a new token |
| `php artisan api:token:list {email}` | List all tokens for a user |
| `php artisan api:token:revoke {tokenId}` | Revoke a token by ID |

**Revoke immediately** when an integration is decommissioned or a token is compromised:

```bash
php artisan api:token:revoke 4
# Token "my-app" (ID: 4) for admin@scify.org has been revoked.
```

---

## Roles

Tokens inherit the role of their owner. Assign the least-privileged role that satisfies the integration's needs.

| Role | Value | Access |
|---|---|---|
| Admin | `admin` | Full platform access, dashboard |
| Annotation Manager | `annotation-manager` | Dashboard, user management |
| Annotator | `annotator` | Basic access only |

---

## Error Format

All errors return JSON. The shape is consistent across all error types:

```json
{
  "error": "Human-readable message"
}
```

### Common status codes

| Status | Meaning |
|---|---|
| `200 OK` | Success |
| `401 Unauthorized` | Missing, invalid, or revoked token |
| `403 Forbidden` | Token is valid but the user lacks permission |
| `404 Not Found` | Endpoint or resource does not exist |
| `500 Internal Server Error` | Unexpected server-side error |

---

## Endpoints

### `GET /api/v1/user/info`

Returns the identity and permissions of the authenticated user (i.e. the owner of the token).

**Request**

```http
GET /api/v1/user/info
Authorization: Bearer <token>
```

**Response `200 OK`**

```json
{
  "user": {
    "id": 12,
    "name": "Admin Account",
    "email": "admin@scify.org",
    "role": "admin",
    "created_at": "2026-01-15T10:30:00.000000Z",
    "updated_at": "2026-01-15T10:30:00.000000Z"
  },
  "permissions": {
    "dashboard": true
  }
}
```

**Error responses**

```json
// 401 — no token or invalid token
{ "error": "Unauthenticated." }
```

---

## Example: curl

```bash
# Store the token in an env var (never hardcode in scripts)
TOKEN="4|abc123..."

# Fetch user info
curl -s -H "Authorization: Bearer $TOKEN" https://your-host/api/v1/user/info | jq .
```

---

## Versioning

The API is prefixed with `/api/v1/`. Breaking changes will be introduced under a new version prefix (`/api/v2/`) rather than modifying the existing version. Non-breaking additions (new fields, new endpoints) may be made to the current version without a version bump.
