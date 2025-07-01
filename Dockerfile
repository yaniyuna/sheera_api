# # Gunakan basis image PHP 8.2 resmi yang ringan dari Alpine Linux
# FROM php:8.2-fpm-alpine

# # Install semua dependensi sistem dan ekstensi PHP yang dibutuhkan Laravel
# # dalam satu blok perintah RUN yang efisien untuk menghemat layer.
# RUN apk add --no-cache \
#     # Dependensi yang dibutuhkan saat aplikasi berjalan
#     postgresql-libs \
#     libzip \
#     libpng \
#     # Dependensi sementara yang dibutuhkan hanya saat proses build, akan dihapus nanti
#     && apk add --no-cache --virtual .build-deps $PHPIZE_DEPS postgresql-dev libzip-dev libpng-dev \
#     # Perintah untuk meng-install ekstensi-ekstensi PHP yang kita butuhkan
#     && docker-php-ext-install pdo pdo_pgsql bcmath zip gd \
#     # Hapus dependensi build yang sudah tidak diperlukan untuk memperkecil ukuran image
#     && apk del .build-deps

# # Install Composer (dependency manager untuk PHP) secara global
# COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# # Atur direktori kerja utama di dalam container
# WORKDIR /app

# # Salin semua file proyek Anda dari komputer ke dalam container
# COPY . .

# # Install semua package Laravel dari composer.json untuk mode produksi
# RUN composer install --no-interaction --no-plugins --no-scripts --prefer-dist --no-dev

# # Atur izin folder storage dan cache agar bisa ditulis oleh server. Ini sangat penting.
# RUN chown -R www-data:www-data /app/storage /app/bootstrap/cache
# RUN chmod -R 775 /app/storage /app/bootstrap/cache

# # Beri jeda 20 detik untuk memastikan layanan database di Fly.io sudah siap menerima koneksi.
# RUN echo "Waiting 20 seconds for database to be ready..." && sleep 20

# # Jalankan migrasi DATABASE di sini, di dalam proses build Docker.
# RUN echo "Running database migrations..." && php artisan migrate --force

# # Generate cache yang dibutuhkan Laravel untuk mempercepat aplikasi.
# RUN echo "Generating caches..." && php artisan view:cache && php artisan route:cache
# # Catatan: config:cache sengaja kita nonaktifkan agar mudah di-debug jika perlu mengubah .env
# # RUN php artisan config:cache

# # Beritahu Docker bahwa aplikasi kita akan berjalan di port 8000 di dalam container
# EXPOSE 8000

# # Perintah terakhir untuk 'menyalakan' server Laravel saat container dimulai
# CMD ["php", "artisan", "serve", "--host", "0.0.0.0", "--port", "8000"]