# Kamus Data & Definisi Resmi Statistik Perikanan (Statistics Data Dictionary)
**Sistem Informasi & Tata Kelola Data Perikanan Tangkap Terpadu**  
*Dokumen Spesifikasi Resmi — Tahap 9.5*

---

## 1. Pendahuluan & Tujuan
Dokumen ini mendefinisikan secara resmi seluruh Key Performance Indicators (KPI), metrik visualisasi (Chart 1–8), aturan agregasi, relasi sumber data, semantik tanggal, serta penanganan filter pada modul ringkasan statistik publik (`/statistik`).

Tujuan kamus data ini adalah:
1. Menetapkan definisi tunggal yang terstandarisasi untuk setiap metrik perikanan.
2. Memisahkan secara tegas konsep **Tangkapan Lapangan (Catch)** dan **Pendaratan Pelabuhan (Landing)**.
3. Mencegah kesalahan agregasi data (misalnya duplikasi denominator durasi upaya tangkap pada CPUE).
4. Menjadi rujukan teknis baku bagi pengembang, analis data, verifikator, dan enumerator.

---

## 2. Perbedaan Fundamental: Total Catch vs Total Landing

| Dimensi | Total Catch (Tangkapan) | Total Landing (Pendaratan) |
| :--- | :--- | :--- |
| **Definisi Domain** | Estimasi bobot total ikan yang tertangkap di laut saat operasi penangkapan (logbook harian). | Bobot aktual ikan yang diturunkan/dibongkar dan ditimbang di pelabuhan perikanan / pangkalan pendaratan (PPI). |
| **Tabel Sumber** | `catches` | `landing_items` join `landings` |
| **Field Nilai** | `catches.weight_kg` | `landing_items.weight_kg` |
| **Field Tanggal** | `fishing_trips.departure_date` (atau setting date) | `landings.landing_date` |
| **Unit** | Kilogram (kg) | Kilogram (kg) |
| **Rantai Relasi** | `catches` → `fishing_efforts` → `fishing_trips` | `landing_items` → `landings` → `fishing_trips` |
| **Korelasi Data Demo** | Pada data simulasi demo (`DEMO_STATISTICS_2026`), total bobot `catches` (13.660 kg) dan `landing_items` (13.660 kg) adalah **identik**. |
| **Korelasi Data Riil/Legacy**| **Tidak selalu sama**. Ikan hasil tangkapan di laut dapat mengalami susut, dikonsumsi di kapal (crew consumption), dibuang (discard), atau didaratkan di luar pangkalan pantau resmi. |

> [!IMPORTANT]
> Seluruh metrik KPI dan grafik visualisasi Chart 1–4, 6–7 saat ini bersumber dari `catches.weight_kg`. Oleh karena itu, label antarmuka pengguna (UI) secara resmi menggunakan istilah **Total Catch** atau **Total Catch / Produksi Tangkapan (Kg)**, bukan *Total Landing*.

---

## 3. Definisi Resmi 4 Kartu KPI Ringkasan

### 3.1 Total Fishing Trip
- **Definisi**: Jumlah pelayaran penangkapan ikan unik yang tercatat dan memenuhi kriteria filter aktif.
- **Formula**: `COUNT(DISTINCT fishing_trips.id)`
- **Sumber Data**: `fishing_trips` (left join `landings`, `fishing_efforts`, `catches` sesuai filter)
- **Unit**: Trip / Pelayaran
- **Keterangan**: Penggunaan `DISTINCT` wajib diterapkan guna mencegah multiplikasi hitungan trip akibat join dengan relasi one-to-many (effort, catch, landing).

### 3.2 Total Vessel
- **Definisi**: Jumlah armada kapal perikanan aktif yang melakukan pelayaran penangkapan pada periode dan filter terpilih.
- **Formula**: `COUNT(DISTINCT fishing_trips.vessel_id)`
- **Sumber Data**: `fishing_trips`
- **Unit**: Kapal / Unit
- **Keterangan**: Satu kapal yang melakukan beberapa kali trip dalam rentang waktu yang sama dihitung tepat satu unit kapal aktif.

### 3.3 Total Catch
- **Definisi**: Akumulasi bobot kotor hasil tangkapan seluruh jenis ikan dari operasi penangkapan yang memenuhi filter aktif.
- **Formula**: `SUM(catches.weight_kg)`
- **Sumber Data**: `catches` join `fishing_efforts` join `fishing_trips`
- **Unit**: Kilogram (kg)
- **Keterangan**: Mewakili total volume tangkapan laut. Tidak mencakup orphan catch yang tidak memiliki asosiasi trip/effort valid.

