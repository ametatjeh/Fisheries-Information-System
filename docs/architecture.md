# System Architecture & Data Flow
**Sistem Informasi & Statistik Perikanan Tangkap Provinsi Aceh**

```text
System: Sistem Informasi Perikanan Tangkap Aceh
Version: 1.0.0
Documentation Version: 1.0.0
Date: 21 September 2026
Status: Final Approved (F9 Audit Compliant)
```

---

## 1. High-Level Architecture

Arsitektur aplikasi dibangun di atas arsitektur *Monolithic Clean MVC with Service Layer* menggunakan Laravel 13, dengan pemisahan domain logic yang ketat.

```text
┌─────────────────────────────────────────────────────────────┐
│                    CLIENT / WEB BROWSER                     │
│  (Desktop, Tablet, Mobile - Chrome / Firefox / Safari / Edge)│
└──────────────────────────────┬──────────────────────────────┘
                               │ HTTPS / REST JSON
                               ▼
┌─────────────────────────────────────────────────────────────┐
│                   WEB SERVER / REVERSE PROXY                │
│                        (Nginx / Caddy)                      │
└──────────────────────────────┬──────────────────────────────┘
                               │ FastCGI (PHP 8.4)
                               ▼
┌─────────────────────────────────────────────────────────────┐
│                     LARAVEL 13 CORE ENGINE                  │
│                                                             │
│  ┌───────────────────────────────────────────────────────┐  │
│  │ HTTP Middleware Pipeline                              │  │
│  │ (Auth, Role/Permission, CSRF, Rate Limiting, Throttling)│
│  └───────────────────────────┬───────────────────────────┘  │
│                              ▼                              │
│  ┌───────────────────────────────────────────────────────┐  │
│  │ Routing & Controllers                                 │  │
│  │ (Admin, DataCollection, Analysis, Output, Gis, Gfw)   │  │
│  └───────────────────────────┬───────────────────────────┘  │
│                              ▼                              │
│  ┌───────────────────────────────────────────────────────┐  │
│  │ Services Layer (Business Logic & Analytics)          │  │
│  │ - AdvancedStatisticService                            │  │
│  │ - CatchEstimationService                              │  │
│  │ - MonthlyProductionService                            │  │
│  │ - GfwApiService & Client                              │  │
│  └───────────────────────────┬───────────────────────────┘  │
│                              ▼                              │
│  ┌───────────────────────────────────────────────────────┐  │
│  │ Eloquent ORM & Data Models (35+ Entities)             │  │
│  └───────────────────────────┬───────────────────────────┘  │
└──────────────────────────────┼──────────────────────────────┘
                               │ PDO MySQL
                               ▼
┌─────────────────────────────────────────────────────────────┐
│                   MARIADB / MYSQL DATABASE                  │
│         (Transactional Tables & Spatial Reference Data)     │
└─────────────────────────────────────────────────────────────┘
```

---

## 2. Core Fisheries Data Flow

Rantai aliran data dari pencatatan hulu di lapangan hingga diseminasi statistik dan visualisasi spasial:

```text
[1. Master Entities]
  Nelayan (Fishers) ──┐
  Kapal (Vessels)   ──┴─► [2. Fishing Trip]
  Alat Tangkap (Gear)     (Pelayaran Penangkapan: Tanggal, WPP, Pelabuhan)
                                 │
                                 ▼
                          [3. Fishing Effort]
                          (Operasi Setting/Hauling, Durasi Jam, Koordinat GPS)
                                 │
                                 ▼
                          [4. Catch Logging]
                          (Berat kg per Jenis Ikan / FAO ASFIS)
                                 │
                                 ▼
                          [5. Pendaratan / Landing]
                          (Volume Ikan Didaratkan di TPI / Pelabuhan)
                                 │
                                 ▼
                          [6. Validasi & Verifikasi]
                          (Pemeriksaan data oleh Verifikator / Petugas Pelabuhan)
                                 │
            ┌────────────────────┴────────────────────┐
            ▼                                         ▼
   [7. Analisis & Statistik]                  [8. Visualisasi WebGIS]
   - Perhitungan CPUE (kg/jam & kg/trip)      - Endpoint API GET /gis/data
   - Estimasi Tangkapan (Raising Factor)      - Transformasi GeoJSON
   - Agregasi Bulanan & Tahunan               - MapLibre GL JS 7 Layers
            │                                         │
            └────────────────────┬────────────────────┘
                                 ▼
                     [9. Output & Pelaporan]
                     - Dashboard Eksekutif & Publik (/statistik)
                     - Laporan Tabular & Grafik Komoditas
                     - Ekspor Excel (.xlsx) & Cetak Dokumen
```

