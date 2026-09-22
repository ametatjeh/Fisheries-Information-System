# GFW-V01 AUDIT REPORT — GFW VESSEL OBSERVATORY

```text
Project           : Sistem Perikanan Aceh v1.0.0
Audit Type        : GFW-V01 READ-ONLY AUDIT & ARCHITECTURE
Baseline Commit   : 043f5fb
Laravel Version   : 13.32.0
PHP Version       : 8.4.16
Database          : sistem_perikanan (Port 3307)
Date of Audit     : 2026-09-22
Audit Status      : PASS (READ-ONLY AUDIT COMPLETE)
```

---

## 1. Executive Summary

Audit arsitektur dan sistem **GFW-V01** dilaksanakan secara strictly **READ-ONLY** untuk mempersiapkan pengembangan fitur **GFW Vessel Observatory — Aceh & Perairan Sekitarnya** pada endpoint `/gfw/vessels`.

Prinsip dasar yang diverifikasi dan ditegakkan selama audit ini:
1. **Database Utama (`sistem_perikanan`) Terisolasi Penuh**: Tabel domain operasional perikanan (`vessels`, `fishermen`, `fishing_trips`, `fishing_efforts`, `catches`, `landings`, `landing_items`, `species`, `fishing_gears`, `statistics`, `sampling`, `validation`) tidak mengalami modifikasi struktur, skema, maupun data.
2. **GFW Vessel $\ne$ Local Vessel**: Data kapal yang diobservasi GFW adalah entitas eksternal maritim global dan tidak disatukan secara langsung atau berelasi foreign key ke tabel lokal `vessels`.
3. **Pemisahan Database GFW (`sistem_gfw`)**: Telah diidentifikasi bahwa arsitektur target yang aman membutuhkan isolasi database khusus (`sistem_gfw`). Tidak ada migrasi, model baru, atau database yang dibuat pada tahap V01 ini.
4. **Canonical GFW Service Reusability**: Seluruh komunikasi upstream ke Global Fishing Watch API v3 berjalan melalui gateway internal Laravel yang aman. Service canonical yang telah ada (`GfwApiService`, `GfwVesselService`, `GfwActivityService`) direkomendasikan untuk digunakan kembali (reuse) tanpa duplikasi.
5. **Zero Token Exposure**: Kredensial API GFW (`GFW_API_TOKEN`) tersimpan secara eksklusif pada environment server (`.env`) dan tidak terekspos ke Blade, JavaScript, browser, response API, maupun git repository.
6. **Regression Integrity**: Seluruh suite pengujian aplikasi (412 tests, 2,197 assertions) lulus 100% tanpa kegagalan (`0 failures, 0 errors`).

---

## 2. Current GFW Architecture

Arsitektur komunikasi data GFW yang aktif saat ini mengikuti pola **Secure Server-Side Gateway Proxy**:

```text
┌─────────────────────────────────────────────────────────────┐
│                   Web Browser / Client UI                   │
│   (MapLibre GL JS v4.7.1 / Leaflet v1.9.4 / UI Controller)   │
└──────────────────────────────┬──────────────────────────────┘
                               │ HTTPS / JSON API Internal
                               │ (Cookie Auth / CSRF / Rate-Limited)
                               ▼
┌─────────────────────────────────────────────────────────────┐
│               Laravel Application Core                      │
│                                                             │
│   Routes       : /api/gfw/*, /gfw/vessels, /gfw/monitoring │
│   Middleware   : web, auth:web, throttle:gfw-api (30/min)  │
│   Controllers  : GfwGatewayController                      │
│                  GfwVesselMonitoringController             │
│                  GfwMonitoringController                    │
└──────────────────────────────┬──────────────────────────────┘
                               │ Internal Service Layer Calls
                               ▼
┌─────────────────────────────────────────────────────────────┐
│               GFW Service Layer                             │
│                                                             │
│   Transport    : GfwApiService (Retry, Backoff, Timeout)    │
│   Domain Logic : GfwVesselService (Identity, Search)        │
│                  GfwActivityService (Tracks, Presence)      │
│                  GfwEventService (Events, Encounters)       │
│                  AoiService (ZEE Aceh GeoJSON)              │
│                  GfwRegionService (Spatial Boundaries)      │
│   Cache Layer  : Laravel Cache (Redis / File, TTL 1h)       │
└──────────────────────────────┬──────────────────────────────┘
                               │ Server-to-Server HTTPS
                               │ Authorization: Bearer {GFW_API_TOKEN}
                               ▼
┌─────────────────────────────────────────────────────────────┐
│          Global Fishing Watch (GFW) API v3 Gateway          │
│                                                             │
│   https://gateway.api.globalfishingwatch.org/v3/            │
│   - /vessels/search                                         │
│   - /vessels/{id}                                           │
│   - /events                                                 │
│   - /4wings (raster / vector tiles)                         │
└─────────────────────────────────────────────────────────────┘
```

