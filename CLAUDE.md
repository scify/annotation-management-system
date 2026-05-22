# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Role & Context

You are an expert in Laravel, PHP, React, Tailwind and related web development technologies, with experience building annotation management and user administration systems.

You are working with a team of senior software engineers who are well-versed in Laravel, React, and modern web development. Communicate as a peer collaborator: skip basic explanations, focus on architectural decisions and tradeoffs, and challenge ideas directly when something seems unclear or suboptimal. Prioritize pragmatic solutions over theoretical perfection.

## Project Overview

**annotrAIn** is a Laravel-based annotation management system. The application manages annotators (users), their roles, and permissions via an Inertia/React SPA.

### Architecture

- **Frontend**: React 19 + TypeScript via Inertia.js (server-driven SPA)
- **Internal API**: Versioned REST API (`/api/v1/`) with Sanctum authentication
- **Authentication**: Role-based access control via Spatie Laravel Permission with enum-based roles
- **SSR**: Enabled — `@inertiajs/vite` plugin handles SSR automatically in development; production uses `php artisan inertia:start-ssr`

### Technology Stack

- **Laravel**: 13 (latest stable)
- **React**: 19 with Inertia.js 3
- **PHP**: 8.4
- **Node**: 24
- **TypeScript**: Strict mode
- **TailwindCSS**: v4
- **shadcn/ui**: UI components (copied into project)
- **Sonner**: Toast notifications
- **Spatie Laravel Permission**: Role-based access control
- **Vite**: Asset compilation (NOT Laravel Mix)
- **PHPStan**: Static analysis at **level 8** (via Larastan)

### Project Structure

```text
app/
├── Enums/                  # RolesEnum, PermissionsEnum
├── Exceptions/             # PresentableError (interface), ExternalAPIException
├── Http/
│   ├── Controllers/
│   │   ├── Api/V1/        # Versioned API controllers (UserController)
│   │   ├── Auth/          # Auth flow controllers
│   │   ├── Settings/      # PasswordController, ProfileController
│   │   ├── DashboardController
│   │   ├── UserController
│   │   └── UserRestoreController
│   ├── Middleware/         # AddSecurityHeaders, HandleInertiaRequests, RestrictApiAccess, TrustProxies
│   ├── Requests/           # UserStoreRequest, UserUpdateRequest, Auth/, Settings/
│   └── Resources/          # UserResource (API)
├── Models/                 # User (with soft-deletes)
├── Policies/               # UserPolicy
├── Providers/              # AppServiceProvider
└── Services/
    ├── Dashboard/          # DashboardService
    └── User/               # UserService

resources/js/
├── components/             # App shell (app-sidebar, app-header, breadcrumbs, etc.)
│   └── ui/                # front-end components (copied — you own the code)
├── hooks/                  # Custom React hooks
│   ├── use-appearance.tsx  # Dark/light/system mode + SSR cookie
│   ├── use-flash-messages.ts  # Sonner toast integration
│   ├── use-initials.tsx
│   ├── use-mobile.tsx
│   └── use-translations.ts
├── layouts/                # Page layouts
│   ├── app/               # app-sidebar-layout, app-header-layout
│   ├── auth/              # auth-card, auth-simple, auth-split
│   ├── settings/          # settings layout
│   ├── app-layout.tsx     # Main authenticated layout (use this)
│   └── auth-layout.tsx    # Auth layout wrapper
├── lib/                    # cn() utility
├── pages/                  # Inertia page components (lowercase dirs)
│   ├── auth/              # login, register, etc.
│   ├── settings/          # profile, password, appearance
│   ├── users/             # index, create, edit, show + components/
│   └── dashboard.tsx
└── types/
    └── index.d.ts          # User, Auth, SharedData, PageProps, AppData, RolesEnum
```

**CRITICAL: Before any command, check `APP_DEVELOPMENT_ENV` in `.env`** — this determines how all commands must be run:

| `.env` value | How to run commands |
|---|---|
| `"native"` | Run directly: `composer ...`, `npm ...`, `php artisan ...`, `vendor/bin/...` |
| `"ddev"` | Prefix with `ddev`: `ddev composer ...`, `ddev npm ...`, `ddev artisan ...`, `ddev exec vendor/bin/...` |

Commands in this document are shown in **native form**. DDEV users: add the `ddev` prefix — `composer` → `ddev composer`, `npm` → `ddev npm`, `php artisan` → `ddev artisan`, `vendor/bin/` → `ddev exec vendor/bin/`.

## Build & Development

**Recommended (via Composer):**

- `composer dev` — Start dev server (auto-detects environment; runs queue + logs + Vite)

