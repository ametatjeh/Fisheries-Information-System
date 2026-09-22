# Dokumentasi Resmi Sistem Informasi & Statistik Perikanan Tangkap Provinsi Aceh

```text
System: Sistem Informasi Perikanan Tangkap Aceh
Version: 1.0.0
Documentation Version: 1.0.0
Date: 21 September 2026
Status: Final Approved (F9 Audit Compliant)
```

Selamat datang di pusat dokumentasi teknis dan operasional **Sistem Informasi & Statistik Perikanan Tangkap Provinsi Aceh** (Laravel 13 + MySQL + Blade + MapLibre GL JS).

Dokumentasi ini disusun secara komprehensif berdasarkan **implementasi aktual** kode sumber, skema basis data, modul statistik, dan visualisasi spasial GIS perikanan.

---

## 📚 Indeks Dokumentasi Sistem

### 1. Arsitektur & Spesifikasi Sistem
* 📘 [**System Technical Documentation**](./system-documentation.md) — Gambaran umum sistem, latar belakang masalah, tujuan, inventaris modul, alur hulu-hilir, dan teknologi stack.
* 🏛️ [**System Architecture & Data Flow**](./architecture.md) — Arsitektur bersih MVC, pipeline rendering GIS, relasi antar layer, dan batasan batas eksternal.
* 🔗 [**Entity Relationship Diagram (ERD)**](./erd.md) — Diagram relasi entitas, hierarki penangkapan ikan, foreign keys, dan aturan keselamatan cascade/restrict.

### 2. Basis Data & Kamus Data
* 📖 [**Data Dictionary & Spatial Coordinates**](./data-dictionary.md) — Kamus data lengkap seluruh tabel database, tipe data kolom, nullability, indeks, deskripsi semantik, dan kamus koordinat GIS.

### 3. Modul Spesifik (GIS, Statistik & Pelaporan)
* 🗺️ [**GIS Documentation**](./gis-documentation.md) — Integrasi MapLibre GL JS 4.7.1, standar GeoJSON `[lng, lat]`, 7 layer spasial aktif, dan batas empiris data perikanan.
* 📊 [**Fisheries Statistics Documentation**](./statistics-documentation.md) — Mesin statistik perikanan, formula CPUE (kg/jam & kg/trip), faktor penimbang (*raising factor*), penanganan satuan (kg/ton), dan isolasi filter.
* 📑 [**Reporting & Export Documentation**](./reporting-documentation.md) — Laporan rekapitulasi produksi, distribusi komoditas, kinerja alat tangkap, serta ekspor format Excel dan PDF aman.

### 4. Antarmuka Pemrograman Aplikasi (API) & Keamanan
* 🔌 [**REST API Documentation**](./api-documentation.md) — Dokumentasi endpoint publik `GET /gis/data`, gateway internal satelit Global Fishing Watch (GFW), parameter query, dan skema JSON response aktual.
* 🔒 [**Security Architecture & Policies**](./security.md) — Manajemen autentikasi & sesi, otorisasi peran Spatie RBAC, proteksi CSRF, sanitasi XSS, validasi form, dan proteksi rahasia server.

### 5. Panduan Instalasi, Deployment & Pemeliharaan
* 💻 [**Installation & Local Setup Guide**](./installation.md) — Panduan instalasi langkah demi langkah untuk developer pada lingkungan lokal (PHP 8.4, Composer, NPM, MySQL).
* 🚀 [**Production Deployment & Operations**](./deployment.md) — Panduan deployment server produksi Linux/Nginx, checklist deployment, optimasi cache, SSL HTTPS, serta strategi backup database.
* 🛠️ [**System Maintenance Guide**](./maintenance.md) — Jadwal pemeliharaan berkala, pemantauan log error, optimasi antrean job, dan siklus pembaruan master data taksonomi ASFIS.

### 6. Panduan Pengguna & Pemecahan Masalah
* 👥 [**Panduan Pengguna (User Manual)**](./user-manual.md) — Buku petunjuk operasional berbahasa Indonesia sederhana untuk petugas pelabuhan, verifikator, dan pimpinan instansi.
* 🩺 [**Troubleshooting Guide**](./troubleshooting.md) — Panduan pemecahan masalah cepat untuk mengatasi kendala koneksi database, kompilasi aset, kanvas peta GIS, dan ekspor data.

### 7. Materi Presentasi & Demonstrasi Langsung (F11)
* 🎤 [**Presentation & Demonstration Hub**](./presentation/README.md) — Indeks materi presentasi, elevator pitch (1 menit & 5 menit), dan ringkasan eksekutif.
* 📑 [**Presentation Outline (20 Slides)**](./presentation/presentation-outline.md) — Kerangka slide presentasi terstruktur lengkap dengan objektif dan visual.
* 🎙️ [**Presentation Script**](./presentation/presentation-script.md) — Naskah tutur verbal slide-demi-slide dalam Bahasa Indonesia natural.
* 💻 [**Live Demo Script (10 Menit)**](./presentation/demo-script.md) — Panduan demonstrasi langsung sistem dari beranda, master data, transaksi, statistik, hingga WebGIS.
* 📖 [**System Story**](./presentation/system-story.md) — Narasi komprehensif perjalanan sistem dan nilai strategis bagi Aceh.
* ❓ [**FAQ Document**](./presentation/faq.md) — Tanya jawab terantisipasi untuk pimpinan, enumerator, dan tim IT.
* 📋 [**F11 Report**](./presentation/F11-report.md) — Laporan penuntasan dokumentasi presentasi F11.

