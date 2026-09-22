# Database Data Dictionary
**Sistem Informasi & Statistik Perikanan Tangkap Provinsi Aceh**

```text
System: Sistem Informasi Perikanan Tangkap Aceh
Version: 1.0.0
Documentation Version: 1.0.0
Date: 21 September 2026
Status: Final Approved (F9 Audit Compliant)
```

---

## 1. Master Data & Entity Reference Tables

### `fishers` (Data Nelayan)
Tabel induk pencatatan profil nelayan di Provinsi Aceh.

| Column | Type | Nullable | Key | Description |
| :--- | :--- | :---: | :---: | :--- |
| `id` | `BIGINT UNSIGNED` | NO | PK | Identifier unik nelayan |
| `nik` | `VARCHAR(16)` | YES | UNI | Nomor Induk Kependudukan (16 digit) |
| `name` | `VARCHAR(255)` | NO | - | Nama lengkap nelayan |
| `kusuka_number` | `VARCHAR(50)` | YES | UNI | Nomor Kartu Pelaku Usaha Kelautan & Perikanan |
| `phone` | `VARCHAR(20)` | YES | - | Nomor kontak telepon nelayan |
| `address` | `TEXT` | YES | - | Alamat domisili |
| `village_id` | `BIGINT UNSIGNED` | YES | FK | Relasi ke desa/gampong domisili (`villages.id`) |
| `fisher_group_id` | `BIGINT UNSIGNED` | YES | FK | Relasi ke kelompok nelayan/KUB (`fisher_groups.id`) |
| `main_role` | `ENUM('owner','captain','crew')` | NO | - | Peran utama: Pemilik, Nahkoda, atau ABK |
| `is_active` | `TINYINT(1)` | NO | - | Status keaktifan (1 = Aktif, 0 = Non-aktif) |
| `created_at` / `updated_at` | `TIMESTAMP` | YES | - | Waktu pembuatan & pembaruan data |

---

### `vessels` (Armada Kapal Tangkap)
Tabel inventaris armada kapal perikanan tangkap.

| Column | Type | Nullable | Key | Description |
| :--- | :--- | :---: | :---: | :--- |
| `id` | `BIGINT UNSIGNED` | NO | PK | Identifier unik kapal |
| `name` | `VARCHAR(255)` | NO | - | Nama lambung kapal |
| `registration_number` | `VARCHAR(100)` | YES | UNI | Tanda Selar / Nomor Registrasi Kapal |
| `owner_id` | `BIGINT UNSIGNED` | YES | FK | Relasi pemilik kapal (`fishers.id`) |
| `vessel_type_id` | `BIGINT UNSIGNED` | YES | FK | Tipe konstruksi kapal (`vessel_types.id`) |
| `gross_tonnage` | `DECIMAL(8,2)` | NO | - | Ukuran Gross Tonnage (GT) kapal |
| `length_loa` | `DECIMAL(6,2)` | YES | - | Panjang kapal (Length Overall / LOA) dalam meter |
| `engine_power_hp` | `DECIMAL(8,2)` | YES | - | Tenaga mesin kapal dalam Horsepower (HP) |
| `primary_gear_id` | `BIGINT UNSIGNED` | YES | FK | Alat tangkap utama kapal (`fishing_gears.id`) |
| `homeport_site_id` | `BIGINT UNSIGNED` | YES | FK | Pelabuhan pangkalan resmi kapal (`landing_sites.id`) |
| `is_active` | `TINYINT(1)` | NO | - | Status operasional kapal (1 = Aktif, 0 = Non-aktif) |
| `created_at` / `updated_at` | `TIMESTAMP` | YES | - | Waktu pembuatan & pembaruan record |

---

### `species` (Master Jenis Ikan)
Tabel katalog komoditas ikan dengan rujukan taksonomi FAO ASFIS.

| Column | Type | Nullable | Key | Description |
| :--- | :--- | :---: | :---: | :--- |
| `id` | `BIGINT UNSIGNED` | NO | PK | Identifier unik spesies lokal |
| `fao_asfis_species_id` | `BIGINT UNSIGNED` | YES | FK | Relasi ke basis data FAO ASFIS (`fao_asfis_species.id`) |
| `alpha3_code` | `VARCHAR(3)` | YES | IDX | Kode 3 huruf standar internasional FAO (e.g., `SKJ`, `YFT`) |
| `scientific_name` | `VARCHAR(255)` | NO | - | Nama ilmiah / latin spesies (e.g., *Katsuwonus pelamis*) |
| `english_name` | `VARCHAR(255)` | YES | - | Nama standar internasional dalam bahasa Inggris |
| `local_name_id` | `VARCHAR(255)` | YES | - | Nama lokal komoditas dalam bahasa Indonesia / Aceh (e.g., Cakalang, Keureung) |
| `family` | `VARCHAR(100)` | YES | - | Famili taksonomi (e.g., Scombridae) |
| `is_active` | `TINYINT(1)` | NO | - | Status komoditas aktif (1 = Aktif, 0 = Non-aktif) |
| `created_at` / `updated_at` | `TIMESTAMP` | YES | - | Timestamp audit record |

