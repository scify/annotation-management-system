<!-- omit in toc -->
# annotrAIn - Development Setup

[![Tests](https://github.com/scify/annotation-management-system/actions/workflows/tests.yml/badge.svg)](https://github.com/scify/annotation-management-system/actions/workflows/tests.yml)
[![Check](https://github.com/scify/annotation-management-system/actions/workflows/check.yml/badge.svg)](https://github.com/scify/annotation-management-system/actions/workflows/check.yml)
[![License](https://img.shields.io/badge/License-Apache_2.0-blue.svg)](https://opensource.org/licenses/Apache-2.0)

<!-- omit in toc -->
## Table of Contents

- [About annotrAIn](#about-annotation-management-system)
- [Features](#features)
- [Tech Stack](#tech-stack)
- [Installation - Local Development](#installation---local-development)
- [Development Environment](#development-environment)
- [Changelog](#changelog)
- [Contributing](#contributing)
  - [PHP code style - Laravel Pint](#php-code-style---laravel-pint)
  - [Running tests](#running-tests)
    - [Backend tests](#backend-tests)
      - [Run all backend tests](#run-all-backend-tests)
      - [Filter by test name or class](#filter-by-test-name-or-class)
      - [Combine flags](#combine-flags)
    - [Frontend / Browser tests](#frontend--browser-tests)
  - [Code Scanning](#code-scanning)
  - [Git Hooks](#git-hooks)
- [Available Scripts](#available-scripts)
  - [Composer scripts](#composer-scripts)
  - [npm scripts](#npm-scripts)
  - [Database commands](#database-commands)
  - [DDEV convenience shortcuts](#ddev-convenience-shortcuts)
- [Releasing a new version](#releasing-a-new-version)
- [Security](#security)
- [License](#license)
- [Credits](#credits)

## About annotrAIn

**annotrAIn** is a Laravel-based annotation management system. The application manages annotators (users), their roles, and permissions via an Inertia/React SPA.

It includes a complete setup for both the backend and frontend, with support for both **DDEV** and **Native** (PHP, Composer, etc. running locally) development environments.

## Features

1. Support for both **DDEV** and **Native** development environments
2. React 19 / Inertia.js 2 frontend with Vite for faster development
3. Tailwind CSS v4 with React Aria Components for accessible, keyboard-navigable UI
4. SCSS support with PostCSS
5. TypeScript (strict mode)
6. Automated code formatting (PHP, JS/TS, SCSS)
7. Git hooks for code quality
8. Comprehensive test suite using Pest (backend) and Jest (frontend)
9. Role-based access control using Spatie Laravel Permission
10. Dark mode support
11. Responsive design
12. GitHub Actions for CI/CD

## Tech Stack

- **Backend:**
  - Laravel 12.x
  - PHP 8.4
  - MySQL/SQLite
  - Laravel Pint (Code Styling)
  - PHPStan / Larastan (Static Analysis at level 8)
  - Pest (Testing)

- **Frontend:**
  - React 19 with Inertia.js 2
  - TypeScript (strict mode)
  - Tailwind CSS v4
  - React Aria Components (accessibility-first UI primitives)
  - Lucide React (icons)
  - Sonner (toast notifications)
  - Vite
  - ESLint + Prettier + Stylelint

## Installation - Local Development

In order to start developing with **annotrAIn**, you will need to read the guide in
the [LOCAL-DEVELOPMENT.md](docs/LOCAL-DEVELOPMENT.md) file.

## Development Environment

The application supports two development environments, controlled by `APP_DEVELOPMENT_ENV` in your `.env` file:

| `APP_DEVELOPMENT_ENV` | How to run commands |
| --- | --- |
| `native` | Run directly: `composer …`, `npm …`, `php artisan …`, `vendor/bin/…` |
| `ddev` | Prefix with `ddev`: `ddev composer …`, `ddev npm …`, `ddev artisan …`, `ddev exec vendor/bin/…` |

> **All commands in this document are shown in native form.**
> DDEV users: add the `ddev` prefix to every command — e.g. `composer test` becomes `ddev composer test`,
> `npm run dev` becomes `ddev npm run dev`, and `php artisan migrate` becomes `ddev artisan migrate`.

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

To contribute to the application, follow these steps:

1. Fork this repository.
2. Read the [CONTRIBUTING](CONTRIBUTING.md) file.
3. Create a branch: `git checkout -b <branch_name>`.
4. Make your changes and commit them: `git commit -m '<commit_message>'`
5. Push to the original branch: `git push origin <project_name>/<location>`
6. Create the pull request

After making changes, follow this workflow:

```shell
composer fix    # auto-fix everything (Rector + Pint + ESLint + Prettier)
composer check  # verify style/lint rules — no file mutations (CI-safe)
composer test   # run the Pest test suite
```

### PHP code style - Laravel Pint

This application uses [Laravel Pint](https://laravel.com/docs/12.x/pint) for PHP code styling,
managed via Composer scripts.

```shell
composer fix          # apply all auto-fixes (Rector + Pint + ESLint + Prettier)
composer fix:backend  # backend only — no Node required (Rector + Pint)
composer check        # dry-run checks — no modifications (CI-safe)
```

### Running tests

#### Backend tests

Run the full test suite (lint + static analysis + Pest) via:

```shell
composer test:backend

# or composer test
```

To run or filter Pest tests directly:

```shell
composer test:backend                        # all tests
composer test:backend -- --filter TestName      # filter by name
composer test:backend -- --testsuite=Feature    # specific suite
```

##### Run all backend tests

```shell
ddev composer test:backend
```

##### Filter by test name or class

```shell
  ddev composer test:backend -- --filter UserControllerTest
  ddev composer test:backend -- --filter "it creates a user"
```

##### Combine flags

```shell
  ddev composer test:backend -- --filter UserControllerTest --coverage
```

To run with code coverage (requires Xdebug):

```shell
XDEBUG_MODE=coverage ddev composer test:coverage
```

#### Frontend / Browser tests

We use Laravel with Pest for end-to-end browser testing via Playwright. This replaces Jest component tests with real Chromium testing.

**Prerequisites:**

```shell
# 1. Kill any running dev servers (to avoid port conflicts)
pkill -f "vite" || pkill -f "composer run dev" || true

# 2. Build frontend assets (required)
npm run build
```

**Run tests:**

```shell
# Headless mode (CI-friendly)
composer test:browser

# Headed mode — watch the browser (debugging)
BROWSER_HEADLESS=false composer test:browser
```

The in-process server serves compiled assets from `public/build/`. Without them, Inertia pages fail to load and tests will fail.

### Code Scanning

Static analysis runs as part of `composer check:types`:

```shell
composer check:types          # PHPStan (level 8) + TypeScript tsc --noEmit
composer check:types:backend  # PHPStan only (no Node required)
```

### Git Hooks

A pre-commit hook runs automatically on every commit. It:

1. Scans staged files for secrets with **Gitleaks** (blocks commit if secrets detected)
2. Runs **Rector** on staged `.php` files (automated refactors)
3. Runs **Pint** on staged `.php` files (code style formatting)
4. Runs **Prettier / ESLint** on staged `.js/.ts/.tsx` files
5. Runs **Prettier** on staged `.scss/.css` files

Modified files are re-staged automatically, so the committed code always matches the formatted output.

#### Setup

The hook is installed automatically when you run:

```shell
composer install
```

For Gitleaks, you will need also to install the executable. See [GITLEAKS-SECURITY.md](docs/GITLEAKS-SECURITY.md) for details.

If you need to reinstall it manually (e.g. after cloning without running `composer install`):

```shell
bash tools/git-hooks/install.sh
```

> **DDEV users:** Git hooks run on the **host machine**, not inside the container. 
> 
> Run `ddev composer install` to install dependencies inside the container, but the hook script itself executes on the host using `vendor/bin/rector`, `vendor/bin/pint`, and `npm` from the project root. 
> 
> Make sure PHP and Node are available in your host shell, or run `ddev composer install` first to populate `vendor/bin/`.

> **Bypassing the hook:** If you genuinely need to skip it (e.g. a work-in-progress commit), use `git commit --no-verify`. This should be rare.

## Available Scripts

### Composer scripts

| Script | Description |
| --- | --- |
| `composer dev` | Start dev server (auto-detects environment) |
| `composer fix` | Apply all auto-fixes — Rector + Pint + ESLint + Prettier (mutates files) |
| `composer fix:backend` | Backend fixes only — Rector + Pint (no Node required) |
| `composer fix:frontend` | Frontend fixes only — ESLint + Stylelint + Prettier |
| `composer check` | Verify style/lint rules — no file mutations (CI-safe) |
| `composer check:backend` | Backend checks only — Pint + Rector dry-run (no Node required) |
| `composer check:frontend` | Frontend checks only — ESLint + Stylelint + Prettier |
| `composer check:types` | Type analysis — PHPStan level 8 + TypeScript tsc |
| `composer check:types:backend` | PHPStan only (no Node required) |
| `composer test` | Run the Pest test suite (excludes browser tests) |
| `composer test:all` | Full CI suite: check + check:types + test:backend + test:browser |
| `composer test:coverage` | Pest with code coverage (requires Xdebug) |
| `composer update:requirements` | Bump Composer + npm dependencies to latest |

### npm scripts

| Script | Description |
| --- | --- |
| `npm run dev` | Start Vite development server |
| `npm run build` | Build frontend assets for production |
| `npm run fix` | Apply all frontend auto-fixes (ESLint + Stylelint + Prettier) |
| `npm run fix:js` | ESLint auto-fix |
| `npm run fix:styles` | Stylelint auto-fix |
| `npm run fix:format` | Prettier write |
| `npm run check` | Verify all frontend rules — no file mutations (CI-safe) |
| `npm run check:js` | ESLint check |
| `npm run check:styles` | Stylelint check |
| `npm run check:format` | Prettier dry-run |
| `npm run types` | TypeScript type-check (`tsc --noEmit`) |

### Database commands

```shell
php artisan migrate                       # migrate the database
php artisan db:seed                       # seed the database
php artisan migrate:fresh                 # drop all tables and re-run migrations
php artisan migrate:fresh --seed          # fresh migrate + seed
php artisan key:generate                  # generate application key
```

### DDEV convenience shortcuts

When using DDEV, the following project-specific shorthand commands are available in addition to the standard `ddev` prefix rules:

```shell
ddev pint     # shorthand for ddev exec vendor/bin/pint
ddev analyse  # shorthand for ddev exec vendor/bin/phpstan analyse
ddev format   # shorthand for pint + npm format
ddev test     # shorthand for ddev exec vendor/bin/pest
```

## Releasing a new version

Version must be updated in two places before tagging:

- `package.json` — `"version": "x.x.x"`
- `config/app.php` — `'version' => env('APP_VERSION', 'x.x.x')`

After committing your changes, create a new git tag:

```shell
git tag -a vx.y.z -m "This is a nice tag name"
```

(for the `x.y.z` version number, follow the [Semantic Versioning](https://semver.org/) guidelines).

Then, push the tag:

```shell
git push origin vx.y.z
```

Then, in the [GitHub Releases page](https://github.com/scify/annotation-management-system/releases), create a new Release
**and correlate it with the tag that you just created.**

Also, don't forget to update the `CHANGELOG.md` file with the new version name, release date, and release notes.

## Security

This project implements several security measures:

- **Secret Scanning**: [Gitleaks](https://gitleaks.io/) integration prevents accidental exposure of sensitive information like API keys, passwords, and tokens. See [GITLEAKS-SECURITY.md](docs/GITLEAKS-SECURITY.md) for detailed configuration and usage.
- **Security Headers**: Custom middleware adds security headers (CSP, HSTS, etc.)
- **CSRF Protection**: Laravel's built-in CSRF protection
- **Role-based Access Control**: Using Spatie Laravel Permission package

If you discover any security-related issues, please email `info[at]scify.org`, instead of using the issue tracker.

## License

This project is open-sourced software licensed under
the [Apache License, Version 2.0](https://www.apache.org/licenses/LICENSE-2.0).

## Credits

- SciFY
    This project is developed and maintained by [SciFY](https://www.scify.org/) and is based on the [Laravel](https://laravel.com/) framework.
