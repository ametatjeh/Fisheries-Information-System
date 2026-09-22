# Narasi Perjalanan Sistem (The System Narrative Arc)
**Sistem Informasi & Statistik Perikanan Tangkap Provinsi Aceh**

```text
System: Sistem Informasi Perikanan Tangkap Aceh
Stage: F11 — System Story
Document Type: Narrative Architecture & Value Journey
Date: 21 September 2026
```

---

## 1. Babak 1: Realitas dan Tantangan di Pesisir Aceh

Provinsi Aceh dianugerahi bentang garis pantai sepanjang lebih dari 2.600 kilometer yang berhadapan langsung dengan dua perairan strategis dunia: Selat Malaka di sisi timur-utara (WPPNRI 571) dan Samudera Hindia di sisi barat-selatan (WPPNRI 572). Ribuan nelayan dan ratusan armada kapal motor setiap harinya menggantungkan hidup pada kelimpahan sumberdaya laut Aceh.

Namun, di balik potensi maritim yang luar biasa tersebut, pengelolaan data perikanan tangkap di tingkat daerah selama bertahun-tahun menghadapi persoalan mendasar: **fragmentasi data**.

Catatan pendaftaran kapal berada di satu kantor perizinan, aktivitas nelayan berada di buku catatan kelompok, buku harian kapal (*logbook*) tertumpuk di pos syahbandar, dan data timbang ikan di TPI dicatat terpisah di masing-masing dermaga pelabuhan. Dampaknya:
* Sulit melacak dari mana ikan berasal ketika telah tiba di tempat pelelangan.
* Angka statistik produksi tahunan sering kali terlambat dirumuskan dan rentan ketidakkonsistenan.
* Pengambil kebijakan di Dinas Kelautan dan Perikanan (DKP) Provinsi Aceh belum memiliki instrumen geospasial terpadu untuk melihat sebaran armada dan wilayah tangkap yang mengalami tekanan penangkapan tinggi.

---

## 2. Babak 2: Fondasi Solusi Terintegrasi (Hulu ke Hilir)

Untuk menjawab kebutuhan tersebut, **Sistem Informasi & Statistik Perikanan Tangkap Provinsi Aceh** dirancang bukan sekadar sebagai aplikasi pencatatan formulir atau dashboard visual sederhana, melainkan sebagai **ekosistem tata kelola data perikanan hulu-ke-hilir**.

Prinsip utamanya sederhana namun revolusioner: **"Satu data tidak boleh berdiri sendiri tanpa riwayat hulu."**

Sistem membangun relasi rantai data yang saling mengunci (*relational integrity*):
1. **Nelayan (*Fishers*):** Tercatat dengan nomor identitas resmi KUSUKA dan perannya (pemilik, nahkoda, atau ABK).
2. **Armada Kapal (*Vessels*):** Terikat pada nelayan pemilik, jenis alat tangkap utama, spesifikasi GT, dan pelabuhan pangkalan asalnya (*homeport*).
3. **Pelayaran Penangkapan (*Fishing Trips*):** Setiap kali kapal bertolak melaut, nomor pelayaran resmi diterbitkan mencatat tanggal berangkat, nahkoda, alat tangkap, dan wilayah WPP target.
4. **Upaya Penangkapan (*Fishing Efforts*):** Di tengah laut, dicatat setiap siklus penurunan jaring (*setting*) dan penarikan (*hauling*), durasi perendaman alat dalam jam, serta koordinat GPS setting jika tersedia.
5. **Hasil Tangkapan (*Catches*):** Berat ikan (kg) dicatat per jenis komoditas dengan rujukan taksonomi ilmiah internasional (FAO ASFIS).
6. **Pendaratan di Pelabuhan (*Landings*):** Hasil tangkapan ditimbang dan diverifikasi secara resmi di pelabuhan pendaratan/TPI.

---

## 3. Babak 3: Dari Transaksi Lapangan Menuju Mesin Statistik

