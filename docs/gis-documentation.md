# Geographic Information System (GIS) Documentation
**Sistem Informasi & Statistik Perikanan Tangkap Provinsi Aceh**

```text
System: Sistem Informasi Perikanan Tangkap Aceh
Version: 1.0.0
Documentation Version: 1.0.0
Date: 21 September 2026
Status: Final Approved (F9 Audit Compliant)
```

---

## 1. GIS Architecture & Data Flow

Sistem Informasi Geografis Perikanan Aceh menyajikan data spasial secara dinamis menggunakan **MapLibre GL JS 4.7.1** yang terhubung langsung ke REST API internal Laravel tanpa perantara server GIS terpisah (*headless GeoJSON pipeline*).

```text
┌─────────────────────────────────────────────────────────────┐
│                      DATABASE MYSQL                         │
│  - landing_sites (latitude, longitude)                      │
│  - fishing_efforts (latitude_setting, longitude_setting)    │
│  - vessels -> homeportSite (latitude, longitude)            │
│  - logbooks (latitude, longitude)                           │
│  - fishing_grounds (master list / description)              │
│  - wppnri (WPP 571 & 572 definitions)                       │
└──────────────────────────────┬──────────────────────────────┘
                               │ Eloquent ORM Builder
                               ▼
┌─────────────────────────────────────────────────────────────┐
│             LARAVEL CONTROLLER: GisController               │
│  - Endpoint: GET /gis/data                                  │
│  - Filter Parameter Sanitization                            │
│  - Zero Synthetic Coordinate Guard                          │
│  - GeoJSON Serialization                                    │
└──────────────────────────────┬──────────────────────────────┘
                               │ JSON Payload Response
                               ▼
┌─────────────────────────────────────────────────────────────┐
│                 FRONTEND JAVASCRIPT CLIENT                  │
│  - MapLibre GL JS v4.7.1 Engine                             │
│  - CartoDB Positron / OSM Raster Basemap Tiles              │
│  - Coordinate Transformation: [lng, lat] GeoJSON standard   │
│  - 7 Interactive Layers with Filter & Popup Controls        │
└─────────────────────────────────────────────────────────────┘
```

---

## 2. GeoJSON Standard & Coordinate Order

> [!IMPORTANT]
> **Standar Urutan Koordinat GeoJSON:**
> Mengikuti spesifikasi resmi **RFC 7946 (GeoJSON)**, seluruh titik spasial pada format GeoJSON dan MapLibre GL JS menggunakan urutan:
> $$\mathbf{[\text{longitude},\ \text{latitude}]}$$
> Hal ini berbeda dengan representasi teks konvensional $(\text{latitude}, \text{longitude})$. Frontend JavaScript sistem secara otomatis memetakan atribut database `(lat, lng)` menjadi `[lng, lat]` sebelum dimasukkan ke dalam `map.addSource()`.

---

## 3. Seven (7) Interactive GIS Layers

