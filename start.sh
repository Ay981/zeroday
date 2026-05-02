#!/usr/bin/env bash

set -e

# Start the background worker using the configured queue connection.
# This avoids hardcoding Redis when the app is configured to use another driver.
QUEUE_CONNECTION="${QUEUE_CONNECTION:-database}"
php artisan queue:work "$QUEUE_CONNECTION" --tries=3 --timeout=90 &

# Ensure public storage files are web-accessible
php artisan storage:link --force >/dev/null 2>&1 || true

# Start the PHP server
# (Using artisan serve for simplicity on Render, 
# though in high-prod you'd use Nginx + FPM)
php artisan serve --host=0.0.0.0 --port=${PORT:-8000}