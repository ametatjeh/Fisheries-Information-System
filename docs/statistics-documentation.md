# Fisheries Statistics Documentation
**Sistem Informasi & Statistik Perikanan Tangkap Provinsi Aceh**

```text
System: Sistem Informasi Perikanan Tangkap Aceh
Version: 1.0.0
Documentation Version: 1.0.0
Date: 21 September 2026
Status: Final Approved (F9 Audit Compliant)
```

---

## 1. Statistics Engine Overview

Modul Statistik Perikanan dikelola melalui [`app/Services/AdvancedStatisticService.php`](file:///c:/XPROJECT/sistem-perikanan/app/Services/AdvancedStatisticService.php) dan [`app/Services/MonthlyProductionService.php`](file:///c:/XPROJECT/sistem-perikanan/app/Services/MonthlyProductionService.php). Modul ini mengagregasi data transaksi harian di laut dan pelabuhan menjadi indikator performa utama (*Key Performance Indicators / KPIs*), estimasi produksi, dan analisis tren komoditas.

---

## 2. Core Statistical Formulas

### A. Catch Per Unit Effort (CPUE)

Catch Per Unit Effort (CPUE) merupakan indikator baku kelimpahan stok ikan dan efisiensi penangkapan:

#### 1. CPUE Berbasis Waktu Operasi ($\text{kg}/\text{jam}$)
Digunakan pada operasi penangkapan yang memiliki catatan durasi perendaman alat tangkap (*soaking time*):

$$\text{CPUE}_{\text{jam}} = \frac{\sum_{i=1}^{n} \text{Catch Weight}_{i}\ (\text{kg})}{\sum_{i=1}^{n} \text{Duration Hours}_{i}\ (\text{jam})}$$

* **Numerator (Pembilang):** Total berat tangkapan ikan ($\text{kg}$) yang terikat langsung pada siklus *effort* terkait.
* **Denominator (Penyebut):** Total akumulasi durasi penarikan/perendaman alat tangkap ($\text{jam}$).
* **Validasi Penyebut:** Hanya dihitung jika $\sum \text{Duration Hours} > 0$. Jika durasi bernilai $0$ atau `NULL`, hasil CPUE disetel ke $0$ untuk mencegah galat *division by zero*.

#### 2. CPUE Berbasis Trip Pelayaran ($\text{kg}/\text{trip}$)
Digunakan untuk mengukur produktivitas armada kapal per satu siklus pelayaran penangkapan:

$$\text{CPUE}_{\text{trip}} = \frac{\sum_{j=1}^{m} \text{Catch Weight}_{j}\ (\text{kg})}{\text{Total Fishing Trips}\ (\text{trip})}$$

---

### B. Faktor Penimbang Estimasi Tangkapan (Raising Factor)

Pada kasus pendataan berbasis sampling pelabuhan, total estimasi produksi dihitung menggunakan *Raising Factor* ($R$) yang dikelola pada `app/Services/CatchEstimationService.php`:

$$R = \frac{N_{\text{total trip}}}{n_{\text{sampled trip}}}$$

$$\text{Estimated Production} = \sum (\text{Sampled Catch Weight}) \times R$$

* $N_{\text{total trip}}$: Total seluruh trip kapal yang beroperasi pada periode dan pelabuhan tertentu.
* $n_{\text{sampled trip}}$: Jumlah trip kapal yang berhasil disampling oleh petugas lapangan.

---

### C. Standardisasi Satuan Berat (Unit Handling)

* **Basis Data Transaksi:** Seluruh catatan transaksi *catches*, *landing items*, dan *biological measurements* disimpan dalam satuan baku **Kilogram ($\text{kg}$)** dengan presisi desimal 2 angka (`DECIMAL(10,2)`).
* **Penyajian Agregat Manajerial:** Pada dashboard eksekutif dan laporan tahunan, data dikonversi secara transparan menjadi **Metrik Ton ($\text{ton}$)** dengan formula:
  $$\text{Weight (ton)} = \frac{\text{Weight (kg)}}{1000}$$

---

## 3. Data Flow & Aggregation Engine

```text
[Catches Table] (kg) ──┐
                       ├──► [AdvancedStatisticService]
[FishingEfforts] (jam) ─┤    - Query Builder with Trip Filters
                       │    - Zero-safe Division & Grouping
[FishingTrips] (trip) ──┘    - KPI Compilation (Trips, Vessels, Catch, CPUE)
                                    │
                                    ▼
                      ┌───────────────────────────┐
                      │ Output Dissemination      │
                      │ 1. Public /statistik page │
                      │ 2. Executive Dashboard    │
                      │ 3. GIS WPP Analysis Layer │
                      │ 4. Periodic Reports       │
                      └───────────────────────────┘
```

---

## 4. Multi-Criteria Statistics Filters

Layanan statistik mendukung parameter filter terstandar:

| Filter Key | Query Parameter | Tipe Data | Deskripsi & Cakupan |
| :--- | :--- | :---: | :--- |
| **Year** | `year` / `tahun` | Integer | Membatasi agregasi pada tahun keberangkatan kapal (e.g., `2024`, `2025`, `2026`). |
| **Month** | `month` / `bulan` | Integer ($1–12$) | Membatasi agregasi pada bulan tertentu. |
| **Date Range** | `start_date`, `end_date` | Date (`Y-m-d`) | Rentang tanggal keberangkatan pelayaran kustom. |
| **Species** | `species_id` / `species` | Integer FK | Menghitung statistik khusus satu jenis komoditas ikan tertentu. |
| **Fishing Gear** | `gear_id` / `gear` | Integer FK | Mengelompokkan statistik berdasarkan alat penangkapan ikan. |
| **WPPNRI** | `wppnri_id` / `wilayah` | Integer FK | Membatasi agregasi pada WPP 571 atau WPP 572. |
| **Landing Site** | `landing_site_id` / `site` | Integer FK | Memfilter data berdasarkan pelabuhan pendaratan atau pangkalan kapal. |
| **Regency** | `regency_id` | Integer FK | Memfilter berdasarkan wilayah administrasi kabupaten/kota di Aceh. |

---

## 5. Statistical Integrity & Isolation Rules

1. **Pencegahan Double-Counting:** Perhitungan total tangkapan dihitung langsung dari `catches` yang terasosiasi unik dengan `fishing_trips` atau `fishing_efforts`. Pendaratan (`landings`) dicatat sebagai transaksi logistik pelabuhan terpisah.
2. **Penanganan Record Non-WPP:** Pelayaran yang belum memiliki asosiasi WPP tidak diabaikan, melainkan dikelompokkan ke dalam kategori *"Tidak Terpetakan (WPP Belum Diisi)"* agar total akumulasi produksi provinsi tetap utuh dan akuntabel.
