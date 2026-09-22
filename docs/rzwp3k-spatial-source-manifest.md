# RZWP3K Aceh Spatial Source Manifest

```text
Document: Spatial Data Source Registry & Verification Manifest
Stage: 18.3 — GeoJSON / WMS Data Integration Audit
Date: 21 September 2026
Status: Verified Legal & Catalog Baseline / Vector Geometry Safe Default (NULL)
Target CRS: EPSG:4326 (WGS84 [longitude, latitude])
```

---

## 1. Dataset Overview

* **Dataset Name:** RZWP3K Provinsi Aceh Tahun 2020–2040
* **Legal Basis:** Qanun Aceh Nomor 1 Tahun 2020 tentang Rencana Zonasi Wilayah Pesisir dan Pulau-Pulau Kecil Aceh Tahun 2020–2040
* **Primary Authority:** Pemerintah Aceh / Dewan Perwakilan Rakyat Aceh (DPRA)
* **Technical Managing Agency:** Dinas Kelautan dan Perikanan (DKP) Provinsi Aceh & Bappeda Aceh
* **Target CRS:** EPSG:4326 (WGS84 Geodetic Decimal Degrees `[longitude, latitude]`)
* **Storage Location (Internal):** `storage/app/rzwp3k/` (Strictly private, never public)

---

## 2. Spatial Sources Registry

| Source Key | Authority | Type | URL / Endpoint | Layer / File | CRS | Format | Verification Status |
| :--- | :--- | :---: | :--- | :---: | :---: | :---: | :---: |
| `official_qanun` | Pemerintah Aceh / DPRA | `legal_document` | `https://jdih.acehprov.go.id/produk-hukum/detail-peraturan/1897` | Naskah & Matriks Lampiran | EPSG:4326 | PDF / Scan Cetak | **VERIFIED** |
| `dkp_aceh_geoportal` | DKP Aceh / Satu Data Aceh | `geojson_url` / `shapefile` | *(Pending official PPID distribution)* | RZWP3K Aceh Vektor | EPSG:4326 | GeoJSON / SHP | **PENDING ATTACHMENT** |
| `kkp_sigap_wms` | Kementerian Kelautan dan Perikanan | `wms` | *(Reference only)* | Penataan Ruang Laut | EPSG:4326 | Raster PNG/WMS | **REFERENCE ONLY** |

---

## 3. Verification Protocol & Checklist

### A. Authority Verification
* [x] Legal authority verified: Pemerintah Aceh & DPRA melalui pengundangan Qanun Aceh No. 1 Tahun 2020.
* [x] Technical authority verified: Dinas Kelautan dan Perikanan Aceh selaku instansi pengelola teknis perikanan dan kelautan.

### B. Dataset Identity & Classification
* [x] Struktur 4 Kawasan Utama terverifikasi:
  1. Kawasan Pemanfaatan Umum (KPU)
  2. Kawasan Konservasi (KK)
  3. Alur Laut (AL)
  4. Kawasan Strategis Nasional Tertentu (KSNT)
* [x] 15 Subzona katalog resmi terverifikasi dan ditanam pada tabel `rzwp3k_zones`.

### C. CRS & Coordinate Integrity
* [x] Standar spasial internal ditetapkan secara tegas pada **EPSG:4326 (WGS84)** dengan susunan `[longitude, latitude]` sesuai RFC 7946.
* [x] Larangan transformasi diam-diam (*silent reprojection*). Apabila sumber data menggunakan proyeksi UTM (Zone 46N/47N), metode transformasi harus didokumentasikan dan diverifikasi sebelum diimpor.

### D. Geometry Validation & Zero-Fake Data Policy
* [x] Validasi struktur geometri menggunakan `App\Services\Rzwp3k\Rzwp3kGeoJsonValidator`:
  * Validasi GeoJSON Feature & FeatureCollection
  * Validasi Polygon dan MultiPolygon
  * Validasi Ring Closure (koordinat awal = koordinat akhir)
  * Validasi batas koordinat derajat bumi ($-180 \le \text{lng} \le 180$, $-90 \le \text{lat} \le 90$)
* [x] **Zero Fake Polygon Guarantee:** Geometri pada database `rzwp3k_zones` tetap bernilai `NULL` hingga dataset vektor resmi GeoJSON diimpor melalui command Artisan terverifikasi.

---

## 4. Attribute Mapping Strategy

Ketika dataset vektor GeoJSON/Shapefile resmi dilampirkan, pemetaan atribut akan disesuaikan dengan skema tabel `rzwp3k_zones`:

