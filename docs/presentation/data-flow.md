# Alur Data & Keterlacakan (Data Flow & Traceability)
**Sistem Informasi & Statistik Perikanan Tangkap Provinsi Aceh**

```text
System: Sistem Informasi Perikanan Tangkap Aceh
Stage: F11 — Data Flow & Traceability
Document Type: Operational Data Life Cycle
Date: 21 September 2026
```

---

## 1. Rantai Aliran Data Operasional Perikanan

Siklus hidup data di dalam Sistem Informasi Perikanan Aceh mengikuti rantai logis 6 tahap:

```text
┌─────────────────────────────────────────────────────────────┐
│ 1. PENDAFTARAN IDENTITAS (NELAYAN & KAPAL)                  │
│ - Nelayan: NIK, Nama, Nomor KUSUKA, Desa Domisili, Peran    │
│ - Kapal: Nama Lambung, Tanda Selar, GT, Mesin HP, Homeport  │
└──────────────────────────────┬──────────────────────────────┘
                               │
                               ▼
┌─────────────────────────────────────────────────────────────┐
│ 2. PELAYARAN PENANGKAPAN (FISHING TRIP)                     │
│ - Nomor Trip Unik (e.g., TRIP-2026-0012)                    │
│ - Kapal yang Berlayar & Nahkoda Penanggung Jawab            │
│ - Pelabuhan Asal, Tanggal Keberangkatan, Wilayah Target WPP │
└──────────────────────────────┬──────────────────────────────┘
                               │
                               ▼
┌─────────────────────────────────────────────────────────────┐
│ 3. UPAYA PENURUNAN ALAT TANGKAP (FISHING EFFORT)            │
│ - Nomor Siklus Setting (Setting 1, Setting 2...)            │
│ - Waktu Penurunan (Setting Date/Time) & Penarikan (Hauling) │
│ - Durasi Jam Operasi Alat Tangkap (Jam Perendaman)          │
│ - Koordinat GPS Setting di Laut (Jika Tersedia)             │
└──────────────────────────────┬──────────────────────────────┘
                               │
                               ▼
┌─────────────────────────────────────────────────────────────┐
│ 4. PENCATATAN HASIL TANGKAPAN (FISH CATCH)                  │
│ - Spesies Ikan Tertangkap (Referensi FAO ASFIS 2026.1)      │
│ - Berat Hasil Tangkapan dalam Satuan Kilogram (kg)          │
│ - Estimasi Jumlah Ekor Ikan Tertangkap                      │
└──────────────────────────────┬──────────────────────────────┘
                               │
                               ▼
┌─────────────────────────────────────────────────────────────┐
│ 5. PENDARATAN & PENIMBANGAN DI TPI (FISH LANDING)           │
│ - Transaksi Pendaratan Resmi di Pelabuhan / TPI             │
│ - Tanggal Pendaratan & Verifikasi Total Berat (kg)          │
│ - Rincian Berat & Harga Jual per Spesies di Dermaga         │
└──────────────────────────────┬──────────────────────────────┘
                               │
                               ▼
┌─────────────────────────────────────────────────────────────┐
│ 6. VALIDASI & PENGOLAHAN HILIR (STATISTICS & GIS)           │
│ - Verifikasi Mutu Transaksi oleh Petugas Pelabuhan          │
│ - Agregasi Bulanan/Tahunan & Perhitungan Metrik CPUE        │
│ - Plotting Titik Spasial & Pemutakhiran Layer WebGIS        │
│ - Penyusunan Dokumen Laporan Rekapitulasi Produksi          │
└─────────────────────────────────────────────────────────────┘
```

---

## 2. Jaminan Keterlacakan Data (Data Traceability Matrix)

Setiap entitas data di hilir dapat ditelusuri kembali ke hulu (*backward traceability*):

| Informasi di Hilir | Pertanyaan Audit | Jalur Penelusuran Hulu di Database |
| :--- | :--- | :--- |
| **Total Produksi Ikan Cakalang (10 Ton)** | Dari mana ikan ini berasal? | `landings` $\rightarrow$ `fishing_trips` $\rightarrow$ `catches` $\rightarrow$ `species.scientific_name = 'Katsuwonus pelamis'` |
| **Titik Koordinat Effort di Laut** | Siapa yang melakukan operasi penangkapan? | `fishing_efforts` $\rightarrow$ `fishing_trips.vessel_id` $\rightarrow$ `vessels.name` & `fishers.name` (Nahkoda) |
| **Metrik CPUE 250 kg/jam** | Bagaimana angka laju tangkap ini diperoleh? | $\sum \text{catches.weight\_kg}$ pada trip terkait dibagi $\sum \text{fishing\_efforts.duration\_hours}$ |
| **Sebaran Armada di Pelabuhan** | Siapa pemilik kapal yang berpangkalan di PPS Lampulo? | `vessels.homeport_site_id = landing_sites.id` $\rightarrow$ `fishers.owner_id` |