**Karakteristik Kunci**:
- **Tidak ada panggilan langsung** dari browser client ke endpoint `gateway.api.globalfishingwatch.org`.
- Semua request melalui internal rate-limiting (`throttle:gfw-api` dibatasi 30 request/menit per IP).
- Payload eksternal dinormalisasi menjadi response JSON terstruktur (`GfwApiResponse`) sebelum disajikan ke frontend.

---

## 3. Existing GFW Services

Audit terhadap direktori `app/Services/` dan `app/Services/Gfw/` mengidentifikasi komponen service sebagai berikut:

| Service Name | Path File | Peran & Tanggung Jawab | Status Audit |
|---|---|---|:---:|
| `GfwApiService` | `app/Services/Gfw/GfwApiService.php` | Canonical HTTP client untuk GFW API v3. Menangani otentikasi Bearer token, timeout (30s), retry logic (3x backoff), error normalization, dan response wrapping. | **CANONICAL CLIENT** (Wajib Reuse) |
| `GfwVesselService` | `app/Services/Gfw/GfwVesselService.php` | Service pencarian kapal, normalisasi profil kapal, parsing dataset identity, integrasi cache 1 jam. | **CANONICAL VESSEL** (Wajib Reuse) |
| `GfwActivityService` | `app/Services/Gfw/GfwActivityService.php` | Mengambil data track/lintasan koordinat dan riwayat posisi kapal dari dataset activity/tracks. | **CANONICAL TRACKS** (Wajib Reuse) |
| `GfwEventService` | `app/Services/Gfw/GfwEventService.php` | Menangani query event encounter, loitering, dan fishing di perairan tertentu. | **CANONICAL EVENTS** (Preserved) |
| `AoiService` | `app/Services/Gfw/AoiService.php` | Menyediakan definisi geometri poligon GeoJSON ZEE Indonesia — Kawasan Aceh. | **CANONICAL AOI** (Preserved) |
| `GfwRegionService` | `app/Services/Gfw/GfwRegionService.php` | Validasi batas koordinat (bounding box) dan kalkulasi spasial wilayah pengamatan. | **CANONICAL REGION** (Preserved) |
| `GFWService` | `app/Services/GFWService.php` | Legacy wrapper/facade yang menyediakan jembatan kompatibilitas awal. | **LEGACY FACADE** (Preserved) |

**Keputusan Arsitektur**:
- `App\Services\Gfw\GfwApiService` adalah **single canonical transport client**.
- `App\Services\Gfw\GfwVesselService` dan `App\Services\Gfw\GfwActivityService` adalah **canonical domain services**.
- **DILARANG** membuat duplicate service baru (misalnya `GfwVesselObservatoryService`) yang mengulang implementasi HTTP client atau normalisasi identitas kapal.

---

## 4. Existing GFW API Endpoints

Aplikasi mengekspos endpoint internal pada prefix `/api/gfw/` melalui `App\Http\Controllers\Gfw\GfwGatewayController`:

```text
GET  /api/gfw/test                         -> GfwGatewayController@testConnection
GET  /api/gfw/aoi/zee-indonesia-aceh       -> GfwGatewayController@getZeeAcehAoi
GET  /api/gfw/events/zee-indonesia-aceh    -> GfwGatewayController@getEvents
GET  /api/gfw/vessels/search               -> GfwGatewayController@searchVessels
GET  /api/gfw/vessels/{id}                 -> GfwGatewayController@getVessel
GET  /api/gfw/vessels/{id}/tracks          -> GfwGatewayController@getVesselTracks
GET  /api/gfw/vessels/{id}/activity        -> GfwGatewayController@getVesselActivity
```

**Evaluasi Teknis**:
1. **HTTP Method**: Seluruh endpoint pembacaan menggunakan `GET`.
2. **Authentication**: Terlindungi oleh session middleware web dan autentikasi user.
3. **Throttle**: Dilindungi middleware `throttle:gfw-api` (30 requests / menit).
4. **Caching**: Respons disimpan di cache menggunakan key berbasis parameter hash MD5 dengan default TTL 3,600 detik (1 jam).
5. **Error Handling**: Menghasilkan format JSON standar `{ "success": false, "message": "...", "code": ... }` tanpa membocorkan stack trace, token, atau exception internal.
6. **Date Range Constraint**: Parameter tanggal dibatasi maksimum rentang tertentu untuk mencegah kuota GFW terlampaui.

---

## 5. Existing /gfw/vessels Implementation

Halaman `/gfw/vessels` telah diimplementasikan sebagai platform visualisasi monitoring kapal:

- **Controller**: `App\Http\Controllers\Gfw\GfwVesselMonitoringController@index`
- **Route**: `GET /gfw/vessels` (Name: `gfw.vessels`)
- **Middleware**: `['web', 'auth']`
- **View Template**: `resources/views/gfw/vessels.blade.php`
- **Map Library**: **MapLibre GL JS v4.7.1** (`https://unpkg.com/maplibre-gl@4.7.1/dist/maplibre-gl.js`)
- **Komponen Tampilan**:
  - **Sidebar Filter**: Pencarian query (Nama Kapal, MMSI, IMO), Flag negara, Vessel Type (Semua, Fishing, Cargo, Tanker, dll.), Rentang tanggal (7 hari, 30 hari, 90 hari, kustom).
  - **Daftar Kapal (Vessel List)**: Menampilkan nama kapal, MMSI, bendera, tipe kapal, kecepatan terakhir, status update, dan navigasi pagination.
  - **Peta Interaktif MapLibre**:
    - Layer Polygon batas ZEE Indonesia — Aceh (warna cyan/biru laut dengan garis tepi tegas).
    - Marker posisi kapal dengan popup detail (kecepatan, koordinat, arah haluan, asal bendera).
    - Polyline lintasan track navigasi kapal dengan gradient waktu/kecepatan.
  - **Status Penanganan**: Loading indicator visual, banner error saat API upstream mengalami limit/timeout, dan empty state ramah pengguna.
- **Kesimpulan Status**: Halaman `/gfw/vessels` **sudah ada dan memiliki arsitektur modern (MapLibre GL JS)** yang siap diperluas menjadi GFW Vessel Observatory penuh pada tahap mendatang tanpa perlu membangun UI dari nol.

---

## 6. AOI (Area of Interest) Audit

- **Identifier**: `zee-indonesia-aceh`
- **Konsep Wilayah**: Zona Ekonomi Eksklusif (ZEE) Indonesia di perairan Aceh, mencakup Selat Malaka bagian utara, Laut Andaman, dan Samudera Hindia barat Aceh.
- **Sumber Data & Implementasi**: Didefinisikan secara programatis di `App\Services\Gfw\AoiService::getZeeAcehPolygon()` dan `config/gfw.php`.
- **Format Spasial**: GeoJSON `Polygon` dengan 8 vertex koordinat:
  ```json
  {
    "type": "Polygon",
    "coordinates": [[
      [93.0, 7.5],
      [97.0, 7.5],
      [99.5, 4.5],
      [98.5, 2.0],
      [96.0, 1.5],
      [94.0, 2.5],
      [92.5, 5.0],
      [93.0, 7.5]
    ]]
  }
  ```
