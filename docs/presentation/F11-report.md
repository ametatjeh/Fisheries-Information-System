# F11 PRESENTATION DOCUMENTATION REPORT

**Sistem Informasi & Statistik Perikanan Tangkap Provinsi Aceh**  
**Proyek:** `C:\XPROJECT\sistem-perikanan`  
**Framework & Stack:** Laravel 13.x | PHP 8.4.x | MariaDB/MySQL | MapLibre GL JS 4.7.1 | Vite + TailwindCSS  
**Tanggal:** 21 September 2026  
**Status Tahap:** **READY FOR F12 — DEPLOYMENT PREPARATION**

---

## 1. Presentation Objective

Tahap **F11 — PRESENTATION DOCUMENTATION & DEMONSTRATION** bertujuan menerjemahkan dokumentasi teknis dan arsitektural hasil F10 menjadi materi presentasi, naskah tutur (*presentation script*), panduan demonstrasi langsung (*live demo script*), narasi sistem, diagram visual, dan lembar tanya-jawab (*FAQ*) yang mudah dipahami oleh jajaran pengambil keputusan, petugas lapangan, maupun pengembang teknis.

---

## 2. Target Audience

Materi presentasi diadaptasi secara fleksibel untuk 3 segmen pemangku kepentingan:
1. **Pimpinan & Pengambil Kebijakan (Kepala DKP / Bappeda):** Fokus pada nilai strategis data hulu-hilir, indikator statistik CPUE, analitika perairan WPP, dan laporan manajerial pendukung keputusan (*decision support*).
2. **Petugas Lapangan & Enumerator (Syahbandar / Enumerator TPI):** Fokus pada alur pencatatan transaksi (trip, effort, tangkapan, pendaratan), pelacakan riwayat data, dan pembacaan peta pelabuhan.
3. **Tim Teknis IT & Pengembang Perangkat Lunak:** Fokus pada arsitektur Clean MVC Laravel 13, integrasi MapLibre GL JS via REST API, keamanan peran RBAC, dan kesiapan infrastruktur deployment.

---

## 3. Slide Count

* **Total Slide:** **20 Slide**.
* Disusun padat, tidak berlebihan teks (*text-heavy*), dilengkapi diagram kotak-panah, formula terstandar, dan visualisasi layer peta aktual.

---

## 4. Presentation Structure

| No | Judul Slide | Objektif Utama |
| :-: | :--- | :--- |
| **1** | Title Slide | Pembukaan resmi & identitas sistem perikanan tangkap Aceh. |
| **2** | Latar Belakang & Tantangan Data | Menguraikan masalah fragmentasi data di 23 Kabupaten/Kota Aceh. |
| **3** | Tujuan & Fungsi Utama Sistem | Memaparkan 5 pilar tujuan strategis digitalisasi data perikanan. |
| **4** | Ruang Lingkup (Scope) Sistem | Menjelaskan cakupan Aceh, WPPNRI 571, dan WPPNRI 572. |
| **5** | Modul Utama Aplikasi | Inventaris 5 pilar modul: Master Data, Data Collection, Statistics, Reporting, GIS. |
| **6** | Workflow & Data Model Utama | Rantai data: Nelayan $\rightarrow$ Kapal $\rightarrow$ Trip $\rightarrow$ Effort $\rightarrow$ Catch $\rightarrow$ Landing. |
| **7** | Keterlacakan Data (Data Traceability) | Menunjukkan auditabilitas penelusuran data dari hilir ke hulu. |
| **8** | Mesin Statistik Perikanan | Alur validasi data operasional menuju perhitungan statistik resmi. |
| **9** | Perhitungan CPUE | Formula baku CPUE per jam ($\text{kg}/\text{jam}$) dan per trip ($\text{kg}/\text{trip}$). |
| **10**| Estimasi Tangkapan & Raising Factor | Proyeksi produksi pelabuhan berbasis sampling ilmiah. |
| **11**| WebGIS: 7 Layer MapLibre GL JS | Visualisasi 7 layer spasial interaktif berbasis WebGL modern. |
| **12**| Arsitektur Integrasi Data GIS | Aliran data dinamis: MySQL $\rightarrow$ `GisController` $\rightarrow$ GeoJSON $\rightarrow$ MapLibre. |
| **13**| Integritas Kualitas Data GIS | Transparansi 10 data effort GPS vs 52 non-GPS (tanpa koordinat palsu). |
| **14**| Penegasan Spasial: Homeport & Logbook| Klarifikasi bahwa Homeport $\neq$ Live Tracking dan Logbook $\neq$ Live AIS. |
| **15**| Modul Pelaporan & Ekspor Data | Diseminasi laporan manajerial dan ekspor spreadsheet Excel/PDF aman. |
| **16**| Keamanan Sistem & Tata Kelola Data | Proteksi Spatie RBAC, CSRF, sanitasi Blade XSS, dan isolasi kredensial `.env`. |
| **17**| Arsitektur Teknologi (Tech Stack) | Ketahanan stack Laravel 13, PHP 8.4, MariaDB, TailwindCSS, dan Vite. |
| **18**| Dari Data Menuju Informasi Keputusan | Transformasi data operasional menjadi kebijakan berbasis bukti (*evidence-based*). |
| **19**| Batasan Sistem (Known Limitations) | Pengakuan terbuka atas keterbatasan GPS dan geometri definitif. |
| **20**| Rencana Pengembangan (Roadmap) | Peta jalan pengembangan masa depan (Existing vs Future Roadmap). |

