# RZWP3K Aceh MapLibre GL JS Integration

```text
Document: RZWP3K Aceh GIS Visualization & MapLibre Integration
Stage: 18.4 — MapLibre RZWP3K Aceh Layers
Date: 21 September 2026
Status: Complete & Verified (Geometry-Ready / Safe Default NULL)
Map Engine: MapLibre GL JS 4.7.1
Internal API: GET /api/rzwp3k/zones
```

---

## 1. Arsitektur Aliran Data Visualisasi

```text
┌─────────────────────────────────────────────────────────────────────────────┐
│                       ARSITEKTUR INTEGRASI RZWP3K GIS                       │
│                                                                             │
│  ┌────────────────────────┐         ┌────────────────────────────────────┐  │
│  │ Tabel rzwp3k_zones     │         │ App\Http\Controllers\Api\          │  │
│  │ (Geometry-Ready/NULL)  │ ──────> │ Rzwp3kZoneController               │  │
│  └────────────────────────┘         └─────────────────┬──────────────────┘  │
│                                                       │                     │
│                                                       ▼                     │
│  ┌────────────────────────┐         ┌────────────────────────────────────┐  │
│  │ MapLibre GL JS (4.7.1) │         │ Endpoint GET /api/rzwp3k/zones     │  │
│  │ - source-rzwp3k        │ <────── │ (GeoJSON FeatureCollection WGS84)  │  │
│  │ - layer-rzwp3k-fill    │         │ + Cache (1 Jam / Hash Filter)      │  │
│  │ - layer-rzwp3k-line    │         └────────────────────────────────────┘  │
│  └───────────┬────────────┘                                                 │
│              │                                                              │
│              ▼                                                              │
│  ┌───────────────────────────────────────────────────────────────────────┐  │
│  │ UI & Interaktivitas:                                                  │  │
│  │ - Layer Toggle Button (Default OFF, Counter Badge)                    │  │
│  │ - Simbologi Warna per Kawasan (KPU: Hijau, KK: Cyan, AL: Amber, KSNT) │  │
│  │ - Popup Spasial Lengkap + Disclaimer Netral Tanpa Kesimpulan Ilegal   │  │
│  │ - Empty State Notice jika Poligon Belum Diimpor                       │  │
│  └───────────────────────────────────────────────────────────────────────┘  │
└─────────────────────────────────────────────────────────────────────────────┘
```

---

## 2. Spesifikasi Endpoint Internal API

