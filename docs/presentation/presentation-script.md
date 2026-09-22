# Naskah Presentasi (Presentation Script)
**Sistem Informasi & Statistik Perikanan Tangkap Provinsi Aceh**

```text
System: Sistem Informasi Perikanan Tangkap Aceh
Stage: F11 — Presentation Script
Target Duration: 15–20 Menit
Language: Bahasa Indonesia (Natural, Professional, & Engaging)
```

---

### SLIDE 1 — TITLE SLIDE
* **Slide:** Judul Utama
* **Visual:** Layar pembuka dengan logo Provinsi Aceh, judul *"Sistem Informasi & Statistik Perikanan Tangkap Provinsi Aceh"*, dan sub-judul *"Integrasi Data Hulu-ke-Hilir: Operasional Penangkapan, Mesin Statistik, Pelaporan, dan WebGIS"*.
* **Presenter Narrative:**
  > "Assalamu'alaikum Warahmatullahi Wabarakatuh, Selamat pagi/siang Bapak, Ibu, dan seluruh jajaran pemangku kepentingan perikanan Provinsi Aceh.
  >
  > Pada kesempatan hari ini, kami dengan bangga mempresentasikan **Sistem Informasi & Statistik Perikanan Tangkap Provinsi Aceh**. Sistem ini merupakan platform digital komprehensif yang dirancang untuk mengintegrasikan seluruh tata kelola data perikanan tangkap di wilayah Aceh—mulai dari pencatatan hulu di laut, transaksi pendaratan di pelabuhan, pemrosesan mesin statistik perikanan, hingga visualisasi spasial berbasis WebGIS. Mari kita mulai dengan melihat latar belakang dan tantangan utama yang mendasari pembangunan sistem ini."
* **Transition:** *"Mari kita lihat kondisi pengelolaan data perikanan sebelum sistem ini dibangun pada slide berikutnya."*

---

### SLIDE 2 — LATAR BELAKANG & TANTANGAN DATA
* **Slide:** Latar Belakang & Tantangan
* **Visual:** Diagram perbandingan data terfragmentasi vs data terintegrasi antar-pelabuhan di Aceh.
* **Presenter Narrative:**
  > "Bapak dan Ibu sekalian, Provinsi Aceh memiliki potensi kelautan yang sangat besar dengan bentang perairan yang mencakup 23 Kabupaten/Kota. Namun, selama ini pendataan operasional perikanan di lapangan menghadapi sejumlah tantangan:
  > Pertama, data kapal, aktivitas nelayan, dan catatan pendaratan di TPI sering kali tercatat secara terpisah dan terfragmentasi di masing-masing pelabuhan.
  > Kedua, sulit bagi kita untuk melacak riwayat dari mana ikan berasal, siapa nahkodanya, dan alat tangkap apa yang digunakan saat sebuah komoditas tiba di dermaga.
  > Dan ketiga, para pengambil kebijakan membutuhkan angka statistik produksi dan pemetaan geospasial yang akurat serta dapat dipertanggungjawabkan untuk merumuskan kebijakan pengelolaan perikanan yang berkelanjutan."
* **Transition:** *"Untuk menjawab seluruh tantangan tersebut, sistem ini dibangun dengan tujuan-tujuan strategis yang jelas."*

---

### SLIDE 3 — TUJUAN SISTEM
* **Slide:** Tujuan & Fungsi Utama
* **Visual:** 5 pilar tujuan sistem dalam bentuk kartu visual berwarna.
* **Presenter Narrative:**
  > "Sistem Informasi Perikanan Tangkap Aceh hadir dengan lima tujuan utama:
  > 1. Mendigitalkan seluruh siklus operasional perikanan dari hulu ke hilir.
  > 2. Menerapkan standardisasi internasional, mengadopsi taksonomi spesies FAO ASFIS dan klasifikasi alat tangkap FAO ISSCFG.
  > 3. Menyediakan mesin kalkulasi statistik otomatis, termasuk perhitungan laju tangkap atau CPUE yang aman dan terstandar.
  > 4. Menyajikan visualisasi geospasial interaktif 7 layer menggunakan teknologi WebGIS modern.
  > 5. Menyediakan sarana pelaporan terverifikasi sebagai instrumen pendukung keputusan atau *decision support system* bagi jajaran pimpinan."
