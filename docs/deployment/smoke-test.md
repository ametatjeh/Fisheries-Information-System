# Post-Deployment Smoke Test Matrix
**Sistem Informasi & Statistik Perikanan Tangkap Provinsi Aceh**

```text
System: Sistem Informasi Perikanan Tangkap Aceh
Stage: F12 — Smoke Test Matrix
Document Type: Verification Matrix
Date: 21 September 2026
```

---

## 1. Production Verification Matrix (15 Fitur Utama)

Jalankan pengujian cepat berikut segera setelah rilis aplikasi dibuka di server target:

| No | Fitur / Rute | Uji / Prosedur | Hasil yang Diharapkan | Status Uji |
| :-: | :--- | :--- | :--- | :---: |
| **1** | **Beranda Publik (`/`)** | Buka URL utama di browser | Halaman memuat responsif (HTTP 200) tanpa error | ✅ **PASS** |
| **2** | **Autentikasi Login (`/login`)** | Masukkan email & kata sandi valid | Berhasil dialihkan ke Dashboard pengguna | ✅ **PASS** |
| **3** | **Otorisasi RBAC** | Login akun Viewer, coba akses Admin Master | Akses dibatasi (HTTP 403 Forbidden) | ✅ **PASS** |
| **4** | **Master Nelayan (`/master/fishermen`)**| Buka daftar nelayan | Menampilkan 23 data nelayan terdaftar | ✅ **PASS** |
| **5** | **Master Kapal (`/master/vessels`)** | Buka daftar kapal | Menampilkan 19 armada kapal dan tanda selar | ✅ **PASS** |
| **6** | **Fishing Trips (`/trips`)** | Buka daftar pelayaran | Menampilkan daftar pelayaran dan status trip | ✅ **PASS** |
| **7** | **Fishing Efforts (`/efforts`)** | Buka riwayat effort | Menampilkan rincian jam operasi setting/hauling | ✅ **PASS** |
| **8** | **Hasil Tangkapan (`/catches`)** | Buka daftar tangkapan | Menampilkan berat tangkapan per jenis ikan (kg) | ✅ **PASS** |
| **9** | **Pendaratan TPI (`/landings`)** | Buka transaksi pendaratan | Menampilkan data pendaratan di pelabuhan | ✅ **PASS** |
| **10**| **Halaman Statistik (`/statistik`)** | Buka menu statistik terbuka | Memuat kartu KPI, grafik, & dropdown tahun bersih | ✅ **PASS** |
| **11**| **Filter CPUE & Komoditas** | Pilih filter Tahun `2026` & komoditas | Grafik & CPUE menyesuaikan secara dinamis | ✅ **PASS** |
| **12**| **Spatial API (`GET /gis/data`)** | Panggil endpoint via curl/fetch | Mengembalikan JSON valid dengan 7 layer data | ✅ **PASS** |
| **13**| **WebGIS MapLibre Canvas** | Buka peta interaktif | Peta merender 7 layer, popup aktif, tanpa galat | ✅ **PASS** |
| **14**| **Ekspor Spreadsheet Excel** | Klik tombol *Ekspor Excel* di Laporan | Mengunduh file `.xlsx` valid tanpa error | ✅ **PASS** |
| **15**| **Ekspor / Cetak Dokumen PDF** | Klik tombol *Cetak PDF* | Menampilkan layout ramah cetak resmi dinas | ✅ **PASS** |
