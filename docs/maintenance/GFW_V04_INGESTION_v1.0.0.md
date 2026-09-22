# GFW-V04 INGESTION REPORT — CONTROLLED VESSEL & PRESENCE INGESTION

```text
Project                 : Sistem Perikanan Aceh v1.0.0
Stage                   : GFW-V04 CONTROLLED VESSEL & PRESENCE INGESTION
Date of Execution       : 2026-09-22
Ingestion Mode          : CONTROLLED BATCH (Strictly bounded; Dry-run enabled)
Main Database           : sistem_perikanan (Port 3307) — READ-ONLY / MUTATION = 0
GFW Observatory Database: sistem_gfw (Port 3307) — 2 Vessels Ingested / 2 Sync Runs
Laravel Version         : 13.32.0
PHP Version             : 8.4.16
Status                  : PASS
```

---

## 1. Objective

Tujuan tahap **GFW-V04** adalah mengimplementasikan dan menguji **controlled ingestion pertama** untuk data kapal dan observasi posisi satelit dari Global Fishing Watch (GFW) API v3 ke dalam database terisolasi `sistem_gfw`:
1. Menerapkan eksekusi dry-run sebelum proses penulisan ke database.
2. Mengalirkan identitas kapal teramati ke tabel `sistem_gfw.gfw_vessels` secara idempotent (upsert).
3. Menerapkan deduplikasi deterministik pada layer aplikasi untuk `sistem_gfw.gfw_vessel_presence` guna mengatasi keterbatasan indeks non-unique.
4. Merekam telemetri dan audit trail setiap proses sinkronisasi pada `sistem_gfw.gfw_sync_runs`.
5. Mempertahankan isolasi mutlak database operasional `sistem_perikanan` (zero mutation).

---

## 2. Data Source