* **Transition:** *"Selanjutnya, mari kita lihat ruang lingkup wilayah yang dicakup oleh aplikasi ini."*

---

### SLIDE 4 — RUANG LINGKUP (SCOPE) SISTEM
* **Slide:** Wilayah Cakupan Aceh & WPPNRI
* **Visual:** Peta administratif Provinsi Aceh yang terhubung dengan wilayah WPPNRI 571 dan 572.
* **Presenter Narrative:**
  > "Secara administratif, sistem ini mencakup seluruh 23 Kabupaten/Kota di Provinsi Aceh, dari Sabang hingga Aceh Singkil dan Aceh Tamiang.
  > Dari sudut pandang pengelolaan sumberdaya ikan nasional, perairan Aceh terbagi ke dalam dua Wilayah Pengelolaan Perikanan yang sangat strategis:
  > Yaitu **WPP 571** yang meliputi perairan Selat Malaka dan Laut Andaman di pesisir timur-utara, serta **WPP 572** yang mencakup Samudera Hindia di sebelah barat-selatan Sumatera.
  > Seluruh data transaksi pelayaran dan pangkalan pelabuhan di sistem dikelompokkan secara terstruktur berdasarkan batas wilayah ini."
* **Transition:** *"Bagaimana sistem mengorganisir data operasional tersebut? Mari kita lihat pembagian modul utamanya."*

---

### SLIDE 5 — MODUL UTAMA APLIKASI
* **Slide:** Inventaris Modul Sistem
* **Visual:** Diagram hierarki 5 pilar modul: Master Data, Data Collection, Statistics, Reporting, dan WebGIS.
* **Presenter Narrative:**
  > "Aplikasi ini dibangun dengan struktur modular yang sangat rapi dan saling terhubung:
  > Pertama, **Master Data** sebagai fondasi referensi (nelayan KUSUKA, armada kapal, jenis ikan, alat tangkap, dan pelabuhan).
  > Kedua, **Data Collection** untuk mencatat transaksi lapangan (trip pelayaran, upaya setting alat tangkap, tangkapan, pendaratan, dan logbook).
  > Ketiga, **Fisheries Statistics** yang memvalidasi data dan menghitung agregasi statistik.
  > Keempat, **Reporting & Export** untuk diseminasi laporan dan ekspor dokumen Excel/PDF.
  > Dan kelima, **WebGIS** yang memetakan seluruh aktivitas tersebut ke dalam peta spasial interaktif."
* **Transition:** *"Mari kita telusuri bagaimana rantai data mengalir dari satu entitas ke entitas berikutnya."*

---

### SLIDE 6 — WORKFLOW & DATA MODEL UTAMA
* **Slide:** Alur Rantai Operasional Perikanan
* **Visual:** Diagram alir horizontal: Nelayan $\rightarrow$ Kapal $\rightarrow$ Trip $\rightarrow$ Effort $\rightarrow$ Catch $\rightarrow$ Landing.
* **Presenter Narrative:**
  > "Bapak dan Ibu, inilah jantung dari arsitektur data sistem perikanan kita. Alurnya sangat konsisten:
  > Data dimulai dari **Nelayan** yang memiliki atau mengoperasikan **Kapal**.
  > Kapal tersebut kemudian melakukan pelayaran penangkapan atau **Fishing Trip**.
  > Selama berada di laut, kapal melakukan satu atau lebih operasi penurunan jaring atau **Fishing Effort**.
  > Dari setiap effort, dicatatlah hasil tangkapan per jenis ikan atau **Catch**.
  > Dan ketika kapal kembali ke dermaga, hasil tangkapan tersebut ditimbang dan didaratkan secara resmi melalui modul **Landing**.
  > Alur ini memastikan seluruh data saling terikat secara logis dan tidak ada transaksi yang terputus."