- **Sistem Koordinat (CRS/SRID)**: `EPSG:4326` (WGS 84 lat/lng).
- **Penyimpanan**: Disimpan di level service dan konfigurasi file, bukan sebagai entitas tabel database yang dapat berubah, sehingga menjamin imutabilitas (tidak terancam corrupt/teredit tidak sengaja).
- **Pemanfaatan**:
  - Dikirimkan ke GFW API sebagai geometry filter atau bounding box (`bbox`).
  - Digunakan di `/gfw/vessels` untuk merender layer poligon pengawasan di atas peta MapLibre.

---

## 7. Vessel Identity Capability

Analisis kapabilitas API GFW (`public-global-vessel-identity:latest`):

| Atribut Identitas | Ketersediaan GFW API | Dukungan Normalisasi | Catatan |
|---|:---:|:---:|---|
| **GFW Vessel ID** | TERSEDIA | Ya (`id`) | ID unik global hash internal GFW. |
| **MMSI** | TERSEDIA | Ya (`mmsi`) | Maritime Mobile Service Identity (9 digit). |
| **IMO** | TERSEDIA | Ya (`imo`) | Nomor identifikasi kapal IMO (7 digit, untuk kapal $\ge$100 GT). |
| **Ship Name** | TERSEDIA | Ya (`shipname` / `name`) | Nama kapal yang dipancarkan transponder AIS. |
| **Flag** | TERSEDIA | Ya (`flag`) | Kode negara bendera kapal (ISO 3166-1 alpha-2/alpha-3). |
| **Vessel Type** | TERSEDIA | Ya (`vesselType` / `shiptype`) | Kategori tipe kapal berdasarkan transmisi AIS & registri. |
| **Vessel Class** | TERSEDIA | Ya (`vesselClass`) | Klasifikasi detail armada. |
| **Gear Type** | TERSEDIA | Ya (`geartype`) | Tipe alat tangkap (khusus kapal perikanan). |
| **Length (m)** | TERSEDIA | Ya (`lengthM` / `length`) | Panjang kapal terdaftar. |
| **Tonnage (GT)** | TERSEDIA | Ya (`tonnageGt` / `tonnage`) | Gross Tonnage kapal. |
| **Engine Power (kW)** | TERSEDIA | Ya (`enginePowerKw`) | Daya mesin terdaftar (jika tersedia di registry). |
| **Registry Source** | TERSEDIA | Ya (`registryInfo`) | Sumber registri resmi (IUU list, RFMO, nasional). |

---

## 8. Vessel Presence Capability

Analisis kapabilitas API GFW Activity & Tracks (`public-global-vessel-tracks:latest`):

| Atribut Observasi | Ketersediaan GFW API | Satuan / Format | Catatan |
|---|:---:|:---:|---|
| **Timestamp Observasi** | TERSEDIA | ISO 8601 UTC | Waktu transmisi sinyal AIS. |
| **Latitude** | TERSEDIA | Desimal derajat (WGS 84) | Presisi tinggi. |
| **Longitude** | TERSEDIA | Desimal derajat (WGS 84) | Presisi tinggi. |
| **Speed over Ground (SOG)** | TERSEDIA | Knots | Kecepatan navigasi saat observasi. |
| **Course over Ground (COG)** | TERSEDIA | Derajat ($0^\circ - 360^\circ$) | Arah haluan kapal. |
| **Distance from Shore** | TERSEDIA | Meter / Kilometer | Jarak kalkulasi dari garis pantai terdekat. |
| **Distance from Port** | TERSEDIA | Meter / Kilometer | Jarak dari pelabuhan terdekat. |
| **Source Dataset** | TERSEDIA | String | Identitas dataset upstream GFW. |

---

## 9. Vessel Type Classification

Sesuai arahan, klasifikasi kapal **WAJIB mengikuti taksonomi asli GFW API v3** dan tidak mengasumsikan kategori lokal:

```text
GFW Vessel Classification Hierarchy:
├── Non-Fishing
│   ├── Cargo (Container, General Cargo, Bulk Carrier)
│   ├── Tanker (Oil Tanker, Chemical Tanker, Gas Carrier)
│   ├── Passenger (Cruise Ship, Ferry)
│   ├── Tug (Tugboat, Pusher)
│   ├── Service (Dredger, Research, Offshore Supply, Patrol)
│   └── Carrier (Reefer, Livestock)
├── Fishing
│   ├── Trawler (Bottom Trawl, Pelagic Trawl)
│   ├── Longliner (Drifting Longline, Set Longline)
│   ├── Purse Seine (Tuna Purse Seine, Small Pelagic)
│   ├── Pole and Line
│   ├── Pot and Trap
│   └── Other Fishing
└── Unspecified / Unknown
    ├── Other
    └── Unknown
```

**Aturan Implementasi**:
- Kategori utama dinormalisasi langsung dari attribute `vesselType` atau `shiptype` respons API GFW.
- Tidak dilakukan pemetaan paksa (forced mapping) ke klasifikasi kapal perikanan daerah Aceh.

---

## 10. Database Isolation Proposal

Saat ini di database `sistem_perikanan` terdapat tabel hasil eksplorasi tahap awal (`gfw_vessels`, `gfw_vessel_activities`, `gfw_events`). 

Untuk **GFW Vessel Observatory skala penuh**, direkomendasikan arsitektur **Isolasi Database Fisik**:

```text
┌──────────────────────────────────────┐     ┌──────────────────────────────────────┐
│     DATABASE: sistem_perikanan       │     │        DATABASE: sistem_gfw          │
│     (Core Fisheries Domain)          │     │     (GFW Vessel Observatory)         │
├──────────────────────────────────────┤     ├──────────────────────────────────────┤
│ - fishermen                          │     │ - gfw_vessel_types                   │
│ - vessels (Local Registry)           │     │ - gfw_vessels (Observatory Registry) │
│ - fishing_trips                      │     │ - gfw_vessel_presence (Spatial Obs)  │
│ - fishing_efforts                    │     │ - gfw_sync_runs (Sync Audit Logs)    │
│ - catches                            │     └──────────────────────────────────────┘
│ - landings                           │                         ▲
│ - landing_items                      │                         │ Strict Separation
│ - species (ASFIS)                    │                         │ NO Foreign Keys
│ - fishing_gears (ISSCFG)             │                         │ Independent Connection
│ - statistics / sampling              │     ┌───────────────────┴──────────────────┐
└──────────────────────────────────────┘     │   Laravel Multi-DB Configuration     │
                                             │   config/database.php: 'gfw'         │
                                             └──────────────────────────────────────┘
```

**Rekomendasi Konfigurasi Multi-DB (Untuk Tahap V02)**:
```php
'connections' => [
    'mysql' => [
        'database' => env('DB_DATABASE', 'sistem_perikanan'),
        // default connection
    ],
    'gfw' => [
        'driver' => 'mysql',
        'host' => env('DB_GFW_HOST', env('DB_HOST', '127.0.0.1')),
        'port' => env('DB_GFW_PORT', env('DB_PORT', '3307')),
        'database' => env('DB_GFW_DATABASE', 'sistem_gfw'),
        'username' => env('DB_GFW_USERNAME', env('DB_USERNAME')),
        'password' => env('DB_GFW_PASSWORD', env('DB_PASSWORD')),
        'charset' => 'utf8mb4',
        'collation' => 'utf8mb4_unicode_ci',
        'prefix' => '',
        'strict' => true,
    ],
],
```

---

## 11. Proposed Data Model (Konseptual V02)

Desain konseptual skema untuk database `sistem_gfw` (tidak diimplementasikan pada V01):