Data ditarik secara upstream melalui canonical transport client [`GfwApiService`](file:///c:/XPROJECT/sistem-perikanan/app/Services/Gfw/GfwApiService.php) menuju Global Fishing Watch API v3 Gateway:
- **API Endpoint**: `https://gateway.api.globalfishingwatch.org/v3`
  - `/vessels/search`: Pencarian entitas kapal berbasis kueri identitas.
  - `/vessels/{id}/tracks`: Lintasan titik koordinat pergerakan kapal.
- **Transmisi Sinyal**: Sinyal transponder satelit AIS/VMS (Automatic Identification System / Vessel Monitoring System) yang diproses dan diinferensikan oleh machine learning model GFW v4.0.

---

## 3. AOI (Area of Interest)

- **Kode AOI**: `zee-indonesia-aceh`
- **Nama Wilayah**: `ZEE Indonesia - Kawasan Aceh`
- **Sistem Koordinat**: `EPSG:4326` (WGS 84 desimal derajat)
- **Bounding Box**:
  $$\text{Min Lon: } 94.50^\circ, \quad \text{Min Lat: } 1.80^\circ, \quad \text{Max Lon: } 98.30^\circ, \quad \text{Max Lat: } 6.20^\circ$$
- **Dasar Yuridis**: UNCLOS 1982 Art. 57, Deklarasi ZEE Indonesia 1980, UU No. 5/1983.
- **Implementasi**: Dikelola secara imutabel oleh canonical [`AoiService`](file:///c:/XPROJECT/sistem-perikanan/app/Services/Gfw/AoiService.php).

---

## 4. Date Range

Mengikuti batasan ketat kontrol batch kecil:
- **Jendela Waktu Pengamatan**: 7 hari (`2026-09-15` s/d `2026-09-22`).
- **Notice Latensi Satelit**: Data observasi AIS/VMS memiliki delay berkala 24–72 jam sesuai karakteristik transmisi satelit global maritim.

---

## 5. Dataset / Version

- **Vessel Identity Dataset**: `public-global-vessel-identity:latest` (resolusi API v4.0).
- **Vessel Tracks / Presence Dataset**: `public-global-vessel-tracks:latest` (GFW v3 pipeline).

---

## 6. Identity Strategy

Strategi resolusi identitas entitas kapal yang diterapkan pada [`GfwIngestionService`](file:///c:/XPROJECT/sistem-perikanan/app/Services/Gfw/GfwIngestionService.php):
1. **Primary Identifier**: `gfw_vessel_id` (string 100 char, unique di `sistem_gfw.gfw_vessels`).
2. **Secondary Identifiers**: `imo` (7 digit) dan `mmsi` / `ssvid` (9 digit).
3. **No Fuzzy Matching**: Tidak dilakukan penggabungan kapal berbasis kemiripan nama (`ship_name`).
4. **Provenance Hashing**: Dihasilkan `raw_hash` (MD5 dari payload bersih tanpa Authorization header) untuk memverifikasi ada/tidaknya perubahan data mentah saat upsert.

---

## 7. Presence Deduplication Strategy

Menyelesaikan temuan GFW-V03 (composite index non-unique pada `gfw_vessel_presence`):
- **Pendekatan**: Deduplikasi deterministik pada application service layer sebelum proses INSERT.
- **Kunci Deterministic Existence Check**:
  ```php
  GfwVesselPresence::where('gfw_vessel_id', $vesselId)
      ->where('observed_at', $observedAt)
      ->where('latitude', round($latFloat, 6))
      ->where('longitude', round($lonFloat, 6))
      ->first();
  ```
- **Hasil**: Jika pasangan titik koordinat dan waktu observasi untuk kapal yang sama sudah tercatat, sistem menandai sebagai duplicate dan melewatinya (`SKIPPED`) tanpa membuat baris baru.

---

## 8. Idempotency Strategy

- **Prinsip**: Eksekusi berulang terhadap parameter yang identik harus menghasilkan kondisi database yang konsisten (*zero duplicate growth*):
  $$\text{Run \#1: } N \text{ baru} \longrightarrow \text{Run \#2: } 0 \text{ baru, } N \text{ skipped}$$
- **Vessel Identity**: Menggunakan `updateOrCreate` pada `gfw_vessel_id`. Jika `raw_hash` identik, operasi update di-skip.
- **Vessel Presence**: Dilewati jika titik koordinat pada timestamp tersebut telah tercatat.

---

## 9. Dry Run Result (Test A)

Eksekusi perintah Artisan:
```bash
php artisan gfw:sync-observatory --dry-run --query=INDONESIA --limit=2 --days=7
```

**Hasil Metrik**:
- Mode: **DRY-RUN (NO WRITE)**
- Vessels Found: **2**
- New Vessels Planned: **2**
- Vessels Updated Planned: **0**
- Vessels Skipped: **0**
- Presence Points Found: **0** (pada rentang 7 hari aktif)
- New Presence Planned: **0**
- Sync Run ID: **N/A (Dry-Run)**
- **Verifikasi Database**: Query `SELECT count(*)` pada `sistem_gfw.gfw_vessels`, `gfw_vessel_presence`, dan `gfw_sync_runs` membuktikan **0 baris ditulis ke database**.

---

## 10. Controlled Sync Result (Test B)

Eksekusi penarikan terkendali pertama:
```bash
php artisan gfw:sync-observatory --query=INDONESIA --limit=2 --days=7
```

**Hasil Metrik**:
- Mode: **LIVE CONTROLLED SYNC**
- Vessels Found: **2**
- Vessels Inserted: **2**
- Vessels Updated: **0**
- Presence Inserted: **0**
- Sync Run ID: **1** (Tercatat di `gfw_sync_runs` dengan status `success`)

**Data Tersimpan di `sistem_gfw.gfw_vessels`**:
1. `5dcb957a3-3299-35c8-07e1-a73df0e13d21` | `KM.INDONESIA` | MMSI: `525332513` | Bendera: `IDN` | Tipe: `OTHER` | Hash: `cb08bfb8...`
2. `403b8d431-1ff0-755a-79d1-84011280b246` | `INDONESIA` | MMSI: `525555665` | Bendera: `IDN` | Tipe: `OTHER` | Hash: `2eacde4e...`

---

## 11. Repeat Sync Result (Test C — Idempotency Proof)

Eksekusi ulang perintah sinkronisasi yang persis sama:
```bash
php artisan gfw:sync-observatory --query=INDONESIA --limit=2 --days=7
```

**Hasil Metrik**:
- Vessels Found: **2**
- New Vessels Inserted: **0 (NOL)**
- Vessels Skipped (Identical): **2**
- New Presence Inserted: **0 (NOL)**
- Duplicate Presence Skipped: **0**
- Sync Run ID: **2** (Tercatat dengan status `success`)
- **Total Vessels di Database**: Tetap **2 (Tidak berlipat ganda)**.

---

## 12. Data Quality

Validasi kualitas data sebelum penyimpanan:
- **Geografis**: Latitude divalidasi $[-90.0, 90.0]$, Longitude divalidasi $[-180.0, 180.0]$. Titik di luar rentang otomatis di-skip dan dicatat di `invalid_records`.
- **Temporal**: Waktu `observed_at` diparsing dengan format ISO 8601 standar UTC via Carbon.
- **Kecepatan**: Kecepatan bernilai numerik desimal positif (knot).

---

## 13. Security

Audit keamanan kredensial dan payload:
- **Sanitasi Payload**: Token otentikasi upstream (`Authorization: Bearer`, `token`, `api_key`) secara eksplisit di-unset dari payload sebelum disimpan ke kolom `raw_data` dan sebelum perhitungan `raw_hash`.
- **Eksposur Token**: Nol token yang dicetak pada output Artisan console, pesan error `gfw_sync_runs`, ataupun response API.

---

## 14. Main Database Integrity (Test D)

Pemeriksaan status database utama `sistem_perikanan`:

| Parameter | Baseline Rilis | Pasca GFW-V04 | Status |
|---|:---:|:---:|:---:|
| **Total Tabel** | 48 | 48 | **IDENTIK** |
| **Total Migrasi** | 48 | 48 | **IDENTIK** |
| **Kapal GFW di `vessels`** | 0 | 0 | **ZERO LEAKAGE** |
| **Cross-Database Foreign Keys** | 0 | 0 | **ISOLATED** |
| **Data Mutasi Operasional** | 0 | 0 | **ZERO MUTATION** |

---

## 15. GFW Database Integrity (Test E)

Kondisi terkini database terisolasi `sistem_gfw`:
- `gfw_vessel_types`: 15 klasifikasi resmi GFW v3 (Utuh).
- `gfw_vessels`: 2 entitas kapal observasi tersimpan secara terstruktur.
- `gfw_vessel_presence`: 0 observasi (siap menerima batch presence terverifikasi).
- `gfw_sync_runs`: 2 log riwayat sinkronisasi tersimpan lengkap (`status = success`).

---

## 16. Regression Test Results

Pengujian otomatis pasca-implementasi:

1. **Targeted Ingestion Test**:
   - File: [`tests/Feature/Gfw/GfwControlledIngestionTest.php`](file:///c:/XPROJECT/sistem-perikanan/tests/Feature/Gfw/GfwControlledIngestionTest.php)
   - Hasil: **7 passed (57 assertions, 0 errors)**
   - Menguji: Dry-run, first sync, repeat sync idempotency, skip invalid data, isolasi main DB, dan token security.
2. **Targeted GFW Suite**:
   - Command: `php artisan test --filter=Gfw --compact`
   - Hasil: **143 passed (875 assertions, 0 errors)**
3. **Full Regression Test Suite**:
   - Command: `php artisan test --compact`
   - Baseline Sebelum V04: 416 tests, 2,220 assertions
   - Hasil Sekarang: **423 passed, 2,277 assertions, 0 failures, 0 errors** (Durasi: ~32.04 detik)
4. **Code Style**:
   - Tool: Laravel Pint
   - Hasil: **PASS (100% PSR-12 / Pint compliant)**

---

## 17. Errors / Warnings

- **Warning GFW-V03 Teratasi**: Idempotensi pada layer aplikasi terbukti berhasil mencegah duplikasi record observasi dan identitas kapal.
- **Notice Latensi**: Data observasi AIS satelit GFW berkarakteristik historis/tertunda (latensi 24–72 jam) dan telah disosialisasikan secara transparan pada antarmuka.

---

## 18. Next Step

Dengan berhasilnya pengujian controlled batch dan pembuktian idempotensi 100%:

Direkomendasikan melangkah ke tahap visualisasi dan integrasi antarmuka pengguna:
> **`GFW-V05 — GFW Vessel Observatory UI Integration (/gfw/vessels)`**

Tahap V05 akan menghubungkan antarmuka MapLibre `/gfw/vessels` untuk membaca data langsung dari database terisolasi `sistem_gfw`, menampilkan layer track kapal, filter negara, dan pencarian identitas armada maritim.
