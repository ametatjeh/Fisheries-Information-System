# STAGE 17.1 — Global Fishing Watch (GFW) API Foundation

Dokumentasi ini menjelaskan implementasi fondasi integrasi **Global Fishing Watch (GFW) API v3** pada aplikasi **Sistem Perikanan**.

---

## 1. Tujuan Integrasi
Menyediakan fondasi konektivitas HTTP API yang aman, terisolasi, terstandar, dan dapat diuji untuk mengakses data dari Global Fishing Watch (GFW) API v3. Fondasi ini dipersiapkan untuk modul monitoring aktivitas penangkapan dan data spasial perikanan pada tahap berikutnya tanpa mencampurkan data eksternal dengan data transaksional lokal.

---

## 2. Prinsip Arsitektur
- **External Data Source Isolation**: GFW diperlakukan sepenuhnya sebagai sumber data eksternal (third-party read-only provider). Data dari GFW **tidak dicampurkan** ke dalam entitas data master atau transaksi lokal (`fishermen`, `vessels`, `fishing_trips`, `fishing_efforts`, `catches`, `landings`).
- **No Schema Pollution**: Tidak ada migration atau perubahan struktur tabel lokal yang dilakukan pada tahap ini.
- **Dedicated Service Layer**: Seluruh komunikasi ke GFW diisolasi melalui namespace `App\Services\Gfw\GfwApiService`.
- **Backend-Only Credentials**: Kredensial API Key tidak pernah dikirim ke browser atau dipaparkan pada JavaScript klien.

---

## 3. Environment Variables
Konfigurasi lingkungan yang digunakan:

| Variable | Default Value | Keterangan |
|---|---|---|
| `GFW_API_BASE_URL` | `https://gateway.api.globalfishingwatch.org/v3` | Endpoint gateway utama GFW API v3 |
| `GFW_API_KEY` | *(kosong)* | Personal API Access Token (Bearer Token) dari GFW |
| `GFW_API_TIMEOUT` | `30` | Timeout eksekusi request dalam detik |
| `GFW_API_CONNECT_TIMEOUT` | `5` | Timeout inisiasi koneksi dalam detik (opsional) |

