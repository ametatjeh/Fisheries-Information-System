# BACKUP & RESTORE VERIFICATION REPORT — v1.0.0

## 1. Release

```text
Version: v1.0.0
Release Description: Sistem Perikanan Aceh — Production Ready Baseline
Baseline Commit: 043f5fb
Date: 2026-09-22
Audit Stage: R2.1–R2.2 Final Backup & Restore Verification
```

---

## 2. Source Database

```text
Database Management System: MariaDB 11.8.3 / MySQL compatible
Host: 127.0.0.1
Port: 3307
Database: sistem_perikanan
Access Mode: STRICT READ-ONLY
Status: UNTOUCHED / ZERO MUTATION
```

---

## 3. Backup (R2.1)

```text
Backup Tool: C:\CodesEasy\DevKit\services\mysql\bin\mysqldump.exe
Target Directory: database/backups/
Backup File: database/backups/sistem_perikanan_v1.0.0_pre_deployment_20260922_163113.sql
Backup Size: 7,173,513 bytes (7.17 MB, 31,154 lines)
Backup Timestamp: 2026-09-22 16:32:01
mysqldump Flags: --single-transaction --routines --triggers (without --add-drop-database)
mysqldump Result: PASS (Exit code 0)
Dump Verification: Valid SQL syntax, complete table definitions, procedures/triggers, and dataset.
```

---

## 4. Restore (R2.2)

```text
Restore Tool: C:\CodesEasy\DevKit\services\mysql\bin\mysql.exe
Target Test Database: sistem_perikanan_restore_test (Isolated temporary testing schema)
Source Database Protected: YES (sistem_perikanan was never targeted or modified)
Restore Result: PASS (Exit code 0, 100% statements executed cleanly)
Test Database Retained: YES (sistem_perikanan_restore_test preserved for external audit review)
```

---

## 5. Integrity & Reconciliation Matrix

| Object / Table | Source DB (`sistem_perikanan`) | Restore-Test DB (`sistem_perikanan_restore_test`) | Match Status |
|---|:---:|:---:|:---:|
| **Total Tables** | **48** | **48** | **MATCH (100%)** |
| **Migrations Applied** | **48** | **48** | **MATCH (100%)** |
| **Foreign Keys** | **74** | **74** | **MATCH (100%)** |
| **Indexes** | **253** | **253** | **MATCH (100%)** |
| `species` (ASFIS) | 13,965 | 13,965 | **MATCH** |
| `fao_asfis_species` | 13,965 | 13,965 | **MATCH** |
| `fishing_gears` (ISSCFG) | 88 | 88 | **MATCH** |
| `wppnri` | 2 | 2 | **MATCH** |
| `rzwp3k_zones` | 15 | 15 | **MATCH** |
| `fishing_grounds` | 8 | 8 | **MATCH** |
| `landing_sites` | 13 | 13 | **MATCH** |
| `fishers` | 23 | 23 | **MATCH** |
| `vessels` | 19 | 19 | **MATCH** |
| `fishing_trips` | 45 | 45 | **MATCH** |
| `fishing_efforts` | 62 | 62 | **MATCH** |
| `catches` | 169 | 169 | **MATCH** |
| `landings` | 40 | 40 | **MATCH** |
| `landing_items` | 144 | 144 | **MATCH** |
| `samples` | 6 | 6 | **MATCH** |
| `sampling_plans` | 4 | 4 | **MATCH** |
| `biological_measurements` | 35 | 35 | **MATCH** |
| `catch_estimations` | 6 | 6 | **MATCH** |
| `monthly_production_statistics` | 12 | 12 | **MATCH** |
| `validation_logs` | 53 | 53 | **MATCH** |

### Referential Integrity Verification on Restore Database:
- **Orphan Catches**: `0`
- **Orphan Landings**: `0`
- **Orphan Fishing Efforts**: `0`
- **Orphan Landing Items**: `0`

---

## 6. Smoke Test Against Restored Database

Verified programmatically against `sistem_perikanan_restore_test`:
- **User Authentication**: PASS (User accounts `admin@gmail.com`, `petugas@gmail.com`, `verifikator@gmail.com` intact and verifiable).
- **Statistics Engine KPIs**: PASS (`AdvancedStatisticService` returns identical observed catch: 29,090 kg, CPUE: 61.08 kg/hr, landings: 21,350 kg).
- **Production Summary**: PASS (`MonthlyProductionService` returns 341,000 kg official monthly production).
- **GIS Layers**: PASS (8 master fishing grounds and 13 landing sites queried).
- **RZWP3K Spatial Zones**: PASS (15 coastal zoning polygons queried).
- **Validation Engine**: PASS (`FisheriesValidationEngineService::auditTrip()` executes diagnostics without mutating records).

---

## 7. Release Gate Decision

```text
========================================
R2.1–R2.2 BACKUP & RESTORE RESULT: PASS
========================================
```