* **Transition:** *"Mengapa keterikatan antar-data ini menjadi begitu krusial? Jawabannya adalah keterlacakan data."*

---

### SLIDE 7 — KETERLACAKAN DATA (DATA TRACEABILITY)
* **Slide:** Nilai Strategis Data Traceability
* **Visual:** Ilustrasi drill-down dari total angka produksi hingga rincian trip dan profil nahkoda.
* **Presenter Narrative:**
  > "Di dalam sistem ini, tidak ada satu pun angka statistik yang muncul secara tiba-tiba tanpa riwayat transaksi.
  > Konsep ini kita sebut sebagai **Data Traceability** atau keterlacakan data.
  > Jika pimpinan melihat laporan bahwa terdapat 10 ton Ikan Cakalang didaratkan di PPS Lampulo, sistem memungkinkan kita untuk menelusuri kembali:
  > Berapa kapal yang berlayar untuk menghasilkan 10 ton tersebut? Siapa nahkodanya? Kapan tanggal keberangkatannya? Alat tangkap apa yang digunakan dan berapa jam jaring direndam di laut?
  > Ini memberikan tingkat kepercayaan dan integritas data yang luar biasa bagi instansi."
* **Transition:** *"Setelah data transaksi terkumpul, bagaimana mesin statistik memprosesnya? Mari kita lihat pada slide 8."*

---

### SLIDE 8 — MESIN STATISTIK PERIKANAN
* **Slide:** Arsitektur Mesin Statistik
* **Visual:** Pipeline validasi data, pengelompokan filter, dan agregasi statistik bulanan/tahunan.
* **Presenter Narrative:**
  > "Mesin statistik perikanan pada sistem bekerja dengan prinsip kehati-hatian.
  > Sebelum data masuk ke dalam kalkulasi resmi, petugas verifikator memeriksa kelengkapan transaksi. Data yang telah tervalidasi kemudian diagregasi secara dinamis berdasarkan filter multi-kriteria—seperti tahun, bulan, alat tangkap, komoditas, maupun wilayah WPP.
  > Sistem juga secara otomatis memisahkan catatan penangkapan di laut dengan catatan timbang di dermaga untuk mencegah terjadinya penghitungan ganda atau *double-counting*."
* **Transition:** *"Salah satu indikator utama dalam perikanan adalah CPUE. Bagaimana formula yang diterapkan?"*

---

### SLIDE 9 — PERHITUNGAN CATCH PER UNIT EFFORT (CPUE)
* **Slide:** Formula & Kalkulasi CPUE
* **Visual:** Dua formula formal CPUE (kg/jam dan kg/trip) dengan penjelasan variabel.
* **Presenter Narrative:**
  > "CPUE atau *Catch Per Unit Effort* adalah ukuran efisiensi penangkapan standar dalam ilmu perikanan.
  > Sistem kami menyediakan dua metrik CPUE yang jelas perbedaannya:
  > Pertama, **CPUE per Jam Operasi (kg/jam)**: dihitung dengan membagi total berat tangkapan dengan total durasi penarikan jaring di laut. Ini menggambarkan kelimpahan riil di titik tangkap.
  > Kedua, **CPUE per Trip (kg/trip)**: dihitung dengan membagi total tangkapan dengan jumlah pelayaran, menggambarkan produktivitas rata-rata armada per trip.
  > Seluruh perhitungan dilengkapi algoritma pengaman pembagian nol (*zero-safe guard*), sehingga jika durasi belum terisi, sistem tidak akan mengalami galat sistem."
* **Transition:** *"Selain CPUE, sistem juga mendukung estimasi produksi pelabuhan melalui metode raising factor."*

---

