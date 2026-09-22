# Rencana Pengembangan Masa Depan (Future Roadmap)
**Sistem Informasi & Statistik Perikanan Tangkap Provinsi Aceh**

```text
System: Sistem Informasi Perikanan Tangkap Aceh
Stage: F11 — Future Roadmap
Document Type: Strategic Development Lifecycle
Date: 21 September 2026
```

---

## 1. Pemisahan Status: Existing vs Future

Untuk menjaga kejelasan komitmen pengembangan, sistem memisahkan secara tegas antara fitur yang telah beroperasi saat ini dengan rencana pengembangan di masa mendatang.

```text
┌─────────────────────────────────────────────────────────────┐
│                    STATUS SAAT INI (EXISTING)               │
│ - Master Data Lengkap (Nelayan KUSUKA, Kapal, Gear, Species)│
│ - Rantai Transaksi (Trip -> Effort -> Catch -> Landing)     │
│ - Mesin Statistik (CPUE Jam & Trip, Agregasi Bulanan/Tahun) │
│ - WebGIS 7 Layer MapLibre GL JS 4.7.1 & Analitika WPP       │
│ - Ekspor Laporan Excel (.xlsx aman) dan Dokumen Cetak PDF   │
│ - Sistem Keamanan RBAC Spatie & CSRF/XSS Protection         │
└──────────────────────────────┬──────────────────────────────┘
                               │
                               ▼
┌─────────────────────────────────────────────────────────────┐
│                 RENCANA PENGEMBANGAN (FUTURE)               │
│                                                             │
│  [Tahap I: Pemutakhiran Geometri Resmi]                     │
│  - Integrasi batas poligon definitif Master Fishing Ground  │
│    segera setelah dirilis resmi oleh DKP Aceh / KKP.        │
│                                                             │
│  [Tahap II: Digitalisasi GPS Nelayan Lapangan]              │
│  - Program peningkatan pencatatan koordinat GPS pada modul  │
│    effort nelayan melalui aplikasi mobile enumerator.       │
│                                                             │
│  [Tahap III: Analitika Biologi Ikan Lanjutan]               │
│  - Pengembangan modul kurva pertumbuhan panjang-berat (L-W) │
│    dan pemantauan Tingkat Kematangan Gonad (TKG).           │
│                                                             │
│  [Tahap IV: Penguatan Integrasi Telemetry Satelit AIS]      │
│  - Pemanfaatan data satelit Global Fishing Watch (GFW)      │
│    untuk pemantauan kepatuhan kapal di batas WPP 571 & 572. │
└─────────────────────────────────────────────────────────────┘
```

---

## 2. Rencana Implementasi & Pelatihan

| Fase | Target Program | Output yang Diharapkan |
| :--- | :--- | :--- |
| **Fase 1 (Bulan 1–2)** | **Deployment Production & UAT** | Sistem terpasang di infrastruktur server resmi Pemerintah Aceh dengan sertifikat SSL aktif. |
| **Fase 2 (Bulan 3–4)** | **Pelatihan Petugas Lapangan** | Bimbingan teknis bagi enumerator dan syahbandar di 13 pelabuhan pendaratan perikanan se-Aceh. |
| **Fase 3 (Bulan 5–6)** | **Integrasi Spasial Definitif** | Pemasukan data shapefile poligon Fishing Ground resmi dan evaluasi tahunan CPUE. |
