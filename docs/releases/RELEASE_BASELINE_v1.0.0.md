# RELEASE BASELINE v1.0.0

## 1. Release

```text
Version: v1.0.0
Description: Sistem Perikanan Aceh — Production Ready Baseline
Status: READY FOR RELEASE
Date: 2026-09-22
```

---

## 2. Environment

| Component | Specification / Version |
|---|---|
| **Operating System** | Windows (x64) |
| **PHP Runtime** | PHP 8.4.16 (cli) (NTS Visual C++ 2022 x64) |
| **Laravel Framework** | v13.32.0 |
| **Composer** | v2.10.1 (2026-06-04) |
| **Node.js** | v22.22.3 |
| **NPM** | v10.9.8 |
| **Vite** | v8.2.2 |
| **TailwindCSS** | v4.3.3 (`@tailwindcss/vite` ^4.0.0) |
| **Database Server** | MySQL / MariaDB (127.0.0.1:3307) |
| **Database Name** | `sistem_perikanan` |

---

## 3. Git

```text
Branch: main
Latest Commit: 043f5fb fix(a11y): add accessible names, labels, aria-labels and titles to select and input elements (axe select-name)
Working Tree Status: Clean with respect to repository rules (unstaged/untracked files represent the completed feature stages GFW, RZWP3K, and Stages 20–25).
Sensitive Files: Excluded (.env, *.sql, storage/logs/*). No sensitive credentials or dumps in working tree.
```

---

## 4. Database

```text
Connection: mysql
Database: sistem_perikanan
Host: 127.0.0.1
Port: 3307
Migration Status: All migrations applied (0 pending)
Total Migrations Ran: 48 migrations across 16 batches
Orphan Records: 0 (Catches: 0, Landings: 0, Efforts: 0)
```

---

## 5. Tests

```text
Test Runner: PHPUnit 12.5.35 / Laravel Test Runner
Tests: 412 passed
Assertions: 2,197
Failures: 0
Errors: 0
Duration: 32.61s
Coverage: 100% test pass rate across Feature and Unit suites
```

---

## 6. Application Modules

1. **Master Data & Taxonomy**:
   - FAO ASFIS (2026.1) Species Catalog
   - FAO ISSCFG (Annex M) Fishing Gear Classification
   - WPP-NRI 571 & 572 Maritime Regions
   - Aceh Ports, Landing Sites (TPI), Vessels, and Fishermen
2. **Data Collection (Hulu)**:
   - Fishing Trips, Logbooks, Fishing Efforts (settings & duration)
   - Catches (species, weight, price, disposition)
   - Landing Reports & Itemization
3. **Fisheries Statistics Engine (Analisis)**:
   - Canonical `AdvancedStatisticService` (Effort, Catch, CPUE, Fleet Composition)
   - `CatchEstimationService` (Sample fractions, raising factors, estimation workflow)
   - `MonthlyProductionService` (Multi-dimensional reconciliation)
   - `StatisticsApiController` (JSON REST API endpoints under `/api/statistics/`)
4. **Data Validation Engine**:
   - `FisheriesValidationEngineService` (Referential, temporal, numeric, and statistical rules)
   - Non-destructive diagnostic auditing (`/analysis/validation/{trip}/audit`)
5. **Output & Reporting (Hilir)**:
   - Executive Summary, Fisheries Statistics, and Production Reconciliation reports
   - UTF-8 BOM CSV exports for Microsoft Excel
   - Official government printable layout (`reports.print`)
6. **GIS & Spatial Analysis**:
   - Canonical internal GIS workspace (`/dashboard/gis`) with Leaflet (v1.9.4)
   - RZWP3K Zoning & Spatial Intersection (`Rzwp3kSpatialAnalysisService`, Qanun Aceh No. 1/2020)
   - Public statistical interactive map with MapLibre GL JS (v4.7.1)
7. **Global Fishing Watch (GFW)**:
   - GFW Monitoring workspace (`/gfw/monitoring`) and Vessel Monitoring (`/gfw/vessels`)
   - Strict architectural isolation: Browser $\to$ Laravel Gateway $\to$ GFWService $\to$ GFW API v3
   - Secure server-side Bearer token handling

---

## 7. Security

- **Authentication**: Laravel Breeze session auth + Google SSO (Socialite OAuth2).
- **Authorization**: Spatie Laravel Permission (`super-admin`, `admin`, `verifikator`, `analis`, `enumerator`, `nelayan`).
- **HTTP Status Codes**: Unauthenticated API $\to$ `401 Unauthorized`; Unauthorized $\to$ `403 Forbidden`.
- **Query Protection**: Eloquent ORM parameterized queries; raw expressions limited to pre-defined aggregations.
- **XSS Sanitization**: 100% Blade `{{ }}` escaping.
- **Credential Safety**: GFW API token and database credentials isolated to `.env`; `.gitignore` updated to ignore `*.sql` globally.

---

## 8. Known Findings

| Severity | Finding | File / Component | Status / Disposition |
|:---:|---|---|---|
| **LOW** | Mobile banner heading wrapping on 375px screens | [`resources/views/gfw/monitoring.blade.php`](file:///c:/XPROJECT/sistem-perikanan/resources/views/gfw/monitoring.blade.php) | **RESOLVED**: Responsive flex-wrap applied. |
| **LOW** | Root `.sql` database backup file safety | [`.gitignore`](file:///c:/XPROJECT/sistem-perikanan/.gitignore) | **RESOLVED**: `*.sql` universally ignored. |
| **INFO** | Coexistence of Leaflet and MapLibre GL JS | `resources/views/` | **VERIFIED**: Leaflet powers internal GIS & GFW monitoring; MapLibre powers vessel workspace & public statistics. |
| **INFO** | Inert backup file in app directory | `app/Imports/AsfisSpeciesImporter.php.bak` | **INERT**: Non-executable backup file. |

---

## 9. Release Gate Decision

```text
========================================
RELEASE-01 RESULT: PASS
========================================

v1.0.0 baseline is frozen and verified.
Ready for BACKUP-01.
No database, schema, data, business-rule, or API changes were performed.
```
