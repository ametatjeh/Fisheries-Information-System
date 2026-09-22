# Alur Kerja Mesin Statistik (Statistics Flow & Formulas)
**Sistem Informasi & Statistik Perikanan Tangkap Provinsi Aceh**

```text
System: Sistem Informasi Perikanan Tangkap Aceh
Stage: F11 — Statistics Flow & Formulas
Document Type: Scientific Calculation Architecture
Date: 21 September 2026
```

---

## 1. Alur Pemrosesan Statistik Perikanan

```text
┌─────────────────────────────────────────────────────────────┐
│ 1. DATA INPUT OPERASIONAL                                   │
│ - Tabel Catches: Berat per spesies (kg)                     │
│ - Tabel Fishing Efforts: Durasi setting alat tangkap (jam)  │
│ - Tabel Fishing Trips: Frekuensi pelayaran operasional      │
└──────────────────────────────┬──────────────────────────────┘
                               │
                               ▼
┌─────────────────────────────────────────────────────────────┐
│ 2. FILTER NORMALIZATION & SCOPE CONSTRAINTS                 │
│ - Parameter: year, month, species_id, gear_id, wppnri_id    │
│ - Normalisasi Rentang Tanggal (start_date - end_date)       │
└──────────────────────────────┬──────────────────────────────┘
                               │
                               ▼
┌─────────────────────────────────────────────────────────────┐
│ 3. KALKULASI INDIKATOR PRODUKTIVITAS                        │
│                                                             │
│  [A] CPUE Berbasis Waktu:                                   │
│      CPUE_jam = Total Catch (kg) / Total Duration (jam)     │
│      (Dihitung jika Total Duration > 0)                     │
│                                                             │
│  [B] CPUE Berbasis Trip:                                    │
│      CPUE_trip = Total Catch (kg) / Total Fishing Trips     │
│      (Dihitung jika Total Fishing Trips > 0)                │
│                                                             │
│  [C] Raising Factor (Modul Estimasi Sampling):              │
│      R = Total Kapal Beroperasi / Total Kapal Disampling    │
│      Estimated Production = Sampled Catch * R               │
└──────────────────────────────┬──────────────────────────────┘
                               │
                               ▼
┌─────────────────────────────────────────────────────────────┐
│ 4. PENYAJIAN INDIKATOR & DISEMINASI                         │
│ - Tampilan Dashboard Eksekutif                              │
│ - Halaman Statistik Terbuka (/statistik)                    │
│ - Panel Analitika Spasial WPP pada WebGIS                   │
│ - Rekapitulasi Laporan Bulanan / Tahunan                    │
└─────────────────────────────────────────────────────────────┘
```

---

## 2. Rincian Metrik & Satuan Baku

| Indikator | Satuan Baku Basis Data | Satuan Penyajian Eksekutif | Formula / Perhitungan | Proteksi Kesalahan (*Safety Guard*) |
| :--- | :---: | :---: | :--- | :--- |
| **Total Tangkapan (*Catch*)** | Kilogram ($\text{kg}$) | Ton ($\text{ton} = \text{kg} / 1000$) | $\sum \text{catches.weight\_kg}$ | Nilai non-negatif ($\ge 0$) |
| **Durasi Upaya (*Effort Duration*)** | Jam ($\text{jam}$) | Jam ($\text{jam}$) | $\sum \text{fishing\_efforts.duration\_hours}$ | Nilai desimal presisi 1 angka |
| **CPUE per Jam Operasi** | $\text{kg}/\text{jam}$ | $\text{kg}/\text{jam}$ | $\frac{\sum \text{Catch (kg)}}{\sum \text{Duration (jam)}}$ | Jika $\text{Duration} = 0 \rightarrow \text{CPUE} = 0$ |
| **CPUE per Trip** | $\text{kg}/\text{trip}$ | $\text{kg}/\text{trip}$ | $\frac{\sum \text{Catch (kg)}}{\text{Total Trips}}$ | Jika $\text{Trips} = 0 \rightarrow \text{CPUE} = 0$ |
| **Pendaratan Pelabuhan (*Landing*)** | Kilogram ($\text{kg}$) | Ton ($\text{ton}$) | $\sum \text{landing\_items.weight\_kg}$ | Terpisah dari catatan laut (mencegah duplikasi) |