| No | Layer Name | Source Table | Geometry Type | Layer Style / Marker | Deskripsi & Atribut Popup |
| :-: | :--- | :--- | :---: | :--- | :--- |
| **1** | **Fishing Effort** | `fishing_efforts` | Point | Lingkaran Oranye / Jingga | Titik setting alat tangkap aktual di laut. Popup: Nama Kapal, Nahkoda, Alat Tangkap, Jam Setting, Durasi Operasi, dan Tangkapan (kg). |
| **2** | **Landing Site** | `landing_sites` | Point | Ikon Pin Biru / Cyan | Pelabuhan perikanan & TPI aktif di pesisir Aceh. Popup: Nama Pelabuhan, Tipe (PPS/PPN/PPI/TPI), Kabupaten, Jumlah Armada Terdaftar, dan Total Pendaratan. |
| **3** | **Homeport Kapal** | `vessels` $\rightarrow$ `landing_sites` | Point | Ikon Kapal Ungu / Indigo | Pangkalan pelabuhan terdaftar armada kapal perikanan Aceh. Popup: Nama Kapal, Nomor Registrasi, Tipe Kapal, Ukuran GT, dan Alat Tangkap Utama. |
| **4** | **Logbook Historis** | `logbooks` | Point | Titik Kuning / Amber | Rekam jejak operasional harian kapal masa lalu. Popup: Kode Trip, Nama Kapal, Tanggal, Jam, Cuaca, Tinggi Gelombang, dan Deskripsi Aktivitas. |
| **5** | **Master Fishing Ground**| `fishing_grounds` | Informational List | Panel Ringkasan | Daftar acuan daerah penangkapan ikan di Aceh. Ditampilkan informatif karena belum memiliki poligon batas resmi. |
| **6** | **WPPNRI 571** | `wppnri` | MultiPolygon | Poligon Transparan Biru Laut | Wilayah Pengelolaan Perikanan Selat Malaka & Laut Andaman (pesisir timur-utara Aceh). Popup: Ringkasan Produksi, Trip, dan CPUE. |
| **7** | **WPPNRI 572** | `wppnri` | MultiPolygon | Poligon Transparan Hijau Laut | Wilayah Pengelolaan Perikanan Samudera Hindia Barat Sumatera (pesisir barat-selatan Aceh). Popup: Ringkasan Produksi, Trip, dan CPUE. |

---

## 4. Layer Filtering & Interactive Features

Antarmuka GIS mendukung filter multi-kriteria yang dapat diaplikasikan bersamaan:
* **Filter Tahun & Bulan:** Memfilter data titik *effort*, *logbook*, dan analisis WPP berdasarkan tanggal pelayaran.
* **Filter Komoditas Ikan (`species_id`):** Menampilkan titik *effort* yang berhasil menangkap komoditas yang dipilih.
* **Filter Alat Tangkap (`gear_id`):** Menampilkan sebaran operasi alat tangkap tertentu (e.g., Pukat Cincin vs Rawai).
* **Filter Wilayah Kabupaten & Pelabuhan Pangkalan:** Menyorot operasi armada dari kabupaten/pelabuhan tertentu.
* **Layer Toggles:** Pengguna dapat mengaktifkan atau menonaktifkan masing-masing dari 7 layer secara independen melalui panel kontrol layer di pojok kanan atas peta.

---

## 5. Empirical GIS Data Limitations (F9 Audit Compliant)

Sistem Informasi Perikanan memegang prinsip integritas data ilmiah di atas estetika visual:

1. **Fishing Effort Tanpa Koordinat:**
   * Dari total 62 data *fishing effort* yang tercatat, **10 data memiliki koordinat GPS presisi** dan diplot di peta.
   * Sebanyak **52 data dicatat tanpa GPS** (hanya durasi & alat tangkap). Sistem **TIDAK** memplot koordinat palsu di laut atau di titik `0,0`, melainkan tetap mencatatnya secara transparan pada ringkasan statistik.
2. **Master Fishing Grounds (Status Geometri Resmi):**
   * 8 entitas master Fishing Ground di database belum memiliki batas poligon resmi yang disahkan oleh instansi berwenang (DKP Aceh / KKP).
   * Sistem secara eksplisit memberikan status: *"Belum Tersedia Geometri Resmi (Tanpa Titik Palsu)"* dan tidak mengarang koordinat centroid fiktif.
3. **Pangkalan Kapal (Homeport) $\neq$ Live Satellite Tracking:**
   * Layer *Homeport Kapal* menunjukkan pelabuhan registrasi resmi kapal, bukan posisi satelit *real-time* kapal saat ini di laut.
4. **Logbook Historis $\neq$ Live AIS Tracking:**
   * Layer *Logbook Historis* menyajikan data rekam jejak pelayaran masa lalu untuk keperluan audit kepatuhan dan riset spasial, bukan transmiter radar waktu-nyata.