### SLIDE 10 — ESTIMASI TANGKAPAN & RAISING FACTOR
* **Slide:** Estimasi Produksi & Raising Factor
* **Visual:** Diagram estimasi sampling pelabuhan dengan formula Raising Factor.
* **Presenter Narrative:**
  > "Di lapangan, sering kali tidak seluruh kapal dapat didata secara sensus penuh karena keterbatasan petugas dermaga.
  > Untuk kondisi tersebut, sistem menyediakan modul **Estimasi Tangkapan** berbasis *Raising Factor*.
  > Dengan membandingkan total kapal yang beroperasi dengan kapal yang berhasil disampling, sistem dapat memproyeksikan estimasi total produksi pelabuhan secara ilmiah.
  > Perlu kami tegaskan bahwa ini adalah metode estimasi operasional pendaratan di pelabuhan, bukan estimasi biomassa stok ikan di laut lepas."
* **Transition:** *"Kini kita beralih ke salah satu fitur paling visual dan interaktif dalam sistem: WebGIS."*

---

### SLIDE 11 — WEBGIS: 7 LAYER SPASIAL MAPLIBRE GL JS
* **Slide:** Peta Interaktif 7 Layer WebGIS
* **Visual:** Tampilan peta MapLibre GL JS Aceh dengan 7 penanda layer aktif berwarna.
* **Presenter Narrative:**
  > "Sistem Informasi Perikanan Aceh dilengkapi peta WebGIS modern berbasis engine **MapLibre GL JS 4.7.1**. Peta ini sangat cepat, responsif di perangkat mobile, dan menyajikan 7 layer spasial terpadu:
  > 1. Titik oranye: **Fishing Effort**, posisi koordinat penurunan jaring aktual di laut.
  > 2. Titik biru: **Landing Site**, 12 pelabuhan perikanan dan TPI aktif di pesisir Aceh.
  > 3. Titik ungu: **Homeport Kapal**, sebaran pelabuhan pangkalan tempat kapal terdaftar.
  > 4. Titik kuning: **Logbook Historis**, catatan rekam jejak operasional masa lalu.
  > 5. Panel ringkasan: **Master Fishing Ground**.
  > 6 & 7. Poligon transparan **WPP 571 dan WPP 572** yang membatasi perairan Selat Malaka dan Samudera Hindia.
  > Setiap layer dapat diaktifkan atau dinonaktifkan secara independen oleh pengguna."
* **Transition:** *"Bagaimana data di peta ini diperbarui? Mari kita lihat arsitektur integrasi data GIS."*

---

### SLIDE 12 — ARSITEKTUR INTEGRASI DATA GIS
* **Slide:** Aliran Data Spasial Realtime
* **Visual:** Diagram alir dari database MySQL $\rightarrow$ REST API `/gis/data` $\rightarrow$ GeoJSON `[lng, lat]` $\rightarrow$ MapLibre.
* **Presenter Narrative:**
  > "Satu keunggulan arsitektural penting: **GIS pada sistem ini bukan peta terpisah atau gambar mati**.
  > Data pada peta terhubung langsung ke database operasional melalui endpoint REST API `GET /gis/data`.
  > Backend Laravel secara otomatis mengubah koordinat transaksi menjadi format baku GeoJSON dengan urutan bujur dan lintang `[longitude, latitude]`.
  > Artinya, setiap kali petugas menambahkan trip atau pendaratan baru di pelabuhan, data peta akan langsung terbarui secara dinamis tanpa perlu proses konversi manual."
* **Transition:** *"Namun, bagaimana dengan kualitas data koordinat di lapangan? Mari kita lihat transparansi data pada slide 13."*

---

### SLIDE 13 — INTEGRITAS KUALITAS DATA GIS
* **Slide:** Transparansi Kualitas Data Spasial
* **Visual:** Tabel perbandingan data terpetakan vs data non-GPS aktual hasil audit F9.
* **Presenter Narrative:**
  > "Bapak dan Ibu, kami memegang prinsip teguh: **Integritas data ilmiah jauh lebih penting daripada sekadar membuat peta terlihat penuh secara fiktif**.
  > Berdasarkan data aktual sistem:
  > Dari 62 catatan *fishing effort*, terdapat 10 data yang memiliki koordinat GPS presisi dan diplot di laut. Sedangkan 52 data lainnya dicatat secara konvensional tanpa GPS.
  > Sistem kami **secara sadar TIDAK mengarang titik palsu di laut atau meletakkannya di koordinat 0,0**. Data non-GPS tetap dihitung pada statistik angka, namun tidak digambar pada peta.
  > Begitu pula 8 Master Fishing Ground yang belum memiliki batas poligon resmi dari kementerian, kami beri status transparan: *Belum Tersedia Geometri Resmi*."
