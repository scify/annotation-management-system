# Test Conventions

## Suite Overview

| Suite | Directory | Purpose | Database |
|---|---|---|---|
| Unit | `tests/Unit/` | Isolated class tests — no database, no HTTP | None |
| Feature | `tests/Feature/` | Full HTTP requests through the Laravel stack | `RefreshDatabase` |
| Integration | `tests/Integration/` | Tests hitting real external services (skip by default) | `RefreshDatabase` |

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
  ```bash
  ddev exec vendor/bin/pest --group=integration
  ```

## Running Tests

```bash
# All suites
ddev composer test

# Just Pest (no lint)
ddev exec vendor/bin/pest

# Specific suite
ddev exec vendor/bin/pest --testsuite=Feature

# Specific test
ddev exec vendor/bin/pest --filter="stores a new user"

# With coverage
ddev xdebug on
ddev exec "XDEBUG_MODE=coverage vendor/bin/pest --coverage"
ddev xdebug off
```
