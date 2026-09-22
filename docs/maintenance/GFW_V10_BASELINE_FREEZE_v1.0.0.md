# GFW-V10 Baseline Freeze Document

**Project:** `C:\XPROJECT\sistem-perikanan`  
**Document Version:** 1.0.0  
**Date:** 2026-09-22  
**Audit Mode:** STRICT READ-ONLY / NO MODIFICATION  
**Status:** **PASS** | **BASELINE FROZEN**

---

## 1. Environment Baseline

- **Framework:** Laravel 13.32.0
- **PHP Version:** PHP 8.4.16 (cli) (NTS Visual C++ 2022 x64)
- **Database Engine:** MySQL / MariaDB
- **Database Host:** `127.0.0.1`
- **Database Port:** `3307`
- **Main Database:** `sistem_perikanan`
- **GFW Database:** `sistem_gfw`
- **Frontend Mapping:** MapLibre GL JS 4.7.1

---

## 2. Main Database (`sistem_perikanan`)

- **Total Tables:** Exactly **48**
- **Total Migrations:** Exactly **48**
- **Cross-Database Foreign Keys:** **0**
- **Schema Integrity:** Intact and verified. Local master data (`vessels`, `species`, `fishing_gears`, `landing_sites`, `fishing_grounds`, `fisher_groups`, `fishers`, `districts`, `villages`) untouched.
- **Operational Data:** Catch records, landings, trips, biological sampling, and estimations remain completely isolated.

---

## 3. GFW Database (`sistem_gfw`)

- **Total Tables:** **5** (`gfw_sync_runs`, `gfw_vessel_presence`, `gfw_vessel_types`, `gfw_vessels`, `migrations`)
- **Row Counts (Production Baseline):**
  - `gfw_vessels`: 0
  - `gfw_vessel_presence`: 0
  - `gfw_sync_runs`: 0
  - `gfw_vessel_types`: 15
- **Data Quality:**
  - Coordinates verified within valid latitude (-90°..90°) and longitude (-180°..180°).
  - Observed timestamps non-null and valid ISO-8601.
  - Zero duplicate records on repeat ingestion.

---

## 4. GFW Architecture

- **Canonical Services:**
  - `App\Services\Gfw\GfwApiService`
  - `App\Services\Gfw\GfwVesselService`
  - `App\Services\Gfw\GfwActivityService`
  - `App\Services\Gfw\AoiService`
  - `App\Services\Gfw\GfwIngestionService`
  - `App\Services\Gfw\GfwObservatoryService`
- **Architectural Flow:**
  $$\text{Browser} \longrightarrow \text{Laravel Internal API Gateway} \longrightarrow \text{Canonical GFW Services} \longrightarrow \text{GFW Upstream API}$$
- **Authentication & Authorization:**
  - 1 unified Laravel authentication system (`users` table in `sistem_perikanan`).
  - 1 unified permission layer (`access.gis` permission required for GFW access).
  - 0 authentication or user management tables in `sistem_gfw`.

---

## 5. GFW UI & Front-End

- **Routes:**
  - `/gfw/monitoring` (GFW Vessel Monitoring Workspace)
  - `/gfw/vessels` / `/gfw/observatory` (GFW Vessel Observatory Local Database)
- **Map Implementation:**
  - MapLibre GL JS 4.7.1 rendered without client-side API tokens.
  - AOI geometry: `zee-indonesia-aceh` (EPSG:4326 Polygon).
  - Interactive vessel markers, tracks, and popups.
- **Vessel Detail Drawer:**
  - Displays vessel identity, classification, dimensions, and observation summary.
  - Non-available attributes explicitly marked as `—` / `N/A` without data fabrication.

---

## 6. GIS & Fisheries Spatial Architecture

- **Routes:**
  - `/dashboard/gis` (Peta Terpadu GIS)
  - `/statistik` (Statistik Publik)
  - `/map` (Authenticated redirect to `/dashboard/gis`)
- **Spatial Isolation:** Master fishing grounds, TPIs, and RZWP3K zoning remain entirely separate from GFW satellite layers.

---

## 7. Dashboard Navigation

- **Sidebar Structure Restored:**
  - Menu Utama: `Dashboard`
  - Master Data: `Wilayah`, `Jenis Ikan`, `Nelayan`, `Kelompok Nelayan`, `Kapal`, `Alat Tangkap`, `Landing Site`, `Fishing Ground`
  - Pengumpulan Data: `Trip Penangkapan`, `Logbook`, `Fishing Effort`, `Hasil Tangkapan`, `Pendaratan`
  - Analisis: `Validasi`, `Sampling`, `Estimasi`, `Statistik`
  - Output: `Laporan`, `Peta Terpadu GIS`
  - GFW Satellite (Dedicated):
    - `GFW Monitoring` (`/gfw/monitoring`)
    - `GFW Vessel Observatory` (`/gfw/vessels`)
  - Administrasi: `Manajemen Pengguna`
  - Pengaturan: `Profil`
- **Data Separation:** Clear conceptual separation between *Data Kapal Lokal* (`Master Data → Kapal`) and *Observasi Kapal Satelit* (`GFW Satellite → GFW Vessel Observatory`).

---

## 8. Security & Credential Isolation

- **Token Safety:**
  - GFW API Token is stored server-side only in `.env` / configuration.
  - Zero token exposure in Blade templates, JavaScript, JSON responses, error traces, or git history.
  - Internal API proxy enforces rate limiting and request validation.

---

## 9. Statistics Isolation

- GFW vessel observations do NOT enter:
  - Catches (`catches`)
  - Landings (`landings`, `landing_items`)
  - Fishing Trips (`fishing_trips`)
  - Fishing Efforts (`fishing_efforts`)
  - Catch Estimations (`catch_estimations`)
  - Monthly Production Statistics (`monthly_production_statistics`)
- GFW Vessel Types remain completely separated from FAO ISSCFG gear types and ASFIS species classifications.

---

## 10. Regression Test Suite

- **Total Tests:** **439 passed**
- **Total Assertions:** **2,370 assertions**
- **Failures:** **0**
- **Errors:** **0**
- **Laravel Pint Code Formatter:** **PASS** (0 style issues)

---

## 11. Final Milestone Status

| Milestone | Scope | Final State |
|---|---|---|
| **GFW-V01** | Read-Only Architecture Audit | **PASS** |
| **GFW-V02** | Isolated GFW Database (`sistem_gfw`) | **PASS** |
| **GFW-V03** | Data Contract Verification | **PASS** |
| **GFW-V04** | Controlled Ingestion Engine | **PASS** |
| **GFW-V05** | Backend / API Vessel Observatory | **PASS** |
| **GFW-V06** | Vessel Observatory UI | **PASS** |
| **GFW-V07** | Sync Management, Monitoring & Retention | **PASS** |
| **GFW-V08** | Final Security & Production Acceptance | **PASS** |
| **GFW-V09** | Dashboard Menu Restoration & Navigation | **PASS** |
| **GFW-V10** | Final Read-Only Audit & Baseline Freeze | **PASS** |

### **FINAL GATE: PASS**
### **BASELINE: FROZEN**
