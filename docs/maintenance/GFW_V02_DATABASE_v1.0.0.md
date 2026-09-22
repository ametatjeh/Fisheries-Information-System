# GFW-V02 DATABASE REPORT — ISOLATED DATABASE DESIGN & IMPLEMENTATION

```text
Project                 : Sistem Perikanan Aceh v1.0.0
Stage                   : GFW-V02 ISOLATED DATABASE DESIGN & IMPLEMENTATION
Date                    : 2026-09-22
Main Database           : sistem_perikanan (Port 3307) — MUTATION = 0
GFW Observatory Database: sistem_gfw (Port 3307) — CREATED & ISOLATED
Laravel Version         : 13.32.0
PHP Version             : 8.4.16
Status                  : PASS (EXECUTION COMPLETE)
```

---

## 1. Database Isolation

Pemisahan fisik dan logis antara domain operasional perikanan dan data observasi Global Fishing Watch telah berhasil diimplementasikan 100%:

- **Database Operasional Utama**: `sistem_perikanan`
  - Berstatus **READ ONLY** selama eksekusi GFW-V02.
  - Jumlah tabel awal: 48 $\to$ Jumlah tabel akhir: **48**.
  - Jumlah migrasi awal: 48 $\to$ Jumlah migrasi akhir: **48**.
  - Mutasi skema/data: **0 (Zero Mutation)**.
- **Database Observasi GFW**: `sistem_gfw`
  - Dibuat khusus pada MariaDB host `127.0.0.1:3307` dengan karakter set `utf8mb4` dan kolasi `utf8mb4_unicode_ci`.
  - Berisi tabel terisolasi untuk taksonomi kapal GFW, master identitas kapal GFW, time-series observasi keberadaan (presence), dan audit log sinkronisasi.
- **Pemisahan Konseptual Kapal**:
  $$\text{GFW Vessel} \neq \text{Local Vessel}$$
  Tidak ada tabel `vessels` atau data nelayan lokal di dalam `sistem_gfw`.

---

## 2. Laravel Connection

Konfigurasi koneksi database multi-DB telah ditambahkan pada `config/database.php` dan file environment:

### A. Konfigurasi `config/database.php`
```php
'gfw' => [
    'driver' => env('GFW_DB_CONNECTION', 'mysql'),
    'url' => env('GFW_DB_URL'),
    'host' => env('GFW_DB_HOST', env('DB_HOST', '127.0.0.1')),
    'port' => env('GFW_DB_PORT', env('DB_PORT', '3307')),
    'database' => env('GFW_DB_DATABASE', 'sistem_gfw'),
    'username' => env('GFW_DB_USERNAME', env('DB_USERNAME', 'root')),
    'password' => env('GFW_DB_PASSWORD', env('DB_PASSWORD', '')),
    'unix_socket' => env('GFW_DB_SOCKET', ''),
    'charset' => env('GFW_DB_CHARSET', 'utf8mb4'),
    'collation' => env('GFW_DB_COLLATION', 'utf8mb4_unicode_ci'),
    'prefix' => '',
    'prefix_indexes' => true,
    'strict' => true,
    'engine' => null,
    'options' => extension_loaded('pdo_mysql') ? array_filter([
        Mysql::ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
    ]) : [],
],
```

### B. Environment Variables (`.env` & `.env.example`)
```env
GFW_DB_CONNECTION=mysql
GFW_DB_HOST=127.0.0.1
GFW_DB_PORT=3307
GFW_DB_DATABASE=sistem_gfw
GFW_DB_USERNAME=root
GFW_DB_PASSWORD=
```

Kredensial tersimpan aman di level server-side tanpa hard-coding.

---

## 3. Migration

Strategi migrasi terisolasi diterapkan untuk menjamin `php artisan migrate` standar tidak menjalankan skrip GFW pada database utama:

1. **Direktori Khusus**: Seluruh file migrasi GFW diletakkan di `database/migrations/gfw/`.
2. **Connection Attribute**: Setiap class migrasi menetapkan property:
   ```php
   protected $connection = 'gfw';
   ```
3. **Execution Command**:
   ```bash
   php artisan migrate --database=gfw --path=database/migrations/gfw
   ```
