# Presentation & Demonstration Hub
**Sistem Informasi & Statistik Perikanan Tangkap Provinsi Aceh**

```text
System: Sistem Informasi Perikanan Tangkap Aceh
Stage: F11 — Presentation Documentation & Demonstration
Version: 1.0.0
Date: 21 September 2026
Status: Approved & Verified
```

Selamat datang di direktori materi presentasi resmi dan panduan demonstrasi langsung (**Live Demonstration**) untuk **Sistem Informasi & Statistik Perikanan Tangkap Provinsi Aceh**.

Dokumen di direktori ini dirancang untuk memudahkan penyampaian sistem kepada berbagai pemangku kepentingan (*stakeholders*), mulai dari jajaran pimpinan (Kepala Dinas / Pengambil Kebijakan), petugas lapangan (Enumerator / Syahbandar Pelabuhan), hingga tim teknis IT dan pengembang perangkat lunak.

---

## ⚡ Quick Pitch Formats

### A. Penjelasan 1 Menit (Elevator Pitch)
> "Sistem Informasi & Statistik Perikanan Tangkap Provinsi Aceh adalah platform digital terintegrasi yang mencatat seluruh rantai operasional perikanan dari hulu ke hilir—mulai dari pendaftaran nelayan, armada kapal, pelayaran penangkapan (*fishing trip*), penurunan alat tangkap (*fishing effort*), hasil tangkapan (*catch*), hingga pendaratan ikan (*landing*) di pelabuhan Aceh. Data transaksi tersebut divalidasi dan diolah secara otomatis menjadi indikator produktivitas seperti CPUE (kg/jam & kg/trip), estimasi produksi berbasis *raising factor*, dan visualisasi spasial 7 layer interaktif MapLibre GL JS untuk memantau sebaran pendaratan dan batas WPP 571/572. Hasil akhirnya adalah laporan manajerial dan statistik terbuka yang dapat dipertanggungjawabkan (*traceable*) guna mendukung pengambilan kebijakan perikanan berkelanjutan."

### B. Penjelasan 5 Menit (Management Executive Pitch)
1. **Latar Belakang & Masalah:** Pengelolaan data perikanan di 23 Kabupaten/Kota Aceh sebelumnya tersebar dan terfragmentasi antar-pelabuhan, menyulitkan pelacakan historis dan perumusan angka statistik produksi yang akurat.
2. **Solusi & Workflow Terpadu:** Sistem menghubungkan relasi data: Nelayan $\rightarrow$ Kapal $\rightarrow$ Trip $\rightarrow$ Effort $\rightarrow$ Catch $\rightarrow$ Landing, sehingga setiap kilogram ikan yang didaratkan memiliki riwayat pelayaran dan alat tangkap yang jelas (*data traceability*).
3. **Mesin Statistik & Validasi:** Menghitung CPUE per jam operasi dan per trip pelayaran dengan proteksi pembagian nol (*zero-safe division*), serta estimasi tangkapan berbasis sampling ilmiah.
4. **Visualisasi Spasial WebGIS:** Menampilkan 7 layer spasial berbasis MapLibre GL JS (titik setting alat tangkap, pelabuhan/TPI, pangkalan kapal terdaftar, logbook historis, master fishing ground, serta poligon batas WPP 571 dan WPP 572).
5. **Transparansi & Limitasi:** Menjunjung tinggi integritas data empiris—sistem tidak mengarang koordinat palsu (`0,0`) untuk data non-GPS dan memperjelas bahwa pangkalan kapal terdaftar bukanlah transmiter satelit pelacakan langsung (*live tracking*).

---

## 📂 Indeks Dokumen Presentasi

| Berkas | Deskripsi & Isi Utama |
| :--- | :--- |
| 📑 [**presentation-outline.md**](./presentation-outline.md) | Kerangka 20 slide presentasi lengkap dengan objektif, poin kunci, dan visualisasi ringkas. |
| 🎙️ [**presentation-script.md**](./presentation-script.md) | Naskah tutur presenter slide-demi-slide dalam Bahasa Indonesia natural dengan transisi antar-slide. |
| 💻 [**demo-script.md**](./demo-script.md) | Skenario demonstrasi langsung (10 menit) langkah-demi-langkah dari Beranda publik hingga WebGIS. |
| 📖 [**system-story.md**](./system-story.md) | Narasi komprehensif perjalanan sistem (*The System Narrative Arc*). |
| 🏛️ [**architecture-diagram.md**](./architecture-diagram.md) | Diagram arsitektur sistem MVC, GIS data flow, dan batasan keamanan. |
| 🔄 [**data-flow.md**](./data-flow.md) | Rantai aliran data operasional perikanan dan keterlacakan (*traceability*). |
| 📊 [**statistics-flow.md**](./statistics-flow.md) | Alur kerja mesin statistik, kalkulasi CPUE, dan faktor penimbang (*raising factor*). |
| 🗺️ [**gis-flow.md**](./gis-flow.md) | Pipeline spasial GeoJSON, koordinat `[lng, lat]`, dan integrasi MapLibre GL JS. |
| ⚠️ [**limitations.md**](./limitations.md) | Batasan sistem dan limitasi data empiris yang disampaikan secara transparan. |
| 🚀 [**roadmap.md**](./roadmap.md) | Rencana pengembangan jangka panjang (Existing vs Future Roadmap). |
| ❓ [**faq.md**](./faq.md) | Tanya-jawab terantisipasi untuk pimpinan, enumerator, dan pengembang teknis. |
| 📋 [**F11-report.md**](./F11-report.md) | Laporan formal penuntasan tahap F11 dan kesiapan menuju F12. |

---

## 🎯 Panduan Adaptasi Audiens

* **Untuk Pimpinan / Pengambil Kebijakan:** Gunakan Slide 1–5, 8, 11, 15, 18, 19, 20. Fokus pada nilai data terpadu, statistik produksi, transparansi wilayah WPP, dan dukungan pelaporan manajerial.
* **Untuk Petugas Lapangan / Enumerator:** Gunakan Slide 5–8, 11, 13, 15. Fokus pada alur pencatatan trip, effort, tangkapan, pendaratan, dan pembacaan peta pelabuhan.
* **Untuk Tim IT / Developer:** Gunakan Slide 11, 12, 16, 17, 18, 19, 20 beserta [architecture.md](../architecture.md) dan [api-documentation.md](../api-documentation.md). Fokus pada stack Laravel 13, MapLibre GL JS, REST API, keamanan peran, dan performa query.
