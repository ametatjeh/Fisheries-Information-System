#!/usr/bin/env bash
# ==============================================================================
# Script Deploy Otomatis - Sistem Perikanan (Laravel on Ubuntu/Debian VPS)
# ==============================================================================

set -e

echo "🚀 Memulai proses deploy Sistem Perikanan..."

# 1. Masuk ke mode maintenance sementara
php artisan down || true

# 2. Ambil update kode terbaru dari GitHub
echo "📦 Mengambil perubahan terbaru dari Git..."
git pull origin main

# 3. Install dependencies PHP untuk production
echo "🐘 Menginstal Composer dependencies..."
composer install --no-dev --optimize-autoloader --no-interaction

# 4. Install dependencies Node.js & Compile assets frontend
echo "🎨 Membangun aset frontend (Vite)..."
npm ci || npm install
npm run build

# 5. Jalankan migrasi database
echo "🗄️ Menjalankan migrasi database..."
php artisan migrate --force

# 6. Bersihkan dan optimalkan cache Laravel
echo "⚡ Mengoptimalkan cache sistem..."
php artisan optimize:clear
php artisan config:cache
php artisan event:cache
php artisan route:cache
php artisan view:cache

# 7. Pastikan storage link tersedia
php artisan storage:link || true

# 8. Set permission folder storage dan bootstrap/cache
echo "🔒 Menyesuaikan izin folder (permissions)..."
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache

# 9. Keluar dari mode maintenance
php artisan up

echo "✅ Deploy selesai dengan sukses! Sistem Perikanan aktif."
