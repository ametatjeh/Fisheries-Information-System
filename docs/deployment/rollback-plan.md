# Emergency Rollback Plan
**Sistem Informasi & Statistik Perikanan Tangkap Provinsi Aceh**

```text
System: Sistem Informasi Perikanan Tangkap Aceh
Stage: F12 — Rollback Plan
Document Type: Incident Response & Disaster Recovery SOP
Date: 21 September 2026
```

---

## 1. Kriteria Pemicu Rollback (Rollback Trigger Conditions)

Rollback darurat wajib segera dieksekusi apabila setelah deployment ditemukan salah satu kondisi berikut:
1. **HTTP 500 Fatal Error:** Halaman utama (`/`), dashboard, atau `/statistik` mengalami fatal exception yang tidak dapat diperbaiki dalam 5 menit.
2. **Kegagalan Endpoint Spasial:** Endpoint `GET /gis/data` mengalami crash atau mengembalikan payload tidak valid sehingga peta WebGIS gagal total.
3. **Kegagalan Autentikasi / RBAC:** Pengguna tidak dapat login atau terjadi kebocoran otorisasi hak akses.
4. **Kerusakan Kompilasi Frontend:** Aset JavaScript/CSS rusak dan kanvas MapLibre tidak terinisialisasi.

---

## 2. Prosedur Rollback Rilis Aplikasi (Application Code Rollback)

```bash
# 1. Aktifkan Maintenance Mode
php artisan down --message="Sistem sedang melakukan pemulihan rilis. Mohon menunggu beberapa menit."

# 2. Kembalikan kode sumber ke commit / tag rilis stabil sebelumnya
git checkout PREVIOUS_STABLE_TAG_OR_COMMIT_HASH

# 3. Install ulang dependensi PHP rilis stabil
composer install --no-dev --prefer-dist --optimize-autoloader --no-interaction

# 4. Bangun ulang aset frontend rilis stabil
npm ci --no-interaction
npm run build

# 5. Bersihkan dan bangun kembali cache aplikasi
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear

php artisan config:cache
php artisan route:cache
php artisan view:cache

# 6. Reload worker PHP-FPM
sudo systemctl reload php8.4-fpm
php artisan queue:restart

# 7. Nonaktifkan Maintenance Mode
php artisan up
```

---

## 3. Prosedur Rollback Basis Data (Database Rollback — Last Resort)

> [!CAUTION]
> **Kebijakan Pemulihan Basis Data:**
> Pemulihan database dari cadangan (*database restore*) adalah **opsi terakhir (last resort)** yang hanya diambil jika skema database mengalami korupsi data fatal yang tidak dapat diselesaikan via patch kode.

```bash
# 1. Pastikan maintenance mode aktif
php artisan down

# 2. Pulihkan database dari file backup pra-deployment
gunzip < /backups/database/pre_deploy_TIMESTAMP.sql.gz | mysql -u sistem_user -p sistem_perikanan_prod

# 3. Bersihkan cache database
php artisan cache:clear

# 4. Verifikasi dan buka kembali layanan
php artisan up
```
