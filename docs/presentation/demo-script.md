# Panduan Demonstrasi Langsung (Live Demo Script)
**Sistem Informasi & Statistik Perikanan Tangkap Provinsi Aceh**

```text
System: Sistem Informasi Perikanan Tangkap Aceh
Stage: F11 — Live Demonstration Script
Target Duration: 10 Menit
Environment: Local / Staging Server (http://127.0.0.1:8000)
```

---

## 1. Live Demo Safety & Pre-Flight Checklist

Lakukan verifikasi berikut **15 menit sebelum sesi presentasi/demo dimulai**:

```text
[ ] Service database MySQL/MariaDB aktif dan stabil.
[ ] Web server Laravel aktif (php artisan serve pada port 8000).
[ ] Aset frontend Vite terkompilasi (npm run build telah dijalankan).
[ ] Endpoint GET /gis/data mengembalikan HTTP 200 OK dengan payload JSON valid.
[ ] Halaman publik /statistik memuat grafik dan dropdown tahun tanpa error.
[ ] Web browser (Google Chrome / Edge) dibuka pada mode normal tanpa ekstensi pemblokir skrip.
[ ] Browser Developer Console (F12) bersih dari uncaught JavaScript exceptions.
[ ] Kredensial akun demo Admin DKP telah disiapkan di formulir login.
[ ] JANGAN menjalankan artisan migrate / migrate:fresh saat sesi demonstrasi berlangsung.
```

---

## 2. Alokasi Waktu Demonstrasi (10 Menit)

| Waktu | Modul / Layar | Fokus Penjelasan |
| :---: | :--- | :--- |
| **00:00 – 01:00** | **Beranda & Landing Page** | Pengantar antarmuka publik, ringkasan capaian perikanan, dan navigasi. |
| **01:00 – 02:30** | **Master Data (Nelayan & Kapal)** | Entitas dasar nelayan KUSUKA, armada kapal, dan pelabuhan pangkalan. |
| **02:30 – 04:30** | **Data Collection (Hulu ke Hilir)** | Alur Fishing Trip $\rightarrow$ Fishing Effort $\rightarrow$ Catch $\rightarrow$ Pendaratan TPI. |
| **04:30 – 06:30** | **Halaman Statistik (`/statistik`)** | Interaksi filter tahun, grafik tren spesies, dan metrik CPUE. |
| **06:30 – 08:30** | **WebGIS Interaktif MapLibre** | 7 Layer spasial, layer toggles, popup info pelabuhan, titik effort, dan WPP. |
| **08:30 – 09:30** | **Laporan & Ekspor Data** | Rekapitulasi tabel produksi dan ekspor dokumen Excel/PDF. |
| **09:30 – 10:00** | **Kesimpulan & Tanya Jawab** | Penutup demo dan transisi ke sesi diskusi. |

---

## 3. Skenario Demonstrasi Langkah-demi-Langkah

---

### Langkah 1: Beranda Publik (Menit 00:00 – 01:00)
* **Aksi:** Buka URL `http://127.0.0.1:8000/` pada browser.
* **Yang Ditampilkan:** Beranda publik modern Sistem Informasi Perikanan Tangkap Aceh.
* **Narasi Demonstrator:**
  > "Bapak dan Ibu sekalian, ini adalah tampilan beranda publik sistem perikanan Aceh. Halaman ini menyajikan ringkasan informasi umum, alur kerja tata kelola data perikanan, serta akses langsung bagi publik dan pelaku usaha untuk melihat informasi statistik resmi."
* **Aksi Lanjutan:** Klik tombol **"Masuk Sistem"** di pojok kanan atas, lalu login menggunakan akun Admin/Enumerator.

---

### Langkah 2: Master Data Nelayan & Kapal (Menit 01:00 – 02:30)
* **Aksi:**
  1. Klik menu **Master Data** $\rightarrow$ **Data Nelayan**.
  2. Buka salah satu data nelayan (e.g., Nelayan KUSUKA terdaftar).
  3. Klik menu **Master Data** $\rightarrow$ **Data Kapal**.
* **Yang Ditampilkan:** Tabel kapal lengkap dengan tanda selar, ukuran GT, jenis alat tangkap, pemilik, dan pangkalan *homeport*.
* **Narasi Demonstrator:**
  > "Fondasi sistem dimulai dari Master Data. Di sini kita melihat 23 nelayan terdaftar lengkap dengan nomor KUSUKA dan perannya. Pada menu Data Kapal, kita melihat 19 armada kapal penangkap ikan dengan rincian GT, alat tangkap utama, dan pelabuhan pangkalan asalnya seperti PPS Lampulo atau PPN Idi. Hubungan ini memastikan setiap kapal terikat pada pemilik dan pelabuhan yang sah."

---

### Langkah 3: Rantai Pencatatan Transaksi Lapangan (Menit 02:30 – 04:30)
* **Aksi:**
  1. Buka menu **Pengumpulan Data** $\rightarrow$ **Trip Penangkapan**.
  2. Tunjukkan rincian satu pelayaran (e.g., `TRIP-2026-0012` KM Bahari Jaya).
  3. Buka menu **Fishing Effort** dan **Hasil Tangkapan**.
  4. Buka menu **Pendaratan Ikan**.
