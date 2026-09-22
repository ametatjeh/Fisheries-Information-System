# Panduan Pengguna (User Manual)
**Sistem Informasi & Statistik Perikanan Tangkap Provinsi Aceh**

```text
System: Sistem Informasi Perikanan Tangkap Aceh
Version: 1.0.0
Documentation Version: 1.0.0
Date: 21 September 2026
Status: Final Approved (F9 Audit Compliant)
```

---

## 1. Pendahuluan

Buku panduan ini ditujukan bagi seluruh pengguna aplikasi (Petugas Pelabuhan/TPI, Verifikator, Petugas Dinas Kelautan & Perikanan, serta Pimpinan) untuk memandu operasional harian pengelolaan data perikanan tangkap.

---

## 2. Mengakses Sistem & Login

1. Buka peramban (*web browser*) seperti Google Chrome atau Mozilla Firefox.
2. Masukkan alamat website: `https://perikanan.acehprov.go.id` (atau `http://127.0.0.1:8000` pada server lokal).
3. Klik tombol **"Masuk Sistem"** di pojok kanan atas.
4. Masukkan **Email** dan **Password** yang telah didaftarkan oleh Administrator. Anda juga dapat menggunakan tombol **"Login dengan Akun Google"** jika email Anda telah ditautkan.
5. Klik tombol **Login**.

---

## 3. Modul Master Data

Modul Master Data digunakan untuk mendaftarkan dan memperbarui data pokok perikanan.

### A. Pendaftaran Nelayan
* **Menu:** Master Data $\rightarrow$ Data Nelayan
* **Langkah Input:**
  1. Klik tombol **"+ Tambah Nelayan"**.
  2. Masukkan NIK (16 digit), Nama Lengkap, Nomor KUSUKA, Nomor Telepon, Alamat, dan Desa/Gampong domisili.
  3. Pilih Peran Nelayan (Pemilik / Nahkoda / ABK).
  4. Klik **Simpan**.

### B. Pendaftaran Armada Kapal
* **Menu:** Master Data $\rightarrow$ Data Kapal
* **Langkah Input:**
  1. Klik tombol **"+ Tambah Kapal"**.
  2. Isi Nama Kapal, Nomor Tanda Selar/Registrasi, Ukuran GT (Gross Tonnage), Panjang (LOA), Tenaga Mesin (HP).
  3. Pilih Pemilik Kapal, Jenis Alat Tangkap Utama, dan Pelabuhan Pangkalan (*Homeport*).
  4. Klik **Simpan**.

---

## 4. Modul Pengumpulan Data Lapangan (Data Collection)

### A. Pencatatan Trip Penangkapan (Fishing Trip)
* **Menu:** Pengumpulan Data $\rightarrow$ Trip Penangkapan
* **Langkah Input:**
  1. Klik tombol **"+ Catat Trip Baru"**.
  2. Pilih Kapal yang berlayar dan Nahkoda yang bertugas.
  3. Pilih Pelabuhan Keberangkatan, Wilayah WPP (571 / 572), dan Alat Tangkap yang dibawa.
  4. Isi Tanggal Keberangkatan, Estimasi Tanggal Pulang, Jumlah ABK, dan Bahan Bakar.
  5. Klik **Simpan Trip**.

### B. Pencatatan Upaya Tangkap (Fishing Effort)
* **Menu:** Pengumpulan Data $\rightarrow$ Fishing Effort
* **Langkah Input:**
  1. Pilih nomor Trip pelayaran terkait.
  2. Masukkan Waktu Penurunan Jaring (*Setting Date/Time*) dan Waktu Penarikan (*Hauling Date/Time*).
  3. *(Jika tersedia GPS)* Masukkan titik Lintang (*Latitude*) dan Bujur (*Longitude*) setting alat tangkap. Jika tidak ada GPS, biarkan kolom koordinat kosong.
  4. Klik **Simpan Effort**.

### C. Pencatatan Hasil Tangkapan (Fish Catch)
* **Menu:** Pengumpulan Data $\rightarrow$ Hasil Tangkapan
* **Langkah Input:**
  1. Pilih Trip dan nomor Effort terkait.
  2. Pilih Jenis Ikan (e.g., Cakalang, Madidihang, Tongkol).
  3. Masukkan Berat Total dalam satuan Kilogram ($\text{kg}$).
  4. Klik **Tambah Tangkapan**.

### D. Pencatatan Pendaratan di TPI (Fish Landing)
* **Menu:** Pengumpulan Data $\rightarrow$ Pendaratan Ikan
* **Langkah Input:**
  1. Klik **"+ Rekam Pendaratan"**.
  2. Pilih Pelabuhan/TPI dan Tanggal Pendaratan.
  3. Masukkan rincian jenis ikan yang ditimbang di dermaga beserta harga lelang per kg.
  4. Klik **Simpan Pendaratan**.

---

## 5. Modul Peta Interaktif (WebGIS)

* **Menu:** GIS / Peta Perikanan (atau menu **Peta** pada beranda publik)
* **Panduan Penggunaan:**
  * **Navigasi:** Gunakan klik-dan-geser *mouse* untuk menggeser peta (*pan*), serta roda *scroll* atau tombol `+` / `-` untuk memperbesar/memperkecil (*zoom*).
  * **Mengaktifkan / Mematikan Layer:** Pada panel pojok kanan atas, centang atau hilangkan centang pada 7 layer:
    1. *Titik Setting Effort (Oranye)*
    2. *Pelabuhan / TPI (Biru)*
    3. *Homeport Kapal (Ungu)*
    4. *Logbook Historis (Kuning)*
    5. *Master Fishing Ground*
    6. *WPP 571 (Selat Malaka)*
    7. *WPP 572 (Samudera Hindia)*
  * **Melihat Detail (Popup):** Klik salah satu titik marker pada peta untuk memunculkan jendela info detail (nama kapal, tangkapan, pelabuhan, dll.).

> [!NOTE]
> **Catatan Pemahaman Peta:**
> * **Layer Homeport Kapal:** Menunjukkan pelabuhan pangkalan tempat kapal terdaftar, **bukan** posisi satelit langsung kapal di laut.
> * **Layer Logbook Historis:** Menunjukkan catatan rekam jejak operasional masa lalu, **bukan** pelacakan waktu-nyata (*real-time*).

---

## 6. Modul Statistik & Pelaporan

### A. Membaca Statistik Publik (`/statistik`)
* Buka menu **Statistik** di navigasi utama.
* Gunakan dropdown **Pilih Tahun** untuk melihat perbandingan angka tahunan (2024, 2025, 2026).
* Gunakan filter **Komoditas Ikan** atau **Alat Tangkap** untuk mempersempit grafik tren.
* Grafik akan secara otomatis menampilkan Volume Produksi, Total Pelayaran (*Trips*), dan Laju Tangkap per Jam (*CPUE*).

### B. Membuat Laporan & Ekspor Data
* **Menu:** Laporan $\rightarrow$ Rekapitulasi Produksi
* Pilih Rentang Tanggal atau Tahun/Bulan yang diinginkan.
* Pilih Pelabuhan atau Wilayah Kabupaten.
* Klik tombol **"Tampilkan Laporan"** untuk melihat tabel di layar.
* Klik tombol **"Ekspor Excel (.xlsx)"** untuk mengunduh spreadsheet atau **"Cetak PDF"** untuk mencetak dokumen fisik.