### 3.4 Total Species
- **Definisi**: Keanekaragaman jenis ikan unik yang berhasil ditangkap berdasarkan taksonomi FAO ASFIS.
- **Formula**: `COUNT(DISTINCT catches.fish_species_id)`
- **Sumber Data**: `catches`
- **Unit**: Spesies / Jenis
- **Keterangan**: Dihitung berdasarkan `DISTINCT catches.fish_species_id`, bukan `COUNT(catches.id)`.

---

## 4. Definisi Resmi 8 Visualisasi Statistik (Chart 1–8)

### 4.1 Chart 1 — Total Catch / Produksi Tangkapan (Trend Bulanan)
- **Judul UI**: *Total Catch / Produksi Tangkapan (Kg)*
- **Tipe Visual**: Bar Chart (Vertikal)
- **Sumbu X**: Bulan Keberangkatan (`1` s/d `12` diformat nama bulan: Jan, Feb, dst.)
- **Sumbu Y**: Total Bobot Tangkapan (`kg`)
- **Formula**: `SUM(catches.weight_kg) GROUP BY MONTH(fishing_trips.departure_date)`
- **Tabel Sumber**: `catches` join `fishing_efforts` join `fishing_trips`
- **Semantik Tanggal**: `fishing_trips.departure_date`

### 4.2 Chart 2 — Top Catch berdasarkan Species
- **Judul UI**: *Top Catch berdasarkan Species (Kg)*
- **Tipe Visual**: Horizontal Bar Chart (`indexAxis: 'y'`)
- **Sumbu X**: Volume Tangkapan (`kg`)
- **Sumbu Y**: Nama Spesies (`local_name_id` dengan fallback ke `scientific_name`)
- **Formula**: `SUM(catches.weight_kg) GROUP BY species.id, local_name_id, scientific_name ORDER BY SUM(catches.weight_kg) DESC LIMIT 10`
- **Tabel Sumber**: `catches` join `species` ON `catches.fish_species_id = species.id`
- **Aturan Identitas**: Identitas unik spesies adalah Primary Key integer `species.id` (bukan string FAO code).

### 4.3 Chart 3 — Komposisi Catch (%)
- **Judul UI**: *Komposisi Catch (%)*
- **Tipe Visual**: Doughnut Chart
- **Dataset**: Berbagi dataset yang sama dengan Chart 2 (Top 10 Spesies)
- **Formula Persentase**:
  $$\text{Persentase Spesies } i = \frac{\text{Catch Species}_i}{\sum \text{Catch Top 10}} \times 100\%$$
- **Validasi**: $\sum \text{Persentase} = 100,00\%$. Jika terdapat lebih dari 10 spesies, sisa volume tangkapan di luar Top 10 didefinisikan sebagai kategori agregat *Lainnya (Others)*.

### 4.4 Chart 4 — Catch Per Unit Effort (CPUE Trend)
- **Judul UI**: *CPUE Trend (Kg/Jam)*
- **Tipe Visual**: Line Chart dengan area fill
- **Sumbu X**: Bulan Keberangkatan
- **Sumbu Y**: Laju Tangkap per Upaya (`kg/jam`)
- **Formula Resmi**:
  $$\text{CPUE} = \frac{\sum \text{Catch } (\text{kg})}{\sum \text{Effort Duration } (\text{jam})}$$
- **Numerator**: `SUM(catches.weight_kg)`
- **Denominator**: `SUM(fishing_efforts.duration_hours)`
- **ATURAN INTEGRITAS DENOMINATOR (Anti-Duplikasi)**:
  Setiap record `fishing_efforts` hanya dihitung **satu kali durasinya**, meskipun effort tersebut menangkap banyak spesies. Query denominator dijalankan terpisah dari query catch (`fishing_efforts` join `fishing_trips`) agar tidak terpengaruh kardinalitas 1:N catches.

### 4.5 Chart 5 — Frekuensi Panjang Ikan (Length Frequency)
- **Judul UI**: *Frekuensi Panjang Ikan* (Badge: *Fork Length (cm)*)
- **Tipe Visual**: Histogram / Bar Chart
- **Sumbu X**: Kelas Interval Panjang Cagak Ikan (`fork_length_cm`, dynamic binning 5 cm)
- **Sumbu Y**: Frekuensi (`Ekor`)
- **Tabel Sumber**: `biological_measurements` join `samples`
- **Semantik Tanggal**: `samples.sample_date` (tanggal pencatatan spesimen biologis)
- **Kriteria Valid**: `fork_length_cm IS NOT NULL AND fork_length_cm > 0`
- **Lebar Bin Interval**: 5,0 cm (contoh kelas: `30–34.9`, `35–39.9`, ..., `90–94.9`)
- **Konsistensi Matematis**:
  $$\sum \text{Frekuensi Seluruh Bin} = \text{Jumlah Pengukuran Biologis Valid}$$
  (Hasil audit: 33 ekor = 33 ekor / 100% cocok).

