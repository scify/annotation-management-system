#!/bin/bash

# Usage: ./set-laravel-permissions-ci.sh /path/to/laravel
# In GitHub Actions, just point to the Laravel app root (default: current directory)

set -e

LARAVEL_ROOT="${1:-.}"

# Only touch files this user owns. Runtime-generated files (e.g. storage/logs)
# are owned by the web-server user; chmod'ing them here would fail with EPERM
# and abort the deploy under `set -e`. They're already managed by the app.
OWNED_BY=( -uid "$(id -u)" )

echo "🔧 Setting Laravel file permissions in CI environment at: $LARAVEL_ROOT"

# General project-wide permissions (skip VCS/build dirs; batch chmod calls)
PRUNE=( \( -path "$LARAVEL_ROOT/.git" -o -path "$LARAVEL_ROOT/node_modules" \) -prune -o )

echo "📁 Setting directory permissions to 755..."
find "$LARAVEL_ROOT" "${PRUNE[@]}" "${OWNED_BY[@]}" -type d -exec chmod 755 {} +

echo "📄 Setting file permissions to 644..."
find "$LARAVEL_ROOT" "${PRUNE[@]}" "${OWNED_BY[@]}" -type f -exec chmod 644 {} +

# Writable directories: storage & bootstrap/cache
echo "📝 Making storage/ and bootstrap/cache/ writable..."
find "$LARAVEL_ROOT/storage" "$LARAVEL_ROOT/bootstrap/cache" "${OWNED_BY[@]}" -type d -exec chmod 775 {} +
find "$LARAVEL_ROOT/storage" "$LARAVEL_ROOT/bootstrap/cache" "${OWNED_BY[@]}" -type f -exec chmod 664 {} +

echo "✅ CI permissions set successfully."
