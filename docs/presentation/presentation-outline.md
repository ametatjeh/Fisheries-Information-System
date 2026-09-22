# Presentation Outline (20 Slides)
**Sistem Informasi & Statistik Perikanan Tangkap Provinsi Aceh**

```text
System: Sistem Informasi Perikanan Tangkap Aceh
Stage: F11 — Presentation Documentation
Total Slides: 20 Slides
Language: Bahasa Indonesia
Target Duration: 15–20 Menit
```

---

### SLIDE 1 — TITLE SLIDE
* **Judul:** Sistem Informasi & Statistik Perikanan Tangkap Provinsi Aceh
* **Sub-judul:** Integrasi Data Hulu-ke-Hilir: Operasional Penangkapan, Mesin Statistik, Pelaporan, dan WebGIS
* **Visual:** Header elegan dengan logo Provinsi Aceh, tagline tata kelola data perikanan, dan ikon modul (Kapal, Statistik, Peta).
* **Objektif:** Membuka sesi presentasi dan menetapkan identitas resmi aplikasi perikanan tangkap Aceh.

---

### SLIDE 2 — LATAR BELAKANG & TANTANGAN DATA
* **Poin Kunci:**
  * Pendataan perikanan di 23 Kabupaten/Kota Aceh sebelumnya terfragmentasi di masing-masing pelabuhan.
  * Hubungan antara data kapal, nahkoda, alat tangkap, dan pendaratan belum terhubung secara sistematis.
  * Perhitungan angka statistik produksi membutuhkan data operasional yang dapat ditelusuri (*traceable*).
  * Kebutuhan representasi geospasial untuk memahami sebaran aktivitas penangkapan di perairan WPP 571 & 572.
* **Visual:** Diagram perbandingan: *"Data Terfragmentasi"* $\rightarrow$ *"Sistem Terpadu"*.
* **Objektif:** Menjelaskan urgensi dan latar belakang dibangunnya sistem.

---

### SLIDE 3 — TUJUAN & FUNGSI UTAMA SISTEM
* **Poin Kunci:**
  * **1. Digitalisasi Transaksi:** Mencatat siklus nelayan, kapal, trip, effort, hasil tangkapan, dan pendaratan.
  * **2. Standardisasi Internasional:** Mengadopsi acuan taksonomi spesies FAO ASFIS dan alat tangkap FAO ISSCFG.
  * **3. Perhitungan Statistik & CPUE:** Menyediakan metrik produktivitas ilmiah bebas *division-by-zero*.
  * **4. Visualisasi Geospasial:** Menyajikan 7 layer peta interaktif WebGIS berbasis MapLibre GL JS.
  * **5. Pengambilan Keputusan (*Decision Support*):** Menyediakan laporan manajerial dan ekspor data terverifikasi.
* **Visual:** 5 pilar tujuan dalam bentuk kartu (*cards*) berwarna.
* **Objektif:** Memberikan gambaran manfaat konkret sistem bagi Dinas Kelautan dan Perikanan.

---

### SLIDE 4 — RUANG LINGKUP (SCOPE) WILAYAH ACEH
* **Poin Kunci:**
  * **Wilayah Administrasi:** 23 Kabupaten/Kota di Provinsi Aceh.
  * **Wilayah Pengelolaan Perikanan (WPPNRI):**
    * **WPP 571:** Selat Malaka & Laut Andaman (pesisir timur-utara Aceh).
    * **WPP 572:** Samudera Hindia sebelah Barat Sumatera (pesisir barat-selatan Aceh).
  * **Pangkalan Pendaratan:** 13 Pelabuhan Perikanan & TPI terdaftar (PPS Lampulo, PPN Idi, PPI Peudada, dll.).
* **Visual:** Peta siluet Provinsi Aceh dengan arsiran wilayah WPP 571 dan 572.
* **Objektif:** Menegaskan batasan geografis dan yuridis operasional sistem.

---

### SLIDE 5 — MODUL UTAMA APLIKASI
* **Poin Kunci:**
  * **1. Master Data:** Wilayah, Nelayan (KUSUKA), Armada Kapal, Spesies, Alat Tangkap, Pelabuhan.
  * **2. Data Collection:** Fishing Trips, Fishing Efforts, Catches, Landings, Historical Logbooks.
  * **3. Fisheries Statistics:** Validasi data, sampling biologi, estimasi produksi (*raising factor*), agregasi bulanan.
  * **4. Reporting & Export:** Rekapitulasi produksi, tren komoditas, ekspor Excel aman dan cetak dokumen.
  * **5. WebGIS Spatial Map:** Visualisasi 7 layer spasial dan analitika perairan WPP.
