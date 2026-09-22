# REST API Documentation
**Sistem Informasi & Statistik Perikanan Tangkap Provinsi Aceh**

```text
System: Sistem Informasi Perikanan Tangkap Aceh
Version: 1.0.0
Documentation Version: 1.0.0
Date: 21 September 2026
Status: Final Approved (F9 Audit Compliant)
```

---

## 1. Overview & General Principles

Sistem Informasi Perikanan Aceh menyediakan API RESTful untuk konsumsi frontend internal dan diseminasi data spasial publik.

* **Base URL:** `http://127.0.0.1:8000` (Local Development) / `https://perikanan.acehprov.go.id` (Production)
* **Format Payload:** JSON (`Content-Type: application/json; charset=utf-8`)
* **Standard Status Code:**
  * `200 OK`: Permintaan berhasil diproses.
  * `400 Bad Request`: Parameter query tidak valid.
  * `401 Unauthorized`: Token/sesi tidak valid atau belum login.
  * `403 Forbidden`: Hak akses tidak mencukupi.
  * `429 Too Many Requests`: Melebihi ambang batas rate limiting.
  * `500 Internal Server Error`: Kesalahan internal server.

---

## 2. Public GIS Spatial API

### Endpoint: `GET /gis/data`
Mengambil dataset geospasial lengkap untuk 7 layer visualisasi MapLibre GL JS beserta ringkasan analisis WPP.

* **Method:** `GET`
* **Authentication:** Publik (Tidak membutuhkan API Key atau autentikasi login)
* **Rate Limiting:** `throttle:60,1` (Maksimal 60 request per menit per IP)

#### Query Parameters

| Parameter | Tipe Data | Wajib? | Default | Deskripsi |
| :--- | :---: | :---: | :---: | :--- |
| `year` / `tahun` | Integer | Tidak | `null` | Filter tahun keberangkatan kapal (e.g., `2026`) |
| `month` / `bulan` | Integer | Tidak | `null` | Filter bulan keberangkatan ($1–12$) |
| `start_date` | Date (`Y-m-d`)| Tidak | `null` | Tanggal awal rentang waktu |
| `end_date` | Date (`Y-m-d`)| Tidak | `null` | Tanggal akhir rentang waktu |
| `species_id` / `species`| Integer | Tidak | `null` | ID referensi spesies ikan (`species.id`) |
| `gear_id` / `gear` | Integer | Tidak | `null` | ID referensi alat tangkap (`fishing_gears.id`) |
| `landing_site_id` / `site` | Integer | Tidak | `null` | ID pelabuhan pangkalan (`landing_sites.id`) |
| `wppnri_id` / `wilayah` | Integer | Tidak | `null` | ID wilayah WPP (`wppnri.id`) |
| `regency_id` | Integer | Tidak | `null` | ID kabupaten/kota (`regencies.id`) |

#### Actual JSON Response Schema

