#!/usr/bin/env bash

set -Eeuo pipefail

# Run on an already configured server: sudo bash /var/www/nikah/deploy.sh
# The checkout needs .env, APP_KEY, database credentials, and vendor/ installed.
# Nginx must serve /var/www/nikah/public. Take a database backup before deploying.
# Override defaults with environment variables, e.g. PHP_FPM_SERVICE=php8.5-fpm.
APP_DIR="${APP_DIR:-/var/www/nikah}"
BRANCH="${BRANCH:-main}"
PHP_BIN="${PHP_BIN:-php}"
COMPOSER_BIN="${COMPOSER_BIN:-composer}"
PHP_FPM_SERVICE="${PHP_FPM_SERVICE:-php8.4-fpm}"
WEB_USER="${WEB_USER:-www-data}"
WEB_GROUP="${WEB_GROUP:-www-data}"

fail() {
    echo "Error: $*" >&2
    exit 1
}

[[ "$EUID" -eq 0 ]] || fail "Run this script as root (or with sudo) to manage permissions and services."
cd "$APP_DIR"

for binary in git "$PHP_BIN" "$COMPOSER_BIN" node npm flock nginx systemctl; do
    command -v "$binary" >/dev/null 2>&1 || fail "Required command is missing: $binary"
done

[[ -f artisan && -f .env && -f vendor/autoload.php && -f composer.lock && -f package-lock.json ]] \
    || fail "$APP_DIR must be a configured Laravel checkout with .env, vendor/, and dependency lock files."
git rev-parse --is-inside-work-tree >/dev/null
id "$WEB_USER" >/dev/null 2>&1 || fail "Web user '$WEB_USER' does not exist."
getent group "$WEB_GROUP" >/dev/null || fail "Web group '$WEB_GROUP' does not exist."

exec 9>"$(git rev-parse --git-path nikah-deploy.lock)"
flock -n 9 || fail "Another deployment is running."

[[ -z "$(git status --porcelain --untracked-files=no)" ]] \
    || fail "The server checkout has tracked changes. Commit or resolve them before deploying."
[[ "$(git branch --show-current)" == "$BRANCH" ]] \
    || fail "Check out '$BRANCH' before running this script."
[[ ! -f storage/framework/down ]] || fail "The application is already in maintenance mode. Resolve it before deploying."

"$PHP_BIN" -r 'exit(PHP_VERSION_ID >= 80400 ? 0 : 1);' || fail "PHP 8.4 or newer is required."
node -e 'const [major, minor] = process.versions.node.split(".").map(Number); process.exit(major > 22 || (major === 22 && minor >= 12) || (major === 20 && minor >= 19) ? 0 : 1);' \
    || fail "Vite requires Node.js 20.19+ or 22.12+."
nginx -t
systemctl is-active --quiet "$PHP_FPM_SERVICE" || fail "PHP-FPM service '$PHP_FPM_SERVICE' is not running."

echo "Fetching origin/$BRANCH..."
git fetch origin "$BRANCH"
git merge-base --is-ancestor HEAD "origin/$BRANCH" \
    || fail "The server branch has diverged from origin/$BRANCH. Resolve it before deploying."

maintenance_enabled=false
finish() {
    local exit_code=$?
    if (( exit_code != 0 )); then
        echo "Deployment failed (exit code $exit_code). No automatic rollback was performed." >&2
        if [[ "$maintenance_enabled" == true ]]; then
            chown -R "$WEB_USER:$WEB_GROUP" storage bootstrap/cache || true
            chmod -R ug+rwX storage bootstrap/cache || true
            echo "Maintenance mode remains enabled. Fix the error and rerun the failed steps; run '$PHP_BIN artisan up --no-interaction' in $APP_DIR only after recovery." >&2
        fi
    fi
}
trap finish EXIT
trap 'exit 130' INT
trap 'exit 143' TERM

echo "Deploying $BRANCH to $APP_DIR..."
"$PHP_BIN" artisan down --retry=60 --no-interaction
maintenance_enabled=true
git merge --ff-only "origin/$BRANCH"

COMPOSER_ALLOW_SUPERUSER=1 "$COMPOSER_BIN" install \
    --no-dev --no-interaction --prefer-dist --optimize-autoloader
"$COMPOSER_BIN" check-platform-reqs --no-dev --no-interaction

# Vite is a dev dependency, so include it even when NODE_ENV=production.
npm ci --include=dev
npm run build
rm -f public/hot

# Clear compiled files without flushing application data or requiring cache tables.
"$PHP_BIN" artisan config:clear --no-interaction
"$PHP_BIN" artisan route:clear --no-interaction
"$PHP_BIN" artisan event:clear --no-interaction
"$PHP_BIN" artisan view:clear --no-interaction
"$PHP_BIN" artisan migrate --force --no-interaction
"$PHP_BIN" artisan storage:link --no-interaction
"$PHP_BIN" artisan config:cache --no-interaction
"$PHP_BIN" artisan route:cache --no-interaction
"$PHP_BIN" artisan event:cache --no-interaction
"$PHP_BIN" artisan view:cache --no-interaction

chown -R "$WEB_USER:$WEB_GROUP" storage bootstrap/cache
chmod -R ug+rwX storage bootstrap/cache

# Signals existing workers; Supervisor/systemd must already manage them.
"$PHP_BIN" artisan queue:restart --no-interaction
systemctl reload "$PHP_FPM_SERVICE"
nginx -t
systemctl reload nginx
"$PHP_BIN" artisan up --no-interaction
maintenance_enabled=false

echo "Deployment completed successfully."
