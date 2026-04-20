# Test Conventions

## Suite Overview

| Suite | Directory | Purpose | Database |
|---|---|---|---|
| Unit | `tests/Unit/` | Isolated class tests — no database, no HTTP | None |
| Feature | `tests/Feature/` | Full HTTP requests through the Laravel stack | `RefreshDatabase` |
| Integration | `tests/Integration/` | Tests hitting real external services (skip by default) | `RefreshDatabase` |
| Browser | `tests/Browser/` | End-to-end tests via Playwright (real Chromium browser) | `RefreshDatabase` |

## Writing Tests

### Style: BDD with `describe()` + `it()`

All tests use Pest's BDD style:

```php
describe('UserPolicy', function () {
    it('allows admins to create users', function () {
        // ...
    });

    it('denies regular users from creating users', function () {
        // ...
    });
});
```

### Pattern: AAA (Arrange / Act / Assert)

Each `it()` block should follow the Arrange / Act / Assert pattern:

```php
it('stores a new user', function () {
    // Arrange
    $admin = User::factory()->create();
    $admin->assignRole(RolesEnum::ADMINISTRATOR);

    // Act
    $response = $this->actingAs($admin)->post(route('users.store'), [
        'name' => 'Jane Doe',
        'email' => 'jane@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'role' => RolesEnum::REGISTERED_USER->value,
    ]);

    // Assert
    $response->assertRedirect();
    $this->assertDatabaseHas('users', ['email' => 'jane@example.com']);
});
```

## Suite-Specific Guidelines

### Unit Tests (`tests/Unit/`)

- No database queries
- No HTTP requests
- Test a single class or method in isolation
- Mock all dependencies
- Run without `RefreshDatabase`

### Feature Tests (`tests/Feature/`)

- Full HTTP stack via `actingAs()` + `get()`/`post()`/etc.
- Use `RefreshDatabase` (configured in `Pest.php`)
- Seed roles/permissions in `beforeEach()` when testing auth:

```php
beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
});
```

### Integration Tests (`tests/Integration/`)

- Reserved for tests that require real external services
- Skip gracefully when services are not configured:

```php
it('connects to the external API', function () {
    if (! config('services.external.url')) {
        $this->markTestSkipped('External service not configured.');
    }

    // ...
})->group('integration');
```

- Use the `integration` group so they can be run selectively:
  
  ```shell
  ddev composer test:backend -- --group=integration
  ```

### Browser Tests (`tests/Browser/`)

Browser tests use **Pest v4 Browser** (Playwright-based) and run real Chromium against an in-process Amphp HTTP server. They replace the previous Jest component tests with real E2E coverage.

**How it works:**

- The Pest browser plugin starts an in-process Amphp HTTP server (no external `php artisan serve` needed)
- Chromium talks to this server — the same PHP process handles both test setup and browser requests
- Database state (`RefreshDatabase`) is shared between test code and server requests, so factory-created users are immediately visible to the browser
- Session state persists across browser requests within the same test (array session driver, shared in-process)

**Before running browser tests** — compile frontend assets and ensure no dev server is running:

```shell
# Kill any running dev servers (critical — Vite on localhost conflicts with
# the Playwright browser origin at 127.0.0.1, causing CORS errors)
pkill -f "vite" || pkill -f "composer run dev" || true

# Build production assets (required for browser tests)
npm run build
# or: ddev npm run build
```

The in-process server serves compiled assets from `public/build/`. Without them, Inertia pages fail to load JS and tests will fail. A running Vite dev server causes CORS errors because the Playwright browser (`127.0.0.1`) cannot load scripts from `localhost:5173`.

**Run browser tests:**

```shell
# Headless mode (CI-friendly default)
composer test:browser
# or: ddev composer test:browser

# Headed mode — opens a visible Chromium window (useful for debugging)
BROWSER_HEADLESS=false composer test:browser
```

**Writing browser tests:**

```php
describe('Dashboard', function (): void {
    beforeEach(fn () => $this->seed(RolesAndPermissionsSeeder::class));

    it('shows the dashboard to an admin', function (): void {
        $admin = User::factory()->create(['password' => Hash::make('password')])
            ->assignRole(RolesEnum::ADMIN);

        // Visit the login form, fill credentials, bypass the Altcha frontend guard
        // (server-side captcha validation is disabled in APP_ENV=testing)
        $page = visit('/login')
            ->type('email', $admin->email)
            ->type('password', 'password');

        // script() returns the JS result (mixed), not $this — call it separately.
        // wait(0.1) lets React flush the captcha state update before pressing.
        $page->script("
            const widget = document.getElementById('altcha-widget');
            if (widget) {
                widget.dispatchEvent(new CustomEvent('statechange', {
                    detail: { payload: 'test-token', state: 'verified' },
                    bubbles: true,
                }));
            }
        ");

        $page->wait(0.1)->press('Log in')
            ->assertRoute('dashboard')
            ->assertSee('Dashboard')
            ->assertNoJavascriptErrors();
    });
});
```

**Key assertions:**

- `assertRoute('dashboard')` — checks only the path (host/port-agnostic)
- `assertSee('text')` — retries until the text appears in the DOM (Playwright auto-wait)
- `assertNoJavascriptErrors()` — fails if the browser console has JS errors
- `assertPathIs('/login')` — direct path comparison

**Captcha in tests:**

`LoginRequest` skips `ValidAltcha` when `APP_ENV=testing`. The frontend still guards submission with `if (!data.captcha)`. Bypass it by dispatching a synthetic `statechange` event on `#altcha-widget` via `script()`.

**Cookie consent banner:**

The cookie consent dialog is suppressed in `APP_ENV=testing` via `@unless(app()->environment('testing'))` in `resources/views/app.blade.php`. This prevents the dialog's JavaScript from initializing entirely, so no banner blocks browser interactions.

**Browser tests are excluded from `test:all`** because they require compiled assets. Run them separately before deploying.
