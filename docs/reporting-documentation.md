# Reporting & Export Documentation
**Sistem Informasi & Statistik Perikanan Tangkap Provinsi Aceh**

```text
System: Sistem Informasi Perikanan Tangkap Aceh
Version: 1.0.0
Documentation Version: 1.0.0
Date: 21 September 2026
Status: Final Approved (F9 Audit Compliant)
```

---

## 1. Overview Modul Pelaporan

Modul Pelaporan ([`app/Http/Controllers/Output/ReportController.php`](file:///c:/XPROJECT/sistem-perikanan/app/Http/Controllers/Output/ReportController.php)) menyediakan fasilitas rekapitulasi data manajerial perikanan tangkap untuk keperluan pengambil kebijakan di Dinas Kelautan dan Perikanan (DKP) Provinsi Aceh, instansi kementerian, serta peneliti.

---

## 2. Kategori Laporan Tersedia

### A. Laporan Rekapitulasi Produksi Perikanan
* **Tujuan:** Menyajikan total volume (kg / ton) dan nilai ekonomi pendaratan ikan berdasarkan pelabuhan, kabupaten, dan periode waktu.
* **Tabel Sumber:** `monthly_production_statistics`, `landings`, `landing_items`.
* **Metode Agregasi:** Pengelompokan bulanan dan tahunan dengan kalkulasi rata-rata harga per kilogram.

### B. Laporan Distribusi Komoditas & Spesies Unggulan
* **Tujuan:** Menampilkan peringkat spesies dengan volume tangkapan tertinggi (e.g., Cakalang, Tongkol, Madidihang/Yellowfin Tuna) beserta kontribusi persentasenya terhadap total produksi daerah.
* **Tabel Sumber:** `catches` $\bowtie$ `species`.

### C. Laporan Kinerja Alat Tangkap (Fishing Gear Performance)
* **Tujuan:** Mengevaluasi efektivitas alat tangkap dominan di Aceh (Purse Seine / Pukat Cincin, Gillnet / Jaring Insang, Longline / Rawai) melalui perbandingan volume tangkapan dan jam operasi (*effort hours*).
* **Tabel Sumber:** `fishing_efforts` $\bowtie$ `fishing_gears`.

### D. Laporan Pemanfaatan Wilayah Penangkapan (WPP 571 & 572)
* **Tujuan:** Memberikan rincian beban penangkapan antara perairan Selat Malaka (WPP 571) dan Samudera Hindia (WPP 572).
* **Tabel Sumber:** `fishing_trips` $\bowtie$ `wppnri`.

---

## 3. Fitur Ekspor Data (Excel & Print)

Sistem menyediakan fungsi ekspor dokumen yang aman:

### 1. Ekspor Format Spreadsheet Excel (`.xlsx` / `.csv`)
* **Route:** `GET /reports/export`
* **Library:** `PhpOffice\PhpSpreadsheet` / Laravel Excel
* **Keamanan:** Dilengkapi sanitasi formula otomatis untuk mencegah kerentanan *CSV/Formula Injection* (menonaktifkan karakter pembuka `=`, `+`, `-`, `@` pada kolom teks).

### 2. Cetak Dokumen & Ekspor PDF
* **Route:** `GET /reports/print`
* **Format:** Layout ramah cetak (*print-friendly stylesheet*) dengan header resmi instansi pemerintah, tanggal pencetakan, dan tanda tangan verifikator.

---

## 4. Parameter Filter Laporan

Seluruh laporan mendukung parameter filter fleksibel:

| Parameter | Tipe Data | Deskripsi |
| :--- | :---: | :--- |
| `report_type` | String | Jenis laporan (`production`, `species`, `gear`, `wpp`, `landing_site`) |
| `year` | Integer | Tahun pelaporan (e.g., `2026`) |
| `month` | Integer | Bulan pelaporan ($1–12$) |
| `start_date` | Date (`Y-m-d`) | Tanggal awal periode |
| `end_date` | Date (`Y-m-d`) | Tanggal akhir periode |
| `regency_id` | Integer FK | Filter kabupaten/kota |
| `landing_site_id` | Integer FK | Filter pelabuhan pendaratan |
| `gear_id` | Integer FK | Filter jenis alat tangkap |
| `species_id` | Integer FK | Filter jenis komoditas ikan |