4. **Hasil Eksekusi**:
   - `2026_09_22_000001_create_gfw_vessel_types_table` $\to$ **DONE**
   - `2026_09_22_000002_create_gfw_vessels_table` $\to$ **DONE**
   - `2026_09_22_000003_create_gfw_vessel_presence_table` $\to$ **DONE**
   - `2026_09_22_000004_create_gfw_sync_runs_table` $\to$ **DONE**

---

## 4. Tables

Daftar tabel yang terbentuk pada database `sistem_gfw`:

| Nama Tabel | Tipe | Fungsi & Deskripsi |
|---|:---:|---|
| `gfw_vessel_types` | Master Referensi | Menyimpan referensi klasifikasi tipe kapal resmi Global Fishing Watch (GFW v3). |
| `gfw_vessels` | Master Identitas | Menyimpan identitas kapal yang terdeteksi/teramati oleh GFW di wilayah observasi. |
| `gfw_vessel_presence` | Time-Series | Menyimpan fakta deteksi koordinat spasial dan kecepatan kapal pada AOI per waktu observasi. |
| `gfw_sync_runs` | Audit & Log | Mencatat riwayat dan status setiap proses batch sinkronisasi data upstream GFW API. |
| `migrations` | Sistem | Tabel tracking migrasi internal database `sistem_gfw`. |

---

## 5. Indexes

Strategi indexing dirancang untuk mendukung pencarian cepat pada antarmuka `/gfw/vessels` dan rendering MapLibre:

1. **`gfw_vessel_types`**:
   - `UNIQUE (code)`: Menjamin tidak ada duplikasi kode tipe kapal.
   - `INDEX (is_active)`: Filter aktif/nonaktif pada dropdown UI.
   - `INDEX (parent_type)`: Pengelompokan sub-tipe alat tangkap/kategori.
2. **`gfw_vessels`**:
   - `UNIQUE (gfw_vessel_id)`: Primary external identity key GFW.
   - `INDEX (mmsi)`: Pencarian cepat berbasis nomor MMSI 9-digit.
   - `INDEX (imo)`: Pencarian cepat berbasis registrasi IMO 7-digit.
   - `INDEX (ship_name)` & `INDEX (name)`: Pencarian teks nama kapal.
   - `INDEX (flag)`: Filter negara bendera kapal (ISO 3166-1).
   - `INDEX (vessel_type)`: Filter jenis kapal (Fishing, Cargo, Tanker, dll.).
   - `INDEX (raw_hash)`: Cek idempotensi update data mentah.
3. **`gfw_vessel_presence`**:
   - `INDEX (observed_at)`: Pemilahan rentang tanggal pengamatan (7d, 30d, 90d).
   - `INDEX (gfw_vessel_id)`: Relasi logis ke entitas master kapal.
   - `COMPOSITE INDEX (gfw_vessel_id, observed_at)`: Query urutan track lintasan kapal per periode.
   - `COMPOSITE INDEX (aoi, observed_at)`: Query observasi per wilayah pemantauan.
4. **`gfw_sync_runs`**:
   - `INDEX (aoi)`: Filter audit log per wilayah.
   - `INDEX (status)`: Monitoring proses running/failed/success.

---

## 6. Foreign Keys

Audit integritas relasi foreign key:
- **Foreign Keys ke Database Utama (`sistem_perikanan`)**: **0 (NOL)**.
  Query verifikasi pada `information_schema.KEY_COLUMN_USAGE` membuktikan tidak ada foreign key yang melintasi batas database.
- **Relasi Antar-Tabel GFW**: Dikelola secara logis dan terisolasi internal di dalam `sistem_gfw`.

---

## 7. Vessel Identity

Struktur `gfw_vessels` mencakup seluruh atribut yang dinormalisasi oleh `GfwVesselService`:
- `gfw_vessel_id`: Hash unik global dari engine GFW.
- `name` / `ship_name`: Nama kapal dari AIS dan registri.
- `mmsi`: Nomor transmisi maritim (9 digit).
- `imo`: Nomor registrasi IMO (7 digit).
- `flag`: Kode bendera negara (3 huruf).
- `vessel_type` & `vessel_class`: Kategori operasional kapal.
- `gear_type`: Tipe alat tangkap perikanan (jika tergolong kapal ikan).
- `length_m` & `gross_tonnage` / `tonnage_gt`: Dimensi fisik kapal.
- `engine_power_kw`: Tenaga mesin utama (bila tersedia pada registri).
- `first_seen_at`, `last_seen_at`, `last_updated_at`, `last_synced_at`: Riwayat temporal.
- `raw_hash` & `raw_data`: Metadata payload audit.