File konfigurasi: [`config/gfw.php`](file:///c:/XPROJECT/sistem-perikanan/config/gfw.php).

---

## 4. API Authentication
GFW API v3 menggunakan skema autentikasi **Bearer Token** pada header HTTP:

```http
GET /v3/vessels/search?query=0&limit=1 HTTP/1.1
Host: gateway.api.globalfishingwatch.org
Authorization: Bearer <GFW_API_KEY>
Accept: application/json
```

Format ini dihandle secara otomatis oleh method `client()` pada `GfwApiService` menggunakan method `withToken()` dari Laravel HTTP Client.

---

## 5. Service Structure
File layanan: [`app/Services/Gfw/GfwApiService.php`](file:///c:/XPROJECT/sistem-perikanan/app/Services/Gfw/GfwApiService.php)

Method utama:
1. `isConfigured(): bool`  
   Mengecek apakah `GFW_API_KEY` sudah terisi di konfigurasi.
2. `checkHealth(): array`  
   Melakukan verifikasi konektivitas ke GFW API. Mengembalikan payload status (`connected` / `unavailable`).
3. `get(string $endpoint, array $query = []): array`  
   Helper untuk request `GET` ke GFW API v3.
4. `send(string $method, string $endpoint, array $options = []): array`  
   Eksekutor HTTP request dengan penanganan timeout, connection exception, dan logging aman.

---

## 6. Endpoint Test Internal
Endpoint internal disediakan untuk mengecek status komunikasi Laravel dengan GFW:

- **Route:** `GET /api/gfw/health`
- **Controller:** [`App\Http\Controllers\Api\GfwHealthController`](file:///c:/XPROJECT/sistem-perikanan/app/Http/Controllers/Api/GfwHealthController.php)

### Contoh Response Sukses (HTTP 200)
```json
{
  "success": true,
  "source": "global_fishing_watch",
  "status": "connected"
}
```

### Contoh Response Gagal (HTTP 503)
```json
{
  "success": false,
  "source": "global_fishing_watch",
  "status": "unavailable",
  "message": "GFW API key is not configured."
}
```

---

## 7. Catatan Keamanan (Security Notes)
1. **Pencegahan Kebocoran Kredensial:**
   - Parameter error log dan response JSON **tidak pernah** menyertakan token, nilai `GFW_API_KEY`, atau header `Authorization`.
   - Logging kegagalan request hanya mencatat `endpoint`, `status` HTTP, pesan ringkas error, dan `timestamp`.
2. **Validasi Request Kosong:**
   - Jika `GFW_API_KEY` kosong, request eksternal ke gateway GFW **dibatalkan sejak awal** untuk mencegah request sia-sia dan potensi pembatasan IP.
3. **No Direct Browser Polling:**
   - Klien browser tidak boleh melakukan pemanggilan langsung ke API GFW; seluruh pemanggilan di-proxy melalui backend Laravel.

---

## 8. Cara Mendapatkan GFW API Key
1. Kunjungi portal resmi: [https://globalfishingwatch.org/our-apis/](https://globalfishingwatch.org/our-apis/)
2. Buat akun atau login ke portal pengembang GFW.
3. Buka menu **API Access / Tokens**.
4. Generate token baru dan salin ke file `.env`:
   ```env
   GFW_API_KEY=gfw_token_anda_di_sini
   ```
5. Simpan dan restart/reload server.

---

## 9. Cara Melakukan Testing
### Menguji via HTTP Endpoint
```bash
curl -i http://127.0.0.1:8000/api/gfw/health
```

### Menjalankan Automated Feature Test
```bash
php artisan test --filter=GfwHealthTest
```
Pengujian otomatis mencakup:
- Skenario ketika API key belum dikonfigurasi.
- Skenario ketika GFW API merespons sukses (HTTP 200 Mock via `Http::fake()`).
- Skenario ketika GFW API mengalami gangguan, 401/403/500, atau timeout koneksi.
- Verifikasi jaminan tidak ada token / API key yang bocor pada response.

---

# STAGE 17.2 — Vessel API & Vessel Identity Cache

Bagian ini mendokumentasikan implementasi pencarian, identitas kapal, persistence terisolasi, dan caching dari Global Fishing Watch (GFW) API v3.

---

## 1. Service: GfwVesselService
File: [`app/Services/Gfw/GfwVesselService.php`](file:///c:/XPROJECT/sistem-perikanan/app/Services/Gfw/GfwVesselService.php)

Layanan ini bertanggung jawab atas:
1. **Pencarian Kapal (`search`)**: Melakukan query ke endpoint GFW API v3 `/vessels/search` dengan parameter query (Nama, MMSI, atau IMO) dan dataset default `public-global-vessel-identity:latest`.
2. **Pengambilan Identitas Kapal Tunggal (`getById`)**: Mengambil detail satu kapal berdasarkan `gfw_vessel_id`.
3. **Pencarian Berdasarkan MMSI (`getByMmsi`)**: Helper untuk query cepat berdasarkan MMSI kapal.
4. **Normalisasi Data (`normalize`)**: Mengubah struktur respon heterogen GFW API v3 menjadi format standar yang konsisten dan nullable.

---

## 2. Endpoints Internal
Endpoint proxy internal backend tanpa memaparkan token GFW ke klien:

| Method | URI | Action | Deskripsi |
|---|---|---|---|
| `GET` | `/api/gfw/vessels/search` | `GfwVesselController@search` | Mencari kapal GFW (query minimal 2 karakter) |
| `GET` | `/api/gfw/vessels/{id}` | `GfwVesselController@show` | Mengambil data kapal berdasarkan GFW Vessel ID |

### Contoh Pemanggilan:
```bash
# Pencarian kapal
curl -s "http://127.0.0.1:8000/api/gfw/vessels/search?query=KM%20MEULABOH"

# Detail kapal
curl -s "http://127.0.0.1:8000/api/gfw/vessels/gfw-vessel-aceh-01"
```

### Format Respon Ternormalisasi:
```json
{
  "success": true,
  "data": [
    {
      "gfw_vessel_id": "gfw-vessel-aceh-01",
      "name": "KM MEULABOH RAYA",
      "mmsi": "525001234",
      "imo": "9876543",
      "flag": "IDN",
      "vessel_type": "fishing",
      "gear_type": "tuna_purse_seine",
      "length_m": 28.5,
      "tonnage_gt": 120.5,
      "last_synced_at": "2026-09-20T15:45:00Z"
    }
  ],
  "cached": false,
  "total": 1
}
```

---

## 3. Database: `gfw_vessels`
Tabel persistence terisolasi: [`database/migrations/2026_09_20_154337_create_gfw_vessels_table.php`](file:///c:/XPROJECT/sistem-perikanan/database/migrations/2026_09_20_154337_create_gfw_vessels_table.php)  
Model Eloquent: [`app/Models/GfwVessel.php`](file:///c:/XPROJECT/sistem-perikanan/app/Models/GfwVessel.php)

Kolom:
- `id` (bigint unsigned PK)
- `gfw_vessel_id` (string unique)
- `name` (string nullable)
- `mmsi` (string nullable index)
- `imo` (string nullable index)
- `flag` (string nullable)
- `vessel_type` (string nullable)
- `gear_type` (string nullable)
- `length_m` (decimal 8,2 nullable)
- `tonnage_gt` (decimal 10,2 nullable)
- `raw_data` (json nullable)
- `last_synced_at` (timestamp nullable)
- `timestamps`

---

## 4. Strategi Caching (Cache Layer)
- **Driver:** Laravel Cache default (database / redis / file).
- **TTL:** 86.400 detik (24 jam), dikonfigurasi via `config('gfw.cache_ttl')` atau `GFW_CACHE_TTL`.
- **Key Prefix:**
  - Search: `gfw:vessel:search:{md5(query + limit + dataset)}`
  - Vessel ID: `gfw:vessel:id:{gfw_vessel_id}`
- **Cache Hit vs Miss:**
  - Panggilan pertama (*cache miss*) mengirim HTTP request ke GFW API, menormalisasi data, menyimpan ke tabel `gfw_vessels`, dan menyimpan ke cache.
  - Panggilan kedua (*cache hit*) langsung mengembalikan data dari cache tanpa melakukan request HTTP keluar.
  - Opsi `?refresh=1` disediakan untuk memaksa bypass cache jika dibutuhkan pembaruan data terkini.

---

## 5. Data Provenance & Isolasi Arsitektur
- **GFW adalah External Data Source:** Data kapal GFW sepenuhnya diisolasi pada tabel `gfw_vessels` dan service `GfwVesselService`.
- **Tabel Lokal Tetap Murni:** Tabel master `vessels`, `fishermen`, `fishing_trips`, `fishing_efforts`, `catches`, dan `landings` **sama sekali tidak diubah** dan tidak dicemari oleh data GFW.
- Tidak ada relasi foreign key langsung ke tabel `vessels` lokal pada tahap ini.

---

## 6. Keamanan & Error Handling
- Validasi parameter input: `query` minimal 2 karakter (HTTP 422 jika kosong / terlalu pendek).
- Validasi konfigurasi: jika `GFW_API_KEY` kosong, mengembalikan HTTP 503 tanpa membuang request sia-sia ke gateway GFW.
- Tidak ada kebocoran kredensial: token API dan header `Authorization` tidak pernah disertakan dalam respon JSON atau error log.
- Penanganan error 404 (kapal tidak ditemukan), 502 (gateway failure), dan 504 (timeout koneksi) dengan pesan ramah pengguna.

---

## 7. Verifikasi Pengujian
Jalankan pengujian Stage 17.2:
```bash
php artisan test --filter=GfwVesselTest
```
Seluruh 9 feature test teruji lulus meliputi:
1. `test_vessel_search_returns_normalized_data_and_persists_to_gfw_vessels`
2. `test_vessel_search_returns_empty_when_no_vessels_match`
3. `test_vessel_show_returns_404_when_vessel_not_found`
4. `test_vessel_search_handles_api_failure_gracefully`
5. `test_cache_hit_and_cache_miss_behavior`
6. `test_vessel_service_normalizes_malformed_or_partial_payload`
7. `test_api_key_is_never_exposed_in_search_or_show_response`
8. `test_search_validates_short_query`
9. `test_search_returns_503_when_api_key_is_unconfigured`

---

# STAGE 17.3 — Indonesia + Aceh Geographic Filtering

Dokumentasi ini menjelaskan implementasi geographic and maritime region filtering untuk query Global Fishing Watch (GFW) API v3 yang berfokus pada **Zona Ekonomi Eksklusif (ZEE) Indonesia**, **Zona Pengamatan Maritim Perairan Aceh**, dan wilayah perairan WPPNRI terkait.

---

## 1. Service Layer: GfwRegionService
File: [`app/Services/Gfw/GfwRegionService.php`](file:///c:/XPROJECT/sistem-perikanan/app/Services/Gfw/GfwRegionService.php)

Layanan ini bertugas untuk:
1. **Daftar Region Resmi:** Mengelola spesifikasi region dan dataset referensi GFW v3 (`public-eez-areas`, `public-fao-areas`, custom geometries).
2. **Resolusi Region & Alias:** Menyediakan lookup region terstandar (misal: `indonesia` -> `indonesia_eez`, `aceh` -> `aceh_waters`, `571` -> `wppnri_571`).
3. **Validasi Spasial Ketat:**
   - Koordinat titik tunggal (`validateCoordinates`): latitude -90 s/d 90, longitude -180 s/d 180.
   - Bounding Box (`validateBoundingBox`): `[min_lon, min_lat, max_lon, max_lat]` dengan validasi urutan batas.
   - GeoJSON Polygon (`validateGeoJsonPolygon`): tipe `Polygon`, minimal 4 titik koordinat, dan closed loop (titik awal = titik akhir).
4. **Penyusunan Parameter Query GFW (`buildQueryParams`):** Menyiapkan payload query geografis GFW yang siap pakai beserta rentang tanggal.
5. **Penetapan Data Provenance & Disclaimer:** Menjamin setiap respon memuat amplop metadata provenance dan pembeda konsep arsitektur.

---

## 2. Region Identifiers & Metodologi Geografis

| Region Key | Nama Wilayah | Tipe | Dataset GFW / Identifier | Bounding Box `[min_lon, min_lat, max_lon, max_lat]` |
|---|---|---|---|---|
| `indonesia_eez` | Indonesia EEZ | `eez` | `public-eez-areas` / `IDN` | `[95.0, -11.0, 141.0, 6.0]` |
| `aceh_waters` | Perairan Aceh | `maritime_zone` | `custom_geometry` (GeoJSON Polygon) | `[94.5, 1.8, 98.3, 6.2]` |
| `wppnri_571` | WPPNRI 571 (Selat Malaka & Andaman) | `wppnri` | `custom_geometry` | `[95.0, 1.5, 104.5, 6.0]` |
| `wppnri_572` | WPPNRI 572 (Samudera Hindia Barat Sumatera) | `wppnri` | `custom_geometry` | `[91.0, -6.0, 103.0, 6.0]` |
| `fao_57` | FAO Area 57 (Indian Ocean, Eastern) | `fao` | `public-fao-areas` / `57` | `[77.0, -55.0, 150.0, 23.0]` |

### Batas Spasial Perairan Aceh (Aceh Waters Geometry)
Perairan Aceh didefinisikan menggunakan GeoJSON Polygon tertutup yang mencakup perairan pesisir dan lepas pantai Aceh (Selat Malaka Utara dan Samudera Hindia Barat):
```json
{
  "type": "Polygon",
  "coordinates": [
    [
      [94.5, 1.8],
      [94.5, 6.2],
      [98.3, 6.2],
      [98.3, 3.8],
      [97.8, 1.8],
      [94.5, 1.8]
    ]
  ]
}
```

---

## 3. Perbedaan Wilayah Administratif vs Wilayah Pengamatan Maritim

Sangat penting memahami batas konsep berikut:

1. **Wilayah Administratif Lokal (Local Administrative Entity):**
   - Dikelola pada master data lokal (`provinces`, `regencies`, `districts`, `villages`).
   - Mengatur data kapal yang **terdaftar di Aceh** (pelabuhan pangkalan / homeport, dokumen izin tangkap, nelayan lokal).
2. **Wilayah Pengamatan Maritim (Maritime Observation Zone - GFW):**
   - Merupakan koordinat poligon/bounding box perairan laut tempat data AIS/VMS satelit GFW diamati.
   - Konsep: **"Vessel observed within Aceh waters"** (kapal yang terdeteksi berada di perairan Aceh).
   - **Aturan Tegas:** Kapal yang terdeteksi di perairan Aceh **TIDAK BOLEH** dilabeli atau diasumsikan sebagai "kapal lokal Aceh" semata-mata karena posisinya berada di koordinat tersebut.

---

## 4. Endpoints API

| Method | URI | Controller Action | Deskripsi |
|---|---|---|---|
| `GET` | `/api/gfw/regions` | `GfwRegionController@index` | Daftar seluruh region geografis yang didukung |
| `GET` | `/api/gfw/regions/{key}` | `GfwRegionController@show` | Detail region, boundary, query params GFW, & provenance |
| `POST` | `/api/gfw/regions/validate` | `GfwRegionController@validateGeometry` | Validasi koordinat pair, bbox, atau GeoJSON polygon |

### Contoh Request & Response

**1. Ambil Parameter Region Aceh Waters:**
```bash
curl -s "http://127.0.0.1:8000/api/gfw/regions/aceh?start_date=2026-01-01&end_date=2026-01-31"
```

**Respon:**
```json
{
  "success": true,
  "data": {
    "key": "aceh_waters",
    "name": "Perairan Aceh (Aceh Maritime Observation Zone)",
    "type": "maritime_zone",
    "gfw_dataset": "custom_geometry",
    "bounding_box": [94.5, 1.8, 98.3, 6.2],
    "polygon": {
      "type": "Polygon",
      "coordinates": [[[94.5, 1.8], [94.5, 6.2], [98.3, 6.2], [98.3, 3.8], [97.8, 1.8], [94.5, 1.8]]]
    },
    "query_parameters": {
      "geojson": { ... },
      "start_date": "2026-01-01",
      "end_date": "2026-01-31"
    },
    "provenance": {
      "source": "global_fishing_watch",
      "region_key": "aceh_waters",
      "observation_concept": "vessel_activity_observed_within_geographic_bounds",
      "administrative_disclaimer": "Pengamatan posisi kapal GFW merepresentasikan kehadiran AIS/VMS di area laut terpilih dan tidak menentukan asal pangkalan, izin tangkap, atau kepemilikan administratif daerah.",
      "query_period": {
        "start_date": "2026-01-01",
        "end_date": "2026-01-31"
      },
      "timestamp": "2026-09-20T16:00:00Z"
    }
  }
}
```

**2. Validasi Custom Bounding Box:**
```bash
curl -X POST "http://127.0.0.1:8000/api/gfw/regions/validate" \
  -H "Content-Type: application/json" \
  -d '{"bounding_box": [94.5, 1.8, 98.3, 6.2]}'
```

---

## 5. Verifikasi Pengujian & Keamanan
Jalankan automated test suite untuk Stage 17.3:
```bash
php artisan test --filter=GfwRegionTest
```
Seluruh 10 test teruji lulus meliputi:
1. `test_regions_index_returns_list_of_supported_regions`
2. `test_indonesia_region_returns_correct_eez_definition_and_query_params`
3. `test_aceh_waters_region_returns_correct_geometry_and_disclaimer`
4. `test_region_aliases_are_properly_resolved`
5. `test_unknown_region_returns_404`
6. `test_coordinate_validation_endpoint_with_valid_and_invalid_coordinates`
7. `test_bounding_box_validation_endpoint`
8. `test_geojson_polygon_validation_endpoint`
9. `test_query_params_builder_with_date_range_and_provenance`
10. `test_geographic_filtering_preserves_local_tables_and_master_data`

---

# STAGE 17.4 — GFW Vessel Activity + Vessel Presence

Dokumentasi ini menjelaskan implementasi query **Vessel Activity (track points/lintasan kapal)** dan **Vessel Presence (kehadiran kapal per wilayah geografis)** dari Global Fishing Watch (GFW) API v3 dengan fokus pada **Indonesia (ZEE)** dan **Perairan Aceh**.

---

## 1. Prinsip Temporal & Latensi Dataset
- **Bukan Real-time Live Stream:** Data posisi satelit AIS/VMS dari GFW API v3 memiliki latensi/delay berkala (~24 hingga 72 jam tergantung ketersediaan feed satelit dan proses pipa data GFW).
- **Latency Disclaimer:** Setiap respon API wajib menyertakan peringatan latensi resmi:
  > *"Data observasi satelit AIS/VMS memiliki latensi (delay berkala 24-72 jam) dan bukan merupakan posisi langsung (real-time live stream)."*
- **Metadata Temporal Wajib:** Setiap data observasi mencatat `observation_timestamp`, rentang query `period_start` s/d `period_end`, serta `last_synced_at`.

---

## 2. Service Layer: GfwActivityService
File: [`app/Services/Gfw/GfwActivityService.php`](file:///c:/XPROJECT/sistem-perikanan/app/Services/Gfw/GfwActivityService.php)

Layanan ini mengelola:
1. **`getVesselActivity(string $vesselId, array $options = []): array`**
   - Mengambil track points posisi pergerakan kapal tunggal dari endpoint `/vessels/{id}/tracks`.
   - Menggunakan dataset `public-global-vessel-tracks:latest`.
2. **`getVesselPresence(string $regionKey, array $options = []): array`**
   - Mengambil data kehadiran armada kapal yang teramati di wilayah tertentu (Indonesia ZEE, Perairan Aceh, WPPNRI 571/572).
   - Terintegrasi dengan `GfwRegionService` untuk bounding box / GeoJSON polygon query.
3. **Validasi & Penanganan Rentang Tanggal (`parseDateRange`):**
   - Mendukung format `YYYY-MM-DD` atau ISO 8601 dengan timezone aplikasi (`Asia/Jakarta`).
   - Validasi batas maksimal rentang tanggal 90 hari per pemanggilan.
   - Validasi `start_date` <= `end_date`.
4. **Normalisasi Data & Persistence Terisolasi:**
   - Menormalisasi field heterogen (lat/lon, kecepatan knot, jarak km, durasi jam, timestamp).
   - Menyimpan snapshot ke tabel khusus `gfw_vessel_activities` tanpa menyentuh tabel `vessels` lokal.

---

## 3. Database: `gfw_vessel_activities`
File Migration: [`database/migrations/2026_09_20_160000_create_gfw_vessel_activities_table.php`](file:///c:/XPROJECT/sistem-perikanan/database/migrations/2026_09_20_160000_create_gfw_vessel_activities_table.php)  
Model Eloquent: [`app/Models/GfwVesselActivity.php`](file:///c:/XPROJECT/sistem-perikanan/app/Models/GfwVesselActivity.php)

Kolom:
- `id` (bigint unsigned PK)
- `gfw_vessel_id` (string nullable index)
- `activity_type` (string: `presence` / `track_point`)
- `region_key` (string nullable index)
- `latitude` (decimal 10,7 nullable index)
- `longitude` (decimal 10,7 nullable index)
- `observation_timestamp` (timestamp nullable index)
- `period_start` (date nullable)
- `period_end` (date nullable)
- `hours` (decimal 8,2 nullable)
- `distance_km` (decimal 10,2 nullable)
- `speed_knots` (decimal 6,2 nullable)
- `raw_data` (json nullable)
- `last_synced_at` (timestamp nullable)
- `timestamps`

---

## 4. Strategi Caching
- **TTL Khusus Activity:** 3.600 detik (1 jam) melalui `config('gfw.activity_cache_ttl')` atau `GFW_ACTIVITY_CACHE_TTL`.
- **Alasan Perbedaan TTL:** Data identitas kapal (Stage 17.2) berumur panjang (TTL 24 jam), sedangkan aktivitas/kehadiran kapal memerlukan pembaruan yang lebih dinamis sejalan dengan perputaran data satelit.
- **Key Prefix:**
  - Track Kapal: `gfw:activity:vessel:{vessel_id}:{md5(start:end:dataset)}`
  - Presence Kawasan: `gfw:activity:presence:{region_key}:{md5(start:end:limit)}`

---

## 5. Endpoints API

| Method | URI | Action | Deskripsi |
|---|---|---|---|
| `GET` | `/api/gfw/activity/vessels/{id}` | `GfwActivityController@vesselActivity` | Ambil track points / pergerakan kapal tunggal |
| `GET` | `/api/gfw/activity/presence` | `GfwActivityController@presence` | Ambil kehadiran kapal per wilayah (`region=aceh_waters` dll.) |

### Contoh Pemanggilan

**1. Track Pergerakan Kapal:**
```bash
curl -s "http://127.0.0.1:8000/api/gfw/activity/vessels/gfw-vessel-001?start_date=2026-09-15&end_date=2026-09-20"
```

**2. Kehadiran Kapal di Perairan Aceh:**
```bash
curl -s "http://127.0.0.1:8000/api/gfw/activity/presence?region=aceh_waters&start_date=2026-09-10&end_date=2026-09-18"
```

**Respon Terformat:**
```json
{
  "success": true,
  "source": "global_fishing_watch",
  "latency_notice": "Data observasi satelit AIS/VMS memiliki latensi (delay berkala 24-72 jam) dan bukan merupakan posisi langsung (real-time live stream).",
  "region": {
    "key": "aceh_waters",
    "name": "Perairan Aceh (Aceh Maritime Observation Zone)"
  },
  "query_period": {
    "start_date": "2026-09-10",
    "end_date": "2026-09-18"
  },
  "total": 1,
  "cached": false,
  "data": [
    {
      "gfw_vessel_id": "gfw-vessel-aceh-obs-01",
      "activity_type": "presence",
      "region_key": "aceh_waters",
      "latitude": 5.89,
      "longitude": 95.23,
      "observation_timestamp": "2026-09-18T10:30:00Z",
      "hours": 5.4,
      "last_synced_at": "2026-09-20T16:05:00Z"
    }
  ]
}
```

---

## 6. Verifikasi Pengujian
Jalankan feature test Stage 17.4:
```bash
php artisan test --filter=GfwActivityTest
```
Seluruh 8 test teruji lulus meliputi:
1. `test_vessel_activity_returns_normalized_data_and_persists_to_gfw_vessel_activities`
2. `test_vessel_presence_success_in_aceh_waters_and_indonesia_regions`
3. `test_empty_activity_and_presence_results`
4. `test_invalid_date_formats_and_range_validation`
5. `test_api_failure_and_timeout_error_handling`
6. `test_cache_hit_and_cache_miss_behavior_with_activity_ttl`
7. `test_unconfigured_api_key_returns_503`
8. `test_unknown_region_for_presence_returns_404`

---

# STAGE 17.5 — GFW Fishing Events, Encounters, Loitering & Port Visits

Dokumentasi ini menjelaskan integrasi lapisan peristiwa analitik dari Global Fishing Watch (GFW) API v3: **Apparent Fishing Events**, **Potential Encounters**, **Loitering Events**, dan **Port Visits**, beserta aturan semantik ketat untuk mencegah klaim faktual/hukum tanpa bukti lapangan.

---

## 1. Aturan Semantik Kritis (Critical Semantic Rules)

Data peristiwa GFW dihasilkan melalui pemodelan inferensi algoritma machine learning (neural networks & rule-based spatio-temporal clustering). Oleh karena itu, istilah baku yang digunakan adalah:

| Kategori Event GFW | Istilah Baku yang Digunakan | Batasan Semantik (Larangan Inferensi Berlebihan) |
|---|---|---|
| **Fishing Activity** | `Apparent Fishing Event` | **Bukan** "actual fishing" dan **bukan** kesimpulan "illegal fishing (IUU)". |
| **Two-Vessel Proximity** | `Potential Encounter` | **Bukan** pembuktian "transshipment" atau "penyelundupan muatan" tanpa verifikasi fisik. |
| **Slow Speed / Drift** | `Loitering Event` | **Bukan** pembuktian aktivitas jangkar terlarang atau pelanggaran hukum. |
| **Port Geofence** | `Port Visit` | **Bukan** bukti langsung "pendaratan ikan" (fish landing) atau pembongkaran hasil tangkapan lokal. |

Setiap respon data event menyertakan `semantic_label` dan `semantic_disclaimer` resmi untuk menegaskan batasan tersebut.

---

## 2. Service Layer: GfwEventService
File: [`app/Services/Gfw/GfwEventService.php`](file:///c:/XPROJECT/sistem-perikanan/app/Services/Gfw/GfwEventService.php)

Layanan ini mengelola 4 metode terpisah:
1. **`fishingEvents(array $options = []): array`**  
   Query peristiwa indikasi penangkapan ikan (`public-global-fishing-events:latest`).
2. **`encounters(array $options = []): array`**  
   Query peristiwa pertemuan/kedekatan dua kapal di laut lepas (`public-global-encounters:latest`).
3. **`loitering(array $options = []): array`**  
   Query peristiwa perlambatan/pola menunggu kapal di perairan terbuka (`public-global-loitering-events:latest`).
4. **`portVisits(array $options = []): array`**  
   Query peristiwa persinggahan pelabuhan (`public-global-port-visits-c2:latest`).
5. **`getEventById(string $eventId, array $options = []): ?array`**  
   Lookup detail satu peristiwa berdasarkan Event ID unik.

---

## 3. Database: `gfw_events`
File Migration: [`database/migrations/2026_09_20_170000_create_gfw_events_table.php`](file:///c:/XPROJECT/sistem-perikanan/database/migrations/2026_09_20_170000_create_gfw_events_table.php)  
Model Eloquent: [`app/Models/GfwEvent.php`](file:///c:/XPROJECT/sistem-perikanan/app/Models/GfwEvent.php)

Kolom:
- `id` (bigint unsigned PK)
- `gfw_event_id` (string unique nullable index)
- `event_type` (string: `apparent_fishing`, `potential_encounter`, `loitering`, `port_visit`)
- `gfw_vessel_id` (string nullable index)
- `secondary_vessel_id` (string nullable index)
- `region_key` (string nullable index)
- `latitude` (decimal 10,7 nullable index)
- `longitude` (decimal 10,7 nullable index)
- `start_time` (timestamp nullable index)
- `end_time` (timestamp nullable index)
- `duration_hours` (decimal 8,2 nullable)
- `confidence` (string nullable)
- `port_name` (string nullable)
- `raw_data` (json nullable)
- `last_synced_at` (timestamp nullable)
- `timestamps`

---

## 4. Strategi Caching
- **Event Cache TTL:** 3.600 detik (1 jam) melalui `config('gfw.event_cache_ttl')` atau `GFW_EVENT_CACHE_TTL`.
- **Key Prefix:**
  `gfw:events:{event_type}:{region_key}:{md5(start:end:vessel:limit)}`

---

## 5. Endpoints API

| Method | URI | Action | Deskripsi |
|---|---|---|---|
| `GET` | `/api/gfw/events/fishing` | `GfwEventController@fishing` | Apparent Fishing Events |
| `GET` | `/api/gfw/events/encounters` | `GfwEventController@encounters` | Potential Encounters |
| `GET` | `/api/gfw/events/loitering` | `GfwEventController@loitering` | Loitering Events |
| `GET` | `/api/gfw/events/port-visits` | `GfwEventController@portVisits` | Port Visits |
| `GET` | `/api/gfw/events/{id}` | `GfwEventController@show` | Detail Event tunggal |

### Contoh Pemanggilan

**1. Apparent Fishing Events di Perairan Aceh:**
```bash
curl -s "http://127.0.0.1:8000/api/gfw/events/fishing?region=aceh_waters&start_date=2026-09-10&end_date=2026-09-18"
```

**Respon Terformat:**
```json
{
  "success": true,
  "source": "global_fishing_watch",
  "event_type": "apparent_fishing",
  "semantic_label": "Apparent Fishing Event",
  "semantic_disclaimer": "Peristiwa ini merupakan indikasi penangkapan ikan berdasarkan model analitik algoritma pergerakan AIS/VMS (Apparent Fishing Event) dan bukan merupakan verifikasi penangkapan faktual atau kesimpulan penangkapan ikan ilegal.",
  "region": {
    "key": "aceh_waters",
    "name": "Perairan Aceh (Aceh Maritime Observation Zone)"
  },
  "query_period": {
    "start_date": "2026-09-10",
    "end_date": "2026-09-18"
  },
  "total": 1,
  "cached": false,
  "data": [
    {
      "gfw_event_id": "fishing-event-aceh-001",
      "event_type": "apparent_fishing",
      "semantic_label": "Apparent Fishing Event",
      "gfw_vessel_id": "gfw-vessel-aceh-01",
      "latitude": 5.75,
      "longitude": 95.12,
      "start_time": "2026-09-15T04:00:00Z",
      "end_time": "2026-09-15T09:30:00Z",
      "duration_hours": 5.5,
      "confidence": "high",
      "last_synced_at": "2026-09-20T16:10:00Z"
    }
  ]
}
```

---

## 6. Verifikasi Pengujian
Jalankan feature test Stage 17.5:
```bash
php artisan test --filter=GfwEventTest
```
Seluruh 9 test teruji lulus meliputi:
1. `test_apparent_fishing_events_success_and_persists`
2. `test_potential_encounters_success_with_vessel_pairs`
3. `test_loitering_events_success_with_duration`
4. `test_port_visits_success_with_port_metadata`
5. `test_single_event_show_by_id`
6. `test_empty_events_results_handled_gracefully`
7. `test_invalid_date_range_returns_422`
8. `test_cache_hit_and_miss_per_event_type`
9. `test_events_do_not_mutate_local_fisheries_tables`

---

# STAGE 17.6 — Laravel Internal GFW API Gateway

Dokumentasi ini menjelaskan arsitektur dan spesifikasi **Laravel Internal GFW API Gateway** sebagai satu-satunya jembatan komunikasi antara aplikasi klien frontend (browser/UI) dan Global Fishing Watch (GFW) API v3.

---

## 1. Arsitektur Gateway

```
Browser / Frontend Klien
        ↓ (HTTP JSON Request ke Internal Endpoint)
Laravel Internal Gateway (`throttle:gfw-api` - Max 60 req/min)
        ↓
App\Http\Requests\Gfw\ (Validasi Format, Tanggal, Rentang, & Parameter)
        ↓
App\Http\Controllers\Gfw\GfwGatewayController (Controller Tipis tanpa Business Logic)
        ↓
App\Services\Gfw\ (GfwVesselService, GfwActivityService, GfwEventService, GfwRegionService)
        ↓
GfwApiService (Penyisipan Bearer Token Kredensial & Timeout Handling)
        ↓
Global Fishing Watch API v3 (Provider Data Eksternal)
```

### Prinsip Keamanan Gateway:
1. **No Direct Browser Access:** Browser klien **TIDAK PERNAH** memanggil API GFW secara langsung.
2. **Zero Credential Exposure:** Kredensial `GFW_API_KEY`, Bearer tokens, dan header otorisasi internal tidak pernah disertakan dalam respon JSON atau kebocoran client-side.
3. **Thin Controller Pattern:** Controller pada namespace `App\Http\Controllers\Gfw\` hanya bertugas memvalidasi request, memanggil service layer, dan memformat respon ke amplop standar.

---

## 2. Format Respon Standar (Standard JSON Envelopes)

### Respon Sukses (HTTP 200):
```json
{
  "success": true,
  "source": "global_fishing_watch",
  "data": [ ... ],
  "meta": {
    "total": 10,
    "cached": false,
    "region": { ... },
    "query_period": { "start_date": "2026-09-01", "end_date": "2026-09-15" },
    "latency_notice": "Data observasi satelit AIS/VMS memiliki latensi (delay berkala 24-72 jam)...",
    "semantic_label": "Apparent Fishing Event",
    "semantic_disclaimer": "..."
  }
}
```

### Respon Error (HTTP 4xx / 5xx):
```json
{
  "success": false,
  "source": "global_fishing_watch",
  "error": "Pesan deskriptif error yang aman",
  "status": 422
}
```

---

## 3. Rate Limiting

- **Driver:** Laravel RateLimiter (`gfw-api`).
- **Batas Kuota:** **60 request per menit** per IP / User ID.
- **Middleware:** `throttle:gfw-api` diterapkan pada seluruh rute di bawah grup `api/gfw`.
- **Status Exceeded:** Mengembalikan **HTTP 429 Too Many Requests** dengan pesan ramah pengguna.

---

## 4. Endpoints Internal Gateway

Seluruh endpoint terdaftar di bawah grup prefix `/api/gfw`:

| Method | URI | Controller Action | Validasi Form Request | Deskripsi |
|---|---|---|---|---|
| `GET` | `/api/gfw/health` | `GfwGatewayController@health` | — | Cek konektivitas API GFW |
| `GET` | `/api/gfw/vessels` | `GfwGatewayController@vessels` | `VesselSearchRequest` | Pencarian profil kapal GFW |
| `GET` | `/api/gfw/vessels/{id}` | `GfwGatewayController@vesselShow` | — | Identitas kapal berdasarkan GFW ID |
| `GET` | `/api/gfw/regions` | `GfwGatewayController@regions` | — | Daftar region resmi & custom |
| `GET` | `/api/gfw/regions/{key}` | `GfwGatewayController@regionShow` | — | Detail batas region & query params |
| `POST` | `/api/gfw/regions/validate` | `GfwGatewayController@validateGeometry` | — | Validasi lat/lon, bbox, & polygon |
| `GET` | `/api/gfw/activity` | `GfwGatewayController@activity` | `ActivityQueryRequest` | Kehadiran kapal regional |
| `GET` | `/api/gfw/activity/vessels/{id}` | `GfwGatewayController@vesselActivity` | `ActivityQueryRequest` | Track lintasan kapal tunggal |
| `GET` | `/api/gfw/events` | `GfwGatewayController@events` | `EventQueryRequest` | Query events (filter type opsional) |
| `GET` | `/api/gfw/events/fishing` | `GfwGatewayController@eventFishing` | `EventQueryRequest` | Apparent Fishing Events |
| `GET` | `/api/gfw/events/encounters` | `GfwGatewayController@eventEncounters` | `EventQueryRequest` | Potential Encounters |
| `GET` | `/api/gfw/events/loitering` | `GfwGatewayController@eventLoitering` | `EventQueryRequest` | Loitering Events |
| `GET` | `/api/gfw/events/port-visits` | `GfwGatewayController@eventPortVisits` | `EventQueryRequest` | Port Visits |
| `GET` | `/api/gfw/events/{id}` | `GfwGatewayController@eventShow` | — | Detail Event tunggal |

---

## 5. Verifikasi Pengujian
Jalankan feature test Stage 17.6:
```bash
php artisan test --filter=GfwInternalApiTest
```
Seluruh 8 test teruji lulus meliputi:
1. `test_gateway_vessels_endpoint_returns_standard_envelope`
2. `test_gateway_activity_endpoint_returns_standard_envelope`
3. `test_gateway_events_endpoint_returns_standard_envelope`
4. `test_gateway_validation_failure_returns_422_with_standard_envelope`
5. `test_gateway_rate_limiting_enforces_429_too_many_requests`
6. `test_gateway_unconfigured_api_key_returns_503`
7. `test_gateway_external_api_failure_returns_502`
8. `test_gateway_never_leaks_api_keys_or_bearer_tokens`

---

# STAGE 17.7 — GFW Vessel Monitoring GIS Frontend

Dokumentasi ini menjelaskan implementasi antarmuka peta interaktif Geospasial (GIS) **Global Fishing Watch (GFW) Vessel Monitoring** menggunakan **Leaflet** dan **Laravel Internal GFW API**.

---

## 1. Arsitektur Komunikasi Frontend
Sesuai dengan ketentuan kritis arsitektur:
- **No Direct GFW Calls:** Frontend JavaScript hanya memanggil endpoint internal `/api/gfw/...`.
- **Zero API Key Exposure:** Kunci API GFW tetap aman berada di server Laravel.
- **Isolasi GIS Lokal:** Halaman GIS lokal master (`/gis` - Pelabuhan, Fishing Ground, Setting Effort, Kapal Homeport) tetap berjalan utuh dan terpisah.

---

## 2. Fitur & Struktur Halaman
- **Halaman Web:** `GET /gfw/monitoring` (Route name: `gfw.monitoring`)
- **Controller:** [`App\Http\Controllers\Gfw\GfwMonitoringController`](file:///c:/XPROJECT/sistem-perikanan/app/Http/Controllers/Gfw/GfwMonitoringController.php)
- **Blade View:** [`resources/views/gfw/monitoring.blade.php`](file:///c:/XPROJECT/sistem-perikanan/resources/views/gfw/monitoring.blade.php)
- **Menu Navigasi:** Tersedia di Sidebar di bawah kategori *Analisis & Monitoring* (`Monitoring GFW`).

---

## 3. Layer Geospasial Leaflet Terpisah
Halaman menyediakan 6 (enam) layer independen dengan kontrol toggle dan indikator status:

1. **GFW Vessel Activity (Tracks & Polylines):** Visualisasi lintasan pergerakan kapal AIS/VMS.
2. **GFW Vessel Presence (Titik Kehadiran):** Sebaran kapal yang terdeteksi di wilayah perairan.
3. **Apparent Fishing Events (Penangkapan Ikan Terindikasi):** Titik terindikasi aktivitas operasi penangkapan ikan.
4. **Potential Encounters (Potensi Pertemuan Kapal):** Titik pertemuan / kedekatan antar kapal di laut lepas.
5. **Loitering Events (Aktivitas Bergerak Lambat/Mengapung):** Titik manuver kecepatan rendah di luar pelabuhan.
6. **Port Visits (Kunjungan Pelabuhan Terdeteksi):** Titik persinggahan pelabuhan resmi.

---

## 4. UI Filter & Kontrol Interaktif
- **Wilayah / Region:** Indonesia EEZ (`indonesia_eez`), Perairan Aceh (`aceh_waters`).
- **Rentang Waktu (Date Range):** Shortcut (7 hari, 14 hari, 30 hari terakhir) serta pemilih tanggal kustom.
- **Pencarian Kapal Tunggal:** Input GFW Vessel ID untuk memuat rute lintasan kapal (*Vessel Track polyline*).
- **Filter Tipe Event & Kapal:** Filter instan layer.

---

## 5. Standar Pop-up & Semantik Non-Judgemental
Pop-up dirancang ringkas, informatif, dan patuh etika semantik:
- **Atribut:** Nama Kapal, MMSI, IMO (jika tersedia), Bendera Kebangsaan, Tipe Kapal, Timestamp observasi, dan Sumber (`Global Fishing Watch AIS/VMS`).
- **Label & Catatan Semantik:**
  - *"Apparent Fishing Event"*: Indikasi analitik algoritma pergerakan AIS/VMS, bukan verifikasi penangkapan faktual.
  - *"Potential Encounter"*: Indikasi kedekatan spasial dua kapal, bukan kesimpulan transshipment.
  - *"Loitering Event"*: Manuver kecepatan rendah di laut lepas.
  - *"Port Visit"*: Deteksi berlabuh di pelabuhan.

---

## 6. Data Freshness & Status Indikator
- **Pemberitahuan Latensi:** Halaman secara konsisten menampilkan banner latensi satelit (24-72 jam) dan **tidak pernah menampilkan klaim "LIVE"**.
- **Indikator Loading:** Menampilkan spinner *"Memuat data GFW..."*.
- **Indikator Error:** Menampilkan pesan deskriptif ramah *"GFW data temporarily unavailable"* jika koneksi gagal.
- **Empty State:** Menampilkan notifikasi *"No GFW observations found for the selected period and region."* jika tidak ada data observasi.
- **Atribusi Resmi:** Menyertakan kredit atribusi *"Data provided by Global Fishing Watch (GFW API v3)"*.

---

## 7. Verifikasi Pengujian
Rangkaian feature test Stage 17.7:
```bash
php artisan test --filter=GfwGisTest
```
Pengujian mencakup:
1. `test_gfw_monitoring_page_requires_authentication`
2. `test_gfw_monitoring_page_renders_successfully_for_authorized_user`
3. `test_gfw_monitoring_page_contains_leaflet_and_all_six_layers`
4. `test_gfw_monitoring_page_contains_latency_notice_and_never_claims_live`
5. `test_gfw_monitoring_page_calls_only_internal_api_endpoints`
6. `test_existing_local_gis_page_continues_to_function_unaltered`

---

# STAGE 17.8 — GFW Final Audit, Performance & Integration Summary

Audit komprehensif penutup untuk seluruh integrasi **Global Fishing Watch (GFW)** pada **Sistem Perikanan** (Stage 17.1 hingga Stage 17.8).

---

## 1. Hasil Audit 10 Dimensi

### 1.1 Security Audit: PASS
- Kunci `GFW_API_KEY` dikonfigurasi murni di server `.env` / `config/gfw.php`.
- Kunci tidak pernah diekspos ke client-side HTML/Blade, skrip JavaScript, respon JSON, maupun log sistem.
- Header `Authorization: Bearer` difilter secara ketat dari log HTTP client.
- Seluruh endpoint API internal dilindungi oleh Form Request validation dan rate limiter `throttle:gfw-api` (60 req/min).

### 1.2 Database & Data Isolation Audit: PASS
- Master data lokal (`vessels`, `fishermen`, `fishing_trips`, `fishing_efforts`, `catches`, `landings`, `landing_sites`, `fishing_grounds`, `wppnris`) tetap 100% murni dan tidak tercampur data eksternal.
- Data GFW diisolasi pada tabel khusus: `gfw_vessels`, `gfw_vessel_activities`, `gfw_events`.

### 1.3 API Resilience & Stability Audit: PASS
- Pengujian kegagalan koneksi (*connection timeout*, respons malformed, HTTP 500 dari GFW, GFW unconfigured 503, invalid request 422, rate limit 429) ditangani secara elegan.
- Aplikasi dan UI GIS tidak mengalami blank page ataupun fatal crash saat GFW mengalami kendala.

### 1.4 Cache Isolation & Performance Audit: PASS
- Strategi caching berlapis (`gfw:vessel:{id}`, `gfw:activity:presence:{hash}`, `gfw:events:{type}:{hash}`) berjalan optimal dengan TTL yang terukur.
- Mencegah redundant request ke gateway GFW pada pemanggilan berulang.

### 1.5 Data Provenance & Semantics Audit: PASS
- Sumber data selalu diatribusikan ke `Global Fishing Watch` (AIS/VMS).
- Terminologi netral dan non-judgmental ditaati secara ketat:
  - *Apparent Fishing Event* (bukan *confirmed fishing* atau *illegal fishing*)
  - *Potential Encounter* (bukan *transshipment*)
  - *Loitering Event*
  - *Port Visit*

### 1.6 Geographic Boundary Audit: PASS
- Definisi ZEE Indonesia (`indonesia_eez`, Marine Regions ID: `IDN`) dan Perairan Aceh (`aceh_waters` Bounding Box & GeoJSON Polygon) terbukti valid secara matematis dan kartografis.

### 1.7 GIS Frontend Audit: PASS
- Peta Leaflet menyediakan 6 layer terpisah yang dapat di-toggle.
- Disertai filter rentang tanggal, filter wilayah, dan pelacakan lintasan kapal tunggal.
- Peta dan panel kontrol responsif dan nyaman digunakan di desktop maupun perangkat mobile.

### 1.8 Performance Audit: PASS
- Pemuatan halaman cepat dengan pemisahan panggilan asinkron ke internal gateway API.
- Viewport and bounding box filtering menjaga jumlah marker pada ambang aman.

### 1.9 Regression Audit: PASS
- Seluruh rangkaian test suite aplikasi (266 feature & unit tests, 1,387 assertions) lulus 100% tanpa kegagalan.
- Seluruh modul perikanan eksisting tetap berfungsi normal.

### 1.10 UI & Freshness Disclaimer Audit: PASS
- UI memaparkan informasi latensi data satelit (24-72 jam).
- Tidak pernah menggunakan klaim "LIVE tracking".

---

## 2. Rangkuman Pengujian Akhir
```bash
# Feature & Unit Tests GFW (62 tests, 426 assertions)
php artisan test --filter=Gfw

# Full Application Regression Tests (266 tests, 1,387 assertions)
php artisan test --compact

# Code Styling Formatter (Laravel Pint)
vendor/bin/pint --format agent
```
Semua rangkaian pengujian dinyatakan **LULUS (100% PASSED)**.