### 1. `gfw_vessel_types`
Tabel referensi taksonomi tipe kapal resmi GFW.
- `id`: BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
- `code`: VARCHAR(50) UNIQUE (contoh: `fishing`, `cargo`, `tanker`, `trawler`)
- `name`: VARCHAR(100)
- `category`: VARCHAR(50) (`fishing`, `merchant`, `service`, `other`)
- `source_dataset`: VARCHAR(100) (versi dataset GFW)
- `is_active`: BOOLEAN DEFAULT TRUE
- `created_at`, `updated_at`: TIMESTAMP

### 2. `gfw_vessels`
Tabel master entitas kapal yang teramati GFW di perairan Aceh & sekitarnya.
- `id`: BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
- `gfw_vessel_id`: VARCHAR(100) UNIQUE (Primary External ID GFW)
- `mmsi`: VARCHAR(15) INDEX NULLABLE
- `imo`: VARCHAR(15) INDEX NULLABLE
- `ship_name`: VARCHAR(150) INDEX NULLABLE
- `flag`: VARCHAR(3) INDEX NULLABLE (ISO country code)
- `vessel_type`: VARCHAR(50) INDEX
- `vessel_class`: VARCHAR(50) NULLABLE
- `gear_type`: VARCHAR(50) NULLABLE
- `length_m`: DECIMAL(8, 2) NULLABLE
- `gross_tonnage`: DECIMAL(10, 2) NULLABLE
- `engine_power_kw`: DECIMAL(10, 2) NULLABLE
- `first_seen`: TIMESTAMP NULLABLE
- `last_seen`: TIMESTAMP NULLABLE
- `raw_hash`: CHAR(32) NULLABLE (MD5 checksum data mentah)
- `created_at`, `updated_at`: TIMESTAMP

### 3. `gfw_vessel_presence`
Tabel time-series posisi observasi kapal di AOI.
- `id`: BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
- `gfw_vessel_id`: VARCHAR(100) INDEX (Relasi logis ke `gfw_vessels`)
- `aoi_code`: VARCHAR(50) DEFAULT 'zee-indonesia-aceh'
- `observed_at`: TIMESTAMP INDEX
- `latitude`: DECIMAL(10, 7)
- `longitude`: DECIMAL(10, 7)
- `speed_knots`: DECIMAL(5, 2) NULLABLE
- `course_deg`: DECIMAL(5, 2) NULLABLE
- `vessel_type`: VARCHAR(50) NULLABLE
- `flag`: VARCHAR(3) NULLABLE
- `source_dataset`: VARCHAR(100)
- `created_at`: TIMESTAMP
- *Indexes*: `INDEX (observed_at, gfw_vessel_id)`, `INDEX (latitude, longitude)`

### 4. `gfw_sync_runs`
Tabel riwayat proses sinkronisasi background.
- `id`: BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
- `aoi_code`: VARCHAR(50)
- `date_from`: DATE
- `date_to`: DATE
- `dataset`: VARCHAR(100)
- `records_found`: INT UNSIGNED DEFAULT 0
- `records_saved`: INT UNSIGNED DEFAULT 0
- `status`: ENUM('pending', 'running', 'completed', 'failed')
- `error_message`: TEXT NULLABLE
- `started_at`: TIMESTAMP NULLABLE
- `finished_at`: TIMESTAMP NULLABLE
- `created_at`, `updated_at`: TIMESTAMP

---

## 12. Identity & Deduplication Strategy

Strategi resolusi identitas entitas kapal:

1. **Primary Identity Candidate: `gfw_vessel_id`**
   - Merupakan identifier unik definitif yang dihasilkan oleh engine identity GFW v3 (menggabungkan sinyal AIS multi-sumber dan registri resmi).
   - Dijadikan constraint `UNIQUE` pada tabel `gfw_vessels`.
2. **Fallback Identity Candidates: `mmsi` & `imo`**
   - Bila query GFW menghasilkan kapal dengan `gfw_vessel_id` berbeda namun `mmsi` sama pada rentang waktu berdekatan (kasus pergantian transmiter atau identity split GFW), sistem mencatat relasi identity alias.