---

### `fishing_gears` (Alat Penangkapan Ikan)
Tabel katalog alat tangkap terstandar FAO ISSCFG.

| Column | Type | Nullable | Key | Description |
| :--- | :--- | :---: | :---: | :--- |
| `id` | `BIGINT UNSIGNED` | NO | PK | Identifier unik alat tangkap |
| `fao_isscfg_gear_id` | `BIGINT UNSIGNED` | YES | FK | Rujukan kode internasional FAO ISSCFG |
| `code` | `VARCHAR(20)` | YES | - | Kode alat tangkap (e.g., `PS`, `LL`, `GN`) |
| `name` | `VARCHAR(255)` | NO | - | Nama alat tangkap (e.g., Pukat Cincin, Rawai Dasar) |
| `category` | `VARCHAR(100)` | YES | - | Kategori alat tangkap (Surrounding Nets, Hooks, dll.) |
| `is_active` | `TINYINT(1)` | NO | - | Status alat tangkap aktif |
| `created_at` / `updated_at` | `TIMESTAMP` | YES | - | Timestamp audit record |

---

### `landing_sites` (Pelabuhan / TPI / Tempat Pendaratan Ikan)
Tabel lokasi fisik pangkalan pendaratan ikan di pesisir Aceh.

| Column | Type | Nullable | Key | Description |
| :--- | :--- | :---: | :---: | :--- |
| `id` | `BIGINT UNSIGNED` | NO | PK | Identifier unik pelabuhan |
| `name` | `VARCHAR(255)` | NO | - | Nama pelabuhan / TPI (e.g., PPS Lampulo, PPI Peudada) |
| `code` | `VARCHAR(50)` | YES | UNI | Kode unik pelabuhan perikanan |
| `site_type` | `ENUM('PPS','PPN','PPI','TPI')` | NO | - | Tipe klasifikasi pelabuhan pendaratan |
| `regency_id` | `BIGINT UNSIGNED` | NO | FK | Relasi kabupaten/kota administrasi (`regencies.id`) |
| `latitude` | `DECIMAL(10,7)` | YES | - | Koordinat lintang lokasi pelabuhan |
| `longitude` | `DECIMAL(10,7)` | YES | - | Koordinat bujur lokasi pelabuhan |
| `address` | `TEXT` | YES | - | Alamat fisik pelabuhan |
| `is_active` | `TINYINT(1)` | NO | - | Status aktif pelabuhan |
| `created_at` / `updated_at` | `TIMESTAMP` | YES | - | Timestamp audit record |

---

### `wppnri` (Wilayah Pengelolaan Perikanan Negara Republik Indonesia)
Tabel batas acuan WPP perairan Indonesia.

| Column | Type | Nullable | Key | Description |
| :--- | :--- | :---: | :---: | :--- |
| `id` | `BIGINT UNSIGNED` | NO | PK | Identifier unik WPP |
| `code` | `VARCHAR(10)` | NO | UNI | Kode numerik WPP (e.g., `571`, `572`) |
| `name` | `VARCHAR(255)` | NO | - | Deskripsi perairan (Selat Malaka, Samudera Hindia) |
| `is_active` | `TINYINT(1)` | NO | - | Status keaktifan record |

---

## 2. Operational Transaction Tables

### `fishing_trips` (Pelayaran Penangkapan Ikan)
Tabel transaksi induk pelayaran operasional kapal penangkap ikan.

