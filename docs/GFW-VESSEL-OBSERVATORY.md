# GFW Vessel Observatory & Operational Dashboard — ZEE Aceh

Sistem integrasi observasi maritim perikanan tangkap untuk memantau pergerakan kapal, aktivitas penangkapan, perjumpaan, dan persinggahan pelabuhan di Wilayah Pengelolaan Perikanan Negara Republik Indonesia (WPPNRI) dan Zona Ekonomi Eksklusif (ZEE) Indonesia kawasan perairan Aceh.

---

## 1. Arsitektur Sistem

```text
Badan Informasi Geospasial (BIG)
      │
      ▼
Polygon ZEE Indonesia - Kawasan Aceh (storage/app/private/gfw/zee-indonesia-aceh.geojson, EPSG:4326)
      │
      ▼
Global Fishing Watch (GFW) API Gateway (Backend-Only)
 ├── /v3/events (Apparent Fishing, Encounters, Loitering, Port Visits)
 └── /v3/vessels/{id}/tracks (Posisi & Lintasan AIS/VMS)
      │
      ▼
Laravel Backend Services
 ├── AoiService (Validasi Boundary BIG ZEE Aceh, GeoJSON caching)
 ├── GFWService (Kueri spasial, normalisasi, deduplikasi vessel unik, kalkulasi umur data)
 ├── GfwActivityService (Ekstraksi lintasan historis & fallback presence)
 ├── GFWController & GfwVesselMonitoringController
 └── Caching & Rate Limiting (Single endpoint gateway untuk frontend)
      │
      ▼
Frontend Interactive UI (MapLibre GL JS & Tailwind CSS)
 ├── /gfw/vessels (Observatorium Kapal, Peta ZEE, Live Monitoring, Profil Intelijen)
 └── /gfw/dashboard (Pusat Kendali Operasional, 6 KPI Cards, Layer Controls, Multi-source Map)
```

---

## 2. Sumber Batas ZEE: BIG (Badan Informasi Geospasial)

- **Peran**: Boundary / Geospatial Source resmi wilayah yurisdiksi maritim Indonesia kawasan Aceh.
- **Lokasi Berkas**: `storage/app/private/gfw/zee-indonesia-aceh.geojson`
- **Spesifikasi Geospasial**:
  - `ID`: `zee-indonesia-aceh`
  - `Nama`: ZEE Indonesia - Kawasan Aceh
  - `Sumber`: BIG (Badan Informasi Geospasial)
  - `Sistem Referensi Koordinat (CRS)`: `EPSG:4326` (WGS84)
- **Integritas Spasial**: Sistem tidak menggunakan perkiraan radius melingkar ataupun bounding box semu, melainkan poligon koordinat batas kontinental terluar ZEE Aceh resmi dari BIG.

---

## 3. Sumber Data Vessel: Global Fishing Watch (GFW)

- **Peran**: Sumber observasi transmisi posisi kapal (AIS / VMS) dan deteksi algoritma event maritim.
- **Batasan Terminologi**: Data kapal **bukan** berasal dari BIG. BIG menyediakan batas laut, sedangkan observasi kapal dan aktivitas di perairan disediakan oleh Global Fishing Watch.
- **Taksonomi Kapal Resmi**:
  - `Fishing`, `Carrier`, `Support`, `Bunker`, `Tanker`, `Cargo`, `Passenger`, `Recreational`, `Other`, `Unknown`.

---

## 4. Pemisahan Vessel Presence vs GFW Events

Sistem membedakan secara tegas dua domain data:

### A. Vessel Presence / Position
- Mewakili identitas fisik kapal yang terdeteksi di dalam batas ZEE Aceh.
- Metrik: `Detected Vessels` (Jumlah Kapal Terdeteksi).
- **Deduplikasi Unik**: Satu kapal fisik dengan banyak transmisi koordinat atau banyak event dalam periode yang sama **tetap dihitung sebagai 1 kapal** (`vesselsById` unik berdasarkan canonical GFW ID, MMSI/SSVID, atau IMO).

### B. GFW Events
- Mewakili aktivitas spesifik yang diklasifikasikan oleh model GFW:
  - **Fishing Activity** (*Apparent Fishing*)
  - **Encounter Event** (*Perjumpaan antar-kapal di laut*)
  - **Loitering Event** (*Perilaku melayang/berkeliling dengan kecepatan rendah*)
  - **Port Visit** (*Persinggahan pelabuhan*)
- Metrik event dihitung secara terpisah dan **tidak menduplikasi** jumlah kapal.

---

## 5. Histori Lintasan Kapal (Vessel Track)

- **Endpoint**: `GET /api/gfw/vessels/{vessel}/track?start_date=YYYY-MM-DD&end_date=YYYY-MM-DD`
- **Ketentuan Integritas**:
  - Posisi diurutkan secara strictly chronological: tertua (*oldest*) ke terbaru (*newest*).
  - Validasi koordinat ketat: $-180^\circ \le \text{longitude} \le 180^\circ$, $-90^\circ \le \text{latitude} \le 90^\circ$.
  - `GeoJSON LineString` hanya dihasilkan jika terdapat **minimal 2 titik posisi valid**.
  - Jika titik $< 2$, sistem tidak melakukan interpolasi koordinat semu, melainkan mengembalikan status `"Track data insufficient"`.