---

## 3. GIS Data Architecture & Rendering Pipeline

Sistem Informasi Geografis (GIS) terintegrasi penuh ke dalam basis data operasional tanpa replikasi data terpisah.

```text
┌──────────────────────────────────────────────────────────────┐
│                       DATABASE MYSQL                         │
│  - LandingSite (latitude, longitude)                         │
│  - FishingEffort (latitude_setting, longitude_setting)       │
│  - Vessel -> HomeportSite (latitude, longitude)              │
│  - Logbook (latitude, longitude)                             │
│  - FishingGround (latitude, longitude / master info)         │
│  - Wppnri (Code, Name, Polygons)                             │
└──────────────────────────────┬───────────────────────────────┘
                               │ Eloquent Query Builder
                               ▼
┌──────────────────────────────────────────────────────────────┐
│                    OUTPUT / GisController                    │
│  - Filter Extraction (Year, Month, Gear, Species, Site, WPP) │
│  - Boundary Sanitization (-90<=lat<=90, -180<=lng<=180)     │
│  - Null Coordinate Isolation (Zero Fake Point Generation)    │
│  - WPP Aggregate Calculation without Double Counting         │
└──────────────────────────────┬───────────────────────────────┘
                               │ JSON Response (GET /gis/data)
                               ▼
┌──────────────────────────────────────────────────────────────┐
│                     FRONTEND / BROWSER                       │
│  - Fetch Async Data via REST API                             │
│  - GeoJSON Converter (Coordinates formatted as [lng, lat])   │
│  - MapLibre GL JS 4.7.1 WebGL Canvas Engine                  │
│                                                              │
│  ┌────────────────────────────────────────────────────────┐  │
│  │ Active Layers:                                         │  │
│  │ 1. Fishing Effort Points (Alat Tangkap Setting)        │  │
│  │ 2. Landing Sites (PPN / PPI / TPI Aktif)               │  │
│  │ 3. Vessel Registered Homeports (Pangkalan Kapal)       │  │
│  │ 4. Historical Logbooks (Rekam Jejak Operasional)       │  │
│  │ 5. Master Fishing Grounds (Status Geometri Resmi)      │  │
│  │ 6. WPPNRI 571 (Selat Malaka - Laut Andaman)            │  │
│  │ 7. WPPNRI 572 (Samudera Hindia Barat Sumatera)         │  │
│  └────────────────────────────────────────────────────────┘  │
└──────────────────────────────────────────────────────────────┘
```

---

## 4. Security & External Boundary Architecture

* **Internal API Gateway GFW:** Permintaan data satelit Global Fishing Watch diakses melalui server Laravel (`app/Services/Gfw/`) dengan enkripsi token server-side, rate limiting (`throttle:gfw-api`), dan tidak pernah mengekspos API key ke JavaScript client.
* **Public Boundary:** Pengunjung publik hanya dapat mengakses rute baca (`/`, `/statistik`, `GET /gis/data`), sementara rute mutasi (`POST`, `PUT`, `DELETE`) dan data operasional dilindungi autentikasi sesi dan otorisasi peran berbasis Spatie Laravel Permission.
