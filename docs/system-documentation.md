# System Technical Documentation
**Sistem Informasi & Statistik Perikanan Tangkap Provinsi Aceh**

```text
System: Sistem Informasi Perikanan Tangkap Aceh
Version: 1.0.0
Documentation Version: 1.0.0
Date: 21 September 2026
Status: Final Approved (F9 Audit Compliant)
```

---

## 1. System Overview

Sistem Informasi & Statistik Perikanan Tangkap Provinsi Aceh adalah platform terpadu berbasis web yang dirancang untuk mengelola seluruh rantai data perikanan tangkap di wilayah Provinsi Aceh. Sistem mencakup tata kelola data hulu (nelayan, kapal, perizinan, dan pelayaran penangkapan), data operasional penangkapan (*fishing effort*, *catch*, dan *landing*), hingga data hilir (analisis biologi, estimasi tangkapan, statistik bulanan/tahunan, pelaporan manajerial, dan visualisasi spasial GIS).

### Scope & Wilayah Cakupan
* **Cakupan Administratif:** 23 Kabupaten/Kota di Provinsi Aceh.
* **Cakupan Pengelolaan Perikanan:** Wilayah Pengelolaan Perikanan Negara Republik Indonesia (WPPNRI):
  * **WPPNRI 571:** Selat Malaka dan Laut Andaman (pesisir timur-utara Aceh).
  * **WPPNRI 572:** Samudera Hindia sebelah Barat Sumatera (pesisir barat-selatan Aceh).
* **Target Pengguna:** Dinas Kelautan dan Perikanan (DKP) Provinsi Aceh, Petugas Pencatat Pelabuhan (PPN/PPI/TPI), Pengawas Perikanan, Peneliti Sumberdaya Ikan, serta Publik/Nelayan.

---

## 2. Problem Statement

Pengelolaan perikanan tangkap di Provinsi Aceh sebelumnya menghadapi tantangan mendasar:
1. **Fragmentasi Data:** Catatan pelayaran nelayan (*logbook*), pendaratan ikan di TPI (*landing*), dan data pendaftaran kapal tercatat secara terpisah di masing-masing pelabuhan.
2. **Keterbatasan Spasial:** Kurangnya pemetaan terpadu mengenai lokasi *fishing effort* (setting alat tangkap), sebaran pelabuhan pangkalan (*homeport*), dan batas WPP.
3. **Standarisasi Spesies & Alat Tangkap:** Penamaan lokal ikan dan alat tangkap yang bervariasi antar-daerah memerlukan standardisasi acuan ilmiah (FAO ASFIS dan FAO ISSCFG).
4. **Validasi & Integritas Statistik:** Kebutuhan kalkulasi Catch Per Unit Effort (CPUE) dan faktor penimbang (*raising factor*) yang akurat tanpa duplikasi atau pembagian nol (*divide-by-zero*).

---

## 3. System Objectives

1. **Digitalisasi Hulu-ke-Hilir:** Mengintegrasikan pencatatan dari data nelayan, armada kapal, *fishing trip*, *fishing effort*, hasil tangkapan (*catch*), hingga pendaratan ikan (*landing*).
2. **Standardisasi Taksonomi & Perikanan:** Menyediakan basis data referensi spesies ikan (FAO ASFIS) dan klasifikasi alat tangkap (FAO ISSCFG).
3. **Analisis Statistik & CPUE:** Menyediakan agregasi produksi bulanan, laju tangkap per jam/trip (CPUE), dan estimasi produksi terstandar.
4. **Visualisasi Spasial WebGIS:** Menampilkan 7 layer spasial interaktif menggunakan MapLibre GL JS untuk memantau sebaran pendaratan, upaya penangkapan, pelabuhan, dan batas WPPNRI secara transparan.

---

## 4. Application Modules

Sistem Informasi Perikanan terbagi dalam 5 pilar modul fungsional:

