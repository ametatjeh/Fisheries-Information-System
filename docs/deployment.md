# Production Deployment & Operations Guide
**Sistem Informasi & Statistik Perikanan Tangkap Provinsi Aceh**

```text
System: Sistem Informasi Perikanan Tangkap Aceh
Version: 1.0.0
Documentation Version: 1.0.0
Date: 21 September 2026
Status: Final Approved (F9 Audit Compliant)
```

---

## 1. Production Server Requirements

* **Sistem Operasi:** Ubuntu Server 24.04 LTS / Debian 12
* **Web Server:** Nginx (disarankan) atau Apache 2.4 dengan mod_rewrite
* **PHP Runtime:** PHP 8.4-FPM
* **Database Server:** MariaDB 10.11+ atau MySQL 8.0+
* **Process Manager:** Supervisor (untuk background queue jobs)
* **SSL / TLS:** Let's Encrypt Certbot atau Sertifikat SSL Komersial (HTTPS Wajib)

---

## 2. Production Environment Configuration

Konfigurasi `.env` pada server produksi harus mematuhi standar pengamanan ketat:

```env
APP_NAME="Sistem Informasi Perikanan Aceh"
APP_ENV=production
APP_KEY=base64:YOUR_GENERATED_PRODUCTION_KEY
APP_DEBUG=false
APP_URL=https://perikanan.acehprov.go.id

LOG_CHANNEL=daily
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=sistem_perikanan_prod
DB_USERNAME=sistem_user
DB_PASSWORD=STRONG_PRODUCTION_DB_PASSWORD

SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_ENCRYPT=true
SESSION_PATH=/
SESSION_DOMAIN=perikanan.acehprov.go.id
SESSION_SECURE_COOKIE=true
SESSION_HTTP_ONLY=true
SESSION_SAME_SITE=lax

QUEUE_CONNECTION=database
CACHE_STORE=database
```

---

## 3. Production Deployment Step-by-Step

### 1. Update Repository & Install Dependensi
```bash
git pull origin main
composer install --no-dev --prefer-dist --optimize-autoloader --no-interaction
npm ci
npm run build
```

### 2. Jalankan Migrasi Database Aman
```bash
php artisan migrate --force
```

### 3. Buat Symbolic Link Storage
```bash
php artisan storage:link
```

### 4. Optimasi Cache Laravel
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
```

### 5. Atur Hak Akses Direktori Linux
Pastikan direktori `storage` dan `bootstrap/cache` dapat ditulis oleh user web server (`www-data`):
```bash
sudo chown -R www-data:www-data /var/www/sistem-perikanan
sudo chmod -R 775 /var/www/sistem-perikanan/storage
sudo chmod -R 775 /var/www/sistem-perikanan/bootstrap/cache
```

---

## 4. Nginx Server Block Configuration

Contoh konfigurasi Nginx aman (`/etc/nginx/sites-available/sistem-perikanan`):

```nginx
server {
    listen 80;
    server_name perikanan.acehprov.go.id;
    return 301 https://$host$request_uri;
}

server {
    listen 443 ssl http2;
    server_name perikanan.acehprov.go.id;
    root /var/www/sistem-perikanan/public;

    ssl_certificate /etc/letsencrypt/live/perikanan.acehprov.go.id/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/perikanan.acehprov.go.id/privkey.pem;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers HIGH:!aNULL:!MD5;

    index index.php index.html;
    charset utf-8;

    # Security Headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header Referrer-Policy "no-referrer-when-downgrade" always;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.4-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

---

## 5. Deployment Verification Checklist

```text
[ ] PHP 8.4-FPM & Ekstensi terpasang lengkap
[ ] MySQL/MariaDB aktif dan database berhasil dibuat
[ ] .env terkonfigurasi dengan APP_ENV=production & APP_DEBUG=false
[ ] APP_KEY ter-generate dan diamankan
[ ] Dependensi Composer --no-dev terinstall
[ ] Aset frontend ter-build (npm run build)
[ ] php artisan migrate --force selesai tanpa error
[ ] php artisan storage:link berhasil
[ ] Cache aktif (config, route, view)
[ ] Permissions www-data pada storage & bootstrap/cache valid (775)
[ ] HTTPS / SSL aktif dan HTTP dialihkan ke HTTPS
[ ] Endpoint GET /gis/data mengembalikan HTTP 200 JSON
[ ] Halaman /statistik dapat diakses publik tanpa login
[ ] Modul login dan auth berfungsi normal
```

---

## 6. Backup & Disaster Recovery Strategy

### A. Backup Otomatis Database Harian
Gunakan cron job untuk dump database setiap malam:
```bash
0 2 * * * mysqldump -u sistem_user -pSTRONG_PASSWORD sistem_perikanan_prod | gzip > /backups/db/sistem_perikanan_$(date +\%F).sql.gz
```

### B. Prosedur Restore Database
Untuk memulihkan data jika terjadi insiden darurat:
```bash
gunzip < /backups/db/sistem_perikanan_YYYY-MM-DD.sql.gz | mysql -u sistem_user -pSTRONG_PASSWORD sistem_perikanan_prod
php artisan cache:clear
```
