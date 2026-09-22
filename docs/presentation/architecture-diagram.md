# Architecture & Data Flow Diagrams
**Sistem Informasi & Statistik Perikanan Tangkap Provinsi Aceh**

```text
System: Sistem Informasi Perikanan Tangkap Aceh
Stage: F11 — Architecture Diagrams
Document Type: Visual & Component Architecture
Date: 21 September 2026
```

---

## 1. High-Level System Architecture Diagram

```text
                      ┌───────────────────────────────┐
                      │         PENGGUNA SISTEM       │
                      │  (Pimpinan DKP, Enumerator,   │
                      │   Verifikator, Publik/Nelayan)│
                      └───────────────┬───────────────┘
                                      │ HTTPS Web Browser
                                      ▼
                      ┌───────────────────────────────┐
                      │    WEB SERVER & REVERSE PROXY │
                      │        (Nginx / Caddy)        │
                      └───────────────┬───────────────┘
                                      │ PHP 8.4-FPM
                                      ▼
┌─────────────────────────────────────────────────────────────────────────────┐
│                           LARAVEL 13 APPLICATION                            │
│                                                                             │
│   ┌─────────────────────────────────────────────────────────────────────┐   │
│   │ HTTP Middleware Pipeline                                            │   │
│   │ [Authentication] [Spatie RBAC Roles] [CSRF Token] [Rate Limiter]    │   │
│   └──────────────────────────────────┬──────────────────────────────────┘   │
│                                      ▼                                      │
│   ┌─────────────────────────────────────────────────────────────────────┐   │
│   │ Routing & Controllers Layer                                         │   │
│   │ - LandingPageController     - FishermanController & VesselController│   │
│   │ - FishingTripController     - CatchController & LandingController   │   │
│   │ - StatisticController       - GisController & ReportController      │   │
│   └──────────────────────────────────┬──────────────────────────────────┘   │
│                                      ▼                                      │
│   ┌─────────────────────────────────────────────────────────────────────┐   │
│   │ Domain Services Layer (Business Logic)                              │   │
│   │ - AdvancedStatisticService (CPUE, Aggregations, Filter Norms)       │   │
│   │ - CatchEstimationService (Raising Factors, Sampling Projections)    │   │
│   │ - MonthlyProductionService (Official Monthly Dissemination)         │   │
│   └──────────────────────────────────┬──────────────────────────────────┘   │
│                                      ▼                                      │
│   ┌─────────────────────────────────────────────────────────────────────┐   │
│   │ Eloquent ORM & Data Models (35+ Entities, Relationships, Scopes)    │   │
│   └──────────────────────────────────┬──────────────────────────────────┘   │
└──────────────────────────────────────┼──────────────────────────────────────┘
                                       │ PDO Connection
                                       ▼
                      ┌───────────────────────────────┐
                      │    BASIS DATA MARIADB/MYSQL   │
                      │ (Tabel Transaksi, Master Data,│
                      │  dan Batas Spasial WPP)       │
                      └───────────────┬───────────────┘
                                      │
                 ┌────────────────────┴────────────────────┐
                 ▼                                         ▼
  ┌─────────────────────────────┐           ┌─────────────────────────────┐
  │   PELAPORAN & EKSPOR DATA   │           │      WEBGIS MAP ENGINE      │
  │ - Rekapitulasi Produksi     │           │ - Endpoint GET /gis/data    │
  │ - Analisis Komoditas        │           │ - Format GeoJSON [lng, lat] │
  │ - Ekspor Excel (.xlsx)      │           │ - MapLibre GL JS v4.7.1     │
  │ - Dokumen Cetak PDF         │           │ - 7 Layer Spasial Interaktif│
  └─────────────────────────────┘           └─────────────────────────────┘
```

---

## 2. GIS Data Flow Pipeline

```text
┌──────────────────────────────────────────────────────────────┐
│                        DATABASE MYSQL                        │
│  - landing_sites (latitude, longitude)                       │
│  - fishing_efforts (latitude_setting, longitude_setting)     │
│  - vessels -> homeportSite (latitude, longitude)             │
│  - logbooks (latitude, longitude)                            │
│  - wppnri (Poligon Batas Wilayah 571 & 572)                  │
└──────────────────────────────┬───────────────────────────────┘
                               │
                               ▼
┌──────────────────────────────────────────────────────────────┐
│                    OUTPUT / GisController                    │
│  1. Ekstraksi Filter Query (Year, Month, Species, Gear, WPP) │
│  2. Validasi Batas Koordinat (-90<=lat<=90, -180<=lng<=180)  │
│  3. Isolasi Nilai Null / Zero (Mencegah Titik Fiktif)        │
│  4. Agregasi Analitik WPP (Catch, Trips, Duration, CPUE)     │
└──────────────────────────────┬───────────────────────────────┘
                               │
                               ▼ JSON Payload Response
┌──────────────────────────────────────────────────────────────┐
│                      REST API ENDPOINT                       │
│                        GET /gis/data                         │
│  (Data Center, Ports, Efforts, Vessels, Logbooks, WPP Array) │
└──────────────────────────────┬───────────────────────────────┘
                               │
                               ▼ Async Fetch via AJAX
┌──────────────────────────────────────────────────────────────┐
│                   FRONTEND MAP CLIENT (BROWSER)              │
│  1. GeoJSON Converter (Standar Koordinat RFC 7946 [lng, lat])│
│  2. Inisialisasi MapLibre GL JS 4.7.1 WebGL Canvas           │
│  3. Layer Registrasi & Simbologi Warna (7 Layer)             │
│  4. Interaksi Klik Popup & Layer Switcher Toggle             │
└──────────────────────────────────────────────────────────────┘
```
