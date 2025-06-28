#!/usr/bin/env bash
# exit on error
set -o errexit

# Perintah yang akan dijalankan oleh Render
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan migrate --force