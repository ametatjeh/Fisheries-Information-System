# GFW-V13 — DOKUMENTASI PENGGANTIAN GARIS ZEE DENGAN DATA RESMI BADAN INFORMASI GEOSPASIAL (BIG)

## 1. TUJUAN

Tujuan utama dari milestone GFW-V13 adalah mengganti visualisasi garis/polygon ZEE yang ditampilkan pada halaman pemantauan kapal `/gfw/vessels` dengan **data resmi Peta Batas ZEE dari Badan Informasi Geospasial (BIG)**.

> **PENEGASAN ARSITEKTUR WAJIB:**  
> Garis batas ZEE yang divisualisasikan pada peta sistem **berasal dari data resmi Badan Informasi Geospasial (BIG)** (Layer ID: 10, ArcGIS MapServer/FeatureServer publik).  
> **Sistem TIDAK menggunakan perhitungan buffer 200 NM dari garis pantai**, dan TIDAK mengarang garis ZEE lokal buatan sendiri.

---

## 2. SUMBER DATA RESMI & SPESIFIKASI UPSTREAM BIG

* **Institusi Penerbit:** Badan Informasi Geospasial (BIG) Republik Indonesia
* **Katalog Layanan:** Kebijakan Satu Peta (KSP) / Satu Peta Publik — Batas Wilayah
* **Endpoint Upstream:**  
  `https://kspservices.big.go.id/satupeta/rest/services/PUBLIK/BATAS_WILAYAH/MapServer/10`
* **Query Endpoint (GeoJSON):**  
  `https://kspservices.big.go.id/satupeta/rest/services/PUBLIK/BATAS_WILAYAH/MapServer/10/query?where=1%3D1&outFields=*&returnGeometry=true&f=geojson`
* **Nama Layer:** `Peta Batas ZEE`
* **Layer ID:** `10`
* **Tipe Geometri:** `Polyline` (`LineString` / `MultiLineString`)
* **Sistem Referensi Spasial:** `EPSG:4326` (WGS 84, derajat desimal [longitude, latitude])
* **Format Output:** `GeoJSON` (`FeatureCollection`)

### Klasifikasi Status Batas BIG (`stslat`):
1. **`1` = Kesepakatan** (Garis batas ZEE yang telah disepakati secara bilateral/multilateral dan berlaku penuh).
2. **`2` = Unilateral** (Klaim batas ZEE sepihak Republik Indonesia sesuai perundang-undangan nasional, misalnya batas barat & utara perairan laut lepas Aceh).
3. **`3` = Kesepakatan Belum Diratifikasi** (Telah disepakati perundingan diplomatik tetapi instrumen ratifikasi belum tuntas).
4. **`4` = Perlu Kesepakatan** (Zona tumpang tindih yang masih memerlukan perundingan batas maritim antar negara tetangga).

---

## 3. ARSITEKTUR SISTEM & PEMISAHAN TANGGUNG JAWAB

Arsitektur sistem dibangun dengan pola proxy internal yang aman, berkinerja tinggi, dan terisolasi:

```text
Browser (MapLibre GL JS)
   │
   │  GET /api/gis/big/zee
   ▼
Laravel Web/API Routing
   │
   ▼
BigZeeApiController
   │
   ▼
BigMaritimeBoundaryService
   │
   ├── Cache Hit (TTL 24 jam / 86.400 detik) ──► Return Cached FeatureCollection
   │
   ├── Cache Miss ──► Query Upstream BIG MapServer/10
   │                      │
   │                      ├── Success ──► Normalize & Enrich Properties
   │                      │               Persist to Storage Backup
   │                      │               Put in Cache (24 jam)
   │                      │               Return GeoJSON
   │                      │
   │                      └── Failed / Timeout / 5xx
   │                              │
   │                              ├── Try Cache (if exists)
   │                              │
   │                              ├── Try Local Storage Backup (storage/app/private/gis/big_peta_batas_zee.geojson)
   │                              │
   │                              └── Graceful Notice ("menggunakan cache terakhir")
```

---

## 4. PEMISAHAN ANTARA BIG ZEE DAN AOI GFW

Sangat penting untuk memahami bahwa sistem membedakan secara tegas tiga entitas spasial berikut:

| Entitas | Sumber Data | Tipe Geometri | Tujuan & Kegunaan |
| :--- | :--- | :--- | :--- |
| **BIG Peta Batas ZEE** | BIG Layer ID 10 | `LineString` / `MultiLineString` | **Visualisasi peta resmi** batas maritim yurisdiksi Indonesia. |
| **AOI GFW (zee-indonesia-aceh)** | `AoiService` / GFW AOI | `Polygon` | **Query spasial data satelit GFW** (API filter batas perairan observasi). |
| **Zona Observasi GFW +100 NM** | `AoiService` Buffer (185.2 km) | `Polygon` | **Analisis kapal lintas perbatasan** / early warning pergerakan kapal asing. |

