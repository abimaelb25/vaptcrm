#!/usr/bin/env bash
set -euo pipefail

DEPLOY_PATH="${DEPLOY_PATH:-}"
BRANCH="${BRANCH:-main}"
RUN_MIGRATIONS="${RUN_MIGRATIONS:-1}"
RUN_NPM_BUILD="${RUN_NPM_BUILD:-auto}"
MAINTENANCE_MODE="${MAINTENANCE_MODE:-1}"

if [ -z "$DEPLOY_PATH" ]; then
  echo "DEPLOY_PATH is required"
  exit 1
fi

if [ ! -d "$DEPLOY_PATH" ]; then
  echo "Deploy path not found: $DEPLOY_PATH"
  exit 1
fi

cd "$DEPLOY_PATH"

if [ ! -f artisan ] || [ ! -d .git ]; then
  echo "Invalid Laravel git project at: $DEPLOY_PATH"
  exit 1
fi

APP_DOWN=0

cleanup() {
  if [ "$APP_DOWN" = "1" ]; then
    php artisan up || true
  fi
}

trap cleanup EXIT

echo "Deploying branch '$BRANCH' at '$DEPLOY_PATH'"

git fetch origin "$BRANCH"
git checkout "$BRANCH"
git pull --ff-only origin "$BRANCH"

if [ "$MAINTENANCE_MODE" = "1" ]; then
  php artisan down || true
  APP_DOWN=1
fi

if command -v composer >/dev/null 2>&1; then
  composer install --no-dev --prefer-dist --optimize-autoloader --no-interaction
else
  echo "Composer not found in PATH"
  exit 1
fi

if [ "$RUN_NPM_BUILD" = "yes" ]; then
  if command -v npm >/dev/null 2>&1; then
    npm ci
    npm run build
  else
    echo "NPM build requested but npm is not available"
    exit 1
  fi
fi

if [ "$RUN_NPM_BUILD" = "auto" ] && command -v npm >/dev/null 2>&1; then
  npm ci
  npm run build
fi

php artisan optimize:clear

if [ "$RUN_MIGRATIONS" = "1" ]; then
  php artisan migrate --force
fi

php artisan config:cache
php artisan route:cache
php artisan view:cache

if [ "$APP_DOWN" = "1" ]; then
  php artisan up
  APP_DOWN=0
fi

echo "Deploy finished successfully"