3. **Scenario Duplikasi & Penanganan**:
   - **Kapal Berganti Bendera/Nama**: Pembaruan record dilakukan secara `upsert` pada `gfw_vessels` berdasarkan `gfw_vessel_id`. Tanggal `last_seen` dan atribut terkini diperbarui tanpa menduplikasi baris.
   - **Observasi Posisi Berulang**: Pada `gfw_vessel_presence`, kombinasi `(gfw_vessel_id, observed_at)` diproteksi dengan unique composite index atau pengecekan hash koordinat sebelum insert.

---

## 13. Sync Strategy

Strategi sinkronisasi berkala dari GFW API:

1. **Incremental Bounded Windows**:
   - Sinkronisasi tidak dilakukan untuk rentang waktu tak terbatas.
   - Sinkronisasi harian/mingguan dipecah menjadi batch berdurasi maksimal 7–14 hari untuk mencegah HTTP 504 Gateway Timeout dan pemborosan kuota API.
2. **Idempotency & Checksum**:
   - Setiap payload identitas dihitung hash MD5 (`raw_hash`). Jika hash identik dengan database, operasi UPDATE diabaikan.
3. **Background Job Isolation**:
   - Sinkronisasi dieksekusi melalui queued job (`GfwSyncVesselsJob`) atau scheduled command, tidak pernah dijalankan secara sinkron pada HTTP request user.

---

## 14. Storage Strategy

Rekomendasi arsitektur penyimpanan bertingkat (Tiered Storage):

```text
┌─────────────────────────────────────────────────────────────┐
│ 1. Relational Database (sistem_gfw)                         │
│    - Menyimpan data terstruktur untuk query UI & peta       │
│    - Fast search: index pada mmsi, imo, flag, vessel_type   │
└──────────────────────────────┬──────────────────────────────┘
                               │
┌──────────────────────────────┴──────────────────────────────┐
│ 2. Raw JSON Storage (Local Filesystem / S3)                 │
│    - storage/app/gfw_raw/YYYY/MM/DD/{gfw_vessel_id}.json    │
│    - Menyimpan respons mentah API GFW untuk audit           │
│    - Retention policy: 90 hari, kemudian gzip/archive       │
└──────────────────────────────┬──────────────────────────────┘
                               │
┌──────────────────────────────┴──────────────────────────────┐
│ 3. Fast In-Memory Cache (Redis / Cache Facade)              │
│    - TTL: 3,600 detik (1 jam)                               │
│    - Cache layer GeoJSON ZEE & filter aktif                 │
└─────────────────────────────────────────────────────────────┘
```

---

## 15. Performance Considerations

Evaluasi performa berdasarkan volume observasi:

- **100 – 1,000 Kapal**:
  - Penanganan tabel MySQL standar dan MapLibre GeoJSON layer berjalan instan (<100ms render).
- **10,000 Kapal**:
  - Wajib menerapkan pagination (25 baris per halaman) pada sidebar list kapal.
  - Peta menggunakan vector source clustering (`cluster: true` pada MapLibre) untuk mencegah lagging DOM di browser.
- **100,000+ Titik Observasi (Presence Points)**:
  - Query track koordinat dibatasi oleh parameter `date_from` dan `date_to`.
  - Menggunakan composite index `(gfw_vessel_id, observed_at)`.
  - Viewport-based bbox clipping: API hanya mengembalikan titik koordinat yang berada dalam bounding box pandangan kamera peta user.

---

## 16. Security Audit

Hasil verifikasi keamanan integrasi GFW:

1. **Token Protection**:
   - Kredensial `GFW_API_TOKEN` hanya dipanggil via `config('gfw.token')` atau `config('services.gfw.token')`.
   - Tidak ada token yang dicetak pada Blade view (`vessels.blade.php`), file JavaScript, atau response header.
2. **Network Isolation**:
   - Panggilan keluar hanya berasal dari server Laravel menggunakan cURL HTTP Client resmi dengan verifikasi SSL aktif.
