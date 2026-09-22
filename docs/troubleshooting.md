# Troubleshooting Guide
**Sistem Informasi & Statistik Perikanan Tangkap Provinsi Aceh**

```text
System: Sistem Informasi Perikanan Tangkap Aceh
Version: 1.0.0
Documentation Version: 1.0.0
Date: 21 September 2026
Status: Final Approved (F9 Audit Compliant)
```

---

## 1. Panduan Pemecahan Masalah Cepat

Dokumen ini merangkum gejala kendala umum yang mungkin dihadapi administrator atau pengguna sistem beserta akar penyebab dan solusi penanganan yang aman.

---

## 2. Masalah Lingkungan & Server

### A. Aplikasi Gagal Dimulai / HTTP 500 Internal Server Error
* **Gejala:** Browser menampilkan pesan *"500 Server Error"* atau halaman putih.
* **Kemungkinan Penyebab:**
  1. Berkas `.env` belum dibuat atau kunci enkripsi `APP_KEY` kosong.
  2. Direktori `storage` atau `bootstrap/cache` tidak memiliki izin tulis (*permission denied*).
* **Solusi Aman:**
  ```bash
  php artisan key:generate
  sudo chown -R www-data:www-data storage bootstrap/cache
  sudo chmod -R 775 storage bootstrap/cache
  php artisan cache:clear
  ```

### B. Database Connection Error (`SQLSTATE[HY000] [2002]`)
* **Gejala:** Pesan kesalahan `Connection refused` atau `Access denied for user`.
* **Kemungkinan Penyebab:**
  1. Service database MySQL/MariaDB belum berjalan.
  2. Nilai `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, atau `DB_PASSWORD` di `.env` tidak sesuai.
* **Solusi Aman:**
  1. Pastikan service database aktif: `sudo systemctl status mariadb` atau `net start MySQL`.
  2. Verifikasi port MySQL (umumnya `3306` atau `3307`).
  3. Uji koneksi via terminal: `php artisan db:monitor`.

### C. Vite / Manifest Build Error (`Unable to locate file in Vite manifest`)
* **Gejala:** Tampilan CSS berantakan atau muncul exception `ViteException`.
* **Kemungkinan Penyebab:** Aset frontend belum dikompilasi atau file manifest Vite hilang.
* **Solusi Aman:**
  ```bash
  npm run build
  ```

---

## 3. Masalah GIS & Peta Interaktif

### A. Peta GIS Tidak Muncul / Kanvas Kosong (Blank Gray Box)
* **Gejala:** Wadah peta hanya menampilkan kotak abu-abu tanpa tile basemap.
* **Kemungkinan Penyebab:**
  1. Terhalang pembatasan Content Security Policy (CSP) atau jaringan internet lokal memblokir akses ke tile CartoDB/OSM.
  2. Panggilan API `GET /gis/data` gagal atau timeout.
* **Solusi Aman:**
  1. Buka *Developer Tools* browser (F12) $\rightarrow$ tab *Console* dan *Network*.
  2. Pastikan request ke `http://127.0.0.1:8000/gis/data` menghasilkan respon HTTP 200 JSON.
  3. Pastikan koneksi internet aktif untuk mengunduh tile basemap.

### B. Titik Effort / Pangkalan Kapal Tertentu Tidak Tampil di Peta
* **Gejala:** Jumlah data di database tercatat ada, namun marker tidak muncul di peta.
* **Kemungkinan Penyebab:**
  * Record tersebut memiliki koordinat `NULL` atau `0,0`. Sesuai aturan keselamatan data perikanan, sistem sengaja tidak merender koordinat palsu.
* **Solusi:**
  * Ini adalah perilaku normal sistem (*intended behavior*). Untuk memunculkan titik, masukkan koordinat GPS yang valid melalui menu Edit data terkait.

---

## 4. Masalah Statistik & Pelaporan

### A. Halaman `/statistik` Menampilkan Dropdown Kosong atau Galat Koleksi
* **Gejala:** Dropdown tahun tidak dapat dipilih atau muncul teks `Illuminate\Support\Collection`.
* **Kemungkinan Penyebab:** Variabel koleksi tahun di controller belum diekstraksi ke array integer terurut.
* **Solusi:**
  * Pastikan versi kode telah menggunakan rilis F9 (`LandingPageController.php` dan `statistik.blade.php`).
  * Jalankan: `php artisan view:clear`.

### B. Ekspor Excel Gagal Diunduh
* **Gejala:** Klik tombol "Ekspor Excel" memunculkan error atau file unduhan 0 bytes.
* **Kemungkinan Penyebab:** Ekstensi PHP `zip` atau `gd` belum terpasang pada server PHP.
* **Solusi Aman:**
  * Install ekstensi PHP yang diperlukan: `sudo apt install php8.4-zip php8.4-gd`.
  * Restart service PHP: `sudo systemctl restart php8.4-fpm`.
