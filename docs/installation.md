# Installation & Local Setup Guide
**Sistem Informasi & Statistik Perikanan Tangkap Provinsi Aceh**

```text
System: Sistem Informasi Perikanan Tangkap Aceh
Version: 1.0.0
Documentation Version: 1.0.0
Date: 21 September 2026
Status: Final Approved (F9 Audit Compliant)
```

---

## 1. System Requirements

Sebelum memulai instalasi, pastikan lingkungan pengembangan lokal telah memenuhi spesifikasi minimum:

* **PHP:** Versi 8.4.x dengan ekstensi wajib: `pdo_mysql`, `mbstring`, `openssl`, `bcmath`, `curl`, `gd`, `zip`, `fileinfo`.
* **Composer:** Versi 2.7+
* **Database:** MySQL 8.0+ atau MariaDB 10.6+
* **Node.js:** Versi 20.x atau 22.x LTS
* **NPM:** Versi 10.x+
* **Git:** Versi 2.40+

---

## 2. Step-by-Step Local Installation

### Langkah 1: Clone Repository
```bash
git clone https://github.com/ametatjeh/Sistem-Perikanan.git
cd Sistem-Perikanan
```

### Langkah 2: Install Dependensi PHP (Composer)
```bash
composer install --no-interaction --prefer-dist --optimize-autoloader
```

### Langkah 3: Install Dependensi JavaScript (NPM)
```bash
npm install
```

### Langkah 4: Konfigurasi File Environment
Salin berkas template `.env.example` menjadi `.env`:
```bash
cp .env.example .env
```

Buka file `.env` dan sesuaikan koneksi database MySQL lokal Anda:
```env
APP_NAME="Sistem Perikanan Aceh"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=sistem_perikanan
DB_USERNAME=root
DB_PASSWORD=YOUR_LOCAL_DB_PASSWORD
```

### Langkah 5: Generate Application Encryption Key
```bash
php artisan key:generate
```

### Langkah 6: Jalankan Migrasi Database & Seeder
Buat database baru di MySQL dengan nama `sistem_perikanan`, lalu jalankan:
```bash
php artisan migrate --seed
```

> [!CAUTION]
> **Peringatan Integritas:**
> Gunakan `php artisan migrate` biasa. **JANGAN** menjalankan `php artisan migrate:fresh` pada basis data yang telah memiliki data operasional aktif.

### Langkah 7: Import Referensi Standar Internasional (Opsional)
Jika memerlukan pembaruan katalog taksonomi FAO ASFIS dan alat tangkap ISSCFG:
```bash
php artisan asfis:import
```

### Langkah 8: Kompilasi Aset Frontend (Vite)
Untuk lingkungan pengembangan:
```bash
npm run dev
```
Atau untuk membangun bundle aset produksi:
```bash
npm run build
```

### Langkah 9: Jalankan Local Development Server
Buka terminal baru dan jalankan:
```bash
php artisan serve
```
Aplikasi kini dapat diakses melalui web browser pada:
👉 **`http://127.0.0.1:8000`**
