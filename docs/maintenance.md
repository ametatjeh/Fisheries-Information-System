# System Maintenance & Operations Guide
**Sistem Informasi & Statistik Perikanan Tangkap Provinsi Aceh**

```text
System: Sistem Informasi Perikanan Tangkap Aceh
Version: 1.0.0
Documentation Version: 1.0.0
Date: 21 September 2026
Status: Final Approved (F9 Audit Compliant)
```

---

## 1. Routine Maintenance Schedule

Pemeliharaan sistem berkala wajib dilakukan oleh Administrator Sistem DKP Aceh untuk menjaga performa, stabilitas, dan keamanan data.

| Frekuensi | Tugas Pemeliharaan | Prosedur / Command |
| :--- | :--- | :--- |
| **Harian** | Backup Database Otomatis | Cron job mysqldump pukul 02:00 WIB |
| **Mingguan** | Rotasi & Pemeriksaan Log Error | Analisis `storage/logs/laravel-YYYY-MM-DD.log` |
| **Bulanan** | Pembersihan Cache & File Sementara | `php artisan cache:clear && php artisan view:clear` |
| **Kuartalan** | Audit Pembaruan Keamanan Dependensi | `composer audit` & `npm audit` |
| **Tahunan** | Sinkronisasi Katalog Taksonomi ASFIS | `php artisan asfis:import` |

---

## 2. Log Monitoring & Analysis

Sistem mencatat seluruh log aplikasi ke dalam direktori `storage/logs/` dengan saluran harian (*daily rotation*).

* **Melihat Log Terkini Secara Realtime:**
  ```bash
  tail -f storage/logs/laravel-$(date +%Y-%m-%d).log
  ```
* **Filter Log Tingkat Error & Critical:**
  ```bash
  grep -E "ERROR|CRITICAL|EMERGENCY" storage/logs/laravel-*.log
  ```

---

## 3. Cache Management & Optimization

Setiap kali melakukan deployment kode baru atau perubahan konfigurasi, jalankan perintah pembersihan dan penghangatan cache berikut:

```bash
# Bersihkan seluruh cache lama
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Bangun kembali cache produksi yang optimal
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

## 4. Background Job & Queue Management

Jika sistem menjalankan proses latar belakang (*background jobs* seperti impor data massal atau sinkronisasi API satelit):

* **Status Antrean Job:**
  ```bash
  php artisan queue:monitor default
  ```
* **Restart Worker Antrean:**
  ```bash
  php artisan queue:restart
  ```
* **Pemeriksaan Failed Jobs:**
  ```bash
  php artisan queue:failed
  ```

---

## 5. Master Data Lifecycle & Catalog Updates

1. **Pembaruan Data Taksonomi FAO ASFIS:**
   Katalog spesies dapat diperbarui tanpa mengganggu data transaksi tangkapan yang telah ada menggunakan perintah Artisan resmi:
   ```bash
   php artisan asfis:import
   ```
2. **Pembaruan Master Fishing Ground:**
   Jika instansi resmi (DKP Aceh / KKP) telah merilis koordinat batas poligon resmi untuk 8 Fishing Ground di Aceh, masukkan data koordinat melalui antarmuka Master Data tanpa mengubah skema tabel database.