* **Visual:** Diagram blok hierarki modul aplikasi.
* **Objektif:** Menjelaskan pembagian modul fungsional dalam sistem.

---

### SLIDE 6 — WORKFLOW & DATA MODEL UTAMA
* **Poin Kunci:**
  * Alur Rantai Perikanan:
    $$\text{Nelayan} \longrightarrow \text{Kapal} \longrightarrow \text{Fishing Trip} \longrightarrow \text{Fishing Effort} \longrightarrow \text{Catch} \longrightarrow \text{Landing}$$
  * Nelayan pemilik/nahkoda terikat pada armada kapal.
  * Kapal melaksanakan pelayaran penangkapan (*Fishing Trip*).
  * Pada setiap trip dilakukan operasi penurunan alat tangkap (*Fishing Effort*).
  * Effort menghasilkan rekaman berat ikan per spesies (*Catch*).
  * Hasil tangkapan didaratkan dan ditimbang di pelabuhan (*Landing*).
* **Visual:** Alur proses panah horizontal dengan ikon entitas.
* **Objektif:** Menunjukkan konsistensi relasi data operasional perikanan.

---

### SLIDE 7 — KETERLACAKAN DATA (DATA TRACEABILITY)
* **Poin Kunci:**
  * Tidak ada data statistik yang berdiri sendiri tanpa sumber transaksi hulu.
  * Setiap angka produksi (kg/ton) dapat ditelusuri kembali:
    * Siapa kapal dan nahkodanya?
    * Kapan tanggal keberangkatan pelayarannya?
    * Alat tangkap apa yang digunakan dan berapa jam operasinya?
    * Di pelabuhan mana ikan tersebut ditimbang?
  * Menjamin akuntabilitas dan audit integritas data perikanan.
* **Visual:** Diagram *Drill-Down* dari total produksi $\rightarrow$ trip $\rightarrow$ kapal $\rightarrow$ nelayan.
* **Objektif:** Menjelaskan keunggulan auditabilitas data pada sistem.

---

### SLIDE 8 — MESIN STATISTIK PERIKANAN
* **Poin Kunci:**
  * **Alur Pengolahan:**
    $$\text{Data Transaksi} \longrightarrow \text{Validasi/Verifikasi} \longrightarrow \text{Agregasi} \longrightarrow \text{Indikator Statistik}$$
  * Mengisolasi data yang belum divalidasi dari perhitungan statistik resmi.
  * Melakukan agregasi bulanan dan tahunan secara dinamis berdasarkan filter multi-kriteria.
  * Mencegah duplikasi data (*double-counting*) antara catatan laut dan transaksi dermaga.
* **Visual:** Diagram alir mesin kalkulasi statistik (*pipeline*).
* **Objektif:** Menguraikan proses transformasi data mentah menjadi informasi statistik.

---

### SLIDE 9 — PERHITUNGAN CATCH PER UNIT EFFORT (CPUE)
* **Poin Kunci:**
  * **1. CPUE Berbasis Waktu Operasi ($\text{kg}/\text{jam}$):**
    $$\text{CPUE}_{\text{jam}} = \frac{\sum \text{Catch (kg)}}{\sum \text{Effort Duration (jam)}}$$
    *Mengukur laju tangkap aktual per jam operasi alat tangkap di laut.*
  * **2. CPUE Berbasis Pelayaran ($\text{kg}/\text{trip}$):**
    $$\text{CPUE}_{\text{trip}} = \frac{\sum \text{Catch (kg)}}{\text{Total Trips}}$$
    *Mengukur produktivitas rata-rata satu siklus pelayaran armada kapal.*
  * **Validasi Penyebut:** Perhitungan aman dari pembagian nol jika durasi bernilai 0.
* **Visual:** Kotak formula matematika formal dengan penjelasan variabel.
* **Objektif:** Menjelaskan formula CPUE terstandar yang digunakan sistem.

---

### SLIDE 10 — ESTIMASI TANGKAPAN & RAISING FACTOR
* **Poin Kunci:**
  * Digunakan untuk mengestimasi total produksi pelabuhan saat pencatatan dilakukan melalui metode sampling.
  * **Formula Raising Factor ($R$):**
    $$R = \frac{N_{\text{total trip}}}{n_{\text{sampled trip}}}$$
    $$\text{Estimated Production} = \sum (\text{Sampled Catch}) \times R$$
  * *Catatan Penting:* Merupakan metode estimasi operasional pelabuhan, bukan estimasi biomassa stok ikan di laut (*stock biomass*).
