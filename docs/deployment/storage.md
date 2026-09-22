# Storage & Filesystem Architecture
**Sistem Informasi & Statistik Perikanan Tangkap Provinsi Aceh**

```text
System: Sistem Informasi Perikanan Tangkap Aceh
Stage: F12 — Storage & Filesystem Architecture
Document Type: Infrastructure Storage Configuration
Date: 21 September 2026
```

---

## 1. Directory Structure & Layout

Aplikasi mengorganisasikan direktori penyimpanan lokal sebagai berikut:

```text
storage/
├── app/
│   ├── private/              # Berkas privat aplikasi internal
│   └── public/               # Berkas unggahan publik (foto kapal, dokumen impor)
├── framework/
│   ├── cache/                # Berkas cache file (jika driver file aktif)
│   ├── sessions/             # Berkas sesi (jika driver file aktif)
│   └── views/                # Berkas kompilasi template Blade
└── logs/
    └── laravel-YYYY-MM-DD.log # Berkas log aplikasi harian
```

---

## 2. Public Storage Symbolic Link

Untuk menghubungkan berkas pada `storage/app/public` agar dapat diakses oleh browser melalui web root:

```bash
php artisan storage:link
```

Perintah ini akan membuat symbolic link:
`public/storage` $\longrightarrow$ `storage/app/public`

*Status Audit Aktual:* Symbolic link `public/storage` telah terverifikasi aktif pada sistem (`storage.public/storage: true`).

---

## 3. Linux File Ownership & Permissions

Pada server produksi Linux / Ubuntu Server dengan web server Nginx/Apache:

```bash
# Set kepemilikan kepada user web server (www-data)
sudo chown -R www-data:www-data /var/www/sistem-perikanan

# Berikan izin baca-tulis pada storage dan cache
sudo chmod -R 775 /var/www/sistem-perikanan/storage
sudo chmod -R 775 /var/www/sistem-perikanan/bootstrap/cache
```

---

## 4. File Upload Validation & Security

Seluruh modul unggahan berkas (e.g., impor Excel referensi atau dokumen pendaftaran) menerapkan:
* **MIME-Type Whitelisting:** Membatasi hanya format yang diizinkan (`application/vnd.openxmlformats-officedocument.spreadsheetml.sheet`, `text/csv`, `image/jpeg`, `image/png`, `application/pdf`).
* **Ukuran Maksimum:** Pembatasan `max:10240` (10 MB).
* **Sanitasi Nama Berkas:** Menggunakan hash nama unik acak (`Str::random()`) untuk mencegah eksekusi skrip berbahaya (*Arbitrary File Upload Attack*).
