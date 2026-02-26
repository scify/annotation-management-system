<!-- omit in toc -->
# Local Development

<!-- omit in toc -->
## Table of Contents

- [1. Clone the Repository](#1-clone-the-repository)
- [2. Gitleaks Setup (Secret Scanning)](#2-gitleaks-setup-secret-scanning)
- [3. Environment Configuration](#3-environment-configuration)
  - [3.1 Base Environment (.env)](#31-base-environment-env)
  - [3.2 DDEV Environment (.env.ddev)](#32-ddev-environment-envddev)
  - [3.3 Native Environment (.env.native)](#33-native-environment-envnative)
- [4. Switching Between DDEV and Native](#4-switching-between-ddev-and-native)
  - [4.1 Using DDEV](#41-using-ddev)
  - [4.2 Using Native](#42-using-native)
- [5. Email Viewing](#5-email-viewing)
  - [5.1 DDEV](#51-ddev)
  - [5.2 Native](#52-native)
- [6. Tips - General Guidelines](#6-tips---general-guidelines)
  - [6.1 Keeping the dependencies up-to-date](#61-keeping-the-dependencies-up-to-date)
    - [6.1.1 Backend](#611-backend)
    - [6.1.2 Frontend](#612-frontend)
- [7. Where to Go From Here](#7-where-to-go-from-here)
- [8. Troubleshooting](#8-troubleshooting)

## 1. Clone the Repository

```shell
git clone git@github.com:scify/annotation-management-system.git

cd annotation-management-system
```

## 2. Gitleaks Setup (Secret Scanning)

The pre-commit hook scans staged files for secrets (API keys, passwords, tokens) before each commit. It requires a `./gitleaks` binary in the project root. The binary is gitignored, so **each developer must install it once**.

Download the binary for your platform:

```shell
# Linux (x64)
curl -sSL https://github.com/gitleaks/gitleaks/releases/download/v8.28.0/gitleaks_8.28.0_linux_x64.tar.gz | tar -xz gitleaks

# macOS (Apple Silicon)
curl -sSL https://github.com/gitleaks/gitleaks/releases/download/v8.28.0/gitleaks_8.28.0_darwin_arm64.tar.gz | tar -xz gitleaks

# macOS (Intel)
curl -sSL https://github.com/gitleaks/gitleaks/releases/download/v8.28.0/gitleaks_8.28.0_darwin_x64.tar.gz | tar -xz gitleaks
```

Make it executable and verify:

```shell
chmod +x gitleaks
./gitleaks version
```

The hook runs automatically on every `git commit`. If it finds a potential secret, the commit is blocked with a clear error message. For false positives, see [docs/GITLEAKS-SECURITY.md](GITLEAKS-SECURITY.md).

## 3. Environment Configuration

Annotation Management System uses different environment configurations based on whether you are running with **DDEV** or **Native**.
The application automatically loads the appropriate environment variables, based on the `APP_DEVELOPMENT_ENV`
environment variable.

### 3.1 Base Environment (.env)

The default `.env` file contains the general configuration, and is used by both **DDEV** and **Native**.
Copy the `.env.example` file to create a new `.env` file, and edit as needed:

```shell
cp .env.example .env

# then edit the APP_DEVELOPMENT_ENV variable to either 'ddev' or 'native'
APP_DEVELOPMENT_ENV=native # or 'ddev'
```

### 3.2 DDEV Environment (.env.ddev)

If you are using **DDEV**, you need a `.env.ddev` file with the following:

```ini
APP_URL = "https://annotation-management-system.ddev.site:8443"
DB_HOST = "db"
DB_DATABASE = "db"
DB_USERNAME = "db"
DB_PASSWORD = "db"
VITE_DEV_URL = "https://annotation-management-system.ddev.site"
VITE_APP_PORT = "8443"
VITE_DEV_PORT = "5179"
```

You can copy the `.env.ddev.example` file to create a new `.env.ddev` file:

```shell
cp .env.ddev.example .env.ddev
```

### 3.3 Native Environment (.env.native)

First you will need to set up a local database (MySQL, SQLite, etc) and create a new database for the application. Then, you can create a `.env.native` file with the appropriate database credentials.

If you are using a Native environment (Composer, PHP, SQL etc running locally), create a `.env.native` file with the following:

```ini
APP_URL = http://localhost:8000
DB_HOST = 127.0.0.1
DB_DATABASE = "my_app"
DB_USERNAME = "admin" # Change to your database username
DB_PASSWORD = "pass" # Change to your database password
VITE_DEV_URL = "http://localhost"
VITE_APP_PORT = "8000"
VITE_DEV_PORT = "5173"
```

You can copy the `.env.native.example` file to create a new `.env.native` file:

```shell
cp .env.native.example .env.native
```

## 4. Switching Between DDEV and Native

If you want to switch between **DDEV** and **Native** for development, you can set the `APP_DEVELOPMENT_ENV`
environment variable to either `ddev` or `native`.
**Note:** After switching environments, you will need to clear the config cache (both DDEV and Native):

```shell
ddev restart # If using DDEV

./clear-cache.sh
```

### 4.1 Using DDEV

First generate an application key:

```shell
ddev artisan key:generate
```

To start the development environment using **DDEV**:

```shell
ddev start
```

Run migrations:

```shell
ddev artisan migrate
```

Run the database seeder:

```shell
ddev artisan db:seed
```

Start the frontend development server:

```shell
ddev npm run dev
```

### 4.2 Using Native

First generate an application key:

```shell
php artisan key:generate
```

To switch back to using Native for local development:

```shell
composer install
```

Run migrations:

```shell
php artisan migrate
```

Run the database seeder:

```shell
php artisan db:seed
```

Start the development server:

```shell
composer run dev
```

## 5. Email Viewing

### 5.1 DDEV

When using **DDEV**, you can view emails sent by the application using [Mailpit](https://github.com/axllent/mailpit). [Read
more here](https://ddev.readthedocs.io/en/stable/users/usage/developer-tools/#email-capture-and-review-mailpit).

### 5.2 Native

When using **Native**, you can view emails sent by the application using one of the [methods
described here](https://laravel.com/docs/12.x/mail#mail-and-local-development).

## 6. Tips - General Guidelines

### 6.1 Keeping the dependencies up-to-date

#### 6.1.1 Backend

Run `composer outdated --direct` to check for outdated Composer dependencies, and update them as needed.

#### 6.1.2 Frontend

Use tools like [ncu](https://www.npmjs.com/package/npm-check-updates) to check for outdated NPM dependencies.

## 7. Where to Go From Here

- Take a look at `app/Providers/AppServiceProvider.php` to check the configuration.

## 8. Troubleshooting

- Ensure the correct environment file is loaded using `env('DB_HOST')` in Tinker.
  - For DDEV, run `ddev exec php artisan tinker`, and then run `env('DB_HOST')`.
  - For Native, run `php artisan tinker`, and then run `env('DB_HOST')`.
- If the frontend fails to load, ensure the correct environment variables are set.
  - For DDEV, ensure the `VITE_DEV_URL` and `VITE_APP_PORT` are set correctly.
  - For Native, ensure the `VITE_DEV_URL` and `VITE_APP_PORT` are set correctly.
- If Vite fails due to port conflicts, restart it using `pkill -f node`.
- Run `ddev restart` if database issues persist.

Enjoy developing! 🚀
