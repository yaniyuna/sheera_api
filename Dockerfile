# Stage 1: Builder - Untuk menginstall dependensi dan menyiapkan file
FROM php:8.2-fpm-alpine AS builder

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app

# Copy file composer & install dependensi Laravel
COPY composer.json composer.lock ./
# Hapus --no-dev agar package untuk testing (jika ada) ikut terinstall
RUN composer install --no-interaction --no-plugins --no-scripts --prefer-dist

# Copy sisa file proyek
COPY . .

# Jalankan perintah build Laravel
RUN php artisan view:cache
RUN php artisan route:cache

# RUN php artisan config:cache 
RUN php artisan storage:link


# Stage 2: Final Image - Image yang akan dijalankan di produksi
FROM php:8.2-fpm-alpine

# Install semua ekstensi PHP yang dibutuhkan oleh Laravel dalam satu perintah
RUN apk add --no-cache \
    # Dependensi yang dibutuhkan saat runtime
    postgresql-libs \
    libzip \
    libpng \
    # Dependensi sementara yang dibutuhkan hanya saat build
    && apk add --no-cache --virtual .build-deps $PHPIZE_DEPS postgresql-dev libzip-dev libpng-dev \
    # Install ekstensi PHP
    && docker-php-ext-install pdo pdo_pgsql bcmath zip gd \
    # Hapus dependensi build yang sudah tidak diperlukan untuk memperkecil ukuran image
    && apk del .build-deps
# ---------------------------------------------

WORKDIR /app

# Copy file yang sudah di-build dari tahap 'builder'
COPY --from=builder /app .

# Atur kepemilikan file agar bisa ditulis oleh web server
RUN chown -R www-data:www-data /app/storage /app/bootstrap/cache
RUN chmod -R 775 /app/storage /app/bootstrap/cache

# Expose port
EXPOSE 8000

# Perintah untuk menjalankan server
CMD ["php", "artisan", "serve", "--host", "0.0.0.0", "--port", "8000"]