### 4.6 Chart 6 — Catch berdasarkan Fishing Gear
- **Judul UI**: *Catch berdasarkan Fishing Gear* (Badge: *Catch (kg)*)
- **Tipe Visual**: Horizontal Bar Chart (`indexAxis: 'y'`)
- **Sumbu X**: Total Bobot (`kg`)
- **Sumbu Y**: Nama Standar Alat Tangkap (`fishing_gears.name_id`)
- **Formula**: `SUM(catches.weight_kg) GROUP BY fishing_gears.id, fishing_gears.name_id ORDER BY SUM(catches.weight_kg) DESC`
- **Tabel Sumber**: `catches` join `fishing_efforts` join `fishing_gears`
- **Standar Nomenklatur**: Mengacu pada klasifikasi FAO CWP ISSCFG Rev.1 (2016).

### 4.7 Chart 7 — Catch berdasarkan WPP/Wilayah
- **Judul UI**: *Catch berdasarkan WPP/Wilayah* (Badge: *WPP-RI (kg)*)
- **Tipe Visual**: Bar Chart
- **Sumbu X**: Kode & Nama WPPNRI (contoh: `WPPNRI 571`, `WPPNRI 572`)
- **Sumbu Y**: Total Bobot (`kg`)
- **Formula**: `SUM(catches.weight_kg) GROUP BY wppnri.id, wppnri.code, wppnri.name ORDER BY SUM(catches.weight_kg) DESC`
- **Tabel Sumber**: `catches` join `fishing_efforts` join `fishing_trips` join `wppnri`
- **Penanganan Catch Tanpa WPP**:
  Catch dari pelayaran yang belum memiliki asosiasi WPP valid (`wppnri_id IS NULL`) tidak boleh diberi label WPP fiktif. Catch ini dikeluarkan dari chart WPP dengan catatan terdokumentasi (13 records transaksi legacy = 15.140 kg).

### 4.8 Chart 8 — Fishing Ground / Fishing Effort Location (GIS Map)
- **Judul UI**: *Fishing Ground / Fishing Effort Location* (Badge: *Lokasi Fishing Effort*)
- **Tipe Visual**: Peta Tematik Interaktif Spasial (Leaflet CDN, tiles CartoDB Dark Matter)
- **Tabel Sumber**: `fishing_efforts` (join `fishing_trips`, `fishing_gears`, `wppnri`, `catches`)
- **Field Koordinat**: `fishing_efforts.latitude_setting` dan `fishing_efforts.longitude_setting`
- **Validasi Spasial**: `latitude BETWEEN -90 AND 90`, `longitude BETWEEN -180 AND 180`, `!= 0`, `NOT NULL`
- **Semantik Terminologi**:
  Titik koordinat merepresentasikan **titik tebar alat tangkap (effort setting location)** aktual, BUKAN entitas poligon master fishing ground. Label popup menyajikan: No. Trip, Alat Tangkap, WPP, Estimasi Tangkapan (kg), serta Latitude & Longitude presisi 4 desimal.

---

## 5. Matriks Semantik Tanggal (Date Semantics Matrix)

Penerapan filter waktu (`tahun`, `bulan`, rentang tanggal) mengikuti karakteristik domain masing-masing entitas:

| Ranah / Modul | Field Tanggal Utama | Alasan Domain & Bisnis |
| :--- | :--- | :--- |
| **Fishing Trip / Pelayaran** | `fishing_trips.departure_date` | Menentukan siklus operasional armada sejak bertolak melaut. |
| **Fishing Effort / Upaya** | `fishing_trips.departure_date` (atau `fishing_efforts.setting_date`) | Merujuk pada periode trip penangkapan tempat upaya berlangsung. |
| **Catch / Tangkapan Logbook** | `fishing_trips.departure_date` | Konsisten dengan trip asal tangkapan diperoleh. |
| **Landing / Pendaratan Pelabuhan** | `landings.landing_date` | Waktu riil saat kapal sandar di dermaga dan ikan ditimbang. |
| **Pengukuran Biologis Ikan** | `samples.sample_date` | Waktu fisik enumerator mengambil sampel spesimen ikan di TPI/PPI. |

