# Environment & Server Readiness Checklist
**Sistem Informasi & Statistik Perikanan Tangkap Provinsi Aceh**

```text
System: Sistem Informasi Perikanan Tangkap Aceh
Stage: F12 — Environment Checklist
Document Type: Infrastructure & Configuration Audit
Date: 21 September 2026
```

---

## 1. Project Runtime & Component Inventory

Berdasarkan audit aktual sistem (`php artisan about`):

| Komponen | Terdeteksi di Sistem | Spesifikasi Wajib Produksi | Status Kesiapan |
| :--- | :---: | :---: | :---: |
| **Laravel Framework** | `v13.32.0` | `v13.x` | ✅ **VERIFIED** |
| **PHP Runtime** | `v8.4.16` | `^8.4` | ✅ **VERIFIED** |
| **Composer** | `v2.10.1` | `^2.7` | ✅ **VERIFIED** |
| **Database Driver** | `mysql` (PDO MySQL) | MariaDB 10.11+ / MySQL 8.0+ | ✅ **VERIFIED** |
| **Session Driver** | `database` (`sessions` table) | `database` / `redis` | ✅ **VERIFIED** |
| **Cache Store** | `database` (`cache` table) | `database` / `redis` | ✅ **VERIFIED** |
| **Queue Connection** | `database` (`jobs` table) | `database` / `redis` | ✅ **VERIFIED** |
| **Asset Bundler** | `Vite v8.0.0` + Tailwind CSS v4 | Node.js 20.x/22.x LTS | ✅ **VERIFIED** |
| **Peta Spasial (GIS)** | `MapLibre GL JS v4.7.1` | WebGL Canvas Compatible | ✅ **VERIFIED** |
| **Permissions Engine** | `Spatie Permission v8.3.0` | Active (Default Features) | ✅ **VERIFIED** |
| **Storage Symbolic Link** | `public/storage` linked | Symlink active | ✅ **VERIFIED** |

---

## 2. PHP Extension Checklist

Pastikan seluruh ekstensi PHP 8.4 berikut aktif pada server target produksi (`php -m`):

```text
[x] pdo_mysql      — Driver koneksi basis data MySQL/MariaDB
[x] mbstring       — Pemrosesan teks multibyte
[x] openssl        — Enkripsi sesi dan keamanan data
[x] bcmath         — Presisi perhitungan angka desimal metrik perikanan
[x] ctype          — Validasi tipe karakter
[x] curl           — Komunikasi HTTP REST API eksternal
[x] fileinfo       — Deteksi MIME-type unggahan berkas
[x] gd             — Manipulasi dan ekspor gambar grafik
[x] zip            — Pembuatan dan pembacaan berkas spreadsheet Excel (.xlsx)
[x] tokenizer      — Kompilasi kode sumber Blade dan caching
[x] xml            — Parsing dokumen terstruktur dan API
```

---

## 3. Production Environment Configuration (`.env`)

Template konfigurasi variabel lingkungan yang wajib dipersiapkan pada server produksi:

```env
APP_NAME="Sistem Informasi & Statistik Perikanan Tangkap Aceh"
APP_ENV=production
APP_KEY=base64:CONFIGURED_SECURE_KEY
APP_DEBUG=false
APP_URL=https://perikanan.acehprov.go.id

LOG_CHANNEL=daily
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=sistem_perikanan_prod
DB_USERNAME=sistem_user
DB_PASSWORD=STRONG_SECURE_PASSWORD

SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_ENCRYPT=true
SESSION_PATH=/
SESSION_DOMAIN=perikanan.acehprov.go.id
SESSION_SECURE_COOKIE=true
SESSION_HTTP_ONLY=true
SESSION_SAME_SITE=lax

CACHE_STORE=database
QUEUE_CONNECTION=database
FILESYSTEM_DISK=public
```

---

## 4. Environment Variables Audit & Secrets Isolation

| Variabel Konfigurasi | Status Konfigurasi | Keterangan & Aturan Keamanan |
| :--- | :---: | :--- |
| `APP_NAME` | `configured` | Nama resmi aplikasi |
| `APP_ENV` | `configured` (Wajib `production`) | Menjamin penonaktifan mode pengembangan |
| `APP_KEY` | `configured` (Base64 Enkripsi) | Kunci enkripsi 32-byte (Dilarang dibocorkan) |
| `APP_DEBUG` | `configured` (Wajib `false`) | Mencegah kebocoran stack trace dan query SQL ke pengguna |
| `APP_URL` | `configured` | Domain resmi dengan protokol HTTPS |
| `DB_CONNECTION` | `configured` (`mysql`) | Driver basis data |
| `DB_HOST` / `DB_PORT` | `configured` | Host lokal / internal container basis data |
| `DB_DATABASE` | `configured` | Nama database terisolasi |
| `DB_USERNAME` | `configured` | Pengguna database hak akses non-root |
| `DB_PASSWORD` | `configured` | Kata sandi database terlindungi |
| `SESSION_DRIVER` | `configured` (`database`) | Penyimpanan sesi persisten di database |
| `CACHE_STORE` | `configured` (`database`) | Cache persisten |
| `QUEUE_CONNECTION` | `configured` (`database`) | Antrean background jobs |
| `GFW_API_KEY` | `configured` | Token server-side satelit GFW (Terisolasi dari client) |

> [!CAUTION]
> **Status Server Fisik & Domain Produksi:**
> Karena server produksi fisik dan domain resmi saat ini berada di bawah kewenangan Dinas Komunikasi, Informatika dan Persandian (Diskominfo) / DKP Aceh dan belum dialokasikan akses IP/SSH langsung, maka status penyediaan infrastruktur server dicatat: **NOT VERIFIED / PENDING PROVISIONING**.