| Column | Type | Nullable | Key | Description |
| :--- | :--- | :---: | :---: | :--- |
| `id` | `BIGINT UNSIGNED` | NO | PK | Identifier unik trip pelayaran |
| `trip_number` | `VARCHAR(100)` | NO | UNI | Nomor registrasi pelayaran (e.g., `TRIP-2026-0001`) |
| `vessel_id` | `BIGINT UNSIGNED` | NO | FK | Relasi kapal yang berlayar (`vessels.id`) |
| `captain_id` | `BIGINT UNSIGNED` | YES | FK | Nahkoda yang bertugas (`fishers.id`) |
| `landing_site_id` | `BIGINT UNSIGNED` | YES | FK | Pelabuhan pangkalan keberangkatan (`landing_sites.id`) |
| `wppnri_id` | `BIGINT UNSIGNED` | YES | FK | Wilayah WPP target penangkapan (`wppnri.id`) |
| `fishing_ground_id` | `BIGINT UNSIGNED` | YES | FK | Daerah penangkapan target (`fishing_grounds.id`) |
| `primary_gear_id` | `BIGINT UNSIGNED` | YES | FK | Alat tangkap utama yang dibawa (`fishing_gears.id`) |
| `departure_date` | `DATE` | NO | - | Tanggal kapal bertolak dari pelabuhan |
| `return_date` | `DATE` | YES | - | Tanggal kapal kembali mendarat |
| `crew_count` | `INT UNSIGNED` | YES | - | Jumlah anak buah kapal (ABK) yang berlayar |
| `fuel_consumed_liters` | `DECIMAL(8,2)` | YES | - | Konsumsi bahan bakar (liter) |
| `status` | `ENUM('planned','active','completed','cancelled')` | NO | - | Status pelayaran |
| `created_at` / `updated_at` | `TIMESTAMP` | YES | - | Timestamp pencatatan trip |

---

### `fishing_efforts` (Upaya Penurunan Alat Tangkap)
Tabel rincian siklus operasi setting dan hauling alat tangkap di laut.

| Column | Type | Nullable | Key | Description |
| :--- | :--- | :---: | :---: | :--- |
| `id` | `BIGINT UNSIGNED` | NO | PK | Identifier unik aktivitas effort |
| `fishing_trip_id` | `BIGINT UNSIGNED` | NO | FK | Relasi trip pelayaran (`fishing_trips.id`) |
| `fishing_gear_id` | `BIGINT UNSIGNED` | YES | FK | Alat tangkap yang dioperasikan (`fishing_gears.id`) |
| `setting_number` | `INT UNSIGNED` | NO | - | Urutan penurunan alat tangkap dalam 1 trip (1, 2, 3...) |
| `setting_date` | `DATETIME` | YES | - | Waktu awal alat tangkap diturunkan (setting) |
| `hauling_date` | `DATETIME` | YES | - | Waktu alat tangkap ditarik ke kapal (hauling) |
| `duration_hours` | `DECIMAL(5,2)` | YES | - | Durasi perendaman alat tangkap dalam jam |
| `latitude_setting` | `DECIMAL(10,7)` | YES | - | Lintang posisi setting GPS di laut |
| `longitude_setting` | `DECIMAL(10,7)` | YES | - | Bujur posisi setting GPS di laut |
| `latitude_hauling` | `DECIMAL(10,7)` | YES | - | Lintang posisi penarikan alat tangkap |
| `longitude_hauling` | `DECIMAL(10,7)` | YES | - | Bujur posisi penarikan alat tangkap |
| `created_at` / `updated_at` | `TIMESTAMP` | YES | - | Timestamp pencatatan effort |

---

### `catches` (Hasil Tangkapan Ikan)
Tabel pencatatan berat hasil tangkapan ikan per spesies.

| Column | Type | Nullable | Key | Description |
| :--- | :--- | :---: | :---: | :--- |
| `id` | `BIGINT UNSIGNED` | NO | PK | Identifier unik tangkapan |
| `fishing_trip_id` | `BIGINT UNSIGNED` | NO | FK | Relasi trip pelayaran (`fishing_trips.id`) |
| `fishing_effort_id` | `BIGINT UNSIGNED` | YES | FK | Relasi effort setting spesifik (`fishing_efforts.id`) |
| `fish_species_id` | `BIGINT UNSIGNED` | NO | FK | Rujukan jenis ikan (`species.id`) |
| `weight_kg` | `DECIMAL(10,2)` | NO | - | Berat total hasil tangkapan dalam kilogram ($\text{kg}$) |
| `number_of_fish` | `INT UNSIGNED` | YES | - | Estimasi jumlah ekor individu ikan |
| `created_at` / `updated_at` | `TIMESTAMP` | YES | - | Timestamp pencatatan tangkapan |

---

### `landings` & `landing_items` (Pendaratan Ikan di Pelabuhan)
Tabel transaksi pendaratan dan penjualan ikan di TPI.