> **PERINGATAN ARSITEKTUR:**  
> Penggantian garis visual ZEE dengan data BIG **TIDAK MENGUBAH** algoritma atau payload `AoiService` yang digunakan untuk berkomunikasi dengan API Global Fishing Watch.

---

## 5. ENDPOINT INTERNAL LARAVEL

* **Route:** `GET /api/gis/big/zee`
* **Controller:** `App\Http\Controllers\Api\BigZeeApiController@index`
* **Nama Route:** `api.gis.big.zee`
* **Format Response Sukses (JSON standard envelope):**
  ```json
  {
    "success": true,
    "source": "BIG",
    "layer": "Peta Batas ZEE",
    "layer_id": 10,
    "crs": "EPSG:4326",
    "data": {
      "type": "FeatureCollection",
      "features": [
        {
          "type": "Feature",
          "geometry": {
            "type": "LineString",
            "coordinates": [[95.3, 5.5], ...]
          },
          "properties": {
            "objectid": 19,
            "stslat": 2,
            "status_label": "Unilateral",
            "pjgbts": 720.54,
            "source": "Badan Informasi Geospasial (BIG)",
            "layer": "Peta Batas ZEE",
            "layer_id": 10
          }
        }
      ]
    },
    "cached": true
  }
  ```
* **Format Response Langsung (Raw GeoJSON):**  
  Jika dipanggil dengan parameter `?geojson=1` atau `?raw=1`, endpoint mengembalikan GeoJSON murni dengan header `Content-Type: application/geo+json`.
* **Format Response Error (Upstream down tanpa fallback):**
  ```json
  {
    "success": false,
    "source": "BIG",
    "layer": "Peta Batas ZEE",
    "layer_id": 10,
    "crs": "EPSG:4326",
    "error": "Garis ZEE BIG tidak dapat dimuat."
  }
  ```
  Status HTTP: `502 Bad Gateway`.

---

## 6. INTEGRASI MAPLIBRE PADA /gfw/vessels

Halaman `/gfw/vessels` diimplementasikan menggunakan MapLibre GL JS:
1. **Source MapLibre:**
   * ID: `big-zee`
   * Type: `geojson`
   * Data: `/api/gis/big/zee`
2. **Layer Garis Batas ZEE:**
   * ID: `big-zee-line`
   * Type: `line`
   * Line Width: `2.5`
   * Line Opacity: `0.95`
   * Color Coding berdasarkan status maritim BIG (`stslat`):
     * Status `1` (Kesepakatan): `#10b981` (Emerald)
     * Status `2` (Unilateral): `#2563eb` (Blue)
     * Status `3` (Kesepakatan Belum Diratifikasi): `#f59e0b` (Amber)
     * Status `4` (Perlu Kesepakatan): `#ef4444` (Rose)
3. **Popup Interaktif Klik Garis:**  
   Ketika user mengklik segmen garis ZEE BIG, popup menampilkan:
   * Sumber: `Badan Informasi Geospasial (BIG)`
   * Layer: `Peta Batas ZEE (Layer ID 10)`
   * Status: Nilai teks status maritim (e.g., `Unilateral`)
   * Kode Status: `stslat`
   * ID Objek: `objectid`
   * Panjang Segmen: `pjgbts` (dalam NM)
   * Sistem Koordinat: `WGS84 (EPSG:4326)`
4. **Layer Toggles:**
   * `☑ ZEE — BIG`: Default **ON** (checked).
   * `☐ Zona Observasi GFW +100 NM`: Default **OFF** (unchecked).
   * `☑ Track`: Default **ON** (checked).
5. **Legenda Spasial:**
   Diperbarui dengan judul `ZEE — Data resmi BIG` dan rincian 4 warna status maritim resmi.

---

## 7. KEAMANAN & INTEGRITAS SISTEM

* **Tanpa Credential Eksternal di Frontend:** Tidak ada token, API key, atau URL upstream BIG yang terekspos ke browser.
* **Anti-SSRF:** Endpoint internal `/api/gis/big/zee` adalah endpoint deterministik terikat pada URL upstream resmi BIG, tidak menerima arbitrary input URL dari client.
* **Isolasi Database:**
  * Tidak ada tabel baru atau migrasi baru di database `sistem_perikanan` (tetap 48 tabel).
  * Tidak ada modifikasi skema di database `sistem_gfw`.
  * Tidak ada foreign key lintas database.

---

## 8. HASIL REGRESI & VERIFIKASI

* **PHPUnit Suite:**
  * Total Tests: **458 passed** (baseline 439 + 12 test BIG + 7 update fitur).
  * Assertions: **2,796 assertions**.
  * Failures: **0**.
  * Errors: **0**.
* **Laravel Pint Code Formatter:** **PASSED** (0 issues).
* **Browser End-to-End Verification:**
  * Visualisasi garis ZEE BIG tampil akurat sesuai koordinat nasional.
  * Toggles layer berfungsi mulus secara real-time.
  * Marker kapal dan popup observasi AIS/VMS tetap beroperasi normal.