---

## 5. Demo Flow (10 Menit)

Alur demonstrasi langsung teruji dan aman:
1. `00:00–01:00`: Beranda Publik & Pengantar Antarmuka.
2. `01:00–02:30`: Master Data Nelayan KUSUKA & Armada Kapal.
3. `02:30–04:30`: Rantai Transaksi Pelayaran (Trip $\rightarrow$ Effort $\rightarrow$ Catch $\rightarrow$ Pendaratan TPI).
4. `04:30–06:30`: Interaktivitas Halaman Statistik Terbuka (`/statistik`), Filter Tahun, dan CPUE.
5. `06:30–08:30`: Peta Spasial WebGIS 7 Layer, Interaksi Popup, dan Batas Poligon WPP 571/572.
6. `08:30–09:30`: Pembuatan Laporan Rekapitulasi & Ekspor File Excel (`.xlsx`) Aman.
7. `09:30–10:00`: Penutup & Pembukaan Sesi Diskusi.

---

## 6. System Story

Terdokumentasi lengkap pada [`docs/presentation/system-story.md`](file:///c:/XPROJECT/sistem-perikanan/docs/presentation/system-story.md), menyajikan alur narasi terpadu:
* *Masalah:* Fragmentasi data pesisir Aceh.
* *Solusi:* Ekosistem rantai data terpadu hulu-hilir.
* *Mesin Statistik:* Kalkulasi CPUE dan validasi mutu.
* *WebGIS:* Peta interaktif MapLibre GL JS berbasis fakta.
* *Kejujuran Ilmiah:* Nol koordinat fiktif.
* *Nilai Akhir:* Pendukung keputusan (*decision support*) bagi DKP Aceh.

---

## 7. Architecture Diagram

Terdokumentasi pada [`docs/presentation/architecture-diagram.md`](file:///c:/XPROJECT/sistem-perikanan/docs/presentation/architecture-diagram.md), memetakan:
* Arsitektur Clean MVC Laravel 13 + Service Layer.
* Pipeline integrasi database MySQL ke MapLibre GL JS via GeoJSON `[lng, lat]`.
* Batasan keamanan sesi, otorisasi peran, dan enkripsi server.

---

## 8. Data Flow

Terdokumentasi pada [`docs/presentation/data-flow.md`](file:///c:/XPROJECT/sistem-perikanan/docs/presentation/data-flow.md), menegaskan rantai logis transaksi dan matriks keterlacakan data (*backward traceability*).

---

## 9. Statistics Flow

Terdokumentasi pada [`docs/presentation/statistics-flow.md`](file:///c:/XPROJECT/sistem-perikanan/docs/presentation/statistics-flow.md), merinci:
* Formula CPUE Waktu: $\frac{\sum \text{Catch (kg)}}{\sum \text{Duration (jam)}}$.
* Formula CPUE Trip: $\frac{\sum \text{Catch (kg)}}{\text{Total Trips}}$.
* Formula Raising Factor: $R = \frac{N_{\text{total}}}{n_{\text{sampled}}}$.

---

## 10. GIS Flow

Terdokumentasi pada [`docs/presentation/gis-flow.md`](file:///c:/XPROJECT/sistem-perikanan/docs/presentation/gis-flow.md), memetakan alur rendering WebGL, pemetaan GeoJSON standar RFC 7946, dan penandaan warna 7 layer spasial.

---

## 11. FAQ (Tanya Jawab Terantisipasi)

Terdokumentasi pada [`docs/presentation/faq.md`](file:///c:/XPROJECT/sistem-perikanan/docs/presentation/faq.md), menyiapkan 11 jawaban faktual bagi pimpinan, petugas lapangan, dan tim IT.

---

## 12. Limitations (Batasan Sistem)

Terdokumentasi pada [`docs/presentation/limitations.md`](file:///c:/XPROJECT/sistem-perikanan/docs/presentation/limitations.md), menegaskan batasan empiris:
* 52 data effort dicatat tanpa GPS $\rightarrow$ tidak diplot fiktif.
* 8 master fishing ground berstatus *"Belum Tersedia Geometri Resmi"*.
* Pangkalan kapal $\neq$ live tracking; logbook historis $\neq$ live AIS.
* Statistik produksi $\neq$ full stock assessment.

---

## 13. Future Development

Terdokumentasi pada [`docs/presentation/roadmap.md`](file:///c:/XPROJECT/sistem-perikanan/docs/presentation/roadmap.md), memisahkan fitur existing dengan rencana masa depan (integrasi poligon definitif, mobile app GPS enumerator, analitika biologi lanjut).

---

## 14. Visual Assets Required

Daftar tangkapan layar (*screenshots*) yang disiapkan untuk melengkapi berkas presentasi:
1. `beranda_publik.png` — Tangkapan layar antarmuka halaman beranda publik.
2. `master_data_kapal.png` — Tangkapan layar tabel registrasi armada kapal dan tanda selar.
3. `transaksi_trip_effort.png` — Tangkapan layar formulir pencatatan trip dan operasi effort penangkapan.
4. `statistik_kpi_charts.png` — Tangkapan layar grafik statistik produksi dan kartu CPUE di `/statistik`.
5. `webgis_7_layers.png` — Tangkapan layar kanvas WebGIS MapLibre dengan popup aktif titik effort di perairan Aceh.
6. `laporan_ekspor_excel.png` — Tangkapan layar pratinjau lembar kerja rekapitulasi laporan.

---

## 15. Documentation QA & Cross-Checking

* **Audit Verifikasi Fakta:** Seluruh metrik angka, nama tabel, rute, formula kalkulasi, layer GIS, dan batasan empiris telah diverifikasi 100% cocok dengan kode sumber aplikasi aktual.
* **Zero Speculative Claims:** Tidak ada klaim berlebihan atau sertifikasi fiktif yang dicantumkan dalam materi presentasi.
* **Automated Feature Tests:** `php artisan test` $\rightarrow$ **10/10 LandingPageTest passed (97 assertions)**.
* **PHP Code Style:** `vendor/bin/pint --dirty --format agent` $\rightarrow$ **Passed**.

---

## 16. Code Changes

```text
NO APPLICATION CODE CHANGES
```
*(Seluruh pekerjaan F11 murni berupa pembuatan materi presentasi pada direktori `docs/presentation/`)*.

---

## 17. Database Changes

```text
NO DATABASE / SCHEMA CHANGES
```
*(Tidak ada migrasi, tidak ada modifikasi skema, dan seluruh basis data transaksi tetap utuh)*.

---

## 18. Final Status

$$\mathbf{READY\ FOR\ F12\ —\ DEPLOYMENT\ PREPARATION}$$

Seluruh materi presentasi, naskah tutur presenter, skenario demonstrasi langsung, narasi arsitektur, dan lembar tanya-jawab telah lengkap, terstruktur rapi, dan siap digunakan untuk presentasi resmi di hadapan pimpinan dan pemangku kepentingan perikanan Provinsi Aceh.
