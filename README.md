<!-- omit in toc -->
# Annotation Management System - Development Setup

[![Tests](https://github.com/scify/annotation-management-system/actions/workflows/tests.yml/badge.svg)](https://github.com/scify/annotation-management-system/actions/workflows/tests.yml)
[![Linter](https://github.com/scify/annotation-management-system/actions/workflows/lint.yml/badge.svg)](https://github.com/scify/annotation-management-system/actions/workflows/lint.yml)
[![License](https://img.shields.io/badge/License-Apache_2.0-blue.svg)](https://opensource.org/licenses/Apache-2.0)

<!-- omit in toc -->
## Table of Contents

- [About Annotation Management System](#about-annotation-management-system)
- [Features](#features)
- [Tech Stack](#tech-stack)
- [Installation - Local Development](#installation---local-development)
- [Development Environment](#development-environment)
- [Changelog](#changelog)
- [Contributing](#contributing)
  - [PHP code style - Laravel Pint](#php-code-style---laravel-pint)
  - [Running tests](#running-tests)
    - [Backend tests](#backend-tests)
    - [Frontend tests](#frontend-tests)
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

## About Annotation Management System

**Annotation Management System** is a Laravel-based annotation management system. The application manages annotators (users), their roles, and permissions via an Inertia/React SPA.

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

In order to start developing with **Annotation Management System**, you will need to read the guide in
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
composer lint   # format everything (Rector + Pint + ESLint + Prettier)
composer test   # verify nothing broke (lint + types + Pest)
```

### PHP code style - Laravel Pint

This application uses [Laravel Pint](https://laravel.com/docs/12.x/pint) for PHP code styling,
managed via Composer scripts.

```shell
composer lint       # format all code — fix mode (Rector + Pint + ESLint + Prettier)
composer test:lint  # dry-run checks only — no modifications (CI-friendly)
```

### Running tests

#### Backend tests

Run the full test suite (lint + static analysis + Pest) via:

```shell
composer test
```

To run or filter Pest tests directly:

```shell
vendor/bin/pest                        # all tests
vendor/bin/pest --filter TestName      # filter by name
vendor/bin/pest --testsuite=Feature    # specific suite
```

To run with code coverage (requires Xdebug):

```shell
composer test:coverage
```

#### Frontend tests

We use Jest for frontend tests.

```shell
npm run test           # run all Jest tests
npm run test:watch     # Jest in watch mode
npm run test:coverage  # with coverage report
```

### Code Scanning

Static analysis runs as part of `composer test:types`:

```shell
composer test:types  # PHPStan (level 8) + TypeScript tsc --noEmit
```

### Git Hooks

The project includes pre-commit hooks that automatically format code. They are installed automatically via:

```shell
composer install
```

## Available Scripts

### Composer scripts

| Script | Description |
| --- | --- |
| `composer dev` | Start dev server (auto-detects environment) |
| `composer dev:ssr` | Start dev server + SSR Node.js server |
| `composer lint` | Format all code — fix mode (Rector + Pint + ESLint + Prettier) |
| `composer test:lint` | Dry-run lint checks without modifying files |
| `composer test:types` | Type analysis (PHPStan level 8 + TypeScript tsc) |
| `composer test` | Full test suite (lint + types + Pest) |
| `composer test:coverage` | Pest with code coverage (requires Xdebug) |
| `composer update:requirements` | Bump Composer + npm dependencies to latest |

### npm scripts

| Script | Description |
| --- | --- |
| `npm run dev` | Start Vite development server |
| `npm run build` | Build frontend assets for production |
| `npm run build:ssr` | Build both client and SSR bundles |
| `npm run lint` | ESLint check |
| `npm run lint:fix` | ESLint auto-fix |
| `npm run lint:styles` | Stylelint check |
| `npm run lint:styles:fix` | Stylelint auto-fix |
| `npm run format` | Prettier formatting (fix mode) |
| `npm run format:check` | Prettier dry-run |
| `npm run types` | TypeScript type-check (`tsc --noEmit`) |
| `npm run test` | Run Jest component tests |
| `npm run test:watch` | Jest in watch mode |
| `npm run test:coverage` | Jest with coverage report |

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
