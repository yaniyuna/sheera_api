# basis image PHP 8.2 resmi dengan FPM-Alpine (lebih ringan)
FROM php:8.2-fpm-alpine AS builder

# Install dependencies yang dibutuhkan oleh Laravel & Composer
RUN apk add --no-cache --virtual .build-deps $PHPIZE_DEPS \
    && apk add --no-cache postgresql-dev \
    && docker-php-ext-install pdo pdo_pgsql \
    && apk del .build-deps

# Install Composer (dependency manager untuk PHP)
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Atur direktori kerja di dalam container
WORKDIR /app

# Copy file composer terlebih dahulu untuk caching
COPY composer.json composer.lock ./
# Install dependencies proyek tanpa dev-dependencies
RUN composer install --no-interaction --no-plugins --no-scripts --prefer-dist --no-dev

# Copy seluruh sisa file proyek
COPY . .

# Generate key aplikasi dan cache untuk production
RUN php artisan key:generate --force
RUN php artisan config:cache
RUN php artisan route:cache
RUN php artisan view:cache

# --- Final Image ---
FROM php:8.2-fpm-alpine

# Install ekstensi postgresql saja yang dibutuhkan saat runtime
RUN apk add --no-cache postgresql-libs

WORKDIR /app

# Copy file yang sudah di-build dari tahap 'builder'
COPY --from=builder /app .

# Atur kepemilikan file agar bisa ditulis oleh server
RUN chown -R www-data:www-data /app/storage /app/bootstrap/cache
RUN chmod -R 775 /app/storage /app/bootstrap/cache

# Expose port yang akan digunakan
EXPOSE 8000

# Perintah untuk menjalankan server saat container dimulai
CMD ["php", "artisan", "serve", "--host", "0.0.0.0", "--port", "8000"]