| Table | Column | Type | Nullable | Key | Description |
| :--- | :--- | :--- | :---: | :---: | :--- |
| `landings` | `id` | `BIGINT UNSIGNED` | NO | PK | Identifier transaksi pendaratan |
| `landings` | `fishing_trip_id` | `BIGINT UNSIGNED` | YES | FK | Relasi trip penangkapan asal (`fishing_trips.id`) |
| `landings` | `landing_site_id` | `BIGINT UNSIGNED` | NO | FK | Pelabuhan tempat ikan didaratkan (`landing_sites.id`) |
| `landings` | `landing_date` | `DATE` | NO | - | Tanggal pendaratan ikan |
| `landings` | `total_weight_kg`| `DECIMAL(10,2)` | YES | - | Total berat pendaratan (kg) |
| `landing_items` | `id` | `BIGINT UNSIGNED` | NO | PK | Identifier rincian item ikan didaratkan |
| `landing_items` | `landing_id` | `BIGINT UNSIGNED` | NO | FK | Relasi transaksi induk (`landings.id`) |
| `landing_items` | `species_id` | `BIGINT UNSIGNED` | NO | FK | Jenis komoditas ikan (`species.id`) |
| `landing_items` | `weight_kg` | `DECIMAL(10,2)` | NO | - | Berat komoditas didaratkan (kg) |
| `landing_items` | `price_per_kg` | `DECIMAL(12,2)` | YES | - | Harga lelang/jual rata-rata per kg (IDR) |

---

### `logbooks` (Buku Harian Operasional Kapal)
Tabel catatan buku harian operasional harian kapal.

| Column | Type | Nullable | Key | Description |
| :--- | :--- | :---: | :---: | :--- |
| `id` | `BIGINT UNSIGNED` | NO | PK | Identifier catatan logbook |
| `fishing_trip_id` | `BIGINT UNSIGNED` | NO | FK | Relasi trip pelayaran (`fishing_trips.id`) |
| `log_date` | `DATE` | NO | - | Tanggal pencatatan |
| `log_time` | `TIME` | YES | - | Jam pencatatan |
| `latitude` | `DECIMAL(10,7)` | YES | - | Posisi lintang kapal saat pencatatan |
| `longitude` | `DECIMAL(10,7)` | YES | - | Posisi bujur kapal saat pencatatan |
| `weather_condition`| `VARCHAR(100)` | YES | - | Kondisi cuaca (Cerah, Berawan, Hujan, Badai) |
| `wave_height_meters`| `DECIMAL(4,2)`| YES | - | Tinggi gelombang dalam meter |
| `activity_description`| `TEXT` | YES | - | Uraian aktivitas operasional kapal |

---

## 3. Coordinate Data Dictionary (Spasial GIS)

Rincian standar koordinat pada kolom-kolom geospasial aplikasi:

| Column Name | Table | Data Type | Nullable | Valid Boundary Range | Semantic Meaning & Usage |
| :--- | :--- | :--- | :---: | :---: | :--- |
| `latitude` | `landing_sites` | `DECIMAL(10,7)` | YES | $-90.0000000 \le \text{lat} \le 90.0000000$ | Titik koordinat fisik pelabuhan/TPI di pesisir darat Aceh. |
| `longitude` | `landing_sites` | `DECIMAL(10,7)` | YES | $-180.0000000 \le \text{lng} \le 180.0000000$ | Titik koordinat fisik bujur pelabuhan/TPI. |
| `latitude_setting` | `fishing_efforts` | `DECIMAL(10,7)` | YES | $-90.0000000 \le \text{lat} \le 90.0000000$ | Koordinat GPS posisi kapal saat menurunkan jaring/pancing di laut. |
| `longitude_setting`| `fishing_efforts` | `DECIMAL(10,7)` | YES | $-180.0000000 \le \text{lng} \le 180.0000000$ | Koordinat GPS bujur setting alat tangkap. |
| `latitude_hauling` | `fishing_efforts` | `DECIMAL(10,7)` | YES | $-90.0000000 \le \text{lat} \le 90.0000000$ | Koordinat GPS saat alat tangkap ditarik kembali ke kapal. |
| `longitude_hauling`| `fishing_efforts` | `DECIMAL(10,7)` | YES | $-180.0000000 \le \text{lng} \le 180.0000000$ | Koordinat GPS bujur hauling alat tangkap. |
| `latitude` | `logbooks` | `DECIMAL(10,7)` | YES | $-90.0000000 \le \text{lat} \le 90.0000000$ | Posisi kapal historis pada jam pencatatan logbook. |
| `longitude` | `logbooks` | `DECIMAL(10,7)` | YES | $-180.0000000 \le \text{lng} \le 180.0000000$ | Posisi bujur historis kapal. |

> [!NOTE]
> **Penanganan Nilai Null & Zero:**
> Jika record memiliki nilai koordinat `NULL` atau angka `0` (tidak tercatat GPS), sistem **TIDAK** merendernya pada peta dan **TIDAK** menghasilkan koordinat pengganti fiktif. Seluruh record non-spasial tetap diperhitungkan pada agregasi numerik statistik.