* **Visual:** Ilustrasi sampling perwakilan trip menuju total estimasi pelabuhan.
* **Objektif:** Menjelaskan modul estimasi tangkapan perikanan.

---

### SLIDE 11 — WEBGIS: 7 LAYER SPASIAL MAPLIBRE GL JS
* **Poin Kunci:**
  * Menggunakan engine modern **MapLibre GL JS 4.7.1** berbasis WebGL yang cepat dan responsif.
  * **7 Layer Interaktif:**
    1. 🟠 **Fishing Effort:** Titik koordinat setting alat tangkap aktual di laut.
    2. 🔵 **Landing Site:** 12 titik pelabuhan perikanan & TPI aktif di pesisir Aceh.
    3. 🟣 **Homeport Kapal:** Sebaran pelabuhan pangkalan 19 armada kapal terdaftar.
    4. 🟡 **Logbook Historis:** 29 titik rekam jejak operasional pelayaran masa lalu.
    5. ⚪ **Master Fishing Ground:** 8 daerah penangkapan (status informatif).
    6. 🔷 **WPPNRI 571:** Poligon batas perairan Selat Malaka & Laut Andaman.
    7. 🟩 **WPPNRI 572:** Poligon batas perairan Samudera Hindia Barat Sumatera.
* **Visual:** Cuplikan antarmuka peta GIS dengan 7 penanda layer berwarna.
* **Objektif:** Memperkenalkan visualisasi spasial 7 layer pada WebGIS.

---

### SLIDE 12 — ARSITEKTUR INTEGRASI DATA GIS
* **Poin Kunci:**
  * GIS bukan aplikasi terpisah atau salinan data manual (*standalone map*).
  * **Aliran Data Realtime:**
    $$\text{Database MySQL} \longrightarrow \text{GisController} \longrightarrow \text{GET /gis/data} \longrightarrow \text{GeoJSON} \longrightarrow \text{MapLibre GL JS}$$
  * Standar koordinat GeoJSON: $[\text{longitude}, \text{latitude}]$ sesuai RFC 7946.
  * Setiap perubahan data di modul operasional langsung tercermin pada data spasial GIS.
* **Visual:** Diagram alir data dari tabel database ke kanvas peta browser.
* **Objektif:** Menjelaskan integritas arsitektur antara backend dan peta frontend.

---

### SLIDE 13 — INTEGRITAS KUALITAS DATA GIS
* **Poin Kunci (Data Aktual F9/F10):**
  * **Fishing Efforts (62 Total):**
    * **10 Mapped:** Memiliki koordinat GPS akurat dan diplot di laut.
    * **52 Unmapped:** Dicatat tanpa GPS (konvensional) $\rightarrow$ **TIDAK dibuat titik palsu di laut atau di `0,0`**.
  * **Master Fishing Grounds (8 Total):**
    * Belum memiliki batas poligon resmi dari instansi berwenang $\rightarrow$ diberi label transparan *"Belum Tersedia Geometri Resmi"*.
  * **Prinsip Utama:** Integritas data ilmiah lebih tinggi daripada sekadar estetika peta.
* **Visual:** Tabel metrik kualitas data spasial aktual.
* **Objektif:** Menunjukkan komitmen transparansi data tanpa manipulasi spasial fiktif.

---

### SLIDE 14 — PENEGASAN SPASIAL: HOMEPORT & LOGBOOK
* **Poin Kunci:**
  * **1. Pangkalan Kapal (Homeport) $\neq$ Live Vessel Tracking:**
    * Menunjukkan lokasi pelabuhan registrasi armada, bukan posisi satelit kapal saat ini di tengah laut.
  * **2. Logbook Historis $\neq$ Live AIS Radar:**
    * Menunjukkan arsip catatan perjalanan masa lalu untuk keperluan evaluasi kepatuhan, bukan pelacak waktu-nyata (*real-time*).
  * Mencegah ekspektasi keliru dari para pemangku kepentingan.
* **Visual:** Kotak perbandingan visual: *"Data Registrasi/Historis"* vs *"Live Telemetry"*.
* **Objektif:** Mengklarifikasi batasan semantik fitur spasial.

---

### SLIDE 15 — MODUL PELAPORAN & EKSPOR DATA
* **Poin Kunci:**
  * **Laporan Manajerial:**
    * Rekapitulasi Produksi menurut WPP, Kabupaten, dan Pelabuhan.
    * Distribusi Komoditas Ikan Unggulan (Cakalang, Madidihang, Tongkol).
    * Evaluasi Kinerja dan Jam Operasi Alat Tangkap.
  * **Ekspor Aman:**
    * Format Excel (`.xlsx`) dengan proteksi sanitasi formula injection.
    * Layout Cetak Resmi (*Print PDF*) siap tanda tangan pejabat dinas.