---

## 6. Semantik Filter & Dukungan Alias URL

Endpoint publik `/statistik` mendukung parameter query fleksibel untuk memudahkan integrasi antarmuka dan API:

| Parameter Resmi Form | Parameter Alias URL | Tipe Data | Cakupan Penerapan |
| :--- | :--- | :--- | :--- |
| `tahun` | `year` | Integer (YYYY) | Memfilter `departure_date` trip dan `sample_date` sample biologis. |
| `bulan` | `month` | Integer (1–12) | Memfilter bulan `departure_date` trip dan `sample_date` sample. |
| `wppnri_id` | `wilayah` | Integer / String | Memfilter `fishing_trips.wppnri_id` (menerima ID integer atau kode string '571'). |
| `landing_site_id` | `site` | Integer | Memfilter pelabuhan pangkalan pendaratan trip atau sample. |
| `fishing_gear_id` | `gear` | Integer | Memfilter alat tangkap pada `fishing_efforts` dan trip primary gear. |
| `family` | - | String | Memfilter taksonomi famili ikan pada tabel `species`. |
| `species_id` | `species` | Integer / String | Menerima ID numerik spesies atau kode ASFIS string (misal: `SKJ`). |

---

## 7. Master Data Dictionary Table

| No | Nama Metrik / Komponen | Tabel Sumber Utama | Field Nilai | Field Tanggal Acuan | Formula Perhitungan | Satuan |
| :-: | :--- | :--- | :--- | :--- | :--- | :---: |
| 1 | **Total Fishing Trip** | `fishing_trips` | `id` | `departure_date` | `COUNT(DISTINCT id)` | Trip |
| 2 | **Total Vessel** | `fishing_trips` | `vessel_id` | `departure_date` | `COUNT(DISTINCT vessel_id)` | Kapal |
| 3 | **Total Catch** | `catches` | `weight_kg` | `departure_date` | `SUM(weight_kg)` | kg |
| 4 | **Total Landing** | `landing_items` | `weight_kg` | `landing_date` | `SUM(weight_kg)` | kg |
| 5 | **Total Species** | `catches` | `fish_species_id` | `departure_date` | `COUNT(DISTINCT fish_species_id)` | Spesies |
| 6 | **Catch Trend (Chart 1)**| `catches` | `weight_kg` | `departure_date` | `SUM(weight_kg) GROUP BY month` | kg |
| 7 | **Catch Species (Chart 2)**| `catches`, `species` | `weight_kg` | `departure_date` | `SUM(weight_kg) GROUP BY species_id` | kg |
| 8 | **Composition (Chart 3)**| `catches`, `species` | `weight_kg` | `departure_date` | `(catch_i / total_catch) * 100` | % |
| 9 | **CPUE (Chart 4)** | `catches`, `fishing_efforts`| `weight_kg`, `duration_hours`| `departure_date` | `SUM(catch_kg) / SUM(effort_hours)` | kg/jam |
| 10 | **Length Freq (Chart 5)** | `biological_measurements`| `fork_length_cm` | `sample_date` | `COUNT(id) GROUP BY length_bin` | Ekor |
| 11 | **Catch by Gear (Chart 6)**| `catches`, `fishing_gears`| `weight_kg` | `departure_date` | `SUM(weight_kg) GROUP BY gear_id` | kg |
| 12 | **Catch by WPP (Chart 7)** | `catches`, `wppnri` | `weight_kg` | `departure_date` | `SUM(weight_kg) GROUP BY wpp_id` | kg |
| 13 | **Effort GIS (Chart 8)** | `fishing_efforts` | `lat_setting`, `lon_setting` | `departure_date` | Koordinat titik setting alat | Lat/Lon |

---

## 8. Catatan Integritas & Keamanan Database
1. **Tidak Ada Mutasi Data**: Halaman statistik publik dan kamus data bersifat *read-only*. Tidak ada proses insert, update, delete, maupun migrasi skema database baru.
2. **Orphan Data**: Satu record orphan catch peninggalan legacy sebelum Tahap 9.2 (290 kg, Tongkol Komo) dipertahankan sebagai data historis tanpa merusak integritas agregasi transaksi.
3. **Data Demo**: Penanda transaksi `DEMO_STATISTICS_2026` terjaga utuh sebanyak 36 fishing trips, 52 efforts, 36 landings, 138 landing items, dan 155 catches.