* **Transition:** *"Selain itu, ada penegasan penting terkait batas kemampuan fitur spasial kita."*

---

### SLIDE 14 — PENEGASAN SPASIAL: HOMEPORT & LOGBOOK
* **Slide:** Batasan Semantik Pangkalan & Logbook
* **Visual:** Kotak penegasan: Homeport $\neq$ Live Satellite Tracking, Logbook $\neq$ Live AIS.
* **Presenter Narrative:**
  > "Agar tidak terjadi kesalahpahaman pemangku kepentingan, kami menegaskan dua hal:
  > Pertama, layer **Homeport Kapal** menunjukkan pelabuhan registrasi tempat kapal beroperasi secara administratif, **bukan** sinyal satelit posisi kapal waktu-nyata di laut.
  > Kedua, layer **Logbook Historis** merupakan rekaman catatan harian pelayaran masa lalu untuk keperluan audit kepatuhan, **bukan** transmiter radar pelacak langsung.
  > Penegasan ini membuktikan bahwa sistem memberikan informasi yang jujur, akurat, dan sesuai dengan kapasitas teknologi aktual."
* **Transition:** *"Setelah data dan peta tersaji, bagaimana sistem membantu pembuatan laporan manajerial?"*

---

### SLIDE 15 — MODUL PELAPORAN & EKSPOR DATA
* **Slide:** Laporan Eksekutif & Ekspor Excel/PDF
* **Visual:** Contoh tampilan lembar kerja laporan tabular dan tombol ekspor aman.
* **Presenter Narrative:**
  > "Modul Pelaporan menyajikan rekapitulasi data komprehensif bagi pimpinan:
  > Kita dapat melihat tren produksi komoditas unggulan seperti Cakalang dan Tuna Sirip Kuning, evaluasi produktivitas alat tangkap pukat cincin vs rawai, serta perbandingan produksi per kabupaten.
  > Seluruh laporan dapat diekspor secara instan ke format **Excel (.xlsx)** yang telah dilengkapi proteksi sanitasi formula, atau dicetak langsung ke dalam format dokumen resmi PDF yang siap ditandatangani."
* **Transition:** *"Bagaimana dengan ketahanan dan keamanan sistem ini secara keseluruhan?"*

---

### SLIDE 16 — KEAMANAN SISTEM & TATA KELOLA DATA
* **Slide:** Arsitektur Keamanan & RBAC
* **Visual:** Matriks 5 peran pengguna, proteksi CSRF, XSS, dan enkripsi server.
* **Presenter Narrative:**
  > "Sistem menerapkan standar keamanan data berlapis:
  > Hak akses dikendalikan melalui sistem *Role-Based Access Control* dengan 5 tingkatan pengguna—dari Super Admin, Admin DKP, Petugas Lapangan, Verifikator, hingga Viewer publik.
  > Seluruh form transaksi dilindungi dari serangan CSRF, input divalidasi ketat, tampilan bebas dari celah XSS, dan endpoint publik dilindungi pembatasan laju permintaan (*rate limiting*). Seluruh kata sandi dan kredensial server terenkripsi dengan aman."
* **Transition:** *"Mari kita lihat fondasi teknologi yang menopang aplikasi ini pada slide 17."*

---

### SLIDE 17 — ARSITEKTUR TEKNOLOGI (TECH STACK)
* **Slide:** Tumpukan Teknologi Modern
* **Visual:** Diagram stack: Laravel 13, PHP 8.4, MariaDB, MapLibre GL JS, TailwindCSS, Vite.
* **Presenter Narrative:**
  > "Aplikasi ini dibangun di atas tumpukan teknologi modern berkinerja tinggi:
  > Menggunakan framework **Laravel 13** dan runtime **PHP 8.4** dengan arsitektur Clean MVC dan Service Layer yang modular.
  > Basis data menggunakan **MariaDB/MySQL** dengan integritas foreign key ketat.
  > Frontend ditenagai antarmuka responsif Tailwind CSS dan engine peta **MapLibre GL JS 4.7.1**.
  > Seluruh test suite otomatis telah lulus 100% dengan 273 skenario pengujian tanpa kegagalan."