### 8. Kesiapan Deployment Produksi & Operasional (F12)
* 🚀 [**Production Deployment & Readiness Hub**](./deployment/README.md) — Pusat dokumentasi kesiapan rilis produksi dan panduan SOP server.
* 📋 [**Environment & Server Checklist**](./deployment/environment-checklist.md) — Audit konfigurasi variabel `.env`, ekstensi PHP 8.4, dan isolasi rahasia.
* 🗄️ [**Database Readiness & Schema Audit**](./deployment/database-readiness.md) — Audit 47 migrasi, integritas foreign key `RESTRICT`, dan strategi deployment.
* 💾 [**Backup & Restore Plan**](./deployment/backup-restore.md) — Prosedur backup otomatis, kompresi GZIP, dan verifikasi pemulihan di staging.
* 📁 [**Storage & Filesystem Architecture**](./deployment/storage.md) — Pengaturan symbolic link `public/storage`, permissions `www-data`, dan sanitasi upload.
* 📜 [**Production Deployment Runbook**](./deployment/deployment-runbook.md) — Prosedur standar operasional (SOP) pra-deploy, deploy, dan pasca-deploy.
* 🔄 [**Emergency Rollback Plan**](./deployment/rollback-plan.md) — SOP pemulihan darurat dan mitigasi kegagalan rilis aplikasi.
* 📊 [**Production Monitoring & Observability**](./deployment/monitoring.md) — Pemantauan log harian, kesehatan koneksi basis data, dan performa query.
* 🛡️ [**Disaster Recovery Plan**](./deployment/disaster-recovery.md) — Kebijakan retensi cadangan 30 hari, pemulihan darurat, dan tim tanggap insiden.
* 🔒 [**Security Checklist & Hardening Audit**](./deployment/security-checklist.md) — Verifikasi pengamanan: `APP_DEBUG=false`, CSRF, XSS, RBAC, dan Nginx headers.
* 🧪 [**Post-Deployment Smoke Test Matrix**](./deployment/smoke-test.md) — Matriks verifikasi cepat 15 fitur kunci pasca-deploy.
* 📑 [**F12 Report**](./deployment/F12-report.md) — Laporan resmi kesiapan deployment produksi (F12).

### 9. RZWP3K Aceh & Integrasi Spasial Maritim (Stage 18)
* 🗺️ [**RZWP3K Aceh Data Foundation & Database Integration**](./rzwp3k-aceh-data-foundation.md) — Fondasi data zonasi pesisir dan pulau kecil (Qanun Aceh No. 1 Tahun 2020), struktur 4 kawasan utama (KPU, KK, AL, KSNT), skema tabel `rzwp3k_zones`, Eloquent model & scopes, seeder 15 katalog zona resmi, RFC 7946 GeoJSON validator service, dan Artisan importer command `php artisan rzwp3k:import` berorientasi *dry-run* & keselamatan data.
* 📍 [**RZWP3K Spatial Source Manifest**](./rzwp3k-spatial-source-manifest.md) — Manifest registri sumber spasial resmi, verifikasi otoritas, pemetaan atribut GeoJSON, audit CRS target EPSG:4326, dan kebijakan *Zero Fake Polygon*.
* 🌐 [**RZWP3K MapLibre Integration**](./rzwp3k-maplibre-integration.md) — Integrasi visualisasi WebGIS MapLibre GL JS 4.7.1, internal API `GET /api/rzwp3k/zones`, layer poligon & garis batas transparan, popup disclaimer netral, dan empty state notice.
* 🛰️ [**RZWP3K Complete GIS Integration & Final Audit**](./rzwp3k-complete-gis-integration.md) — Dokumentasi penuntasan Stage 18: Engine analisis spasial *Point-in-Polygon* (Fishing Ground & GFW Activity), endpoint analisis terisolasi, proteksi cache, audit keselamatan basis data, dan penegakan terminologi netral.



---

## 🌟 Prinsip Integritas Data Sistem
Sistem Informasi Perikanan Tangkap Provinsi Aceh menjunjung tinggi prinsip:
$$\mathbf{Data\ Integrity} > \mathbf{Business\ Logic} > \mathbf{Statistical\ Correctness} > \mathbf{GIS\ Correctness} > \mathbf{Security} > \mathbf{UI}$$

Dokumentasi ini merefleksikan kondisi nyata sistem tanpa data sintetis/koordinat fiktif.