---

## 6. Klasifikasi Status & Umur Data (Data Age)

Status operasional diklasifikasikan berdasarkan selisih waktu observasi terakhir kapal terhadap waktu server (UTC):

| Status | Batas Rentang Waktu | Keterangan |
|---|---|---|
| **LIVE** | $\le 24\text{ jam}$ ($\le 86.400\text{ detik}$) | Observasi sangat baru (< 1 hari) |
| **RECENT** | $> 24\text{ jam}$ s.d. $\le 72\text{ jam}$ ($86.401 - 259.200\text{ detik}$) | Observasi baru dalam 3 hari terakhir |
| **STALE** | $> 72\text{ jam}$ ($> 259.200\text{ detik}$) atau tidak tersedia | Observasi lampau / data historis |

> **Catatan Interpretasi**: Status LIVE/RECENT mengindikasikan *"Detected recently / Last detected"*, bukan klaim absolut bahwa kapal berada di posisi tersebut secara instan tanpa latensi satelit.

---

## 7. Live Monitoring & Polling Terkendali

- **Fitur Toggle**: Sakelar ON/OFF pada `/gfw/vessels` dan `/gfw/dashboard`.
- **Interval Pembaruan**: Auto-refresh setiap **60 detik**.
- **Pembaruan Tanpa Reload**: Peta MapLibre, tabel kapal, feed aktivitas, dan KPI diperbarui dinamis via AJAX tanpa merestart DOM (`location.reload()` dilarang).
- **Pencegahan Timer Gandha**: Toggle ON/OFF membersihkan interval aktif sebelumnya (`clearInterval(liveTimer)`) sehingga dipastikan hanya ada **maksimal 1 active polling timer** per tab browser.

---

## 8. Pusat Kendali Operasional (Operational Dashboard)

- **Akses Halaman**: `/gfw/dashboard`
- **6 KPI Utama**:
  1. *Detected Vessels* (Armada unik terdeteksi)
  2. *Live / Recent* (Kapal dengan observasi $\le 72$ jam)
  3. *Fishing Activity* (Event penangkapan ikan)
  4. *Encounters* (Perjumpaan di laut)
  5. *Loitering* (Aktivitas mengapung / perlambatan)
  6. *Port Visits* (Kunjungan pelabuhan)
- **MapLibre Multi-Layer**:
  - Kontrol Layer independen: ZEE Aceh, Kapal, Lintasan (*Track*), Penangkapan, Perjumpaan, Loitering, Pelabuhan.
  - GeoJSON Clustering: Titik kapal digabungkan dalam kluster dengan nomor hitung, otomatis terurai menjadi marker individu saat peta diperbesar.
- **Feed Aktivitas & Alert Faktual**:
  - Menampilkan linimasa aktivitas terkini dengan koordinat dan nama kapal.
  - Alert berbasis data nyata dengan terminologi faktual (*"Fishing Activity detected"*, *"Encounter detected"*), tanpa membuat tuduhan kriminalitas (*non-accusatory*).

---

## 9. Resiliensi Data: Fallback "Last Successful Data"

Jika koneksi API upstream atau jaringan terputus saat auto-refresh atau filter:
- Sistem **tidak mengosongkan peta** atau menampilkan angka `0 kapal`.
- Sistem mempertahankan dataset terakhir yang berhasil dimuat (`lastSuccessfulDataset`).
- Menampilkan pesan notifikasi:
  > *"Gagal memperbarui data GFW. Menampilkan data terakhir yang berhasil diperoleh."*
- Menyediakan tombol interaktif **Coba Lagi (Retry)**.

---

## 10. Keamanan Kredensial (Token Security)

- **Backend-Only Access**: Kredensial API GFW (`services.gfw.token` / `gfw.api_key`) hanya dibaca dan digunakan oleh server PHP/Laravel.
- **Zero Leakage**: Token tidak pernah disematkan di Blade template, skrip JavaScript, respons JSON publik, query parameter browser, maupun pesan error log.
- **Sanitized Logging**: Log aplikasi hanya mencatat endpoint, kode status HTTP, dan ringkasan pesan error teknis tanpa menyertakan header otorisasi atau token.

---

## 11. Batasan & Limitasi yang Diketahui (Known Limitations)

1. **Batas Rentang Waktu Kueri (7-Day Limitation)**:
   - Sesuai spesifikasi dan kuota API Global Fishing Watch, endpoint analitik dibatasi maksimal rentang 7 hari per kueri.
2. **Latensi Data Satelit**:
   - Terdapat latensi alami pemrosesan AIS/VMS satelit (antara beberapa jam hingga hitungan hari tergantung ketersediaan orbit satelit penerima).
3. **Penyajian Zona Waktu**:
   - Seluruh data internal disimpan dan dihitung dalam UTC (standar GFW).
   - Antarmuka pengguna (UI) mengonversi dan menampilkan waktu lokal secara eksplisit sebagai **WIB (UTC+7)** untuk kenyamanan petugas operasional di Aceh.
