# Production Monitoring & Observability
**Sistem Informasi & Statistik Perikanan Tangkap Provinsi Aceh**

```text
System: Sistem Informasi Perikanan Tangkap Aceh
Stage: F12 — Monitoring & Observability
Document Type: Infrastructure Health & Alerting SOP
Date: 21 September 2026
```

---

## 1. Saluran Log Aplikasi & Rotasi Berkas

Sistem menggunakan saluran log terotasi harian (`LOG_CHANNEL=daily`) pada `storage/logs/laravel-YYYY-MM-DD.log`.

### Perintah Pemantauan Realtime

```bash
# Pantau log Laravel saat ini
tail -f storage/logs/laravel-$(date +%Y-%m-%d).log

# Pantau error log web server Nginx
sudo tail -f /var/log/nginx/error.log

# Pantau log service PHP-FPM
sudo tail -f /var/log/php8.4-fpm.log
```

---

## 2. Pengecekan Kesehatan Komponen Sistem (Health Checks)

| Komponen | Metode / Perintah Pengecekan | Nilai Normal / Indikator Sehat | Tindakan Jika Abnormal |
| :--- | :--- | :--- | :--- |
| **Koneksi Database** | `php artisan db:monitor` | Response `OK`, koneksi $< 100\text{ ms}$ | Periksa service MariaDB / port `3306` |
| **Endpoint Spasial** | `curl -I https://.../gis/data` | HTTP 200 OK, JSON valid | Periksa `GisController.php` & log error |
| **Antrean Queue Jobs**| `php artisan queue:monitor default` | 0 failed jobs, latency $< 5\text{s}$ | Jalankan `php artisan queue:retry all` |
| **Kapasitas Disk Storage** | `df -h /var/www` | Penggunaan $< 80\%$ | Bersihkan log lama & rotasi backup |
| **Penggunaan RAM / CPU** | `htop` atau `top` | CPU Load $< 70\%$, RAM $< 85\%$ | Tingkatkan alokasi worker PHP-FPM |

---

## 3. Log Error Filtering & Audit

Lakukan inspeksi berkala terhadap log berlevel `ERROR` dan `CRITICAL`:

```bash
grep -E "ERROR|CRITICAL|EMERGENCY|Exception" storage/logs/laravel-*.log
```
