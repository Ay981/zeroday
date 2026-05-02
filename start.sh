#!/usr/bin/env bash

# Start the background worker
php artisan queue:work redis --tries=3 --timeout=90 &

# Ensure public storage files are web-accessible
php artisan storage:link --force >/dev/null 2>&1 || true

# Start the PHP server
# (Using artisan serve for simplicity on Render, 
# though in high-prod you'd use Nginx + FPM)
php artisan serve --host=0.0.0.0 --port=${PORT:-8000}