| Target Table Field (`rzwp3k_zones`) | Source GeoJSON Property Candidates | Description & Handling |
| :--- | :--- | :--- |
| `code` | `KODE_ZONA`, `KODE_SUBZONA`, `ZONE_CODE`, `code` | Unique official zone code |
| `name` | `NAMA_ZONA`, `NAMA_SUBZONA`, `NAME`, `name` | Official zone name |
| `zone_type` | `KAWASAN`, `JENIS_ZONA`, `ZONE_TYPE`, `type` | Enum: `KPU`, `KK`, `KSNT`, `AL` |
| `subzone_type` | `SUBZONA`, `SUB_ZONA`, `SUBZONE` | Standard classification string |
| `area_ha` | `LUAS_HA`, `AREA_HA`, `HECTARES`, `area` | Official area in hectares |
| `geometry` | `geometry` (GeoJSON Polygon / MultiPolygon) | Validated via `Rzwp3kGeoJsonValidator` |

---

## 5. Catalog Matching Analysis (15 Zona Resmi)

| Catalog Code | Subzone Name | Zone Type | GIS Vector Match Status | Provenance & Notes |
| :--- | :--- | :---: | :---: | :--- |
| `KPU-PT` | Perikanan Tangkap | KPU | `PENDING_VECTOR_ATTACHMENT` | Qanun 1/2020 Lampiran Batas |
| `KPU-PB` | Perikanan Budidaya | KPU | `PENDING_VECTOR_ATTACHMENT` | Qanun 1/2020 Lampiran Batas |
| `KPU-W` | Pariwisata Bahari | KPU | `PENDING_VECTOR_ATTACHMENT` | Qanun 1/2020 Lampiran Batas |
| `KPU-PL` | Pelabuhan Laut & Perikanan | KPU | `PENDING_VECTOR_ATTACHMENT` | Qanun 1/2020 Lampiran Batas |
| `KPU-PK` | Pemukiman Pesisir | KPU | `PENDING_VECTOR_ATTACHMENT` | Qanun 1/2020 Lampiran Batas |
| `KPU-HM` | Hutan Mangrove / Ekosistem Pesisir | KPU | `PENDING_VECTOR_ATTACHMENT` | Qanun 1/2020 Lampiran Batas |
| `KPU-E` | Energi Migas & EBT | KPU | `PENDING_VECTOR_ATTACHMENT` | Qanun 1/2020 Lampiran Batas |
| `KPU-IM` | Industri Maritim | KPU | `PENDING_VECTOR_ATTACHMENT` | Qanun 1/2020 Lampiran Batas |
| `KPU-PG` | Penggaraman Rakyat | KPU | `PENDING_VECTOR_ATTACHMENT` | Qanun 1/2020 Lampiran Batas |
| `KK-KKP` | Kawasan Konservasi Perairan Daerah | KK | `PENDING_VECTOR_ATTACHMENT` | Qanun 1/2020 Lampiran Batas |
| `KK-KKL` | Kawasan Konservasi Pesisir & Pulau | KK | `PENDING_VECTOR_ATTACHMENT` | Qanun 1/2020 Lampiran Batas |
| `KK-TWA` | Taman Wisata Alam Perairan | KK | `PENDING_VECTOR_ATTACHMENT` | Qanun 1/2020 Lampiran Batas |
| `KK-SP` | Suaka Perikanan / Zona Inti | KK | `PENDING_VECTOR_ATTACHMENT` | Qanun 1/2020 Lampiran Batas |
| `AL-P` | Alur Pelayaran Kapal | AL | `PENDING_VECTOR_ATTACHMENT` | Qanun 1/2020 Lampiran Batas |
| `AL-PK` | Koridor Kabel & Pipa Bawah Laut | AL | `PENDING_VECTOR_ATTACHMENT` | Qanun 1/2020 Lampiran Batas |
| `AL-M` | Alur Migrasi Biota Laut | AL | `PENDING_VECTOR_ATTACHMENT` | Qanun 1/2020 Lampiran Batas |
| `KSNT-PPKT`| Pulau-Pulau Kecil Terluar Perbatasan | KSNT | `PENDING_VECTOR_ATTACHMENT` | Qanun 1/2020 Lampiran Batas |

---

## 6. Known Limitations & Audit Findings

1. **Ketersediaan Endpoint Publik Direct:** Data spasial vektor resmi RZWP3K Aceh berstatus data sektoral pemerintah daerah yang membutuhkan permohonan melalui Pejabat Pengelola Informasi dan Dokumentasi (PPID) DKP Aceh atau login Satu Data Aceh. Tidak ditemukan unauthenticated direct public GeoJSON API URL yang sah tanpa autentikasi.
2. **Integritas Database Terjaga:** Sesuai prinsip *Zero Fake Data*, kolom `geometry` pada `rzwp3k_zones` tetap bernilai `NULL` sampai file vektor resmi diperoleh dan diimpor.
3. **WMS Policy:** Jika layer WMS dihubungkan di masa depan, layer tersebut hanya digunakan sebagai peta referensi visual (*raster reference layer*), bukan sebagai master geometri tabular.

---

## 7. Import & Operational Status

```text
Status: VERIFIED LEGAL BASELINE & CATALOG READY
Geometry Attachment: PENDING OFFICIAL VECTOR ATTACHMENT
Importer Command: php artisan rzwp3k:import <file> (--dry-run / --update)
Audit Command: php artisan rzwp3k:source-audit (--ping)
```