### A. Master Data & Referensi
* **Tujuan:** Pengelolaan entitas induk acuan sistem.
* **Komponen:**
  * Wilayah Administratif (Provinsi, Kabupaten/Kota, Kecamatan, Gampong/Desa).
  * Nelayan & Kelompok Usaha Bersama (KUB/Fisher Groups).
  * Armada Kapal (*Vessels*) & Tipe Kapal (*Vessel Types*).
  * Jenis Ikan (*Species* referensi FAO ASFIS).
  * Alat Penangkapan Ikan (*Fishing Gears* referensi FAO ISSCFG).
  * Tempat Pendaratan Ikan / Pelabuhan Perikanan (*Landing Sites* / TPI).
  * Daerah Penangkapan Ikan (*Fishing Grounds*) & WPPNRI (571 & 572).

### B. Data Collection (Pengumpulan Data Lapangan)
* **Tujuan:** Pencatatan transaksi operasional harian di laut dan pelabuhan.
* **Komponen:**
  * **Fishing Trips:** Registrasi nomor trip, kapal, nahkoda, tanggal berangkat/pulang, dan pelabuhan pangkalan.
  * **Fishing Efforts:** Pencatatan waktu setting/hauling alat tangkap, durasi jam operasi, dan koordinat GPS setting jika tersedia.
  * **Catches:** Pencatatan berat (kg) per spesies ikan yang tertangkap pada setiap trip/effort.
  * **Landings & Landing Items:** Pencatatan volume ikan yang didaratkan di pelabuhan pendaratan.
  * **Historical Logbooks:** Pencatatan buku harian operasional kapal (posisi, cuaca, tinggi gelombang, aktivitas).

### C. Fisheries Analysis & Statistics
* **Tujuan:** Pengolahan data transaksi menjadi indikator saintifik dan statistik resmi.
* **Komponen:**
  * **Validasi Data:** Verifikasi integritas trip dan pendaratan sebelum masuk perhitungan statistik.
  * **Sampling Biologi:** Pencatatan panjang cagak (FL), panjang total (TL), berat individu, dan tingkat kematangan gonad (TKG).
  * **Estimasi Tangkapan:** Perhitungan estimasi produksi menggunakan *raising factor*.
  * **Statistik Produksi Bulanan:** Agregasi resmi volume dan nilai produksi per komoditas, alat tangkap, dan WPP.

### D. Reporting & Export
* **Tujuan:** Diseminasi data manajerial dan pelaporan statistik.
* **Komponen:**
  * Rekapitulasi Produksi Perikanan Tangkap.
  * Tren Produksi Spesies Unggulan.
  * Distribusi Alat Tangkap & Wilayah Pendaratan.
  * Ekspor Tabular format Excel (`.xlsx`) dan PDF.

### E. Geographic Information System (GIS)
* **Tujuan:** Analisis dan visualisasi geospasial sumberdaya perikanan.
* **Komponen:**
  * Peta WebGIS Interaktif MapLibre GL JS.
  * 7 Layer Spasial Terpadu (Effort, Landing Site, Homeport, Logbook, Fishing Ground, WPP 571, WPP 572).
  * Analisis Agregasi Spasial WPP (tangkapan, trip, durasi, CPUE per WPP).
  * API Gateway Internal Global Fishing Watch (GFW) untuk pemantauan satelit AIS.

---

## 5. Technology Stack

* **Backend Engine:** Laravel Framework 13.x
* **Language Runtime:** PHP 8.4.x
* **Database Management System:** MySQL / MariaDB
* **Frontend UI:** Laravel Blade Templates, Tailwind CSS
* **Map & Spatial Visualization:** MapLibre GL JS `v4.7.1` (OSM Raster Tiles / CartoDB)
* **Build Tool:** Vite 6.x & NPM
* **Package Manager:** Composer 2.x
* **Test Runner:** PHPUnit 11.x
* **Code Formatter:** Laravel Pint
