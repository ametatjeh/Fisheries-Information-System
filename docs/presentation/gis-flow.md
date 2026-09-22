# Alur Spasial WebGIS (GIS Flow & Architecture)
**Sistem Informasi & Statistik Perikanan Tangkap Provinsi Aceh**

```text
System: Sistem Informasi Perikanan Tangkap Aceh
Stage: F11 — GIS Flow & Architecture
Document Type: Spatial Architecture
Date: 21 September 2026
```

---

## 1. Alur Pipeline Geospasial

```text
┌─────────────────────────────────────────────────────────────┐
│ 1. DATA SOURCE (MYSQL DATABASE)                             │
│ - landing_sites: latitude, longitude                        │
│ - fishing_efforts: latitude_setting, longitude_setting      │
│ - vessels: homeportSite -> latitude, longitude              │
│ - logbooks: latitude, longitude                             │
│ - wppnri: wpp_571, wpp_572 (GeoJSON MultiPolygon)           │
└──────────────────────────────┬──────────────────────────────┘
                               │
                               ▼
┌─────────────────────────────────────────────────────────────┐
│ 2. LARAVEL SPATIAL CONTROLLER (GisController)               │
│ - Ekstraksi Query Filter (Tahun, Komoditas, Alat, Wilayah)  │
│ - Filter Nilai Valid: -90<=lat<=90, -180<=lng<=180          │
│ - Zero Fake Point Protection: Nilai NULL tidak diplot       │
│ - Konstruksi JSON Payload Respons Terstruktur               │
└──────────────────────────────┬──────────────────────────────┘
                               │
                               ▼
┌─────────────────────────────────────────────────────────────┐
│ 3. REST API ENDPOINT: GET /gis/data                         │
│ - Mengembalikan Objek JSON Lengkap:                         │
│   { center, ports, efforts, vessels, logbooks, wpp_analysis }│
└──────────────────────────────┬──────────────────────────────┘
                               │
                               ▼
┌─────────────────────────────────────────────────────────────┐
│ 4. FRONTEND TRANSFORMATION & MAP RENDERING                  │
│ - MapLibre GL JS v4.7.1 WebGL Canvas                        │
│ - Format Koordinat GeoJSON Standar RFC 7946: [lng, lat]     │
│ - Pemetaan 7 Layer Spasial & Kontrol Interaktif             │
└─────────────────────────────────────────────────────────────┘
```

---

## 2. Rincian 7 Layer Spasial & Simbologi Warna

| Layer ID | Nama Layer | Geometri | Simbol / Warna | Atribut Popup |
| :--- | :--- | :---: | :--- | :--- |
| `efforts` | **Fishing Effort** | Point | 🟠 Lingkaran Oranye | Nama Kapal, Nahkoda, Alat Tangkap, Durasi Jam, Tangkapan (kg) |
| `ports` | **Landing Site** | Point | 🔵 Pin Biru | Nama Pelabuhan, Klasifikasi (PPS/PPN/PPI/TPI), Kabupaten, Jumlah Kapal |
| `vessels` | **Homeport Kapal** | Point | 🟣 Ikon Ungu | Nama Kapal, No. Registrasi, Tipe Kapal, Ukuran GT, Alat Tangkap Utama |
| `logbooks` | **Logbook Historis** | Point | 🟡 Titik Kuning | Kode Trip, Nama Kapal, Tanggal/Jam, Kondisi Cuaca, Tinggi Gelombang |
| `fishing_grounds` | **Master Fishing Ground** | List | ⚪ Panel Info | Nama Daerah Tangkapan, Deskripsi, Status Geometri Resmi |
| `wpp_571` | **WPPNRI 571** | Polygon | 🔷 Arsiran Biru Muda | Selat Malaka & Laut Andaman (Total Trip, Produksi, CPUE) |
| `wpp_572` | **WPPNRI 572** | Polygon | 🟩 Arsiran Hijau Muda | Samudera Hindia Barat Sumatera (Total Trip, Produksi, CPUE) |