**Individual commands (if needed):**

- `npm run dev` — Start Vite development server only
- `npm run build` — Build frontend assets for production
- `npm run build:ssr` — Build both client and SSR bundles (required before production deployment)

## Fix & Check (Code Quality)

Commands are split by *what they do to your repo*:

| Command | Mutates files? | Use when |
|---|---|---|
| `composer fix` | Yes | Before committing — apply all auto-fixes |
| `composer fix:backend` | Yes | Backend dev — Rector + Pint, no Node required |
| `composer check` | No | CI-safe style/lint verification |
| `composer check:backend` | No | Backend dev — Pint + Rector dry-run, no Node |
| `composer check:types` | No | PHPStan level 8 + TypeScript tsc |
| `composer check:types:backend` | No | PHPStan only, no Node required |
| `npm run fix` | Yes | Frontend dev — ESLint + Stylelint + Prettier |
| `npm run check` | No | Frontend dev — CI-safe verification |
| `npm run types` | No | TypeScript type-check only |

## Testing

**Recommended (via Composer):**

- `composer test:all` — Full CI suite (check + check:types + Pest backend + browser)
- `composer test:coverage` — Pest with coverage

**Coverage (requires Xdebug):**

Native (Xdebug must already be installed and configured in `php.ini`):

```bash
XDEBUG_MODE=coverage vendor/bin/pest --coverage --coverage-filter=app/Path/To/Class.php
```

DDEV:

```bash
ddev xdebug on
ddev exec "XDEBUG_MODE=coverage vendor/bin/pest --coverage --coverage-filter=app/Path/To/Class.php"
ddev xdebug off
```

**MANDATORY: Before touching, altering, editing, or adding ANY tests, you MUST read `tests/README.md` first.** No exceptions.

## Code Style Guidelines

### CRITICAL: Never Mute Errors or Warnings

- **NEVER use `@ts-ignore`, `@ts-expect-error`, or ESLint disable comments** to silence type errors
- **NEVER use `@phpstan-ignore`, `@larastan-ignore`, or baseline files** to silence static analysis errors
- **ALWAYS find and fix the underlying cause** of the error or warning
- If a linter catches an issue, it means the code has a real problem that needs fixing
- For Larastan: Errors indicate unsafe operations — use safe accessors (`config()->string()`, `$request->safe()`) instead of suppressing
- For TypeScript: Type errors mean the runtime behaviour doesn't match the types — fix the types or the code
- The only acceptable suppressions are **intentional design decisions** documented with clear comments explaining exactly why the pattern is safe

### PHP

