# Batasan Sistem & Transparansi Data (Known Limitations)
**Sistem Informasi & Statistik Perikanan Tangkap Provinsi Aceh**

```text
System: Sistem Informasi Perikanan Tangkap Aceh
Stage: F11 — Known Limitations & Transparency
Document Type: Scientific Transparency & Boundary Definitions
Date: 21 September 2026
```

---

## 1. Komitmen Integritas Data Ilmiah

Sistem Informasi & Statistik Perikanan Tangkap Provinsi Aceh berpegang teguh pada prinsip bahwa **kejujuran data empiris adalah fondasi utama bagi pengambilan kebijakan publik yang sah**. Seluruh batasan sistem dicatat secara terbuka dan tidak disembunyikan.

---

## 2. Rincian Batasan Empiris (Hasil Audit Aktual F9/F10)

### A. Kelengkapan Koordinat Fishing Effort
* **Kondisi Aktual:** Dari total 62 catatan *fishing effort* di basis data:
  * **10 data (16%)** memiliki koordinat GPS penangkapan yang valid dan diplot pada peta WebGIS.
  * **52 data (84%)** dicatat secara konvensional tanpa telemetry GPS oleh nelayan skala kecil.
* **Perilaku Sistem:** Sistem **TIDAK** mengarang titik fiktif di laut dan **TIDAK** meletakkannya di titik `0,0` (Null Island). Data non-GPS tetap diikutsertakan pada agregasi statistik numerik dan dicatat transparan pada panel metadata.

### B. Geometri Poligon Master Fishing Ground
* **Kondisi Aktual:** Terdapat 8 entitas master Fishing Ground (Daerah Penangkapan Ikan) di perairan Aceh. Seluruhnya belum memiliki batas koordinat poligon resmi yang ditetapkan melalui keputusan instansi berwenang (DKP Aceh / KKP).
* **Perilaku Sistem:** Sistem menandai 8 data tersebut dengan status: *"Belum Tersedia Geometri Resmi (Tanpa Titik Palsu)"* dan tidak membuat poligon asumsi atau titik centroid fiktif.

### C. Pangkalan Kapal (Homeport) $\neq$ Live Satellite Tracking
* **Klarifikasi Semantik:** Layer *Homeport Kapal* menunjukkan pelabuhan registrasi administratif tempat kapal terdaftar di dinas perikanan, **bukan transmiter pelacakan satelit real-time** posisi kapal di laut lepas.

### D. Logbook Historis $\neq$ Live AIS Tracking
* **Klarifikasi Semantik:** Layer *Logbook Historis* menyajikan arsip catatan perjalanan kapal masa lalu untuk keperluan audit kepatuhan dan analisis spasial, **bukan radar pelacak waktu-nyata**.

### E. Statistik Perikanan $\neq$ Full Stock Assessment
* **Klarifikasi Metodologis:** Sistem ini menyediakan **Statistik Produksi Perikanan Tangkap & Indikator CPUE**, bukan model estimasi biomassa stok ikan laut penuh (*Maximum Sustainable Yield / MSY Stock Assessment Model*). CPUE mengukur efisiensi penangkapan dan kelimpahan relatif, bukan estimasi kuantitatif seluruh populasi ikan di perairan Aceh.