* **Visual:** Mockup lembar laporan tabular dan dokumen ekspor.
* **Objektif:** Menunjukkan kemudahan diseminasi laporan perikanan.

---

### SLIDE 16 — KEAMANAN SISTEM & TATA KELOLA DATA
* **Poin Kunci:**
  * **Role-Based Access Control (RBAC):** Pemisahan hak akses ketat (Super Admin, Admin DKP, Petugas Lapangan, Verifikator, Viewer).
  * **Proteksi Mutasi Data:** Perlindungan CSRF, validasi Form Request, dan Model Mass Assignment protection.
  * **Keamanan Output:** Sanitasi Blade escaping untuk mencegah serangan Cross-Site Scripting (XSS).
  * **Keamanan API & Lingkungan:** Rate limiting pada endpoint publik dan isolasi total berkas rahasia `.env`.
* **Visual:** Matriks hak akses peran dan ikon perisai keamanan.
* **Objektif:** Menegaskan kesiapan keamanan data tingkat enterprise.

---

### SLIDE 17 — ARSITEKTUR TEKNOLOGI (TECH STACK)
* **Poin Kunci:**
  * **Backend Framework:** Laravel 13.x (Clean MVC & Service Layer)
  * **Bahasa Pemrograman:** PHP 8.4.x (Tipe data ketat & performa tinggi)
  * **Database Engine:** MySQL / MariaDB (Relasi Foreign Key `ON DELETE RESTRICT`)
  * **Frontend UI & Map:** Blade Templates, Tailwind CSS, MapLibre GL JS 4.7.1
  * **Asset Pipeline:** Vite 6.x
* **Visual:** Diagram tumpukan teknologi (*technology stack stackup*).
* **Objektif:** Memberikan gambaran ketahanan teknis infrastruktur sistem.

---

### SLIDE 18 — DARI DATA MENUJU INFORMASI KEPUTUSAN
* **Poin Kunci:**
  * Sistem mengintegrasikan data transaksi menjadi informasi bernilai tinggi:
    $$\text{Data Input} \longrightarrow \text{Validasi} \longrightarrow \text{Statistik} \longrightarrow \text{GIS} \longrightarrow \text{Reporting} \longrightarrow \text{Decision Support}$$
  * Membantu Dinas Kelautan dan Perikanan Aceh dalam:
    * Memantau intensitas pemanfaatan sumberdaya di WPP 571 dan 572.
    * Mengetahui tren musim dan komoditas perikanan unggulan.
    * Merencanakan alokasi fasilitas pelabuhan dan sarana penangkapan.
* **Visual:** Infografis transformasi data operasional menjadi kebijakan berbasis bukti (*evidence-based policy*).
* **Objektif:** Merangkum nilai strategis sistem bagi pembangunan perikanan Aceh.

---

### SLIDE 19 — BATASAN SISTEM (KNOWN LIMITATIONS)
* **Poin Kunci:**
  * **1. Kelengkapan GPS Lapangan:** Sebagian besar pencatatan *effort* masih konvensional tanpa telemetry GPS otomatis.
  * **2. Batas Poligon Fishing Ground:** Menunggu penetapan peta spasial resmi dari instansi pemerintah.
  * **3. Fisheries Statistics $\neq$ Full Stock Assessment:** Sistem menyajikan statistik produksi dan CPUE, bukan model prediksi biomassa ikan laut lepas (*maximum sustainable yield model*).
* **Visual:** Kotak transparansi batasan sistem dengan penanda verifikasi ilmiah.
* **Objektif:** Menyampaikan batasan sistem secara jujur dan profesional.

---

### SLIDE 20 — RENCANA PENGEMBANGAN (ROADMAP)
* **Poin Kunci:**
  * **Status Saat Ini (F11 Approved):**
    * Sistem inti, master data, transaksi hulu-hilir, statistik CPUE, pelaporan, dan 7 layer WebGIS berfungsi 100%.
  * **Rencana Masa Depan (Future Roadmap):**
    * Integrasi poligon resmi Fishing Ground setelah diterbitkan DKP/KKP.
    * Penguatan integrasi telemetry satelit kapal penangkap ikan.
    * Pengembangan modul analitika biologi lanjutan (struktur ukuran & kematangan gonad).
    * Pelatihan operasional bagi enumerator di seluruh pelabuhan Aceh.
* **Visual:** Timeline roadmap horizontal (Status Existing $\rightarrow$ Tahap Pengembangan Lanjutan).
* **Objektif:** Menutup presentasi dengan visi keberlanjutan sistem ke depan.