---

## 8. Vessel Type Classification

Seeder `GfwVesselTypeSeeder` telah dieksekusi pada koneksi `gfw`, mengisi 15 klasifikasi resmi GFW v3:

### Kategori Utama (Top-Level)
1. `fishing`: Kapal Penangkap Ikan (Fishing Vessel)
2. `cargo`: Kapal Kargo (Cargo Vessel)
3. `tanker`: Kapal Tanker (Tanker Vessel)
4. `passenger`: Kapal Penumpang (Passenger Vessel)
5. `tug`: Kapal Tunda (Tugboat)
6. `service`: Kapal Layanan (Service Vessel)
7. `carrier`: Kapal Pengangkut Hasil Laut (Carrier Vessel)
8. `other`: Lainnya (Other)
9. `unknown`: Tidak Teridentifikasi (Unknown)

### Sub-Tipe Alat Tangkap GFW (Parent: fishing)
10. `trawler`: Pukat Hela (Trawler)
11. `longliner`: Rawai (Longliner)
12. `purse_seine`: Pukat Cincin (Purse Seine)
13. `pole_and_line`: Huhate (Pole and Line)
14. `pot_and_trap`: Perangkap / Bubu (Pot and Trap)
15. `other_fishing`: Perikanan Lainnya (Other Fishing)

*Catatan Keselamatan Data*: Nol kapal dummy atau presence palsu yang dibuat di database produksi/lokal.

---

## 9. Deduplication

Strategi resolusi duplikasi antar-sinkronisasi:
1. **Primary Unique Key**: `gfw_vessel_id` bertindak sebagai identifier utama.
2. **Payload Hashing**: Field `raw_hash` menyimpan nilai MD5 checksum dari payload respons GFW. Jika hash tidak berubah, penulisan disk/database diabaikan.
3. **Upsert Logic**: Menggunakan `updateOrCreate` berbasis `gfw_vessel_id` sehingga pembaruan data kapal tidak menghasilkan baris duplikat baru.

---

## 10. Presence Storage

Observasi pergerakan kapal disimpan pada tabel `gfw_vessel_presence`:
- Menyimpan setiap titik posisi geografis (`latitude`, `longitude`), kecepatan dalam knot (`speed`), arah navigasi (`course`), waktu transmisi (`observed_at`), dan dataset asal (`source_dataset`).
- Pemfilteran data waktu pada antarmuka visualisasi MapLibre membaca tabel ini secara terindeks tinggi tanpa menyentuh tabel operasional perikanan.

---

## 11. Sync History

Tabel `gfw_sync_runs` menyediakan audit trail lengkap terhadap interaksi API GFW:
- Mencatat rentang tanggal yang ditarik (`date_from`, `date_to`).
- Mencatat kuantitas data mentah yang ditemukan (`records_found`) dan yang berhasil disimpan (`records_saved`).
- Mencatat status proses (`running`, `success`, `partial`, `failed`) dan rincian pesan error (`error_message`) jika terjadi limitasi kuota atau gangguan jaringan.

---

## 12. Security

Audit keamanan koneksi database dan kredensial:
- Variabel konfigurasi koneksi (`GFW_DB_*`) sepenuhnya berada di sisi backend (`.env` dan `config/database.php`).
- Tidak ada password atau nama koneksi internal yang terekspos ke frontend, response API, atau script MapLibre.
- Seluruh query menggunakan parameterized binding bawaan Eloquent/PDO MySQL untuk mitigasi SQL Injection.

---

## 13. Tests