Setelah data transaksi harian terekam, data tersebut tidak langsung disajikan begitu saja. Sistem menerapkan lapisan **Validasi & Verifikasi Mutu**. Petugas verifikator memeriksa kelengkapan data trip dan pendaratan sebelum dimasukkan ke dalam agregasi resmi.

Di lapisan analisis, mesin statistik perikanan menjalankan kalkulasi otomatis:
* **Catch Per Unit Effort (CPUE):** Menghitung laju tangkapan per jam operasi jaring ($\text{kg}/\text{jam}$) dan produktivitas rata-rata per pelayaran armada ($\text{kg}/\text{trip}$) dengan algoritma pengaman pembagian nol.
* **Estimasi Tangkapan Pelabuhan (*Raising Factor*):** Memproyeksikan total produksi pelabuhan berdasarkan sampel data pendaratan harian secara saintifik.
* **Agregasi Bulanan & Tahunan:** Mengelompokkan tren komoditas unggulan (Cakalang, Madidihang/Yellowfin Tuna, Tongkol) tanpa risiko duplikasi data (*double-counting*).

---

## 4. Babak 4: Visualisasi Spasial WebGIS yang Berbicara Fakta

Data tabular statistik diperkuat dengan kehadiran modul geospasial **WebGIS** berbasis engine modern **MapLibre GL JS 4.7.1**.

Melalui antarmuka peta interaktif yang cepat dan ringan di perangkat mobile, sistem menyajikan 7 layer spasial:
* Titik-titik operasi setting alat tangkap aktual di laut.
* Sebaran 12 pelabuhan perikanan dan pangkalan pendaratan ikan aktif di pesisir Aceh.
* Lokasi pelabuhan pangkalan resmi armada kapal terdaftar.
* Catatan historis buku harian operasional pelayaran kapal masa lalu.
* Batas wilayah pengelolaan perikanan WPP 571 (Selat Malaka) dan WPP 572 (Samudera Hindia).

Yang terpenting: **Peta GIS ini terhubung langsung ke basis data operasional via REST API internal**. Tidak ada sinkronisasi manual atau duplikasi file shapefile statis.

---

## 5. Babak 5: Komitmen Integritas Data & Kejujuran Ilmiah

Sistem Informasi Perikanan Aceh dibangun di atas prinsip moral dan etika sains:
$$\mathbf{Integritas\ Data} > \mathbf{Estetika\ Visual}$$

Sistem tidak mengorbankan kebenaran demi membuat peta atau grafik terlihat penuh:
* **52 Data Effort Tanpa GPS:** Sistem secara transparan mencatatnya pada ringkasan statistik, dan **TIDAK** memplot koordinat fiktif di laut atau di titik `0,0` (Null Island).
* **8 Master Fishing Ground:** Diberi label terbuka *"Belum Tersedia Geometri Resmi"* karena menunggu penetapan batas definitif dari instansi pemerintah.
* **Penegasan Semantik:** Pangkalan kapal terdaftar (*homeport*) ditegaskan bukan pelacak satelit waktu-nyata (*live tracking*), dan logbook historis ditegaskan bukan radar AIS langsung.

---

## 6. Babak 6: Nilai Akhir — Mendukung Kebijakan Berbasis Bukti (*Decision Support*)

Muara dari seluruh perjalanan data ini adalah satu: **Memberikan informasi yang sahih dan dapat dipertanggungjawabkan kepada Pemerintah Provinsi Aceh**.

Dengan laporan rekapitulasi produksi yang dapat diekspor secara aman ke format Excel dan PDF, Dinas Kelautan dan Perikanan Aceh kini memiliki instrumen pendukung keputusan (*Decision Support System*) untuk:
1. Memantau kesehatan stok dan laju penangkapan di WPP 571 dan WPP 572.
2. Mengarahkan bantuan sarana dan prasarana tangkap tepat sasaran kepada nelayan yang berhak.
3. Menyusun rencana zonasi perikanan tangkap yang berkelanjutan demi kesejahteraan generasi nelayan Aceh di masa depan.