* **Route:** `GET /api/rzwp3k/zones`
* **Route Name:** `api.rzwp3k.zones`
* **Controller:** [`App\Http\Controllers\Api\Rzwp3kZoneController`](file:///c:/XPROJECT/sistem-perikanan/app/Http/Controllers/Api/Rzwp3kZoneController.php)
* **Format Response:** `application/geo+json` (RFC 7946)
* **Kueri Default:** `Rzwp3kZone::query()->active()->withGeometry()`
* **Parameter Filter Opsional:**
  * `zone_type`: `KPU`, `KK`, `AL`, `KSNT` (Sanitasi ketat, case-insensitive).
  * `subzone_type`: String kode subzona (misal: `KPU-PT`, `KK-KKP`).

### Contoh Struktur Response GeoJSON:
```json
{
  "type": "FeatureCollection",
  "features": [
    {
      "type": "Feature",
      "id": 1,
      "properties": {
        "id": 1,
        "code": "KPU-PT",
        "parent_code": "KPU",
        "name": "Zona Perikanan Tangkap",
        "zone_type": "KPU",
        "subzone_type": "KPU-PT",
        "description": "Peruntukan penangkapan ikan pelagis dan demersal.",
        "regency": "Kabupaten Aceh Barat",
        "area_ha": 15420.5,
        "source": "Dinas Kelautan dan Perikanan Aceh",
        "source_document": "Qanun Aceh Nomor 1 Tahun 2020",
        "legal_basis": "Qanun Aceh 1/2020 Lampiran Batas",
        "status": "legal_active",
        "disclaimer": "Informasi ini merupakan informasi zonasi spasial. Tampilan spasial tidak dengan sendirinya menentukan status legalitas suatu aktivitas atau kapal.",
        "metadata": {
          "crs": "EPSG:4326",
          "authority": "Pemerintah Aceh"
        }
      },
      "geometry": {
        "type": "Polygon",
        "coordinates": [...]
      }
    }
  ],
  "metadata": {
    "legal_basis": "Qanun Aceh Nomor 1 Tahun 2020",
    "authority": "Pemerintah Aceh / DKP Aceh",
    "target_crs": "EPSG:4326",
    "count": 1,
    "disclaimer": "Informasi ini merupakan informasi zonasi spasial. Tampilan spasial tidak dengan sendirinya menentukan status legalitas suatu aktivitas atau kapal."
  }
}
```

> [!NOTE]
> Jika seluruh record basis data masih bernilai `geometry = null`, API mengembalikan `{"type": "FeatureCollection", "features": []}` secara valid dengan HTTP 200 tanpa menghasilkan poligon fiktif.

---

## 3. Strategi Caching & Cache Invalidation

* **Cache Engine:** Laravel Cache Driver.
* **Cache Key:** `rzwp3k:zones:geojson:{hash}` (Hash dihitung dari parameter filter `zone_type` dan `subzone_type`).
* **TTL:** 3600 detik (1 jam).
* **Cache Invalidation:** Metode `Rzwp3kZoneController::clearCache()` otomatis dipanggil saat import data via `php artisan rzwp3k:import`.

---

## 4. Konfigurasi Layer MapLibre GL JS

### A. Source
* **ID Source:** `source-rzwp3k`
* **Tipe:** `geojson`
* **Data URL:** Route `api.rzwp3k.zones`

### B. Layer Poligon (`layer-rzwp3k-fill`)
* **Tipe Layer:** `fill`
* **Status Default:** `none` (Tersembunyi / Default OFF hingga diaktifkan pengguna).
* **Opacity:** `0.22` (Transparan agar peta dasar CartoDB Dark Matter dan layer titik tetap terlihat jelas).
* **Simbologi Warna Baku Berdasarkan Jenis Kawasan:**
  * `KPU` (Kawasan Pemanfaatan Umum): `#10b981` (Emerald / Hijau).
  * `KK` (Kawasan Konservasi): `#06b6d4` (Cyan / Biru Terang).
  * `AL` (Alur Laut): `#f59e0b` (Amber / Kuning Jingga).
  * `KSNT` (Strategis Nasional): `#8b5cf6` (Ungu).
  * Default: `#14b8a6` (Teal).

### C. Layer Garis Batas (`layer-rzwp3k-line`)
* **Tipe Layer:** `line`
* **Status Default:** `none`
* **Line Width:** `1.5`
* **Line Dasharray:** `[2, 1.5]` (Garis putus-putus delimitasi maritim).

### D. Urutan Penumpukan Layer (Layer Order)
Layer poligon RZWP3K ditempatkan di bawah layer titik (Fishing Effort, Landing Site, Homeport, Logbook, Master Fishing Ground) sehingga lingkaran marker data perikanan tidak pernah tertutup oleh poligon zonasi.

---

## 5. Desain Popup & Protokol Hukum Netral

Saat poligon RZWP3K diklik, MapLibre menampilkan popup interaktif dengan atribut:
1. **Nama Zona & Kode:** Misal: *Zona Perikanan Tangkap* (`KPU-PT`).
2. **Kawasan Utama & Subzona:** Klasifikasi peruntukan ruang.
3. **Luas Wilayah:** Ditampilkan dalam hektar ($\text{ha}$) atau *"Tidak tersedia"* jika null (tidak menampilkan angka `0`).
4. **Status & Sumber Data:** Otoritas pengelola data.
5. **Dasar Hukum:** `Qanun Aceh Nomor 1 Tahun 2020`.
6. **Disclaimer Hukum Wajib:**
   > *"Informasi ini merupakan informasi zonasi spasial. Tampilan spasial tidak dengan sendirinya menentukan status legalitas suatu aktivitas atau kapal."*

---

## 6. Penanganan Empty State

Ketika layer RZWP3K diaktifkan sementara data vektor poligon pada database masih `NULL`, sistem memunculkan pemberitahuan responsif:
> **🗺️ RZWP3K Aceh (Qanun 1/2020)**
> *Data geometri resmi RZWP3K belum tersedia untuk ditampilkan pada peta. Katalog 15 zona telah aktif dalam sistem. Poligon batas zonasi akan tampil secara otomatis setelah file vektor resmi terverifikasi dan diimpor.*

---

## 7. Keamanan & Isolasi Domain

1. **Proteksi Rahasia:** API `GET /api/rzwp3k/zones` menggunakan serialisasi eksplisit. Tidak ada environment variables, DB password, App Key, atau GFW API Key yang terekspos.
2. **Isolasi Fisheries:** Modul `fishermen`, `vessels`, `trips`, `efforts`, `catches`, `landings`, `fishing_grounds` 100% tidak tersentuh.
3. **Isolasi GFW:** Modul satelit Global Fishing Watch (API, service, controller, cache, layer) 100% tidak tersentuh.
4. **Larangan Kesimpulan Otomatis:** Tidak ada matching otomatis antara kapal/trip dengan zona RZWP3K.

---

## 8. Ringkasan Pengujian

```text
Feature Test Suite: tests/Feature/Rzwp3kMapLibreApiTest.php
✓ api rzwp3k zones endpoint returns 200 and feature collection
✓ api rzwp3k zones excludes null geometry records
✓ api rzwp3k zones filter by zone type
✓ api rzwp3k zones does not expose secrets
✓ statistik page renders rzwp3k layer toggle and maplibre markup

Total Tests Passed: 19 RZWP3K Tests (84 assertions), 30 Regression Tests (237 assertions).
```