* **Yang Ditampilkan:** Rantai pelayaran terstruktur: Kapal $\rightarrow$ Trip $\rightarrow$ 1x Setting Effort (durasi jam) $\rightarrow$ Tangkapan Cakalang/Tongkol (kg) $\rightarrow$ Pendaratan TPI.
* **Narasi Demonstrator:**
  > "Inilah rantai pencatatan hulu ke hilir. Kapal KM Bahari Jaya melakukan pelayaran penangkapan di WPP 571. Di laut, dicatat waktu penurunan jaring dengan durasi operasi 3,5 jam dan koordinat GPS setting. Tangkapan yang diperoleh adalah 450 kg (300 kg Cakalang dan 150 kg Tongkol). Ketika kapal kembali ke PPS Lampulo, hasil tangkapan tersebut ditimbang pada modul Pendaratan. Seluruh rantai ini saling terhubung secara utuh."

---

### Langkah 4: Halaman Statistik Publik (`/statistik`) (Menit 04:30 – 06:30)
* **Aksi:**
  1. Buka menu navigasi **Statistik** (`http://127.0.0.1:8000/statistik`).
  2. Tunjukkan kartu ringkasan KPI (Total Produksi, Total Pelayaran, dan CPUE).
  3. Pilih dropdown **Tahun** $\rightarrow$ pilih tahun `2026` atau `Semua Tahun`.
  4. Pilih filter **Komoditas Ikan** (e.g., Cakalang).
* **Yang Ditampilkan:** Grafik produksi dinamis, perbandingan bulanan, dan kartu CPUE per jam operasi.
* **Narasi Demonstrator:**
  > "Kini kita berada pada Halaman Statistik Terpadu. Seluruh data transaksi diolah secara instan menjadi grafik tren dan indikator CPUE. Saat kita mengubah filter tahun atau memilih komoditas tertentu seperti Ikan Cakalang, grafik dan tabel ringkasan langsung menyesuaikan secara dinamis. Di sini pimpinan dapat langsung melihat performa penangkapan per komoditas secara objektif."

---

### Langkah 5: Peta Spasial WebGIS Interaktif (Menit 06:30 – 08:30)
* **Aksi:**
  1. Buka menu **Peta GIS** (`/gis` atau bagian peta pada halaman statistik).
  2. Lakukan *Zoom In* dan *Pan* di perairan pesisir Aceh.
  3. Klik salah satu titik **Pelabuhan** (ikon biru PPS Lampulo) $\rightarrow$ muncul popup rincian pendaratan.
  4. Klik salah satu titik **Fishing Effort** (titik oranye di laut) $\rightarrow$ muncul popup nama kapal, alat tangkap, durasi jam, dan berat tangkapan.
  5. Buka panel layer toggle di pojok kanan atas $\rightarrow$ matikan/hidupkan layer **Homeport Kapal** dan **WPP 571/572**.
* **Yang Ditampilkan:** Kanvas WebGIS MapLibre GL JS responsif dengan 7 layer aktif dan batas poligon WPP.
* **Narasi Demonstrator:**
  > "Ini adalah visualisasi geospasial WebGIS berbasis engine modern MapLibre GL JS 4.7.1. Kita dapat melihat 7 layer spasial sekaligus. Titik oranye ini adalah lokasi setting alat tangkap aktual di perairan Aceh dengan rincian tangkapannya. Titik biru adalah pelabuhan pendaratan, titik ungu adalah pangkalan kapal terdaftar, dan poligon transparan ini membatasi WPP 571 Selat Malaka serta WPP 572 Samudera Hindia. Peta ini terhubung langsung ke API database tanpa manipulasi koordinat fiktif."

---

### Langkah 6: Modul Pelaporan & Ekspor Data (Menit 08:30 – 09:30)
* **Aksi:**
  1. Buka menu **Laporan** $\rightarrow$ **Rekapitulasi Produksi**.
  2. Pilih periode tahun `2026` dan klik **"Tampilkan Laporan"**.
  3. Klik tombol **"Ekspor Excel (.xlsx)"** $\rightarrow$ tunjukkan proses unduhan file spreadsheet aman.
* **Yang Ditampilkan:** Tabel rekapitulasi data siap audit dan file spreadsheet hasil ekspor.
* **Narasi Demonstrator:**
  > "Terakhir, pada Modul Pelaporan, seluruh agregasi data disajikan dalam format tabel formal yang siap diekspor ke format Excel atau dicetak menjadi dokumen resmi bertanda tangan. Sistem secara otomatis menyaring formula berbahaya saat ekspor untuk menjamin keamanan dokumen dinas."

---

### Langkah 7: Penutup & Transisi Sesi Diskusi (Menit 09:30 – 10:00)
* **Aksi:** Kembali ke halaman Beranda / Dashboard utama.
* **Narasi Demonstrator:**
  > "Demikian demonstrasi langsung alur kerja Sistem Informasi & Statistik Perikanan Tangkap Provinsi Aceh—dari pencatatan master data, pelayaran di laut, penimbangan di pelabuhan, pengolahan statistik CPUE, pemetaan WebGIS, hingga pelaporan manajerial. Kami mengundang Bapak dan Ibu sekalian untuk memasuki sesi tanya jawab."
