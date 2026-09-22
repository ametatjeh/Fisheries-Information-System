# MAINTENANCE BASELINE REPORT — v1.0.0

```text
Project: Sistem Perikanan Aceh
Release: v1.0.0
Commit Baseline: 043f5fb
Date: 2026-09-22
Status: R3 — MAINTENANCE READY
```

---

## 1. System Health Check (R3.1)

- **Laravel Framework**: v13.32.0
- **PHP Runtime**: 8.4.16 (cli) NTS Visual C++ 2022 x64
- **Composer**: 2.10.1
- **Node.js / NPM**: Node v22.22.3 / NPM 10.9.8
- **Vite**: v8.2.2
- **TailwindCSS**: v4.3.3 (`@tailwindcss/vite` ^4.0.0)
- **Application State**:
  - Environment: `local` (Production Mode: `APP_DEBUG=false`)
  - Maintenance Mode: `OFF`
  - Config Cache: `CACHED`
  - Route Cache: `CACHED` (172 routes)
  - View Cache: `CACHED`
  - Storage Link: `public/storage LINKED`

---

## 2. Database Health & Schema Drift (R3.2)

- **Host & Port**: 127.0.0.1:3307
- **Database**: `sistem_perikanan` (MariaDB 11.8.3)
- **Migration Status**: 48 migrations applied across 16 batches, **0 pending migrations**.
- **Table Count**: 48 tables (100% baseline match).
- **Foreign Keys & Indexes**: 74 Foreign Keys, 253 Indexes. Zero schema drift detected.
- **Database Safety**: Source database is strictly protected against unapproved alterations.

---

## 3. Data Integrity & Lineage (R3.3)

- **Lineage**: Nelayan $\to$ Kapal $\to$ Fishing Trip $\to$ Fishing Effort $\to$ Catch $\to$ Landing $\to$ Landing Items.
- **Orphan Catches**: `0` (Catches $\to$ Fishing Trips)
- **Orphan Landings**: `0` (Landings $\to$ Fishing Trips)
- **Orphan Fishing Efforts**: `0` (Fishing Efforts $\to$ Fishing Trips)
- **Orphan Landing Items**: `0` (Landing Items $\to$ Landings)
- **Reference Integrity**: 100% valid linkages to ASFIS Species (13,965), ISSCFG Gears (88), and WPP-NRI (571 & 572).

---

## 4. Statistics Integrity (R3.4)

- **Canonical Architecture**:
  - `AdvancedStatisticService`: Effort, Catch, and CPUE engine.
  - `MonthlyProductionService`: Multi-dimensional reconciliation.
  - `CatchEstimationService`: Raising factor & sampling fraction.
  - `FisheriesValidationEngineService`: Non-destructive quality audits.
- **CPUE Formula Baseline**: $CPUE = \frac{\text{Catch (kg)}}{\text{Fishing Effort (hours)}}$ (Mean CPUE = 61.08 kg/hr).
- **Recalculation Safety**: Zero duplicate formulas in Blade, JavaScript, or Exporters.

---

## 5. Global Fishing Watch (GFW) Operations (R3.5)

- **Architecture**: Strict isolation: Browser $\to$ Laravel Internal Gateway (`/api/gfw/*`) $\to$ GFW Service $\to$ GFW API v3.
- **Credential Security**: Personal Access Token isolated exclusively to server-side `.env`. Zero tokens in client scripts.
- **Rate Limiting & Safety**: `throttle:gfw-api` middleware active. Graceful JSON error envelopes on upstream timeout or rate limits.

---

## 6. GIS Operations (R3.6)

- **Workspaces & Engines**:
  - **Leaflet (v1.9.4)**: Powers internal GIS (`/dashboard/gis`) and GFW Monitoring (`/gfw/monitoring`).
  - **MapLibre GL JS (v4.7.1)**: Powers GFW Vessel Workspace (`/gfw/vessels`) and Public Statistics Map (`/statistik`).
- **Spatial Coverage**: Aceh Provincial boundary, WPP-NRI 571 & 572, 15 RZWP3K zoning polygons (Qanun Aceh No. 1/2020), 8 fishing grounds, and 13 landing sites.

---

## 7. Reporting Operations (R3.7)

- **Report Suite**: All 8 report types operational (`summary`, `statistics`, `production`, `catches`, `efforts`, `landings`, `sampling`, `monthly`).
- **Data Parity**: Report figures derive directly from domain services, matching dashboard analytics 1:1.
- **Export Standards**: CSV exports include UTF-8 Byte Order Mark (`\xEF\xBB\xBF`) for Microsoft Excel compatibility; print view includes official government layout.

---

## 8. Security & Logging (R3.8, R3.9)

- **Debug Mode**: `APP_DEBUG=false` enforced.
- **Secrets Protection**: All credentials masked in reports and logs.
- **Repository Cleanliness**: Universal `*.sql` rule enforced in `.gitignore`.
- **Log Monitoring**: `storage/logs/laravel.log` monitored; zero fatal or uncaught database exceptions.

---

## 9. Backup & Restore Drill Readiness (R3.10, R3.11)

- **Operational Backup**:
  - Pre-deployment snapshot: `database/backups/sistem_perikanan_v1.0.0_pre_deployment_20260922_163113.sql` (7.17 MB).
  - CLI tooling: `mysqldump.exe` and `mysql.exe` at `C:\CodesEasy\DevKit\services\mysql\bin\`.
  - Artisan commands available: `backup:run`, `backup:list`, `backup:clean`, `backup:monitor`.
- **Restore Verification**:
  - Isolated test database `sistem_perikanan_restore_test` verified (48/48 tables, 100% row match, 0 orphans).

---

## 10. Dependency Management (R3.12)

- **Lock Files**: `composer.lock` and `package-lock.json` are frozen and verified.
- **Policy**: Zero automatic package upgrades on production. Any upgrade must follow controlled Change Management.

---

## 11. Regression Testing Baseline (R3.13)

- **Test Suite**: 412 tests, 2,197 assertions, 0 failures, 0 errors.
- **Duration**: 32.61s.
- **Code Style**: Laravel Pint PASS.

---

## 12. Change Management Matrix (R3.14)

| Change Category | Type | Approval Protocol |
|---|:---:|---|
| **Documentation** | TYPE A | Peer review, low risk |
| **Non-breaking UI/UX** | TYPE B | Visual QA & browser console audit |
| **Application Behavior** | TYPE C | Audit $\to$ Feature Test $\to$ Regression $\to$ Release |
| **Database Schema/Data** | TYPE D | Backup $\to$ Migration Review $\to$ Restore Test $\to$ Regression $\to$ Approval |
| **Statistics / Methodology** | TYPE E | Domain & Methodological Review $\to$ Statistical Test $\to$ Approval |
| **API / GFW Gateway** | TYPE F | Gateway Security Audit $\to$ Compatibility Test $\to$ Approval |

---

## 13. Final Maintenance Status (R3.17)

```text
========================================
R3 = MAINTENANCE READY
========================================

Sistem Perikanan v1.0.0
berada dalam status maintenance operasional.

Tidak ada perubahan schema/data/business-rule/API
yang dilakukan tanpa change control.
========================================
```
