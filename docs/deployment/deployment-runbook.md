# Production Deployment Runbook
**Sistem Informasi & Statistik Perikanan Tangkap Provinsi Aceh**

```text
System: Sistem Informasi Perikanan Tangkap Aceh
Stage: F12 — Deployment Runbook
Document Type: Standard Operating Procedure (SOP)
Date: 21 September 2026
```

---

## 1. Fase 1: Pra-Deployment (Pre-Deployment Checklist)

Jalankan langkah-langkah persiapan sebelum menyentuh kode produksi:

```bash
# 1. Masuk ke direktori aplikasi
cd /var/www/sistem-perikanan

# 2. Buat backup database pra-rilis wajib
mysqldump -u sistem_user -p --single-transaction sistem_perikanan_prod | gzip > /backups/database/pre_deploy_$(date +%Y%m%d_%H%M%S).sql.gz

# 3. Verifikasi file backup tidak korup
gzip -t /backups/database/pre_deploy_*.sql.gz

# 4. Aktifkan Mode Pemeliharaan (Maintenance Mode)
php artisan down --secret="DEPLOYMENT_BYPASS_TOKEN" --render="errors.503"
```

---

## 2. Fase 2: Eksekusi Deployment (Deployment Steps)

Eksekusi pembaruan kode, dependensi, basis data, dan kompilasi aset:

```bash
# 1. Unduh rilis kode sumber terbaru dari Git
git fetch origin main
git checkout main
git pull origin main

# 2. Install dependensi PHP produksi teroptimasi
composer install --no-dev --prefer-dist --optimize-autoloader --no-interaction

# 3. Kompilasi aset frontend produksi (Vite + Tailwind)
npm ci --no-interaction
npm run build

# 4. Jalankan migrasi basis data non-destruktif
php artisan migrate --force --no-interaction

# 5. Pastikan symbolic link storage aktif
php artisan storage:link

# 6. Bangun cache produksi Laravel
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# 7. Restart background queue worker & reload PHP-FPM
php artisan queue:restart
sudo systemctl reload php8.4-fpm
```

---

## 3. Fase 3: Pasca-Deployment (Post-Deployment Verification)

Lakukan verifikasi operasional sebelum menonaktifkan mode pemeliharaan:

```bash
# 1. Jalankan pengujian smoke test pada localhost (via bypass token)
curl -I https://perikanan.acehprov.go.id/gis/data

# 2. Periksa log error aplikasi terkini
tail -n 30 storage/logs/laravel-$(date +%Y-%m-%d).log

# 3. Jika seluruh verifikasi berhasil, nonaktifkan Maintenance Mode
php artisan up

# 4. Verifikasi halaman publik dapat diakses umum (HTTP 200)
curl -I https://perikanan.acehprov.go.id/
curl -I https://perikanan.acehprov.go.id/statistik
```