* **Transition:** *"Bagaimana integrasi teknologi ini mewujudkan nilai nyata bagi Dinas Kelautan dan Perikanan?"*

---

### SLIDE 18 — DARI DATA MENUJU INFORMASI KEPUTUSAN
* **Slide:** Transformasi Data Menjadi Keputusan
* **Visual:** Infografis: Data Input $\rightarrow$ Validasi $\rightarrow$ Statistik $\rightarrow$ GIS $\rightarrow$ Reporting $\rightarrow$ Decision Support.
* **Presenter Narrative:**
  > "Bapak dan Ibu sekalian, inilah esensi utama dari sistem informasi ini:
  > Mengubah data transaksi mentah di pelabuhan dan laut menjadi **informasi strategis pendukung keputusan** (*Decision Support*).
  > Dengan sistem ini, Pemerintah Provinsi Aceh dapat:
  > Mengidentifikasi wilayah perairan yang paling produktif di WPP 571 dan 572, mengetahui pola musim komoditas ikan unggulan, mengoptimalkan alokasi sarana pelabuhan, serta menyusun kebijakan perlindungan sumberdaya laut berbasis bukti nyata (*evidence-based policy*)."
* **Transition:** *"Sebagai bentuk akuntabilitas profesional, mari kita cermati batasan sistem saat ini."*

---

### SLIDE 19 — BATASAN SISTEM (KNOWN LIMITATIONS)
* **Slide:** Transparansi Batasan Sistem
* **Visual:** Poin-poin limitasi empiris yang tercatat resmi pada laporan audit.
* **Presenter Narrative:**
  > "Kami menyampaikan batasan sistem saat ini secara transparan:
  > 1. Kelengkapan koordinat GPS penangkapan masih bergantung pada pencatatan mandiri nelayan di lapangan.
  > 2. Batas poligon definitif 8 Fishing Ground masih menunggu penetapan peta zonasi resmi dari dinas/kementerian.
  > 3. Dan yang terpenting: Sistem ini adalah **Sistem Statistik & Informasi Perikanan**, bukan model prediksi biomassa stok ikan laut lepas (*full stock assessment*).
  > Seluruh batasan ini telah terdokumentasi dan menjadi pijakan bagi pengembangan tahap selanjutnya."
* **Transition:** *"Terakhir, mari kita lihat peta jalan pengembangan masa depan aplikasi ini."*

---

### SLIDE 20 — RENCANA PENGEMBANGAN (ROADMAP)
* **Slide:** Rencana Pengembangan Masa Depan
* **Visual:** Garis waktu roadmap: Status Saat Ini (F11 Approved) $\rightarrow$ Pengembangan Lanjutan.
* **Presenter Narrative:**
  > "Menatap ke depan, rencana pengembangan sistem ini mencakup:
  > Pertama, pengintegrasian poligon resmi Fishing Ground segera setelah dirilis oleh instansi berwenang.
  > Kedua, penguatan integrasi data satelit pemantauan kapal penangkap ikan.
  > Ketiga, penambahan modul analisis morfometrik biologi ikan secara mendalam.
  > Dan keempat, pelaksanaan program pelatihan bagi seluruh petugas pencatat dan syahbandar di pelabuhan perikanan se-Aceh.
  >
  > Demikian presentasi kami. Sistem Informasi & Statistik Perikanan Tangkap Provinsi Aceh kini telah siap, stabil, dan teruji untuk mendukung kedaulatan data kelautan Aceh. Terima kasih atas perhatian Bapak dan Ibu sekalian. Wassalamu'alaikum Warahmatullahi Wabarakatuh."