Pengujian isolasi database diimplementasikan pada [`tests/Feature/Gfw/GfwDatabaseIsolationTest.php`](file:///c:/XPROJECT/sistem-perikanan/tests/Feature/Gfw/GfwDatabaseIsolationTest.php):

1. `test_gfw_models_use_dedicated_gfw_connection`:
   - Membuktikan model `GfwVessel`, `GfwVesselType`, `GfwVesselPresence`, dan `GfwSyncRun` menggunakan koneksi `'gfw'`.
2. `test_main_domain_models_use_default_fisheries_connection`:
   - Membuktikan model `Vessel`, `Fisher`, `FishCatch`, dan `Landing` menggunakan koneksi default perikanan dan tidak menyentuh `'gfw'`.
3. `test_connection_configuration_is_strictly_separated`:
   - Membuktikan pemisahan konfigurasi nama database (`sistem_gfw` vs `sistem_perikanan`).
4. `test_critical_isolation_between_gfw_and_fisheries_databases`:
   - Membuktikan tabel `vessels`, `fishermen`, dan `catches` **TIDAK ADA** di `sistem_gfw`.
   - Membuktikan tabel `gfw_vessels`, `gfw_vessel_types`, `gfw_vessel_presence`, dan `gfw_sync_runs` **ADA** di `sistem_gfw`.
   - Membuktikan tabel baru `gfw_vessel_types`, `gfw_vessel_presence`, dan `gfw_sync_runs` **TIDAK ADA** di `sistem_perikanan`.
   - Membuktikan **0 Foreign Keys** lintas database.

Hasil eksekusi: **4 passed, 23 assertions, 0 failures**.

---

## 14. Main Database Integrity

Verifikasi sebelum vs setelah implementasi:

| Indikator Integritas | Sebelum Migrasi | Sesudah Migrasi | Status |
|---|:---:|:---:|:---:|
| **Nama Database Utama** | `sistem_perikanan` | `sistem_perikanan` | **IDENTIK** |
| **Total Tabel** | 48 | 48 | **IDENTIK** |
| **Total Migrasi Tercatat** | 48 | 48 | **IDENTIK** |
| **Tabel Domain Perikanan** | Utuh | Utuh | **PRESERVED** |
| **Mutasi Data Produksi** | 0 | 0 | **ZERO MUTATION** |

---

## 15. Existing GFW Regression

Verifikasi kestabilan sistem secara menyeluruh:

1. **Targeted GFW Suite**:
   - Command: `php artisan test --filter=Gfw --compact`
   - Hasil: **136 passed (136 tests, 818 assertions)**
2. **Full Regression Suite**:
   - Command: `php artisan test --compact`
   - Baseline v1.0.0: 412 tests, 2,197 assertions
   - Hasil Sekarang: **416 passed, 2,220 assertions, 0 failures, 0 errors**
   - Durasi: ~40.07 detik
3. **Endpoint Kontrak Existing**:
   - `/api/gfw/test` $\to$ **PRESERVED**
   - `/api/gfw/aoi/zee-indonesia-aceh` $\to$ **PRESERVED**
   - `/api/gfw/events/zee-indonesia-aceh` $\to$ **PRESERVED**
   - `/gfw/monitoring` $\to$ **PRESERVED**
   - `/gfw/vessels` $\to$ **PRESERVED**

---

## 16. Risks

1. **Sinkronisasi Multi-Database**:
   - Operasi transaksi database antara `sistem_perikanan` dan `sistem_gfw` berada pada koneksi terpisah dan tidak dapat digabung ke dalam satu transaksi atomik MySQL standar. Hal ini adalah konsekuensi desain isolasi dan merupakan pola arsitektur yang benar.
2. **Penyimpanan Observasi Volume Tinggi**:
   - Data observasi koordinat time-series (`gfw_vessel_presence`) berpotensi tumbuh cepat pada observasi jangka panjang, sehingga membutuhkan kebijakan retensi (partitioning/archiving) pada tahap pengembangan lanjutan.

---

## 17. Recommended Next Stage

Berdasarkan pencapaian seluruh kriteria checkpoint GFW-V02 dengan hasil **PASS**:

Direkomendasikan secara resmi untuk melangkah ke tahap implementasi logika data:
> **`GFW-V03 — GFW Vessel Identity & Type Ingestion`**

Tahap V03 akan memfokuskan pada:
1. Pemanfaatan `GfwVesselService` canonical untuk mengalirkan identitas kapal teramati ke `sistem_gfw.gfw_vessels`.
2. Pengisian otomatis mapping tipe kapal terhadap taksonomi `gfw_vessel_types`.
3. Perekaman batch audit sinkronisasi pada `gfw_sync_runs`.