- PSR-12 with Laravel conventions (see `pint.json`)
- Strict typing: `declare(strict_types=1)` in all files
- After a file edit, always run `vendor/bin/phpstan analyse path/to/file.php --no-progress 2>&1` to check for static analysis errors (DDEV: `ddev exec vendor/bin/phpstan analyse path/to/file.php --no-progress 2>&1`)
- Use typed properties and return types
- Organize imports alphabetically
- No inline FQCNs: always use imports, never `\Namespace\Class::class` inline (enforced by Pint's `global_namespace_import` rule)
- Use safe accessors for mixed types: `config()->string()`, `$request->safe()`
- **Always use modern Laravel helpers** — `Str::` for strings, `Arr::` for arrays. These handle edge cases that manual string operations miss
- **Check local sources first** — before searching the web, look in `vendor/` for documentation and implementation details

### TypeScript/React

- 4 spaces indentation, single quotes
- React functional components with hooks (no class components)
- Strict TypeScript typing — no `any`, no unchecked casts
- Organize imports alphabetically (enforced by Prettier `organize-imports` plugin)
- Test files use `.test.tsx` extension
- Component files use kebab-case: `user-card.tsx`
- **Internal links**: Always use `<Link>` from `@inertiajs/react`, NEVER `<a>` tags
  - `<a>` causes full page reload, breaking SPA navigation
  - `<Link>` preserves state and enables Inertia prefetching
- **URLs**: Always use `route('name')` from Ziggy, never hardcode paths like `href="/users"`

### React Component Props Conventions

Define props via TypeScript interfaces. Document optional props:

```tsx
interface UserCardProps {
    user: User;
    /** Override shown count. Falls back to results.length */
    count?: number;
    /** Hidden when not provided */
    title?: string;
}

export default function UserCard({ user, count, title }: UserCardProps) {
    const displayCount = count ?? user.results.length;
    // ...
}
```

- All optional props should have sensible defaults or explicit `undefined` handling
- Prefer minimal required props — derive what you can
- For conditional rendering: use defaults that disable features when not provided

## Testing Conventions

**MANDATORY: Read `tests/README.md` before writing any tests.**

**Test suite layout:**

| Suite | Directory | Purpose | Database |
|---|---|---|---|
| Unit | `tests/Unit/` | Isolated class tests — no database, no HTTP | None |
| Feature | `tests/Feature/` | Full HTTP requests through the Laravel stack | `RefreshDatabase` |
| Integration | `tests/Integration/` | Real external services (skip by default) | `RefreshDatabase` |

**Style: BDD with `describe()` + `it()`; pattern: AAA (Arrange / Act / Assert).**

```php
describe('UserController', function () {
    beforeEach(fn () => $this->seed(RolesAndPermissionsSeeder::class));

    it('allows admins to create users', function () {
        // Arrange
        $admin = User::factory()->create()->assignRole(RolesEnum::ADMINISTRATOR);

        // Act
        $response = $this->actingAs($admin)->post(route('users.store'), [...]);

        // Assert
        $response->assertRedirect();
    });
});
```

## Project-Specific Conventions

### Translations

This is a multi-language project. Whenever a string is used in the frontend, it must be first declared in `lang`
And then be used in the frontend:

```tsx
import { useTranslations } from '@/hooks/use-translations';

{t('auth.login.forgot_password')}
```

### Authentication & Authorization

- Use **Spatie Laravel Permission** for RBAC
- Roles defined in `App\Enums\RolesEnum`
- Permissions defined in `App\Enums\PermissionsEnum`
- `Gate::before` in `AppServiceProvider` grants ADMINISTRATOR all permissions automatically
- Seed roles/permissions via `RolesAndPermissionsSeeder` before tests

### Frontend Permissions Pattern

**Global permissions** (route-level, from `HandleInertiaRequests` middleware):

```tsx
// On authenticated pages using PageProps
const { auth } = usePage<PageProps>().props;
const { can } = auth.user;

// Controls access to routes/features
{can.create_users && <Button>Create User</Button>}
{can.view_users && <Button>View Users</Button>}
```

**Row-level abilities** (per-record, from controller):

```tsx
// Controller passes abilities map keyed by record ID
interface Props {
    users: PaginatedData<User>;
    abilities: Record<number, { update: boolean; delete: boolean; restore: boolean }>;
}

export default function UsersIndex({ users, abilities }: Props) {
    const canUpdate = (user: User) => abilities[user.id]?.update ?? false;
    const canDelete = (user: User) => abilities[user.id]?.delete ?? false;
    // ...
}
```

**Key points:**

- Global permissions come from `HandleInertiaRequests` via `auth.user.can`
- Row-level abilities are computed in the controller using Laravel policies
- Never duplicate policy logic in the frontend — backend is the single source of truth

### Services Layer

- Domain logic lives in `app/Services/{Domain}/{Domain}Service.php`
- Controllers should be thin — delegate to services
- Service methods should be well-typed with return types
- `UserService` — user CRUD, role assignment, soft-delete/restore

### Exception Handling

- `PresentableError` interface (`app/Exceptions/PresentableError.php`) marks exceptions safe to display to users
- Controllers can `catch (PresentableError $e)` broadly to surface `$e->getUserMessage()` in flash messages
- `ExternalAPIException` implements `PresentableError` — shows generic message in production, actual message in development

### Flash Messages

Flash messages are stored as individual session keys (`success`, `error`, `warning`, `info`) and surfaced as Sonner toasts via `useFlashMessages()`:

```tsx
// AppLayout already calls this — don't call it again in pages
const AppLayout = ({ children }) => {
    useFlashMessages(); // wired to Sonner
    return <AppLayoutTemplate>{children}<Toaster /></AppLayoutTemplate>;
};
```

In controllers, set flash before redirecting:

```php
return redirect()->route('users.index')->with('success', 'User created.');
return redirect()->back()->with('error', 'Something went wrong.');
```

### Dark Mode / Appearance

AMS supports light, dark, and system appearance. The `useAppearance()` hook manages this:

- Persists in `localStorage` for client-side
- Persists in a cookie for SSR hydration (avoids flash)
- Applies a `.dark` class to `<html>`
- `initializeTheme()` is called in `app.tsx` before React mounts to prevent FOUC

When building components, support both modes via Tailwind's `dark:` variants.

### Inertia Navigation

- **Internal links**: Always `<Link href={route('users.index')}>` — never `<a href="/users">`
- **Programmatic navigation**: `router.visit(route('users.show', user.id))`
- **Forms**: Use Inertia's `useForm()` hook for form state and submission
- **Back**: `router.visit(route('users.index'))` or `window.history.back()`

### Inertia Page Props Pattern

**CRITICAL: Never use `SharedData` as your page component's prop type.**

- Controller-specific props → TypeScript interface, received as function parameters
- Global Inertia props → `usePage<SharedData>().props` (runtime, works anywhere)
- Authenticated global props → `usePage<PageProps>().props` (non-nullable `auth.user`)

**Correct patterns:**

```tsx
// Page receiving controller props only
interface Props {
    status?: string;
}
export default function VerifyEmail({ status }: Props) {
    return <div>{status}</div>;
}
```

```tsx
// Page needing both controller props AND global props
interface Props {
    mustVerifyEmail: boolean;
}
export default function ProfileEdit({ mustVerifyEmail }: Props) {
    const { auth } = usePage<PageProps>().props;  // non-nullable user on auth routes
    const user = auth.user;
    // ...
}
```

```tsx
// Layout/component that only needs global props
export default function AppLogo() {
    const { app } = usePage<SharedData>().props;
    return <span>{app.name}</span>;
}
```

**Why:** Mixing controller props into `SharedData` would require tests to provide all shared props. Keep them separate — controller props in the function signature, global props via `usePage()`.

**`PageProps<T>` utility type** for authenticated pages (user is non-nullable):

```tsx
type PageProps<T = Record<string, unknown>> = T & {
    auth: { user: User };   // non-nullable — safe on auth-protected routes
    ziggy: Config & { location: string };
};
```

Use `usePage<PageProps>()` on routes protected by the `auth` middleware.

### Controller Organisation

- `Controllers\Auth\*` — Auth flow (login, register, password reset, email verification)
- `Controllers\Settings\*` — User self-service (profile, password updates)
- `Controllers\UserController` — User management (CRUD + soft-delete)
- `Controllers\UserRestoreController` — Restore soft-deleted users (single-action)
- `Controllers\DashboardController` — Dashboard page
- `Controllers\Api\V1\*` — Versioned REST API

### Frontend Page Structure

Page components live in `resources/js/pages/` with **lowercase directories**:

```text
pages/
├── auth/               # login.tsx, register.tsx, etc.
├── settings/           # profile.tsx, password.tsx, appearance.tsx
├── users/              # index.tsx, create.tsx, edit.tsx, show.tsx
│   └── components/    # Page-local components (delete-user-modal.tsx, etc.)
└── dashboard.tsx
```

Use `AppLayout` for authenticated pages, `AuthLayout` for auth pages. For custom layouts, compose with these base layouts.

### TailwindCSS v4

This project uses **Tailwind CSS v4** (NOT v3):

- **PostCSS-based**: Uses `@tailwindcss/postcss` plugin via Vite
- **No `tailwind.config.js`**: Configuration via CSS `@theme` directive in `resources/scss/app.scss`
- **Vite plugin**: Uses `@tailwindcss/vite` for integration

Common utilities still work (`flex`, `bg-primary`, `dark:bg-zinc-900`, etc.), but theme setup is entirely different from v3.

### Color Usage — Never Use Raw Hex Values

**NEVER write arbitrary hex colors like `text-[#475569]` or `bg-[#f2f5fd]` directly in TSX/CSS.** Always resolve to a named token first:

1. **Check `@theme` in `resources/scss/app.scss`** — brand tokens are defined there (e.g. `--color-brand-blue-50: #f2f5fd` → `bg-brand-blue-50`, `--color-brand-blue-500: #7a94e0` → `text-brand-blue-500`).
2. **Check Tailwind's built-in palette** — most grays/slates from Figma map directly (e.g. `#475569` → `text-slate-500`, `#1e293b` → `text-slate-800`, `#94a3b8` → `text-slate-400`).
3. **If no token exists** — add one to `@theme` in `app.scss` before using it, then reference the generated utility.

Arbitrary values (`text-[#hex]`) are a last resort and must never be used for colors already in the palette or `@theme`.

### React-Aria Components

This project uses **[React-Aria](https://react-spectrum.adobe.com/react-aria/index.html)** for accessibility.

**Key rules:**

- Components are **copied into `resources/js/components/ui/`** — you own the code, customize freely
- Use `cn()` from `@/lib/utils` for class merging: `cn('base-classes', condition && 'conditional')`

**Installing new components:**

- **Do NOT install directly** — shadcn's CLI prompts for y/n will hang in Claude Code
- Ask the developer to run: `npx shadcn@latest add [component-name]`

### Lucide Icons

Import from `lucide-react`:

```tsx
import { UserIcon, PlusIcon, TrashIcon } from 'lucide-react';

<UserIcon className="h-4 w-4" />
```

---

## General Laravel/PHP Best Practices

### Key Principles

- Write concise, technical responses with accurate PHP examples
- Follow Laravel best practices and conventions
- Use object-oriented programming with a focus on SOLID principles
- Prefer iteration and modularization over duplication
- Use descriptive variable and method names
- Use lowercase with dashes for directories (e.g., `app/Http/Controllers`)
- Favor dependency injection and service containers

### PHP/Laravel Standards

- Use PHP 8.4 features where appropriate (typed properties, match expressions, enums, property hooks)
- Follow PSR-12 coding standards
- Use strict typing: `declare(strict_types=1)` in all files
- Utilize Laravel's built-in features and helpers
- Implement proper error handling: use custom exceptions, `try/catch` for expected failures
- Use Form Requests for validation — keep controllers clean

### Laravel Best Practices

- Eloquent ORM over raw SQL; query builder for complex queries
- Service layer pattern for domain logic — controllers stay thin
- Use `Password::defaults()` for password rules (configured in `AppServiceProvider`)
- Use Laravel's built-in auth scaffolding
- Vite for asset compilation (NOT Laravel Mix)
- Pest for tests — aim for high assertion count and meaningful coverage
- `Model::shouldBeStrict()` is enabled — eager load relationships, use `$fillable`

---

## Accessible, Fast, Delightful UIs

### Keyboard

- MUST: Full keyboard support per [WAI-ARIA APG](https://www.w3.org/WAI/ARIA/apg/patterns/)
- MUST: Visible focus rings (`:focus-visible`)
- MUST: Manage focus (trap, move, return) per APG patterns
- MUST: Ensure that HTML elements have the "role" attribute
- MUST: Use <hgroup> for headings that have <p> tags as subheadings

### Targets & Input

- MUST: Hit target ≥ 24px (mobile ≥ 44px)
- NEVER: Disable browser zoom
- MUST: `touch-action: manipulation` to prevent double-tap zoom

### Forms

- MUST: Loading buttons show spinner and keep original label
- MUST: Enter submits focused text input; in `<textarea>`, ⌘/Ctrl+Enter submits
- MUST: Keep submit enabled until request starts; then disable, show spinner
- MUST: Errors inline next to fields; on submit, focus first error
- MUST: `autocomplete` + meaningful `name`; correct `type` and `inputmode`
- MUST: Warn on unsaved changes before navigation
- MUST: Trim values to handle trailing spaces
- SHOULD: Disable spellcheck for emails/codes/usernames

### State & Navigation

- MUST: Links are links — use `<Link>` from `@inertiajs/react` for navigation (supports Cmd/Ctrl/middle-click)

### Feedback

- SHOULD: Optimistic UI; reconcile on response; on failure show error and rollback
- MUST: Confirm destructive actions or provide Undo window
- MUST: Use polite `aria-live` for toasts/inline validation
- SHOULD: Ellipsis (`…`) for actions that open dialogs (e.g., "Delete…") and loading states

### Accessibility

- MUST: Redundant status cues (not colour-only)
- MUST: `aria-label` on icon-only buttons
- MUST: Prefer native semantics (`button`, `a`, `label`, `table`) over ARIA
- MUST: `scroll-margin-top` on headings; include "Skip to content" link; hierarchical `<h1–h6>`
- MUST: Use the ellipsis character `…` (not `...`)
- MUST: Resilient to user-generated content (short / avg / very long strings)
- MUST: Use native HTML elements where possible (e.g., `<button>`, `<a>`, `<label>`, `<table>`, `<details>`) instead of ARIA roles where possible.
- MUST: Use `aria-current` on active links
- MUST: When using custom components, make sure to add the appropriate ARIA attributes.

### Animation

- MUST: Honor `prefers-reduced-motion` — use `motion-safe:` Tailwind variant
- SHOULD: Prefer CSS animations over JS
- MUST: Animate compositor-friendly props (`transform`, `opacity`) — avoid `top/left/width/height`
- MUST: Animations are interruptible

### Layout & Design

- MUST: Verify mobile, laptop, and ultra-wide (simulate at 50% zoom)
- MUST: Avoid unwanted scrollbars; fix overflows
- MUST: Accessible charts (colour-blind-friendly palettes)
- MUST: Increase contrast on `:hover/:active/:focus`
- MUST: Tabular numbers for comparisons (`font-variant-numeric: tabular-nums`)
- MUST: Design empty / sparse / dense / error states
- MUST: `<title>` matches current context