```json
{
  "center": {
    "lat": 5.55,
    "lng": 95.32
  },
  "ports": [
    {
      "id": 1,
      "name": "PPS Lampulo",
      "code": "PPS-LMP",
      "type": "PPS",
      "lat": 5.5843,
      "lng": 95.3278,
      "regency": "Kota Banda Aceh",
      "province": "Aceh",
      "address": "Kuta Alam, Banda Aceh",
      "vessels_count": 12,
      "landings_count": 45,
      "layer": "ports",
      "layer_label": "Pelabuhan / TPI"
    }
  ],
  "fishing_grounds": [],
  "master_fishing_grounds": [
    {
      "id": 1,
      "name": "Perairan Sabang - Pulo Rondo",
      "code": "FG-01",
      "wpp_code": "WPP 572",
      "wpp_name": "Samudera Hindia",
      "lat": null,
      "lng": null,
      "has_coordinates": false,
      "geometry_status": "Belum Tersedia Geometri Resmi (Tanpa Titik Palsu)",
      "description": "Daerah penangkapan pelagis besar di utara Sabang",
      "trips_count": 8,
      "layer": "fishing_grounds",
      "layer_label": "Master Fishing Ground"
    }
  ],
  "efforts": [
    {
      "id": 101,
      "trip_code": "TRIP-2026-0012",
      "vessel": "KM Bahari Jaya",
      "captain": "Teuku Johan",
      "gear": "Purse Seine Pelagis Kecil",
      "port": "PPS Lampulo",
      "regency": "Kota Banda Aceh",
      "wpp_code": "571",
      "wpp_name": "Selat Malaka",
      "setting_num": 1,
      "lat_setting": 5.7214,
      "lng_setting": 95.4521,
      "lat_hauling": null,
      "lng_hauling": null,
      "setting_time": "12 Mar 2026, 04:30",
      "duration_hours": 3.5,
      "catch_kg": 450.0,
      "species_list": [
        {
          "name": "Cakalang",
          "scientific": "Katsuwonus pelamis",
          "weight_kg": 300.0
        },
        {
          "name": "Tongkol Komo",
          "scientific": "Euthynnus affinis",
          "weight_kg": 150.0
        }
      ],
      "layer": "efforts",
      "layer_label": "Fishing Effort",
      "note": "Koordinat Setting Alat Tangkap Aktual"
    }
  ],
  "vessels": [
    {
      "id": 15,
      "name": "KM Aceh Sejahtera",
      "registration": "GT.25 No.102/Ac",
      "type": "Kapal Motor",
      "gt": 24.5,
      "gear": "Pukat Cincin",
      "homeport": "PPS Lampulo",
      "regency": "Kota Banda Aceh",
      "lat": 5.5843,
      "lng": 95.3278,
      "layer": "vessels",
      "layer_label": "Homeport Kapal",
      "note": "Pangkalan Kapal Terdaftar (Bukan Live Tracking)"
    }
  ],
  "logbooks": [
    {
      "id": 44,
      "trip_code": "TRIP-2026-0005",
      "vessel": "KM Bintang Samudera",
      "port": "PPN Idi",
      "lat": 5.1205,
      "lng": 97.8921,
      "date": "08 Mar 2026",
      "time": "14:00:00",
      "weather": "Cerah Berawan",
      "wave_height": 0.8,
      "activity": "Melakukan penarikan jaring (hauling)",
      "layer": "logbooks",
      "layer_label": "Titik Logbook Historis",
      "note": "Catatan Operasional Historis (Bukan Posisi Real-time)"
    }
  ],
  "wpp_analysis": [
    {
      "id": 1,
      "name": "Selat Malaka dan Laut Andaman",
      "code": "571",
      "catch_kg": 12450.0,
      "catch_ton": 12.45,
      "trips": 22,
      "efforts": 10,
      "duration_hours": 42.0,
      "cpue": 565.9,
      "cpue_trip": 565.9,
      "cpue_hourly": 296.43,
      "top_species": [
        {
          "name": "Cakalang",
          "weight_kg": 6200.0
        }
      ]
    },
    {
      "id": 2,
      "name": "Samudera Hindia sebelah Barat Sumatera",
      "code": "572",
      "catch_kg": 18200.0,
      "catch_ton": 18.2,
      "trips": 23,
      "efforts": 0,
      "duration_hours": 0.0,
      "cpue": 791.3,
      "cpue_trip": 791.3,
      "cpue_hourly": 0.0,
      "top_species": [
        {
          "name": "Madidihang",
          "weight_kg": 9800.0
        }
      ]
    }
  ],
  "counts": {
    "ports": 12,
    "landing_sites": 12,
    "fishing_grounds": 0,
    "master_fishing_grounds": 8,
    "fishing_grounds_total": 8,
    "fishing_grounds_unmapped": 8,
    "efforts": 10,
    "fishing_efforts": 10,
    "vessels": 19,
    "homeports": 19,
    "logbooks": 29,
    "logbook_points": 29
  }
}
```

---

## 3. Global Fishing Watch (GFW) Internal Gateway API

Seluruh akses ke data satelit Global Fishing Watch dialirkan melalui rute server Laravel terenkripsi:

* **Prefix:** `/api/gfw`
* **Middleware:** `throttle:gfw-api` (60 request per menit)

| Method | Endpoint | Deskripsi |
| :--- | :--- | :--- |
| `GET` | `/api/gfw/health` | Pemeriksaan konektivitas server Laravel ke API Global Fishing Watch |
| `GET` | `/api/gfw/vessels` | Pencarian profil kapal satelit berdasarkan MMSI, IMO, atau nama |
| `GET` | `/api/gfw/vessels/{id}` | Detail profil kapal tertentu dari GFW |
| `GET` | `/api/gfw/regions` | Daftar region maritim terdaftar (Indonesia, Perairan Aceh) |
| `GET` | `/api/gfw/activity` | Data raster/vektor kepadatan aktivitas kapal (*vessel presence*) |
| `GET` | `/api/gfw/events` | Seluruh rekaman anomali/event AIS kapal di laut |
| `GET` | `/api/gfw/events/fishing` | Rekaman indikasi aktivitas penangkapan ikan (*Apparent Fishing Events*) |
| `GET` | `/api/gfw/events/encounters`| Rekaman pertemuan dua kapal di tengah laut (*Vessel Encounters*) |
| `GET` | `/api/gfw/events/loitering` | Rekaman kapal berdiam/bergerak lambat (*Loitering Events*) |
| `GET` | `/api/gfw/events/port-visits` | Rekaman riwayat kunjungan kapal ke pelabuhan (*Port Visits*) |