3. **Endpoint Defense**:
   - Middleware `throttle:gfw-api` aktif (30 request/menit).
   - Autentikasi sesi aktif untuk endpoint `/gfw/vessels`.
4. **Data Sanitization**:
   - Parameter pencarian (`query`, `date_from`, `date_to`, `vessel_type`, `flag`) divalidasi dengan regex dan date format parser sebelum diteruskan ke GFW upstream.

---

## 17. Fisheries Database Isolation Audit

Verifikasi menyeluruh terhadap isolasi domain perikanan:

- **Layanan Statistik Utama**:
  - `AdvancedStatisticService`: CPUE formula $CPUE = \frac{\text{Catch}}{\text{Effort}}$ tidak terpengaruh data GFW.
  - `MonthlyProductionService`: Rekonsiliasi produksi bulanan murni bersumber dari `landing_items` lokal.
  - `CatchEstimationService`: Raising factor & sampling fraction murni berbasis data operasional TPI lokal.
  - `FisheriesValidationEngineService`: Rule validasi biologi dan operasional tidak menyentuh data kapal GFW.
- **Status Database**:
  - Nol tabel perikanan lokal yang ditambah relasi atau foreign key ke entitas GFW.
  - Data kapal GFW berstatus murni sebagai **external observational data**.

---

## 18. Existing Regression Test Result

Hasil pengujian otomatis (*read-only regression tests*) pada baseline:

1. **Targeted GFW Tests**:
   - Command: `php artisan test --filter=Gfw --compact`
   - Hasil: **132 passed (132 tests, 795 assertions)**
   - Durasi: ~3.73 detik
2. **Full Regression Test Suite**:
   - Command: `php artisan test --compact`
   - Hasil: **412 passed (412 tests, 2,197 assertions)**
   - Durasi: ~60.15 detik
   - Kegagalan: **0 failures, 0 errors**

Hasil ini membuktikan bahwa seluruh fungsionalitas sistem (perikanan, GIS, statistik, pelaporan, autentikasi, validasi, dan GFW existing) tetap berada dalam integritas 100% sempurna.

---

## 19. Git Status

Pemeriksaan status repository Git:
- **Branch Aktif**: `main`
- **Commit Terakhir**: `043f5fb` (`fix(a11y): add accessible names, labels, aria-labels and titles to select and input elements (axe select-name)`)
- **Working Tree Integrity**: Tidak ada commit, reset, stash, ataupun perubahan kode aplikasi yang dilakukan pada tahap V01.

---

## 20. Risks / Limitations

1. **Keterbatasan Cakupan AIS**:
   - Data GFW berbasis transmisi AIS/VMS satelit dan terestrial. Kapal nelayan tradisional lokal berukuran kecil (<5 GT) umumnya tidak memasang transponder AIS aktif, sehingga tidak muncul pada observasi GFW.
   - Definisi operasional yang wajib digunakan pada UI adalah: *"Kapal yang terdeteksi/teramati oleh sumber data GFW/AIS di dalam AOI selama periode tertentu"*, bukan klaim absolut *"Seluruh kapal yang secara fisik melintas di perairan Aceh"*.
2. **Batasan Kuota API GFW**:
   - Query data temporal panjang dapat memicu rate limit atau pembatasan kuota upstream GFW API, sehingga sinkronisasi batch terjadwal dengan window terukur mutlak diperlukan.

---

## 21. Recommended Next Stage

Berdasarkan hasil audit menyeluruh yang berstatus **PASS**:

Direkomendasikan secara resmi untuk melangkah ke tahap berikutnya:
> **`GFW-V02 — ISOLATED DATABASE DESIGN & IMPLEMENTATION PLAN`**

Tahap V02 akan merumuskan:
1. Skrip pembentukan database terpisah `sistem_gfw`.
2. Migrasi terisolasi yang dijalankan dengan koneksi `--database=gfw`.
3. Model Eloquent terisolasi (`App\Models\Gfw\*`) dengan `$connection = 'gfw'`.
4. Rencana kerja implementasi backend sync dan visualisasi observatory `/gfw/vessels`.
